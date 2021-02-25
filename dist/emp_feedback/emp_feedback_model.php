<?php
session_start();
include('../app/dbconnect.php');
class emp_feedback_model extends dbconnect{
	public function __construct() {
		$this->open_db();
    }
	//Check Employee Exist in the Offer Letter
	public function check_employee_exist($mobile_number,$offer_ref_no){
		$check_employee_qry    = 'select employee_mobile_number,employee_name from cw_offer_letter where employee_mobile_number = "'.$mobile_number.'" and offer_reference_number = "'.$offer_ref_no.'" and trans_status = 1';
		$check_employee_info   = $this->runQuery("$check_employee_qry");
		$check_employee_result = $this->result($check_employee_info);
		if($check_employee_result){
			return $check_employee_result;
		}else{
			return false;
		}
	}
	public function check_feedback_exist($mobile_number,$offer_ref_no){
		$check_employee_qry    = 'select mobile_number from cw_emp_feedback where mobile_number = "'.$mobile_number.'" and offer_ref_no = "'.$offer_ref_no.'" and trans_status = 1';
		$check_employee_info   = $this->runQuery("$check_employee_qry");
		$check_employee_result = $this->result($check_employee_info);
		if($check_employee_result){
			return $check_employee_result;
		}else{
			return false;
		}
	}	
	
	public function save_feedback($mobile_number,$offer_ref_no,$fdata){
		$qry_key   = "";
		$qry_value = "";
		foreach($fdata as $form_data){
			$name  = $form_data->name;
			$value = $form_data->value;
			if($value){				
				$qry_key     .= "$name,";			
				$qry_value   .= "'$value',";
			}							
		}
		$qry_key    = rtrim($qry_key,',');
		$qry_value  = rtrim($qry_value,','); 
		$query = "insert into cw_emp_feedback ($qry_key,mobile_number,offer_ref_no,trans_created_date) values ($qry_value,$mobile_number,$offer_ref_no,'".date("Y-m-d H:i:s")."')";
		$insert_feedback_info = $this->runQuery($query);
		if($insert_feedback_info){
			return true;
		}else{
			return false;
		}
	}
}
?>