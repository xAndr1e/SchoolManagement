<?php
require_once 'Model.php';

class FacilityReportArchive extends Model {
    protected $table = 'mon_facility_reports_archive';
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'original_id', 'facility_id', 'room_number', 'broken_equipment', 
            'equipment_type', 'quantity_damaged', 'description', 'damage_image', 
            'reported_by', 'report_date', 'status', 'resolved_date', 'created_at',
            'archived_by', 'archived_at' // Added missing archive metadata
        ];
    }

    public function getAllArchives($room = null) {
        try {
            if ($room) {
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE room_number LIKE ? ORDER BY archived_at DESC");
                $stmt->execute(["%{$room}%"]);
                return $stmt->fetchAll();
            }
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY archived_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting facility archive: " . $e->getMessage());
            return [];
        }
    }
}