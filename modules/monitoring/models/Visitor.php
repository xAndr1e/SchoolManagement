<?php
require_once 'Model.php';

class Visitor extends Model {
    protected $table = 'mon_visitors';
    protected $requiredColumns = ['visitor_name', 'contact_number', 'purpose_of_visit'];
    
    public function __construct() {
        parent::__construct();
        $this->allowedColumns = [
            'visitor_name', 'contact_number', 'email', 'home_address',
            'id_type', 'id_number', 'id_attachment', 'id_approved', 'approved_by', 'approved_at',
            'vehicle_type', 'vehicle_plate', 'purpose_of_visit', 
            'person_to_visit', 'department', 'time_in', 'time_out', 
            'status', 'monitored_by', 'additional_notes', 'qr_code',
            'privacy_consent', 'visit_duration', 'registration_ip'
        ];
    }
    
    public function getByDate($date) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM mon_visitors WHERE DATE(time_in) = ? ORDER BY time_in DESC");
            $stmt->execute([$date]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting visitors by date: " . $e->getMessage());
            return [];
        }
    }
    
    public function getInsideVisitors() {
        try {
            $stmt = $this->db->query("SELECT * FROM mon_visitors WHERE status = 'inside' ORDER BY time_in DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting inside visitors: " . $e->getMessage());
            return [];
        }
    }
    
    public function checkout($id, $timeOut) {
        try {
            // Get visitor for duration calculation
            $visitor = $this->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            
            $timeIn = new DateTime($visitor['time_in']);
            $timeOutObj = new DateTime($timeOut);
            $interval = $timeIn->diff($timeOutObj);
            $duration = $interval->format('%H:%I:%S');
            
            $sql = "UPDATE mon_visitors SET time_out = ?, status = 'left', visit_duration = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$timeOut, $duration, $id]);
            
            if ($result) {
                $this->logAudit($id, 'checkout', "Visitor checked out");
            }
            
            return ['success' => $result, 'duration' => $duration];
        } catch (PDOException $e) {
            error_log("Error checking out visitor: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]];
        }
    }
    
    public function checkoutByIdentifier($identifier) {
        try {
            // Search by QR code or contact number
            $stmt = $this->db->prepare("
                SELECT * FROM mon_visitors 
                WHERE (qr_code = ? OR contact_number = ?) 
                AND status = 'inside' 
                ORDER BY time_in DESC LIMIT 1
            ");
            $stmt->execute([$identifier, $identifier]);
            $visitor = $stmt->fetch();
            
            if (!$visitor) {
                return ['success' => false, 'error' => 'No active visitor found with this identifier'];
            }
            
            return $this->checkout($visitor['id'], date('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            error_log("Error checking out by identifier: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM mon_visitors 
                WHERE DATE(time_in) BETWEEN ? AND ? 
                ORDER BY time_in DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting visitors by date range: " . $e->getMessage());
            return [];
        }
    }
    
    public function createVisitor($data) {
        try {
            error_log("=== CREATE VISITOR DEBUG ===");
            error_log("Data received: " . print_r($data, true));
            
            // Generate unique QR code
            $data['qr_code'] = $this->generateQRCode();
            $data['time_in'] = date('Y-m-d H:i:s');
            $data['status'] = 'pending_approval';
            $data['id_approved'] = 0;
            $data['registration_ip'] = $_SERVER['REMOTE_ADDR'] ?? null;
            
            error_log("Data after adding fields: " . print_r($data, true));
            
            // Create visitor
            $result = $this->create($data);
            
            error_log("Create result: " . print_r($result, true));
            
            if ($result['success']) {
                // Get the inserted ID
                $visitorId = $this->db->lastInsertId();
                error_log("Visitor created with ID: " . $visitorId);
                
                $this->logAudit($visitorId, 'registration', 'Visitor registered via QR code');
                
                // Return the complete visitor data
                $visitor = $this->getById($visitorId);
                $result['data'] = $visitor;
                error_log("Returning visitor data: " . print_r($visitor, true));
            } else {
                error_log("Create failed. Result: " . print_r($result, true));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error creating visitor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function generateQRCode() {
        return 'VIS-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }
    
    public function approveID($visitorId, $adminId) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE mon_visitors SET id_approved = 1, approved_by = ?, approved_at = NOW(), status = 'inside' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$adminId, $visitorId]);
            
            if ($result) {
                $this->logAudit($visitorId, 'approve_id', "ID approved by admin ID: $adminId");
                $this->db->commit();
                return ['success' => true];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Failed to approve ID'];
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error approving ID: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getPendingApprovals() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM mon_visitors 
                WHERE id_approved = 0 AND status = 'pending_approval' 
                ORDER BY time_in DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting pending approvals: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStatistics($period = 'today') {
        try {
            $stats = [];
            
            // Today's visitors
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE DATE(time_in) = CURDATE()");
            $stmt->execute();
            $stats['today'] = $stmt->fetch()['count'];
            
            // Currently inside
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE status = 'inside'");
            $stmt->execute();
            $stats['inside'] = $stmt->fetch()['count'];
            
            // Pending approvals
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE status = 'pending_approval'");
            $stmt->execute();
            $stats['pending'] = $stmt->fetch()['count'];
            
            // Checked out today
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE DATE(time_out) = CURDATE()");
            $stmt->execute();
            $stats['checked_out'] = $stmt->fetch()['count'];
            
            // Weekly
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE YEARWEEK(time_in) = YEARWEEK(CURDATE())");
            $stmt->execute();
            $stats['weekly'] = $stmt->fetch()['count'];
            
            // Monthly
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM mon_visitors WHERE MONTH(time_in) = MONTH(CURDATE()) AND YEAR(time_in) = YEAR(CURDATE())");
            $stmt->execute();
            $stats['monthly'] = $stmt->fetch()['count'];
            
            // Daily visitor count for chart (last 7 days)
            $stmt = $this->db->prepare("
                SELECT DATE(time_in) as date, COUNT(*) as count 
                FROM mon_visitors 
                WHERE time_in >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(time_in)
                ORDER BY date ASC
            ");
            $stmt->execute();
            $stats['daily_stats'] = $stmt->fetchAll();
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchVisitors($keyword, $limit = 50) {
        try {
            $searchTerm = "%$keyword%";
            $stmt = $this->db->prepare("
                SELECT * FROM mon_visitors 
                WHERE visitor_name LIKE ? 
                OR contact_number LIKE ? 
                OR email LIKE ? 
                OR purpose_of_visit LIKE ? 
                OR person_to_visit LIKE ?
                OR qr_code LIKE ?
                ORDER BY time_in DESC
                LIMIT ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching visitors: " . $e->getMessage());
            return [];
        }
    }
    
    public function logAudit($visitorId, $action, $details) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO mon_visitor_audit_logs (visitor_id, action, details, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$visitorId, $action, $details]);
            return true;
        } catch (PDOException $e) {
            error_log("Error logging audit: " . $e->getMessage());
            return false;
        }
    }
    
    public function getVisitorById($id) {
        return $this->getById($id);
    }

    public function autoArchivePreviousDays() {
    $today = date('Y-m-d');
    try {
        $stmt = $this->db->prepare("SELECT * FROM mon_visitors WHERE DATE(time_in) < ?");
        $stmt->execute([$today]);
        $records = $stmt->fetchAll();
        
        foreach ($records as $row) {
            $archiveStmt = $this->db->prepare("
                INSERT INTO mon_visitors_archive 
                (original_id, visitor_name, contact_number, email, home_address, id_type, id_number, vehicle_type, vehicle_plate, id_attachment, id_approved, purpose_of_visit, person_to_visit, department, time_in, status, monitored_by, qr_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $archiveStmt->execute([
                $row['id'], $row['visitor_name'], $row['contact_number'], $row['email'], 
                $row['home_address'], $row['id_type'], $row['id_number'], $row['vehicle_type'], 
                $row['vehicle_plate'], $row['id_attachment'], $row['id_approved'], 
                $row['purpose_of_visit'], $row['person_to_visit'], $row['department'], 
                $row['time_in'], $row['status'], $row['monitored_by'], $row['qr_code']
            ]);
        }
        
        $del = $this->db->prepare("DELETE FROM mon_visitors WHERE DATE(time_in) < ?");
        $del->execute([$today]);
        return true;
    } catch (PDOException $e) {
        error_log("Auto-archive visitor error: " . $e->getMessage());
        return false;
    }
}
}
?>