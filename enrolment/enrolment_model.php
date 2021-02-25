<?php
include('../app/dbconnect.php');
error_reporting(0);
session_start();
class enrolment_model extends dbconnect{
	
	public function __construct() {
		$this->open_db();
    }
	
	public function curl($url){		
		$ch = curl_init(); //  Initiate curl		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Will return the response, if false it print the response		
		curl_setopt($ch, CURLOPT_URL,$url); // Set the url		
		$result=curl_exec($ch);// Execute		
		curl_close($ch);// Closing
		return json_decode($result,true);
	}
	
	public function real_escape_string($value){
		$value = mysql_real_escape_string($value);
		if(empty($value)){
			$value = 0;
		}
		return $value;
	}
	
	public function get_page_info($controller_name){
		$this->control_name = $controller_name;
		$field_name_query = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$controller_name.'" and cw_custom_design.input_view in (1,2,3) ORDER BY cw_form_setting.field_sort asc';
		$field_info = $this->runQuery("$field_name_query");
		$field_info = $this->result($field_info);
		$page_info['field_info'] = $field_info;
		$view_info  = $this->view_info($controller_name);
		$page_info['view_info']  = $view_info;
		$all_pick = $this->get_picklist_value($page_info);
		$page_info['all_pick'] = $all_pick;
		$row_view_list = $this->rowset_view($controller_name);
		$page_info['row_view_list'] = $row_view_list;
		$formula_result = $this->formula_label($controller_name);
		$page_info['formula_result'] = $formula_result;
		$condition_list = $this->get_condition();
		$page_info['condition_list'] = $condition_list;
		return $page_info;
	}
	
	public function view_info($controller_name){
		$view_info_query = 'select * from cw_custom_design inner join cw_form_view_setting on cw_form_view_setting.prime_form_view_id =cw_custom_design.input_for  where cw_custom_design.prime_module_id = "'.$controller_name.'" group by input_for ORDER BY form_view_sort asc';
		$view_info_data = $this->runQuery("$view_info_query");
		$view_info_result = $this->result($view_info_data);
		return $view_info_result;
	}
	
	public function get_picklist_value($page_info){
		foreach($page_info['field_info'] as $setting){
			$prime_form_id      = (int)$setting->prime_form_id;
			$prime_module_id    = $setting->prime_module_id;
			$input_view_type    = (int)$setting->input_view_type;
			$input_for          = (int)$setting->input_for;
			$field_type         = (int)$setting->field_type;
			$label_id           = $setting->label_name;
			$label_name         = ucwords($setting->view_name);
			$field_length       = $setting->field_length;
			$field_decimals     = $setting->field_decimals;
			$pick_list_type     = (int)$setting->pick_list_type;
			$pick_list          = $setting->pick_list;
			$pick_table         = $setting->pick_table;
			$auto_prime_id      = $setting->auto_prime_id;
			$auto_dispaly_value = $setting->auto_dispaly_value;
			$field_isdefault    = (int)$setting->field_isdefault;
			$file_type          = (int)$setting->file_type;
			$mandatory_field    = (int)$setting->mandatory_field;
			$unique_field       = (int)$setting->unique_field;
			$search_show        = (int)$setting->search_show;
			$array_list = array();
			$pick_sel_table = "cw_".$prime_module_id;
			if(($field_type === 5) || ($field_type === 7)){
				if($pick_list_type === 1){
					$pick_list_val   = explode(",",$pick_list);
					$pick_list_val_1 = $pick_list_val[0];
					$pick_list_val_2 = $pick_list_val[1];
					if($pick_table == "cw_category"){
						$qry = " and prime_category_id != 1";
					}else{
						$qry = "";
					}
					$pick_query = "select $pick_list from $pick_table where trans_status = 1 $qry";
					$pick_data   = $this->runQuery("$pick_query");
					$pick_result = $this->result($pick_data);
					$array_list = array();
					foreach($pick_result as $pick){
						$pick_key = $pick->$pick_list_val_1;
						$pick_val = $pick->$pick_list_val_2;
						$array_list[$pick_key] = $pick_val;
					}
					$all_pick[$prime_form_id] = $array_list;
				}else
				if($pick_list_type === 2){ 
					$pick_list_val_1 = $pick_table."_id";
					$pick_list_val_2 = $pick_table."_value";
					$pick_list_val_3 = $pick_table."_status";
					$pick_query = "select $pick_list_val_1,$pick_list_val_2 from $pick_table where $pick_list_val_3 = 1";
					$pick_data   = $this->runQuery("$pick_query");
					$pick_result = $this->result($pick_data);
					$array_list  = array();
					foreach($pick_result as $pick){
						$pick_key = $pick->$pick_list_val_1;
						$pick_val = $pick->$pick_list_val_2;
						$array_list[$pick_key] = $pick_val;
					}
					$all_pick[$prime_form_id] = $array_list;
				}
			}
		}
		return $all_pick;
	}
	
