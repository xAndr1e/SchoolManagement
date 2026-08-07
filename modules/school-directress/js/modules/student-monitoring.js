(function () {
    // ⚠️ Verify this matches the router's page key exactly — the Incidents
    // module uses 'incident' (singular) despite plural filenames, so don't
    // assume this one is 'student-monitoring' without checking Page.php.
    const PAGE_KEY  = 'student-monitoring';
    const PAGE_SIZE = 10;

    let els = {};
    let currentPage = 1;
    let lastMatched = [];

    function queryElements() {
        els = {
            search:   document.getElementById('smSearch'),
            status:   document.getElementById('smStatus'),
            course:   document.getElementById('smCourse'),
            year:     document.getElementById('smYear'),
            body:     document.getElementById('smBody'),
            showing:  document.getElementById('smShowing'),
            pageNav:  document.getElementById('smPageNav'),
            exportBtn:document.getElementById('smExportBtn'),
        };
    }

    /* ── Row expand / collapse ─────────────────────────────────────── */
    function getDetailRow(row) {
        const m = row.getAttribute('onclick')?.match(/'(smd-[^']+)'/);
        return m ? document.getElementById(m[1]) : null;
    }

    function collapseRow(row) {
        const dr = getDetailRow(row);
        if (dr) dr.classList.remove('sm--open');
        row.classList.remove('sm-row--expanded');
    }

    function toggle(tr, rid) {
        const detail = document.getElementById(rid);
        if (!detail) return;

        const isOpen = detail.classList.contains('sm--open');

        document.querySelectorAll('.sm-detail-row.sm--open').forEach(el => el.classList.remove('sm--open'));
        document.querySelectorAll('.sm-row.sm-row--expanded').forEach(el => el.classList.remove('sm-row--expanded'));

        if (!isOpen) {
            detail.classList.add('sm--open');
            tr.classList.add('sm-row--expanded');
        }
    }

    /* ── Filter matching (shared by pagination + CSV export) ─────────── */
    function currentFilters() {
        return {
            q:  els.search ? els.search.value.toLowerCase().trim() : '',
            st: els.status ? els.status.value : '',
            co: els.course ? els.course.value : '',
            yr: els.year   ? els.year.value   : '',
        };
    }

    function rowMatches(row, f) {
        return (!f.q  || row.dataset.name.includes(f.q) || row.dataset.snum.includes(f.q) || row.dataset.email.includes(f.q)) &&
               (!f.st || row.dataset.status === f.st) &&
               (!f.co || row.dataset.course === f.co) &&
               (!f.yr || row.dataset.year   === f.yr);
    }

    /* ── Filter + paginate ─────────────────────────────────────────── */
    function runFilter() {
        if (!els.body) return;

        const f       = currentFilters();
        const allRows = Array.from(els.body.querySelectorAll('.sm-row'));

        lastMatched = allRows.filter(row => {
            const ok = rowMatches(row, f);
            if (!ok) {
                row.style.display = 'none';
                collapseRow(row);
            }
            return ok;
        });

        currentPage = 1; // reset to page 1 whenever the filter changes
        paginate();
    }

    function paginate() {
        const total      = lastMatched.length;
        const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * PAGE_SIZE;
        const end   = start + PAGE_SIZE;

        lastMatched.forEach((row, idx) => {
            const onPage = idx >= start && idx < end;
            row.style.display = onPage ? '' : 'none';
            if (!onPage) collapseRow(row);
        });

        updateShowingText(total, start, end);
        renderPageButtons(totalPages);
    }

    function updateShowingText(total, start, end) {
        if (!els.showing) return;

        if (total === 0) {
            els.showing.innerHTML = 'No students found';
            return;
        }

        const from = start + 1;
        const to   = Math.min(end, total);
        els.showing.innerHTML = `Showing <strong>${from}-${to}</strong> of <strong>${total}</strong> students`;
    }

    function renderPageButtons(totalPages) {
        if (!els.pageNav) return;

        els.pageNav.innerHTML = '';

        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sm-page-btn' + (p === currentPage ? ' sm-page-btn--active' : '');
            btn.textContent = p;
            btn.addEventListener('click', () => {
                currentPage = p;
                paginate();
            });
            els.pageNav.appendChild(btn);
        }
    }

    /* ── CSV export (exports ALL filtered rows, not just the visible page) ── */
    function exportCSV() {
        if (!els.body) return;

        const f      = currentFilters();
        const rows   = Array.from(els.body.querySelectorAll('.sm-row')).filter(row => rowMatches(row, f));
        const lines  = [['Name', 'Student Number', 'Course', 'Status', 'Year Level', 'Sex', 'Email']];

        rows.forEach(r => {
            lines.push([
                r.querySelector('.sm-name')?.textContent.trim()  || '',
                r.querySelector('.sm-mono')?.textContent.trim()  || '',
                r.dataset.course || '',
                r.querySelector('.sm-badge')?.textContent.trim() || '',
                r.querySelector('.sm-year')?.textContent.trim()  || '',
                r.dataset.sex   || '',
                r.dataset.email || '',
            ]);
        });

        const csv  = lines.map(row => row.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const a    = document.createElement('a');

        a.href     = URL.createObjectURL(blob);
        a.download = `enrolled_students_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    /* ── Bind + init ────────────────────────────────────────────────── */
    function bindEvents() {
        [els.search, els.status, els.course, els.year].forEach(el => {
            if (!el) return;
            el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', runFilter);
        });

        if (els.exportBtn) els.exportBtn.addEventListener('click', exportCSV);
    }

    function init() {
        queryElements();
        if (!els.body) return; // this page fragment isn't the one currently loaded
        bindEvents();
        runFilter();
    }

    // Inline onclick="" handlers in the markup call this directly
    window.smToggle = toggle;

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('page:loaded', function (e) {
        if (e.detail && e.detail.page === PAGE_KEY) init();
    });
})();