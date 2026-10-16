<?php
// GET ?student_number=...  -> one student row, or {"error": "..."}
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../classes/Student.php';

try {
    $num = trim((string) ($_GET['student_number'] ?? ''));
    if ($num === '') {
        jsonOut(['error' => 'Student number is required.'], 422);
    }
    $student = (new Student(getDb()))->getStudentByNumber($num);
    if (!$student) {
        jsonOut(['error' => 'Student not found.'], 404);
    }
    jsonOut($student);
} catch (Throwable $e) {
    error_log('get_student: ' . $e->getMessage());
    jsonOut(['error' => 'Could not load student.'], 500);
}
