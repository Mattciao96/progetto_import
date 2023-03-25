<?php

function array_flatten($array, $return)
{
  for ($x = 0; $x <= count($array); $x++) {
    if (is_array($array[$x])) {
      $return = array_flatten($array[$x], $return);
    } else {
      if (isset($array[$x])) {
        $return[] = $array[$x];
      }
    }
  }
  return $return;
}
/**
 * Nel momento in cui 2 valori delle colonne devono essere uniti in uno (guarda json connection.json) gestisce il tutto
 * 
 */
function mergeStringValues($rowContent, $columnsToMerge, $separator)
{
  if ($rowContent[$columnsToMerge[0]] != '' and $rowContent[$columnsToMerge[1]] != '') {
    $stringToSearch = $rowContent[$columnsToMerge[0]] . $separator . $rowContent[$columnsToMerge[1]];
  } elseif ($rowContent[$columnsToMerge[0]] != '' and $rowContent[$columnsToMerge[1]] == '') {
    $stringToSearch = $rowContent[$columnsToMerge[0]];
  } elseif ($rowContent[$columnsToMerge[0]] == '' and $rowContent[$columnsToMerge[1]] != '') {
    $stringToSearch = $rowContent[$columnsToMerge[1]];
  } else {
    $stringToSearch = '';
  }

  return $stringToSearch;
}


// Nell'esempio base ho
// $rowContent
// $column['rilievi]
// $separator
function prepareString($rowContent, $column, $columnName, $separator)
{
  if (is_array($column[$columnName])) {

    $stringToSearch = mergeStringValues($rowContent, $column[$columnName] , $separator);
  } else {
    if ($column[$columnName] == '') {
      $stringToSearch = '';
    } else {
      $stringToSearch = $rowContent[$column[$columnName]];
    }
  }
  return $stringToSearch;
}

function addToSelectString($mysqli, $stringToSearch, $column, $columnName)
{

  if ($stringToSearch == '' or $column[$columnName] == '') {
    $query = "{$mysqli->real_escape_string($column['name'])} IS NULL";
  } else {
    $query = "{$mysqli->real_escape_string($column['name'])} LIKE '{$mysqli->real_escape_string($stringToSearch)}'";
  }

  return $query;
}


function addToInsertString($mysqli, $stringToSearch, $column, $columnName)
{
  if ($stringToSearch == '' or $column[$columnName] == '') {

    $queryInto = "{$mysqli->real_escape_string($column['name'])}";
    $queryValues = 'NULL';
  } else {
    $queryInto = "{$mysqli->real_escape_string($column['name'])}";
    $queryValues = "'{$mysqli->real_escape_string($stringToSearch)}'";
  }
  return [$queryInto, $queryValues];
}
