<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$ssp = session_save_path().'/pnpmonitor';
if(!is_dir($ssp)) mkdir($ssp);
ini_set('session.save_path', $ssp);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
session_start();

require_once("functions.php");
