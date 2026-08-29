<?php
require_once 'Model.php';

class Schedule extends Model {
    protected $table = 'cc_schedule';
    private bool $isRelational = true;

    public function __construct() {
        parent::__construct();
        $this->detectSchemaType();
    }

    /**
     * Auto-detects if table uses Foreign Keys or Flat Strings
     */
    private function detectSchemaType(): void {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'faculty_id'");
            $this->isRelational = (bool) $stmt->fetch();
        } catch (PDOException $e) {
            $this->isRelational = false;
        }
    }

    /**
     * Returns a uniform SELECT query structure regardless of database schema
     */
    private function getBaseSelectQuery(): string {
        if ($this->isRelational) {
            return "SELECT 
                        s.id,
                        COALESCE(CONCAT(f.first_name, ' ', f.last_name), 'N/A') AS faculty_name,
                        COALESCE(sub.name, 'N/A') AS subject_name,
                        COALESCE(sub.code, 'N/A') AS subject_code,
                        COALESCE(sec.section_code, 'N/A') AS section_name,
                        COALESCE(r.room_name, 'N/A') AS room_name,
                        s.schedule_type,
                        s.start_time,
                        s.end_time,
                        s.status,
                        s.day_of_week
                    FROM {$this->table} s
                    LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                    LEFT JOIN cc_room r ON s.room_id = r.id
                    LEFT JOIN rgr_subjects sub ON s.subject_id = sub.id
                    LEFT JOIN cc_sections sec ON s.section_id = sec.id";
        }

        return "SELECT 
                    s.id,
                    s.faculty_name,
                    s.subject_name,
                    s.subject_name AS subject_code,
                    s.section_name,
                    s.room_name,
                    s.schedule_type,
                    s.start_time,
                    s.end_time,
                    s.status,
                    s.day_of_week,
                    s.schedule_date
                FROM {$this->table} s";
    }

    public function getByDate($date) {
        try {
            $baseQuery = $this->getBaseSelectQuery();
            
            if ($this->isRelational) {
                $dayOfWeek = date('l', strtotime($date));
                $stmt = $this->db->prepare("{$baseQuery} WHERE s.day_of_week = ? ORDER BY s.start_time");
                $stmt->execute([$dayOfWeek]);
            } else {
                $stmt = $this->db->prepare("{$baseQuery} WHERE s.schedule_date = ? ORDER BY s.start_time");
                $stmt->execute([$date]);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getByDate: " . $e->getMessage());
            return [];
        }
    }

    public function getByDay($day) {
        try {
            $baseQuery = $this->getBaseSelectQuery();
            $stmt = $this->db->prepare("{$baseQuery} WHERE s.day_of_week = ? ORDER BY s.start_time");
            $stmt->execute([$day]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getByDay: " . $e->getMessage());
            return [];
        }
    }

    public function getByRoom($room) {
        try {
            $baseQuery = $this->getBaseSelectQuery();
            $roomColumn = $this->isRelational ? "r.room_name" : "s.room_name";
            
            $stmt = $this->db->prepare("{$baseQuery} WHERE {$roomColumn} = ? AND s.status = 'Scheduled' ORDER BY s.start_time");
            $stmt->execute([$room]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getByRoom: " . $e->getMessage());
            return [];
        }
    }

    public function getByFaculty($faculty) {
        try {
            $baseQuery = $this->getBaseSelectQuery();
            $facultyColumn = $this->isRelational ? "CONCAT(f.first_name, ' ', f.last_name)" : "s.faculty_name";
            
            $stmt = $this->db->prepare("{$baseQuery} WHERE {$facultyColumn} LIKE ? ORDER BY s.start_time");
            $stmt->execute(["%{$faculty}%"]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getByFaculty: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $baseQuery = $this->getBaseSelectQuery();
            $stmt = $this->db->prepare("{$baseQuery} WHERE s.id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }
}
?>