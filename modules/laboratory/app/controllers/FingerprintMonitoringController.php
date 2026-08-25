<?php

require_once __DIR__ . '/../models/FpMonitoring.php';

class FingerprintMonitoringController
{
    public function index()
    {
        $monitoring = new FpMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/fingerprint/fingerprint_monitoring.php';
    }

    public function create()
    {
        $monitoring = new FpMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new FpMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new FpMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new FpMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/fingerprint-monitoring");
        exit;
    }
}