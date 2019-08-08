<?php

class User {
    private $db;

    public function __construct($id) {
        $this->db = Database::getConnection();
    }

    public static function existingUser($email) {
        $db = Database::getConnection();
        $result = $db->query("select * from users where email = '$email'");
        if($result->num_rows > 0) return true;
        else return false;
    }
}