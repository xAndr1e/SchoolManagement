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


    public function updateVerify()
    {
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated A New School Year",
         "Updated A New Schoool Year Information for System"
         );

          // reset the status of school year
          SchoolYear::updateStatus();

          // reset all the status
         Semester::updateStatus();

         // activate the default
         Semester::activateDefaultSemester($id);

         // update the status in school year
         SchoolYear::update($id,[

            'is_active' => true,

         ]);        

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }

    public function allDocumentRequest()
    {
 
          $document = DocumentRequest::allDocumentRequest();
          Response::json($document);

    }


    public function documentRequestDetails($id)
    {
       
      $document = DocumentRequest::documentRequestDetails($id);
      Response::json($document);

    }

   




   

 

  }