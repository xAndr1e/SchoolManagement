<?php 

  namespace App\Controllers;

  use App\Core\Controller;
use App\Helper\Logger;
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


    public function updateVerify($id)
    {
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Verified a student document request"
         );


         DocumentRequest::update($id,[

            'status' => 'verified',

         ]);        

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }


      public function updateProcess($id)
     {
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Processing a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'processing',

         ]);        

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }


     public function markReadyProcess($id)
     {
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Mark Ready a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'ready for release',

         ]);        

        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully.'
        ]);

    }
     public function releasedProcess($id)
     {
 
       header('Content-Type: application/json');


        Logger::log(
        "Updated Document Request ",
         "Released a student document request"
         );



         DocumentRequest::update($id,[

            'status' => 'released',

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