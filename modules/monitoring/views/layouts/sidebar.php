<!-- Font Import: Plus Jakarta Sans for crisp UI typography -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="col-md-2 sidebar-enhanced">
    <div class="sidebar-wrapper">
        
        <!-- User Profile Header Section -->
        <div class="user-header-section">
            <div class="header-top-actions">
                <div class="school-logo">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="header-icons">
                    <i class="bi bi-bell"></i>
                    <div class="user-status-icon">
                        <i class="bi bi-person-circle"></i>
                        <span class="online-indicator"></span>
                    </div>
                </div>
            </div>

            <div class="user-profile-center">
                <div class="user-avatar-circle">
                    <?php 
                        $initials = 'M';
                        if (isset($_SESSION['fullname'])) {
                            $words = explode(' ', trim($_SESSION['fullname']));
                            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                        }
                        echo $initials;
                    ?>
                </div>
                <div class="user-fullname">
                    <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Monitoring'; ?>
                </div>
                <div class="user-email">
                    <?php echo isset($_SESSION['email']) ? $_SESSION['email'] : 'admin@bcp.edu.ph'; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Navigation -->
        <?php $monitoringBase = '/modules/monitoring/public/index.php'; ?>
        <nav class="nav flex-column">
            
            <!-- SECTION 1 -->
            <div class="nav-section-label">
                <span class="section-title">MAIN DASHBOARD</span>
                <span class="section-subtitle">System Overview & Stats</span>
            </div>

            <a href="<?php echo $monitoringBase; ?>?page=dashboard" class="nav-link <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-pie-chart-fill"></i> 
                <span>Dashboard</span>
            </a>
            
            <!-- SECTION 2 -->
            <div class="nav-section-label">
                <span class="section-title">MANAGEMENT</span>
                <span class="section-subtitle">Core Operations</span>
            </div>
            
            <!-- Attendance Dropdown -->
            <div class="nav-item dropdown-wrapper">
                <a href="#" class="nav-link <?php echo in_array($currentPage ?? '', ['attendance', 'online-classes']) ? 'active' : ''; ?>" id="attendanceToggle">
                    <i class="bi bi-calendar-check-fill"></i> 
                    <span>Attendance</span>
                    <span class="dropdown-arrow <?php echo in_array($currentPage ?? '', ['attendance', 'online-classes']) ? 'open' : ''; ?>" id="attendanceArrow">
                        <i class="bi bi-chevron-down"></i>
                    </span>
                </a>
                <div class="sub-menu <?php echo in_array($currentPage ?? '', ['attendance', 'online-classes']) ? 'show' : ''; ?>" id="attendanceSubMenu">
                    <a href="<?php echo $monitoringBase; ?>?page=attendance" class="sub-nav-link <?php echo ($currentPage ?? '') === 'attendance' ? 'active' : ''; ?>">
                        <i class="bi bi-table"></i> 
                        <span>Face To Face</span>
                    </a>
                    <a href="<?php echo $monitoringBase; ?>?page=online-classes" class="sub-nav-link <?php echo ($currentPage ?? '') === 'online-classes' ? 'active' : ''; ?>">
                        <i class="bi bi-camera-video-fill"></i> 
                        <span>Online Classes</span>
                    </a>
                </div>
            </div>
            
            <a href="<?php echo $monitoringBase; ?>?page=facilities" class="nav-link <?php echo ($currentPage ?? '') === 'facilities' ? 'active' : ''; ?>">
                <i class="bi bi-building-fill"></i> 
                <span>Facilities</span>
            </a>
            <a href="<?php echo $monitoringBase; ?>?page=visitors" class="nav-link <?php echo ($currentPage ?? '') === 'visitors' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i> 
                <span>Visitors</span>
            </a>
            
            <!-- SECTION 3 -->
            <div class="nav-section-label">
                <span class="section-title">REPORTS & ANALYTICS</span>
                <span class="section-subtitle">System Documentation</span>
            </div>

            <?php 
                $reportType = $_GET['type'] ?? ''; 
                $isReportActive = (($currentPage ?? '') === 'reports') || in_array($reportType, ['academic', 'facilities', 'security']);
            ?>
            <!-- Reports Dropdown -->
            <div class="nav-item dropdown-wrapper">
                <a href="#" class="nav-link <?php echo $isReportActive ? 'active' : ''; ?>" id="reportsToggle">
                    <i class="bi bi-file-earmark-clipboard-fill"></i> 
                    <span>Reports</span>
                    <span class="badge-beta">BETA</span>
                    <span class="dropdown-arrow <?php echo $isReportActive ? 'open' : ''; ?>" id="reportsArrow">
                        <i class="bi bi-chevron-down"></i>
                    </span>
                </a>
                <div class="sub-menu <?php echo $isReportActive ? 'show' : ''; ?>" id="reportsSubMenu">
                    <a href="<?php echo $monitoringBase; ?>?page=reports&type=academic" class="sub-nav-link <?php echo $reportType === 'academic' ? 'active' : ''; ?>">
                        <i class="bi bi-file-text-fill"></i>
                        <span>Academic Records</span>
                    </a>
                    <a href="<?php echo $monitoringBase; ?>?page=reports&type=facilities" class="sub-nav-link <?php echo $reportType === 'facilities' ? 'active' : ''; ?>">
                        <i class="bi bi-building-check"></i>
                        <span>Facilities Records</span>
                    </a>
                    <a href="<?php echo $monitoringBase; ?>?page=reports&type=security" class="sub-nav-link <?php echo $reportType === 'security' ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Security Records</span>
                    </a>
                </div>
            </div>

            <a href="<?php echo $monitoringBase; ?>?page=archive" class="nav-link <?php echo ($currentPage ?? '') === 'archive' ? 'active' : ''; ?>">
                <i class="bi bi-archive-fill"></i> 
                <span>Archive</span>
            </a>
        </nav>
        
        <!-- Footer / Logout Section -->
        <div class="user-info mt-auto">
            <hr class="divider">
            <a href="#" class="logout-btn" id="logoutBtn">
                <i class="bi bi-box-arrow-right"></i> 
                <span>Sign Out</span>
            </a>
            <div class="sidebar-footer">
                <small>&copy; <?php echo date('Y'); ?> MonitorPro Portal</small>
            </div>
        </div>
    </div>
