<?php
/**
 * Admin — Site Settings
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

requireAdmin();
$adminPageTitle = 'Site Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $fields = [
        'site_name', 'site_tagline', 'contact_phone', 'contact_email',
        'notice_text', 'footer_text', 'meta_description', 'meta_keywords',
        'whatsapp_number', 'telegram_link',
    ];

    foreach ($fields as $key) {
        $val = sanitize($_POST[$key] ?? '');
        setSetting($key, $val);
    }

    setFlash('success', 'Settings saved successfully!');
    header('Location: ' . APP_URL . '/admin/settings.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
<div class="col-12">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title"><i class="bi bi-gear text-gold"></i> Site Settings</div>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="" class="dirty-track">
        <?= csrfField() ?>

        <div class="settings-grid">

          <!-- General -->
          <div>
            <div class="result-section-label mb-3"><i class="bi bi-globe"></i> General</div>
            <div class="admin-form-group">
              <label class="admin-label">Site Name</label>
              <input type="text" name="site_name" class="admin-input"
                     value="<?= e(getSetting('site_name', APP_NAME)) ?>" maxlength="60">
            </div>
            <div class="admin-form-group">
              <label class="admin-label">Site Tagline</label>
              <input type="text" name="site_tagline" class="admin-input"
                     value="<?= e(getSetting('site_tagline')) ?>" maxlength="100">
            </div>
            <div class="admin-form-group">
              <label class="admin-label">Notice / Ticker Text</label>
              <textarea name="notice_text" class="admin-input" rows="3"
                        placeholder="Announcement shown in the news ticker"><?= e(getSetting('notice_text')) ?></textarea>
            </div>
            <div class="admin-form-group">
              <label class="admin-label">Footer Text</label>
              <input type="text" name="footer_text" class="admin-input"
                     value="<?= e(getSetting('footer_text')) ?>" maxlength="200">
            </div>
          </div>

          <!-- Contact -->
          <div>
            <div class="result-section-label mb-3"><i class="bi bi-telephone"></i> Contact Information</div>
            <div class="admin-form-group">
              <label class="admin-label">Phone Number</label>
              <input type="text" name="contact_phone" class="admin-input"
                     value="<?= e(getSetting('contact_phone')) ?>"
                     placeholder="+91 99999 99999">
            </div>
            <div class="admin-form-group">
              <label class="admin-label">Email Address</label>
              <input type="email" name="contact_email" class="admin-input"
                     value="<?= e(getSetting('contact_email')) ?>"
                     placeholder="info@example.com">
            </div>
            <div class="admin-form-group">
              <label class="admin-label">WhatsApp Number</label>
              <input type="text" name="whatsapp_number" class="admin-input"
                     value="<?= e(getSetting('whatsapp_number')) ?>"
                     placeholder="+91 99999 99999 (with country code)">
              <div class="input-hint">Include country code, digits only for the wa.me link</div>
            </div>
            <div class="admin-form-group">
              <label class="admin-label">Telegram Link</label>
              <input type="url" name="telegram_link" class="admin-input"
                     value="<?= e(getSetting('telegram_link')) ?>"
                     placeholder="https://t.me/yourchannel">
            </div>
          </div>

        </div><!-- /settings-grid -->

        <!-- SEO -->
        <hr style="border-color:var(--admin-border);margin:24px 0">
        <div class="result-section-label mb-3"><i class="bi bi-search"></i> SEO Settings</div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Meta Description</label>
              <textarea name="meta_description" class="admin-input" rows="3"
                        maxlength="160"><?= e(getSetting('meta_description')) ?></textarea>
              <div class="input-hint">Max 160 characters. Used in search results.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Meta Keywords</label>
              <textarea name="meta_keywords" class="admin-input" rows="3"><?= e(getSetting('meta_keywords')) ?></textarea>
              <div class="input-hint">Comma-separated keywords</div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn-admin-primary" style="padding:12px 32px">
            <i class="bi bi-check-circle me-1"></i> Save All Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
