<?php

/**
 * StudentMonitoring
 *
 * Handles all data access for the Student Monitoring page (School Directress
 * module). Reads directly from the flat rgr_students table — no joins.
 * Follows the same pattern as Employee.php / Announcement.php: optional
 * constructor injection, falling back to Database::getConnection().
 */
class StudentMonitoring
{
    private PDO $conn;

    public function __construct(?PDO $conn = null)
    {
        if ($conn) {
            $this->conn = $conn;
        } else {
            require_once __DIR__ . '/../../../database/db.php';
            $database   = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Fetches students, distinct courses, and computed stats in one call.
     * Falls back to mock data (for preview/dev) if the query fails.
     * $error will be non-null when the fallback is in use.
     */
    public function getDashboardData(): array
    {
        try {
            $students = $this->getStudents();
            $courses  = $this->getDistinctCourses();

            return [
                'students' => $students,
                'courses'  => $courses,
                'stats'    => $this->computeStats($students),
                'error'    => null,
            ];
        } catch (PDOException $e) {
            $students = $this->mockStudents();

            return [
                'students' => $students,
                'courses'  => $this->mockCourses(),
                'stats'    => $this->computeStats($students),
                'error'    => $e->getMessage(),
            ];
        }
    }

    public function getStudents(): array
    {
        $stmt = $this->conn->query("
            SELECT
                student_number, first_name, middle_name, last_name,
                gender, birth_date, course, year_level, section,
                email, phone, address, academic_status,
                graduated_at, created_at, updated_at
            FROM rgr_students
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll();
    }

    /** No separate courses table — pull the distinct values used in rgr_students itself. */
    public function getDistinctCourses(): array
    {
        $stmt = $this->conn->query("
            SELECT DISTINCT course
            FROM rgr_students
            WHERE course IS NOT NULL AND course != ''
            ORDER BY course
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function computeStats(array $students): array
    {
        $stats = ['total' => count($students), 'active' => 0, 'inactive' => 0, 'graduated' => 0];

        foreach ($students as $r) {
            $key = $r['academic_status'];
            if (isset($stats[$key])) {
                $stats[$key]++;
            }
        }

        return $stats;
    }

    /* ── Presentation helpers ──────────────────────────────────────── */

    public static function fullName(array $r): string
    {
        return trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'][0] . '. ' : '') . $r['last_name']);
    }

    public static function initials(array $r): string
    {
        return strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1));
    }

    public static function age(?string $birthDate): ?int
    {
        if (!$birthDate) {
            return null;
        }
        return (int) date_diff(date_create($birthDate), date_create('today'))->y;
    }

    public static function statusMeta(string $s): array
    {
        $map = [
            'active'    => ['label' => 'Active',    'cls' => 's-active'],
            'inactive'  => ['label' => 'Inactive',  'cls' => 's-inactive'],
            'graduated' => ['label' => 'Graduated', 'cls' => 's-graduated'],
        ];

        return $map[$s] ?? ['label' => ucfirst($s), 'cls' => 's-default'];
    }

    /** year_level is stored as YEAR(4) but treated as a 1–4(-ish) standing, not a calendar year. */
    public static function yearLabel($y): string
    {
        $y      = (int) $y;
        $labels = ['', '1st', '2nd', '3rd', '4th', '5th'];
        if ($y < 1) {
            return '—';
        }
        return ($labels[$y] ?? "{$y}th") . ' Yr';
    }

    /* ── Mock data (dev/preview fallback only) ───────────────────────── */

    private function mockStudents(): array
    {
        return [
            ['student_number'=>2024001,'first_name'=>'Maria','middle_name'=>'Cruz','last_name'=>'Santos','gender'=>'female','birth_date'=>'2005-03-12','course'=>'BSCS','year_level'=>1,'section'=>'CS-1A','email'=>'maria@uni.edu','phone'=>'09171234567','address'=>'Quezon City, Metro Manila','academic_status'=>'active','graduated_at'=>null,'created_at'=>'2024-08-15 09:00:00','updated_at'=>'2024-08-15 09:00:00'],
            ['student_number'=>2024002,'first_name'=>'Juan','middle_name'=>null,'last_name'=>'dela Cruz','gender'=>'male','birth_date'=>'2004-07-22','course'=>'BSIT','year_level'=>2,'section'=>'IT-2B','email'=>'juan@uni.edu','phone'=>'09281234567','address'=>'Makati, Metro Manila','academic_status'=>'active','graduated_at'=>null,'created_at'=>'2024-08-15 09:30:00','updated_at'=>'2024-08-15 09:30:00'],
            ['student_number'=>2023045,'first_name'=>'Ana','middle_name'=>'Luz','last_name'=>'Reyes','gender'=>'female','birth_date'=>'2003-11-05','course'=>'BSCS','year_level'=>3,'section'=>null,'email'=>'ana@uni.edu','phone'=>'09091234567','address'=>'Pasig, Metro Manila','academic_status'=>'inactive','graduated_at'=>null,'created_at'=>'2023-08-10 10:00:00','updated_at'=>'2023-08-10 10:00:00'],
            ['student_number'=>2021012,'first_name'=>'Pedro','middle_name'=>'Gomez','last_name'=>'Villanueva','gender'=>'male','birth_date'=>'2001-04-18','course'=>'BSIT','year_level'=>4,'section'=>'IT-4A','email'=>'pedro@uni.edu','phone'=>'09151234567','address'=>'Taguig, Metro Manila','academic_status'=>'graduated','graduated_at'=>'2025-05-30 00:00:00','created_at'=>'2021-08-12 08:00:00','updated_at'=>'2025-05-30 00:00:00'],
            ['student_number'=>2022033,'first_name'=>'Liza','middle_name'=>null,'last_name'=>'Mercado','gender'=>'female','birth_date'=>'2003-09-29','course'=>'BSCS','year_level'=>2,'section'=>'CS-2C','email'=>'liza@uni.edu','phone'=>'09361234567','address'=>'Mandaluyong, Metro Manila','academic_status'=>'inactive','graduated_at'=>null,'created_at'=>'2022-08-11 11:00:00','updated_at'=>'2022-08-11 11:00:00'],
            ['student_number'=>2024006,'first_name'=>'Carlos','middle_name'=>'Ramos','last_name'=>'Bautista','gender'=>'male','birth_date'=>'2006-01-14','course'=>'BSCpE','year_level'=>1,'section'=>'CPE-1B','email'=>'carlos@uni.edu','phone'=>'09221234567','address'=>'Las Pinas, Metro Manila','academic_status'=>'active','graduated_at'=>null,'created_at'=>'2024-08-16 08:45:00','updated_at'=>'2024-08-16 08:45:00'],
        ];
    }

    private function mockCourses(): array
    {
        return ['BSCS', 'BSCpE', 'BSIT'];
    }
}