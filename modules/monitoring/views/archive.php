<?php 
$currentPage = 'archive';
include 'layouts/header.php'; 
?>
<style>
    /* Fix tab visibility and contrast */
    #archiveTabs .nav-link {
        color: #1a1a2e !important;
        font-weight: 600;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        margin-right: 8px;
        padding: 8px 16px;
    }
    #archiveTabs .nav-link.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd;
    }
    #archiveTabs .nav-link:hover:not(.active) {
        background-color: #e9ecef;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-archive"></i> System Archives</h2>
                <p class="text-muted">View historical and archived system records</p>
            </div>
            
            <!-- Navigation Pills for Archive Types -->
            <ul class="nav nav-pills mb-4 shadow-sm p-2 bg-white rounded" id="archiveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="pill" data-bs-target="#attendance-archive" type="button" role="tab" onclick="switchTab('attendance')">
                        <i class="bi bi-calendar-check"></i> Attendance Records
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="facilities-tab" data-bs-toggle="pill" data-bs-target="#facilities-archive" type="button" role="tab" onclick="switchTab('facilities')">
                        <i class="bi bi-tools"></i> Facility Reports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="visitors-tab" data-bs-toggle="pill" data-bs-target="#visitors-archive" type="button" role="tab" onclick="switchTab('visitors')">
                        <i class="bi bi-people"></i> Visitor Logs
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="archiveTabsContent">
                
                <!-- ATTENDANCE ARCHIVE TAB -->
                <div class="tab-pane fade show active" id="attendance-archive" role="tabpanel">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Faculty</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="archiveFaculty" placeholder="Enter faculty name">
                                <button class="btn btn-primary" onclick="loadAttendanceArchive()">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <button class="btn btn-secondary" onclick="clearAttendanceFilter()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-success" id="btnRestoreAttendance" onclick="restoreSelected('attendance')" disabled>
                                <i class="bi bi-arrow-counterclockwise"></i> Restore Selected (<span id="countAttendanceSelected">0</span>)
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive bg-white p-3 rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-journal-check"></i> Archived Attendance</h5>
                            <span class="badge bg-secondary" id="attendanceCount">0 records</span>
                        </div>
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="selectAllAttendance" onclick="toggleSelectAll('attendance', this)">
                                    </th>
                                    <th>Faculty</th>
                                    <th>Course/Section</th>
                                    <th>Subject</th>
                                    <th>Room</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Verification</th>
                                    <th>Archived By</th>
                                    <th>Archived At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FACILITIES ARCHIVE TAB -->
                <div class="tab-pane fade" id="facilities-archive" role="tabpanel">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Room</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="archiveRoom" placeholder="Enter room number">
                                <button class="btn btn-primary" onclick="loadFacilitiesArchive()">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <button class="btn btn-secondary" onclick="clearFacilitiesFilter()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-success" id="btnRestoreFacilities" onclick="restoreSelected('facilities')" disabled>
                                <i class="bi bi-arrow-counterclockwise"></i> Restore Selected (<span id="countFacilitiesSelected">0</span>)
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive bg-white p-3 rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-tools"></i> Archived Facility Reports</h5>
                            <span class="badge bg-secondary" id="facilitiesCount">0 records</span>
                        </div>
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="selectAllFacilities" onclick="toggleSelectAll('facilities', this)">
                                    </th>
                                    <th>Room</th>
                                    <th>Broken Equipment</th>
                                    <th>Type</th>
                                    <th>Qty Damaged</th>
                                    <th>Reported By</th>
                                    <th>Report Date</th>
                                    <th>Status</th>
                                    <th>Archived At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="facilitiesBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VISITORS ARCHIVE TAB -->
                <div class="tab-pane fade" id="visitors-archive" role="tabpanel">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Visitor Name</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="archiveVisitor" placeholder="Enter visitor name">
                                <button class="btn btn-primary" onclick="loadVisitorsArchive()">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <button class="btn btn-secondary" onclick="clearVisitorsFilter()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 text-end">
                            <button class="btn btn-success" id="btnRestoreVisitors" onclick="restoreSelected('visitors')" disabled>
                                <i class="bi bi-arrow-counterclockwise"></i> Restore Selected (<span id="countVisitorsSelected">0</span>)
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive bg-white p-3 rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-people"></i> Archived Visitor Logs</h5>
                            <span class="badge bg-secondary" id="visitorsCount">0 records</span>
                        </div>
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="selectAllVisitors" onclick="toggleSelectAll('visitors', this)">
                                    </th>
                                    <th>Visitor Name</th>
                                    <th>Contact</th>
                                    <th>Purpose</th>
                                    <th>Person to Visit</th>
                                    <th>Time In</th>
                                    <th>Status</th>
                                    <th>Monitored By</th>
                                    <th>Archived At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="visitorsBody">
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let activeTab = 'attendance';

