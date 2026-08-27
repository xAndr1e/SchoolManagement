<?php
require_once '../models/Facility.php';
require_once '../models/FacilityReport.php';
require_once '../models/FacilityReportArchive.php';

class FacilityController {
    private $facilityModel;
    private $reportModel;
    private $archiveModel;
    
    public function __construct() {
        $this->facilityModel = new Facility();
        $this->reportModel = new FacilityReport();
        $this->archiveModel = new FacilityReportArchive();
    }

    public function getArchive() {
        try {
            $room = $_GET['room'] ?? null;
            return $this->archiveModel->getAllArchives($room);
        } catch (Exception $e) {
            error_log("Error in getArchive (Facility): " . $e->getMessage());
            return [];
        }
    }
    
    // GET /api/facility/damaged
    public function getDamagedEquipment() {
        try {
            return $this->facilityModel->getDamagedEquipment();
        } catch (Exception $e) {
            error_log("Error in getDamagedEquipment: " . $e->getMessage());
            return ['error' => 'Failed to load damaged equipment'];
        }
    }
    
    // GET /api/facility/reports?status=reported
    public function getFacilityReports($status = null) {
        try {
            if ($status) {
                return $this->reportModel->getByStatus($status);
            }
            return $this->reportModel->getAll();
        } catch (Exception $e) {
            error_log("Error in getFacilityReports: " . $e->getMessage());
            return ['error' => 'Failed to load reports'];
        }
    }
    
    // GET /api/facility/all
    public function getAllFacilities() {
        try {
            return $this->facilityModel->getAll();
        } catch (Exception $e) {
            error_log("Error in getAllFacilities: " . $e->getMessage());
            return ['error' => 'Failed to load facilities'];
        }
    }
    
    // ============================================
    // POST METHODS - Called from index.php
    // ============================================
    
    // POST /api/facility/add
    public function addFacility($data) {
        try {
            $required = ['room_number', 'equipment_type', 'equipment_name', 'quantity'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
            $functional = isset($data['functional_quantity']) ? intval($data['functional_quantity']) : 0;
            $damaged = isset($data['damaged_quantity']) ? intval($data['damaged_quantity']) : 0;
            
            if ($quantity < 1) {
                return ['success' => false, 'error' => 'Quantity must be at least 1'];
            }
            
            if ($functional + $damaged > $quantity) {
                return ['success' => false, 'error' => 'Functional + Damaged cannot exceed total quantity'];
            }
            
            if ($functional === $quantity) {
                $data['status'] = 'complete';
            } else if ($functional > 0 && $damaged > 0) {
                $data['status'] = 'incomplete';
            } else if ($damaged === $quantity) {
                $data['status'] = 'needs_repair';
            } else {
                $data['status'] = 'incomplete';
            }
            
            $data['monitored_by'] = isset($data['monitored_by']) ? $data['monitored_by'] : 'Administrator';
            $data['last_checked'] = date('Y-m-d');
            
            return $this->facilityModel->create($data);
        } catch (Exception $e) {
            error_log("Error in addFacility: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add facility: ' . $e->getMessage()];
        }
    }
    
    // POST /api/facility/report
    public function reportBrokenEquipment($data, $files = null) {
        try {
            if ($files === null && isset($_FILES) && !empty($_FILES)) {
                $files = $_FILES;
            }

            $required = ['room_number', 'broken_equipment', 'equipment_type', 'reported_by'];
            $errors = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Handle Damage Photo Upload
            if ($files && isset($files['damage_image']) && $files['damage_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../public/uploads/facility_damage/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $files['damage_image'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'damage_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $data['damage_image'] = 'uploads/facility_damage/' . $filename;
                }
            }
            
            $data['report_date'] = date('Y-m-d');
            $data['status'] = isset($data['status']) ? $data['status'] : 'reported';
            $data['quantity_damaged'] = isset($data['quantity_damaged']) ? intval($data['quantity_damaged']) : 1;
            
            return $this->reportModel->create($data);
        } catch (Exception $e) {
            error_log("Error in reportBrokenEquipment: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to submit report: ' . $e->getMessage()];
        }
    }
    
    // POST /api/facility/update/{id} - for report status update
    public function updateReportStatus($id, $status, $resolvedDate = null) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Report ID is required'];
            }
            
            $report = $this->reportModel->getById($id);
            if (!$report) {
                return ['success' => false, 'error' => 'Report not found'];
            }
            
            $validStatuses = ['reported', 'in_progress', 'resolved', 'replaced'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'error' => 'Invalid status value'];
            }
            
            $data = ['status' => $status];
            if (in_array($status, ['resolved', 'replaced'])) {
                $data['resolved_date'] = $resolvedDate ?: date('Y-m-d');
            }
            
            return $this->reportModel->update($id, $data);
        } catch (Exception $e) {
            error_log("Error in updateReportStatus: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update report: ' . $e->getMessage()];
        }
    }
    
