<?php 
$currentPage = 'attendance';
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
                            <i class="bi bi-calendar-check"></i> 
                            Faculty Attendance Monitoring
                        </h2>
                        <p class="text-muted mb-0">View faculty schedules and attendance records</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-info py-2 px-3">
                            <i class="bi bi-phone me-1"></i> Use mobile device to mark attendance
                        </span>
                        <a href="/mobile-attendance" class="btn btn-primary btn-sm" target="_blank">
                            <i class="bi bi-phone me-1"></i> Open Mobile View
                        </a>
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
                        <h3 id="totalSchedules">0</h3>
                        <span>Total Schedules</span>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalAttendance">0</h3>
                        <span>Attendance Records</span>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="pendingCount">0</h3>
                        <span>Pending Markings</span>
                    </div>
                </div>
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="facultyCount">0</h3>
                        <span>Active Faculty</span>
                    </div>
                </div>
            </div>

            <!-- Week Navigator & Day Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Week Navigation -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <button class="btn btn-outline-secondary btn-sm" onclick="navigateWeek(-1)">
                            <i class="bi bi-chevron-left"></i> Previous Week
                        </button>
                        
                        <div class="text-center">
                            <h5 class="mb-0 fw-semibold" id="currentWeekDisplay">This Week</h5>
                            <small class="text-muted" id="currentWeekRange"></small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" onclick="navigateWeek(1)">
                                Next Week <i class="bi bi-chevron-right"></i>
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="goToCurrentWeek()">
                                <i class="bi bi-calendar-week me-1"></i> Current Week
                            </button>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <!-- Day Selector -->
                    <div class="day-selector">
                        <label class="text-muted small fw-semibold d-block mb-2">
                            <i class="bi bi-calendar3 me-1"></i> Select Day:
                        </label>
                        <div class="d-flex gap-2 flex-wrap justify-content-center" id="daySelector">
                            <button class="btn btn-sm day-btn active" data-day="Monday" onclick="selectDay('Monday')">
                                <i class="bi bi-calendar"></i> Monday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Tuesday" onclick="selectDay('Tuesday')">
                                <i class="bi bi-calendar"></i> Tuesday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Wednesday" onclick="selectDay('Wednesday')">
                                <i class="bi bi-calendar"></i> Wednesday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Thursday" onclick="selectDay('Thursday')">
                                <i class="bi bi-calendar"></i> Thursday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Friday" onclick="selectDay('Friday')">
                                <i class="bi bi-calendar"></i> Friday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Saturday" onclick="selectDay('Saturday')">
                                <i class="bi bi-calendar"></i> Saturday
                            </button>
                            <button class="btn btn-sm btn-outline-primary day-btn" data-day="Sunday" onclick="selectDay('Sunday')">
                                <i class="bi bi-calendar"></i> Sunday
                            </button>
                        </div>
                    </div>
                    
                    <!-- Selected Day Display -->
                    <div class="text-center mt-3">
                        <span class="badge bg-primary fs-6 px-3 py-2" id="selectedDayDisplay">Monday</span>
                        <span class="badge bg-secondary fs-6 px-3 py-2 ms-2" id="scheduleCountDisplay">0 schedules</span>
                    </div>
                </div>
            </div>
            
            <!-- Hidden inputs -->
            <input type="hidden" id="attendanceDateHidden" value="<?php echo date('Y-m-d'); ?>">
            <input type="hidden" id="selectedDay" value="Monday">
            
            <!-- Last updated indicator -->
            <div class="d-flex justify-content-end align-items-center mb-3 gap-2">
                <small class="text-muted" id="lastUpdated">
                    <i class="bi bi-clock-history me-1"></i> Last updated: just now
                </small>
                <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>
            
            <!-- Schedules Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-table text-primary me-2"></i> 
                        Schedules for <span id="scheduleDayLabel" class="text-primary">Monday</span>
                    </h5>
                    <span class="badge bg-primary" id="scheduleCount">0 schedules</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="scheduleTable">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-clock me-1"></i> Time</th>
                                    <th><i class="bi bi-book me-1"></i> Course/Section</th>
                                    <th><i class="bi bi-calendar me-1"></i> Day</th>
                                    <th><i class="bi bi-person me-1"></i> Faculty</th>
                                    <th><i class="bi bi-door-open me-1"></i> Room</th>
                                    <th><i class="bi bi-hash me-1"></i> Subject Code</th>
                                    <th><i class="bi bi-people me-1"></i> Students</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                    <th><i class="bi bi-check-circle me-1"></i> Attendance</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Records -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-check2-square text-success me-2"></i> 
                        Attendance Records for <span id="attendanceDayLabel" class="text-primary">Monday</span>
                    </h5>
                    <span class="badge bg-success" id="attendanceCount">0 records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="attendanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-person me-1"></i> Faculty</th>
                                    <th><i class="bi bi-book me-1"></i> Course/Section</th>
                                    <th><i class="bi bi-hash me-1"></i> Subject</th>
                                    <th><i class="bi bi-door-open me-1"></i> Room</th>
                                    <th><i class="bi bi-people me-1"></i> Students</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                    <th><i class="bi bi-shield-check me-1"></i> Verification</th>
                                    <th><i class="bi bi-camera me-1"></i> Face-to-Face Photo</th>
                                    <th><i class="bi bi-clock me-1"></i> Time</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-image text-primary me-2"></i> 
                    <span id="imageModalTitle">Image</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="imagePreview" src="" alt="Image" class="img-fluid rounded" style="max-height: 80vh; max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <a href="#" id="downloadImage" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<style>
