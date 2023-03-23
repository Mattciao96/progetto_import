<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
include('functions.php');



// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


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




$query = "SHOW COLUMNS FROM rilievi LIKE 'id_rilievi'";
$result = $mysqli->query($query);
if ($result) {
  echo 'Colonne lette<br>';
} else {
  echo $mysqli->error . '<br>';
}

if ($result->num_rows == 0) {
  $query = "ALTER TABLE rilievi ADD COLUMN id_rilievi INT AUTO_INCREMENT PRIMARY KEY FIRST";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La nuova colonna è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} else {
  echo 'La colonna id_rilievi esiste già<br>';
}

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



$query = "SELECT * FROM rilievi";
$result = $mysqli->query($query);
$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row);
  }
}

/* $columnsInRilievi = array_column($columnsData, 'rilievi');
//$columnsTest = array_merge($columnsInRilievi);
$columnsInRilievi = array_flatten($columnsInRilievi, array());
 */

$arrayResult = $arrayResult[1847];
$query = 'SELECT id_location FROM location WHERE ';

/* foreach ($columnsInRilievi as $column) {
  
} */

// parte di test
// abbiamo arrayresult la prima riga di rilievi $arrayResult 
// 
//$columnsData = $columnsData[0];
echo '<pre>';
print_r($columnsData);
echo '<pre>';
echo '<pre>';
print_r($arrayResult);
echo '<pre>';

// caso in cui non ci sono poblemi
//

foreach ($columnsData as $column) {


  // Questo serve a gestire il caso in cui devo unire 2 valori in un' unica nuova colonna
  if (is_array($column['rilievi'])) {

    $stringToSearch = mergeStringValues($arrayResult, $column['rilievi'], '; ');


    if ($stringToSearch == '') {
      $query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
    } {
      $query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
    }
  } else {

    if (
      $column['rilievi'] == '' or
      $arrayResult[$column['rilievi']] == ''
    ) {
      $query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
    } else {
      $query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($arrayResult[$column['rilievi']])}'";
    }
  }

  $query.= ' AND ';
}
$query = substr($query , 0, -4);
echo $query;

$result = $mysqli->query($query);



/* **************************************************************************************************************************************** */
/* 
$query = 'INSERT INTO location ';
$queryInto = '(';
$queryValues = '(';  
if ($result->num_rows == 0) {


  foreach ($columnsData as $column) {


    // Questo serve a gestire il caso in cui devo unire 2 valori in un' unica nuova colonna
    if (is_array($column['rilievi'])) {
  
      $stringToSearch = mergeStringValues($arrayResult, $column['rilievi'], '; ');
  
  
      if ($stringToSearch == '') {
        $query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
        $queryInto = '(';
        $queryValues = '('; 
      } {
        $query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
      }
    } else {
  
      if (
        $column['rilievi'] == '' or
        $arrayResult[$column['rilievi']] == ''
      ) {
        $query .= "{$mysqli->real_escape_string($column['name'])} IS NULL";
      } else {
        $query .= "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($arrayResult[$column['rilievi']])}'";
      }
    }
  
    $query.= ' AND ';
  }
  $query = substr($query , 0, -4);
  echo $query;
}

 */





/* $query = "SELECT * FROM rilievi";
$result = $mysqli->query($query);

$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row);
  }
}
echo '<pre>';
print_r($arrayResult[0]);
echo '<pre>'; */