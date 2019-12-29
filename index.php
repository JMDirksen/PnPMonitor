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
echo '<html><head>';
echo '<title>Page and Port Monitor</title>';
echo '<link rel="stylesheet" href="style.css">';
echo '</head><body>';

// Message
if(isset($_SESSION['msg'])) {
    echo '<div class="msg">'.$_SESSION['msg'].'</div>';
    unset($_SESSION['msg']);
}
// Error message
if(isset($_SESSION['errorMsg'])) {
    echo '<div class="errorMsg">'.$_SESSION['errorMsg'].'</div>';
    unset($_SESSION['errorMsg']);
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

// Monitors
elseif(isset($_SESSION['id'])) {

    // Monitors list
    echo '<table><tr><th>Name</th><th>URL/Host</th><th>Text/Port</th>'.
         '<th>Results</th><th> </th></th>';
    foreach($db->monitors as $monitor) {
          if($monitor->user <> $_SESSION['id']) continue;
          $page = $monitor->type == "page" ? true : false;
          $time = @$monitor->lastTime ?: "n/a";
          if(!isset($monitor->lastResult)) $result = "n/a";
          elseif($monitor->lastResult === false) $result = "failure";
          else $result = $monitor->lastResult." ms";
          echo '<tr>';
          echo '<td>'.$monitor->name.'</td>';
          echo '<td>'.($page?$monitor->url:$monitor->host).'</td>';
          echo '<td>'.($page?$monitor->text:$monitor->port).'</td>';
          echo '<td class="result"><a href="graph.php?id='.$monitor->id.'" target="_blank">'.
               'Time: '.$time.'<br>'.
               'Response: '.$result.'</a></td>';
          echo '<td><form action="action.php" method="post" '.
               'onSubmit="return confirm(\'Delete monitor '.$monitor->name.'?\');">';
          echo '<input type="hidden" name="id" value="'.$monitor->id.'">';
          echo '<input type="submit" name="deleteMonitor" value="Delete">';
          echo '</form></td>';
          echo '</tr>';
    }
    echo "</table>";

    // Add Page monitor form
    echo '<table><tr><th>Name</th><th>URL/Host</th><th>Text/Port</th><th> </th></tr>';
    echo '<tr><form action="action.php" method="post">';
    echo '<input type="hidden" name="type" value="page">';
    echo '<td><input type="text" class="name" name="name" placeholder="name" required';
    if(isset($_SESSION['pagename'])) echo ' value="'.$_SESSION['pagename'].'"';
    echo '></td>';
    echo '<td><input type="text" class="urlhost" name="url"';
    if(isset($_SESSION['url'])) echo ' value="'.$_SESSION['url'].'"';
    echo ' placeholder="http(s)://" required></td>';
    echo '<td><input type="text" class="textport" name="text"';
    if(isset($_SESSION['text'])) echo ' value="'.$_SESSION['text'].'"';
    echo ' placeholder="should contain text"></td>';
    echo '<td><input type="submit" name="addPage" value="Add Page Monitor"></td>';
    echo '</form></tr>';

    // Add Port monitor form
    echo '<tr><form action="action.php" method="post">';
    echo '<input type="hidden" name="type" value="port">';
    echo '<td><input type="text" class="name" name="name" placeholder="name"';
    if(isset($_SESSION['portname'])) echo ' value="'.$_SESSION['portname'].'"';
    echo ' required></td>';
    echo '<td><input type="text" class="urlhost" name="host"';
    if(isset($_SESSION['host'])) echo ' value="'.$_SESSION['host'].'"';
    echo ' placeholder="host" required></td>';
    echo '<td><input type="number" class="textport" min=1 max=65535 '.
         'name="port" placeholder="port" required';
    if(isset($_SESSION['port'])) echo ' value="'.$_SESSION['port'].'"';
    echo '></td>';
    echo '<td><input type="submit" name="addPort" value="Add Port Monitor"></td>';
    echo '</form></tr></table>';

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
