<?php

require_once __DIR__ . '/../models/Lab1Monitoring.php';

class Lab1MonitoringController
{
    public function index()
    {
        $monitoring = new Lab1Monitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/it-lab/lab1/lab1-monitoring.php';
    }

    public function create()
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new Lab1Monitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new Lab1Monitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/lab1-monitoring");
        exit;
    }
}
