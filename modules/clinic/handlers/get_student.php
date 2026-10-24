<?php
// DUMMY VERSION: walang DB.
// GET ?student_number=...  -> one student row, or {"error": "..."}

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
    $num = trim((string) ($_GET['student_number'] ?? ''));
    if ($num === '') {
        jsonOut(['error' => 'Student number is required.'], 422);
    }
    $student = (new Student())->getStudentByNumber($num); // walang getDb() sa dummy
    if (!$student) {
        jsonOut(['error' => 'Student not found.'], 404);
    }
    jsonOut($student);
} catch (Throwable $e) {
    error_log('get_student: ' . $e->getMessage());
    jsonOut(['error' => 'Could not load student.'], 500);
}