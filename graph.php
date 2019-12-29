<?php
    // Init
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
    session_start();

    // Checks
    if(!isset($_SESSION['id'])) die('Not logged in.');
    if(!isset($_GET['id'])) die('Missing monitor id.');

    // Load required files
    $config = require_once('config.php');

    $statsFile = $config['STATS_FILE'];

    // Load stats
    $handle = fopen($statsFile, "c+");
    if(!$handle) die("Unable to open stats");
    if(!flock($handle, LOCK_EX)) die("Unable to lock stats");
    $contents = "";
    if(filesize($statsFile)) {
        $contents = fread($handle, filesize($statsFile));
        if($contents === false) die("Unable to read stats");
    }
    $stats = json_decode($contents);
    if(!$stats) $stats = [];

    // Generate rows data
    $rows = [];
    foreach($stats as $stat) {
        $rows[] = Array($stat[1], $stat[2]);
    }
    $rows = json_encode($rows);
?>

<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('number', 'Topping');
                data.addColumn('number', 'Slices');
                data.addRows(<?php echo $rows; ?>);
                var chart = new google.visualization.LineChart(
                    document.getElementById('chart_div'));
                chart.draw(data, {width: 400, height: 240});
            }
        </script>
    </head>
    <body>
        <div id="chart_div"></div>
    </body>
</html>
