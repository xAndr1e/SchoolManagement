<?php

require_once __DIR__ . '/../models/BalInventory.php';

class BalisticInvController
{
    public function index()
    {
        $inventory = new BalInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/balistic/balistic-inventory.php';
    }

    public function create()
    {
        $inventory = new BalInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new BalInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new BalInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new BalInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/balistic-inventory");
        exit;
    }
    
}


