<?php

require_once __DIR__ . '/../models/CsInventory.php';

class CrimeSceneInvController
{
    public function index()
    {
        $inventory = new CsInventory();

        $inventories = $inventory->getAll();

        require __DIR__ . '/../views/inventories/crim-lab/crime-scene/crime-scene-inventory.php';
    }

    public function create()
    {
        $inventory = new CsInventory();

        $inventory->create($_POST);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }

    public function view($id)
    {
        $inventory = new CsInventory();

        echo json_encode($inventory->getById($id));
    }

    public function update()
    {
        $inventory = new CsInventory();

        $inventory->update($_POST);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }

    public function delete($id)
    {
        $inventory = new CsInventory();

        $inventory->delete($id);

        header("Location: " . BASE_URL . "/crime-scene-inventory");
        exit;
    }
    
}
