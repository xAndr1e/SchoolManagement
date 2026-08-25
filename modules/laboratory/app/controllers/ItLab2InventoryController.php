<?php

require_once __DIR__ . '/../models/Itlab2Inventory.php';

class ItLab2InventoryController
{
    public function index()
    {
        $inventory = new Itlab2Inventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/it-lab/lab2/lab2-inventory.php';
    }

    public function create()
    {
        $inventory = new Itlab2Inventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new Itlab2Inventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new Itlab2Inventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new Itlab2Inventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/it-lab2-inventory");
        exit;
    }
    
}
