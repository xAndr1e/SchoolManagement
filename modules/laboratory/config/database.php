
<?php

class Database
{
    public static function connect()
    {
        $host = 'localhost';
        $dbname = 'labo_payr_bcp';
        $username = 'labo_db';
        $password = '1234';

        return new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}
