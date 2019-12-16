<?php

class Monitor {
    public $type;
    public $url;
    public $text;
    public $host;
    public $port;
    public $name;
    private $failCount;
    private $successCount;
    public $sendMailAtXFails = 3;

    public function __construct($type = "test") {
      $this->type = $type;
    }

    public function getFailCount() {
        return $this->failCount;
    }

    public function getSuccessCount() {
        return $this->successCount;
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
        $this->successCount = $row['successCount'];
        $this->failCount = $row['failCount'];
    }

    private function fail() {
        $this->setFailCount($this->failCount + 1);
        if($this->successCount) $this->setSuccessCount(0);
    }

    private function success() {
        $this->setSuccessCount($this->successCount + 1);
        if($this->failCount) $this->setFailCount(0);
    }

    private function setFailCount($count) {
        $this->failCount = $count;
        $this->db->query("UPDATE monitor SET failCount = $count WHERE id = $this->id");
    }

    private function setSuccessCount($count) {
        $this->successCount = $count;
        $this->db->query("UPDATE monitor SET successCount = $count WHERE id = $this->id");
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
