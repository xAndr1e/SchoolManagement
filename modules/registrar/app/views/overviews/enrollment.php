<!-- ========================================= -->
<!-- ENROLLMENT -->
<!-- ========================================= -->

<div class="tab-pane fade" id="enroll">

    <div class="row g-4 pt-3">

      

        <div class="col-xl-9">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="fw-bold mb-1">
                        Enrollment
                    </h5>

                    <p class="text-muted small mb-0">
                        Current and previous enrollment records.
                    </p>

                </div>

                <div class="d-flex gap-2">

                    <!-- Generate COR -->
                    <button
                        type="button"
                        class="btn btn-primary">

                        <i class="bi bi-file-earmark-text me-2"></i>
                        Generate COR

                    </button>


                    <!-- More -->
                    <button
                        type="button"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-three-dots"></i>

                    </button>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- CURRENT ENROLLMENT -->
            <!-- ========================================= -->

            <div class="mb-4">

                <h6 class="fw-bold text-primary mb-3">
                    Current Enrollment
                </h6>


                <div class="card border shadow-none">

                    <div class="card-body p-4">

                        <div class="row g-4">


                            <!-- School Year -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    School Year
                                </div>

                                <div class="fw-semibold">
                                    <?= $schoolYear ?>
                                </div>

                            </div>


                            <!-- Semester -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Semester
                                </div>

                                <div class="fw-semibold">
                                    <?= $semester['name'] ?>
                                </div>

                            </div>


                            <!-- Year Level -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Year Level
                                </div>

                                <div class="fw-semibold">
                                    
                                   <?php switch($student_year) {

                                        case '1':
                                             echo '1st Year';
                                             break;
                                            case '2':
                                                echo '2nd Year';
                                                break;
                                                case '3':
                                                    echo '3rd Year';
                                                    break;
                                                    case '4':
                                                        echo '4th Year';
                                                        break;
                                         } 
                                         ?>

                                </div>

                            </div>


                            <!-- Program -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Program
                                </div>

                                <div class="fw-semibold">
                                     <?= $applicant_course_name  ?>
                                </div>

                            </div>


                            <!-- Section -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Section
                                </div>

                                <div class="fw-semibold">
                                <?= $section['section_code'] ?>
                                </div>

                            </div>


                            <!-- Enrollment Status -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Enrollment Status
                                </div>

                                <span class="badge bg-success-subtle text-success">
                                    Enrolled
                                </span>

                            </div>


                            <!-- Enrollment Date -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Enrollment Date
                                </div>

                                <div class="fw-semibold">
                                   <?= $student_enrolled_at ?>
                                </div>

                            </div>


                            <!-- Academic Standing -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Academic Standing
                                </div>

                                <div class="fw-semibold">
                                    Regular
                                </div>

                            </div>


                            <!-- Units -->

                            <div class="col-md-4">

                                <div class="text-muted small mb-1">
                                    Units Enrolled
                                </div>

                                <div class="fw-semibold">
                                    <?= $totalUnitPerSem ?> Units
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ENROLLED SUBJECTS -->
            <!-- ========================================= -->

            <div class="mb-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h6 class="fw-bold text-primary mb-0">
                        Enrolled Subjects
                    </h6>

                    <span class="small text-muted">
                        3 Subjects
                    </span>

                </div>


                <div class="card border shadow-none">

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th class="ps-3">
                                            Code
                                        </th>

                                        <th>
                                            Subject
                                        </th>

                                        <th>
                                            Units
                                        </th>

                                        <th>
                                            Instructor
                                        </th>

                                        <th>
                                            Schedule
                                        </th>

                                        <th>
                                            Room
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <!-- Subject 1 -->

                                    <tr>

                                        <td class="ps-3 fw-semibold">
                                            IS101
                                        </td>

                                        <td>
                                            Introduction to Information Systems
                                        </td>

                                        <td>
                                            3
                                        </td>

                                        <td>
                                            Reyes, Pedro L.
                                        </td>

                                        <td>
                                            MW 8:00 - 9:30 AM
                                        </td>

                                        <td>
                                            IT Lab 1
                                        </td>

                                    </tr>


                                    <!-- Subject 2 -->

                                    <tr>

                                        <td class="ps-3 fw-semibold">
                                            IS102
                                        </td>

                                        <td>
                                            Programming 1
                                        </td>

                                        <td>
                                            3
                                        </td>

                                        <td>
                                            Santos, Juan A.
                                        </td>

                                        <td>
                                            TTh 10:00 - 11:30 AM
                                        </td>

                                        <td>
                                            IT Lab 2
                                        </td>

                                    </tr>


                                    <!-- Subject 3 -->

                                    <tr>

                                        <td class="ps-3 fw-semibold">
                                            MATH101
                                        </td>

                                        <td>
                                            College Mathematics
                                        </td>

                                        <td>
                                            3
                                        </td>

                                        <td>
                                            Cruz, Maria C.
                                        </td>

                                        <td>
                                            F 1:00 - 4:00 PM
                                        </td>

                                        <td>
                                            Room 204
                                        </td>

                                    </tr>


                                </tbody>


                                <tfoot>

                                    <tr>

                                        <td colspan="2"
                                            class="text-end fw-bold">

                                            Total Units:

                                        </td>

                                        <td class="fw-bold">
                                            9
                                        </td>

                                        <td colspan="3"></td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ENROLLMENT HISTORY -->
            <!-- ========================================= -->

            <div>

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h6 class="fw-bold text-primary mb-0">
                        Enrollment History
                    </h6>

                    <button
                        type="button"
                        class="btn btn-sm btn-link text-decoration-none">

                        View All

                    </button>

                </div>


                <div class="card border shadow-none">

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th class="ps-3">
                                            School Year
                                        </th>

                                        <th>
                                            Semester
                                        </th>

                                        <th>
                                            Section
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Units
                                        </th>

                                        <th class="text-center">
                                            View
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <!-- Current -->

                                    <tr>

                                        <td class="ps-3">
                                            2026-2027
                                        </td>

                                        <td>
                                            1st Semester
                                        </td>

                                        <td>
                                            BSIS-1A
                                        </td>

                                        <td>

                                            <span
                                                class="badge bg-success-subtle text-success">

                                                Enrolled

                                            </span>

                                        </td>

                                        <td>
                                            9
                                        </td>

                                        <td class="text-center">

                                            <button
                                                class="btn btn-sm btn-light">

                                                <i class="bi bi-eye"></i>

                                            </button>

                                        </td>

                                    </tr>


                                    <!-- Previous -->

                                    <tr>

                                        <td class="ps-3">
                                            2025-2026
                                        </td>

                                        <td>
                                            2nd Semester
                                        </td>

                                        <td>
                                            BSIS-1A
                                        </td>

                                        <td>

                                            <span
                                                class="badge bg-secondary-subtle text-secondary">

                                                Completed

                                            </span>

                                        </td>

                                        <td>
                                            18
                                        </td>

                                        <td class="text-center">

                                            <button
                                                class="btn btn-sm btn-light">

                                                <i class="bi bi-eye"></i>

                                            </button>

                                        </td>

                                    </tr>


                                    <!-- Previous -->

                                    <tr>

                                        <td class="ps-3">
                                            2025-2026
                                        </td>

                                        <td>
                                            1st Semester
                                        </td>

                                        <td>
                                            BSIS-1A
                                        </td>

                                        <td>

                                            <span
                                                class="badge bg-secondary-subtle text-secondary">

                                                Completed

                                            </span>

                                        </td>

                                        <td>
                                            21
                                        </td>

                                        <td class="text-center">

                                            <button
                                                class="btn btn-sm btn-light">

                                                <i class="bi bi-eye"></i>

                                            </button>

                                        </td>

                                    </tr>


                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================= -->
        <!-- RIGHT SIDEBAR -->
        <!-- ========================================= -->

        <div class="col-xl-3">


            <!-- ========================================= -->
            <!-- QUICK SUMMARY -->
            <!-- ========================================= -->

            <div class="card border shadow-none mb-4">

                <div class="card-body p-3">

                    <h6 class="fw-bold mb-4">
                        Quick Summary
                    </h6>


                    <!-- Units Earned -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-book"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Total Units Earned
                            </div>

                        </div>

                        <strong>
                            24
                        </strong>

                    </div>


                    <!-- Units Enrolled -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-journal"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Units Enrolled
                            </div>

                        </div>

                        <strong>
                            9
                        </strong>

                    </div>


                    <!-- Completed -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-success bg-opacity-10
                                   text-success
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-check-circle"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Units Completed
                            </div>

                        </div>

                        <strong>
                            24
                        </strong>

                    </div>


                    <!-- CGWA -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-bar-chart"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                CGWA
                            </div>

                        </div>

                        <strong>
                            1.75
                        </strong>

                    </div>


                    <!-- Last Academic Year -->

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-calendar"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Last Academic Year
                            </div>

                        </div>

                        <strong class="small">
                            2025-2026
                        </strong>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ENROLLMENT ACTIONS -->
            <!-- ========================================= -->

            <div class="card border shadow-none">

                <div class="card-body p-3">

                    <h6 class="fw-bold mb-3">
                        Actions
                    </h6>


                    <!-- COR -->

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100
                               text-start mb-2">

                        <i class="bi bi-file-earmark-text me-2"></i>

                        Generate COR

                    </button>


                    <!-- Enrollment Details -->

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100
                               text-start mb-2">

                        <i class="bi bi-eye me-2"></i>

                        View Enrollment Details

                    </button>


                    <!-- Adjustment -->

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100
                               text-start mb-2">

                        <i class="bi bi-pencil-square me-2"></i>

                        Request Enrollment Adjustment

                    </button>


                    <!-- Section Change -->

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100
                               text-start">

                        <i class="bi bi-arrow-left-right me-2"></i>

                        Request Section Change

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>