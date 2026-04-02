<?php
require_once __DIR__ . '/config.php';
sessionStart();
$user = currentUser();

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: exercises.php'); exit; }

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM exercises WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$ex   = $stmt->fetch();
if (!$ex) { header('Location: exercises.php'); exit; }

// Fetch user progress
$progress = null;
if ($user) {
    $ps = $db->prepare('SELECT * FROM user_progress WHERE user_id = ? AND exercise_id = ? LIMIT 1');
    $ps->execute([$user['id'], $ex['id']]);
    $progress = $ps->fetch();
}

$diffLabels = [1 => 'Easy', 2 => 'Medium', 3 => 'Hard', 4 => 'Expert'];

// Previous / next exercise
$prevStmt = $db->prepare('SELECT slug, title FROM exercises WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1');
$prevStmt->execute([$ex['sort_order']]);
$prevEx = $prevStmt->fetch();

$nextStmt = $db->prepare('SELECT slug, title FROM exercises WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1');
$nextStmt->execute([$ex['sort_order']]);
$nextEx = $nextStmt->fetch();

$pageTitle = $ex['title'];
require 'includes/header.php';
?>

<div class="site-wrap" style="padding-top:1.75rem; padding-bottom:3rem;">

  <!-- Breadcrumb / meta -->
  <div class="flex items-center gap-2 text-sm text-muted mb-2">
    <a href="exercises.php">Exercises</a>
    <span>›</span>
    <span><?= htmlspecialchars($ex['topic']) ?></span>
  </div>

  <div class="flex items-center justify-between" style="flex-wrap:wrap; gap:.75rem;">
    <div>
      <h1 style="margin-bottom:.3rem;"><?= htmlspecialchars($ex['title']) ?></h1>
      <span class="badge badge-<?= $ex['difficulty'] ?>"><?= $diffLabels[$ex['difficulty']] ?></span>
      <span class="text-xs text-muted" style="margin-left:.5rem;">+<?= $ex['xp_reward'] ?> XP on first solve</span>
      <?php if ($progress && $progress['status'] === 'solved'): ?>
        <span class="badge" style="background:var(--success-lt);color:var(--success);margin-left:.5rem;">✓ Solved</span>
      <?php endif; ?>
    </div>
    <div class="flex gap-2">
      <?php if ($prevEx): ?>
      <a href="exercise.php?slug=<?= urlencode($prevEx['slug']) ?>" class="btn btn-secondary btn-sm">← Prev</a>
      <?php endif; ?>
      <?php if ($nextEx): ?>
      <a href="exercise.php?slug=<?= urlencode($nextEx['slug']) ?>" class="btn btn-secondary btn-sm">Next →</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="ex-layout">

    <!-- Left: Description -->
    <div>
      <div class="card" style="margin-top:1.25rem;">
        <h4 style="margin-bottom:.75rem;">Problem</h4>
        <div style="font-size:.95rem; line-height:1.75; color:var(--ink);">
          <?= $ex['description'] /* Already contains safe HTML from DB */ ?>
        </div>

        <?php if ($ex['expected_output']): ?>
        <div style="margin-top:1.25rem;">
          <div class="text-xs text-muted" style="text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Expected output (example)</div>
          <div class="output-panel"><?= htmlspecialchars($ex['expected_output']) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($ex['hint']): ?>
        <details style="margin-top:1.25rem;">
          <summary style="cursor:pointer; font-size:.85rem; color:var(--accent); user-select:none;">Show hint</summary>
          <p style="margin-top:.6rem; font-size:.875rem; color:var(--ink-mid);"><?= $ex['hint'] ?></p>
        </details>
        <?php endif; ?>
      </div>

      <?php if ($progress): ?>
      <div class="card mt-2">
        <div class="text-xs text-muted" style="text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Your progress</div>
        <div class="flex gap-2 text-sm">
          <span>Attempts: <strong><?= $progress['attempts'] ?></strong></span>
          <?php if ($progress['status'] === 'solved'): ?>
          <span style="color:var(--success);">Solved <?= date('M j, Y', strtotime($progress['solved_at'])) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right: Editor -->
    <div class="editor-wrap" style="margin-top:1.25rem;">
      <div class="flex items-center justify-between mb-1">
        <h4>Code editor</h4>
        <span class="text-xs text-muted">JavaScript</span>
      </div>

      <textarea id="code-editor" spellcheck="false" placeholder="// Write your JavaScript solution here…
