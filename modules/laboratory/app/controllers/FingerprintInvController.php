<?php

require_once __DIR__ . '/../models/FpInventory.php';

class FingerprintInvController
{
    public function index()
    {
        $inventory = new FpInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/fingerprint/fingerprint-inventory.php';
    }

    public function create()
    {
        $inventory = new FpInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new FpInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new FpInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new FpInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/fingerprint-inventory");
        exit;
    }
    
}




