<?php
// Shared helpers for clinic JSON handlers.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once __DIR__ . '/../../../database/db.php';

function jsonOut(array $data, int $status = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function getDb(): PDO
{
    global $conn;
    if (class_exists('Database')) {
        $db = (new Database())->getConnection();
        if ($db instanceof PDO) {
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        }
    }
    if (isset($conn) && $conn instanceof PDO) {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
    throw new RuntimeException('Database connection is not available.');
}

function readJsonBody(): array
{
    $data = json_decode((string) file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function requirePostWithCsrf(array $body): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonOut(['success' => false, 'message' => 'POST required.'], 405);
    }
    $sent = (string) ($body['csrf_token'] ?? '');
    $real = (string) ($_SESSION['csrf_token'] ?? '');
    if ($real === '' || !hash_equals($real, $sent)) {
        jsonOut(['success' => false, 'message' => 'Invalid or expired session token. Please refresh the page.'], 403);
    }
}

// DB row -> shape the front-end expects.
function mapConsultation(array $r): array
{
    return [
        'id'               => (int) $r['consultation_id'],
        'student_number'   => $r['student_number'],
        'patient_name'     => $r['patient_name'],
        'patient_age'      => $r['age'],
        'patient_gender'   => $r['gender'],
        'patient_course'   => $r['course'],
        'patient_year'     => $r['year_level'],
        'nurse'            => $r['nurse'],
        'visit_type'       => $r['visit_type'],
        'appointment_date' => $r['visit_date'],
        'appointment_time' => $r['visit_time'] !== null ? substr((string) $r['visit_time'], 0, 5) : '',
        'temperature'      => $r['temperature'],
        'blood_pressure'   => $r['blood_pressure'],
        'heart_rate'       => $r['heart_rate'],
        'respiratory_rate' => $r['respiratory_rate'],
        'oxygen_sat'       => $r['oxygen_saturation'],
        'symptoms'         => $r['symptoms'],
        'diagnosis'        => $r['diagnosis'],
        'treatment'        => $r['treatment_medication'],
        'notes'            => $r['additional_notes'],
        'submitted_at'     => $r['created_at'],
    ];
}
