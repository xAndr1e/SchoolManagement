<?php

class Database {
    private static $instance = null;

    public static function connect() {
        if (!self::$instance) {
            self::$instance = new PDO(
                "mysql:host=localhost;dbname=labo_payr_bcp;charset=utf8",
                "labo_db",
                "1234"
            );

            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$instance;
    }
}
