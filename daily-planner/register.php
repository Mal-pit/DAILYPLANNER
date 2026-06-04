<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Validation
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = '⚠️ Semua ruangan mesti diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = '❌ Username mesti 3–20 karakter dan hanya huruf, nombor, atau _.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Sila masukkan alamat email yang sah.';
    } elseif (strlen($password) < 6) {
        $error = '❌ Password mesti sekurang-kurangnya 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = '❌ Password tidak sepadan. Sila cuba lagi.';
    } elseif (username_exists($username)) {
        $error = '❌ Username "' . htmlspecialchars($username) . '" sudah digunakan. Cuba yang lain.';
    } else {
        // Register the user (saves to users.json)
        if (register_user($username, $password, $name, $email)) {
            // Auto-login after register
            session_regenerate_id(true);
            unset($_SESSION['tasks'], $_SESSION['schedule'], $_SESSION['habits'], $_SESSION['_data_loaded']);
            $_SESSION['user'] = [
                'username' => $username,
                'name'     => $name,
                'role'     => 'Member',
                'email'    => $email,
            ];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = '❌ Ralat semasa mendaftar. Sila cuba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Create a DayFlow account to start planning your day.">
  <title>Register – DayFlow Daily Planner</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrapper">

  <div class="auth-panel-left">
    <div class="auth-brand fade-in">
      <div class="logo-icon">📅</div>
      <h1>DayFlow</h1>
      <p>Create an account and start taking control of your daily routine today.</p>
    </div>
    <div class="auth-features fade-in-2">
      <div class="auth-feature-item"><span class="dot"></span> Free to use, no credit card needed</div>
      <div class="auth-feature-item"><span class="dot"></span> Data anda disimpan, kekal selepas log keluar</div>
      <div class="auth-feature-item"><span class="dot"></span> Track tasks, habits &amp; schedules</div>
    </div>
  </div>

  <div class="auth-panel-right">
    <div class="auth-form-box fade-in">
      <h2>Create account</h2>
      <p class="auth-subtitle">Join DayFlow and plan smarter</p>

      <?php if ($error): ?><div class="form-error"><?= $error ?></div><?php endif; ?>

      <form method="POST" action="register.php" novalidate>

        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name"
            placeholder="e.g. Siti Aisyah"
            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username"
            placeholder="3–20 characters (letters, numbers, _)"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email"
            placeholder="you@example.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
            placeholder="Minimum 6 characters" required>
        </div>

        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm"
            placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
          Create Account &rarr;
        </button>

      </form>

      <div class="auth-switch">
        Already have an account? <a href="index.php">Sign in</a>
      </div>
    </div>
  </div>

</div>

</body>
</html>
