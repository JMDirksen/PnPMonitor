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
<label>Allow register</label>
<input type="checkbox" name="allowRegister" <?php if($db->settings->allowRegister) echo "checked" ?>>
</form>
