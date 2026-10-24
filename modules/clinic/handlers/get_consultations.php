<?php
    // GET  ?page=1&search=&limit=10   -> paginated list
    // GET  ?action=single&id=123      -> one record
    require_once __DIR__ . '/_bootstrap.php';

    const CONSULT_COLUMNS = 'id AS consultation_id, student_id, student_number, patient_name, patient_age,
        patient_gender, patient_course, patient_year, age, gender, course, year_level, nurse, visit_type,
        appointment_date, appointment_time, visit_date, visit_time, temperature, blood_pressure, heart_rate,
        respiratory_rate, oxygen_sat, oxygen_saturation, symptoms, diagnosis, treatment, treatment_medication,
        notes, additional_notes, submitted_at, chief_complaint, physical_examination, assessment,
        treatment_given, medication_given, dosage, instructions, allergies, existing_conditions,
        referral_required, referral_destination, status, section';
    try {
        $db = getDb();

        if (($_GET['action'] ?? '') === 'single') {
            $id = (int) ($_GET['id'] ?? 0);
            $stmt = $db->prepare('SELECT ' . CONSULT_COLUMNS . ' FROM clinic_consultations WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                jsonOut(['success' => false, 'message' => 'Record not found.'], 404);
            }
            jsonOut(['success' => true, 'data' => mapConsultation($row)]);
        }

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = min(200, max(1, (int) ($_GET['limit'] ?? 10)));
        $search = trim((string) ($_GET['search'] ?? ''));

        $where  = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE CAST(student_number AS CHAR) LIKE :s1 OR patient_name LIKE :s2 OR nurse LIKE :s3
                    OR visit_type LIKE :s4 OR diagnosis LIKE :s5';
            $like = '%' . $search . '%';
            $params = [':s1' => $like, ':s2' => $like, ':s3' => $like, ':s4' => $like, ':s5' => $like];
        }

        $cnt = $db->prepare("SELECT COUNT(*) FROM clinic_consultations $where");
        $cnt->execute($params);
        $total      = (int) $cnt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $limit;

        $stmt = $db->prepare('SELECT ' . CONSULT_COLUMNS . " FROM clinic_consultations $where
        ORDER BY submitted_at DESC, id DESC LIMIT :lim OFFSET :off");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        jsonOut([
            'success'     => true,
            'data'        => array_map('mapConsultation', $stmt->fetchAll(PDO::FETCH_ASSOC)),
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'limit'       => $limit,
        ]);
    } catch (Throwable $e) {
        error_log('get_consultations: ' . $e->getMessage());
        jsonOut(['success' => false, 'message' => 'Could not load consultations.'], 500);
    }
