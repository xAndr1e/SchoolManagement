<?php
require_once 'Model.php';

class FacilityReport extends Model {
    protected $table = 'mon_facility_reports';
    protected $requiredColumns = ['room_number', 'broken_equipment', 'equipment_type', 'reported_by'];
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'facility_id', 'room_number', 'broken_equipment', 'equipment_type',
    'quantity_damaged', 'description', 'reported_by', 'report_date',
    'status', 'resolved_date', 'damage_image'
        ];
    }
    
    public function getByDate($date) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facility_reports WHERE report_date = ? ORDER BY created_at DESC");
            $stmt->execute([$date]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting reports by date: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByStatus($status) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facility_reports WHERE status = ? ORDER BY report_date DESC");
            $stmt->execute([$status]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting reports by status: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByRoom($roomNumber) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facility_reports WHERE room_number = ? ORDER BY report_date DESC");
            $stmt->execute([$roomNumber]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting reports by room: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPendingReports() {
        try {
            $stmt = $this->db->query("SELECT * FROM mon_facility_reports WHERE status IN ('reported', 'in_progress') ORDER BY report_date ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pending reports: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facility_reports WHERE report_date BETWEEN ? AND ? ORDER BY report_date DESC");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting reports by date range: " . $e->getMessage());
            return [];
        }
    }

    public function autoArchivePreviousDays() {
    $today = date('Y-m-d');
    try {
        $stmt = $this->db->prepare("SELECT * FROM mon_facility_reports WHERE report_date < ? AND status = 'resolved'");
        $stmt->execute([$today]);
        $records = $stmt->fetchAll();
        
        foreach ($records as $row) {
            $archiveStmt = $this->db->prepare("
                INSERT INTO mon_facility_reports_archive 
                (original_id, facility_id, room_number, broken_equipment, equipment_type, quantity_damaged, description, reported_by, report_date, status, resolved_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $archiveStmt->execute([
                $row['id'], $row['facility_id'], $row['room_number'], $row['broken_equipment'], 
                $row['equipment_type'], $row['quantity_damaged'], $row['description'], 
                $row['reported_by'], $row['report_date'], $row['status'], $row['resolved_date'], $row['created_at']
            ]);
        }
        
        $del = $this->db->prepare("DELETE FROM mon_facility_reports WHERE report_date < ? AND status = 'resolved'");
        $del->execute([$today]);
        return true;
    } catch (PDOException $e) {
        error_log("Auto-archive facility error: " . $e->getMessage());
        return false;
    }
}
}
?>