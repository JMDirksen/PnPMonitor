<?php
    // Init
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // Load required files
    require_once("functions.php");
    $config = require_once('config.php');

    // Load database
    list($db, $handle) = loadDb($config['DB_FILE']);

    // Setup database
    if(!isset($db->users)) $db->users = [];

    session_start();

    if(isset($_POST['emailForm'])) {
        $email = $_POST['email'];
        $user = getUser($db, $email);
        if(!$user) {
            $user = (object) null;
            $user->email = $email;
        }
        $user->token = newToken();
        updateUser($db, $user);
        sendMail($config, "PnPMonitor Login Link", tokenLink($user->token));
        echo "An e-mail has been sent with your login link.\n";
    }

    if(isset($_GET['token'])) {
        $token = $_GET['token'];
        $user = getUserFromToken($db, $token);
        die("User: ".$user->email);
    }

    // Save database
    saveDb($db, $handle);

    $body = '<form method="post">'
    .'<input type="email" name="email" placeholder="email" required>'
    .'<input type="submit" name="emailForm" value="Send login link">'
    .'</form>';

?>
<html>
    <head>
        <title>Page and Port Monitor</title>
    </head>
    <body>
        <?php echo $body; ?>
    </body>
</html>
