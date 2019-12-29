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
    require_once("functions.php");
    $config = require_once('config.php');

    $statsFile = $config['STATS_FILE'];

    // Load stats
    $stats = loadStats(false);

    // Load db
    $db = loadDb(false);
    $monitor = getMonitor($_GET['id']);
    if($monitor->type == 'page') $title = "$monitor->name ($monitor->url with \"$monitor->text\")";
    if($monitor->type == 'port') $title = "$monitor->name ($monitor->host:$monitor->port)";
    
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
            $rows .= "[$datetime,$stat[2]],";
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
                    title: '<?php echo $title; ?>',
                    width: 1000,
                    height: 500,
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
        <meta http-equiv="refresh" content="300">
    </head>
    <body>
        <div id="chart_div"></div>
    </body>
</html>
