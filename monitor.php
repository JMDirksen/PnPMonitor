<?php

// Init
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
chdir(__DIR__);
ini_set("default_socket_timeout", 3);
$debug = @$argv[1] == "debug" ? true : false;

// Load required files
require_once("functions.php");
$config = require_once('config.php');

// Checks
if(php_sapi_name() != "cli")
    die("This script should be run from command line.");
if(!extension_loaded("openssl"))
    die("Error: Extension openssl required.");

// Load database
list($db, $dbhandle) = loadDb();

// Debug
if($debug) {
    echo "Debug: true\n";
    echo "SendMailAt: Failures: ".$db->sendMailAtXFails." Successes: ".
         $db->sendMailAtXSuccesses."\n\n";
}

// Iterate monitors
foreach($db->monitors as $key => $monitor) {
    // Setup monitor
    if(!isset($monitor->failing)) $monitor->failing = false;
    if(!isset($monitor->successCount)) $monitor->successCount = 0;
    if(!isset($monitor->failCount)) $monitor->failCount = 0;

    // Test monitor
    if($debug) echo "Monitor: ".$monitor->name." (".$monitor->type." ".
                    ($monitor->type=="page"?$monitor->url:$monitor->host)." ".
                    ($monitor->type=="page"?$monitor->text:$monitor->port).
                    ")\n";
    $result = testMonitor($monitor);
    if($debug) echo "Result: ".(!$result ? "false" : $result)."\n";
    $monitor->lastResult = $result;
    $monitor->lastTime = date("d-m-Y H:i:s (T)");

    // Process result
    if($result === false) {
        // Failure
        $monitor->successCount = 0;
        $monitor->failCount++;
        switch($monitor->type) {
            case "page":
                $string = "Page %s failed to load correctly!\n";
                $msg = sprintf($string, $monitor->url);
                break;
            case "port":
                $string = "Port %s:%d isn't accepting connections!\n";
                $msg = sprintf($string, $monitor->host, $monitor->port);
                break;
        }
        if($monitor->failCount >= $db->sendMailAtXFails && !$monitor->failing) {
            $monitor->failing = true;
            $subject = "PnPMonitor failed - $monitor->name";
            if($debug) echo "Sending mail\n";
            sendMail($subject, $msg);
        }
    }
    else {
        // Success
        $monitor->successCount++;
        $monitor->failCount = 0;
        if($monitor->successCount >= $db->sendMailAtXSuccesses && $monitor->failing) {
            $monitor->failing = false;
            $subject = "PnPMonitor restored - $monitor->name";
            $body = "Monitor $monitor->name has been restored.\n";
            if($debug) echo "Sending mail\n";
            sendMail($subject, $body);
        }
    }

    if($debug) echo "Stats: ".$monitor->successCount." ".$monitor->failCount.
                    " ".($monitor->failing ? "failing" : "ok")."\n\n";

    // Update monitor to database
    $db->monitors[$key] = $monitor;

    // Stats
    $stat = [];
    $stat[] = $monitor->id;
    $stat[] = time();
    $stat[] = $result;
    if(!isset($stats)) $stats = [];
    $stats[] = $stat;
}

// Save database
saveDb();

// Save stats
addStats($stats);
