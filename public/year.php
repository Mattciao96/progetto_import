<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
$database_name = 'dolomiti';
// script per dividere il campo year in start_year e end_year

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);



// controllo che esistano le due colonne di destinazione start_year e end_year, se non ci sono, le creo
// controllo se nella tabella esistono le colonne
$query = "SHOW COLUMNS FROM record WHERE Field IN ('start_year', 'end_year')";
$result = $mysqli->query($query);
if ($result) {
  echo 'Colonne lette<br>';
} else {
  echo $mysqli->error . '<br>';
}

// metto il risultato della query in array
$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row['Field']);
  }
}
// visualizziamo l'array
//var_dump($arrayResult);

// se nella query precedente non ho avuto risultati, creo le colonne
// se non ne trovo una, la creo
// se le ho trovate entrambe, niente
if ($result->num_rows == 0) {
  $query = "ALTER TABLE record ADD COLUMN `start_year` INT, ADD COLUMN `end_year` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Le nuove colonne sono state create con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} elseif (!in_array('end_year', $arrayResult)) {
    $query = "ALTER TABLE record ADD COLUMN `end_year` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La colonna end_year è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} elseif (!in_array('start_year', $arrayResult)) {
    $query = "ALTER TABLE record ADD COLUMN `start_year` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La colonna start_year è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} else {
    echo 'Le colonne esistono già<br>';
}




// Seleziono valori univoci della colonna year in tabella Record
$query = "SELECT DISTINCT year FROM record WHERE year IS NOT NULL";
$result = $mysqli->query($query);

$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row['year']);
  }
}

// per ciascun valore univoco:
// se sono presenti solo valori numerici, aggiungi valore alle colonne start e end year nei record corrispondenti
// se è presente il simbolo /, aggiungi prima serie di numeri trovati alla colonna start, seconda serie di numeri trovati alla colonna end
foreach ($arrayResult as $string) {
  $string = trim($string);
  $stringa = $string;
  if (preg_match('/^[0-9]+$/', $string)) {
    $string = (int) $string;
    $query = "UPDATE record SET start_year = '{$mysqli->real_escape_string($string)}', end_year = '{$mysqli->real_escape_string($string)}' 
    WHERE year LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;
        $result = $mysqli->query($query);
      if ($result) {
        echo 'L\'anno è stato aggiunto con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
  } elseif (preg_match('/\//', $string)) {
    preg_match_all('/\d+/', $string, $matches);
    $number = $matches[0];
    $query = "UPDATE record SET start_year = '$number[0]', 
    end_year = '$number[1]' 
    WHERE year LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;
        $result = $mysqli->query($query);
      if ($result) {
        echo 'L\'anno di inizio e di fine sono stati aggiunti con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
  } else {
    echo 'Condizione non presente per: '. $stringa . '<br>';
  }
}



