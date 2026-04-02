<?php
// api_submit.php — records exercise attempt/solve, awards XP
require_once __DIR__ . '/config.php';
sessionStart();

header('Content-Type: application/json');

$user = currentUser();
if (!$user) { http_response_code(401); echo json_encode(['error' => 'Not authenticated']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$exerciseId = (int)($body['exercise_id'] ?? 0);

if (!$exerciseId) { http_response_code(400); echo json_encode(['error' => 'Missing exercise_id']); exit; }

try {
    $db = getDB();

    // Fetch exercise
    $stmt = $db->prepare('SELECT * FROM exercises WHERE id = ? LIMIT 1');
    $stmt->execute([$exerciseId]);
    $ex = $stmt->fetch();
    if (!$ex) { http_response_code(404); echo json_encode(['error' => 'Exercise not found']); exit; }

    // Check existing progress
    $ps = $db->prepare('SELECT * FROM user_progress WHERE user_id = ? AND exercise_id = ? LIMIT 1');
    $ps->execute([$user['id'], $exerciseId]);
    $existing = $ps->fetch();

    $firstSolve = false;
    $xpGained   = 0;

    if (!$existing) {
        // First attempt — insert as solved (we trust the browser run)
        $db->prepare('
            INSERT INTO user_progress (user_id, exercise_id, status, attempts, solved_at)
            VALUES (?, ?, "solved", 1, NOW())
        ')->execute([$user['id'], $exerciseId]);
        $firstSolve = true;
        $xpGained   = $ex['xp_reward'];
    } elseif ($existing['status'] === 'attempted') {
        // Mark as solved on subsequent attempt
        $db->prepare('
            UPDATE user_progress SET status = "solved", attempts = attempts + 1, solved_at = NOW()
            WHERE user_id = ? AND exercise_id = ?
        ')->execute([$user['id'], $exerciseId]);
        $firstSolve = true;
        $xpGained   = $ex['xp_reward'];
    } else {
        // Already solved — just log the attempt
        $db->prepare('
            UPDATE user_progress SET attempts = attempts + 1 WHERE user_id = ? AND exercise_id = ?
        ')->execute([$user['id'], $exerciseId]);
    }

    // Award XP
    if ($xpGained > 0) {
        $db->prepare('UPDATE users SET xp = xp + ? WHERE id = ?')->execute([$xpGained, $user['id']]);
    }

    // Fetch updated user
    $us = $db->prepare('SELECT xp FROM users WHERE id = ? LIMIT 1');
    $us->execute([$user['id']]);
    $updatedUser = $us->fetch();
    $totalXP     = (int)$updatedUser['xp'];

    // Recalculate rank
    $rankInfo  = rankForXP($totalXP);
    $rankTitle = $rankInfo['current']['title'];
    $db->prepare('UPDATE users SET rank_title = ? WHERE id = ?')->execute([$rankTitle, $user['id']]);

    // Update session
    $_SESSION['user']['xp']         = $totalXP;
    $_SESSION['user']['rank_title'] = $rankTitle;

    echo json_encode([
        'ok'          => true,
        'first_solve' => $firstSolve,
        'xp_gained'   => $xpGained,
        'total_xp'    => $totalXP,
        'rank_title'  => $rankTitle,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
