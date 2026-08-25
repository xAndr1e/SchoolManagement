<?php

require_once __DIR__ . '/../models/ChemDamage.php';

class ChemistryDmgController

{
    public function index()
    {
        $damage = new ChemDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/chemistry/chemistry-damage.php';
    }

    public function create()
    {
        $damage = new ChemDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/chemistry-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new ChemDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new ChemDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/chemistry-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new ChemDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/chemistry-damage");
        exit;
    }
}


