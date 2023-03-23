<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');




// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


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

# partiamo dalla tabella rilievi
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
$query = "SELECT * FROM rilievi";
$result = $mysqli->query($query);

$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row);
  }
}
echo '<pre>';
print_r($arrayResult[0]);
echo '<pre>';