<?php
    // Init
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // Load required files
    require_once("functions.php");
    $config = require_once('config.php');

    // Checks
    if(php_sapi_name() != "cli")
        die("This script should be run from command line.");
    if(!extension_loaded("openssl"))
        die("Error: Extension openssl required.");

    // Load database
    $db = loadDb($config['DB_FILE']);

    // Setup database
    if(!isset($db->sendMailAtXFails)) $db->sendMailAtXFails = 3;
    if(!isset($db->sendMailAtXSuccesses)) $db->sendMailAtXSuccesses = 2;
    if(!isset($db->monitors)) $db->monitors = [];

    // Iterate monitors
    foreach($db->monitors as $key => $monitor) {
        // Setup monitor
        if(!isset($monitor->failing)) $monitor->failing = false;
        if(!isset($monitor->successCount)) $monitor->successCount = 0;
        if(!isset($monitor->failCount)) $monitor->failCount = 0;
        
        // Test monitor
        echo "Monitor: ".$monitor->name.PHP_EOL;
        $result = testMonitor($monitor);
        echo "Result: ".$result.PHP_EOL;
        $monitor->lastResult = $result;
        
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
                sendMail($config, $subject, $msg);
                echo "Mail sent.\n";
            }
        }
        else {
            // Success
            $monitor->successCount++;
            if($monitor->successCount >= $db->sendMailAtXSuccesses && $monitor->failing) {
                $monitor->failing = false;
                $monitor->failCount = 0;
                $subject = "PnPMonitor restored - $monitor->name";
                $body = "Monitor $monitor->name has been restored.\n";
                sendMail($config, $subject, $body);
                echo "Mail sent.\n";
            }
        }
        
        // Update monitor to database
        $db->monitors[$key] = $monitor;
    }

    // Save database
    saveDb($db, $config['DB_FILE']);
