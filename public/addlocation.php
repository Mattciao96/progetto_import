<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
// decommenta se funzione viene messa nel file functions.php
//include('functions.php');

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


// $shapefile: nome tabella shapefile del database
// $col: colonna da compilare in tabella location
// $den: colonna della tabella shapefile con il nome del poligono

function addlocation ($mysqli, $shapefile, $col, $den)
{

  echo '<br>'.$shapefile . '_____________________________________________________________________________________________________<br><br>';

  // ho già la colonna stateProvince
  // 0: creo una colonna stateProvince se non esiste in location per metterci la regione
  $query = "SHOW COLUMNS FROM location LIKE '{$col}'";
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Colonne lette<br>';
  } else {
    echo $mysqli->error . '<br>';
  }

  if ($result->num_rows == 0) {
    $query = "ALTER TABLE location ADD COLUMN `{$col}` VARCHAR(255) NULL DEFAULT NULL";
    $result = $mysqli->query($query);
    if ($result) {
      echo 'La nuova colonna è stata creata con successo<br>';
    } else {
      echo $mysqli->error . '<br>';
    }
  } else {
    echo 'La colonna \'' . $col . '\' esiste già<br>';
  }


  // 1: per prima cosa, prendo le coordinate di ogni location
  $query = 'SELECT id_location, decimalLongitude, decimalLatitude FROM location';
  $result = $mysqli->query($query);

  if ($result) {
  } else {
    echo 'Select errore: ' . $mysqli->error . '<br>';
  }

  $arrayResult = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      array_push($arrayResult, $row);
    }
  }
  /* echo '<pre>';
print_r($arrayResult);
echo '</pre>'; */



  // 2: con un ciclo for
  // 2.1: per ogni coordinata vedo la regione in cui ricade
  foreach ($arrayResult as $key => $row) {
    $query = "SELECT $den
  FROM $shapefile
  WHERE ST_CONTAINS(
      SHAPE,
      ST_GEOMFROMTEXT(
        CONCAT(
          'POINT(',
          '{$row['decimalLongitude']}',
          ' ',
          '{$row['decimalLatitude']}',
          ')'
        ),
        1
    )
  )";

  



    $result = $mysqli->query($query);

    if ($result) {
      $region = $result->fetch_assoc();

      if (isset($region[$den])) {
        // in tutti questi casi il punto ricade fuori da qualsiasi mio shapefile
        $region = $region[$den];
      } else {
        $region = '';
      }
    } else {
      // i casi in cui non ho coordinate
      // !!! è normale vedere l'errore nel log
      echo 'Select errore: ' . $mysqli->error . '<br>';
      $region = '';
    }


    /* echo '<pre>';
  print_r($region);
  echo '</pre>'; */

    // 2.2: inserisco la regione in cui ricade in una nuova colonna della tabella location

    $query = "UPDATE location SET $col = '{$mysqli->real_escape_string($region)}' WHERE id_location = '{$row['id_location']}'";
    $result = $mysqli->query($query);

    if ($result) {
    } else {
      echo 'Select errore: ' . $mysqli->error . '<br>';
    }
  }
}

////////////////////////////////////////////////////////////////////////////////////////////

addlocation($mysqli, 'regioni_4326', 'stateProvince', 'den_reg');
addlocation($mysqli, 'province_4326', 'county', 'den_uts');
addlocation($mysqli, 'comuni_4326', 'municipality', 'comune');
addlocation($mysqli, 'aree_protette_4326', 'protect_area', 'nome_gazze');



