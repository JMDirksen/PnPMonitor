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

// Header
echo "<html><head><title>Page and Port Monitor</title></head><body>";

// Message
if(isset($_SESSION['msg'])) {
    echo '<div class="msg">'.$_SESSION['msg'].'</div>';
    unset($_SESSION['msg']);
}

// Register form
if(isset($_GET['register'])) {
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
    echo '<form action="action.php" method="post">';
    echo '<input type="email" name="email" placeholder="email" '.
         'value="'.$email.'" required><br>';
    echo '<input type="password" name="password" placeholder="password" '.
         'required><br>';
    echo '<input type="password" name="password2" placeholder="password check"'.
         ' required><br>';
    echo '<input type="submit" name="registerForm" value="Register">';
    echo '</form>';
}

// Monitors form
elseif(isset($_SESSION['id'])) {
    // Monitors list
    foreach($db->monitors as $monitor) {
        if($monitor->user == $_SESSION['id']) {
            if($monitor->type == "page") {
                echo '<form action="action.php" method="post">';
                echo '<input type="hidden" name="id" value="'.$monitor->id.'">';
                echo '<input type="hidden" name="type" value="page">';
                echo '<input type="text" name="name" '.
                     'value="'.$monitor->name.'" placeholder="name" required>';
                echo '<input type="text" name="url" value="'.$monitor->url.'" '.
                     'placeholder="http(s)://" required>';
                echo '<input type="text" name="text" '.
                     'value="'.$monitor->text.'" placeholder="text">';
                echo '<input type="submit" name="editPage" '.
                     'value="Save Page Monitor">';
                echo '<input type="submit" name="deleteMonitor" value="X" '.
                     'onClick="return confirm(\'Delete monitor '.
                     $monitor->name.'?\')">';
                echo '</form>';
            }
            else {
                echo '<form action="action.php" method="post">';
                echo '<input type="hidden" name="id" value="'.$monitor->id.'">';
                echo '<input type="hidden" name="type" value="port">';
                echo '<input type="text" name="name" '.
                     'value="'.$monitor->name.'" placeholder="name" required>';
                echo '<input type="text" name="host" '.
                     'value="'.$monitor->host.'" placeholder="host" required>';
                echo '<input type="text" name="port" '.
                     'value="'.$monitor->port.'" placeholder="port" required>';
                echo '<input type="submit" name="editPort" '.
                     'value="Save Port Monitor">';
                echo '<input type="submit" name="deleteMonitor" value="X" '.
                     'onClick="return confirm(\'Delete monitor '.
                     $monitor->name.'?\')">';
                echo '</form>';
            }
        }        
    }

    // Add Page monitor form
    echo '<form action="action.php" method="post">';
    echo '<input type="hidden" name="type" value="page">';
    echo '<input type="text" name="name" placeholder="name" required>';
    echo '<input type="text" name="url" placeholder="http(s)://" required>';
    echo '<input type="text" name="text" placeholder="text">';
    echo '<input type="submit" name="addPage" value="Add Page Monitor">';
    echo '</form>';
    // Add Port monitor form
    echo '<form action="action.php" method="post">';
    echo '<input type="hidden" name="type" value="port">';
    echo '<input type="text" name="name" placeholder="name" required>';
    echo '<input type="text" name="host" placeholder="host" required>';
    echo '<input type="text" name="port" placeholder="port" required>';
    echo '<input type="submit" name="addPort" value="Add Port Monitor">';
    echo '</form>';
    // Logout
    echo '<form action="action.php" method="post">';
    echo '<input type="submit" name="logout" value="Logout">';
    echo '</form>';
}

// Login form
else {
    echo '<form action="action.php" method="post">';
    echo '<input type="email" name="email" placeholder="email" required><br>';
    echo '<input type="password" name="password" placeholder="password" '.
         'required><br>';
    echo '<input type="submit" name="loginForm" value="Login"> ';
    echo '<a href="?register">Register</a>';
    echo '</form>';
}

// Footer
echo "</body></html>";