</div>

<style>
.sidebar-enhanced {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    min-height: 100vh;
    background: #19006b; /* Updated sidebar background */
    padding: 0;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* User Header Section */
.user-header-section {
    padding: 18px 20px 22px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.header-top-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.school-logo i {
    font-size: 1.6rem;
    color: #ffffff;
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 14px;
    color: #c7c4f0;
    font-size: 1.2rem;
}

.header-icons i {
    cursor: pointer;
    transition: color 0.2s;
}

.header-icons i:hover {
    color: #ffffff;
}

.user-status-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 0px;
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    border: 1.5px solid #19006b;
}

/* Avatar Circle */
.user-profile-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.user-avatar-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #334354; /* Updated avatar background */
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 2.1rem;
    letter-spacing: 1px;
    margin-bottom: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
}

.user-fullname {
    color: #ffffff;
    font-weight: 700;
    font-size: 1.05rem;
    letter-spacing: 0.2px;
    margin-bottom: 3px;
}

.user-email {
    color: #c7c4f0;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Navigation Section Headers */
.nav {
    padding: 10px 14px;
    flex: 1;
}

.nav-section-label {
    display: flex;
    flex-direction: column;
    padding: 22px 12px 8px 12px;
}

.nav-section-label .section-title {
    color: #ffffff; /* Updated header title */
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.nav-section-label .section-subtitle {
    color: #c7c4f0; /* Updated subtext */
    font-size: 0.7rem;
    font-weight: 500;
    margin-top: 2px;
}

/* Nav Links */
.nav .nav-link {
    color: #ffffff;
    padding: 11px 16px;
    margin: 3px 0;
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    position: relative;
    cursor: pointer;
}

.nav .nav-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
    color: #ffffff;
}

.nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

/* Active Nav Link */
.nav .nav-link.active {
    background: #4d49b6; /* Updated active pill color */
    color: #ffffff;
    font-weight: 700;
}

.nav .nav-link .dropdown-arrow {
    margin-left: auto;
    transition: transform 0.3s ease;
    font-size: 0.72rem;
    color: #c7c4f0;
}

.nav .nav-link .dropdown-arrow.open {
    transform: rotate(180deg);
}

/* Yellow Badge */
.badge-beta {
    margin-left: auto;
    margin-right: 6px;
    background: #ffbe00;
    color: #0d172a;
    font-size: 0.62rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 10px;
    letter-spacing: 0.5px;
}

/* Dropdown Sub-menu */
.dropdown-wrapper {
    position: relative;
}

.sub-menu {
    padding-left: 16px;
    margin: 2px 0 6px 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.3s ease;
    opacity: 0;
}

.sub-menu.show {
    max-height: 200px;
    opacity: 1;
}

.sub-nav-link {
    color: #c7c4f0;
    padding: 9px 14px;
    margin: 2px 0;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 500;
    cursor: pointer;
}

.sub-nav-link i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
    color: #c7c4f0;
}

.sub-nav-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
}

.sub-nav-link.active {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.15);
    font-weight: 600;
}

/* User Info & Footer */
.user-info {
    padding: 10px 20px 15px 20px;
}

.divider {
    border-color: rgba(255, 255, 255, 0.2);
    margin: 10px 0 14px 0;
}

.logout-btn {
    color: #c7c4f0;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 0.88rem;
    font-weight: 600;
}

.logout-btn i {
    font-size: 1.1rem;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.18);
    color: #fca5a5;
}

.sidebar-footer {
    text-align: center;
    padding-top: 10px;
    color: #c7c4f0;
    font-size: 0.65rem;
    font-weight: 500;
}

/* Scrollbar */
.sidebar-enhanced::-webkit-scrollbar { width: 4px; }
.sidebar-enhanced::-webkit-scrollbar-track { background: transparent; }
.sidebar-enhanced::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupDropdown(toggleId, menuId, arrowId) {
        const toggleBtn = document.getElementById(toggleId);
        const subMenu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);

        if (toggleBtn && subMenu && arrow) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                subMenu.classList.toggle('show');
                arrow.classList.toggle('open');
            });
        }
    }

    setupDropdown('attendanceToggle', 'attendanceSubMenu', 'attendanceArrow');
    setupDropdown('reportsToggle', 'reportsSubMenu', 'reportsArrow');

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'api/auth/logout';
            }
        });
    }
});
</script>