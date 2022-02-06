<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$ssp = session_save_path().'/pnpmonitor';
if(!is_dir($ssp)) mkdir($ssp);
ini_set('session.save_path', $ssp);
$lifetime = 60*60*24*90;
ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.cookie_lifetime', $lifetime);
session_start();
setcookie(session_name(), session_id(), time()+$lifetime);

require_once("functions.php");
