<?php
require_once __DIR__ . '/config.php';
sessionStart();
$user = currentUser();
if ($user) { header('Location: dashboard.php'); exit; }

$db         = getDB();
$exCount    = (int)$db->query('SELECT COUNT(*) FROM exercises')->fetchColumn();
$userCount  = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();

$pageTitle = 'Learn JavaScript';
require 'includes/header.php';
?>

<div class="site-wrap" style="padding-top:4rem; padding-bottom:5rem; text-align:center; max-width:680px; margin-left:auto; margin-right:auto;">

  <p class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:.12em; margin-bottom:1rem;">
    A structured JavaScript learning platform
  </p>

  <h1 style="font-size:3rem; line-height:1.15; margin-bottom:1rem;">
    Learn to code<br>one exercise at a time.
  </h1>

  <p style="font-size:1.15rem; color:var(--ink-mid); margin-bottom:2.5rem; line-height:1.8;">
    <?= $exCount ?> carefully crafted JavaScript exercises from
    <em>complete beginner</em> to <em>advanced algorithm design</em>.
    Earn XP, climb ranks, and track exactly how far you've come.
  </p>

  <div class="flex gap-2" style="justify-content:center; flex-wrap:wrap;">
    <a href="register.php" class="btn btn-primary btn-lg">Start learning — it's free</a>
    <a href="exercises.php" class="btn btn-secondary btn-lg">Browse exercises</a>
  </div>

  <!-- Social proof mini stats -->
  <div style="display:flex; gap:3rem; justify-content:center; margin-top:3.5rem; flex-wrap:wrap;">
    <div>
      <div style="font-family:var(--ff-body); font-size:2rem; font-weight:600; color:var(--accent);"><?= $exCount ?>+</div>
      <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:.08em;">Exercises</div>
    </div>
    <div>
      <div style="font-family:var(--ff-body); font-size:2rem; font-weight:600; color:var(--accent);">9</div>
      <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:.08em;">Rank levels</div>
    </div>
    <div>
      <div style="font-family:var(--ff-body); font-size:2rem; font-weight:600; color:var(--accent);"><?= $userCount ?></div>
      <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:.08em;">Learners</div>
    </div>
  </div>

  <hr class="divider" style="margin:3rem auto; max-width:200px;">

  <!-- Topic grid -->
  <h2 style="margin-bottom:1.5rem;">What you'll learn</h2>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; text-align:left;">
    <?php
    $topics = [
      ['Variables & Types',      '○'],
      ['Conditionals',           '◎'],
      ['Loops',                  '⟳'],
      ['Arrays',                 '[]'],
      ['Objects',                '{}'],
      ['Functions & Closures',   'λ'],
      ['Strings',                '"'],
      ['Recursion',              '↺'],
      ['Higher-Order Functions', '∘'],
      ['Async & Promises',       '⧖'],
      ['DOM & Web APIs',         '⬡'],
      ['Algorithms',             '◆'],
    ];
    foreach ($topics as [$name, $icon]):
    ?>
    <div style="background:#fff; border:1px solid var(--rule); border-radius:var(--radius); padding:.75rem 1rem; display:flex; align-items:center; gap:.6rem;">
      <span style="font-family:var(--ff-code); font-size:1rem; color:var(--accent); flex-shrink:0;"><?= $icon ?></span>
      <span style="font-size:.85rem; font-weight:500;"><?= htmlspecialchars($name) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:3rem;">
    <a href="register.php" class="btn btn-primary btn-lg">Create your free account →</a>
  </div>

</div>

<?php require 'includes/footer.php'; ?>
