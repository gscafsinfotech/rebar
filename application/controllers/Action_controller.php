<?php
/**********************************************************
	   Filename: Action Controller
	Description: Action Controller for all dynamic module controller.
		 Author: udhayakumar Anandhan
	 Created on: ?30 ?December ?2019
	Reviewed by:
	Reviewed on:
	Approved by:
	Approved on:
	-------------------------------------------------------
	Modification Details
	Changed by:
	Change Info:
	-------------------------------------------------------
***********************************************************/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("Secure_Controller.php");
abstract class Action_controller extends Secure_Controller{
	public $control_name;
	public $logged_id;
	public $logged_role;
	public $logged_consultancy;
	public $logged_dept;
	public $prime_id;
	public $prime_table;
	public $financial_info;
	public $base_query      = "" ;
	public $quick_link      = array();
	public $table_head      = array();
	public $view_info       = array();
	public $form_info       = array();
	public $role_condition;
	public $select_query    = "";
	public $view_select     = "";
	public $all_pick        = array();
	public $master_pick     = array();
	
	public function __construct($module_id = NULL){
		parent::__construct($module_id);
		echo $this->create_formula_file();
	}
	
	//PROVIDE BASE DATA FOR MODULE
	public function collect_base_info(){		
		$this->control_name       = strtolower($this->router->fetch_class());
		$this->logged_id          = $this->session->userdata('logged_id');
		$this->logged_role        = $this->session->userdata('logged_role');
		$this->logged_consultancy = $this->session->userdata('logged_consultancy');
		$this->logged_dept        = $this->session->userdata('logged_dept');
		$this->logged_user_role   = $this->session->userdata('logged_user_role');
		$this->prime_id           = "prime_".$this->control_name."_id";
		$this->prime_table        = $this->db->dbprefix($this->control_name);
		$this->base_query         = "select @SELECT@ from $this->prime_table";
		$this->select_query       = "$this->prime_table.$this->prime_id,";
		$this->get_quick_link();
		$this->get_table_head();		
		$this->get_view_info();
		$this->get_form_info();
		$this->get_role_condition();
		$this->get_query_and_drop();
		$this->get_condition();		
	}
	
	/* ==============================================================*/
	/* =================== BASE FUNCTIONS - START ===================*/
	/* ==============================================================*/
	// PROVIDE QUICK LINK LIST VIEW
	public function get_quick_link(){
		$link_query   = 'select quicklink from cw_modules  where module_id = "'.$this->control_name.'"';
		$link_info    = $this->db->query("CALL sp_a_run ('SELECT','$link_query')");
		$link_result  = $link_info->result();
		$link_info->next_result();
		$this->quick_link = $link_result[0];
	}
	
	// PROVIDE TABLE VIEW
	public function get_table_head(){
		$table_query = 'select label_name,view_name,field_type from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and input_view_type IN (1,2) and table_show = "1" and trans_status = "1" and FIND_IN_SET("'.$this->logged_role.'",field_for) ORDER BY table_sort asc';
		$table_info = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
		$result   = $table_info->result();
		$table_info->next_result();
		$this->table_head    = $result;
		$select_key          = array_column($result, "label_name");
		$this->select_query .= implode(",",$select_key);
	}
	
	// PROVIDE MODLE VIEWS
	public function get_view_info(){
		$view_query = 'select * from cw_form_view_setting  where prime_view_module_id = "'.$this->control_name.'" and form_view_show = "1" and trans_status = "1" and FIND_IN_SET("'.$this->logged_role.'",form_view_for) ORDER BY form_view_sort asc';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$this->view_info = $view_result;
	}
	
