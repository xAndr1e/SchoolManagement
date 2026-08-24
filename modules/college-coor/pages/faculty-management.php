<?php
ob_start();
require_once(__DIR__ . '/../classes/FacultyProfileManager.php');
require_once(__DIR__ . '/../../../database/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$conn = $database->getConnection();
$facultyManager = new FacultyProfileManager($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }

    if ($input && isset($input['action']) && $input['action'] === 'get_faculty_profile' && isset($input['faculty_id'])) {
        $facultyId = (int)$input['faculty_id'];
        $profile = $facultyManager->getFacultyProfileWithDocuments($facultyId);

        if ($profile) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'profile' => $profile
            ]);
            exit;
        } else {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Faculty not found'
            ]);
            exit;
        }
    }

    if (isset($input['action']) && $input['action'] === 'get_faculty_shift_schedule' && isset($input['employee_id'])) {
        $employeeId = (int)$input['employee_id'];
        $schedule = $facultyManager->getFacultyShiftScheduleByEmployeeId($employeeId);

        if ($schedule) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'schedule' => $schedule
            ]);
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No active shift schedule found.'
        ]);
        exit;
    }

    if (isset($input['action'])) {
        $action = $input['action'];
        $response = ['success' => false, 'message' => 'Invalid action'];

        if ($action === 'get_employee_engagement_type' && isset($input['employee_id'])) {
            $employeeId = (int)$input['employee_id'];
            $employee = $facultyManager->getEmployeeById($employeeId);

            if (!$employee) {
                $response = ['success' => false, 'message' => 'Employee not found.'];
            } elseif ((int)($employee['is_archived'] ?? 1) !== 0 || ($employee['employment_status'] ?? '') !== 'Active') {
                $response = ['success' => false, 'message' => 'Employee is not active.'];
            } elseif (!in_array(($employee['employment_type'] ?? ''), ['Part-time', 'OJT/Training'], true)) {
                $response = ['success' => false, 'message' => 'Employee is not eligible for engagement.'];
            } else {
                $response = ['success' => true, 'employment_type' => $employee['employment_type']];
            }
        }

        if ($action === 'add_engagement') {
            error_log('add_engagement handler: received POST request');
            error_log('add_engagement input: ' . json_encode($input));

            $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;
            $title = isset($input['title']) ? trim((string)$input['title']) : '';
            $organization = isset($input['organization']) ? trim((string)$input['organization']) : '';
            $startDate = isset($input['start_date']) ? trim((string)$input['start_date']) : null;
            $endDate = isset($input['end_date']) ? trim((string)$input['end_date']) : null;

            error_log('add_engagement parsed: ID=' . $employeeId . ', title=' . $title . ', org=' . $organization . ', start=' . $startDate . ', end=' . $endDate);

            if ($employeeId <= 0 || $title === '' || $organization === '' || !$startDate || !$endDate) {
                error_log('add_engagement: Validation failed - missing fields');
                $response = ['success' => false, 'message' => 'Missing required fields.'];
            } else {
                $selectedEmployee = $facultyManager->getEmployeeById($employeeId);
                error_log('add_engagement: Employee lookup - found: ' . ($selectedEmployee ? 'yes' : 'no'));

                if (!$selectedEmployee) {
                    error_log('add_engagement: Employee not found');
                    $response = ['success' => false, 'message' => 'Selected employee could not be found.'];
                } elseif ((int)($selectedEmployee['is_archived'] ?? 1) !== 0 || ($selectedEmployee['employment_status'] ?? '') !== 'Active') {
                    error_log('add_engagement: Employee not active - is_archived=' . ($selectedEmployee['is_archived'] ?? 1) . ', status=' . ($selectedEmployee['employment_status'] ?? ''));
                    $response = ['success' => false, 'message' => 'Selected employee is not active and cannot be assigned an engagement.'];
                } elseif (!in_array(($selectedEmployee['employment_type'] ?? ''), ['Part-time', 'OJT/Training'], true)) {
                    error_log('add_engagement: Employee not eligible - employment_type=' . ($selectedEmployee['employment_type'] ?? ''));
                    $response = ['success' => false, 'message' => 'Selected employee is not eligible for an engagement.'];
                } elseif ($facultyManager->hasActiveEngagementForEmployee($employeeId)) {
                    error_log('add_engagement: Employee already has active engagement');
                    $response = ['success' => false, 'message' => 'This employee already has an active engagement record. Duplicate active engagements are not allowed.'];
                } else {
                    error_log('add_engagement: All validations passed, calling addEngagement()');
                    $engagementType = $selectedEmployee['employment_type'];
                    $data = [
                        'employee_id' => $employeeId,
                        'engagement_type' => $engagementType,
                        'title' => $title,
                        'organization' => $organization,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'Pending',
                        'outcome' => 'Not Applicable',
                    ];

                    $insertId = $facultyManager->addEngagement($data);
                    if ($insertId) {
                        error_log('add_engagement: SUCCESS - inserted with ID: ' . $insertId);
                        $response = ['success' => true, 'message' => 'Engagement created successfully.', 'engagement_id' => $insertId];
                    } else {
                        error_log('add_engagement: addEngagement() returned false');
                        $response = ['success' => false, 'message' => 'Unable to create engagement.'];
                    }
                }
            }
        }

        if ($action === 'approve_engagement' && isset($input['engagement_id'])) {
            $engagementId = (int)$input['engagement_id'];
            $approvedBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($input['approved_by']) ? (int)$input['approved_by'] : 0);
            $response = $facultyManager->approveEngagement($engagementId, $approvedBy)
                ? ['success' => true, 'message' => 'Engagement approved successfully.']
                : ['success' => false, 'message' => 'Unable to approve engagement.'];
        }

        if ($action === 'mark_completed_engagement' && isset($input['engagement_id'])) {
            $engagementId = (int)$input['engagement_id'];
            $outcome = isset($input['outcome']) ? trim((string)$input['outcome']) : 'Not Applicable';
            $allowedOutcomes = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
            if (!in_array($outcome, $allowedOutcomes, true)) {
                $outcome = 'Not Applicable';
            }

            $response = $facultyManager->markEngagementCompleted($engagementId, $outcome)
                ? ['success' => true, 'message' => 'Engagement marked completed successfully.']
                : ['success' => false, 'message' => 'Unable to mark engagement as completed.'];
        }

        if ($action === 'generate_engagement_certificate' && isset($input['engagement_id'])) {
            $engagementId = (int)$input['engagement_id'];
            $response = $facultyManager->generateEngagementCertificate($engagementId)
                ? ['success' => true, 'message' => 'Certificate timestamp saved successfully.']
                : ['success' => false, 'message' => 'Unable to generate certificate.'];
        }

        if ($action === 'archive_engagement' && isset($input['engagement_id'])) {
            $engagementId = (int)$input['engagement_id'];
            $response = $facultyManager->archiveEngagement($engagementId)
                ? ['success' => true, 'message' => 'Engagement archived successfully.']
                : ['success' => false, 'message' => 'Unable to archive engagement.'];
        }

        if ($action === 'restore_engagement' && isset($input['engagement_id'])) {
            $engagementId = (int)$input['engagement_id'];
            $response = $facultyManager->restoreEngagement($engagementId)
                ? ['success' => true, 'message' => 'Engagement restored successfully.']
                : ['success' => false, 'message' => 'Unable to restore engagement.'];
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$facultyList = $facultyManager->getAllFacultyCredentials() ?? [];
$educationSummary = $facultyManager->getFacultyEducationSummary() ?? [];
$trainingSummary = $facultyManager->getFacultyTrainingSummary() ?? [];
$eligibleEmployees = $facultyManager->getEligibleEngagementEmployees() ?? [];

$stats = [
    'faculty' => count($facultyList)
];
?>
    <div class="module-header">
        <div>
            <h1><i class="fas fa-chalkboard-teacher"></i> Faculty Management</h1>
            <p>View and manage faculty credentials, attainment, and trainings.</p>
        </div>
    </div>

    <div class="module-content">
        <div class="stats-grid">
        <div class="stat-card faculty">
            <h6>Faculty Members</h6>
            <h3><?php echo $stats['faculty']; ?></h3>
        </div>
    </div>

    <div class="academics-tabs">
        <button class="academics-tab active" data-fm-tab="credentials">
            <i class="fas fa-id-badge"></i> Faculty Credentials
        </button>
        <button class="academics-tab" data-fm-tab="attainment">
            <i class="fas fa-graduation-cap"></i> Educational Attainment
        </button>
        <button class="academics-tab" data-fm-tab="trainings">
            <i class="fas fa-certificate"></i> Trainings & Certifications
        </button>
    </div>

    <div class="academics-panel fm-panel active" id="fm-panel-credentials">
        <div class="section-header">
            <h3>Faculty Credentials</h3>
            <p class="text-muted" style="margin: 0;"><small>Browse faculty account details and employment status placeholders.</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchCredentials" placeholder="Search faculty credentials...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="credentialsTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Employment Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facultyList)): ?>
                            <?php foreach ($facultyList as $faculty): ?>
                                <?php
                                    $middle = '';
                                    if (!empty($faculty['middle_name'])) {
                                        $middle = ' ' . strtoupper(substr(trim($faculty['middle_name']), 0, 1)) . '.';
                                    }
                                    $suffix = !empty($faculty['suffix']) ? ' ' . htmlspecialchars($faculty['suffix']) : '';
                                    $fullName = htmlspecialchars(trim($faculty['first_name'] . $middle . ' ' . $faculty['last_name'] . $suffix));
                                ?>
                                <tr data-faculty-id="<?= (int)$faculty['faculty_id'] ?>"
                                    data-employee-id="<?= (int)($faculty['employee_id'] ?? 0) ?>"
                                    data-employee-code="<?= htmlspecialchars($faculty['employee_code']) ?>"
                                    data-faculty-name="<?= $fullName ?>"
                                    data-department="<?= htmlspecialchars($faculty['department']) ?>"
                                    data-email="<?= htmlspecialchars($faculty['email']) ?>"
                                    data-created-at="<?= htmlspecialchars($faculty['created_at']) ?>"
                                    data-position="<?= htmlspecialchars($faculty['position']) ?>"
                                    data-employment-type="<?= htmlspecialchars($faculty['employment_type']) ?>"
                                    data-employment-status="<?= htmlspecialchars($faculty['employment_status']) ?>">
                                    <td><?= htmlspecialchars($faculty['employee_code']) ?></td>
                                    <td><?= $fullName ?></td>
                                    <td><?= htmlspecialchars($faculty['department']) ?></td>
                                    <td><?= htmlspecialchars($faculty['position']) ?></td>
                                    <td><?= htmlspecialchars($faculty['employment_type']) ?></td>
                                    <td><?= htmlspecialchars($faculty['employment_status']) ?></td>
                                    <td class="actions">
                                        <button type="button" class="btn btn-sm btn-info view-profile-btn">View</button>
                                        <button type="button" class="btn btn-sm btn-secondary shift-schedule-btn" data-employee-id="<?= (int)($faculty['employee_id'] ?? 0) ?>">Shift Schedule</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="7" class="text-center text-muted" style="padding: 20px;">No faculty records available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="academics-panel fm-panel" id="fm-panel-attainment">
        <div class="section-header">
            <h3>Educational Attainment</h3>
            <p class="text-muted" style="margin: 0;"><small>Track each faculty's highest credential.</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchAttainment" placeholder="Search attainment records...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="attainmentTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Highest Degree</th>
                            <th>School</th>
                            <th>Year Graduated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($educationSummary)): ?>
                            <?php foreach ($educationSummary as $education): ?>
                                <?php
                                    $recordsJson = json_encode($education['records'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                ?>
                                <tr data-record-type="education" data-employee-code="<?= htmlspecialchars($education['employee_code']) ?>" data-faculty-name="<?= htmlspecialchars($education['faculty_name']) ?>" data-department="<?= htmlspecialchars($education['department'] ?? 'Not provided') ?>" data-records='<?= htmlspecialchars($recordsJson, ENT_QUOTES, 'UTF-8') ?>'>
                                    <td><?= htmlspecialchars($education['employee_code']) ?></td>
                                    <td><?= htmlspecialchars($education['faculty_name']) ?></td>
                                    <td><?= htmlspecialchars($education['highest_degree']) ?></td>
                                    <td><?= htmlspecialchars($education['school_name']) ?></td>
                                    <td><?= htmlspecialchars($education['year_graduated']) ?></td>
                                    <td class="actions"><button type="button" class="btn btn-sm btn-info view-attainment-btn">View</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="6" class="text-center text-muted" style="padding: 20px;">No educational attainment records yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="academics-panel fm-panel" id="fm-panel-trainings">
        <div class="section-header">
            <h3>Trainings & Certifications</h3>
                <p class="text-muted" style="margin: 0;"><small>Record faculty trainings and certificates.</small></p>
                <div style="margin-top:8px;"><button id="addEngagementBtn" type="button" class="btn btn-sm btn-primary">Add Engagement</button></div>
        </div>

        <div class="search-filter-container training-filter-container">
            <div class="search-box">
                <input type="text" id="searchTrainings" placeholder="Search trainings and certifications...">
            </div>
            <div class="training-status-filters" role="group" aria-label="Training status filter">
                <button type="button" class="training-status-filter active" data-training-filter="all">All</button>
                <button type="button" class="training-status-filter" data-training-filter="active">Active</button>
                <button type="button" class="training-status-filter" data-training-filter="completed">Completed</button>
                <button type="button" class="training-status-filter" data-training-filter="archived">Archived</button>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="trainingsTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Faculty Name</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Organization</th>
                            <th>Date/Period</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($trainingSummary)): ?>
                            <?php foreach ($trainingSummary as $training): ?>
                                <?php
                                    $recordsJson = json_encode($training['records'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                ?>
                                <tr data-record-type="<?= htmlspecialchars($training['record_type'] ?? 'empty') ?>"
                                    data-record-source="<?= htmlspecialchars($training['record_source'] ?? '') ?>"
                                    data-record-id="<?= htmlspecialchars((string)($training['record_id'] ?? '')) ?>"
                                    data-record-status="<?= htmlspecialchars($training['status'] ?? '') ?>"
                                    data-record-outcome="<?= htmlspecialchars($training['outcome'] ?? '') ?>"
                                    data-employee-code="<?= htmlspecialchars($training['employee_code'] ?? '') ?>"
                                    data-faculty-name="<?= htmlspecialchars($training['faculty_name'] ?? '') ?>"
                                    data-employment-type="<?= htmlspecialchars($training['employment_type'] ?? 'N/A') ?>"
                                    data-employment-status="<?= htmlspecialchars($training['employment_status'] ?? 'N/A') ?>"
                                    data-records='<?= htmlspecialchars($recordsJson, ENT_QUOTES, 'UTF-8') ?>'>
                                    <td><?= htmlspecialchars($training['employee_code'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($training['faculty_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($training['type'] ?? 'No record yet') ?></td>
                                    <td><?= htmlspecialchars($training['title'] ?? 'No record yet') ?></td>
                                    <td><?= htmlspecialchars($training['organization'] ?? 'No record yet') ?></td>
                                    <td><?= htmlspecialchars($training['date_period'] ?? 'No record yet') ?></td>
                                    <td><?= htmlspecialchars($training['status'] ?? 'No record yet') ?></td>
                                    <td class="actions">
                                        <button type="button" class="btn btn-sm btn-info view-training-btn">View</button>
                                        <?php if (($training['record_source'] ?? '') === 'cc_certification_engagements'): ?>
                                            <?php
                                                $statusValue = $training['status'] ?? '';
                                                $workflowButtons = '';

                                                if ($statusValue === 'Pending') {
                                                    $workflowButtons .= '<button type="button" class="btn btn-sm btn-success approve-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Approve</button>';
                                                } elseif (in_array($statusValue, ['Approved', 'Ongoing'], true)) {
                                                    $workflowButtons .= '<button type="button" class="btn btn-sm btn-warning complete-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Mark Completed</button>';
                                                } elseif ($statusValue === 'Completed') {
                                                    $workflowButtons .= '<button type="button" class="btn btn-sm btn-primary generate-certificate-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Generate Certificate</button>';
                                                    $workflowButtons .= '<button type="button" class="btn btn-sm btn-secondary archive-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Archive</button>';
                                                } elseif ($statusValue === 'Archived') {
                                                    $workflowButtons .= '<button type="button" class="btn btn-sm btn-success restore-engagement-btn" data-engagement-id="' . (int)($training['record_id'] ?? 0) . '">Restore</button>';
                                                }

                                                echo $workflowButtons;
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="8" class="text-center text-muted" style="padding: 20px;">No trainings or certifications recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="fmAddEngagementModal" class="modal">
    <div class="modal-dialog" style="max-width:560px;">
        <div class="modal-header">
            <h5 class="modal-title">Add Engagement</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addEngagementForm" data-custom-submit>
                <div class="form-group">
                    <label for="aeEmployee">Employee</label>
                    <select id="aeEmployee" name="employee_id" class="form-control" required>
                        <option value="">Select employee</option>
                        <?php foreach ($eligibleEmployees as $f): ?>
                            <?php $fullname = htmlspecialchars(trim($f['first_name'] . ' ' . ($f['middle_name'] ?? '') . ' ' . $f['last_name'] . ' ' . ($f['suffix'] ?? ''))); ?>
                            <option value="<?= (int)$f['employee_id'] ?>" data-employment-type="<?= htmlspecialchars($f['employment_type'] ?? '') ?>" data-position="<?= htmlspecialchars($f['position'] ?? '') ?>"><?= htmlspecialchars($f['employee_code']) ?> - <?= $fullname ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="aeType">Engagement Type</label>
                    <input id="aeType" name="engagement_type" class="form-control" value="" readonly disabled />
                </div>

                <div class="form-group">
                    <label for="aeTitle">Title</label>
                    <input id="aeTitle" name="title" class="form-control" required />
                </div>

                <div class="form-group">
                    <label for="aeOrg">Organization</label>
                    <input id="aeOrg" name="organization" class="form-control" required />
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="aeStart">Start Date</label>
                        <input id="aeStart" name="start_date" type="date" class="form-control" required />
                    </div>
                    <div class="form-group col-md-6">
                        <label for="aeEnd">End Date</label>
                        <input id="aeEnd" name="end_date" type="date" class="form-control" required />
                    </div>
                </div>

                <div style="margin-top:12px; text-align:right;">
                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Engagement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="fmProfileModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Faculty Profile</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr><th scope="row">Employee ID</th><td id="fmModalEmployeeId"></td></tr>
                        <tr><th scope="row">Faculty Name</th><td id="fmModalFacultyName"></td></tr>
                        <tr><th scope="row">Department</th><td id="fmModalDepartment"></td></tr>
                        <tr><th scope="row">Email</th><td id="fmModalEmail"></td></tr>
                        <tr><th scope="row">Date Created</th><td id="fmModalCreatedAt"></td></tr>
                        <tr><th scope="row">Position</th><td id="fmModalPosition"></td></tr>
                        <tr><th scope="row">Employment Type</th><td id="fmModalEmploymentType"></td></tr>
                        <tr><th scope="row">Status</th><td id="fmModalStatus"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<div id="fmEducationModal" class="modal">
    <div class="modal-dialog" style="max-width: 760px;">
        <div class="modal-header">
            <h5 class="modal-title">Educational Attainment</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="padding: 10px 0;">
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; font-size: 14px;">
                    <div>
                        <small style="color: #666;">Employee ID</small>
                        <div id="fmEducationEmployeeId" style="font-weight: 600;">N/A</div>
                    </div>
                    <div>
                        <small style="color: #666;">Faculty Name</small>
                        <div id="fmEducationFacultyName" style="font-weight: 600;">N/A</div>
                    </div>
                    <div>
                        <small style="color: #666;">Department</small>
                        <div id="fmEducationDepartment" style="font-weight: 600;">N/A</div>
                    </div>
                </div>

                <h6 style="font-weight: 700; margin: 10px 0 8px; border-bottom: 1px solid #e1e1e1; padding-bottom: 8px;">EDUCATIONAL HISTORY</h6>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Degree / Program</th>
                                <th>School / Institution</th>
                                <th>Year Graduated</th>
                                <th>Education Level</th>
                            </tr>
                        </thead>
                        <tbody id="fmEducationHistoryBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted" style="padding: 18px;">No educational attainment records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<div id="fmTrainingModal" class="modal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Training & Certification</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="padding: 10px 0;">
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; font-size: 14px;">
                    <div>
                        <small style="color: #666;">Employee ID</small>
                        <div id="fmTrainingEmployeeId" style="font-weight: 600;">N/A</div>
                    </div>
                    <div>
                        <small style="color: #666;">Faculty Name</small>
                        <div id="fmTrainingFacultyName" style="font-weight: 600;">N/A</div>
                    </div>
                    <div>
                        <small style="color: #666;">Employment Type</small>
                        <div id="fmTrainingEmploymentType" style="font-weight: 600;">N/A</div>
                    </div>
                    <div>
                        <small style="color: #666;">Employment Status</small>
                        <div id="fmTrainingEmploymentStatus" style="font-weight: 600;">N/A</div>
                    </div>
                </div>

                <h6 style="font-weight: 700; margin: 10px 0 8px; border-bottom: 1px solid #e1e1e1; padding-bottom: 8px;">TRAINING & CERTIFICATION HISTORY</h6>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Organization</th>
                                <th>Date / Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="fmTrainingHistoryBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 18px;">No training or certification records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<div id="fmShiftScheduleModal" class="modal">
    <div class="modal-dialog" style="max-width: 760px;">
        <div class="modal-header">
            <h5 class="modal-title">Shift Schedule</h5>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="fmShiftScheduleEmpty" class="text-muted" style="display:none;">No active shift schedule found.</div>

            <div id="fmShiftScheduleContent" style="display:none;">
                <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; margin-bottom:18px; font-size:14px;">
                    <div>
                        <small style="color:#666;">Faculty Name</small>
                        <div id="fmShiftFacultyName" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">Employee ID</small>
                        <div id="fmShiftEmployeeId" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">Department</small>
                        <div id="fmShiftDepartment" style="font-weight:600;">N/A</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; margin-bottom:18px; font-size:14px;">
                    <div>
                        <small style="color:#666;">Shift Name</small>
                        <div id="fmShiftName" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">Start Time</small>
                        <div id="fmShiftStartTime" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">End Time</small>
                        <div id="fmShiftEndTime" style="font-weight:600;">N/A</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; margin-bottom:18px; font-size:14px;">
                    <div>
                        <small style="color:#666;">Break Duration</small>
                        <div id="fmShiftBreakDuration" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">Effective From</small>
                        <div id="fmShiftEffectiveFrom" style="font-weight:600;">N/A</div>
                    </div>
                    <div>
                        <small style="color:#666;">Effective To</small>
                        <div id="fmShiftEffectiveTo" style="font-weight:600;">N/A</div>
                    </div>
                </div>

                <div style="margin-bottom:12px; font-size:14px;">
                    <small style="color:#666;">Status</small>
                    <div id="fmShiftStatus" style="font-weight:600;">N/A</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Start</th>
                                <th>Break Start</th>
                                <th>Break End</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody id="fmShiftWeeklyBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

    

<script src="js/modules/faculty-management.js"></script>
<link rel="stylesheet" href="css/pages/faculty-management.css">
