<?php
include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../../../auth/guard.php';
include_once __DIR__ . '/../classes/Position.php';
include_once __DIR__ . '/../classes/Role.php';
include_once __DIR__ . '/../classes/User.php'; // add this

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_positions_roles':
        getPositionsAndRoles();
        break;

    case 'register_employee':
        registerEmployee();
        break;

    case 'get_employee_details':
    getEmployeeDetails();
    break;

    case 'update_employee':
        updateEmployee();
        break;    

    case 'get_employees':
        getEmployees();
        break;    

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

function getPositionsAndRoles() {
    $department_id = intval($_GET['department_id'] ?? 0);

    if (!$department_id) {
        echo json_encode(['success' => false, 'message' => 'No department ID provided.']);
        return;
    }

    try {
        $positionClass = new Position();
        $positions     = $positionClass->getPositionsByDepartment($department_id);

        $roleClass = new Role();
        $roles     = $roleClass->getRolesByDepartment($department_id);

        echo json_encode([
            'success'   => true,
            'positions' => $positions,
            'roles'     => $roles
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function registerEmployee() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        return;
    }

    $employee_id   = intval($_POST['employee']          ?? 0);
    $department_id = intval($_POST['department']        ?? 0);
    $position_id   = intval($_POST['position']          ?? 0);
    $password      = trim($_POST['password']            ?? '');
    $confirm_pass  = trim($_POST['confirm_password']    ?? '');

    if (!$employee_id || !$department_id || !$position_id) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        return;
    }

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password cannot be empty.']);
        return;
    }

    if ($password !== $confirm_pass) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        return;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        return;
    }

    $userClass = new User();
    $result    = $userClass->registerEmployee($employee_id, $department_id, $position_id, $password);
    echo json_encode($result);
}

function getEmployeeDetails() {
    $employee_id = intval($_GET['employee_id'] ?? 0);

    if (!$employee_id) {
        echo json_encode(['success' => false, 'message' => 'No employee ID provided.']);
        return;
    }

    try {
        $db   = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT 
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.department_id AS department,
                e.position_id AS position,
                e.employment_status AS status,
                d.department_name,
                p.position_name,
                r.role_name
            FROM em_employees e
            LEFT JOIN em_departments d ON e.department_id = d.department_id
            LEFT JOIN em_positions   p ON e.position_id   = p.position_id
            LEFT JOIN em_roles       r ON e.role_id        = r.role_id
            WHERE e.employee_id = :employee_id
        ");
        $stmt->execute([':employee_id' => $employee_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            echo json_encode(['success' => false, 'message' => 'Employee not found.']);
            return;
        }

        // Also fetch positions for the employee's current department
        $positionClass = new Position();
        $positions     = $employee['department']
            ? $positionClass->getPositionsByDepartment($employee['department'])
            : [];

        $roleClass = new Role();
        $roles     = $employee['department']
            ? $roleClass->getRolesByDepartment($employee['department'])
            : [];

        echo json_encode([
            'success'   => true,
            'employee'  => $employee,
            'positions' => $positions,
            'roles'     => $roles,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateEmployee() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        return;
    }

    $employee_id   = intval($_POST['employee_id']   ?? 0);
    $department_id = intval($_POST['department']    ?? 0);
    $position_id   = intval($_POST['position']      ?? 0);

    if (!$employee_id || !$department_id || !$position_id) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        return;
    }

    try {
        $db   = new Database();
        $conn = $db->getConnection();

        // Get default role for the department
        $roleStmt = $conn->prepare("
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

        $stmt = $conn->prepare("
            UPDATE em_employees
            SET department_id = :department_id,
                position_id   = :position_id,
                role_id       = :role_id
            WHERE employee_id = :employee_id
        ");
        $stmt->execute([
            ':department_id' => $department_id,
            ':position_id'   => $position_id,
            ':role_id'       => $role_id ?: null,
            ':employee_id'   => $employee_id,
        ]);

        echo json_encode(['success' => true, 'message' => 'Employee updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

    function getEmployees() {
    try {
        $db   = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->query("
            SELECT 
                e.employee_id,
                e.first_name,
                e.middle_name,
                e.last_name,
                e.employment_status AS status,
                d.department_name,
                p.position_name
            FROM em_employees e
            LEFT JOIN em_departments d ON e.department_id = d.department_id
            LEFT JOIN em_positions   p ON e.position_id   = p.position_id
            ORDER BY e.last_name ASC
        ");

        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $employees]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
