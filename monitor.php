<?php
    use PHPMailer\PHPMailer\PHPMailer;
    
    if(!extension_loaded("openssl")) die("Error: Extension openssl required.");
    
    if(php_sapi_name() != "cli")
        die("This script should be run from command line.");

    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    $config = require('config.php');

    $db = loadDb($config['DB_FILE']);

    if(!isset($db->sendMailAtXFails)) $db->sendMailAtXFails = 3;
    if(!isset($db->sendMailAtXSuccesses)) $db->sendMailAtXSuccesses = 2;
    if(!isset($db->monitors)) $db->monitors = [];

    // Reset monitors / test data
    //$db->monitors = [];
    //addMonitor($db, portMonitor("GooglePort", "google.nl", 80));
    //addMonitor($db, pageMonitor("GooglePage", "http://www.google.nl", "zoeken"));
    //sendMail($config, "Test mail", "Test message!\nLine 2");

    foreach($db->monitors as $key => $monitor) {
        if(!isset($monitor->failing)) $monitor->failing = false;
        if(!isset($monitor->successCount)) $monitor->successCount = 0;
        if(!isset($monitor->failCount)) $monitor->failCount = 0;
        
        echo "Monitor: ".$monitor->name.PHP_EOL;
        $result = testMonitor($monitor);
        echo "Result: ".$result.PHP_EOL;
        $monitor->lastResult = $result;
        
        if($result === false) {
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
        
        $db->monitors[$key] = $monitor;
    }

    saveDb($db, $config['DB_FILE']);



    function loadDb($dbFile) {
        $db = json_decode(@file_get_contents($dbFile));
        if(!$db) $db = (object) null;
        return $db;
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
        switch($monitor->type) {
            case "page":
                return testPageLoadTime($monitor);
                break;
            case "port":
                return testPortResponseTime($monitor);
                break;
            default:
                return false;
        }
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

    function sendMail(&$config, $subject, $body) {
        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->SMTPAuth   = true;
            $mailer->Host       = $config['SMTP_HOST'];
            $mailer->Username   = $config['SMTP_USER'];
            $mailer->Password   = $config['SMTP_PASS'];
            $mailer->SMTPSecure = $config['SMTP_SECURE'];
            $mailer->Port       = $config['SMTP_PORT'];
            $mailer->setFrom($config['SMTP_FROM']);
            $mailer->addAddress($config['SMTP_TO']);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->send();
        }
        catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}";
        }
    }
