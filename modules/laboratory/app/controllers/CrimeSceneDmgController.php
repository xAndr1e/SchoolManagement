<?php

require_once __DIR__ . '/../models/CsDamage.php';

class CrimeSceneDmgController
{
    public function index()
    {
        $damage = new CsDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/crime-scene/crime-scene-damage.php';
    }

    public function create()
    {
        $damage = new CsDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new CsDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new CsDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new CsDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/crime-scene-damage");
        exit;
    }
}


