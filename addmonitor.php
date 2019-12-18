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

    // Load database
    $db = loadDb($config['DB_FILE']);

    // Setup database
    if(!isset($db->monitors)) $db->monitors = [];

    // Process input
    $type = $_SERVER["argv"][1];
    switch($type) {
        case "page":
            $monitor = pageMonitor($_SERVER["argv"][2], $_SERVER["argv"][3], @$_SERVER["argv"][4]);
            break;
            
        case "port":
            $monitor = portMonitor($_SERVER["argv"][2], $_SERVER["argv"][3], $_SERVER["argv"][4]);
            break;
        
        default:
            die("Error: Incorrect type");
        
    }
    
    // Add monitor
    addMonitor($db, $monitor);

    // Save database
    saveDb($db, $config['DB_FILE']);
