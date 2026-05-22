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

    <script>
        (function() {
            // Sample data
            let appointments = [
                {
                    id: 1001,
                    studentId: 'S001',
                    studentName: 'Maria Santos',
                    course: 'BS Psychology',
                    year: '3rd Year',
                    type: 'academic',
                    typeLabel: 'Academic Counseling',
                    priority: 'urgent',
                    date: getTodayDate(),
                    time: '09:00',
                    duration: 60,
                    reason: 'Academic probation - failing grades',
                    status: 'scheduled',
                    selected: false
                },
                {
                    id: 1002,
                    studentId: 'S002',
                    studentName: 'Juan Dela Cruz',
                    course: 'BS Computer Science',
                    year: '2nd Year',
                    type: 'career',
                    typeLabel: 'Career Guidance',
                    priority: 'normal',
                    date: getTodayDate(),
                    time: '10:30',
                    duration: 45,
                    reason: 'Internship placement',
                    status: 'scheduled',
                    selected: false
                },
                {
                    id: 1003,
                    studentId: 'S003',
                    studentName: 'Ana Lopez',
                    course: 'BS Nursing',
                    year: '4th Year',
                    type: 'personal',
                    typeLabel: 'Personal Counseling',
                    priority: 'urgent',
                    date: getTodayDate(),
                    time: '13:00',
                    duration: 60,
                    reason: 'Anxiety about board exams',
                    status: 'scheduled',
                    selected: false
                },
                {
                    id: 1004,
                    studentId: 'S004',
                    studentName: 'Jose Reyes',
                    course: 'BS Engineering',
                    year: '3rd Year',
                    type: 'academic',
                    typeLabel: 'Academic Counseling',
                    priority: 'normal',
                    date: getTomorrowDate(),
                    time: '09:30',
                    duration: 45,
                    reason: 'Difficulty with calculus',
                    status: 'scheduled',
                    selected: false
                },
                {
                    id: 1005,
                    studentId: 'S005',
                    studentName: 'Lisa Wong',
                    course: 'BS Business Admin',
                    year: '2nd Year',
                    type: 'career',
                    typeLabel: 'Career Guidance',
                    priority: 'low',
                    date: getDayAfterTomorrowDate(),
                    time: '11:00',
                    duration: 30,
                    reason: 'Career options discussion',
                    status: 'scheduled',
                    selected: false
                }
            ];

            let nextId = 1006;
            let currentDate = new Date();
            let selectedDate = new Date();
            let selectedAppointmentId = null;

            // Helper functions
            function getTodayDate() {
                return new Date().toISOString().split('T')[0];
            }

            function getTomorrowDate() {
                const date = new Date();
                date.setDate(date.getDate() + 1);
                return date.toISOString().split('T')[0];
            }

            function getDayAfterTomorrowDate() {
                const date = new Date();
                date.setDate(date.getDate() + 2);
                return date.toISOString().split('T')[0];
            }

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            function formatDisplayDate(date) {
                return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            }

            // Show toast notification
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                toast.className = `toast ${type}`;
                toastMessage.innerText = message;
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }

            // Update all stats
            function updateStats() {
                const today = getTodayDate();
                const todayAppts = appointments.filter(a => a.date === today);
                const thisWeek = appointments.filter(a => {
                    const appDate = new Date(a.date);
                    const todayDate = new Date();
                    const diffDays = Math.ceil((appDate - todayDate) / (1000 * 60 * 60 * 24));
                    return diffDays >= 0 && diffDays <= 7;
                });
                const pending = appointments.filter(a => a.status === 'scheduled').length;

                document.getElementById('headerTotal').innerText = appointments.length;
                document.getElementById('headerToday').innerText = todayAppts.length;
                document.getElementById('headerWeek').innerText = thisWeek.length;
                document.getElementById('headerPending').innerText = pending;
                document.getElementById('totalStudents').innerText = appointments.length;
                document.getElementById('academicCount').innerText = appointments.filter(a => a.type === 'academic').length;
                document.getElementById('careerCount').innerText = appointments.filter(a => a.type === 'career').length;
                document.getElementById('personalCount').innerText = appointments.filter(a => a.type === 'personal').length;
                document.getElementById('todayCount').innerText = todayAppts.length;
                document.getElementById('weekCount').innerText = thisWeek.length;
            }

            // Render today's appointments
            function renderTodayGrid() {
                const today = getTodayDate();
                const todayAppts = appointments.filter(a => a.date === today);
                const grid = document.getElementById('todayAppointmentsGrid');

                if (todayAppts.length === 0) {
                    grid.innerHTML = '<div class="empty-state">No appointments scheduled for today</div>';
                    return;
                }

                let html = '';
                todayAppts.forEach(app => {
                    html += createAppointmentCard(app);
                });
                grid.innerHTML = html;
                attachCardButtons();
            }

            // Render week appointments
            function renderWeekGrid() {
                const today = new Date();
                const weekAppts = appointments.filter(a => {
                    const appDate = new Date(a.date);
                    const diffDays = Math.ceil((appDate - today) / (1000 * 60 * 60 * 24));
                    return diffDays > 0 && diffDays <= 7;
                });
                const grid = document.getElementById('weekAppointmentsGrid');

                if (weekAppts.length === 0) {
                    grid.innerHTML = '<div class="empty-state">No upcoming appointments this week</div>';
                    return;
                }

                let html = '';
                weekAppts.forEach(app => {
                    html += createAppointmentCard(app);
                });
                grid.innerHTML = html;
                attachCardButtons();
            }

            // Create appointment card
            function createAppointmentCard(app) {
                return `
                    <div class="appointment-card ${app.priority}" data-id="${app.id}">
                        <div class="card-actions">
                            <button class="icon-btn view-btn" data-id="${app.id}" title="View">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z"/>
                                </svg>
                            </button>
                            <button class="icon-btn edit-btn" data-id="${app.id}" title="Edit">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                    <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                </svg>
                            </button>
                        </div>
                        <div class="appointment-time">${app.time}</div>
                        <div class="student-name">${app.studentName}</div>
                        <div class="student-details">${app.course} - ${app.year}</div>
                        <div class="appointment-purpose">${app.reason}</div>
                        <div class="appointment-status status-${app.status}">${app.status}</div>
                    </div>
                `;
            }

            // Render appointments table
            function renderTable() {
                const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
                const typeFilter = document.getElementById('filterType')?.value || '';
                const statusFilter = document.getElementById('filterStatus')?.value || '';

                let filtered = appointments.filter(app => {
                    const matchesSearch = app.studentName.toLowerCase().includes(searchTerm) ||
                                        app.studentId.toLowerCase().includes(searchTerm);
                    const matchesType = !typeFilter || app.type === typeFilter;
                    const matchesStatus = !statusFilter || app.status === statusFilter;
                    return matchesSearch && matchesType && matchesStatus;
                });

                const tbody = document.getElementById('appointmentsTableBody');
                if (filtered.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No appointments found</td></tr>';
                    return;
                }

                let rows = '';
                filtered.forEach(app => {
                    rows += `
                        <tr>
                            <td><input type="checkbox" class="appointment-checkbox" data-id="${app.id}" ${app.selected ? 'checked' : ''}></td>
                            <td>${app.date}<br><small>${app.time}</small></td>
                            <td>${app.studentName}</td>
                            <td>${app.studentId}</td>
                            <td>${app.course}</td>
                            <td>${app.typeLabel}</td>
                            <td><span class="appointment-badge badge-${app.priority}">${app.priority}</span></td>
                            <td><span class="appointment-status status-${app.status}">${app.status}</span></td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn view-btn" data-id="${app.id}" title="View">
                                        <svg viewBox="0 0 24 24" width="16" height="16">
                                            <circle cx="12" cy="12" r="3"/>
                                            <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn edit-btn" data-id="${app.id}" title="Edit">
                                        <svg viewBox="0 0 24 24" width="16" height="16">
                                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn delete-btn" data-id="${app.id}" title="Delete">
                                        <svg viewBox="0 0 24 24" width="16" height="16">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0h10"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = rows;
                attachTableButtons();
            }

            // Render calendar
            function renderCalendar() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const startingDay = firstDay.getDay();
                const totalDays = lastDay.getDate();

                document.getElementById('calendarMonthYear').innerText = 
                    currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

                let calendarHtml = '';
                
                // Empty cells for days before month starts
                for (let i = 0; i < startingDay; i++) {
                    calendarHtml += '<div class="calendar-day"></div>';
                }

                // Days of the month
                for (let day = 1; day <= totalDays; day++) {
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const hasAppointments = appointments.some(a => a.date === dateStr);
                    const isToday = dateStr === getTodayDate();
                    const isSelected = dateStr === formatDate(selectedDate);

                    calendarHtml += `
                        <div class="calendar-day ${isToday ? 'today' : ''} ${hasAppointments ? 'has-appointments' : ''} ${isSelected ? 'selected' : ''}" data-date="${dateStr}">
                            <div class="day-number">${day}</div>
                            ${hasAppointments ? '<div class="appointment-indicator"></div>' : ''}
                        </div>
                    `;
                }

                document.getElementById('calendarGrid').innerHTML = calendarHtml;

                // Add click handlers to calendar days
                document.querySelectorAll('.calendar-day[data-date]').forEach(day => {
                    day.addEventListener('click', function() {
                        const dateStr = this.dataset.date;
                        selectedDate = new Date(dateStr);
                        renderCalendar();
                        renderSelectedDateAppointments(dateStr);
                    });
                });

                // Render appointments for selected date
                renderSelectedDateAppointments(formatDate(selectedDate));
            }

            // Render appointments for selected date
            function renderSelectedDateAppointments(dateStr) {
                document.getElementById('selectedDateDisplay').innerText = formatDisplayDate(new Date(dateStr));
                
                const dayAppointments = appointments.filter(a => a.date === dateStr);
                const listDiv = document.getElementById('selectedDateList');

                if (dayAppointments.length === 0) {
                    listDiv.innerHTML = '<div class="empty-state">No appointments scheduled for this date</div>';
                    return;
                }

                let html = '<div class="appointment-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">';
                dayAppointments.forEach(app => {
                    html += createAppointmentCard(app);
                });
                html += '</div>';
                listDiv.innerHTML = html;
                attachCardButtons();
            }

            // Show appointment details in modal
            function showAppointmentDetails(id) {
                const app = appointments.find(a => a.id === id);
                if (!app) return;

                selectedAppointmentId = id;

                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <div class="detail-item"><span class="detail-label">Student:</span> ${app.studentName}</div>
                    <div class="detail-item"><span class="detail-label">ID:</span> ${app.studentId}</div>
                    <div class="detail-item"><span class="detail-label">Course:</span> ${app.course}</div>
                    <div class="detail-item"><span class="detail-label">Date/Time:</span> ${app.date} at ${app.time}</div>
                    <div class="detail-item"><span class="detail-label">Type:</span> ${app.typeLabel}</div>
                    <div class="detail-item"><span class="detail-label">Priority:</span> ${app.priority}</div>
                    <div class="detail-item"><span class="detail-label">Reason:</span> ${app.reason}</div>
                    <div class="detail-item"><span class="detail-label">Status:</span> ${app.status}</div>
                `;

                document.getElementById('appointmentModal').style.display = 'flex';
            }

            // Edit appointment
            function editAppointment(id) {
                const app = appointments.find(a => a.id === id);
                if (!app) return;

                document.getElementById('appointmentId').value = app.id;
                document.getElementById('studentSelect').value = app.studentId;
                document.getElementById('appointmentType').value = app.type;
                document.getElementById('priority').value = app.priority;
                document.getElementById('appointmentDate').value = app.date;
                document.getElementById('appointmentTime').value = app.time;
                document.getElementById('duration').value = app.duration;
                document.getElementById('reason').value = app.reason;
                document.getElementById('formTitle').innerText = 'Edit Appointment';

                // Switch to schedule tab
                document.querySelector('[data-tab="schedule"]').click();
            }

            // Reset form
            function resetForm() {
                document.getElementById('appointmentId').value = '';
                document.getElementById('studentSelect').value = '';
                document.getElementById('appointmentType').value = '';
                document.getElementById('priority').value = 'normal';
                document.getElementById('appointmentDate').value = getTomorrowDate();
                document.getElementById('appointmentTime').value = '09:00';
                document.getElementById('duration').value = '45';
                document.getElementById('reason').value = '';
                document.getElementById('formTitle').innerText = 'Schedule New Appointment';
            }

            // Save appointment
            function saveAppointment() {
                const id = document.getElementById('appointmentId').value;
                const studentSelect = document.getElementById('studentSelect');
                const studentOption = studentSelect.options[studentSelect.selectedIndex];
                
                if (!studentOption.value) {
                    showToast('Please select a student', 'error');
                    return;
                }

                const studentText = studentOption.text;
                const parts = studentText.split(' - ');
                const studentName = parts[0];
                const courseYear = parts[1].split(' - ');
                const course = courseYear[0];
                const year = courseYear[1];

                const appointmentData = {
                    id: id ? parseInt(id) : nextId++,
                    studentId: studentSelect.value,
                    studentName: studentName,
                    course: course,
                    year: year,
                    type: document.getElementById('appointmentType').value,
                    typeLabel: document.getElementById('appointmentType').options[document.getElementById('appointmentType').selectedIndex].text,
                    priority: document.getElementById('priority').value,
                    date: document.getElementById('appointmentDate').value,
                    time: document.getElementById('appointmentTime').value,
                    duration: parseInt(document.getElementById('duration').value),
                    reason: document.getElementById('reason').value,
                    status: 'scheduled',
                    selected: false
                };

                if (id) {
                    const index = appointments.findIndex(a => a.id === parseInt(id));
                    if (index !== -1) {
                        appointments[index] = { ...appointments[index], ...appointmentData };
                        showToast('Appointment updated successfully!');
                    }
                } else {
                    appointments.push(appointmentData);
                    showToast('Appointment scheduled successfully!');
                }

                // Refresh all views
                updateStats();
                renderTodayGrid();
                renderWeekGrid();
                renderTable();
                renderCalendar();
                resetForm();
                
                // Go to list view
                document.querySelector('[data-tab="list"]').click();
            }

            // Delete appointment
            function deleteAppointment(id) {
                if (confirm('Are you sure you want to delete this appointment?')) {
                    appointments = appointments.filter(a => a.id !== id);
                    updateStats();
                    renderTodayGrid();
                    renderWeekGrid();
                    renderTable();
                    renderCalendar();
                    showToast('Appointment deleted successfully!', 'warning');
                }
            }

            // Attach card buttons
            function attachCardButtons() {
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        showAppointmentDetails(parseInt(btn.dataset.id));
                    });
                });

                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        editAppointment(parseInt(btn.dataset.id));
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        deleteAppointment(parseInt(btn.dataset.id));
                    });
                });
            }

            // Attach table buttons
            function attachTableButtons() {
                attachCardButtons(); // Reuse same function for view/edit buttons
                
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        deleteAppointment(parseInt(btn.dataset.id));
                    });
                });

                // Select all checkbox
                document.getElementById('selectAll')?.addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.appointment-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = e.target.checked;
                        const id = parseInt(cb.dataset.id);
                        const app = appointments.find(a => a.id === id);
                        if (app) app.selected = e.target.checked;
                    });
                });

                // Individual checkboxes
                document.querySelectorAll('.appointment-checkbox').forEach(cb => {
                    cb.addEventListener('change', function(e) {
                        const id = parseInt(this.dataset.id);
                        const app = appointments.find(a => a.id === id);
                        if (app) app.selected = this.checked;
                    });
                });
            }

            // Delete selected appointments
            function deleteSelected() {
                const selected = appointments.filter(a => a.selected);
                if (selected.length === 0) {
                    showToast('No appointments selected', 'error');
                    return;
                }

                if (confirm(`Delete ${selected.length} selected appointments?`)) {
                    appointments = appointments.filter(a => !a.selected);
                    updateStats();
                    renderTodayGrid();
                    renderWeekGrid();
                    renderTable();
                    renderCalendar();
                    showToast(`${selected.length} appointments deleted`, 'warning');
                }
            }

            // Export data
            function exportData() {
                const dataStr = JSON.stringify(appointments, null, 2);
                const blob = new Blob([dataStr], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `appointments_${getTodayDate()}.json`;
                a.click();
                showToast('Data exported successfully!');
            }

            // Initialize event listeners
            function initEventListeners() {
                // Tab switching
                document.querySelectorAll('.tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                        this.classList.add('active');
                        document.getElementById(`tab-${this.dataset.tab}`).classList.add('active');
                        
                        if (this.dataset.tab === 'list') renderTable();
                        if (this.dataset.tab === 'calendar') renderCalendar();
                    });
                });

                // New appointment buttons
                document.getElementById('newAppointmentBtn').addEventListener('click', () => {
                    resetForm();
                    document.querySelector('[data-tab="schedule"]').click();
                });

                document.getElementById('listNewBtn').addEventListener('click', () => {
                    resetForm();
                    document.querySelector('[data-tab="schedule"]').click();
                });

                // View buttons
                document.getElementById('viewTodayBtn').addEventListener('click', () => {
                    document.querySelector('[data-tab="dashboard"]').click();
                });

                document.getElementById('viewAllBtn').addEventListener('click', () => {
                    document.querySelector('[data-tab="list"]').click();
                    renderTable();
                });

                document.getElementById('viewCalendarBtn').addEventListener('click', () => {
                    document.querySelector('[data-tab="calendar"]').click();
                    renderCalendar();
                });

                // Refresh buttons
                document.getElementById('refreshBtn').addEventListener('click', () => {
                    updateStats();
                    renderTodayGrid();
                    renderWeekGrid();
                    renderTable();
                    renderCalendar();
                    showToast('Data refreshed!');
                });

                document.getElementById('dashboardRefreshBtn').addEventListener('click', () => {
                    updateStats();
                    renderTodayGrid();
                    renderWeekGrid();
                    showToast('Dashboard refreshed!');
                });

                // Export button
                document.getElementById('exportBtn').addEventListener('click', exportData);

                // Delete selected
                document.getElementById('deleteSelectedBtn').addEventListener('click', deleteSelected);

                // Calendar navigation
                document.getElementById('prevMonthBtn').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    renderCalendar();
                });

                document.getElementById('nextMonthBtn').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                });

                document.getElementById('currentMonthBtn').addEventListener('click', () => {
                    currentDate = new Date();
                    renderCalendar();
                });

                document.getElementById('calendarTodayBtn').addEventListener('click', () => {
                    currentDate = new Date();
                    selectedDate = new Date();
                    renderCalendar();
                });

                // Save appointment
                document.getElementById('scheduleSaveBtn').addEventListener('click', saveAppointment);

                // Cancel form
                document.getElementById('scheduleCancelBtn').addEventListener('click', () => {
                    if (confirm('Discard changes?')) {
                        resetForm();
                        document.querySelector('[data-tab="list"]').click();
                    }
                });

                // Modal close
                document.getElementById('closeModalBtn').addEventListener('click', () => {
                    document.getElementById('appointmentModal').style.display = 'none';
                });

                document.querySelectorAll('.modal-close-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.getElementById('appointmentModal').style.display = 'none';
                    });
                });

                // Modal delete button
                document.getElementById('modalDeleteBtn').addEventListener('click', () => {
                    if (selectedAppointmentId) {
                        deleteAppointment(selectedAppointmentId);
                        document.getElementById('appointmentModal').style.display = 'none';
                    }
                });

                // Modal edit button
                document.getElementById('modalEditBtn').addEventListener('click', () => {
                    if (selectedAppointmentId) {
                        document.getElementById('appointmentModal').style.display = 'none';
                        editAppointment(selectedAppointmentId);
                    }
                });

                // Search and filters
                document.getElementById('searchInput')?.addEventListener('keyup', renderTable);
                document.getElementById('filterType')?.addEventListener('change', renderTable);
                document.getElementById('filterStatus')?.addEventListener('change', renderTable);

                // Close modal on overlay click
                document.getElementById('appointmentModal').addEventListener('click', (e) => {
                    if (e.target === document.getElementById('appointmentModal')) {
                        document.getElementById('appointmentModal').style.display = 'none';
                    }
                });
            }

            // Initial render
            function init() {
                updateStats();
                renderTodayGrid();
                renderWeekGrid();
                renderTable();
                renderCalendar();
                initEventListeners();
                resetForm();
            }

            init();
        })();
    </script>
</body>
</html>