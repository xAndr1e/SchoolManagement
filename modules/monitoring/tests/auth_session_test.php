<?php
chdir(__DIR__ . '/..');
require_once __DIR__ . '/../controllers/AuthController.php';

if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}
$_SESSION = [];

$controller = new AuthController();
$result = $controller->login('admin', 'admin123');

if (!$result['success']) {
    fwrite(STDERR, "Login failed: " . json_encode($result) . PHP_EOL);
    exit(1);
}

$check = $controller->checkAuth();
if (!$check['authenticated']) {
    fwrite(STDERR, "Session was not persisted after login." . PHP_EOL);
    exit(1);
}

$loggedIn = $controller->getCurrentUser();
if (!$loggedIn || $loggedIn['username'] !== 'admin') {
    fwrite(STDERR, "Current user could not be read from session." . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Auth session test passed." . PHP_EOL);
