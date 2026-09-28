<?php
include_once __DIR__ . '/../../../database/db.php';

Class Employee {
    private $conn;
    private $employeeid;
    private $firstname;
    private $lastname;
    private $middlename;
    private $department;
    private $position;
    private $status; 


    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getEmployees() {
    $sql = "SELECT 
                em.employee_id,
                em.first_name,
                em.middle_name,
                em.last_name,
                em.department_id,
                em.position_id,
                em.employment_status
            FROM em_employees em
            LEFT JOIN em_departments sd 
                ON em.department_id = sd.department_id
            LEFT JOIN em_positions sp
                ON em.position_id = sp.position_id
            ORDER BY em.employee_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeId() {
        return $_SESSION['employee_id'] ?? null;
    }

    public function getEmployeeName() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $employeeId = $_SESSION['employee_id'] ?? null;

        if ($employeeId) {
            $sql = "SELECT first_name, last_name FROM em_employees WHERE employee_id = :employee_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':employee_id', $employeeId);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                return htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']);
            }
        }
        return 'Unknown User';
    }
    
    public function getEmployeePosition() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $employeeId = $_SESSION['employee_id'] ?? null;

        if ($employeeId) {
            $sql = "SELECT sp.position_name 
                    FROM em_employees em
                    LEFT JOIN em_positions sp ON em.position_id = sp.position_id
                    WHERE em.employee_id = :employee_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':employee_id', $employeeId);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                return htmlspecialchars($employee['position_name']);
            }
        }
        return 'Unknown Position';
    }
}

