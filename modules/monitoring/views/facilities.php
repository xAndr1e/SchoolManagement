<?php 
$currentPage = 'facilities';
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
                            <i class="bi bi-building"></i> 
                            Facilities Monitoring
                        </h2>
                        <p class="text-muted mb-0">Monitor and manage facility equipment</p>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6 px-3 py-2" id="facilityCount">
                            <i class="bi bi-boxes me-1"></i> 0 items
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards Row -->
            <div class="stats-grid">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalEquipment">0</h3>
                        <span>Total Equipment</span>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="functionalCount">0</h3>
                        <span>Functional</span>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="damagedCount">0</h3>
                        <span>Damaged/Issues</span>
                    </div>
                </div>
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="reportCount">0</h3>
                        <span>Pending Reports</span>
                    </div>
                </div>
            </div>
            
            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i> Filter by Room
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="roomFilter" placeholder="Enter room number">
                                <button class="btn btn-primary" onclick="filterByRoom()">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="loadAllFacilities()">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tags me-1"></i> Equipment Type
                            </label>
                            <select class="form-select" id="equipmentTypeFilter" onchange="filterByType()">
                                <option value="">All Types</option>
                                <option value="chair">Chair</option>
                                <option value="switch">Switch</option>
                                <option value="light">Light</option>
                                <option value="aircon">Aircon</option>
                                <option value="projector">Projector</option>
                                <option value="computer">Computer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-info-circle me-1"></i> Status
                            </label>
                            <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                                <option value="">All Status</option>
                                <option value="complete">Complete</option>
                                <option value="incomplete">Incomplete</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button class="btn btn-success w-100" onclick="loadAllFacilities()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Facilities Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-boxes text-primary me-2"></i>
                        Equipment Inventory
                    </h5>
                    <span class="badge bg-primary" id="facilityTableCount">0 items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="facilityTable">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-door-open me-1"></i> Room</th>
                                    <th><i class="bi bi-tags me-1"></i> Type</th>
                                    <th><i class="bi bi-box me-1"></i> Equipment</th>
                                    <th><i class="bi bi-hash me-1"></i> Total</th>
                                    <th><i class="bi bi-check-circle me-1"></i> Functional</th>
                                    <th><i class="bi bi-exclamation-triangle me-1"></i> Damaged</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                    <th><i class="bi bi-person me-1"></i> Monitored By</th>
                                    <th><i class="bi bi-clock me-1"></i> Last Checked</th>
                                    <th><i class="bi bi-gear me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody id="facilityBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Reports Logs Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Reported Issues Log
                    </h5>
                    <span class="badge bg-warning" id="reportBadgeCount">0 reports</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="reportTable">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-image me-1"></i> Photo</th>
                                    <th><i class="bi bi-door-open me-1"></i> Room</th>
                                    <th><i class="bi bi-tools me-1"></i> Equipment</th>
                                    <th><i class="bi bi-hash me-1"></i> Quantity</th>
                                    <th><i class="bi bi-chat me-1"></i> Description</th>
                                    <th><i class="bi bi-person me-1"></i> Reported By</th>
                                    <th><i class="bi bi-calendar me-1"></i> Date</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                    <th><i class="bi bi-gear me-1"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-image text-primary me-2"></i> Damage Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="previewImageTarget" src="" class="img-fluid rounded-bottom" alt="Damage Photo Preview" style="max-height: 80vh; object-fit: contain;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <a href="#" id="downloadDamageImage" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Update Report Status Modal -->
<div class="modal fade" id="updateReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square text-primary me-2"></i> Update Report Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateReportForm">
                    <input type="hidden" id="updateReportId" name="report_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status" id="updateReportStatus" required>
                            <option value="reported">Reported</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="replaced">Replaced</option>
                        </select>
                    </div>
                    <div class="mb-3" id="resolvedDateGroup" style="display:none;">
                        <label class="form-label fw-semibold">Resolved Date</label>
                        <input type="date" class="form-control" name="resolved_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="submitUpdateReport()">
                    <i class="bi bi-check-lg me-1"></i> Update Status
                </button>
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

.btn-info {
    color: #fff;
    background-color: var(--info);
    border-color: var(--info);
}

.btn-info:hover {
    background: #0e7490;
    border-color: #0e7490;
}

.btn-danger {
    color: #fff;
    background-color: var(--danger);
    border-color: var(--danger);
}

.btn-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
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

.input-group > .btn {
    border-radius: 0;
}

.input-group > .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.input-group > .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

/* Image Thumbnails */
.img-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid var(--border);
    cursor: pointer;
    transition: all 0.2s ease;
}

.img-thumbnail:hover {
    transform: scale(1.05);
    border-color: #19006b;
    box-shadow: var(--shadow-md);
}

