<?php
	/**********************************************************
		Filename: Base Controller
		Description: Base Controller for all dynamic module controller.
		Author: udhayakumar Anandhan
		Created on: ‎12 ‎December ‎2018
		Reviewed by:
		Reviewed on:
		Approved by:
		Approved on:
		-------------------------------------------------------
		Modification Details
		Changed by: Jaffer
		Change Info: Multiselect picklist updates for view
		-------------------------------------------------------
		***********************************************************/
		if ( ! defined('BASEPATH')) exit('No direct script access allowed');
		require_once("Secure_Controller.php");
		abstract class Base_controller extends Secure_Controller{

			public $control_name;
			public $table_info;
			public $monthly_info;
			public $fanf_header_info;
			public $view_info;
			public $form_info;
			public $financial_info;
			public $print_info;
			public $table_search_info;
			public $table_search_qry;
			public $prime_id;
			public $cf_id;
			public $prime_table;
			public $cf_table;
			public $pro_name;
			public $logged_id;
			public $logged_role;
			public $select_query    = "";
			public $view_select     = "";
			public $base_query      = "" ;
			public $pick_query      = "";
			public $all_pick        = array();
			public $fliter_list     = array();
			public $quick_link      = array();
			public $condition_list  = array();

			public function __construct($module_id = NULL){
				parent::__construct($module_id);
				echo $this->create_formula_file();
			}

		//PROVIDE BASE DATA FOR MODULE
			public function collect_base_info(){
				$this->control_name = strtolower($this->router->fetch_class());
				$this->logged_id    = $this->session->userdata('logged_id');
				$this->logged_role  = $this->session->userdata('logged_role');
				$this->prime_id     = "prime_".$this->control_name."_id";
				$this->cf_id        = "prime_".$this->control_name."_cf_id";
				$this->prime_table  = $this->db->dbprefix($this->control_name);
				$cf_table_name      = $this->control_name."_cf";
				$this->cf_table     = $this->db->dbprefix($cf_table_name);
				$this->pro_name     = "sp_".$this->control_name."_search";
				$this->base_query   = "select  @SELECT  from $this->prime_table inner join $this->cf_table on $this->prime_table.$this->prime_id = $this->cf_table.$this->prime_id";

				$this->get_quick_link();
				$this->get_table_info();
				$this->get_view_info();
				$this->get_form_info();
				$this->get_table_search_info();
				$this->get_query_and_drop();
				$this->get_condition();
				$this->get_print_info();
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
				$this->quick_link = $link_result;
			}

		// PROVIDE TABLE VIEW
			public function get_table_info(){
				$table_query = 'select label_name,view_name,field_type from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and input_view_type IN (1,2) and table_show = "1" ORDER BY table_sort asc';
			//and FIND_IN_SET("'.$this->logged_role.'",field_for) Removed for show all inputs
				$table_info = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
				$result   = $table_info->result();
				$table_info->next_result();
				$this->table_info = $result;
			}

		// PROVIDE MODLE VIEWS
			public function get_view_info(){
				$view_query = 'select * from cw_form_view_setting  where prime_view_module_id = "'.$this->control_name.'" and form_view_show = "1" ORDER BY form_view_sort asc';
			//and FIND_IN_SET("'.$this->logged_role.'",form_view_for) Removed for show all inputs
				$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
				$view_result = $view_data->result();
				$view_data->next_result();
				$this->view_info = $view_result;
			}

		// PROVIDE MODLE FORM INPUT VIEWS
			public function get_form_info(){
				$from_query = 'select * from cw_form_setting  where prime_module_id = "'.$this->control_name.'" and field_show = "1" ORDER BY input_for,field_sort asc';
			//and FIND_IN_SET("'.$this->logged_role.'",field_for) Removed for show all inputs
				$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$from_query')");
				$form_result = $form_data->result();
				$form_data->next_result();
				$this->form_info = $form_result;
			}

		// PROVIDE MODLE FORM PRINT LIST
			public function get_print_info(){
				$print_query = 'select * from cw_print_info where print_info_module_id = "'.$this->control_name.'" and trans_status = "1"';
				$print_data   = $this->db->query("CALL sp_a_run ('SELECT','$print_query')");
				$print_result = $print_data->result();
				$print_data->next_result();
				$this->print_info = $print_result;
			}

		// PROVIDE MODLE TABLE DEFAULT SEARCH
			public function get_table_search_info(){
				$table_search_query = 'select where_condition from cw_form_table_search  where query_module_id = "'.$this->control_name.'" and query_for = "'.$this->logged_role.'" and trans_status = "1"';
				$table_search_data   = $this->db->query("CALL sp_a_run ('SELECT','$table_search_query')");
				$table_search_result = $table_search_data->result();
				$table_search_data->next_result();
				if($table_search_result){
					$where_condition  = str_replace('^','"',$table_search_result[0]->where_condition);
					$get_val = 1;
					if((int)$this->logged_role === 12){
						$get_val = 2;
					}
					$session_date_list  = array("logged_DMY"=>"d-m-Y","logged_YMD"=>"Y-m-d","logged_MY"=>"m-Y","logged_YM"=>"Y-m","logged_Y"=>"Y"); 
					$session_query  = 'select session_value from cw_session_value  where session_for = "'.$get_val.'" and trans_status = "1"';
					$session_data   = $this->db->query("CALL sp_a_run ('SELECT','$session_query')");
					$session_result = $session_data->result();
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
					$this->table_search_info = $where_condition;
				}
			}

		//PROVIDE QUERY AND DROPDOWN VALUES
			public function get_query_and_drop(){
				$this->select_query = "$this->prime_table.$this->prime_id,";
				$this->view_select = "$this->prime_table.$this->prime_id,";
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

					$array_list = array();
					if($field_isdefault === 1){
						$pick_sel_table = "$this->prime_table";
					}else
					if($field_isdefault === 2){
						$pick_sel_table = "$this->cf_table";
					}

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
							if($pick_table == "cw_payroll_formula"){
								$pick_query = "select $pick_list from $pick_table where trans_status = 1";
								$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
								$pick_result = $pick_data->result();
								$pick_data->next_result();
								$array_list[""] = "---- $label_name ----";
								foreach($pick_result as $pick){
									$pick_key = $pick->$pick_list_val_1;
									$pick_val = ucwords(str_replace("_"," ",$pick->$pick_list_val_2));
									$array_list[$pick_key] = $pick_val;
								}
							}else{
								if($label_id === "excemption_component"){
									$pick_query = "select $pick_list from $pick_table where trans_status = 1 and tax_section = 1 $qry";
								}else{
									$pick_query = "select $pick_list from $pick_table where trans_status = 1 $qry";
								}
								$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
								$pick_result = $pick_data->result();
								$pick_data->next_result();

								$array_list[""] = "---- $label_name ----";
								foreach($pick_result as $pick){
									$pick_key = $pick->$pick_list_val_1;
									$pick_val = $pick->$pick_list_val_2;
									$array_list[$pick_key] = $pick_val;
								}
							}
							$this->all_pick[$prime_form_id] = $array_list;
							$pick_query_as = $pick_table."_".$prime_form_id;
							if(($input_view_type === 1) || ($input_view_type === 2)){
								$this->select_query .= "$pick_query_as.$pick_list_val_2 as $label_id,";
								$this->pick_query .= " left join $pick_table as $pick_query_as on $pick_query_as.$pick_list_val_1 = $pick_sel_table.$label_id ";
							}
						}else
						if($pick_list_type === 2){ 
							$pick_list_val_1 = $pick_table."_id";
							$pick_list_val_2 = $pick_table."_value";
							$pick_list_val_3 = $pick_table."_status";

							$pick_query = "select $pick_list_val_1,$pick_list_val_2 from $pick_table where $pick_list_val_3 = 1";
							$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
							$pick_result = $pick_data->result();
							$pick_data->next_result();

							$array_list[""] = "---- $label_name ----";
							foreach($pick_result as $pick){
								$pick_key = $pick->$pick_list_val_1;
								$pick_val = $pick->$pick_list_val_2;
								$array_list[$pick_key] = $pick_val;
							}
							$this->all_pick[$prime_form_id] = $array_list;
							$pick_query_as = $pick_table."_".$prime_form_id;
							if(($input_view_type === 1) || ($input_view_type === 2)){
								$this->select_query .= "$pick_query_as.$pick_list_val_2 as $label_id,";
								$this->pick_query .= " left join $pick_table as $pick_query_as on $pick_query_as.$pick_list_val_1 = $pick_sel_table.$label_id ";
							}
						}
					}else
					if($field_type === 9){
						$pick_query_as = $pick_table."_".$prime_form_id;
						if(($input_view_type === 1) || ($input_view_type === 2)){
							$this->select_query .= "$pick_query_as.$auto_dispaly_value as $label_id,";
							$this->pick_query .= " left join $pick_table as $pick_query_as on $pick_query_as.$auto_prime_id = $pick_sel_table.$label_id ";
						}
					}else{
						if(($input_view_type === 1) || ($input_view_type === 2)){
							if($field_isdefault === 1){
								$this->select_query .= "$this->prime_table.$label_id,";
							}else
							if($field_isdefault === 2){
								$this->select_query .= "$this->cf_table.$label_id,";
							}
						}
					}			
					if(($input_view_type === 1) || ($input_view_type === 2)){
						if($field_isdefault === 1){
							$this->view_select .= "$this->prime_table.$label_id,";
						}else
						if($field_isdefault === 2){
							$this->view_select .= "$this->cf_table.$label_id,";
						}
						if($search_show === 1){
							$this->fliter_list[] = array('label_id'=> $label_id,'label_name'=> $label_name, 'field_isdefault'=> $field_isdefault, 'array_list'=> $array_list, 'field_type'=> $field_type);
						}
					}
				}
				$this->select_query  = rtrim($this->select_query,',');
				$this->view_select   = rtrim($this->view_select,',');
			// ONLY FOR EMPLOYEE AND CUSTOMER
				if($this->control_name === "employees"){
					$this->view_select   = $this->view_select .',user_name,password';
				}
				if($this->control_name === "customer"){
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
						if(($field_type === 1) || ($field_type === 2) || ($field_type === 3)){
							$on_bind_input  .= "$label_name,";
						}else
						if(($field_type === 5) || ($field_type === 6) || ($field_type === 7)){
							$on_change_input  .= "$label_name,";
						}
						if($condition_type === 2){
							if($field_type === 4){
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
					//need to clarify empty is check
										$fill_input = "";
										foreach($condition_bind_to as $bind_to){
											$fill_val    = "rslt.".$bind_to;
											$fill_input .= "$('#$bind_to').val($fill_val);\n $('#$bind_to').trigger('change');";
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
											if($load_script){
												$this->condition_list[] = $load_script;
											}
										}	
										/* ==============================================================*/
										/* ==================== BASE FUNCTIONS - END ====================*/
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
												foreach($col_list as $col){
													$suggest_query .= $col.' like "'.$search_term.'%" or ';
												}			
												$suggest_query  = rtrim($suggest_query," or ");

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
						$cf_id            = "prime_".$module_name."_cf_id";
						$cf_table_name    = $this->db->dbprefix($module_name."_cf");

						$join_module_name      = str_replace("cw_","",$line_join_table);
						$join_prime_id         = "prime_".$join_module_name."_id";
						$join_cf_id            = "prime_".$join_module_name."_cf_id";
						$join_cf_table_name    = $this->db->dbprefix($join_module_name."_cf");

						if((int)$line_sort === 1){
							$line_table_query .= " $line_prime_table inner join $cf_table_name on $line_prime_table.$prime_id = $cf_table_name.$prime_id $line_join_type join $line_join_table on $line_join_col = $line_prime_col inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id"; 
						}else{
							$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col inner join  $join_cf_table_name on $line_join_table.$join_prime_id = $join_cf_table_name.$join_prime_id"; 
						}
					}
					if(!$line_table_query){
						$module_name      = str_replace("cw_","",$condition_table);
						$prime_id         = "prime_".$module_name."_id";
						$cf_id            = "prime_".$module_name."_cf_id";
						$cf_table_name    = $this->db->dbprefix($module_name."_cf");
						$line_table_query = " $condition_table inner join $cf_table_name on $condition_table.$prime_id = $cf_table_name.$prime_id ";
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
						if($field_type === 4){
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
							chmod($dynamic_file_name, 0777);
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
							chmod($dynamic_file_name, 0777);
							require_once("$dynamic_file_name");
							$fianl_result_array[$line_input_bind_to] =  $line_input_bind_to();
							unlink("$dynamic_file_name");
						}
					}
				}
				echo json_encode($fianl_result_array);
			}
			/* ==============================================================*/
			/* ================== CONDITION OPERATION - END =================*/
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
					}			
					$prime_qry_key     .= $label_id.",";
					$prime_qry_value   .= '"'.$value.'",';
					$prime_upd_query   .= $label_id.' = "'.$value.'",';
				}
				$created_on = date("Y-m-d h:i:s");
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
					$auto_dispaly_value = $form->auto_dispaly_value;

					$input_value       = $row_result[0]->$label_name;
					if((int)$field_type === 4){
						$input_value = date('d-m-Y',strtotime($input_value));
						if($input_value === "01-01-1970"){
							$input_value = date('d-m-Y');
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

		//rowset save table display

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
			if($table_name === "cw_employees_language"){//Multiselect updates for display view
				$final_qry    = 'select cw_employees_language.prime_employees_language_id,cw_employees_language.prime_employees_id,cw_employees_language.languages_known, GROUP_CONCAT(cw_zct_19.cw_zct_19_value) as language_proficiency from cw_employees_language left join cw_zct_19 as cw_zct_19 on find_in_set(cw_zct_19.cw_zct_19_id,cw_employees_language.language_proficiency) where cw_employees_language.prime_employees_id = "'.$prime_id.'" and cw_employees_language.trans_status = 1 order by abs(cw_employees_language.prime_employees_language_id) asc';
			}else{
				$final_qry    = "select $select_query from $table_name $pick_query " .' where '.$table_name.'.'.$table_prime_id.' = "'.$prime_id.'" and '.$table_name.'.trans_status = "1" order by abs('.$table_name.'.'.$row_prime_id.') asc';
			}
			$row_data     = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
			$row_result   = $row_data->result();
			$row_data->next_result();
			$tr_line = "";
			foreach($row_result as $data){
				$td_line = "";
				foreach($table_head as $key){
					$value = $data->$key;
					$td_line .= "<td>$value</td>";
				}
				$row_id   = $data->$row_prime_id;
				$tab_name = $this->control_name."_".$form_view_label_name;
				$edit_btn   = "<a class='btn btn-edit btn-xs row_btn' onclick = row_set_edit('$row_id','$tab_name','$prime_form_view_id');>Edit</a>";
				$remove_btn = "<a class='btn btn-danger btn-xs row_btn' onclick = row_set_remove('$row_id','$tab_name','$prime_form_view_id','$prime_id');>Delete</a>";
				$tr_line .= "<tr>$td_line<td>$edit_btn $remove_btn</td></tr>";
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
			
			if($module_id === "employees"){
				$import_type_val = $import_type;
			}else{
				$import_type_val = 1;
			}
			
			$import_query = 'insert into cw_import (import_type,module_id,excel_format,excel_file_path,excel_sheet_name,excel_start_row,excel_end_row,trans_created_by,trans_created_date) value ("'.$import_type.'","'.$module_id.'","'.$excel_format.'","'.$excel_file_path.'","'.$excel_sheet_name.'","'.$excel_start_row.'","'.$excel_end_row.'","'.$logged_id.'","'.$today_date.'")';
			$import_info   = $this->db->query("CALL sp_a_run ('INSERT','$import_query')");
			$import_result = $import_info->result();
			$import_info->next_result();
			$import_id = $import_result[0]->ins_id;
			
			if($module_id === "employees"){
				echo $this->do_excel_emp_import($import_id);
			}elseif($module_id === "loan"){
				echo $this->do_loan_excel_import($import_id);
			}else{
				echo $this->do_excel_import($import_id);
			}
		}
		
//IMPORT DATA FROM FILE PATH OTHER MODULE
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
			$exist_column_name = explode(",",$format_rslt[0]->exist_column_name);
			$excel_format_qry 	= 'select view_name,duplicate_data,picklist_data,field_type,pick_table,pick_list_type,pick_list,mandatory_field,field_isdefault,excel_line_column_name,excel_line_value from cw_util_excel_format_line inner join cw_form_setting on label_name = excel_line_column_name where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id = "'.$excel_format.'" and cw_form_setting.prime_module_id = "'.$module_id.'" and cw_util_excel_format_line.trans_status = 1';		
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
				
				//COMMON DATA VALIDATION FOR ALL IMPORT MODULE
				$err_column_array    = array();
				$err_column_tabview  = array();
				foreach($excel_format_result as $excel_info){
					$mandatory_field         = (int)$excel_info->mandatory_field;
					$field_type              = (int)$excel_info->field_type;
					$excel_line_column_name  = $excel_info->excel_line_column_name;
					$excel_line_value        = $excel_info->excel_line_value;
					$view_name               = $excel_info->view_name;
					$pick_table              = $excel_info->pick_table;
					$pick_list_type          = (int)$excel_info->pick_list_type;
					$pick_list               = $excel_info->pick_list;
					$picklist_data           = (int)$excel_info->picklist_data;
					$duplicate_data          = (int)$excel_info->duplicate_data;
					
					$common_multi_cell_value = $sheet->rangeToArray("$excel_line_value$excel_row_start:$excel_line_value$total_rows", NULL, TRUE, TRUE, TRUE);
					if($mandatory_field === 1){
						$i = $excel_row_start;
						foreach($common_multi_cell_value as $common_value){
							foreach($common_value as $col_key =>$col_value){
								if(empty($col_value) && !is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "columns are empty and invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}elseif(($field_type === 5) || ($field_type === 7)){
									if($pick_list_type === 1){
										$pick_list_val   = explode(",",$pick_list);
										$pick_list_val_1 = $pick_list_val[0];
										$pick_list_val_2 = $pick_list_val[1];
										$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
										$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
										$pick_result = $pick_data->result();
										$pick_count  = $pick_data->num_rows();
										$pick_data->next_result();
										if((int)$pick_count === 0){
											$err_column_array['error']["$excel_line_value$i"] = $view_name;
											$msg_line = "column invalid data is present please check it?";
											$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
										}
									}else{
										if($pick_list_type === 2){
											$pick_list_val_1 = $pick_table."_id";
											$pick_list_val_2 = $pick_table."_value";
											$pick_list_val_3 = $pick_table."_status";
											$pick_query = 'select * from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
											$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
											$pick_result = $pick_data->result();
											$pick_count  = $pick_data->num_rows();
											$pick_data->next_result();
											if((int)$pick_count === 0){
												$err_column_array['error']["$excel_line_value$i"] = $view_name;
												$msg_line = "column invalid data is present please check it?";
												$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
											}
										}
									}
								}elseif($excel_line_column_name === "employee_code"){
									$emp_code_qry = 'select count(*) as rslt_count from cw_employees where trans_status = 1 and employee_code = "'.$col_value.'"';
									$emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
									$emp_data_result = $emp_data->result();
									$emp_data->next_result();
									$rslt_count = $emp_data_result[0]->rslt_count;
									if((int)$rslt_count === 0){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "employee code is not exit in employee master please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
								}
								$i++;
							}
						}
						}elseif(($field_type === 2) || ($field_type === 3)){//decimal and integer validations for non-mandatory field
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
									}
									$j++;
								}
							}
						}elseif($field_type === 4){
							$excel_cell_formate = $excel_obj->getActiveSheet()->getCell($col_key.$i)->getStyle()->getNumberFormat()->getFormatCode();
							$cell_formate = str_replace("[$-14009]","",$excel_cell_formate);
							$cell_formate = trim(strtoupper(str_replace(";@","",$cell_formate)));
						//echo "$col_key$i :: $col_value :: $excel_cell_formate :: $cell_formate\n";
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
						}
					}

				//END CHECKING
					$err_column_count = count($err_column_array['error']);
					$err_column       = implode(",",$err_column_array['error']);
					if((int)$err_column_count > 0) {
						$sts = true;
						$msg = "$err_column $msg_line";
					}
					if($sts){
						$table_info = $this->get_excel_error_ui($err_column_tabview);
						echo json_encode(array('success'=>false,'message'=>$msg,'table_info'=>$table_info));
						exit();
					}else{
						$status_array	    = array();
						for($row =$excel_row_start; $row <= $total_rows; $row++) {
							$prime_column_val = "";
							$prime_cell_val   = "";
							$cf_column_val    = "";
							$cf_cell_val      = "";
							$exist_val        = "";
							$status_info = array();
							$status_info["Excel Row"] = $row;

							foreach($excel_format_result as $excel_info){
								$field_isdefault        = (int)$excel_info->field_isdefault;
								$mandatory_field        = (int)$excel_info->mandatory_field;
								$field_type             = (int)$excel_info->field_type;
								$pick_table             = $excel_info->pick_table;
								$pick_list_type         = (int)$excel_info->pick_list_type;
								$pick_list              = $excel_info->pick_list;
								$excel_line_column_name = $excel_info->excel_line_column_name;
								$excel_line_value       = $excel_info->excel_line_value;
								$get_cell_value         = ucwords(trim($sheet->getCell("$excel_line_value$row")->getValue()));
								if(((int)$field_type === 4) || ((int)$field_type === 8)){
									$get_cell_value    = str_replace("'",'^', $get_cell_value);
								}
							// FOR DATE
								if($field_type === 4){
									$get_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
								}else
							// FOR PICKLIST CHECK
								if(($field_type === 5) || ($field_type === 7)){
									if($pick_list_type === 1){
										$pick_list_val   = explode(",",$pick_list);
										$pick_list_val_1 = $pick_list_val[0];
										$pick_list_val_2 = $pick_list_val[1];
										$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
										$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
										$pick_result = $pick_data->result();
										$pick_count  = $pick_data->num_rows();
										$pick_data->next_result();
										$created_on  = date("Y-m-d H:i:s");
										if((int)$pick_count === 0){
											$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ("'.$get_cell_value.'")';
											$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
											$ins_result = $ins_info->result();
											$ins_info->next_result();
											$get_cell_value     = $ins_result[0]->ins_id;
											$second_insert_id   = $ins_result[0]->ins_id;

											$cf_table = $pick_table."_cf";
											$prime_id = $pick_table."_id";
											$prime_id = str_replace("cw_","prime_",$prime_id);
											$second_column_val = "$prime_id,";
											$second_cell_val   = "$second_insert_id,";
											$second_column_val .= "trans_created_by,trans_created_date";
											$second_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
											$second_column_val  = rtrim($second_column_val,",");
											$second_cell_val    = rtrim($second_cell_val,",");
											$second_query  = "insert into $cf_table ($second_column_val) VALUES ($second_cell_val)";
											$this->db->query("CALL sp_a_run ('RUN','$second_query')");
										}else
										if((int)$pick_count === 1){
											$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
											$pick_status = (int)$pick_result[0]->trans_status;
											if($pick_status === 0){
												$upd_query  = 'update '.$pick_table.' set trans_status = 1 where '.$pick_list_val_1.' = '.$pick_id;
												$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
											}
											$get_cell_value = $pick_id;
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
									$prime_column_val .= $excel_line_column_name . ",";
									$prime_cell_val   .= "\'" . $get_cell_value . "\',";
									if(empty($exist_column_name)) {
										if($mandatory_field === 1){
											$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
									}else{
										if(in_array($excel_line_column_name,$exist_column_name)){
											$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
										$update_column_val = $excel_line_column_name;
										$update_cell_val   = '"'.$get_cell_value.'",';
										$prime_upd_query   .= $update_column_val."=".$update_cell_val;
									}
								}else
								if($field_isdefault === 2){
									$prime_column_val .= $excel_line_column_name . ",";
									$prime_cell_val   .= "\'" . $get_cell_value . "\',";
									if(empty($exist_column_name)) {
										if($mandatory_field === 1){
											$exist_val .= $this->cf_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
									}else{
										if(in_array($excel_line_column_name,$exist_column_name)){
											$exist_val .= $this->cf_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
										}
										$cf_update_column_val = $excel_line_column_name;
										$cf_update_cell_val   = '"'.$get_cell_value.'",';
										$cf_prime_upd_query   .= $cf_update_column_val."=".$cf_update_cell_val;
									}
								}
							}
							if($prime_column_val){
								$prime_id    = "prime_".$module_id."_id";
								$exist_val   = rtrim($exist_val," and ");
								$exist_query = "select count(*) exist_count,$this->prime_table.trans_status,$this->prime_table.$prime_id from $this->prime_table inner join $this->cf_table on $this->cf_table.$this->prime_id = $this->prime_table.$this->prime_id where $exist_val";
								$exist_info   = $this->db->query("CALL sp_a_run ('RUN','$exist_query')");
								$exist_result = $exist_info->result();
								$exist_info->next_result();
								$exist_count = $exist_result[0]->exist_count;
								$created_on  = date("Y-m-d h:i:s");
								if((int)$exist_count === 0){
									$prime_column_val .= "trans_created_by,trans_created_date";
									$prime_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';$prime_column_val  = rtrim($prime_column_val,",");
									$prime_cell_val    = rtrim($prime_cell_val,",");
									$prime_query       = "insert into $this->prime_table ($prime_column_val) VALUES ($prime_cell_val)";

									$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$prime_query')");
									$insert_result = $insert_info->result();
									$insert_info->next_result();
									$insert_id = $insert_result[0]->ins_id;
									$loan_id   = $insert_id;

									$cf_column_val .= "$prime_id,";
									$cf_cell_val   .= "$insert_id,";
									$cf_column_val .= "trans_created_by,trans_created_date";
									$cf_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
									$cf_column_val  = rtrim($cf_column_val,",");
									$cf_cell_val    = rtrim($cf_cell_val,",");
									$cf_query  = "insert into $this->cf_table ($cf_column_val) VALUES ($cf_cell_val)";
									$this->db->query("CALL sp_a_run ('RUN','$cf_query')");
									$status_info['Status'] = "Successfully datas are stored!!!";
								}else
								if((int)$exist_count === 1){
									$trans_status = (int)$exist_result[0]->trans_status;
									$upd_prime_id = (int)$exist_result[0]->$prime_id;
									if($trans_status === 0){
										$upd_query = 'UPDATE '.$this->prime_table.' SET trans_updated_by = "'.$this->logged_id.'",trans_updated_date = "'.$created_on.'" , trans_status = 1 WHERE '.$prime_id.' = "'.$upd_prime_id.'"';
										$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
										$status_info['status'] = "Changed to active";
									}else{
										$upd_query = 'UPDATE '.$this->prime_table.' SET '.$prime_upd_query.' trans_updated_by = "'.$this->logged_id.'",trans_updated_date = "'.$created_on.'" WHERE '.$prime_id.' = "'.$upd_prime_id.'"';
										$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
										$status_info['status'] = "Updated Values in DB";
									}
									$loan_id = $upd_prime_id;
								}else{
									$status_info['status'] = "Already Exist in DB";
								}
								$status_array[] = $status_info;
							}
						}
					}
					$table_info = $this->get_excel_import_ui($status_array);
					echo json_encode(array('success'=>true,'message'=>"Successfully datas are imported",'table_info'=>$table_info));
				}
			}
		}
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
			$excel_format_qry 	= 'select view_name,input_view_type,duplicate_data,picklist_data,field_type,pick_table,pick_list_type,pick_list,mandatory_field,field_isdefault,excel_line_column_name,excel_line_value from cw_util_excel_format_line inner join cw_form_setting on label_name = excel_line_column_name where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id = "'.$excel_format.'" and cw_form_setting.prime_module_id = "'.$module_id.'" and cw_util_excel_format_line.trans_status = 1';
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
					$total_rows = $excel_row_end;
				}else{
					$total_rows = $sheet->getHighestRow();
				}
				$highest_column  = $sheet->getHighestColumn();

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
							if($pick_list_type === 1){
								$pick_list_val   = explode(",",$pick_list);
								$pick_list_val_1 = $pick_list_val[0];
								$pick_list_val_2 = $pick_list_val[1];
								$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
								$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
								$pick_result = $pick_data->result();
								$pick_count  = $pick_data->num_rows();
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
										$pick_query = 'select * from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
										$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
										$pick_result = $pick_data->result();
										$pick_count  = $pick_data->num_rows();
										$pick_data->next_result();
										if((int)$pick_count === 0){
											$err_column_array['error']["$excel_line_value$i"] = $view_name;
											$msg_line = "column invalid data is present please check it?";
											$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
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
								$emp_code_qry = 'select count(*) as rslt_count from cw_employees where trans_status =1 and employee_code = "'.$col_value.'"';
								$emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
								$emp_data_result = $emp_data->result();
								$emp_data->next_result();
								$rslt_count = $emp_data_result[0]->rslt_count;
								if((int)$rslt_count === 0){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "employee code is not exit in employee master please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}
						}
					}
					$i++;
				}
			}
			}elseif(($field_type === 2) || ($field_type === 3)){//decimal and integer validations for non-mandatory field
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
						
	//amendment and rowset all validations start
	//14 years difference and dob date and rowset import checking updates
	
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
			if((int)$str_training_date > (int)$str_today){
				$err_column_array['error']["$training_line_val$row"] = $training_view_name;
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
	}
				
	//amendment and rowset all validations end
	$err_column_count = count($err_column_array['error']);
	$err_column       = implode(",",(array_unique($err_column_array['error'])));
	if((int)$err_column_count > 0){
		$sts = true;
		$msg = "$err_column $msg_line";	
	}
	
	if($sts){
		$table_info = $this->get_excel_error_ui($err_column_tabview);
		echo json_encode(array('success'=>false,'message'=>$msg,'table_info'=>$table_info));
		exit();
	}else{
		$status_array	    = array();
		$formula_process    = array();
		for($row = $excel_row_start; $row <= $total_rows; $row++) {
			$prime_column_val = "";
			$prime_cell_val   = "";
			$cf_column_val    = "";
			$cf_cell_val      = "";
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
				$pick_list              = $excel_info->pick_list;
				$excel_line_column_name = $excel_info->excel_line_column_name;
				$excel_line_value       = $excel_info->excel_line_value;
				$input_view_type        = $excel_info->input_view_type;
				//$get_cell_value         = trim($sheet->getCell("$excel_line_value$row")->getValue());
				$get_cell_value         = trim($sheet->getCell("$excel_line_value$row")->getCalculatedValue());
				if(((int)$field_type === 4) || ((int)$field_type === 8)){
					$get_cell_value    = str_replace("'",'^', $get_cell_value);
				}
				// FOR DATE
				if($field_type === 4){
					$get_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getCalculatedValue())));
				}else
				// FOR PICKLIST CHECK
				if(($field_type === 5) || ($field_type === 7)){
					if($pick_list_type === 1){
						$pick_list_val   = explode(",",$pick_list);
						$pick_list_val_1 = $pick_list_val[0];
						$pick_list_val_2 = $pick_list_val[1];
						$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
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
								$ins_query  = 'insert into '.$pick_table.'('.$pick_list_val_2.') VALUES ('.$get_cell_value_val.')';
								$ins_info   = $this->db->query("CALL sp_a_run ('INSERT','$ins_query')");
								$ins_result = $ins_info->result();
								$ins_info->next_result();
								$get_cell_value     = $ins_result[0]->ins_id;
								$second_insert_id   = $ins_result[0]->ins_id;
								
								$cf_table = $pick_table."_cf";
								$prime_id = $pick_table."_id";
								$prime_id = str_replace("cw_","prime_",$prime_id);
								$second_column_val = "$prime_id,";
								$second_cell_val   = "$second_insert_id,";
								$second_column_val .= "trans_created_by,trans_created_date";
								$second_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								$second_column_val  = rtrim($second_column_val,",");
								$second_cell_val    = rtrim($second_cell_val,",");
								$second_query  = "insert into $cf_table ($second_column_val) VALUES ($second_cell_val)";
								$this->db->query("CALL sp_a_run ('RUN','$second_query')");
							}else
							if((int)$pick_count === 1){
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
					$get_cell_value    = str_replace("'",'^', $get_cell_value);
					$prime_column_val .= $excel_line_column_name . ",";
					$prime_cell_val   .= "\'" . $get_cell_value . "\',";
					if(empty($exist_column_name)) {
						if($mandatory_field === 1){
							$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
						}
					}else{
						if(in_array($excel_line_column_name,$exist_column_name)){
							$exist_val .= $this->prime_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
						}
						$update_column_val = $excel_line_column_name;
						$update_cell_val   = '"'.$get_cell_value.'",';
						$prime_upd_query   .= $update_column_val."=".$update_cell_val;
					}
					if($excel_line_column_name === "employee_code"){
						$user_name = $get_cell_value;
					}
					if($excel_line_column_name === "date_of_joining"){
						$password = md5($get_cell_value);
					}
				}else
				if($field_isdefault === 2){
					$get_cell_value    = str_replace("'",'^',$get_cell_value);
					$prime_column_val .= $excel_line_column_name . ",";
					$prime_cell_val   .= "\'" . $get_cell_value . "\',";
					if(empty($exist_column_name)) {
						if($mandatory_field === 1){
							$exist_val .= $this->cf_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
						}
					}else{
						if(in_array($excel_line_column_name,$exist_column_name)){
							$exist_val .= $this->cf_table .'.'.$excel_line_column_name.' = "'.$get_cell_value.'" and ';
						}
						$cf_update_column_val = $excel_line_column_name;
						$cf_update_cell_val   = '"'.$get_cell_value.'",';
						$cf_prime_upd_query   .= $cf_update_column_val."=".$cf_update_cell_val;
					}
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
						$exist_query = "select count(*) exist_count,$this->prime_table.trans_status,$this->prime_table.$prime_id from $this->prime_table inner join $this->cf_table on $this->cf_table.$this->prime_id = $this->prime_table.$this->prime_id where $exist_val";
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
								
								$cf_column_val .= "$prime_id,";
								$cf_cell_val   .= "$insert_id,";
								$cf_column_val .= "trans_created_by,trans_created_date";
								$cf_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								$cf_column_val  = rtrim($cf_column_val,",");
								$cf_cell_val    = rtrim($cf_cell_val,",");
								$cf_query  = "insert into $this->cf_table ($cf_column_val) VALUES ($cf_cell_val)";
								$this->db->query("CALL sp_a_run ('RUN','$cf_query')");
								$status_info['Status'] = "Successfully datas are imported";
							}else{
								$imp_sts = True;
							}
						}else{
							if((int)$exist_count === 1){
								$upd_prime_id = (int)$exist_result[0]->$prime_id;
								$upd_query = 'UPDATE '.$this->prime_table.' SET '.$prime_upd_query.' trans_updated_by = "'.$this->logged_id.'",trans_updated_date = "'.$created_on.'" WHERE '.$prime_id.' = "'.$upd_prime_id.'"';
								$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
								$status_info['status'] = "Successfully values are updated";
								$code_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT employee_code FROM `cw_employees` where prime_employees_id = ".$upd_prime_id." and trans_status = 1')");
								$code_result = $code_info->result();
								$code_info->next_result();
								$employee_code = $code_result[0]->employee_code;
								$formula_process[] = $employee_code;
							}else{
								$imp_sts = True;
							}
						}
					}
				}
				$status_array[] = $status_info;
			}
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
		$this->esi_statutory();
	}
}
}
}
echo json_encode(array('success'=>$status,'message'=>$msg,'table_info'=>$table_info));
}

