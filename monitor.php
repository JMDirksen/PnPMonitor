<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    $config = require("config.php");
    
    $db = connectDb(
        $config['DB_HOST'],
        $config['DB_USER'],
        $config['DB_PASS'],
        $config['DB_NAME']
    );
    
    $monitors = getMonitors();
    foreach($monitors as $monitor) {
        if(!checkMonitor($monitor)) {
            switch($monitor['type']) {
                case "page":
                    $string = "Page %s failed to load correctly!\n";
                    $msg = sprintf($string, $monitor['url']);
                    break;
                case "port":
                    $string = "Port %s:%d isn't accepting connections!\n";
                    $msg = sprintf($string, $monitor['host'], $monitor['port']);
                    break;
            }
            echo $msg;
            if(!$monitor['failed']) {
                sendMail($msg);
                setMonitorFailed($monitor['id']);
            }
        }
        else {
            printf("Monitor %d OK\n",$monitor['id']);
            if($monitor['failed']) {
                sendMail("Restored");
                setMonitorFailed($monitor['id'], false);
            }
        }
    }

    function setMonitorFailed($monitor_id, $failed = true) {
        global $db;
        $f = $failed ? 1 : 0;
        $db->query("UPDATE monitor SET failed = $f WHERE id = $monitor_id");
    }
    
    function sendMail($message) {
        global $config;
        try {
            $mail = new PHPMailer(true);
            //$mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->SMTPAuth     = true;
            $mail->Host         = $config['SMTP_HOST'];
            $mail->Username     = $config['SMTP_USER'];
            $mail->Password     = $config['SMTP_PASS'];
            $mail->SMTPSecure   = 'tls';
            $mail->Port         = 587;
            
            $mail->setFrom($config['SMTP_FROM']);
            $mail->addAddress($config['SMTP_TO']);
            $mail->Subject = "PnPMonitor failure";
            $mail->Body = $message;
            $mail->send();
        }
        catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    function checkMonitor($monitor) {
        $response = false;
        switch($monitor['type']) {
            case 'page':
                $response = getPageLoadTime($monitor['url'], $monitor['text']);
                break;
            case 'port':
                $response = getPortResponseTime($monitor['host'], $monitor['port']);
                break;
        }
        if($response !== false) return true;
        else return false;
    }

    function getMonitors() {
        global $db;
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

    function connectDb($host, $user, $pass, $dbname) {
        $db = new mysqli($host, $user, $pass, $dbname);    
        if($db->connect_error) die("DB error: " . $db->connect_error);
        return $db;
    }
    
    function getPortResponseTime($host, $port) {
        $time1 = microtime(true);
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    function getPageLoadTime($url, $must_contain = "") {
        $time1 = microtime(true);
        $page = @file_get_contents($url);
        $time2 = microtime(true);
        if(strlen($page) and strlen($must_contain) and stristr($page, $must_contain)===false) {
            $page = false;
        }
        if($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
