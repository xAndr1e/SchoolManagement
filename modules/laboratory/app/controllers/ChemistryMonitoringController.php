<?php

require_once __DIR__ . '/../models/ChemMonitoring.php';

class ChemistryMonitoringController
{
    public function index()
    {
        $monitoring = new ChemMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/chemistry/chemistry_monitoring.php';
    }

    public function create()
    {
        $monitoring = new ChemMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new ChemMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new ChemMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new ChemMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/chemistry-monitoring");
        exit;
    }
}