	//rowset form details
	public function rowset_view($controller_name){
		$rowset_view_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$controller_name.'" and cw_custom_design.input_view = "3"';
		$rowset_view_data   = $this->runQuery("$rowset_view_qry");
		$rowset_view_result = $this->result($rowset_view_data);
		$row_view_list = array();
		$prime_id  = 0;
		foreach($rowset_view_result as $view){
			$prime_form_view_id   = $view->input_for;
			$row_set_data = $this->get_row_set_data($prime_form_view_id,$controller_name,$prime_id);
			$row_view_list[$prime_form_view_id] = $row_set_data;
		}
		return $row_view_list;
	}
	
	public function formula_label($controller_name){
		$formula_qry     = 'select * from cw_form_bind_input where input_cond_module_id = "'.$controller_name.'" and trans_status = 1';
		$formula_data   = $this->runQuery("$formula_qry");
		$formula_result = $this->result($formula_data);
		return $formula_result;
	}
	
	//save function main form
	public function get_save($prime_insert_query){
		$insert_info   = $this->runQuery_insert_id("$prime_insert_query");
		return $insert_info;
	}
	
	//update function main form
	public function get_update($prime_update_query){
		$update_info   = $this->runQuery("$prime_update_query");
		return $update_info;
	}
	
	//Already Exit or not count 
	public function is_exit_data($exit_qry){
		$exit_info       = $this->runQuery("$exit_qry");
		$exit_result     = $this->result($exit_info);
		$prime_id        = $exit_result[0]->prime_candidate_tracker_id;
		$exit_count      = $exit_result[0]->exit_count;
		$candidate_status = $exit_result[0]->candidate_status;
		$incomplete       = $exit_result[0]->inc;
		$exit_rslt = array('exit_count'=>$exit_count,'status'=>$candidate_status,'incomplete'=>$incomplete,'prime_id'=>$prime_id);
		return $exit_rslt;
	}
	
	//Already Exit or not count employee master
	public function is_exit_emp_data($exit_employee_qry){
		$exit_emp_info     = $this->runQuery("$exit_employee_qry");
		$exit_emp_result   = $this->result($exit_emp_info);
		$exit_emp_count    = $exit_emp_result[0]->emp_rslt;
		return $exit_emp_count;
	}
	
