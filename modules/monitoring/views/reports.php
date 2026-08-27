<?php
$currentPage = 'reports';
$reportType = $_GET['type'] ?? 'all';
$validReportTypes = ['academic', 'facilities', 'security', 'all'];
if (!in_array($reportType, $validReportTypes, true)) {
    $reportType = 'all';
}
$reportPageClass = 'report-page report-' . $reportType;
include 'layouts/header.php'; 
?>
<style>
    .report-academic #facilityReport,
    .report-academic #visitorReport,
    .report-academic #comprehensiveReport,
    .report-facilities #attendanceReport,
    .report-facilities #visitorReport,
    .report-facilities #comprehensiveReport,
    .report-security #attendanceReport,
    .report-security #facilityReport,
    .report-security #comprehensiveReport { display: none !important; }

    .report-academic #facility-tab,
    .report-academic #visitor-tab,
    .report-academic #comprehensive-tab,
    .report-facilities #attendance-tab,
    .report-facilities #visitor-tab,
    .report-facilities #comprehensive-tab,
    .report-security #attendance-tab,
    .report-security #facility-tab,
    .report-security #comprehensive-tab,
    .report-academic #btnTypeFacility,
    .report-academic #btnTypeVisitor,
    .report-facilities #btnTypeAttendance,
    .report-facilities #btnTypeVisitor,
    .report-security #btnTypeAttendance,
    .report-security #btnTypeFacility,
    .report-academic .all-report-export,
    .report-facilities .all-report-export,
    .report-security .all-report-export { display: none; }
