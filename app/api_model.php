<?php
include('./dbconnect.php');
//error_reporting(0);
class api_model extends dbconnect{	
	public function __construct() {
		$this->open_db();
		$this->sql_open_db();
    }	
    //check data
	public function get_sql_emp($sql_emp_qry){
		$sql_emp_info    = $this->sql_runQuery("$sql_emp_qry");
		$sql_emp_result  = $this->sql_result($sql_emp_info);
		return $sql_emp_result;
	}
	public function get_mysql_emp($mysql_emp_qry){
		$mysql_emp_info    = $this->runQuery("$mysql_emp_qry");
		$mysql_emp_result  = $this->result($mysql_emp_info);
		$mysql_emp_result  = json_decode(json_encode($mysql_emp_result),true);		
		$emp_result = array_reduce($mysql_emp_result, function($result, $arr){			
		    $result[$arr['employee_code']] = $arr;
		    return $result;
		}, array());
		return $emp_result;
	}
}
?>
