<?php
loginRequired();
if (isset($_GET['new'])) {
  $monitorid = "new";
  $name = "";
  $type = "page";
  $field1 = "";
  $field2 = "";
} else {
  $monitorid = $_GET['id'] ?? null;
  $monitor = getMonitor($monitorid);
  $name = $monitor->name;
  $type = $monitor->type;
  $field1 = $monitor->url ?? $monitor->host;
  $field2 = $monitor->text ?? $monitor->port;
}
?>
<div id="button-bar">
  <div class="button">
    <a href="?p=monitor&id=<?php echo $monitorid; ?>">Cancel</a>
  </div>
  <div class="button">
    <a href="javascript:document.getElementById('editForm').submit();">Save</a>
  </div>
</div>
<?php showMessage(); ?>
<form id="editForm" method="POST" action="action.php">
  <input type="hidden" name="saveMonitor">
  <input type="hidden" name="id" value="<?php echo $monitorid; ?>">
  <label>Name</label>
  <input type="text" name="name" value="<?php echo $name; ?>" placeholder="monitor name" required>
  <label>Type</label>
  <select id="type" name="type">
    <option value="page" <?php if ($type == "page") echo " selected"; ?>>
      Page
    </option>
    <option value="port" <?php if ($type == "port") echo " selected"; ?>>
      Port
    </option>
  </select>
  <label id="lbl1">URL/Host</label>
  <input id="input1" type="text" name="field1" value="<?php echo $field1; ?>" required>
  <label id="lbl2">Text/Port</label>
  <input id="input2" type="text" name="field2" value="<?php echo $field2; ?>">
</form>
<script>
  function setVisibility() {
    var typeSelector = document.getElementById('type');
    var type = typeSelector.options[typeSelector.selectedIndex].value;
    if (type == 'page') {
      document.getElementById('lbl1')
        .innerHTML = 'URL';
      document.getElementById('input1')
        .setAttribute("placeholder", "http(s)://");
      document.getElementById('lbl2')
        .innerHTML = 'Contains text';
      document.getElementById('input2')
        .setAttribute("placeholder", "the text the page should contain");
      document.getElementById('input2')
        .removeAttribute("required");
    } else if (type == 'port') {
      document.getElementById('lbl1')
        .innerHTML = 'Host';
      document.getElementById('input1')
        .setAttribute("placeholder", "localhost");
      document.getElementById('lbl2')
        .innerHTML = 'Port';
      document.getElementById('input2')
        .setAttribute("placeholder", "80");
      document.getElementById('input2')
        .setAttribute("required", "");
    }
  }
  document.getElementById('type').onchange = function() {
    setVisibility()
  };
  setVisibility();
</script>