<?php
/**
 * Admin Login — Rate-limited, CSRF-protected
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/functions.php';

startSecureSession();

// Already logged in → redirect
if (isAdminLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/index.php');
    exit;
}

$error   = '';
$success = '';

if (!empty($_GET['timeout'])) {
    $error = 'Session expired. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $result = attemptLogin($username, $password);
        if ($result['success']) {
            header('Location: ' . APP_URL . '/admin/index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= e($siteName) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-login-page">
  <div class="login-box">

    <!-- Logo -->
    <div class="login-logo">
      <div class="logo-circle">RK</div>
      <h1><?= e($siteName) ?></h1>
      <p>Admin Control Panel</p>
    </div>

    <!-- Alert -->
    <?php if ($error): ?>
    <div class="admin-alert admin-alert-error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="" autocomplete="off">
      <?= csrfField() ?>

      <div class="admin-form-group">
        <label class="admin-label" for="username">
          <i class="bi bi-person me-1"></i> Username
        </label>
        <input type="text" id="username" name="username"
               class="admin-input"
               placeholder="Enter admin username"
               value="<?= e($_POST['username'] ?? '') ?>"
               autocomplete="username"
               required autofocus>
      </div>

      <div class="admin-form-group">
        <label class="admin-label" for="password">
          <i class="bi bi-lock me-1"></i> Password
        </label>
        <div style="position:relative">
          <input type="password" id="password" name="password"
                 class="admin-input"
                 placeholder="Enter password"
                 autocomplete="current-password"
                 required>
          <button type="button"
                  data-toggle-password="password"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--admin-muted);cursor:pointer;font-size:16px">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn">
        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:11px;color:var(--admin-muted)">
      Max <?= LOGIN_MAX_ATTEMPTS ?> attempts • <?= LOGIN_LOCKOUT_MINUTES ?> min lockout
    </div>

    <div style="text-align:center;margin-top:12px">
      <a href="<?= e(APP_URL) ?>/" style="font-size:12px;color:var(--admin-muted)">
        <i class="bi bi-arrow-left me-1"></i> Back to Website
      </a>
    </div>
  </div>
</div>

<script src="<?= e(APP_URL) ?>/assets/js/admin.js"></script>
</body>
</html>
