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
    $email = $_POST['email'];
    $_SESSION['email'] = $email;
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['msg'] = "Invalid email format";
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
    $email = $_POST['email'];
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

// Add monitor
if(isset($_POST['addPage']) or isset($_POST['addPort'])) {
    if(!isset($_SESSION['id'])) redirect();
    if(isset($_POST['addPage'])) {
        $monitor = pageMonitor($_SESSION['id'], $_POST['name'],
                   $_POST['url'], $_POST['text']);
    }
    else {
        $monitor = portMonitor($_SESSION['id'], $_POST['name'],
                   $_POST['host'], $_POST['port']);
    }
    addMonitor($monitor);
    saveDb();
    redirect();
}

// Edit monitor
if(isset($_POST['editPage']) or isset($_POST['editPort'])) {
    $monitor = getMonitor($_POST['id']);
    if(!isset($_SESSION['id'])) redirect();
    if($monitor->user <> $_SESSION['id']) redirect();
    
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
    if(!$monitor->user == $_SESSION['id']) redirect();
    deleteMonitor($monitor);
    saveDb();
    redirect();
}

// Logout
if(isset($_POST['logout'])) {
    unset($_SESSION['id']);
    redirect();
}
