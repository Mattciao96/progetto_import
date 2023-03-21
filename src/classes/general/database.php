<?php

/**
 * @author Stefano Martellos
 * @author Matteo Conti
 * @version 3.0
 * @created 24-Jul-2009
 * @last-modified 01-Mar-2022
 */

class Database
{

	public $connect;
	private $db_selected;


	public function __construct($database, $password, $user, $url="localhost")  // localhost in locale
	{
		//echo $database. $password. $user. $url;
		$this->setConnectDatabase($password, $user, $url);
		$this->setSelectDatabase($database);
	}


	private function setConnectDatabase($password, $user, $url)  // connette a mysql
	{
		$this->connect = new mysqli($url, $user, $password)
		or die("can't connect to the database".' '. $url.' '. $password.' '. $user);
		//mysqli_query("SET NAMES 'UTF8'");
	}


	private function setSelectDatabase($database)  // seleziona un database
	{
		$this->db_selected =$this->connect->select_db($database)
		or die("can't select {$database}.");
	}

  private function getError(){
    trigger_error("ERROR! Error description: " . $this->connect->error, E_USER_WARNING);
  }

  
	public function escapeString($string)
	{
		$result = $this->connect->real_escape_string($string);
		return $result;
	}

	public function setSingleQuery($query)
	{
		//$this->debugQuery($query);

		$result = $this->connect->query($query);
		return $result;
	}


  /**
   * Let you set multiple queries at the same time separated with ;
   * @result a nested array with the result from the queries
   */
	public function setMultipleQuery($query)
	{

		$result = mysqli_multi_query($this->connect, $query);
    if (!$result) {
	    $this->getError();
    }
    else{
		  return $result;
    }
	}


	public function getSingleQuery($query)
	{
		$this->debugQuery($query);

		$result = $this->connect->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$this->debugResults($row);

		return true;
	}




	public function getSingleSelectQuery($query)
	{
		$this->debugQuery($query);

		$result = $this->connect->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		//$this->debugResults($row);

		return $row;
	}


	public function getMultipleSelectQuery($query)
	{
        $return = array();
		//$this->debugQuery($query);

		$result = $this->connect->query($query);
		while($row = $result->fetch_array(MYSQLI_ASSOC))
			$return[] = $row;

		//$this->debugResults($return);
		

	    return $return;
	}


	public function getInsertQuery($query)
	{
		$result = $this->connect->query($query);
    if ($result) {
	    
      return $result;
    }
    else{
		  $this->getError();
    }
	}


	private function debugQuery($query)
	{
//		echo "<hr>".$query."<hr>";
	}


	private function debugResults($query)
	{
//		echo"<pre>";		print_r($query);		echo"</pre>";
	}


	public function findIfTableExist($table)
	{
		//INSERISCI QUESTO TIPO DI QUERY: 'select 1 from `table` LIMIT 1'
		$query='select 1 from' .$table. 'LIMIT 1';
		$ifTable=$this->connect->query($query);
		return$ifTable;
		//SE IFTABLE == FALSE LA table NON ESISTE
	}


	public function getListSelectQuery($query)
	{
		$this->debugQuery($query);

		$result = $this->connect->query($query);

		for($i=0; $array[$i] = $result->fetch_array(); $i++);
		array_pop($array);

		return $array;
	}
}


