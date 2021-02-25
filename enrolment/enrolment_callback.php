<?php	
	$frm = "";
	if(isset($_REQUEST['frm'])){
		$frm = $_REQUEST['frm'];
		require("./enrolment_model.php");
		$api_model            = new enrolment_model;
		$controller_name      = 'candidate_tracker';
		$form_view            = $api_model->get_page_info($controller_name);
		$form_info            = $form_view['field_info'];
	}
	if($frm === "save"){
		$prime_qry_key   = "";
		$prime_qry_value = "";
		$prime_upd_query = "";
		$prime_id        = "prime_".$form_view['field_info'][0]->prime_module_id."_id";
		$form_id         = $_POST[$prime_id];
		$employee_type   = $_POST['employee_type'];		
		foreach($form_view['field_info'] as $setting){
			$field_type      = $setting->field_type;
			$input_view_type = (int)$setting->input_view_type;
			$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
			$field_isdefault = $setting->field_isdefault;
			$unique_field    = (int)$setting->unique_field;
			$view_name       = $setting->view_name;			
			if((int)$field_type === 7){
				$multi_name = $label_id."[]";
				$value = trim(implode(",",$_POST[$multi_name]));
			}else{
				$value = str_replace("  "," ",trim($_POST[$label_id]));
			}
			
			if((int)$field_type === 4){
				$value = date('Y-m-d',strtotime($value));
			}else
			if((int)$field_type === 13){
				$value = date('Y-m-d H:i:s',strtotime($value));
			}else
			if((int)$field_type === 8){//textbox only
				$value = str_replace('"',"xdbquot",$value);
				$value = str_replace("'","xquot",$value);
				$value = str_replace("&","xxamp",$value);
			}
			if($label_id === "current_salary" && $value === ""){
				$value = 0;
			}

			if(($input_view_type === 1) || ($input_view_type === 2)){
				$prime_upd_query   .= $label_id.' = "'.$value.'",';
			}
		}
		$created_on = date("Y-m-d h:i:s");
		$logged_id  = 1;
		$prime_table        = "cw_".$controller_name;
		$prime_upd_query    .= 'trans_updated_by = "'. $logged_id .'",trans_updated_date = "'.$created_on.'"';
		$prime_update_query  = "UPDATE $prime_table SET ". $prime_upd_query .' WHERE '. $prime_id.' = "'. $form_id .'"';		
		$update_info = $api_model->get_update($prime_update_query);
		if($update_info){
			echo json_encode(array('success' => TRUE, 'message' => "Form Incomplete : Proceed to Education Tab to Complete",'insert_id'=>$form_id));
		}		
	}else
	if($frm === "rowset_save"){
		$view_id         = $_POST['view_id'];
		$module_id       = $_POST['module_id'];
		$row_prime_id    = $_POST['row_prime_id'];
		$row_label_name  = $_POST['row_label_name'];
		$prime_id        = $_POST['prime_id'];
		echo $api_model->rowset_save($view_id,$module_id,$row_prime_id,$row_label_name,$prime_id,$controller_name);
	}else
	if($frm === "row_set_edit"){
		$row_id          = $_POST['row_id'];
		$view_id         = $_POST['view_id'];
		$table_name      = $_POST['table_name'];
		echo $api_model->row_set_edit($row_id,$view_id,$table_name);
	}else
	if($frm === "row_set_remove"){
		$row_id          = $_POST['row_id'];
		$view_id         = $_POST['view_id'];
		$table_name      = $_POST['table_name'];
		$prime_id        = $_POST['prime_id'];
		echo $api_model->row_set_remove($row_id,$view_id,$table_name,$prime_id);
	}else
	if($frm === "remove_file"){
		$prime_id_val    = $_POST['prime_id_val'];
		$is_defult       = (int)$_POST['is_defult'];
		$input_name      = $_POST['input_name'];
		$prime_table     = "cw_".$controller_name;
		echo $api_model->row_set_remove($prime_id_val,$is_defult,$input_name,$prime_table);
	}else 
	if($frm === "calculation_suggest"){
		echo $api_model->calculation_suggest();
	}else
	if($frm === "exit_number"){	
		$mobile_number      = $_POST['mobile_number'];
		$_SESSION['mobile_number']    = $mobile_number;
		$prime_table        = "cw_".$controller_name;		
		$exit_qry  = 'select count(*) as exit_count,candidate_status from '.$prime_table.' where  mobile_number= "'.$mobile_number.'" and trans_status=1';
		$exit_rslt =  $api_model->is_exit_data($exit_qry);
		$exit_count     = $exit_rslt['exit_count'];
		$status         = $exit_rslt['status'];		
			if((int)$exit_count === 0){
				$prefix_code = date("y").date("m").date("d");
				$last_count_qry = 'select max(candidate_code) as candidate_code from '.$prime_table.' where  candidate_code like "%'.$prefix_code.'%"';
				$code_info = $api_model->runQuery("$last_count_qry");
				$code_result  = $api_model->result($code_info);
				$candidate_code = $code_result[0]->candidate_code;
				if($candidate_code){
					$candidate_code = $candidate_code + 1;				 
				}else{
					$candidate_code = $prefix_code."0001";
				}
				$created_on = date("Y-m-d h:i:s");
				$logged_id  = 1;
				$prime_table        = "cw_".$controller_name;
				$prime_qry_key     .= "mobile_number,candidate_code,trans_created_by,trans_created_date";
				$prime_qry_value   .= '"'.$mobile_number.'","'.$candidate_code.'","'.$logged_id.'",'.'"'.$created_on.'"';
				$prime_insert_query = "insert into $prime_table ($prime_qry_key) values ($prime_qry_value)";
				$insert_id = $api_model->get_save($prime_insert_query);			
				echo json_encode(array('success' => TRUE, 'message' => "Successfully added fill your further information",'insert_id'=>$insert_id,'mobile_number'=>$mobile_number,'candidate_code'=>$candidate_code));			
			}else{	
				if((int)$status === 6){
					$form_rslt  =  $api_model->get_form_data($mobile_number);
					echo json_encode(array('success' => FALSE, 'message' => "Already exist your details update your profile?",'form_rslt'=>$form_rslt['rslt_info'],'row_set_data'=>$form_rslt['row_view_list']));
				}else{
					echo json_encode(array('success' => false, 'message' => "Your Form Already Submitted.. Please Contact HR",'status'=>true));
				}			
			}
				
	}else
	if($frm === "session_exist"){
		$mobile_number      = $_POST['mobile_number'];
		$prime_table        = "cw_".$controller_name;
		$exit_qry  = 'select count(*) as exit_count,candidate_status from '.$prime_table.' where  mobile_number= "'.$mobile_number.'" and trans_status=1';
		$exit_rslt      =  $api_model->is_exit_data($exit_qry);
		$exit_count     = $exit_rslt['exit_count'];
		$status         = $exit_rslt['status'];	
		if((int)$exit_count > 0){
			if((int)$status === 6){
				$form_rslt  =  $api_model->get_form_data($mobile_number);
				echo json_encode(array('success' => true, 'message' => "Already exist your details update your profile?",'form_rslt'=>$form_rslt['rslt_info'],'row_set_data'=>$form_rslt['row_view_list']));
			}else{
				echo json_encode(array('success' => false, 'message' => "Your Form Already Submitted.. Please Contact HR"));
			}			
		}				
	}else
	if($frm === "clear_session"){
		unset($_SESSION['mobile_number']);	
		echo json_encode(array('success' => true));	
	}else
	if($frm === "get_position"){
		$department      = $_POST['department'];
		$position_qry = 'select prime_position_id,position_name from cw_position where department ="'.$department.'" and trans_status = 1';
		$position_info    = $api_model->runQuery("$position_qry");
		$position_result  = $api_model->result($position_info);
		$position_list = "<option value=''>--- Select Post Applied For ---</option>";
		foreach($position_result as $result){
			$id        = $result->prime_position_id;
			$position  = $result->position_name;
			$position_list .= "<option value='$id'> $position </option>";
		}
		echo $position_list;
	}else
	if($frm === "check_dob_exist"){
		$date_of_birth    = date("Y-m-d",strtotime($_POST['date_of_birth']));
		$prime_id         = $_POST['prime_id_val'];
		$dob_qry = 'select count(*) as count from cw_candidate_tracker where date_of_birth ="'.$date_of_birth.'"';
		$dob_info    = $api_model->runQuery("$dob_qry");
		$dob_result  = $api_model->result($dob_info);
		$dob_count   = $dob_result[0]->count;
		if((int)$dob_count > 0){
			$update_query  = 'UPDATE cw_candidate_tracker SET dob_exist = "1" WHERE prime_candidate_tracker_id = "'. $prime_id .'"';
			$update_info = $api_model->get_update($update_query);
			if($update_info){			
				echo json_encode(array('success' => TRUE, 'message' => "Exist"));
			}	
		}else{
			$update_query  = 'UPDATE cw_candidate_tracker SET dob_exist = "2" WHERE prime_candidate_tracker_id = "'. $prime_id .'"';
			$update_info = $api_model->get_update($update_query);
			if($update_info){			
				echo json_encode(array('success' => TRUE, 'message' => "Not Exist"));
			}	
		}
	}else
	if($frm === "show_education_tab"){
		$prime_id         = $_POST['prime_id_val'];
		$employee_type    = $_POST['employee_type'];
		//Check Education count
		$rowset_edu_count_qry = 'select count(*) as count from cw_candidate_tracker_educational_qualification where trans_status = 1 and prime_candidate_tracker_id ='.$prime_id;
		//echo $rowset_edu_count_qry; die;
		$rowset_edu_info    = $api_model->runQuery("$rowset_edu_count_qry");
		$rowset_edu_result  = $api_model->result($rowset_edu_info);
		$education_count    = $rowset_edu_result[0]->count;	
		if((int)$education_count < 1){
			echo json_encode(array('success' => false, 'message' => "Education Tab Should Be Filled"));
		}else{
			if((int)$employee_type === 1){
				$inc = "2";
			}else{
				$inc = "1";
			}
			$update_query  = 'UPDATE cw_candidate_tracker SET inc = "'.$inc.'" WHERE prime_candidate_tracker_id = "'. $prime_id .'"';
			$update_info = $api_model->get_update($update_query);
			echo json_encode(array('success' => TRUE));
		}		
	}else
	if($frm === "show_experience_tab"){
		$prime_id         = $_POST['prime_id_val'];
		//Check Experience count
		$rowset_count_qry = 'select count(*) as count from cw_candidate_tracker_working_experience where  trans_status = 1 and prime_candidate_tracker_id ='.$prime_id;
		$rowset_info    = $api_model->runQuery("$rowset_count_qry");
		$rowset_result  = $api_model->result($rowset_info);
		$work_experience_count = $rowset_result[0]->count;
		if((int)$work_experience_count === 0){
			echo json_encode(array('success' => false, 'message' => "Work Experience Tab Should Be Filed if you are Experienced"));
		}else{
			$update_query  = 'UPDATE cw_candidate_tracker SET inc = "2" WHERE prime_candidate_tracker_id = "'. $prime_id .'"';
			$update_info = $api_model->get_update($update_query);
			unset($_SESSION['mobile_number']);	
			echo json_encode(array('success' => TRUE, 'message' => "Successfully Updated your profiles"));
		}		
	}else
	if($frm === "company_location_tab"){
		$company_location         = $_POST['company_location'];
		$location_query = 'select * from cw_branch inner join cw_location on cw_branch.location=cw_location.prime_location_id where cw_location.trans_status=1 and prime_location_id="'.$company_location.'"';
		$location_info    = $api_model->runQuery("$location_query");
		$location_result  = $api_model->result($location_info);
		$branch_list = "<option value=''>--- Select Branch ---</option>";
		foreach($location_result as $result){
			$id        = $result->prime_branch_id;
			$branch  = $result->branch;
			$branch_list .= "<option value='$id'> $branch </option>";
		}
		echo $branch_list;

	}
	else{
		echo json_encode(array('success' => false, 'message' => "Something Went Wrong..! Please Try Again Later"));
	}


?>