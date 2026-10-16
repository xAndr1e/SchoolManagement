
<?php

require_once __DIR__ . '/core/DatabaseTwo.php';

class Issues extends DatabaseTwo
{
    protected $table = 'sd_issues';

    public function __construct()
    {
        $this->conn = DatabaseTwo::getInstance()->getConnection();
    }

    /**
     * Get all issues.
     */
    public function all(): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM sd_issues
            ORDER BY submitted_on DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new issue.
     */
    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO sd_issues (
                title,
                details,
                desired_resolution,
                department,
                submitted_by,
                file_path,
                status,
                submitted_on
            ) VALUES (
                :title,
                :details,
                :desired_resolution,
                :department,
                :submitted_by,
                :file_path,
                'submitted',
                NOW()
            )
        ");

        return $stmt->execute([
            ':title' => $data['title'],
            ':details' => $data['details'] ?? $data['description'] ?? '',
            ':desired_resolution' => $data['desired_resolution'] ?? null,
            ':department' => $data['department'],
            ':submitted_by' => $data['submitted_by'],
            ':file_path' => $data['file_path'] ?? null,
        ]);
    }

    /**
     * Update an issue's status.
     */
    public function updateStatus(int $issueId, string $status): bool
    {
        $allowedStatuses = [
            'draft',
            'submitted',
            'reviewed',
            'resolved'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Invalid issue status.');
        }

        $stmt = $this->conn->prepare("
            UPDATE sd_issues
            SET status = :status,
                updated_at = NOW()
            WHERE issue_id = :issue_id
        ");

        return $stmt->execute([
            ':status' => $status,
            ':issue_id' => $issueId,
        ]);
    }

    /**
     * Get issues by status.
     */
    public function getByStatus(string $status): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM sd_issues
            WHERE status = :status
            ORDER BY submitted_on DESC
        ");

        $stmt->execute([
            ':status' => $status,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete an issue.
     */
    public function delete(int $issueId): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM sd_issues
            WHERE issue_id = :id
        ");

        return $stmt->execute([
            ':id' => $issueId,
        ]);
    }
}
