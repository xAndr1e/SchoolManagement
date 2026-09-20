<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/User.php';
include_once __DIR__ . '/../classes/Department.php';

$user = new User();
$user_info = $user->userSession();

$department = new Department();
$departmentsWithDetails = $department->getDepartmentsWithDetails();
?>

<div class="module-header">
    <h1>Department & Unit Management</h1>
    <p>View departments, department heads, and staff counts within the system.</p>
</div>

<div class="module-content dept-module">
    <div class="dept-header">
        <h3>Department List</h3>
    </div>
        <div class="table-responsive">
            <table class="department-table">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Department Head</th>
                        <th>Number of Department Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departmentsWithDetails)): ?>
                        <?php foreach ($departmentsWithDetails as $dept): ?>
                            <tr>
                                <td><?= htmlspecialchars($dept['department_name']) ?></td>
                                <td>
                                    <?= !empty($dept['department_head_name'])
                                        ? htmlspecialchars($dept['department_head_name'])
                                        : '-' ?>
                                </td>
                                <td><?= htmlspecialchars($dept['employee_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="dept-empty">No departments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
</div>