    // POST /api/facility/update-facility/{id}
    public function updateFacility($id, $data) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Facility ID is required'];
            }
            
            $facility = $this->facilityModel->getById($id);
            if (!$facility) {
                return ['success' => false, 'error' => 'Facility not found'];
            }
            
            if (isset($data['quantity'])) {
                $quantity = intval($data['quantity']);
                if ($quantity < 1) {
                    return ['success' => false, 'error' => 'Quantity must be at least 1'];
                }
            }
            
            if (isset($data['functional_quantity']) && isset($data['damaged_quantity'])) {
                $functional = intval($data['functional_quantity']);
                $damaged = intval($data['damaged_quantity']);
                $total = isset($data['quantity']) ? intval($data['quantity']) : intval($facility['quantity']);
                
                if ($functional + $damaged > $total) {
                    return ['success' => false, 'error' => 'Functional + Damaged cannot exceed total quantity'];
                }
                
                if ($functional === $total) {
                    $data['status'] = 'complete';
                } else if ($functional > 0 && $damaged > 0) {
                    $data['status'] = 'incomplete';
                } else if ($damaged === $total) {
                    $data['status'] = 'needs_repair';
                }
            }
            
            return $this->facilityModel->update($id, $data);
        } catch (Exception $e) {
            error_log("Error in updateFacility: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update facility: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // DELETE METHODS - Called from index.php
    // ============================================
    
    // DELETE /api/facility/delete/{id}
    public function deleteFacility($id) {
        try {
            if (!$id) {
                return ['success' => false, 'error' => 'Facility ID is required'];
            }
            return $this->facilityModel->delete($id);
        } catch (Exception $e) {
            error_log("Error in deleteFacility: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete facility: ' . $e->getMessage()];
        }
    }

    // POST /api/facility/archive
    public function archiveReports($data) {
        try {
            $archivedBy = $data['archived_by'] ?? ($_SESSION['fullname'] ?? 'System');
            
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                foreach ($data['ids'] as $id) {
                    $report = $this->reportModel->getById($id);
                    if ($report) {
                        $archiveData = $report;
                        $archiveData['original_id'] = $report['id'];
                        unset($archiveData['id']);
                        $archiveData['archived_by'] = $archivedBy;
                        $archiveData['archived_at'] = date('Y-m-d H:i:s');
                        
                        $res = $this->archiveModel->create($archiveData);
                        if ($res && ($res['success'] ?? false)) {
                            $this->reportModel->delete($id);
                            $count++;
                        }
                    }
                }
                return ['success' => true, 'archived_count' => $count];
            }
            return ['success' => false, 'error' => 'No report IDs provided'];
        } catch (Exception $e) {
            error_log("Error in archiveReports (Facility): " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to archive facility reports: ' . $e->getMessage()];
        }
    }

    // ⭐ NEW RESTORE METHOD
    public function restoreReports($data) {
        try {
            if (isset($data['ids']) && is_array($data['ids']) && !empty($data['ids'])) {
                $count = 0;
                $lastError = 'Report not found in archive.';

                foreach ($data['ids'] as $id) {
                    $archivedReport = $this->archiveModel->getById($id);
                    if ($archivedReport) {
                        $restoreData = $archivedReport;
                        unset($restoreData['id']);
                        unset($restoreData['original_id']);
                        unset($restoreData['archived_at']);

                        $res = $this->reportModel->create($restoreData);
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
            return ['success' => false, 'error' => 'No report IDs provided'];
        } catch (Exception $e) {
            error_log("Error in restoreReports: " . $e->getMessage());
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }
}