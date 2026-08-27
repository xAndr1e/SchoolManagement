<?php
require_once 'Model.php';

class VisitorArchive extends Model {
    protected $table = 'mon_visitors_archive';
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'original_id', 'visitor_name', 'contact_number', 'email', 
            'home_address', 'id_type', 'id_number', 'vehicle_type', 
            'vehicle_plate', 'id_attachment', 'id_approved', 'purpose_of_visit', 
            'person_to_visit', 'department', 'time_in', 'time_out', 'visit_duration',
            'status', 'monitored_by', 'qr_code', 'archived_by', 'archived_at'
        ];
    }

    public function getAllArchives($name = null) {
        try {
            if ($name) {
                $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE visitor_name LIKE ? ORDER BY archived_at DESC");
                $stmt->execute(["%{$name}%"]);
                return $stmt->fetchAll();
            }
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY archived_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting visitor archive: " . $e->getMessage());
            return [];
        }
    }
}