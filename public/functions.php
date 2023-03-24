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
function prepareString($rowContent, $columnName, $separator)
{
  if (is_array($columnName)) {

    $stringToSearch = mergeStringValues($rowContent, $columnName, $separator);
  } else {
    if ($columnName == '') {
      $stringToSearch = '';
    } else {
      $stringToSearch = $rowContent[$columnName];
      
    }
  }
  return $stringToSearch;
}