//LOAN IMPORT DATA AND VALIDATION PARTS START
public function do_loan_excel_import($import_id){
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
			$exist_column_name = explode(",",$format_rslt[0]->exist_column_name);
			$excel_format_qry 	= 'select view_name,duplicate_data,picklist_data,field_type,pick_table,pick_list_type,pick_list,mandatory_field,field_isdefault,excel_line_column_name,excel_line_value from cw_util_excel_format_line inner join cw_form_setting on label_name = excel_line_column_name where excel_line_module_id = "'.$module_id.'" and prime_excel_format_id = "'.$excel_format.'" and cw_form_setting.prime_module_id = "'.$module_id.'" and cw_util_excel_format_line.trans_status = 1';		
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
				
				//LOAN DATA VALIDATION MANDATORY FILED AND 
				$err_column_array    = array();
				$err_column_tabview  = array();
				foreach($excel_format_result as $excel_info){
					$mandatory_field         = (int)$excel_info->mandatory_field;
					$field_type              = (int)$excel_info->field_type;
					$excel_line_column_name  = $excel_info->excel_line_column_name;
					$excel_line_value        = $excel_info->excel_line_value;
					$view_name               = $excel_info->view_name;
					$pick_table              = $excel_info->pick_table;
					$pick_list_type          = (int)$excel_info->pick_list_type;
					$pick_list               = $excel_info->pick_list;
					$picklist_data           = (int)$excel_info->picklist_data;
					$common_multi_cell_value = $sheet->rangeToArray("$excel_line_value$excel_row_start:$excel_line_value$total_rows", NULL, TRUE, TRUE, TRUE);
					$i = $excel_row_start;
					foreach($common_multi_cell_value as $common_value){
						foreach($common_value as $col_key =>$col_value){
							if(empty($col_value) && !is_numeric($col_value)){
								$err_column_array['error']["$excel_line_value$i"] = $view_name;
								$msg_line = "columns are empty and invalid data is present please check it?";
								$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
							}elseif($field_type === 5){
								if($pick_list_type === 1){
									$pick_list_val   = explode(",",$pick_list);
									$pick_list_val_1 = $pick_list_val[0];
									$pick_list_val_2 = $pick_list_val[1];
									$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$col_value.'"';
									$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
									$pick_result = $pick_data->result();
									$pick_count  = $pick_data->num_rows();
									$pick_data->next_result();
									if((int)$pick_count === 0){
										$err_column_array['error']["$excel_line_value$i"] = $view_name;
										$msg_line = "column invalid data is present please check it?";
										$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
									}
								}
							}elseif($field_type === 4){
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
							}elseif($excel_line_column_name === "employee_code"){
								$emp_code_qry = 'select count(*) as rslt_count from cw_employees where trans_status = 1 and employee_code = "'.$col_value.'"';
								$emp_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_code_qry')");
								$emp_data_result = $emp_data->result();
								$emp_data->next_result();
								$rslt_count = $emp_data_result[0]->rslt_count;
								if((int)$rslt_count === 0){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "employee code is not exit in employee master please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}elseif($excel_line_column_name ==="loan_amount"){
								if(!is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "column invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}elseif($excel_line_column_name ==="interest_rate"){
								if(!is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "column invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}elseif($excel_line_column_name ==="number_of_installment"){
								if(!is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "column invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}elseif($excel_line_column_name ==="per_month"){
								if(!is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "column invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}elseif($excel_line_column_name ==="total_amount"){
								if(!is_numeric($col_value)){
									$err_column_array['error']["$excel_line_value$i"] = $view_name;
									$msg_line = "column invalid data is present please check it?";
									$err_column_tabview['error']["$excel_line_value$i"]  = $view_name." ".$msg_line;
								}
							}
							$i++;
						}
					}
				}
				
				for($row = $excel_row_start; $row <= $total_rows; $row++) {
					foreach($excel_format_result as $excel_info){
						$field_type             = (int)$excel_info->field_type;
						$excel_line_column_name = $excel_info->excel_line_column_name;
						$excel_line_value       = $excel_info->excel_line_value;
						$view_name              = $excel_info->view_name;
						$get_cell_value         = trim($sheet->getCell("$excel_line_value$row")->getCalculatedValue());
						if($excel_line_column_name === "emp_code"){
							$emp_code   = $get_cell_value;
							$doj_date_qry = 'select date_of_joining from cw_employees where trans_status = 1 and employee_code = "'.$emp_code.'"';
							$doj_date_data  = $this->db->query("CALL sp_a_run ('SELECT','$doj_date_qry')");
							$doj_date_result = $doj_date_data->result();
							$doj_date_data->next_result();
							$doj_date = $doj_date_result[0]->date_of_joining;
							$doj_date = strtotime($doj_date);
							$emp_line_val   = $excel_info->excel_line_value;
							$emp_view_name  = $view_name;
						}
						if($excel_line_column_name === "loan_date"){
							$loan_date_val   = $get_cell_value;
							if(!empty($loan_date_val)){
								$loan_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
								$loan_date = strtotime($loan_cell_value);
								$loan_line_val   = $excel_info->excel_line_value;
								$loan_view_name  = $view_name;
								$loan_date_value = trim(date('m-Y',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
							}
						}
						if($excel_line_column_name === "apply_year"){
							$apply_date_val   = $get_cell_value;
							if(!empty($apply_date_val)){
								$apply_month_line_val   = $excel_info->excel_line_value;
								$apply_month_view_name  = $view_name;
								$apply_cell_value = trim(date('m-Y',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
							}
						}
						if($excel_line_column_name === "category"){
							$category   = $get_cell_value;
							$cat_id_qry = 'select prime_category_id from cw_category where trans_status = 1 and category_name = "'.$category.'"';
							$cat_id_data  = $this->db->query("CALL sp_a_run ('SELECT','$cat_id_qry')");
							$cat_id_result = $cat_id_data->result();
							$cat_id_data->next_result();
							$cat_id        = $cat_id_result[0]->prime_category_id;
						}
						
						if($excel_line_column_name === "loan_amount"){
							$loan_amount   = $get_cell_value;
							$loan_amt_line_val   = $excel_info->excel_line_value;
							$loan_amt_view_name  = $view_name;
						}
						
						if($excel_line_column_name === "number_of_installment"){
							$no_of_install   = $get_cell_value;
						}
						
						if($excel_line_column_name === "per_month"){
							$per_month   = $get_cell_value;
						}
						
					}
					
					if($loan_date_value){
						$loan_date_qry = 'select count(*) as rslt_count from cw_transactions where trans_status = 1 and transactions_month >= "'.$loan_date_value.'"';
						$loan_data  = $this->db->query("CALL sp_a_run ('SELECT','$loan_date_qry')");
						$loan_data_result = $loan_data->result();
						$loan_data->next_result();
						$loan_count = $loan_data_result[0]->rslt_count;
						if((int)$loan_count !== 0){
							$err_column_array['error']["$loan_line_val$row"] = $loan_view_name;
							$msg_line = "Invalid, please check it?";
							$err_column_tabview['error']["$loan_line_val$row"]  = $loan_view_name." ".$msg_line;
						}
					}
					
					if($apply_cell_value){
						$apply_date_qry = 'select count(*) as rslt_count from cw_transactions where trans_status = 1 and transactions_month >= "'.$apply_cell_value.'"';
						$apply_data  = $this->db->query("CALL sp_a_run ('SELECT','$apply_date_qry')");
						$apply_data_result = $apply_data->result();
						$apply_data->next_result();
						$apply_count = $apply_data_result[0]->rslt_count;
						if((int)$apply_count !== 0){
							$err_column_array['error']["$apply_month_line_val$row"] = $apply_month_view_name;
							$msg_line = "Invalid, please check it?";
							$err_column_tabview['error']["$apply_month_line_val$row"]  = $apply_month_view_name." ".$msg_line;
						}
					}
					
					
					if($loan_date){
						if($loan_date < $doj_date){
							$err_column_array['error']["$loan_line_val$row"] = $loan_view_name;
							$msg_line = "loan date not less than date of joining, please check it?";
							$err_column_tabview['error']["$loan_line_val$row"] = $msg_line;
						}
					}
					
					if($category && $emp_code){
						$emp_exit_qry = 'select count(*) as exit_count from cw_employees where trans_status = 1 and employee_code = "'.$emp_code.'" and role ="'.$cat_id.'"';
						$emp_exit_data  = $this->db->query("CALL sp_a_run ('SELECT','$emp_exit_qry')");
						$emp_exit_result = $emp_exit_data->result();
						$emp_exit_data->next_result();
						$emp_exit_count = $emp_exit_result[0]->exit_count;
						if((int)$emp_exit_count === 0){
							$err_column_array['error']["$emp_line_val$row"] = $emp_view_name;
							$msg_line = "Employee and category mismatched, please check it?";
							$err_column_tabview['error']["$emp_line_val$row"] = $msg_line;
						}
					}
					
					if($loan_amount && $no_of_install && $per_month){
						$tot_amount = 	$no_of_install * $per_month;
						if($tot_amount > $loan_amount){
							$err_column_array['error']["$loan_amt_line_val$row"] = $loan_amt_view_name;
							$msg_line = "Install amount is not greater than total amount, please check installment?";
							$err_column_tabview['error']["$loan_amt_line_val$row"] = $msg_line;
						}
					}
				}
				
				//LOAN DATA VALIDATION END
				$err_column_count = count($err_column_array['error']);
				$err_column       = implode(",",$err_column_array['error']);
				if((int)$err_column_count > 0) {
					$sts = true;
					$msg = "$err_column $msg_line";
				}
				if($sts){
					$table_info = $this->get_excel_error_ui($err_column_tabview);
					echo json_encode(array('success'=>false,'message'=>$msg,'table_info'=>$table_info));
					exit();
				}else{
					$status_array	    = array();
					for($row =$excel_row_start; $row <= $total_rows; $row++) {
						$prime_column_val = "";
						$prime_cell_val   = "";
						$cf_column_val    = "";
						$cf_cell_val      = "";
						$exist_val        = "";
						$status_info = array();
						$status_info["Excel Row"] = $row;
						foreach($excel_format_result as $excel_info){
							$field_isdefault        = (int)$excel_info->field_isdefault;
							$mandatory_field        = (int)$excel_info->mandatory_field;
							$field_type             = (int)$excel_info->field_type;
							$pick_table             = $excel_info->pick_table;
							$pick_list_type         = (int)$excel_info->pick_list_type;
							$pick_list              = $excel_info->pick_list;
							$excel_line_column_name = $excel_info->excel_line_column_name;
							$excel_line_value       = $excel_info->excel_line_value;
							$get_cell_value         = trim($sheet->getCell("$excel_line_value$row")->getCalculatedValue());
							if(((int)$field_type === 4) || ((int)$field_type === 8)){
								$get_cell_value    = str_replace("'",'^', $get_cell_value);
							}
							if($field_type === 4){
								$get_cell_value = trim(date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
							}elseif($field_type === 5){
								$pick_list_val   = explode(",",$pick_list);
								$pick_list_val_1 = $pick_list_val[0];
								$pick_list_val_2 = $pick_list_val[1];
								$pick_query = 'select '.$pick_list.' from '.$pick_table.' where '.$pick_list_val_2.' = "'.$get_cell_value.'"';
								$pick_data  = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
								$pick_result = $pick_data->result();
								$pick_count  = $pick_data->num_rows();
								$pick_data->next_result();
								$created_on  = date("Y-m-d H:i:s");
								if((int)$pick_count === 1){
									$pick_id     = (int)$pick_result[0]->$pick_list_val_1;
									$pick_status = (int)$pick_result[0]->trans_status;
									$get_cell_value = $pick_id;
								}
							}elseif($excel_line_column_name === "apply_year"){
								$get_cell_value = trim(date('m-Y',PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("$excel_line_value$row")->getValue())));
							}
							if($field_isdefault === 1){
								$prime_column_val .= $excel_line_column_name . ",";
								$prime_cell_val   .= "\'" . $get_cell_value . "\',";
								if(empty($exist_column_name)) {
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
						if($prime_column_val){
							$prime_id    = "prime_".$module_id."_id";
							$exist_val   = rtrim($exist_val," and ");
							$exist_query = "select count(*) exist_count,$this->prime_table.trans_status,$this->prime_table.$prime_id from $this->prime_table inner join $this->cf_table on $this->cf_table.$this->prime_id = $this->prime_table.$this->prime_id where $exist_val";
							$exist_info   = $this->db->query("CALL sp_a_run ('RUN','$exist_query')");
							$exist_result = $exist_info->result();
							$exist_info->next_result();
							$exist_count = $exist_result[0]->exist_count;
							$created_on  = date("Y-m-d h:i:s");
							if((int)$exist_count === 0){
								$prime_column_val .= "trans_created_by,trans_created_date";
								$prime_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								$prime_column_val  = rtrim($prime_column_val,",");
								$prime_cell_val    = rtrim($prime_cell_val,",");
								$prime_query       = "insert into $this->prime_table ($prime_column_val) VALUES ($prime_cell_val)";
								$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$prime_query')");
								$insert_result = $insert_info->result();
								$insert_info->next_result();
								$insert_id = $insert_result[0]->ins_id;
								$loan_id   = $insert_id;
								$cf_column_val .= "$prime_id,";
								$cf_cell_val   .= "$insert_id,";
								$cf_column_val .= "trans_created_by,trans_created_date";
								$cf_cell_val   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
								$cf_column_val  = rtrim($cf_column_val,",");
								$cf_cell_val    = rtrim($cf_cell_val,",");
								$cf_query  = "insert into $this->cf_table ($cf_column_val) VALUES ($cf_cell_val)";
								$this->db->query("CALL sp_a_run ('RUN','$cf_query')");
								$status_info['Status'] = "Successfully datas are stored!!!";
								$this->installment_save($loan_id);
								$status_array[] = $status_info;
							}else{
								$status_info['Status'] = "Already Loan is updated in this employees? please check the apply month!!!";
							}
						}
					}
				}
				$table_info = $this->get_excel_import_ui($status_array);
				echo json_encode(array('success'=>true,'message'=>"Successfully datas are imported",'table_info'=>$table_info));
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
		
/* ==============================================================*/
/* =================== IMPORT OPERATION - END ===================*/
/* ==============================================================*/

/* ==============================================================*/
/* ================== PRINT OPERATION - START ===================*/
/* ==============================================================*/

public function sent_print($print_doc_id,$view_id){	
	$data = $this->load_print_data($print_doc_id,$view_id);
	$path = $this->control_name ."/print"; 
	$data['control_name'] = $this->control_name;
	$this->load->view($path,$data);	
}

public function load_print_data($print_doc_id,$view_id){
	$data['print_sts'] = false;
	$design_qry    = 'select * from cw_print_design where print_design_for = "'.$print_doc_id.'" and trans_status = 1';
	$design_data   = $this->db->query("CALL sp_a_run ('SELECT','$design_qry')");
	$design_result = $design_data->result();
	$design_data->next_result();
	$print_design  = "<style>
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
	</style>".$design_result[0]->print_design;
	$print_design = str_replace('~','"',$print_design);
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
			$line_prime_table = $table->line_prime_table;
			$line_prime_col   = $table->line_prime_col;
			$line_join_type   = $table->line_join_type;
			$line_join_table  = $table->line_join_table;
			$line_join_col    = $table->line_join_col;
			$line_sort        = $table->line_sort;
			$module_name      = str_replace("cw_","",$line_prime_table);
			$prime_id         = "prime_".$module_name."_id";
			$cf_id            = "prime_".$module_name."_cf_id";
			$cf_table_name    = $this->db->dbprefix($module_name."_cf");
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
			$line_table_query = " $print_block_table inner join $cf_table_name on $print_block_table.$prime_id = $cf_table_name.$prime_id ";
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
		$final_qry = "select $select_query from $line_table_query $pick_query where $where_trans $where_condition  order by transactions_month DESC limit 1";
		$final_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		$final_result = $final_data->result();
		$final_data->next_result();
		$tr_line = "";
		$th_line = "";
		$count = 0;
		$assign_date_formate_list  = array("DMY"=>"d-m-Y","YMD"=>"Y-m-d","MY"=>"F-Y","YM"=>"Y-F","D"=>"d","M"=>"M","Y"=>"Y");
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
		
public function get_loan_value($process_month,$employee_code){
	$process_month = explode("-",$process_month);
	$loan_month    = $process_month[0];
	$loan_year     = $process_month[1];
	$loan_qry = 'select emp_code,install_amount,cw_loan_type.loan_type from cw_loan_installment inner join cw_loan_type on  cw_loan_type.prime_loan_type_id = cw_loan_installment.loan_type where cw_loan_installment.trans_status = 1 and cw_loan_installment.emp_code ="'.$employee_code.'" and cw_loan_installment.install_year ="'.$process_month.'"';
	//and cw_loan_installment.install_month ='.$loan_month
	$loan_data   = $this->db->query("CALL sp_a_run ('SELECT','$loan_qry')");
	$loan_result = $loan_data->result();
	$loan_data->next_result();
	$loan_tr = "";
	foreach($loan_result as $loan){
		$loan_type      = $loan->loan_type;
		$install_amount = $loan->install_amount;
		$loan_tr .= "<tr>
		<td style='width:77%;'>$loan_type</td>
		<td>$install_amount</td>
		</tr>";
	}
	if($loan_tr !== ""){
		$loan_tr = "<table style='width:100%'>
		$loan_tr
		</table>";
	}
	return $loan_tr;
}
/* ==============================================================*/
/* =================== PRINT OPERATION - END ===================*/
/* ==============================================================*/

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
	$points = ($point) ?
	"." . $words[$point / 10] . " " . 
	$words[$point = $point % 10] : '';
	return $result;
}

//notification list for all date fields not mentions any roles restrications
public function notify(){
	$column_name       = $this->input->post('column_name');
	$days_before       = $this->input->post('days_before');
	$remainder_head    = $this->input->post('remainder_head');
	$emp_role          = $this->logged_role;
	$start_date        = date('m-d');
	$end_date          = date("m-d", strtotime("+$days_before day"));		
	$emp_info_qry = 'select '.$column_name.',employee_code,emp_name from cw_employees where trans_status = 1 and role !=1 and DATE_FORMAT('.$column_name.', "%m-%d") BETWEEN "'.$start_date.'" and "'.$end_date.'"';
	$emp_data_info   = $this->db->query("CALL sp_a_run ('SELECT','$emp_info_qry')");
	$emp_result = $emp_data_info->result();
	$emp_data_info->next_result();
	$emp_rslt_count = $emp_data_info->num_rows();
	if($emp_rslt_count){
		$tr_line = "";
	}else{
		$tr_line = "<tr><td colspan='4' style='text-align:center;font-weight:bold;'>No Data Found!</td></tr>";
	}
	$i = 1;
	foreach($emp_result as $emp_rslt){
		$employee_code  = $emp_rslt->employee_code;
		$emp_name       = $emp_rslt->emp_name;
		$column_val     = date("d-m-Y", strtotime($emp_rslt->$column_name));
		$column_match   = date("m-d", strtotime($emp_rslt->$column_name));
		if($column_match == $start_date){
			$font_color = "font-weight:bold;color:green";
		}else{
			$font_color ='';
		}
		$tr_line .= "<tr style='$font_color'><td>$i</td><td>$employee_code</td><td>$emp_name</td><td>$column_val</td></tr>";
		$i++;
	}
	
	echo "<div class='block_content pd8'>
	<h3>$remainder_head</h3>
	<table id='detail_list' class='table table-bordered col-style'>
	<thead>
	<tr>
	<th>Sl.No</th>
	<th>Employee Code</th>
	<th>Employee Name</th>
	<th>Date</th>
	</tr>
	</thead>
	<tbody>$tr_line</tbody>
	</table>
	</div>";
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
			$get_esi_stat_qry      = 'select esi_limit,esi_eligibilit_formula from cw_statutory where trans_status = 1 and category='.$role;
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
}
?>