	/* ==============================================================*/
	/* ================== ROWSET OPERATION - START ==================*/
	/* ==============================================================*/
	// ROWSET SAVE FORM
	public function rowset_save($view_id,$module_id,$row_prime_id,$row_label_name,$prime_id){
		$table_name      = "cw_".$module_id."_".$row_label_name;
		$table_id        =  $module_id."_".$row_label_name;
		$table_prime     = "prime_".$table_id."_id";
		$prime_qry_key   = "prime_".$module_id."_id,";
		$prime_qry_value = '"'.$prime_id.'",';
		$prime_upd_query = "";
		$form_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$module_id.'" and cw_custom_design.input_for =  "'.$view_id.'" and cw_custom_design.input_view =3 and cw_custom_design.trans_status = "1" order by abs(field_sort)';
		$form_data   = $this->runQuery("$form_qry");
		$form_result = $this->result($form_data);
		foreach($form_result as $setting){
			$field_type      = $setting->field_type;
			$input_view_type = (int)$setting->input_view_type;
			$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
			$field_isdefault = $setting->field_isdefault;
			if((int)$field_type === 7){
				$multi_name = $label_id;
				$value = implode(",",$_POST[$multi_name]);
			}else{
				$value = $_POST[$label_id];
			}			
			if((int)$field_type === 4){
				$value = date('Y-m-d',strtotime($value));
			}	
			if($label_id === "standard"){
				$standard = $value;
			}		
			$prime_qry_key     .= $label_id.",";
			$prime_qry_value   .= '"'.$value.'",';
			$prime_upd_query   .= $label_id.' = "'.$value.'",';
		}
		if($table_name === "cw_candidate_tracker_educational_qualification"){
			if((int)$standard !== 4){
				$count_qry    = 'select count(*) as edu_count from cw_candidate_tracker_educational_qualification where prime_candidate_tracker_id = "'.$prime_id.'" and  standard = "'.$standard.'" and  '. $table_prime .' != "'.$row_prime_id.'" and trans_status = 1';		
				$count_data = $this->runQuery("$count_qry");
				$count_result  = $this->result($count_data);
				$edu_count = $count_result[0]->edu_count;
				if((int)$edu_count > 0){
					echo json_encode(array('success' => FALSE, 'message' => "Standard Already Exist"));
					exit(0);
				}	
			}					
		}
		$created_on = date("Y-m-d h:i:s");
		$logged_id  = 1;
		if((int)$row_prime_id === 0){
			$prime_qry_key     .= "trans_created_by,trans_created_date";
			$prime_qry_value   .= '"'.$logged_id.'",'.'"'.$created_on.'"';
			$prime_insert_query = "insert into $table_name ($prime_qry_key) values ($prime_qry_value)";
			$insert_id         =  $this->runQuery_insert_id("$prime_insert_query");
			$row_set_data = $this->get_row_set_data($view_id,$module_id,$prime_id);
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
		}else{
			$prime_upd_query    .= 'trans_updated_by = "'. $logged_id .'",trans_updated_date = "'.$created_on.'"';
			$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
			$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
			$row_set_data = $this->get_row_set_data($view_id,$module_id,$prime_id);
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
		}
	}
	
