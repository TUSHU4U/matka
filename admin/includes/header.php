<?php
/**
 * Admin Sidebar/Header Partial
 * Included by all admin pages after requireAdmin().
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteName    = getSetting('site_name', APP_NAME);

// Pending results count for badge
$pendingCount = (int) pdo()->query("SELECT COUNT(*) FROM results WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminPageTitle ?? 'Admin') ?> — <?= e($siteName) ?> Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/admin.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="admin-wrapper">

<!-- ─── Sidebar ─────────────────────────────────────────────── -->
<aside class="admin-sidebar" id="admin-sidebar">

  <div class="sidebar-brand">
    <div class="sidebar-logo">RK</div>
    <div class="sidebar-brand-text">
      <span class="sb-title"><?= e($siteName) ?></span>
      <span class="sb-sub">Admin Panel</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="<?= e(APP_URL) ?>/admin/index.php"
       class="<?= $currentPage === 'index' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
    </a>
    <a href="<?= e(APP_URL) ?>/admin/results.php"
       class="<?= in_array($currentPage, ['results','add-result','edit-result']) ? 'active' : '' ?>">
      <i class="bi bi-trophy nav-icon"></i> Results
      <?php if ($pendingCount > 0): ?>
        <span class="nav-badge"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= e(APP_URL) ?>/admin/add-result.php"
       class="<?= $currentPage === 'add-result' ? 'active' : '' ?>">
      <i class="bi bi-plus-circle nav-icon"></i> Add Result
    </a>

    <div class="nav-section-label" style="margin-top:8px">Management</div>
    <a href="<?= e(APP_URL) ?>/admin/games.php"
       class="<?= $currentPage === 'games' ? 'active' : '' ?>">
      <i class="bi bi-dice-6 nav-icon"></i> Games / Markets
    </a>
    <a href="<?= e(APP_URL) ?>/admin/settings.php"
       class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
      <i class="bi bi-gear nav-icon"></i> Site Settings
    </a>
    <a href="<?= e(APP_URL) ?>/admin/users.php"
       class="<?= $currentPage === 'users' ? 'active' : '' ?>">
      <i class="bi bi-people nav-icon"></i> Admin Users
    </a>

    <div class="nav-section-label" style="margin-top:8px">Site</div>
    <a href="<?= e(APP_URL) ?>/" target="_blank">
      <i class="bi bi-box-arrow-up-right nav-icon"></i> View Website
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-user-card">
      <div class="admin-avatar"><i class="bi bi-person-fill"></i></div>
      <div>
        <div class="admin-user-name"><?= e($_SESSION['admin_username'] ?? 'Admin') ?></div>
        <div class="admin-user-role">Administrator</div>
      </div>
    </div>
    <a href="<?= e(APP_URL) ?>/admin/logout.php"
       style="display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:9px;color:var(--admin-red);font-size:13px;font-weight:600;text-decoration:none;transition:.2s;width:100%"
       onmouseover="this.style.background='rgba(230,57,70,0.1)'"
       onmouseout="this.style.background='transparent'">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </div>
</aside>

<!-- ─── Main Area ─────────────────────────────────────────────── -->
<div class="admin-main">

  <!-- Topbar -->
  <div class="admin-topbar">
    <div>
      <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
      </button>
    </div>
    <div>
      <div class="admin-page-title"><?= e($adminPageTitle ?? 'Admin Panel') ?></div>
      <div class="admin-page-breadcrumb">
        <a href="<?= e(APP_URL) ?>/admin/index.php">Dashboard</a>
        <?php if (isset($adminPageTitle)): ?>
          <i class="bi bi-chevron-right" style="font-size:10px;margin:0 4px"></i>
          <?= e($adminPageTitle) ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="topbar-actions">
      <div id="admin-clock"
           style="font-size:11px;color:var(--admin-muted);white-space:nowrap;display:none"
           class="d-md-block"></div>
      <a href="<?= e(APP_URL) ?>/admin/add-result.php"
         class="btn-admin-primary" style="font-size:12px;padding:7px 14px">
        <i class="bi bi-plus-lg"></i> Add Result
      </a>
    </div>
  </div>

  <!-- Flash Message -->
  <?php $flash = getFlash(); if ($flash): ?>
  <div style="padding:0 28px 0">
    <div class="admin-alert admin-alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info') ?>"
         style="margin-top:20px">
      <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill"></i>
      <?= e($flash['message']) ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="admin-content">
