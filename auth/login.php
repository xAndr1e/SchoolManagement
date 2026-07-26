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
            user_account.user_id,
            user_account.employee_id,
            user_account.password,
            sms_employee.role,
            sms_employee.department,
            sd_roles.role_name,
            sd_department.department_name
        FROM user_account
        INNER JOIN sms_employee  ON sms_employee.employee_id  = user_account.employee_id
        INNER JOIN sd_roles      ON sd_roles.role_id          = sms_employee.role
        LEFT  JOIN sd_department ON sd_department.department_id = sms_employee.department
        WHERE user_account.employee_id = :employeeid
        AND   sms_employee.status      = 'active'
        LIMIT 1
    ");
    $stmt->bindParam(':employeeid', $employeeid);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ── Success: clear attempt tracking & build session ───────────────────
        unset($_SESSION[$key]);

        $_SESSION['employee_id']     = $user['employee_id'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['role_name']       = $user['role_name'];
        $_SESSION['department_id']   = $user['department'];        // 👈 fixed
        $_SESSION['department_name'] = $user['department_name'];   // 👈 fixed

        $redirectMap = [
        1 => 'modules/school-directress/index.php',
        2 => 'modules/enrollment/index.php',
        3 => 'modules/registrar/',
        4 => 'modules/clinic/index.php',
        5 => 'modules/library/index.php',
        6 => 'modules/laboratory/',
        7 => 'modules/monitoring/index.php',
        8 => 'modules/guidance/index.php',
        9 => 'modules/college-coor/index.php',
    ];

        $role = (int) $user['role'];

        if (!isset($redirectMap[$role])) {
            echo json_encode(['success' => false, 'locked' => false, 'message' => 'Invalid role.']);
            exit();
        }

        echo json_encode([
            'success'  => true,
            'redirect' => $redirectMap[$role],
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