<?php
// POST (JSON) {id, csrf_token} -> deletes one consultation
require_once __DIR__ . '/_bootstrap.php';

try {
    $b = readJsonBody();
    requirePostWithCsrf($b);

    $id = (int) ($b['id'] ?? 0);
    if ($id <= 0) {
        jsonOut(['success' => false, 'message' => 'Invalid consultation ID.'], 422);
    }
    $stmt = getDb()->prepare('DELETE FROM clinic_consultations WHERE consultation_id = ?');
    $stmt->execute([$id]);

    jsonOut(['success' => true, 'message' => 'Consultation deleted.']);
} catch (Throwable $e) {
    error_log('delete_consultation: ' . $e->getMessage());
    jsonOut(['success' => false, 'message' => 'Could not delete consultation.'], 500);
}
