<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');
$database_name = 'dolomiti';

$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);

function makeApiRequest($url, $options)
{
  $context = stream_context_create($options);
  $response = file_get_contents($url, false, $context);

  if ($response === false) {
    die('Error: Unable to fetch API data.');
  }

  $httpCode = intval($http_response_header[0]);

  if ($httpCode !== 200 and $httpCode !== 0) {
    handleHttpError($httpCode);
  }

  return $response;
}

function handleHttpError($httpCode)
{
  if ($httpCode === 401) {
    die('Error: Unauthorized. Check your API credentials.');
  } elseif ($httpCode === 404) {
    die('Error: API endpoint not found.');
  } else {
    die("Error: An unexpected error occurred.{$httpCode}");
  }
}

function parseApiResponse($response)
{
  $responseData = json_decode($response, true);

  if ($responseData === null) {
    die('Error: Unable to parse API response.');
  }

  return $responseData;
}

function processApiData($responseData)
{
  $name = $responseData['name'];
  $age = $responseData['age'];

  // Process and utilize the data as needed
  echo "Name: $name, Age: $age";
}



function getInaturalistData($url)
{
  $options = [
    'http' => [
      'method' => 'GET',
    ]
  ];
  $response = makeApiRequest($url, $options);
  $responseData = parseApiResponse($response);
  return $responseData;
}

function getInatualistDataFull()
{

  $page = 1;
  $results_per_page = 100;
  $all_results_fetched = false;

  $all_data = [];
  while (!$all_results_fetched) {
    $url = "https://www.inaturalist.org/observations/project/dolichens-the-lichen-biota-of-the-dolomites.json?page={$page}&per_page={$results_per_page}";
    echo $url;
    $data = getInaturalistData($url);

    if (empty($data)) {
      $all_results_fetched = true;
    } else {
      $page = $page + 1;
      $all_data = array_merge($all_data, $data);
      sleep(60); // to respect api request limts
    }
  }

  return $all_data;
}




function fillInaturalistDatabase($mysqli, $data)
{


  $query = 'TRUNCATE `dolomiti`.`inaturalist_data`';
  $result = $mysqli->query($query);

  foreach ($data as $row) {
    insertInaturalistData($mysqli, $row);
  }
}

function insertInaturalistData($mysqli, $row)
{
  $row['positional_accuracy'] = $row['positional_accuracy'] == null ? 'NULL' : $row['positional_accuracy'];
  $row['latitude'] = $row['latitude'] == null ? 'NULL' : $row['latitude'];
  $row['longitude'] = $row['longitude'] == null ? 'NULL' : $row['longitude'];

  $query = "INSERT INTO `inaturalist_data`(`id`, `name`, `observed_on`, `latitude`, `longitude`, `positional_accuracy`, `positioning_method`, `place_guess`, `user_login`, `thumb_url`, `large_url`) 
  VALUES 
  ({$mysqli->real_escape_string($row['id'])},
  '{$mysqli->real_escape_string($row['taxon']['name'])}',
  '{$mysqli->real_escape_string($row['observed_on'])}',
  {$mysqli->real_escape_string($row['latitude'])},
  {$mysqli->real_escape_string($row['longitude'])},
  {$mysqli->real_escape_string($row['positional_accuracy'])},
  '{$mysqli->real_escape_string($row['positioning_method'])}',
  '{$mysqli->real_escape_string($row['place_guess'])}',
  '{$mysqli->real_escape_string($row['user_login'])}',
  '{$mysqli->real_escape_string($row['photos'][0]['thumb_url'])}',
  '{$mysqli->real_escape_string($row['photos'][0]['large_url'])}')";

  echo $query;
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Inserito<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
}



function getNameMatchData($url, $data = null)
{
  $options = [
    'http' => [
      'method' => 'POST',
      'header' => implode("\r\n", [
        'Content-Type: application/json'
      ]),
      'content' => $data !== null ? json_encode($data) : null,
    ]
  ];
  $response = makeApiRequest($url, $options);
  $responseData = parseApiResponse($response);
  return $responseData;
}


function getValidAcceptedNames($responseData)
{
  if ($responseData[0]['match']['score'] != 0 and $responseData[0]['match']['name_score'] > 91) {
    if ($responseData[0]['match']['synonym_of'] == '') {
      echo "Input: {$responseData[0]['input_name']} || Match: {$responseData[0]['match']['original_name']} || Score: {$responseData[0]['match']['name_score']}<br><br>";
      $name_to_insert = $responseData[0]['match']['original_name'];
    } else {
      echo "Input: {$responseData[0]['input_name']} || Match: {$responseData[0]['match']['synonym_of']} || Score: {$responseData[0]['match']['name_score']} || Sinonimo di: {$responseData[0]['match']['original_name']} <br><br>";
      $name_to_insert = $responseData[0]['match']['synonym_of'];
    }
  } else {
    echo "NON INSERITO Input: {$responseData[0]['input_name']} || Match: {$responseData[0]['match']['original_name']} || Score: {$responseData[0]['match']['name_score']}<br><br>";
    $name_to_insert = '';
  }
  return $name_to_insert;
}

function updateNames($mysqli)
{
  // prendo i nomi
  $query = 'SELECT DISTINCT id, name FROM `dolomiti`.`inaturalist_data`';
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Nomi presi<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
  $arrayResult = [];

  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, ['id' => $row['id'], 'name' => $row['name']]);
  }


  foreach ($arrayResult as $row) {
    // chiamo l'api
    if ($row['name'] != '') {

      $data = ['sp' => $row['name']];
      $responseData = getNameMatchData("https://italic.units.it/api/match", $data);
      $name_to_insert = getValidAcceptedNames($responseData);

      // butto dentro
      if ($name_to_insert !== '') {
        $query = "UPDATE `inaturalist_data` SET `accepted_name`= '{$mysqli->real_escape_string($name_to_insert)}' WHERE id = {$row['id']}";
        $result = $mysqli->query($query);
      }
    }
  }
}

function updateYear($mysqli)
{
  // prendo i nomi
  $query = 'SELECT DISTINCT id, observed_on FROM `dolomiti`.`inaturalist_data`';
  $result = $mysqli->query($query);
  if ($result) {
    echo 'Date prese<br>';
  } else {
    echo $mysqli->error . '<br>';
  }
  $arrayResult = [];

  while ($row = $result->fetch_assoc()) {
    array_push($arrayResult, ['id' => $row['id'], 'observed_on' => $row['observed_on']]);
  }
  // Separate year from the date


  foreach ($arrayResult as $row) {
    $year = substr($row['observed_on'], 0, 4);
    if (is_numeric($year)) {
      $query = "UPDATE `inaturalist_data` SET `year`= {$mysqli->real_escape_string($year)} WHERE id = {$row['id']}";
      $result = $mysqli->query($query);
    }
  }
}


$data = getInatualistDataFull();
fillInaturalistDatabase($mysqli, $data);
updateNames($mysqli);
updateYear($mysqli);
