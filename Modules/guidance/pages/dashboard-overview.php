<!-- MODULE HEADER -->
<div class="module-header">
    <h1 id="moduleTitle">Guidance Dashboard</h1>
    <p id="moduleSub">Student counseling, cases, and intervention overview.</p>
</div>

<!-- MODULE CONTENT -->
<div class="module-container">

<div class="dash-page active" id="page-overview">

    <!-- Metric cards -->
    <div class="metrics-grid">

        <div class="metric-card blue">
            <div class="metric-icon"><i class="fa-solid fa-user-graduate"></i></div>
            <div class="metric-label">Total Students</div>
            <div class="metric-value">3,245</div>
            <div class="metric-change up">
                <i class="fa-solid fa-arrow-trend-up" style="font-size:10px;"></i>
                Enrolled this semester
            </div>
        </div>

        <div class="metric-card gold">
            <div class="metric-icon"><i class="fa-solid fa-folder-open"></i></div>
            <div class="metric-label">Active Counseling Cases</div>
            <div class="metric-value">124</div>
            <div class="metric-change down">
                <i class="fa-solid fa-arrow-up" style="font-size:10px;"></i>
                18 new cases this week
            </div>
        </div>

        <div class="metric-card green">
            <div class="metric-icon"><i class="fa-regular fa-calendar-check"></i></div>
            <div class="metric-label">Today's Sessions</div>
            <div class="metric-value">9</div>
            <div class="metric-change neutral">4 remaining</div>
        </div>

        <div class="metric-card red">
            <div class="metric-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="metric-label">Students At-Risk</div>
            <div class="metric-value">37</div>
            <div class="metric-change down">
                <i class="fa-solid fa-arrow-up" style="font-size:10px;"></i>
                Newly referred students
            </div>
        </div>

    </div>

    <div class="grid-2">

        <!-- Today's Counseling Sessions -->
        <div class="card">
            <div class="card-title">
                <i class="fa-regular fa-clock"></i> Today's Counseling Sessions
            </div>

            <div class="appt-item">
                <div class="appt-time">8:30 AM</div>
                <div class="avatar av-blue" style="width:32px;height:32px;">JM</div>
                <div style="flex:1;">
                    <div class="appt-name">John Michael Cruz</div>
                    <div class="appt-type">Academic Counseling</div>
                </div>
                <span class="badge badge-green">Completed</span>
            </div>

            <div class="appt-item">
                <div class="appt-time">9:30 AM</div>
                <div class="avatar av-coral" style="width:32px;height:32px;">AL</div>
                <div style="flex:1;">
                    <div class="appt-name">Angela Lopez</div>
                    <div class="appt-type">Behavioral Concern</div>
                </div>
                <span class="badge badge-green">Completed</span>
            </div>

            <div class="appt-item">
                <div class="appt-time">11:00 AM</div>
                <div class="avatar av-teal" style="width:32px;height:32px;">RS</div>
                <div style="flex:1;">
                    <div class="appt-name">Ryan Santos</div>
                    <div class="appt-type">Mental Health Counseling</div>
                </div>
                <span class="badge badge-amber">In Session</span>
            </div>

            <div class="appt-item">
                <div class="appt-time">1:00 PM</div>
                <div class="avatar av-amber" style="width:32px;height:32px;">KC</div>
                <div style="flex:1;">
                    <div class="appt-name">Karen Castro</div>
                    <div class="appt-type">Family Concern</div>
                </div>
                <span class="badge badge-gray">Upcoming</span>
            </div>

            <div class="appt-item">
                <div class="appt-time">2:30 PM</div>
                <div class="avatar av-purple" style="width:32px;height:32px;">DP</div>
                <div style="flex:1;">
                    <div class="appt-name">Daniel Perez</div>
                    <div class="appt-type">Career Counseling</div>
                </div>
                <span class="badge badge-gray">Upcoming</span>
            </div>
        </div>

        <!-- Priority Guidance Concerns -->
        <div class="card">
            <div class="card-title">
                <i class="fa-solid fa-circle-exclamation" style="color:#ef4444;"></i>
                Priority Guidance Concerns
            </div>

            <div class="concern-item">
                <div class="concern-dot dot-red"></div>
                <div>
                    <div class="concern-text">Students with failing grades in 3+ subjects</div>
                    <div class="concern-sub">Academic intervention required</div>
                </div>
            </div>

            <div class="concern-item">
                <div class="concern-dot dot-red"></div>
                <div>
                    <div class="concern-text">Chronic absenteeism reported</div>
                    <div class="concern-sub">Multiple students exceeding absence limit</div>
                </div>
            </div>

            <div class="concern-item">
                <div class="concern-dot dot-amber"></div>
                <div>
                    <div class="concern-text">Bullying incident report submitted</div>
                    <div class="concern-sub">Investigation ongoing</div>
                </div>
            </div>

            <div class="concern-item">
                <div class="concern-dot dot-amber"></div>
                <div>
                    <div class="concern-text">Students missing scholarship documents</div>
                    <div class="concern-sub">Deadline approaching</div>
                </div>
            </div>

            <div class="concern-item">
                <div class="concern-dot dot-blue"></div>
                <div>
                    <div class="concern-text">Career assessment forms incomplete</div>
                    <div class="concern-sub">Follow-up required</div>
                </div>
            </div>
        </div>

    </div>

    <div class="grid-3">

        <!-- Case Management -->
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-folder"></i> Counseling Case Status</div>

            <div class="stat-row"><span class="stat-label">Open Cases</span><span class="stat-val">124</span></div>
            <div class="stat-row"><span class="stat-label">Under Monitoring</span><span class="stat-val">59</span></div>
            <div class="stat-row"><span class="stat-label">Resolved This Month</span><span class="stat-val">42</span></div>
            <div class="stat-row"><span class="stat-label">External Referral</span><span class="stat-val">8</span></div>
        </div>

        <!-- Attendance Monitoring -->
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-calendar-days"></i> Attendance Monitoring</div>

            <div class="stat-row"><span class="stat-label">Overall Attendance Rate</span><span class="stat-val">93%</span></div>
            <div class="stat-row"><span class="stat-label">Chronically Absent</span><span class="stat-val">29</span></div>
            <div class="stat-row"><span class="stat-label">Watchlist</span><span class="stat-val">64</span></div>
            <div class="stat-row"><span class="stat-label">Perfect Attendance</span><span class="stat-val">402</span></div>
        </div>

        <!-- Counseling Sessions -->
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Counseling Sessions This Week</div>

            <div class="stat-row"><span class="stat-label">Academic Counseling</span><span class="stat-val">21</span></div>
            <div class="stat-row"><span class="stat-label">Behavioral Counseling</span><span class="stat-val">15</span></div>
            <div class="stat-row"><span class="stat-label">Mental Health Support</span><span class="stat-val">12</span></div>
            <div class="stat-row"><span class="stat-label">Career Counseling</span><span class="stat-val">9</span></div>
        </div>

    </div>

</div>