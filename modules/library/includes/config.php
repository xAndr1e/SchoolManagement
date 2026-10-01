<?php
// ============================================================
// includes/config.php — Connected to SMS database
// ============================================================
define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT') ?: '3306');
define('DB_USER',    getenv('DB_USER') ?: 'root');
define('DB_PASS',    getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: ''));
define('DB_NAME',    getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'sms');
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_DIR', __DIR__ . '/../uploads/covers/');
define('UPLOAD_URL', 'uploads/covers/');

// ============================================================
// Autoloader
// ============================================================
spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/';
    $dirs = [$base, $base . '../controllers/', $base . '../api/'];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
