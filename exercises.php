<?php
require_once __DIR__ . '/config.php';
sessionStart();
$user = currentUser();

$db = getDB();

// Fetch exercises with user progress if logged in
if ($user) {
    $stmt = $db->prepare('
        SELECT e.*, up.status
        FROM exercises e
        LEFT JOIN user_progress up ON up.exercise_id = e.id AND up.user_id = ?
        ORDER BY e.sort_order ASC
    ');
    $stmt->execute([$user['id']]);
} else {
    $stmt = $db->query('SELECT *, NULL as status FROM exercises ORDER BY sort_order ASC');
}
$exercises = $stmt->fetchAll();

// Group by topic
$byTopic = [];
foreach ($exercises as $ex) {
    $byTopic[$ex['topic']][] = $ex;
}
$topics = array_keys($byTopic);

$diffLabels = [1 => 'Easy', 2 => 'Medium', 3 => 'Hard', 4 => 'Expert'];

$pageTitle = 'Exercises';
require 'includes/header.php';
?>

<div class="site-wrap" style="padding-top:2rem; padding-bottom:3rem;">

  <div class="flex items-center justify-between mb-2">
    <div>
      <h1>JavaScript Exercises</h1>
      <p class="mt-1"><?= count($exercises) ?> exercises across <?= count($topics) ?> topics — from beginner to expert.</p>
    </div>
    <?php if (!$user): ?>
    <a href="register.php" class="btn btn-primary">Register to track progress</a>
    <?php endif; ?>
  </div>

  <!-- Filter bar -->
  <div class="filter-bar" id="topicFilter">
    <button class="filter-btn active" data-topic="all">All topics</button>
    <?php foreach ($topics as $t): ?>
    <button class="filter-btn" data-topic="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="filter-bar diff-filter" id="diffFilter">
    <button class="filter-btn active" data-diff="all">All levels</button>
    <?php foreach ($diffLabels as $d => $label): ?>
    <button class="filter-btn" data-diff="<?= $d ?>"><?= $label ?></button>
    <?php endforeach; ?>
  </div>

  <!-- Exercise grid -->
  <?php foreach ($byTopic as $topic => $exList): ?>
  <div class="topic-section" data-topic="<?= htmlspecialchars($topic) ?>">
    <h4 class="mt-3 mb-1 text-muted" style="text-transform:uppercase; letter-spacing:.08em; font-size:.75rem;"><?= htmlspecialchars($topic) ?></h4>
    <div class="exercise-grid">
      <?php foreach ($exList as $ex):
        $statusClass = '';
        $statusText  = '';
        if ($ex['status'] === 'solved')    { $statusClass = 'solved';    $statusText = '✓ Solved'; }
        if ($ex['status'] === 'attempted') { $statusClass = 'attempted'; $statusText = '○ Attempted'; }
      ?>
      <a href="exercise.php?slug=<?= urlencode($ex['slug']) ?>"
         class="ex-card <?= $statusClass ?>"
         data-diff="<?= $ex['difficulty'] ?>"
         data-topic="<?= htmlspecialchars($ex['topic']) ?>">
        <div class="ex-title"><?= htmlspecialchars($ex['title']) ?></div>
        <div class="ex-meta">
          <span class="badge badge-<?= $ex['difficulty'] ?>"><?= $diffLabels[$ex['difficulty']] ?></span>
          <span>+<?= $ex['xp_reward'] ?> XP</span>
        </div>
        <?php if ($statusText): ?>
        <div class="ex-status <?= $ex['status'] === 'solved' ? 'ex-solved-mark' : 'ex-attempt-mark' ?>"><?= $statusText ?></div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script>
(function () {
  const topicBtns = document.querySelectorAll('#topicFilter .filter-btn');
  const diffBtns  = document.querySelectorAll('#diffFilter  .filter-btn');
  let activeTopic = 'all';
  let activeDiff  = 'all';

  function applyFilters() {
    document.querySelectorAll('.ex-card').forEach(card => {
      const topicMatch = activeTopic === 'all' || card.dataset.topic === activeTopic;
      const diffMatch  = activeDiff  === 'all' || card.dataset.diff  === activeDiff;
      card.parentElement.style.display = (topicMatch && diffMatch) ? '' : 'none';
    });
    // Hide empty topic sections
    document.querySelectorAll('.topic-section').forEach(sec => {
      const visible = [...sec.querySelectorAll('.ex-card')].some(c => c.parentElement.style.display !== 'none');
      sec.style.display = visible ? '' : 'none';
    });
  }

  topicBtns.forEach(btn => btn.addEventListener('click', () => {
    topicBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeTopic = btn.dataset.topic;
    applyFilters();
  }));

  diffBtns.forEach(btn => btn.addEventListener('click', () => {
    diffBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeDiff = btn.dataset.diff;
    applyFilters();
  }));
})();
</script>

<?php require 'includes/footer.php'; ?>
