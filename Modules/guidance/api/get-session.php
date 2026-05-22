<?php
@ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../database/db.php';

class SessionAPI {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // ── Entry point ─────────────────────────────────────────────
    public function handleRequest(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
                default:
                    $this->respond(false, 'Method not allowed.');
            }
        } catch (Throwable $e) {
            http_response_code(500);
            $this->respond(false, $e->getMessage());
        }
    }

    // ── Helpers ────────────────────────────────────────────────
    private function respond(bool $success, string $message, $data = null): void {
        $payload = ['success' => $success, 'message' => $message];
        if ($data !== null) $payload['data'] = $data;
        echo json_encode($payload);
        exit;
    }

    private function jsonBody(): array {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    private function clean(?string $val): string {
        return htmlspecialchars(strip_tags(trim($val ?? '')));
    }

    // ── GET ────────────────────────────────────────────────────
    private function handleGet(): void {
        if (!empty($_GET['id'])) {
            $this->getSingle((int) $_GET['id']);
            return;
        }

        $status = $this->clean($_GET['status'] ?? '');
        $type   = $this->clean($_GET['type']   ?? '');
        $search = $this->clean($_GET['search'] ?? '');

        $where  = [];
        $params = [];

        if ($status !== '') {
            $where[] = 's.status = ?';
            $params[] = $status;
        }

        if ($type !== '') {
            $where[] = 's.session_type = ?';
            $params[] = $type;
        }

        if ($search !== '') {
            $like = "%{$search}%";
            $where[] = "(st.first_name LIKE ? OR st.last_name LIKE ?
                        OR c.first_name LIKE ? OR c.last_name LIKE ?
                        OR CAST(s.id AS CHAR) LIKE ?)";
            array_push($params, $like, $like, $like, $like, $like);
        }

        $sql = $this->baseQuery();
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY s.session_date DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $this->respond(true, 'OK', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getSingle(int $id): void {
        $sql = $this->baseQuery() . " WHERE s.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) $this->respond(false, 'Session not found.');

        $this->respond(true, 'OK', $row);
    }

    private function baseQuery(): string {
        return "
            SELECT
                s.id,
                s.student_id,
                s.counselor_id,
                CONCAT(st.first_name, ' ', st.last_name) AS student,
                CONCAT(c.first_name, ' ', c.last_name) AS counselor,
                s.session_date AS date,
                s.session_type AS type,
                s.notes,
                s.status
            FROM gd_counseling_sessions s
            LEFT JOIN rgr_students st ON st.student_number = s.student_id
            LEFT JOIN gd_counselors c ON c.id = s.counselor_id
        ";
    }

    // ── POST ───────────────────────────────────────────────────
    private function handlePost(): void {
        $body = $this->jsonBody();

        $student_id   = $this->clean($body['student_id'] ?? '');
        $counselor_id = (int) ($body['counselor_id'] ?? 0);
        $date         = $this->clean($body['session_date'] ?? '');
        $time         = $this->clean($body['session_time'] ?? '');
        $type         = $this->clean($body['session_type'] ?? 'Academic');
        $notes        = $this->clean($body['notes'] ?? '');
        $status       = $this->clean($body['status'] ?? 'Pending');

        if (!$student_id || !$counselor_id || !$date) {
            $this->respond(false, 'Student, counselor, and date are required.');
        }

        $datetime = $this->combineDateTime($date, $time);

        $stmt = $this->conn->prepare("
            INSERT INTO gd_counseling_sessions
            (student_id, counselor_id, session_date, session_type, notes, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $ok = $stmt->execute([$student_id, $counselor_id, $datetime, $type, $notes, $status]);

        $ok
            ? $this->respond(true, 'Session scheduled successfully.')
            : $this->respond(false, 'Failed to schedule session.');
    }

    // ── PUT ────────────────────────────────────────────────────
    private function handlePut(): void {
        $body = $this->jsonBody();

        $id           = (int) ($body['id'] ?? 0);
        $student_id   = $this->clean($body['student_id'] ?? '');
        $counselor_id = (int) ($body['counselor_id'] ?? 0);
        $date         = $this->clean($body['session_date'] ?? '');
        $time         = $this->clean($body['session_time'] ?? '');
        $type         = $this->clean($body['session_type'] ?? 'Academic');
        $notes        = $this->clean($body['notes'] ?? '');
        $status       = $this->clean($body['status'] ?? 'Pending');

        if (!$id || !$student_id || !$counselor_id || !$date) {
            $this->respond(false, 'ID, student, counselor, and date are required.');
        }

        $datetime = $this->combineDateTime($date, $time);

        $stmt = $this->conn->prepare("
            UPDATE gd_counseling_sessions
            SET student_id = ?, counselor_id = ?, session_date = ?, session_type = ?, notes = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $ok = $stmt->execute([$student_id, $counselor_id, $datetime, $type, $notes, $status, $id]);

        $ok
            ? $this->respond(true, 'Session updated successfully.')
            : $this->respond(false, 'Failed to update session.');
    }

    // ── DELETE ─────────────────────────────────────────────────
    private function handleDelete(): void {
        $body = $this->jsonBody();
        $id   = (int) ($body['id'] ?? 0);

        if (!$id) {
            $this->respond(false, 'Session ID is required.');
        }

        $stmt = $this->conn->prepare("DELETE FROM gd_counseling_sessions WHERE id = ?");
        $ok   = $stmt->execute([$id]);

        $ok
            ? $this->respond(true, 'Session deleted successfully.')
            : $this->respond(false, 'Failed to delete session.');
    }

    // ── Utility ────────────────────────────────────────────────
    private function combineDateTime(string $date, string $time): string {
        return $time
            ? "{$date} {$time}:00"
            : "{$date} 00:00:00";
    }
}

// ── Run API ───────────────────────────────────────────────────
$api = new SessionAPI();
$api->handleRequest();