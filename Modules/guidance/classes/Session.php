<?php
include_once __DIR__ . '/../../../database/db.php';

Class Session {
    private $conn;
    private $id;
    private $student_id;
    private $counselor_id;
    private $session_date;
    private $session_type;
    private $notes;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Get all sessions (with student & counselor info)
    public function getSessions() {
        $sql = "SELECT 
                    gs.id,
                    gsp.first_name AS student_first_name,
                    gsp.last_name AS student_last_name,
                    gc.first_name AS counselor_first_name,
                    gc.last_name AS counselor_last_name,
                    gs.session_date,
                    gs.session_type,
                    gs.status
                FROM gd_sessions gs
                LEFT JOIN gd_students_profile gsp 
                    ON gs.student_id = gsp.id
                LEFT JOIN gd_counselors gc 
                    ON gs.counselor_id = gc.id
                ORDER BY gs.session_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single session by ID
    public function getSessionById($id) {
        $sql = "SELECT * FROM gd_sessions WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add new session
    public function addSession($data) {
        $sql = "INSERT INTO gd_sessions (
                    student_id,
                    counselor_id,
                    session_date,
                    session_type,
                    notes,
                    status
                ) VALUES (
                    :student_id,
                    :counselor_id,
                    :session_date,
                    :session_type,
                    :notes,
                    :status
                )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // Update session
    public function updateSession($id, $data) {
        $sql = "UPDATE gd_sessions SET
                    student_id = :student_id,
                    counselor_id = :counselor_id,
                    session_date = :session_date,
                    session_type = :session_type,
                    notes = :notes,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Delete session
    public function deleteSession($id) {
        $sql = "DELETE FROM gd_sessions WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get sessions by student
    public function getSessionsByStudent($student_id) {
        $sql = "SELECT * 
                FROM gd_sessions 
                WHERE student_id = :student_id 
                ORDER BY session_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get sessions by counselor
    public function getSessionsByCounselor($counselor_id) {
        $sql = "SELECT * 
                FROM gd_sessions 
                WHERE counselor_id = :counselor_id 
                ORDER BY session_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':counselor_id', $counselor_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get logged-in student sessions (if using session)
    public function getMySessions() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $studentId = $_SESSION['student_id'] ?? null;

        if ($studentId) {
            return $this->getSessionsByStudent($studentId);
        }
        return [];
    }
}