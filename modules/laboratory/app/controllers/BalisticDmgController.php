<?php

require_once __DIR__ . '/../models/BalDamage.php';

class BalisticDmgController
{
    public function index()
    {
        $damage = new BalDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/balistic/balistic-damage.php';
    }

    public function create()
    {
        $damage = new BalDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/balistic-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new BalDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new BalDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/balistic-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new BalDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/balistic-damage");
        exit;
    }
}

