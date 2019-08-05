<?php

class Database {

    const VERSION = 1;

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

        $this->updateDatabase();
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

    private function updateDatabase() {
        $version = $this->getVersion();
        if($version == 0) {
            echo "Loading new database...\n";
            $this->executeSQLFile('database.sql');
            $version = $this->getVersion();
            echo "Database $version loaded.\n";
        }
        elseif($version < self::VERSION) {
            echo "Updating database $version...\n";
            $this->executeSQLFile('database.update.sql');
            $version = $this->getVersion();
            echo "Database updated to $version.\n";
        }
    }

    private function executeSQLFile($filename) {
        $sql = file_get_contents($filename);
        $this->connection->multi_query($sql);
    }

    private function getVersion() {
        $result = $this->connection->query("SELECT number FROM version LIMIT 1");
        if(@$result->num_rows) {
            $row = $result->fetch_assoc();
            return $row['number'];
        }
        else return 0;      
    }
}
