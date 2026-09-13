function initUserManagement() {
    const base = window.location.origin + '/sms';

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    async function loadEmployeeTable() {
        const tbody = document.getElementById('employee-table-body');
        if (!tbody) return;

        try {
            const res  = await fetch(`${base}/modules/school-directress/controllers/UserController.php?action=get_employees`);
            const json = await res.json();

            if (!json.success || !json.data?.length) {
                tbody.innerHTML = `<tr><td colspan="6">No employees found.</td></tr>`;
                return;
            }

            tbody.innerHTML = json.data.map(e => `
                <tr class="employee-row">
                    <td>${esc(e.first_name  ?? '—')}</td>
                    <td>${esc(e.middle_name ?? '—')}</td>
                    <td>${esc(e.last_name   ?? '—')}</td>
                    <td>${esc(e.department_name ?? '—')}</td>
                    <td>${esc(e.position_name   ?? '—')}</td>
                    <td>${esc(e.status ? e.status.charAt(0).toUpperCase() + e.status.slice(1).toLowerCase() : '—')}</td>
                </tr>
            `).join('');

            applyFilters();
        } catch (err) {
            console.error('loadEmployeeTable error:', err);
        }
    }

    const prevBtn          = document.getElementById('emp-prev-btn');
    const nextBtn          = document.getElementById('emp-next-btn');
    const pageInfo          = document.getElementById('emp-page-info');
    const filterDepartment  = document.getElementById('filter-department');
    const filterPosition    = document.getElementById('filter-position');
    const filterResetBtn    = document.getElementById('filter-reset-btn');

    const rowsPerPage = 10;
    let currentPage   = 1;
    let filteredRows  = [];

    function getColText(row, index) {
        return row.querySelectorAll('td')[index]?.textContent.trim() ?? '';
    }

    function applyFilters() {
        const allEmpRows = Array.from(document.querySelectorAll('.employee-row'));
        const dept = filterDepartment?.value.toLowerCase() ?? '';
        const pos  = filterPosition?.value.toLowerCase()  ?? '';

        filteredRows = allEmpRows.filter(row => {
            const rowDept = getColText(row, 3).toLowerCase();
            const rowPos  = getColText(row, 4).toLowerCase();
            return (!dept || rowDept === dept) && (!pos || rowPos === pos);
        });

        currentPage = 1;
        renderPage();
    }

    function renderPage() {
        const allEmpRows = Array.from(document.querySelectorAll('.employee-row'));
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
        const start      = (currentPage - 1) * rowsPerPage;
        const end        = start + rowsPerPage;

        allEmpRows.forEach(row => row.style.display = 'none');
        filteredRows.slice(start, end).forEach(row => row.style.display = '');

        if (pageInfo) pageInfo.textContent = filteredRows.length
            ? `Page ${currentPage} of ${totalPages}`
            : 'No results found';

        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages || filteredRows.length === 0;
    }

    filterDepartment?.addEventListener('change', () => {
        if (filterPosition) filterPosition.value = '';
        applyFilters();
    });
    filterPosition?.addEventListener('change', applyFilters);
    filterResetBtn?.addEventListener('click', () => {
        if (filterDepartment) filterDepartment.value = '';
        if (filterPosition)   filterPosition.value   = '';
        applyFilters();
    });

    prevBtn?.addEventListener('click', () => { currentPage--; renderPage(); });
    nextBtn?.addEventListener('click', () => { currentPage++; renderPage(); });

    loadEmployeeTable();
}

document.addEventListener('DOMContentLoaded', initUserManagement);
window.addEventListener('page:loaded', initUserManagement);