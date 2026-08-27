<?php
require_once '../models/Report.php';

class ReportController {
    private $reportModel;
    
    public function __construct() {
        $this->reportModel = new Report();
    }

    private function sendJsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
    
    public function getAttendanceReport($startDate = null, $endDate = null) {
        try {
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            $data = $this->reportModel->generateAttendanceReport($startDate, $endDate);
            $this->sendJsonResponse([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getFacilityReport($startDate = null, $endDate = null) {
        try {
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            $data = $this->reportModel->generateFacilityReport($startDate, $endDate);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getVisitorReport($startDate = null, $endDate = null) {
        try {
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            $data = $this->reportModel->generateVisitorReport($startDate, $endDate);
            $this->sendJsonResponse([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getComprehensive($startDate = null, $endDate = null) {
        try {
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            
            $attendance = $this->reportModel->generateAttendanceReport($startDate, $endDate);
            $facility = $this->reportModel->generateFacilityReport($startDate, $endDate);
            $visitor = $this->reportModel->generateVisitorReport($startDate, $endDate);
            
            $response = [
                'success' => true,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'summary' => [
                    'attendance_summary' => $attendance,
                    'facility_summary' => $facility,
                    'visitor_summary' => $visitor
                ]
            ];
            $this->sendJsonResponse($response);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * GET /api/report/details
     * Query params: type=attendance|facility|visitor, start=YYYY-MM-DD, end=YYYY-MM-DD
     */
    public function getDetails($type = null, $startDate = null, $endDate = null) {
        try {
            $type = $_GET['type'] ?? $type ?? 'attendance';
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            
            $validTypes = ['attendance', 'facility', 'visitor'];
            if (!in_array($type, $validTypes)) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'Invalid type. Must be one of: ' . implode(', ', $validTypes)
                ]);
                return;
            }
            
            $data = $this->reportModel->getDetailedRecords($type, $startDate, $endDate);
            
            $this->sendJsonResponse([
                'success' => true,
                'type' => $type,
                'data' => $data,
                'total' => count($data),
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getDetails: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * GET /api/report/stats
     * Query params: start=YYYY-MM-DD, end=YYYY-MM-DD
     */
    public function getStats($startDate = null, $endDate = null) {
        try {
            $startDate = $_GET['start'] ?? $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end'] ?? $endDate ?? date('Y-m-d');
            
            $stats = $this->reportModel->getComprehensiveStats($startDate, $endDate);
            
            $this->sendJsonResponse([
                'success' => true,
                'stats' => $stats,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>