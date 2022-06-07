<div id="button-bar">
    <div class="button"><a href="?p=menu">Back</a></div>
    <div class="button">
        <a href="javascript:document.getElementById('newUser').submit();">Save</a>
    </div>
</div>
<?php showMessage(); ?>
<div id="menu">
    <?php
    loginRequired();
    $db = loadDb(false);
    foreach ($db->users as $user) {
    ?>
        <div class="menu-item">
            <a href="action.php?delUser=<?php echo $user->id; ?>" onClick="return confirm('Delete user <?php echo $user->email; ?>?');">
                <?php echo $user->email; ?>
            </a>
        </div>
    <?php } ?>
</div>
<form id="newUser" method="POST" action="action.php">
    <input type="hidden" name="newUser">
    <label>New user</label>
    <input type="email" name="email" placeholder="email" required>
    <label>Password</label>
    <input type="password" name="password" placeholder="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}" minlength="5" required>
</form>
