<?php
    if(php_sapi_name() != "cli")
        die("monitor.php should be run from command line.");

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    $config = require('config.php');

    $db = loadDb($config['DB_FILE']);

    // Reset monitors / test data
    $db->monitors = [];
    addMonitor($db, portMonitor("GooglePort", "google.nl", 80));
    addMonitor($db, pageMonitor("GooglePage", "http://www.google.nl", "zoeken"));

    foreach($db->monitors as $key => $monitor) {
        echo "Monitor: ".$monitor->name.PHP_EOL;
        $result = testMonitor($monitor);
        echo "Result: ".$result.PHP_EOL;
        $db->monitors[$key]->lastResult = $result;
    }

    saveDb($db, $config['DB_FILE']);



    function loadDb($dbFile) {
        return json_decode(file_get_contents($dbFile));
    }

    function saveDb(&$db, $dbFile) {
        file_put_contents($dbFile, json_encode($db));
    }

    function pageMonitor($name, $url, $text = "") {
        return (object) array(
            "name" => $name,
            "type" => "page",
            "url"  => $url,
            "text" => $text,
        );
    }

    function portMonitor($name, $host, $port) {
        return (object) array(
            "name" => $name,
            "type" => "port",
            "host" => $host,
            "port" => $port,
        );
    }

    function addMonitor(&$db, $monitor) {
        $db->monitors[] = $monitor;
    }

    function testMonitor($monitor) {
        if($monitor->type == "page")
            return testPageLoadTime($monitor);
        elseif($monitor->type == "port")
            return testPortResponseTime($monitor);
    }

    function testPortResponseTime($portMonitor) {
        $time1 = microtime(true);
        $connection = @fsockopen(
            $portMonitor->host,
            $portMonitor->port,
            $errno,
            $errstr,
            10
        );
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    function testPageLoadTime($pageMonitor) {
        $time1 = microtime(true);
        $page = @file_get_contents($pageMonitor->url);
        $time2 = microtime(true);
        if(
            strlen($page)
            and strlen($pageMonitor->text)
            and stristr($page, $pageMonitor->text)===false
        ) {
            return false;
        }
        elseif($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
