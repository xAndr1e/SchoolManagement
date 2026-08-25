<?php

require_once __DIR__ . '/../models/PhysInventory.php';

class PhysInventoryController
{
    public function index()
    {
        $inventory = new PhysInventory();

        $inventories = $inventory->getAll();

            require __DIR__ . '/../views/inventories/physics/physics-inventory.php';
    }

    public function create()
    {
        $inventory = new PhysInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new PhysInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new PhysInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new PhysInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/physics-inventory");
        exit;
    }
    
}
