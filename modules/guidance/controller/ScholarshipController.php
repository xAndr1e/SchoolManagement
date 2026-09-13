<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Scholarship.php';
include_once __DIR__ . '/../classes/User.php';
// NOTE: paths mirror CasesController.php — adjust depth/folder names if
// ScholarshipController.php doesn't live at the same location.

header('Content-Type: application/json');

// ── Guard: only accept authenticated requests, Guidance counselors only ──────
$userClass = new User();
$userInfo  = $userClass->userSession();

if (!$userInfo) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}
// TODO: if User::userSession() doesn't already scope by department/role,
// add an explicit check here (e.g. $userInfo['department_id'] == 8)
// matching however Directress-only gating is enforced in the SD module.

$scholarshipClass = new Scholarship();
$action = $_REQUEST['action'] ?? '';

// ── Upload config ─────────────────────────────────────────────────────────
const UPLOAD_DIR       = __DIR__ . '/../../../uploads/scholarships/';
const UPLOAD_URL_BASE  = 'uploads/scholarships/'; // relative path stored in DB / served from
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5MB
const ALLOWED_EXT      = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

// ── Router ────────────────────────────────────────────────────────────────
switch ($action) {

    // ------------------------------------------------------------------
    // GET list (filtered + paginated)
    // ------------------------------------------------------------------
    case 'list':
        $filters = [
            'search'            => trim($_GET['search'] ?? ''),
            'status'            => $_GET['status'] ?? '',
            'eligibility_basis' => $_GET['eligibility_basis'] ?? '',
            'coverage_type'     => $_GET['coverage_type'] ?? '',
            'counselor_id'      => $_GET['counselor_id'] ?? '',
        ];
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $pageSize = 10;

        $result        = $scholarshipClass->getList($filters, $page, $pageSize);
        $scholarships  = array_map('formatScholarshipRow', $result['rows']);

        echo json_encode([
            'success' => true,
            'data' => [
                'scholarships' => $scholarships,
                'pagination' => [
                    'total'      => $result['total'],
                    'page'       => $page,
                    'pageSize'   => $pageSize,
                    'count'      => count($scholarships),
                    'totalPages' => (int) ceil($result['total'] / $pageSize),
                ],
            ],
        ]);
        break;

    // ------------------------------------------------------------------
    // GET details
    // ------------------------------------------------------------------
    case 'details':
        $scholarshipId = (int) ($_GET['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $overview = $scholarshipClass->getOverview($scholarshipId);
        if (!$overview) {
            echo json_encode(['success' => false, 'message' => 'Scholarship application not found.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => formatScholarshipDetail($overview)]);
        break;

    // ------------------------------------------------------------------
    // GET counselors (Guidance and Counseling Office department)
    // ------------------------------------------------------------------
    case 'counselors':
        echo json_encode(['success' => true, 'data' => $scholarshipClass->getCounselors()]);
        break;

    // ------------------------------------------------------------------
    // POST create — multipart/form-data (not JSON) because of the
    // optional file attachment. Fields arrive via $_POST, file via $_FILES.
    // ------------------------------------------------------------------
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $required = ['student_number', 'counselor_id', 'scholarship_name', 'coverage_type', 'eligibility_basis'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "{$field} is required."]);
                exit;
            }
        }

        if (!$scholarshipClass->studentExists($_POST['student_number'])) {
            echo json_encode(['success' => false, 'message' => 'Student number not found.']);
            exit;
        }

        $attachmentPath = null;
        $attachmentName = null;
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleUpload($_FILES['attachment']);
            if (!$uploadResult['success']) {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
            $attachmentPath = $uploadResult['path'];
            $attachmentName = $uploadResult['original_name'];
        }

        try {
            $scholarshipId = $scholarshipClass->createApplication([
                'student_number'    => $_POST['student_number'],
                'counselor_id'      => $_POST['counselor_id'],
                'scholarship_name'  => trim($_POST['scholarship_name']),
                'sponsor'           => trim($_POST['sponsor'] ?? ''),
                'award_amount'      => trim($_POST['award_amount'] ?? ''),
                'coverage_type'     => $_POST['coverage_type'],
                'eligibility_basis' => $_POST['eligibility_basis'],
                'justification'     => trim($_POST['justification'] ?? ''),
                'attachment_path'   => $attachmentPath,
                'attachment_name'   => $attachmentName,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Scholarship application submitted.',
                'data' => ['scholarship_id' => $scholarshipId],
            ]);
        } catch (Throwable $e) {
            // If DB insert failed after a file was already saved, clean it
            // up so we don't leave an orphaned upload behind.
            if ($attachmentPath && file_exists(UPLOAD_DIR . basename($attachmentPath))) {
                @unlink(UPLOAD_DIR . basename($attachmentPath));
            }
            echo json_encode(['success' => false, 'message' => 'Failed to submit application.', 'debug' => $e->getMessage()]);
        }
        break;

    // ------------------------------------------------------------------
    // POST mark_under_review — Applied -> Under Review
    // ------------------------------------------------------------------
    case 'mark_under_review':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $ok = $scholarshipClass->markUnderReview($scholarshipId);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Application marked as Under Review.' : 'Could not update — application may not be in Applied status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST review — Under Review -> Approved/Rejected
    // ------------------------------------------------------------------
    case 'review':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        $decision = $input['decision'] ?? '';

        if (!$scholarshipId || !in_array($decision, ['approve', 'reject'], true)) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id and a valid decision (approve/reject) are required.']);
            exit;
        }

        $reviewerId = $userInfo['employee_id'] ?? (int) ($input['reviewer_id'] ?? 0);
        if (!$reviewerId) {
            echo json_encode(['success' => false, 'message' => 'Could not determine reviewer identity.']);
            exit;
        }

        $ok = $scholarshipClass->reviewApplication($scholarshipId, $decision, $reviewerId, trim($input['notes'] ?? ''));
        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ('Application ' . ($decision === 'approve' ? 'approved.' : 'rejected.'))
                : 'Could not update — application may not be in Under Review status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST activate — Approved -> Active
    // ------------------------------------------------------------------
    case 'activate':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        if (!$scholarshipId) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id is required.']);
            exit;
        }

        $ok = $scholarshipClass->activate($scholarshipId);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Scholarship activated.' : 'Could not update — application may not be in Approved status.',
        ]);
        break;

    // ------------------------------------------------------------------
    // POST finalize — Active -> Completed/Terminated
    // ------------------------------------------------------------------
    case 'finalize':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $scholarshipId = (int) ($input['scholarship_id'] ?? 0);
        $outcome = $input['outcome'] ?? '';

        if (!$scholarshipId || !in_array($outcome, ['completed', 'terminated'], true)) {
            echo json_encode(['success' => false, 'message' => 'scholarship_id and a valid outcome (completed/terminated) are required.']);
            exit;
        }

        $ok = $scholarshipClass->finalize($scholarshipId, $outcome, trim($input['notes'] ?? ''));
        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ('Scholarship marked as ' . ($outcome === 'completed' ? 'Completed.' : 'Terminated.'))
                : 'Could not update — application may not be in Active status.',
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