// Example:
function strLen(s) {
    return s.length;
}"></textarea>

      <div class="flex gap-2">
        <button class="btn btn-primary" id="runBtn">▶ Run</button>
        <?php if ($user): ?>
        <button class="btn btn-success" id="submitBtn">✓ Submit solution</button>
        <?php else: ?>
        <a href="login.php?redirect=exercise.php?slug=<?= urlencode($slug) ?>" class="btn btn-secondary">Sign in to submit</a>
        <?php endif; ?>
        <button class="btn btn-secondary btn-sm" id="clearBtn" style="margin-left:auto;">Clear</button>
      </div>

      <div>
        <div class="text-xs text-muted mb-1">Output</div>
        <div class="output-panel" id="outputPanel">Run your code to see output here.</div>
      </div>

      <div class="text-xs text-muted" style="line-height:1.5;">
        <strong>How it works:</strong> Your code runs in the browser sandbox. Use <code>console.log()</code> to print results.
        The run button executes your code; the submit button records your attempt in your progress.
      </div>
    </div>
  </div>

</div>

<script>
(function () {
  const editor     = document.getElementById('code-editor');
  const runBtn     = document.getElementById('runBtn');
  const submitBtn  = document.getElementById('submitBtn');
  const clearBtn   = document.getElementById('clearBtn');
  const output     = document.getElementById('outputPanel');
  const exerciseId = <?= (int)$ex['id'] ?>;

  // Tab key support in textarea
  editor.addEventListener('keydown', e => {
    if (e.key === 'Tab') {
      e.preventDefault();
      const s = editor.selectionStart;
      editor.value = editor.value.substring(0, s) + '  ' + editor.value.substring(editor.selectionEnd);
      editor.selectionStart = editor.selectionEnd = s + 2;
    }
  });

  // Restore saved code from localStorage
  const storageKey = 'jslearn_code_<?= $ex['slug'] ?>';
  if (localStorage.getItem(storageKey)) {
    editor.value = localStorage.getItem(storageKey);
  }
  editor.addEventListener('input', () => localStorage.setItem(storageKey, editor.value));

  // ── Run code in sandboxed iframe ─────────────────────────
  function runCode(code) {
    output.textContent = '';
    output.className   = 'output-panel';

    const logs = [];
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);

    try {
      const iWin = iframe.contentWindow;
      iWin.console = {
        log:   (...args) => logs.push(args.map(String).join(' ')),
        error: (...args) => logs.push('[error] ' + args.map(String).join(' ')),
        warn:  (...args) => logs.push('[warn]  ' + args.map(String).join(' ')),
      };
      iWin.eval(code);
      output.textContent = logs.length ? logs.join('\n') : '(no output — use console.log to print results)';
      output.className   = 'output-panel output-ok';
    } catch (err) {
      output.textContent = 'Error: ' + err.message;
      output.className   = 'output-panel output-error';
    } finally {
      document.body.removeChild(iframe);
    }
    return logs;
  }

  runBtn.addEventListener('click', () => runCode(editor.value));

  clearBtn.addEventListener('click', () => {
    editor.value = '';
    localStorage.removeItem(storageKey);
    output.textContent = '';
    output.className   = 'output-panel';
  });

  <?php if ($user): ?>
  // ── Submit solution ───────────────────────────────────────
  submitBtn.addEventListener('click', async () => {
    const code = editor.value.trim();
    if (!code) { output.textContent = 'Write some code first!'; output.className = 'output-panel output-error'; return; }

    runCode(code); // always run first so user sees result

    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving…';

    try {
      const res  = await fetch('api_submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ exercise_id: exerciseId, code })
      });
      const data = await res.json();

      if (data.xp_gained) {
        showXPPopup('+' + data.xp_gained + ' XP');
        // Update nav XP display
        const xpEl = document.querySelector('.nav-xp');
        if (xpEl) xpEl.textContent = data.rank_title + ' · ' + data.total_xp.toLocaleString() + ' XP';
      }
      submitBtn.textContent = data.first_solve ? '✓ Solved! +' + data.xp_gained + ' XP' : '✓ Submitted';
      submitBtn.style.background = 'var(--success)';
    } catch (e) {
      submitBtn.textContent = '✓ Submit solution';
    } finally {
      submitBtn.disabled = false;
    }
  });

  function showXPPopup(text) {
    const el = document.createElement('div');
    el.className   = 'xp-popup';
    el.textContent = text;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2600);
  }
  <?php endif; ?>
})();
</script>

<?php require 'includes/footer.php'; ?>
