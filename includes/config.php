<?php
/**
 * Matka Result Portal — Configuration
 *
 * On Railway, database credentials are injected automatically as environment
 * variables by the MySQL plugin. This file reads those variables first and
 * falls back to hardcoded values for other hosts (InfinityFree, local, etc.).
 *
 * Railway MySQL env vars:  MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD
 * Fallback (InfinityFree): hardcoded values below
 */

// ─── Database ─────────────────────────────────────────────────────────────────
// Priority: Railway env vars → hardcoded fallback
define('DB_HOST',    getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: 'mysql.railway.internal');
define('DB_PORT',    getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT')     ?: '3306');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway');
define('DB_USER',    getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: 'tkVxqNBrZsPMnivyiJLlkHBollusmbAP');
define('DB_CHARSET', 'utf8mb4');

// ─── Application ──────────────────────────────────────────────────────────────
// Railway injects RAILWAY_PUBLIC_DOMAIN (e.g. "myapp.up.railway.app")
$_railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN') ?: '';
define('APP_NAME',    'RK Matka');
define('APP_URL',
    getenv('APP_URL')
    ?: ($_railwayDomain ? 'https://' . $_railwayDomain : 'https://matka-production.up.railway.app')
);   // No trailing slash
unset($_railwayDomain);
define('APP_VERSION', '1.0.0');
define('TIMEZONE',    'Asia/Kolkata');

// ─── Security ─────────────────────────────────────────────────────────────────
define('SESSION_NAME',          'matka_sess');
define('SESSION_LIFETIME',      3600);          // 1 hour
define('CSRF_TOKEN_LENGTH',     32);
define('LOGIN_MAX_ATTEMPTS',    5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ─── Paths ────────────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('ADMIN_PATH',  ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// ─── Environment ──────────────────────────────────────────────────────────────
define('DEBUG_MODE', false);   // Set true only on localhost

// ─── Timezone & Error Reporting ───────────────────────────────────────────────
date_default_timezone_set(TIMEZONE);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
