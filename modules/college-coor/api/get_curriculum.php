<?php
require_once(__DIR__ . '/../classes/ProgramManager.php');
require_once(__DIR__ . '/../../../database/db.php');
header('Content-Type: application/json');

$courseId = $_GET['course_id'] ?? $_GET['program_id'] ?? 0;

error_log('get_curriculum course_id=' . var_export($courseId, true));

if (!$courseId || $courseId === 'N/A' || $courseId === 'null') {
    echo json_encode(['error' => 'Course ID required']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $programManager = new ProgramManager($conn);

    $courseStmt = $conn->prepare("SELECT id, name FROM rgr_courses WHERE id = ?");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    error_log('course lookup result=' . json_encode($course));

    if (!$course) {
        echo json_encode([
            'debug' => [
                'course_id' => $courseId,
                'course_lookup' => 'not_found'
            ],
            'program_name' => 'No program data found',
            'curriculum_name' => 'No curriculum data found',
            'effective_year' => 'No curriculum data found',
            'status' => 'No curriculum data found',
            'subject_flow' => [],
            'enrollment_summary' => [
                'total_enrolled_students' => 0,
                'year_levels' => [
                    'first_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'second_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'third_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0],
                    'fourth_year' => ['enrolled_students' => 0, 'total_curriculum_units' => 0]
                ]
            ]
        ]);
        exit;
    }

    $curriculumStmt = $conn->prepare("SELECT
            curriculum_name,
            effective_year,
            is_active
        FROM rgr_curriculums
        WHERE course_id = ?
        ORDER BY effective_year DESC, id DESC
        LIMIT 1");
    $curriculumStmt->execute([$courseId]);
    $curriculumRow = $curriculumStmt->fetch(PDO::FETCH_ASSOC);

    error_log('curriculum lookup result=' . json_encode($curriculumRow));

    $curriculum = $programManager->getCurriculum($courseId);
    error_log('subject flow count=' . count($curriculum));

    $subjectCountStmt = $conn->prepare("SELECT COUNT(*) AS subject_count
        FROM rgr_curriculum_subjects cs
        INNER JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id
        WHERE cu.course_id = ?");
    $subjectCountStmt->execute([$courseId]);
    $subjectCountRow = $subjectCountStmt->fetch(PDO::FETCH_ASSOC);
    error_log('mapped subject count=' . ($subjectCountRow['subject_count'] ?? 0));

    $summaryStmt = $conn->prepare("SELECT
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 1 THEN 1 ELSE 0 END) AS first_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 2 THEN 1 ELSE 0 END) AS second_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 3 THEN 1 ELSE 0 END) AS third_year_count,
            SUM(CASE WHEN CAST(es.year_level AS UNSIGNED) = 4 THEN 1 ELSE 0 END) AS fourth_year_count,
            COUNT(*) AS total_enrolled_students
        FROM enr_students es
        WHERE es.course_id = ?
          AND es.enrollment_status = 'enrolled'");
    $summaryStmt->execute([$courseId]);
    $summaryRow = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $unitSummaryStmt = $conn->prepare("SELECT
            cs.year_level,
            COALESCE(SUM(rs.units), 0) AS total_curriculum_units
        FROM rgr_curriculums cu
        INNER JOIN rgr_curriculum_subjects cs ON cs.curriculum_id = cu.id
        INNER JOIN rgr_subjects rs ON rs.id = cs.subject_id
        WHERE cu.course_id = ?
        GROUP BY cs.year_level");
    $unitSummaryStmt->execute([$courseId]);
    $unitSummaryRows = $unitSummaryStmt->fetchAll(PDO::FETCH_ASSOC);

    $yearUnitMap = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0
    ];

    foreach ($unitSummaryRows as $row) {
        $yearLevel = (int)($row['year_level'] ?? 0);
        if (isset($yearUnitMap[$yearLevel])) {
            $yearUnitMap[$yearLevel] = (int)($row['total_curriculum_units'] ?? 0);
        }
    }

    $programName = $course['name'] ?? 'No program data found';
    $curriculumName = $curriculumRow['curriculum_name'] ?? 'No curriculum data found';
    $effectiveYear = $curriculumRow['effective_year'] ?? 'No curriculum data found';
    $status = $curriculumRow['is_active'] == 1 ? 'Active' : ($curriculumRow['is_active'] == 0 ? 'Inactive' : 'No curriculum data found');

    echo json_encode([
        'debug' => [
            'course_id' => $courseId,
            'course_lookup' => 'found',
            'curriculum_lookup' => $curriculumRow ? 'found' : 'not_found',
            'mapped_subject_count' => (int)($subjectCountRow['subject_count'] ?? 0)
        ],
        'program_name' => $programName,
        'curriculum_name' => $curriculumName,
        'effective_year' => $effectiveYear,
        'status' => $status,
        'subject_flow' => $curriculum,
        'enrollment_summary' => [
            'total_enrolled_students' => (int)($summaryRow['total_enrolled_students'] ?? 0),
            'year_levels' => [
                'first_year' => [
                    'enrolled_students' => (int)($summaryRow['first_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[1]
                ],
                'second_year' => [
                    'enrolled_students' => (int)($summaryRow['second_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[2]
                ],
                'third_year' => [
                    'enrolled_students' => (int)($summaryRow['third_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[3]
                ],
                'fourth_year' => [
                    'enrolled_students' => (int)($summaryRow['fourth_year_count'] ?? 0),
                    'total_curriculum_units' => (int)$yearUnitMap[4]
                ]
            ]
        ]
    ]);
} catch (Exception $e) {
    error_log('get_curriculum exception=' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>
