<?php
include_once __DIR__ . '/../../../database/db.php';

/**
 * CentralFile — metadata for documents stored on the central FTP server.
 * Table: sd_central_files. Same constructor convention as Approval.php:
 * pass a PDO to share a transaction, or omit it to open a new connection.
 *
 * A record's own file_path column holds a reference "central:<file_uuid>"
 * for central files, so existing "has attachment" checks keep working and
 * legacy local paths (approvals/attachments/...) keep being served as before.
 */
class CentralFile
{
    const REF_PREFIX = 'central:';

    private $conn;

    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // ── Reference helpers ────────────────────────────────────────
    public static function isRef($value)
    {
        return is_string($value) && strncmp($value, self::REF_PREFIX, strlen(self::REF_PREFIX)) === 0;
    }

    public static function makeRef($uuid)
    {
        return self::REF_PREFIX . $uuid;
    }

    /** Returns the uuid, or null if $ref is not a well-formed central reference. */
    public static function uuidFromRef($ref)
    {
        if (!self::isRef($ref)) {
            return null;
        }
        $uuid = substr($ref, strlen(self::REF_PREFIX));
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) ? $uuid : null;
    }

    public static function newUuid()
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }

    /** Display-only MIME guess from a file name (the authoritative MIME is stored in the table). */
    public static function mimeFromName($name)
    {
        $map = [
            'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        return $map[$ext] ?? 'application/octet-stream';
    }

    // ── Queries ──────────────────────────────────────────────────
    /**
     * $row keys: file_uuid, module_type, record_id, original_name, stored_name,
     *            storage_path, mime_type, file_size, uploaded_by
     * Returns the new file_id.
     */
    public function insert(array $row)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO sd_central_files
                (file_uuid, module_type, record_id, original_name, stored_name,
                 storage_path, mime_type, file_size, uploaded_by)
             VALUES
                (:file_uuid, :module_type, :record_id, :original_name, :stored_name,
                 :storage_path, :mime_type, :file_size, :uploaded_by)"
        );
        $stmt->execute([
            ':file_uuid'     => $row['file_uuid'],
            ':module_type'   => $row['module_type'],
            ':record_id'     => (int) $row['record_id'],
            ':original_name' => $row['original_name'],
            ':stored_name'   => $row['stored_name'],
            ':storage_path'  => $row['storage_path'],
            ':mime_type'     => $row['mime_type'],
            ':file_size'     => (int) $row['file_size'],
            ':uploaded_by'   => (int) $row['uploaded_by'],
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /** Active (not soft-deleted) file by uuid, or null. */
    public function getByUuid($uuid)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM sd_central_files WHERE file_uuid = :uuid AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':uuid' => $uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Active files attached to one record. */
    public function getActiveByRecord($moduleType, $recordId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM sd_central_files
             WHERE module_type = :m AND record_id = :r AND deleted_at IS NULL
             ORDER BY file_id ASC"
        );
        $stmt->execute([':m' => $moduleType, ':r' => (int) $recordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Soft delete. Returns true if a row was marked deleted. */
    public function softDeleteByUuid($uuid)
    {
        $stmt = $this->conn->prepare(
            "UPDATE sd_central_files SET deleted_at = NOW()
             WHERE file_uuid = :uuid AND deleted_at IS NULL"
        );
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->rowCount() > 0;
    }
}