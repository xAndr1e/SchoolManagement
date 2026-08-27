<?php
require_once __DIR__ . '/../../../database/db.php';

class User {
    private $authDb;

    public function __construct() {
        $this->authDb = (new Database())->getConnection();
    }

    public function authenticate($employeeId, $password) {
        try {
            $stmt = $this->authDb->prepare("SELECT user_account.user_id AS id,
                    user_account.employee_id AS username,
                    user_account.password,
                    CONCAT_WS(' ', sms_employee.first_name, sms_employee.middle_name, sms_employee.last_name) AS fullname,
                    CASE WHEN sms_employee.role = 1 THEN 'admin' ELSE 'monitor' END AS role
                FROM user_account
                INNER JOIN sms_employee ON sms_employee.employee_id = user_account.employee_id
                WHERE user_account.employee_id = :employee_id
                  AND sms_employee.status = 'active'
                LIMIT 1");
            $stmt->execute(['employee_id' => $employeeId]);
            $user = $stmt->fetch();
            return $user && password_verify($password, $user['password']) ? $user : false;
        } catch (PDOException $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return false;
        }
    }

    public function updateLastLogin($id) {
        return ['success' => true];
    }
}
?>