	//PROVIDE ROWSET DATA BY ID
	public function get_row_set_data($view_id,$controller_name,$prime_id){
		$viewset_data_qry    = 'select * from cw_form_view_setting where prime_form_view_id = "'.$view_id.'" and prime_view_module_id = "'.$controller_name.'" and  form_view_type = "3" and trans_status = 1';
		$row_view_data   = $this->runQuery("$viewset_data_qry");
		$row_view_result = $this->result($row_view_data);
		$prime_form_view_id   = $row_view_result[0]->prime_form_view_id;
		$prime_view_module_id = $row_view_result[0]->prime_view_module_id;
		$form_view_label_name = $row_view_result[0]->form_view_label_name;
	
		$div_id       	 = $form_view_label_name."_div_".$prime_form_view_id;
		$table_id        = $form_view_label_name."_tbl_".$prime_form_view_id;
		$table_name      = "cw_".$controller_name."_".$form_view_label_name;
		$row_id          = $controller_name."_".$form_view_label_name;
		$row_prime_id    = "prime_".$row_id."_id";
		$table_prime_id  = "prime_".$controller_name."_id";
		
		$form_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$controller_name.'" and cw_custom_design.input_for =  "'.$prime_form_view_id.'" and cw_custom_design.input_view =3 and cw_custom_design.trans_status = "1" order by abs(field_sort)';
		$form_data   = $this->runQuery("$form_qry");
		$form_result = $this->result($form_data);
		$table_head = array();
		$thead_line = "";
		$select_query = "$table_name.$row_prime_id,$table_name.$table_prime_id,";
		$pick_query ="";
		foreach($form_result as $form){
			$prime_form_id  = (int)$form->prime_form_id;
			$view_name      = $form->view_name;
			$label_name     = $form->label_name;
			$field_type     = (int)$form->field_type;
			$pick_list_type = (int)$form->pick_list_type;
			$pick_list      = $form->pick_list;
			$pick_table     = $form->pick_table;
			$auto_prime_id      = $form->auto_prime_id;
			$auto_dispaly_value = $form->auto_dispaly_value;
			
			if((int)$field_type === 4){
				$select_query .= 'DATE_FORMAT('.$table_name.'.'.$label_name.', "%d-%m-%Y") as '.$label_name.' , ';
			}else
			if(($field_type === 5) || ($field_type === 7)){
				if($pick_list_type === 1){
					$pick_list_val   = explode(",",$pick_list);
					$pick_list_val_1 = $pick_list_val[0];
					$pick_list_val_2 = $pick_list_val[1];
					
					$pick_query_as = $pick_table."_".$prime_form_id;
					
					$select_query .= "$pick_query_as.$pick_list_val_2 as $label_name , ";
					$pick_query .= " left join $pick_table as $pick_query_as on $pick_query_as.$pick_list_val_1 = $table_name.$label_name ";
				}else
				if($pick_list_type === 2){ 
					$pick_list_val_1 = $pick_table."_id";
					$pick_list_val_2 = $pick_table."_value";
					$pick_list_val_3 = $pick_table."_status";
					
					$pick_query_as = $pick_table."_".$prime_form_id;
					$select_query .= "$pick_query_as.$pick_list_val_2 as $label_name , ";
					$pick_query   .= " left join $pick_table as $pick_query_as on $pick_query_as.$pick_list_val_1 = $table_name.$label_name ";
				}
			}else
			if($field_type === 9){
				$pick_query_as = $pick_table."_".$prime_form_id;
				$select_query .= "$pick_query_as.$auto_dispaly_value as $label_name,";
				$pick_query .= " left join $pick_table as $pick_query_as on $pick_query_as.$auto_prime_id = $table_name.$label_name ";
			}else{
				$select_query .= "$table_name.$label_name , ";
			}
			$table_head[] = $label_name;
			$thead_line  .= "<th>$view_name</th>";
		}
		$thead = "<tr>$thead_line<th>Option</th></tr>";
		$select_query = rtrim($select_query,',');
		$select_query = rtrim($select_query,' , ');
		$final_qry    = "select $select_query from $table_name $pick_query " .' where '.$table_name.'.'.$table_prime_id.' = "'.$prime_id.'" and '.$table_name.'.trans_status = "1" order by abs('.$table_name.'.'.$row_prime_id.') asc';
		$row_data   = $this->runQuery("$final_qry");
		$row_result = $this->result($row_data);
		$tr_line = "";
		foreach($row_result as $data){
			$td_line = "";
			foreach($table_head as $key){
				$value = $data->$key;
				if($value === "01-01-1970"){
					$value = "";
				}
				$td_line .= "<td>$value</td>";
			}
			$row_id   = $data->$row_prime_id;
			$tab_name = $controller_name."_".$form_view_label_name;
			$edit_btn   = "<a class='btn btn-edit btn-xs row_btn' onclick = row_set_edit('$row_id','$tab_name','$prime_form_view_id');>Edit</a>";
			$remove_btn = "<a class='btn btn-danger btn-xs row_btn' onclick = row_set_remove('$row_id','$tab_name','$prime_form_view_id','$prime_id');>Delete</a>";
			$tr_line .= "<tr>$td_line<td>$edit_btn $remove_btn</td></tr>";
		}
		$row_set_view = "<table id='$table_id' class='table table-bordered'>
							<thead>$thead</thead>
							<tbody>$tr_line</tbody>
						</table>";
		return array('div_id' => $div_id, 'table_id' => $table_id,'row_set_view'=>$row_set_view);
	}
	
