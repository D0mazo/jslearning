<?php
require_once __DIR__ . '/config.php';
sessionStart();

if (currentUser()) { header('Location: dashboard.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $login    = trim($_POST['login']    ?? '');
    $password = $_POST['password']      ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
                $_SESSION['user'] = $user;
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                header('Location: ' . htmlspecialchars($redirect));
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign in — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><?= SITE_NAME ?><span>.</span></div>
    <p class="sub">Sign in to continue your JavaScript practice.</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['registered'])): ?>
      <div class="alert alert-success">Account created! Please sign in.</div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $token ?>">

      <div class="form-group">
        <label for="login">Username or email</label>
        <input type="text" id="login" name="login" autocomplete="username" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn btn-primary w-full mt-2">Sign in</button>
    </form>

    <p class="auth-switch">New here? <a href="register.php">Create an account</a></p>
  </div>
</div>
</body>
</html>
