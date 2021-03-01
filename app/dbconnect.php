<?php
class dbconnect {	
	//Mysql
	protected $db;	
	private $host     = "localhost";
	private $username = "root";
	private $password = "";
	private $database = "rebardnd";

	//MSSQL
	protected $sql_db;	
	private $sql_host     = "SATHISH\SQLEXPRESS_2019";
	private $sql_database = "SmartHrRdd";	
	public function open_db(){
		$this->db = new mysqli($this->host, $this->username, $this->password, $this->database);
		if(mysqli_connect_errno()){
			return false;
		}else{
			return true;
		}
	}
	
	public function runQuery($query) {
		$result = mysqli_query($this->db,$query);
		if(!$result){
			echo("Error description: ".mysqli_error($this->db)."<br/>");
			return false;
		}else{
			return $result;
		}		
	}
	
	public function result($result){
		$data = array();
		while ($obj = mysqli_fetch_object($result)) {
			if($obj){				
				$data[] = $obj;
			}
		}
		return $data;
	}
	
	public function runQuery_insert_id($query) {
		$result    = mysqli_query($this->db,$query);
		$insert_id = $this->db->insert_id;
		if(!$result){
			echo("Error description: ".mysqli_error($this->db)."<br/>");
			return false;
		}else{
			return $insert_id;
		}
	}
	
	public function num_rows($result){
		return mysqli_num_rows($result);
	}
	
	public function close_db(){
		mysqli_close($this->db);
	}
	//SQL Functions
	public function sql_open_db(){
		$this->sql_db = sqlsrv_connect( $this->sql_host, array( "Database"=>$this->sql_database));
		if( $this->sql_db === false ) {
			return false;
		}else{
			return true;
		}
	}
	
	public function sql_runQuery($query) {
		$result = sqlsrv_query($this->sql_db,$query);
		if(!$result){
			echo("Error description: ".mysqli_error($this->sql_db)."<br/>");
			return false;
		}else{
			return $result;
		}		
	}
	
	public function sql_result($result){
		$data = array();
		while ($obj = sqlsrv_fetch_object($result)){
			if($obj){				
				$data[] = $obj;
			}
		}
		return $data;
	}	
	public function sql_runQuery_insert_id($query) {
		$result    = sqlsrv_query($this->sql_db,$query);
		$insert_id = $this->sql_db->insert_id;
		if(!$result){
			echo("Error description: ".sqlsrv_errors($this->sql_db)."<br/>");
			return false;
		}else{
			return $insert_id;
		}
	}
	
	public function sql_num_rows($result){
		return sqlsrv_num_rows($result);
	}
	
	public function sql_close_db(){
		sqlsrv_close($this->sql_db);
	}
}
?>