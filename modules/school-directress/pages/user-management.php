<?php
include_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../classes/Employee.php';
include __DIR__ . '/../classes/Department.php';
include __DIR__ . '/../classes/Position.php';
include __DIR__ . '/../../../auth/guard.php';

/*Employee Class*/
$employeeClass = new Employee();
$employees = $employeeClass->getEmployees();

/*Department Class*/
$departmentsClass = new Department();
$departments = $departmentsClass->getAllDepartments();

/*Position Class*/
$positionClass = new Position();
$positions = $positionClass->getAllPositions();
?>

<div class="module-header">
    <h1>User Management</h1>
    <p>View and monitor user accounts within the system.</p>
</div>
<div class="module-content">
    <div class="user-section-header">
        <h3>Employee List</h3>
            <div class="employee-filters">
                <select id="filter-department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['department_name']); ?>">
                            <?= htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-position">
                    <option value="">All Positions</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?= htmlspecialchars($pos['position_name']); ?>">
                            <?= htmlspecialchars($pos['position_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button id="filter-reset-btn">Reset</button>
            </div>
        </div>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="employee-table-body">
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $e): ?>
                                <tr class="employee-row">
                                    <td><?= htmlspecialchars($e['first_name'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($e['middle_name'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($e['last_name'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($e['department_name'] ?? $e['department'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($e['position_name'] ?? $e['position'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars(isset($e['status']) && $e['status'] !== '' ? ucfirst(strtolower($e['status'])) : '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No employees found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <div class="pagination-controls">
                <button id="emp-prev-btn" disabled>&laquo; Prev</button>
                <span id="emp-page-info"></span>
                <button id="emp-next-btn">Next &raquo;</button>
            </div>
    </div>