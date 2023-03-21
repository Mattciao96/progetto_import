<?php

class MappingController {

  private $header_id = 'header';
  private $dataset_id = 'dataset';

  private $dataset;
  private $header;
  private $database_header;
  private $dataset_example;
  private $number_of_examples = 3;


  public function setHeader($header){
    $this->header = $header;
    $this->sortHeader(); // remove if you don't want sorting
  }
  public function setDataset($dataset){
    $this->dataset = $dataset;
  }
  public function getHeader(){
    return $this->header;
  }
  public function getDataset(){
    return $this->dataset;
  }

  public function sortHeader(){
    natcasesort($this->header);
  }

  public function jsonToArray($json){
    $array = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $array;
    } else {
        trigger_error("ERROR! Invalid json provided to jsonToArray");
    }
  }

  public function setTableNames($database_column_names){
    $this->database_header = $database_column_names;
  }


  public function setHeadAndDatasetFromJson($json){
    $array = $this->jsonToArray($json);
    $this->setHeader($array[$this->header_id]);
    $this->setDataset($array[$this->dataset_id]);
  }

  public function setOnlyHeaderFromJson($json){
    $array = $this->jsonToArray($json);
    $this->setHeader($array[$this->header_id]);
  }

  public function setDatasetExamples(){
    $this->dataset_example = array_slice($this->dataset, 0, $this->number_of_examples);
    //print_r($this->dataset_example);
  }
  
  /**
   * createExamplesJson
   * create an array for each header name with n examples
   * the number of examples is given by the private variable $number_of_examples
   *
   * @return void
   */
  public function createExamplesJson(){
    $examples_array_concept_order = array();
    foreach ($this->header as $header_name){
      $examples_array_concept_order[$header_name] = array();
      foreach ($this->dataset_example as $row_example){

        if (isset($row_example[$header_name])){
          array_push($examples_array_concept_order[$header_name], $row_example[$header_name]);
          
        } else {
          array_push($examples_array_concept_order[$header_name], '');
        }
      }
    }
    //print_r($examples_array_concept_order);
    return($examples_array_concept_order);
  }

  public function setOrderedExamples(){
    $this->dataset_example = $this->createExamplesJson();
  }

  // pass even the dataset as index = 3
  public function passValuesToJson(){
    $array = array($this->database_header, $this->header, $this->dataset_example, $this->dataset);
    return json_encode($array);
  }


}
