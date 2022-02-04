<?php
if(isset($_SESSION['id'])) redirect();
?>
<div id="button-bar">
    <div></div>
    <div>
        <div class="button">
            <a href="javascript:document.getElementById('loginForm').submit();">Login</a>
        </div>
    </div>
</div>
<?php showMessage(); ?>
<form id="loginForm" method="POST" action="action.php">
<input type="hidden" name="loginForm">
<label>Email</label>
<input type="email" name="email" placeholder="email" required>
</form>
