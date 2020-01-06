<?php
loginRequired();
$db = loadDb(false);
$monitorid = $_GET['id'] ?? null;
$userid = $_SESSION['id'];
$monitor = getMonitor($monitorid);
if($monitor->user <> $userid) message("Not your monitor", true);
$name = $monitor->name;
$type = $monitor->type;
?>
<form method="POST" action="action.php">
<input type="hidden" name="id" value="<?php echo $monitorid; ?>">
<label>Name</label>
<input type="text" name="name" value="<?php echo $name; ?>" disabled>
<label>Type</label>
<input type="text" name="type" value="<?php echo $type; ?>" disabled>
<?php
if($type == 'page') {
    $url = $monitor->url;
    $text = $monitor->text;
    ?>
    <label>URL</label>
    <input type="text" name="url" value="<?php echo $url; ?>" disabled>
    <label>Contains</label>
    <input type="text" name="text" value="<?php echo $text; ?>" disabled>
    <?php
}
elseif($type == 'port') {
    $host = $monitor->host;
    $port = $monitor->port;
    ?>
    <label>Host</label>
    <input type="text" name="host" value="<?php echo $host; ?>" disabled>
    <label>Port</label>
    <input type="text" name="port" value="<?php echo $port; ?>" disabled>
    <?php
}
?>
<input type="submit" name="editMonitor" value="Edit">
<input type="submit" name="deleteMonitor" value="Delete"
    onClick="return confirm('Delete monitor?');">
</form>
<div id="menu">
<div class="menu-item"><a href="?p=monitors">Back</a></div>
</div>
