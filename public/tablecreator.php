<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
include('functions.php');






$database_name = 'dolomiti';
// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


// prima di eseguire lo script svuoto tutte le tabelle
$query = 'TRUNCATE `dolomiti`.`location`';
$result = $mysqli->query($query);
$query = 'TRUNCATE `dolomiti`.`record`';
$result = $mysqli->query($query);


# partiamo dalla tabella rilievi
// 0 parte bonus, converti le date se queste sono un numero grezzo di excel
$query = "SELECT DISTINCT Data FROM rilievi WHERE Data IS NOT NULL";
$result = $mysqli->query($query);

$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row['Data']);
  }
}

foreach ($arrayResult as $string) {
  $stringa = $string;
  if (preg_match('/^[0-9]+$/', $string)) {
    $string = (int) $string;
    if ($string > 2023) {
      $string = date('d/m/Y', strtotime('1900-01-01 +' . ($string - 2) . ' days'));

      $query = "UPDATE rilievi SET Data = '{$mysqli->real_escape_string($string)}' WHERE Data LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;

      $result = $mysqli->query($query);
      if ($result) {
        echo 'La data è stata modificata con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
    }
  }
}


// 1: controlla se esiste la colonna rilievi, altrimenti creala e inserisci numeri autoincrementali
echo 'RILIEVI______________________________________________________________________________________<br><br>';




$query = "SHOW COLUMNS FROM rilievi LIKE 'ID'";
$result = $mysqli->query($query);
if ($result) {
  echo 'Colonne lette<br>';
} else {
  echo $mysqli->error . '<br>';
}

if ($result->num_rows == 0) {
  $query = "ALTER TABLE rilievi ADD COLUMN `ID` INT AUTO_INCREMENT PRIMARY KEY FIRST";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La nuova colonna è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} else {
  echo 'La colonna ID esiste già<br>';
}

// 2: seleziono le colonne che devo portare in location

$jsonData = file_get_contents('connection.json');
$jsonData = json_decode($jsonData, true);

/* echo '<pre>';
print_r($jsonData);
echo '<pre>'; */

$columnsData = $jsonData['location'];
//print_r($columnsData);





$tables = ['rilievi','moderna', 'dts'];

foreach ($tables as $table) {
  echo $table . '__________________________________________________________________________________________________________<br><br>';

  $query = "SELECT * FROM {$table}";
  $result = $mysqli->query($query);
  $arrayResult = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      array_push($arrayResult, $row);
    }
  }

  $arrayResults = $arrayResult;

    // !!! discommenta per testare
    /* $arrayResults = [$arrayResult[0]];
  echo '<pre>';
  print_r($arrayResults);
  echo '<pre>';
  echo '<pre>';
  print_r($columnsData);
  echo '<pre>'; */




  foreach ($arrayResults as $arrayResult) {


    $columnsData = $jsonData['location'];
    $query = 'SELECT id_location FROM location WHERE ';

    foreach ($columnsData as $column) {

      $stringToSearch = prepareString($arrayResult, $column, $table, isset($column['separator']) ? $column['separator'] : '');
      $query .= addToSelectString($mysqli, $stringToSearch, $column, $table);
      $query .= ' AND ';
    }
    $query = substr($query, 0, -4);
    //echo $query;

    $result = $mysqli->query($query);
    if ($result) {
    } else {
      echo 'Select errore: ' . $mysqli->error . '<br>';
    }

    /* **************************************************************************************************************************************** */


    if ($result->num_rows == 0) {
      $query = 'INSERT INTO location ';
      $queryInto = '(';
      $queryValues = ' VALUES (';
      //echo '<br>';


      foreach ($columnsData as $column) {

        // Questo serve a gestire il caso in cui devo unire 2 valori in un' unica nuova colonna
        $stringToSearch = prepareString($arrayResult, $column, $table, isset($column['separator']) ? $column['separator'] : '');

        $queryParts = addToInsertString($mysqli, $stringToSearch, $column, $table);
        $queryInto .= $queryParts[0];
        $queryValues .= $queryParts[1];

        $queryInto .= ', ';
        $queryValues .= ', ';
      }


      $queryInto = substr($queryInto, 0, -2);
      $queryInto .= ')';
      $queryValues = substr($queryValues, 0, -2);
      $queryValues .= ')';

      $query = $query . $queryInto . $queryValues;
      //echo $query; // !!! guarda qua per vedere la query insert
      $result = $mysqli->query($query);
      if ($result) {
        $locationId = (mysqli_insert_id($mysqli));
      } else {
        echo 'Insert errore: ' . $mysqli->error . '<br>';
      }
    } else {
      $locationId = $result->fetch_assoc()['id_location'];
    }

    //echo $locationId.'<br>';

    /**************************************************************************************************************************************************************/
    // !!! prendi source_id
    /**************************************************************************************************************************************************************/
    $columnsData = $jsonData['source'][2];

    $query = "SELECT id_source from source WHERE short_source LIKE '{$mysqli->real_escape_string($arrayResult[$columnsData[$table]])}'";

    $result = $mysqli->query($query);
    if ($result) {
      $sourceIdProvvisorio = $result->fetch_assoc();
      if (isset($sourceIdProvvisorio['id_source'])) {
        $sourceId = $sourceIdProvvisorio['id_source'];
      } else {
        echo 'In source non esiste ' . $arrayResult[$columnsData[$table]] . '<br><br>';
        $sourceId = '';
      }
    } else {
      echo 'Insert errore: ' . $mysqli->error . '<br>';
    }

    /**************************************************************************************************************************************************************/
    // !!! inserisci in record
    /**************************************************************************************************************************************************************/

    // PROVIAMO A INSERIRE
    $columnsData = $jsonData['record'];

    $query = 'INSERT INTO record ';
    $queryInto = '(';
    $queryValues = ' VALUES (';
    foreach ($columnsData as $column) {

      // Questo serve a gestire il caso in cui devo unire 2 valori in un' unica nuova colonna
      $stringToSearch = prepareString($arrayResult, $column, $table, isset($column['separator']) ? $column['separator'] : '');

      $queryParts = addToInsertString($mysqli, $stringToSearch, $column, $table);
      $queryInto .= $queryParts[0];
      $queryValues .= $queryParts[1];

      $queryInto .= ', ';
      $queryValues .= ', ';
    }


    $queryInto = substr($queryInto, 0, -2);
    $queryInto .= ", {$mysqli->real_escape_string('id_location')}";
    $queryInto .= ", {$mysqli->real_escape_string('table_original')}";
    $queryInto .= ", {$mysqli->real_escape_string('id_source')})";

    $queryValues = substr($queryValues, 0, -2);
    $queryValues .= ", {$mysqli->real_escape_string($locationId)}";
    $queryValues .= ", '{$mysqli->real_escape_string($table)}'";
    if ($sourceId == '') {
      $queryValues .= ", NULL)";
    } else {
      $queryValues .= ", {$mysqli->real_escape_string($sourceId)})";
    }

    $query = $query . $queryInto . $queryValues;
    //echo $query; // !!! guarda qua per vedere la query insert
    $result = $mysqli->query($query);
    if ($result) {
    } else {
      echo $query;
      echo 'Insert errore: ' . $mysqli->error . '<br>';
    }
  }
}
