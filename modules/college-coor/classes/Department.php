<?php

class Department {
    private $conn;
    private $department_id;
    private $department_name;   

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    private function resolveTable(array $candidates) {
        foreach ($candidates as $table) {
            try {
                $stmt = $this->conn->query("SHOW TABLES LIKE '" . str_replace("'", "''", $table) . "'");
                if ($stmt && $stmt->fetch()) {
                    return $table;
                }
            } catch (Throwable $e) {
                // Ignore and continue to the next possibility.
            }
        }

        return $candidates[0];
    }

    public function getAllDepartments() {
        $table = $this->resolveTable(['em_departments', 'sd_department']);
        $stmt = $this->conn->prepare("SELECT department_id, department_name FROM `{$table}` ORDER BY department_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDepartmentEmployees($department_id) {
        $table = $this->resolveTable(['em_employees', 'sms_employee']);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM `{$table}` WHERE department_id = :department_id OR department = :department_id");
        $stmt->execute([':department_id' => $department_id]);
        return $stmt->fetchColumn();
    }

    public function getDepartmentsWithDetails() {
        $deptTable = $this->resolveTable(['em_departments', 'sd_department']);
        $employeeTable = $this->resolveTable(['em_employees', 'sms_employee']);

        $sql = "SELECT 
                    d.department_id,
                    d.department_name,
                    CONCAT(e.first_name, ' ', e.last_name) AS department_head_name,
                    COUNT(emp.employee_id) AS employee_count
                FROM `{$deptTable}` d
                LEFT JOIN `{$employeeTable}` e 
                    ON d.department_head = e.employee_id
                LEFT JOIN `{$employeeTable}` emp 
                    ON emp.department_id = d.department_id OR emp.department = d.department_id
                GROUP BY d.department_id, d.department_name, e.first_name, e.last_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDepartment($department_name) {
        $table = $this->resolveTable(['em_departments', 'sd_department']);
        $stmt = $this->conn->prepare("INSERT INTO `{$table}` (department_name) VALUES (:department_name)");
        return $stmt->execute([':department_name' => $department_name]);
    }
}