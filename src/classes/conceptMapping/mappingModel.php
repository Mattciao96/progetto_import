<?php

class MappingModel{
  private $db;
  private $table;
  function __construct($db, $table)
  {
    $this->db = $db;
    $this->table = $table;
  }

  public function getTableNames(){

    $query = "SHOW COLUMNS FROM {$this->db->escapeString($this->table)}";
    $result = array_column($this->db->getMultipleSelectQuery($query), 'Field');

    return $result;
  }
}
