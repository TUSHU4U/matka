<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$page_title = "Dashboard Overview";

// 1. Fetch Analytics
$total_results = $db->query("SELECT COUNT(*) FROM fatafat_results")->fetchColumn();
$today = date('Y-m-d');
$today_results = $db->prepare("SELECT COUNT(*) FROM fatafat_results WHERE result_date = ?");
$today_results->execute([$today]);
$today_count = $today_results->fetchColumn();

// 2. Determine "Next Slot"
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

$current_time = date('H:i');
$next_slot = "10:00"; // default

// Handle Quick-Fill Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_fill'])) {
    $q_time = $_POST['q_time'];
    $q_val = trim($_POST['q_val']);
    
    if ($q_val !== '') {
        $stmt = $db->prepare("INSERT INTO fatafat_results (result_date, result_time, result_val) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE result_val = VALUES(result_val)");
        $stmt->execute([$today, $q_time, $q_val]);
        $success = "Successfully saved result for today at $q_time!";
    }
}

// Find next slot that doesn't have a result yet today
foreach ($timeslots as $t) {
    // If it's past midnight and checking '00:00'
    $compare_t = ($t === '00:00') ? '24:00' : $t;
    if ($compare_t >= $current_time) {
        // check if result already exists
        $chk = $db->prepare("SELECT id FROM fatafat_results WHERE result_date = ? AND result_time = ?");
        $chk->execute([$today, $t]);
        if (!$chk->fetchColumn()) {
            $next_slot = $t;
            break;
        }
    }
}

include 'includes/header.php';
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card stat-card bg-primary text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-uppercase text-white-50">Today's Results</h5>
                    <h2 class="mb-0 fw-bold"><?= $today_count ?> <span class="fs-5 text-white-50">/ 57</span></h2>
                </div>
                <i class="bi bi-calendar-day stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card bg-success text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-uppercase text-white-50">Total Records</h5>
                    <h2 class="mb-0 fw-bold"><?= $total_results ?></h2>
                </div>
                <i class="bi bi-database stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="bi bi-lightning-fill"></i> Quick Fill Result (Today)
            </div>
            <div class="card-body bg-light">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Upcoming Timeslot</label>
                        <select name="q_time" class="form-select form-select-lg">
                            <?php foreach($timeslots as $t): ?>
                                <option value="<?= $t ?>" <?= $t === $next_slot ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Result Value</label>
                        <input type="text" name="q_val" class="form-control form-control-lg font-monospace text-center" placeholder="e.g. 123-45" required autofocus>
                    </div>
                    <button type="submit" name="quick_fill" class="btn btn-warning w-100 fw-bold">Save Result</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-info-circle"></i> System Info
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP Version
                        <span class="badge bg-secondary rounded-pill"><?= phpversion() ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Database Engine
                        <span class="badge bg-secondary rounded-pill">MySQL</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Timezone
                        <span class="badge bg-secondary rounded-pill"><?= date_default_timezone_get() ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Server Time
                        <span class="badge bg-secondary rounded-pill"><?= date('H:i') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
