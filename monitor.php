<?php
    if (php_sapi_name() != "cli")
        die("monitor.php should be run from command line.");

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
    
    spl_autoload_register(function ($class_name) {
        @include strtolower($class_name . ".class.php");
    });

    $config = Config::getConfig();
    $ml = new MonitorList();
    
    foreach($ml->getMonitors() as $monitor) {
        $mailSent = $monitor->getFailCount() >= $monitor->sendMailAtXFails;

        // Failed
        if(!$monitor->test()) {
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
            if($monitor->getFailCount() == $monitor->sendMailAtXFails) {
                $mail = new Mailer();
                $mail->send("PnPMonitor failed - $monitor->name", $msg);
            }
        }

        // Success
        else {
            if($monitor->getSuccessCount() == 1 && $mailSent) {
                $mail = new Mailer();
                $mail->send("PnPMonitor restored - $monitor->name",
                    "Monitor $monitor->name has been restored\n");
            }
        }
    }
