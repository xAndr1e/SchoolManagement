<!-- MODULE HEADER -->
<div class="module-header">
    <h1 id="moduleTitle">Appointments</h1>
    <p id="moduleSub">Manage and view your appointments</p>
</div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="college-info">
                    <h1>Guidance Office</h1>
                    <p>College of Arts and Sciences · Student Development & Counseling</p>
                </div>
                <div class="counselor-card">
                    <div class="counselor-avatar">JD</div>
                    <div class="counselor-details">
                        <h3>Johary Dimatingkal, RPm</h3>
                        <span>Guidance Counselor</span>
                    </div>
                </div>
            </div>
            <div class="header-stats">
                <div class="header-stat">
                    <span class="header-stat-value" id="headerTotal">0</span>
                    <span class="header-stat-label">Total</span>
                </div>
                <div class="header-stat">
                    <span class="header-stat-value" id="headerToday">0</span>
                    <span class="header-stat-label">Today</span>
                </div>
                <div class="header-stat">
                    <span class="header-stat-value" id="headerWeek">0</span>
                    <span class="header-stat-label">This Week</span>
                </div>
                <div class="header-stat">
                    <span class="header-stat-value" id="headerPending">0</span>
                    <span class="header-stat-label">Pending</span>
                </div>
            </div>
        </div>

        <!-- Button Container -->
        <div class="button-container">
            <div class="button-group">
                <button class="btn btn-primary" id="newAppointmentBtn">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    New Appointment
                </button>
                <button class="btn btn-secondary" id="viewTodayBtn">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Today
                </button>
                <button class="btn btn-secondary" id="viewAllBtn">
                    <svg viewBox="0 0 24 24">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                    </svg>
                    All
                </button>
            </div>
            <div class="button-group">
                <button class="btn btn-outline" id="refreshBtn">
                    <svg viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                    Refresh
                </button>
                <button class="btn btn-success" id="exportBtn">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-label">Total Students</div>
                <div class="stat-value" id="totalStudents">7</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-label">Academic</div>
                <div class="stat-value" id="academicCount">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💼</div>
                <div class="stat-label">Career</div>
                <div class="stat-value" id="careerCount">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-label">Personal</div>
                <div class="stat-value" id="personalCount">0</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="dashboard">Dashboard</button>
            <button class="tab" data-tab="list">All Appointments</button>
            <button class="tab" data-tab="calendar">Calendar</button>
            <button class="tab" data-tab="schedule">Schedule</button>
        </div>

        <!-- DASHBOARD TAB -->
        <div class="tab-content active" id="tab-dashboard">
            <div class="main-card">
                <div class="section-header">
                    <h2>Today's Appointments <span id="todayCount">0</span></h2>
                    <div>
                        <button class="btn btn-sm btn-secondary" id="dashboardRefreshBtn">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <polyline points="23 4 23 10 17 10"/>
                                <polyline points="1 20 1 14 7 14"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
                <div id="todayAppointmentsGrid" class="appointment-grid">
                    <div class="empty-state">Loading today's appointments...</div>
                </div>

                <div class="section-header" style="margin-top: 2rem;">
                    <h2>Upcoming This Week <span id="weekCount">0</span></h2>
                    <button class="btn btn-sm btn-secondary" id="viewCalendarBtn">
                        <svg viewBox="0 0 24 24" width="16" height="16">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                        </svg>
                        View Calendar
                    </button>
                </div>
                <div id="weekAppointmentsGrid" class="appointment-grid">
                    <div class="empty-state">Loading upcoming appointments...</div>
                </div>
            </div>
        </div>

        <!-- LIST TAB -->
        <div class="tab-content" id="tab-list">
            <div class="main-card">
                <div class="section-header">
                    <h2>All Appointments</h2>
                    <div>
                        <button class="btn btn-sm btn-primary" id="listNewBtn">+ New</button>
                        <button class="btn btn-sm btn-secondary" id="deleteSelectedBtn">Delete Selected</button>
                    </div>
                </div>

                <div class="filter-bar">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7a9bc2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" id="searchInput" placeholder="Search students...">
                    </div>
                    <select class="filter-select" id="filterType">
                        <option value="">All Types</option>
                        <option value="academic">Academic</option>
                        <option value="career">Career</option>
                        <option value="personal">Personal</option>
                    </select>
                    <select class="filter-select" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Date & Time</th>
                                <th>Student</th>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CALENDAR TAB -->
        <div class="tab-content" id="tab-calendar">
            <div class="main-card">
                <div class="section-header">
                    <h2>Appointment Calendar</h2>
                    <div class="button-group">
                        <button class="btn btn-sm btn-secondary" id="prevMonthBtn">← Previous</button>
                        <button class="btn btn-sm btn-primary" id="currentMonthBtn">Current</button>
                        <button class="btn btn-sm btn-secondary" id="nextMonthBtn">Next →</button>
                    </div>
                </div>

                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="calendar-title" id="calendarMonthYear">March 2025</div>
                        <div class="button-group">
                            <button class="btn btn-sm btn-outline" id="calendarTodayBtn">Today</button>
                        </div>
                    </div>

                    <div class="calendar-weekdays">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>

                    <div class="calendar-grid" id="calendarGrid"></div>

                    <div class="selected-date-appointments" id="selectedDateAppointments">
                        <h3 style="margin-bottom: 1rem; color: #0a2a44;">Appointments for <span id="selectedDateDisplay">March 15, 2025</span></h3>
                        <div id="selectedDateList"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SCHEDULE TAB -->
        <div class="tab-content" id="tab-schedule">
            <div class="main-card">
                <div class="section-header">
                    <h2 id="formTitle">Schedule New Appointment</h2>
                    <div>
                        <button class="btn btn-sm btn-secondary" id="scheduleCancelBtn">Cancel</button>
                        <button class="btn btn-sm btn-primary" id="scheduleSaveBtn">Save</button>
                    </div>
                </div>

                <form id="appointmentForm">
                    <input type="hidden" id="appointmentId">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Student</label>
                            <select class="form-control" id="studentSelect" required>
                                <option value="">Select Student</option>
                                <option value="S001">Maria Santos - BS Psychology - 3rd Year</option>
                                <option value="S002">Juan Dela Cruz - BS Computer Science - 2nd Year</option>
                                <option value="S003">Ana Lopez - BS Nursing - 4th Year</option>
                                <option value="S004">Jose Reyes - BS Engineering - 3rd Year</option>
                                <option value="S005">Lisa Wong - BS Business Admin - 2nd Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select class="form-control" id="appointmentType" required>
                                <option value="">Select</option>
                                <option value="academic">Academic Counseling</option>
                                <option value="career">Career Guidance</option>
                                <option value="personal">Personal Counseling</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-control" id="priority">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" id="appointmentDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" id="appointmentTime" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration</label>
                            <select class="form-control" id="duration">
                                <option value="30">30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" id="reason" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal-overlay" id="appointmentModal">
            <div class="modal">
                <div class="modal-header">
                    <h3>Appointment Details</h3>
                    <button class="modal-close" id="closeModalBtn">&times;</button>
                </div>
                <div class="modal-body" id="modalBody"></div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-close-btn">Close</button>
                    <button class="btn btn-danger" id="modalDeleteBtn">Delete</button>
                    <button class="btn btn-primary" id="modalEditBtn">Edit</button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div class="toast" id="toast">
            <span id="toastMessage">Appointment saved successfully!</span>
        </div>
    </div>

</body>
</html>