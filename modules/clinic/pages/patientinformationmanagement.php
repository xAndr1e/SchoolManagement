<?php
include_once __DIR__ . '/../../../database/db.php';
include_once __DIR__ . '/../classes/Student.php';

$studentClass = new Student();
$allStudents  = $studentClass->getAllStudents();
if (!$allStudents) $allStudents = [];

// ── Server-side search + pagination (initial load / fallback) ────────────────
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($searchTerm)) {
    $filteredStudents = array_filter($allStudents, function ($student) use ($searchTerm) {
        $q = strtolower($searchTerm);
        return strpos(strtolower($student['student_number']),    $q) !== false
            || strpos(strtolower($student['first_name']),        $q) !== false
            || strpos(strtolower($student['last_name']),         $q) !== false
            || strpos(strtolower($student['course']   ?? ''),    $q) !== false
            || strpos(strtolower($student['email']    ?? ''),    $q) !== false;
    });
    $students = array_values($filteredStudents);
} else {
    $students = $allStudents;
}

$rowsPerPage   = 10;
$totalStudents = count($students);
$totalPages    = max(1, (int) ceil($totalStudents / $rowsPerPage));

// FIX: gamit na ang "pg" imbes na "page" para hindi mag-clash sa dashboard router
$currentPage   = isset($_GET['pg']) && is_numeric($_GET['pg']) ? (int) $_GET['pg'] : 1;
$currentPage   = max(1, min($currentPage, $totalPages));
$offset        = ($currentPage - 1) * $rowsPerPage;
$studentsOnPage = array_slice($students, $offset, $rowsPerPage);
?>

<div class="module-header">
    <h1>Patient Information</h1>
</div>

