<?php

function loadDb($lockfile = true)
{
    $dbFile = "../db.json";
    $handle = fopen($dbFile, "c+");
    if (!$handle) die("Unable to open db");
    if ($lockfile && !flock($handle, LOCK_EX)) die("Unable to lock db");
    $contents = "";
    if (filesize($dbFile)) {
        $contents = fread($handle, filesize($dbFile));
        if ($contents === false) die("Unable to read db");
    }
    $db = json_decode($contents);
    if (!$db) $db = (object) null;
    if (!isset($db->users)) {
        $db->users = [];
    }
    if (!isset($db->monitors))
        $db->monitors = [];
    if (!isset($db->settings))
        $db->settings = (object) null;
    if (!isset($db->settings->notify))
        $db->settings->notify = "username@gmail.com";
    if (!isset($db->settings->sendMailAtXFails))
        $db->settings->sendMailAtXFails = 3;
    if (!isset($db->settings->sendMailAtXSuccesses))
        $db->settings->sendMailAtXSuccesses = 2;
    if (!isset($db->settings->smtpHost))
        $db->settings->smtpHost = "smtp.gmail.com";
    if (!isset($db->settings->smtpSecure))
        $db->settings->smtpSecure = "tls";
    if (!isset($db->settings->smtpPort))
        $db->settings->smtpPort = 587;
    if (!isset($db->settings->smtpUser))
        $db->settings->smtpUser = "username@gmail.com";
    if (!isset($db->settings->smtpPass))
        $db->settings->smtpPass = "";
    if (!isset($db->settings->smtpFrom))
        $db->settings->smtpFrom = "username@gmail.com";
    if ($lockfile) return array($db, $handle);
    else return $db;
}

function saveDb()
{
    global $db, $dbhandle;
    if (!ftruncate($dbhandle, 0)) die("Unable to truncate db");
    if (!rewind($dbhandle)) die("Unable to rewind db");
    if (fwrite($dbhandle, json_encode($db)) === false)
        die("Unable to write db");
}

function pageMonitor($name, $url, $text = "")
{
    return (object) array(
        "name" => $name,
        "type" => "page",
        "url"  => $url,
        "text" => $text,
    );
}

function portMonitor($name, $host, $port)
{
    return (object) array(
        "name" => $name,
        "type" => "port",
        "host" => $host,
        "port" => $port,
    );
}

function pingMonitor($name, $host)
{
    return (object) array(
        "name" => $name,
        "type" => "ping",
        "host" => $host,
    );
}

function addMonitor($monitor)
{
    global $db;
    $monitor->id = newMonitorId();
    $db->monitors[] = $monitor;
}

function editMonitor($monitor)
{
    global $db;
    foreach ($db->monitors as $key => $value) {
        if ($value->id == $monitor->id)
            $db->monitors[$key] = $monitor;
    }
}

function testMonitor($monitor)
{
    switch ($monitor->type) {
        case "page":
            return testPageLoadTime($monitor);
            break;
        case "port":
            return testPortResponseTime($monitor);
            break;
        case "ping":
            return testPingResponseTime($monitor);
            break;
        default:
            return -1;
    }
}

function testPortResponseTime($portMonitor)
{
    $time1 = microtime(true);
    $connection = @fsockopen(
        $portMonitor->host,
        $portMonitor->port,
        $errno,
        $errstr
    );
    $time2 = microtime(true);
    if (is_resource($connection)) {
        fclose($connection);
        return (int)round(($time2 - $time1) * 1000);
    } else return -1;
}

function testPingResponseTime($pingMonitor)
{
    if (getOS() == 'linux') {
        $cmd = 'ping -c 1 ' . $pingMonitor->host;
    } else if (getOS() == 'windows') {
        $cmd = 'ping -n 1 ' . $pingMonitor->host;
    } else return -1;

    $output = shell_exec($cmd);
    $match = '#time(=|<)(.*) ?ms#';
    if (preg_match($match, $output, $matches)) {
        return (int)round($matches[2]);
    } else return -1;
}

function getOS()
{
    if (DIRECTORY_SEPARATOR === '/') return 'linux';
    else if (DIRECTORY_SEPARATOR === '\\') return 'windows';
    else return 'unknown';
}

function testPageLoadTime($pageMonitor)
{
    $time1 = microtime(true);
    $opts = array(
        "ssl" => array("verify_peer" => false, "verify_peer_name" => false),
        "http" => array("ignore_errors" => true)
    );
    $context = stream_context_create($opts);
    $page = @file_get_contents($pageMonitor->url, false, $context);
    $time2 = microtime(true);
    if (
        strlen($page)
        and strlen($pageMonitor->text)
        and stristr($page, $pageMonitor->text) === false
    ) {
        return -1;
    } elseif ($page) return (int)round(($time2 - $time1) * 1000);
    else return -1;
}

function sendMail($to, $subject, $body)
{
    global $db;
    require_once '../PHPMailer/src/Exception.php';
    require_once '../PHPMailer/src/PHPMailer.php';
    require_once '../PHPMailer/src/SMTP.php';
    try {
        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->SMTPAuth   = true;
        $mailer->Host       = $db->settings->smtpHost;
        $mailer->Username   = $db->settings->smtpUser;
        $mailer->Password   = $db->settings->smtpPass;
        $mailer->SMTPSecure = $db->settings->smtpSecure;
        $mailer->Port       = $db->settings->smtpPort;
        $mailer->setFrom($db->settings->smtpFrom);
        $mailer->addAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->send();
    } catch (Exception $e) {
        die("Message could not be sent. Error: {$mailer->ErrorInfo}");
    }
}

