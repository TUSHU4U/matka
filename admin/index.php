<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check auth
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

// Handle Marquee Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marquee'])) {
    $new_text = $_POST['marquee_text'] ?? '';
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'marquee'");
    $stmt->execute([$new_text]);
    $success = "Marquee updated successfully!";
}

// Fetch current marquee
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'marquee'");
$marquee = $stmt->fetchColumn() ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Fatafat System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Fatafat Admin</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="index.php">Dashboard</a>
                <a class="nav-link" href="results.php">Manage Results</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Scrolling Marquee Text</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Marquee Content</label>
                                <textarea name="marquee_text" class="form-control" rows="5" required><?= htmlspecialchars($marquee) ?></textarea>
                            </div>
                            <button type="submit" name="update_marquee" class="btn btn-primary">Update Text</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