/* ==============================================
   UNIFIED UI STYLES - Sidebar Color Theme
   Matches the sidebar background: #19006b
   ============================================== */

/* CSS Variables - Matching Sidebar Color */
:root {
    --primary: #19006b;
    --primary-light: #e8e3f5;
    --primary-dark: #0f0045;
    --primary-medium: #2d1a7a;
    --primary-rgb: 25, 0, 107;
    --primary-gradient-start: #0f0045;
    --primary-gradient-end: #2d1a7a;
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

.page-header .btn-primary {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    backdrop-filter: blur(4px);
}

.page-header .btn-primary:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
}

/* Stats Cards - Matching Dashboard Style */
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

/* Stat Card Colors - Matching Sidebar Theme */
.stat-card-primary .stat-icon {
    background: #e8e3f5;
    color: #19006b;
}

.stat-card-success .stat-icon {
    background: var(--success-light);
    color: var(--success);
}

.stat-card-warning .stat-icon {
    background: var(--warning-light);
    color: var(--warning);
}

.stat-card-purple .stat-icon {
    background: var(--purple-light);
    color: var(--purple);
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

.card-header .text-primary {
    color: var(--primary) !important;
}

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
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border);
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Badges - Using Sidebar Color */
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

.badge.fs-6 {
    font-size: 0.9rem;
}

/* Buttons - Using Sidebar Color */
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

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 6px;
}

.btn i {
    font-size: 1em;
}

/* Day Selector */
.day-selector {
    text-align: center;
}

.day-btn {
    min-width: 90px;
    justify-content: center;
    border-radius: 8px;
}

