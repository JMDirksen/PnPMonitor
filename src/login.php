<div id="button-bar">
    <div></div>
    <div class="button">
        <a href="javascript:document.getElementById('loginForm').submit();">Login</a>
    </div>
</div>
<?php showMessage(); ?>
<form id="loginForm" method="POST" action="action.php">
<input type="hidden" name="loginForm">
<label>Email</label>
<input type="email" name="email" placeholder="email" required>
<label>Password</label>
<input type="password" name="password" placeholder="password" required>
</form>
