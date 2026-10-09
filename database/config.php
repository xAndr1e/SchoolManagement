<?php

// Shared by the main login, Database2, and the monitoring module.
// Hosting environment variables take precedence over config.local.php.
return (static function () {
    $localPath = __DIR__ . '/config.local.php';
    $local = is_file($localPath) ? require $localPath : [];

    if (!is_array($local)) {
        throw new RuntimeException('database/config.local.php must return an array.');
    }

    $env = static function (array $names, $fallback, bool $allowEmpty = false) {
        foreach ($names as $name) {
            $value = getenv($name);
            if ($value !== false && ($allowEmpty || $value !== '')) {
                return $value;
            }
        }

        return $fallback;
    };

    return [
        'host' => $env(['DB_HOST'], $local['host'] ?? 'localhost'),
        'port' => $env(['DB_PORT'], $local['port'] ?? '3306'),
        'dbname' => $env(['DB_DATABASE', 'DB_NAME'], $local['dbname'] ?? 'moni_test'),
        'username' => $env(['DB_USER'], $local['username'] ?? 'moni_monitoring'),
        'password' => $env(['DB_PASSWORD', 'DB_PASS'], $local['password'] ?? '', true),
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ],
        'timezone' => '+08:00',
    ];
})();
