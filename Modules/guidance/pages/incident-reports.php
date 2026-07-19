<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Reports · Tabbed Detail</title>
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

</body>
</html>