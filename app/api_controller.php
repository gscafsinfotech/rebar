 <?php
	$frm = "";
	/* Module Initilization */
	if (isset($_REQUEST["frm"])){
	    $frm = $_REQUEST['frm']; 
	    require("./api_model.php");
	    $api_model = new api_model;
	}
	if($frm === "get_employees"){
		$rslt ="";
			$sql_emp_rslt   = $api_model->get_sql_emp("SELECT CODE,EMPNAME FROM EMPLY_MASTER");
			$mysql_emp_rslt = $api_model->get_mysql_emp("select employee_code from cw_employees");
			echo "<pre>";
			print_r($mysql_emp_rslt); die;
			foreach ($sql_emp_rslt as $key => $value) {
				$employee_code = $value->CODE;				
				if($mysql_emp_rslt[$employee_code]['employee_code']){
					$sql = "insert into cw_employees (employee_code,emp_name) values $insert_values";
					$rslt = $api_model->offer_insert_update($prime_insert_query);
				}else{
					$prime_update_query  = 'UPDATE cw_offer_letter SET rms_code = "'. $rms_code .'",employee_name = "'. $candidate_name .'", emp_dept = "'. $department .'",employee_designation = "'. $post_applied_for .'",salary = "'. $salary_commited .'",branch = "'. $candidate_branch .'",offer_location = "'. $candidate_loc .'",employee_email_id = "'. $candidate_email .'",joining_date = "'. $doj .'" WHERE employee_mobile_number = "'. $mobile_number .'"';
					$rslt = $api_model->offer_insert_update($prime_update_query);
				}
			}
			die;			
		return_rslt($frm,$rslt);
	}else{
	    echo json_encode(array(
			'Status' => 400,
	        'success' => False,
	        'data' => "Bad Request"
	    ));
	}
	function return_rslt($frm,$rslt){
		if(!$rslt){
			echo json_encode(array('success' => FALSE, 'sts' =>"No Record found"));
		}else{
			echo json_encode(array('success' => TRUE, "$frm" => $rslt));
		}
	}
?>