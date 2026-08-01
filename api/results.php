<?php
/**
 * API — Results JSON Endpoint
 * GET /api/results.php?date=YYYY-MM-DD
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only allow XHR requests
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once dirname(__DIR__) . '/includes/functions.php';

// Validate & sanitize date
$date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

// Clamp date (not in the future by more than 1 day, not older than 365 days)
$ts = strtotime($date);
if ($ts === false || $ts > strtotime('+1 day') || $ts < strtotime('-365 days')) {
    http_response_code(400);
    echo json_encode(['error' => 'Date out of range']);
    exit;
}

try {
    $results = getResultsByDate($date);

    // Sanitize output
    $clean = array_map(function ($r) {
        return [
            'id'          => (int)$r['id'],
            'name'        => htmlspecialchars($r['name'],        ENT_QUOTES|ENT_HTML5, 'UTF-8'),
            'slug'        => htmlspecialchars($r['slug'],        ENT_QUOTES|ENT_HTML5, 'UTF-8'),
            'open_time'   => $r['open_time'],
            'close_time'  => $r['close_time'],
            'category'    => $r['category'],
            'sort_order'  => (int)$r['sort_order'],
            'result_id'   => $r['result_id'] ? (int)$r['result_id'] : null,
            'result_date' => $r['result_date'],
            'open_panna'  => $r['open_panna']  ?? null,
            'open_digit'  => $r['open_digit']  ?? null,
            'jodi'        => $r['jodi']         ?? null,
            'close_digit' => $r['close_digit']  ?? null,
            'close_panna' => $r['close_panna']  ?? null,
            'status'      => $r['status']       ?? null,
        ];
    }, $results);

    echo json_encode([
        'success' => true,
        'date'    => $date,
        'count'   => count($clean),
        'results' => $clean,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
