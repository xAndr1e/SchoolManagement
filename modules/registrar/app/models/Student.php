<?php 

    namespace App\Models;
    use App\Core\Model;
    use PDO;


    class Student extends Model
    {
        
        public $tableName = 'enr_students';
        public $primaryKey = 'student_id';


      protected function generateCor(int $id)
{
    $sql = "
        SELECT 
            ea.surname AS student_last_name,
            ea.first_name AS student_first_name,
            rc.name AS course_name,
            ccs.start_time,
            ccs.end_time,
            csec.section_code,
            ccs.day_of_week,
            een.academic_standing,
            rs.code AS subject_code,
            rs.name AS subject_name,
            ccfac.*

        FROM $this->tableName es

        JOIN enr_applicants ea 
            ON ea.applicant_id = es.applicant_id

        JOIN enr_enrollments een 
            ON een.student_id = es.student_id

        JOIN cc_sections csec 
            ON csec.id = een.section_id

        JOIN rgr_courses rc 
            ON rc.id = csec.program_id

        JOIN cc_schedule ccs 
            ON ccs.id = een.schedule_id

        JOIN cc_faculty_load ccf 
            ON ccf.id = ccs.faculty_load_id

        JOIN cc_faculty ccfac 
             ON ccfac.id = ccf.faculty_id

        JOIN rgr_subjects rs 
            ON rs.id = ccs.subject_id

        WHERE es.student_id = :studentId
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':studentId' => $id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

        protected function generateStudentNumber()
        {
    
            $year = date('Y');

            $stmt = $this->pdo->prepare("
                SELECT student_number
                FROM {$this->tableName}
                WHERE student_number LIKE :year
                ORDER BY student_number DESC
                LIMIT 1
            ");

            $stmt->execute([
                ':year' => $year . '-%'
            ]);

            $last = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$last) {
                return $year . '-000001';
            }

            $parts = explode('-', $last['student_number']);
            $next = (int)$parts[1] + 1;

            return $year . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);

        }


   
        protected function countActiveStudents() {

            $stmt = $this->pdo->query("SELECT COUNT(*) as totalActiveStudent FROM $this->tableName where enrollment_status = 'enrolled' ");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }


        protected function activeStudents($perPage = 10)
        {
            
            $page = isset($_GET['page']) ? (int) $_GET['page'] :  1;

            if ($page < 1) {
                $page = 1;
            }

            $offset = ($page - 1) * $perPage;

            $stmt = $this->pdo->prepare("
                SELECT * 
                FROM {$this->tableName}
                WHERE academic_status = :status
                LIMIT :limit OFFSET :offset
            ");

            $stmt->bindValue(':status', 'Active');
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }



 protected function allStudents($paginate = true)
    {
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

    if (!empty($status) && strtolower($status) !== 'all') {
        $where .= " AND stud.enrollment_status = :status";
        $params[':status'] = $status;
    }

    if (!empty($search)) {
        $where .= " AND (
            stud.student_number LIKE :search OR
            app.first_name LIKE :search OR
            app.surname LIKE :search OR
            app.email LIKE :search OR
            co.code LIKE :search OR
            co.name LIKE :search
        )";
        $params[':search'] = "%{$search}%";
    }


    // school year

    $schoolYearSql = "SELECT name as school_year FROM rgr_school_years WHERE is_active = true";
    $schoolYearStmt = $this->pdo->prepare($schoolYearSql);

    $schoolYearStmt->execute();
    $school_year = $schoolYearStmt->fetch(PDO::FETCH_ASSOC)['school_year'];


    // Count total
    $countSql = "SELECT COUNT(*) as total  FROM {$this->tableName} stud
    JOIN enr_applicants app ON app.applicant_id = stud.applicant_id
    JOIN rgr_courses co ON co.id = stud.course_id
    $where ORDER BY id $order ";
    $countStmt = $this->pdo->prepare($countSql);

    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base query
    $dataSql = "SELECT 
    stud.*,
    app.*,
    co.code as course_code,
    co.name as course_name
    FROM {$this->tableName} stud
    JOIN enr_applicants app ON app.applicant_id = stud.applicant_id
    JOIN rgr_courses co ON co.id = stud.course_id $where ORDER BY id $order ";

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

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();     
        return $instance->$name(...$arguments);
    }

      

    }