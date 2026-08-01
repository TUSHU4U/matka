<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
startSecureSession();
$siteName = getSetting('site_name', APP_NAME);
$phone    = getSetting('contact_phone', '');
$email    = getSetting('contact_email', '');
$whatsapp = getSetting('whatsapp_number', '');
$telegram = getSetting('telegram_link', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us — <?= e($siteName) ?></title>
  <meta name="description" content="Contact <?= e($siteName) ?> for any queries about Satta Matka results.">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/style.css">
</head>
<body>
<div class="header-top">★ <?= e($siteName) ?> — CONTACT US ★</div>
<header class="site-header">
  <div class="container">
    <div class="main-nav d-flex align-items-center justify-content-between">
      <a href="<?= e(APP_URL) ?>/" class="navbar-brand-custom">
        <div class="brand-logo">RK</div>
        <div class="brand-text">
          <span class="brand-name"><?= e($siteName) ?></span>
          <span class="brand-sub">Contact Us</span>
        </div>
      </a>
      <ul class="nav-menu" id="nav-menu">
        <li><a href="<?= e(APP_URL) ?>/">🏠 Home</a></li>
        <li><a href="<?= e(APP_URL) ?>/chart.php">📊 Chart</a></li>
        <li><a href="<?= e(APP_URL) ?>/contact.php" class="active">📞 Contact</a></li>
      </ul>
      <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
      </button>
    </div>
  </div>
</header>

<main class="section">
  <div class="container">
    <div class="section-title mb-5">
      <div class="icon">📞</div>
      <h1 style="font-size:20px;font-weight:700">Contact Us</h1>
      <div class="line"></div>
    </div>

    <div class="row g-4 justify-content-center">
      <?php if ($phone): ?>
      <div class="col-lg-3 col-md-6">
        <div class="contact-card text-center fade-in">
          <div class="contact-icon-box mx-auto"><i class="bi bi-telephone-fill" style="color:var(--gold)"></i></div>
          <h2 style="font-size:15px;font-weight:700;color:var(--gold);margin-bottom:8px">Phone / Call</h2>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Available 10 AM – 11 PM IST</p>
          <a href="tel:<?= e($phone) ?>" class="btn-gold d-inline-block"><?= e($phone) ?></a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($whatsapp): ?>
      <div class="col-lg-3 col-md-6">
        <div class="contact-card text-center fade-in">
          <div class="contact-icon-box mx-auto"><i class="bi bi-whatsapp" style="color:#25D366"></i></div>
          <h2 style="font-size:15px;font-weight:700;color:var(--gold);margin-bottom:8px">WhatsApp</h2>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Quick response via chat</p>
          <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $whatsapp)) ?>" target="_blank" rel="noopener"
             class="btn-gold d-inline-block" style="background:#25D366">Chat on WhatsApp</a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($email): ?>
      <div class="col-lg-3 col-md-6">
        <div class="contact-card text-center fade-in">
          <div class="contact-icon-box mx-auto"><i class="bi bi-envelope-fill" style="color:var(--blue)"></i></div>
          <h2 style="font-size:15px;font-weight:700;color:var(--gold);margin-bottom:8px">Email</h2>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">We reply within 24 hours</p>
          <a href="mailto:<?= e($email) ?>" class="btn-gold d-inline-block"><?= e($email) ?></a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($telegram): ?>
      <div class="col-lg-3 col-md-6">
        <div class="contact-card text-center fade-in">
          <div class="contact-icon-box mx-auto"><i class="bi bi-telegram" style="color:#2CA5E0"></i></div>
          <h2 style="font-size:15px;font-weight:700;color:var(--gold);margin-bottom:8px">Telegram</h2>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Join our official channel</p>
          <a href="<?= e($telegram) ?>" target="_blank" rel="noopener"
             class="btn-gold d-inline-block" style="background:#2CA5E0">Open Telegram</a>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="notice-banner mt-5">
      <div class="notice-icon">ℹ️</div>
      <div class="notice-text">
        <strong>Note:</strong> We only provide result information. We are not responsible for any financial losses.
        Satta Matka involves risk. Please check your local laws before participating.
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.APP_URL = '<?= e(APP_URL) ?>';</script>
<script src="<?= e(APP_URL) ?>/assets/js/main.js"></script>
</body>
</html>
