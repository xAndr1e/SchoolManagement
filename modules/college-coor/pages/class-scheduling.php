<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 3) . '/database/db.php';
require_once dirname(__DIR__) . '/classes/Schedule.php';
require_once dirname(__DIR__) . '/classes/Faculty.php';
require_once dirname(__DIR__) . '/classes/SectionManager.php';

$database = new Database();
$db = $database->getConnection();

// Check database connection
if (!$db) {
    die("Database connection failed");
}

$schedule = new Schedule($db);
$faculty = new Faculty($db);
$section = new SectionManager($db);

// Load active academic periods for the View Schedules filters.
$school_years = $db->query("SELECT id, name FROM rgr_school_years WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$requestedSchoolYear = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : 0;
$school_year = 0;
foreach ($school_years as $schoolYearOption) {
    if ((int)$schoolYearOption['id'] === $requestedSchoolYear) {
        $school_year = $requestedSchoolYear;
        break;
    }
}
$school_year = $school_year ?: (!empty($school_years) ? (int)$school_years[0]['id'] : 0);

$semesterStmt = $db->prepare("SELECT id, name, school_year_id, is_active
    FROM rgr_semesters
    WHERE school_year_id = :school_year_id
      AND is_active = 1
    ORDER BY id");
$semesterStmt->bindValue(':school_year_id', $school_year, PDO::PARAM_INT);
$semesterStmt->execute();
$semesters = $semesterStmt->fetchAll(PDO::FETCH_ASSOC);

$requestedSemester = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;
$semester = 0;
foreach ($semesters as $semesterOption) {
    if ((int)$semesterOption['id'] === $requestedSemester) {
        $semester = $requestedSemester;
        break;
    }
}
$semester = $semester ?: (!empty($semesters) ? (int)$semesters[0]['id'] : 0);
$subjects = $db->query("SELECT id, code, name FROM rgr_subjects ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
$faculty_loads = $db->query("SELECT
    fl.id,
    fl.faculty_id,
    fl.subject_id,
    fl.section_id,
    fl.semester_id,
    fl.school_year_id,
    CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
    sec.section_code
    FROM cc_faculty_load fl
    INNER JOIN cc_faculty f ON f.id = fl.faculty_id
    INNER JOIN cc_sections sec ON sec.id = fl.section_id
    INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
    INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
        AND sem.school_year_id = fl.school_year_id
        AND sem.is_active = 1
    WHERE fl.school_year_id = {$school_year}
      AND fl.semester_id = {$semester}
      AND NOT EXISTS (
          SELECT 1
          FROM cc_schedule cs
          WHERE cs.faculty_load_id = fl.id
            AND cs.schedule_type = 'Class'
            AND cs.status = 'Scheduled'
      )
    ORDER BY f.last_name, f.first_name, sec.section_code, fl.id")->fetchAll(PDO::FETCH_ASSOC);

// Existing Class schedules may still need their Faculty Load displayed in Edit.
$edit_faculty_loads_stmt = $db->prepare("SELECT
    fl.id, fl.faculty_id, fl.subject_id, fl.section_id, fl.semester_id, fl.school_year_id,
    CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
    sec.section_code
    FROM cc_faculty_load fl
    INNER JOIN cc_faculty f ON f.id = fl.faculty_id
    INNER JOIN cc_sections sec ON sec.id = fl.section_id
    INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
    INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
        AND sem.school_year_id = fl.school_year_id
        AND sem.is_active = 1
    ORDER BY f.last_name, f.first_name, sec.section_code, fl.id");
$edit_faculty_loads_stmt->execute();
$edit_faculty_loads = $edit_faculty_loads_stmt->fetchAll(PDO::FETCH_ASSOC);

$getActiveFacultyLoad = static function (PDO $db, int $facultyLoadId, int $schoolYearId, int $semesterId): ?array {
    $stmt = $db->prepare("SELECT fl.faculty_id, fl.subject_id, fl.section_id, fl.semester_id, fl.school_year_id
        FROM cc_faculty_load fl
        INNER JOIN rgr_school_years sy ON sy.id = fl.school_year_id AND sy.is_active = 1
        INNER JOIN rgr_semesters sem ON sem.id = fl.semester_id
            AND sem.school_year_id = fl.school_year_id
            AND sem.is_active = 1
        WHERE fl.id = :faculty_load_id
          AND fl.school_year_id = :school_year_id
          AND fl.semester_id = :semester_id
        LIMIT 1");
    $stmt->bindValue(':faculty_load_id', $facultyLoadId, PDO::PARAM_INT);
    $stmt->bindValue(':school_year_id', $schoolYearId, PDO::PARAM_INT);
    $stmt->bindValue(':semester_id', $semesterId, PDO::PARAM_INT);
    $stmt->execute();
    $load = $stmt->fetch(PDO::FETCH_ASSOC);
    return $load ?: null;
};

// Remaining filter values use IDs from the selected academic period.
$day_of_week = isset($_GET['day_of_week']) ? $_GET['day_of_week'] : '';
$room = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

// Handle form submissions - FIXED HERE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an add schedule submission (either by button or by having room field)
    if (isset($_POST['add_schedule']) || isset($_POST['room_id'])) {
        try {
            // Convert time format from HH:MM to HH:MM:SS
            $start_time = $_POST['start_time'] . ':00';
            $end_time = $_POST['end_time'] . ':00';
            
            // Add new schedule using Schedule class
            $schedule->room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
            $schedule->start_time = $start_time;
            $schedule->end_time = $end_time;
            $schedule->day_of_week = trim($_POST['day_of_week']);
            $schedule->subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
            $schedule->grade_section_id = isset($_POST['grade_section_id']) ? (int)$_POST['grade_section_id'] : 0;
            $schedule->faculty_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
            $schedule->semester_id = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : 0;
            $schedule->school_year_id = isset($_POST['school_year_id']) ? (int)$_POST['school_year_id'] : 0;
            $schedule->schedule_type = isset($_POST['schedule_type']) ? trim($_POST['schedule_type']) : 'Class';
            $schedule->faculty_load_id = isset($_POST['faculty_load_id']) ? (int)$_POST['faculty_load_id'] : 0;

            $submittedClassFields = [
                'faculty_load_id' => 'Faculty Load',
                'subject_id' => 'Subject',
                'grade_section_id' => 'Section',
                'room_id' => 'Room'
            ];

            if (empty($schedule->day_of_week) || empty($schedule->start_time) || empty($schedule->end_time)) {
                throw new Exception('Day, start time, and end time are required.');
            }

            if ($schedule->schedule_type === 'Break Time') {
                foreach ($submittedClassFields as $field => $label) {
                    if ($field !== 'room_id' && array_key_exists($field, $_POST) && trim((string)$_POST[$field]) !== '') {
                        throw new Exception("Break Time cannot include a {$label}.");
                    }
                }

                // Break Time has no teaching-load relationships, but it may still
                // use a selected room and must retain the directly selected faculty.
                $schedule->faculty_load_id = 0;
                $schedule->subject_id = 0;
                $schedule->grade_section_id = 0;
                $schedule->semester_id = (int)$semester;
                $schedule->school_year_id = (int)$school_year;

                if (empty($schedule->faculty_id)) {
                    throw new Exception('Break Time requires an Assigned Faculty.');
                }

                $facultyStmt = $db->prepare('SELECT id FROM cc_faculty WHERE id = :faculty_id LIMIT 1');
                $facultyStmt->bindValue(':faculty_id', (int)$schedule->faculty_id, PDO::PARAM_INT);
                $facultyStmt->execute();
                if (!$facultyStmt->fetchColumn()) {
                    throw new Exception('The selected Assigned Faculty does not exist.');
                }
            } elseif (empty($schedule->room_id)) {
                throw new Exception('Class schedules require a Room.');
            }

            // If a faculty load was selected, auto-fill faculty, subject, section
            if ($schedule->schedule_type === 'Class') {
                if (empty($schedule->faculty_load_id)) {
                    throw new Exception('Class scheduling requires a Faculty Load.');
                }

                $fld = $getActiveFacultyLoad($db, $schedule->faculty_load_id, $school_year, $semester);
                if (!$fld) {
                    throw new Exception('Class scheduling is only available for the active school year and semester.');
                }

                $schedule->faculty_id = (int)$fld['faculty_id'];
                $schedule->subject_id = (int)$fld['subject_id'];
                $schedule->grade_section_id = (int)$fld['section_id'];
                $schedule->semester_id = (int)$fld['semester_id'];
                $schedule->school_year_id = (int)$fld['school_year_id'];
            }
            
            if ($schedule->create()) {
                echo "<script>alert('Schedule added successfully!'); sessionStorage.setItem('refreshFacultyLoad', 'true'); window.location.href=window.location.href;</script>";
                exit;
            } else {
                $errorInfo = $db->errorInfo();
                throw new Exception("Failed to add schedule. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
    
    if (isset($_POST['update_schedule'])) {
        try {
            // Convert time format from HH:MM to HH:MM:SS
            $start_time = $_POST['start_time'] . ':00';
            $end_time = $_POST['end_time'] . ':00';
            
            // Update schedule using Schedule class
            $schedule->id = (int)$_POST['schedule_id'];
            $schedule->room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
            $schedule->start_time = $start_time;
            $schedule->end_time = $end_time;
            $schedule->day_of_week = trim($_POST['day_of_week']);
            $schedule->subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : null;
            $schedule->grade_section_id = isset($_POST['grade_section_id']) ? (int)$_POST['grade_section_id'] : 0;
            $schedule->faculty_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;
            $schedule->semester_id = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : 0;
            $schedule->school_year_id = isset($_POST['school_year_id']) ? (int)$_POST['school_year_id'] : 0;
            $schedule->schedule_type = isset($_POST['schedule_type']) ? trim($_POST['schedule_type']) : 'Class';
            $schedule->faculty_load_id = isset($_POST['faculty_load_id']) ? (int)$_POST['faculty_load_id'] : 0;

            // If a faculty load was selected, auto-fill faculty, subject, section
            if ($schedule->schedule_type === 'Class') {
                if (empty($schedule->faculty_load_id)) {
                    throw new Exception('Class scheduling requires a Faculty Load.');
                }

                $fld = $getActiveFacultyLoad($db, $schedule->faculty_load_id, $school_year, $semester);
                if (!$fld) {
                    throw new Exception('Class scheduling is only available for the active school year and semester.');
                }

                $schedule->faculty_id = (int)$fld['faculty_id'];
                $schedule->subject_id = (int)$fld['subject_id'];
                $schedule->grade_section_id = (int)$fld['section_id'];
                $schedule->semester_id = (int)$fld['semester_id'];
                $schedule->school_year_id = (int)$fld['school_year_id'];
            }
            
            if ($schedule->update()) {
                echo "<script>alert('Schedule updated successfully!'); sessionStorage.setItem('refreshFacultyLoad', 'true'); window.location.href=window.location.href;</script>";
                exit;
            } else {
                $errorInfo = $db->errorInfo();
                throw new Exception("Failed to update schedule. Error: " . ($errorInfo[2] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
    
    if (isset($_POST['delete_schedule'])) {
        try {
            $schedule->id = (int)$_POST['schedule_id'];
            if ($schedule->delete()) {
                echo "<script>alert('Schedule deleted successfully!'); sessionStorage.setItem('refreshFacultyLoad', 'true'); window.location.href=window.location.href;</script>";
                exit;
            } else {
                throw new Exception("Failed to delete schedule.");
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Get all schedules using the new method
$schedules_result = $schedule->getAllWithFilters($semester, $school_year, $day_of_week, $room);
$schedules = $schedules_result ? $schedules_result->fetchAll(PDO::FETCH_ASSOC) : [];

// Get distinct values for filters using the new methods
$distinct_days_result = $schedule->getDistinctDays($semester, $school_year);
$distinct_days = $distinct_days_result ? $distinct_days_result->fetchAll() : [];

$distinct_rooms_result = $schedule->getDistinctRooms($semester, $school_year);
$distinct_rooms = $distinct_rooms_result ? $distinct_rooms_result->fetchAll() : [];
$all_rooms = $db->query("SELECT id AS room_id, room_name AS room FROM cc_room WHERE status = 'Available' ORDER BY room_name")->fetchAll(PDO::FETCH_ASSOC);

// Get data for dropdowns
$all_faculty = $db->query("SELECT id, faculty_code, first_name, last_name FROM cc_faculty ORDER BY last_name")->fetchAll();
$all_sections = $db->query("SELECT cs.id, cs.section_code, cs.grade_level, c.code AS program FROM cc_sections cs LEFT JOIN rgr_courses c ON cs.program_id = c.id ORDER BY cs.section_code")->fetchAll();

// Calculate statistics using the new method
$stats = $schedule->getStatistics($semester, $school_year);
$total_schedules = $stats['total_schedules'] ?? 0;
$rooms_count = $stats['total_rooms'] ?? 0;
$faculty_count = $stats['total_faculty'] ?? 0;
$sections_count = $stats['total_sections'] ?? 0;

// Get weekly summary
$weekly_summary_result = $schedule->getWeeklySummary($semester, $school_year);
$weekly_summary = $weekly_summary_result ? $weekly_summary_result->fetchAll(PDO::FETCH_ASSOC) : [];

// Create daily summary from weekly_summary
$daily_summary = [];
foreach ($weekly_summary as $item) {
    $daily_summary[$item['day_of_week']] = $item['total_classes'];
}

// Helper maps for display
$semester_map = array_column($semesters, 'name', 'id');
$school_year_map = array_column($school_years, 'name', 'id');
?>
        <!-- Header -->
        <div class="module-header">
            <h1><i class="fas fa-calendar-alt"></i> Schedule Management System</h1>
            <p>Office of the Safety and Security | Class Schedule Management</p>
        </div>

        <div class="module-content">
            <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-check fa-2x" style="color: #4361ee;"></i>
                <div class="stat-value"><?= $total_schedules ?></div>
                <div class="stat-label">Total Classes</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-door-open fa-2x" style="color: #3a0ca3;"></i>
                <div class="stat-value"><?= $rooms_count ?></div>
                <div class="stat-label">Rooms Used</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher fa-2x" style="color: #4cc9f0;"></i>
                <div class="stat-value"><?= $faculty_count ?></div>
                <div class="stat-label">Faculty Scheduled</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users fa-2x" style="color: #f72585;"></i>
                <div class="stat-value"><?= $sections_count ?></div>
                <div class="stat-label">Sections</div>
            </div>
        </div>

        <!-- Daily Summary -->
        <?php if (!empty($daily_summary)): ?>
        <div class="form-card">
            <h3><i class="fas fa-chart-bar"></i> Weekly Schedule Distribution</h3>
            <div class="summary-grid">
                <?php 
                $days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days_order as $day): 
                    $count = $daily_summary[$day] ?? 0;
                ?>
                <div class="summary-item">
                    <div class="summary-day"><?= $day ?></div>
                    <div class="summary-count"><?= $count ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab <?= !isset($_GET['action']) || $_GET['action'] == 'view' ? 'active' : '' ?>" onclick="showTab('view')">
                <i class="fas fa-eye"></i> View Schedules
            </button>
            <button class="tab <?= isset($_GET['action']) && $_GET['action'] == 'add' ? 'active' : '' ?>" onclick="showTab('add')">
                <i class="fas fa-plus-circle"></i> Add Schedule
            </button>
            <button class="tab <?= isset($_GET['action']) && $_GET['action'] == 'proctoring' ? 'active' : '' ?>" onclick="showTab('proctoring')">
                <i class="fas fa-user-shield"></i> Exam Proctoring
            </button>
        </div>

        <!-- Filters -->
        <div id="classScheduleFilters" class="filters">
            <form method="GET" class="filter-group">
                <div class="filter-item">
                    <label><i class="fas fa-graduation-cap"></i> Semester</label>
                    <select name="semester_id">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> School Year</label>
                    <select name="school_year_id" onchange="this.form.submit()">
                        <?php foreach ($school_years as $sy): ?>
                            <option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-calendar-day"></i> Day of Week</label>
                    <select name="day_of_week">
                        <option value="">All Days</option>
                        <?php foreach ($distinct_days as $day): ?>
                            <option value="<?= htmlspecialchars($day['day_of_week']) ?>" <?= $day_of_week == $day['day_of_week'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($day['day_of_week']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label><i class="fas fa-door-open"></i> Room</label>
                    <select name="room_id">
                        <option value="">All Rooms</option>
                        <?php foreach ($distinct_rooms as $room_item): ?>
                            <option value="<?= (int)$room_item['room_id'] ?>" <?= $room == $room_item['room_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room_item['room']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="schedule_manager.php" class="btn btn-warning">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
            <?php if ($school_year && empty($semesters)): ?>
                <div class="alert alert-warning" style="margin-top: 12px;">The selected school year has no active semester. No schedules are available for this academic period.</div>
            <?php endif; ?>
        </div>

        <!-- View Schedules Tab -->
        <div id="view-tab" class="tab-content <?= !isset($_GET['action']) || $_GET['action'] == 'view' ? 'active' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-list"></i> All Schedules (<?= $total_schedules ?>)</h2>
                <div>
                    <button onclick="exportToCSV()" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <label for="facultySelector" style="margin: 0; font-weight: 600;">Select Faculty to Print:</label>
                        <select id="facultySelector" class="form-control" style="width: 250px; flex: 0;">
                            <option value="">-- Select Faculty --</option>
                            <?php 
                                $faculty_list = [];
                                foreach ($schedules as $schedule) {
                                    $faculty_key = (int)($schedule['faculty_id'] ?? 0);
                                    if ($faculty_key && !isset($faculty_list[$faculty_key])) {
                                        $faculty_list[$faculty_key] = $schedule['faculty_code'] ?? 'Unknown';
                                    }
                                }
                                foreach ($faculty_list as $fac_id => $fac_code) {
                                    echo '<option value="' . $fac_id . '">' . htmlspecialchars($fac_code) . '</option>';
                                }
                            ?>
                        </select>
                        <button id="printScheduleBtn" onclick="printSchedule()" class="btn btn-primary">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            
            <?php if ($total_schedules > 0): ?>
                <div class="table-container" id="viewScheduleTableContainer">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time Slot</th>
                                <th>Room</th>
                                <th>Subject</th>
                                <th>Section</th>
                                <th>Faculty</th>
                                <th>Semester</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $row): ?>
                            <tr data-faculty-id="<?= (int)($row['faculty_id'] ?? 0) ?>">
                                <td>
                                    <span class="badge badge-primary"><?= htmlspecialchars($row['day_of_week'] ?? '') ?></span>
                                </td>
                                <td>
                                    <small><?= date('h:i A', strtotime($row['start_time'])) ?> - <?= date('h:i A', strtotime($row['end_time'])) ?></small>
                                </td>
                                <td><span class="badge badge-warning"><?= htmlspecialchars($row['room'] ?? '') ?></span></td>
                                <td><strong><?= htmlspecialchars($row['subject_code'] ?? '') ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($row['section_code'] ?? '') ?><br>
                                    <small><?= htmlspecialchars($row['grade_level'] ?? '') ?> - <?= htmlspecialchars($row['program'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['last_name'] ?? '') ?>, <?= htmlspecialchars($row['first_name'] ?? '') ?><br>
                                    <small><?= htmlspecialchars($row['faculty_code'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($semester_map[$row['semester_id']] ?? ($row['semester'] ?? '')) ?><br>
                                    <small><?= htmlspecialchars($school_year_map[$row['school_year_id']] ?? ($row['school_year'] ?? '')) ?></small>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick='editSchedule(<?= json_encode($row) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteSchedule(<?= $row['id'] ?>)"
                                            title="Delete Schedule">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-calendar-times fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <h3>No schedules found</h3>
                    <p>Try different filters or add new schedules</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Schedule Tab -->
        <div id="add-tab" class="tab-content <?= isset($_GET['action']) && $_GET['action'] == 'add' ? 'active' : '' ?>">
            <h2><i class="fas fa-plus-circle"></i> Add New Schedule</h2>
            
            <form method="POST" class="form-grid" onsubmit="return validateForm()">
                <div class="form-card">
                    <h3>Basic Information</h3>
                    <div class="form-group schedule-type-field" id="add-room-field">
                        <label for="room_id">Room</label>
                        <select id="room_id" name="room_id" required>
                            <option value="">Select Room</option>
                            <?php foreach ($all_rooms as $room_item): ?>
                                <option value="<?= (int)$room_item['room_id'] ?>"><?= htmlspecialchars($room_item['room']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="schedule_type">Schedule Type</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="Class">Class</option>
                            <option value="Break Time">Break Time</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Course Information</h3>
                    <div class="form-group">
                        <label for="day_of_week">Day of Week</label>
                        <select id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-subject-field">
                        <label for="subject_id">Subject</label>
                        <select id="subject_id" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= (int)$sub['id'] ?>"><?= htmlspecialchars($sub['code']) ?> - <?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-section-field">
                        <label for="section_id">Section</label>
                        <select id="section_id" name="grade_section_id" required>
                            <option value="">Select Section</option>
                            <?php foreach ($all_sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>">
                                    <?= htmlspecialchars($sec['section_code']) ?> (<?= htmlspecialchars($sec['grade_level']) ?> - <?= htmlspecialchars($sec['program']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Faculty & Semester</h3>
                    <div class="form-group schedule-type-field" id="add-faculty-load-field">
                        <label for="faculty_load_id">Faculty Load</label>
                        <select id="faculty_load_id" name="faculty_load_id" required>
                            <option value="">-- Select Faculty Load --</option>
                            <?php foreach ($faculty_loads as $fl): ?>
                                <option value="<?= (int)$fl['id'] ?>" data-faculty="<?= (int)$fl['faculty_id'] ?>" data-subject="<?= (int)$fl['subject_id'] ?>" data-section="<?= (int)$fl['section_id'] ?>" data-semester="<?= (int)$fl['semester_id'] ?>" data-school-year="<?= (int)$fl['school_year_id'] ?>">
                                    <?= htmlspecialchars($fl['faculty_name']) ?> | <?= htmlspecialchars($fl['section_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="add-faculty-field">
                        <label for="faculty_id">Assigned Faculty</label>
                        <select id="faculty_id" name="faculty_id" required>
                            <option value="">Select Faculty</option>
                            <?php foreach ($all_faculty as $fac): ?>
                                <option value="<?= (int)$fac['id'] ?>">
                                    <?= htmlspecialchars($fac['last_name']) ?>, <?= htmlspecialchars($fac['first_name']) ?> (<?= htmlspecialchars($fac['faculty_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-semester-field">
                        <label for="semester_id">Semester</label>
                        <select id="semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group schedule-type-field" id="add-school-year-field">
                        <label for="school_year_id">School Year</label>
                        <select id="school_year_id" name="school_year_id" required>
                            <option value="">Select School Year</option>
                            <?php foreach ($school_years as $sy): ?>
                                <option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; text-align: center;">
                    <input type="hidden" name="semester_filter" value="<?= htmlspecialchars($semester) ?>">
                    <input type="hidden" name="school_year_filter" value="<?= htmlspecialchars($school_year) ?>">
                    <button type="submit" name="add_schedule" value="1" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
                <?php if (!empty($all_faculty)): ?>
                    <script id="preload-scheduleAllFaculty" type="application/json">
                        <?= json_encode(array_map(function($f){ return [
                            'id' => (int)($f['id'] ?? $f['faculty_id'] ?? 0),
                            'first_name' => $f['first_name'] ?? '',
                            'last_name' => $f['last_name'] ?? '',
                            'faculty_code' => $f['faculty_code'] ?? ''
                        ]; }, $all_faculty), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>
                    </script>
                <?php endif; ?>
        </div>

        <!-- Exam Proctoring Tab -->
        <div id="proctoring-tab" class="tab-content <?= isset($_GET['action']) && $_GET['action'] == 'proctoring' ? 'active' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
                <h2><i class="fas fa-user-shield"></i> Exam Proctoring Assignment</h2>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-success" onclick="openExamModal()"><i class="fas fa-file-medical"></i> Create Exam</button>
                    <button type="button" class="btn btn-primary" onclick="openExamScheduleModal()"><i class="fas fa-calendar-plus"></i> Add Exam Schedule</button>
                    <button type="button" class="btn btn-primary" onclick="openProctorModal()"><i class="fas fa-plus"></i> Assign Proctor</button>
                    <button type="button" class="btn btn-secondary" onclick="printProctorSchedule()"><i class="fas fa-print"></i> Print Schedule</button>
                </div>
            </div>
            <div class="filters" style="margin-bottom: 20px;">
                <div class="filter-group">
                    <div class="filter-item"><label for="proctorSemesterFilter">Semester</label><select id="proctorSemesterFilter">
                        <option value="">All Semesters</option>
                        <?php foreach ($semesters as $sem): ?><option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="filter-item"><label for="proctorSchoolYearFilter">School Year</label><select id="proctorSchoolYearFilter">
                        <option value="">All School Years</option>
                        <?php foreach ($school_years as $sy): ?><option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="filter-item"><label for="proctorExamFilter">Exam</label><select id="proctorExamFilter"><option value="">All Exams</option></select></div>
                    <div class="filter-item"><label for="proctorStatusFilter">Status</label><select id="proctorStatusFilter"><option value="Assigned" selected>Assigned</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option><option value="">All Statuses</option></select></div>
                    <div class="filter-item"><button type="button" class="btn btn-primary" onclick="loadProctorAssignments()"><i class="fas fa-filter"></i> Apply Filters</button> <button type="button" class="btn btn-warning" onclick="clearProctorFilters()"><i class="fas fa-times"></i> Clear</button></div>
                </div>
            </div>
            <div class="table-container"><table class="data-table" style="min-width: 1250px;"><thead><tr>
                <th>Exam</th><th>Subject</th><th>Section</th><th>Room</th><th>Exam Date</th><th>Time</th><th>Proctor</th><th>Role</th><th>Status</th><th>Actions</th>
            </tr></thead><tbody id="proctorAssignmentsBody"><tr><td colspan="10" style="text-align:center;">Loading assignments...</td></tr></tbody></table></div>
            <h3 style="margin: 25px 0 12px;"><i class="fas fa-calendar-alt"></i> Exam Schedule List</h3>
            <div class="table-container"><table class="data-table" style="min-width: 900px;"><thead><tr>
                <th>Type</th><th>Exam</th><th>Subject</th><th>Section</th><th>Room</th><th>Date</th><th>Time</th><th>Status</th>
            </tr></thead><tbody id="examSchedulesBody"><tr><td colspan="8" style="text-align:center;">Loading schedules...</td></tr></tbody></table></div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="margin-bottom: 10px;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="attendance_form.php" class="btn btn-success">
                    <i class="fas fa-clipboard-check"></i> Mark Attendance
                </a>
                <a href="view_enrollees.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> View Enrollees
                </a>
            </div>
            <p>Schedule Management System &copy; <?= date('Y') ?> | Office of the Safety and Security</p>
        </div>
    </div>

    <!-- Create Examination Modal -->
    <div id="examModal" class="modal">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header"><h2><i class="fas fa-file-medical"></i> Create Examination</h2><button class="close-modal" type="button" onclick="closeExamModal()">&times;</button></div>
            <form id="examForm" onsubmit="submitExam(event)">
                <div class="form-grid">
                    <div class="form-group"><label for="examName">Exam Name</label><input type="text" name="exam_name" id="examName" required></div>
                    <div class="form-group"><label for="examType">Exam Type</label><select name="exam_type" id="examType" required><option value="">Select Exam Type</option><option value="Preliminary">Prelim</option><option value="Midterm">Midterm</option><option value="Final">Final</option><option value="Special">Special</option></select></div>
                    <div class="form-group"><label for="examSchoolYear">School Year</label><select name="school_year_id" id="examSchoolYear" required><?php foreach ($school_years as $sy): ?><option value="<?= (int)$sy['id'] ?>" <?= $school_year == $sy['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sy['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label for="examSemester">Semester</label><select name="semester_id" id="examSemester" required><?php foreach ($semesters as $sem): ?><option value="<?= (int)$sem['id'] ?>" <?= $semester == $sem['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sem['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label for="examStartDate">Start Date</label><input type="date" name="start_date" id="examStartDate" required></div>
                    <div class="form-group"><label for="examEndDate">End Date</label><input type="date" name="end_date" id="examEndDate" required></div>
                    <div class="form-group"><label for="examStatus">Status</label><select name="status" id="examStatus" required><option value="Scheduled">Scheduled</option><option value="Draft">Draft</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align:center; margin-top:15px;"><button type="button" class="btn btn-warning" onclick="closeExamModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Examination</button></div>
            </form>
        </div>
    </div>

    <!-- Add Exam Schedule Modal -->
    <div id="examScheduleModal" class="modal">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header"><h2><i class="fas fa-calendar-plus"></i> Add Exam Schedule</h2><button class="close-modal" type="button" onclick="closeExamScheduleModal()">&times;</button></div>
            <form id="examScheduleForm" onsubmit="submitExamSchedule(event)">
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1;"><label for="scheduleExamId">Exam</label><select name="exam_id" id="scheduleExamId" required></select><small id="scheduleExamContext" style="display:block;margin-top:5px;color:#666;"></small></div>
                    <div class="form-group"><label for="scheduleType">Schedule Type</label><select name="schedule_type" id="scheduleType" required><option value="Exam">Exam</option><option value="Break Time">Break Time</option></select></div>
                    <div id="examScheduleClassFields" style="display: contents;">
                        <div class="form-group"><label for="scheduleSubjectId">Subject</label><select name="subject_id" id="scheduleSubjectId" required></select></div>
                        <div class="form-group"><label for="scheduleSectionId">Section</label><select name="section_id" id="scheduleSectionId" required></select></div>
                        <div class="form-group"><label for="scheduleRoomId">Room</label><select name="room_id" id="scheduleRoomId" required></select></div>
                    </div>
                    <div class="form-group"><label for="scheduleExamDate">Exam Date</label><input type="date" name="exam_date" id="scheduleExamDate" required></div>
                    <div class="form-group"><label for="scheduleStartTime">Start Time</label><input type="time" name="start_time" id="scheduleStartTime" required></div>
                    <div class="form-group"><label for="scheduleEndTime">End Time</label><input type="time" name="end_time" id="scheduleEndTime" required></div>
                    <div class="form-group"><label for="scheduleStatus">Status</label><select name="status" id="scheduleStatus" required><option value="Scheduled">Scheduled</option><option value="Draft">Draft</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align:center; margin-top:15px;"><button type="button" class="btn btn-warning" onclick="closeExamScheduleModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Schedule</button></div>
            </form>
        </div>
    </div>

    <!-- Exam Proctor Modal -->
    <div id="proctorModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header"><h2 id="proctorModalTitle"><i class="fas fa-user-shield"></i> Assign Exam Proctor</h2><button class="close-modal" type="button" onclick="closeProctorModal()">&times;</button></div>
            <form id="proctorForm" onsubmit="submitProctor(event)">
                <input type="hidden" name="id" id="proctorAssignmentId">
                <div class="form-group"><label for="proctorScheduleId">Exam Schedule</label><select name="exam_schedule_id" id="proctorScheduleId" required><option value="">Select Exam Schedule</option></select></div>
                <div id="proctorScheduleInfo" class="form-card" style="margin-bottom: 15px; min-height: 20px;"></div>
                <div class="form-grid" style="margin-bottom: 10px;">
                    <div class="form-group"><label for="proctorFacultyId">Faculty / Proctor</label><select name="faculty_id" id="proctorFacultyId" required><option value="">Select Faculty</option></select></div>
                    <div class="form-group"><label for="proctorRole">Role</label><select name="role" id="proctorRole" required><option value="Proctor">Proctor</option><option value="Lead Proctor">Lead Proctor</option><option value="Reliever">Reliever</option></select></div>
                    <div class="form-group"><label for="proctorStatus">Status</label><select name="status" id="proctorStatus" required><option value="Assigned">Assigned</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Cancelled">Cancelled</option></select></div>
                </div>
                <div style="text-align: center; margin-top: 15px;"><button type="button" class="btn btn-warning" onclick="closeProctorModal()">Cancel</button> <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Assignment</button></div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Schedule</h2>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" id="editForm" class="form-grid" onsubmit="return validateEditForm()">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                
                <div class="form-card">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label>Room</label>
                        <select name="room_id" id="edit_room" required>
                            <option value="">Select Room</option>
                            <?php foreach ($all_rooms as $room_item): ?>
                                <option value="<?= (int)$room_item['room_id'] ?>"><?= htmlspecialchars($room_item['room']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Schedule Type</label>
                        <select name="schedule_type" id="edit_schedule_type">
                            <option value="Class">Class</option>
                            <option value="Break Time">Break Time</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" required>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Course Information</h3>
                    <div class="form-group">
                        <label>Day of Week</label>
                        <select name="day_of_week" id="edit_day_of_week" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject_id" id="edit_subject_id" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= (int)$sub['id'] ?>"><?= htmlspecialchars($sub['code']) ?> - <?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Section</label>
                        <select name="grade_section_id" id="edit_grade_section_id" required>
                            <?php foreach ($all_sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>">
                                    <?= htmlspecialchars($sec['section_code']) ?> (<?= htmlspecialchars($sec['grade_level']) ?> - <?= htmlspecialchars($sec['program']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Faculty & Semester</h3>
                    <div class="form-group">
                        <label>Faculty Load (optional)</label>
                        <select name="faculty_load_id" id="edit_faculty_load_id">
                            <option value="">-- Select Faculty Load --</option>
                            <?php foreach ($edit_faculty_loads as $fl): ?>
                                <option value="<?= (int)$fl['id'] ?>" data-faculty="<?= (int)$fl['faculty_id'] ?>" data-subject="<?= (int)$fl['subject_id'] ?>" data-section="<?= (int)$fl['section_id'] ?>" data-semester="<?= (int)$fl['semester_id'] ?>" data-school-year="<?= (int)$fl['school_year_id'] ?>"><?= htmlspecialchars($fl['faculty_name']) ?> | <?= htmlspecialchars($fl['section_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigned Faculty</label>
                        <select name="faculty_id" id="edit_faculty_id" required>
                            <?php foreach ($all_faculty as $fac): ?>
                                <option value="<?= (int)$fac['id'] ?>"><?= htmlspecialchars($fac['last_name']) ?>, <?= htmlspecialchars($fac['first_name']) ?> (<?= htmlspecialchars($fac['faculty_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester_id" id="edit_semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= (int)$sem['id'] ?>"><?= htmlspecialchars($sem['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>School Year</label>
                        <select name="school_year_id" id="edit_school_year_id" required>
                            <option value="">Select School Year</option>
                            <?php foreach ($school_years as $sy): ?>
                                <option value="<?= (int)$sy['id'] ?>"><?= htmlspecialchars($sy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; text-align: center; margin-top: 20px;">
                    <button type="submit" name="update_schedule" value="1" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Schedule
                    </button>
                    <button type="button" class="btn btn-warning" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
        <!-- Statistics -->
        

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            const classScheduleFilters = document.getElementById('classScheduleFilters');
            if (classScheduleFilters) {
                classScheduleFilters.style.display = tabName === 'proctoring' ? 'none' : '';
            }
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Update URL parameter without reload
            const url = new URL(window.location);
            url.searchParams.set('action', tabName);
            window.history.pushState({}, '', url);
        }

        // Set active tab based on URL
        const urlParams = new URLSearchParams(window.location.search);
        const actionParam = urlParams.get('action');
        const classScheduleFilters = document.getElementById('classScheduleFilters');
        if (classScheduleFilters && actionParam === 'proctoring') {
            classScheduleFilters.style.display = 'none';
        }
        if (actionParam && ['view', 'add', 'proctoring'].includes(actionParam)) {
            // Don't reload, just update active states
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(actionParam + '-tab').classList.add('active');
            document.querySelectorAll('.tab').forEach((tab, index) => {
                if ((actionParam === 'view' && index === 0) ||
                    (actionParam === 'add' && index === 1) ||
                    (actionParam === 'proctoring' && index === 2)) {
                    tab.classList.add('active');
                }
            });
        }

        const proctorApi = '/sms/modules/college-coor/api/exam_proctoring.php';
        let proctorExamSchedules = [];
        let allProctorExamSchedules = [];
        let proctorFaculty = [];
        let proctorExams = [];

        function proctorEscape(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
        }

        function formatProctorTime(value) {
            const parts = String(value || '').split(':');
            if (parts.length < 2) return '';
            const hour24 = Number(parts[0]);
            const minute = parts[1];
            return `${hour24 % 12 || 12}:${minute} ${hour24 >= 12 ? 'PM' : 'AM'}`;
        }

        function formatProctorDate(value) {
            if (!value) return '';
            const date = new Date(`${value}T00:00:00`);
            return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function selectedProctorPeriodParams() {
            return new URLSearchParams({
                semester_id: document.getElementById('proctorSemesterFilter')?.value || '',
                school_year_id: document.getElementById('proctorSchoolYearFilter')?.value || ''
            });
        }

        async function loadProctorReferenceData() {
            const params = selectedProctorPeriodParams();
            const [scheduleResponse, facultyResponse, examResponse, subjectResponse, sectionResponse, roomResponse] = await Promise.all([
                fetch(`${proctorApi}?action=exam-schedules&${params}`),
                fetch(`${proctorApi}?action=faculties`),
                fetch(`${proctorApi}?action=exams`),
                fetch(`${proctorApi}?action=subjects`),
                fetch(`${proctorApi}?action=sections`),
                fetch(`${proctorApi}?action=rooms`)
            ]);
            const scheduleData = await scheduleResponse.json();
            const facultyData = await facultyResponse.json();
            const examData = await examResponse.json();
            const subjectData = await subjectResponse.json();
            const sectionData = await sectionResponse.json();
            const roomData = await roomResponse.json();
            if (!scheduleData.success || !facultyData.success || !examData.success || !subjectData.success || !sectionData.success || !roomData.success) throw new Error('Unable to load proctoring reference data.');
            allProctorExamSchedules = scheduleData.exam_schedules || [];
            proctorExamSchedules = allProctorExamSchedules.filter(item => item.schedule_type === 'Exam');
            proctorFaculty = facultyData.faculties || [];
            proctorExams = examData.exams || [];
            const examFilter = document.getElementById('proctorExamFilter');
            const currentExam = examFilter.value;
            const exams = [...new Map(proctorExamSchedules.map(item => [item.exam_id, item.exam_name])).entries()];
            examFilter.innerHTML = '<option value="">All Exams</option>' + exams.map(([id, name]) => `<option value="${proctorEscape(id)}">${proctorEscape(name)}</option>`).join('');
            if (exams.some(([id]) => String(id) === currentExam)) examFilter.value = currentExam;
            const scheduleSelect = document.getElementById('proctorScheduleId');
            if (scheduleSelect) {
                // Group exam schedules by exam_id + exam_date + room_id + section_id
                const groups = {};
                proctorExamSchedules.forEach(item => {
                    const key = [item.exam_id, item.exam_date, item.room_id, item.section_id].join('::');
                    if (!groups[key]) groups[key] = { items: [], exam_name: item.exam_name, exam_id: item.exam_id, exam_date: item.exam_date, room_name: item.room_name, section_code: item.section_code };
                    groups[key].items.push(item);
                });
                const groupOptions = Object.values(groups).map(group => {
                    const count = group.items.length;
                    const sampleId = group.items[0].id;
                    const label = `${proctorEscape(group.exam_name)} | ${proctorEscape(group.section_code || '')} | ${proctorEscape(formatProctorDate(group.exam_date))} (${count} Exam${count>1?'s':''})`;
                    return { id: sampleId, label };
                });
                scheduleSelect.innerHTML = '<option value="">Select Exam Schedule</option>' + groupOptions.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            }
            const scheduleBody = document.getElementById('examSchedulesBody');
            if (scheduleBody) {
                // Group schedules visually by exam_id + exam_date + room_id + section_id
                const groups = {};
                allProctorExamSchedules.forEach(item => {
                    const key = [item.exam_id, item.exam_date, item.room_id, item.section_id].join('::');
                    if (!groups[key]) groups[key] = { items: [], exam_name: item.exam_name, exam_id: item.exam_id, exam_date: item.exam_date, room_name: item.room_name, section_code: item.section_code };
                    groups[key].items.push(item);
                });
                // Create ordered list of groups using desired sort order
                const orderedGroups = Object.values(groups).sort((a, b) => {
                    if (a.exam_date !== b.exam_date) return a.exam_date.localeCompare(b.exam_date);
                    if (a.exam_name !== b.exam_name) return a.exam_name.localeCompare(b.exam_name);
                    if ((a.room_name || '') !== (b.room_name || '')) return (a.room_name || '').localeCompare(b.room_name || '');
                    return (a.section_code || '').localeCompare(b.section_code || '');
                });
                const rows = orderedGroups.map(group => {
                    // sort schedules inside group by start_time
                    group.items.sort((x, y) => (x.start_time || '').localeCompare(y.start_time || ''));
                    const header = `<tr class="group-header"><td colspan="8" style="padding:8px 10px;background:#f4f6f8;font-weight:700;">${proctorEscape(formatProctorDate(group.exam_date))} &nbsp;|&nbsp; ${proctorEscape(group.exam_name)} &nbsp;|&nbsp; ${proctorEscape(group.room_name || '')} &nbsp;|&nbsp; ${proctorEscape(group.section_code || '')}</td></tr>`;
                    const itemRows = group.items.map(item => {
                        const isBreak = item.schedule_type === 'Break Time';
                        return `<tr><td><strong>${proctorEscape(item.schedule_type)}</strong></td><td>${proctorEscape(item.exam_name)}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.subject_code || '')}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.section_code || '')}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.room_name || '')}</td><td>${proctorEscape(formatProctorDate(item.exam_date))}</td><td>${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}</td><td>${proctorEscape(item.status || '')}</td></tr>`;
                    }).join('');
                    return header + itemRows;
                }).join('');
                scheduleBody.innerHTML = rows || '<tr><td colspan="8" style="text-align:center;">No exam schedules found.</td></tr>';
            }
            const examSelect = document.getElementById('scheduleExamId');
            if (examSelect) {
                examSelect.innerHTML = '<option value="">Select Examination</option>' + proctorExams.map(item => `<option value="${item.id}">${proctorEscape(item.exam_name)} (${proctorEscape(item.school_year_name || '')} / ${proctorEscape(item.semester_name || '')})</option>`).join('');
            }
            const fillSelect = (id, placeholder, items, label) => {
                const select = document.getElementById(id);
                if (select) select.innerHTML = `<option value="">${placeholder}</option>` + items.map(item => `<option value="${item.id}">${proctorEscape(label(item))}</option>`).join('');
            };
            fillSelect('scheduleSubjectId', 'Select Subject', subjectData.subjects || [], item => `${item.code} - ${item.name}`);
            fillSelect('scheduleSectionId', 'Select Section', sectionData.sections || [], item => item.section_code);
            fillSelect('scheduleRoomId', 'Select Room', roomData.rooms || [], item => item.room_name);
        }

        async function loadProctorAssignments() {
            const body = document.getElementById('proctorAssignmentsBody');
            if (!body) return;
            body.innerHTML = '<tr><td colspan="10" style="text-align:center;">Loading assignments...</td></tr>';
            const params = selectedProctorPeriodParams();
            params.set('action', 'list');
            params.set('exam_id', document.getElementById('proctorExamFilter')?.value || '');
            params.set('status', document.getElementById('proctorStatusFilter')?.value || '');
            try {
                const data = await (await fetch(`${proctorApi}?${params}`)).json();
                if (!data.success) throw new Error(data.message);
                body.innerHTML = data.assignments.length ? data.assignments.map(item => `<tr>
                    <td>${proctorEscape(item.exam_name)}</td><td>${proctorEscape(item.subject_codes || '')}</td><td>${proctorEscape(item.section_code || '')}</td><td>${proctorEscape(item.room_name || '')}</td>
                    <td>${proctorEscape(formatProctorDate(item.exam_date))}</td><td>${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}</td>
                    <td>${proctorEscape(`${item.last_name || ''}, ${item.first_name || ''}`)}</td><td>${proctorEscape(item.role)}</td><td>${proctorEscape(item.status)}</td>
                    <td><button type="button" class="btn btn-primary btn-sm" onclick="editProctor(${Number(item.id)})"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="cancelProctor(${Number(item.id)})"><i class="fas fa-ban"></i></button></td></tr>`).join('') : '<tr><td colspan="10" style="text-align:center;">No proctor assignments found.</td></tr>';
                window.proctorAssignments = data.assignments;
            } catch (error) { body.innerHTML = `<tr><td colspan="10" style="text-align:center;color:#b00020;">${proctorEscape(error.message)}</td></tr>`; }
        }

        async function printProctorSchedule() {
            const filters = selectedProctorPeriodParams();
            const examId = document.getElementById('proctorExamFilter')?.value || '';
            const status = document.getElementById('proctorStatusFilter')?.value || '';
            if (examId) filters.set('exam_id', examId);
            if (status) filters.set('status', status);
            filters.set('action', 'list');

            const assignmentResponse = await fetch(`${proctorApi}?${filters}`);
            const assignmentData = await assignmentResponse.json();
            if (!assignmentData.success) {
                alert(assignmentData.message || 'Unable to load proctor assignments for printing.');
                return;
            }

            // Use current filters for schedules as well.
            const scheduleFilters = selectedProctorPeriodParams();
            if (examId) scheduleFilters.set('exam_id', examId);
            const scheduleResponse = await fetch(`${proctorApi}?action=exam-schedules&${scheduleFilters}`);
            const scheduleData = await scheduleResponse.json();
            if (!scheduleData.success) {
                alert(scheduleData.message || 'Unable to load exam schedules for printing.');
                return;
            }

            const currentStatuses = ['Assigned', 'Confirmed', 'Completed'];
            const assignments = (assignmentData.assignments || []).filter(item => {
                if (status === 'Cancelled') return item.status === 'Cancelled';
                return currentStatuses.includes(item.status);
            });

            if (!assignments.length) {
                alert('No proctoring assignments available for the selected filters.');
                return;
            }

            const schedules = scheduleData.exam_schedules || [];
            const examSelect = document.getElementById('proctorExamFilter');
            const examName = examSelect ? examSelect.options[examSelect.selectedIndex]?.text || 'All Exams' : 'All Exams';
            const semesterSelect = document.getElementById('proctorSemesterFilter');
            const semesterName = semesterSelect ? semesterSelect.options[semesterSelect.selectedIndex]?.text || 'All Semesters' : 'All Semesters';
            const schoolYearSelect = document.getElementById('proctorSchoolYearFilter');
            const schoolYearName = schoolYearSelect ? schoolYearSelect.options[schoolYearSelect.selectedIndex]?.text || 'All School Years' : 'All School Years';
            const generatedDate = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

            const groupedByDate = {};
            assignments.forEach(item => {
                const dateKey = item.exam_date || 'Unknown';
                if (!groupedByDate[dateKey]) groupedByDate[dateKey] = [];
                groupedByDate[dateKey].push(item);
            });

            const buildTimeSlots = group => {
                const groupSchedules = schedules.filter(item => {
                    if (item.exam_id !== group.exam_id || item.exam_date !== group.exam_date) return false;
                    // Include Break Time only if it explicitly matches this group's room and section
                    if (item.schedule_type === 'Break Time') {
                        return String(item.room_id || '') === String(group.room_id || '') && String(item.section_id || '') === String(group.section_id);
                    }
                    return String(item.room_id || '') === String(group.room_id || '') && String(item.section_id || '') === String(group.section_id);
                });
                return groupSchedules.sort((a, b) => a.start_time.localeCompare(b.start_time));
            };

            const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[ch]));
            const formatProctorDate = value => {
                if (!value) return '';
                const date = new Date(`${value}T00:00:00`);
                return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            };
            const formatTimeLabel = value => {
                const [hour, minute] = String(value || '').split(':').map(Number);
                if (Number.isNaN(hour)) return value;
                const suffix = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${String(minute).padStart(2, '0')} ${suffix}`;
            };
            const printWindow = window.open('', '', 'width=1400,height=900');
            if (!printWindow) {
                alert('Please allow pop-ups to print the schedule.');
                return;
            }

            const rowsHtml = Object.keys(groupedByDate).sort().map(dateKey => {
                const dateItems = groupedByDate[dateKey];
                const headerHtml = `<div class="print-section-header"><div class="print-section-date">${escapeHtml(formatProctorDate(dateKey))}</div></div>`;
                const groupRows = dateItems.sort((a, b) => {
                    if (a.exam_name !== b.exam_name) return a.exam_name.localeCompare(b.exam_name);
                    if (a.room_name !== b.room_name) return a.room_name.localeCompare(b.room_name);
                    return a.section_code.localeCompare(b.section_code);
                }).map(group => {
                    const slots = buildTimeSlots(group);
                    const cells = slots.map(slot => {
                        if (slot.schedule_type === 'Break Time') {
                            return `<td class="break-time-cell">BREAK TIME</td>`;
                        }
                        return `<td>${escapeHtml(slot.subject_code || slot.subject_name || '')}</td>`;
                    }).join('');
                    const timeHeaders = slots.map(slot => `<th>${escapeHtml(formatTimeLabel(slot.start_time))} - ${escapeHtml(formatTimeLabel(slot.end_time))}</th>`).join('');
                    return `<div class="group-block"><div class="group-title"><strong>${escapeHtml(group.exam_name)}</strong> | ${escapeHtml(group.room_name)} | ${escapeHtml(group.section_code)}</div><table class="print-table"><thead><tr><th>PROCTOR</th><th>ROOM</th><th>SECTION</th>${timeHeaders}</tr></thead><tbody><tr><td>${escapeHtml(`${group.last_name || ''}, ${group.first_name || ''}`)}</td><td>${escapeHtml(group.room_name)}</td><td>${escapeHtml(group.section_code)}</td>${cells}</tr></tbody></table></div>`;
                }).join('');
                return `${headerHtml}${groupRows}`;
            }).join('');

            const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Exam Proctoring Schedule</title><style>
                @page { size: A4 landscape; margin: 10mm; }
                body { margin: 0; padding: 20px; font-family: Arial, sans-serif; color: #111; }
                .document { width: 100%; }
                .header { text-align: center; margin-bottom: 20px; }
                .school-name { font-size: 18px; font-weight: bold; color: #000; margin-bottom: 5px; letter-spacing: 0.5px; }
                .college-name { font-size: 18px; font-weight: bold; color: #000; margin-bottom: 5px; letter-spacing: 0.5px; }
                .college-address { font-size: 11px; color: #333; line-height: 1.3; margin-bottom: 3px; }
                .college-office { font-size: 12px; font-weight: bold; color: #000; margin-top: 8px; }
                .document-title { font-size: 16px; font-weight: bold; margin: 8px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
                .report-info { margin: 14px 0 20px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 12px; }
                .report-info div { line-height: 1.4; }
                .print-section-header { margin-top: 24px; margin-bottom: 8px; border-bottom: 1px solid #333; padding-bottom: 6px; }
                .print-section-date { font-size: 14px; font-weight: bold; }
                .group-block { margin-bottom: 24px; }
                .group-title { font-size: 13px; margin-bottom: 6px; }
                .print-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
                .print-table th, .print-table td { border: 1px solid #444; padding: 6px 8px; text-align: center; font-size: 11px; }
                .print-table th { background: #f2f2f2; }
                .break-time-cell { background: #fff3cd; color: #856404; font-weight: 700; }
                .signatures { display: flex; justify-content: space-between; gap: 16px; margin-top: 36px; }
                .signature { flex: 1; text-align: center; font-size: 12px; }
                .signature-line { margin-top: 40px; border-top: 1px solid #111; }
                .signature-title { margin-top: 6px; font-weight: bold; }
                .date-prepared { font-size: 10px; color: #666; margin-top: 15px; }

                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                        background: white;
                    }
                    .print-container {
                        padding: 20px;
                        border-radius: 0;
                        box-shadow: none;
                    }
                    @page {
                        size: A4 landscape;
                        margin: 10mm;
                    }
                }
            </style></head><body><div class="document">
                <div class="header"><div class="school-name">BESTLINK COLLEGE OF THE PHILIPPINES</div><div class="document-title">EXAMINATION PROCTORING SCHEDULE</div></div>
                <div class="report-info"><div><strong>Exam:</strong> ${escapeHtml(examName || 'All Exams')}</div><div><strong>School Year:</strong> ${escapeHtml(schoolYearName)}</div><div><strong>Semester:</strong> ${escapeHtml(semesterName)}</div><div><strong>Generated Date:</strong> ${escapeHtml(generatedDate)}</div></div>
                ${rowsHtml}
                <div class="signatures"><div class="signature"><div class="signature-line"></div><div class="signature-title">Prepared by</div></div><div class="signature"><div class="signature-line"></div><div class="signature-title">Checked by</div></div><div class="signature"><div class="signature-line"></div><div class="signature-title">Approved by</div></div></div>
            </div></body></html>`;
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => printWindow.print(), 300);
        }

        // Retained as a fallback reference for the previous row-based output.
        async function printSchedule() {
            // Get selected faculty
            const selectedFaculty = document.getElementById('facultySelector').value;
            
            if (!selectedFaculty) {
                alert('Please select a faculty to print');
                return;
            }

            // Get current filter values (semester and school year)
            const semester_id = document.querySelector('select[name="semester_id"]')?.value;
            const school_year_id = document.querySelector('select[name="school_year_id"]')?.value;
            
            if (!semester_id || !school_year_id) {
                alert('Please select a semester and school year');
                return;
            }

            // Fetch schedule data from API
            try {
                const apiUrl = 'api/get_faculty_schedule_timetable.php?faculty_id=' + encodeURIComponent(selectedFaculty) + 
                               '&semester_id=' + encodeURIComponent(semester_id) + 
                               '&school_year_id=' + encodeURIComponent(school_year_id);
                
                const response = await fetch(apiUrl);
                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Failed to load schedule data');
                    return;
                }

                const schedules = data.schedules || [];
                const facultyInfo = data.faculty_info || {};
                const semesterName = data.semester_name || semester_id;
                const schoolYearName = data.school_year_name || school_year_id;

                if (schedules.length === 0) {
                    alert('No schedules found for selected faculty');
                    return;
                }

                // Build faculty display name
                const faculty_name = facultyInfo.last_name ? 
                    (facultyInfo.first_name + ', ' + facultyInfo.last_name) : 'N/A';
                const faculty_code = facultyInfo.faculty_code || 'N/A';

                // Define time slots for the weekly grid (7:00 AM to 5:00 PM)
                const timeSlots = [
                    { label: '7:00 AM - 8:00 AM', start: '07:00', end: '08:00' },
                    { label: '8:00 AM - 9:00 AM', start: '08:00', end: '09:00' },
                    { label: '9:00 AM - 10:00 AM', start: '09:00', end: '10:00' },
                    { label: '10:00 AM - 11:00 AM', start: '10:00', end: '11:00' },
                    { label: '11:00 AM - 12:00 PM', start: '11:00', end: '12:00' },
                    { label: '12:00 PM - 1:00 PM', start: '12:00', end: '13:00' },
                    { label: '1:00 PM - 2:00 PM', start: '13:00', end: '14:00' },
                    { label: '2:00 PM - 3:00 PM', start: '14:00', end: '15:00' },
                    { label: '3:00 PM - 4:00 PM', start: '15:00', end: '16:00' },
                    { label: '4:00 PM - 5:00 PM', start: '16:00', end: '17:00' }
                ];
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                // Helper function to convert time string to minutes for comparison
                const timeToMinutes = (timeStr) => {
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    return hours * 60 + (minutes || 0);
                };

                // Build merged schedule blocks per day to support rowspan rendering
                // Map timeSlots to numeric ranges for quick lookup
                const slotStarts = timeSlots.map(ts => timeToMinutes(ts.start));
                const slotEnds = timeSlots.map(ts => timeToMinutes(ts.end));

                function getSlotRange(sch) {
                    const s = timeToMinutes(sch.start_time);
                    const e = timeToMinutes(sch.end_time);
                    let startIdx = null, endIdx = null;
                    for (let i = 0; i < timeSlots.length; i++) {
                        const slotStart = slotStarts[i];
                        const slotEnd = slotEnds[i];
                        if (s < slotEnd && e > slotStart) {
                            if (startIdx === null) startIdx = i;
                            endIdx = i;
                        }
                    }
                    return { startIdx, endIdx };
                }

                // Bucket schedules by day and attach slot ranges
                const dayBuckets = days.map(() => []);
                schedules.forEach(sch => {
                    const dayIndex = days.indexOf(sch.day_of_week);
                    if (dayIndex === -1) return;
                    const range = getSlotRange(sch);
                    if (range.startIdx === null) return; // doesn't intersect visible slots
                    sch._startIdx = range.startIdx;
                    sch._endIdx = range.endIdx;
                    dayBuckets[dayIndex].push(sch);
                });

                function createSegment(sch) {
                    return {
                        type: sch.schedule_type,
                        start_time: sch.start_time,
                        end_time: sch.end_time,
                        subject_code: sch.subject_code,
                        section_code: sch.section_code,
                        room_full: sch.room_full
                    };
                }

                // For each day, sort schedules and merge consecutive/overlapping schedules
                const mergedDayBlocks = dayBuckets.map(bucket => {
                    bucket.sort((a, b) => (a._startIdx - b._startIdx) || (timeToMinutes(a.start_time) - timeToMinutes(b.start_time)));
                    const merged = [];
                    for (let i = 0; i < bucket.length; i++) {
                        const s = bucket[i];
                        // Seed a current block
                        const cur = {
                            subject_code: s.subject_code,
                            subject_name: s.subject_name,
                            section_code: s.section_code,
                            room_full: s.room_full,
                            startIdx: s._startIdx,
                            endIdx: s._endIdx,
                            segments: [createSegment(s)]
                        };

                        // Merge following schedules that are consecutive/overlapping and share identifiers
                        let j = i + 1;
                        while (j < bucket.length) {
                            const n = bucket[j];
                            const sameIdentifiers = (n.subject_code === cur.subject_code && n.section_code === cur.section_code && n.room_full === cur.room_full);
                            const adjacentOrOverlap = (n._startIdx <= cur.endIdx + 1);
                            const curIsBreakOnly = cur.segments.length === 1 && cur.segments[0].type === 'Break Time';
                            const nextIsBreak = n.schedule_type === 'Break Time';
                            const mergeWithBreak = adjacentOrOverlap && (nextIsBreak || curIsBreakOnly);

                            if ((sameIdentifiers && adjacentOrOverlap) || mergeWithBreak) {
                                cur.endIdx = Math.max(cur.endIdx, n._endIdx);
                                cur.segments.push(createSegment(n));
                                if (curIsBreakOnly && !nextIsBreak) {
                                    // If a break-only block is followed by a class, adopt the class details
                                    cur.subject_code = n.subject_code;
                                    cur.subject_name = n.subject_name;
                                    cur.section_code = n.section_code;
                                    cur.room_full = n.room_full;
                                }
                                j++;
                            } else {
                                break;
                            }
                        }

                        merged.push(cur);
                        i = j - 1;
                    }
                    return merged;
                });

                // Calculate totalUnits (sum of durations for class schedules)
                let totalUnits = 0;
                schedules.forEach(sch => {
                    if (sch.schedule_type !== 'Break Time') {
                        const s = timeToMinutes(sch.start_time);
                        const e = timeToMinutes(sch.end_time);
                        const durationHours = (e - s) / 60;
                        totalUnits += Math.ceil(durationHours);
                    }
                });

                // Build the timetable HTML using rowspan for merged blocks
                let timetableHtml = '<table class="timetable-grid"><thead><tr><th class="time-header">Time</th>';
                days.forEach(day => { timetableHtml += `<th class="day-header">${day}</th>`; });
                timetableHtml += '</tr></thead><tbody>';

                for (let timeIndex = 0; timeIndex < timeSlots.length; timeIndex++) {
                    const timeSlot = timeSlots[timeIndex];
                    timetableHtml += `<tr><td class="time-cell">${timeSlot.label}</td>`;

                    for (let dayIndex = 0; dayIndex < days.length; dayIndex++) {
                        const blocks = mergedDayBlocks[dayIndex] || [];

                        // Find a block that starts at this slot
                        const startingBlock = blocks.find(b => b.startIdx === timeIndex);
                        if (startingBlock) {
                            const rowspan = (startingBlock.endIdx - startingBlock.startIdx) + 1;
                            const blockStartMin = timeToMinutes(timeSlots[startingBlock.startIdx].start);
                            const blockEndMin = timeToMinutes(timeSlots[startingBlock.endIdx].end);
                            const totalMin = blockEndMin - blockStartMin;
                            const totalHeightPx = rowspan * 45;

                            let content = `<div style="position:relative; height:${totalHeightPx}px;">`;
                            startingBlock.segments.forEach(seg => {
                                const segStart = timeToMinutes(seg.start_time);
                                const segEnd = timeToMinutes(seg.end_time);
                                const topPx = ((segStart - blockStartMin) / totalMin) * totalHeightPx;
                                const heightPx = ((segEnd - segStart) / totalMin) * totalHeightPx;
                                if (seg.type === 'Break Time') {
                                    content += `<div class="break-time" style="position:absolute; left:0; right:0; top:${topPx}px; height:${heightPx}px;">BREAK TIME</div>`;
                                } else {
                                    const roomHtml = seg.room_full ? seg.room_full.replace(' - ', '<br>') : '';
                                    content += `<div class="schedule-entry" style="position:absolute; left:0; right:0; top:${topPx}px; height:${heightPx}px;">
                                        <strong>${seg.subject_code || ''}</strong><br>
                                        <small>${seg.section_code || ''}</small><br>
                                        <small>${roomHtml}</small>
                                    </div>`;
                                }
                            });
                            content += '</div>';

                            timetableHtml += `<td class="schedule-cell" rowspan="${rowspan}">${content}</td>`;
                        } else {
                            // If this slot is covered by a previous rowspan, skip adding a cell
                            const covered = blocks.some(b => b.startIdx < timeIndex && b.endIdx >= timeIndex);
                            if (!covered) {
                                timetableHtml += '<td class="schedule-cell"></td>';
                            }
                        }
                    }

                    timetableHtml += '</tr>';
                }

                timetableHtml += '</tbody></table>';

                // Create print window
                const printWindow = window.open('', '', 'width=1400,height=900');
                const today = new Date();
                const formattedDate = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                
                const htmlContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <title>Faculty Class Schedule - ${faculty_name}</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }

                            body {
                                font-family: 'Calibri', 'Arial', sans-serif;
                                background: white;
                                color: #333;
                                line-height: 1.4;
                            }

                            .print-container {
                                width: 100%;
                                padding: 20px;
                                background: white;
                            }

                            /* HEADER SECTION */
                            .header {
                                text-align: center;
                                border-bottom: 2px solid #000;
                                padding-bottom: 10px;
                                margin-bottom: 15px;
                            }

                            .college-name {
                                font-size: 16px;
                                font-weight: bold;
                                color: #000;
                            }

                            .college-address {
                                font-size: 10px;
                                color: #333;
                                line-height: 1.3;
                            }

                            .office-name {
                                font-size: 11px;
                                font-weight: bold;
                                color: #000;
                                margin-top: 3px;
                            }

                            .document-title {
                                font-size: 13px;
                                font-weight: bold;
                                margin-top: 5px;
                                text-transform: uppercase;
                                letter-spacing: 0.5px;
                            }

                            /* FACULTY INFO SECTION */
                            .faculty-info {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 15px 30px;
                                margin-bottom: 15px;
                                font-size: 10px;
                            }

                            .info-item {
                                display: flex;
                            }

                            .info-label {
                                font-weight: bold;
                                width: 80px;
                                flex-shrink: 0;
                            }

                            .info-value {
                                flex: 1;
                            }

                            /* TIMETABLE SECTION */
                            .timetable-grid {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 15px 0;
                                font-size: 10px;
                            }

                            .timetable-grid thead {
                                background: #fff;
                            }

                            .timetable-grid th {
                                border: 1px solid #000;
                                padding: 6px 4px;
                                text-align: center;
                                font-weight: bold;
                                font-size: 9px;
                            }

                            .time-header {
                                width: 80px;
                            }

                            .day-header {
                                width: 13%;
                            }

                            .timetable-grid td {
                                border: 1px solid #000;
                                padding: 4px;
                                height: 45px;
                                vertical-align: top;
                                font-size: 9px;
                            }

                            .time-cell {
                                font-weight: bold;
                                background: #f5f5f5;
                                width: 80px;
                                text-align: center;
                            }

                            .schedule-cell {
                                background: white;
                                overflow: hidden;
                            }

                            .schedule-entry {
                                font-size: 8px;
                                line-height: 1.2;
                            }

                            .schedule-entry strong {
                                display: block;
                                font-weight: bold;
                            }

                            .break-time {
                                background: #fff3cd;
                                border: 1px solid #ffc107;
                                color: #856404;
                                font-weight: bold;
                                text-align: center;
                                padding: 8px 2px;
                                height: 100%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 8px;
                            }

                            /* FOOTER SECTION */
                            .summary-section {
                                margin-top: 10px;
                                font-size: 10px;
                            }

                            .footer {
                                margin-top: 20px;
                                padding-top: 15px;
                                border-top: 1px solid #000;
                            }

                            .footer-row {
                                display: flex;
                                justify-content: space-between;
                                gap: 20px;
                                margin-top: 20px;
                            }

                            .signature-block {
                                flex: 1;
                                text-align: center;
                                font-size: 9px;
                            }

                            .signature-line {
                                border-top: 1px solid #000;
                                margin-top: 35px;
                                padding-top: 3px;
                                font-weight: bold;
                            }

                            .signature-title {
                                font-size: 8px;
                                color: #666;
                                margin-top: 2px;
                            }

                            @media print {
                                body {
                                    margin: 0;
                                    padding: 0;
                                }
                                .print-container {
                                    padding: 15px;
                                }
                                @page {
                                    size: A4 landscape;
                                    margin: 8mm;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-container">
                            <!-- HEADER -->
                            <div class="header">
                                <div class="college-name">BESTLINK COLLEGE OF THE PHILIPPINES</div>
                                <div class="college-address">1071 Brgy. Kaligayahan, Quirino Highway, Novaliches<br>Quezon City, Philippines 1116</div>
                                <div class="office-name">College Coordinator Office</div>
                                <div class="document-title">FACULTY CLASS SCHEDULE</div>
                            </div>

                            <!-- FACULTY INFORMATION -->
                            <div class="faculty-info">
                                <div class="info-item">
                                    <div class="info-label">Faculty:</div>
                                    <div class="info-value">${faculty_name}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Faculty Code:</div>
                                    <div class="info-value">${faculty_code}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Semester:</div>
                                    <div class="info-value">${semesterName}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Academic Year:</div>
                                    <div class="info-value">${schoolYearName}</div>
                                </div>
                            </div>

                            <!-- TIMETABLE -->
                            ${timetableHtml}

                            <!-- SUMMARY AND FOOTER -->
                            <div class="summary-section">
                                Total Units: <strong>${totalUnits} / 15</strong>
                            </div>

                            <div class="footer">
                                <div class="footer-row">
                                    <div class="signature-block">
                                        <div>Prepared by:</div>
                                        <div class="signature-line"></div>
                                        <div class="signature-title">College Coordinator</div>
                                    </div>

                                    <div class="signature-block">
                                        <div>Noted by:</div>
                                        <div class="signature-line"></div>
                                        <div class="signature-title">Dean / Director</div>
                                    </div>

                                    <div class="signature-block">
                                        <div>Date:</div>
                                        <div class="signature-line">${formattedDate}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </body>
                    </html>
                `;
                
                printWindow.document.write(htmlContent);
                printWindow.document.close();
                
                // Focus and print
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 250);

            } catch (error) {
                console.error('Error loading schedule:', error);
                alert('Error loading schedule data: ' + error.message);
            }
        }

        // Delete schedule via AJAX
        function deleteSchedule(scheduleId) {
            if (!confirm('Delete this schedule? This will also delete attendance records.')) {
                return;
            }

            const formData = new FormData();
            formData.append('schedule_id', scheduleId);

            fetch('/sms/modules/college-coor/api/delete_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting schedule: ' + error);
            });
        }

        function populateFacultySelect(selectedId = '') {
            const select = document.getElementById('proctorFacultyId');
            if (!select) return;
            select.innerHTML = '<option value="">Select Faculty</option>' + proctorFaculty.map(item => `<option value="${item.id}">${proctorEscape(item.last_name)}, ${proctorEscape(item.first_name)} (${proctorEscape(item.faculty_code)})</option>`).join('');
            select.value = selectedId;
        }

        async function openProctorModal(assignment = null) {
            try {
                await loadProctorReferenceData();
            } catch (error) {
                alert(error.message);
                return;
            }
            document.getElementById('proctorForm')?.reset();
            document.getElementById('proctorAssignmentId').value = assignment?.id || '';
            document.getElementById('proctorModalTitle').innerHTML = assignment ? '<i class="fas fa-edit"></i> Edit Exam Proctor' : '<i class="fas fa-user-shield"></i> Assign Exam Proctor';
            populateFacultySelect(assignment?.faculty_id || '');
            const scheduleSelect = document.getElementById('proctorScheduleId');
            scheduleSelect.disabled = Boolean(assignment);
            if (assignment) {
                scheduleSelect.value = assignment.exam_schedule_id;
                document.getElementById('proctorRole').value = assignment.role;
                document.getElementById('proctorStatus').value = assignment.status;
            }
            updateProctorScheduleInfo();
            document.getElementById('proctorModal').style.display = 'flex';
        }

        function closeProctorModal() {
            const modal = document.getElementById('proctorModal');
            if (modal) modal.style.display = 'none';
        }

        function updateProctorScheduleInfo() {
            const item = proctorExamSchedules.find(schedule => String(schedule.id) === String(document.getElementById('proctorScheduleId')?.value));
            const info = document.getElementById('proctorScheduleInfo');
            if (!info) return;
            info.innerHTML = item ? `<strong>Exam:</strong> ${proctorEscape(item.exam_name)}<br><strong>Subject:</strong> ${proctorEscape(item.subject_code || '')} - ${proctorEscape(item.subject_name || '')}<br><strong>Section:</strong> ${proctorEscape(item.section_code || '')}<br><strong>Room:</strong> ${proctorEscape(item.room_name || '')}<br><strong>Date:</strong> ${proctorEscape(item.exam_date)}<br><strong>Time:</strong> ${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}` : '';
        }

        function editProctor(id) {
            const assignment = (window.proctorAssignments || []).find(item => Number(item.id) === Number(id));
            if (!assignment) return;
            assignment.exam_schedule_id = assignment.sample_schedule_id;
            openProctorModal(assignment);
        }

        async function submitProctor(event) {
            event.preventDefault();
            const form = new FormData(event.target);
            form.append('action', form.get('id') ? 'update' : 'create');
            try {
                const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
                if (!data.success) throw new Error(data.message);
                alert(data.message);
                closeProctorModal();
                await loadProctorAssignments();
            } catch (error) {
                alert(error.message);
            }
        }

        async function cancelProctor(id) {
            if (!confirm('Cancel this exam proctor assignment?')) return;
            const form = new FormData();
            form.append('action', 'cancel');
            form.append('id', id);
            try {
                const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
                if (!data.success) throw new Error(data.message);
                alert(data.message);
                await loadProctorAssignments();
            } catch (error) {
                alert(error.message);
            }
        }

        function clearProctorFilters() {
            const semesterFilter = document.getElementById('proctorSemesterFilter');
            const schoolYearFilter = document.getElementById('proctorSchoolYearFilter');
            const examFilter = document.getElementById('proctorExamFilter');
            const statusFilter = document.getElementById('proctorStatusFilter');
            if (semesterFilter) semesterFilter.value = '';
            if (schoolYearFilter) schoolYearFilter.value = '';
            if (examFilter) examFilter.value = '';
            if (statusFilter) statusFilter.value = 'Assigned';
            loadProctorReferenceData().then(loadProctorAssignments);
        }

        function openExamModal() {
            const examForm = document.getElementById('examForm');
            if (examForm) examForm.reset();
            const examSchoolYear = document.getElementById('examSchoolYear');
            const examSemester = document.getElementById('examSemester');
            if (examSchoolYear) examSchoolYear.value = document.getElementById('proctorSchoolYearFilter')?.value || '';
            if (examSemester) examSemester.value = document.getElementById('proctorSemesterFilter')?.value || '';
            const modal = document.getElementById('examModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeExamModal() {
            const modal = document.getElementById('examModal');
            if (modal) modal.style.display = 'none';
        }

        async function submitExam(event) {
            event.preventDefault();
            const form = new FormData(event.target);
            form.append('action', 'create_exam');
            try {
                const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
                if (!data.success) throw new Error(data.message);
                alert(data.message);
                closeExamModal();
                // Create Examination should only insert into cc_exams.
                // Do not automatically trigger proctoring loads after exam creation.
            } catch (error) {
                alert(error.message);
            }
        }

        function openExamScheduleModal() {
            const form = document.getElementById('examScheduleForm');
            if (form) form.reset();
            updateExamScheduleTypeFields();
            loadProctorReferenceData().then(() => {
                updateExamScheduleTypeFields();
                const modal = document.getElementById('examScheduleModal');
                if (modal) modal.style.display = 'flex';
            }).catch(error => alert(error.message));
        }

        function closeExamScheduleModal() {
            const modal = document.getElementById('examScheduleModal');
            if (modal) modal.style.display = 'none';
        }

        function updateExamScheduleContext() {
            const exam = proctorExams.find(item => String(item.id) === String(document.getElementById('scheduleExamId').value));
            const context = document.getElementById('scheduleExamContext');
            if (!context) return;
            if (!exam) { context.textContent = ''; return; }
            context.textContent = `${exam.school_year_name || ''} / ${exam.semester_name || ''} | Exam dates: ${exam.start_date} to ${exam.end_date}`;
            const scheduleExamDate = document.getElementById('scheduleExamDate');
            if (scheduleExamDate) {
                scheduleExamDate.min = exam.start_date;
                scheduleExamDate.max = exam.end_date;
            }
        }

        function updateExamScheduleTypeFields() {
            const scheduleTypeElement = document.getElementById('scheduleType');
            const fields = document.getElementById('examScheduleClassFields');
            if (!scheduleTypeElement || !fields) return;
            const type = scheduleTypeElement.value;
            const subjectField = document.getElementById('scheduleSubjectId');
            const sectionField = document.getElementById('scheduleSectionId');
            const roomField = document.getElementById('scheduleRoomId');

            if (type === 'Exam') {
                fields.style.display = 'contents';
                if (subjectField) { subjectField.disabled = false; subjectField.required = true; subjectField.setAttribute('name', 'subject_id'); }
                if (sectionField) { sectionField.disabled = false; sectionField.required = true; sectionField.setAttribute('name', 'section_id'); }
                if (roomField) { roomField.disabled = false; roomField.required = true; roomField.setAttribute('name', 'room_id'); }
            } else if (type === 'Break Time') {
                fields.style.display = 'contents';
                if (subjectField) { subjectField.disabled = true; subjectField.required = false; subjectField.value = ''; subjectField.removeAttribute('name'); }
                if (sectionField) { sectionField.disabled = false; sectionField.required = true; sectionField.setAttribute('name', 'section_id'); }
                if (roomField) { roomField.disabled = false; roomField.required = true; roomField.setAttribute('name', 'room_id'); }
            } else {
                fields.style.display = 'none';
                [subjectField, sectionField, roomField].forEach(f => {
                    if (f) { f.disabled = true; f.required = false; f.value = ''; f.removeAttribute('name'); }
                });
            }
        }

        // Toggle fields in Add Schedule tab based on Schedule Type (Class vs Break Time)
        function updateScheduleTypeFields() {
            const scheduleTypeEl = document.getElementById('schedule_type');
            if (!scheduleTypeEl) return;
            const type = scheduleTypeEl.value;

            const wrappers = {
                subject: document.getElementById('add-subject-field'),
                section: document.getElementById('add-section-field'),
                facultyLoad: document.getElementById('add-faculty-load-field'),
                semester: document.getElementById('add-semester-field'),
                schoolYear: document.getElementById('add-school-year-field')
            };

            const fields = {
                subject: document.getElementById('subject_id'),
                section: document.getElementById('section_id'),
                facultyLoad: document.getElementById('faculty_load_id'),
                faculty: document.getElementById('faculty_id'),
                semester: document.getElementById('semester_id'),
                schoolYear: document.getElementById('school_year_id'),
                room: document.getElementById('room_id')
            };

            if (type === 'Break Time') {
                // Hide and disable fields that are not needed for Break Time
                ['subject', 'section', 'facultyLoad', 'semester', 'schoolYear'].forEach(k => {
                    const wrap = wrappers[k];
                    const f = fields[k];
                    if (wrap) wrap.style.display = 'none';
                    if (f) { f.disabled = true; f.required = false; f.value = ''; f.removeAttribute('name'); }
                });

                // Assigned faculty should be enabled and required for Break Time
                if (fields.faculty) { fields.faculty.disabled = false; fields.faculty.required = true; fields.faculty.setAttribute('name', 'faculty_id'); }

                // Room remains visible but optional
                if (fields.room) fields.room.required = false;
            } else {
                // Show and enable fields for Class (default)
                ['subject', 'section', 'facultyLoad', 'semester', 'schoolYear'].forEach(k => {
                    const wrap = wrappers[k];
                    const f = fields[k];
                    if (wrap) wrap.style.display = '';
                    if (f) {
                        f.disabled = false;
                        f.required = true;
                        // restore expected name attributes
                        if (k === 'subject') f.setAttribute('name', 'subject_id');
                        if (k === 'section') f.setAttribute('name', 'grade_section_id');
                        if (k === 'facultyLoad') f.setAttribute('name', 'faculty_load_id');
                        if (k === 'semester') f.setAttribute('name', 'semester_id');
                        if (k === 'schoolYear') f.setAttribute('name', 'school_year_id');
                    }
                });

                // Assigned faculty stays enabled (populated) but not required if faculty_load is used
                if (fields.faculty) { fields.faculty.disabled = false; fields.faculty.required = true; fields.faculty.setAttribute('name', 'faculty_id'); }

                // Room is required for Class
                if (fields.room) fields.room.required = true;
            }
        }

        async function submitExamSchedule(event) {
            event.preventDefault();
            const form = new FormData(event.target);
            form.append('action', 'create_schedule');
            try {
                const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
                if (!data.success) throw new Error(data.message);
                alert(data.message);
                closeExamScheduleModal();
                await loadProctorReferenceData();
                await loadProctorAssignments();
            } catch (error) {
                alert(error.message);
            }
        }

        document.getElementById('proctorScheduleId')?.addEventListener('change', updateProctorScheduleInfo);
        document.getElementById('scheduleExamId')?.addEventListener('change', updateExamScheduleContext);
        document.addEventListener('change', function(event) {
            if (event.target && event.target.id === 'scheduleType') {
                updateExamScheduleTypeFields();
            }
        });
        // Bind and initialize Add Schedule type fields
        document.getElementById('schedule_type')?.addEventListener('change', updateScheduleTypeFields);
        // Initialize on load in case the form is pre-filled
        try { updateScheduleTypeFields(); } catch(e) { /* ignore */ }
        ['proctorSemesterFilter', 'proctorSchoolYearFilter'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => {
                loadProctorReferenceData().catch(error => alert(error.message));
            });
        });
        if (document.getElementById('proctoring-tab')) {
            loadProctorReferenceData().then(loadProctorAssignments).catch(console.error);
        }
    </script>
</body>
</html>