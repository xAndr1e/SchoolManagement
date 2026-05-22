<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../database/db.php';
require_once __DIR__ . '/../classes/Student.php';

try {
    if (!isset($_GET['student_number']) || empty($_GET['student_number'])) {
        throw new Exception('student_number parameter missing');
    }
    $student_number = $_GET['student_number'];
    $db = new Database();
    $student = new Student($db->getConnection());
    $data = $student->getStudentById($student_number);
    if ($data) {
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
}