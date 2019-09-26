<?php

class MonitorList {
    private $db;
    private $list = [];

    function __construct() {
        $this->db = Database::getConnection();

        $result = $this->db->query("SELECT id FROM monitor");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->list[] = new Monitor($row['id']);
            }
            $result->close();
        }
    }

    function getMonitors() {
        return $this->list;
    }

}