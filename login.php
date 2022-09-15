<?php
if (isset($_SESSION['id'])) redirect();
?>
<div id="button-bar">
    <div></div>
    <div></div>
</div>
<?php showMessage(); ?>
<form id="loginForm" method="POST" action="action.php">
    <input type="hidden" name="loginForm">
    <label>Email</label>
    <input type="email" name="email" placeholder="email" required>
    <label>Password</label>
    <input type="password" name="password" placeholder="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}" minlength="5" required>
    <input type="submit" id="submit" value="Login">
</form>
