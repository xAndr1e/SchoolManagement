<?php 

 namespace App\Controllers;

 use App\Core\Controller;
 use App\Models\Course;
 use App\Models\Employee;
 use App\Models\SchoolYear;
 use App\Models\Semester;

 class AcademicRecordsController extends Controller
 {
 
    public function index()
    {   
        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/academic_records', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    
    }
    

      

 }