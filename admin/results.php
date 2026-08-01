<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$page_title = "Manage Results";

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
                $stmt = $db->prepare("DELETE FROM fatafat_results WHERE result_date = ? AND result_time = ?");
                $stmt->execute([$post_date, $time]);
            }
        }
    }
    
    $success = "Results saved successfully for " . htmlspecialchars($post_date);
    $date = $post_date;
}

// Handle Clear All
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all'])) {
    $post_date = $_POST['result_date'];
    $stmt = $db->prepare("DELETE FROM fatafat_results WHERE result_date = ?");
    $stmt->execute([$post_date]);
    $success = "All results cleared for " . htmlspecialchars($post_date);
    $date = $post_date;
}

// Fetch existing results for this date
$stmt = $db->prepare("SELECT result_time, result_val FROM fatafat_results WHERE result_date = ?");
$stmt->execute([$date]);
$existing = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existing[$row['result_time']] = $row['result_val'];
}

include 'includes/header.php';
?>

<style>
    .time-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    .time-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        transition: border-color 0.2s;
    }
    .time-card:focus-within {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }
    .time-label {
        font-weight: bold;
        color: #495057;
        margin-bottom: 5px;
    }
</style>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Date Selector -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body d-flex justify-content-between align-items-center bg-white rounded">
        <form method="GET" class="d-flex align-items-center mb-0">
            <label class="me-3 fw-bold text-muted">Viewing Date:</label>
            <input type="date" name="date" class="form-control w-auto me-3" value="<?= htmlspecialchars($date) ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Load</button>
        </form>

        <form method="POST" class="mb-0" onsubmit="return confirm('Are you sure you want to delete ALL results for <?= htmlspecialchars($date) ?>? This cannot be undone.');">
            <input type="hidden" name="result_date" value="<?= htmlspecialchars($date) ?>">
            <button type="submit" name="clear_all" class="btn btn-outline-danger"><i class="bi bi-trash3-fill"></i> Clear All for Date</button>
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
                <input type="text" name="<?= $input_name ?>" class="form-control text-center font-monospace <?= $val ? 'border-success bg-success bg-opacity-10' : '' ?>" 
                       value="<?= htmlspecialchars($val) ?>" placeholder="---">
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="position-sticky bottom-0 bg-white p-3 border-top shadow-lg" style="z-index: 1000; margin-left: -20px; margin-right: -20px;">
        <button type="submit" name="save_results" class="btn btn-success btn-lg px-5 shadow"><i class="bi bi-save2-fill"></i> Save All Results</button>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
