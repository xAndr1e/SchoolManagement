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
    
    public function getSchedules($date = null) {
        try {
            if (!$date) {
                $date = date('Y-m-d');
            }
            $schedules = $this->scheduleModel->getByDate($date);
            
            $result = [];
            foreach ($schedules as $schedule) {
                $result[] = [
                    'id' => $schedule['id'],
                    'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                    'subject_name' => $schedule['subject_name'] ?? 'N/A',
                    'subject_code' => $schedule['subject_name'] ?? 'N/A',
                    'course_section' => $schedule['section_name'] ?? 'N/A',
                    'room' => $schedule['room_name'] ?? 'N/A',
                    'room_name' => $schedule['room_name'] ?? 'N/A',
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'day' => $schedule['day_of_week'],
                    'day_of_week' => $schedule['day_of_week'],
                    'status' => $schedule['status'] ?? 'Scheduled',
                    'schedule_date' => $schedule['schedule_date'] ?? $date,
                    'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                    'student_count' => 0
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getSchedules: " . $e->getMessage());
            return [];
        }
    }
    
    public function getSchedulesByDay($day = null) {
    try {
        // Fallback to $_GET or today's day name if $day was not passed by the Router
        if (!$day) {
            $day = $_GET['day'] ?? date('l');
        }
        
        // Fetch schedules matching the requested day of the week
        $allSchedules = $this->scheduleModel->getByDay($day);
        
        // Sort schedules chronologically
        usort($allSchedules, function($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });
        
        $result = [];
        foreach ($allSchedules as $schedule) {
            $result[] = [
                'id' => $schedule['id'],
                'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                'subject_name' => $schedule['subject_name'] ?? 'N/A',
                'subject_code' => $schedule['subject_name'] ?? 'N/A',
                'course_section' => $schedule['section_name'] ?? 'N/A',
                'room' => $schedule['room_name'] ?? 'N/A',
                'room_name' => $schedule['room_name'] ?? 'N/A',
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'day' => $schedule['day_of_week'],
                'day_of_week' => $schedule['day_of_week'],
                'status' => $schedule['status'] ?? 'Scheduled',
                'schedule_date' => $schedule['schedule_date'] ?? date('Y-m-d'),
                'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                'student_count' => 0
            ];
        }
        return $result;
    } catch (Exception $e) {
        error_log("Error in getSchedulesByDay: " . $e->getMessage());
        return [];
    }
}

    public function getFacultySchedules($faculty) {
        try {
            $today = date('Y-m-d');
            $day = date('l');
            $schedules = $this->scheduleModel->getByDay($day);
            
            $filtered = array_filter($schedules, function($schedule) use ($faculty, $today) {
                $matchesFaculty = isset($schedule['faculty_name']) && stripos($schedule['faculty_name'], $faculty) !== false;
                $matchesDate = !isset($schedule['schedule_date']) || $schedule['schedule_date'] >= $today;
                return $matchesFaculty && $matchesDate;
            });
            
            $result = [];
            foreach ($filtered as $schedule) {
                $result[] = [
                    'id' => $schedule['id'],
                    'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                    'subject_name' => $schedule['subject_name'] ?? 'N/A',
                    'subject_code' => $schedule['subject_name'] ?? 'N/A',
                    'course_section' => $schedule['section_name'] ?? 'N/A',
                    'room' => $schedule['room_name'] ?? 'N/A',
                    'room_name' => $schedule['room_name'] ?? 'N/A',
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'day' => $schedule['day_of_week'],
                    'day_of_week' => $schedule['day_of_week'],
                    'status' => $schedule['status'] ?? 'Scheduled',
                    'schedule_date' => $schedule['schedule_date'] ?? $today,
                    'schedule_type' => $schedule['schedule_type'] ?? 'Class',
                    'student_count' => 0
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getFacultySchedules: " . $e->getMessage());
            return [];
        }
    }
    
    // ============================================
    // MARK / UPDATE ATTENDANCE
    // ============================================
    public function markAttendance($data, $files = null) {
        try {
            if ($files === null && isset($_FILES) && !empty($_FILES)) {
                $files = $_FILES;
            }
            
            // Normalize Schedule Details
            if (isset($data['schedule_id']) && !empty($data['schedule_id'])) {
                $schedule = $this->scheduleModel->getById($data['schedule_id']);
                if ($schedule) {
                    if (empty($data['faculty_name'])) $data['faculty_name'] = $schedule['faculty_name'] ?? '';
                    if (empty($data['course_section'])) $data['course_section'] = $schedule['section_name'] ?? '';
                    if (empty($data['subject_code'])) $data['subject_code'] = $schedule['subject_name'] ?? '';
                    if (empty($data['room'])) $data['room'] = $schedule['room_name'] ?? '';
                }
            }
            
            // Normalize status to lowercase
            $data['status'] = strtolower(trim($data['status'] ?? 'present'));
            
            // Validate required fields
            $required = ['faculty_name', 'course_section', 'subject_code', 'room', 'status'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Allowed Statuses
            $allowedStatuses = ['present', 'absent', 'late', 'excused', 'online', 'nt', 'eb', 'ed', 'ob', 'at', 'pending'];
            if (!in_array($data['status'], $allowedStatuses)) {
                return ['success' => false, 'error' => 'Invalid status: ' . $data['status']];
            }
            
            // Set Defaults
            $data['student_count'] = isset($data['student_count']) ? (int)$data['student_count'] : 0;
            $data['check_time'] = date('Y-m-d H:i:s');
            $data['attendance_date'] = !empty($data['attendance_date']) ? $data['attendance_date'] : date('Y-m-d');
            $data['is_online'] = ($data['status'] === 'online') ? 1 : 0;
            $data['verified_by'] = !empty($data['verified_by']) ? $data['verified_by'] : 'Administrator';
            $data['verification_method'] = !empty($data['verification_method']) ? $data['verification_method'] : 'physical_check';
            
            // Handle Face-to-Face Image (Optional for absent/excused, required for present physical checks if provided)
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
            
            // Handle Online Meeting Screenshot
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
            
            // Clean Payload for DB
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
            
            // ⭐ RE-MARK / UPDATE SUPPORT:
            // Check if attendance record already exists for today's schedule
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
            
            // If already marked, UPDATE existing record instead of throwing error
            if ($existingRecord) {
                // Keep previous images if new ones were not uploaded
                if (empty($cleanData['face_to_face_image'])) {
                    $cleanData['face_to_face_image'] = $existingRecord['face_to_face_image'];
                }
                if (empty($cleanData['meeting_screenshot'])) {
                    $cleanData['meeting_screenshot'] = $existingRecord['meeting_screenshot'];
                }
                
                return $this->attendanceModel->update($existingRecord['id'], $cleanData);
            }
            
            // Otherwise, insert new record
            return $this->attendanceModel->create($cleanData);
            
        } catch (Exception $e) {
            error_log("❌ Error in markAttendance: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to mark attendance: ' . $e->getMessage()];
        }
    }

    public function getRecords($date = null) {
        try {
            if (!$date) $date = date('Y-m-d');
            return $this->attendanceModel->getByDate($date);
        } catch (Exception $e) {
            error_log("Error in getRecords: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAttendanceRecords($date) {
        return $this->getRecords($date);
    }
    
    public function getAttendanceRecordsByDay($day) {
        try {
            $today = date('Y-m-d');
            $allRecords = $this->attendanceModel->getByDay($day);
            
            $filtered = array_filter($allRecords, function($record) use ($today) {
                return isset($record['attendance_date']) && $record['attendance_date'] === $today;
            });
            
            return array_values($filtered);
        } catch (Exception $e) {
            error_log("Error in getAttendanceRecordsByDay: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteRecord($id) {
        try {
            if (!$id) return ['success' => false, 'error' => 'Record ID is required'];
            return $this->attendanceModel->delete($id);
        } catch (Exception $e) {
            error_log("Error in deleteRecord: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete record: ' . $e->getMessage()];
        }
    }
    
    public function getOnlineClasses($date = null) {
        try {
            return $this->attendanceModel->getOnlineClasses($date);
        } catch (Exception $e) {
            error_log("Error in getOnlineClasses: " . $e->getMessage());
            return [];
        }
    }
    
    public function getArchivedRecords($faculty = null) {
        try {
            if ($faculty) return $this->archiveModel->getByFaculty($faculty);
            return $this->archiveModel->getAll();
        } catch (Exception $e) {
            error_log("Error in getArchivedRecords: " . $e->getMessage());
            return [];
        }
    }
    
    public function archiveRecords($data) {
        try {
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
                    return ['success' => true, 'archived_count' => $count];
                }
                return ['success' => false, 'error' => 'Archive Failed: ' . $lastError];
            }

            $date = $data['date'] ?? null;
            if ($date) {
                return $this->attendanceModel->archiveAll($archivedBy, $date, $date);
            }
            return $this->attendanceModel->archiveAll($archivedBy);
        } catch (Exception $e) {
            error_log("Error in archiveRecords: " . $e->getMessage());
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }

    // ⭐ NEW RESTORE METHOD
    public function restoreRecords($data) {
        try {
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
                    return ['success' => true, 'restored_count' => $count];
                }
                return ['success' => false, 'error' => 'Restore Failed: ' . $lastError];
            }
            return ['success' => false, 'error' => 'No record IDs provided'];
        } catch (Exception $e) {
            error_log("Error in restoreRecords: " . $e->getMessage());
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }
}