	//ROW SET EDIT DATA
	public function row_set_edit($row_id,$view_id,$table_name){
		$table_prime_id  = "prime_".$table_name."_id";
		$cust_tab        = "cw_".$table_name;
		$final_qry  = "select * from $cust_tab " .' where '.$table_prime_id.' = "'.$row_id.'" and  trans_status = "1"';
		$row_data   = $this->runQuery("$final_qry");
		$row_result = $this->result($row_data);
		$form_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$this->control_name.'" and cw_custom_design.input_for =  "'.$view_id.'" and cw_custom_design.input_view =3 and cw_custom_design.trans_status = "1" order by abs(field_sort)';
		$form_data   = $this->runQuery("$form_qry");
		$form_result = $this->result($form_data);
		$rslt_info = array();
		$rslt_info[$table_prime_id] = array('input_value'=>$row_result[0]->$table_prime_id,'field_type'=>1); ;
		foreach($form_result as $form){
			$prime_form_id      = (int)$form->prime_form_id;
			$label_name         = $form->label_name;
			$field_type         = $form->field_type;
			$pick_table         = $form->pick_table;
			$auto_prime_id      = $form->auto_prime_id;
			$auto_dispaly_value = $form->auto_dispaly_value;
			
			$input_value       = $row_result[0]->$label_name;
			if((int)$field_type === 4){
				$input_value = date('d-m-Y',strtotime($input_value));
				if($input_value === "01-01-1970"){
					$input_value = "";
				}
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else
			if((int)$field_type === 9){
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
				$pick_query = 'select '.$auto_dispaly_value.' from '.$pick_table.' where '.$auto_prime_id.' = "'.$input_value.'" and trans_status = 1';
				$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
				$pick_result = $pick_data->result();
				$pick_data->next_result();
				$input_value = $pick_result[0]->$auto_dispaly_value;
				$label_name  = $label_name."_hidden_".$prime_form_id;
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else{
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}
		}
		echo json_encode(array('success' => TRUE, 'row_result' => $rslt_info));
	}
	
	//ROW SET REMOVE DATA
	public function row_set_remove($row_id,$view_id,$table_name,$prime_id){
		$table_prime_id  = "prime_".$table_name."_id";
		$table_name      = "cw_".$table_name;
		$logged_id       = 1;		
		$today_date      = date("Y-m-d h:i:s");
		$final_qry = 'UPDATE '.$table_name.' SET trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" , trans_status = 0 WHERE '.$table_prime_id.' = "'.$row_id.'"';
		$row_data     = $this->runQuery("$final_qry");
		$controller_name = $this->control_name;
		$row_set_data = $this->get_row_set_data($view_id,$controller_name,$prime_id);
		echo json_encode(array('success' => TRUE, 'msg' => "Remove Successfully",'row_set_data' => $row_set_data));
	}
	
		//UPDATE STATUS TO DELETE FOR UPLOAD FILES or DOCUMENTS
	public function remove_file($prime_id_val,$is_defult,$input_name,$table_name){
		if($table_name){
			$created_on    = date("Y-m-d h:i:s");
			$set_query     = $input_name .' = "" ,trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
			$update_query  = 'UPDATE '.$table_name .' SET '. $set_query .' WHERE '. $this->prime_id .' = "'. $prime_id_val .'"';
			$this->runQuery("$update_query");
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Unable to process your request"));
		}
	}
	/* ==============================================================*/
	/* ================== ROWSET OPERATION - END   ==================*/
	/* ==============================================================*/
	
	// PROVIDE MODLE ONLOAD CONDITION & FORMULA (CONDITION 2 ONLY HERE TAKE)
	public function get_condition(){
		$condition_query = 'select * from cw_form_condition_formula inner join cw_custom_design ON cw_custom_design.label_name = condition_bind_to where cond_module_id = "'.$this->control_name.'" and cw_form_condition_formula.trans_status = "1"';
		$condition_data   = $this->runQuery("$condition_query");
		$condition_result = $this->result($condition_data);
		$load_script = "";
		foreach($condition_result as $condition){
			$prime_cond_id         = $condition->prime_cond_id;
			$cond_module_id        = $condition->cond_module_id;
			$condition_label_name  = strtolower(str_replace(" ","_",$condition->condition_label_name));
			$condition_type        = (int)$condition->condition_type;
			$condition_check_form  = explode(",",$condition->condition_check_form);
			$condition_bind_to     = explode(",",$condition->condition_bind_to);
			$on_bind_input   = "";
			$on_change_input = "";
			$on_blur_input   = "";
			foreach($condition_check_form as $label_name){
				$cond_from_query = 'select cw_form_setting.field_type as field_type,cw_form_setting.label_name as label_name from cw_form_setting inner join cw_custom_design on cw_form_setting.label_name=cw_custom_design.label_name where cw_form_setting.prime_module_id = "'.$this->control_name.'" and cw_form_setting.label_name in ("'.$label_name.'") ORDER BY cw_form_setting.input_for,cw_form_setting.field_sort asc';
				$cond_form_data   = $this->runQuery("$cond_from_query");
				$cond_form_result = $this->result($cond_form_data);
				$field_type = (int)$cond_form_result[0]->field_type;
				
				/*============ 
					NOTE:  AUTO COMPLETE BOX & FILE UPLOAD BOX ARE NOT INCLUDED IN ON LOAD SCRIPT
				============*/
				if(($field_type === 1) || ($field_type === 2) || ($field_type === 3) || ($field_type === 11) || ($field_type === 12)){
					$on_bind_input  .= "$label_name,";
				}else
				if(($field_type === 5) || ($field_type === 6) || ($field_type === 7)){
					$on_change_input  .= "$label_name,";
				}
				if($condition_type === 2){
					if(($field_type === 4) || ($field_type === 13)){
						$on_blur_input  .= "$label_name,";
					}
				}
			}
			$check_input = "";
			if($on_bind_input){
				$on_bind_input = rtrim($on_bind_input,',');
				$on_bind_input = str_replace(",",",#",$on_bind_input);
				$on_bind_input = "#".$on_bind_input;
				$check_input .= "$on_bind_input";
			}
			if($on_change_input){
				$on_change_input = rtrim($on_change_input,',');
				$on_change_input = str_replace(",",",#",$on_change_input);
				$on_change_input = "#".$on_change_input;
				if($check_input){
					$check_input .= ",$on_change_input";
				}else{
					$check_input .= "$on_change_input";
				}
			}
			if($on_blur_input){
				$on_blur_input = rtrim($on_blur_input,',');
				$on_blur_input = str_replace(",",",#",$on_blur_input);
				$on_blur_input = "#".$on_blur_input;
				if($check_input){
					$check_input .= ",$on_blur_input";
				}else{
					$check_input .= "$on_blur_input";
				}
			}
			$send_for  = implode(",",$condition_check_form);
			$send_data = "prime_cond_id:$prime_cond_id,for_input:'$send_for',";
			foreach($condition_check_form as $check_form){
				$send_data .= "$check_form:$('#$check_form').val(),";
			}
			$send_data = "{".rtrim($send_data,',')."}";
			if($condition_type === 2){//auto binding not for users
				//need to clarify empty is check
				$fill_input = "";
				foreach($condition_bind_to as $bind_to){
					$fill_val    = "rslt.".$bind_to;
					$fill_input .= "$('#$bind_to').val($fill_val);\n $('#$bind_to').trigger('change');";
				}
				//$send_url  = site_url("$this->control_name/calculation_suggest");
				$send_url        = "enrolment_callback.php?frm=calculation_suggest";
				$function_info = "function $condition_label_name(){
									var isValid = true;
									$('$check_input').each(function() {
									if ($(this).val() === '') {
									isValid = false;
									$(this).addClass('error');
									}else{
									$(this).removeClass('error');
									}
									});
									if(isValid){
									$.ajax({
									type: 'POST',
									url: '$send_url',
									data:$send_data,
									success: function(data) {
									// Added to check array is empty value not blanked
									var rslt = JSON.parse(data);
									if(rslt!=''){
									$fill_input
									}
									}
									});
									}
				}";
				if($on_bind_input){
					$load_script .= "$('$on_bind_input').bind('keyup blur change', function(e) {
					$condition_label_name();
					});\n";
				}
				if($on_change_input){
					$load_script .= "$('$on_change_input').change(function(){
					$condition_label_name();
					});\n";
				}
				if($on_blur_input){
					$load_script .= "$('$on_blur_input').blur(function(){
					$condition_label_name();
					});\n";
				}
				$load_script .= "\n$function_info\n";
			}
		}
		$condition_list = array();
		if($load_script){
			$condition_list = $load_script;
		}
		return $condition_list;
	}
	
	//PROVIDE DATA FOR  ONCHANGE CALCUATION
	public function calculation_suggest(){
		$for_input        = $_POST['for_input'];
		$prime_cond_id    = $_POST['prime_cond_id'];
		$logged_id = 1;
		$cond_query = 'select * from cw_form_condition_formula  where prime_cond_id = "'.$prime_cond_id.'" and trans_status = 1';
		$cond_data   = $this->runQuery("$cond_query");
		$cond_result = $this->result($cond_data);
		
		$condition_check_form = explode(",",$cond_result[0]->condition_check_form);
		$condition_bind_to    = $cond_result[0]->condition_bind_to;
		$condition_table      = $cond_result[0]->condition_table;
		$condition_type       = $cond_result[0]->condition_type;
		$is_drop_down         = (int)$cond_result[0]->is_drop_down;//no need dropdown
		$cond_drop_down       = $cond_result[0]->cond_drop_down;
		$fianl_result_array = array();
		$input_query = 'select * from cw_form_bind_input where input_cond_id = "'.$prime_cond_id.'"';
		$input_data   = $this->runQuery("$input_query");
		$input_result = $this->result($input_data);
		$line_input_bind_col = "";
		foreach($input_result as $input){
			$line_input_bind_to    = $input->line_input_bind_to;
			$line_input_bind_col   = $input->line_input_bind_col;
			$line_input_bind_col  = str_replace("~","'",$line_input_bind_col);
			$line_input_bind_col  = str_replace("!",'"',$line_input_bind_col);
			if($line_input_bind_col){
				foreach($condition_check_form as $check_form){
					if(strpos($line_input_bind_col,"@$check_form@") !== false) {
						$value = $_POST[$check_form];
						if(strpos($check_form,"date") !== false) {
							$value = new DateTime($value);
							$value = $value->format("Y-m-d");
							$value = "'$value'";
						}
						$line_input_bind_col = str_replace("@$check_form@",$value, $line_input_bind_col);
					}
				}
				$dynamic_file_name= $line_input_bind_to."_".$logged_id.".php";
				unlink("$dynamic_file_name");
				$fname = $line_input_bind_to."(){";
				$code = "<?php function $fname $line_input_bind_col }?>";
				fopen("$dynamic_file_name", "w");
				file_put_contents("$dynamic_file_name",$code);
				chmod($dynamic_file_name, 0777);
				require_once("$dynamic_file_name");
				$fianl_result_array[$line_input_bind_to] =  $line_input_bind_to();
				unlink("$dynamic_file_name");
			}
		}
		echo json_encode($fianl_result_array);
	}
	
	//ROW SET EDIT DATA
	public function get_form_data($mobile_number){
		$table_prime_id  = "prime_".$this->control_name."_id";
		$cust_tab        = "cw_".$this->control_name;
		$module_id       =  $this->control_name;
		$final_qry  = "select * from $cust_tab " .' where mobile_number = "'.$mobile_number.'" and  trans_status = "1"';
		$row_data   = $this->runQuery("$final_qry");
		$row_result = $this->result($row_data);
		$form_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$this->control_name.'" and cw_custom_design.input_view !=3 and cw_custom_design.trans_status = "1" order by abs(field_sort)';
		$form_data   = $this->runQuery("$form_qry");
		$form_result = $this->result($form_data);
		$rslt_info = array();
		$rslt_info[$table_prime_id] = array('input_value'=>$row_result[0]->$table_prime_id,'field_type'=>1); ;
		foreach($form_result as $form){
			$prime_form_id      = (int)$form->prime_form_id;
			$label_name         = $form->label_name;
			$field_type         = $form->field_type;
			$pick_table         = $form->pick_table;
			$auto_prime_id      = $form->auto_prime_id;
			$auto_dispaly_value = $form->auto_dispaly_value;
			$input_value        = $row_result[0]->$label_name;
			if((int)$field_type === 4){
				if($input_value){
					$input_value = date('d-m-Y',strtotime($input_value));
					if($input_value === "01-01-1970"){
						$input_value = date('d-m-Y');
					}
				}
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else
			if((int)$field_type === 9){
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
				$pick_query = 'select '.$auto_dispaly_value.' from '.$pick_table.' where '.$auto_prime_id.' = "'.$input_value.'" and trans_status = 1';
				$pick_data   = $this->runQuery("$pick_query");
				$pick_result = $this->result($pick_data);
				$input_value = $pick_result[0]->$auto_dispaly_value;
				$label_name  = $label_name."_hidden_".$prime_form_id;
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else
			if((int)$field_type === 13){
				if($input_value){
					$input_value = date('d-m-Y H:i:s',strtotime($input_value));
					if($input_value === "01-01-1970 00:00:00"){
						$input_value = date('d-m-Y');
					}
				}
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else
			if((int)$field_type === 8){//textbox only
				if($input_value){
					$input_value = str_replace("xdbquot",'"',$input_value);
					$input_value = str_replace("xquot","'",$input_value);
					$input_value = str_replace("xxamp","&",$input_value);
				}
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}else{
				$rslt_info[$label_name] = array('input_value'=>$input_value,'field_type'=>$field_type);
			}
		}
		$prime_id = $row_result[0]->$table_prime_id;
		$row_set_qry    = 'select * from cw_custom_design inner join cw_form_setting on cw_form_setting.label_name=cw_custom_design.label_name and cw_form_setting.prime_module_id=cw_custom_design.prime_module_id and cw_form_setting.input_for=cw_custom_design.input_for where cw_custom_design.prime_module_id="'.$this->control_name.'" and cw_custom_design.input_view =3 and cw_custom_design.trans_status = "1" order by abs(field_sort)';
		$row_set_info   = $this->runQuery("$row_set_qry");
		$row_set_result = $this->result($row_set_info);
		$row_view_list = array();
		foreach($row_set_result as $row_result){
			$view_id      = $row_result->input_for;
			$row_set_data = $this->get_row_set_data($view_id,$module_id,$prime_id);
			$row_view_list[$view_id] = $row_set_data;
		}
		$form_result = array('rslt_info'=>$rslt_info,'row_view_list'=>$row_view_list);
		return $form_result;
	}
	public function walk_status($mobile_number){
		$new_filed ="<select class='form-control input-sm valid' name='walk_status' id='walk_status'>
						<option>---Select---</option>
						<option>Reached</option>
					</select>";
		return $new_filed;
	}
}
?>
