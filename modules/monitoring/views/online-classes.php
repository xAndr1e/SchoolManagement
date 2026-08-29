<?php 
$currentPage = 'online-classes';
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
                            <i class="bi bi-camera-video"></i> 
                            Online Classes Monitoring
                        </h2>
                        <p class="text-muted mb-0">Monitor online classes with meeting links and screenshots</p>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6 px-3 py-2" id="onlineCount">
                            <i class="bi bi-camera-reels me-1"></i> 0 classes
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Date Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-lg-5">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar3 me-1"></i> Select Date
                            </label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="onlineDate" value="<?php echo date('Y-m-d'); ?>">
                                <button class="btn btn-primary" onclick="loadOnlineClasses()">
                                    <i class="bi bi-search"></i> Load
                                </button>
                                <button class="btn btn-outline-primary" onclick="setToday()">
                                    <i class="bi bi-calendar-today"></i> Today
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-7">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                <button class="btn btn-sm btn-success" onclick="refreshOnlineClasses()">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="loadAllOnlineClasses()">
                                    <i class="bi bi-list-ul me-1"></i> View All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Online Classes Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-video text-primary me-2"></i>
                        Online Classes
                    </h5>
                    <span class="badge bg-primary" id="onlineTableCount">0 classes</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="onlineTable">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-person me-1"></i> Faculty</th>
                                    <th><i class="bi bi-book me-1"></i> Course/Section</th>
                                    <th><i class="bi bi-hash me-1"></i> Subject</th>
                                    <th><i class="bi bi-door-open me-1"></i> Room</th>
                                    <th><i class="bi bi-people me-1"></i> Students</th>
                                    <th><i class="bi bi-link-45deg me-1"></i> Meeting Link</th>
                                    <th><i class="bi bi-image me-1"></i> Screenshot</th>
                                    <th><i class="bi bi-clock me-1"></i> Time</th>
                                    <th><i class="bi bi-info-circle me-1"></i> Status</th>
                                </tr>
                            </thead>
                            <tbody id="onlineBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="background: rgba(0,0,0,0.95);">
            <div class="modal-header border-0" style="position: absolute; top: 0; left: 0; right: 0; z-index: 10; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
                <h5 class="modal-title text-white" id="imageModalTitle">
                    <i class="bi bi-image me-2"></i> Screenshot
                </h5>
                <div>
                    <button class="btn btn-outline-light btn-sm me-2" onclick="toggleFullscreen()" title="Toggle Fullscreen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <a href="#" id="downloadImage" class="btn btn-outline-light btn-sm me-2" download>
                        <i class="bi bi-download"></i>
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center p-0" style="min-height: 100vh;">
                <img id="modalImage" src="" alt="Screenshot" 
                     class="img-fluid" 
                     style="max-height: 95vh; max-width: 95vw; object-fit: contain; cursor: pointer; transition: transform 0.3s ease;"
                     onclick="toggleFullscreen()">
            </div>
            <div class="modal-footer border-0" style="position: absolute; bottom: 0; left: 0; right: 0; z-index: 10; background: rgba(0,0,0,0.3); backdrop-filter: blur(4px);">
                <div class="text-white w-100 d-flex justify-content-between align-items-center">
                    <span id="imageInfo" style="font-size: 0.85rem;">
                        <i class="bi bi-info-circle me-1"></i> Click image to toggle fullscreen
                    </span>
                    <span class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm" onclick="zoomIn()" title="Zoom In">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="zoomOut()" title="Zoom Out">
                            <i class="bi bi-zoom-out"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="resetZoom()" title="Reset Zoom">
                            <i class="bi bi-arrows-angle-expand"></i>
                        </button>
                    </span>
                </div>
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

.input-group {
    display: flex;
    align-items: stretch;
    width: 100%;
}

.input-group > .form-control {
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

/* Screenshot Thumbnails */
.screenshot-thumb {
    max-width: 120px;
    max-height: 80px;
    border-radius: 6px;
    border: 2px solid var(--border);
    object-fit: cover;
    cursor: pointer;
    transition: all 0.2s ease;
}

.screenshot-thumb:hover {
    transform: scale(1.05);
    border-color: #19006b;
    box-shadow: var(--shadow-md);
}

/* Meeting Link */
.meeting-link-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}

.meeting-link-wrapper .btn {
    font-size: 0.75rem;
    padding: 4px 10px;
}

