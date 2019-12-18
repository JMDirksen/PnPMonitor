<?php
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
