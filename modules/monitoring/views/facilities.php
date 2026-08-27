<?php 
$currentPage = 'facilities';
include 'layouts/header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-building"></i> Facilities Monitoring</h2>
                <p class="text-muted">Monitor and manage facility equipment</p>
            </div>
            
            <!-- Action Buttons -->
             
            
            <!-- Room Selector -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="roomFilter" class="form-label">Filter by Room</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="roomFilter" placeholder="Enter room number">
                        <button class="btn btn-primary" onclick="filterByRoom()">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button class="btn btn-secondary" onclick="loadAllFacilities()">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Equipment Type</label>
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
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                        <option value="">All Status</option>
                        <option value="complete">Complete</option>
                        <option value="incomplete">Incomplete</option>
                        <option value="needs_repair">Needs Repair</option>
                    </select>
                </div>
            </div>
            
           
            
            
            <!-- Reports Logs Section -->
            <div class="table-responsive mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="bi bi-exclamation-triangle"></i> Reported Issues Log</h5>
                    <span class="badge bg-warning" id="reportCount">0 reports</span>
                </div>
                <table class="table table-bordered table-hover align-middle" id="reportTable">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Room</th>
                            <th>Equipment</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Reported By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image"></i> Damage Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="previewImageTarget" src="" class="img-fluid rounded-bottom" alt="Damage Photo Preview" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- Update Report Status Modal -->
<div class="modal fade" id="updateReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Update Report Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateReportForm">
                    <input type="hidden" id="updateReportId" name="report_id">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="updateReportStatus" required>
                            <option value="reported">Reported</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="replaced">Replaced</option>
                        </select>
                    </div>
                    <div class="mb-3" id="resolvedDateGroup" style="display:none;">
                        <label class="form-label">Resolved Date</label>
                        <input type="date" class="form-control" name="resolved_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitUpdateReport()">
                    <i class="bi bi-check-lg"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Document ready - loading facilities...');
    loadAllFacilities();
    loadReports();
});

// ============================================
// API CALL FUNCTION - IMPROVED
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

        // Try both with and without /api/ prefix
        let url = endpoint;
        if (!endpoint.startsWith('http') && !endpoint.startsWith('/api/')) {
            url = '/api/' + endpoint;
        }

        const response = await fetch(url, options);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            if (response.status === 401 || response.status === 302) {
                showToast('Session expired. Please login again.', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                return null;
            }
            throw new Error('HTTP error ' + response.status);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            // Try to parse as JSON anyway (some servers might not set correct content-type)
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
        showToast('Error: ' + error.message, 'error');
        return null;
    }
}

