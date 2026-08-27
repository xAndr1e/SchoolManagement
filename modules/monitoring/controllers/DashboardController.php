<?php
require_once '../models/Attendance.php';
require_once '../models/FacilityReport.php';
require_once '../models/Visitor.php';
require_once '../models/Schedule.php';
require_once '../models/Facility.php';

class DashboardController {
    private $attendanceModel;
    private $facilityReportModel;
    private $visitorModel;
    private $scheduleModel;
    private $facilityModel;
    
    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->facilityReportModel = new FacilityReport();
        $this->visitorModel = new Visitor();
        $this->scheduleModel = new Schedule();
        $this->facilityModel = new Facility();
    }
    
    // Called from index.php: $dashboardController->getDashboardData()
    public function getDashboardData() {
        try {
            $today = date('Y-m-d');
            
            $attendance = $this->attendanceModel->getByDate($today);
            $visitors = $this->visitorModel->getByDate($today);
            $visitorsInside = $this->visitorModel->getInsideVisitors();
            $schedules = $this->scheduleModel->getByDate($today);
            $pendingReports = $this->facilityReportModel->getByStatus('reported');
            $inProgressReports = $this->facilityReportModel->getByStatus('in_progress');
            $damaged = $this->facilityModel->getDamagedEquipment();
            
            return [
                'summary' => [
                    'today_attendance' => is_array($attendance) ? count($attendance) : 0,
                    'pending_reports' => (is_array($pendingReports) ? count($pendingReports) : 0) + 
                                        (is_array($inProgressReports) ? count($inProgressReports) : 0),
                    'visitors_today' => is_array($visitors) ? count($visitors) : 0,
                    'visitors_inside' => is_array($visitorsInside) ? count($visitorsInside) : 0,
                    'total_schedules' => is_array($schedules) ? count($schedules) : 0,
                    'damaged_equipment' => is_array($damaged) ? count($damaged) : 0
                ],
                'recent_activity' => $this->getRecentActivity(),
                'attendance_rate' => $this->getAttendanceRate($today)
            ];
        } catch (Exception $e) {
            error_log("Error in getDashboardData: " . $e->getMessage());
            return [
                'summary' => [
                    'today_attendance' => 0,
                    'pending_reports' => 0,
                    'visitors_today' => 0,
                    'visitors_inside' => 0,
                    'total_schedules' => 0,
                    'damaged_equipment' => 0
                ],
                'recent_activity' => [],
                'attendance_rate' => 0
            ];
        }
    }
    
    private function getRecentActivity($limit = 10) {
        try {
            $activities = [];
            $today = date('Y-m-d');
            
            $attendance = $this->attendanceModel->getByDate($today);
            if (is_array($attendance) && !empty($attendance)) {
                $count = 0;
                foreach ($attendance as $record) {
                    if ($count >= 5) break;
                    $activities[] = [
                        'type' => 'attendance',
                        'message' => "{$record['faculty_name']} marked as {$record['status']} for {$record['course_section']}",
                        'time' => $record['check_time']
                    ];
                    $count++;
                }
            }
            
            $reports = $this->facilityReportModel->getAll('created_at DESC');
            if (is_array($reports) && !empty($reports)) {
                $count = 0;
                foreach ($reports as $report) {
                    if ($count >= 3) break;
                    $activities[] = [
                        'type' => 'facility',
                        'message' => "New report: {$report['broken_equipment']} in Room {$report['room_number']}",
                        'time' => $report['created_at']
                    ];
                    $count++;
                }
            }
            
            $visitors = $this->visitorModel->getByDate($today);
            if (is_array($visitors) && !empty($visitors)) {
                $count = 0;
                foreach ($visitors as $visitor) {
                    if ($count >= 2) break;
                    $activities[] = [
                        'type' => 'visitor',
                        'message' => "{$visitor['visitor_name']} entered for {$visitor['purpose_of_visit']}",
                        'time' => $visitor['time_in']
                    ];
                    $count++;
                }
            }
            
            usort($activities, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (Exception $e) {
            error_log("Error in getRecentActivity: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAttendanceRate($date) {
        try {
            $records = $this->attendanceModel->getByDate($date);
            if (!is_array($records) || empty($records)) {
                return 0;
            }
            
            $present = 0;
            foreach ($records as $record) {
                if (in_array($record['status'], ['present', 'online'])) {
                    $present++;
                }
            }
            
            return round(($present / count($records)) * 100, 2);
        } catch (Exception $e) {
            error_log("Error in getAttendanceRate: " . $e->getMessage());
            return 0;
        }
    }
}
?>