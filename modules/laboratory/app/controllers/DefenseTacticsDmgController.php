<?php

require_once __DIR__ . '/../models/DtDamage.php';

class DefenseTacticsDmgController
{
    public function index()
    {
        $damage = new DtDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/defense-tactics/defense-tactics-damage.php';
    }

    public function create()
    {
        $damage = new DtDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new DtDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new DtDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new DtDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/defense-tactics-damage");
        exit;
    }
}


