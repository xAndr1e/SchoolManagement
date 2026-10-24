<?php
// POST (JSON) -> inserts one row into clinic_consultations
require_once __DIR__ . '/_bootstrap.php';

try {
    $b = readJsonBody();
    foreach ($b as $value) {
        if (!is_scalar($value) && $value !== null) {
            jsonOut(['success' => false, 'message' => 'Consultation fields must contain simple values.'], 422);
        }
    }
    requirePostWithCsrf($b);

    $s = static fn($k) => trim((string) ($b[$k] ?? ''));
    $n = static function ($k) use ($b) {
        $v = trim((string) ($b[$k] ?? ''));
        return $v === '' ? null : $v;
    };

    foreach (['student_number' => 'student number', 'patient_name' => 'patient name', 'nurse' => 'nurse', 'visit_type' => 'visit type',
              'appointment_date' => 'date', 'appointment_time' => 'time'] as $k => $label) {
        if ($s($k) === '') {
            jsonOut(['success' => false, 'message' => "Please provide $label."], 422);
        }
    }

    $studentId = $n('student_id');
    if ($studentId !== null && (!ctype_digit($studentId) || (int) $studentId <= 0)) {
        jsonOut(['success' => false, 'message' => 'Please select a valid student.'], 422);
    }

    $date = $s('appointment_date');
    $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$dateValue || $dateValue->format('Y-m-d') !== $date) {
        jsonOut(['success' => false, 'message' => 'Please provide a valid date.'], 422);
    }

    $time = $s('appointment_time');
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?:\:[0-5]\d)?$/', $time)) {
        jsonOut(['success' => false, 'message' => 'Please provide a valid time.'], 422);
    }

    $age = $n('patient_age');
    if ($age !== null && (!ctype_digit($age) || (int) $age > 150)) {
        jsonOut(['success' => false, 'message' => 'Please provide a valid patient age.'], 422);
    }

    $oxygenSat = $n('oxygen_sat');
    if ($oxygenSat !== null && (!ctype_digit($oxygenSat) || (int) $oxygenSat > 100)) {
        jsonOut(['success' => false, 'message' => 'Oxygen saturation must be between 0 and 100.'], 422);
    }

    foreach ([
        'temperature' => [35, 42, false],
        'heart_rate' => [40, 200, true],
        'respiratory_rate' => [8, 40, true],
    ] as $field => [$minimum, $maximum, $integer]) {
        $number = $n($field);
        if ($number !== null && (
            !is_numeric($number)
            || (float) $number < $minimum
            || (float) $number > $maximum
            || ($integer && (float) $number !== (float) (int) $number)
        )) {
            jsonOut(['success' => false, 'message' => 'Please provide a valid ' . str_replace('_', ' ', $field) . '.'], 422);
        }
    }

    $bloodPressure = $n('blood_pressure');
    if ($bloodPressure !== null && !preg_match('/^\d{2,3}\/\d{2,3}$/', $bloodPressure)) {
        jsonOut(['success' => false, 'message' => 'Blood pressure must be in format 120/80.'], 422);
    }

    $timeForDatabase = strlen($time) === 5 ? $time . ':00' : $time;
    $db = getDb();
    $stmt = $db->prepare('INSERT INTO clinic_consultations
        (student_id, student_number, patient_name, patient_age, patient_gender, patient_course, patient_year,
         nurse, visit_type, appointment_date, appointment_time, temperature, blood_pressure, heart_rate,
         respiratory_rate, oxygen_sat, symptoms, diagnosis, treatment, notes, visit_date, visit_time,
         age, gender, course, year_level, oxygen_saturation, treatment_medication, additional_notes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $studentId !== null ? (int) $studentId : null,
        $n('student_number'), $s('patient_name'), $age !== null ? (int) $age : null,
        $n('patient_gender'), $n('patient_course'), $n('patient_year'),
        $s('nurse'), $s('visit_type'), $date, $timeForDatabase, $n('temperature'),
        $bloodPressure, $n('heart_rate'), $n('respiratory_rate'),
        $oxygenSat !== null ? (int) $oxygenSat : null,
        $n('symptoms'), $n('diagnosis'), $n('treatment'), $n('notes'),
        $date, $timeForDatabase,
        $age !== null ? (int) $age : null, $n('patient_gender'), $n('patient_course'), $n('patient_year'),
        $oxygenSat, $n('treatment'), $n('notes'),
    ]);

    jsonOut(['success' => true, 'message' => 'Consultation saved successfully.', 'id' => (int) $db->lastInsertId()]);
} catch (Throwable $e) {
    error_log('save_consultation: ' . $e->getMessage());
    $message = 'Could not save consultation.';
    $debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
    if ($debug && $e instanceof PDOException) {
        $message .= ' ' . $e->getMessage();
    }
    jsonOut(['success' => false, 'message' => $message], 500);
}
