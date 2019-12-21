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
    list($db, $handle) = loadDb($config['DB_FILE']);
    if(!isset($db->users)) $db->users = [];

    // Request login token
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
        saveDb($db, $handle);
        $_SESSION['msg'] = "An e-mail has been sent with your login link.";
        redirect();
    }

    // Token login
    if(isset($_GET['token'])) {
        $token = $_GET['token'];
        $user = getUserFromToken($db, $token);
        $_SESSION["email"] = $user->email;
        $user->token = null;
        updateUser($db, $user);
        saveDb($db, $handle);
        redirect();
    }

    $body = '<form method="post">'
    .'<input type="email" name="email" placeholder="email" required>'
    .'<input type="submit" name="emailForm" value="Send login link">'
    .'</form>';
    
    if(isset($_SESSION['msg'])) {
        $body = $_SESSION['msg']."<br>".$body;
        unset($_SESSION['msg']);
    }

?>
<html>
    <head>
        <title>Page and Port Monitor</title>
    </head>
    <body>
        <?php echo $body; ?>
    </body>
</html>
