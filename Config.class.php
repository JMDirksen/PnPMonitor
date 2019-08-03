<?php

class Config {

    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = include("config.php");
    }

    public static function getConfig() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance->config;
    }
}
