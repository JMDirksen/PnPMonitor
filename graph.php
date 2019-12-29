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
            $result = $stat[2] ?: 0;
            $rows .= "[$datetime,$result],";
        }
    $rows .= "]";
?>

<html>
    <head>
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
                    width: 500,
                    height: 250,
                    hAxis: { format: 'dd H:mm' }
                };
                var dtformat = new google.visualization.DateFormat({pattern: "yyyy-MM-dd H:mm"});
                dtformat.format(data, 0);
                var chart = new google.visualization.LineChart(
                    document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        </script>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div id="chart_div"></div>
    </body>
</html>
