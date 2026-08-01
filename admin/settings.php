<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$page_title = "Settings";

// Handle Marquee Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marquee'])) {
    $new_text = $_POST['marquee_text'] ?? '';
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'marquee'");
    $stmt->execute([$new_text]);
    $success = "Marquee updated successfully!";
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_pw = $_POST['current_pw'] ?? '';
    $new_pw = $_POST['new_pw'] ?? '';
    $confirm_pw = $_POST['confirm_pw'] ?? '';

    if ($new_pw !== $confirm_pw) {
        $error_pw = "New passwords do not match.";
    } else {
        $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $hash = $stmt->fetchColumn();

        if (password_verify($current_pw, $hash)) {
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $update->execute([$new_hash, $_SESSION['admin_id']]);
            $success_pw = "Password updated successfully!";
        } else {
            $error_pw = "Incorrect current password.";
        }
    }
}

// Fetch current marquee
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'marquee'");
$marquee = $stmt->fetchColumn() ?: '';

include 'includes/header.php';
?>

<div class="row">
    <!-- Marquee Settings -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-text-paragraph"></i> Marquee Text Settings
            </div>
            <div class="card-body bg-light">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Scrolling Text on Homepage</label>
                        <textarea name="marquee_text" class="form-control" rows="5" required><?= htmlspecialchars($marquee) ?></textarea>
                    </div>
                    <button type="submit" name="update_marquee" class="btn btn-primary w-100 fw-bold"><i class="bi bi-save"></i> Update Marquee</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-top border-4 border-danger">
            <div class="card-header bg-white fw-bold text-danger">
                <i class="bi bi-shield-lock-fill"></i> Change Admin Password
            </div>
            <div class="card-body bg-light">
                <?php if (isset($success_pw)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_pw) ?></div>
                <?php endif; ?>
                <?php if (isset($error_pw)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_pw) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Password</label>
                        <input type="password" name="current_pw" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">New Password</label>
                        <input type="password" name="new_pw" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Confirm New Password</label>
                        <input type="password" name="confirm_pw" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" name="update_password" class="btn btn-danger w-100 fw-bold"><i class="bi bi-key-fill"></i> Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
