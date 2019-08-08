<?php
    session_start();

    spl_autoload_register(function ($class_name) {
        @include $class_name . '.class.php';
    });

    if(isset($_POST['email'])) {
        
    }

    $body = '<form method="post"><input type="email" name="email" placeholder="email" required></form>';
?>
<html>
    <head>
        <title>Page and Port Monitor</title>
    </head>
    <body>
        <?php echo $body; ?>
    </body>
</html>
