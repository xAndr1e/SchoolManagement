<?php 
$currentPage = 'online-classes';
include 'layouts/header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-camera-video"></i> Online Classes Monitoring</h2>
                <p class="text-muted">Monitor online classes with meeting links and screenshots</p>
            </div>
            
            <!-- Date Filter -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Select Date</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="onlineDate" value="<?php echo date('Y-m-d'); ?>">
                        <button class="btn btn-primary" onclick="loadOnlineClasses()">
                            <i class="bi bi-search"></i> Load
                        </button>
                        <button class="btn btn-outline-secondary" onclick="document.getElementById('onlineDate').value = '<?php echo date('Y-m-d'); ?>'; loadOnlineClasses();">
                            <i class="bi bi-calendar-today"></i> Today
                        </button>
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="badge bg-info fs-6" id="onlineCount">0 classes</span>
                    <button class="btn btn-sm btn-success ms-2" onclick="refreshOnlineClasses()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            
            <!-- Online Classes Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="onlineTable">
                    <thead class="table-light">
                        <tr>
                            <th>Faculty</th>
                            <th>Course/Section</th>
                            <th>Subject</th>
                            <th>Room</th>
                            <th>Students</th>
                            <th>Meeting Link</th>
                            <th>Screenshot</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="onlineBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="background: rgba(0,0,0,0.95);">
            <div class="modal-header border-0" style="position: absolute; top: 0; left: 0; right: 0; z-index: 10; background: transparent;">
                <h5 class="modal-title text-white" id="imageModalTitle"><i class="bi bi-image"></i> Screenshot</h5>
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
                     style="max-height: 95vh; max-width: 95vw; object-fit: contain; cursor: pointer;"
                     onclick="toggleFullscreen()"
                     ondblclick="closeModal()">
            </div>
            <div class="modal-footer border-0" style="position: absolute; bottom: 0; left: 0; right: 0; z-index: 10; background: rgba(0,0,0,0.3);">
                <div class="text-white w-100 d-flex justify-content-between align-items-center">
                    <span id="imageInfo" style="font-size: 0.85rem;">Click image to toggle fullscreen</span>
                    <span>
                        <button class="btn btn-outline-light btn-sm" onclick="zoomIn()">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="zoomOut()">
                            <i class="bi bi-zoom-out"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="resetZoom()">
                            <i class="bi bi-arrows-angle-expand"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    
    .screenshot-thumb {
        max-width: 120px;
        max-height: 80px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        object-fit: cover;
        cursor: pointer;
        transition: opacity 0.3s;
    }
    
    .screenshot-thumb:hover {
        opacity: 0.7;
    }
    
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
    
    .meeting-link-wrapper .link-text {
        font-size: 0.65rem;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #6c757d;
    }
    
    .meeting-link-wrapper .link-text a {
        color: #0d6efd;
        text-decoration: none;
    }
    
    .meeting-link-wrapper .link-text a:hover {
        text-decoration: underline;
    }
    
    .shortcut-hint {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.75rem;
        z-index: 1050;
        display: none;
        backdrop-filter: blur(4px);
        pointer-events: none;
    }
    
    .shortcut-hint.show {
        display: block;
        animation: fadeInUp 0.3s ease;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateX(-50%) translateY(10px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
</style>

<div class="shortcut-hint" id="shortcutHint">
    <i class="bi bi-keyboard"></i> 
    <kbd>Esc</kbd> Close · <kbd>F</kbd> Fullscreen · <kbd>+</kbd>/<kbd>-</kbd> Zoom · <kbd>0</kbd> Reset
</div>

<script>
let currentZoom = 1;

$(document).ready(function() {
    loadOnlineClasses();
});

function loadOnlineClasses() {
    const date = $('#onlineDate').val();
    if (!date) {
        alert('Please select a date');
        return;
    }
    
    $.ajax({
        url: '/api/attendance/online?date=' + date,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Data received:', data);
            
            const tbody = $('#onlineBody');
            tbody.empty();
            
            if (data && data.length > 0) {
                const onlineClasses = data.filter(item => item.is_online == 1 || item.status === 'online');
                $('#onlineCount').text(onlineClasses.length + ' classes');
                
                if (onlineClasses.length === 0) {
                    tbody.append('<tr><td colspan="9" class="text-center">No online classes found</td></tr>');
                    return;
                }
                
                onlineClasses.forEach(function(item) {
                    const statusBadge = {
                        'present': 'bg-success',
                        'absent': 'bg-danger',
                        'late': 'bg-warning',
                        'online': 'bg-info',
                        'excused': 'bg-primary'
                    }[item.status] || 'bg-secondary';
                    
                    // ⭐ NEW: Student count
                    const studentCount = item.student_count || 0;
                    const studentBadge = studentCount > 0 
                        ? `<span class="badge bg-info">${studentCount}</span>`
                        : `<span class="badge bg-secondary">0</span>`;
                    
                    // Meeting Link
                    let linkHtml = '<span class="text-muted">No link</span>';
                    if (item.meeting_link) {
                        let displayLink = item.meeting_link;
                        if (displayLink.length > 35) {
                            displayLink = displayLink.substring(0, 35) + '...';
                        }
                        linkHtml = `
                            <div class="meeting-link-wrapper">
                                <a href="${item.meeting_link}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-link-45deg"></i> Join Meeting
                                </a>
                                <span class="link-text" title="${item.meeting_link}">
                                    <a href="${item.meeting_link}" target="_blank">${displayLink}</a>
                                </span>
                            </div>
                        `;
                    }
                    
                    // Screenshot
                    let screenshotHtml = '<span class="text-muted">No screenshot</span>';
                    if (item.meeting_screenshot) {
                        var imgSrc = '/' + item.meeting_screenshot;
                        
                        screenshotHtml = `
                            <img src="${imgSrc}" 
                                 alt="Screenshot" 
                                 class="screenshot-thumb" 
                                 onclick="viewImage('${imgSrc}', '${item.faculty_name} - ${item.subject_code}')"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Not found</span>';">
                        `;
                    }
                    
                    tbody.append(`
                        <tr>
                            <td><strong>${item.faculty_name}</strong></td>
                            <td>${item.course_section}</td>
                            <td><span class="badge bg-secondary">${item.subject_code}</span></td>
                            <td>${item.room}</td>
                            <td>${studentBadge}</td>
                            <td>${linkHtml}</td>
                            <td>${screenshotHtml}</td>
                            <td>${formatDate(item.check_time)}</td>
                            <td><span class="badge ${statusBadge}">${item.status}</span></td>
                        </tr>
                    `);
                });
            } else {
                $('#onlineCount').text('0 classes');
                tbody.append('<tr><td colspan="9" class="text-center">No online classes found</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Error loading data: ' + error + '\nStatus: ' + xhr.status);
            $('#onlineBody').empty().append('<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>');
        }
    });
}

// ============================================
// FULLSCREEN IMAGE VIEWER
// ============================================
function viewImage(imageUrl, title) {
    console.log('Opening image:', imageUrl);
    
    if (!imageUrl) {
        alert('No image to display');
        return;
    }
    
    currentZoom = 1;
    
    const img = document.getElementById('modalImage');
    img.src = imageUrl;
    img.style.transform = 'scale(1)';
    img.onerror = function() {
        alert('Failed to load image: ' + imageUrl);
    };
    img.onload = function() {
        console.log('Image loaded successfully');
    };
    
    document.getElementById('imageModalTitle').textContent = title || 'Screenshot';
    document.getElementById('imageInfo').textContent = 'Click image to toggle fullscreen';
    
    const downloadEl = document.getElementById('downloadImage');
    const filename = imageUrl.split('/').pop() || 'screenshot.jpg';
    downloadEl.href = imageUrl;
    downloadEl.download = filename;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    // Show shortcut hint
    setTimeout(() => {
        const hint = document.getElementById('shortcutHint');
        hint.classList.add('show');
        setTimeout(() => hint.classList.remove('show'), 3000);
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

function formatDate(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function refreshOnlineClasses() {
    loadOnlineClasses();
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