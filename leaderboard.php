<?php
require_once __DIR__ . '/config.php';
sessionStart();
$user = currentUser();

$db = getDB();

$rows = $db->query('
    SELECT u.id, u.username, u.xp, u.rank_title,
           COUNT(up.id) as solved
    FROM users u
    LEFT JOIN user_progress up ON up.user_id = u.id AND up.status = "solved"
    GROUP BY u.id
    ORDER BY u.xp DESC, u.created_at ASC
    LIMIT 50
')->fetchAll();

$pageTitle = 'Leaderboard';
require 'includes/header.php';
?>

<div class="site-wrap" style="padding-top:2rem; padding-bottom:3rem;">

  <h1>Leaderboard</h1>
  <p class="mt-1 mb-3">Top coders ranked by total XP. Complete exercises to climb the ranks.</p>

  <?php if (empty($rows)): ?>
  <div class="card text-center" style="padding:2.5rem;">
    <p>No one here yet — <a href="register.php">create an account</a> and be the first!</p>
  </div>
  <?php else: ?>
  <div class="card" style="padding:0; overflow:hidden;">
    <table class="leaderboard-table">
      <thead>
        <tr>
          <th style="width:3rem;">#</th>
          <th>User</th>
          <th>Rank</th>
          <th>Solved</th>
          <th>XP</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $i => $row): ?>
      <tr <?= ($user && $row['id'] == $user['id']) ? 'class="me"' : '' ?>>
        <td class="rank-num">
          <?php if ($i === 0): ?>🥇
          <?php elseif ($i === 1): ?>🥈
          <?php elseif ($i === 2): ?>🥉
          <?php else: ?><?= $i + 1 ?><?php endif; ?>
        </td>
        <td style="font-weight:500;">
          <?= htmlspecialchars($row['username']) ?>
          <?php if ($user && $row['id'] == $user['id']): ?>
            <span style="font-size:.75rem; color:var(--accent); margin-left:.4rem;">(you)</span>
          <?php endif; ?>
        </td>
        <td>
          <?php
            $ri = rankForXP((int)$row['xp']);
            echo $ri['current']['icon'] . ' ' . htmlspecialchars($ri['current']['title']);
          ?>
        </td>
        <td style="font-family:var(--ff-code); font-size:.85rem;"><?= (int)$row['solved'] ?></td>
        <td style="font-family:var(--ff-code); font-size:.85rem; color:var(--accent); font-weight:500;">
          <?= number_format((int)$row['xp']) ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>
