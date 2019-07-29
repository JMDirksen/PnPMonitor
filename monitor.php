<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    $config = require("config.php");
    
    $db = connect_db(
        $config['DB_HOST'],
        $config['DB_USER'],
        $config['DB_PASS'],
        $config['DB_NAME']
    );
    
    $monitors = get_monitors($db);
    foreach($monitors as $monitor) {
        check_monitor($monitor);
    }

    function check_monitor($monitor) {
        switch($monitor['type']) {
            case 'page':
                $response = page_load_time($monitor['url']);
                break;
            case 'port':
                $response = port_response_time($monitor['host'], $monitor['port']);
                break;
        }
        echo "Monitor " . $monitor['id'] . " response time: " . $response . PHP_EOL;
    }

    function get_monitors($db) {
        $rows = [];
        $result = $db->query("SELECT * FROM monitor");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->close();
        }
        return $rows;
    }

    function connect_db($host, $user, $pass, $dbname) {
        $db = new mysqli($host, $user, $pass, $dbname);    
        if($db->connect_error) die("DB error: " . $db->connect_error);
        return $db;
    }
    
    function port_response_time($host, $port) {
        $time1 = microtime(true);
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    function page_load_time($url, $must_contain = "") {
        $time1 = microtime(true);
        $page = @file_get_contents($url);
        $time2 = microtime(true);
        if(strlen($page) and strlen($must_contain) and stristr($page, $must_contain)===false) {
            $page = false;
        }
        if($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
