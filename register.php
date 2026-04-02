<?php
require_once __DIR__ . '/config.php';
sessionStart();

if (currentUser()) { header('Location: dashboard.php'); exit; }

$errors = [];
$values = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    $values = ['username' => $username, 'email' => $email];

    // Validation
    if (!preg_match('/^[a-zA-Z0-9_]{3,40}$/', $username)) {
        $errors['username'] = 'Username must be 3–40 characters: letters, numbers, underscores only.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$username, $email]);
            $dup  = $stmt->fetch();

            if ($dup) {
                $errors['general'] = 'Username or email is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)')->execute([$username, $email, $hash]);
                $newId = $db->lastInsertId();
                $user  = $db->prepare('SELECT * FROM users WHERE id = ?');
                $user->execute([$newId]);
                $_SESSION['user'] = $user->fetch();
                header('Location: dashboard.php?welcome=1');
                exit;
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error. Please try again.';
        }
    }
}
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create account — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><?= SITE_NAME ?><span>.</span></div>
    <p class="sub">Create your account to start learning JavaScript.</p>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $token ?>">

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($values['username']) ?>" autocomplete="username" required>
        <?php if (!empty($errors['username'])): ?><span class="form-error"><?= htmlspecialchars($errors['username']) ?></span><?php endif; ?>
        <span class="field-hint">Letters, numbers, underscores — 3 to 40 characters.</span>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email']) ?>" autocomplete="email" required>
        <?php if (!empty($errors['email'])): ?><span class="form-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
        <?php if (!empty($errors['password'])): ?><span class="form-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
        <span class="field-hint">Minimum 8 characters.</span>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
        <?php if (!empty($errors['confirm'])): ?><span class="form-error"><?= htmlspecialchars($errors['confirm']) ?></span><?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-full mt-2">Create account</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
  </div>
</div>
</body>
</html>
