<?php
require_once('init.php');
list($db, $dbhandle) = loadDb();

// Login
if (isset($_POST['loginForm'])) {
    if (isset($_SESSION['id'])) redirect();
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $user = getUser($email);
    if ($user && password_verify($password, $user->password)) {
        $_SESSION['id'] = $user->id;
        message('Logged in');
    } elseif (!count($db->users)) {
        $user = newUser($email, password_hash($password, PASSWORD_DEFAULT));
        saveDb();
        $_SESSION['id'] = $user->id;
        message('Welcome, please configure email settings', false, '?p=settings');
    }
    message('Invalid login', true, '?p=login');
}

// Login check
loginRequired();
// --- Must be logged in for below actions ---

// Save monitor
if (isset($_POST['saveMonitor'])) {
    $name = htmlentities($_POST['name']);
    if (!$name) message("Invalid name", true, "?p=edit&id=" . $_POST['id']);
    if ($_POST['type'] == "page") {
        $url = filter_var($_POST['field1'], FILTER_VALIDATE_URL);
        $text = htmlentities($_POST['field2']);
        if (!$url) message("Invalid url", true, "?p=edit&id=" . $_POST['id']);
        $monitor = pageMonitor($name, $url, $text);
    } elseif ($_POST['type'] == "port") {
        $host = filter_var(
            $_POST['field1'],
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME
        );
        $port = filter_var($_POST['field2'], FILTER_VALIDATE_INT);
        if (!$host) message("Invalid host", true, "?p=edit&id=" . $_POST['id']);
        if (!$port) message("Invalid port", true, "?p=edit&id=" . $_POST['id']);
        $monitor = portMonitor($name, $host, $port);
    } elseif ($_POST['type'] == "ping") {
        $host = filter_var(
            $_POST['field1'],
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME
        );
        if (!$host) message("Invalid host", true, "?p=edit&id=" . $_POST['id']);
        $monitor = pingMonitor($name, $host);
    }
    if ($_POST['id'] == "new") addMonitor($monitor);
    else {
        $monitor->id = (int)$_POST['id'];
        editMonitor($monitor);
    }
    saveDb();
    redirect("?p=monitor&id=" . $_POST['id']);
}

// Save settings
if (isset($_POST['settingsForm'])) {
    $db->settings->notify = $_POST['notify'];
    $db->settings->smtpHost = $_POST['smtpHost'];
    $db->settings->smtpSecure = $_POST['smtpSecure'];
    $db->settings->smtpPort = $_POST['smtpPort'];
    $db->settings->smtpUser = $_POST['smtpUser'];
    if (!empty($_POST['smtpPass'])) $db->settings->smtpPass = $_POST['smtpPass'];
    $db->settings->smtpFrom = $_POST['smtpFrom'];
    saveDb();
    sendMail($db->settings->notify, "PnPMonitor test email", "This is a test email.");
    message("Changes saved, test email sent", false, "?p=settings");
}

// Delete monitor
if (isset($_GET['delete'])) {
    $monitor = getMonitor($_GET['delete']);
    if ($monitor->type == "page") {
        $_SESSION['pagename'] = $monitor->name;
        $_SESSION['url'] = $monitor->url;
        $_SESSION['text'] = $monitor->text;
    } else {
        $_SESSION['portname'] = $monitor->name;
        $_SESSION['host'] = $monitor->host;
        $_SESSION['port'] = $monitor->port;
    }
    deleteMonitor($monitor);
    saveDb();
    redirect("?p=monitors");
}

// New user
if (isset($_POST['newUser'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) message("Invalid email", true, "?p=users");
    $password = $_POST['password'];
    $test = getUser($email);
    if ($test) message("User already exists", true, "?p=users");
    newUser($email, password_hash($password, PASSWORD_DEFAULT));
    saveDb();
    message("User added", false, "?p=users");
}

// Delete user
if (isset($_GET['delUser'])) {
    if (count($db->users) == 1)
        message(
            "Can not delete the only user, add a new user first",
            true,
            "?p=users"
        );
    $user = getUser($_GET['delUser']);
    deleteUser($user);
    saveDb();
    message("User $user->email deleted", false, "?p=users");
}

// Logout
if (isset($_GET['logout'])) {
    session_unset();
    message("Logged out", false, "?p=login");
}
