<?php

class ScheduleModel
{
    protected $table = 'laboratory_schedule';

    private $db;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $this->db = Database::connect();
    }

    // GET ALL
    public function getAll()
    {
        $sql = "
            SELECT 
                s.*,
                l.laboratory_name
            FROM {$this->table} s
            INNER JOIN laboratories l 
                ON s.lab_id = l.lab_id
            ORDER BY s.day, s.start_time
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET ONE
    public function getById($id)
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CHECK TIME CONFLICT
    public function hasConflict($lab_id, $day, $start_time, $end_time)
    {
        $sql = "
        SELECT COUNT(*)
        FROM {$this->table}
        WHERE lab_id = ?
        AND day = ?
        AND status != 'Cancelled'
        AND start_time < ?
        AND end_time > ?
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $lab_id,
            $day,
            $end_time,
            $start_time
        ]);

        return $stmt->fetchColumn() > 0;
    }

    // CREATE
    public function create($data)
    {
        // Check for time conflict
        if ($this->hasConflict(
            $data['lab_id'],
            $data['day'],
            $data['start_time'],
            $data['end_time']
        )) {
            return [
                'success' => false,
                'message' => 'There is a time conflict with an existing schedule.'
            ];
        }

        $sql = "
        INSERT INTO {$this->table}
        (
            lab_id,
            subject_code,
            subject_name,
            instructor,
            section,
            day,
            start_time,
            end_time,
            semester,
            school_year,
            status,
            remarks
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            $data['lab_id'],
            $data['subject_code'],
            $data['subject_name'],
            $data['instructor'],
            $data['section'],
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $data['semester'],
            $data['school_year'],
            $data['status'],
            $data['remarks']
        ]);

        return [
            'success' => $result,
            'message' => $result
                ? 'Schedule added successfully.'
                : 'Failed to add schedule.'
        ];
    }
    
    // UPDATE
    public function update($id, $data)
    {
        $sql = "
            UPDATE {$this->table}
            SET
                lab_id = ?,
                subject_code = ?,
                subject_name = ?,
                instructor = ?,
                section = ?,
                day = ?,
                start_time = ?,
                end_time = ?,
                semester = ?,
                school_year = ?,
                status = ?,
                remarks = ?
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['lab_id'],
            $data['subject_code'],
            $data['subject_name'],
            $data['instructor'],
            $data['section'],
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $data['semester'],
            $data['school_year'],
            $data['status'],
            $data['remarks'],
            $id
        ]);
    }

    // DELETE
    public function delete($id)
    {
        $sql = "
            DELETE FROM {$this->table}
            WHERE schedule_id = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }
}
