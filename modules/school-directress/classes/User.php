<?php

include_once __DIR__ . '/../../../database/db.php';

class User {
    private $conn;
    private $infoid;
    private $firstname;
    private $lastname;
    private $middlename;
    private $role;
    private $status; 

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function userSession() {
        if (isset($_SESSION['employee_id'])) {
            $sql = "SELECT 
                        e.employee_id,
                        e.first_name,
                        e.last_name,
                        e.middle_name,
                        e.department_id,
                        r.role_name AS role,
                        e.employment_status AS status
                    FROM `em_employees` e
                    LEFT JOIN `em_roles` r ON e.role_id = r.role_id
                    LEFT JOIN `em_departments` d ON e.department_id = d.department_id
                    WHERE e.employee_id = :employee_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':employee_id' => $_SESSION['employee_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null; // No user session found
    }

    public function registerEmployee($employee_id, $department_id, $position_id, $password) {
    $checkStmt = $this->conn->prepare("SELECT user_id FROM user_account WHERE employee_id = :employee_id");
    $checkStmt->execute([':employee_id' => $employee_id]);
    if ($checkStmt->fetchColumn()) {
        return ['success' => false, 'message' => 'Employee already has a user account.'];
    }

    // Get the default role tied to this department code.
    $roleStmt = $this->conn->prepare("
        SELECT
            CASE department_code
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
            END AS role_id
        FROM em_departments
        WHERE department_id = :dept_id
        LIMIT 1
    ");
    $roleStmt->execute([':dept_id' => $department_id]);
    $role_id = $roleStmt->fetchColumn();

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $this->conn->beginTransaction();

        // 1. INSERT into user_account
        $insertStmt = $this->conn->prepare("
            INSERT INTO user_account (password, employee_id, created_at)
            VALUES (:password, :employee_id, NOW())
        ");
        $insertStmt->execute([
            ':password'    => $hashed_password,
            ':employee_id' => $employee_id,
        ]);

        $user_id = $this->conn->lastInsertId();

        // 2. UPDATE em_employees
            $updateStmt = $this->conn->prepare("
                UPDATE em_employees
                SET department_id     = :department_id,
                    position_id       = :position_id,
                    role_id           = :role_id,
                    user_id           = :user_id,
                    employment_status = 'Active'
                WHERE employee_id = :employee_id
            ");
            $updateStmt->execute([
                ':department_id' => $department_id,
                ':position_id'   => $position_id,
                ':role_id'       => $role_id ?: null,
                ':user_id'       => $user_id,
                ':employee_id'   => $employee_id,
            ]);

        $this->conn->commit();
        return ['success' => true, 'message' => 'Employee registered successfully.'];

    } catch (Exception $e) {
        $this->conn->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
}
