<?php
/**
 * SessionController
 * Path: controllers/SessionController.php
 *
 * Bridges HTTP layer to the Session model.
 * All public methods return ['success' => bool, 'message' => string, ?'data' => mixed].
 */

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../guidance/classes/Session.php';

class SessionController {

    private Session $session;

    public function __construct() {
        $db            = new Database();
        $this->session = new Session($db->getConnection());
    }

    // ── CRUD ────────────────────────────────────────────────

    public function create(array $data): array {
        if (empty($data['student_id']) || empty($data['counselor_id']) || empty($data['session_date'])) {
            return ['success' => false, 'message' => 'Student, counselor, and date are required.'];
        }

        $this->mapData($data);

        return $this->session->create()
            ? ['success' => true,  'message' => 'Session scheduled successfully.']
            : ['success' => false, 'message' => 'Failed to schedule session.'];
    }

    public function getAll(array $filters = []): array {
        $stmt     = $this->session->read();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // PHP-side filtering (status / type / search)
        if (!empty($filters['status'])) {
            $f = $filters['status'];
            $sessions = array_filter($sessions, fn($s) => strcasecmp($s['status'] ?? '', $f) === 0);
        }
        if (!empty($filters['type'])) {
            $f = $filters['type'];
            $sessions = array_filter($sessions, fn($s) => strcasecmp($s['session_type'] ?? '', $f) === 0);
        }
        if (!empty($filters['search'])) {
            $q = strtolower($filters['search']);
            $sessions = array_filter($sessions, function ($s) use ($q) {
                $student   = strtolower(($s['student_first']  ?? '') . ' ' . ($s['student_last']  ?? ''));
                $counselor = strtolower(($s['counselor_first'] ?? '') . ' ' . ($s['counselor_last'] ?? ''));
                return str_contains($student, $q)
                    || str_contains($counselor, $q)
                    || str_contains((string)($s['id'] ?? ''), $q);
            });
        }

        $result = array_map(fn($s) => [
            'id'        => $s['id']            ?? null,
            'student_id'   => $s['student_id'] ?? null,
            'counselor_id' => $s['counselor_id'] ?? null,
            'student'   => trim(($s['student_first']   ?? '') . ' ' . ($s['student_last']   ?? '')),
            'counselor' => trim(($s['counselor_first']  ?? '') . ' ' . ($s['counselor_last']  ?? '')),
            'date'      => $s['session_date']   ?? '',
            'type'      => $s['session_type']   ?? '',
            'status'    => $s['status']         ?? '',
            'notes'     => $s['notes']          ?? '',
        ], $sessions);

        return ['success' => true, 'data' => array_values($result)];
    }

    public function getOne(int $id): array {
        $this->session->id = $id;
        $row = $this->session->readOne();
        return $row
            ? ['success' => true,  'data' => $row]
            : ['success' => false, 'message' => 'Session not found.'];
    }

    public function update(int $id, array $data): array {
        if (!$id) {
            return ['success' => false, 'message' => 'Session ID required.'];
        }
        $this->session->id = $id;
        $this->mapData($data);

        return $this->session->update()
            ? ['success' => true,  'message' => 'Session updated successfully.']
            : ['success' => false, 'message' => 'Failed to update session.'];
    }

    public function delete(int $id): array {
        if (!$id) {
            return ['success' => false, 'message' => 'Session ID required.'];
        }
        $this->session->id = $id;
        return $this->session->delete()
            ? ['success' => true,  'message' => 'Session deleted.']
            : ['success' => false, 'message' => 'Failed to delete session.'];
    }

    // ── Private helper ──────────────────────────────────────

    private function mapData(array $data): void {
        $date = htmlspecialchars(strip_tags($data['session_date'] ?? ''));
        $time = htmlspecialchars(strip_tags($data['session_time'] ?? '00:00:00'));

        $this->session->student_id   = (int)($data['student_id']   ?? 0);
        $this->session->counselor_id = (int)($data['counselor_id'] ?? 0);
        $this->session->session_date = trim($date . ' ' . $time);
        $this->session->session_type = htmlspecialchars(strip_tags($data['session_type'] ?? 'Academic'));
        $this->session->notes        = htmlspecialchars(strip_tags($data['notes']        ?? ''));
        $this->session->status       = htmlspecialchars(strip_tags($data['status']       ?? 'Pending'));
    }
}