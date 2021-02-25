<?php
include('./dbconnect.php');
class api_model extends dbconnect{	
	public function __construct() {
		$this->open_db();
    }
	
	//check data
	public function is_exit_data($exit_qry){
		$exit_info     = $this->runQuery("$exit_qry");
		$exit_result   = $this->result($exit_info);
		$exit_count    = $exit_result[0]->rslt;
		return $exit_count;
	}
	//insert data
	public function rms_update($prime_query){
		$insert_info   = $this->runQuery("$prime_query");
		if($insert_info){
			return json_encode(array('status' => true,'data' => 'Successfully Updated!!!'));
		}else{
			return json_encode(array('status' => false,'data' => 'Failed to Update..'));
		}	
	}
}
?>
