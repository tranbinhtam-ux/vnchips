<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Admin.php';

$error = '';

// If already logged in, redirect to dashboard
if (Admin::check()) {
    header("Location: " . admin_url('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if (Admin::login($username, $password)) {
            header("Location: " . admin_url('index.php'));
            exit;
        } else {
            $error = (Admin::$lastError === 'database')
                ? 'Cannot connect to the database. Please check the DB settings in config/config.local.php.'
                : 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Sign In | VNCHIP Management</title>
  <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= admin_url('assets/css/admin.css') ?>">
  <style>
    body {
      background: linear-gradient(135deg, #060F1E 0%, #0A192F 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    .login-card {
      background: #FFF;
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 30px;
    }
    .login-brand-logo {
      display: inline-block;
      background: #0C1A30;
      border-radius: 14px;
      padding: 14px 22px;
      margin-bottom: 16px;
    }
    .login-brand-logo img {
      display: block;
      height: 52px;
      width: auto;
    }
    .login-brand h2 {
      font-size: 1.2rem;
      font-weight: 800;
      color: #0F172A;
    }
    .login-brand p {
      font-size: 0.85rem;
      color: #64748B;
    }
    .demo-credentials {
      background: #F1F5F9;
      border-radius: 8px;
      padding: 12px;
      font-size: 0.8rem;
      color: #475569;
      margin-top: 24px;
      border: 1px dashed #CBD5E1;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-brand">
    <div class="login-brand-logo">
      <img src="<?= asset(Setting::getOr('logo_header', 'images/logo_vnchip_labs.png')) ?>" alt="VNCHIP">
    </div>
    <h2>ADMIN PORTAL</h2>
    <p>Sign in to manage VNCHIP articles & homepage</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?>
    </div>
  <?php endif; ?>

  <form action="<?= admin_url('login.php') ?>" method="POST">
    <div class="form-group">
      <label class="form-label"><i class="fa-solid fa-user"></i> Username</label>
      <input type="text" name="username" class="form-control" autocomplete="username" required autofocus>
    </div>

    <div class="form-group">
      <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
      <input type="password" name="password" class="form-control" autocomplete="current-password" required>
    </div>

    <button type="submit" id="btn-login-submit" name="login_submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 0.95rem; margin-top: 10px;">
      Sign In <i class="fa-solid fa-arrow-right"></i>
    </button>
  </form>
  
  <div style="text-align: center; margin-top: 20px;">
    <a href="<?= url() ?>" style="font-size: 0.85rem; color: #64748B; text-decoration: none;">
      <i class="fa-solid fa-arrow-left"></i> Return to Website
    </a>
  </div>
</div>

</body>
</html>
