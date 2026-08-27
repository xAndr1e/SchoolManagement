<?php 
$currentPage = 'dashboard';
include 'layouts/header.php'; 
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="page-title">
                <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
                <p class="text-muted">Welcome to the Monitoring System Dashboard</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row" id="statsContainer">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number" id="todayAttendance">0</div>
                                <div class="stat-label">Today's Attendance</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number" id="pendingReports">0</div>
                                <div class="stat-label">Pending Reports</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number" id="visitorsToday">0</div>
                                <div class="stat-label">Visitors Today</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-people text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number" id="visitorsInside">0</div>
                                <div class="stat-label">Visitors Inside</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-door-open text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Visitor Statistics -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number" id="totalVisitors">0</div>
                        <div class="stat-label">Total Today</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-success" id="insideVisitors">0</div>
                        <div class="stat-label">Currently Inside</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-warning" id="pendingApprovals">0</div>
                        <div class="stat-label">Pending Approvals</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-info" id="checkedOut">0</div>
                        <div class="stat-label">Checked Out Today</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity and Charts -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <h5><i class="bi bi-clock-history"></i> Recent Activity</h5>
                        <table class="table table-hover" id="activityTable">
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
                <div class="col-md-4">
                    <div class="stat-card">
                        <h5><i class="bi bi-pie-chart"></i> Today's Attendance Rate</h5>
                        <div class="text-center">
                            <div class="display-1" id="attendanceRate">0%</div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" id="attendanceProgress" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- End of col-md-10 main-content -->
    </div> <!-- End of row -->
</div> <!-- End of container-fluid -->

<script>
$(document).ready(function() {
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
                tbody.append('<tr><td colspan="3" class="text-center">No recent activity</td></tr>');
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