// ============================================
// FACILITIES FUNCTIONS - IMPROVED
// ============================================
async function loadAllFacilities() {
    console.log('loadAllFacilities called');
    showLoading();
    try {
        const data = await apiCall('facility/all');
        console.log('Facilities data received:', data);
        
        if (data && Array.isArray(data) && data.length > 0) {
            renderFacilities(data);
            $('#facilityCount').text(`${data.length} items`);
        } else if (data && Array.isArray(data) && data.length === 0) {
            // Empty array - show empty state
            renderEmptyFacilities('No facilities found');
            $('#facilityCount').text('0 items');
        } else if (data && data.data && Array.isArray(data.data)) {
            // Handle nested data structure
            if (data.data.length > 0) {
                renderFacilities(data.data);
                $('#facilityCount').text(`${data.data.length} items`);
            } else {
                renderEmptyFacilities('No facilities found');
                $('#facilityCount').text('0 items');
            }
        } else {
            // Invalid data structure
            console.warn('Invalid data structure received:', data);
            renderEmptyFacilities('Invalid data format received');
            $('#facilityCount').text('0 items');
        }
    } catch (error) {
        console.error('Error loading facilities:', error);
        renderEmptyFacilities('Error loading facilities: ' + error.message);
        $('#facilityCount').text('0 items');
        showToast('Error loading facilities: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

function renderEmptyFacilities(message) {
    const tbody = $('#facilityBody');
    tbody.empty();
    tbody.append(`
        <tr>
            <td colspan="10" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                ${escapeHtml(message)}
            </td>
        </tr>
    `);
}

function renderFacilities(facilities) {
    console.log('renderFacilities called with:', facilities);
    const tbody = $('#facilityBody');
    tbody.empty();
    
    if (facilities && Array.isArray(facilities) && facilities.length > 0) {
        facilities.forEach((item, index) => {
            // Validate required fields
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
            if (data && Array.isArray(data)) {
                renderFacilities(data);
                $('#facilityCount').text(`${data.length} items`);
            } else if (data && data.data && Array.isArray(data.data)) {
                renderFacilities(data.data);
                $('#facilityCount').text(`${data.data.length} items`);
            } else {
                renderEmptyFacilities(`No facilities found in room: ${room}`);
                $('#facilityCount').text('0 items');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by room');
        });
    } else {
        loadAllFacilities();
    }
}

function filterByType() {
    const type = $('#equipmentTypeFilter').val();
    if (type) {
        showLoading();
        // Use the stored full data or fetch all and filter
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
                $('#facilityCount').text(`${filtered.length} items`);
            } else {
                renderEmptyFacilities(`No ${type} equipment found`);
                $('#facilityCount').text('0 items');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by type');
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
                $('#facilityCount').text(`${filtered.length} items`);
            } else {
                renderEmptyFacilities(`No ${status} facilities found`);
                $('#facilityCount').text('0 items');
            }
        }).catch(() => {
            hideLoading();
            renderEmptyFacilities('Error filtering by status');
        });
    } else {
        loadAllFacilities();
    }
}

// ============================================
// REPORTS FUNCTIONS - IMPROVED
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
        
        if (reports.length > 0) {
            $('#reportCount').text(`${reports.length} reports`);
            reports.forEach((report, index) => {
                const statusBadge = getStatusBadge(report.status);
                const photoHtml = report.damage_image 
                    ? `<img src="/${escapeHtml(report.damage_image)}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" onclick="previewImage('/${escapeHtml(report.damage_image)}')" title="Click to view full photo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'text-muted small\'><i class=\'bi bi-image\'></i> No photo</span>'">`
                    : `<span class="text-muted small"><i class="bi bi-image"></i> No photo</span>`;

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
            $('#reportCount').text('0 reports');
            tbody.append(`
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No reported issues found
                    </td>
                </tr>
            `);
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        $('#reportCount').text('0 reports');
        $('#reportBody').html(`
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    Error loading reports: ${escapeHtml(error.message)}
                </td>
            </tr>
        `);
        showToast('Error loading reports: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// ============================================
// UTILITY FUNCTIONS - IMPROVED
// ============================================
function previewImage(imagePath) {
    $('#previewImageTarget').attr('src', imagePath);
    $('#imagePreviewModal').modal('show');
}

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
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        console.error('Error deleting facility:', error);
        showToast('Failed to delete equipment. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

function openUpdateReportModal(id, currentStatus) {
    $('#updateReportId').val(id);
    $('#updateReportStatus').val(currentStatus || 'reported');
    if (currentStatus === 'resolved' || currentStatus === 'replaced') {
        $('#resolvedDateGroup').show();
        // Set resolved date to today if not set
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
        showToast('Invalid report ID', 'error');
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
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        console.error('Error updating report:', error);
        showToast('Failed to update report status. Please try again.', 'error');
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

function showToast(message, type = 'info') {
    // Check if bootstrap toast is available
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }
    
    // Create a temporary toast using Bootstrap if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }
        
        const toastEl = document.createElement('div');
        const bgClass = type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-info';
        toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.querySelector('.toast-container').appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        return;
    }
    
    // Fallback: use alert
    console.error('Toast:', message, type);
    // Only use alert for errors
    if (type === 'error') {
        alert('⚠️ ' + message);
    }
}

function showLoading() {
    $('#loadingOverlay').fadeIn(200);
}

function hideLoading() {
    $('#loadingOverlay').fadeOut(200);
}
</script>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>