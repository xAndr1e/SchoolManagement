<?php

require_once __DIR__ . '/../models/Itlab3Inventory.php';

class Itlab3InventoryController
{
    public function index()
    {
        $inventory = new Itlab3Inventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/it-lab/lab3/lab3_inventory.php';
    }

    public function create()
    {
        $inventory = new Itlab3Inventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/it-lab3-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new Itlab3Inventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new Itlab3Inventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/it-lab3-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new Itlab3Inventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/it-lab3-inventory");
        exit;
    }
    
}