.day-btn.active {
    background: linear-gradient(135deg, #19006b, #2d1a7a);
    color: #fff;
    border-color: #19006b;
}

.day-btn.active:hover {
    background: linear-gradient(135deg, #0f0045, #19006b);
    border-color: #0f0045;
}

.day-btn.btn-outline-primary {
    color: #19006b;
    border-color: #19006b;
}

.day-btn.btn-outline-primary:hover {
    background: linear-gradient(135deg, #19006b, #2d1a7a);
    color: #fff;
    border-color: #19006b;
}

/* Status Colors in Tables */
.status-scheduled { background: linear-gradient(135deg, #19006b, #2d1a7a); color: #fff; }
.status-completed { background-color: var(--success); color: #fff; }
.status-cancelled { background-color: var(--danger); color: #fff; }
.status-present { background-color: var(--success); color: #fff; }
.status-absent { background-color: var(--danger); color: #fff; }
.status-late { background-color: var(--warning); color: #fff; }
.status-excused { background-color: var(--info); color: #fff; }
.status-online { background: linear-gradient(135deg, #19006b, #2d1a7a); color: #fff; }
.status-reported { background-color: var(--warning); color: #fff; }
.status-in-progress { background-color: var(--info); color: #fff; }
.status-resolved { background-color: var(--success); color: #fff; }
.status-replaced { background-color: var(--secondary); color: #fff; }

/* Images in Tables */
.attendance-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid var(--border);
    transition: all 0.2s ease;
}

.attendance-thumbnail:hover {
    transform: scale(1.05);
    border-color: #19006b;
    box-shadow: var(--shadow-md);
}

/* Toast Notifications */
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

.toast-custom .toast-icon {
    font-size: 1.25rem;
}

.toast-custom .toast-message {
    flex: 1;
    font-size: 0.875rem;
    color: var(--dark);
}

.toast-custom .toast-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--muted);
    padding: 0 0.25rem;
}

.toast-custom .toast-close:hover {
    color: var(--dark);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Modal */
.modal-content {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
}

.modal-body {
    padding: 1.5rem;
    background: #fafafa;
}

.modal-footer {
    padding: 0.75rem 1.5rem;
    border-top: 1px solid var(--border);
    background: #fafafa;
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

/* Empty State */
.empty-state {
    padding: 2.5rem 1.5rem;
    text-align: center;
}

.empty-state i {
    font-size: 3rem;
    color: var(--muted);
    opacity: 0.5;
}

.empty-state h6 {
    margin-top: 1rem;
    font-weight: 600;
}

.empty-state small {
    color: var(--muted);
}

/* Responsive */
@media (max-width: 992px) {
    .main-content {
        padding: 1rem;
    }
    
    .page-header {
        padding: 1.25rem;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 0.75rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-card .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .stat-card .stat-info h3 {
        font-size: 1.25rem;
    }
    
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.5rem 0.6rem;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .badge {
        font-size: 0.65em;
        padding: 0.25em 0.5em;
    }
    
    .page-title {
        font-size: 1.1rem;
    }
    
    .day-btn {
        min-width: 70px;
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
    }
    
    .card-header {
        padding: 0.625rem 0.875rem;
    }
    
    .card-header h5 {
        font-size: 0.95rem;
    }
    
    .attendance-thumbnail {
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 576px) {
    .col-md-10 {
        width: 100%;
        padding: 0.25rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    
    .stat-card {
        padding: 0.75rem;
    }
    
    .stat-card .stat-icon {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }
    
    .stat-card .stat-info h3 {
        font-size: 1rem;
    }
    
    .stat-card .stat-info span {
        font-size: 0.7rem;
    }
    
    .modal-lg {
        max-width: 95%;
        margin: 0.25rem;
    }
    
    .toast-custom {
        max-width: 100%;
        margin: 0 0.5rem 0.5rem 0.5rem;
    }
}
</style>

<script>
// ============================================
// ATTENDANCE PAGE CONTROLLER
// ============================================

let currentSelectedDay = 'Monday';
let currentWeekOffset = 0;
let autoRefreshInterval = null;
let isLoading = false;

$(document).ready(function() {
    // Set default selected day
    const today = new Date();
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    currentSelectedDay = days[today.getDay()];
    
    $('#selectedDay').val(currentSelectedDay);
    
    updateDayDisplay(currentSelectedDay);
    updateWeekDisplay();
    loadAttendanceData(currentSelectedDay);
    
    // Highlight current day
    highlightDay(currentSelectedDay);
    
    // Start auto-refresh
    startAutoRefresh();
});

// ============================================
// HELPER FUNCTIONS
// ============================================

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getStatusBadge(status) {
    const statusMap = {
        'Scheduled': 'bg-primary',
        'Completed': 'bg-success',
        'Cancelled': 'bg-danger',
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'excused': 'bg-info',
        'online': 'bg-primary',
        'reported': 'bg-warning',
        'in_progress': 'bg-info',
        'resolved': 'bg-success',
        'replaced': 'bg-secondary'
    };
    return statusMap[status] || 'bg-secondary';
}

function getStatusClass(status) {
    const classMap = {
        'Scheduled': 'status-scheduled',
        'Completed': 'status-completed',
        'Cancelled': 'status-cancelled',
        'present': 'status-present',
        'absent': 'status-absent',
        'late': 'status-late',
        'excused': 'status-excused',
        'online': 'status-online',
        'reported': 'status-reported',
        'in_progress': 'status-in-progress',
        'resolved': 'status-resolved',
        'replaced': 'status-replaced'
    };
    return classMap[status] || '';
}

function formatSingleTime(timeStr) {
    if (!timeStr) return 'N/A';
    
    try {
        if (typeof timeStr === 'string' && timeStr.match(/^\d{2}:\d{2}(:\d{2})?$/)) {
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const hours12 = hours % 12 || 12;
            return `${hours12}:${minutes} ${ampm}`;
        }
        
        const date = new Date(timeStr);
        if (!isNaN(date.getTime())) {
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
        }
        
        return timeStr;
    } catch (e) {
        return timeStr || 'N/A';
    }
}

function formatTime(startTime, endTime) {
    if (!startTime) return 'N/A';
    
    try {
        if (endTime) {
            const startFormatted = formatSingleTime(startTime);
            const endFormatted = formatSingleTime(endTime);
            return `${startFormatted} - ${endFormatted}`;
        }
        return formatSingleTime(startTime);
    } catch (e) {
        console.error('Error formatting time:', e);
        return startTime || 'N/A';
    }
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleString('en-US', { 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    } catch (e) {
        return dateStr;
    }
}

function showToast(message, type = 'info') {
    const iconMap = {
        success: 'bi-check-circle-fill text-success',
        danger: 'bi-exclamation-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        info: 'bi-info-circle-fill text-primary'
    };
    
    const icon = iconMap[type] || iconMap.info;
    
    const toast = $(`
        <div class="toast-custom toast-${type}">
            <i class="bi ${icon} toast-icon"></i>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="$(this).closest('.toast-custom').remove()">&times;</button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.css('animation', 'slideOut 0.3s ease');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// ============================================
// API CALL
// ============================================

function getMonitoringBasePath() {
    const pathname = window.location.pathname || '/';
    const candidates = [
        '/modules/monitoring/public',
        '/Monitoring2/public',
        '/modules/monitoring',
        '/Monitoring2'
    ];

    for (const candidate of candidates) {
        const index = pathname.indexOf(candidate);
        if (index !== -1) {
            return pathname.substring(0, index + candidate.length);
        }
    }

    return '';
}

function getMonitoringApiUrl(endpoint) {
    const cleanEndpoint = (endpoint || '').replace(/^\/+/, '');
    const normalized = cleanEndpoint.startsWith('api/') ? cleanEndpoint : 'api/' + cleanEndpoint;
    const basePath = getMonitoringBasePath();
    return basePath ? `${basePath}/${normalized}` : `/${normalized}`;
}

async function apiCall(endpoint) {
    try {
        const response = await fetch(getMonitoringApiUrl(endpoint));
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API call error:', error);
        throw error;
    }
}

// ============================================
// WEEK NAVIGATION
// ============================================

function navigateWeek(direction) {
    currentWeekOffset += direction;
    updateWeekDisplay();
    loadAttendanceData(currentSelectedDay);
}

function goToCurrentWeek() {
    if (currentWeekOffset !== 0) {
        currentWeekOffset = 0;
        updateWeekDisplay();
        loadAttendanceData(currentSelectedDay);
    }
}

function updateWeekDisplay() {
    const today = new Date();
    const currentWeekStart = new Date(today);
    const dayOfWeek = today.getDay();
    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    currentWeekStart.setDate(today.getDate() + mondayOffset + (currentWeekOffset * 7));
    
    const weekEnd = new Date(currentWeekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);
    
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const startStr = currentWeekStart.toLocaleDateString('en-US', options);
    const endStr = weekEnd.toLocaleDateString('en-US', options);
    
    if (currentWeekOffset === 0) {
        $('#currentWeekDisplay').text('This Week');
    } else if (currentWeekOffset === -1) {
        $('#currentWeekDisplay').text('Last Week');
    } else if (currentWeekOffset === 1) {
        $('#currentWeekDisplay').text('Next Week');
    } else if (currentWeekOffset < 0) {
        $('#currentWeekDisplay').text(`${Math.abs(currentWeekOffset)} Weeks Ago`);
    } else {
        $('#currentWeekDisplay').text(`In ${currentWeekOffset} Weeks`);
    }
    
    $('#currentWeekRange').text(`${startStr} - ${endStr}`);
}

// ============================================
// DAY SELECTION
// ============================================

function selectDay(day) {
    if (day === currentSelectedDay) return;
    
    currentSelectedDay = day;
    $('#selectedDay').val(day);
    updateDayDisplay(day);
    highlightDay(day);
    loadAttendanceData(day);
}

function updateDayDisplay(day) {
    $('#selectedDayDisplay').text(day);
    $('#scheduleDayLabel').text(day);
    $('#attendanceDayLabel').text(day);
}

function highlightDay(day) {
    $('.day-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
    const $targetBtn = $(`.day-btn[data-day="${day}"]`);
    $targetBtn.removeClass('btn-outline-primary').addClass('active btn-primary');
}

function updateLastUpdated() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    $('#lastUpdated').html(`<i class="bi bi-clock-history me-1"></i> Last updated: ${timeStr}`);
}

// ============================================
// AUTO REFRESH
// ============================================

function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshInterval = setInterval(function() {
        if (!isLoading) {
            try {
                loadAttendanceData(currentSelectedDay, true);
            } catch (error) {
                console.error('Auto-refresh error:', error);
            }
        }
    }, 30000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// ============================================
// LOAD DATA
// ============================================

function loadAttendanceData(day, silent = false) {
    if (!day) day = currentSelectedDay;
    if (isLoading) return;
    
    isLoading = true;
    
    // Load both in parallel
    Promise.all([
        loadSchedules(day, silent),
        loadAttendanceRecords(day, silent)
    ]).finally(() => {
        isLoading = false;
        updateLastUpdated();
    });
}

// ============================================
// LOAD SCHEDULES
// ============================================

async function loadSchedules(day, silent = false) {
    try {
        const schedules = await apiCall('attendance/schedules?day=' + encodeURIComponent(day));
        const tbody = $('#scheduleBody');
        
        if (schedules && schedules.length > 0) {
            try {
                const attendanceRecords = await apiCall('attendance/records?day=' + encodeURIComponent(day));
                const markedScheduleIds = attendanceRecords ? attendanceRecords.map(r => r.schedule_id) : [];
                
                $('#scheduleCount').text(`${schedules.length} schedules`);
                $('#scheduleCountDisplay').text(`${schedules.length} schedules`);
                
                // Update stats
                $('#totalSchedules').text(schedules.length);
                const pending = schedules.filter(s => !markedScheduleIds.includes(s.id)).length;
                $('#pendingCount').text(pending);
                
                let html = '';
                schedules.forEach(schedule => {
                    const statusBadge = getStatusBadge(schedule.status);
                    const statusClass = getStatusClass(schedule.status);
                    const isMarked = markedScheduleIds.includes(schedule.id);
                    const attendanceBadge = isMarked 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Marked</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-clock"></i> Pending</span>';
                    
                    const studentCount = schedule.student_count || 0;
                    const studentBadge = studentCount > 0 
                        ? `<span class="badge bg-info">${studentCount}</span>`
                        : `<span class="badge bg-secondary">0</span>`;
                    
                    const timeDisplay = formatTime(schedule.start_time, schedule.end_time);
                    
                    html += `
                        <tr>
                            <td><strong>${timeDisplay}</strong></td>
                            <td>${escapeHtml(schedule.course_section)}</td>
                            <td>${escapeHtml(schedule.day)}</td>
                            <td><strong>${escapeHtml(schedule.faculty_name)}</strong></td>
                            <td>${escapeHtml(schedule.room)}</td>
                            <td><span class="badge bg-secondary">${escapeHtml(schedule.subject_code)}</span></td>
                            <td>${studentBadge}</td>
                            <td><span class="badge ${statusBadge} ${statusClass}">${escapeHtml(schedule.status)}</span></td>
                            <td>${attendanceBadge}</td>
                        </tr>
                    `;
                });
                
                if (tbody.html() !== html) {
                    tbody.html(html);
                }
            } catch (error) {
                console.error('Error getting attendance records:', error);
                // Fallback: Show schedules without attendance status
                let html = '';
                schedules.forEach(schedule => {
                    const statusBadge = getStatusBadge(schedule.status);
                    const statusClass = getStatusClass(schedule.status);
                    const studentCount = schedule.student_count || 0;
                    const studentBadge = studentCount > 0 
                        ? `<span class="badge bg-info">${studentCount}</span>`
                        : `<span class="badge bg-secondary">0</span>`;
                    
                    const timeDisplay = formatTime(schedule.start_time, schedule.end_time);
                    
                    html += `
                        <tr>
                            <td><strong>${timeDisplay}</strong></td>
                            <td>${escapeHtml(schedule.course_section)}</td>
                            <td>${escapeHtml(schedule.day)}</td>
                            <td><strong>${escapeHtml(schedule.faculty_name)}</strong></td>
                            <td>${escapeHtml(schedule.room)}</td>
                            <td><span class="badge bg-secondary">${escapeHtml(schedule.subject_code)}</span></td>
                            <td>${studentBadge}</td>
                            <td><span class="badge ${statusBadge} ${statusClass}">${escapeHtml(schedule.status)}</span></td>
                            <td><span class="badge bg-secondary">Unknown</span></td>
                        </tr>
                    `;
                });
                if (tbody.html() !== html) {
                    tbody.html(html);
                }
            }
        } else {
            $('#scheduleCount').text('0 schedules');
            $('#scheduleCountDisplay').text('0 schedules');
            $('#totalSchedules').text('0');
            $('#pendingCount').text('0');
            
            const html = `
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h6>No schedules found for ${escapeHtml(day)}</h6>
                            <small>Check other days or add schedules</small>
                        </div>
                    </td>
                </tr>
            `;
            if (tbody.html() !== html) {
                tbody.html(html);
            }
        }
    } catch (error) {
        console.error('Error loading schedules:', error);
        const html = `
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h6 class="mt-2">Error loading schedules</h6>
                    <small>Please check your connection and try again</small>
                </td>
            </tr>
        `;
        const tbody = $('#scheduleBody');
        if (tbody.html() !== html) {
            tbody.html(html);
        }
    }
}

// ============================================
// LOAD ATTENDANCE RECORDS
// ============================================

async function loadAttendanceRecords(day, silent = false) {
    try {
        const records = await apiCall('attendance/records?day=' + encodeURIComponent(day));
        const tbody = $('#attendanceBody');
        
        if (records && records.length > 0) {
            $('#attendanceCount').text(`${records.length} records`);
            $('#totalAttendance').text(records.length);
            
            // Count unique faculty
            const facultyNames = records.map(r => r.faculty_name).filter(Boolean);
            const uniqueFaculty = new Set(facultyNames);
            $('#facultyCount').text(uniqueFaculty.size);
            
            let html = '';
            records.forEach(record => {
                const statusBadge = getStatusBadge(record.status);
                const statusClass = getStatusClass(record.status);
                const isOnline = record.is_online ? '<span class="badge bg-info ms-1">Online</span>' : '';
                const studentCount = record.student_count || 0;
                const studentBadge = studentCount > 0 
                    ? `<span class="badge bg-info">${studentCount}</span>`
                    : `<span class="badge bg-secondary">0</span>`;
                
                let imageHtml = '<span class="text-muted">No image</span>';
                
                if (record.face_to_face_image) {
                    let imgSrc = record.face_to_face_image;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imageHtml = `
                        <img src="${imgSrc}" 
                             alt="Face-to-face" 
                             class="attendance-thumbnail"
                             onclick="viewImage('${imgSrc}', 'Face-to-Face Photo - ${escapeHtml(record.faculty_name)}')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid image</span>';">`;
                } else if (record.meeting_screenshot) {
                    let imgSrc = record.meeting_screenshot;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imageHtml = `
                        <img src="${imgSrc}" 
                             alt="Meeting Screenshot" 
                             class="attendance-thumbnail"
                             onclick="viewImage('${imgSrc}', 'Meeting Screenshot - ${escapeHtml(record.faculty_name)}')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid image</span>';">`;
                }
                
                const checkTime = record.check_time ? formatDate(record.check_time) : 'N/A';
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(record.faculty_name)}</strong></td>
                        <td>${escapeHtml(record.course_section)}</td>
                        <td><span class="badge bg-secondary">${escapeHtml(record.subject_code)}</span></td>
                        <td>${escapeHtml(record.room)}</td>
                        <td>${studentBadge}</td>
                        <td><span class="badge ${statusBadge} ${statusClass}">${escapeHtml(record.status)}</span>${isOnline}</td>
                        <td><span class="badge bg-light text-dark">${escapeHtml(record.verification_method || 'N/A')}</span></td>
                        <td>${imageHtml}</td>
                        <td><small>${checkTime}</small></td>
                    </tr>
                `;
            });
            if (tbody.html() !== html) {
                tbody.html(html);
            }
        } else {
            $('#attendanceCount').text('0 records');
            $('#totalAttendance').text('0');
            $('#facultyCount').text('0');
            
            const html = `
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-clock-history"></i>
                            <h6>No attendance records for ${escapeHtml(day)}</h6>
                            <small>Use mobile device to mark attendance</small>
                        </div>
                    </td>
                </tr>
            `;
            if (tbody.html() !== html) {
                tbody.html(html);
            }
        }
    } catch (error) {
        console.error('Error loading attendance records:', error);
        const html = `
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h6 class="mt-2">Error loading attendance records</h6>
                    <small>Please check your connection and try again</small>
                </td>
            </tr>
        `;
        const tbody = $('#attendanceBody');
        if (tbody.html() !== html) {
            tbody.html(html);
        }
    }
}

// ============================================
// VIEW IMAGE IN MODAL
// ============================================

function viewImage(imageUrl, title) {
    if (!imageUrl) {
        showToast('No image available', 'warning');
        return;
    }
    
    console.log('Viewing image:', imageUrl);
    
    const img = document.getElementById('imagePreview');
    img.src = imageUrl;
    img.onerror = function() {
        console.error('Failed to load image:', imageUrl);
        showToast('Failed to load image. Path: ' + imageUrl, 'danger');
    };
    img.onload = function() {
        console.log('Image loaded successfully');
    };
    
    document.getElementById('imageModalTitle').textContent = title || 'Image';
    
    const downloadLink = document.getElementById('downloadImage');
    downloadLink.href = imageUrl;
    const filename = imageUrl.split('/').pop() || 'image.jpg';
    downloadLink.download = filename;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// ============================================
// MANUAL REFRESH
// ============================================

function refreshData() {
    showToast('Refreshing data...', 'info');
    loadAttendanceData(currentSelectedDay);
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

$(document).keydown(function(e) {
    if ($(e.target).is('input, textarea, select, [contenteditable="true"]')) {
        return;
    }
    
    switch(e.key.toLowerCase()) {
        case 'arrowleft':
            navigateWeek(-1);
            e.preventDefault();
            break;
        case 'arrowright':
            navigateWeek(1);
            e.preventDefault();
            break;
        case 't':
            goToCurrentWeek();
            e.preventDefault();
            break;
        case 'r':
            refreshData();
            e.preventDefault();
            break;
    }
});

// ============================================
// CLEANUP
// ============================================

$(window).on('beforeunload', function() {
    stopAutoRefresh();
});

// Visibility change - resume/pause auto-refresh
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
        loadAttendanceData(currentSelectedDay, true);
    }
});
</script>

<?php include 'layouts/footer.php'; ?>