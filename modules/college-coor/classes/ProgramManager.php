<?php
class ProgramManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllPrograms() {
        $stmt = $this->conn->prepare("SELECT
                c.id,
                c.code,
                c.name,
                c.years,
                cu.effective_year AS effective_year,
                cu.is_active AS is_active,
                COUNT(DISTINCT CASE WHEN es.enrollment_status = 'enrolled' THEN es.student_id END) AS enrolled_students
            FROM rgr_courses c
            LEFT JOIN rgr_curriculums cu ON cu.course_id = c.id
            LEFT JOIN enr_students es ON es.course_id = c.id AND es.enrollment_status = 'enrolled'
            GROUP BY c.id, c.code, c.name, c.years, cu.effective_year, cu.is_active
            ORDER BY c.code");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurriculum($courseId) {
        $stmt = $this->conn->prepare("SELECT
                cs.year_level,
                cs.semester,
                s.code,
                s.name,
                s.units
            FROM rgr_curriculums cu
            INNER JOIN rgr_curriculum_subjects cs ON cs.curriculum_id = cu.id
            INNER JOIN rgr_subjects s ON s.id = cs.subject_id
            WHERE cu.course_id = ?
            ORDER BY cs.year_level, cs.semester, s.code");
        $stmt->execute([$courseId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $curriculum = [];
        foreach ($rows as $row) {
            $year = $row['year_level'];
            $sem = $row['semester'];
            if (!isset($curriculum[$year])) {
                $curriculum[$year] = [];
            }
            if (!isset($curriculum[$year][$sem])) {
                $curriculum[$year][$sem] = [];
            }
            $curriculum[$year][$sem][] = [
                'code' => $row['code'],
                'name' => $row['name'],
                'units' => $row['units']
            ];
        }

        $result = [];
        foreach ($curriculum as $year => $semesters) {
            $semArr = [];
            foreach ($semesters as $sem => $subjects) {
                $semArr[] = ['semester' => $sem, 'subjects' => $subjects];
            }
            $result[] = ['year_level' => $year, 'semesters' => $semArr];
        }

        return $result;
    }
}
