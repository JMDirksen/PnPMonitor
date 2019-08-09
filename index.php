<?php
    session_start();

    spl_autoload_register(function ($class_name) {
        @include $class_name . '.class.php';
    });

    if(isset($_POST['emailForm'])) {
        $email = $_POST['email'];
        if(User::existingUser($email)) {
            die('Existing user');
        }
        else {
            $user = new User();
            $user->setEmail($email);
            $user->generateToken();
            $user->save();
        }
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
