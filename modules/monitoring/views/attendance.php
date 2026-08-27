<?php 
$currentPage = 'attendance';
include 'layouts/header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-calendar-check"></i> Faculty Attendance Monitoring</h2>
                <p class="text-muted">View faculty schedules and attendance records</p>
                <div class="mt-2">
                    <span class="badge bg-info">📱 Use mobile device to mark attendance</span>
                    <a href="/mobile-attendance" class="btn btn-sm btn-primary ms-2" target="_blank">
                        <i class="bi bi-phone"></i> Open Mobile View
                    </a>
                </div>
            </div>
            
            <!-- Week Navigator -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-secondary" onclick="navigateWeek(-1)">
                                    <i class="bi bi-chevron-left"></i> Previous Week
                                </button>
                                <div class="text-center">
                                    <h5 class="mb-0" id="currentWeekDisplay">This Week</h5>
                                    <small class="text-muted" id="currentWeekRange"></small>
                                </div>
                                <div>
                                    <button class="btn btn-outline-secondary me-2" onclick="navigateWeek(1)">
                                        Next Week <i class="bi bi-chevron-right"></i>
                                    </button>
                                    <button class="btn btn-primary" onclick="goToCurrentWeek()">
                                        <i class="bi bi-calendar-week"></i> Current Week
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Day Selector -->
                            <div class="d-flex justify-content-center mt-3 gap-2 flex-wrap" id="daySelector">
                                <button class="btn btn-sm btn-outline-primary day-btn" data-day="Monday" onclick="selectDay('Monday')">
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
                            
                            <!-- Selected Day Display -->
                            <div class="text-center mt-2">
                                <span class="badge bg-primary" id="selectedDayDisplay">Monday</span>
                                <span class="badge bg-secondary" id="scheduleCountDisplay">0 schedules</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden inputs -->
            <input type="hidden" id="attendanceDateHidden" value="<?php echo date('Y-m-d'); ?>">
            <input type="hidden" id="selectedDay" value="Monday">
            
            <!-- Last updated indicator -->
            <div class="text-end mb-2">
                <small class="text-muted" id="lastUpdated">Last updated: just now</small>
                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            
            <!-- Schedules Table -->
            <div class="table-responsive" id="schedulesContainer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="bi bi-table"></i> Schedules for <span id="scheduleDayLabel">Monday</span></h5>
                    <span class="badge bg-primary" id="scheduleCount">0 schedules</span>
                </div>
                <table class="table table-bordered table-hover" id="scheduleTable">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Course/Section</th>
                            <th>Day</th>
                            <th>Faculty</th>
                            <th>Room</th>
                            <th>Subject Code</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleBody">
                    </tbody>
                </table>
            </div>
            
            <!-- Attendance Records -->
            <div class="table-responsive mt-4" id="attendanceContainer">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="bi bi-check2-square"></i> Attendance Records for <span id="attendanceDayLabel">Monday</span></h5>
                    <span class="badge bg-success" id="attendanceCount">0 records</span>
                </div>
                <table class="table table-bordered table-hover" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>Faculty</th>
                            <th>Course/Section</th>
                            <th>Subject</th>
                            <th>Room</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>Face-to-Face Photo</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image"></i> <span id="imageModalTitle">Image</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreview" src="" alt="Image" class="img-fluid" style="max-height: 80vh; max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="downloadImage" class="btn btn-primary" download>
                    <i class="bi bi-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// ATTENDANCE PAGE CONTROLLER - VIEW ONLY
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

