<?php

require_once __DIR__ . '/../models/PhysMonitoring.php';

class PhysMonitoringController
{
    public function index()
    {
        $monitoring = new PhysMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/physics/phys_monitoring.php';
    }

    public function create()
    {
        $monitoring = new PhysMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/phys-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new PhysMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new PhysMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/phys-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new PhysMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/phys-monitoring");
        exit;
    }
}
