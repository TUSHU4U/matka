<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/db.php';

// ─── Output Sanitization ──────────────────────────────────────────────────────

/**
 * Escape output for HTML context (XSS prevention).
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize a string input.
 */
function sanitize(string $input): string {
    return trim(strip_tags($input));
}

// ─── Results ──────────────────────────────────────────────────────────────────

/**
 * Get all active games with their latest result for a given date.
 */
function getResultsByDate(string $date): array {
    $stmt = pdo()->prepare("
        SELECT g.id, g.name, g.slug, g.open_time, g.close_time, g.category, g.sort_order,
               r.open_panna, r.open_digit, r.jodi, r.close_digit, r.close_panna,
               r.status, r.id AS result_id, r.result_date
        FROM games g
        LEFT JOIN results r ON g.id = r.game_id AND r.result_date = :date AND r.status = 'published'
        WHERE g.is_active = 1
        ORDER BY g.sort_order ASC, g.name ASC
    ");
    $stmt->execute([':date' => $date]);
    return $stmt->fetchAll();
}

/**
 * Get results for a date range (chart view).
 */
function getResultsByDateRange(int $gameId, string $from, string $to): array {
    $stmt = pdo()->prepare("
        SELECT r.*, g.name AS game_name, g.open_time, g.close_time
        FROM results r
        JOIN games g ON g.id = r.game_id
        WHERE r.game_id = :game_id
          AND r.result_date BETWEEN :from AND :to
          AND r.status = 'published'
        ORDER BY r.result_date DESC
    ");
    $stmt->execute([':game_id' => $gameId, ':from' => $from, ':to' => $to]);
    return $stmt->fetchAll();
}

/**
 * Get all active games (for dropdowns, chart selector, etc.).
 */
function getAllGames(bool $activeOnly = true): array {
    $sql = "SELECT * FROM games";
    if ($activeOnly) $sql .= " WHERE is_active = 1";
    $sql .= " ORDER BY sort_order ASC, name ASC";
    return pdo()->query($sql)->fetchAll();
}

/**
 * Get a single game by slug.
 */
function getGameBySlug(string $slug): ?array {
    $stmt = pdo()->prepare("SELECT * FROM games WHERE slug = :slug AND is_active = 1 LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get a single game by id.
 */
function getGameById(int $id): ?array {
    $stmt = pdo()->prepare("SELECT * FROM games WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get a result row for a game/date.
 */
function getResult(int $gameId, string $date): ?array {
    $stmt = pdo()->prepare("
        SELECT * FROM results WHERE game_id = :gid AND result_date = :date LIMIT 1
    ");
    $stmt->execute([':gid' => $gameId, ':date' => $date]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get a single result by id.
 */
function getResultById(int $id): ?array {
    $stmt = pdo()->prepare("SELECT r.*, g.name AS game_name FROM results r JOIN games g ON g.id = r.game_id WHERE r.id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ─── Settings ─────────────────────────────────────────────────────────────────

/**
 * Get a site setting by key.
 */
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = pdo()->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['setting_value'] : $default;
    }
    return $cache[$key];
}

/**
 * Set a site setting.
 */
function setSetting(string $key, string $value): void {
    $stmt = pdo()->prepare("
        INSERT INTO site_settings (setting_key, setting_value)
        VALUES (:k, :v)
        ON DUPLICATE KEY UPDATE setting_value = :v2
    ");
    $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}

// ─── Formatting ───────────────────────────────────────────────────────────────

/**
 * Format time as 12-hour (e.g., 15:15 → 3:15 PM).
 */
function formatTime(string $time): string {
    if (!$time) return '—';
    return date('g:i A', strtotime($time));
}

/**
 * Format date nicely (e.g., 01/08/2026).
 */
function formatDate(string $date): string {
    return date('d/m/Y', strtotime($date));
}

/**
 * Display a result digit with a dash if empty.
 */
function displayResult(?string $val): string {
    return ($val !== null && $val !== '') ? e($val) : '**';
}

/**
 * Get today's date in Y-m-d format (IST).
 */
function today(): string {
    return date('Y-m-d');
}

/**
 * Generate a URL-safe slug.
 */
function makeSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ─── Pagination ───────────────────────────────────────────────────────────────

function paginate(int $total, int $perPage, int $current): array {
    $totalPages = (int) ceil($total / $perPage);
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $current,
        'total_pages' => $totalPages,
        'offset'      => ($current - 1) * $perPage,
        'has_prev'    => $current > 1,
        'has_next'    => $current < $totalPages,
    ];
}

// ─── Flash Messages ───────────────────────────────────────────────────────────

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash(): void {
    $flash = getFlash();
    if (!$flash) return;
    $type = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info');
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    echo e($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}
