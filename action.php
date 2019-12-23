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

    if(!$email) {
        $_SESSION['msg'] = "Invalid email";
        redirect(thisUrl()."?register");
    }

    if(strlen($password) < 5) {
        $_SESSION['msg'] = "Password needs to be at least 5 characters long";
        redirect(thisUrl()."?register");
    }

    if($password <> $password2) {
        $_SESSION['msg'] = "Passwords do not match";
        redirect(thisUrl()."?register");
    }

    $user = getUser($email);
    if($user) {
        $_SESSION['msg'] = "A user with this email already exists";
        redirect(thisUrl()."?register");
    }
    
    $user = (object) null;
    $user->id = newUserId();
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->confirm = newSecret();
    $db->users[] = $user;
    saveDb();
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    $_SESSION['msg'] = "An email has been sent for confirmation";
    redirect();
}

// Resend confirmation code
if(isset($_GET['resend'])) {
    $user = getUser($_SESSION['email']);
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    $_SESSION['msg'] = "An email has been sent for confirmation";
    redirect();
}

// Confirm
if(isset($_GET['confirm'])) {
    confirm($_GET['confirm']);
    saveDb();
    $_SESSION['msg'] = "Email has been confirmed";
    redirect();
}

// Login
if(isset($_POST['loginForm'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $_SESSION["email"] = $email;
    $user = verifyLogin($email, $password);
    if(!$user) {
        $_SESSION['msg'] = "Incorrect username or password";
        redirect();
    }
    if(isset($user->confirm)) {
        $_SESSION['msg'] = "Email has to be confirmed first, ".
            "find the confirmation link in your mailbox ".
            "(<a href=\"action.php?resend\">resend</a>)";
        redirect();
    }
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
    if(!$name) {
        $_SESSION['msg'] = "Invalid name";
        redirect();
    }
    if(isset($_POST['addPage'])) {
        $url = filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
        $text = filter_var($_POST['text'], FILTER_SANITIZE_STRING);
        if(!$url) {
            $_SESSION['msg'] = "Invalid url";
            redirect();
        }
        $monitor = pageMonitor($userid, $name, $url, $text);
    }
    else {
        $host = filter_var($_POST['host'], FILTER_VALIDATE_DOMAIN,
                           FILTER_FLAG_HOSTNAME);
        $port = filter_var($_POST['port'], FILTER_VALIDATE_INT);
        if(!$host) {
            $_SESSION['msg'] = "Invalid host";
            redirect();
        }
        if(!$port) {
            $_SESSION['msg'] = "Invalid port";
            redirect();
        }
        $monitor = portMonitor($userid, $name, $host, $port);
    }
    addMonitor($monitor);
    saveDb();
    redirect();
}

// Edit monitor
if(isset($_POST['editPage']) or isset($_POST['editPort'])) {
    $monitor = getMonitor($_POST['id']);
    if($monitor->user <> $userid) redirect();
    
    $monitor->name = $_POST['name'];
    if(isset($_POST['editPage'])) {
        $monitor->url = $_POST['url'];
        $monitor->text = $_POST['text'];
    }
    else {
        $monitor->host = $_POST['host'];
        $monitor->port = $_POST['port'];
    }
    updateMonitor($monitor);
    saveDb();
    redirect();
}

// Delete monitor
if(isset($_POST['deleteMonitor'])) {
    $monitor = getMonitor($_POST['id']);
    if($monitor->user <> $userid) redirect();
    deleteMonitor($monitor);
    saveDb();
    redirect();
}

// Logout
if(isset($_POST['logout'])) {
    unset($_SESSION['id']);
    redirect();
}
