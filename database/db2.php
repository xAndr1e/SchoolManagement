<?php

class Database2 {
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        $config = require __DIR__ . '/config.php';
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->db   = $config['dbname'];
        $this->user = $config['username'];
        $this->pass = $config['password'];

        try {
            $this->conn = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
    
}

$database2 = new Database2();
$conn2 = $database2->getConnection();
