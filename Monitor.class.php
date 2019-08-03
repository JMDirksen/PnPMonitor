<?php

class Monitor {
    private $db;

    public $id;
    public $type;
    public $url;
    public $text;
    public $host;
    public $port;
    private $failed;

    public function __construct($id) {
        $this->db = Database::getConnection();
        $this->load($id);
    }

    public function getFailed() {
        return $this->failed;
    }

    public function setFailed($failed) {
        $this->failed = (bool) $failed;
        $f = $failed ? 1 : 0;
        $this->db->query("UPDATE monitor SET failed = $f WHERE id = $this->id");
    }

    private function load($id) {
        $this->id = $id;
        $result = $this->db->query("SELECT * FROM monitor WHERE id = $id");
        $row = $result->fetch_assoc();
        $this->type = $row['type'];
        $this->url = $row['url'];
        $this->text = $row['text'];
        $this->host = $row['host'];
        $this->port = $row['port'];
        $this->failed = $row['failed'] ? true : false;
    }

    public function test() {
        $response = false;
        switch($this->type) {
            case 'page':
                $response = $this->getPageLoadTime($this->url, $this->text);
                break;
            case 'port':
                $response = $this->getPortResponseTime($this->host, $this->port);
                break;
        }
        if($response !== false) return true;
        else return false;
    }

    private function getPortResponseTime($host, $port) {
        $time1 = microtime(true);
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    private function getPageLoadTime($url, $must_contain = "") {
        $time1 = microtime(true);
        $page = @file_get_contents($url);
        $time2 = microtime(true);
        if(strlen($page) and strlen($must_contain) and stristr($page, $must_contain)===false) {
            $page = false;
        }
        if($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
    
}
