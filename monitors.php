<div id="menu">
<div class="menu-item"><a href="?p=monitor&new">+ New</a></div>
<?php
loginRequired();
$db = loadDb(false);
foreach($db->monitors as $monitor) {
    if($monitor->user <> $_SESSION['id']) continue;
    $id = $monitor->id;
    $name = $monitor->name;
?>
<div class="menu-item">
    <a href="?p=monitor&id=<?php echo $id; ?>"><?php echo $name; ?></a>
</div>
<?php } ?>
<div class="menu-item bottom"><a href="?p=menu">Back</a></div>
</div>
