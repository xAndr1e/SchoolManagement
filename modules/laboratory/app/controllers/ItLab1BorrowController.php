<?php

require_once __DIR__ . '/../models/lab1Borrow.php';

class Itlab1BorrowController
{
    // DISPLAY ALL BORROWS
    public function index()
    {
        $borrow = new lab1Borrow();
        $borrows = $borrow->getAll();
        require __DIR__ . '/../views/barrow/it-lab/lab1/it_lab1_borrow.php';
    }


    // CREATE
    public function create()
    {
        $borrow = new lab1Borrow();

        $borrow->create($_POST);

        header("Location: " . BASE_URL . "/lab1-borrow");
        exit;
    }


    // VIEW
    public function view($id)
    {
        $borrow = new lab1Borrow();

        $data = $borrow->getById($id);

        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }


    // UPDATE
    public function update()
    {
        $borrow = new lab1Borrow();

        $borrow->update($_POST);

        header("Location: " . BASE_URL . "/lab1-borrow");
        exit;
    }


    // DELETE
    public function delete($id)
    {
        $borrow = new lab1Borrow();

        $borrow->delete($id);

        header("Location: " . BASE_URL . "/lab1-borrow");
        exit;
    }
}
