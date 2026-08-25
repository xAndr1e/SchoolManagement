<?php

require_once __DIR__ . '/../models/DtMonitoring.php';

class DefenseTacticsMonitoringController
{
    public function index()
    {
        $monitoring = new DtMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/defense_tactics/defense_tactics_monitoring.php';
    }

    public function create()
    {
        $monitoring = new DtMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new DtMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new DtMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new DtMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/defense-tactics-monitoring");
        exit;
    }
}