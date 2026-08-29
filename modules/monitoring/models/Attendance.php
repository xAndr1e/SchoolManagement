<?php
require_once 'Model.php';

class Attendance extends Model {
    protected $table = 'mon_attendance_records';
    protected $requiredColumns = ['faculty_name', 'course_section', 'subject_code', 'room', 'attendance_date', 'check_time', 'status', 'verification_method'];
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'schedule_id', 'faculty_name', 'course_section', 'subject_code', 
            'room', 'student_count', 'attendance_date', 'check_time', 'status', 'is_online', 
            'meeting_link', 'meeting_screenshot', 'face_to_face_image', 'verified_by', 
            'verification_method', 'remarks'
        ];
    }

    private function isRelationalSchedule(): bool {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM cc_schedule LIKE 'faculty_id'");
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getByDate($date) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_records WHERE attendance_date = ? ORDER BY check_time DESC");
            $stmt->execute([$date]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting attendance by date: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByDay($day) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_records WHERE DAYNAME(attendance_date) = ? ORDER BY check_time DESC");
            $stmt->execute([$day]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting attendance by day: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByFaculty($facultyName) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_records WHERE faculty_name LIKE ? ORDER BY attendance_date DESC");
            $stmt->execute(["%{$facultyName}%"]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting attendance by faculty: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_records WHERE attendance_date BETWEEN ? AND ? ORDER BY attendance_date DESC");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting attendance by date range: " . $e->getMessage());
            return [];
        }
    }
    
    public function hasAttendance($scheduleId, $date) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_attendance_records WHERE schedule_id = ? AND attendance_date = ?");
            $stmt->execute([$scheduleId, $date]);
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking attendance: " . $e->getMessage());
            return false;
        }
    }
    
    // ============================================
    // FIXED: getOnlineClasses - Returns Array
    // ============================================
    public function getOnlineClasses($date = null) {
        try {
            $isRelational = $this->isRelationalSchedule();

            if ($isRelational) {
                $sql = "
                    SELECT ar.*, 
                           COALESCE(CONCAT(f.first_name, ' ', f.last_name), ar.faculty_name) AS faculty_name,
                           COALESCE(sec.section_code, ar.course_section) AS course_section,
                           COALESCE(sub.code, ar.subject_code) AS subject_code,
                           COALESCE(r.room_name, ar.room) AS room
                    FROM mon_attendance_records ar
                    LEFT JOIN cc_schedule s ON ar.schedule_id = s.id
                    LEFT JOIN cc_faculty f ON s.faculty_id = f.id
                    LEFT JOIN cc_sections sec ON s.section_id = sec.id
                    LEFT JOIN rgr_subjects sub ON s.subject_id = sub.id
                    LEFT JOIN cc_room r ON s.room_id = r.id
                    WHERE ar.is_online = 1";

                if ($date) {
                    $sql .= " AND ar.attendance_date = ?";
                }
                $sql .= " ORDER BY ar.attendance_date DESC, ar.check_time DESC";

                $stmt = $this->db->prepare($sql);
                if ($date) {
                    $stmt->execute([$date]);
                } else {
                    $stmt->execute();
                }
                return $stmt->fetchAll();
            }

            $sql = "SELECT * FROM mon_attendance_records WHERE is_online = 1";
            if ($date) {
                $sql .= " AND attendance_date = ?";
            }
            $sql .= " ORDER BY attendance_date DESC, check_time DESC";

            $stmt = $this->db->prepare($sql);
            if ($date) {
                $stmt->execute([$date]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error getting online classes: " . $e->getMessage());
            return [];
        }
    }

    public function archive($id, $archivedBy) {
        try {
            $record = $this->getById($id);
            if (!$record) {
                return ['success' => false, 'errors' => ['Record not found']];
            }
            
            $archive = new AttendanceArchive();
            $archiveData = [
                'original_id' => $record['id'],
                'schedule_id' => $record['schedule_id'],
                'faculty_name' => $record['faculty_name'],
                'course_section' => $record['course_section'],
                'subject_code' => $record['subject_code'],
                'room' => $record['room'],
                'student_count' => $record['student_count'] ?? 0,
                'attendance_date' => $record['attendance_date'],
                'check_time' => $record['check_time'],
                'status' => $record['status'],
                'is_online' => $record['is_online'],
                'meeting_link' => $record['meeting_link'],
                'meeting_screenshot' => $record['meeting_screenshot'],
                'face_to_face_image' => $record['face_to_face_image'] ?? null,
                'verified_by' => $record['verified_by'],
                'verification_method' => $record['verification_method'],
                'remarks' => $record['remarks'],
                'archived_by' => $archivedBy
            ];
            
            $archiveResult = $archive->create($archiveData);
            if (!$archiveResult['success']) {
                return $archiveResult;
            }
            
            return $this->delete($id);
            
        } catch (Exception $e) {
            error_log("Error archiving record: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Archive failed: ' . $e->getMessage()]];
        }
    }
    
    public function archiveAll($archivedBy, $startDate = null, $endDate = null) {
        try {
            $sql = "SELECT id FROM mon_attendance_records";
            $params = [];
            
            if ($startDate && $endDate) {
                $sql .= " WHERE attendance_date BETWEEN ? AND ?";
                $params = [$startDate, $endDate];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            $archivedCount = 0;
            $errors = [];
            
            foreach ($records as $record) {
                $result = $this->archive($record['id'], $archivedBy);
                if ($result['success']) {
                    $archivedCount++;
                } else {
                    $errors[] = "Failed to archive record ID {$record['id']}: " . implode(', ', $result['errors']);
                }
            }
            
            return [
                'success' => $archivedCount > 0,
                'archived_count' => $archivedCount,
                'total' => count($records),
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            error_log("Error archiving all records: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Archive failed: ' . $e->getMessage()]];
        }
    }

    public function autoArchivePreviousDays() {
        $today = date('Y-m-d');
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_attendance_records WHERE attendance_date < ?");
            $stmt->execute([$today]);
            $records = $stmt->fetchAll();
            
            foreach ($records as $row) {
                $archiveStmt = $this->db->prepare("
                    INSERT INTO mon_attendance_archive 
                    (original_id, schedule_id, faculty_name, course_section, subject_code, room, student_count, attendance_date, check_time, status, is_online, meeting_link, meeting_screenshot, verified_by, verification_method, remarks, archived_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Auto-System')
                ");
                $archiveStmt->execute([
                    $row['id'], $row['schedule_id'], $row['faculty_name'], $row['course_section'], 
                    $row['subject_code'], $row['room'], $row['student_count'], $row['attendance_date'], 
                    $row['check_time'], $row['status'], $row['is_online'], $row['meeting_link'], 
                    $row['meeting_screenshot'], $row['verified_by'], $row['verification_method'], $row['remarks']
                ]);
            }
            
            $del = $this->db->prepare("DELETE FROM mon_attendance_records WHERE attendance_date < ?");
            $del->execute([$today]);
            return true;
        } catch (PDOException $e) {
            error_log("Auto-archive attendance error: " . $e->getMessage());
            return false;
        }
    }
}
?>