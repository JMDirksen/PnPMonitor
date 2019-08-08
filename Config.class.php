<?php

class Config {

    private static $instance = null;
    private $config = false;

    private function __construct() {
        $this->config = @include "config.php";
        if($this->config === false) die("Error loading config.php");
    }

    public static function getConfig() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance->config;
    }
}
