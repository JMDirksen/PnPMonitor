<?php
    if (php_sapi_name() != "cli")
        die("monitor.php should be run from command line.");

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
    
    spl_autoload_register(function ($class_name) {
        @include strtolower($class_name . ".class.php");
    });

  $db = new Database;
  print_r($db);
