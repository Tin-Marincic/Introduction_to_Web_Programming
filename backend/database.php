<?php
require_once __DIR__ . "/dao/config.php";

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . Config::DB_HOST() .
                       ";port=" . Config::DB_PORT() .
                       ";dbname=" . Config::DB_NAME();

                self::$connection = new PDO(
                    $dsn,
                    Config::DB_USER(),
                    Config::DB_PASSWORD(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt' // ssl certificate
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
