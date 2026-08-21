<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Schedule extends Model
 {

     public $tableName = 'cc_schedule';
     public $primaryKey = 'id';



    protected function allClassList($paginate = true)
    {
    $school_year = $_GET['school_year'] ?? '';
    $semester    = $_GET['semester'] ?? '';
    $section     = $_GET['section'] ?? '';
    $subject     = $_GET['subject'] ?? '';

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $perPage;

    $order = $_GET['order'] ?? 'desc';

    $order = in_array(strtolower($order), ['asc', 'desc'])
        ? strtoupper($order)
        : 'DESC';

    $search = isset($_GET['search'])
        ? trim($_GET['search'])
        : '';

    $where = " WHERE 1=1 ";

    $params = [];


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    if (!empty($school_year)) {
        $where .= " AND ccs.school_year_id = :school_year ";
        $params[':school_year'] = $school_year;
    }

    if (!empty($semester)) {
        $where .= " AND ccs.semester_id = :semester ";
        $params[':semester'] = $semester;
    }

    if (!empty($section)) {
        $where .= " AND ccs.section_id = :section ";
        $params[':section'] = $section;
    }

    if (!empty($subject)) {
        $where .= " AND ccs.subject_id = :subject ";
        $params[':subject'] = $subject;
    }




    if (!empty($search)) {

        $where .= " AND (
            rs.code LIKE :search
            OR rs.name LIKE :search
            OR ccsec.section_code LIKE :search
        ) ";

        $params[':search'] = "%{$search}%";
    }


 

    $countSql = "
        SELECT COUNT(DISTINCT ccs.id) AS total

        FROM cc_schedule ccs

        LEFT JOIN rgr_subjects rs
            ON rs.id = ccs.subject_id

        LEFT JOIN cc_sections ccsec
            ON ccsec.id = ccs.section_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = ccs.semester_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = ccs.school_year_id

        $where
    ";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $total = (int) $countStmt
        ->fetch(PDO::FETCH_ASSOC)['total'];


  

    $dataSql = "
        SELECT

            ccs.id AS schedule_id,

            ccsec.id AS section_id,
            ccsec.section_code AS section,

            rs.id AS subject_id,
            rs.code AS subject_code,
            rs.name AS subject_name,
            rs.units,

            rsem.id AS semester_id,
            rsem.name AS semester,

            rsy.id AS school_year_id,
            rsy.name AS school_year,

            ccs.day_of_week,
            ccs.start_time,
            ccs.end_time,

            ccs.room_id,

            room.room_code AS room,

            (
                SELECT COUNT(*)
                FROM enr_enrollments ee
                WHERE ee.schedule_id = ccs.id
                  AND ee.enrollment_status = 'enrolled'
            ) AS student_count

        FROM $this->tableName ccs

        LEFT JOIN rgr_subjects rs
            ON rs.id = ccs.subject_id

        LEFT JOIN cc_sections ccsec
            ON ccsec.id = ccs.section_id

        LEFT JOIN rgr_semesters rsem
            ON rsem.id = ccs.semester_id

        LEFT JOIN rgr_school_years rsy
            ON rsy.id = ccs.school_year_id

        LEFT JOIN cc_room room
            ON room.id = ccs.room_id

        $where

        ORDER BY ccs.id $order
    ";

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset ";
    }


    $dataStmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }

    if ($paginate) {

        $dataStmt->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $dataStmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );
    }

    $dataStmt->execute();

    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    if (!$paginate) {
        return $data;
    }

    return [
        'data' => $data,
        'total' => $total,
        'current_page' => $page,
        'last_page' => $perPage > 0
            ? ceil($total / $perPage)
            : 1
    ];
    }
     

    
     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }