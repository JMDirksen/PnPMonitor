<?php
    require_once('init.php');
    $show = "";
    if(isset($_SESSION['message'])) {
        $show = '<div id="message">'.$_SESSION['message'].'</div>';
    }
    if(isset($_SESSION['error'])) {
        $show .= '<div id="error">'.$_SESSION['error'].'</div>';
    }
    unset($_SESSION['message'], $_SESSION['error']);
?>
<html>
<head>
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div id="header">Page and Port Monitor</div>
<?php echo $show; ?>
<form method="POST" action="action.php">
<label>Email</label>
<input type="email" name="email" placeholder="email" required>
<label>Password</label>
<input type="password" name="password" placeholder="password" required>
<input type="submit" name="loginForm" value="Login">
</form>
</body>
</html>
