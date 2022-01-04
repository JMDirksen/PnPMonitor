<?php
loginRequired();
$db = loadDb(false);
$user = getUser();
?>
<div id="button-bar">
    <div class="button"><a href="?p=menu">Back</a></div>
    <div class="button">
        <a href="javascript:document.getElementById('accountForm').submit();">Save</a>
    </div>
</div>
<?php showMessage(); ?>
<form id="accountForm" method="POST" action="action.php">
<input type="hidden" name="accountForm">
<label>Email</label>
<input type="email" name="email" placeholder="email" value="<?php echo $user->email; ?>" required>
<label>Current password (required)</label>
<input type="password" name="currentpassword" placeholder="password" required>
<label>New password (optional)</label>
<input type="password" name="newpassword" placeholder="password">
<label>Confirm new password</label>
<input type="password" name="newpassword2" placeholder="password">
</form>
