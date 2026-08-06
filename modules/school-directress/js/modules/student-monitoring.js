(function () {
    // ⚠️ Verify this matches the router's page key exactly — the Incidents
    // module uses 'incident' (singular) despite plural filenames, so don't
    // assume this one is 'student-monitoring' without checking Page.php.
    const PAGE_KEY = 'student-monitoring';

    let els = {};

    function queryElements() {
        els = {
            search: document.getElementById('epSearch'),
            status: document.getElementById('epStatus'),
            course: document.getElementById('epCourse'),
            year:   document.getElementById('epYear'),
            sex:    document.getElementById('epSex'), // not currently rendered in the markup — see note in view page
            count:  document.getElementById('epCount'),
            body:   document.getElementById('epBody'),
        };
    }

    /* ── Row expand / collapse ─────────────────────────────────────── */
    function toggle(tr, rid) {
        const detail = document.getElementById(rid);
        if (!detail) return;

        const isOpen = detail.classList.contains('ep--open');

        document.querySelectorAll('.ep-detail-row.ep--open').forEach(el => el.classList.remove('ep--open'));
        document.querySelectorAll('.ep-data-row.ep--expanded').forEach(el => el.classList.remove('ep--expanded'));

        if (!isOpen) {
            detail.classList.add('ep--open');
            tr.classList.add('ep--expanded');
        }
    }

    /* ── Filter ─────────────────────────────────────────────────────── */
    function runFilter() {
        if (!els.search || !els.body) return;

        const q  = els.search.value.toLowerCase().trim();
        const st = els.status.value;
        const co = els.course.value;
        const yr = els.year.value;
        const sx = els.sex ? els.sex.value : '';

        let n = 0;

        els.body.querySelectorAll('.ep-data-row').forEach(row => {
            const ok =
                (!q  || row.dataset.name.includes(q) || row.dataset.snum.includes(q) || row.dataset.email.includes(q)) &&
                (!st || row.dataset.status === st) &&
                (!co || row.dataset.course === co) &&
                (!yr || row.dataset.year   === yr) &&
                (!sx || row.dataset.sex    === sx);

            const m  = row.getAttribute('onclick')?.match(/'(epd-[^']+)'/);
            const dr = m ? document.getElementById(m[1]) : null;

            row.style.display = ok ? '' : 'none';
            if (dr && !ok) dr.classList.remove('ep--open');
            if (ok) n++;
        });

        if (els.count) els.count.textContent = n;
    }

    /* ── CSV export ─────────────────────────────────────────────────── */
    function exportCSV() {
        if (!els.body) return;

        const rows  = els.body.querySelectorAll('.ep-data-row');
        const lines = [['Name', 'Student Number', 'Course', 'Status', 'Year Level', 'Sex', 'Email']];

        rows.forEach(r => {
            if (r.style.display === 'none') return;

            lines.push([
                r.querySelector('.ep-sname')?.textContent.trim() || '',
                r.querySelector('.ep-mono')?.textContent.trim()  || '',
                r.querySelector('.ep-ccode')?.textContent.trim() || '',
                r.querySelector('.ep-badge')?.textContent.trim() || '',
                r.querySelector('.ep-year')?.textContent.trim()  || '',
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
        [els.search, els.status, els.course, els.year, els.sex].forEach(el => {
            if (!el) return; // fixes original bug: addEventListener on a missing #epSex threw and killed all filters
            el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', runFilter);
        });
    }

    function init() {
        queryElements();
        if (!els.body) return; // this page fragment isn't the one currently loaded
        bindEvents();
        runFilter();
    }

    // Inline onclick="" handlers in the markup call these directly
    window.epToggle    = toggle;
    window.epExportCSV = exportCSV;

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('page:loaded', function (e) {
        if (e.detail && e.detail.page === PAGE_KEY) init();
    });
})();