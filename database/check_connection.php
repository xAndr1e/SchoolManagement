<?php

// Run on the hosting server: php database/check_connection.php
// Do not expose connection diagnostics through a public web endpoint.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$config = require __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        $config['options'] + [PDO::ATTR_TIMEOUT => 10]
    );
    $pdo->exec('SET SESSION time_zone = ' . $pdo->quote($config['timezone']));
    $database = $pdo->query('SELECT DATABASE()')->fetchColumn();
    fwrite(STDOUT, "Connected to {$database} at {$config['host']}:{$config['port']}." . PHP_EOL);

    $requiredTables = [
        'user_account', 'em_employees', 'em_roles', 'em_departments',
        'mon_attendance_records', 'mon_attendance_archive', 'mon_facilities',
        'mon_facility_reports', 'mon_facility_reports_archive', 'mon_visitors',
        'mon_visitors_archive', 'mon_visitor_audit_logs',
        'cc_schedule', 'cc_faculty', 'cc_room', 'cc_sections', 'rgr_subjects',
    ];
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_diff($requiredTables, $tables);

    if ($missing) {
        fwrite(STDERR, 'Missing tables: ' . implode(', ', $missing) . PHP_EOL);
        fwrite(STDERR, 'Import the system database into moni_test before using the application.' . PHP_EOL);
        exit(1);
    }

    // Validate the main login schema without reading any employee records.
    $pdo->query("SELECT ua.user_id, ua.employee_id, ua.password,
        e.first_name, e.middle_name, e.last_name, e.role_id, e.department_id,
        e.employment_status, r.role_name, d.department_code, d.department_name
        FROM user_account ua
        INNER JOIN em_employees e ON e.employee_id = ua.employee_id
        LEFT JOIN em_roles r ON r.role_id = e.role_id
        LEFT JOIN em_departments d ON d.department_id = e.department_id
        LIMIT 0");

    fwrite(STDOUT, 'Required login and monitoring tables are present; login columns are valid.' . PHP_EOL);
    exit(0);
} catch (PDOException $e) {
    // Avoid printing the password or any database contents.
    fwrite(STDERR, 'Database check failed (SQLSTATE ' . $e->getCode() . ').' . PHP_EOL);
    fwrite(STDERR, 'Check the MySQL host, port, database user permissions, password, and imported schema.' . PHP_EOL);
    exit(1);
}
