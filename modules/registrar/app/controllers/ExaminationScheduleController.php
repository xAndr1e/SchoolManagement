<?php 
 
  namespace App\Controllers;

  use App\Core\Controller;
  use App\Helper\Response;
  use App\Models\Course;
  use App\Models\Employee;
  use App\Models\ExamSchedule;
  use App\Models\SchoolYear;
  use App\Models\Semester;

  class ExaminationScheduleController extends Controller
  {
    
    public function index()
    {
        
        $user = Employee::find('1003'); 
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();  
        $course = Course::all(); 
        
        $this->render('/students/exam_schedule', 
        [
            'user' => $user,
             'course' => $course,
             'semester' => $semester,
             'schoolYear' => $schoolYear
        ]);
    }


    public function getSectionOnThatDay()
    {
        $section = ExamSchedule::getSectionOnThatDay();
        Response::json($section);
    }

    public function getSchedule()
    {
      $schedule = ExamSchedule::getSchedule();
      Response::json($schedule);
    }
 

  }