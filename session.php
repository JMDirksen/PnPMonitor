<?php

class Session
{
  private $session_store = "../sessions.json";
  private $session_expire = 60 * 60 * 24 * 90;
  private $session_name = "session";
  private $contents = [];

  public function __construct()
  {
    if(headers_sent()) throw new Exception('Headers already sent.');
    $this->session_store = realpath($this->session_store);
    $this->session_expire += time();
    $this->getSession();
  }

  public function __destruct()
  {
    $this->saveSession();
  }

  public function get($key)
  {
    return $this->contents[$key];
  }

  public function set($key, $value)
  {
    $this->contents[$key] = $value;
  }

  public function getAll()
  {
    return $this->contents;
  }

  private function getSession()
  {
    $sessionId = $this->getSessionId();
    $store = @file_get_contents($this->session_store);
    $sessions = json_decode($store, true);
    if (!$sessions) $sessions = [];
    foreach ($sessions as $id => $s) {
      if ($id == $sessionId) $this->contents = $s['contents'];
    }
  }

  public function saveSession()
  {
    $sessionId = $this->getSessionId();

    // Lock n load sessions
    $handle = fopen($this->session_store, "c+");
    if (!$handle) die("Unable to open session store");
    if (!flock($handle, LOCK_EX)) die("Unable to lock session store");
    $contents = "";
    if (filesize($this->session_store)) {
      $contents = fread($handle, filesize($this->session_store));
      if ($contents === false) die("Unable to read session store");
    }
    $sessions = json_decode($contents, true);
    if (!$sessions) $sessions = [];

    // Update $sessions
    $sessions[$sessionId]['expire'] = $this->session_expire;
    $sessions[$sessionId]['contents'] = $this->contents;

    // Save sessions
    if (!ftruncate($handle, 0)) die("Unable to truncate db");
    if (!rewind($handle)) die("Unable to rewind db");
    if (fwrite($handle, json_encode($sessions)) === false)
      die("Unable to write db");
  }

  private function getSessionId()
  {
    if (
      isset($_COOKIE[$this->session_name])
      && $this->isValidSessionId($_COOKIE[$this->session_name])
    ) {
      setcookie(
        $this->session_name,
        $_COOKIE[$this->session_name],
        $this->session_expire
      );
      return $_COOKIE[$this->session_name];
    } else {
      $newSessionId = $this->generateSessionId();
      setcookie($this->session_name, $newSessionId, $this->session_expire);
      return $newSessionId;
    }
  }

  private function isValidSessionId($test)
  {
    if (strlen($test) == 25) return true;
    else return false;
  }

  private function generateSessionId($length = 25)
  {
    $token = "";
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" .
      "abcdefghijklmnopqrstuvwxyz" .
      "0123456789";
    for ($i = 0; $i < $length; $i++) {
      $rnd = rand(0, strlen($chars) - 1);
      $char = $chars[$rnd];
      $token .= $char;
    }
    return $token;
  }
}
