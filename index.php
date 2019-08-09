<?php
    session_start();

    spl_autoload_register(function ($class_name) {
        @include $class_name . '.class.php';
    });

    if(isset($_POST['emailForm'])) {
        $email = $_POST['email'];
        if(User::existingUser($email)) {
            $user = new User();
            $user->loadFromEmail($email);
            $protocol = ($_SERVER['HTTPS']=="on") ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $link = $protocol.$host."/?token=".$user->token;
            $mailer = new Mailer();
            $mailer->send("PnPMonitor Login Link", $link);
        }
        else {
            $user = new User();
            $user->setEmail($email);
            $user->generateToken();
            $user->save();
            $protocol = ($_SERVER['HTTPS']=="on") ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $link = $protocol.$host."/?token=".$user->token;
            $mailer = new Mailer();
            $mailer->send("PnPMonitor Login Link", $link);
        }
        die("An e-mail has been sent with your login link.");
    }

    if(isset($_GET['token'])) {
        $token = $_GET['token'];
        $user = new User();
        $user->loadFromToken($token);
        die($user->id." ".$user->email);
    }

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
