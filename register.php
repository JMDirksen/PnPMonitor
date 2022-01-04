<div id="button-bar">
    <div></div>
    <div class="button">
        <a href="javascript:document.getElementById('registerForm').submit();">Register</a>
    </div>
</div>
<?php showMessage(); ?>
<form id="registerForm" method="POST" action="action.php">
<input type="hidden" name="registerForm">
<label>Email</label>
<input type="email" name="email" placeholder="email" required>
<label>Password</label>
<input type="password" name="password" placeholder="password" required>
<label>Confirm password</label>
<input type="password" name="password2" placeholder="password" required>
</form>
