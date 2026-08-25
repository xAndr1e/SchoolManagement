<?php

require_once __DIR__ . '/../models/DtInventory.php';

class DefenseInvController
{
    public function index()
    {
        $inventory = new DtInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/defense-tactics/defense-inventory.php';
    }

    public function create()
    {
        $inventory = new DtInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/defense-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new DtInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new DtInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/defense-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new DtInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/defense-inventory");
        exit;
    }
    
}




