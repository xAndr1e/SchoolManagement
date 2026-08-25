<?php

require_once __DIR__ . '/../models/QdMonitoring.php';

class QuestionDocumentMonitoringController
{
    public function index()
    {
        $monitoring = new QdMonitoring();

        $monitorings = $monitoring->getAll();

        require __DIR__ . '/../views/monitoring/crim/questionDocument/question_document_monitoring.php';
    }

    public function create()
    {
        $monitoring = new QdMonitoring();

        $monitoring->create($_POST);

        header("Location: " . BASE_URL . "/question-document-monitoring");
        exit;
    }

    public function view($id)
    {
        $monitoring = new QdMonitoring();

        echo json_encode($monitoring->getById($id));
    }

    public function update()
    {
        $monitoring = new QdMonitoring();

        $monitoring->update($_POST);

        header("Location: " . BASE_URL . "/question-document-monitoring");
        exit;
    }

    public function delete($id)
    {
        $monitoring = new QdMonitoring();

        $monitoring->delete($id);

        header("Location: " . BASE_URL . "/question-document-monitoring");
        exit;
    }
}