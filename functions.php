<?php
    function loadDb() {
        global $config;
        $dbFile = $config['DB_FILE'];
        $handle = fopen($dbFile, "c+");
        if(!$handle) die("Unable to open db");
        if(!flock($handle, LOCK_EX)) die("Unable to lock db");
        $contents = "";
        if(filesize($dbFile)) {
            $contents = fread($handle, filesize($dbFile));
            if($contents === false) die("Unable to read db");
        }
        $db = json_decode($contents);
        if(!$db) $db = (object) null;
        if(!isset($db->users)) $db->users = [];
        if(!isset($db->monitors)) $db->monitors = [];
        if(!isset($db->sendMailAtXFails)) $db->sendMailAtXFails = 3;
        if(!isset($db->sendMailAtXSuccesses)) $db->sendMailAtXSuccesses = 2;
        return array($db, $handle);
    }

    function saveDb() {
        global $db, $dbhandle;
        if(!ftruncate($dbhandle, 0)) die("Unable to truncate db");
        if(!rewind($dbhandle)) die("Unable to rewind db");
        if(fwrite($dbhandle, json_encode($db)) === false)
            die("Unable to write db");
    }

    function pageMonitor($userid, $name, $url, $text = "") {
        return (object) array(
            "name" => $name,
            "type" => "page",
            "url"  => $url,
            "text" => $text,
            "user" => $userid,
        );
    }

    function portMonitor($userid, $name, $host, $port) {
        return (object) array(
            "name" => $name,
            "type" => "port",
            "host" => $host,
            "port" => $port,
            "user" => $userid,
        );
    }

    function addMonitor($monitor) {
        global $db;
        $monitor->id = newMonitorId();
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
            $errstr
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

    function sendMail($subject, $body) {
        global $config;
        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        try {
            $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
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
            echo "Message could not be sent. Error: {$mailer->ErrorInfo}";
        }
    }

    function getUser($email) {
        global $db;
        foreach($db->users as $user) {
            if($user->email == $email) return $user;
        }
        return false;
    }

    function getMonitor($id) {
        global $db;
        foreach($db->monitors as $monitor) {
            if($monitor->id == $id) return $monitor;
        }
        return false;
    }

    function getUserFromToken($token) {
        global $db;
        foreach($db->users as $user) {
            if($user->token == $token) return $user;
        }
        return false;
    }

    function newSecret($length = 5) {
        $token = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".
                 "abcdefghijklmnopqrstuvwxyz".
                 "0123456789";
        for($i = 0; $i < $length; $i++) {
            $rnd = rand(0,strlen($chars)-1);
            $char = $chars{$rnd};
            $token .= $char;
        }
        return $token;
    }

    function confirmLink($secret) {
        $protocol = ($_SERVER['HTTPS']=="on") ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol.$host."/action.php?confirm=".$secret;
    }

    function updateUser($user) {
        global $db;
        foreach($db->users as $key => $value) {
            if($value->id == $user->id) {
                $db->users[$key] = $user;
                return;
            }
        }
    }

    function deleteMonitor($monitor) {
        global $db;
        foreach($db->monitors as $key => $value) {
            if($value->id == $monitor->id) {
                unset($db->monitors[$key]);
                $db->monitors = array_values($db->monitors);
                return;
            }
        }
    }

    function newUserId() {
        global $db;
        $id = 1;
        foreach($db->users as $user)
            if($user->id >= $id)
                $id = $user->id+1;
        return $id;
    }

    function newMonitorId() {
        global $db;
        $id = 1;
        foreach($db->monitors as $monitor)
            if($monitor->id >= $id)
                $id = $monitor->id+1;
        return $id;
    }

    function verifyLogin($email, $password) {
        global $db;
        foreach($db->users as $user) {
            if($user->email == $email) {
                if(password_verify($password, $user->password))
                    return $user;
                else
                    return false;
            }
        }
        return false;
    }

    function confirm($code) {
        global $db;
        foreach($db->users as $user) {
            if($user->confirm == $code) {
                unset($user->confirm);
                updateUser($user);
            }
        }
    }

    function thisUrl() {
        $protocol = ($_SERVER['HTTPS']=="on") ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol.$host."/";
    }

    function redirect($url = "") {
        $location = thisUrl().$url;
        header("Location: $location", true, 303);
        die();
    }

    function msg($message, $error = false, $redirect = "") {
        if($error) $_SESSION['errorMsg'] = $message;
        else $_SESSION['msg'] = $message;
        redirect($redirect);
    }

    function loadStats() {
        global $config;
        $statsFile = $config['STATS_FILE'];
        $handle = fopen($statsFile, "c+");
        if(!$handle) die("Unable to open stats");
        if(!flock($handle, LOCK_EX)) die("Unable to lock stats");
        $contents = "";
        if(filesize($statsFile)) {
            $contents = fread($handle, filesize($statsFile));
            if($contents === false) die("Unable to read stats");
        }
        $stats = json_decode($contents);
        if(!$stats) $stats = [];
        return Array($stats, $handle);
    }

    function saveStats($stats, $handle) {
        if(!ftruncate($handle, 0)) die("Unable to truncate stats");
        if(!rewind($handle)) die("Unable to rewind stats");
        if(fwrite($handle, json_encode($stats)) === false)
            die("Unable to write stats");
    }

    function addStats($newStats) {
        list($stats, $handle) = loadStats();
        $stats = cleanupStats($stats);
        $stats = array_merge($stats, $newStats);
        saveStats($stats, $handle);
    }

    function cleanupStats($stats) {
        global $config;
        $cleanupTime = time() - 86400 * $config["STATS_DAYS"];
        foreach($stats as $key => $stat)
            if($stat[1] < $cleanupTime) unset($stats[$key]);
        return array_values($stats);
    }
