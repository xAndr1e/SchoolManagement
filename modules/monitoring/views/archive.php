<?php 
$currentPage = 'archive';
include 'layouts/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <!-- Page Header with Gradient - Matching Sidebar Color -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="page-title">
                            <i class="bi bi-archive"></i> 
                            System Archives
                        </h2>
                        <p class="text-muted mb-0">View historical and archived system records</p>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6 px-3 py-2" id="archiveHeaderCount">
                            <i class="bi bi-collection me-1"></i> 0 records
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards Row -->
            <div class="stats-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalAttendanceArchived">0</h3>
                        <span>Attendance Archived</span>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalFacilitiesArchived">0</h3>
                        <span>Facility Reports</span>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalVisitorsArchived">0</h3>
                        <span>Visitor Logs</span>
                    </div>
                </div>
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalRestorable">0</h3>
                        <span>Restorable</span>
                    </div>
                </div>
            </div>
            
            <!-- Archive Type Tabs -->
            <ul class="nav nav-pills mb-3 archive-tabs" id="archiveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="pill" data-bs-target="#attendance-archive" type="button" role="tab" onclick="switchTab('attendance')">
                        <i class="bi bi-calendar-check me-1"></i> Attendance Records
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="facilities-tab" data-bs-toggle="pill" data-bs-target="#facilities-archive" type="button" role="tab" onclick="switchTab('facilities')">
                        <i class="bi bi-tools me-1"></i> Facility Reports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="visitors-tab" data-bs-toggle="pill" data-bs-target="#visitors-archive" type="button" role="tab" onclick="switchTab('visitors')">
                        <i class="bi bi-people me-1"></i> Visitor Logs
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="archiveTabsContent">
                
                <!-- ATTENDANCE ARCHIVE TAB -->
                <div class="tab-pane fade show active" id="attendance-archive" role="tabpanel">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i> Filter by Faculty
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="archiveFaculty" placeholder="Enter faculty name">
                                        <button class="btn btn-primary" onclick="loadAttendanceArchive()">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearAttendanceFilter()">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-7 text-end">
                                    <button class="btn btn-success" id="btnRestoreAttendance" onclick="restoreSelected('attendance')" disabled>
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected (<span id="countAttendanceSelected">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-check text-primary me-2"></i>
                                Archived Attendance
                            </h5>
                            <span class="badge bg-primary" id="attendanceCount">0 records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px;">
                                                <input type="checkbox" id="selectAllAttendance" onclick="toggleSelectAll('attendance', this)">
                                            </th>
                                            <th><i class="bi bi-person me-1"></i> Faculty</th>
                                            <th><i class="bi bi-book me-1"></i> Course/Section</th>
                                            <th><i class="bi bi-journal-text me-1"></i> Subject</th>
                                            <th><i class="bi bi-door-open me-1"></i> Room</th>
                                            <th><i class="bi bi-calendar me-1"></i> Date</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th><i class="bi bi-shield-check me-1"></i> Verification</th>
                                            <th><i class="bi bi-person-check me-1"></i> Archived By</th>
                                            <th><i class="bi bi-clock me-1"></i> Archived At</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i> Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FACILITIES ARCHIVE TAB -->
                <div class="tab-pane fade" id="facilities-archive" role="tabpanel">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-door-open me-1"></i> Filter by Room
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="archiveRoom" placeholder="Enter room number">
                                        <button class="btn btn-primary" onclick="loadFacilitiesArchive()">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearFacilitiesFilter()">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-7 text-end">
                                    <button class="btn btn-success" id="btnRestoreFacilities" onclick="restoreSelected('facilities')" disabled>
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected (<span id="countFacilitiesSelected">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-tools text-warning me-2"></i>
                                Archived Facility Reports
                            </h5>
                            <span class="badge bg-primary" id="facilitiesCount">0 records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px;">
                                                <input type="checkbox" id="selectAllFacilities" onclick="toggleSelectAll('facilities', this)">
                                            </th>
                                            <th><i class="bi bi-door-open me-1"></i> Room</th>
                                            <th><i class="bi bi-tools me-1"></i> Broken Equipment</th>
                                            <th><i class="bi bi-tags me-1"></i> Type</th>
                                            <th><i class="bi bi-hash me-1"></i> Qty Damaged</th>
                                            <th><i class="bi bi-person me-1"></i> Reported By</th>
                                            <th><i class="bi bi-calendar me-1"></i> Report Date</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th><i class="bi bi-clock me-1"></i> Archived At</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i> Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="facilitiesBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VISITORS ARCHIVE TAB -->
                <div class="tab-pane fade" id="visitors-archive" role="tabpanel">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person me-1"></i> Filter by Visitor Name
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="archiveVisitor" placeholder="Enter visitor name">
                                        <button class="btn btn-primary" onclick="loadVisitorsArchive()">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearVisitorsFilter()">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-7 text-end">
                                    <button class="btn btn-success" id="btnRestoreVisitors" onclick="restoreSelected('visitors')" disabled>
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Selected (<span id="countVisitorsSelected">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people text-success me-2"></i>
                                Archived Visitor Logs
                            </h5>
                            <span class="badge bg-primary" id="visitorsCount">0 records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px;">
                                                <input type="checkbox" id="selectAllVisitors" onclick="toggleSelectAll('visitors', this)">
                                            </th>
                                            <th><i class="bi bi-person me-1"></i> Visitor Name</th>
                                            <th><i class="bi bi-telephone me-1"></i> Contact</th>
                                            <th><i class="bi bi-chat me-1"></i> Purpose</th>
                                            <th><i class="bi bi-person-check me-1"></i> Person to Visit</th>
                                            <th><i class="bi bi-clock me-1"></i> Time In</th>
                                            <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                            <th><i class="bi bi-person-badge me-1"></i> Monitored By</th>
                                            <th><i class="bi bi-clock-history me-1"></i> Archived At</th>
                                            <th class="text-center"><i class="bi bi-gear me-1"></i> Action</th>
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
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(25,0,107,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<style>
/* ==============================================
   ARCHIVE PAGE — Unified UI matching Facilities
   ============================================== */

/* CSS Variables - Matching Sidebar Color */
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

/* =========================================================
   FIX: Force Bootstrap Icons to render
   ========================================================= */
.main-content i.bi,
.main-content i[class^="bi-"],
.main-content i[class*=" bi-"] {
    font-family: "bootstrap-icons" !important;
    font-style: normal !important;
    font-weight: normal !important;
    font-variant: normal !important;
    text-transform: none !important;
    line-height: 1 !important;
    display: inline-block;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.main-content i.bi::before,
.main-content i[class^="bi-"]::before,
.main-content i[class*=" bi-"]::before {
    font-family: "bootstrap-icons" !important;
    display: inline-block;
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

/* Page Header - Matching Sidebar Color */
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

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
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
    position: relative;
    overflow: hidden;
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

.stat-card .stat-info h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    color: var(--dark);
}

.stat-card .stat-info span {
    font-size: 0.8rem;
    color: var(--muted);
    font-weight: 500;
}

.stat-card-primary .stat-icon { background: #e8e3f5; color: #19006b; }
.stat-card-success .stat-icon { background: var(--success-light); color: var(--success); }
.stat-card-warning .stat-icon { background: var(--warning-light); color: var(--warning); }
.stat-card-purple .stat-icon { background: var(--purple-light); color: var(--purple); }

/* Archive Tabs (nav-pills restyled) */
.archive-tabs {
    background: #ffffff;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 1.5rem !important;
}

.archive-tabs .nav-link {
    color: var(--dark);
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: none;
    background: transparent;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all var(--transition);
}

.archive-tabs .nav-link:hover:not(.active) {
    background: #f3f4f6;
    color: var(--primary);
}

.archive-tabs .nav-link.active {
    background: linear-gradient(135deg, #19006b, #2d1a7a) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(25, 0, 107, 0.3);
}

.archive-tabs .nav-link i {
    font-size: 1rem;
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
}

.card-header .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.75em;
}

.card-header .text-primary { color: var(--primary) !important; }

.card-body {
    padding: 1.25rem;
}

.card-body.p-0 .table {
    margin-bottom: 0;
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

.table-bordered { border: 1px solid var(--border); }
.table-bordered > :not(caption) > * > * { border-width: 1px; }

.table-hover tbody tr:hover {
    background-color: #f8fafc;
    transition: background-color 0.15s ease;
}

.table-light { background-color: #f8fafc; }

.table > :not(caption) > * > * {
    padding: 0.75rem 1rem;
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
.badge.fs-6 { font-size: 0.9rem; }

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

.btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
.btn:active { transform: translateY(0); }

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

.btn-success:disabled {
    background: #86efac;
    border-color: #86efac;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
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

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 6px;
}

.btn i { font-size: 1em; }

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

.input-group {
    display: flex;
    align-items: stretch;
    width: 100%;
}

.input-group > .form-control,
.input-group > .form-select {
    flex: 1 1 auto;
    width: 1%;
    min-width: 0;
}

.input-group > .btn { border-radius: 0; }
.input-group > .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}
.input-group > .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

/* Toast */
.toast-custom {
    min-width: 250px;
    max-width: 450px;
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    border-left: 4px solid var(--primary);
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideIn 0.3s ease;
}

.toast-custom.toast-success { border-left-color: var(--success); }
.toast-custom.toast-danger { border-left-color: var(--danger); }
.toast-custom.toast-warning { border-left-color: var(--warning); }
.toast-custom.toast-info { border-left-color: var(--primary); }

.toast-custom .toast-icon { font-size: 1.25rem; }
.toast-custom .toast-message { flex: 1; font-size: 0.875rem; color: var(--dark); }
.toast-custom .toast-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--muted);
    padding: 0 0.25rem;
}
.toast-custom .toast-close:hover { color: var(--dark); }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Checkbox */
.table input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #19006b;
}

/* Empty State (used implicitly by current JS text rows) */
.text-center.text-muted.py-4 {
    padding: 2.5rem 1rem !important;
    font-style: italic;
}

/* Utilities */
.fw-semibold { font-weight: 600; }
.text-primary { color: #19006b !important; }
.text-success { color: var(--success) !important; }
.text-danger { color: var(--danger) !important; }
.text-warning { color: var(--warning) !important; }

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
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .stat-card { padding: 1rem; }
    .stat-card .stat-icon { width: 40px; height: 40px; font-size: 1.25rem; }
    .stat-card .stat-info h3 { font-size: 1.25rem; }
    .table-responsive { font-size: 0.8rem; }
    .table > :not(caption) > * > * { padding: 0.5rem 0.6rem; }
    .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.7rem; }
    .badge { font-size: 0.65em; padding: 0.25em 0.5em; }
    .card-header h5 { font-size: 0.95rem; }
    .archive-tabs .nav-link { font-size: 0.8rem; padding: 0.4rem 0.75rem; }
}

@media (max-width: 576px) {
    .col-md-10 { width: 100%; padding: 0.25rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .stat-card { padding: 0.75rem; }
    .stat-card .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
    .stat-card .stat-info h3 { font-size: 1rem; }
    .stat-card .stat-info span { font-size: 0.7rem; }
    .toast-custom { max-width: 100%; margin: 0 0.5rem 0.5rem 0.5rem; }
}
</style>

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

// ============================================
// TOAST NOTIFICATION
// ============================================
function showToast(message, type = 'info') {
    const iconMap = {
        success: 'bi-check-circle-fill text-success',
        danger: 'bi-exclamation-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        error: 'bi-exclamation-circle-fill text-danger',
        info: 'bi-info-circle-fill text-primary'
    };
    
    const icon = iconMap[type] || iconMap.info;
    const toastType = (type === 'error') ? 'danger' : type;
    
    const toast = $(`
        <div class="toast-custom toast-${toastType}">
            <i class="bi ${icon} toast-icon"></i>
            <span class="toast-message">${escapeHtml(message)}</span>
            <button class="toast-close" onclick="$(this).closest('.toast-custom').remove()">&times;</button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.css('animation', 'slideOut 0.3s ease');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ============================================
// API CALL FUNCTION
// ============================================
async function apiCall(endpoint, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        let url = endpoint;
        if (!endpoint.startsWith('http') && !endpoint.startsWith('/api/')) {
            url = '/api/' + endpoint;
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            if (response.status === 401 || response.status === 302) {
                showToast('Session expired. Please login again.', 'danger');
                setTimeout(() => { window.location.href = '/'; }, 2000);
                return null;
            }
            throw new Error('HTTP error ' + response.status);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            try { return JSON.parse(text); }
            catch (e) { throw new Error('Unexpected response format'); }
        }

        return await response.json();
    } catch (error) {
        console.error('API call error:', error);
        showToast('Error: ' + error.message, 'danger');
        return null;
    }
}

// ============================================
// ATTENDANCE ARCHIVE
// ============================================
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
            $('#totalAttendanceArchived').text(data.length);
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
            $('#totalAttendanceArchived').text(0);
            tbody.append('<tr><td colspan="11" class="text-center text-muted py-4">No archived attendance records found</td></tr>');
        }
        updateSelectionCounter('attendance');
        updateHeaderCount();
    } catch (error) {
        console.error('Error loading attendance archive:', error);
        showToast('Error loading attendance archive records', 'danger');
    } finally {
        hideLoading();
    }
}

function clearAttendanceFilter() {
    $('#archiveFaculty').val('');
    loadAttendanceArchive();
}

// ============================================
// FACILITIES ARCHIVE
// ============================================
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
            $('#totalFacilitiesArchived').text(data.length);
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
            $('#totalFacilitiesArchived').text(0);
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4">No archived facility reports found</td></tr>');
        }
        updateSelectionCounter('facilities');
        updateHeaderCount();
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

// ============================================
// VISITORS ARCHIVE
// ============================================
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
            $('#totalVisitorsArchived').text(data.length);
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
            $('#totalVisitorsArchived').text(0);
            tbody.append('<tr><td colspan="10" class="text-center text-muted py-4">No archived visitor logs found</td></tr>');
        }
        updateSelectionCounter('visitors');
        updateHeaderCount();
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

// ============================================
// HEADER COUNT (sum of all three stat cards)
// ============================================
function updateHeaderCount() {
    const a = parseInt($('#totalAttendanceArchived').text()) || 0;
    const f = parseInt($('#totalFacilitiesArchived').text()) || 0;
    const v = parseInt($('#totalVisitorsArchived').text()) || 0;
    const total = a + f + v;
    $('#archiveHeaderCount').html(`<i class="bi bi-collection me-1"></i> ${total} records`);
    $('#totalRestorable').text(total);
}

// ============================================
// SELECTION & RESTORE FUNCTIONS
// ============================================
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
            showToast(result?.error || 'Failed to restore records', 'danger');
        }
    } catch (error) {
        console.error('Error restoring records:', error);
        showToast('Failed to restore selected records. Please try again.', 'danger');
    } finally {
        hideLoading();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function getStatusBadge(status) {
    if(!status) return 'bg-secondary';
    const s = status.toLowerCase();
    if(s === 'present' || s === 'resolved' || s === 'replaced') return 'bg-success';
    if(s === 'absent') return 'bg-danger';
    if(s === 'late' || s === 'reported') return 'bg-warning';
    if(s === 'in_progress' || s === 'inside') return 'bg-info';
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
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: '2-digit',
            hour: '2-digit', minute: '2-digit', hour12: true
        });
    } catch (e) {
        return dateString;
    }
}

function showLoading() {
    $('#loadingOverlay').fadeIn(200).css('display', 'flex');
}

function hideLoading() {
    $('#loadingOverlay').fadeOut(200);
}
</script>

<?php include 'layouts/footer.php'; ?>