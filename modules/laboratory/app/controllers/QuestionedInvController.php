<?php

require_once __DIR__ . '/../models/QdInventory.php';

class QuestionedInvController
{
    public function index()
    {
        $inventory = new QdInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/questioned-lab/questioned-inventory.php';
    }

    public function create()
    {
        $inventory = new QdInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new QdInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new QdInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new QdInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/questioned-inventory");
        exit;
    }
    
}


