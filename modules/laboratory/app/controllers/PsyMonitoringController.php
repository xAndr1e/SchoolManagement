<?php

require_once __DIR__ . '/../models/PsyMonitoring.php';

class PsyMonitoringController
{
    public function index()
    {
        $monitoring = new PsyMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/psychology/psy_monitoring.php';
    }

    public function create()
    {
        $monitoring = new PsyMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new PsyMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new PsyMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new PsyMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/psy_monitoring");
        exit;
    }
}
