<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$page_title = "Bulk Upload";

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

// Handle Template Download
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="matka_fatafat_template.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date (YYYY-MM-DD)', 'Time (HH:MM)', 'Result (e.g. 123-45)']);
    fputcsv($output, [date('Y-m-d'), '10:00', '123-45']);
    fputcsv($output, [date('Y-m-d'), '10:15', '456-78']);
    fclose($output);
    exit;
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext === 'csv') {
            $handle = fopen($tmp_name, 'r');
            // Skip header row
            fgetcsv($handle);
            
            $success_count = 0;
            $error_count = 0;
            
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("INSERT INTO fatafat_results (result_date, result_time, result_val) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE result_val = VALUES(result_val)");
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 3) {
                        $date = trim($data[0]);
                        $time = trim($data[1]);
                        $val = trim($data[2]);
                        
                        // Basic validation
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && in_array($time, $timeslots) && $val !== '') {
                            $stmt->execute([$date, $time, $val]);
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                $db->commit();
                $success = "Successfully imported $success_count records. (Skipped/Invalid: $error_count)";
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Database error during import: " . $e->getMessage();
            }
            fclose($handle);
        } else {
            $error = "Please upload a valid CSV file.";
        }
    } else {
        $error = "File upload error.";
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-cloud-arrow-up-fill"></i> Upload CSV File
            </div>
            <div class="card-body bg-light">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select CSV File</label>
                        <input class="form-control" type="file" name="csv_file" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-upload"></i> Process File</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white fw-bold">
                <i class="bi bi-filetype-csv"></i> CSV Instructions
            </div>
            <div class="card-body">
                <p>Use the CSV bulk upload to import multiple results at once.</p>
                <ul>
                    <li><strong>Format:</strong> Must be a <code>.csv</code> file.</li>
                    <li><strong>Columns:</strong> Date, Time, Result</li>
                    <li><strong>Date:</strong> <code>YYYY-MM-DD</code> (e.g. 2024-05-12)</li>
                    <li><strong>Time:</strong> Must be one of the exact 57 timeslots (e.g. <code>10:15</code>, <code>14:30</code>, <code>00:00</code>)</li>
                    <li><strong>Result:</strong> Any string (e.g. <code>123-45</code>)</li>
                </ul>
                
                <a href="?download_template=1" class="btn btn-outline-dark mt-3"><i class="bi bi-download"></i> Download CSV Template</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