// ── Upload helper ────────────────────────────────────────────────────────
function handleUpload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed.'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return ['success' => false, 'message' => 'File exceeds the 5MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        return ['success' => false, 'message' => 'File type not allowed. Accepted: ' . implode(', ', ALLOWED_EXT)];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $filename = $safeName . '_' . time() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Could not save uploaded file.'];
    }

    return [
        'success' => true,
        'path' => UPLOAD_URL_BASE . $filename,
        'original_name' => $file['name'],
    ];
}

// ── Formatting helpers ───────────────────────────────────────────────────
function formatScholarshipRow(array $row): array
{
    $row['applied_at_display']  = date('M d, Y', strtotime($row['applied_at']));
    $row['reviewed_at_display'] = !empty($row['reviewed_at']) ? date('M d, Y', strtotime($row['reviewed_at'])) : null;
    $row['award_amount_display'] = $row['award_amount'] !== null ? number_format((float) $row['award_amount'], 2) : null;
    return $row;
}

function formatScholarshipDetail(array $row): array
{
    $row['applied_at_display']  = date('M d, Y g:i A', strtotime($row['applied_at']));
    $row['reviewed_at_display'] = !empty($row['reviewed_at']) ? date('M d, Y g:i A', strtotime($row['reviewed_at'])) : null;
    $row['award_amount_display'] = $row['award_amount'] !== null ? number_format((float) $row['award_amount'], 2) : null;
    return $row;
}