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
    .export-pdf-btn {
        display: inline-flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .all-report-export {
        display: block;
    }

    /* Small export button for table rows */
    .btn-export-row {
        padding: 0.15rem 0.4rem;
        font-size: 0.7rem;
        line-height: 1;
    }
    .btn-export-row i {
        font-size: 0.8rem;
    }
</style>
<div class="container-fluid <?php echo htmlspecialchars($reportPageClass, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <!-- Page Header with Gradient - Matching Sidebar Color -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="page-title">
                            <i class="bi bi-file-text"></i> 
                            <?php echo $reportType === 'all' ? 'Reports' : ucfirst($reportType) . ' Records'; ?>
                        </h2>
                        <p class="text-muted mb-0">Generate and export <?php echo $reportType === 'all' ? 'comprehensive' : strtolower($reportType); ?> reports</p>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6 px-3 py-2" id="reportCountBadge">
                            <i class="bi bi-file-earmark-text me-1"></i> Ready
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Date Range Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 all-report-export">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-start me-1"></i> Start Date
                            </label>
                            <input type="date" class="form-control" id="reportStart" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-end me-1"></i> End Date
                            </label>
                            <input type="date" class="form-control" id="reportEnd" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button class="btn btn-primary w-100" onclick="generateReports()">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i> Generate Reports
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="dropdown">
                                <button class="btn btn-success w-100 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download me-1"></i> Export Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('attendance')">
                                        <i class="bi bi-calendar-check text-primary"></i> Academic Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('facility')">
                                        <i class="bi bi-building text-warning"></i> Facilities Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('visitor')">
                                        <i class="bi bi-people text-success"></i> Security Report
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('comprehensive')">
                                        <i class="bi bi-file-earmark-text text-purple"></i> Comprehensive Report
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Department Quick Export Buttons -->
            <div class="card mb-4 all-report-export">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fw-semibold me-2"><i class="bi bi-building"></i> Quick Export:</span>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportReport('attendance')">
                            <i class="bi bi-calendar-check"></i> Academic
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="exportReport('facility')">
                            <i class="bi bi-building"></i> Facilities
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportReport('visitor')">
                            <i class="bi bi-people"></i> Security
                        </button>
                        <span class="text-muted ms-2 small">Export reports for specific departments</span>
                    </div>
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
                <!-- Academic Department - Attendance Report -->
                <div class="tab-pane fade show active" id="attendanceReport">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-check text-primary me-2"></i>
                                Attendance Records
                            </h5>
                            <button type="button" class="btn btn-sm btn-success export-pdf-btn" data-export-type="attendance" onclick="exportReport('attendance')">
                                <i class="bi bi-download me-1"></i> Export PDF
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Summary Stats -->
                            <div class="stats-grid-sm mb-3" id="attendanceSummaryStats">
                                <div class="stat-box">
                                    <span class="stat-label">Total Records</span>
                                    <span class="badge bg-secondary fs-5" id="attTotalRecords">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Present</span>
                                    <span class="badge bg-success fs-5" id="attPresentCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Absent</span>
                                    <span class="badge bg-danger fs-5" id="attAbsentCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Late</span>
                                    <span class="badge bg-warning fs-5" id="attLateCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Online</span>
                                    <span class="badge bg-info fs-5" id="attOnlineCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Excused</span>
                                    <span class="badge bg-secondary fs-5" id="attExcusedCount">0</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="attendanceReportTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="bi bi-person me-1"></i> Faculty</th>
                                            <th><i class="bi bi-book me-1"></i> Course/Section</th>
                                            <th><i class="bi bi-hash me-1"></i> Subject</th>
                                            <th><i class="bi bi-door-open me-1"></i> Room</th>
                                            <th><i class="bi bi-people me-1"></i> Students</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th><i class="bi bi-shield-check me-1"></i> Verification</th>
                                            <th><i class="bi bi-camera me-1"></i> Photo</th>
                                            <th><i class="bi bi-clock me-1"></i> Time</th>
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
                </div>
                
                <!-- Facilities Department - Facility Report -->
                <div class="tab-pane fade" id="facilityReport">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-building text-warning me-2"></i>
                                Facilities Department Report
                            </h5>
                            <button type="button" class="btn btn-sm btn-success export-pdf-btn" data-export-type="facility" onclick="exportReport('facility')">
                                <i class="bi bi-download me-1"></i> Export PDF
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Summary Stats -->
                            <div class="stats-grid-sm mb-3" id="facilitySummaryStats">
                                <div class="stat-box">
                                    <span class="stat-label">Total Reports</span>
                                    <span class="badge bg-secondary fs-5" id="totalReportsCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Reported</span>
                                    <span class="badge bg-danger fs-5" id="reportedCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">In Progress</span>
                                    <span class="badge bg-warning fs-5" id="inProgressCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Resolved</span>
                                    <span class="badge bg-success fs-5" id="resolvedCount">0</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="facilityReportTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:80px;"><i class="bi bi-image me-1"></i> Photo</th>
                                            <th><i class="bi bi-door-open me-1"></i> Room</th>
                                            <th><i class="bi bi-tools me-1"></i> Equipment</th>
                                            <th style="width:70px;"><i class="bi bi-hash me-1"></i> Qty</th>
                                            <th><i class="bi bi-chat me-1"></i> Description</th>
                                            <th><i class="bi bi-person me-1"></i> Reported By</th>
                                            <th><i class="bi bi-calendar me-1"></i> Date</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th style="width:100px;"><i class="bi bi-gear me-1"></i> Actions</th>
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
                </div>
                
                <!-- Security Department - Visitor Report -->
                <div class="tab-pane fade" id="visitorReport">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people text-success me-2"></i>
                                Visitor Records
                            </h5>
                            <button type="button" class="btn btn-sm btn-success export-pdf-btn" data-export-type="visitor" onclick="exportReport('visitor')">
                                <i class="bi bi-download me-1"></i> Export PDF
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Summary Stats -->
                            <div class="stats-grid-sm mb-3" id="visitorSummaryStats">
                                <div class="stat-box">
                                    <span class="stat-label">Total Visitors</span>
                                    <span class="badge bg-secondary fs-5" id="visTotalCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Inside</span>
                                    <span class="badge bg-success fs-5" id="visInsideCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Left</span>
                                    <span class="badge bg-secondary fs-5" id="visLeftCount">0</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Pending</span>
                                    <span class="badge bg-warning fs-5" id="visPendingCount">0</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="visitorReportTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="bi bi-person me-1"></i> Name</th>
                                            <th><i class="bi bi-phone me-1"></i> Contact</th>
                                            <th><i class="bi bi-card-id me-1"></i> ID Type</th>
                                            <th><i class="bi bi-truck me-1"></i> Vehicle Plate</th>
                                            <th><i class="bi bi-chat me-1"></i> Purpose</th>
                                            <th><i class="bi bi-image me-1"></i> ID Image</th>
                                            <th><i class="bi bi-clock me-1"></i> Time In</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th style="width:100px;"><i class="bi bi-gear me-1"></i> Actions</th>
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
                </div>
                
                <!-- Executive Summary - Comprehensive Report -->
                <div class="tab-pane fade" id="comprehensiveReport">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text text-purple me-2"></i>
                                Executive Summary
                            </h5>
                            <button type="button" class="btn btn-sm btn-success export-pdf-btn" data-export-type="comprehensive" onclick="exportReport('comprehensive')">
                                <i class="bi bi-download me-1"></i> Export PDF
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid" id="comprehensiveStats">
                                <div class="stat-card stat-card-primary">
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h6>Academic</h6>
                                        <div id="compAttendanceSummary">Loading...</div>
                                    </div>
                                </div>
                                <div class="stat-card stat-card-warning">
                                    <div class="stat-icon">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h6>Facilities</h6>
                                        <div id="compFacilitySummary">Loading...</div>
                                    </div>
                                </div>
                                <div class="stat-card stat-card-success">
                                    <div class="stat-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h6>Security</h6>
                                        <div id="compVisitorSummary">Loading...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Records Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        Detailed Records Log
                    </h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary" id="deptLabel">Academic</span>
                        <button class="btn btn-warning btn-sm" id="btnArchiveSelected" onclick="archiveSelectedRecords()" disabled>
                            <i class="bi bi-archive-fill me-1"></i> Archive (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary active" id="btnTypeAttendance" onclick="loadAttendanceDetails()">
                                <i class="bi bi-calendar-check"></i> Academic
                            </button>
                            <button class="btn btn-sm btn-warning" id="btnTypeFacility" onclick="loadFacilityDetails()">
                                <i class="bi bi-building"></i> Facilities
                            </button>
                            <button class="btn btn-sm btn-success" id="btnTypeVisitor" onclick="loadVisitorDetails()">
                                <i class="bi bi-people"></i> Security
                            </button>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshDetails()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
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

<!-- Loading Overlay -->
<div id="loading-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(25,0,107,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;padding:30px;border-radius:12px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
        <p class="mt-3 mb-0 fw-semibold">Loading...</p>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>

<!-- PDF libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
/* ==============================================
   UNIFIED UI STYLES - Sidebar Color Theme
   Matches the sidebar background: #19006b
   ============================================== */

/* CSS Variables */
:root {
    --primary: #19006b;
    --primary-light: #e8e3f5;
    --primary-dark: #0f0045;
    --primary-medium: #2d1a7a;
    --primary-rgb: 25, 0, 107;
    --success: #16a34a;
    --success-light: #dcfce7;
    --success-rgb: 22, 163, 74;
    --danger: #dc2626;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --warning-rgb: 245, 158, 11;
    --purple: #7c3aed;
    --purple-light: #ede9fe;
    --purple-rgb: 124, 58, 237;
    --info: #0891b2;
    --secondary: #6b7280;
    --light: #f3f4f6;
    --dark: #111827;
    --muted: #6b7280;
    --border: #e5e7eb;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --transition: 0.2s ease-in-out;
}

/* Base */
body {
    font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--dark);
    background: #f0f2f5;
}

/* Layout */
.main-content {
    padding: 1.5rem 2rem;
    background: #f0f2f5;
    min-height: 100vh;
    transition: padding 0.3s ease;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #0f0045 0%, #19006b 40%, #2d1a7a 70%, #3d2a8a 100%);
    padding: 1.75rem 2rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
    border: none;
    box-shadow: 0 4px 12px rgba(25, 0, 107, 0.3);
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: rgba(255, 255, 255, 0.03);
    transform: rotate(25deg);
    pointer-events: none;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 0.25rem;
    position: relative;
    z-index: 1;
}

.page-title i {
    color: rgba(255, 255, 255, 0.9);
}

.page-header .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
    position: relative;
    z-index: 1;
}

