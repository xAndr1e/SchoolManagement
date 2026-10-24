<?php
// REAL DATABASE VERSION: kumukuha ng students mula sa enr_students
// (+ enr_applicants para sa personal info, + rgr_courses para sa course code).
//
// Ang enrollment tables ay READ-ONLY dito. Ang mga edit na ginawa sa Patient Information
// page ay sine-save sa hiwalay na table: cln_student_overrides (auto-created).
//
// Same public methods ng dating dummy: getStudentByNumber(), getStudentName(),
// getAllStudents(), updateStudent(). Kaya hindi na kailangang galawin ang mga page/handler.

include_once __DIR__ . '/../../../database/db.php';

class Student {

    private $conn;
    private static $overridesReady = false;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database   = new Database();
            $this->conn = $database->getConnection();
        }
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->ensureOverridesTable();
    }

    /* ---------- Overrides table (clinic-only edits) ---------- */

    private function ensureOverridesTable(): void {
        if (self::$overridesReady) return;
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS `cln_student_overrides` (
                  `student_number` varchar(20) NOT NULL,
                  `first_name`    varchar(50)  DEFAULT NULL,
                  `last_name`     varchar(50)  DEFAULT NULL,
                  `gender`        varchar(10)  DEFAULT NULL,
                  `date_of_birth` date         DEFAULT NULL,
                  `phone`         varchar(20)  DEFAULT NULL,
                  `email`         varchar(100) DEFAULT NULL,
                  `course`        varchar(50)  DEFAULT NULL,
                  `year_level`    int(11)      DEFAULT NULL,
                  `address`       text         DEFAULT NULL,
                  `updated_at`    timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`student_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
            self::$overridesReady = true;
        } catch (Throwable $e) {
            // Kung walang CREATE permission, patakbuhin na lang ang SQL nang manu-mano.
            error_log('Student::ensureOverridesTable: ' . $e->getMessage());
        }
    }

    /* ---------- Base query ---------- */

    // Sino ang lalabas sa clinic: enrolled lang, active, at hindi naka-archive.
    // Kusang nilalaktawan ang filter kung wala ang column sa DB (iba-iba ang version ng enr_students).
    private function activeFilter(): string {
        static $cached = null;
        if ($cached !== null) return $cached;

        $cols = [];
        try {
            foreach ($this->conn->query("SHOW COLUMNS FROM enr_students") as $row) {
                $cols[strtolower($row['Field'])] = true;
            }
        } catch (Throwable $e) {
            error_log('Student::activeFilter: ' . $e->getMessage());
        }

        $where = [];
        if (isset($cols['enrollment_status'])) $where[] = "s.enrollment_status = 'enrolled'";
        if (isset($cols['status']))            $where[] = "s.status = 'active'";
        if (isset($cols['archived_at']))       $where[] = "s.archived_at IS NULL";

        return $cached = ($where ? implode(' AND ', $where) : '1=1');
    }

    private function baseSelect(): string {
        return "
            SELECT
                s.student_number,
                COALESCE(NULLIF(o.first_name, ''), a.first_name)                                  AS first_name,
                COALESCE(NULLIF(o.last_name,  ''), a.surname)                                     AS last_name,
                COALESCE(NULLIF(o.gender,     ''), a.sex)                                         AS gender,
                COALESCE(o.date_of_birth, a.date_of_birth)                                        AS date_of_birth,
                COALESCE(NULLIF(o.phone,      ''), a.contact_number)                              AS phone,
                COALESCE(NULLIF(o.email,      ''), a.email)                                       AS email,
                COALESCE(NULLIF(o.course,     ''), c.code)                                        AS course,
                COALESCE(o.year_level, s.year_level)                                              AS year_level,
                COALESCE(NULLIF(o.address,    ''), NULLIF(a.address_complete, ''), a.address)     AS address
            FROM enr_students s
            INNER JOIN enr_applicants a ON a.applicant_id = s.applicant_id
            LEFT  JOIN rgr_courses c    ON c.id = s.course_id
            LEFT  JOIN cln_student_overrides o ON o.student_number = s.student_number
        ";
    }

    /* ---------- Public API (same as the old dummy) ---------- */

    public function getStudentByNumber($student_number) {
        $sql  = $this->baseSelect() . " WHERE s.student_number = :num AND " . $this->activeFilter() . " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':num' => (string) $student_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // false kapag walang nahanap
    }

    public function getStudentName($student_number) {
        $s = $this->getStudentByNumber($student_number);
        return $s ? [
            'student_number' => $s['student_number'],
            'first_name'     => $s['first_name'],
            'last_name'      => $s['last_name'],
        ] : false;
    }

    public function getAllStudents() {
        $sql  = $this->baseSelect() . " WHERE " . $this->activeFilter() . " ORDER BY s.student_number ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para sa update_student.php. Sine-save sa cln_student_overrides (hindi ginagalaw ang enr_* tables).
    // Ang student_number ay hindi na mababago (galing sa enrollment system).
    public function updateStudent($original_number, array $data) {
        if (!$this->getStudentByNumber($original_number)) {
            return false;
        }

        $text = static function ($v) {
            $v = trim((string) $v);
            return $v === '' ? null : $v;
        };

        $dob = $text($data['date_of_birth'] ?? '');
        if ($dob !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $dob = null;
        }
        $year = $text($data['year_level'] ?? '');
        $year = ($year !== null && ctype_digit($year)) ? (int) $year : null;

        $sql = "INSERT INTO cln_student_overrides
                    (student_number, first_name, last_name, gender, date_of_birth, phone, email, course, year_level, address)
                VALUES
                    (:num, :fn, :ln, :gender, :dob, :phone, :email, :course, :year, :address)
                ON DUPLICATE KEY UPDATE
                    first_name = VALUES(first_name), last_name = VALUES(last_name), gender = VALUES(gender),
                    date_of_birth = VALUES(date_of_birth), phone = VALUES(phone), email = VALUES(email),
                    course = VALUES(course), year_level = VALUES(year_level), address = VALUES(address)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':num'     => (string) $original_number,
            ':fn'      => $text($data['first_name']    ?? ''),
            ':ln'      => $text($data['last_name']     ?? ''),
            ':gender'  => $text($data['gender']        ?? ''),
            ':dob'     => $dob,
            ':phone'   => $text($data['phone']         ?? ''),
            ':email'   => $text($data['email']         ?? ''),
            ':course'  => $text($data['course']        ?? ''),
            ':year'    => $year,
            ':address' => $text($data['address']       ?? ''),
        ]);
    }
}
?>