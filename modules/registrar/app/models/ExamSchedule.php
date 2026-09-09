<?php 

namespace App\Models;

use App\Core\Model;
use PDO;


class ExamSchedule extends Model
{

    public $tableName = 'cc_exam_schedule';
    public $primaryKey = 'id';



 protected function getSchedule()
 {
  
    $section = $_GET['section_id'] ?? null;
    $exam_date = $_GET['exam_date'] ?? null;
    $exam_id = $_GET['exam_id'] ?? null;

    $sql = "SELECT 
        cces.start_time AS exam_start,
        cces.end_time AS exam_end,
        DAYNAME(cces.exam_date) AS exam_day,
        cces.schedule_type AS exam_type,
        ccr.room_code AS room_code,
        CONCAT(ccf.first_name, '  ', ccf.last_name) AS proctor,
        ccr.room_name AS room_name,
        rs.code AS subject_code,
        rs.name AS subject_name
        FROM $this->tableName cces
        JOIN cc_room ccr ON ccr.id = cces.room_id
        LEFT JOIN rgr_subjects rs ON rs.id = cces.subject_id
        LEFT JOIN cc_exam_proctor ccep ON ccep.exam_schedule_id = cces.id
        LEFT JOIN cc_faculty ccf ON ccf.id = ccep.faculty_id
        WHERE cces.section_id = :section_id
        AND cces.exam_date = :exam_date
        AND cces.exam_id = :exam_id";

         $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':exam_id'   => $exam_id,
        ':exam_date' => $exam_date,
        ':section_id' => $section
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
      
 }

  protected function getSectionOnThatDay()
{
    $exam_id = $_GET['exam_id'] ?? null;
    $exam_date = $_GET['exam_date'] ?? null;

    $sql = "SELECT DISTINCT
                es.section_id,
                sec.section_code
            FROM $this->tableName es
            INNER JOIN cc_sections sec
                ON sec.id = es.section_id
            WHERE es.exam_id = :exam_id
            AND es.exam_date = :exam_date
            ORDER BY sec.section_code ASC";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':exam_id'   => $exam_id,
        ':exam_date' => $exam_date,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }

}