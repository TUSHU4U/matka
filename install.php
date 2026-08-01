<?php
/**
 * Database Installer — Fatafat System
 * Run once, then DELETE this file.
 */

define('INSTALLER_SECRET', 'install_matka_2024');

require_once __DIR__ . '/includes/config.php';

if (($_GET['key'] ?? '') !== INSTALLER_SECRET) {
    die('<h2 style="font-family:sans-serif;color:red">Access Denied. Add ?key=install_matka_2024 to URL.</h2>');
}

$messages = [];

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $messages[] = '✅ Database connection successful.';

    // Drop old tables to migrate to Fatafat system
    $pdo->exec("DROP TABLE IF EXISTS `results`");
    $pdo->exec("DROP TABLE IF EXISTS `games`");
    $pdo->exec("DROP TABLE IF EXISTS `site_settings`");
    $pdo->exec("DROP TABLE IF EXISTS `login_attempts`");
    $messages[] = '✅ Old Open/Close schema dropped.';

    // 1. Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key` VARCHAR(50) PRIMARY KEY,
        `setting_value` TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `settings` created.';

    // Insert default marquee
    $pdo->exec("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES 
        ('marquee', 'Disclaimer: viewing this website is on your own risk. All the information here is based on numeric astrology n is not related to any type of gambling . We warn you that gambling in our country may be banned or illegal .. We are not responsible for any issue or scam.. We respect all country rules/laws..if you not agree with our site disclaimer..please quit our site right now .')");

    // 2. Fatafat Results Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `fatafat_results` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `result_date` DATE NOT NULL,
        `result_time` VARCHAR(10) NOT NULL,
        `result_val` VARCHAR(15) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `date_time_unique` (`result_date`, `result_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `fatafat_results` created.';

    // 3. Admin Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `admin_users` created.';

    // Default admin
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `admin_users` (`username`, `password_hash`) VALUES ('admin', ?)");
    $stmt->execute([$hash]);
    $messages[] = '✅ Default admin created (username: <b>admin</b> / password: <b>admin123</b>).';

    echo "<h3>Installation Successful!</h3>";
    echo "<ul>";
    foreach ($messages as $msg) {
        echo "<li>{$msg}</li>";
    }
    echo "</ul>";
    echo "<p style='color:red'><strong>CRITICAL: Delete install.php immediately!</strong></p>";
    echo "<a href='admin/'>Go to Admin Panel</a>";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