$(document).ready(function() {
    loadAttendanceArchive();
});

function switchTab(tabName) {
    activeTab = tabName;
    if (tabName === 'attendance') {
        loadAttendanceArchive();
    } else if (tabName === 'facilities') {
        loadFacilitiesArchive();
    } else if (tabName === 'visitors') {
        loadVisitorsArchive();
    }
}

async function loadAttendanceArchive() {
    const faculty = $('#archiveFaculty').val().trim();
    let url = 'attendance/archive';
    if (faculty) url += `?faculty=${encodeURIComponent(faculty)}`;
    
    showLoading();
    try {
        const data = await apiCall(url);
        const tbody = $('#attendanceBody');
        tbody.empty();
        $('#selectAllAttendance').prop('checked', false);
        
        if (data && data.length > 0) {
            $('#attendanceCount').text(`${data.length} records`);
            data.forEach(item => {
                const statusBadge = getStatusBadge(item.status);
                tbody.append(`
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="attendance-checkbox" value="${item.id}" onchange="updateSelectionCounter('attendance')">
                        </td>
                        <td><strong>${escapeHtml(item.faculty_name)}</strong></td>
                        <td>${escapeHtml(item.course_section)}</td>
                        <td>${escapeHtml(item.subject_code)}</td>
                        <td>${escapeHtml(item.room)}</td>
                        <td>${formatDate(item.attendance_date)}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status)}</span></td>
                        <td>${escapeHtml(item.verification_method || 'N/A')}</td>
                        <td>${escapeHtml(item.archived_by || 'System')}</td>
                        <td>${formatDate(item.archived_at)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-success" onclick="restoreSingle('attendance', ${item.id})">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#attendanceCount').text('0 records');
            tbody.append('<tr><td colspan="11" class="text-center text-muted py-4">No archived attendance records found</td></tr>');
        }
        updateSelectionCounter('attendance');
    } catch (error) {
        console.error('Error loading attendance archive:', error);
        showToast('Error loading attendance archive records', 'error');
    } finally {
        hideLoading();
    }
}

function clearAttendanceFilter() {
    $('#archiveFaculty').val('');
    loadAttendanceArchive();
}

async function loadFacilitiesArchive() {
    const room = $('#archiveRoom').val().trim();
    let url = 'facility/archive'; 
    if (room) url += `?room=${encodeURIComponent(room)}`;
    
    showLoading();
    try {
        const data = await apiCall(url);
        const tbody = $('#facilitiesBody');
        tbody.empty();
        $('#selectAllFacilities').prop('checked', false);
        
        if (data && data.length > 0) {
            $('#facilitiesCount').text(`${data.length} records`);
            data.forEach(item => {
                const statusBadge = getStatusBadge(item.status);
                tbody.append(`
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="facilities-checkbox" value="${item.id}" onchange="updateSelectionCounter('facilities')">
                        </td>
                        <td><strong>${escapeHtml(item.room_number)}</strong></td>
                        <td>${escapeHtml(item.broken_equipment)}</td>
                        <td><span class="badge bg-secondary">${escapeHtml(item.equipment_type)}</span></td>
                        <td>${item.quantity_damaged}</td>
                        <td>${escapeHtml(item.reported_by)}</td>
                        <td>${formatDate(item.report_date)}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status)}</span></td>
                        <td>${formatDate(item.archived_at)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-success" onclick="restoreSingle('facilities', ${item.id})">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#facilitiesCount').text('0 records');
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4">No archived facility reports found</td></tr>');
        }
        updateSelectionCounter('facilities');
    } catch (error) {
        console.error('Error loading facilities archive:', error);
        $('#facilitiesBody').html('<tr><td colspan="10" class="text-center text-muted py-4">Error loading facility archives.</td></tr>');
    } finally {
        hideLoading();
    }
}

function clearFacilitiesFilter() {
    $('#archiveRoom').val('');
    loadFacilitiesArchive();
}

async function loadVisitorsArchive() {
    const name = $('#archiveVisitor').val().trim();
    let url = 'visitor/archive';
    if (name) url += `?name=${encodeURIComponent(name)}`;
    
    showLoading();
    try {
        const data = await apiCall(url);
        const tbody = $('#visitorsBody');
        tbody.empty();
        $('#selectAllVisitors').prop('checked', false);
        
        if (data && data.length > 0) {
            $('#visitorsCount').text(`${data.length} records`);
            data.forEach(item => {
                const statusBadge = getStatusBadge(item.status);
                tbody.append(`
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="visitors-checkbox" value="${item.id}" onchange="updateSelectionCounter('visitors')">
                        </td>
                        <td><strong>${escapeHtml(item.visitor_name)}</strong></td>
                        <td>${escapeHtml(item.contact_number)}</td>
                        <td>${escapeHtml(item.purpose_of_visit)}</td>
                        <td>${escapeHtml(item.person_to_visit || 'N/A')}</td>
                        <td>${formatDate(item.time_in)}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status)}</span></td>
                        <td>${escapeHtml(item.monitored_by || 'N/A')}</td>
                        <td>${formatDate(item.archived_at)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-success" onclick="restoreSingle('visitors', ${item.id})">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#visitorsCount').text('0 records');
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4">No archived visitor logs found</td></tr>');
        }
        updateSelectionCounter('visitors');
    } catch (error) {
        console.error('Error loading visitors archive:', error);
        $('#visitorsBody').html('<tr><td colspan="10" class="text-center text-muted py-4">Error loading visitor archives.</td></tr>');
    } finally {
        hideLoading();
    }
}

function clearVisitorsFilter() {
    $('#archiveVisitor').val('');
    loadVisitorsArchive();
}

// ==========================================
// SELECTION & RESTORE FUNCTIONS
// ==========================================
function toggleSelectAll(type, master) {
    $(`.${type}-checkbox`).prop('checked', master.checked);
    updateSelectionCounter(type);
}

function updateSelectionCounter(type) {
    const selected = $(`.${type}-checkbox:checked`).length;
    if (type === 'attendance') {
        $('#countAttendanceSelected').text(selected);
        $('#btnRestoreAttendance').prop('disabled', selected === 0);
    } else if (type === 'facilities') {
        $('#countFacilitiesSelected').text(selected);
        $('#btnRestoreFacilities').prop('disabled', selected === 0);
    } else if (type === 'visitors') {
        $('#countVisitorsSelected').text(selected);
        $('#btnRestoreVisitors').prop('disabled', selected === 0);
    }
}

function restoreSingle(type, id) {
    executeRestore(type, [id]);
}

function restoreSelected(type) {
    const selectedIds = $(`.${type}-checkbox:checked`).map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one record to restore', 'warning');
        return;
    }
    
    executeRestore(type, selectedIds);
}

async function executeRestore(type, ids) {
    if (!confirm(`Are you sure you want to restore ${ids.length} selected record(s) back to active reports?`)) {
        return;
    }
    
    showLoading();
    try {
        let endpoint = '';
        if (type === 'attendance') endpoint = 'attendance/restore';
        else if (type === 'facilities') endpoint = 'facility/restore';
        else if (type === 'visitors') endpoint = 'visitor/restore';
        
        const result = await apiCall(endpoint, 'POST', { ids: ids });
        
        if (result && (result.success || result.restored_count > 0)) {
            showToast(`Successfully restored ${ids.length} record(s)!`, 'success');
            if (type === 'attendance') loadAttendanceArchive();
            else if (type === 'facilities') loadFacilitiesArchive();
            else if (type === 'visitors') loadVisitorsArchive();
        } else {
            showToast(result?.error || 'Failed to restore records', 'error');
        }
    } catch (error) {
        console.error('Error restoring records:', error);
        showToast('Failed to restore selected records. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

function getStatusBadge(status) {
    if(!status) return 'bg-secondary';
    const s = status.toLowerCase();
    if(s === 'present' || s === 'resolved' || s === 'replaced') return 'bg-success';
    if(s === 'absent') return 'bg-danger';
    if(s === 'late' || s === 'reported') return 'bg-warning text-dark';
    if(s === 'in_progress' || s === 'inside') return 'bg-info text-dark';
    return 'bg-secondary';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: '2-digit',
            hour: '2-digit', minute: '2-digit', hour12: true
        });
    } catch (e) {
        return dateString;
    }
}

function showLoading() {
    if ($('#loadingOverlay').length === 0) {
        $('body').append(`
            <div id="loadingOverlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
                <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    } else {
        $('#loadingOverlay').fadeIn(200);
    }
}

function hideLoading() {
    $('#loadingOverlay').fadeOut(200);
}
</script>

<?php include 'layouts/footer.php'; ?>