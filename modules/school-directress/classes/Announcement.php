<?php

include_once __DIR__ . '/../../../database/db.php';

/** Expected, user-correctable problems (not found, already archived). */
if (!class_exists('AnnouncementException')) {
    class AnnouncementException extends \RuntimeException {
        public $status;

        public function __construct($message, $status = 422) {
            parent::__construct($message);
            $this->status = (int) $status;
        }
    }
}

class Announcement {
    private $conn;
    private $uploadDir = __DIR__ . '/../uploads/announcements/';

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /* ── Existing: create (unchanged) ───────────────────────── */

    public function postAnnouncement($title, $message, $publishDate, $createdBy, $imageFile = null) {
        $imageFilename = null;

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType     = (new finfo(FILEINFO_MIME_TYPE))->file($imageFile['tmp_name']);

            if (!in_array($mimeType, $allowedTypes, true)) {
                return ['success' => false, 'message' => 'Invalid image type.'];
            }

            if ($imageFile['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Image must be 5 MB or smaller.'];
            }

            $imageFilename = uniqid('ann_', true) . '.' . strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
            move_uploaded_file($imageFile['tmp_name'], $this->uploadDir . $imageFilename);
        }

        $sql = "INSERT INTO `sd_announcements` (`title`, `message`, `publish_date`, `created_by`, `image_file`)
                VALUES (:title, :message, :publish_date, :created_by, :image_file)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':title'        => $title,
            ':message'      => $message,
            ':publish_date' => $publishDate,
            ':created_by'   => $createdBy,
            ':image_file'   => $imageFilename,
        ]);

        return $this->conn->lastInsertId();
    }

    /* ── Active list: archived posts are excluded ───────────── */

    public function getAnnouncement() {
        $sql = "SELECT 
                    a.announcement_id,
                    a.title,
                    a.message,
                    a.publish_date,
                    a.image_file,
                    CONCAT(e.first_name, ' ', e.last_name) AS created_by
                FROM `sd_announcements` a
                LEFT JOIN `em_employees` e ON a.created_by = e.employee_id
                WHERE a.is_archived = 0
                ORDER BY a.publish_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Archived list (search + pagination) ────────────────── */

    private function archivedWhere($search) {
        $where  = 'a.is_archived = 1';
        $params = [];

        $search = trim((string) $search);
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where .= " AND (a.title LIKE :q1
                             OR a.message LIKE :q2
                             OR CONCAT_WS(' ', c.first_name, c.last_name) LIKE :q3)";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        return [$where, $params];
    }

    public function countArchived($search = '') {
        list($where, $params) = $this->archivedWhere($search);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM sd_announcements a
             LEFT JOIN em_employees c ON c.employee_id = a.created_by
             WHERE $where"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getArchived($search = '', $limit = 10, $offset = 0) {
        list($where, $params) = $this->archivedWhere($search);

        $sql = "SELECT a.announcement_id, a.title, a.message, a.publish_date, a.image_file, a.archived_at,
                       TRIM(CONCAT_WS(' ', c.first_name, c.last_name))  AS created_by_name,
                       TRIM(CONCAT_WS(' ', ab.first_name, ab.last_name)) AS archived_by_name
                FROM sd_announcements a
                LEFT JOIN em_employees c  ON c.employee_id  = a.created_by
                LEFT JOIN em_employees ab ON ab.employee_id = a.archived_by
                WHERE $where
                ORDER BY a.archived_at DESC, a.announcement_id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $text = trim(strip_tags((string) $r['message']));
            $r['preview'] = mb_strlen($text) > 160 ? mb_substr($text, 0, 160) . '…' : $text;
            unset($r['message']); // the list only needs the preview
        }
        unset($r);
        return $rows;
    }

    /* ── Archive / restore ──────────────────────────────────── */

    // The conditional UPDATE is atomic, so two clicks (or two admins) can't double-archive.
    public function archiveAnnouncement($id, $employeeId) {
        $stmt = $this->conn->prepare(
            "UPDATE sd_announcements
             SET is_archived = 1, archived_at = NOW(), archived_by = :by
             WHERE announcement_id = :id AND is_archived = 0"
        );
        $stmt->execute([':by' => (int) $employeeId, ':id' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            $this->explainNoChange($id, 'archived');
        }
    }

    // Restore only clears the archive flag. Dates, image and content are untouched.
    public function restoreAnnouncement($id) {
        $stmt = $this->conn->prepare(
            "UPDATE sd_announcements
             SET is_archived = 0, archived_at = NULL, archived_by = NULL
             WHERE announcement_id = :id AND is_archived = 1"
        );
        $stmt->execute([':id' => (int) $id]);

        if ($stmt->rowCount() === 0) {
            $this->explainNoChange($id, 'active');
        }
    }

    private function explainNoChange($id, $alreadyState) {
        $s = $this->conn->prepare('SELECT is_archived FROM sd_announcements WHERE announcement_id = :id');
        $s->execute([':id' => (int) $id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new AnnouncementException('Announcement not found.', 404);
        }
        throw new AnnouncementException('This announcement is already ' . $alreadyState . '.', 409);
    }
}
?>