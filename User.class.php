<?php

class User {
    private $db;
    public $id;
    public $email;
    public $token;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public static function existingUser($email) {
        $db = Database::getConnection();
        $result = $db->query("SELECT * FROM users WHERE email = '$email'");
        if($result->num_rows > 0) return true;
        else return false;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function save() {
        $this->db->query("INSERT INTO users (email, token) VALUES ('$this->email', '$this->token')");
        if($this->db->errno) {
            die($this->db->error);
        }
    }

    public function loadFromToken($token) {
        $result = $this->db->query("SELECT id FROM users WHERE token = '$token'");
        if($result->num_rows) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
        }
        $this->load($id);
    }

    public function loadFromEmail($email) {
        $result = $this->db->query("SELECT id FROM users WHERE email = '$email'");
        if($result->num_rows) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
        }
        $this->load($id);
    }

    public function load($userid) {
        $result = $this->db->query("SELECT * FROM users WHERE id = $userid");
        if($result->num_rows) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->token = $row['token'];
        }
    }

    public function generateToken($length = 32) {
        $token = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for($i = 0; $i < $length; $i++) {
            $rnd = rand(0,strlen($chars)-1);
            $char = $chars{$rnd};
            $token .= $char;
        }
        $this->token = $token;
    }
}
