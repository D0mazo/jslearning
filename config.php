<?php
// ============================================================
//  config.php  —  edit these values before uploading
// ============================================================

define('DB_HOST', 'sql111.infinityfree.com');
define('DB_NAME', 'if0_41004146_tasks');
define('DB_USER', 'if0_41004146');
define('DB_PASS', 'D0mukas1');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'JSLearn');
define('SITE_URL',  'https://decode.fwh.is'); // no trailing slash

// Session lifetime (seconds)
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days

// Rank thresholds (XP required)
define('RANKS', [
    ['title' => 'Beginner',    'min_xp' =>    0, 'icon' => '○'],
    ['title' => 'Apprentice',  'min_xp' =>   50, 'icon' => '◎'],
    ['title' => 'Developer',   'min_xp' =>  150, 'icon' => '●'],
    ['title' => 'Engineer',    'min_xp' =>  300, 'icon' => '◆'],
    ['title' => 'Senior',      'min_xp' =>  500, 'icon' => '◈'],
    ['title' => 'Architect',   'min_xp' =>  800, 'icon' => '★'],
    ['title' => 'Expert',      'min_xp' => 1200, 'icon' => '✦'],
    ['title' => 'Master',      'min_xp' => 1800, 'icon' => '✸'],
    ['title' => 'Legend',      'min_xp' => 2500, 'icon' => '⬡'],
]);

// ── PDO connection (shared across all pages) ─────────────────────────────────
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

// ── Session helpers ───────────────────────────────────────────────────────────
function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_set_cookie_params(SESSION_LIFETIME);
        session_start();
    }
}

function currentUser() {
    sessionStart();
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

// ── Rank calculator ───────────────────────────────────────────────────────────
function rankForXP($xp) {
    $current = RANKS[0];
    $next    = null;
    foreach (RANKS as $i => $rank) {
        if ($xp >= $rank['min_xp']) {
            $current = $rank;
            $next    = RANKS[$i + 1] ?? null;
        }
    }
    $progress = 0;
    if ($next) {
        $progress = ($xp - $current['min_xp']) / ($next['min_xp'] - $current['min_xp']) * 100;
    } else {
        $progress = 100;
    }
    return ['current' => $current, 'next' => $next, 'progress' => round($progress)];
}

// ── CSRF helpers ─────────────────────────────────────────────────────────────
function csrfToken() {
    sessionStart();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}