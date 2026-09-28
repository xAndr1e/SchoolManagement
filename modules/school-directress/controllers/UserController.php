<?php

include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../../../auth/guard.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_employees':
        getEmployees();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
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
                e.employment_status,
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