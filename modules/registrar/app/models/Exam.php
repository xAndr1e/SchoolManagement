<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Exam extends Model
 {
  
    public $tableName= 'cc_exams';
    public $primaryKey = 'id';


   protected function allExaminations($paginate = true)
    {

    // $year_level = $_GET['year_level'] ?? '';
    // $course = $_GET['course'] ?? '';
    // $school_year_select = $_GET['school_year'] ?? '';
     
    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

     $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';
    
    

    $where = " WHERE 1=1 ";
    
    $params = [];


//    if ($year_level !== '') {
//     $where .= " AND stud.year_level = :year_level";
//     $params[':year_level'] = $year_level;
//    }


//    if ($course !== '') {
//     $where .= " AND stud.course_id = :course";
//     $params[':course'] = $course;
//    }

//    if (!empty($school_year_select) && $school_year_select !== 'all_school_year') {
//     $where .= " AND sem.school_year_id = :school_year";
//     $params[':school_year'] = $school_year_select;
//    }




    if (!empty($status) && strtolower($status) !== 'all') {
        $where .= " AND ccx.status = :status";
        $params[':status'] = $status;
    }
    

    if (!empty($search)) {
        $where .= " AND (
            ccx.exam_name LIKE :search OR
            ccx.exam_type LIKE :search 
        )";
        $params[':search'] = "%{$search}%";
    }


    // school year

    $schoolYearSql = "SELECT name as school_year FROM rgr_school_years WHERE is_active = true";
    $schoolYearStmt = $this->pdo->prepare($schoolYearSql);

    $schoolYearStmt->execute();
    $school_year = $schoolYearStmt->fetch(PDO::FETCH_ASSOC)['school_year'];


    // Count total
    $countSql = "SELECT COUNT(*) as total  FROM {$this->tableName} ccx

    $where ORDER BY id $order ";
    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
    $dataSql = "SELECT 
    *
    FROM {$this->tableName} ccx
    $where ORDER BY id $order ";

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset";
    }

    $dataStmt = $this->pdo->prepare($dataSql);

    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }

    if ($paginate) {
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $dataStmt->execute();
    $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$paginate) {
        // return $data; // For PDF

        return [
            'data' => $data,
            'schoolYear' => $school_year
        ];

    }

    return [
        'data' => $data,
        'total' => (int) $total,
        'current_page' => $page,
        'last_page' => ceil($total / $perPage)
    ];
}

  protected function allExaminationsSplittedByTime($examId, $paginate = true)
{
    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $perPage;

    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $order = in_array($order, ['asc', 'desc'])
        ? strtoupper($order)
        : 'DESC';


    $where = " WHERE 1=1 ";
    $params = [];

    $where .= " AND ccx.id = :exam_id";
    $params[':exam_id'] = $examId;



    if (!empty($status) && strtolower($status) !== 'all') {

        $where .= " AND ccx.status = :status";

        $params[':status'] = $status;
    }


    if (!empty($search)) {

        $where .= " AND (
            ccx.exam_name LIKE :search OR
            ccx.exam_type LIKE :search
        )";

        $params[':search'] = "%{$search}%";
    }


   

    $schoolYearSql = "
        SELECT name AS school_year
        FROM rgr_school_years
        WHERE is_active = true
    ";

    $schoolYearStmt = $this->pdo->prepare($schoolYearSql);
    $schoolYearStmt->execute();

    $schoolYearResult = $schoolYearStmt->fetch(PDO::FETCH_ASSOC);

    $school_year = $schoolYearResult['school_year'] ?? null;
  

    $countSql = "
        SELECT COUNT(DISTINCT es.exam_date) AS total
        FROM {$this->tableName} ccx
        INNER JOIN cc_exam_schedule es
            ON es.exam_id = ccx.id
        $where
    ";


    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];


   $dataSql = "
    SELECT 
        ccx.id AS exam_id,
        ccx.exam_name,
        ccx.exam_type,
        ccx.status,
        es.exam_date,
        COUNT(es.id) AS schedule_count
    FROM {$this->tableName} ccx
    INNER JOIN cc_exam_schedule es
        ON es.exam_id = ccx.id
    $where
    GROUP BY
        ccx.id,
        ccx.exam_name,
        ccx.exam_type,
        ccx.status,
        es.exam_date
    ORDER BY es.exam_date ASC
   ";

   

    if ($paginate) {
        $dataSql .= " LIMIT :limit OFFSET :offset";
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



    foreach ($data as $index => &$row) {

        $row['day'] = $index + 1;

    }

    unset($row);



    if (!$paginate) {

        return [
            'data' => $data,
            'schoolYear' => $school_year
        ];
    }


    return [
        'data' => $data,
        'total' => (int) $total,
        'current_page' => $page,
        'last_page' => ceil($total / $perPage)
    ];
}




     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }
    

 }