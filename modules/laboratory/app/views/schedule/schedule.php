<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="main-content">
    <div class="container-fluid px-4">
        <h1 class="h3 mb-2 text-gray-800">Laboratory Schedule</h1>
        <p class="mb-4">Schedule</p>

        <div class="card mb-4 card shadow-sm border-0 border-top border-4 border-secondary shadow-lg p-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Laboratory Schedule
                </div>

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="bi bi-plus-lg"></i>
                    Add Schedule
                </button>

            </div>
            <div class="card-body">
                <table id="scheduleTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Instructor</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($schedules as $schedule): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $schedule['laboratory_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $schedule['subject_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $schedule['section']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $schedule['instructor']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $schedule['day']
                                    ) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'h:i A',
                                        strtotime($schedule['start_time'])
                                    ) ?>

                                    -

                                    <?= date(
                                        'h:i A',
                                        strtotime($schedule['end_time'])
                                    ) ?>
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= htmlspecialchars(
                                            $schedule['status']
                                        ) ?>
                                    </span>
                                </td>


                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#" class="dropdown-item viewBtn"
                                                    data-id="<?= $schedule['schedule_id'] ?>">
                                                    <i class="fas fa-eye me-2"></i> View
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#" class="dropdown-item editBtn"
                                                    data-id="<?= $schedule['schedule_id'] ?>">
                                                    <i class="fas fa-edit me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item text-danger deleteBtn"
                                                    data-id="<?= $schedule['schedule_id'] ?>">
                                                    <i class="fas fa-trash me-2"></i>
                                                    Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>


<!-- <main class="main-content">
    <div class="container-fluid px-4">
        <h1 class="h3 mb-2 text-gray-800">Schedule</h1>
        <p class="mb-4">Calendar</p>

        <div class="card mb-4 card shadow-sm border-0 border-top border-4 border-secondary shadow-lg p-3">

            <div class="card-body">
                <div id='homeCalendar' style="height: 100%; width: 100%;"></div>
            </div>

        </div>
    </div>
</main> -->

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js'></script>

<script>
    $(document).ready(function() {
        $('#scheduleTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 30, 40],
        });
    });
</script>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/js/calendar.js"></script>
<script src="<?= BASE_URL ?>/js/schedule.js"></script>

<?php require __DIR__ . '/addScheduleModal.php'; ?>
<?php require __DIR__ . '/scheduleEditModal.php'; ?>
<?php require __DIR__ . '/scheduleViewModal.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>