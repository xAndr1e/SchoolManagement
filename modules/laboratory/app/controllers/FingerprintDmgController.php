<?php

require_once __DIR__ . '/../models/FpDamage.php';

class FingerprintDmgController
{
    public function index()
    {
        $damage = new FpDamage();

        $damages = $damage->getAll();

        require __DIR__ . '/../views/damages/crim/fingerprint/fingerprint-damage.php';
    }

    public function create()
    {
        $damage = new FpDamage();

        $damage->create($_POST);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }

    public function view($id)
    {
        $damage = new FpDamage();

        echo json_encode($damage->getById($id));
    }

    public function update()
    {
        $damage = new FpDamage();

        $damage->update($_POST);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }

    public function delete($id)
    {
        $damage = new FpDamage();

        $damage->delete($id);

        header("Location: " . BASE_URL . "/fingerprint-damage");
        exit;
    }
}



