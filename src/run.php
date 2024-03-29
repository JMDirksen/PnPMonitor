<?php

// Init
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
date_default_timezone_set("Europe/Amsterdam");
chdir(__DIR__);
ini_set("default_socket_timeout", 1);

// Load required files
require_once("functions.php");

// Checks
if (php_sapi_name() != "cli")
    die("This script should be run from command line.");
if (!extension_loaded("openssl"))
    die("Error: Extension openssl required.");

// Load database
list($db, $dbhandle) = loadDb();

echo "SendMailAt: Failures: " . $db->settings->sendMailAtXFails .
    " Successes: " .
    $db->settings->sendMailAtXSuccesses . "\n\n";

// Iterate monitors
foreach ($db->monitors as $key => $monitor) {
    // Setup monitor
    if (!isset($monitor->failing)) $monitor->failing = false;
    if (!isset($monitor->successCount)) $monitor->successCount = 0;
    if (!isset($monitor->failCount)) $monitor->failCount = 0;

    // Test monitor
    printf(
        "Monitor: %s (%s %s %s)\n",
        $monitor->name,
        $monitor->type,
        $monitor->url ?? $monitor->host,
        $monitor->text ?? $monitor->port ?? null,
    );
    $result = testMonitor($monitor);
    $result = min($result, 1001);
    if ($result == 1001) $result = -1;
    if ($result != -1) {
        if (!isset($monitor->avg)) $monitor->avg = $result;
        $avgSamples = 5;
        $avg = round(
            (($avgSamples - 1) * $monitor->avg + $result) / $avgSamples,
            2
        );
        $monitor->avg = $avg;
    }
    echo "Result: $result (avg: $avg)\n";
    $monitor->lastResult = $result;
    $monitor->lastTime = date("d-m-Y H:i:s (T)");

    // Process result
    if ($result == -1) {
        // Failure
        $monitor->successCount = 0;
        $monitor->failCount++;
        switch ($monitor->type) {
            case "page":
                $string = "Page %s failed to load correctly!\n";
                $msg = sprintf($string, $monitor->url);
                break;
            case "port":
                $string = "Port %s:%d isn't accepting connections!\n";
                $msg = sprintf($string, $monitor->host, $monitor->port);
                break;
            case "ping":
                $string = "Host %s isn't responding to ping!\n";
                $msg = sprintf($string, $monitor->host);
                break;
        }
        if (
            $monitor->failCount >= $db->settings->sendMailAtXFails
            && !$monitor->failing
        ) {
            $monitor->failing = true;
            $subject = "PnPMonitor failed - $monitor->name";
            echo "Sending mail\n";
            sendMail($db->settings->notify, $subject, $msg);
        }
    } else {
        // Success
        $monitor->successCount++;
        $monitor->failCount = 0;
        if (
            $monitor->successCount >= $db->settings->sendMailAtXSuccesses
            && $monitor->failing
        ) {
            $monitor->failing = false;
            $subject = "PnPMonitor restored - $monitor->name";
            $body = "Monitor $monitor->name has been restored.\n";
            echo "Sending mail\n";
            sendMail($db->settings->notify, $subject, $body);
        }
    }

    echo "Stats: " . $monitor->successCount .
        " " . $monitor->failCount .
        " " . ($monitor->failing ? "failing" : "ok") . "\n\n";

    // Update monitor to database
    $db->monitors[$key] = $monitor;

    // Stats
    $stat = [];
    $stat[] = $monitor->id;
    $stat[] = time();
    $stat[] = $result == -1 ? -1 : round($avg);
    if (!isset($stats)) $stats = [];
    $stats[] = $stat;
    sleep(1);
}

// Save database
saveDb();

// Save stats
if (isset($stats)) addStats($stats);
