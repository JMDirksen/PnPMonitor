<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
session_start();

require_once("functions.php");
