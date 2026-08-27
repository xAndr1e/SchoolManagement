<?php 
$currentPage = 'visitors';
include 'layouts/header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-people"></i> Visitor Log</h2>
                <p class="text-muted">Monitor and manage visitor entries</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <button class="btn btn-success" onclick="showEntryModal()">
                        <i class="bi bi-person-plus"></i> Log Entry
                    </button>
                    <button class="btn btn-info" onclick="loadVisitorsInside()">
                        <i class="bi bi-door-open"></i> Visitors Inside
                    </button>
                    <button class="btn btn-primary" onclick="loadTodayVisitors()">
                        <i class="bi bi-calendar-day"></i> Today's Visitors
                    </button>
                    <button class="btn btn-secondary" onclick="loadVisitorHistory()">
                        <i class="bi bi-clock-history"></i> History
                    </button>
                    <button class="btn btn-warning" onclick="loadPendingApprovals()">
                        <i class="bi bi-clock-history"></i> Pending Approvals
                    </button>
                </div>
            </div>
            
            <!-- Date Filter -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="historyStart" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" id="historyEnd" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary form-control" onclick="loadVisitorHistory()">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
            
            <!-- Visitor Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="totalVisitors">0</div>
                        <div class="stat-label">Total Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-success" id="insideVisitors">0</div>
                        <div class="stat-label">Currently Active</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-warning" id="pendingCount">0</div>
                        <div class="stat-label">Pending Approvals</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-info" id="vehiclesCount">0</div>
                        <div class="stat-label">Vehicles Registered</div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Approvals Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pending ID Approvals</h5>
                            <span class="badge bg-dark" id="pendingBadge">0</span>
                        </div>
                        <div class="card-body">
                            <div id="pendingApprovalsList">
                                <div class="text-center text-muted">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Visitors Table -->
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="bi bi-list-ul"></i> Visitor Records</h5>
                    <span class="badge bg-primary" id="visitorCount">0 records</span>
                </div>
                <table class="table table-bordered table-hover" id="visitorTable">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="visitorBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Log Entry Modal -->
<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Log Visitor Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="entryForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Visitor Name</label>
                                <input type="text" class="form-control" name="visitor_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" placeholder="09XX-XXX-XXXX" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ID Type</label>
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
                                <label class="form-label">ID Number</label>
                                <input type="text" class="form-control" name="id_number" placeholder="ID Number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vehicle Type</label>
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
                                <label class="form-label">Vehicle Plate Number</label>
                                <input type="text" class="form-control" name="vehicle_plate" placeholder="Plate Number">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose of Visit</label>
                        <textarea class="form-control" name="purpose_of_visit" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Person to Visit</label>
                                <input type="text" class="form-control" name="person_to_visit" placeholder="Who are you visiting?">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" placeholder="Department">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monitored By</label>
                        <input type="text" class="form-control" name="monitored_by" value="Administrator">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitEntry()">
                    <i class="bi bi-person-plus"></i> Log Entry
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
                <h5 class="modal-title"><i class="bi bi-image"></i> ID Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="idImagePreview" src="" alt="ID Image" class="img-fluid" style="max-height: 80vh; max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="downloadIdImage" class="btn btn-primary" download>
                    <i class="bi bi-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// PAGE INITIALIZATION
// ============================================
$(document).ready(function() {
    loadTodayVisitors();
    loadPendingApprovals();
    loadDashboardStats();
});

// ============================================
// LOAD FUNCTIONS
// ============================================
async function loadTodayVisitors() {
    try {
        const data = await apiCall('visitor/today');
        if (data) {
            renderVisitors(data);
            updateStats(data);
        } else {
            renderVisitors([]);
            updateStats([]);
        }
    } catch (error) {
        console.error('Error loading visitors:', error);
        renderVisitors([]);
        updateStats([]);
    }
}

async function loadVisitorsInside() {
    try {
        const data = await apiCall('visitor/inside');
        if (data) {
            renderVisitors(data);
            showToast(`Found ${data.length} active records`, 'info');
        } else {
            renderVisitors([]);
            showToast('No active records found', 'info');
        }
    } catch (error) {
        console.error('Error loading visitors inside:', error);
        showToast('Error loading active visitors', 'error');
    }
}

async function loadVisitorHistory() {
    const start = $('#historyStart').val();
    const end = $('#historyEnd').val();
    
    if (!start || !end) {
        showToast('Please select both start and end dates', 'warning');
        return;
    }
    
    try {
        const data = await apiCall(`visitor/history?start=${start}&end=${end}`);
        if (data) {
            renderVisitors(data);
            updateStats(data);
        } else {
            renderVisitors([]);
            updateStats([]);
        }
    } catch (error) {
        console.error('Error loading visitor history:', error);
        showToast('Error loading visitor history', 'error');
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
            badge.style.display = 'inline';
            $('#pendingCount').text(data.length);
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Time In</th>
                                <th>Purpose</th>
                                <th>ID Image</th>
                                <th>Action</th>
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
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #dee2e6;"
                             onclick="viewID('${imgSrc}', '${escapeHtml(visitor.visitor_name)} ID')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid image</span>';">
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
            badge.style.display = 'inline';
            $('#pendingCount').text('0');
            
            container.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle" style="font-size: 24px;"></i>
                    <p class="mt-2">No pending approvals</p>
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
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #dee2e6;"
                         onclick="viewID('${imgSrc}', '${escapeHtml(visitor.visitor_name)} ID')"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Not found</span>';">
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
                    <td><span class="badge ${statusBadge}">${escapeHtml(visitor.status)}</span></td>
                    <td>
                        <span class="text-muted">Logged In</span>
                    </td>
                </tr>
            `);
        });
    } else {
        $('#visitorCount').text('0 records');
        tbody.append('<tr><td colspan="9" class="text-center">No visitors found</td></tr>');
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
    const vehicles = visitors.filter(v => v.vehicle_plate && v.vehicle_plate !== 'none').length;
    
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
        } else {
            const errorMsg = result?.errors ? result.errors.join(', ') : (result?.error || 'Failed to log entry');
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        console.error('Error logging entry:', error);
        showToast('Failed to log visitor entry. Please try again.', 'error');
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
    
    document.getElementById('viewIDModal').querySelector('.modal-title').textContent = title || 'ID Attachment';
    
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
            if (typeof loadDashboardStats === 'function') {
                loadDashboardStats();
            }
        } else {
            const errorMsg = result?.error || result?.message || 'Failed to approve ID';
            showToast('❌ Failed to approve ID: ' + errorMsg, 'error');
        }
    } catch (error) {
        console.error('❌ Error approving ID:', error);
        showToast('❌ Failed to approve ID: ' + error.message, 'error');
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
        } else {
            showToast('Failed to reject registration', 'error');
        }
    } catch (error) {
        console.error('Error rejecting ID:', error);
        showToast('Failed to reject registration', 'error');
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
        'pending_approval': 'bg-warning text-dark',
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
</script>

<?php include 'layouts/footer.php'; ?>