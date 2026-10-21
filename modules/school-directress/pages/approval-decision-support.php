<?php
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    /* User */
    $userClass    = new User();
    $userInfo     = $userClass->userSession();
    $isDirectress = (($userInfo['role'] ?? '') === 'School Directress');

    /* Departments (only the Directress can filter across departments) */
    $departments = [];
    if ($isDirectress) {
        $departmentClass = new Department();
        $departments     = $departmentClass->getAllDepartments();
    }

    /* Status filter options. "pending" = submitted + reviewed (the work waiting on the Directress). */
    if ($isDirectress) {
        $statusOptions = [
            'pending'   => 'Needs a decision',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'all'       => 'All statuses',
        ];
    } else {
        $statusOptions = [
            'all'       => 'All statuses',
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
        ];
    }
?>

<div class="sd-approval-module" id="sd-approval-module" data-is-directress="<?= $isDirectress ? '1' : '0' ?>">

    <!-- A. Header -->
    <div class="module-header sd-approval-head">
        <div>
            <h1>Approval &amp; Decision Support</h1>
            <p>Review requests, make informed decisions, and monitor approval activity.</p>
        </div>
    </div>

    <div class="module-content">

        <!-- B. Summary: one segment per real workflow status -->
        <div class="sd-approval-pipeline" role="group" aria-label="Request summary. Select a segment to filter the list.">
            <button type="button" class="sd-approval-stage sd-approval-stage--submitted" data-action="filter-status" data-status="submitted" aria-pressed="false">
                <span class="sd-approval-stage-count" data-count="submitted">–</span>
                <span class="sd-approval-stage-label">Pending Decisions</span>
                <span class="sd-approval-stage-hint">Submitted, not yet reviewed</span>
            </button>
            <button type="button" class="sd-approval-stage sd-approval-stage--reviewed" data-action="filter-status" data-status="reviewed" aria-pressed="false">
                <span class="sd-approval-stage-count" data-count="reviewed">–</span>
                <span class="sd-approval-stage-label">Under Review</span>
                <span class="sd-approval-stage-hint">Reviewed, awaiting decision</span>
            </button>
            <button type="button" class="sd-approval-stage sd-approval-stage--approved" data-action="filter-status" data-status="approved" aria-pressed="false">
                <span class="sd-approval-stage-count" data-count="approved">–</span>
                <span class="sd-approval-stage-label">Approved</span>
                <span class="sd-approval-stage-hint">Decision recorded</span>
            </button>
            <button type="button" class="sd-approval-stage sd-approval-stage--rejected" data-action="filter-status" data-status="rejected" aria-pressed="false">
                <span class="sd-approval-stage-count" data-count="rejected">–</span>
                <span class="sd-approval-stage-label">Rejected</span>
                <span class="sd-approval-stage-hint">Decision recorded</span>
            </button>
        </div>

        <div class="sd-approval-main">

            <!-- C + F. Queue with filters -->
            <section class="sd-approval-panel" aria-labelledby="sd-approval-queue-title">
                <div class="sd-approval-panel-head">
                    <h2 id="sd-approval-queue-title">Needs your decision</h2>
                    <span class="sd-approval-count" id="sd-approval-queue-count" aria-live="polite"></span>
                </div>

                <div class="sd-approval-filters">
                    <input type="search" id="sd-approval-search" class="sd-approval-input sd-approval-input--search"
                           placeholder="Search title, request ID or submitter" aria-label="Search requests" maxlength="100">

                    <?php if ($isDirectress) : ?>
                        <select id="sd-approval-department" class="sd-approval-input" aria-label="Department">
                            <option value="">All departments</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <select id="sd-approval-status" class="sd-approval-input" aria-label="Status">
                        <?php foreach ($statusOptions as $value => $label) : ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="sd-approval-datefield">
                        <span>Submitted from</span>
                        <input type="date" id="sd-approval-date-from" class="sd-approval-input">
                    </label>
                    <label class="sd-approval-datefield">
                        <span>to</span>
                        <input type="date" id="sd-approval-date-to" class="sd-approval-input">
                    </label>

                    <button type="button" class="sd-approval-btn sd-approval-btn--ghost" data-action="clear-filters">Clear filters</button>
                        <button type="button" class="sd-approval-btn sd-approval-btn--primary" data-action="open-create">
                            Submit for Approval
                        </button>
                </div>

                <div id="sd-approval-queue-list" class="sd-approval-list" aria-busy="true">
                    <div class="sd-approval-state">Loading requests…</div>
                </div>
            </section>

            <!-- E. Recent decisions -->
            <aside class="sd-approval-panel" aria-labelledby="sd-approval-recent-title">
                <div class="sd-approval-panel-head">
                    <h2 id="sd-approval-recent-title">Recent decisions</h2>
                </div>
                <div id="sd-approval-recent-list" class="sd-approval-recent" aria-busy="true">
                    <div class="sd-approval-state">Loading…</div>
                </div>
            </aside>
        </div>
    </div>

    <!-- D. Decision Review Workspace (slide-over: details on the left, document on the right) -->
    <div class="sd-approval-overlay sd-approval-overlay--drawer" id="sd-approval-drawer-overlay">
        <aside class="sd-approval-drawer" role="dialog" aria-modal="true" aria-labelledby="sd-approval-drawer-title">
            <header class="sd-approval-drawer-head">
                <div class="sd-approval-drawer-heading">
                    <p class="sd-approval-drawer-id" id="sd-approval-drawer-id"></p>
                    <h2 id="sd-approval-drawer-title">Request details</h2>
                </div>
                <span id="sd-approval-drawer-status"></span>
                <button type="button" class="sd-approval-close" data-action="close-drawer" aria-label="Close review workspace">&times;</button>
            </header>
            <div class="sd-approval-drawer-body" id="sd-approval-drawer-body"></div>
        </aside>
    </div>

    <!-- Submit for Approval modal -->
    <div class="sd-approval-overlay sd-approval-overlay--center" id="sd-approval-create-overlay">
        <div class="sd-approval-modal" role="dialog" aria-modal="true" aria-labelledby="sd-approval-create-title">
            <div class="sd-approval-modal-head">
                <h2 id="sd-approval-create-title">Submit for Approval</h2>
                <button type="button" class="sd-approval-close" data-action="close-create" aria-label="Close">&times;</button>
            </div>
            <div class="sd-approval-modal-body">
                <form id="sd-approval-form" enctype="multipart/form-data" data-skip novalidate>
                    <div class="sd-approval-field">
                        <label for="sd-approval-title">Title</label>
                        <input type="text" id="sd-approval-title" name="title" class="sd-approval-input" placeholder="Enter request title" maxlength="255" required>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-description">Description</label>
                        <textarea id="sd-approval-description" name="description" class="sd-approval-input" rows="3" placeholder="What is being requested" required></textarea>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-justification">Justification</label>
                        <textarea id="sd-approval-justification" name="justification" class="sd-approval-input" rows="3" placeholder="Why is this needed"></textarea>
                    </div>
                    <div class="sd-approval-field">
                        <label for="sd-approval-attachment">Request document</label>
                        <input type="file" id="sd-approval-attachment" name="attachment" class="sd-approval-input sd-approval-file"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="sd-approval-muted">Required to submit. PDF, JPG or PNG, up to 10 MB. The School Directress views it here in the system.</small>
                    </div>

                    <input type="hidden" id="sd-approval-id" name="approval_id" value="">
                    <div id="sd-approval-form-error" class="sd-approval-error" role="alert" hidden></div>

                    <div class="sd-approval-actions">
                        <button type="submit" class="sd-approval-btn sd-approval-btn--primary" id="sd-approval-submit-btn">Submit for Approval</button>
                        <button type="button" class="sd-approval-btn" id="sd-approval-draft-btn" data-action="save-draft">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation dialog for consequential actions -->
    <div class="sd-approval-overlay sd-approval-overlay--center sd-approval-overlay--confirm" id="sd-approval-confirm-overlay">
        <div class="sd-approval-modal sd-approval-modal--small" role="alertdialog" aria-modal="true"
             aria-labelledby="sd-approval-confirm-title" aria-describedby="sd-approval-confirm-message">
            <div class="sd-approval-modal-body">
                <h2 id="sd-approval-confirm-title"></h2>
                <p id="sd-approval-confirm-message"></p>
                <div class="sd-approval-actions">
                    <button type="button" class="sd-approval-btn" id="sd-approval-confirm-cancel">Cancel</button>
                    <button type="button" class="sd-approval-btn sd-approval-btn--primary" id="sd-approval-confirm-ok">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="sd-approval-toasts" id="sd-approval-toasts" aria-live="polite"></div>
</div>