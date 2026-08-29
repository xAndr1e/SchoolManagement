<?php
require_once '../models/Schedule.php';
require_once '../models/Attendance.php';
require_once '../models/AttendanceArchive.php';

class AttendanceController {
    private $scheduleModel;
    private $attendanceModel;
    private $archiveModel;
    
    public function __construct() {
        $this->scheduleModel = new Schedule();
        $this->attendanceModel = new Attendance();
        $this->archiveModel = new AttendanceArchive();
    }
    
    // ============================================
    // getOnlineClasses - Returns JSON
    // ============================================
    public function getOnlineClasses($date = null) {
        try {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if ($date === null && isset($_GET['date'])) {
                $date = $_GET['date'];
            }

            $records = $this->attendanceModel->getOnlineClasses($date);

            if (empty($records)) {
                header('Content-Type: application/json');
                echo json_encode([]);
                return;
            }

            $formattedRecords = [];
            foreach ($records as $record) {
                $formattedRecords[] = [
                    'id' => $record['id'],
                    'schedule_id' => $record['schedule_id'] ?? null,
                    'faculty_name' => $record['faculty_name'] ?? 'Unknown',
                    'course_section' => $record['course_section'] ?? 'N/A',
                    'subject_code' => $record['subject_code'] ?? 'N/A',
                    'room' => $record['room'] ?? 'N/A',
                    'student_count' => isset($record['student_count']) ? (int)$record['student_count'] : 0,
                    'attendance_date' => $record['attendance_date'] ?? date('Y-m-d'),
                    'check_time' => $record['check_time'] ?? date('Y-m-d H:i:s'),
                    'created_at' => $record['check_time'] ?? date('Y-m-d H:i:s'),
                    'status' => $record['status'] ?? 'online',
                    'is_online' => isset($record['is_online']) ? (int)$record['is_online'] : 1,
                    'meeting_link' => $record['meeting_link'] ?? null,
                    'meeting_screenshot' => $record['meeting_screenshot'] ?? null,
                    'face_to_face_image' => $record['face_to_face_image'] ?? null,
                    'verified_by' => $record['verified_by'] ?? 'System',
                    'verification_method' => $record['verification_method'] ?? 'online',
                    'remarks' => $record['remarks'] ?? null
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($formattedRecords);

        } catch (Exception $e) {
            error_log("Error in getOnlineClasses: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }
    
    // ============================================
    // getSchedules - Returns JSON
    // ============================================
    public function getSchedules($date = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (!$date && isset($_GET['date'])) {
                $date = $_GET['date'];
            }
            if (!$date) {
                $date = date('Y-m-d');
            }

            $schedules = $this->scheduleModel->getByDate($date);

            $result = [];
            foreach ($schedules as $schedule) {
                $subjectCode = $schedule['subject_code'] ?? ($schedule['subject_name'] ?? 'N/A');
                $sectionName = $schedule['section_name'] ?? ($schedule['course_section'] ?? 'N/A');
                $roomName = $schedule['room_name'] ?? ($schedule['room'] ?? 'N/A');
                $dayOfWeek = $schedule['day_of_week'] ?? ($schedule['day'] ?? '');

                $result[] = [
                    'id' => (int) ($schedule['id'] ?? 0),
                    'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                    'subject_name' => $schedule['subject_name'] ?? 'N/A',
                    'subject_code' => $subjectCode,
                    'course_section' => $sectionName,
                    'section_name' => $sectionName,
                    'room' => $roomName,
                    'room_name' => $roomName,
                    'start_time' => $schedule['start_time'] ?? null,
                    'end_time' => $schedule['end_time'] ?? null,
                    'day' => $dayOfWeek,
                    'day_of_week' => $dayOfWeek,
                    'status' => $schedule['status'] ?? 'Scheduled',
                    'schedule_date' => $schedule['schedule_date'] ?? $date,
                    'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                    'student_count' => 0
                ];
            }

            echo json_encode($result);

        } catch (Exception $e) {
            error_log("Error in getSchedules: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // getSchedulesByDay - Returns JSON
    // ============================================
    public function getSchedulesByDay($day = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (!$day && isset($_GET['day'])) {
                $day = $_GET['day'];
            }
            if (!$day) {
                $day = date('l');
            }

            $allSchedules = $this->scheduleModel->getByDay($day);

            usort($allSchedules, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });

            $result = [];
            foreach ($allSchedules as $schedule) {
                $subjectCode = $schedule['subject_code'] ?? ($schedule['subject_name'] ?? 'N/A');
                $sectionName = $schedule['section_name'] ?? ($schedule['course_section'] ?? 'N/A');
                $roomName = $schedule['room_name'] ?? ($schedule['room'] ?? 'N/A');
                $dayOfWeek = $schedule['day_of_week'] ?? ($schedule['day'] ?? $day);

                $result[] = [
                    'id' => (int) ($schedule['id'] ?? 0),
                    'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                    'subject_name' => $schedule['subject_name'] ?? 'N/A',
                    'subject_code' => $subjectCode,
                    'course_section' => $sectionName,
                    'section_name' => $sectionName,
                    'room' => $roomName,
                    'room_name' => $roomName,
                    'start_time' => $schedule['start_time'] ?? null,
                    'end_time' => $schedule['end_time'] ?? null,
                    'day' => $dayOfWeek,
                    'day_of_week' => $dayOfWeek,
                    'status' => $schedule['status'] ?? 'Scheduled',
                    'schedule_date' => $schedule['schedule_date'] ?? date('Y-m-d'),
                    'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                    'student_count' => 0
                ];
            }

            echo json_encode($result);

        } catch (Exception $e) {
            error_log("Error in getSchedulesByDay: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // getRecords - Returns JSON
    // ============================================
    public function getRecords($date = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (!$date && isset($_GET['date'])) {
                $date = $_GET['date'];
            }
            if (!$date) {
                $date = date('Y-m-d');
            }

            $records = $this->attendanceModel->getByDate($date);
            echo json_encode($records);

        } catch (Exception $e) {
            error_log("Error in getRecords: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // getAttendanceRecordsByDay - Returns JSON
    // ============================================
    public function getAttendanceRecordsByDay($day = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (!$day && isset($_GET['day'])) {
                $day = $_GET['day'];
            }
            if (!$day) {
                $day = date('l');
            }
            
            $today = date('Y-m-d');
            $allRecords = $this->attendanceModel->getByDay($day);
            
            $filtered = array_filter($allRecords, function($record) use ($today) {
                return isset($record['attendance_date']) && $record['attendance_date'] === $today;
            });
            
            echo json_encode(array_values($filtered));
            
        } catch (Exception $e) {
            error_log("Error in getAttendanceRecordsByDay: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // markAttendance - Returns JSON
    // ============================================
    public function markAttendance($data, $files = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if ($files === null && isset($_FILES) && !empty($_FILES)) {
                $files = $_FILES;
            }
            
            if (isset($data['schedule_id']) && !empty($data['schedule_id'])) {
                $schedule = $this->scheduleModel->getById($data['schedule_id']);
                if ($schedule) {
                    if (empty($data['faculty_name'])) $data['faculty_name'] = $schedule['faculty_name'] ?? '';
                    if (empty($data['course_section'])) $data['course_section'] = $schedule['section_name'] ?? '';
                    if (empty($data['subject_code'])) $data['subject_code'] = $schedule['subject_name'] ?? '';
                    if (empty($data['room'])) $data['room'] = $schedule['room_name'] ?? '';
                }
            }
            
            $data['status'] = strtolower(trim($data['status'] ?? 'present'));
            
            $required = ['faculty_name', 'course_section', 'subject_code', 'room', 'status'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }
            
            $allowedStatuses = ['present', 'absent', 'late', 'excused', 'online', 'nt', 'eb', 'ed', 'ob', 'at', 'pending'];
            if (!in_array($data['status'], $allowedStatuses)) {
                echo json_encode(['success' => false, 'error' => 'Invalid status: ' . $data['status']]);
                return;
            }
            
            $data['student_count'] = isset($data['student_count']) ? (int)$data['student_count'] : 0;
            $data['check_time'] = date('Y-m-d H:i:s');
            $data['attendance_date'] = !empty($data['attendance_date']) ? $data['attendance_date'] : date('Y-m-d');
            $data['is_online'] = ($data['status'] === 'online') ? 1 : 0;
            $data['verified_by'] = !empty($data['verified_by']) ? $data['verified_by'] : 'Administrator';
            $data['verification_method'] = !empty($data['verification_method']) ? $data['verification_method'] : 'physical_check';
            
            if ($files && isset($files['face_to_face_image']) && $files['face_to_face_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/uploads/face_to_face/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $files['face_to_face_image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'face_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $data['face_to_face_image'] = 'uploads/face_to_face/' . $filename;
                }
            }
            
            if ($data['is_online'] && $files && isset($files['meeting_screenshot']) && $files['meeting_screenshot']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/uploads/meeting_screenshots/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $files['meeting_screenshot'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'screenshot_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $data['meeting_screenshot'] = 'uploads/meeting_screenshots/' . $filename;
                }
            }
            
            $cleanData = [
                'schedule_id' => !empty($data['schedule_id']) ? $data['schedule_id'] : null,
                'faculty_name' => $data['faculty_name'],
                'course_section' => $data['course_section'],
                'subject_code' => $data['subject_code'],
                'room' => $data['room'],
                'student_count' => $data['student_count'],
                'attendance_date' => $data['attendance_date'],
                'check_time' => $data['check_time'],
                'status' => $data['status'],
                'is_online' => $data['is_online'],
                'meeting_link' => $data['meeting_link'] ?? null,
                'meeting_screenshot' => $data['meeting_screenshot'] ?? null,
                'face_to_face_image' => $data['face_to_face_image'] ?? null,
                'verified_by' => $data['verified_by'],
                'verification_method' => $data['verification_method'],
                'remarks' => $data['remarks'] ?? null
            ];
            
            $existingRecord = null;
            if (!empty($cleanData['schedule_id'])) {
                $recordsToday = $this->attendanceModel->getByDate($cleanData['attendance_date']);
                foreach ($recordsToday as $rec) {
                    if ($rec['schedule_id'] == $cleanData['schedule_id']) {
                        $existingRecord = $rec;
                        break;
                    }
                }
            }
            
            if ($existingRecord) {
                if (empty($cleanData['face_to_face_image'])) {
                    $cleanData['face_to_face_image'] = $existingRecord['face_to_face_image'];
                }
                if (empty($cleanData['meeting_screenshot'])) {
                    $cleanData['meeting_screenshot'] = $existingRecord['meeting_screenshot'];
                }
                
                $result = $this->attendanceModel->update($existingRecord['id'], $cleanData);
                echo json_encode($result);
                return;
            }
            
            $result = $this->attendanceModel->create($cleanData);
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("❌ Error in markAttendance: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to mark attendance: ' . $e->getMessage()]);
        }
    }
    
    // ============================================
    // deleteRecord - Returns JSON
    // ============================================
    public function deleteRecord($id) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'Record ID is required']);
                return;
            }
            
            $result = $this->attendanceModel->delete($id);
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("Error in deleteRecord: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to delete record: ' . $e->getMessage()]);
        }
    }
    
    // ============================================
    // archiveRecords - Returns JSON
    // ============================================
    public function archiveRecords($data) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            $archivedBy = $data['archived_by'] ?? ($_SESSION['fullname'] ?? 'System');
            
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                $lastError = 'No valid records found or filtered out by model.';
                
                foreach ($data['ids'] as $id) {
                    $rec = $this->attendanceModel->getById($id);
                    if ($rec) {
                        $archiveData = $rec;
                        $archiveData['original_id'] = $rec['id'];
                        unset($archiveData['id']);
                        $archiveData['archived_by'] = $archivedBy;
                        
                        $res = $this->archiveModel->create($archiveData);
                        if ($res && ($res['success'] ?? false)) {
                            $this->attendanceModel->delete($id);
                            $count++;
                        } else {
                            $lastError = $res['error'] ?? 'Database insert failed.';
                        }
                    }
                }
                
                if ($count > 0) {
                    echo json_encode(['success' => true, 'archived_count' => $count]);
                    return;
                }
                echo json_encode(['success' => false, 'error' => 'Archive Failed: ' . $lastError]);
                return;
            }
            
            $date = $data['date'] ?? null;
            if ($date) {
                $result = $this->attendanceModel->archiveAll($archivedBy, $date, $date);
                echo json_encode($result);
                return;
            }
            
            $result = $this->attendanceModel->archiveAll($archivedBy);
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("Error in archiveRecords: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
    }
    
    // ============================================
    // restoreRecords - Returns JSON
    // ============================================
    public function restoreRecords($data) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                $lastError = 'Record not found in archive.';
                
                foreach ($data['ids'] as $id) {
                    $archivedRec = $this->archiveModel->getById($id);
                    if ($archivedRec) {
                        $restoreData = $archivedRec;
                        unset($restoreData['id']);
                        unset($restoreData['original_id']);
                        unset($restoreData['archived_by']);
                        unset($restoreData['archived_at']);
                        
                        $res = $this->attendanceModel->create($restoreData);
                        if ($res && ($res['success'] ?? false)) {
                            $this->archiveModel->delete($id);
                            $count++;
                        } else {
                            $lastError = $res['error'] ?? 'Database restore failed.';
                        }
                    }
                }
                
                if ($count > 0) {
                    echo json_encode(['success' => true, 'restored_count' => $count]);
                    return;
                }
                echo json_encode(['success' => false, 'error' => 'Restore Failed: ' . $lastError]);
                return;
            }
            
            echo json_encode(['success' => false, 'error' => 'No record IDs provided']);
            
        } catch (Exception $e) {
            error_log("Error in restoreRecords: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
    }
    
    // ============================================
    // getArchivedRecords - Returns JSON
    // ============================================
    public function getArchivedRecords($faculty = null) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            
            if ($faculty) {
                $records = $this->archiveModel->getByFaculty($faculty);
            } else {
                $records = $this->archiveModel->getAll();
            }
            
            echo json_encode($records);
            
        } catch (Exception $e) {
            error_log("Error in getArchivedRecords: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // getFacultySchedules - Returns JSON
    // ============================================
    public function getFacultySchedules($faculty) {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');

            $today = date('Y-m-d');
            $day = date('l');
            $schedules = $this->scheduleModel->getByDay($day);

            $filtered = array_filter($schedules, function($schedule) use ($faculty, $today) {
                $facultyName = $schedule['faculty_name'] ?? '';
                $matchesFaculty = stripos($facultyName, $faculty) !== false;
                $matchesDate = !isset($schedule['schedule_date']) || $schedule['schedule_date'] >= $today;
                return $matchesFaculty && $matchesDate;
            });

            $result = [];
            foreach ($filtered as $schedule) {
                $subjectCode = $schedule['subject_code'] ?? ($schedule['subject_name'] ?? 'N/A');
                $sectionName = $schedule['section_name'] ?? ($schedule['course_section'] ?? 'N/A');
                $roomName = $schedule['room_name'] ?? ($schedule['room'] ?? 'N/A');
                $dayOfWeek = $schedule['day_of_week'] ?? ($schedule['day'] ?? $day);

                $result[] = [
                    'id' => (int) ($schedule['id'] ?? 0),
                    'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                    'subject_name' => $schedule['subject_name'] ?? 'N/A',
                    'subject_code' => $subjectCode,
                    'course_section' => $sectionName,
                    'section_name' => $sectionName,
                    'room' => $roomName,
                    'room_name' => $roomName,
                    'start_time' => $schedule['start_time'] ?? null,
                    'end_time' => $schedule['end_time'] ?? null,
                    'day' => $dayOfWeek,
                    'day_of_week' => $dayOfWeek,
                    'status' => $schedule['status'] ?? 'Scheduled',
                    'schedule_date' => $schedule['schedule_date'] ?? $today,
                    'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                    'student_count' => 0
                ];
            }

            echo json_encode($result);

        } catch (Exception $e) {
            error_log("Error in getFacultySchedules: " . $e->getMessage());
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }
    
    // ============================================
    // getAttendanceRecords - Returns JSON
    // ============================================
    public function getAttendanceRecords($date) {
        return $this->getRecords($date);
    }
}
?>