<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
$database_name = 'dolomiti';
// script per dividere il campo quota in quota min e max

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);



// controllo che esistano le due colonne di destinazione quota min e quota max, se non ci sono, le creo
// controllo se nella tabella esistono le colonne
$query = "SHOW COLUMNS FROM location WHERE Field IN ('minimumElevationInMeters', 'maximumElevationInMeters')";
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
  $query = "ALTER TABLE location ADD COLUMN `minimumElevationInMeters` INT, ADD COLUMN `maximumElevationInMeters` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Le nuove colonne sono state create con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} elseif (!in_array('maximumElevationInMeters', $arrayResult)) {
    $query = "ALTER TABLE location ADD COLUMN `maximumElevationInMeters` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La colonna maximumElevationInMeters è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} elseif (!in_array('minimumElevationInMeters', $arrayResult)) {
    $query = "ALTER TABLE location ADD COLUMN `minimumElevationInMeters` INT ";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'La colonna minimumElevationInMeters è stata creata con successo<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
} else {
    echo 'Le colonne esistono già<br>';
}




// Seleziono valori univoci della colonna verbatimElevation in tabella Record
$query = "SELECT DISTINCT verbatimElevation FROM location WHERE verbatimElevation IS NOT NULL";
$result = $mysqli->query($query);

$arrayResult = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, $row['verbatimElevation']);
  }
}

// per ciascun valore univoco:
// se sono presenti solo valori numerici, aggiungi valore alla colonna min elevation nei record corrispondenti
// se è presente il simbolo >, aggiungi valore alla colonna min elevation
// se è presente il simbolo <, aggiungi valore alla colonna max elevation
// se è presente il simbolo -, aggiungi prima serie di numeri trovati alla colonna min, seconda serie di numeri trovati alla colonna max
foreach ($arrayResult as $string) {
  $string = trim($string);
  $stringa = $string;
  if (preg_match('/^[0-9]+$/', $string)) {
    $string = (int) $string;
    $query = "UPDATE location SET minimumElevationInMeters = '{$mysqli->real_escape_string($string)}', maximumElevationInMeters = '{$mysqli->real_escape_string($string)}' 
    WHERE verbatimElevation LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;
        $result = $mysqli->query($query);
      if ($result) {
        echo 'La quota è stata aggiunta con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
  } elseif (preg_match('/>/', $string)) {
    preg_match('/\d+/', $string, $matches);
    $number = $matches[0];
    $query = "UPDATE location SET minimumElevationInMeters = '$number', maximumElevationInMeters = '$number' 
    WHERE verbatimElevation LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;
        $result = $mysqli->query($query);
      if ($result) {
        echo 'La quota min è stata aggiunta con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
  } elseif (preg_match('/</', $string)) {
        preg_match('/\d+/', $string, $matches);
        $number = $matches[0];
        $query = "UPDATE location SET maximumElevationInMeters = '$number', minimumElevationInMeters = '$number' 
        WHERE verbatimElevation LIKE '{$mysqli->real_escape_string($stringa)}'";
          echo $query;
            $result = $mysqli->query($query);
          if ($result) {
            echo 'La quota max è stata aggiunta con successo<br>';
          } else {
            echo $mysqli->error . '<br>';
          }
  } elseif (preg_match('/-/', $string)) {
    preg_match_all('/\d+/', $string, $matches);
    $number = $matches[0];
    $query = "UPDATE location SET minimumElevationInMeters = '$number[0]', 
    maximumElevationInMeters = '$number[1]' 
    WHERE verbatimElevation LIKE '{$mysqli->real_escape_string($stringa)}'";
      echo $query;
        $result = $mysqli->query($query);
      if ($result) {
        echo 'La quota max e min sono state aggiunte con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
  } else {
    echo 'Condizione non presente per: '.'real_escape_string($stringa)'.'<br>';
  }
}



