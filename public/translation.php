<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "4096M");

require_once('../config/init.php');

// creo connessione al database
$mysqli = new mysqli($database_adress, $database_login, $database_password, $database_name);

/* prove query separate per ogni colonna

// partiamo dalla colonna substratum
$query = "UPDATE record JOIN translation SET record.substratum = translation.eng";
echo $query. '<br>';
$result = $mysqli->query($query);
if ($result) {
 echo 'Il valore è stato tradotto con successo<br>';
 } else {
 echo $mysqli->error . '<br>';
}
*/


// mostriamo se qualche valore non ha una corrispondenza nella tabella delle traduzioni

/* Ciclo in cui, per ogni colonna da tradurre della tabella record, viene selezionato il valore che, 
in seguito al join con la tabella translation, non ha corrispondenza (NULL nella colonna substratum o eng).
E' importante fare in join comparando entrambe le colonne di translation, per evitare di ottenere come risultato 
anche i valori già tradotti in inglese e quindi corrispondenti alla colonna eng
*/

$columns = ['specific_substratum', 'substratum'];

foreach ($columns as $column) {
    echo $column . '__________________________________________________________________________________________________________<br><br>';

    $query = "SELECT DISTINCT record.{$column} FROM record LEFT JOIN translation 
    ON (record.{$column} LIKE translation.substratum OR record.{$column} LIKE translation.eng) 
    WHERE translation.substratum IS NULL";
    $result = $mysqli->query($query);
    if ($result) {
        if ($result->num_rows > 0) {
            echo 'Traduzioni non presenti per:<br>'; 
            while ($row = $result->fetch_assoc()) {
                echo $row[$column] . '<br>';
            }
        } else {
            echo 'Tutti i valori hanno una traduzione' . '<br>';
        }
    } else {
        echo 'Select errore: ' . $mysqli->error . '<br>';
    }
}




// Traduciamo i valori nella tabella record

/* Aggiorniamo i valori che hanno una corrispondenza nella colonna substratum della tabella translation 
con i valori della colonna eng e lasciamo invariati gli altri. 
*/

foreach ($columns as $column) {
    echo $column . '__________________________________________________________________________________________________________<br><br>';

    $query = "UPDATE record LEFT JOIN translation 
    ON record.{$column} LIKE translation.substratum SET 
    record.{$column} = (CASE WHEN record.{$column} = translation.substratum THEN translation.eng ELSE record.{$column} END)";
    $result = $mysqli->query($query);
    if ($result) {
        echo 'Il valore è stato tradotto con successo<br><br>';
    } else {
        echo $mysqli->error . '<br>';
    }
}