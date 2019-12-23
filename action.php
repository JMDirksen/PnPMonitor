<?php

// Init
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();

// Load required files
require_once("functions.php");
$config = require_once('config.php');

// Load database
list($db, $dbhandle) = loadDb();

// Register
if(isset($_POST['registerForm'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $_SESSION['email'] = $email;
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if(!$email) msg("Invalid email", true, "?register");

    if(strlen($password) < 5)
        msg("Password must be at least 5 characters long", true, "?register");

    if($password <> $password2)
        msg("Passwords do not match", true, "?register");

    $user = getUser($email);
    if($user) msg("A user with this email already exists", true, "?register");

    $user = (object) null;
    $user->id = newUserId();
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->confirm = newSecret();
    $db->users[] = $user;
    saveDb();
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    msg("An email has been sent for confirmation");
}

// Resend confirmation code
if(isset($_GET['resend'])) {
    $user = getUser($_SESSION['email']);
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    msg("An email has been sent for confirmation");
}

// Confirm
if(isset($_GET['confirm'])) {
    confirm($_GET['confirm']);
    saveDb();
    msg("Email has been confirmed");
}

// Login
if(isset($_POST['loginForm'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $_SESSION["email"] = $email;
    $user = verifyLogin($email, $password);
    if(!$user) msg("Incorrect username or password", true);
    if(isset($user->confirm)) msg("Email has to be confirmed first, ".
        "find the confirmation link in your mailbox ".
        "(<a href=\"action.php?resend\">resend</a>)");
    $_SESSION['id'] = $user->id;
    redirect();
}

// Login check
if(!isset($_SESSION['id'])) redirect();
$userid = $_SESSION['id'];
// --- Must be logged in for below actions ---


// Add monitor
if(isset($_POST['addPage']) or isset($_POST['addPort'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    if(!$name) msg("Invalid name", true);
    if(isset($_POST['addPage'])) {
        $url = filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
        $text = filter_var($_POST['text'], FILTER_SANITIZE_STRING);
        if(!$url) msg("Invalid url", true);
        $monitor = pageMonitor($userid, $name, $url, $text);
        unset($_SESSION['pagename']);
        unset($_SESSION['url']);
        unset($_SESSION['text']);
    }
    else {
        $host = filter_var($_POST['host'], FILTER_VALIDATE_DOMAIN,
                           FILTER_FLAG_HOSTNAME);
        $port = filter_var($_POST['port'], FILTER_VALIDATE_INT);
        if(!$host) msg("Invalid host", true);
        if(!$port) msg("Invalid port", true);
        $monitor = portMonitor($userid, $name, $host, $port);
        unset($_SESSION['portname']);
        unset($_SESSION['host']);
        unset($_SESSION['port']);
    }
    addMonitor($monitor);
    saveDb();
    redirect();
}

// Delete monitor
if(isset($_POST['deleteMonitor'])) {
    $monitor = getMonitor($_POST['id']);
    if($monitor->user <> $userid) msg("Not your monitor", true);
    if($monitor->type == "page") {
        $_SESSION['pagename'] = $monitor->name;
        $_SESSION['url'] = $monitor->url;
        $_SESSION['text'] = $monitor->text;
    }
    else {
        $_SESSION['portname'] = $monitor->name;
        $_SESSION['host'] = $monitor->host;
        $_SESSION['port'] = $monitor->port;
    }
    deleteMonitor($monitor);
    saveDb();
    redirect();
}

// Logout
if(isset($_POST['logout'])) {
    unset($_SESSION['id']);
    msg("Logged out");
}
