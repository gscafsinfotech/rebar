<?php
class dbconnect {	
	protected $db;	
	private $host     = "localhost";
	private $username = "root";
	private $password = "";
	private $database = "cafsrms";
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
}
?>