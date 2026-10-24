<?php
require_once __DIR__ . '/../includes/announcement_auth.php';
require_once __DIR__ . '/../classes/Announcement.php'; // ayusin ang path kung iba

header('Content-Type: application/json');

if (!announcement_can_manage()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not allowed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST only.']);
    exit;
}

if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Invalid session token. Refresh the page.']);
    exit;
}

$model  = new Announcement();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $errors = $model->validate($_POST);
            if ($errors) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }
            $ok = $model->create($_POST, announcement_poster_name());
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Announcement posted.' : 'Could not save.']);
            break;

        case 'archive':
            $ok = $model->archive((int) ($_POST['id'] ?? 0));
            echo json_encode(['success' => $ok]);
            break;

        case 'pin':
            $ok = $model->togglePin((int) ($_POST['id'] ?? 0));
            echo json_encode(['success' => $ok]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
}