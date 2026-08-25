<?php

require_once __DIR__ . '/../models/PsyInventory.php';

class PsychoInvController
{
    public function index()
    {
        $inventory = new PsyInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/psych-lab/psycho-inventory.php';
    }

    public function create()
    {
        $inventory = new PsyInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new PsyInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new PsyInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new PsyInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/psycho-inventory");
        exit;
    }
    
}

