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
<label>Allow registration of new users</label>
<input type="checkbox" name="allowRegister" <?php if($db->settings->allowRegister) echo "checked" ?>>
<label>SMTP Host</label>
<input type="text" name="smtpHost" value="<?php echo $db->settings->smtpHost; ?>">
<label>SMTP Secure</label>
<input type="text" name="smtpSecure" value="<?php echo $db->settings->smtpSecure; ?>">
<label>SMTP Port</label>
<input type="text" name="smtpPort" value="<?php echo $db->settings->smtpPort; ?>">
<label>SMTP User</label>
<input type="text" name="smtpUser" value="<?php echo $db->settings->smtpUser; ?>">
<label>SMTP Pass</label>
<input type="password" name="smtpPass" value="<?php echo $db->settings->smtpPass; ?>">
<label>SMTP From</label>
<input type="email" name="smtpFrom" value="<?php echo $db->settings->smtpFrom; ?>">
</form>
