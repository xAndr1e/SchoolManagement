<?php

require_once __DIR__ . '/../models/CsMonitoring.php';

class CrimeSceneMonitoringController
{
    public function index()
    {
        $monitoring = new CsMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/crimescene/crimeScene_monitoring.php';
    }

    public function create()
    {
        $monitoring = new CsMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/crime-scene-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new CsMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new CsMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/crime-scene-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new CsMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/crime-scene-monitoring");
        exit;
    }
}