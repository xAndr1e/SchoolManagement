<?php

require_once __DIR__ . '/../models/heInventory.php';

class HeInvController
{
    public function index()
    {
        $inventory = new heInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/home-eco/he-inventory.php';
    }

    public function create()
    {
        $inventory = new heInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new heInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new heInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new heInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/he-inventory");
        exit;
    }
    
}
