<?php

class Database {
    private $host = "localhost";
    private $db = "sms";
    private $user = "root";
    private $pass = "";
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db};charset=utf8", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }

    public function getRoles() {
        $query = "SELECT role_id, role_name FROM sd_roles ORDER BY role_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

$database = new Database();
$roles = $database->getRoles();
$conn = $database->getConnection();

