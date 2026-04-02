<?php
require_once __DIR__ . '/config.php';
requireLogin();
$user = currentUser();

$db = getDB();

// Refresh user from DB
$us = $db->prepare('SELECT * FROM users WHERE id = ?');
$us->execute([$user['id']]);
$user = $us->fetch();
$_SESSION['user'] = $user;

// Stats
$statsStmt = $db->prepare('
    SELECT
      COUNT(*) as attempted,
      SUM(status = "solved") as solved
    FROM user_progress WHERE user_id = ?
');
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

// Per-difficulty breakdown
$diffStmt = $db->prepare('
    SELECT e.difficulty, COUNT(*) as total,
           SUM(up.status = "solved") as solved
    FROM exercises e
    LEFT JOIN user_progress up ON up.exercise_id = e.id AND up.user_id = ?
    GROUP BY e.difficulty ORDER BY e.difficulty
');
$diffStmt->execute([$user['id']]);
$diffRows = $diffStmt->fetchAll();

// Total exercise count
$totalExCount = (int)$db->query('SELECT COUNT(*) FROM exercises')->fetchColumn();

// Recent solves
$recentStmt = $db->prepare('
    SELECT e.title, e.slug, e.difficulty, e.xp_reward, up.solved_at
    FROM user_progress up
    JOIN exercises e ON e.id = up.exercise_id
    WHERE up.user_id = ? AND up.status = "solved"
    ORDER BY up.solved_at DESC LIMIT 8
');
$recentStmt->execute([$user['id']]);
$recent = $recentStmt->fetchAll();

// Rank info
$rankInfo  = rankForXP((int)$user['xp']);
$diffLabels = [1 => 'Easy', 2 => 'Medium', 3 => 'Hard', 4 => 'Expert'];

$pageTitle = 'My Progress';
require 'includes/header.php';
?>

    <div class="site-wrap" style="padding-top:2rem; padding-bottom:3rem;">

        <?php if (!empty($_GET['welcome'])): ?>
            <div class="alert alert-success mb-2">Welcome to <?= SITE_NAME ?>! Start with the first few Easy exercises to earn XP.</div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-2" style="flex-wrap:wrap; gap:1rem;">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($user['username']) ?></h1>
                <p class="mt-1">Track your JavaScript learning progress.</p>
            </div>
            <a href="exercises.php" class="btn btn-primary">Browse exercises →</a>
        </div>

        <!-- Rank card -->
        <div class="card mt-2" style="border-left:4px solid var(--accent);">
            <div class="flex items-center justify-between" style="flex-wrap:wrap; gap:1rem;">
                <div>
                    <div class="text-xs text-muted" style="text-transform:uppercase;letter-spacing:.07em;">Current rank</div>
                    <div style="font-family:var(--ff-body); font-size:1.8rem; font-weight:600; line-height:1.2; margin-top:.1rem;">
                        <?= $rankInfo['current']['icon'] ?> <?= htmlspecialchars($rankInfo['current']['title']) ?>
                    </div>
                    <div class="text-sm text-muted mt-1"><?= number_format((int)$user['xp']) ?> XP total</div>
                </div>
                <?php if ($rankInfo['next']): ?>
                    <div style="min-width:220px; flex:1; max-width:340px;">
                        <div class="flex justify-between text-xs text-muted mb-1">
                            <span>Progress to <?= htmlspecialchars($rankInfo['next']['title']) ?></span>
                            <span><?= $rankInfo['progress'] ?>%</span>
                        </div>
                        <div class="progress-wrap">
                            <div class="progress-bar" style="width:<?= $rankInfo['progress'] ?>%"></div>
                        </div>
                        <div class="text-xs text-muted mt-1">
                            <?= number_format($rankInfo['next']['min_xp'] - (int)$user['xp']) ?> XP to next rank
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-sm" style="color:var(--success);font-weight:600;">🏆 Maximum rank achieved!</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats row -->
        <div class="stat-grid mt-3">
            <div class="stat-box">
                <div class="stat-val"><?= (int)$stats['solved'] ?></div>
                <div class="stat-label">Exercises solved</div>
            </div>
            <div class="stat-box">
                <div class="stat-val"><?= $totalExCount ?></div>
                <div class="stat-label">Total exercises</div>
            </div>
            <div class="stat-box">
                <div class="stat-val"><?= $totalExCount > 0 ? round(((int)$stats['solved'] / $totalExCount) * 100) : 0 ?>%</div>
                <div class="stat-label">Completion</div>
            </div>
            <div class="stat-box">
                <div class="stat-val"><?= number_format((int)$user['xp']) ?></div>
                <div class="stat-label">XP earned</div>
            </div>
        </div>

        <!-- Difficulty breakdown -->
        <div class="card mt-3">
            <h4 style="margin-bottom:1rem;">Progress by difficulty</h4>
            <?php foreach ($diffRows as $row):
                $pct = $row['total'] > 0 ? round($row['solved'] / $row['total'] * 100) : 0;
                ?>
                <div style="margin-bottom:.9rem;">
                    <div class="flex justify-between text-sm mb-1">
        <span>
          <span class="badge badge-<?= $row['difficulty'] ?>"><?= $diffLabels[$row['difficulty']] ?></span>
          &nbsp;<?= (int)$row['solved'] ?> / <?= (int)$row['total'] ?> solved
        </span>
                        <span class="text-muted text-sm"><?= $pct ?>%</span>
                    </div>
                    <div class="progress-wrap">
                        <div class="progress-bar" style="width:<?= $pct ?>%;
                                background:<?= ['','#2e7d55','#f57f17','#c62828','#4527a0'][$row['difficulty']] ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Recent activity -->
        <?php if ($recent): ?>
            <div class="card mt-3">
                <h4 style="margin-bottom:.75rem;">Recent solves</h4>
                <table class="leaderboard-table">
                    <thead>
                    <tr><th>Exercise</th><th>Level</th><th>XP</th><th>Solved</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><a href="exercise.php?slug=<?= urlencode($r['slug']) ?>"><?= htmlspecialchars($r['title']) ?></a></td>
                            <td><span class="badge badge-<?= $r['difficulty'] ?>"><?= $diffLabels[$r['difficulty']] ?></span></td>
                            <td style="font-family:var(--ff-code); font-size:.82rem; color:var(--success);">+<?= $r['xp_reward'] ?></td>
                            <td class="text-muted text-xs"><?= date('M j, Y', strtotime($r['solved_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card mt-3 text-center" style="padding:2.5rem;">
                <p style="margin-bottom:1rem;">No solves yet — start your first exercise now!</p>
                <a href="exercises.php" class="btn btn-primary">Go to exercises</a>
            </div>
        <?php endif; ?>

        <!-- Rank progression reference -->
        <div class="card mt-3">
            <h4 style="margin-bottom:.75rem;">Rank progression</h4>
            <table class="leaderboard-table">
                <thead><tr><th>Rank</th><th>XP required</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach (RANKS as $rank): ?>
                    <tr <?= $rank['title'] === $user['rank_title'] ? 'class="me"' : '' ?>>
                        <td><?= $rank['icon'] ?> <?= htmlspecialchars($rank['title']) ?></td>
                        <td style="font-family:var(--ff-code); font-size:.82rem;"><?= number_format($rank['min_xp']) ?></td>
                        <td>
                            <?php if ((int)$user['xp'] >= $rank['min_xp']): ?>
                                <?= $rank['title'] === $user['rank_title'] ? '<span style="color:var(--accent);font-weight:500;">← current</span>' : '<span style="color:var(--success);">✓ achieved</span>' ?>
                            <?php else: ?>
                                <span class="text-muted text-xs"><?= number_format($rank['min_xp'] - (int)$user['xp']) ?> XP away</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

<?php require 'includes/footer.php'; ?>