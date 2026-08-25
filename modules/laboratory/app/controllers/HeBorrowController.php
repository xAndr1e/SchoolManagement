<?php

require_once __DIR__ . '/../models/HeBorrow.php';

class HeBorrowController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new HeBorrow();

        $borrows = $borrow->getAll();

        require __DIR__ . '/../views/barrow/he/he_borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new HeBorrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/he_borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new HeBorrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new HeBorrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/he_borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new HeBorrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/he_borrow");
        exit;
    }
}