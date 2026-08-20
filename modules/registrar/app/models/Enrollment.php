<?php 

 namespace App\Models;

 use App\Core\Model;
 use PDO;

 class Enrollment extends Model
 {

    public $tableName = 'enr_enrollments';
    public $primaryKey= 'enrollment_id'; 



   
    

     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }