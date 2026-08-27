<?php
require_once '../models/Schedule.php';
require_once '../models/Attendance.php';

class ScheduleController {
    private $scheduleModel;
    private $attendanceModel;
    
    public function __construct() {
        $this->scheduleModel = new Schedule();
        $this->attendanceModel = new Attendance();
    }
    
    // Helper to safely filter schedules without excluding recurring weekly items
    private function filterValidSchedules(array $schedules, string $today): array {
        return array_filter($schedules, function($schedule) {
            // Allows weekly recurring schedules matched by day_of_week
            return true;
        });
    }

    // Helper to format uniform schedule responses across both schemas
    private function formatScheduleResponse(array $schedule, string $today, string $currentTime): array {
        $hasAttendance = $this->attendanceModel->hasAttendance($schedule['id'], $today);
        $attendanceStatus = 'pending';
        $studentCount = 0;
        
        if ($hasAttendance) {
            $records = $this->attendanceModel->getByDate($today);
            foreach ($records as $record) {
                if ($record['schedule_id'] == $schedule['id']) {
                    $attendanceStatus = $record['status'];
                    $studentCount = $record['student_count'] ?? 0;
                    break;
                }
            }
        }
        
        $scheduleDate = $schedule['schedule_date'] ?? $today;

        return [
            'id' => $schedule['id'],
            'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
            'subject_name' => $schedule['subject_name'] ?? 'N/A',
            'subject_code' => $schedule['subject_name'] ?? 'N/A',
            'course_section' => $schedule['section_name'] ?? 'N/A',
            'section_name' => $schedule['section_name'] ?? 'N/A',
            'room' => $schedule['room_name'] ?? 'N/A',
            'room_name' => $schedule['room_name'] ?? 'N/A',
            'start_time' => $schedule['start_time'],
            'end_time' => $schedule['end_time'],
            'day' => $schedule['day_of_week'],
            'day_of_week' => $schedule['day_of_week'],
            'schedule_date' => $scheduleDate,
            'schedule_type' => $schedule['schedule_type'] ?? 'Class',
            'status' => $schedule['status'] ?? 'Scheduled',
            'has_attendance' => $hasAttendance,
            'attendance_status' => $attendanceStatus,
            'student_count' => $studentCount,
            'is_past' => $scheduleDate < $today,
            'is_past_time' => $schedule['start_time'] < $currentTime
        ];
    }
    
    // GET /api/schedule/today
    public function getTodaySchedules() {
        try {
            $today = date('Y-m-d');
            $dayOfWeek = date('l');
            $currentTime = date('H:i:s');
            
            $schedules = $this->scheduleModel->getByDay($dayOfWeek);
            $filtered = $this->filterValidSchedules($schedules, $today);
            
            usort($filtered, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
            
            $result = [];
            foreach ($filtered as $schedule) {
                $result[] = $this->formatScheduleResponse($schedule, $today, $currentTime);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getTodaySchedules: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/schedule/room/{room}
    public function getSchedulesByRoom($params) {
        try {
            $room = $params['room'] ?? '';
            $today = date('Y-m-d');
            $dayOfWeek = date('l');
            $currentTime = date('H:i:s');
            
            $schedules = $this->scheduleModel->getByDay($dayOfWeek);
            $validSchedules = $this->filterValidSchedules($schedules, $today);
            
            if (!empty($room)) {
                $filtered = array_filter($validSchedules, function($schedule) use ($room) {
                    return isset($schedule['room_name']) && 
                           strtolower($schedule['room_name']) === strtolower($room);
                });
            } else {
                $filtered = $validSchedules;
            }
            
            usort($filtered, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
            
            $result = [];
            foreach ($filtered as $schedule) {
                $result[] = $this->formatScheduleResponse($schedule, $today, $currentTime);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getSchedulesByRoom: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/schedule/all-rooms
    public function getAllRoomsWithSchedules() {
        try {
            $today = date('Y-m-d');
            $dayOfWeek = date('l');
            
            $schedules = $this->scheduleModel->getByDay($dayOfWeek);
            $filtered = $this->filterValidSchedules($schedules, $today);
            
            $rooms = [];
            foreach ($filtered as $schedule) {
                $roomName = $schedule['room_name'] ?? 'Unknown Room';
                if (!isset($rooms[$roomName])) {
                    $rooms[$roomName] = [];
                }
                $rooms[$roomName][] = $schedule;
            }
            
            $result = [];
            foreach ($rooms as $roomName => $roomSchedules) {
                usort($roomSchedules, function($a, $b) {
                    return strcmp($a['start_time'], $b['start_time']);
                });
                
                $roomData = [
                    'room' => $roomName,
                    'schedules' => []
                ];
                
                foreach ($roomSchedules as $schedule) {
                    $hasAttendance = $this->attendanceModel->hasAttendance($schedule['id'], $today);
                    
                    $roomData['schedules'][] = [
                        'id' => $schedule['id'],
                        'faculty_name' => $schedule['faculty_name'] ?? 'N/A',
                        'subject_name' => $schedule['subject_name'] ?? 'N/A',
                        'course_section' => $schedule['section_name'] ?? 'N/A',
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'has_attendance' => $hasAttendance
                    ];
                }
                
                $result[] = $roomData;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllRoomsWithSchedules: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/schedule/mobile-view
    public function getMobileView() {
        try {
            $today = date('Y-m-d');
            $dayOfWeek = date('l');
            $currentTime = date('H:i:s');
            
            $schedules = $this->scheduleModel->getByDay($dayOfWeek);
            $filtered = $this->filterValidSchedules($schedules, $today);
            
            usort($filtered, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
            
            $result = [];
            $summary = ['total' => 0, 'marked' => 0, 'pending' => 0];
            
            foreach ($filtered as $schedule) {
                $item = $this->formatScheduleResponse($schedule, $today, $currentTime);
                $result[] = $item;
                
                $summary['total']++;
                if ($item['has_attendance']) {
                    $summary['marked']++;
                } else {
                    $summary['pending']++;
                }
            }
            
            return [
                'schedules' => $result,
                'summary' => $summary,
                'date' => $today,
                'day' => $dayOfWeek,
                'current_time' => $currentTime
            ];
        } catch (Exception $e) {
            error_log("Error in getMobileView: " . $e->getMessage());
            return [
                'schedules' => [],
                'summary' => ['total' => 0, 'marked' => 0, 'pending' => 0],
                'error' => 'Failed to load schedules: ' . $e->getMessage()
            ];
        }
    }
}
?>