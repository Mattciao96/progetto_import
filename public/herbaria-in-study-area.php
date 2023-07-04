<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
// decommenta se funzione viene messa nel file functions.php
//include('functions.php');

//stampo errori più specifici
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


$query = "CREATE TABLE IF NOT EXISTS dolomiti.herbaria_in_dolomites (
  id INT AUTO_INCREMENT,
  occurrence_id INT,
  in_study_area BOOLEAN,
  PRIMARY KEY (id))";

$result = $mysqli->query($query);
if ($result) {
  echo 'Tabella tutto ok<br>';
} else {
  echo $mysqli->error . '<br>';
}

$query = "TRUNCATE dolomiti.herbaria_in_dolomites";
$result = $mysqli->query($query);
if ($result) {
  echo 'Tabella svuotata<br>';
} else {
  echo $mysqli->error . '<br>';
}

function isInStudyArea($mysqli)
{

  // 1: per prima cosa, prendo le coordinate di ogni location
  $query = "SELECT italic.HB_dati.table_id, italic.HB_dati.`long`, italic.HB_dati.lat FROM italic.HB_dati WHERE italic.HB_dati.`long` IS NOT NULL AND italic.HB_dati.lat IS NOT NULL";
  $result = $mysqli->query($query);

  if ($result->num_rows > 0) {
  } else {
    echo 'Select errore: ' . $mysqli->error . '<br>';
  }

  $arrayResult = [];
  if (
    $result->num_rows > 0
  ) {
    while ($row = $result->fetch_assoc()) {
      array_push($arrayResult, $row);
    }
  }

  // 2: con un ciclo for
  // 2.1: per ogni coordinata vedo la regione in cui ricade
  foreach ($arrayResult as $key => $row) {

    $query = "SELECT id
    FROM dolomiti.area_buffer
              WHERE
              ST_CONTAINS(SHAPE, ST_GEOMFROMTEXT(
                            CONCAT(
                              'POINT(',
                                '{$row['long']}',
                                ' ',
                                '{$row['lat']}',
                                    ')'
                                  ),
                                1
                          ))";


    /*   echo $query;
    exit;     */
    $result = $mysqli->query($query);
    if ($result->num_rows != 0) {
      $query = "INSERT INTO dolomiti.herbaria_in_dolomites (occurrence_id, in_study_area) VALUES ({$row['table_id']}, 1)";
      $result = $mysqli->query($query);
      if ($result) {
      } else {
        echo $mysqli->error . '<br>';
      }
    }
  }
}

isInStudyArea($mysqli);
