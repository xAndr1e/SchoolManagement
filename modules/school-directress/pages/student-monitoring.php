<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/StudentMonitoring.php';

$model = new StudentMonitoring();
$data  = $model->getDashboardData();

$students = $data['students'];
$courses  = $data['courses'];
$stats    = $data['stats'];
$error    = $data['error'];
?>

<!--
  NOTE: If this fragment is loaded via your AJAX page router
  (page-switcher.js swapping .container's innerHTML), a <script src>
  injected through innerHTML will NOT execute on subsequent navigations —
  only on a full page load. Move these two tags into your main layout's
  global CSS/JS bundle for consistency with the rest of the module.
-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/student-monitoring.css">

<div class="module-header">
    <h2>Student Monitoring</h2>
    <p>Track and manage student information and status</p>
</div>

<div class="ep">

    <!-- ── HERO ── -->
    <div class="ep-hero">
        <div class="ep-hero__inner">
            <div class="ep-hero__text">
                <h1>Enrolled Students</h1>
                <p>Click any row to expand full student profile</p>
            </div>
            <div class="ep-hero__actions">
                <button class="ep-btn ep-btn--glass" onclick="epExportCSV()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </button>
                <button class="ep-btn ep-btn--glass" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print
                </button>
            </div>
        </div>

        <!-- Stat strip -->
        <div class="ep-stats">
            <div class="ep-stat ep-stat--all">
                <div class="ep-stat__num"><?= $stats['total'] ?></div>
                <div class="ep-stat__lbl">Total</div>
            </div>
            <div class="ep-stat ep-stat--enrolled">
                <div class="ep-stat__num"><?= $stats['active'] ?></div>
                <div class="ep-stat__lbl">Active</div>
            </div>
            <div class="ep-stat ep-stat--leave">
                <div class="ep-stat__num"><?= $stats['inactive'] ?></div>
                <div class="ep-stat__lbl">Inactive</div>
            </div>
            <div class="ep-stat ep-stat--grad">
                <div class="ep-stat__num"><?= $stats['graduated'] ?></div>
                <div class="ep-stat__lbl">Graduated</div>
            </div>
        </div>
    </div>

    <!-- ── NOTICE ── -->
    <?php if ($error): ?>
    <div class="ep-notice">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Database unavailable — showing mock data. <em>(<?= htmlspecialchars(substr($error, 0, 120)) ?>)</em></span>
    </div>
    <?php endif; ?>

    <!-- ── TOOLBAR ── -->
    <div class="ep-toolbar">
        <div class="ep-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="epSearch" placeholder="Search name, student no., email…" autocomplete="off">
        </div>

        <select class="ep-sel" id="epStatus">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="graduated">Graduated</option>
        </select>

        <select class="ep-sel" id="epCourse">
            <option value="">All Courses</option>
            <?php foreach ($courses as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
        </select>

        <select class="ep-sel" id="epYear">
            <option value="">All Year Levels</option>
            <?php for ($y = 1; $y <= 5; $y++): ?>
            <option value="<?= $y ?>"><?= StudentMonitoring::yearLabel($y) ?></option>
            <?php endfor; ?>
        </select>

        <span class="ep-count">Showing <strong id="epCount"><?= count($students) ?></strong> students</span>
    </div>

    <!-- ── TABLE ── -->
    <div class="ep-table-wrap">
        <table class="ep-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Student</th>
                    <th>Student No.</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody id="epBody">

            <?php if (empty($students)): ?>
            <tr><td colspan="8">
                <div class="ep-empty">
                    <div class="ep-empty__icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>No students found</h3>
                    <p>No enrollment records are available yet.</p>
                </div>
            </td></tr>

            <?php else: foreach ($students as $i => $s):
                $name = StudentMonitoring::fullName($s);
                $sm   = StudentMonitoring::statusMeta($s['academic_status']);
                $age  = StudentMonitoring::age($s['birth_date']);
                $rid  = 'epd-' . $s['student_number'];
            ?>
            <!-- DATA ROW -->
            <tr class="ep-data-row"
                style="animation-delay:<?= $i * 28 ?>ms"
                data-name="<?= htmlspecialchars(strtolower($name)) ?>"
                data-snum="<?= htmlspecialchars(strtolower((string) $s['student_number'])) ?>"
                data-email="<?= htmlspecialchars(strtolower($s['email'] ?? '')) ?>"
                data-status="<?= htmlspecialchars($s['academic_status']) ?>"
                data-course="<?= htmlspecialchars($s['course'] ?? '') ?>"
                data-year="<?= (int) $s['year_level'] ?>"
                data-sex="<?= htmlspecialchars($s['gender'] ?? '') ?>"
                onclick="epToggle(this,'<?= $rid ?>')"
            >
                <td><div class="ep-toggle">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                </div></td>

                <td>
                    <div class="ep-student">
                        <div class="ep-avatar <?= $s['gender'] === 'female' ? 'ep-avatar--f' : '' ?>"><?= StudentMonitoring::initials($s) ?></div>
                        <div>
                            <div class="ep-sname"><?= htmlspecialchars($name) ?></div>
                            <div class="ep-smeta"><?= htmlspecialchars(ucfirst($s['gender'] ?? '—')) ?> · <?= htmlspecialchars($s['email'] ?? '—') ?></div>
                        </div>
                    </div>
                </td>

                <td><span class="ep-mono"><?= htmlspecialchars($s['student_number']) ?></span></td>

                <td><span class="ep-ccode"><?= htmlspecialchars($s['course'] ?? '—') ?></span></td>

                <td><span class="ep-badge <?= $sm['cls'] ?>"><?= $sm['label'] ?></span></td>

                <td><span class="ep-year"><?= StudentMonitoring::yearLabel($s['year_level']) ?></span></td>

                <td>
                    <?php if (!empty($s['section'])): ?>
                    <div class="ep-sec-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                        <?= htmlspecialchars($s['section']) ?>
                    </div>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--color4);">Unassigned</span>
                    <?php endif; ?>
                </td>

                <td style="font-size:12.5px;color:var(--color5);white-space:nowrap;">
                    <?= $s['created_at'] ? date('M j, Y', strtotime($s['created_at'])) : '—' ?>
                </td>
            </tr>

            <!-- DETAIL ROW -->
            <tr class="ep-detail-row" id="<?= $rid ?>">
                <td class="ep-detail-cell" colspan="8">
                    <div class="ep-detail-inner">
                        <div class="ep-fl">
                            <div class="ep-fl__k">Phone</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['phone'] ?? '—') ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Birth Date</div>
                            <div class="ep-fl__v">
                                <?= $s['birth_date'] ? date('F j, Y', strtotime($s['birth_date'])) : '—' ?>
                                <?php if ($age !== null): ?>
                                <span style="color:var(--color5);font-size:11.5px;">(<?= $age ?> yrs old)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Address</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['address'] ?? '—') ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Academic Status</div>
                            <div class="ep-fl__v"><?= htmlspecialchars(ucfirst($s['academic_status'])) ?></div>
                        </div>
                        <?php if ($s['academic_status'] === 'graduated' && $s['graduated_at']): ?>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Graduated On</div>
                            <div class="ep-fl__v"><?= date('F j, Y', strtotime($s['graduated_at'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Record Created</div>
                            <div class="ep-fl__v"><?= $s['created_at'] ? date('F j, Y', strtotime($s['created_at'])) : '—' ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Last Updated</div>
                            <div class="ep-fl__v"><?= $s['updated_at'] ? date('F j, Y', strtotime($s['updated_at'])) : '—' ?></div>
                        </div>
                    </div>
                </td>
            </tr>

            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div><!-- /ep-table-wrap -->
</div><!-- /ep -->
