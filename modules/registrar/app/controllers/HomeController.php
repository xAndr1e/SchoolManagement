<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Employee;
use App\Models\Student;

require_once __DIR__ . '/../Models/HomeModel.php';

class HomeController extends Controller
{
    public function index()
    {   
        // CPU Usage
        $cpuUsage = 0;

        $cmd = "wmic cpu get loadpercentage";
        @exec($cmd, $output);

        if (!empty($output[1])) {
            $cpuUsage = (int)$output[1];
        }


        // Logged-in user
        $user = Employee::find('1003');


        // Equipment Status
        $homeModel = new \HomeModel();

        $equipmentStatus = $homeModel->getEquipmentStatus();


        // Send data to view
        $this->render('/home', [
            'cpu' => $cpuUsage,
            'user' => $user,
            'equipmentStatus' => $equipmentStatus
        ]);
    }


    public function countNumber()
    {
        header('Content-Type: application/json');

        $active_count = Student::countActiveStudents();

        echo json_encode($active_count);     
    }
}