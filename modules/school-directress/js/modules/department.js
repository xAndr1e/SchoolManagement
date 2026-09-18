function initDepartmentManagement() {
    loadDepartmentTable();
}

async function loadDepartmentTable() {
    const tbody = document.querySelector('.department-table tbody');
    if (!tbody) return;

    const base = window.location.origin + '/sms';

    try {
        const res  = await fetch(`${base}/modules/school-directress/controllers/DepartmentController.php?action=get_departments`);
        const json = await res.json();

        if (!json.success || !json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="3" class="dept-empty">No departments found.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(dept => `
            <tr>
                <td>${esc(dept.department_name)}</td>
                <td>${esc(dept.head_name ?? '-')}</td>
                <td>${esc(dept.employee_count ?? '0')}</td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('loadDepartmentTable error:', err);
    }
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

document.addEventListener('DOMContentLoaded', initDepartmentManagement);
window.addEventListener('page:loaded', initDepartmentManagement);