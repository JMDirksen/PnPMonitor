<?php

class Database {

    const VERSION = 3;

    private static $instance;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = Config::getConfig();
        try {
            $this->connection = new MySQLi(
                $this->config['DB_HOST'],
                $this->config['DB_USER'],
                $this->config['DB_PASS'],
                $this->config['DB_NAME']
            );
        }
        catch(Error $e) {
            die("Error: Unable to load MySQLi.\n");
        }

        $this->updateDatabase();
    }

    function __destruct() {
        try {
            $this->connection->close();
        }
        catch(Error $e) {}
    }

    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }

    private function updateDatabase() {
        $version = $this->getVersion();
        while($version < self::VERSION) {
            $updateTo = $version + 1;
            echo "Updating database version $version to $updateTo ... ";
            $filename = "database.v" . $updateTo . ".sql";
            $this->executeSQLFile($filename);
            $version = $this->getVersion();
            echo "done.\n";
        }
    }

    private function executeSQLFile($filename) {
        $sql = file_get_contents(__DIR__.'/'.$filename);
        if ($this->connection->multi_query($sql)) {
            while (true) {
                if($this->connection->errno) {
                    die($filename . ": " . $this->connection->error);
                }
                elseif($this->connection->more_results()) {
                    $this->connection->next_result();
                }
                else {
                    break;
                }
            }
        }
        else {
            die($filename . ": " . $this->connection->error);
        }
    }

    private function getVersion() {
        $result = $this->connection->query("SELECT dbversion FROM settings LIMIT 1");
        if(@$result->num_rows) {
            $row = $result->fetch_assoc();
            return $row['dbversion'];
        }
        else return 0;      
    }
}
