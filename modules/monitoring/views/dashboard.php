<?php 
$currentPage = 'dashboard';
include 'layouts/header.php'; 
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="col-md-10 main-wrapper">
            
            <!-- Top Navigation Bar -->
            <div class="top-navbar">
                <button class="btn toggle-sidebar-btn" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="live-clock" id="liveClock">00:00:00 AM</div>
            </div>

            <!-- Dashboard Content Scrollable Area -->
            <div class="dashboard-container">
                
                <!-- Main Title Card -->
                <div class="card header-card border-0 mb-4">
                    <div class="card-body">
                        <h1 class="dashboard-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
                        <p class="dashboard-subtitle">Welcome to the Monitoring System Dashboard</p>
                    </div>
                </div>

                <!-- Welcome Banner -->
                <div class="welcome-banner mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="welcome-title">Welcome back, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'User'; ?>!</h2>
                            <p class="welcome-subtitle">Here's a quick snapshot of the school's current status.</p>
                        </div>
                        <div class="text-end date-badge">
                            <div class="day-number"><?php echo date('d'); ?></div>
                            <div class="month-year"><?php echo strtoupper(date('F Y')); ?></div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 1: SYSTEM OVERVIEW -->
                <div class="section-group mb-4">
                    <div class="section-category-title">SYSTEM OVERVIEW</div>
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">TODAY'S ATTENDANCE</div>
                                <div class="stat-card-value text-primary" id="todayAttendance">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">PENDING REPORTS</div>
                                <div class="stat-card-value text-warning" id="pendingReports">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">VISITORS TODAY</div>
                                <div class="stat-card-value text-success" id="visitorsToday">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">VISITORS INSIDE</div>
                                <div class="stat-card-value text-info" id="visitorsInside">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: VISITOR STATISTICS -->
                <div class="section-group mb-4">
                    <div class="section-category-title">VISITOR STATISTICS</div>
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">TOTAL TODAY</div>
                                <div class="stat-card-value" id="totalVisitors">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">CURRENTLY INSIDE</div>
                                <div class="stat-card-value text-success" id="insideVisitors">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">PENDING APPROVALS</div>
                                <div class="stat-card-value text-warning" id="pendingApprovals">0</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card">
                                <div class="stat-card-label">CHECKED OUT TODAY</div>
                                <div class="stat-card-value text-info" id="checkedOut">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: RECENT ACTIVITY & ANALYTICS -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <h5 class="content-card-title"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle custom-table" id="activityTable">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Activity</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activityBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="content-card text-center d-flex flex-column justify-content-between h-100">
                            <h5 class="content-card-title text-start"><i class="bi bi-pie-chart me-2"></i>Today's Attendance Rate</h5>
                            <div class="py-4">
                                <div class="display-2 fw-bold text-dark" id="attendanceRate">0%</div>
                                <div class="progress mt-3" style="height: 12px; border-radius: 10px;">
                                    <div class="progress-bar bg-primary" id="attendanceProgress" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <small class="text-muted">Updated in real-time</small>
                        </div>
                    </div>
                </div>

            </div> <!-- End of dashboard-container -->
        </div> <!-- End of main-wrapper -->
    </div>
</div>

<style>
/* Dashboard Base Layout */
.main-wrapper {
    background-color: #f1f3f6;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Top Navbar */
.top-navbar {
    background-color: #ffffff;
    height: 60px;
    padding: 0 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
}

.toggle-sidebar-btn {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    color: #111827;
    padding: 0;
    cursor: pointer;
}

.live-clock {
    font-size: 0.95rem;
    font-weight: 600;
    color: #111827;
    letter-spacing: 0.3px;
}

/* Dashboard Inner Container */
.dashboard-container {
    padding: 30px;
    flex: 1;
}

/* Header Card */
.header-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.dashboard-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0d0f12;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}

.dashboard-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
    font-weight: 500;
    margin: 0;
}

/* Welcome Banner */
.welcome-banner {
    background: #19006b;
    border-radius: 14px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(25, 0, 107, 0.2);
}

.welcome-title {
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 6px 0;
    letter-spacing: -0.2px;
}

.welcome-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0;
}

.date-badge .day-number {
    font-size: 2.8rem;
    font-weight: 800;
    line-height: 1;
    color: #ffffff;
}

.date-badge .month-year {
    font-size: 0.75rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.8px;
    margin-top: 4px;
}

/* Section Labels */
.section-category-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6b7280;
    letter-spacing: 0.6px;
    margin-bottom: 12px;
    text-transform: uppercase;
}

/* Metric Cards */
.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px 22px;
    min-height: 105px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(0, 0, 0, 0.02);
}

.stat-card-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #6b7280;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.stat-card-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
}

/* Content Cards & Tables */
.content-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.content-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 18px;
}

.custom-table th {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #6b7280;
    border-bottom-width: 1px;
}
</style>

<script>
$(document).ready(function() {
    // Live digital clock
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; 
        const formattedHours = String(hours).padStart(2, '0');

        const clockElement = document.getElementById('liveClock');
        if (clockElement) {
            clockElement.textContent = `${formattedHours}:${minutes}:${seconds} ${ampm}`;
        }
    }

    setInterval(updateClock, 1000);
    updateClock();

    // Original API loader calls
    loadDashboardData();
    loadDashboardStats();
    
    // Refresh every 60 seconds
    setInterval(function() {
        loadDashboardData();
        loadDashboardStats();
    }, 60000);
});

async function loadDashboardData() {
    try {
        const data = await apiCall('dashboard');
        if (data && !data.error) {
            const summary = data.summary || {};
            $('#todayAttendance').text(summary.today_attendance || 0);
            $('#pendingReports').text(summary.pending_reports || 0);
            $('#visitorsToday').text(summary.visitors_today || 0);
            $('#visitorsInside').text(summary.visitors_inside || 0);
            
            const rate = data.attendance_rate || 0;
            $('#attendanceRate').text(rate + '%');
            $('#attendanceProgress').css('width', rate + '%');
            
            const tbody = $('#activityBody');
            tbody.empty();
            
            const activities = data.recent_activity || [];
            if (activities.length > 0) {
                activities.forEach(activity => {
                    const badgeClass = {
                        'attendance': 'bg-primary',
                        'facility': 'bg-warning',
                        'visitor': 'bg-success'
                    }[activity.type] || 'bg-secondary';
                    
                    tbody.append(`
                        <tr>
                            <td>${formatDate(activity.time)}</td>
                            <td>${escapeHtml(activity.message)}</td>
                            <td><span class="badge ${badgeClass}">${escapeHtml(activity.type)}</span></td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="3" class="text-center text-muted">No recent activity</td></tr>');
            }
        } else {
            showToast('Failed to load dashboard data', 'error');
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showToast('Error loading dashboard data', 'error');
    }
}

async function loadDashboardStats() {
    try {
        const stats = await apiCall('visitor/statistics');
        if (stats) {
            $('#totalVisitors').text(stats.today || 0);
            $('#insideVisitors').text(stats.inside || 0);
            $('#pendingApprovals').text(stats.pending || 0);
            $('#checkedOut').text(stats.checked_out || 0);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}
</script>

<?php include 'layouts/footer.php'; ?>