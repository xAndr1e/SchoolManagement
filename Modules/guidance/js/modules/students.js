/**
 * students.js
 * Module: Student Profile Management (Guidance Office)
 *
 * Talks to StudentsController.php (server-side) via fetch(). This file
 * only handles DOM/UI behavior + AJAX calls — no direct DB access happens
 * here. StudentsController.php is a flat procedural router (switch on
 * ?action=), not a class — every response is shaped as:
 *   { success: bool, message?: string, data?: {...} }
 *
 * Expected endpoints on StudentsController.php:
 *   GET  StudentsController.php?action=list&search=&year_level=&section=&course=&risk=&status=&page=
 *   GET  StudentsController.php?action=profile&student_number=
 *   POST StudentsController.php?action=save_remark      { student_number, remarks }
 *   POST StudentsController.php?action=upload_document  (FormData: student_number, document_type, document_file)
 *   GET  StudentsController.php?action=export&search=&year_level=&...
 */

(function () {
    'use strict';

    const API_URL = '/sms/modules/guidance/controller/StudentsController.php';

    /* ---------------------------------------------------------
       Element references
    --------------------------------------------------------- */
    const els = {
        searchInput: document.getElementById('stdSearchInput'),
        filterYear: document.getElementById('stdFilterYear'),
        filterSection: document.getElementById('stdFilterSection'),
        filterCourse: document.getElementById('stdFilterCourse'),
        filterRisk: document.getElementById('stdFilterRisk'),
        filterStatus: document.getElementById('stdFilterStatus'),
        exportBtn: document.getElementById('stdExportBtn'),
        tableBody: document.querySelector('.std-table tbody'),
        pagination: document.querySelector('.std-pagination'),
        modalOverlay: document.getElementById('stdProfileModal'),
        closeModalBtn: document.getElementById('stdCloseModalBtn'),
        remarksForm: document.getElementById('stdRemarksForm'),
        uploadForm: document.getElementById('stdUploadForm'),
    };

    let currentPage = 1;
    let activeStudentNumber = null;

    /* ---------------------------------------------------------
       Init
    --------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        if (!els.tableBody) return; // fragment not loaded on this page

        bindFilterEvents();
        bindTableEvents();
        bindModalEvents();
        bindTabEvents();
        bindRemarksForm();
        bindUploadForm();
        bindPaginationEvents();
        bindExportButton();
    }

    /* ---------------------------------------------------------
       Filters / search (debounced)
    --------------------------------------------------------- */
    function bindFilterEvents() {
        let debounceTimer;

        const triggerSearch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                loadStudents();
            }, 350);
        };

        els.searchInput?.addEventListener('input', triggerSearch);
        [els.filterYear, els.filterSection, els.filterCourse, els.filterRisk, els.filterStatus]
            .forEach(select => select?.addEventListener('change', () => {
                currentPage = 1;
                loadStudents();
            }));
    }

    function getFilterParams(extra = {}) {
        return new URLSearchParams({
            search: els.searchInput?.value.trim() || '',
            year_level: els.filterYear?.value || '',
            section: els.filterSection?.value || '',
            course: els.filterCourse?.value || '',
            risk: els.filterRisk?.value || '',
            status: els.filterStatus?.value || '',
            page: currentPage,
            ...extra,
        });
    }

    /* ---------------------------------------------------------
       Load + render student list
    --------------------------------------------------------- */
    async function loadStudents() {
        setTableLoading(true);
        try {
            const params = getFilterParams();
            const res = await fetch(`${API_URL}?action=list&${params.toString()}`);
            const payload = await res.json();

            if (!payload.success) throw new Error(payload.message || 'Request failed');

            renderStudentRows(payload.data.students || []);
            renderPagination(payload.data.pagination || null);
        } catch (err) {
            console.error('Failed to load students:', err);
            renderTableError();
        } finally {
            setTableLoading(false);
        }
    }

    function setTableLoading(isLoading) {
        if (!els.tableBody) return;
        if (isLoading) {
            els.tableBody.innerHTML = `
                <tr><td colspan="7" class="std-table__empty">Loading students...</td></tr>
            `;
        }
    }

    function renderTableError() {
        if (!els.tableBody) return;
        els.tableBody.innerHTML = `
            <tr><td colspan="7" class="std-table__empty">Something went wrong loading students. Please try again.</td></tr>
        `;
    }

    function renderStudentRows(students) {
        if (!els.tableBody) return;

        if (!students.length) {
            els.tableBody.innerHTML = `
                <tr><td colspan="7" class="std-table__empty">No students found for the selected filters.</td></tr>
            `;
            return;
        }

        els.tableBody.innerHTML = students.map(rowTemplate).join('');
    }

    function rowTemplate(s) {
        return `
            <tr>
                <td>
                    <div class="std-table__student-cell">
                        <div class="std-avatar">${escapeHtml(s.initials || '')}</div>
                        <div>
                            <div class="std-table__name">${escapeHtml(s.name)}</div>
                            <div class="std-table__subtext">#${escapeHtml(s.student_number)}</div>
                        </div>
                    </div>
                </td>
                <td>Year ${escapeHtml(s.year_level)} - ${escapeHtml(s.section)}</td>
                <td>${escapeHtml(s.course)}</td>
                <td><span class="std-badge ${riskBadgeClass(s.risk_level)}">${escapeHtml(s.risk_level)}</span></td>
                <td><span class="std-badge ${statusBadgeClass(s.guidance_status)}">${escapeHtml(s.guidance_status)}</span></td>
                <td class="std-table__subtext">${escapeHtml(s.updated_at_display || s.updated_at)}</td>
                <td>
                    <button type="button"
                            class="std-btn std-btn--outline std-btn--sm std-view-profile-btn"
                            data-profile-id="${escapeHtml(s.profile_id)}"
                            data-student-number="${escapeHtml(s.student_number)}">
                        View Profile
                    </button>
                </td>
            </tr>
        `;
    }

    function riskBadgeClass(level) {
        if (level === 'High') return 'std-badge--risk-high';
        if (level === 'Moderate') return 'std-badge--risk-moderate';
        return 'std-badge--risk-low';
    }

    function statusBadgeClass(status) {
        if (status === 'Active') return 'std-badge--status-active';
        if (status === 'Monitoring') return 'std-badge--status-monitoring';
        return 'std-badge--status-closed';
    }

    /* ---------------------------------------------------------
       Pagination
    --------------------------------------------------------- */
    function bindPaginationEvents() {
        els.pagination?.addEventListener('click', (e) => {
            const pageBtn = e.target.closest('.std-pagination__page');
            if (!pageBtn || pageBtn.classList.contains('std-pagination__page--active')) return;

            const page = pageBtn.dataset.page;
            if (page) {
                currentPage = parseInt(page, 10);
                loadStudents();
            }
        });
    }

    function renderPagination(pagination) {
        if (!pagination || !els.pagination) return;

        const { total, page, totalPages, pageSize, count } = pagination;
        const summary = els.pagination.querySelector('span');
        if (summary) {
            const start = (page - 1) * pageSize + 1;
            const end = start + count - 1;
            summary.textContent = `Showing ${start}-${end} of ${total} students`;
        }

        const pagesWrap = els.pagination.querySelector('.std-pagination__pages');
        if (!pagesWrap) return;

        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="std-pagination__page ${i === page ? 'std-pagination__page--active' : ''}" data-page="${i}">${i}</button>`;
        }
        if (page < totalPages) {
            html += `<button class="std-pagination__page" data-page="${page + 1}">&rsaquo;</button>`;
        }
        pagesWrap.innerHTML = html;
    }

    /* ---------------------------------------------------------
       Table row actions -> open profile modal
    --------------------------------------------------------- */
    function bindTableEvents() {
        els.tableBody?.addEventListener('click', (e) => {
            const btn = e.target.closest('.std-view-profile-btn');
            if (!btn) return;
            openProfileModal(btn.dataset.studentNumber);
        });
    }

    /* ---------------------------------------------------------
       Modal open/close
    --------------------------------------------------------- */
    function bindModalEvents() {
        els.closeModalBtn?.addEventListener('click', closeProfileModal);
        els.modalOverlay?.addEventListener('click', (e) => {
            if (e.target === els.modalOverlay) closeProfileModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeProfileModal();
        });
    }

    async function openProfileModal(studentNumber) {
        if (!studentNumber || !els.modalOverlay) return;

        activeStudentNumber = studentNumber;
        els.modalOverlay.classList.add('std-modal-overlay--open');
        setActiveTab('overview');

        try {
            const res = await fetch(`${API_URL}?action=profile&student_number=${encodeURIComponent(studentNumber)}`);
            const payload = await res.json();

            if (!payload.success) throw new Error(payload.message || 'Request failed');

            populateProfileModal(payload.data);
        } catch (err) {
            console.error('Failed to load student profile:', err);
        }
    }

    function closeProfileModal() {
        els.modalOverlay?.classList.remove('std-modal-overlay--open');
        activeStudentNumber = null;
    }

    function populateProfileModal(profile) {
        if (!els.modalOverlay || !profile) return;

        const setText = (selector, value) => {
            const el = els.modalOverlay.querySelector(selector);
            if (el) el.textContent = value ?? '';
        };

        setText('.std-modal__title h2', profile.name);
        setText('.std-modal__subtitle', `#${profile.student_number} \u00B7 ${profile.course} \u00B7 Year ${profile.year_level} - ${profile.section}`);
        setText('#stdModalAvatar', initialsFromName(profile.name));

        const fields = els.modalOverlay.querySelectorAll('[data-panel="overview"] .std-profile-field__value');
        const overviewValues = [
            `#${profile.student_number}`,
            profile.course,
            `Year ${profile.year_level} - ${profile.section}`,
            profile.gender ? profile.gender.charAt(0).toUpperCase() + profile.gender.slice(1) : '',
            profile.birth_date,
            profile.email,
            profile.phone,
            profile.academic_status ? profile.academic_status.charAt(0).toUpperCase() + profile.academic_status.slice(1) : '',
        ];
        fields.forEach((field, i) => {
            if (overviewValues[i] !== undefined) field.textContent = overviewValues[i];
        });

        const riskBadge = els.modalOverlay.querySelector('[data-panel="overview"] .std-badge--risk-low, [data-panel="overview"] .std-badge--risk-moderate, [data-panel="overview"] .std-badge--risk-high');
        if (riskBadge) {
            riskBadge.className = `std-badge ${riskBadgeClass(profile.risk_level)}`;
            riskBadge.textContent = profile.risk_level;
        }

        const statusBadge = els.modalOverlay.querySelector('[data-panel="overview"] .std-badge--status-active, [data-panel="overview"] .std-badge--status-monitoring, [data-panel="overview"] .std-badge--status-closed');
        if (statusBadge) {
            statusBadge.className = `std-badge ${statusBadgeClass(profile.guidance_status)}`;
            statusBadge.textContent = profile.guidance_status;
        }

        renderHistoryList('counseling', profile.counseling_history);
        renderHistoryList('referrals', profile.referral_history);
        renderHistoryList('appointments', profile.appointment_history);
        renderHistoryList('incidents', profile.incident_history);
        renderRemarksHistory(profile.remarks_history);
        renderDocuments(profile.documents);
    }

    function renderHistoryList(panelName, items = []) {
        const panel = els.modalOverlay?.querySelector(`[data-panel="${panelName}"] .std-history-list`);
        if (!panel) return;

        if (!items.length) {
            panel.innerHTML = `<div class="std-table__empty">No records yet.</div>`;
            return;
        }

        panel.innerHTML = items.map(item => `
            <div class="std-history-item">
                <div class="std-history-item__date">${escapeHtml(item.date)}</div>
                <div class="std-history-item__body">
                    <div class="std-history-item__title">${escapeHtml(item.title)}</div>
                    <div class="std-history-item__desc">${escapeHtml(item.desc)}</div>
                </div>
                <span class="std-badge ${statusBadgeClass(item.status)} std-history-item__status">${escapeHtml(item.status)}</span>
            </div>
        `).join('');
    }

    function renderRemarksHistory(remarks = []) {
        const wrap = els.modalOverlay?.querySelector('.std-remarks-history');
        if (!wrap) return;

        wrap.innerHTML = remarks.map(r => `
            <div class="std-remark-item">
                <div class="std-remark-item__meta">${escapeHtml(r.date)} \u00B7 ${escapeHtml(r.by)}</div>
                <div class="std-remark-item__text">${escapeHtml(r.text)}</div>
            </div>
        `).join('');
    }

    function renderDocuments(documents = []) {
        const wrap = els.modalOverlay?.querySelector('.std-doc-list');
        if (!wrap) return;

        if (!documents.length) {
            wrap.innerHTML = `<div class="std-table__empty">No documents uploaded yet.</div>`;
            return;
        }

        wrap.innerHTML = documents.map(d => `
            <div class="std-doc-item">
                <div class="std-doc-item__info">
                    <i class="fa fa-file-pdf-o"></i>
                    <div>
                        <div class="std-doc-item__name">${escapeHtml(d.name)}</div>
                        <div class="std-doc-item__meta">${escapeHtml(d.type)} \u00B7 ${escapeHtml(d.date)}</div>
                    </div>
                </div>
                <button type="button" class="std-btn std-btn--ghost std-btn--sm std-download-doc-btn" data-doc-id="${escapeHtml(d.id ?? '')}">
                    Download
                </button>
            </div>
        `).join('');
    }

    /* ---------------------------------------------------------
       Tabs
    --------------------------------------------------------- */
    function bindTabEvents() {
        els.modalOverlay?.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.std-tab');
            if (!tabBtn) return;
            setActiveTab(tabBtn.dataset.tab);
        });
    }

    function setActiveTab(tabName) {
        if (!els.modalOverlay) return;

        els.modalOverlay.querySelectorAll('.std-tab').forEach(tab => {
            tab.classList.toggle('std-tab--active', tab.dataset.tab === tabName);
        });
        els.modalOverlay.querySelectorAll('.std-tab-panel').forEach(panel => {
            panel.classList.toggle('std-tab-panel--active', panel.dataset.panel === tabName);
        });
    }

    /* ---------------------------------------------------------
       Guidance remarks form
    --------------------------------------------------------- */
    function bindRemarksForm() {
        els.remarksForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeStudentNumber) return;

            const textarea = els.remarksForm.querySelector('textarea[name="remarks"]');
            const remarks = textarea?.value.trim();
            if (!remarks) return;

            const submitBtn = els.remarksForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Saving...');

            try {
                const res = await fetch(`${API_URL}?action=save_remark`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ student_number: activeStudentNumber, remarks }),
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                textarea.value = '';
                openProfileModal(activeStudentNumber); // refresh remarks history
            } catch (err) {
                console.error('Failed to save remark:', err);
                alert(err.message || 'Could not save the remark. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Save Remark');
            }
        });
    }

    /* ---------------------------------------------------------
       Document upload form
    --------------------------------------------------------- */
    function bindUploadForm() {
        els.uploadForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeStudentNumber) return;

            const fileInput = els.uploadForm.querySelector('input[type="file"]');
            if (!fileInput?.files?.length) {
                alert('Please choose a file to upload.');
                return;
            }

            const formData = new FormData(els.uploadForm);
            formData.append('student_number', activeStudentNumber);

            const submitBtn = els.uploadForm.querySelector('button[type="submit"]');
            toggleButtonLoading(submitBtn, true, 'Uploading...');

            try {
                const res = await fetch(`${API_URL}?action=upload_document`, {
                    method: 'POST',
                    body: formData,
                });
                const payload = await res.json();
                if (!payload.success) throw new Error(payload.message || 'Request failed');

                els.uploadForm.reset();
                openProfileModal(activeStudentNumber); // refresh document list
            } catch (err) {
                console.error('Failed to upload document:', err);
                alert(err.message || 'Could not upload the document. Please try again.');
            } finally {
                toggleButtonLoading(submitBtn, false, 'Upload');
            }
        });
    }

    /* ---------------------------------------------------------
       Export
    --------------------------------------------------------- */
    function bindExportButton() {
        els.exportBtn?.addEventListener('click', () => {
            const params = getFilterParams();
            window.location.href = `${API_URL}?action=export&${params.toString()}`;
        });
    }

    /* ---------------------------------------------------------
       Helpers
    --------------------------------------------------------- */
    function toggleButtonLoading(btn, isLoading, label) {
        if (!btn) return;
        btn.disabled = isLoading;
        btn.textContent = label;
    }

    function initialsFromName(name) {
        if (!name) return '';
        const parts = name.split(/[\s,]+/).filter(Boolean);
        return parts.slice(0, 2).map(p => p.charAt(0).toUpperCase()).join('');
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