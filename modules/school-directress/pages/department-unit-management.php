<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/User.php';
include_once __DIR__ . '/../classes/Department.php';

/*User*/
$user = new User();
$user_info = $user->userSession();

/*Department*/
$department = new Department();
$departmentsWithDetails = $department->getDepartmentsWithDetails();
?>

<div class="module-header">
    <h1>Department & Unit Management</h1>
    <p>Add and manage departments, units, and staff assignments within the system.</p>
</div>

<div class="module-content">
    <div class="tab-container">
        <ul class="tab-list">
            <li class="tab-item active" data-tab="departments">Departments</li>
            <li class="tab-item" data-tab="add-department">Add Department</li>
            <li class="tab-item" data-tab="assign-department-head">Assign Department Head</li>
        </ul>

        <!-- Tab: Department List -->
        <div class="tab-content active" id="departments">
            <div class="table-responsive">
                <div class="form-section">
                    <div class="form-section-header">
                        <h3>Department List</h3>
                    </div>
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
                                        <td><?= $dept['employee_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;">No departments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Add Department -->
        <div class="tab-content" id="add-department">
            <div class="form-section">
                <h3>Add Department</h3>
                <form id="add-department-form" data-skip>
                    <div id="add-dept-banner" class="assign-banner" style="display:none;"></div>
                    <div class="form-group">
                        <label for="dept-name">Department Name</label>
                        <input type="text" id="dept-name" name="dept-name" placeholder="Enter department name">
                    </div>
                    <div class="form-group">
                        <label for="dept-desc">Description</label>
                        <textarea id="dept-desc" name="dept-desc" rows="3" placeholder="Enter department description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Save Department</button>
                        <button type="button" class="btn-cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab: Assign Department Head -->
        <div class="tab-content" id="assign-department-head">
            <div class="form-section">
                <h3>Assign Department Head</h3>
                <form id="assign-head-form" data-skip>
                    <div id="assign-head-banner" class="assign-banner" style="display:none;"></div>
                    <div class="form-group">
                        <label for="dept-select">Select Department</label>
                        <select id="dept-select" name="dept-select">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departmentsWithDetails as $dept): ?>
                                <option value="<?= $dept['department_id'] ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="employee-select">Select Employee</label>
                        <select id="employee-select" name="employee-select" disabled>
                            <option value="">-- Select a department first --</option>
                        </select>
                        <span id="employee-load-status" class="form-hint"></span>
                    </div>
                    <div class="form-actions">
                        <button type="submit" id="btn-assign-head" class="btn-save" disabled>Assign as Head</button>
                        <button type="button" class="btn-cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>