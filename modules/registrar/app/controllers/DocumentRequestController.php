<?php 

  namespace App\Controllers;

  use App\Core\Controller;
use App\Helper\Response;
  use App\Models\DocumentRequest;
use App\Models\Employee;
  use App\Models\SchoolYear;
  use App\Models\Semester;


  class DocumentRequestController extends Controller
  {
  
    public function index()
    {

        
       
        $user = Employee::find('1003');
        $semester = Semester::activeSemester();
        $schoolYear = SchoolYear::activeSchoolYear();

        $this->render('/reports/documentRequest', 
        [    
        'user' => $user,
        'semester' => $semester,
        'schoolYear' => $schoolYear
      ]);


    }


    public function allDocumentRequest()
    {
 
      $document = DocumentRequest::allDocumentRequest();
      Response::json($document);

    }




   

 

  }