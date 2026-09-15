<?php

namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Scholarship extends Model
 {

    public $tableName = 'gd_scholarships'; 
    public $primaryKey = 'scholarship_id';
  
    public function getScholarshipById($scholarshipId)
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE scholarship_id = :scholarship_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':scholarship_id', $scholarshipId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function __callStatic($name, $arguments)
    {
                $instance = new self();     
                return $instance->$name(...$arguments);
    }
 }