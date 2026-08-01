<?php
/**
 * Authentication & Session Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ─── Session Bootstrap ────────────────────────────────────────────────────────

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// ─── Login / Logout ───────────────────────────────────────────────────────────

/**
 * Attempt admin login with rate limiting.
 * Returns ['success' => bool, 'message' => string]
 */
function attemptLogin(string $username, string $password): array {
    $ip = getUserIP();

    // Rate-limit check
    if (isRateLimited($ip)) {
        return ['success' => false, 'message' => 'Too many failed attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes.'];
    }

    $stmt = pdo()->prepare("SELECT * FROM admin_users WHERE username = :u AND is_active = 1 LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Clear failed attempts on success
        clearLoginAttempts($ip);

        // Regenerate session to prevent fixation
        session_regenerate_id(true);

        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_last_activity'] = time();

        // Update last_login timestamp
        pdo()->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id")
             ->execute([':id' => $user['id']]);

        return ['success' => true, 'message' => 'Login successful.'];
    }

    // Record failed attempt
    recordLoginAttempt($ip);
    return ['success' => false, 'message' => 'Invalid username or password.'];
}

function adminLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ─── Auth Check ───────────────────────────────────────────────────────────────

/**
 * Require admin authentication. Redirect to login if not authenticated.
 */
function requireAdmin(): void {
    startSecureSession();

    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }

    // Session timeout check
    if (isset($_SESSION['admin_last_activity']) &&
        (time() - $_SESSION['admin_last_activity']) > SESSION_LIFETIME) {
        adminLogout();
        header('Location: ' . APP_URL . '/admin/login.php?timeout=1');
        exit;
    }

    $_SESSION['admin_last_activity'] = time();
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

// ─── Rate Limiting ────────────────────────────────────────────────────────────

function recordLoginAttempt(string $ip): void {
    pdo()->prepare("INSERT INTO login_attempts (ip_address, attempted_at) VALUES (:ip, NOW())")
         ->execute([':ip' => $ip]);
}

function clearLoginAttempts(string $ip): void {
    pdo()->prepare("DELETE FROM login_attempts WHERE ip_address = :ip")
         ->execute([':ip' => $ip]);
}

function isRateLimited(string $ip): bool {
    $window = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60));
    $stmt = pdo()->prepare("
        SELECT COUNT(*) AS cnt FROM login_attempts
        WHERE ip_address = :ip AND attempted_at > :window
    ");
    $stmt->execute([':ip' => $ip, ':window' => $window]);
    $row = $stmt->fetch();
    return (int)$row['cnt'] >= LOGIN_MAX_ATTEMPTS;
}

// ─── Utilities ────────────────────────────────────────────────────────────────

function getUserIP(): string {
    $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Hash a password using bcrypt.
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}
