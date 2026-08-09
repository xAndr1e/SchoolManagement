<?php 
  
  namespace App\Models;

  use App\Core\Model;
  use PDO;

  class CurriculumSubject extends Model 
  {

    public $tableName = 'rgr_curriculum_subjects';
    public $primaryKey = 'id';



    protected function allCurriculumSubjects($id,$paginate=false )
    {
 
    $curriculum_id = $id ?? '';

    $perPage = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;

    $offset = ($page - 1) * $perPage;

    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    $order = in_array($order, ['asc', 'desc']) 
            ? strtoupper($order) 
            : 'DESC';

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = " WHERE 1 = 1";
    $params = [];


    if (!empty($search)) {
        $where .= " AND (
            course_name LIKE :search
            OR subject_name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($curriculum_id)) {
    $where .= " AND cusu.curriculum_id = :curriculum_id";
    $params[':curriculum_id'] = $curriculum_id;
    }

  
    $countSql = "SELECT COUNT(*) as total 
                 FROM {$this->tableName} cusu
                 JOIN rgr_curriculums rcu ON rcu.id = cusu.curriculum_id
     JOIN rgr_courses rc ON rc.id = rcu.course_id
     JOIN rgr_subjects rs ON rs.id = cusu.subject_id
                 $where";

    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    
   $dataSql = "
SELECT
    cusu.id AS id,
    cusu.year_level,
    cusu.semester,
    rcu.curriculum_name,
    rcu.effective_year,
    rc.code AS course_code,
    rc.name AS course_name,
    rs.code AS subject_code,
    rs.name AS subject_name,
    rs.units AS subject_units
FROM {$this->tableName} cusu
JOIN rgr_curriculums rcu ON rcu.id = cusu.curriculum_id
JOIN rgr_courses rc ON rc.id = rcu.course_id
JOIN rgr_subjects rs ON rs.id = cusu.subject_id
$where
ORDER BY
    cusu.year_level ASC,
    cusu.semester ASC,
    rs.code ASC
";

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
        return $data; 
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