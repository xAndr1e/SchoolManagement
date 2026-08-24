<?php
require_once(__DIR__ . '/../classes/ProgramManager.php');
require_once(__DIR__ . '/../classes/SubjectManager.php');
require_once(__DIR__ . '/../classes/SectionManager.php');
require_once(__DIR__ . '/../classes/FacultyManager.php');
require_once(__DIR__ . '/../../../database/db.php');

$database = new Database();
$conn = $database->getConnection();

$programManager = new ProgramManager($conn);
$subjectManager = new SubjectManager($conn);
$sectionManager = new SectionManager($conn);
$facultyManager = new FacultyManager($conn);

// Fetch data
$programs = $programManager->getAllPrograms() ?? [];
$subjects = $subjectManager->getAllSubjects() ?? [];
$subjectCatalog = $subjectManager->getAllSubjectAssignments() ?? [];
$subjectAssignmentsById = [];
foreach ($subjectCatalog as $subjectRow) {
    $subjectId = $subjectRow['id'];
    if (!isset($subjectAssignmentsById[$subjectId])) {
        $subjectAssignmentsById[$subjectId] = [];
    }
    $subjectAssignmentsById[$subjectId][] = $subjectRow;
}
$sectionsStmt = $sectionManager->getAll();
$sections = $sectionsStmt ? $sectionsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
$sectionSchoolYears = [];
foreach ($sections as $section) {
    if (!empty($section['school_year'])) {
        $sectionSchoolYears[$section['school_year']] = true;
    }
}
$facultyLoads = $facultyManager->getFacultyLoad() ?? [];

