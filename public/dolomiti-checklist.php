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


$query = "CREATE TABLE IF NOT EXISTS dolomiti.dolomites_checklist (
  id INT AUTO_INCREMENT,
  id_taxa INT(8),
  PRIMARY KEY (id))";

$result = $mysqli->query($query);
if ($result) {
  echo 'Tabella tutto ok<br>';
} else {
  echo $mysqli->error . '<br>';
}

$query = "TRUNCATE dolomiti.dolomites_checklist";
$result = $mysqli->query($query);
if ($result) {
  echo 'Tabella svuotata<br>';
} else {
  echo $mysqli->error . '<br>';
}

function getNames($mysqli)
{
  $query = "SELECT DISTINCT scientificName FROM dolomiti.record WHERE scientificName IS NOT NULL AND scientificName != ''";
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
      array_push($arrayResult, $row['scientificName']);
    }
  }
  return $arrayResult;
}

function getHerbariaNames($mysqli)
{
  $query = "SELECT DISTINCT italic.HB_dati.nome_accettato FROM italic.HB_dati
  INNER JOIN dolomiti.herbaria_in_dolomites on italic.HB_dati.table_id = dolomiti.herbaria_in_dolomites.occurrence_id 
  WHERE italic.HB_dati.nome_accettato IS NOT NULL AND italic.HB_dati.nome_accettato != ''";
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
      array_push($arrayResult, $row['nome_accettato']);
    }
  }
  return $arrayResult;
}

function populateChecklistTable($mysqli)
{
  $herbaria_names = getHerbariaNames($mysqli);
  $names = getNames($mysqli);
  $unique_names = array_unique(array_merge($names, $herbaria_names));
  foreach ($unique_names as $name) {
    $query = "SELECT NUM from italic.distribution  WHERE SP LIKE '{$name}' LIMIT 1";
    $result = $mysqli->query($query);
    if (
      $result->num_rows > 0
    ) {
      $result = $result->fetch_assoc();
      $query = "INSERT INTO dolomiti.dolomites_checklist (id_taxa) VALUES({$result['NUM']})";
      $result = $mysqli->query($query);
      if ($result) {
      } else {
        echo $mysqli->error . '<br>';
      }
    }
  }
}
populateChecklistTable($mysqli);
