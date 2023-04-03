<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);