.page-header .badge.bg-info {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff;
    backdrop-filter: blur(4px);
}

/* Cards */
.card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1rem;
    transition: box-shadow 0.3s ease;
    overflow: hidden;
}

.card:hover {
    box-shadow: var(--shadow-md);
}

.card-header {
    padding: 0.875rem 1.25rem;
    background: #fafafa;
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card-header .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.75em;
}

.card-header .text-primary {
    color: var(--primary) !important;
}
.card-header .text-warning { color: var(--warning) !important; }
.card-header .text-success { color: var(--success) !important; }
.card-header .text-purple { color: var(--purple) !important; }

.card-body {
    padding: 1.25rem;
}

.card-body.p-0 .table {
    margin-bottom: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stats-grid-sm {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
}

.stat-card {
    background: #ffffff;
    border-radius: var(--border-radius);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    transition: all var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-card .stat-info h6 {
    font-size: 0.8rem;
    margin: 0 0 0.25rem 0;
    color: var(--muted);
    font-weight: 600;
}

.stat-card .stat-info div {
    font-size: 0.85rem;
    color: var(--dark);
}

.stat-card .stat-info div strong {
    font-weight: 700;
}

.stat-card-primary .stat-icon {
    background: #e8e3f5;
    color: #19006b;
}

.stat-card-warning .stat-icon {
    background: var(--warning-light);
    color: var(--warning);
}

.stat-card-success .stat-icon {
    background: var(--success-light);
    color: var(--success);
}

.stat-box {
    background: var(--light);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    text-align: center;
    border: 1px solid var(--border);
}

.stat-box .stat-label {
    display: block;
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 500;
    margin-bottom: 0.15rem;
}

.stat-box .badge {
    font-size: 1rem;
    padding: 0.25rem 0.75rem;
}

/* Tables */
.table {
    width: 100%;
    margin-bottom: 0;
    color: var(--dark);
    background: #ffffff;
    border-collapse: separate;
    border-spacing: 0;
}

.table-bordered {
    border: 1px solid var(--border);
}

.table-bordered > :not(caption) > * > * {
    border-width: 1px;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
    transition: background-color 0.15s ease;
}

.table-light {
    background-color: #f8fafc;
}

.table > :not(caption) > * > * {
    padding: 0.6rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border);
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Badges */
.badge {
    font-weight: 500;
    padding: 0.35em 0.75em;
    font-size: 0.75em;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.badge.bg-primary { 
    background: linear-gradient(135deg, #19006b, #2d1a7a) !important; 
    color: #fff; 
}
.badge.bg-success { background-color: var(--success); color: #fff; }
.badge.bg-danger { background-color: var(--danger); color: #fff; }
.badge.bg-warning { background-color: var(--warning); color: #fff; }
.badge.bg-info { background-color: var(--info); color: #fff; }
.badge.bg-secondary { background-color: var(--secondary); color: #fff; }

.badge.fs-5 {
    font-size: 1.1rem;
}

/* Buttons */
.btn {
    padding: 0.375rem 0.875rem;
    font-size: 0.875rem;
    border-radius: 8px;
    transition: all var(--transition);
    cursor: pointer;
    border: 1px solid transparent;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    text-decoration: none;
    white-space: nowrap;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn:active {
    transform: translateY(0);
}

.btn-primary {
    color: #fff;
    background: linear-gradient(135deg, #19006b, #2d1a7a);
    border-color: #19006b;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0f0045, #19006b);
    border-color: #0f0045;
    box-shadow: 0 0 0 0.25rem rgba(25, 0, 107, 0.25);
}

.btn-outline-primary {
    color: #19006b;
    border-color: #19006b;
    background: transparent;
}

.btn-outline-primary:hover {
    color: #fff;
    background: linear-gradient(135deg, #19006b, #2d1a7a);
    border-color: #19006b;
}

.btn-outline-secondary {
    color: var(--secondary);
    border-color: var(--border);
    background: transparent;
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: var(--secondary);
    border-color: var(--secondary);
}

.btn-outline-warning {
    color: var(--warning);
    border-color: var(--warning);
    background: transparent;
}

.btn-outline-warning:hover {
    color: #fff;
    background-color: var(--warning);
    border-color: var(--warning);
}

.btn-outline-success {
    color: var(--success);
    border-color: var(--success);
    background: transparent;
}

.btn-outline-success:hover {
    color: #fff;
    background-color: var(--success);
    border-color: var(--success);
}

.btn-success {
    color: #fff;
    background-color: var(--success);
    border-color: var(--success);
}

.btn-success:hover {
    background: #15803d;
    border-color: #15803d;
    box-shadow: 0 0 0 0.25rem rgba(22, 163, 74, 0.25);
}

.btn-warning {
    color: #fff;
    background-color: var(--warning);
    border-color: var(--warning);
}

.btn-warning:hover {
    background: #d97706;
    border-color: #d97706;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 6px;
}

.btn i {
    font-size: 1em;
}

/* Form Controls */
.form-label {
    margin-bottom: 0.25rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--dark);
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.6;
    color: var(--dark);
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color var(--transition), box-shadow var(--transition);
}

.form-control:focus {
    border-color: #19006b;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(25, 0, 107, 0.25);
}

/* Nav Tabs */
.nav-tabs {
    border-bottom: 2px solid var(--border);
}

.nav-tabs .nav-link {
    border: none;
    color: var(--muted);
    font-weight: 500;
    padding: 0.65rem 1.25rem;
    border-radius: 0;
    transition: all var(--transition);
    position: relative;
}

.nav-tabs .nav-link:hover {
    color: var(--primary);
    background: transparent;
}

.nav-tabs .nav-link.active {
    color: var(--primary);
    background: transparent;
    border-bottom: 3px solid var(--primary);
}

.nav-tabs .nav-link i {
    margin-right: 0.4rem;
}

/* Dropdown */
.dropdown-menu {
    border-radius: var(--border-radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-md);
    padding: 0.25rem 0;
}

.dropdown-item {
    padding: 0.4rem 1.25rem;
    font-size: 0.85rem;
    transition: background var(--transition);
}

.dropdown-item:hover {
    background: #f8fafc;
}

.dropdown-item .text-primary { color: var(--primary) !important; }
.dropdown-item .text-warning { color: var(--warning) !important; }
.dropdown-item .text-success { color: var(--success) !important; }
.dropdown-item .text-purple { color: var(--purple) !important; }

.dropdown-divider {
    border-color: var(--border);
}

/* Loading Overlay */
#loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(25, 0, 107, 0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

/* Toast */
.toast {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--shadow-lg);
}

/* Utilities */
.fw-semibold { font-weight: 600; }
.text-primary { color: #19006b !important; }
.text-success { color: var(--success) !important; }
.text-danger { color: var(--danger) !important; }
.text-warning { color: var(--warning) !important; }
.text-purple { color: var(--purple) !important; }

.gap-1 { gap: 0.25rem; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 1rem; }

.flex-wrap { flex-wrap: wrap; }

/* Responsive */
@media (max-width: 992px) {
    .main-content { padding: 1rem; }
    .page-header { padding: 1.25rem; }
    .page-title { font-size: 1.25rem; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
}

@media (max-width: 768px) {
    .main-content { padding: 0.75rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .stats-grid-sm { grid-template-columns: repeat(3, 1fr); }
    .stat-card { padding: 1rem; flex-direction: column; text-align: center; }
    .stat-card .stat-icon { width: 40px; height: 40px; font-size: 1.25rem; }
    .table-responsive { font-size: 0.8rem; }
    .table > :not(caption) > * > * { padding: 0.4rem 0.5rem; }
    .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.7rem; }
    .badge { font-size: 0.65em; padding: 0.25em 0.5em; }
    .card-header h5 { font-size: 0.95rem; }
    .nav-tabs .nav-link { padding: 0.5rem 0.75rem; font-size: 0.8rem; }
}

@media (max-width: 576px) {
    .col-md-10 { width: 100%; padding: 0.25rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .stats-grid-sm { grid-template-columns: repeat(2, 1fr); }
    .stat-card { padding: 0.75rem; }
    .stat-card .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
    .toast { max-width: 100%; margin: 0 0.5rem 0.5rem 0.5rem; }
}
</style>

<script>
// ==========================================
// TOAST NOTIFICATION
// ==========================================

function showToast(message, type = 'info') {
    const bgClass = type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-info';
    const toast = $(`
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    $('.toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0], { delay: 5000 });
    bsToast.show();
    setTimeout(() => toast.remove(), 5000);
}

// ==========================================
// LOADING OVERLAY
// ==========================================

function showLoading() {
    $('#loading-overlay').css('display', 'flex');
}

function hideLoading() {
    $('#loading-overlay').hide();
}

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
    
    try {
        const response = await fetch(`/api/${endpoint}`, options);
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
// ATTENDANCE REPORT
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
                
                let photoHtml = '<span class="text-muted">No photo</span>';
                if (item.face_to_face_image) {
                    const photoPath = `/uploads/${item.face_to_face_image}`;
                    photoHtml = `<img src="${photoPath}" alt="Face photo" style="width:40px;height:40px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="window.open('${photoPath}','_blank')" onerror="this.style.display='none';">`;
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
            tbody.append('<tr><td colspan="9" class="text-center text-muted py-4">No attendance records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading attendance report:', error);
        $('#attendanceReportBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Error loading attendance report</td></tr>');
    }
}

// ==========================================
// FACILITY REPORT
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
                if (item.photo) {
                    const photoPath = `/uploads/${item.photo}`;
                    photoHtml = `<img src="${photoPath}" alt="Damage photo" style="width:40px;height:40px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="window.open('${photoPath}','_blank')" onerror="this.style.display='none';">`;
                }
                
                const statusBadge = getStatusBadge(item.status);
                
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
                            <button class="btn btn-sm btn-primary" onclick="updateFacilityReport(${item.id || 0})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="archiveReportRecord('facility', ${item.id || 0})">
                                <i class="bi bi-archive"></i>
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
            tbody.append('<tr><td colspan="9" class="text-center text-muted py-4">No facility records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading facility report:', error);
        $('#facilityReportBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Error loading facility report</td></tr>');
    }
}

// ==========================================
// VISITOR REPORT
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
                
                let idHtml = '<span class="text-muted">No ID</span>';
                if (item.id_attachment) {
                    const idPath = `/uploads/${item.id_attachment}`;
                    idHtml = `<img src="${idPath}" alt="ID Image" style="width:40px;height:40px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="window.open('${idPath}','_blank')" onerror="this.style.display='none';">`;
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
                            <button class="btn btn-sm btn-success" onclick="editVisitor(${item.id || 0})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="archiveReportRecord('visitor', ${item.id || 0})">
                                <i class="bi bi-archive"></i>
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
            tbody.append('<tr><td colspan="9" class="text-center text-muted py-4">No visitor records found for the selected period</td></tr>');
        }
    } catch (error) {
        console.error('Error loading visitor report:', error);
        $('#visitorReportBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Error loading visitor report</td></tr>');
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
            
            $('#compAttendanceSummary').html(`
                <strong>Records:</strong> ${attendance.length}<br>
                <strong>Present:</strong> ${attendance.filter(item => item.status === 'present').length}<br>
                <strong>Absent:</strong> ${attendance.filter(item => item.status === 'absent').length}<br>
                <strong>Online:</strong> ${attendance.filter(item => item.status === 'online').length}
            `);
            
            $('#compFacilitySummary').html(`
                <strong>Reports:</strong> ${facility.length}<br>
                <strong>Reported:</strong> ${facility.filter(item => item.status === 'reported').length}<br>
                <strong>In Progress:</strong> ${facility.filter(item => item.status === 'in_progress').length}<br>
                <strong>Resolved:</strong> ${facility.filter(item => item.status === 'resolved').length}
            `);
            
            $('#compVisitorSummary').html(`
                <strong>Visitors:</strong> ${visitor.length}<br>
                <strong>Inside:</strong> ${visitor.filter(item => item.status === 'inside').length}<br>
                <strong>Left:</strong> ${visitor.filter(item => item.status === 'left').length}<br>
                <strong>Pending:</strong> ${visitor.filter(item => item.status === 'pending_approval').length}
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

let currentDetailType = 'attendance';
let currentDetailData = [];

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
            'Faculty', 'Course', 'Subject', 'Room', 'Status', 'Verification', 'Date', 'Action'
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
            'Room', 'Equipment', 'Quantity', 'Description', 'Reported By', 'Status', 'Date', 'Action'
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
            'Name', 'Contact', 'Purpose', 'Time In', 'Time Out', 'Status', 'Action'
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
    
    let headerHtml = '<tr><th class="text-center" style="width: 40px;"><input type="checkbox" id="selectAllDetails" onchange="toggleSelectAll(this)"></th>';
    headers.forEach(header => {
        headerHtml += `<th>${escapeHtml(header)}</th>`;
    });
    headerHtml += '</tr>';
    thead.append(headerHtml);
    
    if (data && data.length > 0) {
        data.slice(0, 50).forEach(item => {
            // Add the Export button to the Action column
            let exportBtn = `<button class="btn btn-success btn-export-row" onclick="exportSingleRecord('${currentDetailType}', ${item.id})" title="Export this record as PDF">
                                <i class="bi bi-download"></i> PDF
                             </button>`;
            
            // If original buttons existed (for facility/visitor), include them here
            if (currentDetailType === 'facility') {
                exportBtn += ` <button class="btn btn-sm btn-primary" onclick="updateFacilityReport(${item.id || 0})"><i class="bi bi-pencil"></i></button>`;
            } else if (currentDetailType === 'visitor') {
                exportBtn += ` <button class="btn btn-sm btn-success" onclick="editVisitor(${item.id || 0})"><i class="bi bi-pencil"></i></button>`;
            }

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
            // Append the action cell
            rowHtml += `<td>${exportBtn}</td>`;
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

// ==========================================
// NEW: EXPORT SINGLE RECORD
// ==========================================

function exportSingleRecord(type, id) {
    // Find the single record in the currentDetailData array
    const record = currentDetailData.find(item => item.id == id);
    
    if (!record) {
        showToast('Record not found for export!', 'error');
        return;
    }
    
    // Create a single-item array to pass to the main export function
    let singleDataArray;
    if (type === 'attendance') {
        singleDataArray = [record];
    } else if (type === 'facility') {
        singleDataArray = [record];
    } else if (type === 'visitor') {
        singleDataArray = [record];
    } else {
        singleDataArray = [record];
    }

    // Temporarily replace the global dataset with the single item for the export
    const originalData = {
        attendance: null,
        facility: null,
        visitor: null
    };

    // We'll hijack the global exportReport by passing a modified object
    // But easiest way: call the export function but pass the specific type and let it use currentDetailData
    // Or recreate the logic using the specific type.
    
    // We will call the internal logic of exportReport manually for just this one record:
    exportSinglePDF(type, singleDataArray);
}

// A modified export function specifically for 1 record
async function exportSinglePDF(type, singleData) {
    const start = $('#reportStart').val();
    const end = $('#reportEnd').val();
    
    showLoading();
    
    try {
        const SCHOOL_NAME = 'YOUR SCHOOL NAME';
        const SCHOOL_ADDRESS = 'School Address, City, Province';

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
                subtitle: 'Single Attendance Record',
                recipient: 'Academic Department Head',
                color: [41, 128, 185],
                tableHead: [['Faculty', 'Course', 'Subject', 'Room', 'Students', 'Status', 'Verification', 'Time']],
                tableBody: singleData.map(item => [
                    item.faculty_name || 'N/A',
                    item.course_section || 'N/A',
                    item.subject_code || 'N/A',
                    item.room || 'N/A',
                    item.student_count || 0,
                    item.status || 'N/A',
                    item.verification_method || 'N/A',
                    formatDateDisplay(item.attendance_date)
                ])
            },
            facility: {
                title: 'FACILITIES DEPARTMENT',
                subtitle: 'Single Facility Report',
                recipient: 'Facilities Department Head',
                color: [243, 156, 18],
                tableHead: [['Room', 'Equipment', 'Qty Damaged', 'Description', 'Reported By', 'Date Reported', 'Status']],
                tableBody: singleData.map(item => [
                    item.room_number || 'N/A',
                    item.equipment || item.broken_equipment || 'N/A',
                    item.quantity || item.quantity_damaged || 1,
                    (item.description || 'N/A').substring(0, 50),
                    item.reported_by || 'N/A',
                    formatDateDisplay(item.date || item.report_date),
                    (item.status || 'N/A').toUpperCase()
                ])
            },
            visitor: {
                title: 'SECURITY DEPARTMENT',
                subtitle: 'Single Visitor Record',
                recipient: 'Security Department Head',
                color: [46, 204, 113],
                tableHead: [['Name', 'Contact', 'ID Type', 'Vehicle Plate', 'Purpose', 'Time In', 'Time Out', 'Status']],
                tableBody: singleData.map(item => [
                    item.visitor_name || 'N/A',
                    item.contact_number || 'N/A',
                    item.id_type || 'N/A',
                    item.vehicle_plate || 'N/A',
                    (item.purpose_of_visit || 'N/A').substring(0, 50),
                    formatDateDisplay(item.time_in),
                    item.time_out ? formatDateDisplay(item.time_out) : 'Still Inside',
                    (item.status || 'N/A').toUpperCase()
                ])
            },
            comprehensive: {
                title: 'EXECUTIVE SUMMARY',
                subtitle: 'Single Record Summary',
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

        // Table
        doc.autoTable({
            startY: cursorY,
            margin: { left: marginX, right: marginX },
            styles: { fontSize: 7, cellPadding: 0.05 },
            headStyles: { fillColor: config.color },
            head: config.tableHead,
            body: config.tableBody,
        });
        cursorY = doc.lastAutoTable.finalY + 0.5;

        // Signature block
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

        const fileName = `${type.charAt(0).toUpperCase() + type.slice(1)}_Record_${singleData[0].id}.pdf`;
        doc.save(fileName);
        showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} record PDF generated successfully!`, 'success');
    } catch (error) {
        console.error('Error generating single PDF report:', error);
        showToast('Error generating PDF report. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

// ==========================================
// END NEW EXPORT SINGLE RECORD
// ==========================================

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
                window.location.href = '/archive';
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
                window.location.href = '/archive';
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

function updateFacilityReport(id) {
    if (id) {
        window.location.href = `/facilities?edit=${id}`;
    }
}

function editVisitor(id) {
    if (id) {
        window.location.href = `/visitors?edit=${id}`;
    }
}

// ==========================================
// PDF EXPORT - COMPLETE WITH DETAILED REPORTS
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

        const SCHOOL_NAME = 'YOUR SCHOOL NAME';
        const SCHOOL_ADDRESS = 'School Address, City, Province';

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
                tableHead: [['Photo', 'Room', 'Equipment', 'Qty Damaged', 'Description', 'Reported By', 'Date Reported', 'Status', 'Resolved Date']],
                tableBody: facility.length ? facility.map(item => [
                    item.photo ? '📷' : 'No photo',
                    item.room_number || 'N/A',
                    item.equipment || item.broken_equipment || 'N/A',
                    item.quantity || item.quantity_damaged || 1,
                    (item.description || 'N/A').substring(0, 50) + ((item.description || '').length > 50 ? '...' : ''),
                    item.reported_by || 'N/A',
                    formatDateDisplay(item.date || item.report_date),
                    (item.status || 'N/A').toUpperCase(),
                    item.resolved_date ? formatDateDisplay(item.resolved_date) : 'N/A'
                ]) : [['No facility data found', '', '', '', '', '', '', '', '']]
            },
            visitor: {
                title: 'SECURITY DEPARTMENT',
                subtitle: 'Visitor Records Report',
                recipient: 'Security Department Head',
                color: [46, 204, 113],
                tableHead: [['Name', 'Contact', 'ID Type', 'ID Number', 'Vehicle Plate', 'Purpose', 'Person to Visit', 'Time In', 'Time Out', 'Status']],
                tableBody: visitor.length ? visitor.map(item => [
                    item.visitor_name || 'N/A',
                    item.contact_number || 'N/A',
                    item.id_type || 'N/A',
                    item.id_number || 'N/A',
                    item.vehicle_plate || 'N/A',
                    (item.purpose_of_visit || 'N/A').substring(0, 40) + ((item.purpose_of_visit || '').length > 40 ? '...' : ''),
                    item.person_to_visit || 'N/A',
                    formatDateDisplay(item.time_in),
                    item.time_out ? formatDateDisplay(item.time_out) : 'Still Inside',
                    (item.status || 'N/A').toUpperCase()
                ]) : [['No visitor data found', '', '', '', '', '', '', '', '', '']]
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

        // ==========================================
        // FACILITY DETAILED REPORT
        // ==========================================
        if (type === 'facility') {
            const totalReports = facility.length;
            const reported = facility.filter(item => item.status === 'reported').length;
            const inProgress = facility.filter(item => item.status === 'in_progress').length;
            const resolved = facility.filter(item => item.status === 'resolved').length;
            const replaced = facility.filter(item => item.status === 'replaced').length;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text('📊 Summary Statistics', marginX, cursorY);
            cursorY += 0.2;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            const statsText = [
                `Total Reports: ${totalReports}`,
                `Reported: ${reported}`,
                `In Progress: ${inProgress}`,
                `Resolved: ${resolved}`,
                `Replaced: ${replaced}`
            ];
            doc.text(statsText.join('  |  '), marginX, cursorY);
            cursorY += 0.3;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 7, cellPadding: 0.05 },
                headStyles: { fillColor: config.color },
                head: config.tableHead,
                body: config.tableBody,
                columnStyles: {
                    0: { cellWidth: 0.6 },
                    1: { cellWidth: 0.7 },
                    2: { cellWidth: 0.9 },
                    3: { cellWidth: 0.5 },
                    4: { cellWidth: 1.8 },
                    5: { cellWidth: 0.9 },
                    6: { cellWidth: 0.9 },
                    7: { cellWidth: 0.8 },
                    8: { cellWidth: 0.9 }
                }
            });
            cursorY = doc.lastAutoTable.finalY + 0.5;

            if (cursorY + 0.8 > bottomLimit) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text('📝 Notes:', marginX, cursorY);
            cursorY += 0.15;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.text('• This report shows all facility maintenance and damage reports for the selected period.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• "Photo" column indicates if a damage photo is available (📷 = has photo).', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Status: Reported → In Progress → Resolved/Replaced', marginX, cursorY);

        // ==========================================
        // VISITOR DETAILED REPORT
        // ==========================================
        } else if (type === 'visitor') {
            const totalVisitors = visitor.length;
            const inside = visitor.filter(item => item.status === 'inside').length;
            const left = visitor.filter(item => item.status === 'left').length;
            const pending = visitor.filter(item => item.status === 'pending_approval').length;
            const approved = visitor.filter(item => item.status === 'approved').length;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text('📊 Summary Statistics', marginX, cursorY);
            cursorY += 0.2;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            const statsText = [
                `Total Visitors: ${totalVisitors}`,
                `Currently Inside: ${inside}`,
                `Left: ${left}`,
                `Pending Approval: ${pending}`,
                `Approved: ${approved}`
            ];
            doc.text(statsText.join('  |  '), marginX, cursorY);
            cursorY += 0.3;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 7, cellPadding: 0.05 },
                headStyles: { fillColor: config.color },
                head: config.tableHead,
                body: config.tableBody,
                columnStyles: {
                    0: { cellWidth: 1.0 },
                    1: { cellWidth: 0.7 },
                    2: { cellWidth: 0.6 },
                    3: { cellWidth: 0.7 },
                    4: { cellWidth: 0.7 },
                    5: { cellWidth: 1.3 },
                    6: { cellWidth: 0.7 },
                    7: { cellWidth: 0.8 },
                    8: { cellWidth: 0.8 },
                    9: { cellWidth: 0.7 }
                }
            });
            cursorY = doc.lastAutoTable.finalY + 0.5;

            if (cursorY + 0.8 > bottomLimit) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text('📝 Notes:', marginX, cursorY);
            cursorY += 0.15;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.text('• This report shows all visitor records for the selected period.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• "Still Inside" indicates the visitor has not checked out yet.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Status: pending_approval → approved → inside → left', marginX, cursorY);

        // ==========================================
        // ATTENDANCE DETAILED REPORT
        // ==========================================
        } else if (type === 'attendance') {
            const totalRecords = attendance.length;
            const present = attendance.filter(item => item.status === 'present').length;
            const absent = attendance.filter(item => item.status === 'absent').length;
            const late = attendance.filter(item => item.status === 'late').length;
            const online = attendance.filter(item => item.status === 'online').length;
            const excused = attendance.filter(item => item.status === 'excused').length;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.text('📊 Summary Statistics', marginX, cursorY);
            cursorY += 0.2;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            const statsText = [
                `Total Records: ${totalRecords}`,
                `Present: ${present}`,
                `Absent: ${absent}`,
                `Late: ${late}`,
                `Online: ${online}`,
                `Excused: ${excused}`
            ];
            doc.text(statsText.join('  |  '), marginX, cursorY);
            cursorY += 0.3;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 7, cellPadding: 0.05 },
                headStyles: { fillColor: config.color },
                head: config.tableHead,
                body: config.tableBody
            });
            cursorY = doc.lastAutoTable.finalY + 0.5;

            if (cursorY + 0.8 > bottomLimit) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text('📝 Notes:', marginX, cursorY);
            cursorY += 0.15;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.text('• This report shows all attendance records for the selected period.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Status indicates the attendance state of each faculty member.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Verification method shows how attendance was confirmed.', marginX, cursorY);

        // ==========================================
        // COMPREHENSIVE REPORT
        // ==========================================
        } else if (type === 'comprehensive') {
            // ============== ATTENDANCE SECTION ==============
            if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(41, 128, 185);
            doc.text('A. ACADEMIC DEPARTMENT - ATTENDANCE RECORDS', marginX, cursorY);
            doc.setTextColor(0);
            cursorY += 0.1;

            const attTotal = attendance.length;
            const attPresent = attendance.filter(item => item.status === 'present').length;
            const attAbsent = attendance.filter(item => item.status === 'absent').length;
            const attLate = attendance.filter(item => item.status === 'late').length;
            const attOnline = attendance.filter(item => item.status === 'online').length;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            const attStats = `Total: ${attTotal}  |  Present: ${attPresent}  |  Absent: ${attAbsent}  |  Late: ${attLate}  |  Online: ${attOnline}`;
            doc.text(attStats, marginX, cursorY);
            cursorY += 0.2;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 6.5, cellPadding: 0.04 },
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

            // ============== FACILITY SECTION ==============
            if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(243, 156, 18);
            doc.text('B. FACILITIES DEPARTMENT - MAINTENANCE REPORTS', marginX, cursorY);
            doc.setTextColor(0);
            cursorY += 0.1;

            const facTotal = facility.length;
            const facReported = facility.filter(item => item.status === 'reported').length;
            const facInProgress = facility.filter(item => item.status === 'in_progress').length;
            const facResolved = facility.filter(item => item.status === 'resolved').length;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            const facStats = `Total Reports: ${facTotal}  |  Reported: ${facReported}  |  In Progress: ${facInProgress}  |  Resolved: ${facResolved}`;
            doc.text(facStats, marginX, cursorY);
            cursorY += 0.2;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 6.5, cellPadding: 0.04 },
                headStyles: { fillColor: [243, 156, 18] },
                head: [['Photo', 'Room', 'Equipment', 'Qty', 'Description', 'Reported By', 'Date', 'Status']],
                body: facility.length ? facility.map(item => [
                    item.photo ? '📷' : 'No photo',
                    item.room_number || 'N/A',
                    item.equipment || item.broken_equipment || 'N/A',
                    item.quantity || item.quantity_damaged || 1,
                    (item.description || 'N/A').substring(0, 40) + ((item.description || '').length > 40 ? '...' : ''),
                    item.reported_by || 'N/A',
                    formatDateDisplay(item.date || item.report_date),
                    item.status || 'N/A'
                ]) : [['No facility data found', '', '', '', '', '', '', '']]
            });
            cursorY = doc.lastAutoTable.finalY + 0.3;

            // ============== VISITOR SECTION ==============
            if (cursorY > bottomLimit - 2.0) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(46, 204, 113);
            doc.text('C. SECURITY DEPARTMENT - VISITOR RECORDS', marginX, cursorY);
            doc.setTextColor(0);
            cursorY += 0.1;

            const visTotal = visitor.length;
            const visInside = visitor.filter(item => item.status === 'inside').length;
            const visLeft = visitor.filter(item => item.status === 'left').length;
            const visPending = visitor.filter(item => item.status === 'pending_approval').length;

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            const visStats = `Total Visitors: ${visTotal}  |  Inside: ${visInside}  |  Left: ${visLeft}  |  Pending: ${visPending}`;
            doc.text(visStats, marginX, cursorY);
            cursorY += 0.2;

            doc.autoTable({
                startY: cursorY,
                margin: { left: marginX, right: marginX },
                styles: { fontSize: 6.5, cellPadding: 0.04 },
                headStyles: { fillColor: [46, 204, 113] },
                head: [['Name', 'Contact', 'ID Type', 'Vehicle', 'Purpose', 'Time In', 'Status']],
                body: visitor.length ? visitor.map(item => [
                    item.visitor_name || 'N/A',
                    item.contact_number || 'N/A',
                    item.id_type || 'N/A',
                    item.vehicle_plate || 'N/A',
                    (item.purpose_of_visit || 'N/A').substring(0, 35) + ((item.purpose_of_visit || '').length > 35 ? '...' : ''),
                    formatDateDisplay(item.time_in),
                    item.status || 'N/A'
                ]) : [['No visitor data found', '', '', '', '', '', '']]
            });
            cursorY = doc.lastAutoTable.finalY + 0.5;

            // Comprehensive notes
            if (cursorY + 0.8 > bottomLimit) { doc.addPage(); cursorY = 0.75; }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text('📝 Executive Summary Notes:', marginX, cursorY);
            cursorY += 0.15;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.text('• This comprehensive report combines data from all three departments.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Academic: Tracks faculty attendance with status (Present, Absent, Late, Online, Excused).', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Facilities: Tracks equipment damage reports and maintenance status.', marginX, cursorY);
            cursorY += 0.15;
            doc.text('• Security: Tracks visitor entries, exits, and ID verification status.', marginX, cursorY);
        }

        // ============== SIGNATURE BLOCK ==============
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

        // ============== FOOTER ==============
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
        showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} PDF generated successfully!`, 'success');
    } catch (error) {
        console.error('Error generating PDF report:', error);
        showToast('Error generating PDF report. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}
</script>

<?php include 'layouts/footer.php'; ?>