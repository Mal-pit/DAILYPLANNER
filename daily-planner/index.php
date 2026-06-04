<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = '⚠️ Sila masukkan username dan password.';
    } else {
        $user_data = verify_login($username, $password);
        if ($user_data) {
            // Clear old session data so fresh data loads from file
            session_regenerate_id(true);
            unset($_SESSION['tasks'], $_SESSION['schedule'], $_SESSION['habits'], $_SESSION['_data_loaded']);
            $_SESSION['user'] = [
                'username' => $username,
                'name'     => $user_data['name'],
                'role'     => $user_data['role'],
                'email'    => $user_data['email'],
            ];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = '❌ Username atau password salah. Sila cuba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DayFlow – Smart Daily Planner. Login to manage your tasks, schedule, and habits.">
  <title>Login – DayFlow Daily Planner</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrapper">

  <div class="auth-panel-left">
    <div class="auth-brand fade-in">
      <div class="logo-icon">📅</div>
      <h1>DayFlow</h1>
      <p>Your personal daily planning companion. Stay organised, stay productive.</p>
    </div>

    <div class="auth-features fade-in-2">
      <div class="auth-feature-item"><span class="dot"></span> Manage tasks with priority tracking</div>
      <div class="auth-feature-item"><span class="dot"></span> Visualise your daily schedule</div>
      <div class="auth-feature-item"><span class="dot"></span> Build streaks with habit tracking</div>
      <div class="auth-feature-item"><span class="dot"></span> Dashboard with charts &amp; insights</div>
    </div>
  </div>

  <div class="auth-panel-right">
    <div class="auth-form-box fade-in">
      <h2>Welcome back</h2>
      <p class="auth-subtitle">Sign in to your planner account</p>

      <?php if ($error): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php" novalidate>
        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="e.g. admin"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            autocomplete="username"
            required
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Your password"
            autocomplete="current-password"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
          Sign In &rarr;
        </button>
      </form>

      <div style="margin-top:28px; background:var(--amber-pale); border:1.5px solid var(--amber-light); border-radius:var(--radius); padding:14px 18px;">
        <div style="font-size:.78rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--amber); margin-bottom:8px;">🔑 Demo Credentials</div>
        <div style="font-size:.84rem; color:var(--ink-soft); display:flex; flex-direction:column; gap:4px;">
          <span><strong>admin</strong> / admin123</span>
          <span><strong>sarah</strong> / sarah2024</span>
          <span><strong>demo</strong> / demo1234</span>
        </div>
      </div>

      <div class="auth-switch">
        Don't have an account? <a href="register.php">Register here</a>
      </div>
    </div>
  </div>

</div>

</body>
</html>
