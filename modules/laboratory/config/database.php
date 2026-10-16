
<?php

class Database
{
    public static function connect()
    {
        $host = 'localhost';
        $dbname = 'labo_payr_bcp';
        $username = 'YOUR_CPANEL_MYSQL_USERNAME';
        $password = 'YOUR_CPANEL_MYSQL_PASSWORD';

        try {
            return new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed.');
        }
    }
}
