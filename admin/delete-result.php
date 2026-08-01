<?php
/**
 * Admin — Delete Result (POST only)
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/results.php');
    exit;
}

validateCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $r = getResultById($id);
    if ($r) {
        pdo()->prepare("DELETE FROM results WHERE id = :id")->execute([':id' => $id]);
        setFlash('success', 'Result deleted successfully.');
    } else {
        setFlash('error', 'Result not found.');
    }
} else {
    setFlash('error', 'Invalid request.');
}

header('Location: ' . APP_URL . '/admin/results.php');
exit;
