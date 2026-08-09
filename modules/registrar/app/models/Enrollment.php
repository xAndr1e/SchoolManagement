<?php 

 namespace App\Models;

 use App\Core\Model;

 class Enrollment extends Model
 {

    public $tableName = 'rgr_enrollments';
    public $primaryKey= 'id'; 



     public static function __callStatic($name, $arguments)
    {
            $instance = new self();     
            return $instance->$name(...$arguments);
    }


 }