<div class="module-content" id="patientInfoModule">

    <!-- ── Student Search ──────────────────────────────────────────────────── -->
    <div class="form-group">
        <label for="studentSearch">Search by Student Number, Name, or Course:</label>
        <div class="search-container">
            <input type="text"
                id="studentSearch"
                class="search-input"
                placeholder="Enter search term..."
                value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="button" id="searchBtn" class="btn-primary">Search</button>
            <button type="button" id="clearBtn"  class="btn-secondary">Clear</button>
        </div>
    </div>

    <!-- ── Student Select Dropdown ─────────────────────────────────────────── -->
    <div class="form-group">
        <label for="studentID">Or Select Student from List:</label>
        <div class="search-container">
            <select id="studentID" class="search-select">
                <option value="">-- Select Student --</option>
                <?php foreach ($allStudents as $student) : ?>
                    <option value="<?= htmlspecialchars($student['student_number']) ?>">
                        <?= htmlspecialchars($student['student_number']) ?> -
                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="selectSearchBtn" class="btn-primary">Load Student</button>
        </div>
    </div>

    <!-- ── Edit Form (hidden until a student is loaded) ────────────────────── -->
    <div id="studentInfo" class="student-info" style="display:none;">
        <h2>Edit Student Information</h2>
        <form id="studentForm" novalidate data-custom-handler>
            <input type="hidden" id="hiddenNumber"   name="student_number">
            <input type="hidden" id="originalNumber" name="original_number">

            <div class="form-row">
                <div class="form-col">
                    <label for="firstName">First Name: <span class="required">*</span></label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>
                <div class="form-col">
                    <label for="lastName">Last Name: <span class="required">*</span></label>
                    <input type="text" id="lastName" name="last_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-col">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="date_of_birth">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g., 09123456789">
                </div>
                <div class="form-col">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="student@example.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" placeholder="e.g., BSIT">
                </div>
                <div class="form-col">
                    <label for="yearLevel">Year Level:</label>
                    <select id="yearLevel" name="year_level">
                        <option value="">Select Year</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                        <option value="5">5th Year</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col-full">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" rows="3" placeholder="Enter complete address"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit"  class="btn-primary">Update Student</button>
                <button type="button" id="cancelEditBtn" class="btn-secondary">Cancel</button>
            </div>
        </form>
        <div id="updateMessage" class="message" style="display:none;"></div>
    </div>

    <!-- ── Student Records Table ───────────────────────────────────────────── -->
    <h2>Student Records <span id="searchTitle"><?= !empty($searchTerm)
        ? '- Search Results for "' . htmlspecialchars($searchTerm) . '"'
        : '' ?></span></h2>

    <button type="button" id="backBtn" class="btn-secondary"
            style="margin-bottom:10px;<?= empty($searchTerm) ? 'display:none;' : '' ?>">
        &larr; Back to all students
    </button>

    <div class="table-info" id="tableInfo"
        <?= $totalStudents === 0 ? 'style="display:none;"' : '' ?>>
        <p>Showing
            <span id="startResult"><?= $totalStudents > 0 ? $offset + 1 : 0 ?></span> to
            <span id="endResult"><?= min($offset + $rowsPerPage, $totalStudents) ?></span> of
            <span id="totalResults"><?= $totalStudents ?></span> students
        </p>
        <div class="table-actions">
            <button type="button" id="exportBtn" class="btn-secondary">Export to CSV</button>
        </div>
    </div>

    <div class="table-container">
        <table id="studentRecords" class="data-table">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (empty($studentsOnPage)) : ?>
                    <tr><td colspan="11" class="no-data">No students found.</td></tr>
                <?php else : ?>
                    <?php foreach ($studentsOnPage as $s) : ?>
                        <tr>
                            <td><?= htmlspecialchars($s['student_number']) ?></td>
                            <td><?= htmlspecialchars($s['first_name']) ?></td>
                            <td><?= htmlspecialchars($s['last_name']) ?></td>
                            <td><?= htmlspecialchars($s['gender']        ?? '') ?></td>
                            <td><?= htmlspecialchars($s['date_of_birth'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['phone']         ?? '') ?></td>
                            <td><?= htmlspecialchars($s['email']         ?? '') ?></td>
                            <td><?= htmlspecialchars($s['course']        ?? '') ?></td>
                            <td><?= htmlspecialchars($s['year_level']    ?? '') ?></td>
                            <td><?= htmlspecialchars($s['address']       ?? '') ?></td>
                            <td>
                                <button type="button"
                                        class="btn-view"
                                        data-student="<?= htmlspecialchars($s['student_number']) ?>">
                                    View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Pagination ──────────────────────────────────────────────────────── -->
    <div id="paginationContainer">
        <?php if ($totalPages > 1) : ?>
        <div class="pagination">
            <div class="pagination-info">
                Page <span id="currentPageDisplay"><?= $currentPage ?></span>
                of   <span id="totalPagesDisplay"><?= $totalPages ?></span>
            </div>
            <div class="pagination-controls">
                <?php if ($currentPage > 1) : ?>
                    <a href="#" class="page-link" data-page="1" title="First Page">««</a>
                    <a href="#" class="page-link" data-page="<?= $currentPage - 1 ?>" title="Previous Page">«</a>
                <?php endif; ?>

                <?php
                    $win   = 2;
                    $start = max(1, $currentPage - $win);
                    $end   = min($totalPages, $currentPage + $win);
                    if ($start > 1) echo '<span class="page-ellipsis">…</span>';
                ?>
                <?php for ($i = $start; $i <= $end; $i++) : ?>
                    <?php if ($i === $currentPage) : ?>
                        <span class="page-current"><?= $i ?></span>
                    <?php else : ?>
                        <a href="#" class="page-link" data-page="<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($end < $totalPages) : ?>
                    <span class="page-ellipsis">…</span>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages) : ?>
                    <a href="#" class="page-link" data-page="<?= $currentPage + 1 ?>" title="Next Page">»</a>
                    <a href="#" class="page-link" data-page="<?= $totalPages ?>" title="Last Page">»»</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── View Student Details Modal ──────────────────────────────────────── -->
    <div id="viewModal" class="pi-modal" style="display:none;" aria-hidden="true">
        <div class="pi-modal-box" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
            <div class="pi-modal-header">
                <h2 id="viewModalTitle">Patient Details</h2>
                <button type="button" id="viewCloseX" class="pi-modal-x" aria-label="Close">&times;</button>
            </div>
            <div class="pi-modal-body" id="viewModalBody">
                <!-- filled by JS -->
            </div>
            <div class="pi-modal-footer">
                <button type="button" id="viewEditBtn" class="btn-primary">Edit This Student</button>
                <button type="button" id="viewCloseBtn" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

</div><!-- /module-content -->

<style>
.btn-view { margin-right: 4px; }
.pi-modal {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.5);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
}
.pi-modal-box {
    background: #fff; border-radius: 10px;
    width: 100%; max-width: 640px; max-height: 90vh;
    display: flex; flex-direction: column;
    box-shadow: 0 10px 30px rgba(0,0,0,.3);
}
.pi-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 20px; border-bottom: 1px solid #e5e7eb;
}
.pi-modal-header h2 { margin: 0; font-size: 1.2rem; }
.pi-modal-x {
    background: none; border: none; font-size: 1.8rem; line-height: 1;
    cursor: pointer; color: #6b7280;
}
.pi-modal-body { padding: 16px 20px; overflow-y: auto; }
.pi-detail-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px;
}
.pi-detail-item.full { grid-column: 1 / -1; }
.pi-detail-label {
    font-size: .75rem; text-transform: uppercase; letter-spacing: .04em;
    color: #6b7280; margin-bottom: 2px;
}
.pi-detail-value { font-size: 1rem; color: #111827; word-break: break-word; }
.pi-detail-value.empty { color: #9ca3af; font-style: italic; }
.pi-modal-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 12px 20px; border-top: 1px solid #e5e7eb;
}
@media (max-width: 520px) {
    .pi-detail-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
'use strict';

// ── Module state ─────────────────────────────────────────────────────────────
// FIX: json_encode imbes na addslashes (mas safe sa JS string)
let currentSearchTerm = <?= json_encode($searchTerm) ?>;
let currentPage       = <?= (int) $currentPage ?>;

// ── Utilities ────────────────────────────────────────────────────────────────
function $(id) { return document.getElementById(id); }

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    return /^[\d\s+\-]{10,15}$/.test(phone);
}

function showMsg(cls, text) {
    const el = $('updateMessage');
    el.className     = 'message ' + cls;
    el.textContent   = text;
    el.style.display = 'block';
}

function hideMsg() {
    const el = $('updateMessage');
    el.style.display = 'none';
    el.textContent   = '';
}

// ── Load a student into the edit form via AJAX ───────────────────────────────
function loadStudent(studentNumber) {
    if (!studentNumber) {
        alert('Please select a student.');
        return;
    }

    showMsg('info', 'Loading student data…');

    fetch((window.CLINIC_BASE || '.') + '/handlers/get_student.php?student_number=' + encodeURIComponent(studentNumber))
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(student => {
            if (!student || student.error) {
                alert('Student not found.');
                $('studentInfo').style.display = 'none';
                hideMsg();
                return;
            }

            $('hiddenNumber').value   = student.student_number || '';
            $('originalNumber').value = student.student_number || '';
            $('firstName').value      = student.first_name     || '';
            $('lastName').value       = student.last_name      || '';
            $('gender').value         = student.gender         || '';
            $('dob').value            = student.date_of_birth  || '';
            $('phone').value          = student.phone          || '';
            $('email').value          = student.email          || '';
            $('course').value         = student.course         || '';
            $('yearLevel').value      = student.year_level     || '';
            $('address').value        = student.address        || '';

            $('studentInfo').style.display = 'block';
            hideMsg();
            $('studentInfo').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(err => {
            console.error('loadStudent:', err);
            alert('Error loading student data. Please try again.');
            $('studentInfo').style.display = 'none';
            hideMsg();
        });
}

// ── View student details (modal) ─────────────────────────────────────────────
let viewedStudentNumber = '';

function yearLabel(y) {
    const map = { '1': '1st Year', '2': '2nd Year', '3': '3rd Year', '4': '4th Year', '5': '5th Year' };
    return map[String(y)] || '';
}

function detailItem(label, value, full) {
    const has = value !== null && value !== undefined && String(value).trim() !== '';
    return '<div class="pi-detail-item' + (full ? ' full' : '') + '">' +
           '<div class="pi-detail-label">' + escapeHtml(label) + '</div>' +
           '<div class="pi-detail-value' + (has ? '' : ' empty') + '">' +
           (has ? escapeHtml(value) : 'Not provided') + '</div></div>';
}

function openViewModal() {
    const m = $('viewModal');
    m.style.display = 'flex';
    m.setAttribute('aria-hidden', 'false');
}

function closeViewModal() {
    const m = $('viewModal');
    m.style.display = 'none';
    m.setAttribute('aria-hidden', 'true');
}

function viewStudent(studentNumber) {
    if (!studentNumber) return;

    viewedStudentNumber = studentNumber;
    $('viewModalBody').innerHTML = '<p>Loading…</p>';
    openViewModal();

    fetch((window.CLINIC_BASE || '.') + '/handlers/get_student.php?student_number=' + encodeURIComponent(studentNumber))
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(s => {
            if (!s || s.error) {
                $('viewModalBody').innerHTML = '<p>Student not found.</p>';
                return;
            }
            $('viewModalTitle').textContent =
                (s.first_name || '') + ' ' + (s.last_name || '');

            $('viewModalBody').innerHTML =
                '<div class="pi-detail-grid">' +
                detailItem('Student Number', s.student_number) +
                detailItem('Gender',         s.gender) +
                detailItem('First Name',     s.first_name) +
                detailItem('Last Name',      s.last_name) +
                detailItem('Date of Birth',  s.date_of_birth) +
                detailItem('Year Level',     yearLabel(s.year_level) || s.year_level) +
                detailItem('Course',         s.course) +
                detailItem('Phone',          s.phone) +
                detailItem('Email',          s.email, true) +
                detailItem('Address',        s.address, true) +
                '</div>';
        })
        .catch(err => {
            console.error('viewStudent:', err);
            $('viewModalBody').innerHTML = '<p>Error loading details. Please try again.</p>';
        });
}

// ── Back button (lalabas lang kapag may active na search) ────────────────────
function updateBackBtn() {
    const b = $('backBtn');
    if (b) b.style.display = currentSearchTerm ? '' : 'none';
}

// ── AJAX table search + pagination ───────────────────────────────────────────
function performSearch(searchTerm, page) {
    page = page || 1;
    currentSearchTerm = searchTerm;
    currentPage       = page;

    $('tableBody').innerHTML =
        '<tr><td colspan="11" class="no-data">Searching…</td></tr>';

    // Ito ay request sa handler (hindi sa browser URL), kaya "page" pa rin ang param dito
    fetch((window.CLINIC_BASE || '.') + '/handlers/search_students.php?search=' + encodeURIComponent(searchTerm) + '&page=' + page)
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            renderTableBody(data.students);
            renderPagination(data.currentPage, data.totalPages);
            renderResultsInfo(data.start, data.end, data.totalStudents);

            $('searchTitle').textContent = searchTerm
                ? '- Search Results for "' + searchTerm + '"' : '';
            updateBackBtn();

            $('tableInfo').style.display = data.totalStudents > 0 ? '' : 'none';

            // FIX: huwag galawin ang "page" param ng dashboard; "pg" ang ginagamit natin
            try {
                const url = new URL(window.location.href);
                if (searchTerm) url.searchParams.set('search', searchTerm);
                else            url.searchParams.delete('search');
                if (data.currentPage > 1) url.searchParams.set('pg', data.currentPage);
                else                      url.searchParams.delete('pg');
                window.history.replaceState({}, '', url);
            } catch (e) { /* ignore */ }
        })
        .catch(err => {
            console.error('performSearch:', err);
            $('tableBody').innerHTML =
                '<tr><td colspan="11" class="no-data">Error searching. Please try again.</td></tr>';
        });
}

// ── Render helpers ───────────────────────────────────────────────────────────
function renderTableBody(students) {
    const tbody = $('tableBody');

    if (!students || students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="no-data">No students found.</td></tr>';
        return;
    }

    let html = '';
    students.forEach(s => {
        html +=
            '<tr>' +
            '<td>' + escapeHtml(s.student_number)     + '</td>' +
            '<td>' + escapeHtml(s.first_name)         + '</td>' +
            '<td>' + escapeHtml(s.last_name)          + '</td>' +
            '<td>' + escapeHtml(s.gender        || '') + '</td>' +
            '<td>' + escapeHtml(s.date_of_birth || '') + '</td>' +
            '<td>' + escapeHtml(s.phone         || '') + '</td>' +
            '<td>' + escapeHtml(s.email         || '') + '</td>' +
            '<td>' + escapeHtml(s.course        || '') + '</td>' +
            '<td>' + escapeHtml(s.year_level    || '') + '</td>' +
            '<td>' + escapeHtml(s.address       || '') + '</td>' +
            '<td>' +
                '<button type="button" class="btn-view" data-student="' +
                    escapeHtml(s.student_number) + '">View</button>' +
            '</td>' +
            '</tr>';
    });

    tbody.innerHTML = html;
    // Wala nang rebind: event delegation na ang gamit (tingnan sa init)
}

function renderPagination(page, totalPages) {
    const container = $('paginationContainer');

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    const win   = 2;
    const start = Math.max(1, page - win);
    const end   = Math.min(totalPages, page + win);

    let html =
        '<div class="pagination">' +
        '<div class="pagination-info">Page ' + page + ' of ' + totalPages + '</div>' +
        '<div class="pagination-controls">';

    if (page > 1) {
        html += '<a href="#" class="page-link" data-page="1" title="First Page">««</a>';
        html += '<a href="#" class="page-link" data-page="' + (page - 1) + '" title="Previous Page">«</a>';
    }

    if (start > 1) html += '<span class="page-ellipsis">…</span>';

    for (let i = start; i <= end; i++) {
        if (i === page) {
            html += '<span class="page-current">' + i + '</span>';
        } else {
            html += '<a href="#" class="page-link" data-page="' + i + '">' + i + '</a>';
        }
    }

    if (end < totalPages) html += '<span class="page-ellipsis">…</span>';

    if (page < totalPages) {
        html += '<a href="#" class="page-link" data-page="' + (page + 1) + '" title="Next Page">»</a>';
        html += '<a href="#" class="page-link" data-page="' + totalPages + '" title="Last Page">»»</a>';
    }

    html += '</div></div>';
    container.innerHTML = html;
}

function renderResultsInfo(start, end, total) {
    const s = $('startResult');
    const e = $('endResult');
    const t = $('totalResults');
    if (s) s.textContent = start;
    if (e) e.textContent = end;
    if (t) t.textContent = total;
}

// ── Init (tumatakbo kahit na-load via AJAX / innerHTML) ──────────────────────
function init() {

    // EVENT DELEGATION: Edit buttons (gumagana kahit ma-rerender ang table)
    $('tableBody').addEventListener('click', function (e) {
        const viewBtn = e.target.closest('button.btn-view[data-student]');
        if (viewBtn) {
            viewStudent(viewBtn.getAttribute('data-student'));
            return;
        }

    });

    // EVENT DELEGATION: Pagination links
    $('paginationContainer').addEventListener('click', function (e) {
        const link = e.target.closest('a.page-link[data-page]');
        if (!link) return;
        e.preventDefault();
        e.stopPropagation();
        performSearch(currentSearchTerm, parseInt(link.getAttribute('data-page'), 10));
    });

    // View modal: close buttons, click on backdrop, Esc key
    $('viewCloseBtn').addEventListener('click', closeViewModal);
    $('viewCloseX').addEventListener('click', closeViewModal);
    $('viewModal').addEventListener('click', function (e) {
        if (e.target === this) closeViewModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && $('viewModal') && $('viewModal').style.display !== 'none') {
            closeViewModal();
        }
    });
    // "Edit This Student" sa loob ng modal
    $('viewEditBtn').addEventListener('click', function () {
        const num    = viewedStudentNumber;
        const select = $('studentID');
        closeViewModal();
        if (select && Array.from(select.options).some(o => o.value === num)) select.value = num;
        loadStudent(num);
    });

    // Back button: balik sa buong listahan
    $('backBtn').addEventListener('click', function () {
        $('studentSearch').value       = '';
        $('studentID').value           = '';
        $('studentInfo').style.display = 'none';
        hideMsg();
        performSearch('', 1);
    });
    updateBackBtn();

    // Search button
    $('searchBtn').addEventListener('click', function () {
        performSearch($('studentSearch').value.trim(), 1);
    });

    // Enter key sa search input
    $('studentSearch').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(this.value.trim(), 1);
        }
    });

    // Load Student button (dropdown)
    $('selectSearchBtn').addEventListener('click', function () {
        const num = $('studentID').value.trim();
        if (!num) {
            alert('Please select a student from the list.');
            return;
        }
        loadStudent(num);
    });

    // Clear button
    $('clearBtn').addEventListener('click', function () {
        $('studentSearch').value       = '';
        $('studentID').value           = '';
        $('studentInfo').style.display = 'none';
        hideMsg();
        performSearch('', 1);
    });

    // Cancel edit button
    $('cancelEditBtn').addEventListener('click', function () {
        $('studentInfo').style.display = 'none';
        $('studentID').value           = '';
        hideMsg();
    });

    // Update student form
    $('studentForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const firstName = $('firstName').value.trim();
        const lastName  = $('lastName').value.trim();

        if (!firstName || !lastName) {
            alert('First name and last name are required.');
            return;
        }

        const email = $('email').value.trim();
        if (email && !isValidEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }

        const phone = $('phone').value.trim();
        if (phone && !isValidPhone(phone)) {
            alert('Please enter a valid phone number (10–15 digits).');
            return;
        }

        const payload = {
            original_number: $('originalNumber').value,
            student_number:  $('hiddenNumber').value,
            first_name:      firstName,
            last_name:       lastName,
            gender:          $('gender').value,
            date_of_birth:   $('dob').value,
            phone,
            email,
            course:          $('course').value.trim(),
            year_level:      $('yearLevel').value,
            address:         $('address').value.trim()
        };

        showMsg('info', 'Updating student record…');

        fetch((window.CLINIC_BASE || '.') + '/handlers/update_student.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(result => {
            if (result.success) {
                showMsg('success', 'Student updated successfully!');
                setTimeout(() => {
                    $('studentInfo').style.display = 'none';
                    hideMsg();
                    performSearch(currentSearchTerm, currentPage);
                }, 1500);
            } else {
                showMsg('error', result.message || 'Update failed. Please try again.');
            }
        })
        .catch(err => {
            console.error('update:', err);
            showMsg('error', 'Server error. Please try again.');
        });
    });

    // Export to CSV
    const exportBtn = $('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            const rows = document.querySelectorAll('#tableBody tr');

            if (!rows.length || (rows.length === 1 && rows[0].querySelector('.no-data'))) {
                alert('No data to export.');
                return;
            }

            const csv     = [];
            const headers = [];
            document.querySelectorAll('#studentRecords thead th').forEach(th => {
                if (th.textContent.trim() !== 'Actions') {
                    headers.push('"' + th.textContent.trim().replace(/"/g, '""') + '"');
                }
            });
            csv.push(headers.join(','));

            rows.forEach(row => {
                if (row.querySelector('.no-data')) return;
                const cells   = row.querySelectorAll('td');
                const rowData = [];
                for (let i = 0; i < cells.length - 1; i++) {   // skip Actions
                    let txt = cells[i].textContent.trim().replace(/"/g, '""');
                    if (txt.includes(',') || txt.includes('\n') || txt.includes('"')) {
                        txt = '"' + txt + '"';
                    }
                    rowData.push(txt);
                }
                csv.push(rowData.join(','));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href     = URL.createObjectURL(blob);
            link.download = 'student_records_' + new Date().toISOString().split('T')[0] + '.csv';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }
}

// FIX: tatakbo agad kung tapos na ang DOM (kapag na-load via AJAX), kung hindi, hintayin
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

})(); // IIFE
</script>