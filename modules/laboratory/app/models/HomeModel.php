<?php

require_once __DIR__ . '/../../config/Database.php';

class HomeModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getEquipmentStatus()
    {
        $sql = "
            SELECT status, COUNT(*) AS total
            FROM (
                SELECT status FROM lab_phys_inventory
                UNION ALL
                SELECT status FROM lab_psy_inventory
                UNION ALL
                SELECT status FROM lab_ballistic_inventory
                UNION ALL
                SELECT status FROM lab_chemistry_inventory
            ) AS all_inventory
            GROUP BY status
            ORDER BY status ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}