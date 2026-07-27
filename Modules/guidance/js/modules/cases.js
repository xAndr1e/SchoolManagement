/**
 * cases.js
 * Module: Case Management (Referral + Counseling)
 *
 * Talks to controller/CasesController.php (flat procedural router) via
 * fetch(). Every response is shaped as { success, message?, data? }.
 *
 * Endpoints used:
 *   GET  controller/CasesController.php?action=list&search=&status=&priority=&case_type=&counselor_id=&page=
 *   GET  controller/CasesController.php?action=details&case_id=
 *   GET  controller/CasesController.php?action=counselors
 *   POST controller/CasesController.php?action=create_case
 *   POST controller/CasesController.php?action=submit_referral
 *   POST controller/CasesController.php?action=review_referral
 *   POST controller/CasesController.php?action=update_status
 *   POST controller/CasesController.php?action=assign_counselor
 *   POST controller/CasesController.php?action=set_priority
 *   POST controller/CasesController.php?action=record_session
 *   POST controller/CasesController.php?action=update_session
 */

(function () {
    'use strict';

    const API_URL = '/sms/modules/guidance/controller/CasesController.php';

    const els = {
        searchInput: document.getElementById('caseSearchInput'),
        filterStatus: document.getElementById('caseFilterStatus'),
        filterPriority: document.getElementById('caseFilterPriority'),
        filterType: document.getElementById('caseFilterType'),
        filterCounselor: document.getElementById('caseFilterCounselor'),
        tableBody: document.querySelector('.case-table tbody'),
        pagination: document.querySelector('.case-pagination'),

        createBtn: document.getElementById('caseCreateBtn'),
        createModal: document.getElementById('caseCreateModal'),
        createForm: document.getElementById('caseCreateForm'),
        createType: document.getElementById('caseCreateType'),
        createCloseBtn: document.getElementById('caseCreateCloseBtn'),
        createCancelBtn: document.getElementById('caseCreateCancelBtn'),

        overlay: document.getElementById('caseOverlay'),
        drawer: document.getElementById('caseDrawer'),
        drawerCloseBtn: document.getElementById('caseDrawerCloseBtn'),
        drawerNumber: document.getElementById('caseDrawerNumber'),
        drawerTitle: document.getElementById('caseDrawerTitle'),
        drawerBadges: document.getElementById('caseDrawerBadges'),
        referralTabBtn: document.getElementById('caseReferralTabBtn'),
        referralContent: document.getElementById('caseReferralContent'),
        sessionList: document.getElementById('caseSessionList'),

        assignCounselorBtn: document.getElementById('caseAssignCounselorBtn'),
        updateStatusBtn: document.getElementById('caseUpdateStatusBtn'),
        setPriorityBtn: document.getElementById('caseSetPriorityBtn'),
        recordSessionBtn: document.getElementById('caseRecordSessionBtn'),

        sessionModal: document.getElementById('caseSessionModal'),
        sessionModalTitle: document.getElementById('caseSessionModalTitle'),
        sessionForm: document.getElementById('caseSessionForm'),
        sessionCloseBtn: document.getElementById('caseSessionCloseBtn'),
        sessionCancelBtn: document.getElementById('caseSessionCancelBtn'),

        quickActionModal: document.getElementById('caseQuickActionModal'),
        quickActionTitle: document.getElementById('caseQuickActionTitle'),
        quickActionBody: document.getElementById('caseQuickActionBody'),
        quickActionForm: document.getElementById('caseQuickActionForm'),
        quickActionCloseBtn: document.getElementById('caseQuickActionCloseBtn'),
        quickActionCancelBtn: document.getElementById('caseQuickActionCancelBtn'),
    };

    let currentPage = 1;
    let activeCaseId = null;
    let activeCaseType = null;
    let quickActionMode = null; // 'assign_counselor' | 'update_status' | 'set_priority'

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        if (!els.tableBody) return;

        bindFilterEvents();
        bindTableEvents();
        bindPaginationEvents();
        bindCreateModal();
        bindDrawerEvents();
        bindTabEvents();
        bindSessionModal();
        bindQuickActionModal();
    }

    /* ---------------------------------------------------------
       Filters / search
    --------------------------------------------------------- */
    function bindFilterEvents() {
        let debounceTimer;
        const triggerSearch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { currentPage = 1; loadCases(); }, 350);
        };

        els.searchInput?.addEventListener('input', triggerSearch);
        [els.filterStatus, els.filterPriority, els.filterType, els.filterCounselor]
            .forEach(sel => sel?.addEventListener('change', () => { currentPage = 1; loadCases(); }));
    }

    function getFilterParams(extra = {}) {
        return new URLSearchParams({
            search: els.searchInput?.value.trim() || '',
            status: els.filterStatus?.value || '',
            priority: els.filterPriority?.value || '',
            case_type: els.filterType?.value || '',
            counselor_id: els.filterCounselor?.value || '',
            page: currentPage,
            ...extra,
        });
    }

    async function loadCases() {
        setTableLoading(true);
        try {
            const params = getFilterParams();
            const res = await fetch(`${API_URL}?action=list&${params.toString()}`);
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');

            renderCaseRows(payload.data.cases || []);
            renderPagination(payload.data.pagination || null);
        } catch (err) {
            console.error('Failed to load cases:', err);
            if (els.tableBody) {
                els.tableBody.innerHTML = `<tr><td colspan="7" class="case-table__empty">Something went wrong loading cases. Please try again.</td></tr>`;
            }
        } finally {
            setTableLoading(false);
        }
    }

    function setTableLoading(isLoading) {
        if (isLoading && els.tableBody) {
            els.tableBody.innerHTML = `<tr><td colspan="7" class="case-table__empty">Loading cases...</td></tr>`;
        }
    }

    function renderCaseRows(cases) {
        if (!els.tableBody) return;
        if (!cases.length) {
            els.tableBody.innerHTML = `<tr><td colspan="7" class="case-table__empty">No cases found for the selected filters.</td></tr>`;
            return;
        }
        els.tableBody.innerHTML = cases.map(rowTemplate).join('');
    }

    function rowTemplate(c) {
        return `
            <tr class="case-row" data-case-id="${escapeHtml(c.case_id)}">
                <td class="case-number">#${escapeHtml(c.case_number)}</td>
                <td>
                    <div class="case-student-name">${escapeHtml(c.student_name)}</div>
                    <div class="case-student-sub">#${escapeHtml(c.student_number)}</div>
                </td>
                <td>${escapeHtml(c.case_type)}</td>
                <td><span class="case-badge ${priorityBadgeClass(c.priority)}">${escapeHtml(c.priority)}</span></td>
                <td><span class="case-badge ${statusBadgeClass(c.status)}">${escapeHtml(c.status)}</span></td>
                <td>${escapeHtml(c.counselor_name)}</td>
                <td>${escapeHtml(c.opened_at_display || c.opened_at)}</td>
            </tr>
        `;
    }

    function statusBadgeClass(status) {
        if (status === 'Open') return 'case-badge--status-open';
        if (status === 'In Progress') return 'case-badge--status-progress';
        return 'case-badge--status-closed';
    }

    function priorityBadgeClass(priority) {
        if (priority === 'Critical') return 'case-badge--priority-critical';
        if (priority === 'High') return 'case-badge--priority-high';
        if (priority === 'Medium') return 'case-badge--priority-medium';
        return 'case-badge--priority-low';
    }

    function referralBadgeClass(status) {
        if (status === 'Accepted') return 'case-badge--referral-accepted';
        if (status === 'Rejected') return 'case-badge--referral-rejected';
        return 'case-badge--referral-pending';
    }

    /* ---------------------------------------------------------
       Pagination
    --------------------------------------------------------- */
    function bindPaginationEvents() {
        els.pagination?.addEventListener('click', (e) => {
            const btn = e.target.closest('.case-pagination__page');
            if (!btn || btn.classList.contains('case-pagination__page--active')) return;
            const page = btn.dataset.page;
            if (page) { currentPage = parseInt(page, 10); loadCases(); }
        });
    }

    function renderPagination(pagination) {
        if (!pagination || !els.pagination) return;
        const { total, page, totalPages, pageSize, count } = pagination;

        const summary = els.pagination.querySelector('span');
        if (summary) {
            if (total > 0) {
                const start = (page - 1) * pageSize + 1;
                summary.textContent = `Showing ${start}-${start + count - 1} of ${total} cases`;
            } else {
                summary.textContent = 'No cases found';
            }
        }

        const pagesWrap = els.pagination.querySelector('.case-pagination__pages');
        if (!pagesWrap) return;
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="case-pagination__page ${i === page ? 'case-pagination__page--active' : ''}" data-page="${i}">${i}</button>`;
        }
        if (page < totalPages) {
            html += `<button class="case-pagination__page" data-page="${page + 1}">&rsaquo;</button>`;
        }
        pagesWrap.innerHTML = html;
    }

    /* ---------------------------------------------------------
       Table row -> open drawer
    --------------------------------------------------------- */
    function bindTableEvents() {
        els.tableBody?.addEventListener('click', (e) => {
            const row = e.target.closest('.case-row');
            if (!row) return;
            openDrawer(row.dataset.caseId);
        });
    }

    /* ---------------------------------------------------------
       Create Case modal
    --------------------------------------------------------- */
    function bindCreateModal() {
        els.createBtn?.addEventListener('click', () => {
            els.createForm?.reset();
            toggleReferralFields(false);
            els.createModal?.classList.add('case-modal-overlay--open');
        });
        els.createCloseBtn?.addEventListener('click', closeCreateModal);
        els.createCancelBtn?.addEventListener('click', closeCreateModal);

        els.createType?.addEventListener('change', () => {
            toggleReferralFields(els.createType.value === 'Referral');
        });

        els.createForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(els.createForm);
            const isReferral = formData.get('case_type') === 'Referral';
            const action = isReferral ? 'submit_referral' : 'create_case';

            const body = Object.fromEntries(formData.entries());

            const submitBtn = els.createForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Saving...');

            try {
                const res = await fetch(`${API_URL}?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                closeCreateModal();
                loadCases();
            } catch (err) {
                console.error('Failed to create case:', err);
                alert(err.message || 'Could not create the case. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Create Case');
            }
        });
    }

    function toggleReferralFields(show) {
        ['caseReferralFields1', 'caseReferralFields2', 'caseReferralFields3'].forEach(id => {
            document.getElementById(id)?.classList.toggle('case-form-group--hidden', !show);
        });
        const referredBy = els.createForm?.querySelector('[name="referred_by"]');
        const referralSource = els.createForm?.querySelector('[name="referral_source"]');
        const referralReason = els.createForm?.querySelector('[name="referral_reason"]');
        [referredBy, referralSource, referralReason].forEach(f => { if (f) f.required = show; });
    }

    function closeCreateModal() {
        els.createModal?.classList.remove('case-modal-overlay--open');
    }

    /* ---------------------------------------------------------
       Drawer open/close + tabs
    --------------------------------------------------------- */
    function bindDrawerEvents() {
        els.drawerCloseBtn?.addEventListener('click', closeDrawer);
        els.overlay?.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });
    }

    async function openDrawer(caseId) {
        if (!caseId) return;
        activeCaseId = caseId;

        els.overlay?.classList.add('case-overlay--open');
        els.drawer?.classList.add('case-drawer--open');
        setTab('overview');

        try {
            const res = await fetch(`${API_URL}?action=details&case_id=${encodeURIComponent(caseId)}`);
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');
            populateDrawer(payload.data);
        } catch (err) {
            console.error('Failed to load case details:', err);
        }
    }

    function closeDrawer() {
        els.overlay?.classList.remove('case-overlay--open');
        els.drawer?.classList.remove('case-drawer--open');
        activeCaseId = null;
        activeCaseType = null;
    }

    function populateDrawer(data) {
        activeCaseType = data.case_type;

        if (els.drawerNumber) els.drawerNumber.textContent = `#${data.case_number}`;
        if (els.drawerTitle) els.drawerTitle.textContent = `${data.student_name} — #${data.student_number}`;
        if (els.drawerBadges) {
            els.drawerBadges.innerHTML = `
                <span class="case-badge ${priorityBadgeClass(data.priority)}">${escapeHtml(data.priority)} Priority</span>
                <span class="case-badge ${statusBadgeClass(data.status)}">${escapeHtml(data.status)}</span>
            `;
        }

        document.querySelectorAll('[data-panel="overview"] [data-field]').forEach(el => {
            const field = el.dataset.field;
            el.textContent = data[field] ?? '—';
        });

        // Referral tab only relevant for case_type = 'Referral'
        const showReferralTab = data.case_type === 'Referral';
        els.referralTabBtn?.classList.toggle('case-form-group--hidden', !showReferralTab);
        if (els.referralTabBtn) els.referralTabBtn.style.display = showReferralTab ? '' : 'none';

        if (showReferralTab && data.referral) {
            const r = data.referral;
            els.referralContent.innerHTML = `
                <div class="case-field-grid">
                    <div><div class="case-field-label">Referred By</div><div class="case-field-value">${escapeHtml(r.referred_by_name)}</div></div>
                    <div><div class="case-field-label">Source</div><div class="case-field-value">${escapeHtml(r.referral_source)}</div></div>
                    <div><div class="case-field-label">Referral Date</div><div class="case-field-value">${escapeHtml(r.referral_date_display)}</div></div>
                    <div><div class="case-field-label">Referral Status</div><div class="case-field-value"><span class="case-badge ${referralBadgeClass(r.referral_status)}">${escapeHtml(r.referral_status)}</span></div></div>
                </div>
                <div class="case-summary-label">Reason</div>
                <div class="case-summary-box">${escapeHtml(r.referral_reason)}</div>
                ${r.referral_status === 'Pending' ? `
                    <div class="case-quick-actions" style="margin-top:16px;">
                        <button type="button" class="case-btn case-btn--sm" id="caseAcceptReferralBtn">Accept Referral</button>
                        <button type="button" class="case-btn case-btn--ghost case-btn--sm" id="caseRejectReferralBtn">Reject Referral</button>
                    </div>
                ` : ''}
            `;
            document.getElementById('caseAcceptReferralBtn')?.addEventListener('click', () => reviewReferral('accept'));
            document.getElementById('caseRejectReferralBtn')?.addEventListener('click', () => reviewReferral('reject'));
        } else if (showReferralTab) {
            els.referralContent.innerHTML = `<div class="case-empty-state">No referral record found.</div>`;
        }

        renderSessions(data.sessions || []);
    }

    function renderSessions(sessions) {
        if (!els.sessionList) return;
        if (!sessions.length) {
            els.sessionList.innerHTML = `<div class="case-empty-state">No counseling sessions recorded yet.</div>`;
            return;
        }
        els.sessionList.innerHTML = sessions.map(s => `
            <div class="case-session-item">
                <div class="case-session-item__top">
                    <span class="case-session-item__date">${escapeHtml(s.session_date_display)}</span>
                    <span class="case-badge case-badge--status-progress">${escapeHtml(s.session_type)}</span>
                </div>
                <div class="case-session-item__type">${s.duration_minutes ? escapeHtml(s.duration_minutes) + ' min session' : 'Session'}</div>
                ${s.session_notes ? `<div class="case-session-item__notes">${escapeHtml(s.session_notes)}</div>` : ''}
                ${s.recommendations ? `<div class="case-session-item__recommendations"><strong>Recommendations:</strong> ${escapeHtml(s.recommendations)}</div>` : ''}
                ${s.next_session_display ? `<div class="case-session-item__next">Next session: ${escapeHtml(s.next_session_display)}</div>` : ''}
                <div class="case-session-item__actions">
                    <button type="button" class="case-btn case-btn--ghost case-btn--sm case-edit-session-btn" data-session='${escapeHtml(JSON.stringify(s))}'>Edit</button>
                </div>
            </div>
        `).join('');

        els.sessionList.querySelectorAll('.case-edit-session-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const session = JSON.parse(btn.dataset.session.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&'));
                openSessionModal(session);
            });
        });
    }

    async function reviewReferral(decision) {
        if (!activeCaseId) return;
        try {
            const res = await fetch(`${API_URL}?action=review_referral`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ case_id: activeCaseId, decision }),
            });
            const payload = await res.json();
            if (!payload.success) throw new Error(payload.message || 'Request failed');

            openDrawer(activeCaseId); // refresh
            loadCases(); // status may have changed in the list too
        } catch (err) {
            console.error('Failed to review referral:', err);
            alert(err.message || 'Could not update the referral. Please try again.');
        }
    }

    /* ---------------------------------------------------------
       Tabs
    --------------------------------------------------------- */
    function bindTabEvents() {
        document.querySelectorAll('.case-tab').forEach(tab => {
            tab.addEventListener('click', () => setTab(tab.dataset.tab));
        });
    }

    function setTab(tabName) {
        document.querySelectorAll('.case-tab').forEach(t => t.classList.toggle('case-tab--active', t.dataset.tab === tabName));
        document.querySelectorAll('.case-tab-panel').forEach(p => p.classList.toggle('case-tab-panel--active', p.dataset.panel === tabName));
    }

    /* ---------------------------------------------------------
       Record / Edit Session modal
    --------------------------------------------------------- */
    function bindSessionModal() {
        els.recordSessionBtn?.addEventListener('click', () => openSessionModal(null));
        els.sessionCloseBtn?.addEventListener('click', closeSessionModal);
        els.sessionCancelBtn?.addEventListener('click', closeSessionModal);

        els.sessionForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeCaseId) return;

            const formData = new FormData(els.sessionForm);
            const sessionId = formData.get('session_id');
            const isEdit = !!sessionId;
            const action = isEdit ? 'update_session' : 'record_session';

            const body = Object.fromEntries(formData.entries());
            body.case_id = activeCaseId;
            if (!isEdit) delete body.session_id;

            const submitBtn = els.sessionForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Saving...');

            try {
                const res = await fetch(`${API_URL}?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                closeSessionModal();
                openDrawer(activeCaseId); // refresh sessions list
            } catch (err) {
                console.error('Failed to save session:', err);
                alert(err.message || 'Could not save the session. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Save Session');
            }
        });
    }

    function openSessionModal(session) {
        els.sessionForm?.reset();
        const isEdit = !!session;

        if (els.sessionModalTitle) els.sessionModalTitle.textContent = isEdit ? 'Edit Counseling Session' : 'Record Counseling Session';

        if (isEdit) {
            els.sessionForm.querySelector('[name="session_id"]').value = session.session_id;
            els.sessionForm.querySelector('[name="session_type"]').value = session.session_type;
            els.sessionForm.querySelector('[name="duration_minutes"]').value = session.duration_minutes || '';
            els.sessionForm.querySelector('[name="session_notes"]').value = session.session_notes || '';
            els.sessionForm.querySelector('[name="recommendations"]').value = session.recommendations || '';
            if (session.next_session) {
                els.sessionForm.querySelector('[name="next_session"]').value = toDatetimeLocal(session.next_session);
            }
        }

        els.sessionModal?.classList.add('case-modal-overlay--open');
    }

    function closeSessionModal() {
        els.sessionModal?.classList.remove('case-modal-overlay--open');
    }

    function toDatetimeLocal(value) {
        const d = new Date(value);
        if (isNaN(d.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    /* ---------------------------------------------------------
       Quick actions: Assign Counselor / Update Status / Set Priority
    --------------------------------------------------------- */
    function bindQuickActionModal() {
        els.assignCounselorBtn?.addEventListener('click', () => openQuickAction('assign_counselor'));
        els.updateStatusBtn?.addEventListener('click', () => openQuickAction('update_status'));
        els.setPriorityBtn?.addEventListener('click', () => openQuickAction('set_priority'));

        els.quickActionCloseBtn?.addEventListener('click', closeQuickAction);
        els.quickActionCancelBtn?.addEventListener('click', closeQuickAction);

        els.quickActionForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeCaseId || !quickActionMode) return;

            const formData = new FormData(els.quickActionForm);
            const body = Object.fromEntries(formData.entries());
            body.case_id = activeCaseId;

            const submitBtn = els.quickActionForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Saving...');

            try {
                const res = await fetch(`${API_URL}?action=${quickActionMode}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                closeQuickAction();
                openDrawer(activeCaseId);
                loadCases();
            } catch (err) {
                console.error('Failed to save:', err);
                alert(err.message || 'Could not save. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Save');
            }
        });
    }

    async function openQuickAction(mode) {
        quickActionMode = mode;

        if (mode === 'assign_counselor') {
            if (els.quickActionTitle) els.quickActionTitle.textContent = 'Assign Counselor';
            els.quickActionBody.innerHTML = `<div class="case-form-group"><label>Counselor</label><select name="counselor_id" required id="caseQuickCounselorSelect"><option value="">Loading...</option></select></div>`;

            try {
                const res = await fetch(`${API_URL}?action=counselors`);
                const payload = await res.json();
                const select = document.getElementById('caseQuickCounselorSelect');
                if (payload.success && select) {
                    select.innerHTML = '<option value="">Select counselor</option>' +
                        payload.data.map(c => `<option value="${escapeHtml(c.employee_id)}">${escapeHtml(c.name)} (${escapeHtml(c.position_name)})</option>`).join('');
                }
            } catch (err) {
                console.error('Failed to load counselors:', err);
            }
        } else if (mode === 'update_status') {
            if (els.quickActionTitle) els.quickActionTitle.textContent = 'Update Case Status';
            els.quickActionBody.innerHTML = `
                <div class="case-form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
            `;
        } else if (mode === 'set_priority') {
            if (els.quickActionTitle) els.quickActionTitle.textContent = 'Set Case Priority';
            els.quickActionBody.innerHTML = `
                <div class="case-form-group">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            `;
        }

        els.quickActionModal?.classList.add('case-modal-overlay--open');
    }

    function closeQuickAction() {
        els.quickActionModal?.classList.remove('case-modal-overlay--open');
        quickActionMode = null;
    }

    /* ---------------------------------------------------------
       Helpers
    --------------------------------------------------------- */
    function toggleButtonLoading(btn, isLoading, label) {
        if (!btn) return;
        btn.disabled = isLoading;
        btn.textContent = label;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();