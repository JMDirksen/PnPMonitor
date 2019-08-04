<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    spl_autoload_register(function ($class_name) {
        include $class_name . '.class.php';
    });

    $config = Config::getConfig();
    $ml = new MonitorList();
    
    foreach($ml->getMonitors() as $monitor) {

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
            echo $msg;
            if($monitor->getFailed() == $config['MAIL_ON_FAILS_COUNT']) {
                $mail = new Mailer();
                $mail->send("PnPMonitor failure", $msg);
            }
        }

        // Success
        else {
            printf("Monitor %d OK\n",$monitor->id);
            if($monitor->restored) {
                $mail = new Mailer();
                $mail->send("PnPMonitor restored", "Restored");
            }
        }
    }
