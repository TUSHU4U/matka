<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();

$date = $_GET['date'] ?? date('Y-m-d');

$timeslots = [
    "10:00", "10:15", "10:30", "10:45", "11:00", "11:15", "11:30", "11:45",
    "12:00", "12:15", "12:30", "12:45", "13:00", "13:15", "13:30", "13:45",
    "14:00", "14:15", "14:30", "14:45", "15:00", "15:15", "15:30", "15:45",
    "16:00", "16:15", "16:30", "16:45", "17:00", "17:15", "17:30", "17:45",
    "18:00", "18:15", "18:30", "18:45", "19:00", "19:15", "19:30", "19:45",
    "20:00", "20:15", "20:30", "20:45", "21:00", "21:15", "21:30", "21:45",
    "22:00", "22:15", "22:30", "22:45", "23:00", "23:15", "23:30", "23:45",
    "00:00"
];

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_results'])) {
    $post_date = $_POST['result_date'];
    
    foreach ($timeslots as $time) {
        $val_key = 'res_' . str_replace(':', '_', $time);
        if (isset($_POST[$val_key])) {
            $val = trim($_POST[$val_key]);
            
            if ($val !== '') {
                $stmt = $db->prepare("
                    INSERT INTO fatafat_results (result_date, result_time, result_val) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE result_val = VALUES(result_val)
                ");
                $stmt->execute([$post_date, $time, $val]);
            } else {
                // If empty, delete the record so it doesn't show
                $stmt = $db->prepare("DELETE FROM fatafat_results WHERE result_date = ? AND result_time = ?");
                $stmt->execute([$post_date, $time]);
            }
        }
    }
    
    $success = "Results saved successfully for " . htmlspecialchars($post_date);
    $date = $post_date; // stay on same date
}

// Fetch existing results for this date
$stmt = $db->prepare("SELECT result_time, result_val FROM fatafat_results WHERE result_date = ?");
$stmt->execute([$date]);
$existing = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing[$row['result_time']] = $row['result_val'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .time-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .time-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .time-label {
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Fatafat Admin</a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Dashboard</a>
                <a class="nav-link active" href="results.php">Manage Results</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h4>Manage Fatafat Results</h4>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Date Selector -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="GET" class="d-flex align-items-center">
                    <label class="me-2 fw-bold">Select Date:</label>
                    <input type="date" name="date" class="form-control w-auto me-3" value="<?= htmlspecialchars($date) ?>">
                    <button type="submit" class="btn btn-secondary">Load Date</button>
                </form>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="result_date" value="<?= htmlspecialchars($date) ?>">
            
            <div class="time-grid mb-4">
                <?php foreach ($timeslots as $time): 
                    $val = $existing[$time] ?? '';
                    $input_name = 'res_' . str_replace(':', '_', $time);
                ?>
                    <div class="time-card shadow-sm">
                        <div class="time-label"><?= $time ?></div>
                        <input type="text" name="<?= $input_name ?>" class="form-control text-center font-monospace" 
                               value="<?= htmlspecialchars($val) ?>" placeholder="e.g. 123-45">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" name="save_results" class="btn btn-primary btn-lg w-100 shadow">Save Results</button>
        </form>
    </div>
</body>
</html>
