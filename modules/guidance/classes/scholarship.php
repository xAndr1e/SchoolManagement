<?php
/**
 * Scholarship.php
 * Model / data-access layer for the Scholarship submodule
 * (Guidance & Counseling), matching the Cases.php pattern.
 *
 * Confirmed tables: gd_scholarships, rgr_students, sms_employee
 * (+ sd_position for counselor scoping, same as Cases.php).
 *
 * Business-logic decisions locked in with the project owner:
 *   - Student applications only (no staff scholarships)
 *   - Extended workflow:
 *       Applied -> Under Review -> Approved/Rejected
 *       Approved -> Active -> Completed/Terminated
 *   - "Counselor" = any employee in department 8 (Guidance and
 *     Counseling Office), same scoping as Cases.php
 *   - review_notes covers both the Approved/Rejected decision AND the
 *     Completed/Terminated outcome — no separate termination_reason
 *   - Attachment is optional (like Approval module)
 */

include_once __DIR__ . '/../../../database/db.php';
// NOTE: adjust this relative path if Scholarship.php doesn't sit at the
// same folder depth as Cases.php (modules/guidance/classes/).

class Scholarship
{
    private $conn;

    /** Status groups — used to validate transitions in updateStatus() */
    private const VALID_STATUSES = [
        'Applied', 'Under Review', 'Approved', 'Rejected', 'Active', 'Completed', 'Terminated',
    ];

    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /* ---------------------------------------------------------------
       List + pagination
    --------------------------------------------------------------- */
    public function getList(array $filters, int $page, int $pageSize): array
    {
        [$whereSql, $params] = $this->buildListFilters($filters);
        $offset = ($page - 1) * $pageSize;

        $total = $this->countList($whereSql, $params);

        $sql = "
            SELECT
                sc.scholarship_id,
                sc.student_number,
                CONCAT(s.last_name, ', ', s.first_name) AS student_name,
                sc.scholarship_name,
                sc.sponsor,
                sc.award_amount,
                sc.coverage_type,
                sc.eligibility_basis,
                sc.status,
                sc.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                sc.applied_at,
                sc.reviewed_at
            FROM gd_scholarships sc
            JOIN rgr_students s ON s.student_number = sc.student_number
            JOIN sms_employee e ON e.employee_id = sc.counselor_id
            {$whereSql}
            ORDER BY sc.applied_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    private function countList(string $whereSql, array $params): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM gd_scholarships sc
            JOIN rgr_students s ON s.student_number = sc.student_number
            JOIN sms_employee e ON e.employee_id = sc.counselor_id
            {$whereSql}
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildListFilters(array $filters): array
    {
        $where = [];
        $params = [];

        // NOTE: '' !== value checks throughout (not just !empty on the
        // outer array) since '0'-ish values could otherwise be dropped —
        // matches the department_id/status split fix already made
        // elsewhere in the project.
        if (!empty($filters['search'])) {
            $where[] = '(s.first_name LIKE :search OR s.last_name LIKE :search OR sc.scholarship_name LIKE :search)';
            $params['search'] = "%{$filters['search']}%";
        }
        if (!empty($filters['status'])) {
            $where[] = 'sc.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['eligibility_basis'])) {
            $where[] = 'sc.eligibility_basis = :eligibility_basis';
            $params['eligibility_basis'] = $filters['eligibility_basis'];
        }
        if (!empty($filters['coverage_type'])) {
            $where[] = 'sc.coverage_type = :coverage_type';
            $params['coverage_type'] = $filters['coverage_type'];
        }
        if (!empty($filters['counselor_id'])) {
            $where[] = 'sc.counselor_id = :counselor_id';
            $params['counselor_id'] = $filters['counselor_id'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        return [$whereSql, $params];
    }

    /* ---------------------------------------------------------------
       Detail
    --------------------------------------------------------------- */
    public function getOverview(int $scholarshipId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                sc.scholarship_id,
                sc.student_number,
                CONCAT(s.last_name, ', ', s.first_name) AS student_name,
                sc.scholarship_name,
                sc.sponsor,
                sc.award_amount,
                sc.coverage_type,
                sc.eligibility_basis,
                sc.justification,
                sc.status,
                sc.review_notes,
                sc.counselor_id,
                CONCAT(e.first_name, ' ', e.last_name) AS counselor_name,
                sc.reviewed_by,
                r.first_name AS reviewer_first_name,
                r.last_name AS reviewer_last_name,
                sc.attachment_path,
                sc.attachment_name,
                sc.applied_at,
                sc.reviewed_at
            FROM gd_scholarships sc
            JOIN rgr_students s ON s.student_number = sc.student_number
            JOIN sms_employee e ON e.employee_id = sc.counselor_id
            LEFT JOIN sms_employee r ON r.employee_id = sc.reviewed_by
            WHERE sc.scholarship_id = :scholarship_id
        ");
        $stmt->execute(['scholarship_id' => $scholarshipId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['reviewer_name'] = $row['reviewed_by']
            ? trim(($row['reviewer_first_name'] ?? '') . ' ' . ($row['reviewer_last_name'] ?? ''))
            : null;
        unset($row['reviewer_first_name'], $row['reviewer_last_name']);

        return $row;
    }

    /* ---------------------------------------------------------------
       Create
    --------------------------------------------------------------- */
    public function createApplication(array $data): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO gd_scholarships
                (student_number, counselor_id, scholarship_name, sponsor, award_amount,
                 coverage_type, eligibility_basis, justification, status,
                 attachment_path, attachment_name, applied_at)
            VALUES
                (:student_number, :counselor_id, :scholarship_name, :sponsor, :award_amount,
                 :coverage_type, :eligibility_basis, :justification, 'Applied',
                 :attachment_path, :attachment_name, NOW())
        ");
        $stmt->execute([
            'student_number'    => $data['student_number'],
            'counselor_id'      => $data['counselor_id'],
            'scholarship_name'  => $data['scholarship_name'],
            'sponsor'           => $data['sponsor'] ?: null,
            'award_amount'      => $data['award_amount'] !== '' ? $data['award_amount'] : null,
            'coverage_type'     => $data['coverage_type'] ?? 'Full',
            'eligibility_basis' => $data['eligibility_basis'] ?? 'Academic',
            'justification'     => $data['justification'] ?: null,
            'attachment_path'   => $data['attachment_path'] ?? null,
            'attachment_name'   => $data['attachment_name'] ?? null,
        ]);

        return (int) $this->conn->lastInsertId();
    }

    /* ---------------------------------------------------------------
       Workflow transitions
    --------------------------------------------------------------- */

    /** Applied -> Under Review (counselor picks it up for processing) */
    public function markUnderReview(int $scholarshipId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships SET status = 'Under Review'
            WHERE scholarship_id = :id AND status = 'Applied'
        ");
        $stmt->execute(['id' => $scholarshipId]);
        return $stmt->rowCount() > 0;
    }

    /** Under Review -> Approved/Rejected */
    public function reviewApplication(int $scholarshipId, string $decision, int $reviewerId, ?string $notes = null): bool
    {
        $status = $decision === 'approve' ? 'Approved' : 'Rejected';

        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships
            SET status = :status, review_notes = :notes, reviewed_by = :reviewer_id, reviewed_at = NOW()
            WHERE scholarship_id = :id AND status = 'Under Review'
        ");
        $stmt->execute([
            'status'      => $status,
            'notes'       => $notes,
            'reviewer_id' => $reviewerId,
            'id'          => $scholarshipId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /** Approved -> Active (scholarship is now ongoing/disbursing) */
    public function activate(int $scholarshipId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships SET status = 'Active'
            WHERE scholarship_id = :id AND status = 'Approved'
        ");
        $stmt->execute(['id' => $scholarshipId]);
        return $stmt->rowCount() > 0;
    }

    /** Active -> Completed/Terminated. $notes appended to review_notes (per owner's call: no separate termination_reason field). */
    public function finalize(int $scholarshipId, string $outcome, ?string $notes = null): bool
    {
        $status = $outcome === 'completed' ? 'Completed' : 'Terminated';

        // Read current notes first, then append in PHP — simpler and
        // less error-prone than building the concatenation in SQL.
        $stmt = $this->conn->prepare("SELECT review_notes FROM gd_scholarships WHERE scholarship_id = :id AND status = 'Active'");
        $stmt->execute(['id' => $scholarshipId]);
        $existing = $stmt->fetchColumn();
        if ($existing === false) {
            return false; // not found, or not currently Active
        }

        $notes = trim($notes ?? '');
        $tag = "[{$status}] {$notes}";
        $updatedNotes = $notes === ''
            ? $existing
            : (empty($existing) ? $tag : $existing . "\n\n" . $tag);

        $update = $this->conn->prepare("
            UPDATE gd_scholarships
            SET status = :status, review_notes = :notes
            WHERE scholarship_id = :id AND status = 'Active'
        ");
        $update->execute(['status' => $status, 'notes' => $updatedNotes, 'id' => $scholarshipId]);
        return $update->rowCount() > 0;
    }

    public function setAttachment(int $scholarshipId, string $path, string $originalName): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE gd_scholarships SET attachment_path = :path, attachment_name = :name
            WHERE scholarship_id = :id
        ");
        return $stmt->execute(['path' => $path, 'name' => $originalName, 'id' => $scholarshipId]);
    }

    /* ---------------------------------------------------------------
       Counselors (whole Guidance and Counseling Office department)
       — identical scoping to Cases::getCounselors()
    --------------------------------------------------------------- */
    public function getCounselors(): array
    {
        $stmt = $this->conn->prepare("
            SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) AS name, p.position_name
            FROM sms_employee e
            JOIN sd_position p ON p.position_id = e.position
            WHERE e.department = 8 AND e.status = 'active'
            ORDER BY e.last_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Look up a student by number, for validating student_number entered
     * at application-creation time (mirrors an equivalent check pattern
     * seen in Cases' incident-linking flow).
     */
    public function studentExists(string $studentNumber): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM rgr_students WHERE student_number = :sn");
        $stmt->execute(['sn' => $studentNumber]);
        return (bool) $stmt->fetchColumn();
    }
}