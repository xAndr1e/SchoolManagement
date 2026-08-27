<?php
require_once 'Model.php';

class Report extends Model {

    public function getDetailedRecords($type, $startDate, $endDate) {
        try {
            if ($type === 'attendance') {
                $stmt = $this->db->prepare("
                    SELECT 
                        id, 
                        faculty_name, 
                        course_section, 
                        subject_code, 
                        room, 
                        status, 
                        verification_method, 
                        attendance_date,
                        student_count,
                        is_online,
                        meeting_link,
                        remarks,
                        check_time,
                        created_at
                    FROM mon_attendance_records 
                    WHERE attendance_date BETWEEN ? AND ? 
                    ORDER BY attendance_date DESC, check_time DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($type === 'facility') {
                $stmt = $this->db->prepare("
                    SELECT 
                        id, 
                        room_number, 
                        broken_equipment, 
                        equipment_type,
                        quantity_damaged, 
                        description, 
                        reported_by, 
                        status, 
                        report_date,
                        resolved_date,
                        damage_image
                    FROM mon_facility_reports 
                    WHERE report_date BETWEEN ? AND ? 
                    ORDER BY report_date DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($type === 'visitor') {
                $stmt = $this->db->prepare("
                    SELECT 
                        id, 
                        visitor_name, 
                        contact_number, 
                        email,
                        purpose_of_visit,
                        person_to_visit,
                        department,
                        time_in, 
                        time_out, 
                        status,
                        id_attachment,
                        vehicle_type,
                        vehicle_plate,
                        qr_code,
                        created_at
                    FROM mon_visitors 
                    WHERE DATE(time_in) BETWEEN ? AND ? 
                    ORDER BY time_in DESC
                ");
                $stmt->execute([$startDate, $endDate]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return [];
        } catch (PDOException $e) {
            error_log("Error in getDetailedRecords: " . $e->getMessage());
            return [];
        }
    }

    public function generateAttendanceReport($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    faculty_name,
                    course_section,
                    subject_code,
                    room,
                    status,
                    verification_method,
                    attendance_date,
                    student_count,
                    is_online,
                    meeting_link,
                    remarks,
                    check_time,
                    created_at
                FROM mon_attendance_records
                WHERE attendance_date BETWEEN ? AND ?
                ORDER BY attendance_date DESC, check_time DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in generateAttendanceReport: " . $e->getMessage());
            return [];
        }
    }

    public function generateFacilityReport($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    room_number,
                    broken_equipment as equipment,
                    equipment_type,
                    quantity_damaged as quantity,
                    description,
                    reported_by,
                    report_date as date,
                    status,
                    damage_image as photo,
                    resolved_date,
                    created_at
                FROM mon_facility_reports
                WHERE report_date BETWEEN ? AND ?
                ORDER BY report_date DESC, room_number ASC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in generateFacilityReport: " . $e->getMessage());
            return [];
        }
    }

    public function generateVisitorReport($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    visitor_name,
                    contact_number,
                    email,
                    purpose_of_visit,
                    person_to_visit,
                    department,
                    time_in,
                    time_out,
                    status,
                    id_attachment,
                    vehicle_type,
                    vehicle_plate,
                    qr_code,
                    created_at
                FROM mon_visitors
                WHERE DATE(time_in) BETWEEN ? AND ?
                ORDER BY time_in DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in generateVisitorReport: " . $e->getMessage());
            return [];
        }
    }

    public function getFacilityStatusSummary($startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count,
                    GROUP_CONCAT(DISTINCT room_number ORDER BY room_number SEPARATOR ', ') as rooms,
                    SUM(quantity_damaged) as total_damaged
                FROM mon_facility_reports
                WHERE report_date BETWEEN ? AND ?
                GROUP BY status
                ORDER BY 
                    CASE status
                        WHEN 'reported' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'resolved' THEN 3
                        WHEN 'replaced' THEN 4
                    END
            ");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getFacilityStatusSummary: " . $e->getMessage());
            return [];
        }
    }
    public function getComprehensiveStats($startDate, $endDate) {
        try {
            $attendanceCount = count($this->generateAttendanceReport($startDate, $endDate));
            $facilityCount = count($this->generateFacilityReport($startDate, $endDate));
            $visitorCount = count($this->generateVisitorReport($startDate, $endDate));

            return [
                'total_attendance' => $attendanceCount,
                'total_facility_reports' => $facilityCount,
                'total_visitors' => $visitorCount,
                'total_records' => $attendanceCount + $facilityCount + $visitorCount
            ];
        } catch (PDOException $e) {
            error_log("Error in getComprehensiveStats: " . $e->getMessage());
            return [];
        }
    }
    
}