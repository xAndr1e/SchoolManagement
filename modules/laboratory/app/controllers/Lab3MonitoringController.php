<?php

require_once __DIR__ . '/../models/Lab3Monitoring.php';

class Lab3MonitoringController
{
    public function index()
    {
        $monitoring = new Lab3Monitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/it-lab/lab3/lab3-monitoring.php';
    }

    public function create()
    {
        $monitoring = new Lab3Monitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/lab3-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new Lab3Monitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new Lab3Monitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/lab3-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new Lab3Monitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/lab3-monitoring");
        exit;
    }
}