/* Status Colors in Tables */
.status-complete { background: var(--success); color: #fff; }
.status-incomplete { background: var(--warning); color: #fff; }
.status-needs_repair { background: var(--danger); color: #fff; }
.status-reported { background: var(--warning); color: #fff; }
.status-in_progress { background: var(--info); color: #fff; }
.status-resolved { background: var(--success); color: #fff; }
.status-replaced { background: var(--secondary); color: #fff; }

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
$(document).ready(function() {
    console.log('Document ready - loading facilities...');
    loadAllFacilities();
    loadReports();
});

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
// FACILITIES FUNCTIONS
// ============================================

async function loadAllFacilities() {
    console.log('loadAllFacilities called');
    showLoading();
    try {
        const data = await apiCall('facility/all');
        console.log('Facilities data received:', data);
        
        let facilities = [];
        if (data && Array.isArray(data)) {
            facilities = data;
        } else if (data && data.data && Array.isArray(data.data)) {
            facilities = data.data;
        }
        
        if (facilities.length > 0) {
            renderFacilities(facilities);
            updateStats(facilities);
            $('#facilityCount').html(`<i class="bi bi-boxes me-1"></i> ${facilities.length} items`);
            $('#facilityTableCount').text(`${facilities.length} items`);
        } else {
            renderEmptyFacilities('No facilities found');
            $('#facilityCount').html(`<i class="bi bi-boxes me-1"></i> 0 items`);
            $('#facilityTableCount').text('0 items');
            resetStats();
        }
    } catch (error) {
        console.error('Error loading facilities:', error);
        renderEmptyFacilities('Error loading facilities: ' + error.message);
        $('#facilityCount').html(`<i class="bi bi-boxes me-1"></i> 0 items`);
        $('#facilityTableCount').text('0 items');
        showToast('Error loading facilities: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

function updateStats(facilities) {
    let total = 0;
    let functional = 0;
    let damaged = 0;
    
    facilities.forEach(item => {
        total += parseInt(item.quantity) || 0;
        functional += parseInt(item.functional_quantity) || 0;
        damaged += parseInt(item.damaged_quantity) || 0;
    });
    
    $('#totalEquipment').text(total);
    $('#functionalCount').text(functional);
    $('#damagedCount').text(damaged);
}

function resetStats() {
    $('#totalEquipment').text('0');
    $('#functionalCount').text('0');
    $('#damagedCount').text('0');
}

function renderEmptyFacilities(message) {
    const tbody = $('#facilityBody');
    tbody.empty();
    tbody.append(`
        <tr>
            <td colspan="10" class="text-center">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h6>${escapeHtml(message)}</h6>
                    <small>Try adjusting your filters</small>
                </div>
            </td>
        </tr>
    `);
}

function renderFacilities(facilities) {
    console.log('renderFacilities called with:', facilities.length, 'items');
    const tbody = $('#facilityBody');
    tbody.empty();
    
    if (facilities && Array.isArray(facilities) && facilities.length > 0) {
        facilities.forEach((item, index) => {
            const roomNumber = item.room_number || item.room || 'N/A';
            const equipmentType = item.equipment_type || item.type || 'N/A';
            const equipmentName = item.equipment_name || item.name || 'N/A';
            const quantity = item.quantity || item.total_qty || 0;
            const functionalQty = item.functional_quantity || item.functional || 0;
            const damagedQty = item.damaged_quantity || item.damaged || 0;
            const status = item.status || 'unknown';
            const monitoredBy = item.monitored_by || item.user || 'N/A';
            const lastChecked = item.last_checked || item.updated_at || null;
            
            const statusBadge = getStatusBadge(status);
            
            tbody.append(`
                <tr>
                    <td><strong>${escapeHtml(roomNumber)}</strong></td>
                    <td><span class="badge bg-secondary">${escapeHtml(equipmentType)}</span></td>
                    <td>${escapeHtml(equipmentName)}</td>
                    <td>${quantity}</td>
                    <td class="text-success">${functionalQty}</td>
                    <td class="text-danger">${damagedQty}</td>
                    <td><span class="badge ${statusBadge}">${escapeHtml(status)}</span></td>
                    <td>${escapeHtml(monitoredBy)}</td>
                    <td>${lastChecked ? formatDate(lastChecked) : 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editFacility(${item.id || index})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteFacility(${item.id || index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    } else {
        renderEmptyFacilities('No facilities found');
    }
}

function filterByRoom() {
    const room = $('#roomFilter').val().trim();
    if (room) {
        showLoading();
        apiCall(`facility/room/${encodeURIComponent(room)}`).then(data => {
            hideLoading();
            let facilities = [];
            if (data && Array.isArray(data)) {
                facilities = data;
            } else if (data && data.data && Array.isArray(data.data)) {
                facilities = data.data;
            }
            
            if (facilities.length > 0) {
                renderFacilities(facilities);
                updateStats(facilities);
                $('#facilityTableCount').text(`${facilities.length} items`);
                showToast(`Found ${facilities.length} items in room ${room}`, 'success');
            } else {
                renderEmptyFacilities(`No facilities found in room: ${room}`);
                resetStats();
                $('#facilityTableCount').text('0 items');
                showToast(`No facilities found in room ${room}`, 'info');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by room');
            showToast('Error filtering by room', 'danger');
        });
    } else {
        loadAllFacilities();
    }
}

function filterByType() {
    const type = $('#equipmentTypeFilter').val();
    if (type) {
        showLoading();
        apiCall('facility/all').then(data => {
            hideLoading();
            let facilities = [];
            if (data && Array.isArray(data)) {
                facilities = data;
            } else if (data && data.data && Array.isArray(data.data)) {
                facilities = data.data;
            }
            
            const filtered = facilities.filter(item => {
                const itemType = item.equipment_type || item.type || '';
                return itemType.toLowerCase() === type.toLowerCase();
            });
            
            if (filtered.length > 0) {
                renderFacilities(filtered);
                updateStats(filtered);
                $('#facilityTableCount').text(`${filtered.length} items`);
                showToast(`Found ${filtered.length} ${type} items`, 'success');
            } else {
                renderEmptyFacilities(`No ${type} equipment found`);
                resetStats();
                $('#facilityTableCount').text('0 items');
                showToast(`No ${type} equipment found`, 'info');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by type');
            showToast('Error filtering by type', 'danger');
        });
    } else {
        loadAllFacilities();
    }
}

function filterByStatus() {
    const status = $('#statusFilter').val();
    if (status) {
        showLoading();
        apiCall('facility/all').then(data => {
            hideLoading();
            let facilities = [];
            if (data && Array.isArray(data)) {
                facilities = data;
            } else if (data && data.data && Array.isArray(data.data)) {
                facilities = data.data;
            }
            
            const filtered = facilities.filter(item => {
                const itemStatus = item.status || '';
                return itemStatus.toLowerCase() === status.toLowerCase();
            });
            
            if (filtered.length > 0) {
                renderFacilities(filtered);
                updateStats(filtered);
                $('#facilityTableCount').text(`${filtered.length} items`);
                showToast(`Found ${filtered.length} ${status} items`, 'success');
            } else {
                renderEmptyFacilities(`No ${status} facilities found`);
                resetStats();
                $('#facilityTableCount').text('0 items');
                showToast(`No ${status} facilities found`, 'info');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by status');
            showToast('Error filtering by status', 'danger');
        });
    } else {
        loadAllFacilities();
    }
}

// ============================================
// REPORTS FUNCTIONS
// ============================================

async function loadReports() {
    console.log('loadReports called');
    showLoading();
    try {
        const data = await apiCall('facility/reports');
        console.log('Reports data received:', data);
        
        const tbody = $('#reportBody');
        tbody.empty();
        
        let reports = [];
        if (data && Array.isArray(data)) {
            reports = data;
        } else if (data && data.data && Array.isArray(data.data)) {
            reports = data.data;
        }
        
        // Count pending reports (not resolved or replaced)
        const pendingReports = reports.filter(r => {
            const status = (r.status || '').toLowerCase();
            return status !== 'resolved' && status !== 'replaced';
        });
        $('#reportBadgeCount').text(`${pendingReports.length} pending`);
        
        if (reports.length > 0) {
            $('#reportCount').text(reports.length);
            
            reports.forEach((report, index) => {
                const statusBadge = getStatusBadge(report.status);
                const photoHtml = report.damage_image 
                    ? `<img src="/${escapeHtml(report.damage_image)}" class="img-thumbnail" onclick="previewImage('/${escapeHtml(report.damage_image)}', '${escapeHtml(report.broken_equipment || 'Equipment')}')" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-muted small\\'><i class=\\'bi bi-image\\'></i></span>'">`
                    : `<span class="text-muted small"><i class="bi bi-image"></i></span>`;

                tbody.append(`
                    <tr>
                        <td class="text-center">${photoHtml}</td>
                        <td><strong>${escapeHtml(report.room_number || report.room || 'N/A')}</strong></td>
                        <td>${escapeHtml(report.broken_equipment || report.equipment || 'N/A')}</td>
                        <td>${report.quantity_damaged || report.quantity || 1}</td>
                        <td>${escapeHtml(report.description || report.issue || 'N/A')}</td>
                        <td>${escapeHtml(report.reported_by || report.user || 'N/A')}</td>
                        <td>${report.report_date || report.created_at ? formatDate(report.report_date || report.created_at) : 'N/A'}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(report.status || 'reported')}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openUpdateReportModal(${report.id || index}, '${report.status || 'reported'}')">
                                <i class="bi bi-pencil"></i> Update
                            </button>
                        </td>
                    </tr>
                `);
            });
        } else {
            $('#reportCount').text('0');
            tbody.append(`
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h6>No reported issues found</h6>
                            <small>All clear! No issues reported.</small>
                        </div>
                    </td>
                </tr>
            `);
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        $('#reportCount').text('0');
        $('#reportBody').html(`
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <h6 class="mt-2">Error loading reports</h6>
                    <small>${escapeHtml(error.message)}</small>
                </td>
            </tr>
        `);
        showToast('Error loading reports: ' + error.message, 'danger');
    } finally {
        hideLoading();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function previewImage(imagePath, title) {
    $('#previewImageTarget').attr('src', imagePath);
    $('#previewImageTarget').on('error', function() {
        $(this).attr('src', 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="1"%3E%3Crect x="3" y="3" width="18" height="18" rx="2"/%3E%3Ccircle cx="8.5" cy="8.5" r="1.5"/%3E%3Cpath d="M21 15l-5-5L5 21"/%3E%3C/svg%3E');
    });
    $('#imagePreviewModal').modal('show');
}

$('#imagePreviewModal').on('shown.bs.modal', function() {
    const imgSrc = $('#previewImageTarget').attr('src');
    $('#downloadDamageImage').attr('href', imgSrc);
    const filename = imgSrc.split('/').pop() || 'damage-photo.jpg';
    $('#downloadDamageImage').attr('download', filename);
});

function editFacility(id) {
    showToast('Edit functionality coming soon', 'info');
}

async function deleteFacility(id) {
    if (!confirm('Are you sure you want to delete this equipment?')) {
        return;
    }
    
    showLoading();
    
    try {
        const result = await apiCall(`facility/delete/${id}`, 'DELETE');
        if (result && result.success) {
            showToast('Equipment deleted successfully!', 'success');
            loadAllFacilities();
        } else {
            const errorMsg = result?.errors ? result.errors.join(', ') : (result?.error || 'Failed to delete equipment');
            showToast(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Error deleting facility:', error);
        showToast('Failed to delete equipment. Please try again.', 'danger');
    } finally {
        hideLoading();
    }
}

function openUpdateReportModal(id, currentStatus) {
    $('#updateReportId').val(id);
    $('#updateReportStatus').val(currentStatus || 'reported');
    if (currentStatus === 'resolved' || currentStatus === 'replaced') {
        $('#resolvedDateGroup').show();
        if (!$('#updateReportForm input[name="resolved_date"]').val()) {
            $('#updateReportForm input[name="resolved_date"]').val(new Date().toISOString().split('T')[0]);
        }
    } else {
        $('#resolvedDateGroup').hide();
    }
    $('#updateReportModal').modal('show');
}

$('#updateReportStatus').change(function() {
    const status = $(this).val();
    if (status === 'resolved' || status === 'replaced') {
        $('#resolvedDateGroup').show();
        if (!$('#updateReportForm input[name="resolved_date"]').val()) {
            $('#updateReportForm input[name="resolved_date"]').val(new Date().toISOString().split('T')[0]);
        }
    } else {
        $('#resolvedDateGroup').hide();
    }
});

async function submitUpdateReport() {
    const id = $('#updateReportId').val();
    const status = $('#updateReportStatus').val();
    const resolvedDate = $('#updateReportForm input[name="resolved_date"]').val();
    
    if (!id) {
        showToast('Invalid report ID', 'danger');
        return;
    }
    
    const data = { status: status };
    if (resolvedDate && (status === 'resolved' || status === 'replaced')) {
        data.resolved_date = resolvedDate;
    }
    
    showLoading();
    
    try {
        const result = await apiCall(`facility/update/${id}`, 'POST', data);
        if (result && result.success) {
            showToast('Report status updated successfully!', 'success');
            $('#updateReportModal').modal('hide');
            await loadReports();
            await loadAllFacilities();
        } else {
            const errorMsg = result?.errors ? result.errors.join(', ') : (result?.error || 'Failed to update report');
            showToast(errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Error updating report:', error);
        showToast('Failed to update report status. Please try again.', 'danger');
    } finally {
        hideLoading();
    }
}

function getStatusBadge(status) {
    const badges = {
        'complete': 'bg-success',
        'incomplete': 'bg-warning',
        'needs_repair': 'bg-danger',
        'reported': 'bg-warning',
        'in_progress': 'bg-info',
        'resolved': 'bg-success',
        'replaced': 'bg-secondary',
        'pending': 'bg-warning',
        'approved': 'bg-success',
        'rejected': 'bg-danger'
    };
    return badges[status?.toLowerCase()] || 'bg-secondary';
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