<?php

    // Page and Port Monitor

    // Examples:
    //var_export(port_response_time('example.com', 80));
    //var_export(page_load_time('http://example.com'));
    //var_export(page_load_time('http://example.com', 'this domain'));

    function port_response_time($host, $port) {
        $time1 = microtime(true);
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        $time2 = microtime(true);
        if(is_resource($connection)) {
            fclose($connection);
            return (int)round(($time2 - $time1)*1000);
        }
        else return false;
    }

    function page_load_time($url, $must_contain = '') {
        $time1 = microtime(true);
        $page = @file_get_contents($url);
        $time2 = microtime(true);
        if(strlen($page) and strlen($must_contain) and stristr($page, $must_contain)===false) {
            $page = false;
        }
        if($page) return (int)round(($time2 - $time1)*1000);
        else return false;
    }
