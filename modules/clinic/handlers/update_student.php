<?php
// DUMMY VERSION: walang DB. Sine-save lang sa $_SESSION.
// POST JSON -> {"success": true} o {"success": false, "message": "..."}

// Ibalik ito kapag may DB na (at tanggalin ang fallback sa ibaba):
// require_once __DIR__ . '/_bootstrap.php';
if (!function_exists('jsonOut')) {
    function jsonOut($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
require_once __DIR__ . '/../classes/Student.php';

try {
    $in = json_decode(file_get_contents('php://input'), true);
    if (!is_array($in)) {
        jsonOut(['success' => false, 'message' => 'Invalid request.'], 400);
    }

    $orig = trim((string) ($in['original_number'] ?? ''));
    if ($orig === '' || trim((string) ($in['first_name'] ?? '')) === '' || trim((string) ($in['last_name'] ?? '')) === '') {
        jsonOut(['success' => false, 'message' => 'Student number, first name and last name are required.'], 422);
    }

    $ok = (new Student())->updateStudent($orig, $in);
    jsonOut($ok ? ['success' => true] : ['success' => false, 'message' => 'Student not found.'], $ok ? 200 : 404);
} catch (Throwable $e) {
    error_log('update_student: ' . $e->getMessage());
    jsonOut(['success' => false, 'message' => 'Could not update student.'], 500);
}