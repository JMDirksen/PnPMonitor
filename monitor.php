<?php
loginRequired();
$db = loadDb(false);
$monitorid = $_GET['id'] ?? null;
if($monitorid == "new") redirect("?p=monitors");
$userid = $_SESSION['id'];
$monitor = getMonitor($monitorid);
if($monitor->user <> $userid) message("Not your monitor", true);
$name = $monitor->name;
$type = $monitor->type;
$time = @$monitor->lastTime ?: "n/a";
if(!isset($monitor->lastResult)) $result = "n/a";
elseif($monitor->lastResult == -1) $result = "failure";
else $result = $monitor->lastResult." ms";
?>
<div id="button-bar">
    <div>
        <div class="button"><a href="?p=monitors">Back</a></div>
    </div>
    <div>
        <div class="button"><a href="action.php?delete=<?php echo $monitorid; ?>" onClick="return confirm('Delete monitor?');">Delete</a></div>
        <div class="button"><a href="?p=edit&id=<?php echo $monitorid; ?>">Edit</a></div>
    </div>
</div>
<form>
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
    <label>Contains text</label>
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
</form>
<div id="lastresult">
<div>Last result</div>
<div class="row"><div class="label">Time:</div><span><?php echo $time; ?></span></div>
<div class="row"><div class="label">Response:</div><span><?php echo $result; ?></span></div>
</div>
<div id="graph">
<div>Graph</div>
</div>
