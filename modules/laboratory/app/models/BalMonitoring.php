<?php

class BalMonitoring
{
    private $db;
    private $table = "balistic_lab_monitoring";

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

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=:id");
        $stmt->execute([':id'=>$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
        (item_name,laboratory,`equipment_condition`,last_checked,checked_by,remarks)

        VALUES
        (:item_name,:laboratory,:condition,:last_checked,:checked_by,:remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':item_name'=>$data['item_name'],
            ':laboratory'=>$data['laboratory'],
            ':condition'=>$data['equipment_condition'],
            ':last_checked'=>$data['last_checked'],
            ':checked_by'=>$data['checked_by'],
            ':remarks'=>$data['remarks']
        ]);
    }

    public function update($data)
    {
        $sql = "UPDATE {$this->table}

        SET
        item_name=:item_name,
        laboratory=:laboratory,
        `equipment_condition`=:condition,
        last_checked=:last_checked,
        checked_by=:checked_by,
        remarks=:remarks

        WHERE id=:id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'=>$data['id'],
            ':item_name'=>$data['item_name'],
            ':laboratory'=>$data['laboratory'],
            ':condition'=>$data['equipment_condition'],
            ':last_checked'=>$data['last_checked'],
            ':checked_by'=>$data['checked_by'],
            ':remarks'=>$data['remarks']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id=:id");

        return $stmt->execute([
            ':id'=>$id
        ]);
    }
}


?>