.meeting-link-wrapper .btn-info {
    background: linear-gradient(135deg, #19006b, #2d1a7a);
    border-color: #19006b;
    color: #fff;
}

.meeting-link-wrapper .btn-info:hover {
    background: linear-gradient(135deg, #0f0045, #19006b);
    border-color: #0f0045;
}

.meeting-link-wrapper .link-text {
    font-size: 0.65rem;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--muted);
}

.meeting-link-wrapper .link-text a {
    color: #19006b;
    text-decoration: none;
}

.meeting-link-wrapper .link-text a:hover {
    text-decoration: underline;
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

/* Modal */
.modal-fullscreen .modal-content {
    border-radius: 0;
    border: none;
}

.modal-fullscreen .modal-body {
    overflow: hidden;
}

#modalImage {
    transition: transform 0.3s ease;
    transform-origin: center center;
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

/* Shortcut Hint */
.shortcut-hint {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.8rem;
    z-index: 1050;
    display: none;
    backdrop-filter: blur(8px);
    pointer-events: none;
    box-shadow: var(--shadow-lg);
}

.shortcut-hint.show {
    display: block;
    animation: fadeInUp 0.3s ease;
}

.shortcut-hint kbd {
    background: rgba(255,255,255,0.15);
    padding: 2px 8px;
    border-radius: 4px;
    margin: 0 2px;
    font-size: 0.75rem;
    border: 1px solid rgba(255,255,255,0.2);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateX(-50%) translateY(10px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
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

/* Responsive */
@media (max-width: 992px) {
    .main-content { padding: 1rem; }
    .page-header { padding: 1.25rem; }
    .page-title { font-size: 1.25rem; }
}

@media (max-width: 768px) {
    .main-content { padding: 0.75rem; }
    .table-responsive { font-size: 0.8rem; }
    .table > :not(caption) > * > * { padding: 0.5rem 0.6rem; }
    .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.7rem; }
    .badge { font-size: 0.65em; padding: 0.25em 0.5em; }
    .screenshot-thumb { max-width: 80px; max-height: 55px; }
    .meeting-link-wrapper .link-text { max-width: 80px; }
    .card-header h5 { font-size: 0.95rem; }
    
    .row.g-3 > .col-md-4,
    .row.g-3 > .col-md-8 {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .col-md-10 { width: 100%; padding: 0.25rem; }
    .modal-fullscreen .modal-body { padding: 0; }
    .screenshot-thumb { max-width: 60px; max-height: 40px; }
}
</style>

<!-- Shortcut Hint -->
<div class="shortcut-hint" id="shortcutHint">
    <i class="bi bi-keyboard me-1"></i> 
    <kbd>Esc</kbd> Close · <kbd>F</kbd> Fullscreen · <kbd>+</kbd>/<kbd>-</kbd> Zoom · <kbd>0</kbd> Reset · <kbd>R</kbd> Refresh
</div>

<script>
// ============================================
// ONLINE CLASSES CONTROLLER
// ============================================

let currentZoom = 1;
let isLoading = false;

$(document).ready(function() {
    loadOnlineClasses();
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
            <span class="toast-message">${message}</span>
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
// DATE FUNCTIONS
// ============================================

function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('onlineDate').value = today;
    loadOnlineClasses();
}

function getStatusBadge(status) {
    const statusMap = {
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'online': 'bg-primary',
        'excused': 'bg-primary',
        'Scheduled': 'bg-primary',
        'Completed': 'bg-success',
        'Cancelled': 'bg-danger',
        'in_progress': 'bg-info',
        'resolved': 'bg-success',
        'pending': 'bg-warning'
    };
    return statusMap[status] || 'bg-secondary';
}

function formatTime(datetime) {
    if (!datetime) return 'N/A';
    try {
        const date = new Date(datetime);
        if (isNaN(date.getTime())) return datetime;
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (e) {
        return datetime;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// API URL RESOLVER
// ============================================

function getMonitoringApiUrl(endpoint) {
    const pathname = window.location.pathname || '/';
    
    const patterns = [
        '/modules/monitoring/public',
        '/Monitoring2/public',
        '/modules/monitoring',
        '/Monitoring2'
    ];
    
    let basePath = '';
    for (const pattern of patterns) {
        if (pathname.includes(pattern)) {
            const index = pathname.indexOf(pattern);
            basePath = pathname.substring(0, index + pattern.length);
            break;
        }
    }
    
    if (!basePath) {
        const apiIndex = pathname.indexOf('/api/');
        if (apiIndex !== -1) {
            basePath = pathname.substring(0, apiIndex);
        }
    }
    
    let cleanEndpoint = endpoint || '';
    if (cleanEndpoint.startsWith('/')) {
        cleanEndpoint = cleanEndpoint.substring(1);
    }
    if (!cleanEndpoint.startsWith('api/')) {
        cleanEndpoint = 'api/' + cleanEndpoint;
    }
    
    const fullUrl = basePath ? `${basePath}/${cleanEndpoint}` : `/${cleanEndpoint}`;
    return fullUrl;
}

// ============================================
// LOAD ONLINE CLASSES
// ============================================

function loadOnlineClasses() {
    if (isLoading) return;
    
    const date = $('#onlineDate').val();
    if (!date) {
        showToast('Please select a date', 'warning');
        return;
    }
    
    isLoading = true;
    showToast('Loading online classes...', 'info');
    
    const apiUrl = getMonitoringApiUrl('/api/attendance/online') + '?date=' + encodeURIComponent(date) + '&_t=' + Date.now();
    
    $.ajax({
        url: apiUrl,
        type: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            isLoading = false;
            
            const tbody = $('#onlineBody');
            tbody.empty();
            
            let data = [];
            if (response && typeof response === 'object') {
                if (response.data && Array.isArray(response.data)) {
                    data = response.data;
                } else if (response.error) {
                    showToast('Error: ' + (response.message || 'Unknown error'), 'danger');
                    tbody.append(`
                        <tr>
                            <td colspan="9" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">${escapeHtml(response.message || 'Error loading data')}</h6>
                                <small>Please try again or contact support</small>
                            </td>
                        </tr>
                    `);
                    return;
                } else if (Array.isArray(response)) {
                    data = response;
                } else if (typeof response === 'object' && !Array.isArray(response)) {
                    data = [response];
                }
            }
            
            if (!Array.isArray(data)) {
                data = [];
            }
            
            const onlineClasses = data.filter(item => 
                item.is_online == 1 || 
                item.status === 'online' ||
                (item.meeting_link !== null && item.meeting_link !== '') ||
                (item.meeting_screenshot !== null && item.meeting_screenshot !== '')
            );
            
            const count = onlineClasses.length;
            $('#onlineCount').html(`<i class="bi bi-camera-reels me-1"></i> ${count} classes`);
            $('#onlineTableCount').text(`${count} classes`);
            
            if (count === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="empty-state">
                                <i class="bi bi-camera-reels"></i>
                                <h6>No online classes found for ${date}</h6>
                                <small>Try selecting a different date or check your data</small>
                            </div>
                        </td>
                    </tr>
                `);
                showToast('No online classes found for this date', 'info');
                return;
            }
            
            showToast(`Found ${count} online classes`, 'success');
            
            onlineClasses.forEach(function(item) {
                const statusBadge = getStatusBadge(item.status);
                
                const studentCount = item.student_count || 0;
                const studentBadge = studentCount > 0 
                    ? `<span class="badge bg-info">${studentCount}</span>`
                    : `<span class="badge bg-secondary">0</span>`;
                
                let linkHtml = '<span class="text-muted">No link</span>';
                if (item.meeting_link) {
                    let displayLink = item.meeting_link;
                    if (displayLink.length > 35) {
                        displayLink = displayLink.substring(0, 35) + '...';
                    }
                    linkHtml = `
                        <div class="meeting-link-wrapper">
                            <a href="${item.meeting_link}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-link-45deg me-1"></i> Join Meeting
                            </a>
                            <span class="link-text" title="${item.meeting_link}">
                                <a href="${item.meeting_link}" target="_blank">${displayLink}</a>
                            </span>
                        </div>
                    `;
                }
                
                let screenshotHtml = '<span class="text-muted">No screenshot</span>';
                if (item.meeting_screenshot) {
                    let imgSrc = item.meeting_screenshot;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imgSrc += (imgSrc.includes('?') ? '&' : '?') + '_t=' + Date.now();
                    
                    screenshotHtml = `
                        <img src="${imgSrc}" 
                             alt="Screenshot" 
                             class="screenshot-thumb" 
                             onclick="viewImage('${imgSrc}', '${escapeHtml(item.faculty_name || 'Faculty')} - ${escapeHtml(item.subject_code || 'Subject')}')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'><i class=\\'bi bi-exclamation-triangle\\'></i> Not found</span>';">
                    `;
                }
                
                const facultyName = item.faculty_name || item.faculty || 'Unknown';
                const courseSection = item.course_section || item.course || 'N/A';
                const subjectCode = item.subject_code || item.subject || 'N/A';
                const room = item.room || 'N/A';
                const timeDisplay = formatTime(item.check_time || item.created_at);
                
                tbody.append(`
                    <tr>
                        <td><strong>${escapeHtml(facultyName)}</strong></td>
                        <td>${escapeHtml(courseSection)}</td>
                        <td><span class="badge bg-secondary">${escapeHtml(subjectCode)}</span></td>
                        <td>${escapeHtml(room)}</td>
                        <td>${studentBadge}</td>
                        <td>${linkHtml}</td>
                        <td>${screenshotHtml}</td>
                        <td><small>${timeDisplay}</small></td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(item.status || 'N/A')}</span></td>
                    </tr>
                `);
            });
        },
        error: function(xhr, status, error) {
            isLoading = false;
            
            const tbody = $('#onlineBody');
            tbody.empty();
            
            let errorMessage = 'Error loading data. ';
            
            if (xhr.status === 404) {
                errorMessage += 'API endpoint not found.';
            } else if (xhr.status === 0) {
                errorMessage += 'Network error. Please check your connection.';
            } else if (xhr.status >= 500) {
                errorMessage += 'Server error. Please try again later.';
            } else if (xhr.responseText) {
                if (xhr.responseText.trim().startsWith('<!DOCTYPE') || xhr.responseText.trim().startsWith('<')) {
                    errorMessage += 'Server returned HTML instead of JSON. There may be a PHP error.';
                } else {
                    errorMessage += xhr.responseText.substring(0, 200);
                }
            } else {
                errorMessage += error || 'Unknown error occurred.';
            }
            
            tbody.append(`
                <tr>
                    <td colspan="9" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">${escapeHtml(errorMessage)}</h6>
                        <small class="text-muted">Status: ${xhr.status} - ${status}</small>
                        <br>
                        <button class="btn btn-sm btn-primary mt-2" onclick="loadOnlineClasses()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                        </button>
                    </td>
                </tr>
            `);
            
            showToast(errorMessage.substring(0, 100), 'danger');
        }
    });
}

// ============================================
// LOAD ALL ONLINE CLASSES
// ============================================

function loadAllOnlineClasses() {
    showToast('Loading all online classes...', 'info');
    
    const dateInput = document.getElementById('onlineDate');
    dateInput.value = '';
    
    const apiUrl = getMonitoringApiUrl('/api/attendance/online') + '?_t=' + Date.now();
    
    $.ajax({
        url: apiUrl,
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            $('#onlineCount').html(`<i class="bi bi-camera-reels me-1"></i> All classes`);
            
            let data = [];
            if (response && typeof response === 'object') {
                if (response.data && Array.isArray(response.data)) {
                    data = response.data;
                } else if (response.online_classes && Array.isArray(response.online_classes)) {
                    data = response.online_classes;
                } else if (Array.isArray(response)) {
                    data = response;
                } else if (typeof response === 'object' && !Array.isArray(response)) {
                    data = [response];
                }
            }
            
            if (!Array.isArray(data)) data = [];
            
            const onlineClasses = data.filter(item => 
                item.is_online == 1 || 
                item.status === 'online' ||
                (item.meeting_link !== null && item.meeting_link !== '')
            );
            
            $('#onlineTableCount').text(`${onlineClasses.length} classes`);
            showToast(`Loaded ${onlineClasses.length} online classes`, 'success');
            
            displayOnlineClasses(onlineClasses);
        },
        error: function(xhr, status, error) {
            showToast('Error loading all classes: ' + error, 'danger');
        }
    });
}

// ============================================
// DISPLAY ONLINE CLASSES
// ============================================

function displayOnlineClasses(classes) {
    const tbody = $('#onlineBody');
    tbody.empty();
    
    if (!classes || classes.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center">
                    <div class="empty-state">
                        <i class="bi bi-camera-reels"></i>
                        <h6>No online classes found</h6>
                        <small>Try adjusting your filters</small>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    classes.forEach(function(item) {
        const statusBadge = getStatusBadge(item.status);
        const studentCount = item.student_count || 0;
        const studentBadge = studentCount > 0 
            ? `<span class="badge bg-info">${studentCount}</span>`
            : `<span class="badge bg-secondary">0</span>`;
        
        let linkHtml = '<span class="text-muted">No link</span>';
        if (item.meeting_link) {
            let displayLink = item.meeting_link;
            if (displayLink.length > 35) {
                displayLink = displayLink.substring(0, 35) + '...';
            }
            linkHtml = `
                <div class="meeting-link-wrapper">
                    <a href="${item.meeting_link}" target="_blank" class="btn btn-sm btn-info">
                        <i class="bi bi-link-45deg me-1"></i> Join Meeting
                    </a>
                    <span class="link-text" title="${item.meeting_link}">
                        <a href="${item.meeting_link}" target="_blank">${displayLink}</a>
                    </span>
                </div>
            `;
        }
        
        let screenshotHtml = '<span class="text-muted">No screenshot</span>';
        if (item.meeting_screenshot) {
            let imgSrc = item.meeting_screenshot;
            if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                imgSrc = '/' + imgSrc;
            }
            imgSrc += (imgSrc.includes('?') ? '&' : '?') + '_t=' + Date.now();
            
            screenshotHtml = `
                <img src="${imgSrc}" 
                     alt="Screenshot" 
                     class="screenshot-thumb" 
                     onclick="viewImage('${imgSrc}', '${escapeHtml(item.faculty_name || 'Faculty')} - ${escapeHtml(item.subject_code || 'Subject')}')"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'><i class=\\'bi bi-exclamation-triangle\\'></i> Not found</span>';">
            `;
        }
        
        const facultyName = item.faculty_name || item.faculty || 'Unknown';
        const courseSection = item.course_section || item.course || 'N/A';
        const subjectCode = item.subject_code || item.subject || 'N/A';
        const room = item.room || 'N/A';
        const timeDisplay = formatTime(item.check_time || item.created_at);
        
        tbody.append(`
            <tr>
                <td><strong>${escapeHtml(facultyName)}</strong></td>
                <td>${escapeHtml(courseSection)}</td>
                <td><span class="badge bg-secondary">${escapeHtml(subjectCode)}</span></td>
                <td>${escapeHtml(room)}</td>
                <td>${studentBadge}</td>
                <td>${linkHtml}</td>
                <td>${screenshotHtml}</td>
                <td><small>${timeDisplay}</small></td>
                <td><span class="badge ${statusBadge}">${escapeHtml(item.status || 'N/A')}</span></td>
            </tr>
        `);
    });
}

// ============================================
// REFRESH
// ============================================

function refreshOnlineClasses() {
    loadOnlineClasses();
}

// ============================================
// FULLSCREEN IMAGE VIEWER
// ============================================

function viewImage(imageUrl, title) {
    if (!imageUrl) {
        showToast('No image to display', 'warning');
        return;
    }
    
    currentZoom = 1;
    
    const img = document.getElementById('modalImage');
    img.src = imageUrl;
    img.style.transform = 'scale(1)';
    img.onerror = function() {
        showToast('Failed to load image: ' + imageUrl, 'danger');
    };
    img.onload = function() {
        console.log('Image loaded successfully');
    };
    
    document.getElementById('imageModalTitle').textContent = title || 'Screenshot';
    document.getElementById('imageInfo').innerHTML = '<i class="bi bi-info-circle me-1"></i> Click image to toggle fullscreen';
    
    const downloadEl = document.getElementById('downloadImage');
    const filename = imageUrl.split('/').pop() || 'screenshot.jpg';
    downloadEl.href = imageUrl;
    downloadEl.download = filename;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    setTimeout(() => {
        const hint = document.getElementById('shortcutHint');
        hint.classList.add('show');
        setTimeout(() => hint.classList.remove('show'), 4000);
    }, 500);
}

// ============================================
// MODAL CONTROLS
// ============================================

function toggleFullscreen() {
    const modal = document.getElementById('imageModal');
    if (!document.fullscreenElement) {
        modal.requestFullscreen().catch(err => {
            zoomIn();
        });
    } else {
        document.exitFullscreen();
    }
}

function zoomIn() {
    currentZoom = Math.min(currentZoom + 0.25, 3);
    applyZoom();
}

function zoomOut() {
    currentZoom = Math.max(currentZoom - 0.25, 0.5);
    applyZoom();
}

function resetZoom() {
    currentZoom = 1;
    applyZoom();
}

function applyZoom() {
    const img = document.getElementById('modalImage');
    if (img) {
        img.style.transform = 'scale(' + currentZoom + ')';
    }
}

function closeModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
    if (modal) {
        modal.hide();
    }
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

$(document).keydown(function(e) {
    if ($(e.target).is('input, textarea, select')) return;
    
    if (e.key.toLowerCase() === 'r') {
        refreshOnlineClasses();
        e.preventDefault();
    }
    
    if (!$('#imageModal').hasClass('show')) return;
    
    switch(e.key) {
        case 'Escape':
            closeModal();
            break;
        case 'f':
        case 'F':
            toggleFullscreen();
            e.preventDefault();
            break;
        case '+':
        case '=':
            zoomIn();
            e.preventDefault();
            break;
        case '-':
            zoomOut();
            e.preventDefault();
            break;
        case '0':
            resetZoom();
            e.preventDefault();
            break;
    }
});

$('#imageModal').on('hidden.bs.modal', function() {
    if (document.fullscreenElement) {
        document.exitFullscreen();
    }
    document.getElementById('shortcutHint').classList.remove('show');
});
</script>

<?php include 'layouts/footer.php'; ?>