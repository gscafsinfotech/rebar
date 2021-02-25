<?php
/**********************************************************
	   Filename: Custom action controller
	Description: Custom Action Controller for custom approval data move to main emoloyee master controller.
		 Author: Jaffer
	 Created on: 28 JAN 2020
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
abstract class Custom_base_controller extends Secure_Controller{
	public $control_name;
	public $logged_id;
	public $logged_role;
	public $logged_user_role;
	public $prime_id;
	public $prime_table;
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
	}
	
	//PROVIDE BASE DATA FOR MODULE
	public function collect_base_info(){
		$this->control_name      = 'employees';
		$this->logged_id         = $this->session->userdata('logged_id');
		$this->logged_role       = $this->session->userdata('logged_role');
		$this->logged_user_role  = $this->session->userdata('logged_user_role');
		$this->logged_emp_code   = $this->session->userdata('logged_emp_code');
		$this->prime_id          = "prime_custom_".$this->control_name."_id";
		$this->prime_table       = "cw_custom_".$this->control_name;
		$this->base_query        = "select @SELECT@ from $this->prime_table";
		$this->select_query      = "$this->prime_table.$this->prime_id,";
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
		$table_query = 'select label_name,view_name,field_type from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and input_view_type IN (1,2) and table_show = "1" and trans_status =1  ORDER BY table_sort asc';
		$table_info = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
		$result   = $table_info->result();
		$table_info->next_result();
		$this->table_head    = $result;
		$select_key          = array_column($result, "label_name");
		$this->select_query .= implode(",",$select_key);
	}
	
	// PROVIDE MODLE VIEWS
	public function get_view_info(){
		$view_query = 'select * from cw_form_view_setting  where prime_view_module_id = "'.$this->control_name.'" and form_view_show = "1" and trans_status =1 ORDER BY form_view_sort asc';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$this->view_info = $view_result;
	}
	
	// PROVIDE MODLE FORM INPUT VIEWS
	public function get_form_info(){
		$from_query = 'select * from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and field_show = "1" and trans_status =1 ORDER BY input_for,field_sort asc';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$from_query')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$this->form_info = $form_result;
	}
	// PROVIDE MODLE TABLE ROLE BASED SEARCH
	public function get_role_condition(){
		$table_search_query = 'select where_condition from cw_form_table_search  where query_module_id = "custom_approval" and query_for = "'.$this->logged_user_role.'" and trans_status = "1"';
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
			if($this->logged_user_role == 4){
				$this->role_condition = $where_condition. " and manager_status in (1) ";
			}else{
				$this->role_condition =  $where_condition;
			}
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
							$pick_query = "select $pick_list from $pick_table where trans_status = 1  $where_condition";
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
					$send_url     = site_url("custom_approval/bind_autocomplete_suggest");
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
					
					$send_url  = site_url("custom_approval/bind_change_suggest");
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
					$fill_input .= "$('#$bind_to').val($fill_val);\n $('#$bind_to').trigger('change');";
				}
				$send_url  = site_url("custom_approval/calculation_suggest");
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
				
				$join_module_name      = str_replace("cw_","",$line_join_table);
				$join_prime_id         = "prime_".$join_module_name."_id";
				
				if((int)$line_sort === 1){
					$line_table_query .= " $line_prime_table $line_join_type join $line_join_table on $line_join_col = $line_prime_col"; 
				}else{
					$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col"; 
				}
			}
			if(!$line_table_query){
				$module_name      = str_replace("cw_","",$condition_table);
				$prime_id         = "prime_".$module_name."_id";
				$line_table_query = " $condition_table  ";
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
		
		$table_name      = "custom_".$module_id."_".$row_label_name;
		$table_prime     = "prime_".$table_name."_id";
		$table_name      = $this->db->dbprefix($table_name);
		
		$prime_qry_key   = "prime_custom_".$module_id."_id,";
		$prime_qry_value = '"'.$prime_id.'",';
		$prime_upd_query = "";

		$form_qry  = 'select * from cw_form_setting where prime_module_id = "'.$module_id.'" and  input_for = "'.$view_id.'" and  field_show = "1" and trans_status = 1';
		$form_data = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
		$form_result = $form_data->result();
		$form_data->next_result();
		foreach($form_result as $setting){
			$field_type      = $setting->field_type;
			$input_view_type = (int)$setting->input_view_type;
			$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
			$field_isdefault = $setting->field_isdefault;
			if((int)$field_type === 7){
				$multi_name = $label_id."[]";
				$value = implode(",",$this->input->post($multi_name));
			}else{
				$value = $this->input->post($label_id);
			}			
			if((int)$field_type === 4){
				$value = date('Y-m-d',strtotime($value));
			}else
			if((int)$field_type === 13){
				$value = date('Y-m-d H:i:s',strtotime($value));
			}			
			$prime_qry_key     .= $label_id.",";
			$prime_qry_value   .= '"'.$value.'",';
			$prime_upd_query   .= $label_id.' = "'.$value.'",';
			$exist_qry         .= $label_id.' = "'.$value.'" and ';
		}
		$created_on  = date("Y-m-d h:i:s");
		$exist_count = 0;
		
		if((int)$row_prime_id === 0){
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
			$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
			$prime_update_query  = "UPDATE $table_name SET ". $prime_upd_query .' WHERE '. $table_prime .' = "'. $row_prime_id .'"';
			$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
			$row_set_data = $this->get_row_set_data($view_id,$prime_id);
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $row_prime_id,'row_set_data' => $row_set_data));
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
			$auto_dispaly_value = $form->auto_dispaly_value;;
			
			$input_value       = $row_result[0]->$label_name;
			if((int)$field_type === 4){
				$input_value = date('d-m-Y',strtotime($input_value));
				if($input_value === "01-01-1970"){
					$input_value = date('d-m-Y');
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
		$table_name      = "custom_".$this->control_name."_".$form_view_label_name;
		$row_prime_id    = "prime_".$table_name."_id";
		$table_name      =  $this->db->dbprefix($table_name);
		$table_prime_id  = "prime_custom_".$this->control_name."_id";
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
		$final_qry    = "select $select_query from $table_name $pick_query " .' where '.$table_name.'.'.$table_prime_id.' = "'.$prime_id.'" and '.$table_name.'.trans_status = "1" order by abs('.$table_name.'.'.$row_prime_id.') desc';
		$row_data     = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		$row_result   = $row_data->result();
		$row_data->next_result();
		$tr_line = "";
		foreach($row_result as $data){
			$td_line = "";
			foreach($table_head as $label){
				$value = $data->$label;
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
	/* ==============================================================*/
	/* =================== ROWSET OPERATION - END ===================*/
	/* ==============================================================*/
}
?>