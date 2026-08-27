<?php
require_once 'Model.php';

class Facility extends Model {
    protected $table = 'mon_facilities';
    protected $requiredColumns = ['room_number', 'equipment_type', 'equipment_name', 'quantity'];
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'room_number', 'equipment_type', 'equipment_name', 'quantity',
            'functional_quantity', 'damaged_quantity', 'status', 
            'monitored_by', 'last_checked'
        ];
    }
    
    public function getByRoom($roomNumber) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facilities WHERE room_number = ?");
            $stmt->execute([$roomNumber]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting facilities by room: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByEquipmentType($type) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_facilities WHERE equipment_type = ?");
            $stmt->execute([$type]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting facilities by equipment type: " . $e->getMessage());
            return [];
        }
    }
    
    public function getDamagedEquipment() {
        try {
            $stmt = $this->db->query("SELECT * FROM mon_facilities WHERE damaged_quantity > 0 ORDER BY damaged_quantity DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting damaged equipment: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateQuantities($id, $functional, $damaged) {
        try {
            $sql = "UPDATE mon_facilities SET functional_quantity = ?, damaged_quantity = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$functional, $damaged, $id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log("Error updating quantities: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]];
        }
    }
    
    public function countAll() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error counting facilities: " . $e->getMessage());
            return 0;
        }
    }
}
?>