<?php

class Database implements JsonSerializable {
  public $monitors = [];
  
  function __construct() {
    $this->monitors = json_decode(file_get_contents('db.json'))->monitors;
  }
  
  function __destruct() {
    file_put_contents('db.json', json_encode($this));
  }
  
  public function jsonSerialize() {  
    return [
      'monitors' => $this->monitors
    ];
  } 

}
