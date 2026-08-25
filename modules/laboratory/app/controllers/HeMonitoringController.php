<?php

require_once __DIR__ . '/../models/HeMonitoring.php';

class HeMonitoringController
{
    public function index()
    {
        $monitoring = new HeMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/he/he_monitoring.php';
    }

    public function create()
    {
        $monitoring = new HeMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/he_monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new HeMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new HeMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/he_monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new HeMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/he_monitoring");
        exit;
    }
}