function getUser($mixed = "")
{
    global $db;
    // Current user
    if (empty($mixed)) $mixed = $_SESSION['id'];
    // User id
    if (is_numeric($mixed)) {
        foreach ($db->users as $user) {
            if ($user->id == $mixed) return $user;
        }
        return false;
    }
    // User email
    else {
        foreach ($db->users as $user) {
            if ($user->email == $mixed) return $user;
        }
        return false;
    }
}

function getMonitor($id)
{
    global $db;
    foreach ($db->monitors as $monitor) {
        if ($monitor->id == $id) return $monitor;
    }
    return false;
}

function newSecret($length = 25)
{
    $token = "";
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" .
        "abcdefghijklmnopqrstuvwxyz" .
        "0123456789";
    for ($i = 0; $i < $length; $i++) {
        $rnd = rand(0, strlen($chars) - 1);
        $char = $chars[$rnd];
        $token .= $char;
    }
    return $token;
}

function loginLink($secret)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
        ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . "/action.php?login=" . $secret;
}

function newUser($email, $password)
{
    global $db;
    $user = (object) null;
    $user->id = newUserId();
    $user->email = $email;
    $user->password = $password;
    $db->users[] = $user;
    return $user;
}

function updateUser($user)
{
    global $db;
    foreach ($db->users as $key => $value) {
        if ($value->id == $user->id) {
            $db->users[$key] = $user;
            return;
        }
    }
}

function deleteUser($user)
{
    global $db;
    foreach ($db->users as $key => $value) {
        if ($value->id == $user->id) {
            unset($db->users[$key]);
            $db->users = array_values($db->users);
            return;
        }
    }
}

function deleteMonitor($monitor)
{
    global $db;
    foreach ($db->monitors as $key => $value) {
        if ($value->id == $monitor->id) {
            unset($db->monitors[$key]);
            $db->monitors = array_values($db->monitors);
            deleteStats($monitor->id);
            return;
        }
    }
    deleteStats($monitor->id);
}

function newUserId()
{
    global $db;
    $id = 1;
    foreach ($db->users as $user)
        if ($user->id >= $id)
            $id = $user->id + 1;
    return $id;
}

function newMonitorId()
{
    global $db;
    $id = 1;
    foreach ($db->monitors as $monitor)
        if ($monitor->id >= $id)
            $id = $monitor->id + 1;
    return $id;
}

function login($code)
{
    global $db;
    foreach ($db->users as $user) {
        if ($user->loginCode == $code) {
            unset($user->loginCode);
            updateUser($user);
            saveDb();
            $_SESSION['id'] = $user->id;
            return $user;
        }
    }
    return false;
}

function thisUrl()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . "/";
}

function redirect($url = "")
{
    $location = thisUrl() . $url;
    header("Location: $location", true, 303);
    die();
}

function message($message, $error = false, $redirect = "")
{
    if ($error) $_SESSION['error'] = $message;
    else $_SESSION['message'] = $message;
    redirect($redirect);
}

function loadStats($lockfile = true)
{
    $statsFile = "../stats.json";
    $handle = fopen($statsFile, "c+");
    if (!$handle) die("Unable to open stats");
    if ($lockfile && !flock($handle, LOCK_EX)) die("Unable to lock stats");
    $contents = "";
    if (filesize($statsFile)) {
        $contents = fread($handle, filesize($statsFile));
        if ($contents === false) die("Unable to read stats");
    }
    $stats = json_decode($contents);
    if (!$stats) $stats = [];
    if ($lockfile) return array($stats, $handle);
    else return $stats;
}

function saveStats($stats, $handle)
{
    if (!ftruncate($handle, 0)) die("Unable to truncate stats");
    if (!rewind($handle)) die("Unable to rewind stats");
    if (fwrite($handle, json_encode($stats)) === false)
        die("Unable to write stats");
}

function addStats($newStats)
{
    list($stats, $handle) = loadStats();
    $stats = cleanupStats($stats);
    $stats = array_merge($stats, $newStats);
    saveStats($stats, $handle);
}

function cleanupStats($stats)
{
    $cleanupTime = time() - 2 * 24 * 60 * 60;
    foreach ($stats as $key => $stat)
        if ($stat[1] < $cleanupTime) unset($stats[$key]);
    return array_values($stats);
}

function deleteStats($monitorid)
{
    list($stats, $handle) = loadStats();
    foreach ($stats as $key => $stat)
        if ($stat[0] == $monitorid)
            unset($stats[$key]);
    saveStats(array_values($stats), $handle);
}

function loginRequired()
{
    if (!isset($_SESSION['id'])) redirect('?p=login');
}

function showMessage()
{
    if (isset($_SESSION['message'])) {
        echo '<div id="message">' . $_SESSION['message'] . '</div>';
    }
    if (isset($_SESSION['error'])) {
        echo '<div id="error">' . $_SESSION['error'] . '</div>';
    }
    unset($_SESSION['message'], $_SESSION['error']);
}
