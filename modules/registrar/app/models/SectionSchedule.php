<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class SectionSchedule extends Model
 {

     public $tableName = 'cc_schedule';
     public $primaryKey = 'id';


     protected function allStudentSchedule()
      {
 
        $section = $_GET['section_id'] ?? null ;

        $sql = "SELECT 
                ccf.first_name AS first_name,
                ccf.last_name AS last_name,
                rsub.name AS subject_name,
                rsub.code AS subject_code,
                ccr.room_name AS room_name,
                ccs.day_of_week AS day,
                ccsec.section_code AS section_code,
                ccs.end_time AS end_time,
                ccs.start_time AS start_time
                FROM $this->tableName ccs
                JOIN cc_faculty_load ccfl ON ccfl.id = ccs.faculty_load_id
                JOIN cc_sections ccsec ON ccsec.id = ccs.section_id
                JOIN rgr_school_years rsy ON rsy.id = ccs.school_year_id
                JOIN rgr_semesters rsem ON rsem.id = ccs.semester_id
                JOIN cc_room ccr ON ccr.id = ccs.room_id
                JOIN rgr_subjects rsub ON rsub.id = ccs.subject_id
                JOIN  cc_faculty ccf ON ccf.id = ccfl.faculty_id
                WHERE ccsec.id = :section_id
                AND rsy.is_active = 1
                AND rsem.is_active = 1";

           $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
                ':section_id'   => $section,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }


      protected function getSectionScheduleAvailable()
      {
        $sql = "
                 SELECT 
                DISTINCT
                ccsec.id AS section_id,
                ccsec.section_code AS section_code
                FROM $this->tableName ccs
                JOIN cc_sections ccsec ON ccsec.id = ccs.section_id
        ";

          $stmt = $this->pdo->prepare($sql);
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);


      }

    
     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }