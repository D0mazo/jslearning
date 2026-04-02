<?php
// includes/header.php
// Usage:  $pageTitle = 'Dashboard'; require 'includes/header.php';
require_once __DIR__ . '/../config.php';
sessionStart();
$user = currentUser();
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>

<header class="site-nav">
  <div class="inner">
    <a href="<?= SITE_URL ?>/index.php" class="brand"><?= SITE_NAME ?><span>.</span></a>
    <nav>
      <a href="<?= SITE_URL ?>/exercises.php" class="<?= basename($_SERVER['PHP_SELF']) === 'exercises.php' ? 'active' : '' ?>">Exercises</a>
      <?php if ($user): ?>
      <a href="<?= SITE_URL ?>/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Progress</a>
      <a href="<?= SITE_URL ?>/leaderboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'leaderboard.php' ? 'active' : '' ?>">Leaderboard</a>
      <?php endif; ?>
    </nav>
    <?php if ($user): ?>
    <div class="nav-user">
      <span class="nav-xp"><?= $user['rank_title'] ?> · <?= number_format($user['xp']) ?> XP</span>
      <a href="<?= SITE_URL ?>/logout.php" class="btn btn-secondary btn-sm">Sign out</a>
    </div>
    <?php else: ?>
    <div class="nav-user">
      <a href="<?= SITE_URL ?>/login.php"    class="btn btn-secondary btn-sm">Sign in</a>
      <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Register</a>
    </div>
    <?php endif; ?>
  </div>
</header>

<main>
