<?php

loginRequired();
$db = loadDb(false);

$monitorid = $_GET['id'] ?? null;
if($monitorid == "new") redirect("?p=monitors");
$monitor = getMonitor($monitorid);
$name = $monitor->name;
$type = $monitor->type;
$time = @$monitor->lastTime ?: "n/a";
if(!isset($monitor->lastResult)) $result = "n/a";
elseif($monitor->lastResult == -1) $result = "failure";
else $result = $monitor->lastResult." ms";

// Graph data
$stats = loadStats(false);
$rows = "[";
foreach($stats as $stat)
    if($stat[0] == $_GET['id']) {
        $year = date("Y", $stat[1]);
        $month = date("n", $stat[1])-1;
        $day = date("j", $stat[1]);
        $hours = date("G", $stat[1]);
        $minutes = date("i", $stat[1]);
        $seconds = date("s", $stat[1]);
        $datetime = "new Date($year,$month,$day,$hours,$minutes,$seconds)";
        $rows .= "[$datetime,$stat[2]],";
    }
$rows .= "]";
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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Time');
        data.addColumn('number', 'Response time (ms)');
        data.addRows(<?php echo $rows; ?>);
        var options = {
            backgroundColor: { fill:'transparent' },
            colors: ['SeaGreen'],
            legend: 'none',
            title: '',
            width: 330,
            height: 100,
            hAxis: { format: 'dd H:mm' },
            vAxis: { viewWindowMode: 'maximized' },
            trendlines: { 0: { color: 'red', lineWidth: 1 } }
        };
        var dtformat = new google.visualization.DateFormat({pattern: "yyyy-MM-dd H:mm"});
        dtformat.format(data, 0);
        var chart = new google.visualization.LineChart(
            document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>
<div id="chart_div" onclick="window.location.href='?p=graph&id=<?php echo $monitorid; ?>';"></div>
</div>
