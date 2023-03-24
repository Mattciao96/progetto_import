<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
include('functions.php');



// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


# partiamo dalla tabella dts
// 0 parte bonus, converti le date se queste sono un numero grezzo di excel
$query = "SELECT DISTINCT Data FROM dts WHERE Data IS NOT NULL";
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

      $query = "UPDATE dts SET Data = '{$mysqli->real_escape_string($string)}' WHERE Data LIKE '{$mysqli->real_escape_string($stringa)}'";
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


// 1: controlla se esiste la colonna dts, altrimenti creala e inserisci numeri autoincrementali
echo 'dts______________________________________________________________________________________<br><br>';



// 2: seleziono le colonne che devo portare in location

/* $query = "SELECT 
WGS84_E as decimalLongitude,
WGS84_N as decimalLatitude,
Località
Habitat originale as habitat,
Incertezza m
Quota_dem as
,  FROM table_name"; */



$jsonData = file_get_contents('connection.json');
$jsonData = json_decode($jsonData, true);

/* echo '<pre>';
print_r($jsonData);
echo '<pre>'; */

$columnsData = $jsonData['location'];
//print_r($columnsData);



$query = "SELECT * FROM dts";
$result = $mysqli->query($query);
$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row);
  }
}

/* $columnsIndts = array_column($columnsData, 'dts');
//$columnsTest = array_merge($columnsIndts);
$columnsIndts = array_flatten($columnsIndts, array());
 */

//$arrayResult = $arrayResult[1247];
//$query = 'SELECT id_location FROM location WHERE ';

/* foreach ($columnsIndts as $column) {
  
} */

// parte di test
// abbiamo arrayresult la prima riga di dts $arrayResult 
// 
//$columnsData = $columnsData[0];
/* echo '<pre>';
print_r($columnsData);
echo '<pre>';
echo '<pre>';
print_r($arrayResult);
echo '<pre>';
 */
// caso in cui non ci sono poblemi
//



$arrayResults = $arrayResult;

// !!! discommenta per testare
/* $arrayResults = [$arrayResult[0]];
echo '<pre>';
print_r($arrayResults);
echo '<pre>'; */

foreach ($arrayResults as $arrayResult) {




  $columnsData = $jsonData['location'];
  $query = 'SELECT id_location FROM location WHERE ';
  foreach ($columnsData as $column) {


    // Questo serve a gestire il caso in cui devo unire 2 valori in un' unica nuova colonna
    /* if (is_array($column['dts'])) {

      $stringToSearch = mergeStringValues($arrayResult, $column['dts'], '; ');
    } else {
      if ($column['dts'] == '') {
        $stringToSearch = '';
      } else {
        $stringToSearch = $arrayResult[$column['dts']];
        //echo $arrayResult[$column['dts']].'<br>';
      }
    } */

    $stringToSearch = prepareString($arrayResult, $column['dts'], isset($column['separator']) ? $column['separator'] : '');



    if ($stringToSearch == '' or $column['dts'] == '') {
      $query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
    } else {
      $query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
    }

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
      $stringToSearch = prepareString($arrayResult, $column['dts'], isset($column['separator']) ? $column['separator'] : '');



      if ($stringToSearch == '' or $column['dts'] == '') {

        //$query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
        $queryInto .= "{$mysqli->real_escape_string($column['name'])}";
        $queryValues .= 'NULL';
      } else {
        //$query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
        $queryInto .= "{$mysqli->real_escape_string($column['name'])}";
        $queryValues .= "'{$mysqli->real_escape_string($stringToSearch)}'";
      }

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

  $query = "SELECT id_source from source WHERE short_source LIKE '{$mysqli->real_escape_string($arrayResult[$columnsData['dts']])}'";
  
  $result = $mysqli->query($query);
  if ($result) {
    $sourceIdProvvisorio = $result->fetch_assoc();
    if (isset($sourceIdProvvisorio['id_source'])) {
      $sourceId = $sourceIdProvvisorio['id_source'];
    } else {
      echo 'In source non esiste ' .$arrayResult[$columnsData['dts']].'<br><br>';
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
    $stringToSearch = prepareString($arrayResult, $column['dts'], isset($column['separator']) ? $column['separator'] : '');



    if ($stringToSearch == '' or $column['dts'] == '') {

      //$query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
      $queryInto .= "{$mysqli->real_escape_string($column['name'])}";
      $queryValues .= 'NULL';
    } else {
      //$query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
      $queryInto .= "{$mysqli->real_escape_string($column['name'])}";
      $queryValues .= "'{$mysqli->real_escape_string($stringToSearch)}'";
    }

    $queryInto .= ', ';
    $queryValues .= ', ';
  }


  $queryInto = substr($queryInto, 0, -2);
  $queryInto .= ", {$mysqli->real_escape_string('id_location')}";
  $queryInto .= ", {$mysqli->real_escape_string('id_source')})";
  $queryValues = substr($queryValues, 0, -2);
  $queryValues .= ", {$mysqli->real_escape_string($locationId)}";

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
    echo 'Insert errore: ' . $mysqli->error . '<br>';
  }
}