	// PROVIDE MODLE FORM INPUT VIEWS
	public function get_form_info(){
		$from_query = 'select * from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and field_show = "1" and trans_status = "1" and FIND_IN_SET("'.$this->logged_role.'",field_for) ORDER BY input_for,field_sort asc';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$from_query')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$this->form_info = $form_result;
	}
	// PROVIDE MODLE TABLE ROLE BASED SEARCH
	public function get_role_condition(){
		$table_search_query = 'select where_condition from cw_form_table_search  where query_module_id = "'.$this->control_name.'" and query_for = "'.$this->logged_user_role.'" and trans_status = "1"';
		$table_search_data   = $this->db->query("CALL sp_a_run ('SELECT','$table_search_query')");
		$table_search_result = $table_search_data->result();
		$table_search_data->next_result();
		if($table_search_result){
			$where_condition  = str_replace('^','"',$table_search_result[0]->where_condition);
			$session_query  = 'select session_value from cw_session_value  where session_for = 1 and trans_status = "1"';
			$session_data   = $this->db->query("CALL sp_a_run ('SELECT','$session_query')");
			$session_result = $session_data->result();
			$session_data->next_result();
			foreach($session_result as $rslt){
				$session_value 	   = $rslt->session_value;
				if($session_value !== "access_data"){
					$saved_session_val = $this->session->userdata($session_value);
					$exist_val = "@".$session_value."@";
					$where_condition  = str_replace($exist_val,$saved_session_val,$where_condition);
				}
			}
			$this->role_condition = $where_condition;
		}
	}
	// PROVIDE ROLE BASED PICK LIST /*UDY-13-02-2020*/
	public function get_role_based_picklist($query_list_id,$module_id){		
		$pick_query = 'select pick_where_condition from cw_pick_base_search  where pick_module_id = "'.$module_id.'" and query_list_id = "'.$query_list_id.'" and pick_query_for = "'.$this->logged_role.'" and trans_status = "1"';
		$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
		$pick_result = $pick_data->result();
		$pick_data->next_result();
		$where_condition = "";
		if($pick_result){
			$where_condition  = str_replace('^','"',$pick_result[0]->pick_where_condition);
			$session_query  = 'select session_value from cw_session_value  where session_for = 1 and trans_status = "1"';
			$session_data   = $this->db->query("CALL sp_a_run ('SELECT','$session_query')");
			$session_result = $session_data->result();
			$session_data->next_result();
			foreach($session_result as $rslt){
				$session_value 	   = $rslt->session_value;
				if($session_value !== "access_data"){
					$saved_session_val = $this->session->userdata($session_value);
					$exist_val = "@".$session_value."@";
					$where_condition  = str_replace($exist_val,$saved_session_val,$where_condition);
				}
			}
		}
		return $where_condition;
	}	
	//PROVIDE SEARCH AND VIEW SELECT QUERY, DROPDOWN VALUES AND SEARCH FLITERS
	public function get_query_and_drop(){
		$this->view_select  = "$this->prime_table.$this->prime_id,";
		foreach($this->form_info as $setting){
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
			$default_value      = (int)$setting->default_value;
			if($default_value === 0){
				$default_value = "";
			}
			$pick_drop   = array();
			$pick_master = array();
			$pick_key    = array();
			$pick_val    = array();
			$final_pick  = array();
			if($field_isdefault === 1){				
				if(($field_type === 5) || ($field_type === 7)){
					$where_condition = $this->get_role_based_picklist($prime_form_id,$prime_module_id);
					if($pick_list_type === 1){
						$pick_list_val   = explode(",",$pick_list);
						$pick_list_val_1 = $pick_list_val[0];
						$pick_list_val_2 = $pick_list_val[1];
						if($pick_table === "cw_category"){
							$pick_query = "select $pick_list from $pick_table where trans_status = 1 and prime_category_id != 1 $where_condition";
						}else{
							$pick_query = "select $pick_list from $pick_table where trans_status = 1 $where_condition";
						}						
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						if($pick_result){
							$pick_key   = array_column($pick_result, $pick_list_val_1);
							$pick_val   = array_column($pick_result, $pick_list_val_2);
							$final_pick = array_combine( $pick_key, $pick_val);
						}
						$final_pick = array("" => "---- $label_name ----") + $final_pick;
						$this->master_pick[$label_id]   = $final_pick;
						 //array_unshift($final_pick,"---- $label_name ----");
						$this->all_pick[$prime_form_id] = $final_pick;
					}else
					if($pick_list_type === 2){
						$pick_list_val_1 = $pick_table."_id";
						$pick_list_val_2 = $pick_table."_value";
						$pick_list_val_3 = $pick_table."_status";
						
						$pick_query = "select $pick_list_val_1,$pick_list_val_2 from $pick_table where $pick_list_val_3 = 1";
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						if($pick_result){
							$pick_key   = array_column($pick_result, $pick_list_val_1);
							$pick_val   = array_column($pick_result, $pick_list_val_2);				
							$final_pick = array_combine( $pick_key, $pick_val);
						}
						$final_pick = array("" => "---- $label_name ----") + $final_pick;
						$this->master_pick[$label_id]   = $final_pick;
						//array_unshift($final_pick,"---- $label_name ----");
						$this->all_pick[$prime_form_id] = $final_pick;
					}
				}else
				if($field_type === 9){
					$pick_query = "select $auto_prime_id,$auto_dispaly_value from $pick_table where trans_status = 1";
					$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
					$pick_result = $pick_data->result();
					$pick_data->next_result();
					if($pick_result){
						$pick_key   = array_column($pick_result, $auto_prime_id);
						$pick_val   = array_column($pick_result, $auto_dispaly_value);
						$final_pick = array_combine( $pick_key, $pick_val);
					}
					$this->master_pick[$label_id]   = $final_pick;
				}
				if(($input_view_type === 1) || ($input_view_type === 2)){
					$this->view_select  .= "$this->prime_table.$label_id,";
					if($search_show === 1){						
						$this->fliter_list[] = array('label_id'=> $label_id, 'label_name'=> $label_name, 'field_isdefault'=> $field_isdefault, 'array_list'=> $final_pick, 'field_type'=> $field_type);
					}
				}
			}
		}
		$this->view_select   = rtrim($this->view_select,',');
		// ONLY FOR EMPLOYEE
		if($this->control_name === "employees"){
			$this->view_select   = $this->view_select .',user_name,password';
		}
	}
	// PROVIDE MODLE ONLOAD CONDITION & FORMULA
	public function get_condition(){
		$condition_query = 'select * from cw_form_condition_formula  where cond_module_id = "'.$this->control_name.'" and trans_status = "1" and FIND_IN_SET("'.$this->logged_role.'",condition_for)';
		$condition_data   = $this->db->query("CALL sp_a_run ('SELECT','$condition_query')");
		$condition_result = $condition_data->result();		
		$condition_data->next_result();
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
				$cond_from_query = 'select field_type,label_name from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and label_name in ("'.$label_name.'") ORDER BY input_for,field_sort asc';
				$cond_form_data   = $this->db->query("CALL sp_a_run ('SELECT','$cond_from_query')");
				$cond_form_result = $cond_form_data->result();
				$cond_form_data->next_result();
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
			
			//BIND VALUE FROM DATABASE TABLE
			if($condition_type === 1){
				if($on_bind_input){
					$fill_input = "";
					foreach($condition_bind_to as $bind_to){
						$fill_val    = "ui.item.".$bind_to;
						$fill_input .= "$('#$bind_to').val($fill_val);\n";
					}
					$send_url     = site_url("$this->control_name/bind_autocomplete_suggest");
					$load_script .= "$('$on_bind_input').autocomplete({
										 source: function(request, response) {
											$.getJSON('$send_url',{term:request.term, prime_cond_id:$prime_cond_id },response);
										},
										minChars:2,
										autoFocus: true,
										delay:10,
										appendTo: '.modal-content',
										select: function(e, ui) {
											$fill_input
											return false;
										}
									});\n";
				}
				if($on_change_input){
					$fill_input = "";
					foreach($condition_bind_to as $bind_to){
						$fill_val    = "rslt[0].".$bind_to;
						$fill_input .= "$('#$bind_to').val($fill_val);\n";
					}
					
					$send_url  = site_url("$this->control_name/bind_change_suggest");
					$load_script .= "$('$on_change_input').change(function(){
										var isValid = true;
										$('$on_change_input').each(function() {
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
													var rslt = JSON.parse(data);
													$fill_input
												}
											});
										}
									});\n";
				}
			}else
			if($condition_type === 2){
				$fill_input = "";
				foreach($condition_bind_to as $bind_to){
					$fill_val    = "rslt.".$bind_to;
					if($this->control_name === 'employees'){
						$fill_input .= "$('#$bind_to').val($fill_val);";
					}else{
						$fill_input .= "$('#$bind_to').val($fill_val);\n $('#$bind_to').trigger('change');";
					}
				}
				$send_url  = site_url("$this->control_name/calculation_suggest");
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
												var rslt = JSON.parse(data);
												$fill_input
											}
										});
									}
								}";
 				if($on_bind_input){
					if($this->control_name === 'employees'){
						$load_script .= "$('$on_bind_input').bind('keyup change', function(e) {
											$condition_label_name();
										});\n";
					}else{
						$load_script .= "$('$on_bind_input').bind('keyup blur change', function(e) {
											$condition_label_name();
										});\n";
					}
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
		if($load_script){
			$this->condition_list[] = $load_script;
		}
	}
	/* ==============================================================*/
	/* =================== BASE FUNCTIONS - END ===================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================= CONDITION OPERATION - START ================*/
	/* ==============================================================*/
	//PROVIDE ALL SINGLE BOX AUTOCOMPLETE DROP DOWN
	public function suggest(){
		$search_term    = $this->input->post_get('term');
		$prime_form_id  = $this->input->get('prime_form_id');
		$form_query     = 'select * from cw_form_setting where prime_form_id = "'.$prime_form_id.'"';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$form_query')");
		$form_result = $form_data->result();
		$form_data->next_result();
		if($form_result){
			$pick_table          = $form_result[0]->pick_table;
			$pick_list           = $form_result[0]->pick_list;
			$auto_prime_id       = $form_result[0]->auto_prime_id;
			$auto_dispaly_value  = $form_result[0]->auto_dispaly_value;
			
			$auto_list = "CONCAT(".str_replace(",",'," - ",',$pick_list).") as auto_list";
			$suggest_query = "select $auto_prime_id,$auto_dispaly_value,$auto_list from $pick_table where ";
			$col_list      = explode(",",$pick_list);
			$where_query  = "";
			foreach($col_list as $col){
				$where_query .= $col.' like "'.$search_term.'%" or ';
			}
			$where_query    = rtrim($where_query," or ");
			$suggest_query .= $where_query;
			$suggest_data   = $this->db->query("CALL sp_a_run ('SELECT','$suggest_query')");
			$suggest_result = $suggest_data->result();
			$suggest_data->next_result();
			foreach($suggest_result as $result){
				$suggest_prime_id  = $result->$auto_prime_id;
				$suggest_dispaly   = $result->$auto_dispaly_value;
				$suggest_list      = $result->auto_list;
				$suggestions[] = array('value' => $suggest_prime_id, 'label' => $suggest_list, 'display_name' => $suggest_dispaly);
			}
		}
		if(empty($suggestions)){
			$suggestions[] = array('value' => "0", 'label' => "No data found for this search");
		}
		echo json_encode($suggestions);
	}
	
	//PROVIDE AUTOCOMPLETE DROP DOWN TO FILL MULTI INPUT BOX
	public function bind_autocomplete_suggest(){
		$search_term      = $this->input->post_get('term');
		$prime_cond_id    = $this->input->post_get('prime_cond_id');
		echo $this->get_bind_final_query("autocomplete",$search_term,$prime_cond_id);
	}
	
	//PROVIDE DATA WHILE ON CHANGE EVENT TO FILL MULTI INPUT BOX
	public function bind_change_suggest(){
		$for_input        = $this->input->post_get('for_input');
		$prime_cond_id    = $this->input->post_get('prime_cond_id');
		$search_term      = $this->input->post_get($for_input);
		echo $this->get_bind_final_query("change",$search_term,$prime_cond_id);
	}
	// UDY NEED TO REVIEW
	//PROVIDE QUERY AND RESULT ARRAY TO BIND INPUT
	public function get_bind_final_query($from,$search_term,$prime_cond_id){
		/*============ 
			NOTE:  VAR FROM NOT USED MAY CAN USE IN FURTURE PROCESS
		============*/
		$cond_query = 'select * from cw_form_condition_formula  where prime_cond_id = "'.$prime_cond_id.'" and trans_status = 1';
		$cond_data   = $this->db->query("CALL sp_a_run ('SELECT','$cond_query')");
		$cond_result = $cond_data->result();
		$cond_data->next_result();
		$final_qry = "";
		if($cond_result){
			$cond_module_id       = $cond_result[0]->cond_module_id;
			$condition_check_form = $cond_result[0]->condition_check_form;
			$condition_bind_to    = $cond_result[0]->condition_bind_to;
			$condition_table      = $cond_result[0]->condition_table;
			
			$table_query = 'select * from cw_form_table_cond_for  where table_cond_id = "'.$prime_cond_id.'" ORDER BY abs(line_sort) asc';
			$table_data   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
			$table_result = $table_data->result();
			$table_data->next_result();
			$line_table_query = "";
			foreach($table_result as $table){
				$line_prime_table = $table->line_prime_table;
				$line_prime_col   = $table->line_prime_col;
				$line_join_type   = $table->line_join_type;
				$line_join_table  = $table->line_join_table;
				$line_join_col    = $table->line_join_col;
				$line_sort        = $table->line_sort;
				
				$module_name      = str_replace("cw_","",$line_prime_table);
				$prime_id         = "prime_".$module_name."_id";
				//$cf_id            = "prime_".$module_name."_cf_id";
				//$cf_table_name    = $this->db->dbprefix($module_name."_cf");
				
				$join_module_name      = str_replace("cw_","",$line_join_table);
				$join_prime_id         = "prime_".$join_module_name."_id";
				//$join_cf_id            = "prime_".$join_module_name."_cf_id";
				//$join_cf_table_name    = $this->db->dbprefix($join_module_name."_cf");
				//inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id
				//inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id
				//inner join $cf_table_name on $line_prime_table.$prime_id = $cf_table_name.$prime_id
				if((int)$line_sort === 1){
					$line_table_query .= " $line_prime_table $line_join_type join $line_join_table on $line_join_col = $line_prime_col "; 
				}else{
					$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col"; 
				}
			}
			if(!$line_table_query){
				$module_name      = str_replace("cw_","",$condition_table);
				$prime_id         = "prime_".$module_name."_id";
				//$cf_id            = "prime_".$module_name."_cf_id";
				//$cf_table_name    = $this->db->dbprefix($module_name."_cf");			
				//inner join $cf_table_name on $condition_table.$prime_id = $cf_table_name.$prime_id
				$line_table_query = " $condition_table ";
			}
			
			
			$for_input_query = 'select * from cw_form_for_input  where input_for_cond_id = "'.$prime_cond_id.'" and trans_status = 1';
			$for_input_data   = $this->db->query("CALL sp_a_run ('SELECT','$for_input_query')");
			$for_input_result = $for_input_data->result();
			$for_input_data->next_result();
			$for_line_input_query = "";
			foreach($for_input_result as $for_input){
				$line_input_for       = $for_input->line_input_for;
				$line_input_for_table = $for_input->line_input_for_table;
				$line_input_for_col   = $for_input->line_input_for_col;	
								
				$for_line_input_query .= $line_input_for_col.' like "'.$search_term.'%" or ';
			}
			if($for_line_input_query){
				$for_line_input_query  = " where ". rtrim($for_line_input_query," or ");
			}
			
			$input_query = 'select * from cw_form_bind_input  where input_cond_id = "'.$prime_cond_id.'" and trans_status = 1';
			$input_data   = $this->db->query("CALL sp_a_run ('SELECT','$input_query')");
			$input_result = $input_data->result();
			$input_data->next_result();
			$line_input_query = "";
			foreach($input_result as $input){
				$line_input_bind_to    = $input->line_input_bind_to;
				$line_input_bind_table = $input->line_input_bind_table;
				$line_input_bind_col   = $input->line_input_bind_col;
				
				$select_query  = 'select field_type from cw_form_setting  where prime_module_id = "'.$cond_module_id.'" and label_name = "'.$line_input_bind_to.'"';
				$select_data   = $this->db->query("CALL sp_a_run ('SELECT','$select_query')");
				$select_result = $select_data->result();
				$select_data->next_result();
				$field_type = (int)$select_result[0]->field_type;
				if(($field_type === 4) || ($field_type === 13)){
					$line_input_query .= 'DATE_FORMAT('.$line_input_bind_col.',"%d-%m-%Y") as '.$line_input_bind_to.',';
				}else{
					$line_input_query .= "$line_input_bind_col as $line_input_bind_to,";
				}
			}
			if($line_input_query){
				$line_input_query  = rtrim($line_input_query,',');
			}else{
				$line_input_query  = " * ";
			}
			$final_qry = "select $line_input_query from $line_table_query $for_line_input_query";
		}
		if($final_qry){
			$final_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
			$final_result = $final_data->result();
			$final_data->next_result();
			foreach($final_result as $rslt){
				$line = array();
				$lable = "";
				foreach($input_result as $input){
					$line_input_bind_to    = $input->line_input_bind_to;
					$rslt_val = $rslt->$line_input_bind_to;
					$line[$line_input_bind_to] =  $rslt_val;
					if($rslt_val){
						$lable .= "$rslt_val - ";
						}
				}
				$lable  = rtrim($lable," - ");
				$line['value'] = '';
				$line['label'] = $lable;
				$suggestions[] = $line;
			}
		}
		if(empty($suggestions)){
			$suggestions[] = array('value' => "0", 'label' => "No data found for this search");
		}
		return json_encode($suggestions);
	}
	
	//PROVIDE DATA FOR  ONCHANGE CALCUATION
	public function calculation_suggest(){
		$for_input        = $this->input->post_get('for_input');
		$prime_cond_id    = $this->input->post_get('prime_cond_id');		
		$cond_query = 'select * from cw_form_condition_formula  where prime_cond_id = "'.$prime_cond_id.'" and trans_status = 1';
		$cond_data   = $this->db->query("CALL sp_a_run ('SELECT','$cond_query')");
		$cond_result = $cond_data->result();
		$cond_data->next_result();		
		$condition_check_form = explode(",",$cond_result[0]->condition_check_form);
		$condition_bind_to    = $cond_result[0]->condition_bind_to;
		$condition_table      = $cond_result[0]->condition_table;
		$condition_type       = $cond_result[0]->condition_type;
		$is_drop_down         = (int)$cond_result[0]->is_drop_down;
		$cond_drop_down       = $cond_result[0]->cond_drop_down;
		$fianl_result_array = array();
		if($is_drop_down === 1){
			$search_term          = $this->input->post_get($cond_drop_down);
			$input_query = 'select * from cw_form_bind_input  where input_cond_id = "'.$prime_cond_id.'" and line_input_bind_table = "'.$search_term.'"';
			$input_data   = $this->db->query("CALL sp_a_run ('SELECT','$input_query')");
			$input_result = $input_data->result();
			$input_data->next_result();
			$line_input_bind_col = "";
			foreach($input_result as $input){
				$line_input_bind_to    = $input->line_input_bind_to;
				$line_input_bind_col   = $input->line_input_bind_col;
				$line_input_bind_col  = str_replace("~","'",$line_input_bind_col);
				$line_input_bind_col  = str_replace("!",'"',$line_input_bind_col);
				if($line_input_bind_col){
					foreach($condition_check_form as $check_form){
						if(strpos($line_input_bind_col,"@$check_form@") !== false) {
								$value = $this->input->post_get($check_form);
								if(strpos($check_form,"date") !== false) {
									$value = new DateTime($value);
									$value = $value->format("Y-m-d");
									$value = "'$value'";
								}
							$line_input_bind_col = str_replace("@$check_form@",$value, $line_input_bind_col);
						}
					}
					$dynamic_file_name= $line_input_bind_to."_".$this->logged_id.".php";
					unlink("$dynamic_file_name");
					$fname = $line_input_bind_to."(){";
					$code = "<?php function $fname $line_input_bind_col }?>";
					fopen("$dynamic_file_name", "w");
					file_put_contents("$dynamic_file_name",$code);
					require_once("$dynamic_file_name");
					$fianl_result_array[$line_input_bind_to] =  $line_input_bind_to();
					unlink("$dynamic_file_name");
				}
			}
		}else{
			$input_query = 'select * from cw_form_bind_input  where input_cond_id = "'.$prime_cond_id.'"';
			$input_data   = $this->db->query("CALL sp_a_run ('SELECT','$input_query')");
			$input_result = $input_data->result();
			$input_data->next_result();
			$line_input_bind_col = "";
			foreach($input_result as $input){
				$line_input_bind_to    = $input->line_input_bind_to;
				$line_input_bind_col   = $input->line_input_bind_col;
				$line_input_bind_col  = str_replace("~","'",$line_input_bind_col);
				$line_input_bind_col  = str_replace("!",'"',$line_input_bind_col);
				if($line_input_bind_col){
					foreach($condition_check_form as $check_form){
						if(strpos($line_input_bind_col,"@$check_form@") !== false) {
								$value = $this->input->post_get($check_form);
								if(strpos($check_form,"date") !== false) {
									$value = new DateTime($value);
									$value = $value->format("Y-m-d");
									$value = "'$value'";
								}
							$line_input_bind_col = str_replace("@$check_form@",$value, $line_input_bind_col);
						}
					}
					$dynamic_file_name= $line_input_bind_to."_".$this->logged_id.".php";
					unlink("$dynamic_file_name");
					$fname = $line_input_bind_to."(){";
					$code = "<?php function $fname $line_input_bind_col }?>";
					fopen("$dynamic_file_name", "w");
					file_put_contents("$dynamic_file_name",$code);
					require_once("$dynamic_file_name");
					$fianl_result_array[$line_input_bind_to] =  $line_input_bind_to();
					unlink("$dynamic_file_name");
				}
			}
		}
		echo json_encode($fianl_result_array);
	}
	
	// UDY NEED TO REVIEW
	/* ==============================================================*/
	/* ================= CONDITION OPERATION - END ==================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================== ROWSET OPERATION - START ==================*/
	/* ==============================================================*/
	// ROWSET SAVE
	public function rowset_save(){
		$view_id         = $this->input->post('view_id');
		$module_id       = $this->input->post('module_id');
		$row_prime_id    = (int)$this->input->post('row_prime_id');
		$row_label_name  =  $this->input->post('row_label_name');
		$prime_id        = (int)$this->input->post('prime_id');
		
		$table_name      = $module_id."_".$row_label_name;
		$table_prime     = "prime_".$table_name."_id";
		$table_name      = $this->db->dbprefix($table_name);
		$prime_qry_key   = "prime_".$module_id."_id,";
		$prime_qry_value = '"'.$prime_id.'",';
		$prime_upd_query = "";

		$form_qry  = 'select * from cw_form_setting where prime_module_id = "'.$module_id.'" and  input_for = "'.$view_id.'" and  field_show = "1" and trans_status = 1';
		$form_data = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
		$form_result = $form_data->result();
		$form_data->next_result();
		if($module_id === 'employees' && $row_prime_id > 0){
			$row_set_log = array();
		}
		$labelid_for_approval = "";
		$value_for_approval   = "";
		$approval_update 	  = "";
		foreach($form_result as $setting){
			$field_type      = $setting->field_type;
			$input_view_type = (int)$setting->input_view_type;
			$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
			$field_isdefault = $setting->field_isdefault;
			$prime_module_id = $setting->prime_module_id;
			if((int)$field_type === 7){
				$logged_team	      = $this->session->userdata('logged_team');
				if($label_id === "team_log"){
					$value  = $logged_team;
				}else{
					$multi_name = $label_id."[]";
					$value = implode(",",$this->input->post($multi_name));
				}
			}else{
				if($prime_module_id === "team_target"){
					if($label_id === "detailer_name"){
						$detailer_name = $this->input->post($label_id);
					}else
					if($label_id === "target_value"){
						$target_value = $this->input->post($label_id);
					}
				}
				if($label_id === 'client_name' || $label_id === 'project' || $label_id === 'drawing_no' || $label_id === 'tonnage' || $label_id === 'actual_tonnage' || $label_id === 'billable_hours' || $label_id === 'non_billable_hours' || $label_id === 'actual_billable_time'){
					$labelid_for_approval  .= $label_id.",";
					$value_for_approval    .= '"'.$this->input->post($label_id).'",';
					$approval_update       .= $label_id.' = "'.$this->input->post($label_id).'",';
				}else
				if($label_id === 'work_status'){
					$work_status = $this->input->post($label_id);
				}else
				if($label_id === 'entry_type'){
					$entry_type  = $this->input->post($label_id);
				}
				if($label_id === 'work_type'){
					$work_type   = $this->input->post($label_id);
				}
				$value = $this->input->post($label_id);
			}			
			if((int)$field_type === 4){
				$value = date('Y-m-d',strtotime($value));
			}else
			if((int)$field_type === 13){
				$value = date('Y-m-d H:i:s',strtotime($value));
			}			
			$prime_qry_key          .= $label_id.",";
			$prime_qry_value        .= '"'.$value.'",';
			$prime_upd_query        .= $label_id.' = "'.$value.'",';
			$exist_qry              .= $label_id.' = "'.$value.'" and ';
			if($module_id === 'employees' && $row_prime_id > 0){
				$row_set_log[$label_id]  = $value;
			}
		}
		$created_on  = date("Y-m-d h:i:s");
		$exist_count = 0;
		if($module_id === 'employees' && $row_prime_id > 0){
			$this->update_row_set_log($row_prime_id,$prime_id,$view_id,$module_id."_".$row_label_name,$row_set_log);
		}
		if($prime_module_id === "team_target"){
			$target_date_qry     = 'select from_date,to_date from cw_team_target where prime_team_target_id = "'.$prime_id.'" and trans_status = 1';
			$target_date_info    = $this->db->query("CALL sp_a_run ('SELECT','$target_date_qry')");
			$target_date_result  = $target_date_info->result();
			$target_date_info->next_result();
			$from_date 		= $target_date_result[0]->from_date;
			$to_date   		= $target_date_result[0]->to_date;
			$to_date 		= date('Y-m-d', strtotime($to_date .' +1 day'));
			$target_qry     = 'select count(*) as rlst_count,prime_team_target_detailer_wise_target_id from cw_team_target_detailer_wise_target inner join cw_team_target on cw_team_target.prime_team_target_id = cw_team_target_detailer_wise_target.prime_team_target_id where cw_team_target_detailer_wise_target.detailer_name = "'.$detailer_name.'" and cw_team_target.from_date >= "'.$from_date.'" and cw_team_target.to_date <= "'.$to_date.'" and cw_team_target_detailer_wise_target.trans_status = 1';
			$target_info    = $this->db->query("CALL sp_a_run ('SELECT','$target_qry')");
			$target_result  = $target_info->result();
			$target_info->next_result();
			$rlst_count 	= $target_result[0]->rlst_count;
			$row_get_id 	= $target_result[0]->prime_team_target_detailer_wise_target_id;
		}
		// die;
		if((int)$row_prime_id === 0){
			if($prime_module_id === "team_target"){
				if((int)$rlst_count === 0){
					$prime_qry_key     .= "trans_created_by,trans_created_date";
					$prime_qry_value   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
					$prime_insert_query = "insert into $table_name ($prime_qry_key) values ($prime_qry_value)";
					$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
					$insert_result      = $insert_info->result();
					$insert_info->next_result();
					$insert_id = $insert_result[0]->ins_id;
					$row_set_data = $this->get_row_set_data($view_id,$prime_id);
					echo json_encode(array('success' => TRUE, 'message' => "Successfully added", 'insert_id' => $insert_id, 'row_set_data' => $row_set_data));
				}else{
					echo json_encode(array('success' => false, 'message' => "Detailer Already Exist"));
				}
			}else
			if($prime_module_id === "time_sheet"){
				$prime_qry_key     .= "trans_created_by,trans_created_date";
				$prime_qry_value   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
				$prime_insert_query = "insert into $table_name ($prime_qry_key) values ($prime_qry_value)";
				$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
				$insert_result      = $insert_info->result();
				$insert_info->next_result();
				$insert_id 			= $insert_result[0]->ins_id;
				$row_set_data 		= $this->get_row_set_data($view_id,$prime_id);
				$logged_role 	    = $this->session->userdata('logged_role');
				if((int)$logged_role === 5){
					if((int)$work_type === 1 || (int)$work_type === 2){ 
						if((int)$work_status === 3 && (int)$entry_type === 2){
							$labelid_for_approval .= "work_type,detailer_name,team_leader_name,team,prime_time_sheet_time_line_id,trans_created_by,trans_created_date";
							$value_for_approval   .= '"'.$work_type.'","'.$this->session->userdata('logged_emp_code').'","'.$this->session->userdata('logged_reporting').'","'.$this->session->userdata('logged_team').'","'.$insert_id.'",'.'"'.$this->logged_id.'",'.'"'.$created_on.'"';
							$approval_query 	   = "insert into cw_tonnage_approval ($labelid_for_approval) values ($value_for_approval)";
							$approval_info         = $this->db->query("CALL sp_a_run ('INSERT','$approval_query')");
							$approval_result       = $approval_info->result();
							$approval_info->next_result();
						}
					}
				}
				echo json_encode(array('success' => TRUE, 'message' => "Successfully added", 'insert_id' => $insert_id, 'row_set_data' => $row_set_data));
			}
			else{
				$prime_qry_key     .= "trans_created_by,trans_created_date";
				$prime_qry_value   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
				$prime_insert_query = "insert into $table_name ($prime_qry_key) values ($prime_qry_value)";
				$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
				$insert_result      = $insert_info->result();
				$insert_info->next_result();
				$insert_id = $insert_result[0]->ins_id;
				$row_set_data = $this->get_row_set_data($view_id,$prime_id);
				echo json_encode(array('success' => TRUE, 'message' => "Successfully added", 'insert_id' => $insert_id, 'row_set_data' => $row_set_data));
			}
		}else{
			if($prime_module_id === "team_target"){
				if((int)$rlst_count === 0){
					$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
					$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
					$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
					$row_set_data = $this->get_row_set_data($view_id,$prime_id);
					echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
				}else{
					if((int)$row_get_id === (int)$row_prime_id){
						$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
					$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
					$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
					$row_set_data = $this->get_row_set_data($view_id,$prime_id);
					echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
					}else{
						echo json_encode(array('success' => false, 'message' => "Detailer Already Exist"));
					}
				}
			}else
			if($prime_module_id === "time_sheet"){
				$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
				$approval_update    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
				$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
				$row_set_data = $this->get_row_set_data($view_id,$prime_id);
				$update_query  = "UPDATE cw_tonnage_approval SET ". $approval_update .' WHERE prime_time_sheet_time_line_id = "'. $row_prime_id .'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$update_query')");
				echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
			}
			else{
				$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
				$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
				$row_set_data = $this->get_row_set_data($view_id,$prime_id);
				echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
			}
		}
	}	
	//ROW SET EDIT DATA
	public function row_set_edit(){
		$row_id          = (int)$this->input->post('row_id');
		$view_id         = (int)$this->input->post('view_id');
		$table_name      = $this->input->post('table_name');
		$table_prime_id  =	 "prime_".$table_name."_id";
		$table_name      = $this->db->dbprefix($table_name);		
		
		$final_qry  = "select * from $table_name " .' where '.$table_prime_id.' = "'.$row_id.'" and  trans_status = "1"';
		$row_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		$row_result = $row_data->result();
		$row_data->next_result();
		
		$form_qry    = 'select * from cw_form_setting where prime_module_id = "'.$this->control_name.'" and  input_for = "'.$view_id.'" and  input_view_type = "3" and trans_status = "1"';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
		$form_result = $form_data->result();
		$form_data->next_result();
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
			if((int)$field_type === 13){
				$input_value = date('d-m-Y H:i:s',strtotime($input_value));
				if(strpos($input_value, '01-01-1970') !== false) {
					$input_value = date("d-m-Y H:i:s");
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
	public function row_set_remove(){
		$row_id          = (int)$this->input->post('row_id');
		$view_id         = (int)$this->input->post('view_id');
		$table_name      = $this->input->post('table_name');
		$prime_id        = $this->input->post('prime_id');
		$table_prime_id  =	 "prime_".$table_name."_id";
		$table_name      = $this->db->dbprefix($table_name);
		$logged_id     = $this->session->userdata('logged_id');		
		$today_date = date("Y-m-d h:i:s");
		$final_qry = 'UPDATE '.$table_name.' SET trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" , trans_status = 0 WHERE '.$table_prime_id.' = "'.$row_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		if($table_name === "cw_time_sheet_time_line"){
			$approve_qry = 'UPDATE cw_tonnage_approval SET trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" , trans_status = 0 WHERE '.$table_prime_id.' = "'.$row_id.'"';
			$this->db->query("CALL sp_a_run ('SELECT','$approve_qry')");
		}
		$row_set_data = $this->get_row_set_data($view_id,$prime_id);
		echo json_encode(array('success' => TRUE, 'msg' => "Remove Successfully",'row_set_data' => $row_set_data));
	}
	//PROVIDE ROWSET DATA BY ID
	public function get_row_set_data($view_id,$prime_id){
		$view_qry    = 'select * from cw_form_view_setting where prime_form_view_id = "'.$view_id.'" and prime_view_module_id = "'.$this->control_name.'" and  form_view_type = "3" and trans_status = 1';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_qry')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$prime_form_view_id   = $view_result[0]->prime_form_view_id;
		$prime_view_module_id = $view_result[0]->prime_view_module_id;
		$form_view_label_name = $view_result[0]->form_view_label_name;
		
		$div_id       	 = $form_view_label_name."_div_".$prime_form_view_id;
		$table_id        = $form_view_label_name."_tbl_".$prime_form_view_id;
		$table_name      = $this->control_name."_".$form_view_label_name;
		$row_prime_id    = "prime_".$table_name."_id";
		$table_name      =  $this->db->dbprefix($table_name);
		$table_prime_id  = "prime_".$this->control_name."_id";
		
		$form_qry    = 'select prime_form_id,view_name,label_name,field_type,pick_list_type,pick_list,pick_table,auto_prime_id,auto_dispaly_value from cw_form_setting where prime_module_id = "'.$this->control_name.'" and  input_for = "'.$prime_form_view_id.'" and  input_view_type = "3" and table_show = "1" and trans_status = "1" order by abs(field_sort)';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$table_head = array();
		$thead_line = "";
		$select_query = "$table_name.$row_prime_id,$table_name.$table_prime_id,";
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
			if((int)$field_type === 13){
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
		if($table_name === "cw_candidate_tracker_working_experience"){
			$order_qry = 'order by cw_candidate_tracker_working_experience.joined_date desc';
		}else{
			$order_qry = 'order by abs('.$table_name.'.'.$row_prime_id.') desc';
		}
		$final_qry    = "select $select_query from $table_name $pick_query " .' where '.$table_name.'.'.$table_prime_id.' = "'.$prime_id.'" and '.$table_name.'.trans_status = "1" '.$order_qry;
		$row_data     = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		$row_result   = $row_data->result();
		$row_data->next_result();
		$tr_line = "";
		foreach($row_result as $data){
			$td_line = "";
			foreach($table_head as $label){
				$value = $data->$label;
				if($value === "01-01-1970"){
					$value = "";
				}
				$td_line .= "<td>$value</td>";
			}
			$row_id   = $data->$row_prime_id;
			$tab_name = $this->control_name."_".$form_view_label_name;
			$illustration_btn = "";
			if($form_view_label_name === "eligibility_information"){
				$illustration_btn   = "<a class='btn btn-edit btn-xs row_btn' onclick = add_illustration('$row_id','$tab_name','$prime_form_view_id');>Illustration</a>";
			}
			$edit_btn   = "<a class='btn btn-edit btn-xs row_btn' onclick = row_set_edit('$row_id','$tab_name','$prime_form_view_id');>Edit</a>";
			$remove_btn = "<a class='btn btn-danger btn-xs row_btn' onclick = row_set_remove('$row_id','$tab_name','$prime_form_view_id','$prime_id');>Delete</a>";
			$tr_line .= "<tr>$td_line<td>$illustration_btn $edit_btn $remove_btn</td></tr>";
		}
		$row_set_view = "<table id='$table_id' class='table table-bordered' style='background-color: #FFFFFF; box-shadow: none;'>
							<thead>$thead</thead>
							<tbody>$tr_line</tbody>
						</table>";
		return array('div_id' => $div_id, 'table_id' => $table_id,'row_set_view'=>$row_set_view);
	}
	
	public function update_row_set_log($row_prime_id,$prime_id,$view_id,$table_prime,$row_set_log){
		$logged_id     = $this->session->userdata('logged_id');
		$created_date  = date("Y-m-d H:i:s");
		$label_name    = array_keys($row_set_log);
		$label_value   = implode(",",$label_name);	
		$table_name    = $this->db->dbprefix($table_prime);	
		$table_prime   = "prime_".$table_prime."_id";
		$select_query  = "select $label_value from $table_name where $table_prime = \"$row_prime_id\"";
		$select_info   = $this->db->query("CALL sp_a_run ('RUN','$select_query')");
		$select_result = json_decode(json_encode($select_info->row()),true);
		$select_info->next_result();
		$result           = array_diff($row_set_log,$select_result);
		$prime_qry_value  = '';
		$prime_qry_key    = "prime_employee_id,row_set_view_id,row_set_view_name,row_prime_id,label_name,old_value,new_value,ceated_by,created_date";
		foreach($result as $key => $value){
			$check_value      = $select_result[$key];
			$prime_qry_value .= "(\"$prime_id\",\"$view_id\",\"$table_name\",\"$row_prime_id\",\"$key\",\"$check_value\",\"$value\",\"$logged_id\",\"$created_date\"),";
		}
		if($prime_qry_value !== ''){
			$prime_qry_value = rtrim($prime_qry_value,',');
			$prime_insert_query = "insert into cw_row_set_log ($prime_qry_key) values $prime_qry_value";
			$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
			$insert_result      = $insert_info->result();
			$insert_info->next_result();
		}
	}
	/* ==============================================================*/
	/* =================== ROWSET OPERATION - END ===================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================== IMPORT OPERATION - START ==================*/
	/* ==============================================================*/
	//SAVE IMPORT FILE PATH
	public function save_import(){
		$module_id        = $this->input->post('module_id');
		$import_type      = $this->input->post('import_type');
		$excel_format     = $this->input->post('excel_format');
		$excel_file_path  = $this->input->post('excel_file_path');
		$excel_sheet_name = $this->input->post('excel_sheet_name');
		$excel_start_row  = $this->input->post('excel_start_row');
		$excel_end_row    = $this->input->post('excel_end_row');
		$logged_id        = $this->session->userdata('logged_id');
		$today_date       = date("Y-m-d H:i:s");
		
		// if($module_id === "employees"){
		// 	$import_type_val = $import_type;
		// }else{
			$import_type_val = 1;
		// }
		
		$import_query = 'insert into cw_import (import_type,module_id,excel_format,excel_file_path,excel_sheet_name,excel_start_row,excel_end_row,trans_created_by,trans_created_date) value ("'.$import_type.'","'.$module_id.'","'.$excel_format.'","'.$excel_file_path.'","'.$excel_sheet_name.'","'.$excel_start_row.'","'.$excel_end_row.'","'.$logged_id.'","'.$today_date.'")';
		$import_info   = $this->db->query("CALL sp_a_run ('INSERT','$import_query')");
		$import_result = $import_info->result();
		$import_info->next_result();
		$import_id = $import_result[0]->ins_id;
		
		// if($module_id === "employees"){
		// 	echo $this->do_excel_emp_import($import_id);
		// }else{
			echo $this->do_excel_import($import_id);
		// }
	}
	
	//IMPORT DATA FROM FILE PATH
	public function do_excel_import($import_id){
		$filename = dirname(__FILE__)."/php_excel/PHPExcel/IOFactory.php";
		include($filename);
		if($import_id < 0){
			return json_encode(array('success' => false, 'message' => "Invalid file upload"));
		}
		
		$excel_path_qry    = 'select * from cw_import where import_id = "'.$import_id.'"';
		$excel_path_info   = $this->db->query("CALL sp_a_run ('SELECT','$excel_path_qry')");
		$excel_path_result = $excel_path_info->result();
		$excel_path_info->next_result();
		if(!$excel_path_result){
			return json_encode(array('success' => false, 'message' => "Invalid file upload"));
		}else{
			$excel_file_path    = $excel_path_result[0]->excel_file_path;
			$module_id          = $excel_path_result[0]->module_id;
			$excel_format       = $excel_path_result[0]->excel_format;
			$excel_sheet_name   = (int)$excel_path_result[0]->excel_sheet_name;
			$excel_row_start    = (int)$excel_path_result[0]->excel_start_row;
			$excel_row_end      = (int)$excel_path_result[0]->excel_end_row;
			
			$format_qry 	= 'select * from cw_util_excel_format where prime_excel_format_id = "'.$excel_format.'" and cw_util_excel_format.trans_status = 1';
			$format_info    = $this->db->query("CALL sp_a_run ('SELECT','$format_qry')");
			$format_rslt    = $format_info->result();
			$format_info->next_result();
			if(!$format_rslt){
				return json_encode(array('success' => false, 'message' => "Please add excel format before import"));
			}else{
				
				//$excel_row_start   = (int)$format_rslt[0]->excel_row_start;
				$exist_column_name = explode(",",$format_rslt[0]->exist_column_name);
				$excel_format_qry 	= 'select field_type,pick_table,pick_list_type,pick_list_import,pick_list,mandatory_field,field_isdefault,excel_line_column_name,excel_line_value from cw_util_excel_format_line inner join cw_form_setting on label_name = excel_line_column_name where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id = "'.$excel_format.'" and cw_form_setting.prime_module_id = "'.$module_id.'" and cw_util_excel_format_line.trans_status = 1';			
				$excel_format        = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
				$excel_format_result = $excel_format->result();
				$excel_format->next_result();
				if(!$excel_format_result){
					return json_encode(array('success' => false, 'message' => "Please map excel cell column before import"));
				}else{
					try{
						$excel_obj = PHPExcel_IOFactory::load($excel_file_path);
					}catch(Exception $e){
						die('Error loading file "' . pathinfo($excel_file_path, PATHINFO_BASENAME). '": ' . $e->getMessage());
						return json_encode(array('success' => false, 'message' => "Invalid file or path"));
					}
					$sheet           = $excel_obj->getSheet($excel_sheet_name);
					if($excel_row_end){
						$total_rows = $excel_row_end;
					}else{
						$total_rows = $sheet->getHighestRow();
					}
					$highest_column  = $sheet->getHighestColumn();
					$worksheetTitle  = $sheet->getTitle();
					$status_array	= array();
					for($row =$excel_row_start; $row <= $total_rows; $row++) {
						$prime_column_val = "";
						$prime_cell_val   = "";
						$exist_val        = "";
						$status_info = array();
						$status_info["Excel Row"] = $row;
						$sts = TRUE;
						$email_sts = TRUE;
						foreach($excel_format_result as $excel_info){
							$field_isdefault        = (int)$excel_info->field_isdefault;
							$mandatory_field        = (int)$excel_info->mandatory_field;
							$field_type             = (int)$excel_info->field_type;
							$pick_table             = $excel_info->pick_table;
							$pick_list_type         = (int)$excel_info->pick_list_type;
							$pick_list_import       = (int)$excel_info->pick_list_import;
							$pick_list              = $excel_info->pick_list;
							$excel_line_column_name = $excel_info->excel_line_column_name;
							$excel_line_value       = $excel_info->excel_line_value;
							$get_cell_value         = ucwords(trim($sheet->getCell("$excel_line_value$row")->getValue()));
                            if($get_cell_value){
								//FOR MOBILE NUMBER
								 if($field_type === 11){
									$length = strlen($get_cell_value);
									$length_query = 'select field_length from cw_form_setting where cw_form_setting.prime_module_id = "'.$module_id.'" and cw_form_setting.field_type = 11';
									$length_data  = $this->db->query("CALL sp_a_run ('SELECT','$length_query')");
									$length_result = $length_data->result();
									$length_data->next_result();
									$field_length = $length_result[0]->field_length;
									if($field_length != $length){
										$sts = FALSE;
									}
								}else 
								if($field_type === 12){
									//FOR EMAIL
									$email = $get_cell_value;
									if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
										$email_sts = FALSE;
									}
								}else
								if($field_type === 4){
									// FOR DATE
									$get_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
								}else
								if($field_type === 13){
									$get_cell_value = trim(date('Y-m-d H:i:s',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
								}else
								// FOR PICKLIST CHECK
								if(($field_type === 5) || ($field_type === 7)){
									if($pick_list_type === 1){
										$pick_list_val   = explode(",",$pick_list);
										$pick_list_val_1 = $pick_list_val[0];
										$pick_list_val_2 = $pick_list_val[1];
										$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
										if($pick_list_import === 1){
											$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_1.' = "'.$get_cell_value.'"';	
										}else{
											$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
										}	
										$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
										$pick_result = $pick_data->result();
										$pick_count  = $pick_data->num_rows();
										$pick_data->next_result();
										if((int)$pick_count === 0){
											if($pick_table !== $this->prime_table){
												$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ("'.$get_cell_value.'")';
												$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
												$ins_result = $ins_info->result();
												$ins_info->next_result();
												$get_cell_value  = $ins_result[0]->ins_id;
											}
										}else
										if((int)$pick_count === 1){
											if($pick_table !== $this->prime_table){
												$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
												$pick_status = (int)$pick_result[0]->trans_status;
												if($pick_status === 0){
													$upd_query  = 'update '.$pick_table.' set trans_status = 1 where '.$pick_list_val_1.' = '.$pick_id;
													$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
												}
												$get_cell_value = $pick_id;
											}
										}
									}else
									if($pick_list_type === 2){
										$pick_list_val_1 = $pick_table."_id";
										$pick_list_val_2 = $pick_table."_value";
										$pick_list_val_3 = $pick_table."_status";
										$pick_query = 'select * from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
										$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
										$pick_result = $pick_data->result();
										$pick_count  = $pick_data->num_rows();
										$pick_data->next_result();
										if((int)$pick_count === 0){
											$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ("'.$get_cell_value.'")';
											$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
											$ins_result = $ins_info->result();
											$ins_info->next_result();
											$get_cell_value  = $ins_result[0]->ins_id;
										}else
										if((int)$pick_count === 1){
											$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
											$pick_status = (int)$pick_result[0]->$pick_list_val_3;
											if($pick_status === 0){
												$upd_query  = 'update '.$pick_table.' set '.$pick_list_val_3.' = 1 where '.$pick_list_val_1.' = '.$pick_id;
												$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
											}
											$get_cell_value = $pick_id;
										}
									}
								}
								if($field_isdefault === 1){
									//$status_info[$excel_line_column_name] = $get_cell_value;
									$get_cell_value    = str_replace("'",'^', $get_cell_value);
									$prime_column_val .= $excel_line_column_name . ",";
									$prime_cell_val   .= "\'" . $get_cell_value . "\',";
									if(empty($exist_column_name)){
										 if($mandatory_field === 1){
											$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
									}else{
										if(in_array($excel_line_column_name,$exist_column_name)){
											$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
									}
								}
							}
						}
						if($prime_column_val){
							$prime_id    = "prime_".$module_id."_id";
							$exist_val   = rtrim($exist_val," and ");
							$exist_query = "select count(*) exist_count,$this->prime_table.trans_status,$this->prime_table.$prime_id from $this->prime_table where $exist_val";
							//echo "$exist_query\n";
							$exist_info   = $this->db->query("CALL sp_a_run ('RUN','$exist_query')");
							$exist_result = $exist_info->result();
							$exist_info->next_result();
							$exist_count = $exist_result[0]->exist_count;
							$created_on  = date("Y-m-d h:i:s");
							if(!$sts){
								$status_info['status'] = "Invalid Mobile Number";
							}else
							if(!$email_sts){
								$status_info['status'] = "Invalid Email";
							}else
							if((int)$exist_count === 0){
								$prime_column_val .= "trans_created_by,trans_created_date";
								$prime_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								$prime_column_val  = rtrim($prime_column_val,",");
								$prime_cell_val    = rtrim($prime_cell_val,",");
								$prime_query       = "insert into $this->prime_table ($prime_column_val) VALUES ($prime_cell_val)";
								//echo "$prime_query\n";
								$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$prime_query')");
								$insert_result = $insert_info->result();
								$insert_info->next_result();
								$insert_id = $insert_result[0]->ins_id;
								
								$status_info['Status'] = "Inserted to DB";
							}else
							if((int)$exist_count === 1){
								$trans_status = (int)$exist_result[0]->trans_status;
								$upd_prime_id = (int)$exist_result[0]->$prime_id;
								if($trans_status === 0){
									$upd_query = 'UPDATE '.$this->prime_table.' SET trans_updated_by = "'.$this->logged_id.'",trans_updated_date = "'.$created_on.'" , trans_status = 1 WHERE '.$prime_id.' = "'.$upd_prime_id.'"';
									//echo "$upd_query\n";
									$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
									$status_info['status'] = "Changed to active";
								}else{
									$status_info['status'] = "Already Exist in DB";
								}							
							}else{
								$status_info['status'] = "Already Exist in DB";
							}
							$status_array[] = $status_info;
						}
					}
					$table_info = $this->get_excel_import_ui($status_array);
					return json_encode(array('success'=>true,'message'=>"Successfully file imported",'table_info'=>$table_info));
				}
			}
		}
	}
	public function get_excel_import_ui($status_array){
		$table_info = "";
		$th_line = "";
		$tr_line = "";		
		$count = 0;
		foreach($status_array as $status){
			$count++;
			$status_array_count  = count($status);
			$status_count = 0;
			foreach($status as $key => $value){
				$status_count++;
				if((int)$count === 1){
					$th_line .= "<th style='text-align:center !important;'>$key</th>";
				}
				$td_line .= "<td>$value</td>";	
				if((int)$status_count === (int)$status_array_count){
					$color = "style='color:#15da15 !important;'";
					if($value === "Already Exist in DB"){
						$color = "style='color:#ff0303 !important;'";
					}
					$tr_line .= "<tr $color>$td_line</tr>";
					$td_line = "";
				}
			}
		}
		if($th_line !== ""){
			$table_info = "<table class='table table-bordered' style='text-align:center;'>
								<thead>
									<tr>
										$th_line
									</tr>
								</thead>
								<tbody>
									$tr_line
								</tbody>
						   </table>";
		}
		return $table_info;
	}
	
	
public function do_excel_emp_import($import_id){
	$filename = dirname(__FILE__)."/php_excel/PHPExcel/IOFactory.php";
	include($filename);
	if($import_id < 0){
		return json_encode(array('success' => false, 'message' => "Invalid file upload"));
	}		
	$excel_path_qry    = 'select * from cw_import where import_id = "'.$import_id.'"';
	$excel_path_info   = $this->db->query("CALL sp_a_run ('SELECT','$excel_path_qry')");
	$excel_path_result = $excel_path_info->result();
	$excel_path_info->next_result();
	if(!$excel_path_result){
		return json_encode(array('success' => false, 'message' => "Invalid file upload"));
	}else{
		$excel_file_path    = $excel_path_result[0]->excel_file_path;
		$module_id          = $excel_path_result[0]->module_id;
		$import_type        = $excel_path_result[0]->import_type;
		$excel_format       = $excel_path_result[0]->excel_format;
		$excel_sheet_name   = (int)$excel_path_result[0]->excel_sheet_name;
		$excel_row_start    = (int)$excel_path_result[0]->excel_start_row;
		$excel_row_end      = (int)$excel_path_result[0]->excel_end_row;
		$format_qry 	= 'select * from cw_util_excel_format where prime_excel_format_id = "'.$excel_format.'" and cw_util_excel_format.trans_status = 1';
		$format_info    = $this->db->query("CALL sp_a_run ('SELECT','$format_qry')");
		$format_rslt    = $format_info->result();
		$format_info->next_result();
		if(!$format_rslt){
			return json_encode(array('success' => false, 'message' => "Please add excel format before import"));
		}else{
			$exist_column_name = explode(",",$format_rslt[0]->exist_column_name);
			$table_name_list   = explode(",",$format_rslt[0]->excel_table_name);
			$excel_format_qry 	= 'select view_name,input_view_type,duplicate_data,picklist_data,field_type,pick_table,pick_list_type,pick_list_import,pick_list,mandatory_field,field_isdefault,excel_line_column_name,excel_line_value from cw_util_excel_format_line inner join cw_form_setting on label_name = excel_line_column_name where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id = "'.$excel_format.'" and cw_form_setting.prime_module_id = "'.$module_id.'" and cw_util_excel_format_line.trans_status = 1';
			$excel_format        = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
			$excel_format_result = $excel_format->result();
			$excel_format->next_result();
			if(!$excel_format_result){
				return json_encode(array('success' => false, 'message' => "Please map excel cell column before import"));
			}else{
				try{
					$excel_obj = PHPExcel_IOFactory::load($excel_file_path);
				}catch(Exception $e){
					die('Error loading file "' . pathinfo($excel_file_path, PATHINFO_BASENAME). '": ' . $e->getMessage());
					return json_encode(array('success' => false, 'message' => "Invalid file or path"));
				}
				$sheet = $excel_obj->getSheet($excel_sheet_name);
				if($excel_row_end){
					$total_rows    = $excel_row_end;
				}else{
					$total_rows    = $sheet->getHighestRow();
				}
				$highest_column    = $sheet->getHighestColumn();
				//Columnwise
				$columnwise_result   = $this->emp_excel_columnwise_checking($sheet,$excel_obj,$import_type,$excel_format_result,$excel_row_start,$total_rows,$module_id);
				$err_column_array    = $columnwise_result['err_column_array'];
				$err_column_tabview  = $columnwise_result['err_column_tabview'];

				$err_column_count = count($err_column_array['error']);
				$err_column       = implode(",",(array_unique($err_column_array['error'])));
				if((int)$err_column_count > 0){
					$table_info = $this->get_excel_error_ui($err_column_tabview);
					echo json_encode(array('success'=>false,'message'=>"Column Wise Error",'table_info'=>$table_info));
					exit();
				}else{
					//Rowwise
					$rowwise_result   = $this->emp_excel_rowwise_checking($sheet,$excel_obj,$import_type,$excel_format_result,$excel_row_start,$total_rows);
					$err_column_array    = $rowwise_result['err_column_array'];
					$err_column_tabview  = $rowwise_result['err_column_tabview'];
					$err_column_count = count($err_column_array['error']);
					$err_column       = implode(",",(array_unique($err_column_array['error'])));
					if((int)$err_column_count > 0){
						$table_info = $this->get_excel_error_ui($err_column_tabview);
						echo json_encode(array('success'=>false,'message'=>"Row wise Error",'table_info'=>$table_info));
						exit();
					}else{
						$final_result   = $this->emp_final_excel_import($module_id,$sheet,$import_type,$excel_format_result,$excel_row_start,$total_rows,$exist_column_name);
					}
				}
   			}
  		}
 	}	
}

function filterArray($value){
    return ($value == 2);
}

public function emp_excel_columnwise_checking($sheet,$excel_obj,$import_type,$excel_format_result,$excel_row_start,$total_rows,$module_id){
	$emp_code_qry = 'select employee_code,employee_status from cw_employees where trans_status =1';
	$emp_code_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
	$emp_code_data_result = $emp_code_data->result_array();
	$emp_code_data->next_result();
	$emp_code_data_result = array_map(function($v){
		$return_array = array();
		$return_array['employee_status'] = $v['employee_status'];
		$return_array['employee_code'] = $v['employee_code'];
	    return $return_array;
	}, $emp_code_data_result);
	$emp_code_data_result = array_column($emp_code_data_result,'employee_status','employee_code');
	//print_r($emp_code_data_result);
	//START CHECKING
	//DATA VALIDATION FOR MANDATORY FIELDS EMPTY OR INVALID DATA AND TAX RANGE ALSO CHECKED
	//DATE VALIDATION ONLY PENDING
	if($import_type !==3){
		$err_column_array     = array();
		$err_column_tabview   = array();
		foreach($excel_format_result as $excel_info){
			$mandatory_field        = (int)$excel_info->mandatory_field;
			$field_type             = (int)$excel_info->field_type;
			$excel_line_column_name = $excel_info->excel_line_column_name;
			$excel_line_value       = $excel_info->excel_line_value;
			$view_name              = $excel_info->view_name;
			$pick_table             = $excel_info->pick_table;
			$pick_list_type         = (int)$excel_info->pick_list_type;
			$pick_list              = $excel_info->pick_list;
			$picklist_data          = (int)$excel_info->picklist_data;
			$duplicate_data         = (int)$excel_info->duplicate_data;
			$input_view_type        = (int)$excel_info->input_view_type;
			$pick_list_import       = (int)$excel_info->pick_list_import;
		//Columns Based total array
			$multi_get_cell_value = $sheet->rangeToArray("$excel_line_value$excel_row_start:$excel_line_value$total_rows", NULL, TRUE, TRUE, TRUE);
		//Mandatory fields and popup data validation
			if(($mandatory_field === 1) || ($picklist_data === 1)){
				$i = $excel_row_start;
				foreach($multi_get_cell_value as $arr_value){
					foreach($arr_value as $col_key =>$col_value){
						if(empty($col_value) && !is_numeric($col_value)){
							$err_column_array['error']["$excel_line_value$i"] = $view_name;
							$msg_line = "columns are empty and invalid data is present please check it?";
							$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
						}else{
						if(($field_type === 5) || ($field_type === 7)){//picklist validation with data also validations
							if($pick_list_import !== 1){
								if($pick_list_type === 1){
									$pick_list_val   = explode(",",$pick_list);
									$pick_list_val_1 = $pick_list_val[0];
									$pick_list_val_2 = $pick_list_val[1];
									$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';								
									$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
									$pick_result = $pick_data->result();
									$pick_count  = $pick_data->num_rows();
									//echo "BSK $pick_query :: $pick_count <br/>";
									$pick_data->next_result();
									if((int)$pick_count === 0){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "column invalid data is present please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
										}else{ //Tax Range is checking for your mention location
											if($pick_table === "cw_professional_tax_location"){
												$tax_location_range = $pick_result[0]->$pick_list_val_1;
												$tax_location_range_qry = 'select count(*) as rslt_range from cw_professional_tax where trans_status =1 and location = "'.$tax_location_range.'"';
												$tax_range_data  = $this->db->query("CALL sp_a_run ('SELECT','$tax_location_range_qry')");
												$tax_range_data_result = $tax_range_data->result();
												$tax_range_data->next_result();
												$range_count = $tax_range_data_result[0]->rslt_range;
												if((int)$range_count === 0){
													$tax_sts = 1;
													$tax_location = $col_value;
													$err_column_array['error']["$excel_line_value$i"] = $view_name;
													$msg_line = "$col_value range not present please check it?";
													$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
												}
											}
										}
									}else{
										if($pick_list_type === 2){
											$pick_list_val_1 = $pick_table."_id";
											$pick_list_val_2 = $pick_table."_value";
											$pick_list_val_3 = $pick_table."_status";
											$pick_query = 'select count(*) as rslt_count from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
											$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
											$pick_result = $pick_data->row();
											$pick_data->next_result();
											$pick_count  = (int)$pick_result->rslt_count;
											if((int)$pick_count === 0){
												$err_column_array['error']["$excel_line_value$i"] = $view_name;
												$msg_line = "column invalid data is present please check it?";
												$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
											}
										}
									}
								}
							}elseif($field_type === 4){//date validations for mandatory field
								$excel_cell_formate = $excel_obj->getActiveSheet()->getCell($col_key.$i)->getStyle()->getNumberFormat()->getFormatCode();
								$cell_formate = str_replace("[$-14009]","",$excel_cell_formate);
								$cell_formate = trim(strtoupper(str_replace(";@","",$cell_formate)));
								if($cell_formate === "DD/MM/YYYY"){
									$year_month_rslt = explode('/', $col_value);
									$date  			 = $year_month_rslt[0];
									$month  	     = $year_month_rslt[1];
									$year			 = $year_month_rslt[2];
									$tot_days 		 = cal_days_in_month(CAL_GREGORIAN,$month,$year);
									if(((int)$date  === 0) || ((int)$month === 0) || ((int)$year === 0)){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "Please enter valid date... Please map The Date Format Like (DD/MM/YYYY)";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}else
									if((int)$month > 12){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "Invalid Month... Please map The Date Format Like (DD/MM/YYYY)";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}else
									if((int)$date > (int)$tot_days){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "Invalid date... Please map The Date Format Like (DD/MM/YYYY)";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line; 
									}
								}else{
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "Invalid Date Format... Please map The Date Format Like (DD/MM/YYYY)";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line; 
								}
							}else 
								if($field_type === 1){
									if($excel_line_column_name === 'entry_time' || $excel_line_column_name === 'exit_time'){
										$value      = $col_value;
										$value      = explode(":", $value);					
										$hours      = (int)$value[0];
										$mins       = (int)substr("$value[1]",0,2);
										$meridiem   = substr("$value[1]",2,2);
										//echo "$hours :: $mins :: $meridiem";
										if(!(($hours >= 0 && $hours <= 12) && ($mins <= 60 && $mins >= 0) && ($meridiem === "AM" || $meridiem === "PM"))){
											if($excel_line_column_name === 'exit_time'){
												$err_column_array['error']["$excel_line_value$i"] = $view_name;
												$msg_line = "Invalid... Please map The Date Format Like (06:30AM)";
												$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line; 
											}else{
												$err_column_array['error']["$excel_line_value$i"] = $view_name;
												$msg_line = "Invalid... Please map The Date Format Like (09:30AM)";
												$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
											}
										}
									}
								}else 
								if($field_type === 12){
									//FOR EMAIL
									$email = $col_value;
									if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "Invalid... Please map The correct Email";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
								}else    
								if($field_type === 11){
									//FOR MOBILE NUMBER
									$length = strlen($col_value);
									$length_query = 'select field_length from cw_form_setting where cw_form_setting.prime_module_id = "'.$module_id.'" and label_name = "'.$excel_line_column_name.'"';
									//echo $length_query; die;
									$length_data  = $this->db->query("CALL sp_a_run ('SELECT','$length_query')");
									$length_result = $length_data->result();
									$length_data->next_result();
									$field_length = $length_result[0]->field_length;
									//echo "BSK $field_length :: $length"; die;
									if($field_length != $length){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "Invalid... Please map The correct mobile number";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
								}

							//amendment checking for duplicate data start
							if(((int)$import_type === 2) && ((int)$input_view_type !==3)){
								if($duplicate_data === 1){
									$get_emp_value = array_map('array_filter', $multi_get_cell_value);//empty remove
									$get_emp_value = array_filter($get_emp_value);
									foreach ($get_emp_value as $current_key => $current_array) {
										foreach ($get_emp_value as $search_key => $search_array) {
											if($search_array["$excel_line_value"] == $current_array["$excel_line_value"]){
												if ($search_key != $current_key) {
													$err_column_array['error']["$excel_line_value$current_key"] = $view_name;
													$msg_line = "duplicate data present in column, please check it?";
													$err_column_tabview['error']["$excel_line_value$current_key"]  = $view_name." ".$msg_line;
												}
											}
										}
									}
								}
								
								//amendment checking for invalid emp code
								if($excel_line_column_name === "employee_code"){
									/*$emp_code_qry = 'select count(*) as rslt_count from cw_employees where trans_status =1 and employee_code = "'.$col_value.'"';
									$emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
									$emp_data_result = $emp_data->result();
									$emp_data->next_result();
									$rslt_count = $emp_data_result[0]->rslt_count;
									if((int)$rslt_count === 0){
									if(!$emp_code_result[$col_value]){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = " is not exit in employee master please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}*/
									if(!isset($emp_code_data_result[$col_value])){

										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = " is not exit in employee master please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
									//resigned or not checking MRJ 18APR2020
									/*$resign_emp_code_qry = 'select count(*) as rslt_count from cw_employees where trans_status =1 and termination_status = 1 and employee_code = "'.$col_value.'"';
									$resign_emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$resign_emp_code_qry')");
									$resign_emp_data_result = $resign_emp_data->result();
									$resign_emp_data->next_result();
									$resign_rslt_count = $resign_emp_data_result[0]->rslt_count;
									*/
									if($emp_code_data_result[$col_value] === "1"){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = " is already resigned, please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
								}
							}
						}
						$i++;
					}
				}
			}elseif(($field_type === 2) || ($field_type === 3)){ //decimal and integer validations for non-mandatory field
				$get_number_value = $sheet->rangeToArray("$excel_line_value$excel_row_start:$excel_line_value$total_rows", NULL, TRUE, TRUE, TRUE);		
				$j = $excel_row_start;
				foreach($get_number_value as $number_valid){
					foreach($number_valid as $num_val){
						if(is_null($num_val)){
							$num_val = 0;
						}
						if(!is_numeric($num_val)){
							$err_column_array['error']["$excel_line_value$j"] = $view_name;
							$msg_line = "column invalid data is present please check it?";
							$err_column_tabview['error']["$excel_line_value$j"]  = $view_name." ".$msg_line;
						//break;
						}
						$j++;
					}
				}
			}elseif(($duplicate_data === 1) && ((int)$input_view_type !== 3)){//duplicate number validations for non-mandatory field
				$get_duplicat_value = $sheet->rangeToArray("$excel_line_value$excel_row_start:$excel_line_value$total_rows", NULL, TRUE, TRUE, TRUE);
				$get_duplicat_value = array_map('array_filter', $get_duplicat_value);//empty remove
				$get_duplicat_value = array_filter($get_duplicat_value);
				foreach ($get_duplicat_value as $current_key => $current_array) {
					foreach ($get_duplicat_value as $search_key => $search_array) {
						if($search_array["$excel_line_value"] == $current_array["$excel_line_value"]){
							if ($search_key != $current_key) {
								$err_column_array['error']["$excel_line_value$current_key"] = $view_name;
								$msg_line = "duplicate data present in column, please check it?";
								$err_column_tabview['error']["$excel_line_value$current_key"]  = $view_name." ".$msg_line;
							}
						}
					}
				}
			}
		}
	}
	//END CHECKING
	$check_array = array("err_column_array"=>$err_column_array,"err_column_tabview"=>$err_column_tabview);
	return $check_array;
}

public function emp_excel_rowwise_checking($sheet,$excel_obj,$import_type,$excel_format_result,$excel_row_start,$total_rows){
		$msg_line = "";
	for($row = $excel_row_start; $row <= $total_rows; $row++) {
		foreach($excel_format_result as $excel_info){
			$field_type             = (int)$excel_info->field_type;
			$excel_line_column_name = $excel_info->excel_line_column_name;
			$excel_line_value       = $excel_info->excel_line_value;
			$view_name              = $excel_info->view_name;
			$get_cell_value         = trim($sheet->getCell("$excel_line_value$row")->getCalculatedValue());			
			if($excel_line_column_name === "date_of_birth"){
				$dob_date = $get_cell_value;
				if(!empty($dob_date)){
					$dob_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_dob        = strtotime($dob_date_value);
					$date_diff_val  = date( "Y-m-d", strtotime( "$dob_date_value +14 years" ));//after 14 years add
					$date_diff_val  = strtotime($date_diff_val);
					$dob_line_val   = $excel_info->excel_line_value;
					$dob_view_name  = $view_name;
				}
			}			
			if($excel_line_column_name === "date_of_joining"){
				$doj_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
				$str_doj = strtotime($doj_date_value);
			}			
			if($excel_line_column_name === "family_date_of_birth"){
				$family_date = $get_cell_value;
				if(!empty($family_date)){
					$family_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_family_date   = strtotime($family_date_value);
					$family_line_val   = $excel_info->excel_line_value;
					$family_view_name  = $view_name;
				}
			}			
			if($excel_line_column_name === "course_year_of_passing"){
				$course_date = $get_cell_value;
				if(!empty($course_date)){
					$course_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_course_date   = strtotime($course_date_value);
					$course_line_val   = $excel_info->excel_line_value;
					$course_view_name  = $view_name;
				}
			}			
			if($excel_line_column_name === "training_date"){
				$training_date = $get_cell_value;
				if(!empty($training_date)){
					$training_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_training_date   = strtotime($training_date_value);
					$training_line_val   = $excel_info->excel_line_value;
					$training_view_name  = $view_name;
				}
			}			
			if($excel_line_column_name === "previous_from_date"){
				$from_date = $get_cell_value;
				if(!empty($from_date)){
					$from_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_from_date   = strtotime($from_date_value);
				}else{
					$today         = date('Y-m-d');
					$str_from_date = strtotime($today);
				}
				$from_date_line_val   = $excel_info->excel_line_value;
				$from_date_view_name  = $view_name;
			}			
			if($excel_line_column_name === "past_to_date"){
				$past_date = $get_cell_value;
				if(!empty($past_date)){
					$past_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
					$str_past_date   = strtotime($past_date_value);
				}else{
					$today         = date('Y-m-d');
					$str_past_date = strtotime($today);
				}
				$past_date_line_val   = $excel_info->excel_line_value;
				$past_date_view_name  = $view_name;
			}
			if($excel_line_column_name === "resignation_date"){
				$resign_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
				$str_resign = strtotime($resign_date_value);
				$resign_line_val   = $excel_info->excel_line_value;
				$resign_view_name  = $view_name;
			}
			if($excel_line_column_name === "last_working_date"){
				$last_work_date_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
				$str_last_work_date        = strtotime($last_work_date_value);
				$last_work_date_val        = $excel_info->excel_line_value;
				$last_work_date_view_name  = $view_name;
			}
		}		
		$today     = date('Y-m-d');
		$str_today = strtotime($today);
		if($str_dob){
			if((int)$str_dob > (int)$str_doj){
				$err_column_array['error']["$dob_line_val$row"] = $dob_view_name;
				$msg_line = " is not greater then date of joining";
				$err_column_tabview['error']["$dob_line_val$row"]  = $dob_view_name." ".$msg_line; 
			}else
			if((int)$date_diff_val > (int)$str_doj){
				$err_column_array['error']["$dob_line_val$row"] = $dob_view_name;
				$msg_line = " and date of birth minimum difference is 14 years, please change the date?";
				$err_column_tabview['error']["$dob_line_val$row"]  = $dob_view_name." ". $msg_line; 
			}
		}
		
		if($str_family_date){//family details updated---29OCT2019
			if((int)$str_family_date > (int)$str_today){
				$err_column_array['error']["$family_line_val$row"] = $family_view_name;
				$msg_line = " not greater than today date, please change the date?";
				$err_column_tabview['error']["$family_line_val$row"]  = $family_view_name." ".$msg_line;
			}
		}
		
		if($str_course_date){//course year checking updated---30OCT2019
			$today     = date('Y-m-d');
			$str_today = strtotime($today);
			if((int)$str_course_date > (int)$str_today){
				$err_column_array['error']["$course_line_val$row"] = $course_view_name;
				$msg_line = " not greater than today date, please change the date?";
				$err_column_tabview['error']["$course_line_val$row"]  = $course_view_name." ". $msg_line;
			}
		}
		
		if($str_training_date){//Training date checking updated---30OCT2019
			$today     = date('Y-m-d');
			$str_today = strtotime($today);
			if((int)$sttraining_line_valr_training_date > (int)$str_today){
				$err_column_array['error']["$$row"] = $training_view_name;
				$msg_line = " is not greater than today date, please change the date?";
				$err_column_tabview['error']["$training_line_val$row"]  = $training_view_name." ".$msg_line;
			}
		}
		
		//experience start date and end date checking
		if($str_training_date){
			if((int)$str_from_date > (int)$str_today){
				$err_column_array['error']["$from_date_line_val$row"] = $from_date_view_name;
				$msg_line = " is not greater than today date, please change the date?";
				$err_column_tabview['error']["$from_date_line_val$row"]  = $from_date_view_name." ".$msg_line;
			}
		}
		
		if($str_past_date){
			if((int)$str_past_date > (int)$str_today){
				$err_column_array['error']["$past_date_line_val$row"] = $past_date_view_name;
				$msg_line = " is not greater than today date, please change the date?";
				$err_column_tabview['error']["$past_date_line_val$row"]  = $past_date_view_name." ".$msg_line;
			}
			if((int)$str_from_date >= (int)$str_past_date){
				$err_column_array['error']["$from_date_line_val$row"] = $from_date_view_name;
				$msg_line = " is not greater than to date, please change the date?";
				$err_column_tabview['error']["$from_date_line_val$row"]  = $from_date_view_name." ".$msg_line;
			}
		}
		
		if($str_resign){  //resgin date checking updated---17APR2020
			if((int)$str_resign > (int)$str_today){
				$err_column_array['error']["$resign_line_val$row"] = $resign_view_name;
				$msg_line = " not greater than today date, please change the date?";
				$err_column_tabview['error']["$resign_line_val$row"]  = $resign_view_name." ".$msg_line;
			}else
			if((int)$str_resign > (int)$str_last_work_date){
				$err_column_array['error']["$last_work_date_val$row"] = $last_work_date_view_name;
				$msg_line = " not lesser than Resign date, please change the date?";
				$err_column_tabview['error']["$last_work_date_val$row"]  = $last_work_date_view_name." ".$msg_line;
			}
		}
	}
	$check_array = array("err_column_array"=>$err_column_array,"err_column_tabview"=>$err_column_tabview);
	return $check_array;
}

public function emp_final_excel_import($module_id,$sheet,$import_type,$excel_format_result,$excel_row_start,$total_rows,$exist_column_name){
	$status_array	    = array();
	$formula_process    = array();
	for($row = $excel_row_start; $row <= $total_rows; $row++) {
		$prime_upd_query  = "";
		$prime_column_val = "";
		$prime_cell_val   = "";
		$exist_val        = "";
		$status_info = array();
		$status_info["Excel Row"] = $row;
		$sts = 1;
		foreach($excel_format_result as $excel_info){
			$field_isdefault        = (int)$excel_info->field_isdefault;
			$mandatory_field        = (int)$excel_info->mandatory_field;
			$field_type             = (int)$excel_info->field_type;
			$pick_table             = $excel_info->pick_table;
			$pick_list_type         = (int)$excel_info->pick_list_type;
			$pick_list_import       = (int)$excel_info->pick_list_import;
			$pick_list              = $excel_info->pick_list;
			$excel_line_column_name = $excel_info->excel_line_column_name;
			$excel_line_value       = $excel_info->excel_line_value;
			$input_view_type        = $excel_info->input_view_type;
			$get_cell_value         = trim(iconv("UTF-8","ISO-8859-1",$sheet->getCell("$excel_line_value$row")->getCalculatedValue())," \t\n\r\0\x0B\xA0");
			if($field_type === 4){
			// FOR DATE			
				if($sheet->getCell("$excel_line_value$row")->getCalculatedValue()){
					$get_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
				}else{
					$get_cell_value ='';
				}
			}else
			// FOR PICKLIST CHECK
			if(($field_type === 5) || ($field_type === 7)){
				if(($get_cell_value !='') || ($get_cell_value !=0)){ 
					if($pick_list_type === 1){
						$pick_list_val   = explode(",",$pick_list);
						$pick_list_val_1 = $pick_list_val[0];
						$pick_list_val_2 = $pick_list_val[1];
						if($pick_list_import === 1){
							$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_1.' = "'.$get_cell_value.'"';
						}else{
							$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
						}
						$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_count  = $pick_data->num_rows();
						$pick_data->next_result();
						$created_on  = date("Y-m-d H:i:s");
						if((int)$sts != 0){
							if((int)$pick_count === 0){
								$pick_list_val_2 .= ",trans_created_by,trans_created_date";
								$get_cell_value_val = '"'.$get_cell_value.'",';
								$get_cell_value_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								if($pick_table !== $this->prime_table){
									$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ('.$get_cell_value_val.')';
									$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
									$ins_result = $ins_info->result();
									$ins_info->next_result();
									$get_cell_value     = $ins_result[0]->ins_id;
									$second_insert_id   = $ins_result[0]->ins_id;
									$prime_id = $pick_table."_id";
									$prime_id = str_replace("cw_","prime_",$prime_id);
								}
							}else
							if((int)$pick_count === 1){
								if($pick_table !== $this->prime_table){
									$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
									$pick_status = (int)$pick_result[0]->trans_status;
									if($pick_status === 0){
										$upd_query  = 'update '.$pick_table.' set trans_status = 1 where '.$pick_list_val_1.' = '.$pick_id;
	
										$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
									}
									$get_cell_value = $pick_id;
								}
								
							}
						}
					}else
					if($pick_list_type === 2){
						$pick_list_val_1 = $pick_table."_id";
						$pick_list_val_2 = $pick_table."_value";
						$pick_list_val_3 = $pick_table."_status";
						if($pick_list_import === 1){
							$pick_query = 'select * from '.$pick_table.' where '.$pick_list_val_1.' = "'.$get_cell_value.'"';
						}else{
							$pick_query = 'select * from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
						}
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_count  = $pick_data->num_rows();
						$pick_data->next_result();
						if((int)$pick_count === 0){
							if($pick_table !== $this->prime_table){
								$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ("'.$get_cell_value.'")';
								$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
								$ins_result = $ins_info->result();
								$ins_info->next_result();
								$get_cell_value  = $ins_result[0]->ins_id;
							}
						}else
						if((int)$pick_count === 1){
							if($pick_table !== $this->prime_table){
								$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
								$pick_status = (int)$pick_result[0]->$pick_list_val_3;
								if($pick_status === 0){
									$upd_query  = 'update '.$pick_table.' set '.$pick_list_val_3.' = 1 where '.$pick_list_val_1.' = '.$pick_id;
									$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
								}
								$get_cell_value = $pick_id;
							}
						}
					}
				}
			}
			if($field_isdefault === 1){
				$get_cell_value    = str_replace("'",'^', $get_cell_value);
				$prime_column_val .= $excel_line_column_name . ",";
				$prime_cell_val   .= "\'" . $get_cell_value . "\',";
				if(empty($exist_column_name)){
					if($mandatory_field === 1){
						$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
					}
					$update_cell_val   = '"'.$get_cell_value.'",';
					$prime_upd_query   .= $excel_line_column_name."=".$update_cell_val;
				}else{
					if(in_array($excel_line_column_name,$exist_column_name)){
						$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
					}
					$update_column_val = $excel_line_column_name;
					$update_cell_val   = '"'.$get_cell_value.'",';
					$prime_upd_query   .= $update_column_val."=".$update_cell_val;
				}
				
				//old application insert query building details start --18JAN2020
				if($excel_line_column_name === "employee_code"){
					$user_name = $get_cell_value;
					$code      = $get_cell_value;
				}
				if($excel_line_column_name === "date_of_joining"){
					$password = md5($get_cell_value);
					$doj =  $get_cell_value;
				}
				if($excel_line_column_name === "emp_name"){
					$empname = $get_cell_value;
				}
				if($excel_line_column_name === "department"){
					$department = $get_cell_value;
					$depart_query = 'select department from cw_department where prime_department_id = "'.$department.'" and trans_status =1';
					$depart_data   = $this->db->query("CALL sp_a_run ('SELECT','$depart_query')");
					$depart_result = $depart_data->result();
					$depart_data->next_result();
					$department = $depart_result[0]->department;
				}
				if($excel_line_column_name === "designation"){
					$designation = $get_cell_value;
					$design_query = 'select designation from cw_designation where prime_designation_id = "'.$designation.'"  and trans_status =1';
					$design_data   = $this->db->query("CALL sp_a_run ('SELECT','$design_query')");
					$design_result = $design_data->result();
					$design_data->next_result();
					$designation = $design_result[0]->designation;
				}
				if($excel_line_column_name === "gender"){
					$gender = $get_cell_value;
					if((int)$gender === 2){
						$gender = "F";
					}else{
						$gender = "M";
					}
				}
				if($excel_line_column_name === "date_of_birth"){
					$dob = $get_cell_value;
				}
				if($excel_line_column_name === "marital_status"){
					$marital_status = $get_cell_value;
					if((int)$marital_status === 1){
						$marital_status = "Married";
					}else{
						$marital_status = "UnMarried";
					}
				}
				if($excel_line_column_name === "role"){
					$role = $get_cell_value;
				}
				//old application insert query building details end --20JAN2020
			}
			if((int)$import_type === 3){
				$rowset_column_val .= $excel_line_column_name.",";
				$rowset_cell_val   .= '"'.$get_cell_value.'",';
				if($excel_line_column_name === "employee_code"){
					$emp_code_qry = 'select prime_employees_id from cw_employees where trans_status =1 and employee_code = "'.$get_cell_value.'"';
					$emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
					$emp_data_result = $emp_data->result();
					$emp_data->next_result();
					$emp_id = $emp_data_result[0]->prime_employees_id;
					$rowset_column_val = "prime_employees_id,";
					$rowset_cell_val   ='"'.$emp_id.'",';
				}
			}
		}
		
		if((int)$sts !== 0){
			$created_on  = date("Y-m-d h:i:s");
			if((int)$import_type === 3){
				$rowset_column_val .= "trans_created_by,trans_created_date";
				$rowset_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
				$rowset_column_val  = rtrim($rowset_column_val,",");
				$rowset_cell_val    = rtrim($rowset_cell_val,",");
				$table_name         = $table_name_list[2];
				$rowset_query       = "insert into $table_name ($rowset_column_val) VALUES ($rowset_cell_val)";
				$rowset_insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$rowset_query')");
				$rowset_insert_result = $rowset_insert_info->result();
				$rowset_insert_info->next_result();
				$insert_id = $rowset_insert_result[0]->ins_id;
				$status_info['Status'] = "Successfully datas are imported";
			}else{
				if($prime_column_val){
					$prime_id    = "prime_".$module_id."_id";
					$exist_val   = rtrim($exist_val," and ");
					$exist_query = "select count(*) exist_count,trans_status,$prime_id from $this->prime_table where $exist_val";
					$exist_info   = $this->db->query("CALL sp_a_run ('RUN','$exist_query')");
					$exist_result = $exist_info->result();
					$exist_info->next_result();
					$exist_count = $exist_result[0]->exist_count;
					if((int)$import_type === 1){
						if((int)$exist_count === 0){
							$prime_column_val .= "user_name,password,trans_created_by,trans_created_date";
							$prime_cell_val   .= '"'.$user_name.'","'.$password.'","'.$this->logged_id.'",'.'"'.$created_on.'"';
							$prime_column_val  = rtrim($prime_column_val,",");
							$prime_cell_val    = rtrim($prime_cell_val,",");
							$prime_query       = "insert into $this->prime_table ($prime_column_val) VALUES ($prime_cell_val)";
							$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$prime_query')");
							$insert_result = $insert_info->result();
							$insert_info->next_result();
							$insert_id = $insert_result[0]->ins_id;
							$code_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT employee_code FROM `cw_employees` where prime_employees_id = ".$insert_id." and trans_status = 1')");
							$code_result = $code_info->result();
							$code_info->next_result();
							$employee_code = $code_result[0]->employee_code;
							$formula_process[] = $employee_code;
							$status_info['Status'] = "Successfully datas are imported";
							$emp_data = array("Compcode"=>"C0001","CODE"=>$code,"EMPNAME"=>$empname,"DEPT"=>$department,"DESIG"=>$designation,"DOJ"=>$doj,"DOB"=>$dob,"MARTIAL"=>$marital_status,"SEX"=>$gender,"cCode"=>$role);
							//$this->curl($emp_data);
							$imp_sts = False;
						}else{
							$imp_sts = True;
						}
					}else{
						if((int)$exist_count === 1){
							$upd_prime_id = (int)$exist_result[0]->$prime_id;
							$upd_query = 'UPDATE '.$this->prime_table.' SET '.$prime_upd_query.' trans_updated_by = "'.$this->logged_id.'",trans_updated_date = "'.$created_on.'" WHERE '.$prime_id.' = "'.$upd_prime_id.'"';
							$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
							$status_info['Status'] = "Successfully values are updated";
							$code_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT employee_code FROM `cw_employees` where prime_employees_id = ".$upd_prime_id." and trans_status = 1')");
							$code_result = $code_info->result();
							$code_info->next_result();
							$employee_code = $code_result[0]->employee_code;
							$formula_process[] = $employee_code;
							$emp_data = array("Compcode"=>"C0001","CODE"=>$code,"EMPNAME"=>$empname,"DEPT"=>$department,"DESIG"=>$designation,"DOJ"=>$doj,"DOB"=>$dob,"MARTIAL"=>$marital_status,"SEX"=>$gender,"cCode"=>$role);
							//$this->curl($emp_data);
						}else{
							$imp_sts = True;
						}
						
					}
				}
			}
			$status_array[] = $status_info;
		}
	}
	
	if($imp_sts){
		$table_info = "";
		$status     = False;
		$msg        = "Invalid Excel Format to Import";
	}else{
		$status     = True;
		$msg        = "Successfully files imported in database!!!";
		$table_info = $this->get_excel_import_ui($status_array);
	}
	$result   = $this->get_trans_array($formula_process);
	$emp_rslt = $this->Formula_model->import_formula($result);
	if($emp_rslt){
		foreach($emp_rslt as $emp_code => $final_trans){
			$import_update = "";
			foreach($final_trans as $col_key => $col_value){
				$column_name  = $col_key;
				if(($column_name !== "$prime_id") && ($column_name !== "user_name")) {
					if(($column_name == "confirmation_date") || ($column_name == "retirement_date")) {
						$column_value =  date('Y-m-d',strtotime($col_value));
					}else{
						$column_value  = $col_value;
					}
					$import_update    .= $column_name.' = "'.$column_value.'",';
				}	
			}
			$import_update     = rtrim($import_update,",");
			$import_update_query = 'UPDATE '.$this->prime_table.' SET '.$import_update.' WHERE employee_code= "'.$emp_code.'"';
			$this->db->query("CALL sp_a_run ('UPDATE','$import_update_query')");
		}
		//$this->esi_statutory();
	}
	echo json_encode(array('success'=>$status,'message'=>$msg,'table_info'=>$table_info));
}
//ERROR COLUMN AND ROW DISPLAY FOR INVALID DATAS
public function get_excel_error_ui($err_column_tabview){
	$table_info = "";
	$th_line = "";
	$tr_line = "";
	foreach($err_column_tabview as $err_column){
		foreach($err_column as $key => $value){
			$tr_line .= "<tr><td>$key</td><td style='color:#ff0303 !important;'>$value</td></tr>";
		}
	}
	$table_info = "<table class='table table-bordered'>
	<thead>
	<tr>
	<th>Row and Column</th>
	<th>Input Column</th>
	</tr>
	</thead>
	<tbody>
	$tr_line
	</tbody>
	</table>";
	return $table_info;
}
//get all insert emp id
public function get_trans_array($formula_process){	
	$trans_array = array();
	foreach($formula_process as $key => $value){
		$emp_data_query = 'select * from '.$this->prime_table.' where cw_employees.trans_status=1 and cw_employees.employee_code = "'.$value.'"';
		$emp_data_info   = $this->db->query("CALL sp_a_run ('RUN','$emp_data_query')");
		$emp_data_result = $emp_data_info->result_array();
		$emp_data_info->next_result();
		$trans_array[$value] = $emp_data_result[0];
	}
	return $trans_array;
}
		
public function create_formula_file(){
	$filename = dirname(__FILE__)."/"."Formula_model.php";
	$filename = str_replace('controllers','models',$filename);
	$can_process = false;
	if(file_exists($filename)){
		$created_date     = date("Y-m-d H:i:s",filemtime($filename));
		$isupdated_qry    = 'SELECT count(*) as tot_count FROM cw_form_bind_input WHERE trans_created_date >= "'.$created_date.'" or trans_updated_date >= "'.$created_date.'"';
		$isupdated_data   = $this->db->query("CALL sp_a_run ('SELECT','$isupdated_qry')");
		$isupdated_result = $isupdated_data->result();
		$isupdated_data->next_result();
		$tot_count = (int)$isupdated_result[0]->tot_count;
		if((int)$tot_count > 0){
			$can_process = true;
		}else{
			$can_process = false;
		}
	}else{
		$can_process = true;
	}
	if($can_process){
		$category_info   = $this->db->query("CALL sp_a_run ('select','select * from `cw_category` where trans_status = 1')");
		$category_result    = $category_info->result();
		$category_info->next_result();
		$import_formula = array();
		foreach($category_result as $category){
			$category_id   = $category->prime_category_id;
			$category_name = $category->category_name;
			$input_query = 'select * from cw_form_bind_input inner join cw_form_condition_formula on cw_form_condition_formula.prime_cond_id = cw_form_bind_input.input_cond_id where cw_form_bind_input.trans_status= 1 and input_cond_module_id = "employees" and FIND_IN_SET("'.$category_id.'",condition_for) order by cond_order asc';
			$input_data   = $this->db->query("CALL sp_a_run ('SELECT','$input_query')");
			$input_result = $input_data->result();
			$input_data->next_result();
			$line_input_bind_col = "";
			$line_input = "";
			foreach($input_result as $input){
				$line_input_bind_to      = $input->line_input_bind_to;
				$line_input_bind_col     = $input->line_input_bind_col;
				$is_drop_down            = $input->is_drop_down;
				$cond_drop_down          = $input->cond_drop_down;
				$line_input_bind_table   = $input->line_input_bind_table;
				$condition_check_form    = $input->condition_check_form;
				$condition_check_form    = explode(",",$condition_check_form);
				$line_input_bind_col     = str_replace("~","'",$line_input_bind_col);
				$line_input_bind_col     = str_replace("!",'"',$line_input_bind_col);
				if($line_input_bind_col){
					foreach($condition_check_form as $check_form){
						if(strpos($line_input_bind_col,"@$check_form@") !== false) {
							$preg_match      = preg_match('#\@(.*?)\@ (months|years)#', $line_input_bind_col);
							if($preg_match){
								$pattern = '/\@(.*?)\@ (months|years)/i';
								$replacement = '".$trans[\'${1}\']." ${2}';
								$match_line =  preg_replace($pattern, $replacement, $line_input_bind_col);
								$value = "\$trans['".$check_form."']";
								$line_input_bind_col = str_replace("@$check_form@",$value, $match_line);
							}
							$value = "\$trans['".$check_form."']";
							$line_input_bind_col = str_replace("@$check_form@",$value, $line_input_bind_col);
							$line_input_bind_col = str_replace("return","\$trans['$line_input_bind_to']=", $line_input_bind_col);
						}
					}
				}
				if($is_drop_down == 1){
					if($line_input_bind_col){
						$line_input_bind_col = "\n\t\t\t\tif(\$trans['".$cond_drop_down."'] == ".$line_input_bind_table."){\n\t\t\t\t\t".$line_input_bind_col."\n\t\t\t\t}";
						$line_input .= $line_input_bind_col;
					}else{
						$line_input = "";
					}
				}else{
					$line_input = $line_input_bind_col;
				}
				
				$import_formula[$category_id][$line_input_bind_to] = array("formula"=>$line_input);
			}
		}
		$formula_code = "";
		foreach($import_formula as $cat => $formulas){
			$formula_line = "";
			foreach($formulas as $key => $value){
				$formula_line    .= $value['formula'];
			}
			$formula_code .= "\n\t\t\t".'if((int)$trans["role"] === '.$cat."){\n\t\t\t\t$formula_line\n\t\t\t}";
		}	
		$emp_code    = "\n\t\t\t".'$employee_code = $trans["employee_code"];';
		$import_code = "\n\t\t".'foreach($trans_array as $trans){'.$emp_code.$formula_code."\n\t\t \$trans_array[\$employee_code] = \$trans;\n\t\t} \n\t\t";
		$fname = "import_formula(\$trans_array){";
		$final_code    = "<?php\nclass Formula_model extends CI_Model{\n\tpublic function $fname $import_code \n\t\treturn \$trans_array;\n\t}\n}\n?>";
		$formula_temp_file = dirname(__FILE__)."/"."Formula_model.php";
		$formula_temp_file = str_replace('controllers','models',$formula_temp_file);
		fopen("$formula_temp_file", "w");
		file_put_contents("$formula_temp_file",$final_code);
		chmod($formula_temp_file, 0777);
	}
}

//ESI STATUS UPDATED DYNAMIC BASED ON STATUS
	public function esi_statutory(){
		$get_emp_info_qry      ='select employee_code,role from cw_employees where trans_status = 1';
		$get_emp_data    = $this->db->query("CALL sp_a_run ('SELECT','$get_emp_info_qry')");
		$get_emp_result  = $get_emp_data->result();
		$get_emp_data->next_result();
		foreach($get_emp_result as $emp_rslt){
			$role          = $emp_rslt->role;
			$employee_code = $emp_rslt->employee_code;
			$get_esi_stat_qry      = 'select esi_limit,esi_eligibilit_formula from cw_statutory where trans_status = 1 and category = "'.$role.'"';
			$esi_statutory_data    = $this->db->query("CALL sp_a_run ('SELECT','$get_esi_stat_qry')");
			$esi_statutory_result  = $esi_statutory_data->result();
			$esi_statutory_data->next_result();
			if($esi_statutory_result){
				$esi_limit        = $esi_statutory_result[0]->esi_limit;
				$esi_elig_formula = $esi_statutory_result[0]->esi_eligibilit_formula;
				$esi_elig_formula = str_replace('@', '', $esi_elig_formula);
				$esi_elig_query = 'SELECT '.$esi_elig_formula.' AS esi_elig_amt FROM cw_employees WHERE trans_status = 1 and employee_code = "'.$employee_code.'"';
				$esi_elig_data    = $this->db->query("CALL sp_a_run ('SELECT','$esi_elig_query')");
				$esi_elig_result  = $esi_elig_data->result();
				$esi_elig_data->next_result();
				$esi_elig_amt = $esi_elig_result[0]->esi_elig_amt;
				if($esi_elig_amt > $esi_limit){
					$upd_esi_elig_query  = 'UPDATE cw_employees SET esi_eligibility = 2 WHERE trans_status = 1 and employee_code="'.$employee_code.'"';
				}else{
					$upd_esi_elig_query  = 'UPDATE cw_employees SET esi_eligibility = 1 WHERE trans_status = 1 and employee_code="'.$employee_code.'"';
				}
				$this->db->query("CALL sp_a_run ('UPDATE','$upd_esi_elig_query')");
			}
		}
		return true;
	}
	/* ==============================================================*/
	/* =================== IMPORT OPERATION - END ===================*/
	/* ==============================================================*/
	
	/*public function curl($emp_data){
		$postdata = '';
		foreach($emp_data as $key => $val){ 
			$postdata .= $key . '='.$val.'&'; 
		}
		$postdata = rtrim($postdata, '&');
		$url_path = base_url();
		$url = $url_path."app/timeoffice_api.php?frm=save_emp";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, count($postdata));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result= curl_exec($ch);
		curl_close($ch);
	}*/
	
	//CURL GET METHOD TO FETCH MASTER DATA
	public function curl($post_url,$post_data=null){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $post_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post_data,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}
/* ==============================================================*/
/* ================== PRINT OPERATION - START ===================*/
/* ==============================================================*/

	//NEHA EDIT START
	public function sent_print($view_id){
		
		if($this->control_name === "offer_letter"){
			$cat_query = 'select category from cw_offer_letter where prime_offer_letter_id = "'.$view_id.'" and trans_status = "1"';
			$cat_data     = $this->db->query("CALL sp_a_run ('SELECT','$cat_query')");
			$cat_result = $cat_data->result();
			$cat_data->next_result();
			$category = $cat_result[0]->category;
			$qry      =  '  and FIND_IN_SET("'.$category.'",print_info_for)';
		}else{
			$qry  ='';
		}
		
		/*$category_query = "print_info_for LIKE \"%".$category."%\" OR print_info_for LIKE \"".$category."%\" OR print_info_for LIKE \"%".$category."\" OR ";
		$category_query = rtrim($category_query,"OR ");  
		$query = 'select print_info_for from cw_print_info where print_info_module_id = "'.$this->control_name.'" and trans_status = "1"';
		$query_data   = $this->db->query("CALL sp_a_run ('SELECT','$query')");
		$query_result = $query_data->result();
		$query_data->next_result();
		foreach($query_result as $result){
			$print_info_for  = explode(",",$result->print_info_for);
		}*/
		
		$print_query = 'select prime_print_info_id from cw_print_info where print_info_module_id = "'.$this->control_name.'" and trans_status = "1"'.$qry;
		$print_data   = $this->db->query("CALL sp_a_run ('SELECT','$print_query')");
		$print_result = $print_data->result();
		$print_data->next_result();
		$print_doc_id = $print_result[0]->prime_print_info_id;
		$data = $this->load_print_data($print_doc_id,$view_id);
		$path = $this->control_name ."/print";
		$data['control_name'] = $this->control_name;
		$this->load->view($path,$data);	
	}

	public function load_print_data($print_doc_id,$view_id){
		$data['print_sts'] = false;
		$design_qry    = 'select print_design,print_type from cw_print_design inner join cw_print_info on cw_print_info.prime_print_info_id=cw_print_design.print_design_for where print_design_for = "'.$print_doc_id.'" and cw_print_info.trans_status = 1';
		$design_data   = $this->db->query("CALL sp_a_run ('SELECT','$design_qry')");
		$design_result = $design_data->result();
		$design_data->next_result();
		$print_design    = $design_result[0]->print_design;
		$print_type      = $design_result[0]->print_type;
		if((int)$print_type === 4){
			$style = '';
		}else{
			$style  = "<style>
			table{
				border: 1px !important;
				border-collapse: collapse !important;
				empty-cells: show !important;
				max-width: 100% !important;
				font-size: 13px !important;
			}
			tbody {
				border: 1px !important;
				border-collapse: collapse !important; 
				empty-cells: show !important;
				max-width: 100% !important;
				font-size: 13px !important;
			}
			td, th {
				border: 1px solid #000 !important;
				font-size: 13px !important;
			}
			td.fr-thick,th.fr-thick {
				border-width: 2px !important;
			}
			table.fr-dashed-borders td, table.fr-dashed-borders th {
				border-style: dashed !important;
			}
			</style>";
		}
		$print_design  = $style."".$print_design;
		$print_design  = str_replace('~','"',$print_design);
		$block_qry    = 'select * from cw_print_block where print_block_for = "'.$print_doc_id.'" and trans_status = 1';
		$block_data   = $this->db->query("CALL sp_a_run ('SELECT','$block_qry')");
		$block_result = $block_data->result();
		$block_data->next_result();
		foreach($block_result as $block){
			$prime_print_block_id  = $block->prime_print_block_id;
			$print_block_name      = $block->print_block_name;
			$print_block_type      = (int)$block->print_block_type;
			$print_block_table     = $block->print_block_table;
			$print_block_column    = $block->print_block_column;
			$suppressed_data       = $block->suppressed_data;
			$cumulative_data       = $block->cumulative_data;
			
			$table_qry    = 'select * from cw_print_table where print_table_for_id = "'.$prime_print_block_id.'" and trans_status = 1';
			$table_data   = $this->db->query("CALL sp_a_run ('SELECT','$table_qry')");
			$table_result = $table_data->result();
			$table_data->next_result();
			$line_table_query = "";
			$cutome_table_check = array('transactions'=>'cw_transactions');
			foreach($table_result as $table){
				$line_prime_table      = $table->line_prime_table;
				$line_prime_col        = $table->line_prime_col;
				$line_join_type        = $table->line_join_type;
				$line_join_table       = $table->line_join_table;
				$line_join_col         = $table->line_join_col;
				$line_sort             = $table->line_sort;
				$module_name           = str_replace("cw_","",$line_prime_table);
				$prime_id              = "prime_".$module_name."_id";
				$cf_id                 = "prime_".$module_name."_cf_id";
				$cf_table_name         = $this->db->dbprefix($module_name."_cf");
				$join_module_name      = str_replace("cw_","",$line_join_table);
				$join_prime_id         = "prime_".$join_module_name."_id";
				$join_cf_id            = "prime_".$join_module_name."_cf_id";
				$join_cf_table_name    = $this->db->dbprefix($join_module_name."_cf");	
				if((int)$line_sort === 1){
					if($cutome_table_check[$module_name]){
						$line_prime_table = " $line_prime_table ";
					}else{
						$line_prime_table = " $line_prime_table inner join $cf_table_name on $line_prime_table.$prime_id = $cf_table_name.$prime_id ";
					}
					if($cutome_table_check[$join_module_name]){
						$line_join_table = " $line_join_table on $line_join_col = $line_prime_col";
					}else{
						$line_join_table = " $line_join_table on $line_join_col = $line_prime_col inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id ";
					}
					$line_table_query .= " $line_prime_table  $line_join_type join $line_join_table"; 
				}else{
					if($cutome_table_check[$join_module_name]){
						$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col "; 
					}else{
						$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id "; 
					}
				}
			}
			if(!$line_table_query){
				$module_name      = str_replace("cw_","",$print_block_table);
				$prime_id         = "prime_".$module_name."_id";
				$cf_id            = "prime_".$module_name."_cf_id";
				$cf_table_name    = $this->db->dbprefix($module_name."_cf");
				$line_table_query = " $print_block_table ";
			}
			if(!$print_block_column){
				$print_block_column = "*";
			}else{
				$select_query = "";
				$select_ytd_query = "";
				$pick_query   = "";
				$map_column = explode(",",$print_block_column);
				foreach($map_column as $table_column){
					$map_column   = explode(".",$table_column);
					$table_name   = $map_column[0];
					$column 	  = $map_column[1];
					$control_name = str_replace('cw_',"",$table_name);
					if($control_name === "transactions"){
						$control_name = "employees";
					}
					$form_qry    = 'select prime_form_id,view_name,label_name,field_type,pick_list_type,pick_list,pick_table,auto_prime_id,auto_dispaly_value from cw_form_setting where prime_module_id = "'.$control_name.'" and  label_name = "'.$column.'"  and trans_status = "1"';
					$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
					$form_result = $form_data->result();
					$form_data->next_result();
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
						}else
						if(($field_type === 2) || ($field_type === 3)){
							$label_ytd  =	$label_name."_ytd";
							$select_ytd_query .= "sum($table_name.$label_name) as $label_ytd, ";
							$select_query .= "$table_name.$label_name , ";
						}else{
							$select_query .= "$table_name.$label_name , ";
						}
					}					
				}
			}
			$where_trans = "";
			$where_trans_info = explode(",",$print_block_table);
			foreach($where_trans_info as $trans_info){
				if($trans_info === "cw_transactions"){
					$select_query .= "cw_transactions.transactions_month , ";
				}				
				$where_trans .= "$trans_info.trans_status = 1 and ";
			}
			$where_trans = rtrim($where_trans,'and ');
			$where_qry    = 'select * from cw_print_table_where where where_for_id = "'.$prime_print_block_id.'" and trans_status = 1';
			$where_data   = $this->db->query("CALL sp_a_run ('SELECT','$where_qry')");
			$where_result = $where_data->result();
			$where_data->next_result();
			$where_condition = "";
			if($where_result){
				$where_condition = str_replace('^','"',$where_result[0]->where_condition);
				$where_condition = str_replace('@logged_id@',$view_id,$where_condition);				
				$session_date_list  = array("logged_DMY"=>"d-m-Y","logged_YMD"=>"Y-m-d","logged_MY"=>"m-Y","logged_YM"=>"Y-m","logged_Y"=>"Y"); 
				$session_query      = 'select session_value from cw_session_value where session_for = 1 and trans_status = "1"';
				$session_data       = $this->db->query("CALL sp_a_run ('SELECT','$session_query')");
				$session_result     = $session_data->result();
				$session_data->next_result();
				foreach($session_result as $rslt){
					$session_value 	   = $rslt->session_value;
					if($session_value !== "access_data"){
						$exist_val = "@".$session_value."@";
						if($session_date_list[$session_value]){
							$date_formate      = $session_date_list[$session_value];
							$saved_session_val = date($date_formate);
						}else{
							$saved_session_val = $this->session->userdata($session_value);
						}
						$where_condition  = str_replace($exist_val,$saved_session_val,$where_condition);
					}
				}
			}
			$select_query = rtrim($select_query,',');
			$select_query = rtrim($select_query,' , ');
			if((int)$cumulative_data === 1){
				$start_fin_date = $this->financial_info[0]->start_date;
				$start_fin_date = date('m-Y',strtotime($start_fin_date));
				$end_fin_date   = $this->financial_info[0]->end_date;
				$end_fin_date   = date('m-Y',strtotime($end_fin_date));
				$select_ytd_query = rtrim($select_ytd_query,',');
				$select_ytd_query = rtrim($select_ytd_query,' , ');
				$where_ytd_condition  = ' and date_format(str_to_date(transactions_month, "%m-%Y") , "%Y-%m")  >= date_format(str_to_date("'.$start_fin_date.'", "%m-%Y"), "%Y-%m") and date_format(str_to_date(transactions_month, "%m-%Y") , "%Y-%m")  <= date_format(str_to_date("'.$end_fin_date.'", "%m-%Y"), "%Y-%m")';
				$final_ytd_qry = "select $select_ytd_query from $line_table_query $pick_query  where $where_trans $where_condition  $where_ytd_condition";
				$final_ytd_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_ytd_qry')");
				$final_ytd_result = $final_ytd_data->result();
				$final_ytd_data->next_result();
				foreach($final_ytd_result as $ytd_rslt){
					$map_column = explode(",",$print_block_column);
					foreach($map_column as $table_column){
						$map_column   = explode(".",$table_column);
						$ytd_column 	  = $map_column[1]."_ytd";
						$ytd_value        = $ytd_rslt->$ytd_column;
						$replace_ytd_val  = "@".$ytd_column."@";
						$print_design  = str_replace($replace_ytd_val,$ytd_value,$print_design);
					}
				}
			}
			$final_qry = "select cw_employees.role,$select_query from ".$line_table_query." $pick_query where $where_trans $where_condition";
			$final_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
			$final_result = $final_data->result();
			$final_data->next_result();
			$tr_line = "";
			$th_line = "";
			$count = 0;
			$assign_date_formate_list  = array("DMY"=>"d-m-Y","YMD"=>"Y-m-d","DFY"=>"d F Y","MY"=>"F-Y","YM"=>"Y-F","D"=>"d","M"=>"M","Y"=>"Y");
			$split_qry    = 'select * from cw_print_split where trans_status = 1 and split_table_info ="'.$print_doc_id.'"';
			$split_data   = $this->db->query("CALL sp_a_run ('SELECT','$split_qry')");
			$split_result = $split_data->result();
			$split_data->next_result();
			$split_array = array();
			foreach($split_result as $split){
				$split_info  = $split->split_info;
				$split_colum = $split->split_colum;
				$split_array[$split_colum] = $split_info;
			}		
			if($final_result){
				$data['print_sts'] = true;
				foreach($final_result as $rslt){
					$count++;
					$map_column = explode(",",$print_block_column);
					$td_line = "";
					foreach($map_column as $table_column){
						$map_column   = explode(".",$table_column);
						$column 	  = $map_column[1];
						$value        = $rslt->$column;
						$replace_val  = "@".$column."@";
						//amount number is changed to in words for net pays--07SEP2019
						if($column == 'net_pay'){
							$value         = $rslt->$column;
							$print_design  = str_replace($replace_val,$value,$print_design);
							$net_pay_val   = $value;
							$net_pay_words = $this->numbertowords($net_pay_val);
							$net_pay_words = strtoupper($net_pay_words);
							$print_design  = str_replace("@net_pay_words@",$net_pay_words,$print_design);
						}else
						if($column == 'employee_name'){
							$value         = ucwords($rslt->$column);
							$print_design  = str_replace($replace_val,$value,$print_design);
						}else
						if($column == 'reporting_person'){
							$value         = ucwords($rslt->$column);
							$print_design  = str_replace($replace_val,$value,$print_design);
						}else
						if($column == 'salary'){
							$value         = $rslt->$column;
							$print_design  = str_replace($replace_val,$value,$print_design);
							$salary_val   = $value;
							$salary_words = $this->numbertowords($salary_val);
							$salary_words = ucwords($salary_words);
							$print_design  = str_replace("@salary_words@",$salary_words,$print_design);
						}
						
						if($split_array[$replace_val]){
							//Process split informtion 
							$process_function = $split_array[$replace_val];
							if((int)$process_function === 1){
								$transactions_month = $final_result[0]->transactions_month;
								$employee_code      = $final_result[0]->employee_code;
								$loan_info = $this->get_loan_value($transactions_month,$employee_code);
								$print_design = str_replace($replace_val,$loan_info,$print_design);
							}
						}else{
							if($print_block_type === 1){
								$print_design = str_replace($replace_val,$value,$print_design);
								foreach($assign_date_formate_list as $key=>$formate){
									if($column == 'transactions_month'){//transactions month static updated
										$start         = "@".$key."_";
										$end           = "_".$key."@";
										$replace_val   = $start.$column.$end;
										$value         = date('Y-m-d',strtotime("01-".$rslt->$column));
										$date_value    = date_create($value);
										$replace_value = strtoupper(date_format($date_value,$formate));
										$print_design  = str_replace($replace_val,$replace_value,$print_design);
									}else{//not static month updated
										$start         = "@".$key."_";
										$end           = "_".$key."@";
										$replace_val   = $start.$column.$end;
										$replace_val   = $start.$column.$end;
										$date_value    = date_create($value);
										$replace_value = date_format($date_value,$formate);
										$print_design  = str_replace($replace_val,$replace_value,$print_design);
									}
								}
							}else
							if($print_block_type === 2){
								$td_line .= "<td style='text-align:center;'>$value</td>";
							}
							if($count === 1){
								$head_name = ucwords(str_replace("_"," ",$column));
								$th_line .= "<th style='text-align:center;'>$head_name</th>";
							}
						}
					}
					if($print_block_type === 2){
						if($count === 1){
							$th_line  = "$th_line";
						}
						$tr_line .= "<tr>$td_line</tr>";
					}
				}
				if($print_block_type === 2){
					$table_list  = "<table style='width:100%;'><thead>$th_line</thead><tbody>$tr_line</tbody></table>";
					$replce_block = "@".strtolower(str_replace(" ","_",$print_block_name))."@";
					$print_design = str_replace($replce_block,$table_list,$print_design);
				}
			}
			$data['suppressed_data'] = $suppressed_data;
		}
		$print_design = str_replace("<br>","",$print_design);
		$data['print_design'] = $print_design;
		return $data;
	}
	//NEHA EDIT END
	
	//number to words changed in payslip
	public function numbertowords($number){
		$no       = round($number);
		$point    = round($number - $no, 2) * 100;
		$hundred  = null;
		$digits_1 = strlen($no);
		$i = 0;
		$str = array();
		$words = array('0' => '', '1' => 'One', '2' => 'Two',
			'3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
			'7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
			'10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
			'13' => 'Thirteen', '14' => 'Fourteen',
			'15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
			'18' => 'Eighteen', '19' =>'Nineteen', '20' => 'Twenty',
			'30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
			'60' => 'Sixty', '70' => 'Seventy',
			'80' => 'Eighty', '90' => 'Ninety');
		$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
		while ($i < $digits_1) {
			$divider = ($i == 2) ? 10 : 100;
			$number = floor($no % $divider);
			$no = floor($no / $divider);
			$i += ($divider == 10) ? 1 : 2;
			if ($number) {
				$plural = (($counter = count($str)) && $number > 9) ? '' : null;
				$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
				$str [] = ($number < 21) ? $words[$number] .
				" " . $digits[$counter] . $plural . " " . $hundred
				:
				$words[floor($number / 10) * 10]
				. " " . $words[$number % 10] . " "
				. $digits[$counter] . $plural . " " . $hundred;
			} else $str[] = null;
		}
		$str = array_reverse($str);
		$result = implode('', $str);
		$points = ($point) ? "." . $words[$point / 10]. " ".$words[$point = $point % 10] : '';
		return $result;
	}
	
	//Sheet Name display in import page
	public function sheet_name(){
		$file_path  = $this->input->post('file_path');
		$filename = dirname(__FILE__)."/php_excel/PHPExcel/IOFactory.php";
		include($filename);
		$excel_obj   = PHPExcel_IOFactory::load($file_path);
		$sheet_count = $excel_obj->getSheetCount();
		$sheet_name  = array();
		for($i= 0; $i< $sheet_count; $i++){
			$sheet        = $excel_obj->getSheet($i);
			$sheet_name[] = $sheet->getTitle();
		}
		echo json_encode(array('sheet_name' =>$sheet_name));
	}
	
	//IMPORT FILE VIEW INFORMATION
	public function import(){
		$data['module_id']     = $this->control_name;		
		$excel_format_qry = 'select prime_excel_format_id,excel_name from cw_util_excel_format where excel_module_id = "'.$this->control_name.'" and trans_status = 1';
		$excel_format   = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
		$excel_result    = $excel_format->result();
		$excel_format->next_result();
		$excel_format_drop[""] = "---- Excel Format ----";
		foreach($excel_result as $excel){
			$prime_excel_format_id = $excel->prime_excel_format_id;
			$excel_name            = $excel->excel_name;
			$excel_format_drop[$prime_excel_format_id] = $excel_name;
		}
		$data['excel_format_drop'] = $excel_format_drop;
		
		$this->load->view("$this->control_name/import",$data);
	}
	
	//NEHA EDIT START
	public function excel($module_id,$excel_format){
		$excel_format_qry = 'select excel_line_column_name,excel_line_value from cw_util_excel_format_line where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id ="'.$excel_format.'" and trans_status = 1';
		$excel_format   = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
		$excel_result    = $excel_format->result();
		$excel_format->next_result();		
		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();		
		//Set the first row as the header row
		foreach($excel_result as $excel){
			$excel_line_column_name = $excel->excel_line_column_name;
			$excel_line_value       = $excel->excel_line_value;
			$obj->getActiveSheet()->setCellValue($excel_line_value."1", $excel_line_column_name);
		}
		// Rename worksheet name
		 $filename= $module_id.".xls"; //save our workbook as this file name
		 header('Content-Type: application/vnd.ms-excel'); //mime type
		 header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		 header('Cache-Control: max-age=0'); //no cache
		 
		//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
		 //if you want to save it as .XLSX Excel 2007 format
		 $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel5');
		 //force user to download the Excel file without writing it to server's HD
		 $objWriter->save('php://output');
		echo json_encode(array('success' => TRUE, 'output' => $excelOutput));
	}
	//NEHA EDIT END
}
?>