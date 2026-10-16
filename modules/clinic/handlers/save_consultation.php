<?php
// POST (JSON) -> inserts one row into clinic_consultations
require_once __DIR__ . '/_bootstrap.php';

try {
    $b = readJsonBody();
    requirePostWithCsrf($b);

    $s = static fn($k) => trim((string) ($b[$k] ?? ''));
    $n = static function ($k) use ($b) {          // optional field -> null when empty
        $v = trim((string) ($b[$k] ?? ''));
        return $v === '' ? null : $v;
    };

    foreach (['patient_name' => 'patient name', 'nurse' => 'nurse', 'visit_type' => 'visit type',
              'appointment_date' => 'date', 'appointment_time' => 'time'] as $k => $label) {
        if ($s($k) === '') {
            jsonOut(['success' => false, 'message' => "Please provide $label."], 422);
        }
    }

    $db = getDb();
    $stmt = $db->prepare('INSERT INTO clinic_consultations
        (student_number, patient_name, age, gender, course, year_level, temperature, blood_pressure,
         heart_rate, respiratory_rate, oxygen_saturation, nurse, visit_type, visit_date, visit_time,
         symptoms, diagnosis, treatment_medication, additional_notes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $n('student_number'), $s('patient_name'), $n('patient_age'), $n('patient_gender'),
        $n('patient_course'), $n('patient_year'), $n('temperature'), $n('blood_pressure'),
        $n('heart_rate'), $n('respiratory_rate'), $n('oxygen_sat'), $s('nurse'), $s('visit_type'),
        $s('appointment_date'), $s('appointment_time'),
        $n('symptoms'), $n('diagnosis'), $n('treatment'), $n('notes'),
    ]);

    jsonOut(['success' => true, 'message' => 'Consultation saved successfully.', 'id' => (int) $db->lastInsertId()]);
} catch (Throwable $e) {
    error_log('save_consultation: ' . $e->getMessage());
    jsonOut(['success' => false, 'message' => 'Could not save consultation.'], 500);
}
