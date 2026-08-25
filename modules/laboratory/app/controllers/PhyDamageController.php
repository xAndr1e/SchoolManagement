<?php

require_once __DIR__ . '/../models/phyDamage.php';

class PhyDamageController
{
    public function index()
    {
        $damage = new PhyDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/physics/physics-damage.php';
    }

    public function create()
    {
        $damage = new PhyDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new PhyDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new PhyDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new PhyDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/physics-damage");
        exit;
    }
}

