<?php
ob_start();

// Roles allowed to archive, restore and view the archive.
// Keep in step with $annArchiveRoles in the view.
const ANN_ARCHIVE_ROLES = ['School Directress'];
const ANN_PER_PAGE      = 10;

function ann_respond(array $payload, int $code = 200) {
    if (ob_get_length()) {
        ob_clean(); // discard any stray warnings/notices before the JSON
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server error.']);
    }
});

try {
    include_once __DIR__ . '/../../../auth/session.php';
    require_once __DIR__ . '/../classes/Announcement.php';

    // Authorization on every request, not just in the view
    if (!in_array((string) ($_SESSION['role_name'] ?? ''), ANN_ARCHIVE_ROLES, true)) {
        ann_respond(['success' => false, 'message' => 'You are not authorized to manage the announcement archive.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $model  = new Announcement();

    switch ($action) {

        case 'list_archived':
            $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
            $page   = max(1, (int) ($_GET['page'] ?? 1));
            $total  = $model->countArchived($search);
            $pages  = max(1, (int) ceil($total / ANN_PER_PAGE));
            if ($page > $pages) {
                $page = $pages;
            }
            ann_respond([
                'success' => true,
                'data'    => [
                    'rows'     => $model->getArchived($search, ANN_PER_PAGE, ($page - 1) * ANN_PER_PAGE),
                    'total'    => $total,
                    'page'     => $page,
                    'pages'    => $pages,
                    'per_page' => ANN_PER_PAGE,
                ],
            ]);
            break;

        case 'archive':
        case 'restore':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                ann_respond(['success' => false, 'message' => 'Method not allowed.'], 405);
            }

            $expected = (string) ($_SESSION['ann_csrf'] ?? '');
            $sent     = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if ($expected === '' || !hash_equals($expected, $sent)) {
                ann_respond(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.'], 403);
            }

            $id = filter_var($_POST['announcement_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                ann_respond(['success' => false, 'message' => 'Invalid announcement.'], 422);
            }

            if ($action === 'archive') {
                $model->archiveAnnouncement($id, (int) $_SESSION['employee_id']);
                ann_respond(['success' => true, 'message' => 'Announcement archived.']);
            }
            $model->restoreAnnouncement($id);
            ann_respond(['success' => true, 'message' => 'Announcement restored.']);
            break;

        default:
            ann_respond(['success' => false, 'message' => 'Invalid or missing action.'], 400);
    }

} catch (AnnouncementException $e) {
    ann_respond(['success' => false, 'message' => $e->getMessage()], $e->status);
} catch (\Throwable $e) {
    error_log('AnnouncementArchiveController: ' . $e->getMessage());
    ann_respond(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}