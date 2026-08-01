<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = Database::getInstance();

$dd = $_POST['dd'] ?? ''; // Format expected: dd_mm_yyyy e.g. 12_05_2024

if (!$dd) {
    echo "<br>";
    exit;
}

$parts = explode('_', $dd);
if (count($parts) !== 3) {
    echo "<br>";
    exit;
}

$date = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // YYYY-MM-DD

// Fetch results for this date
$stmt = $db->prepare("SELECT result_time, result_val FROM fatafat_results WHERE result_date = ?");
$stmt->execute([$date]);

$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[$row['result_time']] = $row['result_val'];
}

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

$output = "";
foreach ($timeslots as $time) {
    if (isset($results[$time]) && $results[$time] !== '') {
        $output .= $time . "," . $results[$time] . "-";
    } else {
        $output .= $time . "-";
    }
}

if (empty($results)) {
    // If absolutely no results, the reference site sometimes expects "<br" or something to trigger empty chart
    // But sending the empty array with hyphens works too.
    echo $output;
} else {
    echo $output;
}
