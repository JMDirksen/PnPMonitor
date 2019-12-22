<?php

// Init
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();

// Load required files
require_once("functions.php");
$config = require_once('config.php');

// Load/Setup database
list($db, $dbhandle) = loadDb();
if(!isset($db->users)) $db->users = [];


// Handle: Register
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
    updateUser($user);
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    saveDb();
    $_SESSION['msg'] = "An email has been sent for confirmation";
    redirect();
}

// Handle: Resend confirmation code
if(isset($_GET['resend'])) {
    $user = getUser($_SESSION['email']);
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    $_SESSION['msg'] = "An email has been sent for confirmation";
    redirect();
}

// Handle: Confirm
if(isset($_GET['confirm'])) {
    confirm($_GET['confirm']);
    saveDb();
    $_SESSION['msg'] = "Email has been confirmed";
    redirect();
}

// Handle: Login
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
        $_SESSION['msg'] = "Email has to be confirmed first, find the confirmation link in your mailbox (<a href=\"?resend\">resend</a>)";
        redirect();
    }
    $_SESSION['id'] = $user->id;
    redirect();
}

// Handle: Logout
if(isset($_GET['logout'])) {
    unset($_SESSION['id']);
    redirect();
}

// Header
echo "<html><head><title>Page and Port Monitor</title></head><body>";

// Message
if(isset($_SESSION['msg'])) {
    echo '<div class="msg">'.$_SESSION['msg'].'</div>';
    unset($_SESSION['msg']);
}

// Register form
if(isset($_GET['register'])) {
    echo '<form method="post">';
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
    echo '<input type="email" name="email" placeholder="email" value="'.$email.'" required><br>';
    echo '<input type="password" name="password" placeholder="password" required><br>';
    echo '<input type="password" name="password2" placeholder="password check" required><br>';
    echo '<input type="submit" name="registerForm" value="Register">';
    echo '</form>';
}

// Login form
else {
    echo '<form method="post">';
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
    echo '<input type="email" name="email" placeholder="email" value="'.$email.'" required><br>';
    echo '<input type="password" name="password" placeholder="password" required><br>';
    echo '<input type="submit" name="loginForm" value="Login"> ';
    echo '<a href="?register">Register</a>';
    echo '</form>';
}

// Footer
echo "</body></html>";
