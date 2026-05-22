<?php
/**
 * Get Counselors API
 * Path: modules/counseling-reports/api/get-counselors.php
 *
 * Returns a JSON array of all counselors for dropdown population.
 */

@ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/../classes/Counselors.php';

try {
    $db         = new Database();
    $counselors = new Counselors($db->getConnection());

    $stmt = $counselors->read();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}