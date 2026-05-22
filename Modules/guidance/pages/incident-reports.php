<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Reports · Tabbed Detail</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            background: #f0f5fc;
            color: #15334a;
            padding: 2rem 1.8rem;
            min-height: 100vh;
        }
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* views */
        .view {
            display: none;
        }
        .view.active {
            display: block;
        }

        /* module header */
        .module-header {
            margin-bottom: 2rem;
        }
        .module-header h1 {
            font-size: 2.2rem;
            font-weight: 600;
            color: #11344c;
            border-left: 8px solid #2563eb;
            padding-left: 1.2rem;
        }
        .module-header p {
            color: #3e6182;
            margin-top: 0.3rem;
            font-size: 1rem;
            padding-left: 1.5rem;
        }

        /* stats grid */
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.4rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 28px;
            padding: 1.4rem 1.8rem;
            flex: 1 1 180px;
            min-width: 160px;
            box-shadow: 0 12px 28px -12px rgba(10, 55, 80, 0.2);
            display: flex;
            flex-direction: column;
            position: relative;
            border: 1px solid #ffffff;
        }
        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #50748e;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0f344c;
            line-height: 1.1;
        }
        .stat-icon {
            position: absolute;
            top: 1.2rem;
            right: 1.5rem;
            color: #3a7199;
            opacity: 0.3;
        }

        /* dashboard bottom */
        .dashboard-bottom {
            background: white;
            border-radius: 32px;
            padding: 1.8rem 2rem;
            box-shadow: 0 20px 32px -14px rgba(5, 45, 70, 0.15);
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
            flex-wrap: wrap;
        }
        .panel-header h2 {
            font-size: 1.4rem;
            color: #1a4d72;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .link-btn {
            background: none;
            border: none;
            color: #22638b;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .recent-list {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        .recent-item {
            background: #f8fcff;
            border-radius: 20px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border: 1px solid #e3effa;
        }
        .empty-state {
            text-align: center;
            padding: 2.5rem;
            color: #5b7e9e;
        }

        /* list view controls */
        .section-header {
            margin-bottom: 2rem;
        }
        .section-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #11344c;
        }
        .section-header p {
            color: #3e6182;
        }
        .list-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }
        .search-wrap {
            flex: 2 1 260px;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 60px;
            padding: 0.1rem 1.2rem;
            border: 2px solid #dde7f2;
        }
        .search-wrap svg {
            stroke: #3f6b91;
            margin-right: 0.5rem;
        }
        .search-wrap input {
            border: none;
            padding: 0.8rem 0;
            width: 100%;
            background: transparent;
            outline: none;
            font-size: 0.95rem;
        }
        .filter-wrap {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }
        .filter-wrap select {
            padding: 0.7rem 1.5rem;
            border-radius: 40px;
            border: 2px solid #dde7f2;
            background: white;
            font-weight: 500;
            color: #1f4a6e;
        }

        /* table */
        .table-wrap {
            background: white;
            border-radius: 28px;
            padding: 0 0 1.5rem 0;
            overflow-x: auto;
            box-shadow: 0 10px 24px -12px #0a3042;
        }
        .incident-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }
        .incident-table th {
            background: #e5f0fa;
            color: #0f3d5e;
            padding: 1.2rem 0.8rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        .incident-table td {
            padding: 1rem 0.8rem;
            border-bottom: 1px solid #e0ebf5;
            color: #1c405d;
        }
        .badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .badge.open { background: #e3f0fd; color: #14639e; }
        .badge.in_progress { background: #fff3d8; color: #b45b1c; }
        .badge.resolved { background: #d9f0e5; color: #1d6e4a; }
        .badge.closed { background: #ecf1f6; color: #3f546b; }
        .badge.low { background: #e2f0e2; color: #256f3a; }
        .badge.medium { background: #fff3d8; color: #b45b1c; }
        .badge.high { background: #ffe1db; color: #b33f2c; }
        .badge.critical { background: #fcd7d7; color: #b32626; }

        .action-btn {
            background: none;
            border: none;
            margin: 0 0.3rem;
            cursor: pointer;
            color: #3b6f9a;
            font-size: 1rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
        }
        .action-btn:hover { 
            background: #e3effa;
            color: #0c3b5e; 
        }

        /* form view */
        .form-card {
            background: #f9fcff;
            border-radius: 28px;
            padding: 1.8rem 2rem;
            margin-bottom: 1.8rem;
            border: 1px solid #dae8f5;
        }
        .form-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1d4d75;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .card-num {
            background: #2563eb;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
        }
        .form-group.full {
            grid-column: 1 / -1;
        }
        .form-group label {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #2f5d85;
            display: block;
            margin-bottom: 0.2rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid #d9e6f2;
            border-radius: 24px;
            background: white;
            font-size: 0.9rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #1f6e9f;
            outline: none;
        }
        .req { color: #c52a2a; }

        .file-drop {
            border: 2px dashed #b8d2ec;
            border-radius: 28px;
            padding: 1.5rem;
            text-align: center;
            background: #f2f9ff;
            cursor: pointer;
            position: relative;
        }
        .file-drop input {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            top:0; left:0;
            cursor: pointer;
        }
        .file-label { font-weight: 500; color: #216794; }
        .file-hint { display: block; font-size: 0.7rem; color: #6793b5; }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            position: relative;
            z-index: 10;
        }
        .btn-primary, .btn-ghost, .btn-outline-danger {
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            pointer-events: auto;
        }
        .btn-primary { background: #195e83; color: white; }
        .btn-primary:hover { background: #0e4463; }
        .btn-ghost { background: #e6f0f8; color: #1f5680; }
        .btn-ghost:hover { background: #d0e2f2; }
        .btn-outline-danger { border: 2px solid #b33f2c; background: white; color: #b33f2c; }
        .btn-outline-danger:hover { background: #fff1ef; }

        /* MODAL - FIXED Z-INDEX ISSUES */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1001;
            border: 3px solid #2563eb;
            overflow: hidden;
        }
        
        .modal-header {
            background: #1e4a76;
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2d68a4;
        }
        
        .modal-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 2.5rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 1;
            transition: 0.2s;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        /* Tab navigation */
        .modal-tabs {
            display: flex;
            background: #f0f7ff;
            padding: 0 2rem;
            border-bottom: 2px solid #bdd3e8;
        }
        
        .modal-tab {
            padding: 1.2rem 2rem 1rem 2rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e4a76;
            cursor: pointer;
            border-bottom: 4px solid transparent;
            margin-right: 1rem;
            transition: 0.1s;
        }
        
        .modal-tab.active {
            color: #0a2a44;
            border-bottom-color: #2563eb;
            background: transparent;
        }
        
        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            background: white;
            max-height: 50vh;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Detail rows - HIGH CONTRAST */
        .detail-row {
            display: flex;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #e0e9f0;
            align-items: flex-start;
        }
        
        .detail-label {
            width: 140px;
            flex-shrink: 0;
            font-weight: 700;
            color: #0a2a44;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .detail-value {
            flex: 1;
            color: #000000;
            font-size: 1.1rem;
            font-weight: 500;
            line-height: 1.5;
            background: #f9f9f9;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .detail-value.full-width {
            background: #f0f7ff;
            padding: 1.2rem;
            border-radius: 12px;
            border-left: 5px solid #2563eb;
            color: #000000;
            font-size: 1.1rem;
            margin-top: 0.3rem;
        }
        
        /* Modal footer */
        .modal-footer {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-top: 2px solid #dde7f0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        /* Make badges bigger in modal */
        .modal .badge {
            font-size: 1rem;
            padding: 0.5rem 1.5rem;
        }
        
        .new-incident-btn {
            background: #1f6185;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            margin-bottom: 1rem;
            cursor: pointer;
        }
        .new-incident-btn:hover {
            background: #0e4463;
        }

        /* Ensure form buttons are clickable */
        #view-form {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
<div class="app-container">

    <!-- module header (shared) -->
    <div class="module-header">
        <h1>Incident Reports</h1>
        <p>Track student incidents and disciplinary actions.</p>
    </div>

    <!-- ===== DASHBOARD / RECENT VIEW (home) ===== -->
    <section class="view active" id="view-dashboard">
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card stat-total">
                <div class="stat-label">Total Incidents</div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
            </div>
            <div class="stat-card stat-open">
                <div class="stat-label">Open</div>
                <div class="stat-value" id="statOpen">0</div>
                <div class="stat-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
            </div>
            <div class="stat-card stat-progress">
                <div class="stat-label">In Progress</div>
                <div class="stat-value" id="statProgress">0</div>
                <div class="stat-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                </div>
            </div>
            <div class="stat-card stat-resolved">
                <div class="stat-label">Resolved</div>
                <div class="stat-value" id="statResolved">0</div>
                <div class="stat-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="dashboard-bottom">
            <div class="panel-header">
                <h2>📋 Recent Incidents</h2>
                <button class="link-btn" id="viewAllBtn">View all →</button>
            </div>
            <div class="recent-list" id="recentList">
                <div class="empty-state">Loading incidents...</div>
            </div>
        </div>
    </section>

    <!-- ===== LIST VIEW (all incidents) ===== -->
    <section class="view" id="view-list">
        <div class="section-header">
            <h1>All Incidents</h1>
            <p>Manage and track every report</p>
            <button class="new-incident-btn" id="newIncidentBtn">➕ New Report</button>
        </div>

        <div class="list-controls">
            <div class="search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Search by title, reporter, location…">
            </div>
            <div class="filter-wrap">
                <select id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
                <select id="filterSeverity">
                    <option value="">All Severities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <table class="incident-table" id="incidentTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Title</th>
                        <th>Reporter</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="8" class="empty-state">Loading incidents...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ===== FORM VIEW (new/edit) ===== -->
    <section class="view" id="view-form">
        <div class="section-header">
            <h1 id="formTitle">New Incident Report</h1>
            <p id="formSubtitle">Fill in all required fields to log an incident</p>
        </div>

        <form id="incidentForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" id="incidentId" name="id" value="">

            <!-- Reporter Information -->
            <div class="form-card">
                <div class="form-card-title"><div class="card-num">01</div>Reporter Information</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="title">Incident Title <span class="req">*</span></label>
                        <input type="text" id="title" name="title" placeholder="Brief title describing the incident" required>
                    </div>
                    <div class="form-group">
                        <label for="reporter_name">Full Name <span class="req">*</span></label>
                        <input type="text" id="reporter_name" name="reporter_name" placeholder="John Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label for="reporter_email">Email Address <span class="req">*</span></label>
                        <input type="email" id="reporter_email" name="reporter_email" placeholder="john@company.com" required>
                    </div>
                    <div class="form-group">
                        <label for="reporter_phone">Phone Number</label>
                        <input type="tel" id="reporter_phone" name="reporter_phone" placeholder="+63 912 345 6789">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="e.g. Operations, IT, HR">
                    </div>
                </div>
            </div>

            <!-- Date, Time & Location -->
            <div class="form-card">
                <div class="form-card-title"><div class="card-num">02</div>Date, Time &amp; Location</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="incident_date">Incident Date <span class="req">*</span></label>
                        <input type="date" id="incident_date" name="incident_date" required>
                    </div>
                    <div class="form-group">
                        <label for="incident_time">Incident Time <span class="req">*</span></label>
                        <input type="time" id="incident_time" name="incident_time" required>
                    </div>
                    <div class="form-group full">
                        <label for="location">Location <span class="req">*</span></label>
                        <input type="text" id="location" name="location" placeholder="Building, floor, room, or full address" required>
                    </div>
                </div>
            </div>

            <!-- Description & Actions Taken -->
            <div class="form-card">
                <div class="form-card-title"><div class="card-num">03</div>Description &amp; Actions Taken</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="description">Incident Description <span class="req">*</span></label>
                        <textarea id="description" name="description" rows="5" placeholder="Provide a detailed description of what happened…" required></textarea>
                    </div>
                    <div class="form-group full">
                        <label for="actions_taken">Immediate Actions Taken</label>
                        <textarea id="actions_taken" name="actions_taken" rows="4" placeholder="Describe any immediate steps taken to address the incident…"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="severity">Severity Level <span class="req">*</span></label>
                        <select id="severity" name="severity" required>
                            <option value="low">🟢 Low</option>
                            <option value="medium" selected>🟡 Medium</option>
                            <option value="high">🟠 High</option>
                            <option value="critical">🔴 Critical</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="attachment">Evidence / Attachment</label>
                        <div class="file-drop" id="fileDrop">
                            <input type="file" id="attachment" name="attachment" accept="image/*,.pdf">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span class="file-label">Drop file here or <u>browse</u></span>
                            <span class="file-hint">JPG, PNG, PDF · Max 5MB</span>
                            <span class="file-name" id="fileName"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Resolution -->
            <div class="form-card">
                <div class="form-card-title"><div class="card-num">04</div>Status &amp; Resolution</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="open" selected>Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="resolution">Resolution Notes</label>
                        <textarea id="resolution" name="resolution" rows="3" placeholder="Describe how the incident was resolved or closed…"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-ghost" id="cancelBtn">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Submit Report
                </button>
            </div>
        </form>
    </section>

    <!-- ===== REDESIGNED MODAL - MAXIMUM VISIBILITY ===== -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal" id="detailModal">
            <div class="modal-header">
                <h2><span>🔍</span> Incident Details</h2>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            
            <!-- Tab navigation -->
            <div class="modal-tabs">
                <div class="modal-tab active" data-tab="tab1">Reporter & Basic</div>
                <div class="modal-tab" data-tab="tab2">Description & Actions</div>
                <div class="modal-tab" data-tab="tab3">Status & Resolution</div>
            </div>

            <div class="modal-body" id="modalBody">
                <!-- Tab 1: Reporter & Basic -->
                <div class="tab-pane active" id="tab1">
                    <div class="detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value" id="detail-id"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Title:</span>
                        <span class="detail-value" id="detail-title"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Reporter:</span>
                        <span class="detail-value" id="detail-reporter"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value" id="detail-email"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value" id="detail-phone"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value" id="detail-dept"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date/Time:</span>
                        <span class="detail-value" id="detail-datetime"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value" id="detail-location"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Severity:</span>
                        <span class="detail-value" id="detail-severity"></span>
                    </div>
                </div>

                <!-- Tab 2: Description & Actions -->
                <div class="tab-pane" id="tab2">
                    <div class="detail-row">
                        <span class="detail-label">Description:</span>
                    </div>
                    <div class="detail-value full-width" id="detail-description"></div>
                    <div style="margin: 1.5rem 0 0.5rem;">
                        <span class="detail-label">Actions Taken:</span>
                    </div>
                    <div class="detail-value full-width" id="detail-actions"></div>
                    <div style="margin-top:1.5rem;">
                        <span class="detail-label">Attachment:</span>
                        <span class="detail-value" id="detail-attachment"></span>
                    </div>
                </div>

                <!-- Tab 3: Status & Resolution -->
                <div class="tab-pane" id="tab3">
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" id="detail-status"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Resolution Notes:</span>
                    </div>
                    <div class="detail-value full-width" id="detail-resolution"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-ghost" id="modalClose2">Close</button>
                <button class="btn-outline-danger" id="modalDelete">Delete</button>
                <button class="btn-primary" id="modalEdit">Edit Report</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // ---------- INITIAL DATA ----------
        let incidents = [
            { 
                id: 1001, 
                title: 'Classroom disruption', 
                reporter_name: 'Angela Lopez', 
                reporter_email: 'a.lopez@school.edu', 
                reporter_phone: '0918123456', 
                department: 'SHS', 
                incident_date: '2025-03-10', 
                incident_time: '09:30', 
                location: 'Room 204', 
                description: 'Student repeatedly interrupting lesson, refusing to follow instructions, and being disrespectful to the teacher.', 
                actions_taken: 'Talked with student privately, notified parents, and scheduled a follow-up meeting.', 
                severity: 'medium', 
                status: 'in_progress', 
                resolution: '', 
                attachment: '' 
            },
            { 
                id: 1002, 
                title: 'Bullying incident', 
                reporter_name: 'Mark Santos', 
                reporter_email: 'msantos@school.edu', 
                reporter_phone: '0917987654', 
                department: 'Grade 10', 
                incident_date: '2025-03-12', 
                incident_time: '14:15', 
                location: 'Courtyard', 
                description: 'Verbal altercation between two students with name-calling and threats.', 
                actions_taken: 'Separated students, parents called, both parties sent to guidance office.', 
                severity: 'high', 
                status: 'open', 
                resolution: '', 
                attachment: '' 
            },
            { 
                id: 1003, 
                title: 'Lost ID card', 
                reporter_name: 'Jasmine Tan', 
                reporter_email: 'j.tan@school.edu', 
                reporter_phone: '0918222333', 
                department: 'Grade 11', 
                incident_date: '2025-03-09', 
                incident_time: '11:00', 
                location: 'Canteen', 
                description: 'Student lost her ID card somewhere in the canteen during lunch break.', 
                actions_taken: 'Reported to security, checked canteen, filed lost item report.', 
                severity: 'low', 
                status: 'resolved', 
                resolution: 'ID card was found and returned to student.', 
                attachment: '' 
            }
        ];
        let nextId = 1004;

        // DOM elements
        const dashboardView = document.getElementById('view-dashboard');
        const listView = document.getElementById('view-list');
        const formView = document.getElementById('view-form');
        const modalOverlay = document.getElementById('modalOverlay');
        const modalClose = document.getElementById('modalClose');
        const modalClose2 = document.getElementById('modalClose2');
        const modalDelete = document.getElementById('modalDelete');
        const modalEdit = document.getElementById('modalEdit');

        const statTotal = document.getElementById('statTotal');
        const statOpen = document.getElementById('statOpen');
        const statProgress = document.getElementById('statProgress');
        const statResolved = document.getElementById('statResolved');
        const recentList = document.getElementById('recentList');
        const tableBody = document.getElementById('tableBody');

        const incidentId = document.getElementById('incidentId');
        const formTitle = document.getElementById('formTitle');
        const formSubtitle = document.getElementById('formSubtitle');
        const cancelBtn = document.getElementById('cancelBtn');
        const submitBtn = document.getElementById('submitBtn');

        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const filterSeverity = document.getElementById('filterSeverity');

        const viewAllBtn = document.getElementById('viewAllBtn');
        const newIncidentBtn = document.getElementById('newIncidentBtn');
        const fileNameSpan = document.getElementById('fileName');

        // Tab switching inside modal
        const modalTabs = document.querySelectorAll('.modal-tab');
        const tabPanes = document.querySelectorAll('.tab-pane');

        // Detail spans
        const detailId = document.getElementById('detail-id');
        const detailTitle = document.getElementById('detail-title');
        const detailReporter = document.getElementById('detail-reporter');
        const detailEmail = document.getElementById('detail-email');
        const detailPhone = document.getElementById('detail-phone');
        const detailDept = document.getElementById('detail-dept');
        const detailDatetime = document.getElementById('detail-datetime');
        const detailLocation = document.getElementById('detail-location');
        const detailSeverity = document.getElementById('detail-severity');
        const detailDescription = document.getElementById('detail-description');
        const detailActions = document.getElementById('detail-actions');
        const detailAttachment = document.getElementById('detail-attachment');
        const detailStatus = document.getElementById('detail-status');
        const detailResolution = document.getElementById('detail-resolution');

        // ---------- helper functions ----------
        function showView(viewName) {
            dashboardView.classList.remove('active');
            listView.classList.remove('active');
            formView.classList.remove('active');
            if (viewName === 'dashboard') dashboardView.classList.add('active');
            else if (viewName === 'list') listView.classList.add('active');
            else if (viewName === 'form') formView.classList.add('active');
        }

        function updateStats() {
            const total = incidents.length;
            const open = incidents.filter(i => i.status === 'open').length;
            const prog = incidents.filter(i => i.status === 'in_progress').length;
            const resolved = incidents.filter(i => i.status === 'resolved' || i.status === 'closed').length;
            statTotal.innerText = total;
            statOpen.innerText = open;
            statProgress.innerText = prog;
            statResolved.innerText = resolved;
        }

        // Show most recent incidents first (by ID descending)
        function renderRecent() {
            if (!incidents.length) {
                recentList.innerHTML = '<div class="empty-state">No incidents reported</div>';
                return;
            }
            // Sort by ID descending (newest first) and take first 4
            const sorted = [...incidents].sort((a, b) => b.id - a.id).slice(0, 4);
            let html = '';
            sorted.forEach(inc => {
                html += `<div class="recent-item">
                    <strong>${inc.title}</strong> · ${inc.reporter_name} · 
                    <span class="badge ${inc.status}">${inc.status.replace('_',' ')}</span>
                    <span class="badge ${inc.severity}">${inc.severity}</span>
                </div>`;
            });
            recentList.innerHTML = html;
        }

        // Sort table by ID descending (newest first) after filtering
        function renderTable() {
            let filtered = [...incidents];
            const searchTerm = searchInput.value.toLowerCase();
            const statusFilter = filterStatus.value;
            const severityFilter = filterSeverity.value;

            if (searchTerm) {
                filtered = filtered.filter(i => 
                    i.title.toLowerCase().includes(searchTerm) ||
                    i.reporter_name.toLowerCase().includes(searchTerm) ||
                    i.location.toLowerCase().includes(searchTerm)
                );
            }
            if (statusFilter) filtered = filtered.filter(i => i.status === statusFilter);
            if (severityFilter) filtered = filtered.filter(i => i.severity === severityFilter);

            // Sort by newest first (ID descending)
            filtered.sort((a, b) => b.id - a.id);

            if (!filtered.length) {
                tableBody.innerHTML = `<tr><td colspan="8" class="empty-state">No incidents match</td></tr>`;
                return;
            }
            let rows = '';
            filtered.forEach(inc => {
                rows += `<tr>
                    <td>#${inc.id}</td>
                    <td>${inc.title}</td>
                    <td>${inc.reporter_name}</td>
                    <td>${inc.incident_date}</td>
                    <td>${inc.location}</td>
                    <td><span class="badge ${inc.severity}">${inc.severity}</span></td>
                    <td><span class="badge ${inc.status}">${inc.status.replace('_',' ')}</span></td>
                    <td>
                        <button class="action-btn view-detail" data-id="${inc.id}" title="View Details">👁️</button>
                        <button class="action-btn edit-incident" data-id="${inc.id}" title="Edit">✏️</button>
                        <button class="action-btn delete-incident" data-id="${inc.id}" title="Delete">🗑️</button>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = rows;

            // re-attach events
            document.querySelectorAll('.view-detail').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = parseInt(btn.dataset.id);
                    showDetailModal(id);
                });
            });
            document.querySelectorAll('.edit-incident').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = parseInt(btn.dataset.id);
                    editIncident(id);
                });
            });
            document.querySelectorAll('.delete-incident').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = parseInt(btn.dataset.id);
                    if (confirm('Delete this incident?')) {
                        incidents = incidents.filter(i => i.id !== id);
                        updateStats();
                        renderRecent();
                        renderTable();
                    }
                });
            });
        }

        function editIncident(id) {
            const inc = incidents.find(i => i.id === id);
            if (!inc) return;
            incidentId.value = inc.id;
            document.getElementById('title').value = inc.title || '';
            document.getElementById('reporter_name').value = inc.reporter_name || '';
            document.getElementById('reporter_email').value = inc.reporter_email || '';
            document.getElementById('reporter_phone').value = inc.reporter_phone || '';
            document.getElementById('department').value = inc.department || '';
            document.getElementById('incident_date').value = inc.incident_date || '';
            document.getElementById('incident_time').value = inc.incident_time || '';
            document.getElementById('location').value = inc.location || '';
            document.getElementById('description').value = inc.description || '';
            document.getElementById('actions_taken').value = inc.actions_taken || '';
            document.getElementById('severity').value = inc.severity || 'medium';
            document.getElementById('status').value = inc.status || 'open';
            document.getElementById('resolution').value = inc.resolution || '';
            fileNameSpan.innerText = inc.attachment ? inc.attachment : '';
            formTitle.innerText = 'Edit Incident Report';
            formSubtitle.innerText = 'Update the details below';
            showView('form');
        }

        function showDetailModal(id) {
            const inc = incidents.find(i => i.id === id);
            if (!inc) {
                alert('Incident not found');
                return;
            }

            // Populate all detail fields with clear text
            detailId.innerText = '#' + inc.id;
            detailTitle.innerText = inc.title || 'N/A';
            detailReporter.innerText = inc.reporter_name || 'N/A';
            detailEmail.innerText = inc.reporter_email || 'N/A';
            detailPhone.innerText = inc.reporter_phone || 'Not provided';
            detailDept.innerText = inc.department || 'Not specified';
            detailDatetime.innerText = (inc.incident_date || 'Unknown') + ' at ' + (inc.incident_time || 'Unknown');
            detailLocation.innerText = inc.location || 'N/A';
            
            // Severity with badge
            detailSeverity.innerHTML = `<span class="badge ${inc.severity}">${inc.severity}</span>`;
            
            // Description and actions
            detailDescription.innerText = inc.description || 'No description provided';
            detailActions.innerText = inc.actions_taken || 'No actions recorded';
            detailAttachment.innerText = inc.attachment || 'No attachment';
            
            // Status with badge
            detailStatus.innerHTML = `<span class="badge ${inc.status}">${inc.status.replace('_',' ')}</span>`;
            detailResolution.innerText = inc.resolution || 'No resolution notes yet';

            // Reset to first tab
            modalTabs.forEach(t => t.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            modalTabs[0].classList.add('active');
            document.getElementById('tab1').classList.add('active');

            // Show modal with proper z-index
            modalOverlay.style.display = 'flex';

            // Store current id for delete/edit
            let currentId = id;

            // Remove old listeners and add new ones
            const newDelete = modalDelete.cloneNode(true);
            const newEdit = modalEdit.cloneNode(true);
            modalDelete.parentNode.replaceChild(newDelete, modalDelete);
            modalEdit.parentNode.replaceChild(newEdit, modalEdit);

            newDelete.onclick = () => {
                if (confirm('Delete this incident?')) {
                    incidents = incidents.filter(i => i.id !== currentId);
                    updateStats();
                    renderRecent();
                    renderTable();
                    closeModal();
                }
            };

            newEdit.onclick = () => {
                closeModal();
                editIncident(currentId);
            };
        }

        function closeModal() { 
            modalOverlay.style.display = 'none';
        }

        function resetForm() {
            incidentId.value = '';
            document.getElementById('title').value = '';
            document.getElementById('reporter_name').value = '';
            document.getElementById('reporter_email').value = '';
            document.getElementById('reporter_phone').value = '';
            document.getElementById('department').value = '';
            document.getElementById('incident_date').value = new Date().toISOString().slice(0,10);
            document.getElementById('incident_time').value = '';
            document.getElementById('location').value = '';
            document.getElementById('description').value = '';
            document.getElementById('actions_taken').value = '';
            document.getElementById('severity').value = 'medium';
            document.getElementById('status').value = 'open';
            document.getElementById('resolution').value = '';
            fileNameSpan.innerText = '';
            formTitle.innerText = 'New Incident Report';
            formSubtitle.innerText = 'Fill in all required fields to log an incident';
        }

        // ---------- EVENT LISTENERS ----------
        document.getElementById('incidentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const required = ['title','reporter_name','reporter_email','incident_date','incident_time','location','description','severity'];
            for (let f of required) {
                if (!document.getElementById(f).value.trim()) {
                    alert('Please fill all required fields');
                    return;
                }
            }
            
            // Validate email format
            const email = document.getElementById('reporter_email').value.trim();
            if (!email.includes('@') || !email.includes('.')) {
                alert('Please enter a valid email address');
                return;
            }

            const incidentData = {
                id: incidentId.value ? parseInt(incidentId.value) : nextId++,
                title: document.getElementById('title').value.trim(),
                reporter_name: document.getElementById('reporter_name').value.trim(),
                reporter_email: email,
                reporter_phone: document.getElementById('reporter_phone').value,
                department: document.getElementById('department').value,
                incident_date: document.getElementById('incident_date').value,
                incident_time: document.getElementById('incident_time').value,
                location: document.getElementById('location').value.trim(),
                description: document.getElementById('description').value.trim(),
                actions_taken: document.getElementById('actions_taken').value,
                severity: document.getElementById('severity').value,
                status: document.getElementById('status').value,
                resolution: document.getElementById('resolution').value,
                attachment: fileNameSpan.innerText || '',
            };

            const editingId = incidentId.value ? parseInt(incidentId.value) : null;
            if (editingId) {
                const idx = incidents.findIndex(i => i.id === editingId);
                if (idx !== -1) {
                    incidents[idx] = incidentData;
                    alert('Incident updated successfully!');
                }
            } else {
                incidents.push(incidentData);
                alert('New incident reported successfully!');
            }
            updateStats();
            renderRecent();
            renderTable();
            resetForm();
            showView('list');
        });

        cancelBtn.addEventListener('click', () => {
            if (confirm('Discard changes?')) {
                resetForm();
                showView('list');
            }
        });

        document.getElementById('attachment').addEventListener('change', function(e) {
            if (this.files.length) {
                fileNameSpan.innerText = this.files[0].name;
            } else {
                fileNameSpan.innerText = '';
            }
        });

        viewAllBtn.addEventListener('click', () => {
            renderTable();
            showView('list');
        });

        newIncidentBtn.addEventListener('click', () => {
            resetForm();
            showView('form');
        });

        searchInput.addEventListener('keyup', renderTable);
        filterStatus.addEventListener('change', renderTable);
        filterSeverity.addEventListener('change', renderTable);

        // Tab switching
        modalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                modalTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const target = tab.getAttribute('data-tab');
                tabPanes.forEach(pane => pane.classList.remove('active'));
                document.getElementById(target).classList.add('active');
            });
        });

        // Modal close events
        modalClose.addEventListener('click', closeModal);
        modalClose2.addEventListener('click', closeModal);
        
        window.addEventListener('click', (e) => { 
            if (e.target === modalOverlay) closeModal(); 
        });

        // Prevent modal from closing when clicking inside it
        document.getElementById('detailModal').addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // ---------- INITIAL RENDER ----------
        updateStats();
        renderRecent();
        renderTable();
        // set default date
        document.getElementById('incident_date').value = new Date().toISOString().slice(0,10);
    })();
</script>
</body>
</html>