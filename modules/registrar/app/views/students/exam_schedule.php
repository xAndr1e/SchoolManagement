<?php include __DIR__ .'/../partials/sidebar.php'; ?>
<?php include  __DIR__ .'/../partials/header.php'; ?>

<main class="main-content">
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <div id="dashboard-title">
            <h1 class="h3 fw-bold mb-0">Examination Schedule</h1>
             <p class="text-muted small">Manage and organize academic programs for the registrar.</p>
        </div>


    </div>


    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="fs-6 mb-0 text-primary">Examination Schedule List</h3>

            <div class="d-flex gap-2">

                <button type="button" class="btn btn-outline-danger btn-sm" id="pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </button>

                <button type="button" class="btn btn-outline-success btn-sm" id="excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                </button>

                <button type="button" class="btn btn-outline-primary btn-sm" id="csv">
                    <i class="bi bi-filetype-csv"></i> CSV
                </button>

            </div>
        </div>


        <div class="card-body">

            <div class="row g-2 mb-3">

                <div class="col-md-3">
                    <label class="form-label small">Section</label>
                    <select class="form-select form-select-sm" id="section">
                        <?php foreach ($sections as $section) {?>
                        <option value=<?= $section['id'] ?>><?= $section['name'] ?></option>
                        <?php  } ?>
                    </select>
                </div>

        </div>


        <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            
                        <th style="width:100px;">Time</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                           
                        </tr>
                    </thead>

                    <tbody id="grid-body">
                        
                    </tbody>
                   

                </table>

            </div>


            
            <div class="d-flex align-items-center justify-content-between mt-3">

                <div class="fw-semibold small" id="pageInfo"></div>

                <div id="pagination" class="d-flex gap-1"></div>

            </div>

        </div>

    </div>

</div>
</main>





<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script> const BASE_URL = "<?php echo BASE_URL ?>" </script>
<script src="<?= BASE_URL ?>/js/exam_schedule.js"></script>

<?php include  __DIR__ .'/../partials/footer.php'; ?> 