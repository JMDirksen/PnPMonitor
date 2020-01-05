<?php
    require_once('init.php');
    $page = 'menu.php';
    if(isset($_GET['login'])) $page = 'login.php';
    elseif(isset($_GET['monitors'])) $page = 'monitors.php';
    elseif(isset($_GET['settings'])) $page = 'settings.php';
?>
<html>
<head>
<title>Page and Port Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<link rel="manifest" href="manifest.json">
</head>
<body>
<div id="header">Page and Port Monitor</div>
<?php showMessage(); ?>
<?php @include($page); ?>
</body>
</html>
