<?php

class Role {
    private $conn;
    private $roleid;
    private $rolename;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getRoles() {
        $sql = "SELECT role_id, role_name FROM em_roles WHERE status = 'Active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolesByDepartment($departmentId) {
        $sql = "
            SELECT r.role_id, r.role_name
            FROM em_roles r
            JOIN em_departments d ON r.role_id = CASE d.department_code
                WHEN 'SMS_SD' THEN 14
                WHEN 'SMS_ENR' THEN 17
                WHEN 'SMS_REG' THEN 16
                WHEN 'SMS_CLINIC' THEN 19
                WHEN 'SMS_MON' THEN 18
                WHEN 'SMS_GD' THEN 15
                WHEN 'SMS_COORD' THEN 22
                WHEN 'SMS_LIB' THEN 20
                WHEN 'SMS_LAB' THEN 21
                WHEN 'HR_REC' THEN 2
                WHEN 'HR_PAY' THEN 4
                WHEN 'HR_ATT' THEN 5
                WHEN 'HR_EMP' THEN 3
                WHEN 'HR_COM' THEN 8
                WHEN 'HR_WFA' THEN 9
                WHEN 'HR_LND' THEN 7
                WHEN 'HR_PER' THEN 6
                WHEN 'HR_EER' THEN 12
                WHEN 'HR_EXIT' THEN 10
                WHEN 'HR_CLINIC' THEN 11
                ELSE 13
            END
            WHERE d.department_id = :department_id
              AND r.status = 'Active'
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':department_id' => $departmentId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug logging
        error_log("Department ID: " . $departmentId);
        error_log("Found roles: " . print_r($result, true));
        
        return $result;
    }
    
}
