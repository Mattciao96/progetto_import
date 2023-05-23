<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
// decommenta se funzione viene messa nel file functions.php
//include('functions.php');

//stampo errori più specifici
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


// $shapefile: nome tabella shapefile del database
// $col: colonna da compilare in tabella location
// $den: colonna della tabella shapefile con il nome del poligono

function addlocation($mysqli, $shapefile, $col, $den)
{

  echo '<br>' . $shapefile . '_____________________________________________________________________________________________________<br><br>';

  // ho già la colonna stateProvince
  // 0: creo una colonna stateProvince se non esiste in location per metterci la regione
  foreach ($col as $cols) {
    $query = "SHOW COLUMNS FROM dolomiti.herbaria_in_dolomites LIKE '{$cols}'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
      echo 'Colonne lette<br>';
    } else {
      echo $mysqli->error . '<br>';
    }

    if ($result->num_rows == 0) {
      $query = "ALTER TABLE dolomiti.herbaria_in_dolomites ADD COLUMN `{$cols}` VARCHAR(255) NULL DEFAULT NULL";
      $result = $mysqli->query($query);
      if ($result) {
        echo 'La nuova colonna è stata creata con successo<br>';
      } else {
        echo $mysqli->error . '<br>';
      }
    } else {
      echo 'La colonna \'' . $cols . '\' esiste già<br>';
    }
  }

  // 1: per prima cosa, prendo le coordinate di ogni location
  $query = 'SELECT italic.HB_dati.table_id, italic.HB_dati.`long`, italic.HB_dati.`lat` FROM italic.HB_dati INNER JOIN dolomiti.herbaria_in_dolomites ON dolomiti.herbaria_in_dolomites.occurrence_id = italic.HB_dati.table_id';
  $result = $mysqli->query($query);

  if ($result->num_rows > 0) {
  } else {
    echo 'Select errore: ' . $mysqli->error . '<br>';
  }

  $arrayResult = [];
  if ($result->num_rows > 0
  ) {
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
    foreach ($den as $index => $name) {

    $query = "SELECT $name
      FROM $shapefile
      WHERE ST_CONTAINS(
      SHAPE,
      ST_GEOMFROMTEXT(
        CONCAT(
          'POINT(',
          '{$row['long']}',
          ' ',
          '{$row['lat']}',
          ')'
        ),
        1
    )
  )";

    $result = $mysqli->query($query);
    //echo $select_query . '<br>';
    
  
  
      // !!! discommenta per testare
     /* $arrayResults = $select_result->fetch_assoc();
    echo '<pre>';
    print_r($arrayResults);
    echo '<pre>';
    echo '<pre>'; */
    

    
      if ($result->num_rows > 0) {
        $region = $result->fetch_assoc();

        if (isset($region[$name])) {
          // in tutti questi casi il punto ricade fuori da qualsiasi mio shapefile
          $region = $region[$name];
          //echo $region . '<br>';
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

      $query = "UPDATE dolomiti.herbaria_in_dolomites SET $col[$index] = '{$mysqli->real_escape_string($region)}' WHERE occurrence_id = '{$row['table_id']}'";
      $result = $mysqli->query($query);
      //echo $query. '<br>';

      if ($result) {
      } else {
        echo 'Update errore: ' . $mysqli->error . '<br>';
      }
    }
  }
}

////////////////////////////////////////////////////////////////////////////////////////////

addlocation($mysqli, 'dolomiti.regioni_4326', ['stateProvince'], ['den_reg']);
addlocation($mysqli, 'dolomiti.province_4326', ['county'], ['den_uts']);
addlocation($mysqli, 'dolomiti.comuni_4326', ['municipality'], ['comune']);
addlocation($mysqli, 'dolomiti.aree_protette_4326', ['protect_area'], ['nome_gazze']);
addlocation($mysqli, 'dolomiti.bacini_idro_princ_4326', ['main_drainage_basin'], ['nome_bac']);
addlocation($mysqli, 'dolomiti.bacini_idro_sec_4326', ['secondary_drainage_basin'], ['nome_bac']);
addlocation($mysqli, 'dolomiti.soiusa_4326', ['soiusa_major_sector','soiusa_section','soiusa_subsection','soiusa_supergroup','soiusa_group'], 
['grande_set', 'sezione_sz', 'sottosezio', 'supergrupp', 'gruppo_gr']);
addlocation($mysqli, 'dolomiti.natura_2000_4326', ['cod_natura_2000', 'natura_2000_name', 'sic_zsc', 'zps'], ['codice', 'denominazi', 'sic_zsc', 'zps']);

