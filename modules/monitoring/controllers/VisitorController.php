<?php
require_once '../models/Visitor.php';
require_once '../models/VisitorArchive.php';

class VisitorController {
    private $visitorModel;
    private $archiveModel;
    private $uploadDir;
    
    public function __construct() {
        $this->visitorModel = new Visitor();
        $this->archiveModel = new VisitorArchive();
        $this->uploadDir = __DIR__ . '/../public/uploads/visitor_ids/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getArchive() {
        try {
            $name = $_GET['name'] ?? null;
            return $this->archiveModel->getAllArchives($name);
        } catch (Exception $e) {
            error_log("Error in getArchive (Visitor): " . $e->getMessage());
            return [];
        }
    }
    
    // ============================================
    // GET METHODS - Called from index.php
    // ============================================
    
    // GET /api/visitor/today
    public function getTodayVisitors() {
        try {
            return $this->visitorModel->getByDate(date('Y-m-d'));
        } catch (Exception $e) {
            error_log("Error in getTodayVisitors: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/inside
    public function getVisitorsInside() {
        try {
            return $this->visitorModel->getInsideVisitors();
        } catch (Exception $e) {
            error_log("Error in getVisitorsInside: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/history?start=YYYY-MM-DD&end=YYYY-MM-DD
    public function getVisitorHistory($startDate, $endDate) {
        try {
            return $this->visitorModel->getByDateRange($startDate, $endDate);
        } catch (Exception $e) {
            error_log("Error in getVisitorHistory: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/all
    public function getAllVisitors() {
        try {
            return $this->visitorModel->getAll();
        } catch (Exception $e) {
            error_log("Error in getAllVisitors: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/pending-approvals
    public function getPendingApprovals() {
        try {
            return $this->visitorModel->getPendingApprovals();
        } catch (Exception $e) {
            error_log("Error in getPendingApprovals: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/statistics
    public function getStatistics() {
        try {
            return $this->visitorModel->getStatistics();
        } catch (Exception $e) {
            error_log("Error in getStatistics: " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/visitor/search?keyword=...
    public function searchVisitors($keyword) {
        try {
            if (empty($keyword)) {
                return ['success' => false, 'error' => 'Search keyword is required'];
            }
            $results = $this->visitorModel->searchVisitors($keyword);
            return ['success' => true, 'data' => $results];
        } catch (Exception $e) {
            error_log("Error in searchVisitors: " . $e->getMessage());
            return ['success' => false, 'error' => 'Search failed'];
        }
    }
    
    // GET /api/visitor/{id}
    public function getVisitor($id) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Visitor ID is required'];
            }
            $visitor = $this->visitorModel->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            return $visitor;
        } catch (Exception $e) {
            error_log("Error in getVisitor: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get visitor'];
        }
    }
    
    // ============================================
    // PUBLIC REGISTRATION METHODS - No auth required
    // ============================================
    
    // POST /api/visitor/register
    public function registerVisitor($data, $files = null) {
        try {
            // Validate required fields
            $required = ['visitor_name', 'contact_number', 'purpose_of_visit', 'home_address', 'person_to_visit'];
            $errors = [];
            
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            // Validate privacy consent
            if (!isset($data['privacy_consent']) || $data['privacy_consent'] != '1') {
                $errors[] = "You must agree to the Privacy Notice";
            }
            
            // Validate contact number format
            if (isset($data['contact_number']) && !preg_match('/^[0-9+\-\s()]{7,20}$/', $data['contact_number'])) {
                $errors[] = "Invalid contact number format";
            }
            
            if (!empty($errors)) {
                error_log("Validation errors: " . print_r($errors, true));
                return ['success' => false, 'errors' => $errors];
            }
            
            // Handle ID attachment upload
            $idAttachmentPath = null;
            
            if ($files === null || empty($files)) {
                error_log("No files received");
                return ['success' => false, 'errors' => ['No file uploaded. Please select an ID image.']];
            }
            
            if (!isset($files['id_attachment'])) {
                error_log("id_attachment not found in files. Available: " . print_r(array_keys($files), true));
                return ['success' => false, 'errors' => ['ID attachment field is missing']];
            }
            
            $file = $files['id_attachment'];
            error_log("File data: " . print_r($file, true));
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds php.ini upload_max_filesize (' . ini_get('upload_max_filesize') . ')',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
                    UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload',
                ];
                $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
                error_log("Upload error: " . $errorMsg);
                return ['success' => false, 'errors' => ['File upload error: ' . $errorMsg]];
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            error_log("File info - Mime: " . $mimeType . ", Extension: " . $extension . ", Size: " . $file['size']);
            
            $isAllowedMime = in_array($mimeType, $allowedTypes);
            $isAllowedExt = in_array($extension, $allowedExtensions);
            
            if (!$isAllowedMime && !$isAllowedExt) {
                return ['success' => false, 'errors' => [
                    'Invalid file type. Only JPG, JPEG, and PNG are allowed.',
                    'Your file: ' . $mimeType . ' (' . $extension . ')'
                ]];
            }
            
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                return ['success' => false, 'errors' => [
                    'File size exceeds 5MB limit.',
                    'Your file: ' . round($file['size'] / 1024 / 1024, 2) . 'MB'
                ]];
            }
            
            // Generate unique filename
            $filename = 'ID_' . uniqid() . '_' . date('Ymd') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;
            
            error_log("Saving to: " . $filepath);
            
            if (!file_exists($this->uploadDir)) {
                error_log("Creating upload directory: " . $this->uploadDir);
                if (!mkdir($this->uploadDir, 0777, true)) {
                    error_log("Failed to create upload directory");
                    return ['success' => false, 'errors' => ['Failed to create upload directory']];
                }
            }
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $idAttachmentPath = 'uploads/visitor_ids/' . $filename;
                error_log("File uploaded successfully to: " . $idAttachmentPath);
            } else {
                error_log("Failed to move uploaded file from: " . $file['tmp_name'] . " to: " . $filepath);
                error_log("Last error: " . print_r(error_get_last(), true));
                return ['success' => false, 'errors' => ['Failed to save uploaded file']];
            }
            
            $data['id_attachment'] = $idAttachmentPath;
            
            error_log("Calling createVisitor with data: " . print_r($data, true));
            
            $result = $this->visitorModel->createVisitor($data);
            
            error_log("createVisitor result: " . print_r($result, true));
            
            if (!$result['success']) {
                if (file_exists($filepath)) {
                    unlink($filepath);
                    error_log("Deleted uploaded file: " . $filepath);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in registerVisitor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'error' => 'Failed to register visitor: ' . $e->getMessage()];
        }
    }
    
    private function uploadID($file) {
        try {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
            $maxSize = 5 * 1024 * 1024;
            
            $fileType = strtolower($file['type'] ?? '');
            $fileExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            $isAllowedType = in_array($fileType, $allowedTypes);
            $isAllowedExt = in_array($fileExt, $allowedExtensions);
            
            if (!$isAllowedType && !$isAllowedExt) {
                return ['success' => false, 'error' => 'Invalid file type. Only JPG, JPEG, PNG, WebP, HEIC, and HEIF are allowed.'];
            }
            
            if ($file['size'] > $maxSize) {
                return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'ID_' . uniqid() . '_' . date('Ymd') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return ['success' => true, 'file_path' => 'uploads/visitor_ids/' . $filename];
            } else {
                return ['success' => false, 'error' => 'Failed to upload file'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Upload error: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // POST METHODS - Called from index.php
    // ============================================
    
    // POST /api/visitor/entry
    public function logEntry($data) {
        try {
            $required = ['visitor_name', 'contact_number', 'purpose_of_visit'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $data['time_in'] = date('Y-m-d H:i:s');
            $data['status'] = 'inside';
            $data['monitored_by'] = isset($data['monitored_by']) ? $data['monitored_by'] : 'Administrator';
            
            return $this->visitorModel->create($data);
        } catch (Exception $e) {
            error_log("Error in logEntry: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to log entry: ' . $e->getMessage()];
        }
    }
    
    // POST /api/visitor/exit/{id}
    public function logExit($id) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Visitor ID is required'];
            }
            
            $visitor = $this->visitorModel->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            
            if ($visitor['status'] !== 'inside') {
                return ['success' => false, 'error' => 'Visitor is already checked out'];
            }
            
            $timeOut = date('Y-m-d H:i:s');
            return $this->visitorModel->checkout($id, $timeOut);
        } catch (Exception $e) {
            error_log("Error in logExit: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to log exit: ' . $e->getMessage()];
        }
    }
    
    // POST /api/visitor/checkout-by-identifier
    public function checkoutByIdentifier($data) {
        try {
            if (!isset($data['identifier']) || empty(trim($data['identifier']))) {
                return ['success' => false, 'error' => 'Identifier is required'];
            }
            
            return $this->visitorModel->checkoutByIdentifier($data['identifier']);
        } catch (Exception $e) {
            error_log("Error in checkoutByIdentifier: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to checkout: ' . $e->getMessage()];
        }
    }
    
    // POST /api/visitor/update/{id}
    public function updateVisitor($id, $data) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Visitor ID is required'];
            }
            
            $visitor = $this->visitorModel->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            
            return $this->visitorModel->update($id, $data);
        } catch (Exception $e) {
            error_log("Error in updateVisitor: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update visitor: ' . $e->getMessage()];
        }
    }
    
    // POST /api/visitor/approve/{id}
    public function approveID($id, $adminId = null) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Visitor ID is required'];
            }
            
            if (!$adminId && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
                $adminId = $_SESSION['user_id'];
            }
            
            if (!$adminId) {
                return ['success' => false, 'error' => 'Admin ID is required'];
            }
            
            $visitor = $this->visitorModel->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            
            if ($visitor['id_approved'] == 1) {
                return ['success' => false, 'error' => 'ID already approved'];
            }
            
            return $this->visitorModel->approveID($id, $adminId);
        } catch (Exception $e) {
            error_log("Error in approveID: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to approve ID: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // DELETE METHODS - Called from index.php
    // ============================================
    
    // DELETE /api/visitor/delete/{id}
    public function deleteVisitor($id) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Visitor ID is required'];
            }
            
            $visitor = $this->visitorModel->getById($id);
            if (!$visitor) {
                return ['success' => false, 'error' => 'Visitor not found'];
            }
            
            $this->visitorModel->logAudit($id, 'delete', 'Visitor record deleted');
            
            return $this->visitorModel->delete($id);
        } catch (Exception $e) {
            error_log("Error in deleteVisitor: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete visitor: ' . $e->getMessage()];
        }
    }

    // POST /api/visitor/archive
    public function archiveVisitors($data) {
        try {
            $archivedBy = $data['archived_by'] ?? ($_SESSION['fullname'] ?? 'System');
            
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                foreach ($data['ids'] as $id) {
                    $visitor = $this->visitorModel->getById($id);
                    if ($visitor) {
                        $archiveData = $visitor;
                        $archiveData['original_id'] = $visitor['id'];
                        unset($archiveData['id']);
                        $archiveData['archived_by'] = $archivedBy;
                        $archiveData['archived_at'] = date('Y-m-d H:i:s');
                        
                        $res = $this->archiveModel->create($archiveData);
                        if ($res && ($res['success'] ?? false)) {
                            $this->visitorModel->delete($id);
                            $count++;
                        }
                    }
                }
                return ['success' => true, 'archived_count' => $count];
            }
            return ['success' => false, 'error' => 'No visitor IDs provided'];
        } catch (Exception $e) {
            error_log("Error in archiveVisitors: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to archive visitor records: ' . $e->getMessage()];
        }
    }

    // ⭐ NEW RESTORE METHOD
    public function restoreVisitors($data) {
        try {
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                $lastError = 'Visitor log not found in archive.';

                foreach ($data['ids'] as $id) {
                    $archivedVisitor = $this->archiveModel->getById($id);
                    if ($archivedVisitor) {
                        $restoreData = $archivedVisitor;
                        unset($restoreData['id']);
                        unset($restoreData['original_id']);
                        unset($restoreData['archived_at']);

                        $res = $this->visitorModel->create($restoreData);
                        if ($res && ($res['success'] ?? false)) {
                            $this->archiveModel->delete($id);
                            $count++;
                        } else {
                            $lastError = $res['error'] ?? 'Database restore failed.';
                        }
                    }
                }

                if ($count > 0) {
                    return ['success' => true, 'restored_count' => $count];
                }
                return ['success' => false, 'error' => 'Restore Failed: ' . $lastError];
            }
            return ['success' => false, 'error' => 'No visitor IDs provided'];
        } catch (Exception $e) {
            error_log("Error in restoreVisitors: " . $e->getMessage());
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }
}