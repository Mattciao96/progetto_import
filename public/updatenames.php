<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);


// prendo tutti i nomi
$query = 'SELECT DISTINCT scientificName FROM `dolomiti`.`record`';
$result = $mysqli->query($query);
if ($result) {
  echo 'Nomi presi<br>';
} else {
  echo $mysqli->error . '<br>';
}
$arrayResult = [];
while ($row = $result->fetch_assoc()) {
  array_push($arrayResult, $row['scientificName']);
}

foreach ($arrayResult as $name) {
  // chiamo l'api
  if ($name != '') {
    $name = urlencode($name);
    $url = "https://italic.units.it/?procedure=matchapi&sp={$name}";
    $jsonData = file_get_contents($url);
    $responseData = json_decode($jsonData, true);
    //print_r($responseData);

    // se non è un match lo printo
    if ($responseData['match']['score'] != 100 and $responseData['match']['score'] != 0) {
      if ($responseData['match']['synonym_of'] == '') {
        echo "Input: {$responseData['input_name']} || Match: {$responseData['match']['original_name']} || Score: {$responseData['match']['score']}<br><br>";
      } else {
        echo "Input: {$responseData['input_name']} || Match: {$responseData['match']['synonym_of']} || Score: {$responseData['match']['score']} || Sinonimo di: {$responseData['match']['original_name']} <br><br>";
      }
    }
  }
}
