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

// crea la tabella per i dati
try {
  $query = "DROP TABLE dolomiti.HB_dati_dol";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Tabella tutto ok<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} catch (Error $e) {
  echo 'no problem ora creo la tablella';
}
$query = "CREATE TABLE dolomiti.HB_dati_dol AS
SELECT italic.HB_dati.*
FROM italic.HB_dati
JOIN dolomiti.herbaria_in_dolomites ON dolomiti.herbaria_in_dolomites.occurrence_id = italic.HB_dati.table_id";
$result = $mysqli->query($query);
if ($result) {
  echo 'Tabella tutto ok<br>';
} else {
  echo $mysqli->error . '<br>';
}

$query = "ALTER TABLE dolomiti.herbaria_in_dolomites ADD COLUMN data_type VARCHAR(255) DEFAULT 'Herbaria'";
$result = $mysqli->query($query);
if ($result) {
  echo 'data_type created<br>';
} else {
  echo $mysqli->error . '<br>';
}

// add indexes herbaria_in_dolomites
$query = "ALTER TABLE dolomiti.`herbaria_in_dolomites` ADD INDEX(`occurrence_id`)";
$result = $mysqli->query($query);
if ($result) {
  echo 'occurrence_id indexed<br>';
} else {
  echo $mysqli->error . '<br>';
}

// add indexes HB_dati_dol
$query = "ALTER TABLE dolomiti.`HB_dati_dol` ADD INDEX(`table_id`)";
$result = $mysqli->query($query);
if ($result) {
  echo 'table_id indexed<br>';
} else {
  echo $mysqli->error . '<br>';
}
$query = "ALTER TABLE dolomiti.`HB_dati_dol` ADD INDEX(`herbarium_id`)";
$result = $mysqli->query($query);
if ($result) {
  echo 'table_id indexed<br>';
} else {
  echo $mysqli->error . '<br>';
}
$query = "ALTER TABLE dolomiti.`HB_dati_dol` ADD INDEX(`nome_accettato`)";
$result = $mysqli->query($query);
if ($result) {
  echo 'nome_accettato indexed<br>';
} else {
  echo $mysqli->error . '<br>';
}