function formatSingleTime(timeStr) {
    if (!timeStr) return 'N/A';
    
    try {
        // If it's already in HH:MM:SS format
        if (typeof timeStr === 'string' && timeStr.match(/^\d{2}:\d{2}(:\d{2})?$/)) {
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const hours12 = hours % 12 || 12;
            return `${hours12}:${minutes} ${ampm}`;
        }
        
        // Try parsing as date
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
        // If we have both start and end
        if (endTime) {
            const startFormatted = formatSingleTime(startTime);
            const endFormatted = formatSingleTime(endTime);
            return `${startFormatted} - ${endFormatted}`;
        }
        
        // Single time
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
    // Simple toast implementation
    const toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    const toastEl = toast.find('.toast');
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
    bsToast.show();
    
    setTimeout(() => {
        toast.remove();
    }, 3500);
}

// ============================================
// API CALL
// ============================================

async function apiCall(endpoint) {
    try {
        const response = await fetch('/api/' + endpoint);
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
    $('#lastUpdated').text('Last updated: ' + timeStr);
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
                
                let html = '';
                schedules.forEach(schedule => {
                    const statusBadge = getStatusBadge(schedule.status);
                    const isMarked = markedScheduleIds.includes(schedule.id);
                    const attendanceBadge = isMarked 
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Marked</span>'
                        : '<span class="badge bg-secondary"><i class="bi bi-clock"></i> Pending</span>';
                    
                    const studentCount = schedule.student_count || 0;
                    const studentBadge = studentCount > 0 
                        ? `<span class="badge bg-info">${studentCount}</span>`
                        : `<span class="badge bg-secondary">0</span>`;
                    
                    // FIX: Use start_time and end_time
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
                            <td><span class="badge ${statusBadge}">${escapeHtml(schedule.status)}</span></td>
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
                            <td><span class="badge ${statusBadge}">${escapeHtml(schedule.status)}</span></td>
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
            const html = `
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">No schedules found for ${escapeHtml(day)}</h6>
                            <small class="text-muted">Check other days or add schedules</small>
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
            let html = '';
            records.forEach(record => {
                const statusBadge = getStatusBadge(record.status);
                const isOnline = record.is_online ? '<span class="badge bg-info ms-1">Online</span>' : '';
                const studentCount = record.student_count || 0;
                const studentBadge = studentCount > 0 
                    ? `<span class="badge bg-info">${studentCount}</span>`
                    : `<span class="badge bg-secondary">0</span>`;
                
                // Face-to-face image or meeting screenshot
                let imageHtml = '<span class="text-muted">No image</span>';
                
                // Check for face-to-face image first
                if (record.face_to_face_image) {
                    let imgSrc = record.face_to_face_image;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imageHtml = `
                        <img src="${imgSrc}" 
                             alt="Face-to-face" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #dee2e6;"
                             onclick="viewImage('${imgSrc}', 'Face-to-Face Photo - ${escapeHtml(record.faculty_name)}')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid image</span>';">
                    `;
                } 
                // Check for meeting screenshot (online)
                else if (record.meeting_screenshot) {
                    let imgSrc = record.meeting_screenshot;
                    if (!imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
                        imgSrc = '/' + imgSrc;
                    }
                    imageHtml = `
                        <img src="${imgSrc}" 
                             alt="Meeting Screenshot" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #dee2e6;"
                             onclick="viewImage('${imgSrc}', 'Meeting Screenshot - ${escapeHtml(record.faculty_name)}')"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-danger\\'>Invalid image</span>';">
                    `;
                }
                
                // Format the check time
                const checkTime = record.check_time ? formatDate(record.check_time) : 'N/A';
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(record.faculty_name)}</strong></td>
                        <td>${escapeHtml(record.course_section)}</td>
                        <td>${escapeHtml(record.subject_code)}</td>
                        <td>${escapeHtml(record.room)}</td>
                        <td>${studentBadge}</td>
                        <td><span class="badge ${statusBadge}">${escapeHtml(record.status)}</span>${isOnline}</td>
                        <td>${escapeHtml(record.verification_method || 'N/A')}</td>
                        <td>${imageHtml}</td>
                        <td>${checkTime}</td>
                    </tr>
                `;
            });
            if (tbody.html() !== html) {
                tbody.html(html);
            }
        } else {
            $('#attendanceCount').text('0 records');
            const html = `
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="py-4">
                            <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                            <h6 class="mt-2">No attendance records for ${escapeHtml(day)}</h6>
                            <small class="text-muted">Use mobile device to mark attendance</small>
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
        showToast('Failed to load image. Path: ' + imageUrl, 'error');
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