</style>
<div class="container-fluid <?php echo htmlspecialchars($reportPageClass, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-file-text"></i> <?php echo $reportType === 'all' ? 'Reports' : ucfirst($reportType) . ' Records'; ?></h2>
                <p class="text-muted">Generate and export <?php echo $reportType === 'all' ? 'comprehensive' : strtolower($reportType); ?> reports</p>
            </div>
            
            <!-- Date Range Selector -->
            <div class="row mb-3">
                <div class="col-md-3 all-report-export">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="reportStart" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" id="reportEnd" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary form-control" onclick="generateReports()">
                        <i class="bi bi-file-earmark-arrow-down"></i> Generate Reports
                    </button>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="dropdown">
                        <button class="btn btn-success form-control dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export Report
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportReport('attendance')">
                                <i class="bi bi-calendar-check"></i> Attendance Report
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportReport('facility')">
                                <i class="bi bi-building"></i> Facility Report
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportReport('visitor')">
                                <i class="bi bi-people"></i> Visitor Report
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="exportReport('comprehensive')">
                                <i class="bi bi-file-earmark-text"></i> Comprehensive Report
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Department Quick Export Buttons -->
            <div class="row mb-3 all-report-export">
                <div class="col-md-12">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary" onclick="exportReport('attendance')">
                            <i class="bi bi-calendar-check"></i> Academic Department
                        </button>
                        <button class="btn btn-outline-warning" onclick="exportReport('facility')">
                            <i class="bi bi-building"></i> Facilities Department
                        </button>
                        <button class="btn btn-outline-success" onclick="exportReport('visitor')">
                            <i class="bi bi-people"></i> Security Department
                        </button>
                    </div>
                    <small class="text-muted ms-2">Export reports for specific departments</small>
                </div>
            </div>
            
            <!-- Report Tabs -->
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="attendance-tab" data-bs-toggle="tab" href="#attendanceReport">
                        <i class="bi bi-calendar-check"></i> Academic Department
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="facility-tab" data-bs-toggle="tab" href="#facilityReport">
                        <i class="bi bi-building"></i> Facilities Department
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="visitor-tab" data-bs-toggle="tab" href="#visitorReport">
                        <i class="bi bi-people"></i> Security Department
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="comprehensive-tab" data-bs-toggle="tab" href="#comprehensiveReport">
                        <i class="bi bi-file-earmark-text"></i> Executive Summary
                    </a>
                </li>
            </ul>
            
            <div class="tab-content mt-3" id="reportTabContent">
                <!-- Academic Department - Attendance Report (DETAILED) -->
                <div class="tab-pane fade show active" id="attendanceReport">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-calendar-check"></i> Attendance Records</h5>
                            <button class="btn btn-sm btn-success" onclick="exportReport('attendance')">
                                <i class="bi bi-download"></i> Export PDF
                            </button>
                        </div>
                        
                        <!-- Summary Stats -->
                        <div class="row mb-3" id="attendanceSummaryStats">
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Total Records</h6>
                                    <span class="badge bg-secondary fs-5" id="attTotalRecords">0</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Present</h6>
                                    <span class="badge bg-success fs-5" id="attPresentCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Absent</h6>
                                    <span class="badge bg-danger fs-5" id="attAbsentCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Late</h6>
                                    <span class="badge bg-warning fs-5" id="attLateCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Online</h6>
                                    <span class="badge bg-info fs-5" id="attOnlineCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Excused</h6>
                                    <span class="badge bg-secondary fs-5" id="attExcusedCount">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="attendanceReportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Faculty</th>
                                        <th>Course/Section</th>
                                        <th>Subject</th>
                                        <th>Room</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Verification</th>
                                        <th>Photo</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceReportBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Detailed attendance records with status tracking
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Facilities Department - Facility Report (DETAILED) -->
                <div class="tab-pane fade" id="facilityReport">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-building"></i> Facilities Department Report</h5>
                            <button class="btn btn-sm btn-success" onclick="exportReport('facility')">
                                <i class="bi bi-download"></i> Export PDF
                            </button>
                        </div>
                        
                        <!-- Summary Stats -->
                        <div class="row mb-3" id="facilitySummaryStats">
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Total Reports</h6>
                                    <span class="badge bg-secondary fs-5" id="totalReportsCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Reported</h6>
                                    <span class="badge bg-danger fs-5" id="reportedCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>In Progress</h6>
                                    <span class="badge bg-warning fs-5" id="inProgressCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Resolved</h6>
                                    <span class="badge bg-success fs-5" id="resolvedCount">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="facilityReportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px;">Photo</th>
                                        <th>Room</th>
                                        <th>Equipment</th>
                                        <th style="width:70px;">Qty</th>
                                        <th>Description</th>
                                        <th>Reported By</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th style="width:100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="facilityReportBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Detailed facility reports with photos and status tracking
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Security Department - Visitor Report (DETAILED) -->
                <div class="tab-pane fade" id="visitorReport">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-people"></i> Visitor Records</h5>
                            <button class="btn btn-sm btn-success" onclick="exportReport('visitor')">
                                <i class="bi bi-download"></i> Export PDF
                            </button>
                        </div>
                        
                        <!-- Summary Stats -->
                        <div class="row mb-3" id="visitorSummaryStats">
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Total Visitors</h6>
                                    <span class="badge bg-secondary fs-5" id="visTotalCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Inside</h6>
                                    <span class="badge bg-success fs-5" id="visInsideCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Left</h6>
                                    <span class="badge bg-secondary fs-5" id="visLeftCount">0</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box border p-2 rounded text-center bg-light">
                                    <h6>Pending</h6>
                                    <span class="badge bg-warning fs-5" id="visPendingCount">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="visitorReportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>ID Type</th>
                                        <th>Vehicle Plate</th>
                                        <th>Purpose</th>
                                        <th>ID Image</th>
                                        <th>Time In</th>
                                        <th>Status</th>
                                        <th style="width:100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="visitorReportBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Detailed visitor records with ID verification
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Executive Summary - Comprehensive Report -->
                <div class="tab-pane fade" id="comprehensiveReport">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-file-earmark-text"></i> Executive Summary</h5>
                            <button class="btn btn-sm btn-success" onclick="exportReport('comprehensive')">
                                <i class="bi bi-download"></i> Export PDF
                            </button>
                        </div>
                        <div class="row" id="comprehensiveStats">
                            <div class="col-md-4">
                                <div class="stat-card border p-3 rounded">
                                    <h6><i class="bi bi-calendar-check text-primary"></i> Academic</h6>
                                    <div id="compAttendanceSummary">Loading...</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card border p-3 rounded">
                                    <h6><i class="bi bi-building text-warning"></i> Facilities</h6>
                                    <div id="compFacilitySummary">Loading...</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card border p-3 rounded">
                                    <h6><i class="bi bi-people text-success"></i> Security</h6>
                                    <div id="compVisitorSummary">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Records Section with Department Filter -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5><i class="bi bi-list-ul"></i> Detailed Records Log</h5>
                            <div>
                                <span class="badge bg-primary me-2" id="deptLabel">Academic</span>
                                <button class="btn btn-warning btn-sm" id="btnArchiveSelected" onclick="archiveSelectedRecords()" disabled>
                                    <i class="bi bi-archive-fill"></i> Archive Selected (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary active" id="btnTypeAttendance" onclick="loadAttendanceDetails()">
                                    <i class="bi bi-calendar-check"></i> Academic Records
                                </button>
                                <button class="btn btn-sm btn-warning" id="btnTypeFacility" onclick="loadFacilityDetails()">
                                    <i class="bi bi-building"></i> Facilities Records
                                </button>
                                <button class="btn btn-sm btn-success" id="btnTypeVisitor" onclick="loadVisitorDetails()">
                                    <i class="bi bi-people"></i> Security Records
                                </button>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="refreshDetails()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="detailTable">
                                <thead class="table-light" id="detailHeader">
                                </thead>
                                <tbody id="detailBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:10px;text-align:center;">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Loading...</p>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>

<!-- PDF generation libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
let currentDetailType = 'attendance';
let currentDetailData = [];

// TODO: update these to match your school's actual name and address
const SCHOOL_NAME = 'YOUR SCHOOL NAME';
const SCHOOL_ADDRESS = 'School Address, City, Province';

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function getStatusBadge(status) {
    const badges = {
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'excused': 'bg-info',
        'online': 'bg-primary',
        'nt': 'bg-secondary',
        'eb': 'bg-secondary',
        'ed': 'bg-secondary',
        'ob': 'bg-secondary',
        'at': 'bg-secondary',
        'pending': 'bg-warning',
        'reported': 'bg-danger',
        'in_progress': 'bg-warning',
        'resolved': 'bg-success',
        'replaced': 'bg-primary',
        'inside': 'bg-success',
        'left': 'bg-secondary',
        'pending_approval': 'bg-warning',
        'approved': 'bg-info'
    };
    return badges[status] || 'bg-secondary';
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDateDisplay(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    $('.toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();
    setTimeout(() => toast.remove(), 5000);
}

function showLoading() {
    $('#loading-overlay').show();
}

function hideLoading() {
    $('#loading-overlay').hide();
}

function viewPhoto(photoPath) {
    if (photoPath && photoPath !== '') {
        window.open(photoPath, '_blank');
    } else {
        showToast('No photo available', 'warning');
    }
}

function viewID(photoPath) {
    if (photoPath && photoPath !== '') {
        window.open(photoPath, '_blank');
    } else {
        showToast('No ID image available', 'warning');
    }
}

function updateFacilityReport(id) {
    if (id) {
        window.location.href = `/my-app/public/facilities?edit=${id}`;
    }
}

function editVisitor(id) {
    if (id) {
        window.location.href = `/my-app/public/visitors?edit=${id}`;
    }
}

// ==========================================
// API CALL WRAPPER
// ==========================================

async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    const basePath = window.location.pathname.includes('/my-app/public') ? '' : '/my-app/public';
    const url = `${basePath}/api/${endpoint}`;
    
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        return result.data || result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Error loading data. Please try again.', 'error');
        throw error;
    }
}

// ==========================================
// INITIALIZATION
// ==========================================

$(document).ready(function() {
    const endDate = new Date().toISOString().split('T')[0];
    const startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    $('#reportStart').val(startDate);
    $('#reportEnd').val(endDate);
    
    generateReports();
    <?php if ($reportType === 'academic'): ?>loadAttendanceDetails();<?php endif; ?>
    <?php if ($reportType === 'facilities'): ?>loadFacilityDetails();<?php endif; ?>
    <?php if ($reportType === 'security'): ?>loadVisitorDetails();<?php endif; ?>
});

// ==========================================
// REPORT GENERATION
// ==========================================

async function generateReports() {
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();
    
    if (!start || !end) {
        showToast('Please select both start and end dates', 'warning');
        return;
    }
    
    showLoading();
    
    try {
        const reportType = '<?php echo $reportType; ?>';
        if (reportType === 'academic') await loadAttendanceReport(start, end);
        else if (reportType === 'facilities') await loadFacilityReport(start, end);
        else if (reportType === 'security') await loadVisitorReport(start, end);
        else {
            await loadAttendanceReport(start, end);
            await loadFacilityReport(start, end);
            await loadVisitorReport(start, end);
            await loadComprehensiveReport(start, end);
        }
        showToast('Reports generated successfully!', 'success');
    } catch (error) {
        console.error('Error generating reports:', error);
        showToast('Error generating reports. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

// ==========================================
// ATTENDANCE REPORT - DETAILED VIEW
// ==========================================

async function loadAttendanceReport(start, end) {
    try {
        const result = await apiCall(`report/attendance?start=${start}&end=${end}`);
        const data = result.data || result;
        const tbody = $('#attendanceReportBody');
        tbody.empty();
        
        let totalRecords = 0;
        let present = 0, absent = 0, late = 0, excused = 0, online = 0;
        
        if (data && data.length > 0) {
            data.forEach(item => {
                totalRecords++;
                const status = item.status || '';
                if (status === 'present') present++;
                else if (status === 'absent') absent++;
                else if (status === 'late') late++;
                else if (status === 'excused') excused++;
                else if (status === 'online') online++;
            });
            
            $('#attTotalRecords').text(totalRecords);
            $('#attPresentCount').text(present);
            $('#attAbsentCount').text(absent);
            $('#attLateCount').text(late);
            $('#attOnlineCount').text(online);
            $('#attExcusedCount').text(excused);
            
            data.forEach(item => {
                const statusBadge = getStatusBadge(item.status);
                
                // Face-to-face photo
                let photoHtml = '<span class="text-muted">No photo</span>';
                if (item.face_to_face_image && item.face_to_face_image !== '') {
                    const photoPath = `/my-app/public/${item.face_to_face_image}`;
                    photoHtml = `<img src="${photoPath}" alt="Face photo" style="width:50px;height:50px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="viewPhoto('${photoPath}')">`;
                }
                
                tbody.append(`
                    <tr>
                        <td><strong>${escapeHtml(item.faculty_name)}</strong></td>
                        <td>${escapeHtml(item.course_section || 'N/A')}</td>
                        <td>${escapeHtml(item.subject_code || 'N/A')}</td>
                        <td>${escapeHtml(item.room || 'N/A')}</td>
                        <td class="text-center">${item.student_count || 0}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status || 'N/A')}</span></td>
                        <td>${escapeHtml(item.verification_method || 'N/A')}</td>
                        <td>${photoHtml}</td>
                        <td>${formatDate(item.attendance_date)}</td>
                    </tr>
                `);
            });
        } else {
            $('#attTotalRecords').text(0);
            $('#attPresentCount').text(0);
            $('#attAbsentCount').text(0);
            $('#attLateCount').text(0);
            $('#attOnlineCount').text(0);
            $('#attExcusedCount').text(0);
            tbody.append('<tr><td colspan="9" class="text-center">No attendance records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading attendance report:', error);
        $('#attendanceReportBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading attendance report: ' + error.message + '</td></tr>');
    }
}

// ==========================================
// FACILITY REPORT - DETAILED VIEW
// ==========================================

async function loadFacilityReport(start, end) {
    try {
        const result = await apiCall(`report/facility?start=${start}&end=${end}`);
        const data = result.data || result;
        const tbody = $('#facilityReportBody');
        tbody.empty();
        
        let totalReports = 0;
        let reported = 0, inProgress = 0, resolved = 0;
        
        if (data && data.length > 0) {
            data.forEach(item => {
                totalReports++;
                const status = item.status || '';
                if (status === 'reported') reported++;
                else if (status === 'in_progress') inProgress++;
                else if (status === 'resolved') resolved++;
            });
            
            $('#totalReportsCount').text(totalReports);
            $('#reportedCount').text(reported);
            $('#inProgressCount').text(inProgress);
            $('#resolvedCount').text(resolved);
            
            data.forEach(item => {
                let photoHtml = '<span class="text-muted">No photo</span>';
                if (item.photo && item.photo !== '') {
                    const photoPath = `/my-app/public/${item.photo}`;
                    photoHtml = `<img src="${photoPath}" alt="Damage photo" style="width:50px;height:50px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="viewPhoto('${photoPath}')">`;
                }
                
                const statusBadge = getStatusBadge(item.status);
                const itemId = item.id || '';
                
                tbody.append(`
                    <tr>
                        <td>${photoHtml}</td>
                        <td><strong>${escapeHtml(item.room_number)}</strong></td>
                        <td>${escapeHtml(item.equipment || item.broken_equipment || 'N/A')}</td>
                        <td class="text-center">${item.quantity || item.quantity_damaged || 1}</td>
                        <td>${escapeHtml(item.description || 'N/A')}</td>
                        <td>${escapeHtml(item.reported_by || 'N/A')}</td>
                        <td>${formatDate(item.date || item.report_date)}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status || 'unknown')}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="updateFacilityReport(${itemId})">
                                <i class="bi bi-pencil"></i> Update
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="archiveReportRecord('facility', ${itemId})">
                                <i class="bi bi-archive"></i> Archive
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#totalReportsCount').text(0);
            $('#reportedCount').text(0);
            $('#inProgressCount').text(0);
            $('#resolvedCount').text(0);
            tbody.append('<tr><td colspan="9" class="text-center">No facility records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading facility report:', error);
        $('#facilityReportBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading facility report: ' + error.message + '</td></tr>');
    }
}

// ==========================================
// VISITOR REPORT - DETAILED VIEW
// ==========================================

async function loadVisitorReport(start, end) {
    try {
        const result = await apiCall(`report/visitor?start=${start}&end=${end}`);
        const data = result.data || result;
        const tbody = $('#visitorReportBody');
        tbody.empty();
        
        let totalVisitors = 0;
        let inside = 0, left = 0, pending = 0;
        
        if (data && data.length > 0) {
            data.forEach(item => {
                totalVisitors++;
                const status = item.status || '';
                if (status === 'inside') inside++;
                else if (status === 'left') left++;
                else if (status === 'pending_approval') pending++;
            });
            
            $('#visTotalCount').text(totalVisitors);
            $('#visInsideCount').text(inside);
            $('#visLeftCount').text(left);
            $('#visPendingCount').text(pending);
            
            data.forEach(item => {
                const statusBadge = getStatusBadge(item.status);
                const itemId = item.id || '';
                
                // ID Image
                let idHtml = '<span class="text-muted">No ID</span>';
                if (item.id_attachment && item.id_attachment !== '') {
                    const idPath = `/my-app/public/${item.id_attachment}`;
                    idHtml = `<img src="${idPath}" alt="ID Image" style="width:50px;height:50px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="viewPhoto('${idPath}')">`;
                }
                
                tbody.append(`
                    <tr>
                        <td><strong>${escapeHtml(item.visitor_name)}</strong></td>
                        <td>${escapeHtml(item.contact_number || 'N/A')}</td>
                        <td>${escapeHtml(item.id_type || 'N/A')}</td>
                        <td>${escapeHtml(item.vehicle_plate || 'N/A')}</td>
                        <td>${escapeHtml(item.purpose_of_visit || 'N/A')}</td>
                        <td>${idHtml}</td>
                        <td>${formatDate(item.time_in)}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status || 'N/A')}</span></td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="editVisitor(${itemId})">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="archiveReportRecord('visitor', ${itemId})">
                                <i class="bi bi-archive"></i> Archive
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#visTotalCount').text(0);
            $('#visInsideCount').text(0);
            $('#visLeftCount').text(0);
            $('#visPendingCount').text(0);
            tbody.append('<tr><td colspan="9" class="text-center">No visitor records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading visitor report:', error);
        $('#visitorReportBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading visitor report: ' + error.message + '</td></tr>');
    }
}

// ==========================================
// COMPREHENSIVE REPORT
// ==========================================

async function loadComprehensiveReport(start, end) {
    try {
        const result = await apiCall(`report/comprehensive?start=${start}&end=${end}`);
        const data = result.data || result;
        
        if (data && data.summary) {
            const attendance = data.summary.attendance_summary || [];
            const facility = data.summary.facility_summary || [];
            const visitor = data.summary.visitor_summary || [];
            
            // Attendance Summary
            $('#compAttendanceSummary').html(`
                <div><strong>Records:</strong> ${attendance.length}</div>
                <div><strong>Present:</strong> ${attendance.filter(item => item.status === 'present').length}</div>
                <div><strong>Absent:</strong> ${attendance.filter(item => item.status === 'absent').length}</div>
                <div><strong>Online:</strong> ${attendance.filter(item => item.status === 'online').length}</div>
            `);
            
            // Facility Summary
            $('#compFacilitySummary').html(`
                <div><strong>Reports:</strong> ${facility.length}</div>
                <div><strong>Reported:</strong> ${facility.filter(item => item.status === 'reported').length}</div>
                <div><strong>In Progress:</strong> ${facility.filter(item => item.status === 'in_progress').length}</div>
                <div><strong>Resolved:</strong> ${facility.filter(item => item.status === 'resolved').length}</div>
            `);
            
            // Visitor Summary
            $('#compVisitorSummary').html(`
                <div><strong>Visitors:</strong> ${visitor.length}</div>
                <div><strong>Inside:</strong> ${visitor.filter(item => item.status === 'inside').length}</div>
                <div><strong>Left:</strong> ${visitor.filter(item => item.status === 'left').length}</div>
                <div><strong>Pending:</strong> ${visitor.filter(item => item.status === 'pending_approval').length}</div>
            `);
        }
    } catch (error) {
        console.error('Error loading comprehensive report:', error);
        $('#compAttendanceSummary').html('Error loading data');
        $('#compFacilitySummary').html('Error loading data');
        $('#compVisitorSummary').html('Error loading data');
    }
}

// ==========================================
// DETAILED RECORDS LOG
// ==========================================

function updateActiveDetailBtn(type) {
    currentDetailType = type;
    $('#btnTypeAttendance, #btnTypeFacility, #btnTypeVisitor').removeClass('active');
    
    const labels = {
        attendance: { btn: 'btnTypeAttendance', label: 'Academic', badge: 'bg-primary' },
        facility: { btn: 'btnTypeFacility', label: 'Facilities', badge: 'bg-warning' },
        visitor: { btn: 'btnTypeVisitor', label: 'Security', badge: 'bg-success' }
    };
    
    const info = labels[type];
    if (info) {
        $(`#${info.btn}`).addClass('active');
        $('#deptLabel').text(info.label).removeClass('bg-primary bg-warning bg-success').addClass(info.badge);
    }
    updateSelectionCounter();
}

function refreshDetails() {
    if (currentDetailType === 'attendance') loadAttendanceDetails();
    else if (currentDetailType === 'facility') loadFacilityDetails();
    else if (currentDetailType === 'visitor') loadVisitorDetails();
}

async function loadAttendanceDetails() {
    updateActiveDetailBtn('attendance');
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();
    
    showLoading();
    try {
        const result = await apiCall(`report/details?type=attendance&start=${start}&end=${end}`);
        const data = result.data || result;
        currentDetailData = data || [];
        renderDetails(currentDetailData, [
            'Faculty', 'Course', 'Subject', 'Room', 'Status', 'Verification', 'Date'
        ], ['faculty_name', 'course_section', 'subject_code', 'room', 'status', 'verification_method', 'attendance_date']);
    } catch (error) {
        console.error('Error loading attendance details:', error);
        showToast('Error loading attendance details', 'error');
    } finally {
        hideLoading();
    }
}

async function loadFacilityDetails() {
    updateActiveDetailBtn('facility');
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();
    
    showLoading();
    try {
        const result = await apiCall(`report/details?type=facility&start=${start}&end=${end}`);
        const data = result.data || result;
        currentDetailData = data || [];
        renderDetails(currentDetailData, [
            'Room', 'Equipment', 'Quantity', 'Description', 'Reported By', 'Status', 'Date'
        ], ['room_number', 'broken_equipment', 'quantity_damaged', 'description', 'reported_by', 'status', 'report_date']);
    } catch (error) {
        console.error('Error loading facility details:', error);
        showToast('Error loading facility details', 'error');
    } finally {
        hideLoading();
    }
}

async function loadVisitorDetails() {
    updateActiveDetailBtn('visitor');
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();
    
    showLoading();
    try {
        const result = await apiCall(`report/details?type=visitor&start=${start}&end=${end}`);
        const data = result.data || result;
        currentDetailData = data || [];
        renderDetails(currentDetailData, [
            'Name', 'Contact', 'Purpose', 'Time In', 'Time Out', 'Status'
        ], ['visitor_name', 'contact_number', 'purpose_of_visit', 'time_in', 'time_out', 'status']);
    } catch (error) {
        console.error('Error loading visitor details:', error);
        showToast('Error loading visitor details', 'error');
    } finally {
        hideLoading();
    }
}

function renderDetails(data, headers, fields) {
    const thead = $('#detailHeader');
    const tbody = $('#detailBody');
    
    thead.empty();
    tbody.empty();
    
    let headerHtml = '<tr><th class="text-center" style="width: 40px;"><input type="checkbox" id="selectAllDetails" onclick="toggleSelectAll(this)"></th>';
    headers.forEach(header => {
        headerHtml += `<th>${escapeHtml(header)}</th>`;
    });
    headerHtml += '</tr>';
    thead.append(headerHtml);
    
    if (data && data.length > 0) {
        data.slice(0, 50).forEach(item => {
            let rowHtml = `<tr>
                <td class="text-center">
                    <input type="checkbox" class="record-checkbox" value="${item.id}" onchange="updateSelectionCounter()">
                </td>`;
            fields.forEach(field => {
                let value = item[field] || 'N/A';
                if (field === 'status') {
                    const badge = getStatusBadge(value);
                    rowHtml += `<td><span class="badge ${badge}">${escapeHtml(value)}</span></td>`;
                } else if (field.includes('time') || field.includes('date')) {
                    rowHtml += `<td>${formatDate(value)}</td>`;
                } else {
                    rowHtml += `<td>${escapeHtml(value)}</td>`;
                }
            });
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        });
        if (data.length > 50) {
            tbody.append(`<tr><td colspan="${headers.length + 1}" class="text-center text-muted">Showing first 50 of ${data.length} records</td></tr>`);
        }
    } else {
        tbody.append(`<tr><td colspan="${headers.length + 1}" class="text-center py-4 text-muted">No details found</td></tr>`);
    }
    
    updateSelectionCounter();
}

function toggleSelectAll(master) {
    $('.record-checkbox').prop('checked', master.checked);
    updateSelectionCounter();
}

function updateSelectionCounter() {
    const selected = $('.record-checkbox:checked').length;
    $('#selectedCount').text(selected);
    $('#btnArchiveSelected').prop('disabled', selected === 0);
}

async function archiveReportRecord(type, id) {
    const labels = { facility: 'facility', visitor: 'visitor' };
    const endpoint = { facility: 'facility/archive', visitor: 'visitor/archive' }[type];

    if (!endpoint || !id || !confirm(`Are you sure you want to archive this ${labels[type]} record?`)) {
        return;
    }

    showLoading();

    try {
        const result = await apiCall(endpoint, 'POST', { ids: [id] });
        if (result && result.success && result.archived_count > 0) {
            showToast(`Successfully archived ${labels[type]} record.`, 'success');
            setTimeout(() => {
                window.location.href = '/my-app/public/archive';
            }, 800);
        } else {
            showToast(result?.error || `Failed to archive ${labels[type]} record.`, 'error');
        }
    } catch (error) {
        console.error(`Error archiving ${type} record:`, error);
        showToast(`Failed to archive ${labels[type]} record. Please try again.`, 'error');
    } finally {
        hideLoading();
    }
}

async function archiveSelectedRecords() {
    const selectedIds = $('.record-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one record to archive', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to archive ${selectedIds.length} selected ${currentDetailType} record(s)?`)) {
        return;
    }
    
    showLoading();
    
    try {
        let endpoint = '';
        if (currentDetailType === 'attendance') endpoint = 'attendance/archive';
        else if (currentDetailType === 'facility') endpoint = 'facility/archive';
        else if (currentDetailType === 'visitor') endpoint = 'visitor/archive';
        
        const result = await apiCall(endpoint, 'POST', { ids: selectedIds });
        
        if (result && (result.success || result.archived_count > 0)) {
            showToast(`Successfully archived ${selectedIds.length} record(s)! Redirecting...`, 'success');
            setTimeout(() => {
                window.location.href = '/my-app/public/archive';
            }, 1200);
        } else {
            showToast(result?.error || 'Failed to archive records', 'error');
        }
    } catch (error) {
        console.error('Error archiving records:', error);
        showToast('Failed to archive selected records. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

// ==========================================
// PDF EXPORT
// ==========================================

async function exportReport(type = 'comprehensive') {
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();

    if (!start || !end) {
        showToast('Please select both start and end dates', 'warning');
        return;
    }

    showLoading();

    try {
        let attendance = [], facility = [], visitor = [];
        
        if (type === 'attendance' || type === 'comprehensive') {
            const result = await apiCall(`report/attendance?start=${start}&end=${end}`);
            attendance = result.data || result || [];
        }
        if (type === 'facility' || type === 'comprehensive') {
            const result = await apiCall(`report/facility?start=${start}&end=${end}`);
            facility = result.data || result || [];
        }
        if (type === 'visitor' || type === 'comprehensive') {
            const result = await apiCall(`report/visitor?start=${start}&end=${end}`);
            visitor = result.data || result || [];
        }

        buildDepartmentPDF(start, end, type, attendance, facility, visitor);
        showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} PDF generated successfully!`, 'success');
    } catch (error) {
        console.error('Error generating PDF report:', error);
        showToast('Error generating PDF report. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

function buildDepartmentPDF(start, end, type, attendance, facility, visitor) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'in', format: 'letter', orientation: 'landscape' });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const marginX = 0.5;
    const bottomLimit = pageHeight - 0.75;
    let cursorY = 0.75;

    const deptConfigs = {
        attendance: {
            title: 'ACADEMIC DEPARTMENT',
            subtitle: 'Attendance Records Report',
            recipient: 'Academic Department Head',
            color: [41, 128, 185],
            tableHead: [['Faculty', 'Course', 'Subject', 'Room', 'Students', 'Status', 'Verification', 'Time']],
            tableBody: attendance.length ? attendance.map(item => [
                item.faculty_name || 'N/A',
                item.course_section || 'N/A',
                item.subject_code || 'N/A',
                item.room || 'N/A',
                item.student_count || 0,
                item.status || 'N/A',
                item.verification_method || 'N/A',
                formatDateDisplay(item.attendance_date)
            ]) : [['No attendance data found', '', '', '', '', '', '', '']]
        },
        facility: {
            title: 'FACILITIES DEPARTMENT',
            subtitle: 'Equipment Maintenance & Damage Report',
            recipient: 'Facilities Department Head',
            color: [243, 156, 18],
            tableHead: [['Photo', 'Room', 'Equipment', 'Qty', 'Description', 'Reported By', 'Date', 'Status']],
            tableBody: facility.length ? facility.map(item => [
                item.photo ? '📷' : 'No photo',
                item.room_number || 'N/A',
                item.equipment || item.broken_equipment || 'N/A',
                item.quantity || item.quantity_damaged || 1,
                item.description || 'N/A',
                item.reported_by || 'N/A',
                formatDateDisplay(item.date || item.report_date),
                item.status || 'N/A'
            ]) : [['No facility data found', '', '', '', '', '', '', '']]
        },
        visitor: {
            title: 'SECURITY DEPARTMENT',
            subtitle: 'Visitor Records Report',
            recipient: 'Security Department Head',
            color: [46, 204, 113],
            tableHead: [['Name', 'Contact', 'ID Type', 'Vehicle Plate', 'Purpose', 'Time In', 'Status']],
            tableBody: visitor.length ? visitor.map(item => [
                item.visitor_name || 'N/A',
                item.contact_number || 'N/A',
                item.id_type || 'N/A',
                item.vehicle_plate || 'N/A',
                item.purpose_of_visit || 'N/A',
                formatDateDisplay(item.time_in),
                item.status || 'N/A'
            ]) : [['No visitor data found', '', '', '', '', '', '']]
        },
        comprehensive: {
            title: 'EXECUTIVE SUMMARY',
            subtitle: 'Comprehensive School Activity Report',
            recipient: 'School Directress',
            color: [142, 68, 173],
            tableHead: [],
            tableBody: []
        }
    };

    const config = deptConfigs[type];

    // Letterhead
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text(SCHOOL_NAME, pageWidth / 2, cursorY, { align: 'center' });
    cursorY += 0.22;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.text(SCHOOL_ADDRESS, pageWidth / 2, cursorY, { align: 'center' });
    cursorY += 0.22;

    doc.setDrawColor(0);
    doc.setLineWidth(0.015);
    doc.line(marginX, cursorY, pageWidth - marginX, cursorY);
    cursorY += 0.35;

    // Title
    const [r, g, b] = config.color;
    doc.setTextColor(r, g, b);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text(config.title, pageWidth / 2, cursorY, { align: 'center' });
    cursorY += 0.25;

    doc.setTextColor(0);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text(config.subtitle, pageWidth / 2, cursorY, { align: 'center' });
    cursorY += 0.24;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Period Covered: ${formatDateDisplay(start)} to ${formatDateDisplay(end)}`, pageWidth / 2, cursorY, { align: 'center' });
    cursorY += 0.18;

    doc.setFontSize(8.5);
    doc.setTextColor(100);
    doc.text(`Generated on: ${new Date().toLocaleString()}`, pageWidth / 2, cursorY, { align: 'center' });
    doc.setTextColor(0);
    cursorY += 0.35;

    if (type === 'comprehensive') {
        buildComprehensivePDF(doc, pageWidth, marginX, bottomLimit, attendance, facility, visitor);
    } else {
        doc.autoTable({
            startY: cursorY,
            margin: { left: marginX, right: marginX },
            styles: { fontSize: 7, cellPadding: 0.05 },
            headStyles: { fillColor: config.color },
            head: config.tableHead,
            body: config.tableBody
        });
        cursorY = doc.lastAutoTable.finalY + 0.5;
    }

    // Signature Block
    const sigBlockHeight = 1.1;
    if (cursorY + sigBlockHeight > bottomLimit) {
        doc.addPage();
        cursorY = 1.0;
    } else {
        cursorY += 0.4;
    }

    const sigLineWidth = 2.6;
    const leftSigX = marginX;
    const rightSigX = pageWidth - marginX - sigLineWidth;

    doc.setDrawColor(0);
    doc.setLineWidth(0.01);
    doc.line(leftSigX, cursorY, leftSigX + sigLineWidth, cursorY);
    doc.line(rightSigX, cursorY, rightSigX + sigLineWidth, cursorY);
    cursorY += 0.16;

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.text('Prepared by', leftSigX, cursorY);
    doc.text('Approved by', rightSigX, cursorY);
    cursorY += 0.15;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8.5);
    doc.text(config.recipient, leftSigX, cursorY);
    doc.text('School Directress', rightSigX, cursorY);

    // Footer
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(120);
        doc.text(`Page ${i} of ${pageCount}`, pageWidth - marginX, pageHeight - 0.4, { align: 'right' });
        doc.setTextColor(0);
    }

    const fileName = `${type.charAt(0).toUpperCase() + type.slice(1)}_Report_${start}_to_${end}.pdf`;
    doc.save(fileName);
}

function buildComprehensivePDF(doc, pageWidth, marginX, bottomLimit, attendance, facility, visitor) {
    let cursorY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 0.3 : 1.5;

    // Attendance Summary
    if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(41, 128, 185);
    doc.text('A. Academic Department - Attendance Records', marginX, cursorY);
    doc.setTextColor(0);
    cursorY += 0.1;

    doc.autoTable({
        startY: cursorY,
        margin: { left: marginX, right: marginX },
        styles: { fontSize: 7, cellPadding: 0.05 },
        headStyles: { fillColor: [41, 128, 185] },
        head: [['Faculty', 'Course', 'Subject', 'Room', 'Students', 'Status', 'Verification', 'Time']],
        body: attendance.length ? attendance.map(item => [
            item.faculty_name || 'N/A',
            item.course_section || 'N/A',
            item.subject_code || 'N/A',
            item.room || 'N/A',
            item.student_count || 0,
            item.status || 'N/A',
            item.verification_method || 'N/A',
            formatDateDisplay(item.attendance_date)
        ]) : [['No attendance data found', '', '', '', '', '', '', '']]
    });
    cursorY = doc.lastAutoTable.finalY + 0.3;

    // Facility Summary
    if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(243, 156, 18);
    doc.text('B. Facilities Department - Maintenance Reports', marginX, cursorY);
    doc.setTextColor(0);
    cursorY += 0.1;

    doc.autoTable({
        startY: cursorY,
        margin: { left: marginX, right: marginX },
        styles: { fontSize: 7, cellPadding: 0.05 },
        headStyles: { fillColor: [243, 156, 18] },
        head: [['Photo', 'Room', 'Equipment', 'Qty', 'Description', 'Reported By', 'Date', 'Status']],
        body: facility.length ? facility.map(item => [
            item.photo ? '📷' : 'No photo',
            item.room_number || 'N/A',
            item.equipment || item.broken_equipment || 'N/A',
            item.quantity || item.quantity_damaged || 1,
            item.description || 'N/A',
            item.reported_by || 'N/A',
            formatDateDisplay(item.date || item.report_date),
            item.status || 'N/A'
        ]) : [['No facility data found', '', '', '', '', '', '', '']]
    });
    cursorY = doc.lastAutoTable.finalY + 0.3;

    // Visitor Summary
    if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(46, 204, 113);
    doc.text('C. Security Department - Visitor Records', marginX, cursorY);
    doc.setTextColor(0);
    cursorY += 0.1;

    doc.autoTable({
        startY: cursorY,
        margin: { left: marginX, right: marginX },
        styles: { fontSize: 7, cellPadding: 0.05 },
        headStyles: { fillColor: [46, 204, 113] },
        head: [['Name', 'Contact', 'ID Type', 'Vehicle Plate', 'Purpose', 'Time In', 'Status']],
        body: visitor.length ? visitor.map(item => [
            item.visitor_name || 'N/A',
            item.contact_number || 'N/A',
            item.id_type || 'N/A',
            item.vehicle_plate || 'N/A',
            item.purpose_of_visit || 'N/A',
            formatDateDisplay(item.time_in),
            item.status || 'N/A'
        ]) : [['No visitor data found', '', '', '', '', '', '']]
    });
    cursorY = doc.lastAutoTable.finalY + 0.5;
}
</script>

<?php include 'layouts/footer.php'; ?>