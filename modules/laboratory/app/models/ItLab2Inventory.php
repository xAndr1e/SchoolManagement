<?php

class Itlab2Inventory
{
    private $db;
    private $table = "it_lab2_inventory";

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->db = Database::connect();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
            (item_name, category, laboratory, total_item, available_item, status)
            VALUES
            (:item_name, :category, :laboratory, :total_item, :available_item, :status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':item_name'      => $data['item_name'],
            ':category'       => $data['category'],
            ':laboratory'     => $data['laboratory'],
            ':total_item'     => $data['total_item'],
            ':available_item' => $data['available_item'],
            ':status'         => $data['status']
        ]);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $sql = "UPDATE {$this->table}
            SET
                item_name = :item_name,
                category = :category,
                laboratory = :laboratory,
                total_item = :total_item,
                available_item = :available_item,
                status = :status
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $data['id'],
            ':item_name' => $data['item_name'],
            ':category' => $data['category'],
            ':laboratory' => $data['laboratory'],
            ':total_item' => $data['total_item'],
            ':available_item' => $data['available_item'],
            ':status' => $data['status']
        ]);
    }

    //delete
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
