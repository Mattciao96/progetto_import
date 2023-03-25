<?php

class InsertModel
{
  private $db;
  private $table;
  private $queryStart = '';
  private $queryEnd = ')';
  private $mappingvalues = array();
  function __construct($db, $table)
  {
    $this->db = $db;
    $this->table = $table;
  }

  public function insertColumn($mapping)
  {
    $query = "INSERT INTO {$this->table} (";
    foreach ($mapping as $key => $value) {
      array_push($this->mappingvalues, array_filter($value));
      $query .=  '`' . $this->db->escapeString($key) . '`, ';
    }
    $query = substr($query, 0, -2);
    $query .= ') VALUES (';
    $this->queryStart = $query;
  }

  

  public function insertValues($dataset)
  {
    echo "<p>Database: {$_SESSION['dstoedit']} </p>";

    // svuoto la tabella;
    $table = $_SESSION['dstoedit'];

    if ($table == 'dts' or $table == 'moderna' or $table == 'rilievi' or $table == 'source') {
      $truncateQuery = "TRUNCATE `dolomiti`.`{$this->db->escapeString($table)}`";
      $this->db->getInsertQuery($truncateQuery);
    }
    

    $counter = 0;
    foreach ($dataset as $row) {

      $query = '';
      foreach ($this->mappingvalues as $value) {

        if (count($value) == 1) {

          if (isset($row[$value[0]])) {
            $query .= '"' . $this->db->escapeString($row[$value[0]]) . '", ';
          } else {
            $query .= 'NULL, ';
          }
        } elseif (count($value) > 1) {
          $lastElement = end($value);

          // Vediamo se mettere null
          $existenceCount = 0;
          foreach ($value as $subValue) {
            if (isset($row[$subValue])) {
              $existenceCount++;
            }
          }

          if ($existenceCount !== 0) {
            foreach ($value as $subValue) {

              if ($subValue == $value[0]) {
                if (isset($row[$subValue])) {
                  $query .= '"' . $this->db->escapeString($row[$subValue]) . ' ';
                } else {
                  $query .= '"';
                }
              } elseif ($subValue == $lastElement) {
                if (isset($row[$subValue])) {
                  $query .= $this->db->escapeString($row[$subValue]) . '", ';
                } else {
                  $query .= '",';
                }
              } else {
                if (isset($row[$subValue])) {
                  $query .= $this->db->escapeString($row[$subValue]) . ' ';
                } else {
                  $query .= '';
                }
              }
            }
          } else {
            $query .= 'NULL, ';
          }
        }
      }
      $query = substr($query, 0, -2);
      $query .= ')';
      $finalquery = $this->queryStart . $query;
      
      //$this->db->getInsertQuery($finalquery);

      // LOG ERRORI
      if (mysqli_query($this->db->connect, $finalquery)) {
        //echo "<br><br>New record created successfully<br><br>";
    } else {
        // Log errors
        $error = "Error: " . $finalquery . "<br>" . mysqli_error($this->db->connect);
        file_put_contents('error.log', $error . PHP_EOL, FILE_APPEND);
        echo "<br><br>Error: " . $finalquery . "<br>" . mysqli_error($this->db->connect).'<br><br>';
    }
    



      $counter++;
    }
  }
}
