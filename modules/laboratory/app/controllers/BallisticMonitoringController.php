<?php

require_once __DIR__ . '/../models/BalMonitoring.php';

class BallisticMonitoringController
{
    public function index()
    {
        $monitoring = new BalMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/ballistic/ballistic_monitoring.php';
    }

    public function create()
    {
        $monitoring = new BalMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new BalMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new BalMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new BalMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/ballistic-monitoring");
        exit;
    }
}