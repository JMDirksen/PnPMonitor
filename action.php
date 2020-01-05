<?php
require_once('init.php');
list($db, $dbhandle) = loadDb();

// Register
if(isset($_POST['registerForm'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $_SESSION['email'] = $email;
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if(!$email) message("Invalid email", true, "?p=register");

    if(strlen($password) < 5)
        message("Password must be at least 5 characters long", true, "?p=register");

    if($password <> $password2)
        message("Passwords do not match", true, "?p=register");

    $user = getUser($email);
    if($user) message("A user with this email already exists", true, "?p=register");

    $user = (object) null;
    $user->id = newUserId();
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->confirm = newSecret();
    $db->users[] = $user;
    saveDb();
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    message("An email has been sent for confirmation");
}

// Resend confirmation code
if(isset($_GET['resend'])) {
    $user = getUser($_SESSION['email']);
    sendMail("PnPMonitor email confirmation", confirmLink($user->confirm));
    message("An email has been sent for confirmation");
}

// Confirm
if(isset($_GET['confirm'])) {
    confirm($_GET['confirm']);
    saveDb();
    message("Email has been confirmed");
}

// Login
if(isset($_POST['loginForm'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $user = verifyLogin($email, $password);
    if(!$user) message("Incorrect username or password", true, "?p=login");
    if(isset($user->confirm)) message("Email has to be confirmed first, ".
        "find the confirmation link in your mailbox ".
        "(<a href=\"action.php?resend\">resend</a>)", false, "?p=login");
    $_SESSION['id'] = $user->id;
    redirect();
}

// Login check
loginRequired();
$userid = $_SESSION['id'];
// --- Must be logged in for below actions ---

// Add monitor
if(isset($_POST['addPage']) or isset($_POST['addPort'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    if(!$name) message("Invalid name", true);
    if(isset($_POST['addPage'])) {
        $url = filter_var($_POST['url'], FILTER_VALIDATE_URL);
        $text = filter_var($_POST['text'], FILTER_SANITIZE_STRING);
        if(!$url) message("Invalid url", true);
        $monitor = pageMonitor($userid, $name, $url, $text);
        unset($_SESSION['pagename']);
        unset($_SESSION['url']);
        unset($_SESSION['text']);
    }
    else {
        $host = filter_var($_POST['host'], FILTER_VALIDATE_DOMAIN,
                           FILTER_FLAG_HOSTNAME);
        $port = filter_var($_POST['port'], FILTER_VALIDATE_INT);
        if(!$host) message("Invalid host", true);
        if(!$port) message("Invalid port", true);
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
    if($monitor->user <> $userid) message("Not your monitor", true);
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
if(isset($_GET['logout'])) {
    session_unset();
    message("Logged out", false, "?p=login");
}
