<?php

class Database {

    private static $instance;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = Config::getConfig();
        $this->connection = new MySQLi(
            $this->config['DB_HOST'],
            $this->config['DB_USER'],
            $this->config['DB_PASS'],
            $this->config['DB_NAME']
        );
    }

    function __destruct() {
        $this->connection->close();
    }

    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