// Sample statistics
$stats = [
    'programs' => count($programs),
    'subjects' => count($subjects),
    'sections' => count($sections),
    'faculty' => count($facultyLoads)
];
?>
    <!-- Page Header -->
    <div class="module-header">
        <div>
            <h1><i class="fas fa-graduation-cap"></i> Academics Management</h1>
            <p>Monitor programs, curriculum, subjects, sections, and faculty load</p>
        </div>
    </div>


    <div class="module-content">
    <div class="stats-grid">
        <div class="stat-card">
            <h6>Programs</h6>
            <h3><?php echo $stats['programs']; ?></h3>
        </div>
        <div class="stat-card subjects">
            <h6>Subjects</h6>
            <h3><?php echo $stats['subjects']; ?></h3>
        </div>
        <div class="stat-card sections">
            <h6>Sections</h6>
            <h3><?php echo $stats['sections']; ?></h3>
        </div>
        <div class="stat-card faculty">
            <h6>Faculty Load</h6>
            <h3><?php echo $stats['faculty']; ?></h3>
        </div>
    </div>

    <!-- Tabs -->
    <div class="academics-tabs">
        <button class="academics-tab active" data-tab="programs">
            <i class="fas fa-book"></i> Programs & Curriculum
        </button>
        <button class="academics-tab" data-tab="subjects">
            <i class="fas fa-list"></i> Subjects
        </button>
        <button class="academics-tab" data-tab="sections">
            <i class="fas fa-layer-group"></i> Sections
        </button>
        <button class="academics-tab" data-tab="faculty">
            <i class="fas fa-users"></i> Faculty Load
        </button>
    </div>

    <!-- TAB: Programs & Curriculum -->
    <div class="academics-panel active" id="tab-programs">
        <div class="section-header">
            <h3>Programs & Curriculum</h3>
            <p class="text-muted" style="margin: 0;"><small>Read-only monitoring</small></p>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchPrograms" placeholder="Search programs...">
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="programsTable">
                    <thead>
                        <tr>
                            <th>Program Code</th>
                            <th>Program Name</th>
                            <th>Effective Year</th>
                            <th>Active Curriculum</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $prog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prog['code'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($prog['name'] ?? 'Program'); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($prog['effective_year']) ? $prog['effective_year'] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($prog['is_active']) ? ($prog['is_active'] == 1 ? 'Active' : 'Inactive') : 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewCurriculumDetails(<?php echo (int)($prog['id'] ?? 0); ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No programs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Subjects -->
    <div class="academics-panel" id="tab-subjects">
        <div class="section-header">
            <h3>Subject Catalog</h3>
        </div>

        <div class="card mb-3 compact-filter-card">
            <div class="card-body py-3 px-3 px-md-4">
                <!-- Filter Row 1: Search, Course, Year Level -->
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-6 filter-col">
                        <label for="searchSubjects" class="form-label">Search</label>
                        <input type="text" id="searchSubjects" class="form-control" placeholder="Search subjects...">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 filter-col">
                        <label for="filterSubjectCourse" class="form-label">Course</label>
                        <select id="filterSubjectCourse" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog['code']); ?>"><?php echo htmlspecialchars($prog['code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 filter-col">
                        <label for="filterSubjectYear" class="form-label">Year Level</label>
                        <select id="filterSubjectYear" class="form-select">
                            <option value="">All Year Levels</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                </div>
                <!-- Filter Row 2: Semester, Status, Reset Filters -->
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-4 filter-col">
                        <label for="filterSubjectSemester" class="form-label">Semester</label>
                        <select id="filterSubjectSemester" class="form-select">
                            <option value="">All Semesters</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 filter-col">
                        <label for="filterSubjectStatus" class="form-label">Status</label>
                        <select id="filterSubjectStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end filter-col">
                        <button type="button" id="resetSubjectFilters" class="btn btn-outline-secondary w-100">Reset Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="subjectsTable" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Units</th>
                            <th>Lecture Hours</th>
                            <th>Laboratory Hours</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($subjectCatalog)): ?>
                            <?php foreach ($subjectCatalog as $subj): ?>
                                <tr data-year-level="<?php echo htmlspecialchars($subj['year_level'] ?? ''); ?>"
                                    data-semester="<?php echo htmlspecialchars($subj['semester'] ?? ''); ?>"
                                    data-course="<?php echo htmlspecialchars($subj['course_code'] ?? ''); ?>"
                                    data-status="<?php echo htmlspecialchars(!empty($subj['is_active']) ? 'active' : 'inactive'); ?>">
                                    <td><?php echo htmlspecialchars($subj['code'] ?? 'SUBJ101'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['name'] ?? 'Subject'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['units'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['lecture_hours'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['lab_hours'] ?? '0'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['course_code'] ?: 'Unassigned'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['year_level'] ? ($subj['year_level'] === '1' ? '1st Year' : ($subj['year_level'] === '2' ? '2nd Year' : ($subj['year_level'] === '3' ? '3rd Year' : ($subj['year_level'] === '4' ? '4th Year' : $subj['year_level'])))) : 'Unassigned'); ?></td>
                                    <td><?php echo htmlspecialchars($subj['semester'] ? $subj['semester'] . ' Semester' : 'Unassigned'); ?></td>
                                    <td>
                                        <?php if ($subj['curriculum_id'] && $subj['is_active'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($subj['curriculum_id']): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewSubjectDetails('<?php echo htmlspecialchars($subj['id'] ?? ''); ?>')">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No subjects found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Sections -->
    <div class="academics-panel" id="tab-sections">
        <div class="section-header">
            <h3>Manage Sections</h3>
        </div>

        <div class="card mb-3 compact-filter-card" id="sectionsFilterCard">
            <div class="card-body py-3 px-3 px-md-4">
                <!-- Filter Row 1: Search (larger) and Program -->
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-8 filter-col">
                        <label for="searchSections" class="form-label">Search Section Code</label>
                        <input type="text" id="searchSections" class="form-control" placeholder="Search section code...">
                    </div>
                    <div class="col-12 col-md-4 filter-col">
                        <label for="filterSectionProgram" class="form-label">Course / Program</label>
                        <select id="filterSectionProgram" class="form-select">
                            <option value="">All Programs</option>
                            <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog['code']); ?>"><?php echo htmlspecialchars($prog['code']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Filter Row 2: Year Level | Semester | Reset (inline on desktop) -->
                <div class="row g-2">
                    <div class="col-12 col-md-4 filter-col">
                        <div style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label for="filterSectionYear" class="form-label">Year Level</label>
                                <select id="filterSectionYear" class="form-select">
                                    <option value="">All Year Levels</option>
                                    <option value="1st">1st Year</option>
                                    <option value="2nd">2nd Year</option>
                                    <option value="3rd">3rd Year</option>
                                    <option value="4th">4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 filter-col">
                        <div style="display:flex; gap:15px;">
                            <div style="flex:1;">
                                <label for="filterSectionSemester" class="form-label">Semester</label>
                                <select id="filterSectionSemester" class="form-select">
                                    <option value="">All Semesters</option>
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end filter-col">
                        <div style="width:100%;">
                            <button type="button" id="resetSectionFilters" class="btn btn-outline-secondary w-100">Reset Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="sectionsTable">
                    <thead>
                        <tr>
                            <th>Section Code</th>
                            <th>Year Level</th>
                            <th>Semester</th>
                            <th>School Year</th>
                            <th>Adviser</th>
                            <th>Total Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sections)): ?>
                            <?php foreach ($sections as $sec): ?>
                                <?php
                                    $sectionId = $sec['id'] ?? '';
                                    $sectionProgram = $sec['program'] ?? 'N/A';
                                    $sectionYearLevel = $sec['grade_level'] ?? 'N/A';
                                    $sectionSemester = $sec['semester_name'] ?? 'N/A';
                                    $sectionSchoolYear = $sec['school_year_name'] ?? 'N/A';
                                    $adviserName = trim($sec['adviser_name'] ?? '') ?: 'Not Assigned';
                                    $totalStudents = $sec['total_students'] ?? '0';
                                    $statusText = (!empty($sec['status']) && strtolower($sec['status']) === 'inactive') ? 'Inactive' : 'Active';
                                    $statusBadgeClass = $statusText === 'Active' ? 'badge-success' : 'badge-secondary';
                                    $adviserActionLabel = $adviserName === 'Not Assigned' ? 'Assign Adviser' : 'Reassign Adviser';
                                ?>
                                <tr data-section-id="<?php echo htmlspecialchars($sectionId); ?>"
                                    data-program="<?php echo htmlspecialchars($sectionProgram); ?>"
                                    data-year-level="<?php echo htmlspecialchars($sectionYearLevel); ?>"
                                    data-semester="<?php echo htmlspecialchars($sectionSemester); ?>"
                                    data-school-year="<?php echo htmlspecialchars($sectionSchoolYear); ?>"
                                    data-adviser="<?php echo htmlspecialchars($adviserName); ?>"
                                    data-total-students="<?php echo htmlspecialchars($totalStudents); ?>"
                                    data-status="<?php echo htmlspecialchars($statusText); ?>">
                                    <td><?php echo htmlspecialchars($sec['section_code'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($sectionYearLevel); ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($sectionSemester); ?></span></td>
                                    <td><?php echo htmlspecialchars($sectionSchoolYear); ?></td>
                                    <td><?php echo htmlspecialchars($adviserName); ?></td>
                                    <td><?php echo htmlspecialchars($totalStudents); ?></td>
                                    <td><span class="badge <?php echo $statusBadgeClass; ?>"><?php echo htmlspecialchars($statusText); ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewSectionDetails(this.closest('tr'))">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openAssignAdviserModal(this.closest('tr'))">
                                                <i class="bi bi-person-badge"></i> <?php echo $adviserActionLabel; ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No sections found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Faculty Load -->
    <div class="academics-panel" id="tab-faculty">
        <div class="section-header">
            <h3>Manage Faculty Load</h3>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchFaculty" placeholder="Search faculty...">
            </div>
            <select class="filter-select" id="filterLoadStatus">
                <option value="">All Status</option>
                <option value="Fully Loaded">Fully Loaded</option>
                <option value="Underloaded">Underloaded</option>
                <option value="Overloaded">Overloaded</option>
            </select>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table id="facultyTable">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Assigned Sections</th>
                            <th>Assigned Subjects</th>
                            <th>Teaching Units</th>
                            <th>Maximum Load</th>
                            <th>Load Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facultyLoads)): ?>
                            <?php foreach ($facultyLoads as $fac): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?></td>
                                    <td><?php echo intval($fac['assigned_sections'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['assigned_subjects'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['teaching_units'] ?? 0); ?></td>
                                    <td><?php echo intval($fac['max_load'] ?? 15); ?></td>
                                    <td>
                                        <?php
                                        $teachingUnits = intval($fac['teaching_units'] ?? 0);
                                        if ($teachingUnits <= 8) {
                                            $loadStatus = 'Underloaded';
                                            $badgeClass = 'badge-warning';
                                        } elseif ($teachingUnits >= 9 && $teachingUnits <= 15) {
                                            $loadStatus = 'Normal Load';
                                            $badgeClass = 'badge-success';
                                        } else {
                                            $loadStatus = 'Overloaded';
                                            $badgeClass = 'badge-danger';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $loadStatus; ?></span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-info" onclick="openAssignFacultyLoadModal('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                Assign Instructor
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="openAssignFacultySubjectModal('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                Assign Subjects
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="viewFacultyLoadDetails('<?php echo $fac['id'] ?? 'N/A'; ?>', '<?php echo htmlspecialchars($fac['faculty_name'] ?? 'N/A'); ?>', '<?php echo intval($fac['teaching_units'] ?? 0); ?>', '<?php echo intval($fac['max_load'] ?? 15); ?>', '<?php echo intval($fac['assigned_sections'] ?? 0); ?>', '<?php echo intval($fac['assigned_subjects'] ?? 0); ?>', '<?php echo htmlspecialchars($fac['department'] ?? 'N/A'); ?>')">
                                                View Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No faculty loads found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    </div>
    <!-- Statistics Cards -->
    
<!-- MODALS -->

<!-- Curriculum Details Modal -->
<div class="modal" id="curriculumDetailsModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Curriculum Details</h5>
            <button type="button" class="modal-close" onclick="closeCurriculumModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Program</label>
                <input type="text" class="form-control" id="currProgramName" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Curriculum</label>
                <input type="text" class="form-control" id="currVersion" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Effective Year</label>
                <input type="text" class="form-control" id="currUnits" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" id="currStatus" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Subject Flow</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;">
                    <div id="subjectFlowList"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Enrollment & Curriculum Summary</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <div id="enrollmentSummaryContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subject Details Modal -->
<div class="modal" id="subjectDetailsModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Subject Details</h5>
            <button type="button" class="modal-close" onclick="closeSubjectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="card mb-3">
                <div class="card-header">Subject Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subjCode" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Units</label>
                            <input type="text" class="form-control" id="subjUnits" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lecture Hours</label>
                            <input type="text" class="form-control" id="subjLectureHours" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subjName" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Laboratory Hours</label>
                            <input type="text" class="form-control" id="subjLabHours" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Curriculum Assignment</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="subjectAssignmentTable">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Curriculum Name</th>
                                    <th>Effective Year</th>
                                    <th>Year Level</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="subjectAssignmentBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading assignments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <p id="subjectAssignmentStatus" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Section Modal -->
<div class="modal" id="sectionModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title" id="sectionModalTitle">Add Section</h5>
            <button type="button" class="modal-close" onclick="closeSectionModal()">&times;</button>
        </div>
        <form id="sectionForm">
            <div class="modal-body">
                <input type="hidden" id="sectionId">
                <div class="form-group">
                    <label class="form-label">Section Code <span class="required">*</span></label>
                    <input type="text" class="form-control" id="sectionCode" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Program <span class="required">*</span></label>
                    <select class="form-control" id="sectionProgram" required>
                        <option value="">Select Program</option>
                        <?php foreach ($programs as $prog): ?>
                            <option value="<?php echo htmlspecialchars($prog['code']); ?>"><?php echo htmlspecialchars($prog['code']); ?> - <?php echo htmlspecialchars($prog['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Year Level <span class="required">*</span></label>
                    <select class="form-control" id="sectionYear" required>
                        <option value="">Select Year</option>
                        <option value="1st">1st Year</option>
                        <option value="2nd">2nd Year</option>
                        <option value="3rd">3rd Year</option>
                        <option value="4th">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity <span class="required">*</span></label>
                    <input type="number" class="form-control" id="sectionCapacity" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">School Year <span class="required">*</span></label>
                    <select class="form-control" id="sectionSchoolYear" required>
                        <option value="">Select School Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester <span class="required">*</span></label>
                    <select class="form-control" id="sectionSemester" required disabled>
                        <option value="">Select Semester</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Adviser Modal -->
<div class="modal" id="adviserModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title" id="adviserModalTitle">Assign Adviser</h5>
            <button type="button" class="modal-close" onclick="closeAdviserModal()">&times;</button>
        </div>
        <form id="adviserForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="adviserSectionId">

                <div class="card mb-3">
                    <div class="card-header">Section Information</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Section Code</label>
                                <p class="mb-0" id="adviserSectionCode">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year Level</label>
                                <p class="mb-0" id="adviserSectionYear">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Semester</label>
                                <p class="mb-0" id="adviserSectionSemester">N/A</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School Year</label>
                                <p class="mb-0" id="adviserSectionSchoolYear">N/A</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Current Adviser</div>
                    <div class="card-body">
                        <p class="mb-0" id="adviserCurrentAdviser">Not Assigned</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Faculty Assignment</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label" for="adviserSelect">Select Faculty Member <span class="required">*</span></label>
                            <select class="form-control" id="adviserSelect" required>
                                <option value="">Choose Faculty Member</option>
                                <?php foreach ($facultyLoads as $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['faculty_name']); ?>" data-id="<?php echo htmlspecialchars($faculty['id']); ?>">
                                        <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAdviserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="adviserActionButton">Assign Adviser</button>
            </div>
        </form>
    </div>
</div> 

<!-- Section Details Modal -->
<div class="modal" id="sectionDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Section Details</h5>
            <button type="button" class="modal-close" onclick="closeSectionDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="card mb-3">
                <div class="card-header">Section Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Code</label>
                            <p class="mb-0" id="sectionDetailsCode">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year Level</label>
                            <p class="mb-0" id="sectionDetailsYear">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <p class="mb-0" id="sectionDetailsSemester">N/A</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">School Year</label>
                            <p class="mb-0" id="sectionDetailsSchoolYear">N/A</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Enrollment</div>
                <div class="card-body">
                    <p class="mb-0" id="sectionDetailsTotalStudents">0</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Faculty</div>
                <div class="card-body">
                    <p class="mb-0" id="sectionDetailsAdviser">Not Assigned</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <span class="badge badge-success" id="sectionDetailsStatus">Active</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeSectionDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Add/Edit Faculty Load Modal -->
<div class="modal" id="facultyLoadModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title" id="facultyLoadModalTitle">Assign Faculty Load</h5>
            <button type="button" class="modal-close" onclick="closeFacultyLoadModal()">&times;</button>
        </div>
        <form id="facultyLoadForm">
            <div class="modal-body">
                <input type="hidden" id="facultyLoadId">
                <div class="form-group">
                    <label class="form-label">Faculty Member <span class="required">*</span></label>
                    <select class="form-control" id="facultySelect" required>
                        <option value="">Select Faculty</option>
                        <option value="Dr. Maria Santos">Dr. Maria Santos</option>
                        <option value="Prof. Juan Reyes">Prof. Juan Reyes</option>
                        <option value="Dr. Rita Ocampo">Dr. Rita Ocampo</option>
                        <option value="Prof. Ramon Miguel">Prof. Ramon Miguel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Section/Class <span class="required">*</span></label>
                    <select class="form-control" id="loadSectionSelect" required>
                        <option value="">Select Section</option>
                        <option value="IT-1A">IT-1A</option>
                        <option value="IT-1B">IT-1B</option>
                        <option value="CS-2A">CS-2A</option>
                        <option value="EN-3A">EN-3A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject Code <span class="required">*</span></label>
                    <input type="text" class="form-control" id="loadSubject" placeholder="e.g., SUBJ101" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Load (Units) <span class="required">*</span></label>
                    <input type="number" class="form-control" id="loadUnits" min="1" max="6" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Schedule <span class="required">*</span></label>
                    <input type="text" class="form-control" id="loadSchedule" placeholder="e.g., MWF 9:00-10:30" required>
                </div>
                <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 15px; display: none;" id="conflictWarning">
                    <small><strong>⚠️ Schedule Conflict Detected:</strong> This faculty member has another class during this time.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFacultyLoadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Load</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Sections Modal -->
<div class="modal" id="assignFacultyLoadModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Instructor</h5>
            <button type="button" class="modal-close" onclick="closeAssignFacultyLoadModal()">&times;</button>
        </div>
        <form id="assignFacultyLoadForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="assignLoadFacultyId">
                
                <!-- Faculty Information Section -->
                <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Faculty Information</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Faculty Name</label>
                            <input type="text" class="form-control" id="assignLoadFacultyName" readonly>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="assignLoadDepartment" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Assignment Details Section -->
                <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                    <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Assignment Details</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">School Year <span class="required">*</span></label>
                            <select class="form-select" id="assignLoadSchoolYear" required>
                                <option value="">Select School Year</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Semester <span class="required">*</span></label>
                            <select class="form-select" id="assignLoadSemester" required disabled>
                                <option value="">Select Semester</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sections <span class="required">*</span></label>
                        <select class="form-select" id="assignLoadSection" multiple size="8" required>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple sections.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignFacultyLoadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Instructor Assignments</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Faculty Subjects Modal -->
<div class="modal" id="assignFacultySubjectModal">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h5 class="modal-title">Assign Subjects</h5>
            <button type="button" class="modal-close" onclick="closeAssignFacultySubjectModal()">&times;</button>
        </div>
        <form id="assignFacultySubjectForm" data-custom-submit>
            <div class="modal-body">
                <input type="hidden" id="assignSubjectFacultyId">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">Faculty Name</label>
                            <input type="text" class="form-control" id="assignSubjectFacultyName" readonly>
                        </div>
                        <div>
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="assignSubjectDepartment" readonly>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div>
                            <label class="form-label">Maximum Load</label>
                            <input type="text" class="form-control" id="assignSubjectMaxLoad" readonly>
                        </div>
                        <div>
                            <label class="form-label">Current Teaching Load</label>
                            <input type="text" class="form-control" id="assignSubjectCurrentLoad" readonly>
                        </div>
                        <div>
                            <label class="form-label">Remaining Load</label>
                            <input type="text" class="form-control" id="assignSubjectRemainingLoad" readonly>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label class="form-label">Section <span class="required">*</span></label>
                            <select class="form-select" id="assignSubjectSection" required>
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Subject <span class="required">*</span></label>
                            <select class="form-select" id="assignSubjectSubject" required disabled>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignFacultySubjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject Assignment</button>
            </div>
        </form>
    </div>
</div>

<!-- Faculty Load Details Modal -->
<div class="modal" id="facultyLoadDetailsModal">
    <div class="modal-dialog" style="max-width: 900px;">
        <div class="modal-header">
            <h5 class="modal-title">Faculty Load Details</h5>
            <button type="button" class="modal-close" onclick="closeFacultyLoadDetailsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Faculty Information Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0d6efd;">
                <h6 style="margin-bottom: 15px; font-weight: 600; color: #333;">Faculty Information</h6>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Faculty Name</strong></label>
                        <p id="detailFacultyName" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Department</strong></label>
                        <p id="detailDepartment" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6;"></p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Maximum Load</strong></label>
                        <p id="detailMaxLoad" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Teaching Units</strong></label>
                        <p id="detailTeachingUnits" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600;"></p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label"><strong>Remaining Load</strong></label>
                        <p id="detailRemainingLoad" style="margin: 5px 0; padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #28a745;"></p>
                    </div>
                </div>
            </div>

            <!-- Assigned Sections Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Instructor Sections <span id="detailAssignedSectionsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedSections" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">
                    No instructor sections assigned
                </div>
            </div>
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Adviser Sections <span id="detailAssignedAdviserSectionsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedAdviserSections" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">
                    No adviser sections assigned
                </div>
            </div>

            <!-- Assigned Subjects Section -->
            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Assigned Subjects <span id="detailAssignedSubjectsCount" style="background: #0d6efd; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">0</span></h6>
                <div id="detailAssignedSubjects" style="margin: 0; padding: 10px; background: white; border-radius: 3px; border: 1px solid #dee2e6; color: #6c757d;">No subjects assigned</div>
            </div>

            <!-- Class Schedule Section -->
            <div style="padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <h6 style="margin-bottom: 12px; font-weight: 600; color: #333;">Schedule Status</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: white; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Subject Code</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Section</th>
                                <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Day</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Time</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Room</th>
                            </tr>
                        </thead>
                        <tbody id="detailScheduleList">
                            <tr>
                                <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">No schedules assigned</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <details style="margin-top: 25px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d;">
                <summary style="cursor: pointer; font-weight: 600; color: #333; font-size: 1rem;">Faculty Load History</summary>
                <div id="detailLoadHistory" style="margin-top: 15px; color: #6c757d;">Loading historical assignments...</div>
            </details>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeFacultyLoadDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Section Students Modal -->
<div class="modal" id="sectionStudentsModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Section Students</h5>
            <button type="button" class="modal-close" onclick="closeSectionStudentsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" class="form-control" id="sectionStudentSearch" placeholder="Search students..." style="margin-bottom: 15px;">
            <div style="max-height: 400px; overflow-y: auto;">
                <table style="width: 100%; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 8px; text-align: left;">Student ID</th>
                            <th style="padding: 8px; text-align: left;">Name</th>
                            <th style="padding: 8px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="sectionStudentsList">
                        <tr>
                            <td colspan="3" style="padding: 8px; text-align: center; color: #6c757d;">No students loaded</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab Persistence - Save and restore active tab using localStorage
    const ACTIVE_TAB_KEY = 'academicsManagement_activeTab';
    
    // Function to switch to a specific tab
    function switchToTab(tabName) {
        document.querySelectorAll('.academics-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.querySelectorAll('.academics-tab').forEach(t => {
            t.classList.remove('active');
        });
        
        const tab = document.querySelector(`.academics-tab[data-tab="${tabName}"]`);
        if (tab) {
            tab.classList.add('active');
        }
        
        const panel = document.getElementById(`tab-${tabName}`);
        if (panel) {
            panel.classList.add('active');
        }
        
        // Save active tab to localStorage
        localStorage.setItem(ACTIVE_TAB_KEY, tabName);
    }
    
    // Tab Switching - Click handlers
    document.querySelectorAll('.academics-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            switchToTab(tabName);
        });
    });
    
    // Restore active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedTab = localStorage.getItem(ACTIVE_TAB_KEY);
        if (savedTab) {
            // Give DOM a moment to fully load before switching
            setTimeout(() => {
                switchToTab(savedTab);
            }, 100);
        }
    });

    // Modal Functions
    function closeModalById(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    function openModalById(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    const subjectCatalogDetails = <?php echo json_encode($subjectAssignmentsById, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

    // Curriculum Details
    function viewCurriculumDetails(programId) {
        openModalById('curriculumDetailsModal');
        document.getElementById('currProgramName').value = 'Loading...';
        document.getElementById('currVersion').value = 'Loading...';
        document.getElementById('currUnits').value = 'Loading...';
        document.getElementById('currStatus').value = 'Loading...';
        document.getElementById('subjectFlowList').innerHTML = '<div class="text-muted">Loading curriculum details...</div>';
        document.getElementById('enrollmentSummaryContent').innerHTML = '<div class="text-muted">Loading summary...</div>';

        fetch(`/sms/modules/college-coor/api/get_curriculum.php?course_id=${encodeURIComponent(programId)}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('currProgramName').value = data.program_name || 'No program data found';
                document.getElementById('currVersion').value = data.curriculum_name || 'No curriculum data found';
                document.getElementById('currUnits').value = data.effective_year || 'No curriculum data found';
                document.getElementById('currStatus').value = data.status || 'No curriculum data found';

                if (data.subject_flow && data.subject_flow.length > 0) {
                    const flowHtml = data.subject_flow.map(yearGroup => {
                        const yearLabel = yearGroup.year_level === 1 ? 'FIRST YEAR' :
                            yearGroup.year_level === 2 ? 'SECOND YEAR' :
                            yearGroup.year_level === 3 ? 'THIRD YEAR' :
                            yearGroup.year_level === 4 ? 'FOURTH YEAR' : `YEAR ${yearGroup.year_level}`;

                        const semestersHtml = yearGroup.semesters.map(semGroup => {
                            const subjectsHtml = (semGroup.subjects || []).map(subject => `
                                <tr>
                                    <td style="padding: 6px 8px; border-bottom: 1px solid #e9ecef;">${subject.code || ''}</td>
                                    <td style="padding: 6px 8px; border-bottom: 1px solid #e9ecef;">${subject.name || ''}</td>
                                </tr>`).join('');

                            return `
                                <div style="margin-bottom: 10px;">
                                    <strong>${semGroup.semester || 'N/A'}</strong>
                                    <table style="width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 13px;">
                                        <thead>
                                            <tr style="background: #e9ecef;">
                                                <th style="text-align: left; padding: 6px 8px;">Code</th>
                                                <th style="text-align: left; padding: 6px 8px;">Subject</th>
                                            </tr>
                                        </thead>
                                        <tbody>${subjectsHtml}</tbody>
                                    </table>
                                </div>`;
                        }).join('');

                        return `<div style="margin-bottom: 18px;"><h6 style="margin: 0 0 8px 0; font-size: 15px; text-transform: uppercase; color: #0d6efd;">${yearLabel}</h6>${semestersHtml}</div>`;
                    }).join('');

                    document.getElementById('subjectFlowList').innerHTML = flowHtml;
                } else {
                    document.getElementById('subjectFlowList').innerHTML = '<div class="text-muted">No subject flow available for this curriculum.</div>';
                }

                const summary = data.enrollment_summary || {};
                const summaryRows = [
                    { label: 'First Year', count: summary.year_levels?.first_year?.enrolled_students ?? 0, units: summary.year_levels?.first_year?.total_curriculum_units ?? 0 },
                    { label: 'Second Year', count: summary.year_levels?.second_year?.enrolled_students ?? 0, units: summary.year_levels?.second_year?.total_curriculum_units ?? 0 },
                    { label: 'Third Year', count: summary.year_levels?.third_year?.enrolled_students ?? 0, units: summary.year_levels?.third_year?.total_curriculum_units ?? 0 },
                    { label: 'Fourth Year', count: summary.year_levels?.fourth_year?.enrolled_students ?? 0, units: summary.year_levels?.fourth_year?.total_curriculum_units ?? 0 }
                ];

                const summaryHtml = `
                    <div style="margin-bottom: 12px;">
                        <strong>Total Enrolled Students:</strong> ${Number(summary.total_enrolled_students ?? 0)}
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #e9ecef;">
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Year Level</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Enrolled Students</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Total Curriculum Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${summaryRows.map(row => `
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.label}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.count}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.units}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>`;

                document.getElementById('enrollmentSummaryContent').innerHTML = summaryHtml;
            })
            .catch((error) => {
                console.error('Curriculum details fetch failed:', error);
                document.getElementById('currProgramName').value = 'No program data found';
                document.getElementById('currVersion').value = 'No curriculum data found';
                document.getElementById('currUnits').value = 'No curriculum data found';
                document.getElementById('currStatus').value = 'No curriculum data found';
                document.getElementById('subjectFlowList').innerHTML = '<div class="text-muted">Unable to load curriculum details.</div>';
                document.getElementById('enrollmentSummaryContent').innerHTML = '<div class="text-muted">Unable to load enrollment summary.</div>';
            });
    }

    function closeCurriculumModal() {
        closeModalById('curriculumDetailsModal');
    }

    // Subject Details
    function viewSubjectDetails(subjectId) {
        const details = subjectCatalogDetails[subjectId] || [];
        const subjectInfo = details.length ? details[0] : null;

        openModalById('subjectDetailsModal');
        document.getElementById('subjCode').value = subjectInfo?.code || 'N/A';
        document.getElementById('subjName').value = subjectInfo?.name || 'N/A';
        document.getElementById('subjUnits').value = subjectInfo?.units ?? '0';
        document.getElementById('subjLectureHours').value = subjectInfo?.lecture_hours ?? '0';
        document.getElementById('subjLabHours').value = subjectInfo?.lab_hours ?? '0';

        const tbody = document.getElementById('subjectAssignmentBody');
        tbody.innerHTML = '';

        if (!details.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No curriculum assignments found for this subject.</td></tr>';
            document.getElementById('subjectAssignmentStatus').textContent = 'Status: Unassigned';
            return;
        }

        details.forEach(assign => {
            const row = document.createElement('tr');
            const yearLevel = assign.year_level === '1' ? '1st Year' : assign.year_level === '2' ? '2nd Year' : assign.year_level === '3' ? '3rd Year' : assign.year_level === '4' ? '4th Year' : (assign.year_level || 'Unassigned');
            const semester = assign.semester ? `${assign.semester} Semester` : 'Unassigned';
            const statusBadge = assign.curriculum_id
                ? `<span class="badge ${assign.is_active == 1 ? 'bg-success' : 'bg-secondary'}">${assign.is_active == 1 ? 'Active' : 'Inactive'}</span>`
                : '<span class="badge bg-warning text-dark">Unassigned</span>';

            row.innerHTML = `
                <td>${assign.course_code ? `${assign.course_code}` : 'Unassigned'}</td>
                <td>${assign.course_name ? `${assign.course_name}` : 'Unassigned'}</td>
                <td>${assign.curriculum_name ? `${assign.curriculum_name}` : 'Unassigned'}</td>
                <td>${assign.effective_year ? `${assign.effective_year}` : 'Unassigned'}</td>
                <td>${yearLevel}</td>
                <td>${semester}</td>
                <td>${statusBadge}</td>
            `;
            tbody.appendChild(row);
        });

        const activeCount = details.filter(assign => assign.curriculum_id && assign.is_active == 1).length;
        const inactiveCount = details.filter(assign => assign.curriculum_id && assign.is_active != 1).length;
        const statusText = activeCount > 0 ? 'Active' : (inactiveCount > 0 ? 'Inactive' : 'Unassigned');
        document.getElementById('subjectAssignmentStatus').innerHTML = `<strong>Status:</strong> <span class="badge ${statusText === 'Active' ? 'bg-success' : statusText === 'Inactive' ? 'bg-secondary' : 'bg-warning text-dark'}">${statusText}</span>`;
    }

    function closeSubjectModal() {
        closeModalById('subjectDetailsModal');
    }

    // Section Management
    function loadSectionSchoolYears() {
        const schoolYearSelect = document.getElementById('sectionSchoolYear');
        const semesterSelect = document.getElementById('sectionSemester');
        
        schoolYearSelect.innerHTML = '<option value="">Select School Year</option>';
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        semesterSelect.disabled = true;
        
        fetch('/sms/modules/college-coor/api/get_school_years.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.school_years && data.school_years.length > 0) {
                    schoolYearSelect.innerHTML = '<option value="">Select School Year</option>' +
                        data.school_years.map(year => `
                            <option value="${year.id}">${year.name}</option>
                        `).join('');
                } else {
                    schoolYearSelect.innerHTML = '<option value="">No active school years found</option>';
                }
            })
            .catch(error => {
                console.error('Error loading school years:', error);
                schoolYearSelect.innerHTML = '<option value="">Error loading school years</option>';
            });
    }
    
    function loadSectionSemesters() {
        const schoolYearSelect = document.getElementById('sectionSchoolYear');
        const semesterSelect = document.getElementById('sectionSemester');
        const schoolYearId = schoolYearSelect.value;
        
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        semesterSelect.disabled = true;
        
        if (!schoolYearId) {
            return;
        }
        
        fetch(`/sms/modules/college-coor/api/get_semesters.php?school_year_id=${schoolYearId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.semesters && data.semesters.length > 0) {
                    semesterSelect.innerHTML = '<option value="">Select Semester</option>' +
                        data.semesters.map(semester => `
                            <option value="${semester.id}">${semester.name}</option>
                        `).join('');
                    semesterSelect.disabled = false;
                } else {
                    semesterSelect.innerHTML = '<option value="">No active semesters found</option>';
                    semesterSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading semesters:', error);
                semesterSelect.innerHTML = '<option value="">Error loading semesters</option>';
                semesterSelect.disabled = true;
            });
    }

    function openAddSectionModal() {
        document.getElementById('sectionId').value = '';
        document.getElementById('sectionModalTitle').textContent = 'Add Section';
        document.getElementById('sectionCode').value = '';
        document.getElementById('sectionProgram').value = '';
        document.getElementById('sectionYear').value = '';
        document.getElementById('sectionCapacity').value = '';
        document.getElementById('sectionSchoolYear').value = '';
        document.getElementById('sectionSemester').value = '';
        document.getElementById('sectionSemester').disabled = true;
        
        loadSectionSchoolYears();
        openModalById('sectionModal');
    }

    function editSection(sectionId) {
        document.getElementById('sectionId').value = sectionId;
        document.getElementById('sectionModalTitle').textContent = 'Edit Section';
        document.getElementById('sectionCode').value = 'IT-1A';
        document.getElementById('sectionProgram').value = 'BS-IT';
        document.getElementById('sectionYear').value = '1st';
        document.getElementById('sectionCapacity').value = '45';
        document.getElementById('sectionSchoolYear').value = '';
        document.getElementById('sectionSemester').value = '';
        document.getElementById('sectionSemester').disabled = true;
        
        loadSectionSchoolYears();
        openModalById('sectionModal');
    }

    function closeSectionModal() {
        closeModalById('sectionModal');
    }

    function assignAdviser(sectionId, sectionCode) {
        document.getElementById('adviserSectionId').value = sectionId;
        document.getElementById('adviserSectionCode').textContent = sectionCode || 'N/A';
        document.getElementById('adviserSectionYear').textContent = 'N/A';
        document.getElementById('adviserSectionSemester').textContent = 'N/A';
        document.getElementById('adviserSectionSchoolYear').textContent = 'N/A';
        document.getElementById('adviserCurrentAdviser').textContent = 'Not Assigned';
        document.getElementById('adviserSelect').value = '';
        document.getElementById('adviserModalTitle').textContent = 'Assign Adviser';
        document.getElementById('adviserActionButton').textContent = 'Assign Adviser';
        openModalById('adviserModal');
    }

    function closeAdviserModal() {
        closeModalById('adviserModal');
    }

    function openAssignAdviserModal(row) {
        if (!row) return;
        const sectionId = row.dataset.sectionId || '';
        const sectionCode = row.querySelector('td')?.textContent.trim() || 'N/A';
        const sectionYear = row.dataset.yearLevel || 'N/A';
        const sectionSemester = row.dataset.semester || 'N/A';
        const sectionSchoolYear = row.dataset.schoolYear || 'N/A';
        const adviserName = row.dataset.adviser || 'Not Assigned';
        const hasAdviser = adviserName !== 'Not Assigned';

        document.getElementById('adviserSectionId').value = sectionId;
        document.getElementById('adviserSectionCode').textContent = sectionCode;
        document.getElementById('adviserSectionYear').textContent = sectionYear;
        document.getElementById('adviserSectionSemester').textContent = sectionSemester;
        document.getElementById('adviserSectionSchoolYear').textContent = sectionSchoolYear;
        document.getElementById('adviserCurrentAdviser').textContent = adviserName;
        document.getElementById('adviserSelect').value = '';
        document.getElementById('adviserModalTitle').textContent = hasAdviser ? 'Reassign Adviser' : 'Assign Adviser';
        document.getElementById('adviserActionButton').textContent = hasAdviser ? 'Reassign Adviser' : 'Assign Adviser';
        openModalById('adviserModal');
    }

    function viewSectionDetails(row) {
        if (!row) return;
        const sectionCode = row.querySelector('td')?.textContent.trim() || 'N/A';
        const sectionYear = row.dataset.yearLevel || 'N/A';
        const sectionSemester = row.dataset.semester || 'N/A';
        const sectionSchoolYear = row.dataset.schoolYear || 'N/A';
        const totalStudents = row.dataset.totalStudents || '0';
        const adviserName = row.dataset.adviser || 'Not Assigned';
        const statusText = row.dataset.status || 'Active';

        document.getElementById('sectionDetailsCode').textContent = sectionCode;
        document.getElementById('sectionDetailsYear').textContent = sectionYear;
        document.getElementById('sectionDetailsSemester').textContent = sectionSemester;
        document.getElementById('sectionDetailsSchoolYear').textContent = sectionSchoolYear;
        document.getElementById('sectionDetailsTotalStudents').textContent = totalStudents;
        document.getElementById('sectionDetailsAdviser').textContent = adviserName;
        const statusBadge = document.getElementById('sectionDetailsStatus');
        statusBadge.textContent = statusText;
        statusBadge.className = statusText.toLowerCase() === 'inactive' ? 'badge badge-secondary' : 'badge badge-success';
        openModalById('sectionDetailsModal');
    }

    function closeSectionDetailsModal() {
        closeModalById('sectionDetailsModal');
    }

    function viewSectionStudents(sectionId) {
        openModalById('sectionStudentsModal');
        const sampleStudents = [
            { id: 'STU-0001', name: 'Maria Santos Garcia', status: 'Active' },
            { id: 'STU-0002', name: 'Juan Carlos Reyes', status: 'Active' },
            { id: 'STU-0003', name: 'Ana Maria Cruz', status: 'Active' },
            { id: 'STU-0004', name: 'Miguel Fernando Lopez', status: 'Active' },
            { id: 'STU-0005', name: 'Rosa Isabel Ocampo', status: 'Active' }
        ];
        
        const tbody = document.getElementById('sectionStudentsList');
        tbody.innerHTML = sampleStudents.map(std => `
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${std.id}</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${std.name}</td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><span class="badge badge-success">${std.status}</span></td>
            </tr>
        `).join('');
    }

    function closeSectionStudentsModal() {
        closeModalById('sectionStudentsModal');
    }

    // Faculty Load Management
    function openAddFacultyLoadModal() {
        document.getElementById('facultyLoadId').value = '';
        document.getElementById('facultyLoadModalTitle').textContent = 'Assign Faculty Load';
        document.getElementById('facultySelect').value = '';
        document.getElementById('loadSectionSelect').value = '';
        document.getElementById('loadSubject').value = '';
        document.getElementById('loadUnits').value = '3';
        document.getElementById('loadSchedule').value = '';
        document.getElementById('conflictWarning').style.display = 'none';
        openModalById('facultyLoadModal');
    }

    function editFacultyLoad(loadId) {
        document.getElementById('facultyLoadId').value = loadId;
        document.getElementById('facultyLoadModalTitle').textContent = 'Edit Faculty Load';
        document.getElementById('facultySelect').value = 'Dr. Maria Santos';
        document.getElementById('loadSectionSelect').value = 'IT-1A';
        document.getElementById('loadSubject').value = 'SUBJ101';
        document.getElementById('loadUnits').value = '3';
        document.getElementById('loadSchedule').value = 'MWF 9:00-10:30';
        document.getElementById('conflictWarning').style.display = 'none';
        openModalById('facultyLoadModal');
    }

    function deleteFacultyLoad(loadId) {
        if (confirm('Are you sure you want to delete this faculty load assignment?')) {
            // Handle delete
            alert('Faculty load deleted successfully');
        }
    }

    function closeFacultyLoadModal() {
        closeModalById('facultyLoadModal');
    }

    function viewFacultyLoadDetails(facultyId, facultyName, teachingUnits, maxLoad, assignedSections, assignedSubjects, department) {
        // Set faculty info
        document.getElementById('detailFacultyName').textContent = facultyName || 'N/A';
        document.getElementById('detailDepartment').textContent = department || 'N/A';
        document.getElementById('detailMaxLoad').textContent = maxLoad + ' Units';
        document.getElementById('detailTeachingUnits').textContent = teachingUnits + ' Units';
        
        const remainingLoad = Math.max(0, maxLoad - teachingUnits);
        document.getElementById('detailRemainingLoad').textContent = remainingLoad + ' Units';
        
        // Set assigned sections and subjects counts
        document.getElementById('detailAssignedSectionsCount').textContent = assignedSections || 0;
        document.getElementById('detailAssignedAdviserSectionsCount').textContent = 0;
        document.getElementById('detailAssignedSubjectsCount').textContent = assignedSubjects || 0;
        
        const instructorSectionsContainer = document.getElementById('detailAssignedSections');
        const adviserSectionsContainer = document.getElementById('detailAssignedAdviserSections');
        const assignedSubjectsContainer = document.getElementById('detailAssignedSubjects');
        instructorSectionsContainer.innerHTML = '<div style="color: #6c757d;">Loading instructor assignments...</div>';
        adviserSectionsContainer.innerHTML = '<div style="color: #6c757d;">Loading adviser assignments...</div>';
        assignedSubjectsContainer.innerHTML = '<div style="color: #6c757d;">Loading assigned subjects...</div>';
        
        fetch(`/sms/modules/college-coor/api/get_faculty_sections.php?faculty_id=${facultyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.sections && data.sections.length > 0) {
                    const instructorSections = data.sections.filter(section => section.role === 'Instructor');
                    const adviserSections = data.sections.filter(section => section.role === 'Adviser');

                    function groupByYearSemester(sections) {
                        return sections.reduce((groups, section) => {
                            const year = section.school_year || 'Unknown School Year';
                            const semester = section.semester || 'Unknown Semester';
                            const key = `${year}||${semester}`;
                            if (!groups[key]) {
                                groups[key] = { year, semester, sections: [] };
                            }
                            groups[key].sections.push(section);
                            return groups;
                        }, {});
                    }

                    function renderGroupedSections(groups) {
                        return Object.values(groups).map(group => {
                            const header = `<div style="margin-bottom: 10px; font-weight: 700;">${group.year} - ${group.semester}</div>`;
                            const rows = group.sections.map(section => {
                                const yearLabel = getSectionYearLabel(section.grade_level || '0');
                                const warning = section.period_mismatch ? `<div style="color: #dc3545; margin-top: 10px; font-weight: 600;">Warning: selected semester does not belong to this section's school year.</div>` : '';
                                return `
                                    <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                                        <h6 style="margin: 0 0 10px; font-weight: 700;">${section.section_code || 'N/A'}</h6>
                                        <div style="margin-bottom: 8px;"><strong>Program:</strong> ${section.program_code || 'N/A'}</div>
                                        <div style="margin-bottom: 8px;"><strong>Year Level:</strong> ${yearLabel}</div>
                                        <div style="margin-bottom: 8px;"><strong>Status:</strong> ${section.status || 'N/A'}</div>
                                        <div style="margin-bottom: 8px;"><strong>Assigned At:</strong> ${section.assigned_at ? new Date(section.assigned_at).toLocaleString() : 'N/A'}</div>
                                        ${warning}
                                    </div>
                                `;
                            }).join('');
                            return `<div style="margin-bottom: 20px;">${header}${rows}</div>`;
                        }).join('');
                    }

                    if (instructorSections.length > 0) {
                        const instructorGroups = groupByYearSemester(instructorSections);
                        instructorSectionsContainer.innerHTML = renderGroupedSections(instructorGroups);
                    } else {
                        instructorSectionsContainer.innerHTML = '<div style="color: #6c757d;">No instructor sections assigned.</div>';
                    }

                    if (adviserSections.length > 0) {
                        const adviserGroups = groupByYearSemester(adviserSections);
                        adviserSectionsContainer.innerHTML = renderGroupedSections(adviserGroups);
                    } else {
                        adviserSectionsContainer.innerHTML = '<div style="color: #6c757d;">No adviser sections assigned.</div>';
                    }

                    document.getElementById('detailAssignedSectionsCount').textContent = instructorSections.length;
                    document.getElementById('detailAssignedAdviserSectionsCount').textContent = adviserSections.length;
                } else {
                    instructorSectionsContainer.innerHTML = '<div style="color: #6c757d;">No instructor sections assigned.</div>';
                    adviserSectionsContainer.innerHTML = '<div style="color: #6c757d;">No adviser sections assigned.</div>';
                    document.getElementById('detailAssignedSectionsCount').textContent = 0;
                    document.getElementById('detailAssignedAdviserSectionsCount').textContent = 0;
                }
            })
            .catch(error => {
                console.error('Error loading faculty sections:', error);
                instructorSectionsContainer.innerHTML = '<div style="color: #dc3545;">Unable to load instructor assignments.</div>';
                adviserSectionsContainer.innerHTML = '<div style="color: #dc3545;">Unable to load adviser assignments.</div>';
            });

        // Fetch assignment detail rows from faculty load
        fetch(`/sms/modules/college-coor/api/get_faculty_assignments.php?faculty_id=${facultyId}`)
            .then(response => response.json())
            .then(data => {
                const scheduleList = document.getElementById('detailScheduleList');
                
                if (data.success && data.assignments && data.assignments.length > 0) {
                    const grouped = data.assignments.reduce((groups, assignment) => {
                        const year = assignment.school_year || 'Unknown School Year';
                        const semester = assignment.semester || 'Unknown Semester';
                        const key = `${year}||${semester}`;
                        if (!groups[key]) {
                            groups[key] = { year, semester, rows: [] };
                        }
                        groups[key].rows.push(assignment);
                        return groups;
                    }, {});

                    assignedSubjectsContainer.innerHTML = Object.values(grouped).map(group => {
                        const rowsHtml = group.rows.map(assignment => {
                            return `
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.section_code || 'N/A'}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_code || 'N/A'}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_name || 'N/A'}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.units || 0}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.lecture_hours || 0}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.lab_hours || 0}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.assignment_status || 'Active'}</td>
                                </tr>
                            `;
                        }).join('');

                        return `
                            <div style="margin-bottom: 1.25rem;">
                                <div style="font-weight: 700; margin-bottom: 0.75rem;">${group.year} - ${group.semester}</div>
                                <div style="overflow-x:auto;">
                                    <table style="width:100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9;">
                                                <th style="padding: 10px; text-align:left;">Section Code</th>
                                                <th style="padding: 10px; text-align:left;">Subject Code</th>
                                                <th style="padding: 10px; text-align:left;">Subject Name</th>
                                                <th style="padding: 10px; text-align:center;">Units</th>
                                                <th style="padding: 10px; text-align:center;">Lecture Hours</th>
                                                <th style="padding: 10px; text-align:center;">Lab Hours</th>
                                                <th style="padding: 10px; text-align:center;">Assignment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${rowsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    }).join('');

                    document.getElementById('detailAssignedSubjectsCount').textContent = data.assignments.length;
                } else {
                    assignedSubjectsContainer.innerHTML = '<div style="color: #6c757d;">No subjects assigned.</div>';
                    document.getElementById('detailAssignedSubjectsCount').textContent = 0;
                }

                scheduleList.innerHTML = data.success && data.assignments && data.assignments.length > 0
                    ? data.assignments.map(assignment => {
                        const status = assignment.schedule_status || 'Not Yet Scheduled';
                        const statusBadgeClass = status === 'Scheduled' ? 'badge-success' : 'badge-warning';
                        return `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_code || 'N/A'}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.section_code || 'N/A'}</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;"><span class="badge ${statusBadgeClass}">${status}</span></td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">-</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">-</td>
                            </tr>
                        `;
                    }).join('')
                    : '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">No schedules assigned</td></tr>';

                const historyContainer = document.getElementById('detailLoadHistory');
                historyContainer.innerHTML = '<div style="color: #6c757d;">Loading historical assignments...</div>';
                fetch(`/sms/modules/college-coor/api/get_faculty_assignments_history.php?faculty_id=${facultyId}`)
                    .then(response => response.json())
                    .then(historyData => {
                        if (historyData.success && historyData.history && historyData.history.length > 0) {
                            const groupedHistory = historyData.history.reduce((groups, assignment) => {
                                const year = assignment.school_year || 'Unknown School Year';
                                const semester = assignment.semester || 'Unknown Semester';
                                const section = assignment.section_code || 'Unknown Section';
                                const key = `${year}||${semester}`;

                                if (!groups[key]) {
                                    groups[key] = { year, semester, sections: {} };
                                }

                                if (!groups[key].sections[section]) {
                                    groups[key].sections[section] = { section_code: section, assignments: [] };
                                }

                                groups[key].sections[section].assignments.push(assignment);
                                return groups;
                            }, {});

                            historyContainer.innerHTML = Object.values(groupedHistory).map(group => {
                                return `
                                    <div style="margin-bottom: 18px;">
                                        <div style="font-weight: 700; margin-bottom: 8px;">School Year: ${group.year} · Semester: ${group.semester}</div>
                                        ${Object.values(group.sections).map(sectionGroup => `
                                            <div style="margin-left: 16px; margin-bottom: 12px;">
                                                <div style="font-weight: 600; margin-bottom: 6px;">${sectionGroup.section_code}</div>
                                                ${sectionGroup.assignments.map(assignment => `
                                                    <div style="margin-left: 16px; margin-bottom: 8px; padding: 10px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center;">
                                                        <div>
                                                            <div style="font-weight: 600;">${assignment.subject_code || 'N/A'} - ${assignment.subject_name || 'N/A'}</div>
                                                            <div style="color: #6c757d; font-size: 0.95rem;">Section: ${assignment.section_code || 'N/A'} · Units: ${assignment.units || 0} · Assigned: ${assignment.assignment_date ? new Date(assignment.assignment_date).toLocaleDateString() : 'N/A'}</div>
                                                            <div style="color: #6c757d; font-size: 0.95rem;">Status: ${assignment.status || 'Inactive'} · Reason: ${assignment.reason || 'Academic Period Inactive'}</div>
                                                        </div>
                                                        <span style="background: #6c757d; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">${assignment.status || 'Inactive'}</span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        `).join('')}
                                    </div>
                                `;
                            }).join('');
                        } else {
                            historyContainer.innerHTML = '<div style="color: #6c757d;">No historical assignments found.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading faculty load history:', error);
                        historyContainer.innerHTML = '<div style="color: #dc3545;">Unable to load faculty load history.</div>';
                    });
            })
            .catch(error => {
                console.error('Error fetching assignments:', error);
                assignedSubjectsContainer.innerHTML = '<div style="color: #dc3545;">Error loading assigned subjects.</div>';
                document.getElementById('detailScheduleList').innerHTML = '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #dc3545;">Error loading assignments</td></tr>';
            });
        
        openModalById('facultyLoadDetailsModal');
    }

    function closeFacultyLoadDetailsModal() {
        closeModalById('facultyLoadDetailsModal');
    }

    // Assign Faculty Load Modal Functions
    let tempAssignments = [];
    let currentAssignFacultyId = null;

    function initializeAssignLoadModalEvents() {
        const schoolYearSelect = document.getElementById('assignLoadSchoolYear');
        const semesterSelect = document.getElementById('assignLoadSemester');

        if (schoolYearSelect.dataset.initialized === 'true') {
            return;
        }

        schoolYearSelect.addEventListener('change', function() {
            loadSemestersForSchoolYear();
        });

        semesterSelect.addEventListener('change', function() {
            loadSectionsForAssignment();
        });

        schoolYearSelect.dataset.initialized = 'true';
    }

    function openAssignFacultyLoadModal(facultyId, facultyName, teachingUnits, maxLoad, department) {
        initializeAssignLoadModalEvents();

        currentAssignFacultyId = facultyId;
        document.getElementById('assignLoadFacultyId').value = facultyId;
        document.getElementById('assignLoadFacultyName').value = facultyName;
        document.getElementById('assignLoadDepartment').value = department;

        const schoolYearSelect = document.getElementById('assignLoadSchoolYear');
        const semesterSelect = document.getElementById('assignLoadSemester');
        const sectionSelect = document.getElementById('assignLoadSection');

        schoolYearSelect.innerHTML = '<option value="">Select School Year</option>';
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        semesterSelect.disabled = true;
        sectionSelect.innerHTML = '';
        sectionSelect.disabled = true;

        loadSchoolYears();
        openModalById('assignFacultyLoadModal');
    }

    function loadSchoolYears() {
        const schoolYearSelect = document.getElementById('assignLoadSchoolYear');
        const semesterSelect = document.getElementById('assignLoadSemester');
        const sectionSelect = document.getElementById('assignLoadSection');

        schoolYearSelect.innerHTML = '<option value="">Select School Year</option>';
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        semesterSelect.disabled = true;
        sectionSelect.innerHTML = '';
        sectionSelect.disabled = true;

        fetch('/sms/modules/college-coor/api/get_school_years.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.school_years && data.school_years.length > 0) {
                    schoolYearSelect.innerHTML = '<option value="">Select School Year</option>' +
                        data.school_years.map(year => `
                            <option value="${year.id}">${year.name}</option>
                        `).join('');
                    schoolYearSelect.value = data.school_years[0].id;
                    loadSemestersForSchoolYear();
                } else {
                    schoolYearSelect.innerHTML = '<option value="">No active school years found</option>';
                }
            })
            .catch(error => {
                console.error('Error loading school years:', error);
                schoolYearSelect.innerHTML = '<option value="">Error loading school years</option>';
            });
    }

    function loadSemestersForSchoolYear() {
        const schoolYearSelect = document.getElementById('assignLoadSchoolYear');
        const semesterSelect = document.getElementById('assignLoadSemester');
        const sectionSelect = document.getElementById('assignLoadSection');
        const schoolYearId = schoolYearSelect.value;

        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        semesterSelect.disabled = true;
        sectionSelect.innerHTML = '';
        sectionSelect.disabled = true;

        if (!schoolYearId) {
            return;
        }

        fetch(`/sms/modules/college-coor/api/get_semesters.php?school_year_id=${schoolYearId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.semesters && data.semesters.length > 0) {
                    semesterSelect.innerHTML = '<option value="">Select Semester</option>' +
                        data.semesters.map(semester => `
                            <option value="${semester.id}">${semester.name}</option>
                        `).join('');
                    semesterSelect.value = data.semesters[0].id;
                    semesterSelect.disabled = false;
                    loadSectionsForAssignment();
                } else {
                    semesterSelect.innerHTML = '<option value="">No active semesters found</option>';
                    semesterSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading semesters:', error);
                semesterSelect.innerHTML = '<option value="">Error loading semesters</option>';
                semesterSelect.disabled = true;
            });
    }

    function getSectionYearLabel(yearLevel) {
        const year = parseInt(yearLevel, 10);
        if (year === 1) return '1st Year';
        if (year === 2) return '2nd Year';
        if (year === 3) return '3rd Year';
        return `${year}th Year`;
    }

    function loadSectionsForAssignment() {
        const schoolYearSelect = document.getElementById('assignLoadSchoolYear');
        const semesterSelect = document.getElementById('assignLoadSemester');
        const sectionSelect = document.getElementById('assignLoadSection');
        const schoolYearId = schoolYearSelect.value;
        const semesterId = semesterSelect.value;
        const facultyId = document.getElementById('assignLoadFacultyId').value;

        sectionSelect.innerHTML = '';
        sectionSelect.disabled = true;

        if (!schoolYearId || !semesterId || !facultyId) {
            return;
        }

        fetch(`/sms/modules/college-coor/api/get_adviser_sections.php?faculty_id=${facultyId}&school_year_id=${schoolYearId}&semester_id=${semesterId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.sections && data.sections.length > 0) {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>' +
                        data.sections.map(section => `
                            <option value="${section.id}" data-code="${section.section_code}" data-program="${section.program_code}" data-year-level="${section.grade_level}">
                                ${section.section_code} | ${section.program_code} | ${getSectionYearLabel(section.grade_level)}
                            </option>
                        `).join('');
                    sectionSelect.disabled = false;
                } else {
                    sectionSelect.innerHTML = '<option value="">No sections found</option>';
                    sectionSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading sections:', error);
                sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                sectionSelect.disabled = true;
            });
    }

    function loadSubjectsForSection() {
        const sectionId = document.getElementById('assignLoadSection').value;
        const subjectSelect = document.getElementById('assignLoadSubject');
        const saveBtn = document.querySelector('#assignFacultyLoadForm button[type="submit"]');
        const modalBody = document.querySelector('#assignFacultyLoadModal .modal-body');
        let alertEl = modalBody ? modalBody.querySelector('#assignSubjectAlert') : null;

        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        subjectSelect.disabled = true;

        if (!sectionId) {
            return;
        }

        fetch(`/sms/modules/college-coor/api/get_section_subjects.php?section_id=${sectionId}`)
            .then(response => response.json())
            .then(data => {
                // remove existing alert
                if (alertEl) { alertEl.remove(); alertEl = null; }

                if (data && data.success && Array.isArray(data.subjects) && data.subjects.length > 0) {
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>' +
                        data.subjects.map(subject => `
                            <option value="${subject.id}" data-code="${subject.code}" data-name="${subject.name}" data-units="${subject.units}">
                                ${subject.code} - ${subject.name} (${subject.units} units)
                            </option>
                        `).join('');
                    subjectSelect.disabled = false;
                    if (saveBtn) saveBtn.disabled = false;
                } else {
                    // create info alert explaining absence of subjects
                    if (modalBody) {
                        alertEl = document.createElement('div');
                        alertEl.id = 'assignSubjectAlert';
                        alertEl.className = 'alert alert-info';
                        alertEl.style.marginBottom = '10px';
                        alertEl.textContent = (data && data.message) ? data.message : 'No subjects are available for the active curriculum. Please wait for the latest Registrar data or contact the Registrar.';
                        modalBody.insertBefore(alertEl, modalBody.firstChild);
                    }
                    subjectSelect.innerHTML = '<option value="">No subjects found for this section</option>';
                    subjectSelect.disabled = true;
                    if (saveBtn) saveBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                if (modalBody) {
                    if (alertEl) { alertEl.remove(); }
                    alertEl = document.createElement('div');
                    alertEl.id = 'assignSubjectAlert';
                    alertEl.className = 'alert alert-warning';
                    alertEl.style.marginBottom = '10px';
                    alertEl.textContent = 'Error loading subjects. Please try again later.';
                    modalBody.insertBefore(alertEl, modalBody.firstChild);
                }
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                subjectSelect.disabled = true;
                if (saveBtn) saveBtn.disabled = true;
            });
    }

    function closeAssignFacultyLoadModal() {
        closeModalById('assignFacultyLoadModal');
    }

    function openAssignFacultySubjectModal(facultyId, facultyName, teachingUnits, maxLoad, department) {
        document.getElementById('assignSubjectFacultyId').value = facultyId;
        document.getElementById('assignSubjectFacultyName').value = facultyName;
        document.getElementById('assignSubjectDepartment').value = department;
        document.getElementById('assignSubjectMaxLoad').value = maxLoad + ' Units';
        document.getElementById('assignSubjectCurrentLoad').value = teachingUnits + ' Units';
        document.getElementById('assignSubjectRemainingLoad').value = Math.max(0, maxLoad - teachingUnits) + ' Units';

        const sectionSelect = document.getElementById('assignSubjectSection');
        const subjectSelect = document.getElementById('assignSubjectSubject');
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        subjectSelect.disabled = true;

        fetch(`/sms/modules/college-coor/api/get_instructor_sections.php?faculty_id=${facultyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.sections && data.sections.length > 0) {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>' +
                        data.sections.map(section => `
                            <option value="${section.section_id}"
                                data-program-id="${section.program_id}"
                                data-year-level="${section.grade_level}"
                                data-semester-id="${section.semester_id}"
                                data-school-year-id="${section.school_year_id}">
                                ${section.section_code} | ${section.program_code || 'N/A'} | ${getSectionYearLabel(section.grade_level)} | ${section.semester || 'N/A'} | ${section.school_year || 'N/A'}
                            </option>
                        `).join('');
                    sectionSelect.disabled = false;
                } else {
                    sectionSelect.innerHTML = '<option value="">No assigned instructor sections found</option>';
                    sectionSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading faculty sections:', error);
                sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                sectionSelect.disabled = true;
            });

        openModalById('assignFacultySubjectModal');
    }

    function closeAssignFacultySubjectModal() {
        closeModalById('assignFacultySubjectModal');
    }

    function loadSubjectsForAssignedSection() {
        const sectionId = document.getElementById('assignSubjectSection').value;
        const subjectSelect = document.getElementById('assignSubjectSubject');
        const saveBtn = document.querySelector('#assignFacultySubjectForm button[type="submit"]');
        const modalBody = document.querySelector('#assignFacultySubjectModal .modal-body');
        let alertEl = modalBody ? modalBody.querySelector('#assignSubjectAlert') : null;

        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        subjectSelect.disabled = true;

        if (!sectionId) {
            return;
        }

        fetch(`/sms/modules/college-coor/api/get_section_subjects.php?section_id=${sectionId}`)
            .then(response => response.json())
            .then(data => {
                // remove existing alert
                if (alertEl) { alertEl.remove(); alertEl = null; }

                if (data && data.success && Array.isArray(data.subjects) && data.subjects.length > 0) {
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>' +
                        data.subjects.map(subject => `
                            <option value="${subject.id}" data-code="${subject.code}" data-name="${subject.name}" data-units="${subject.units}">
                                ${subject.code} - ${subject.name} (${subject.units} units)
                            </option>
                        `).join('');
                    subjectSelect.disabled = false;
                    if (saveBtn) saveBtn.disabled = false;
                } else {
                    // show explanation alert
                    if (modalBody) {
                        alertEl = document.createElement('div');
                        alertEl.id = 'assignSubjectAlert';
                        alertEl.className = 'alert alert-info';
                        alertEl.style.marginBottom = '10px';
                        alertEl.textContent = (data && data.message) ? data.message : 'No subjects are available for the active curriculum. Please wait for the latest Registrar data or contact the Registrar.';
                        modalBody.insertBefore(alertEl, modalBody.querySelector('div'));
                    }
                    subjectSelect.innerHTML = '<option value="">No subjects found for this section</option>';
                    subjectSelect.disabled = true;
                    if (saveBtn) saveBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                if (modalBody) {
                    if (alertEl) { alertEl.remove(); }
                    alertEl = document.createElement('div');
                    alertEl.id = 'assignSubjectAlert';
                    alertEl.className = 'alert alert-warning';
                    alertEl.style.marginBottom = '10px';
                    alertEl.textContent = 'Error loading subjects. Please try again later.';
                    modalBody.insertBefore(alertEl, modalBody.querySelector('div'));
                }
                subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                subjectSelect.disabled = true;
                if (saveBtn) saveBtn.disabled = true;
            });
    }

    function addAssignmentToTemp() {
        const sectionSelect = document.getElementById('assignLoadSection');
        const subjectSelect = document.getElementById('assignLoadSubject');
        const sectionId = sectionSelect.value;
        const subjectId = subjectSelect.value;
        
        // Validation: Check if section and subject are selected
        if (!sectionId || !subjectId) {
            alert('Please select both section and subject');
            return;
        }
        
        // Get section code using data attribute
        const sectionOption = sectionSelect.options[sectionSelect.selectedIndex];
        const sectionCode = sectionOption.getAttribute('data-code') || sectionOption.text || 'N/A';
        
        // Get subject details using data attributes
        const subjectOption = subjectSelect.options[subjectSelect.selectedIndex];
        const subjectCode = subjectOption.getAttribute('data-code') || 'N/A';
        const subjectName = subjectOption.getAttribute('data-name') || 'N/A';
        const units = parseInt(subjectOption.getAttribute('data-units')) || 1;
        
        // Check for duplicates in tempAssignments
        const isDuplicate = tempAssignments.some(item => 
            item.sectionId === sectionId && item.subjectId === subjectId
        );
        
        if (isDuplicate) {
            alert('This subject is already assigned to this section');
            return;
        }
        
        // Add to temporary assignments
        tempAssignments.push({
            sectionId,
            sectionCode,
            subjectId,
            subjectCode,
            subjectName,
            units
        });
        
        // Add row to table
        const tbody = document.getElementById('assignmentTableBody');
        const rowIndex = tempAssignments.length - 1;
        const row = document.createElement('tr');
        row.id = `assignmentRow_${rowIndex}`;
        row.innerHTML = `
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${sectionCode}</td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${subjectCode} - ${subjectName}</td>
            <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; font-weight: 600;">${units}</td>
            <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAssignmentFromTemp(${rowIndex})" style="padding: 4px 10px; font-size: 12px;">
                    Remove
                </button>
            </td>
        `;
        tbody.appendChild(row);
        
        // Update running total
        updateRunningTotal();
        
        // Clear form inputs
        document.getElementById('assignLoadSection').value = '';
        document.getElementById('assignLoadSubject').value = '';
    }

    function removeAssignmentFromTemp(index) {
        // Remove from temporary assignments
        tempAssignments.splice(index, 1);
        
        // Rebuild table
        const tbody = document.getElementById('assignmentTableBody');
        tbody.innerHTML = '';
        
        if (tempAssignments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">No assignments added yet</td></tr>';
        } else {
            tempAssignments.forEach((assignment, idx) => {
                const row = document.createElement('tr');
                row.id = `assignmentRow_${idx}`;
                row.innerHTML = `
                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.sectionCode}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subjectCode} - ${assignment.subjectName}</td>
                    <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; font-weight: 600;">${assignment.units}</td>
                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeAssignmentFromTemp(${idx})" style="padding: 4px 10px; font-size: 12px;">
                            Remove
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Update running total
        updateRunningTotal();
    }

    function updateRunningTotal() {
        const total = tempAssignments.reduce((sum, assignment) => sum + assignment.units, 0);
        document.getElementById('runningTotalUnits').textContent = total;
    }

    // Form submission for Assign Sections
    (function() {
        const assignSectionsForm = document.getElementById('assignFacultyLoadForm');
        console.log('Assign Sections form found:', assignSectionsForm);
        if (assignSectionsForm && assignSectionsForm.dataset.customSubmitAttached) {
            return;
        }

        async function handleAssignSectionsSubmit(e) {
            console.log('Assign Sections submit event fired');
            e.preventDefault();
            
            const facultyId = document.getElementById('assignLoadFacultyId').value;
            const schoolYearId = document.getElementById('assignLoadSchoolYear').value;
            const semesterId = document.getElementById('assignLoadSemester').value;
            const sectionSelect = document.getElementById('assignLoadSection');
            const selectedSections = Array.from(sectionSelect.selectedOptions).map(opt => opt.value).filter(Boolean);

            if (!facultyId) {
                alert('Please select a faculty member');
                return;
            }
            if (!schoolYearId) {
                alert('Please select School Year');
                return;
            }
            if (!semesterId) {
                alert('Please select Semester');
                return;
            }
            if (selectedSections.length === 0) {
                alert('Please select at least one section');
                return;
            }

            // Frontend duplicate check for active instructor assignments
            try {
                const duplicateCheckResponse = await fetch(`/sms/modules/college-coor/api/get_faculty_sections.php?faculty_id=${facultyId}`);
                const duplicateCheckData = await duplicateCheckResponse.json();
                if (duplicateCheckData.success && Array.isArray(duplicateCheckData.sections)) {
                    const activeDuplicates = duplicateCheckData.sections.filter(section => {
                        return section.role === 'Instructor'
                            && (section.status === 'Active' || section.status === null || section.status === undefined)
                            && section.school_year_id == schoolYearId
                            && section.semester_id == semesterId
                            && selectedSections.includes(section.section_id.toString());
                    });

                    if (activeDuplicates.length > 0) {
                        alert('This faculty is already assigned to this section for the selected school year and semester.');
                        return;
                    }
                }
            } catch (checkError) {
                console.error('Error checking duplicate assignments:', checkError);
            }

            const data = {
                faculty_id: facultyId,
                school_year_id: schoolYearId,
                semester_id: semesterId,
                section_ids: selectedSections
            };

            console.log('Submitting instructor assignment with payload:', data);

            fetch('/sms/modules/college-coor/api/add_section_faculty.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(responseData => {
                if (responseData.success) {
                    alert('Section assignments saved successfully');
                    closeAssignFacultyLoadModal();
                    loadFacultyLoadData();
                } else {
                    alert('Error: ' + (responseData.message || 'Failed to save section assignments'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving section assignments: ' + error.message);
            });
        }

        if (assignSectionsForm) {
            assignSectionsForm.addEventListener('submit', handleAssignSectionsSubmit);
            assignSectionsForm.dataset.customSubmitAttached = 'true';
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                const retryForm = document.getElementById('assignFacultyLoadForm');
                console.log('Retry Assign Sections form lookup after DOMContentLoaded:', retryForm);
                if (retryForm) {
                    retryForm.addEventListener('submit', handleAssignSectionsSubmit);
                    retryForm.dataset.customSubmitAttached = 'true';
                }
            });
        }
    })();

    document.getElementById('assignSubjectSection')?.addEventListener('change', loadSubjectsForAssignedSection);
    document.getElementById('assignFacultySubjectForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const facultyId = document.getElementById('assignSubjectFacultyId').value;
        const sectionId = document.getElementById('assignSubjectSection').value;
        const subjectId = document.getElementById('assignSubjectSubject').value;
        const selectedSection = document.getElementById('assignSubjectSection').selectedOptions[0];
        const schoolYearId = selectedSection ? selectedSection.getAttribute('data-school-year-id') : null;
        const semesterId = selectedSection ? selectedSection.getAttribute('data-semester-id') : null;

        if (!facultyId || !sectionId || !subjectId || !schoolYearId || !semesterId) {
            alert('Please select a valid section and subject');
            return;
        }

        const data = {
            faculty_id: facultyId,
            school_year_id: schoolYearId,
            semester_id: semesterId,
            assignments: [
                {
                    section_id: sectionId,
                    subject_id: subjectId
                }
            ]
        };

        fetch('/sms/modules/college-coor/api/save_faculty_load.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(responseData => {
            if (responseData.success) {
                alert('Subject assignment saved successfully');
                closeAssignFacultySubjectModal();
                loadFacultyLoadData();
            } else {
                alert('Error: ' + (responseData.message || 'Failed to save subject assignment'));
            }
        })
        .catch(error => {
            console.error('Error saving subject assignment:', error);
            alert('Error saving subject assignment: ' + error.message);
        });
    });

    // Form Submissions
    document.getElementById('sectionSchoolYear')?.addEventListener('change', loadSectionSemesters);

    document.getElementById('sectionForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const sectionId = document.getElementById('sectionId').value;
        const sectionCode = document.getElementById('sectionCode').value;
        const sectionProgram = document.getElementById('sectionProgram').value;
        const sectionYear = document.getElementById('sectionYear').value;
        const sectionCapacity = document.getElementById('sectionCapacity').value;
        const sectionSchoolYearId = document.getElementById('sectionSchoolYear').value;
        const sectionSemesterId = document.getElementById('sectionSemester').value;
        
        if (!sectionCode || !sectionProgram || !sectionYear || !sectionCapacity || !sectionSchoolYearId || !sectionSemesterId) {
            alert('Please fill in all required fields');
            return;
        }
        
        const formData = new FormData();
        formData.append('section_id', sectionId);
        formData.append('section_code', sectionCode);
        formData.append('program', sectionProgram);
        formData.append('year_level', sectionYear);
        formData.append('capacity', sectionCapacity);
        formData.append('school_year_id', sectionSchoolYearId);
        formData.append('semester_id', sectionSemesterId);
        
        const endpoint = sectionId ? '/sms/modules/college-coor/api/update_section.php' : '/sms/modules/college-coor/api/add_section.php';
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeSectionModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the section');
        });
    });

    document.getElementById('adviserForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const sectionId = document.getElementById('adviserSectionId').value;
        const adviserSelect = document.getElementById('adviserSelect');
        const facultyId = adviserSelect.options[adviserSelect.selectedIndex].getAttribute('data-id');
        
        console.log('Assigning adviser - Section ID:', sectionId, 'Faculty ID:', facultyId);
        
        // Validate inputs
        if (!sectionId || !facultyId) {
            alert('Please select a valid adviser');
            return;
        }

        // Send to API endpoint
        const formData = new FormData();
        formData.append('section_id', sectionId);
        formData.append('faculty_id', facultyId);

        fetch('/sms/modules/college-coor/api/assign_adviser.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('API Response Status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok - Status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response Data:', data);
            if (data.success) {
                alert(data.message);
                closeAdviserModal();
                // Reload the page to show updated adviser from database
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error assigning adviser: ' + error.message);
        });
    });

    document.getElementById('facultyLoadForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Faculty load assigned successfully');
        
        // Refresh faculty load table to show updated Total Units, Classes Assigned, and Load Status
        setTimeout(() => {
            loadFacultyLoadData();
        }, 500);
        
        closeFacultyLoadModal();
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('show');
        }
    });

    // ================================================================
    // FACULTY LOAD - DYNAMIC LOADING AND AUTO-REFRESH
    // ================================================================
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Calculate Load Status based on Teaching Units
    function calculateLoadStatus(totalUnits, maxLoad) {
        totalUnits = parseInt(totalUnits) || 0;
        
        if (totalUnits <= 8) {
            return { status: 'Underloaded', badge: 'badge-warning' };
        } else if (totalUnits >= 9 && totalUnits <= 15) {
            return { status: 'Normal Load', badge: 'badge-success' };
        } else {
            return { status: 'Overloaded', badge: 'badge-danger' };
        }
    }
    
    // Load faculty load data dynamically from API
    function loadFacultyLoadData() {
        const tbody = document.getElementById('facultyTable')?.querySelector('tbody');
        if (!tbody) return;
        
        // Show loading state
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading faculty load...</td></tr>';
        
        fetch('/sms/modules/college-coor/api/get_faculty_load.php')
            .then(response => {
                if (!response.ok) throw new Error('API request failed');
                return response.json();
            })
            .then(facultyLoads => {
                if (!facultyLoads || facultyLoads.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No faculty loads found</td></tr>';
                    return;
                }
                
                // Build HTML for each faculty load row
                tbody.innerHTML = facultyLoads.map(fac => {
                    const assignedSections = parseInt(fac.assigned_sections) || 0;
                    const assignedSubjects = parseInt(fac.assigned_subjects) || 0;
                    const teachingUnits = parseInt(fac.teaching_units) || 0;
                    const maxLoad = parseInt(fac.max_load) || 15;
                    const loadStatusData = calculateLoadStatus(teachingUnits, maxLoad);
                    
                    return `
                        <tr data-faculty-id="${fac.id}" data-faculty-name="${escapeHtml(fac.faculty_name)}" data-max-load="${maxLoad}" data-teaching-units="${teachingUnits}" data-assigned-sections="${assignedSections}" data-assigned-subjects="${assignedSubjects}" data-department="${escapeHtml(fac.department)}">
                            <td>${escapeHtml(fac.faculty_name)}</td>
                            <td>${escapeHtml(fac.department)}</td>
                            <td class="faculty-assigned-sections">${assignedSections}</td>
                            <td class="faculty-assigned-subjects">${assignedSubjects}</td>
                            <td class="faculty-teaching-units">${teachingUnits}</td>
                            <td>${maxLoad}</td>
                            <td class="faculty-load-status"><span class="badge ${loadStatusData.badge}">${loadStatusData.status}</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-sm btn-info" onclick="openAssignFacultyLoadModal('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${escapeHtml(fac.department)}')">
                                        Assign Instructor
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="openAssignFacultySubjectModal('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${escapeHtml(fac.department)}')">
                                        Assign Subjects
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="viewFacultyLoadDetails('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${assignedSections}', '${assignedSubjects}', '${escapeHtml(fac.department)}')">
                                        View Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error('Error loading faculty load:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load faculty load data</td></tr>';
            });
    }
    
    // Update faculty load values for a specific faculty member
    function updateFacultyLoadValues(facultyId) {
        const row = document.querySelector(`tr[data-faculty-id="${facultyId}"]`);
        if (!row) return;
        
        // Fetch updated faculty load data for this specific faculty
        fetch(`/sms/modules/college-coor/api/get_faculty_load.php?faculty_id=${facultyId}`)
            .then(response => response.json())
            .then(data => {
                if (data && (Array.isArray(data) ? data[0] : data)) {
                    const fac = Array.isArray(data) ? data[0] : data;
                    const classesAssigned = parseInt(fac.classes_assigned) || 0;
                    const totalUnits = parseInt(fac.total_units) || 0;
                    const maxLoad = parseInt(fac.max_load) || 15;
                    const loadStatusData = calculateLoadStatus(totalUnits, maxLoad);
                    
                    // Update Classes Assigned cell (auto-calculated)
                    const classesCell = row.querySelector('.faculty-classes-assigned');
                    if (classesCell) classesCell.textContent = classesAssigned;
                    
                    // Update Total Units cell (auto-calculated, read-only)
                    const totalUnitsCell = row.querySelector('.faculty-total-units');
                    if (totalUnitsCell) totalUnitsCell.textContent = totalUnits;
                    
                    // Update Load Status cell (auto-calculated)
                    const statusCell = row.querySelector('.faculty-load-status');
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge ${loadStatusData.badge}">${loadStatusData.status}</span>`;
                    }
                }
            })
            .catch(error => console.error('Error updating faculty load values:', error));
    }
    
    // Refresh faculty load data (can be called from other pages/modules)
    function refreshFacultyLoad() {
        loadFacultyLoadData();
    }
    
    // Make it globally accessible for other pages to call
    window.refreshFacultyLoad = refreshFacultyLoad;
    
    // Load faculty load data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadFacultyLoadData();
    });
    
    // Also load when tab is clicked
    const facultyTab = document.querySelector('[data-tab="faculty"]');
    if (facultyTab) {
        facultyTab.addEventListener('click', function() {
            loadFacultyLoadData();
        });
    }
    
    // Load when page is dynamically loaded (via page-switcher)
    window.addEventListener('page:loaded', function(e) {
        if (e.detail && e.detail.page === 'academics-management') {
            loadFacultyLoadData();
        }
    });
    
    // Also listen for when coming FROM class-scheduling (schedule was created/updated/deleted)
    window.addEventListener('page:loaded', function(e) {
        if (e.detail && e.detail.page === 'class-scheduling') {
            // Signal that faculty load data needs refresh when user navigates to academics
            sessionStorage.setItem('refreshFacultyLoad', 'true');
        }
    });
    
    // Check if we need to refresh on page load
    window.addEventListener('page:loaded', function(e) {
        if (e.detail && e.detail.page === 'academics-management') {
            if (sessionStorage.getItem('refreshFacultyLoad') === 'true') {
                sessionStorage.removeItem('refreshFacultyLoad');
                // Delay slightly to ensure DOM is ready
                setTimeout(loadFacultyLoadData, 100);
            }
        }
    });
    
    // ================================================================
    // SEARCH FUNCTIONALITY FOR ALL TABLES
    // ================================================================
    
    // Initialize all search functionality
    function initializeAllSearches() {
        // Programs search
        attachTableSearch('searchPrograms', 'programsTable');
        
        // Subjects search and filters
        attachSubjectCatalogSearch();
        
        // Sections search
        attachSectionSearch();
        
        // Faculty search
        attachTableSearch('searchFaculty', 'facultyTable');
        
    }
    
    // Attach search functionality to a table
    function attachTableSearch(searchInputId, tableId) {
        const searchInput = document.getElementById(searchInputId);
        const table = document.getElementById(tableId);
        
        if (!searchInput || !table) return;
        
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        // Store original rows
        const originalRows = Array.from(tbody.querySelectorAll('tr')).filter(row => 
            !row.classList.contains('no-data')
        );
        
        if (originalRows.length === 0) return;
        
        // Search + Filter function
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const semesterSelect = document.getElementById('filterSectionSemester');
            const yearSelect = document.getElementById('filterSectionYear');
            const semesterFilter = semesterSelect ? semesterSelect.value : '';
            const yearFilter = yearSelect ? yearSelect.value : '';

            let visibleCount = 0;

            originalRows.forEach(row => {
                const cells = row.querySelectorAll('td');

                // Search match
                let matchesSearch = false;
                if (!searchTerm) {
                    matchesSearch = true;
                } else {
                    matchesSearch = Array.from(cells).some(cell =>
                        cell.textContent.toLowerCase().includes(searchTerm)
                    );
                }

                // Semester match (column index 3)
                let matchesSemester = true;
                if (semesterFilter) {
                    const cellSemester = (cells[3]?.textContent || '').toLowerCase();
                    if (semesterFilter.toLowerCase().includes('1st')) {
                        matchesSemester = cellSemester.includes('1st');
                    } else if (semesterFilter.toLowerCase().includes('2nd')) {
                        matchesSemester = cellSemester.includes('2nd');
                    } else {
                        matchesSemester = cellSemester.includes(semesterFilter.toLowerCase());
                    }
                }

                // Year match (column index 2)
                let matchesYear = true;
                if (yearFilter) {
                    const cellYear = (cells[2]?.textContent || '').toLowerCase();
                    matchesYear = cellYear.includes(yearFilter.toLowerCase());
                }

                const visible = matchesSearch && matchesSemester && matchesYear;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            // Show no-results when nothing matches any active filter/search
            if (visibleCount === 0 && (searchTerm || semesterFilter || yearFilter)) {
                const noDataRow = tbody.querySelector('tr.no-data');
                if (!noDataRow) {
                    const newRow = document.createElement('tr');
                    newRow.className = 'no-data';
                    const cellCount = originalRows[0]?.querySelectorAll('td').length || 6;
                    let message = 'No results found';
                    if (searchTerm) message = `No results found for "${searchTerm}"`;
                    newRow.innerHTML = `<td colspan="${cellCount}" style="text-align: center; padding: 20px; color: #999;">${message}</td>`;

                    // Remove existing rows temporarily and append the no-data row
                    originalRows.forEach(row => tbody.removeChild(row));
                    tbody.appendChild(newRow);

                    tbody.dataset.noDataRow = 'true';
                }
            } else if (visibleCount > 0) {
                const noDataRow = tbody.querySelector('tr.no-data');
                if (noDataRow) noDataRow.remove();

                // Restore filtered rows
                tbody.innerHTML = '';
                const filteredRows = originalRows.filter(row => {
                    const cells = row.querySelectorAll('td');

                    // apply same combined filter logic used above
                    let matchesSearch = !searchTerm || Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(searchTerm));

                    let matchesSemester = true;
                    if (semesterFilter) {
                        const cellSemester = (cells[3]?.textContent || '').toLowerCase();
                        if (semesterFilter.toLowerCase().includes('1st')) matchesSemester = cellSemester.includes('1st');
                        else if (semesterFilter.toLowerCase().includes('2nd')) matchesSemester = cellSemester.includes('2nd');
                        else matchesSemester = cellSemester.includes(semesterFilter.toLowerCase());
                    }

                    let matchesYear = true;
                    if (yearFilter) {
                        const cellYear = (cells[2]?.textContent || '').toLowerCase();
                        matchesYear = cellYear.includes(yearFilter.toLowerCase());
                    }

                    return matchesSearch && matchesSemester && matchesYear;
                });
                filteredRows.forEach(row => tbody.appendChild(row));
            }
        }
        
        // Attach event listeners
        searchInput.addEventListener('keyup', performSearch);
        searchInput.addEventListener('change', performSearch);

        // If this table has section filters, attach them
        const semSel = document.getElementById('filterSectionSemester');
        const yearSel = document.getElementById('filterSectionYear');
        if (semSel) semSel.addEventListener('change', performSearch);
        if (yearSel) yearSel.addEventListener('change', performSearch);
    }

    function attachSectionSearch() {
        const searchInput = document.getElementById('searchSections');
        const table = document.getElementById('sectionsTable');
        const programSelect = document.getElementById('filterSectionProgram');
        const semesterSelect = document.getElementById('filterSectionSemester');
        const yearSelect = document.getElementById('filterSectionYear');
        const resetButton = document.getElementById('resetSectionFilters');

        if (!searchInput || !table) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const originalRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('no-data'));

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const programValue = programSelect?.value.toLowerCase().trim() || '';
            const semesterValue = semesterSelect?.value.toLowerCase().trim() || '';
            const yearValue = yearSelect?.value.toLowerCase().trim() || '';

            let visibleCount = 0;
            originalRows.forEach(row => {
                const data = row.dataset;
                const rowText = row.textContent.toLowerCase();
                const matchesSearch = !searchTerm || rowText.includes(searchTerm);
                const matchesProgram = !programValue || (data.program || '').toLowerCase().includes(programValue);
                const matchesSemester = !semesterValue || (data.semester || '').toLowerCase().includes(semesterValue);
                const matchesYear = !yearValue || (data.yearLevel || '').toLowerCase().includes(yearValue);
                const visible = matchesSearch && matchesProgram && matchesSemester && matchesYear;

                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            const noDataRow = tbody.querySelector('tr.no-data');
            if (visibleCount === 0) {
                if (!noDataRow) {
                    const newRow = document.createElement('tr');
                    newRow.className = 'no-data';
                    newRow.innerHTML = '<td colspan="8" class="text-center text-muted" style="padding: 20px;">No sections match the current filters.</td>';
                    tbody.appendChild(newRow);
                }
            } else if (noDataRow) {
                noDataRow.remove();
            }
        }

        searchInput.addEventListener('keyup', performSearch);
        searchInput.addEventListener('change', performSearch);
        [programSelect, semesterSelect, yearSelect].forEach(select => {
            if (select) select.addEventListener('change', performSearch);
        });

        if (resetButton) {
            resetButton.addEventListener('click', () => {
                searchInput.value = '';
                if (programSelect) programSelect.value = '';
                if (semesterSelect) semesterSelect.value = '';
                if (yearSelect) yearSelect.value = '';
                performSearch();
            });
        }
    }

    function attachSubjectCatalogSearch() {
        const searchInput = document.getElementById('searchSubjects');
        const table = document.getElementById('subjectsTable');
        const courseFilter = document.getElementById('filterSubjectCourse');
        const yearFilter = document.getElementById('filterSubjectYear');
        const semesterFilter = document.getElementById('filterSubjectSemester');
        const statusFilter = document.getElementById('filterSubjectStatus');
        const resetButton = document.getElementById('resetSubjectFilters');

        if (!searchInput || !table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        const originalRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('no-data'));

        function performSearch() {
            const term = searchInput.value.toLowerCase().trim();
            const courseValue = courseFilter ? courseFilter.value.toLowerCase() : '';
            const yearValue = yearFilter ? yearFilter.value.toLowerCase() : '';
            const semValue = semesterFilter ? semesterFilter.value.toLowerCase() : '';
            const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

            let visibleCount = 0;
            tbody.innerHTML = '';

            originalRows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                const rowText = cells.map(cell => cell.textContent.toLowerCase()).join(' ');
                const matchesSearch = !term || rowText.includes(term);
                const matchesCourse = !courseValue || rowText.includes(courseValue);
                const yearText = (() => {
                    const year = row.dataset.yearLevel || '';
                    if (year === '1') return '1st';
                    if (year === '2') return '2nd';
                    if (year === '3') return '3rd';
                    if (year === '4') return '4th';
                    return year.toLowerCase();
                })();
                const matchesYear = !yearValue || yearText.includes(yearValue);
                const matchesSemester = !semValue || row.dataset.semester.toLowerCase().includes(semValue);
                const matchesStatus = !statusValue || row.dataset.status.toLowerCase() === statusValue;

                if (matchesSearch && matchesCourse && matchesYear && matchesSemester && matchesStatus) {
                    tbody.appendChild(row);
                    visibleCount += 1;
                }
            });

            if (visibleCount === 0) {
                const noDataRow = document.createElement('tr');
                noDataRow.className = 'no-data';
                noDataRow.innerHTML = `<td colspan="10" style="text-align: center; padding: 20px; color: #999;">No subjects match the active filters.</td>`;
                tbody.appendChild(noDataRow);
            }
        }

        searchInput.addEventListener('input', performSearch);
        if (courseFilter) courseFilter.addEventListener('change', performSearch);
        if (yearFilter) yearFilter.addEventListener('change', performSearch);
        if (semesterFilter) semesterFilter.addEventListener('change', performSearch);
        if (statusFilter) statusFilter.addEventListener('change', performSearch);
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (courseFilter) courseFilter.value = '';
                if (yearFilter) yearFilter.value = '';
                if (semesterFilter) semesterFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                performSearch();
            });
        }
    }
    
    // Initialize searches when page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initializeAllSearches, 100);
        loadFacultyLoadData();
    });
    
    // Re-initialize when tab switches
    window.addEventListener('page:loaded', function(e) {
        if (e.detail && e.detail.page === 'academics-management') {
            setTimeout(initializeAllSearches, 100);
        }
    });
</script>

<link rel="stylesheet" href="css/pages/academics-management.css">