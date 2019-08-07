<?php

class Monitor {
    private $db;

    public $id;
    public $type;
    public $url;
    public $text;
    public $host;
    public $port;
    public $restored = false;
    public $name;
    private $failed;
    public $sendMailAtXFails = 3;

    public function __construct($id) {
        $this->db = Database::getConnection();
        $this->load($id);
    }

    public function getFailed() {
        return $this->failed;
    }

    public function test() {
        $response = false;
        switch($this->type) {
            case 'page':
                $response = $this->testPageLoadTime();
                break;
            case 'port':
                $response = $this->testPortResponseTime();
                break;
        }
        if($response === false) {
            $this->fail();
            return false;
        }
        else {
            $this->success();
            return true;
        }
    }

    private function setFailed($failed) {
        $this->failed = $failed;
        $this->db->query("UPDATE monitor SET failed = $failed WHERE id = $this->id");
    }

    private function load($id) {
        $this->id = $id;
        $result = $this->db->query("SELECT * FROM monitor WHERE id = $id");
        $row = $result->fetch_assoc();
        $this->name = $row['name'];
        $this->type = $row['type'];
        $this->url = $row['url'];
        $this->text = $row['text'];
        $this->host = $row['host'];
        $this->port = $row['port'];
        $this->failed = $row['failed'];
    }

    private function fail() {
        $this->setFailed($this->failed+1);
    }

    private function success() {
        if($this->failed >= $this->sendMailAtXFails) $this->restored = true;
        $this->setFailed(0);
    }

    private function testPortResponseTime() {
        $time1 = microtime(true);
        $connection = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    private function testPageLoadTime() {
        $time1 = microtime(true);
        $page = @file_get_contents($this->url);
        $time2 = microtime(true);
        if(strlen($page) and strlen($this->text) and stristr($page, $this->text)===false) {
            return false;
        }
        elseif($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
    
}
