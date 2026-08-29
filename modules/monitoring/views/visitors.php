<?php 
$currentPage = 'visitors';
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
                            <i class="bi bi-people"></i> 
                            Visitor Log
                        </h2>
                        <p class="text-muted mb-0">Monitor and manage visitor entries</p>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6 px-3 py-2" id="visitorCountBadge">
                            <i class="bi bi-people me-1"></i> 0 records
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards Row -->
            <div class="stats-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalVisitors">0</h3>
                        <span>Total Today</span>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="insideVisitors">0</h3>
                        <span>Currently Active</span>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="pendingCount">0</h3>
                        <span>Pending Approvals</span>
                    </div>
                </div>
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="vehiclesCount">0</h3>
                        <span>Vehicles Registered</span>
                    </div>
                </div>
            </div>
            
            
            <!-- Date Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-start me-1"></i> Start Date
                            </label>
                            <input type="date" class="form-control" id="historyStart" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-end me-1"></i> End Date
                            </label>
                            <input type="date" class="form-control" id="historyEnd" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button class="btn btn-primary w-100" onclick="loadVisitorHistory()">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button class="btn btn-success w-100" onclick="loadTodayVisitors()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Approvals Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        Pending ID Approvals
                    </h5>
                    <span class="badge bg-warning" id="pendingBadge">0</span>
                </div>
                <div class="card-body" id="pendingApprovalsList">
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-hourglass-split" style="font-size: 1.5rem;"></i>
                        <p class="mt-2">Loading...</p>
                    </div>
                </div>
            </div>
            
            <!-- Visitors Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul text-primary me-2"></i>
                        Visitor Records
                    </h5>
                    <span class="badge bg-primary" id="visitorCount">0 records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="visitorTable">
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
                                    <th><i class="bi bi-gear me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody id="visitorBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Entry Modal -->
<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus text-primary me-2"></i> Log Visitor Entry
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="entryForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Visitor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="visitor_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contact_number" placeholder="09XX-XXX-XXXX" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">ID Type</label>
                                <select class="form-select" name="id_type">
                                    <option value="government_id">Government ID</option>
                                    <option value="school_id">School ID</option>
                                    <option value="company_id">Company ID</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">ID Number</label>
                                <input type="text" class="form-control" name="id_number" placeholder="ID Number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vehicle Type</label>
                                <select class="form-select" name="vehicle_type">
                                    <option value="none">None</option>
                                    <option value="car">Car</option>
                                    <option value="motorcycle">Motorcycle</option>
                                    <option value="bicycle">Bicycle</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vehicle Plate Number</label>
                                <input type="text" class="form-control" name="vehicle_plate" placeholder="Plate Number">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purpose of Visit <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="purpose_of_visit" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Person to Visit</label>
                                <input type="text" class="form-control" name="person_to_visit" placeholder="Who are you visiting?">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" class="form-control" name="department" placeholder="Department">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monitored By</label>
                        <input type="text" class="form-control" name="monitored_by" value="Administrator">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitEntry()">
                    <i class="bi bi-person-plus me-1"></i> Log Entry
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View ID Modal -->
<div class="modal fade" id="viewIDModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-image text-primary me-2"></i> <span id="viewIDModalTitle">ID Attachment</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="idImagePreview" src="" alt="ID Image" class="img-fluid rounded" style="max-height: 80vh; max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <a href="#" id="downloadIdImage" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
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

