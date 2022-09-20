<div id="button-bar">
  <div class="button"><a href="?p=menu">Back</a></div>
  <div class="button"><a href="?p=edit&new"><b>+</b></a></div>
</div>
<div id="menu">
  <?php
  loginRequired();
  $db = loadDb(false);
  foreach ($db->monitors as $monitor) {
    $id = $monitor->id;
    $name = $monitor->name;
    $response = $monitor->lastResult;
    $fail = $response == -1 ? "fail" : "";
  ?>
    <div class="menu-item">
      <a href="?p=monitor&id=<?php echo $id; ?>"><?php echo $name; ?><span class="status <?php echo $fail; ?>"><?php echo $response; ?></span></a>
    </div>
  <?php } ?>
</div>
