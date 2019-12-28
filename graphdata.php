<?php

// Init
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
session_start();

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

// Generate graph data
$graphdata = (object) null;

$graphdata->cols = [];
$col1 = (object) null;
$col1->id = "";
$col1->label = "Topping";
$col1->pattern = "";
$col1->type = "string";
$col2 = (object) null;
$col2->id = "";
$col2->label = "Slices";
$col2->pattern = "";
$col2->type = "number";
$graphdata->cols[] = $col1;
$graphdata->cols[] = $col2;

$graphdata->rows = [];
$value1 = (object) null;
$value1->v = "Mushrooms";
$value1->f = null;
$value2 = (object) null;
$value2->v = 3;
$value2->f = null;
$row = (object) null;
$row->c = [];
$row->c[] = $value1;
$row->c[] = $value2;
$graphdata->rows[] = $row;

echo json_encode($graphdata);
