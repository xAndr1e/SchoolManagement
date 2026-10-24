<?php
// Shared helpers for clinic JSON handlers.
ob_start();
ini_set('display_errors', '0');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    $value = static function (array $keys) use ($r) {
        foreach ($keys as $key) {
            if (isset($r[$key]) && $r[$key] !== '') {
                return $r[$key];
            }
        }
        return null;
    };

    $appointmentDate = trim((string) ($r['appointment_date'] ?? ''));
    $useVisitDate = $appointmentDate === '' || $appointmentDate === '0000-00-00';
    if ($useVisitDate) {
        $appointmentDate = trim((string) ($r['visit_date'] ?? ''));
    }
    if ($appointmentDate === '0000-00-00') {
        $appointmentDate = '';
    }

    $appointmentTime = $useVisitDate
        ? ($r['visit_time'] ?? $r['appointment_time'] ?? '')
        : ($r['appointment_time'] ?? $r['visit_time'] ?? '');

    return [
        'id'               => (int) ($r['consultation_id'] ?? $r['id'] ?? 0),
        'consultation_id'  => (int) ($r['consultation_id'] ?? $r['id'] ?? 0),
        'student_id'       => $value(['student_id']),
        'student_number'   => $value(['student_number']),
        'patient_name'     => $value(['patient_name']),
        'patient_age'      => $value(['patient_age', 'age']),
        'patient_gender'   => $value(['patient_gender', 'gender']),
        'patient_course'   => $value(['patient_course', 'course']),
        'patient_year'     => $value(['patient_year', 'year_level']),
        'nurse'            => $value(['nurse']),
        'visit_type'       => $value(['visit_type']),
        'appointment_date' => $appointmentDate,
        'appointment_time' => $appointmentTime !== null && $appointmentTime !== ''
            ? substr((string) $appointmentTime, 0, 5)
            : '',
        'temperature'      => $value(['temperature']),
        'blood_pressure'   => $value(['blood_pressure']),
        'heart_rate'       => $value(['heart_rate']),
        'respiratory_rate' => $value(['respiratory_rate']),
        'oxygen_sat'       => $value(['oxygen_sat', 'oxygen_saturation']),
        'symptoms'         => $value(['symptoms']),
        'diagnosis'        => $value(['diagnosis']),
        'treatment'        => $value(['treatment', 'treatment_medication']),
        'notes'            => $value(['notes', 'additional_notes']),
        'submitted_at'     => $value(['submitted_at', 'created_at']),
        'created_at'       => $value(['created_at', 'submitted_at']),
        'chief_complaint'  => $value(['chief_complaint']),
        'physical_examination' => $value(['physical_examination']),
        'assessment'       => $value(['assessment']),
        'treatment_given'  => $value(['treatment_given']),
        'medication_given' => $value(['medication_given']),
        'dosage'           => $value(['dosage']),
        'instructions'     => $value(['instructions']),
        'allergies'        => $value(['allergies']),
        'existing_conditions' => $value(['existing_conditions']),
        'referral_required' => $value(['referral_required']),
        'referral_destination' => $value(['referral_destination']),
        'status'           => $value(['status']),
        'section'          => $value(['section']),
    ];
}
