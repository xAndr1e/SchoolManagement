<?php

require_once __DIR__ . '/../models/Itlab1Inventory.php';

class Itlab1InventoryController
{
    public function index()
    {
        $inventory = new Itlab1Inventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/it-lab/lab1/it_lab1_inventory.php';
    }

    public function create()
    {
        $inventory = new Itlab1Inventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/it-lab1-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new Itlab1Inventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new Itlab1Inventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/it-lab1-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new Itlab1Inventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/it-lab1-inventory");
        exit;
    }
    
}
