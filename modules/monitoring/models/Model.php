<?php
class Model {
    protected $db;
    protected $table;
    protected $allowedColumns = [];
    protected $requiredColumns = [];
    
    public function __construct() {
        date_default_timezone_set('Asia/Manila');
        
        try {
            $this->db = new PDO(
                "mysql:host=127.0.0.1;dbname=monitoring;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            $this->db->exec("SET time_zone = '+08:00'");
            $this->db->exec("SET SESSION time_zone = '+08:00'");
            
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getDb() {
        return $this->db;
    }
    
    public function getPhilippinesTime() {
        return date('Y-m-d H:i:s');
    }
    
    public function getPhilippinesDate() {
        return date('Y-m-d');
    }
    
    // Get table columns from database
    public function getTableColumns() {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table}");
            $stmt->execute();
            $columns = [];
            while ($row = $stmt->fetch()) {
                $columns[] = $row['Field'];
            }
            return $columns;
        } catch (PDOException $e) {
            error_log("Error getting table columns: " . $e->getMessage());
            return [];
        }
    }
    
    // Validate and filter data before insert/update
    public function validateData($data, $forUpdate = false) {
        $errors = [];
        $validated = [];
        $columns = $this->getTableColumns();
        
        // Check required fields (only for insert)
        if (!$forUpdate && !empty($this->requiredColumns)) {
            foreach ($this->requiredColumns as $required) {
                if (!isset($data[$required]) || empty(trim($data[$required]))) {
                    $errors[] = "Field '{$required}' is required";
                }
            }
        }
        
        // Filter only allowed columns
        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                // Sanitize string values
                if (is_string($value)) {
                    $value = trim($value);
                    // Remove potentially dangerous characters for HTML
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                $validated[$key] = $value;
            }
        }
        
        return ['data' => $validated, 'errors' => $errors];
    }
    
    public function getAll($orderBy = 'id DESC') {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return false;
        }
    }
    
    // ⭐ FIXED: Create method with proper debugging
    public function create($data) {
        try {
            error_log("=== Model::create called ===");
            error_log("Table: " . $this->table);
            error_log("Data: " . print_r($data, true));
            error_log("Allowed columns: " . print_r($this->allowedColumns, true));
            
            // Filter allowed columns
            $filteredData = [];
            foreach ($this->allowedColumns as $column) {
                if (isset($data[$column])) {
                    $filteredData[$column] = $data[$column];
                }
            }
            
            error_log("Filtered data: " . print_r($filteredData, true));
            
            if (empty($filteredData)) {
                error_log("⚠️ No data to insert after filtering");
                return ['success' => false, 'error' => 'No valid data to insert'];
            }
            
            // Build query
            $columns = array_keys($filteredData);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            error_log("SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(array_values($filteredData));
            $lastId = $this->db->lastInsertId();
            
            error_log("Result: " . ($result ? 'TRUE' : 'FALSE'));
            error_log("Last insert ID: " . $lastId);
            
            if ($result) {
                return ['success' => true, 'id' => $lastId];
            } else {
                return ['success' => false, 'error' => 'Failed to insert data'];
            }
        } catch (PDOException $e) {
            error_log("❌ PDO Error in create: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function update($id, $data) {
        try {
            $filteredData = [];
            foreach ($this->allowedColumns as $column) {
                if (isset($data[$column])) {
                    $filteredData[$column] = $data[$column];
                }
            }
            
            if (empty($filteredData)) {
                return ['success' => false, 'error' => 'No valid data to update'];
            }
            
            $setClause = [];
            foreach ($filteredData as $key => $value) {
                $setClause[] = "{$key} = ?";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = ?";
            $values = array_values($filteredData);
            $values[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            return ['success' => $result, 'id' => $id];
            
        } catch (PDOException $e) {
            error_log("Update error in {$this->table}: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]];
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $result = $stmt->execute([$id]);
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log("Delete error in {$this->table}: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]];
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getByDateRange: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTodayRecords() {
        $today = $this->getPhilippinesDate();
        try {
            $sql = "SELECT * FROM {$this->table} WHERE DATE(created_at) = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in getTodayRecords: " . $e->getMessage());
            return [];
        }
    }
}
?>