<?php
require_once 'Model.php';

class AttendanceArchive extends Model {
    protected $table = 'mon_attendance_archive';
    
    public function __construct() {
        parent::__construct();
        // Exact column alignment with mon_attendance_archive table
        $this->allowedColumns = [
            'original_id', 'schedule_id', 'faculty_name', 'course_section', 
            'subject_code', 'room', 'student_count', 'attendance_date', 
            'check_time', 'status', 'is_online', 'meeting_link', 
            'meeting_screenshot', 'face_to_face_image', 'verified_by', 
            'verification_method', 'remarks', 'archived_by'
        ];
    }
    
    public function getArchive($facultyName = null) {
        try {
            if ($facultyName) {
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE faculty_name LIKE ? ORDER BY archived_at DESC");
                $stmt->execute(["%{$facultyName}%"]);
                return $stmt->fetchAll();
            }
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY archived_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting archive by faculty: " . $e->getMessage());
            return [];
        }
    }

    public function getByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_archive WHERE attendance_date BETWEEN ? AND ? ORDER BY archived_at DESC");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting archive by date range: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByFaculty($facultyName) {
        return $this->getArchive($facultyName);
    }
    
    public function getAll($orderBy = 'archived_at DESC') {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all archive records: " . $e->getMessage());
            return [];
        }
    }
}
?>