.badge.fs-6 {
    font-size: 0.9rem;
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

.btn-info {
    color: #fff;
    background-color: var(--info);
    border-color: var(--info);
}

.btn-info:hover {
    background: #0e7490;
    border-color: #0e7490;
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

.form-select {
    display: block;
    width: 100%;
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.6;
    color: var(--dark);
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    appearance: none;
    transition: border-color var(--transition), box-shadow var(--transition);
}

.form-select:focus {
    border-color: #19006b;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(25, 0, 107, 0.25);
}

/* Images in Tables */
.visitor-id-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid var(--border);
    cursor: pointer;
    transition: all 0.2s ease;
}

.visitor-id-thumb:hover {
    transform: scale(1.05);
    border-color: #19006b;
    box-shadow: var(--shadow-md);
}

/* Status Colors in Tables */
.status-inside { background: var(--success); color: #fff; }
.status-left { background: var(--secondary); color: #fff; }
.status-pending_approval { background: var(--warning); color: #fff; }
.status-approved { background: var(--info); color: #fff; }

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

.modal-header .text-primary {
    color: var(--primary) !important;
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
    .visitor-id-thumb { width: 40px; height: 40px; }
}

@media (max-width: 576px) {
    .col-md-10 { width: 100%; padding: 0.25rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .stat-card { padding: 0.75rem; }
    .stat-card .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
    .stat-card .stat-info h3 { font-size: 1rem; }
    .stat-card .stat-info span { font-size: 0.7rem; }
    .modal-lg { max-width: 95%; margin: 0.25rem; }
    .toast-custom { max-width: 100%; margin: 0 0.5rem 0.5rem 0.5rem; }
}
</style>

<script>
// ============================================
// TOAST NOTIFICATION
// ============================================

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
// PAGE INITIALIZATION
// ============================================
$(document).ready(function() {
    loadTodayVisitors();
    loadPendingApprovals();
    loadDashboardStats();
});

// ============================================
// API CALL FUNCTION
// ============================================

async function apiCall(endpoint, method = 'GET', data = null) {
    console.log('apiCall:', endpoint, method, data);
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
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            if (response.status === 401 || response.status === 302) {
                showToast('Session expired. Please login again.', 'danger');
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
                return null;
            }
            throw new Error('HTTP error ' + response.status);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Unexpected response format');
            }
        }

        const result = await response.json();
        console.log('API Response:', result);
        return result;
    } catch (error) {
        console.error('API call error:', error);
        showToast('Error: ' + error.message, 'danger');
        return null;
    }
}

// ============================================
// LOAD FUNCTIONS
// ============================================

async function loadTodayVisitors() {
    showLoading();
    try {
        const data = await apiCall('visitor/today');
        if (data) {
            renderVisitors(data);
            updateStats(data);
            $('#visitorCountBadge').html(`<i class="bi bi-people me-1"></i> ${data.length} records`);
        } else {
            renderVisitors([]);
            updateStats([]);
            $('#visitorCountBadge').html(`<i class="bi bi-people me-1"></i> 0 records`);
        }
    } catch (error) {
        console.error('Error loading visitors:', error);
        renderVisitors([]);
        updateStats([]);
        showToast('Error loading visitors', 'danger');
    } finally {
        hideLoading();
    }
}

async function loadVisitorsInside() {
    showLoading();
    try {
        const data = await apiCall('visitor/inside');
        if (data && data.length > 0) {
            renderVisitors(data);
            showToast(`Found ${data.length} active records`, 'success');
        } else {
            renderVisitors([]);
            showToast('No active records found', 'info');
        }
    } catch (error) {
        console.error('Error loading visitors inside:', error);
        showToast('Error loading active visitors', 'danger');
    } finally {
        hideLoading();
    }
}

async function loadVisitorHistory() {
    const start = $('#historyStart').val();
    const end = $('#historyEnd').val();
    
    if (!start || !end) {
        showToast('Please select both start and end dates', 'warning');
        return;
    }
    
    showLoading();
    try {
        const data = await apiCall(`visitor/history?start=${start}&end=${end}`);
        if (data) {
            renderVisitors(data);
            updateStats(data);
            showToast(`Found ${data.length} records`, 'success');
        } else {
            renderVisitors([]);
            updateStats([]);
            showToast('No records found', 'info');
        }
    } catch (error) {
        console.error('Error loading visitor history:', error);
        showToast('Error loading visitor history', 'danger');
    } finally {
        hideLoading();
    }
}

async function loadDashboardStats() {
    try {
        const data = await apiCall('visitor/statistics');
        if (data) {
            $('#totalVisitors').text(data.today || 0);
            $('#insideVisitors').text(data.inside || 0);
            $('#pendingCount').text(data.pending || 0);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadPendingApprovals() {
    try {
        const data = await apiCall('visitor/pending-approvals');
        const container = document.getElementById('pendingApprovalsList');
        const badge = document.getElementById('pendingBadge');
        
        if (data && data.length > 0) {
            badge.textContent = data.length;
            badge.style.display = 'inline-flex';
            $('#pendingCount').text(data.length);
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bi bi-person me-1"></i> Name</th>
                                <th><i class="bi bi-phone me-1"></i> Contact</th>
                                <th><i class="bi bi-clock me-1"></i> Time In</th>
                                <th><i class="bi bi-chat me-1"></i> Purpose</th>
                                <th><i class="bi bi-image me-1"></i> ID Image</th>
                                <th><i class="bi bi-gear me-1"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(visitor => {
                let imageHtml = '<span class="text-muted">No ID</span>';
                if (visitor.id_attachment) {
                    let imgSrc = visitor.id_attachment;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imageHtml = `
                        <img src="${imgSrc}" 
                             alt="ID" 
                             class="visitor-id-thumb"
                             onclick="viewID('${imgSrc}', '${escapeHtml(visitor.visitor_name)} ID')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid</span>';">
                    `;
                }
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(visitor.visitor_name)}</strong></td>
                        <td>${escapeHtml(visitor.contact_number)}</td>
                        <td>${formatDate(visitor.time_in)}</td>
                        <td>${escapeHtml(visitor.purpose_of_visit)}</td>
                        <td>${imageHtml}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="approveID(${visitor.id})">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectID(${visitor.id})">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            container.innerHTML = html;
        } else {
            badge.textContent = '0';
            badge.style.display = 'inline-flex';
            $('#pendingCount').text('0');
            
            container.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle" style="font-size: 1.5rem; color: var(--success);"></i>
                    <p class="mt-2 mb-0">No pending approvals</p>
                    <small>All visitor IDs have been reviewed</small>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading pending approvals:', error);
        document.getElementById('pendingApprovalsList').innerHTML = 
            '<div class="alert alert-danger">Error loading pending approvals</div>';
    }
}

// ============================================
// RENDER FUNCTIONS
// ============================================

function renderVisitors(visitors) {
    const tbody = $('#visitorBody');
    tbody.empty();
    
    if (visitors && visitors.length > 0) {
        $('#visitorCount').text(`${visitors.length} records`);
        visitors.forEach(visitor => {
            const statusBadge = getStatusBadge(visitor.status);
            
            let imageHtml = '<span class="text-muted">No ID</span>';
            if (visitor.id_attachment) {
                let imgSrc = visitor.id_attachment;
                if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                    imgSrc = '/' + imgSrc;
                }
                imageHtml = `
                    <img src="${imgSrc}" 
                         alt="ID" 
                         class="visitor-id-thumb"
                         onclick="viewID('${imgSrc}', '${escapeHtml(visitor.visitor_name)} ID')"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid</span>';">
                `;
            }
            
            tbody.append(`
                <tr>
                    <td><strong>${escapeHtml(visitor.visitor_name)}</strong></td>
                    <td>${escapeHtml(visitor.contact_number)}</td>
                    <td>${escapeHtml(visitor.id_type || 'N/A')}</td>
                    <td>${escapeHtml(visitor.vehicle_plate || 'N/A')}</td>
                    <td>${escapeHtml(visitor.purpose_of_visit)}</td>
                    <td>${imageHtml}</td>
                    <td>${formatDate(visitor.time_in)}</td>
                    <td><span class="badge ${statusBadge}">${escapeHtml(visitor.status || 'N/A')}</span></td>
                    <td>
                        <span class="text-muted">Logged In</span>
                    </td>
                </tr>
            `);
        });
    } else {
        $('#visitorCount').text('0 records');
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="empty-state">
                        <i class="bi bi-people"></i>
                        <h6>No visitors found</h6>
                        <small>Click "Log Entry" to add a new visitor</small>
                    </div>
                </td>
            </tr>
        `);
    }
}

function updateStats(visitors) {
    if (!visitors || visitors.length === 0) {
        $('#totalVisitors').text('0');
        $('#insideVisitors').text('0');
        $('#vehiclesCount').text('0');
        return;
    }
    
    const total = visitors.length;
    const inside = visitors.filter(v => v.status === 'inside' || v.status === 'approved').length;
    const vehicles = visitors.filter(v => v.vehicle_plate && v.vehicle_plate !== 'none' && v.vehicle_plate !== '').length;
    
    $('#totalVisitors').text(total);
    $('#insideVisitors').text(inside);
    $('#vehiclesCount').text(vehicles);
}

// ============================================
// ACTION FUNCTIONS
// ============================================

function showEntryModal() {
    $('#entryForm')[0].reset();
    $('#entryModal').modal('show');
}

async function submitEntry() {
    const form = document.getElementById('entryForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    const phoneRegex = /^[0-9+\-\s()]{7,20}$/;
    if (!phoneRegex.test(data.contact_number)) {
        showToast('Please enter a valid contact number', 'warning');
        return;
    }
    
    showLoading();
    
    try {
        const result = await apiCall('visitor/entry', 'POST', data);
        if (result && result.success) {
            showToast('Visitor entry logged successfully!', 'success');
            $('#entryModal').modal('hide');
            loadTodayVisitors();
            loadDashboardStats();
        } else {
            const errorMsg = result?.errors ? result.errors.join(', ') : (result?.error || 'Failed to log entry');
            showToast(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Error logging entry:', error);
        showToast('Failed to log visitor entry. Please try again.', 'danger');
    } finally {
        hideLoading();
    }
}

// ============================================
// VIEW ID IMAGE
// ============================================

function viewID(imagePath, title) {
    if (!imagePath) {
        showToast('No ID image available', 'warning');
        return;
    }
    
    const img = document.getElementById('idImagePreview');
    img.src = imagePath;
    img.onerror = function() {
        showToast('Failed to load image', 'danger');
    };
    
    document.getElementById('viewIDModalTitle').textContent = title || 'ID Attachment';
    
    const downloadLink = document.getElementById('downloadIdImage');
    downloadLink.href = imagePath;
    const filename = imagePath.split('/').pop() || 'id_image.jpg';
    downloadLink.download = filename;
    
    const modal = new bootstrap.Modal(document.getElementById('viewIDModal'));
    modal.show();
}

// ============================================
// APPROVE FUNCTIONS
// ============================================

async function approveID(id) {
    if (!confirm('✅ Approve this visitor ID? The visitor will be allowed entry.')) return;
    
    showLoading();
    
    try {
        const result = await apiCall(`visitor/approve/${id}`, 'POST');
        
        if (result && result.success) {
            showToast('✅ ID approved successfully!', 'success');
            await loadPendingApprovals();
            await loadTodayVisitors();
            loadDashboardStats();
        } else {
            const errorMsg = result?.error || result?.message || 'Failed to approve ID';
            showToast('❌ Failed to approve ID: ' + errorMsg, 'danger');
        }
    } catch (error) {
        console.error('❌ Error approving ID:', error);
        showToast('❌ Failed to approve ID: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

async function rejectID(id) {
    if (!confirm('Reject this visitor ID? They will need to register again.')) return;
    
    showLoading();
    
    try {
        const result = await apiCall(`visitor/delete/${id}`, 'DELETE');
        if (result && result.success) {
            showToast('Visitor registration rejected and removed.', 'info');
            loadPendingApprovals();
            loadTodayVisitors();
            loadDashboardStats();
        } else {
            showToast('Failed to reject registration', 'danger');
        }
    } catch (error) {
        console.error('Error rejecting ID:', error);
        showToast('Failed to reject registration', 'danger');
    } finally {
        hideLoading();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function getStatusBadge(status) {
    const badges = {
        'inside': 'bg-success',
        'left': 'bg-secondary',
        'pending_approval': 'bg-warning',
        'approved': 'bg-info'
    };
    return badges[status] || 'bg-secondary';
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
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
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