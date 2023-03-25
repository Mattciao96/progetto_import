<?php

/**
 * @author Matteo Conti
 * @version 1.0
 * @created 20-02-2022
 * 
 * !!! NOTA BENE, I DATABASE GLI SCEGLI IN home/homeView.php
 * !!! MA EDITA ANCHE I DATABASE ACCETTATI SOTTO IN procedure=upload
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

// if a big json is sent through post, these values need to be changed in php.ini
// max upload filesize (upload_max_filesize)
// max post data size (post_max_size)
// memory limit (memory_limit)

session_start();

$now = time();
if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
  // this session has worn out its welcome; kill it and start a brand new one
  session_unset();
  session_destroy();
  session_start();
}

// either new or old, it should live at most for another 10 minutes
$_SESSION['discard_after'] = $now + 3600;

require_once('../config/init.php');
require('../src/classes/general/database.php');
require('../src/classes/general/view.php');





$database_name = 'dolomiti';
try {
  $db = new Database($database_name, $database_password, $database_login, $database_adress);
} catch (Exception $e) {
  echo $e;
}
$view = new View();




if (isset($_REQUEST['procedure']) and $_REQUEST['procedure'] == 'upload') {
  if (isset($_REQUEST['dstoedit']) and ($_REQUEST['dstoedit'] == 'dts' 
  or $_REQUEST['dstoedit'] == 'rilievi'
  or $_REQUEST['dstoedit'] == 'moderna'
  or $_REQUEST['dstoedit'] == 'source'
)) {
    $_SESSION['dstoedit'] = $_REQUEST['dstoedit']; //butto in session

    require('../src/classes/upload/uploadView.php');
    $upload_view = new UploadView();
    $upload_view->getUploadPage();
  }
} elseif (isset($_REQUEST['procedure']) and $_REQUEST['procedure'] == 'conceptmapping') {

  require('../src/classes/conceptMapping/mappingController.php');
  require('../src/classes/conceptMapping/mappingModel.php');
  require('../src/classes/conceptMapping/mappingView.php');
  $mapping_model = new MappingModel($db, $_SESSION['dstoedit']);
  $mapping_controller = new MappingController();
  $mapping_view = new MappingView();

  $database_names = $mapping_model->getTableNames();
  $mapping_controller->setTableNames($database_names);

  $mapping_controller->setHeadAndDatasetFromJson($_REQUEST['dataset']);
  $dataset_names = $mapping_controller->getHeader();
  $mapping_controller->setDatasetExamples();
  $mapping_controller->setOrderedExamples();
  $mappingJson = $mapping_controller->passValuesToJson();
  $mapping_view->getMappingContent($database_names, $dataset_names);
  $mapping_view->setJavascriptVariable($mappingJson);

  $mapping_view->getMappingPage();
} elseif (isset($_REQUEST['procedure']) and $_REQUEST['procedure'] == 'conceptmapped') {
  require('../src/classes/insert/insertModel.php');
  $insert_model = new InsertModel($db, $_SESSION['dstoedit']);
  function jsonToArray($json)
  {
    $array = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $array;
    } else {
      trigger_error("ERROR! Invalid json provided to jsonToArray");
    }
  }
  $mappingAndDataset = jsonToArray($_REQUEST['dataset']);
  $insert_model->insertColumn($mappingAndDataset['map']);
  $insert_model->insertValues($mappingAndDataset['dataset']);
  //print_r($a);

} else {
  require('../src/classes/home/homeView.php');
  $home_view = new HomeView();
  $home_view->getHomePage();
}
exit;
