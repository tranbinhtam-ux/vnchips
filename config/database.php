<?php
/**
 * Database Connection Manager (PDO Singleton)
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Database {
    private static $instance = null;
    private $pdo = null;
    private $error = null;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Try connecting without dbname and auto-creating database if MySQL is up
            try {
                $rawDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                $rawPdo = new PDO($rawDsn, DB_USER, DB_PASS, $options);
                $rawPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $ex) {
                $this->pdo = null;
                $this->error = $ex->getMessage();
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function getError() {
        return $this->error;
    }

    public function isConnected() {
        return $this->pdo !== null;
    }
}
