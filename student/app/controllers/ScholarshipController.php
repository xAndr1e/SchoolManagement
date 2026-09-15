<?php

    namespace App\Controllers;

    use App\Core\Controller;
    use App\Core\Session;
    use App\Helper\Response;
    use App\Models\Student;
    use App\Models\Scholarship;
    use App\Models\Users;
    use App\Models\Semester;
    use App\Models\SchoolYear;
    use App\Models\Section;
    use App\Models\Course;


    class ScholarshipController extends Controller
    {
        public function index()
        {
            $id = Session::get('user_id');
            $user = Users::find($id);
            $student = Student::find($user['student_id']);
            $semester = Semester::activeSemester();
            $schoolYear = SchoolYear::activeSchoolYear();
            
            $section = Section::find($student['section_id']);
            $course = Course::find($student['course_id']);
            
            $scholarshipId = $student['scholarship_id'];
            $scholarship = Scholarship::getScholarshipById($scholarshipId);
            
            $this->render('/scholarship', [
                'schoolYear' => $schoolYear,
                'semester' => $semester,
                'student' => $student,
                'section' => $section,
                'course' => $course,
                'scholarship' => $scholarship
            ]);
        }

        
        
    }