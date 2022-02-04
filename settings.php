<?php
loginRequired();
$db = loadDb(false);
?>
<div id="button-bar">
    <div class="button"><a href="?p=menu">Back</a></div>
    <div class="button">
        <a href="javascript:document.getElementById('settingsForm').submit();">Save</a>
    </div>
</div>
<?php showMessage(); ?>
<form id="settingsForm" method="POST" action="action.php">
<input type="hidden" name="settingsForm">
<label>Notify email address</label>
<input type="text" name="notify" value="<?php echo $db->settings->notify; ?>">
<label>SMTP Host</label>
<input type="text" name="smtpHost" value="<?php echo $db->settings->smtpHost; ?>">
<label>SMTP Secure</label>
<input type="text" name="smtpSecure" value="<?php echo $db->settings->smtpSecure; ?>">
<label>SMTP Port</label>
<input type="text" name="smtpPort" value="<?php echo $db->settings->smtpPort; ?>">
<label>SMTP Username</label>
<input type="text" name="smtpUser" value="<?php echo $db->settings->smtpUser; ?>">
<label>SMTP Password</label>
<input type="password" name="smtpPass" value="" placeholder="hidden (enter new password to change)" autocomplete="new-password">
<label>SMTP From email address</label>
<input type="email" name="smtpFrom" value="<?php echo $db->settings->smtpFrom; ?>">
</form>
