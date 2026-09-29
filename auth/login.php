<?php
// auth/login.php
include "../database/db.php";

$db   = new Database();
$conn = $db->getConnection();

session_start();

header('Content-Type: application/json');

define('MAX_ATTEMPTS', 3);
define('LOCKOUT_TIME', 60); // 60 seconds lockout

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeid = trim($_POST['employee_id']);
    $password   = $_POST['password'];

    $ip  = $_SERVER['REMOTE_ADDR'];
    $key = 'login_attempts_' . $ip . '_' . $employeeid;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }

    $attempts = &$_SESSION[$key];
    $elapsed  = time() - $attempts['last_attempt'];

    // Reset if lockout period has passed
    if ($elapsed > LOCKOUT_TIME) {
        $attempts = ['count' => 0, 'last_attempt' => time()];
        $elapsed  = 0;
    }

    // Block if already locked
    if ($attempts['count'] >= MAX_ATTEMPTS) {
        $remaining_seconds = max(0, (int)(LOCKOUT_TIME - $elapsed));
        echo json_encode([
            'success'           => false,
            'locked'            => true,
            'remaining_seconds' => $remaining_seconds,
            'message'           => 'Too many failed attempts.',
        ]);
        exit();
    }

    // ── Authentication ────────────────────────────────────────────────────────
    $stmt = $conn->prepare("
        SELECT
            ua.user_id,
            ua.employee_id,
            ua.password,
            e.role_id AS role,
            e.department_id AS department,
            CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS employee_name,
            r.role_name,
            d.department_code,
            d.department_name
        FROM user_account ua
        INNER JOIN em_employees e ON e.employee_id = ua.employee_id
        LEFT  JOIN em_roles r ON r.role_id = e.role_id
        LEFT  JOIN em_departments d ON d.department_id = e.department_id
        WHERE ua.employee_id = :employeeid
        AND   e.employment_status = 'Active'
        LIMIT 1
    ");
    $stmt->bindParam(':employeeid', $employeeid);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ── Success: clear attempt tracking & build session ───────────────────
        unset($_SESSION[$key]);

        $_SESSION['user_id']        = $user['user_id'];
        $_SESSION['employee_id']     = $user['employee_id'];
        $_SESSION['username']        = $user['employee_id'];
        $_SESSION['employee_name']   = $user['employee_name'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['role_name']       = $user['role_name'];
        $_SESSION['department_id']   = $user['department'];        
        $_SESSION['department_name'] = $user['department_name'];  
        $_SESSION['sensitive_auth']  = true;
        $_SESSION['sensitive_last_activity'] = time();
        $_SESSION['sensitive_timeout'] = 900;

        $redirectMap = [
            'SMS_SD'      => 'modules/school-directress/index.php',
            'SMS_ENR'     => 'modules/enrollment/index.php',
            'SMS_REG'     => 'modules/registrar/',
            'SMS_CLINIC'  => 'modules/clinic/index.php',
            'SMS_LIB'     => 'modules/library/index.php',
            'SMS_LAB'     => 'modules/laboratory/',
            'SMS_MON'     => 'modules/monitoring/public/index.php?page=dashboard',
            'SMS_GD'      => 'modules/guidance/index.php',
            'SMS_COORD'   => 'modules/college-coor/index.php',
            'HR_REC'      => 'modules/recruitment/index.php',
        ];

        $requestedRedirect = $_POST['redirect'] ?? '';
        $allowedRedirects = [
            'modules/monitoring/public/index.php?page=mobile-attendance',
            'modules/monitoring/public/index.php?page=mobile-facilities',
        ];
        $departmentCode = $user['department_code'] ?? '';
        if ($departmentCode === 'SMS_MON' && in_array($requestedRedirect, $allowedRedirects, true)) {
            $redirectMap[$departmentCode] = $requestedRedirect;
        }

        if (!isset($redirectMap[$departmentCode])) {
            echo json_encode(['success' => false, 'locked' => false, 'message' => 'No module is assigned to this account.']);
            exit();
        }

        echo json_encode([
            'success'  => true,
            'redirect' => '/' . ltrim($redirectMap[$departmentCode], '/'),
        ]);
        exit();

    } else {
        // ── Failed: increment attempt counter ─────────────────────────────────
        $attempts['count']++;
        $attempts['last_attempt'] = time();

        $remaining_attempts = MAX_ATTEMPTS - $attempts['count'];

        if ($remaining_attempts <= 0) {
            echo json_encode([
                'success'           => false,
                'locked'            => true,
                'remaining_seconds' => (int) LOCKOUT_TIME,
                'message'           => 'Too many failed attempts. Account locked for 15 minutes.',
            ]);
        } else {
            echo json_encode([
                'success'            => false,
                'locked'             => false,
                'remaining_attempts' => $remaining_attempts,
                'message'            => 'Invalid Employee ID or Password.',
            ]);
        }
        exit();
    }
}
