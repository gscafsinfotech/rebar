<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Employees  extends Action_controller{	
	public function __construct(){
		parent::__construct('employees');
		$this->collect_base_info();
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;
		$this->load->view("$this->control_name/manage",$data);
	}
	
	//LOAD TABEL WITH FILTERS
	public function search(){
		$draw         = $this->input->post('draw');
		$start        = $this->input->post('start');
		$per_page     = $this->input->post('length');
		$order        = $this->input->post('order');
		$order_col    = $this->input->post('columns');
		$search       = $this->input->post('search');
		$column       = $order[0]['column'];
		$order_sor    = $order[0]['dir'];
		$order_col    = $order_col[$column]['data'];
		$search       = trim($search['value']);
		$search_query = str_replace("@SELECT@",$this->select_query,$this->base_query);
		
		//ADDED BASIC,FILTER,COMMON QUERY HERE 
		$role_condition   = "";
		if($this->role_condition){
			$role_condition = $this->role_condition;
		}
		
		$fliter_query = "";
		foreach($this->fliter_list as $fliter){
			$label_id         = $fliter['label_id'];
			$label_name       = $fliter['label_name'];
			$field_isdefault  = (int)$fliter['field_isdefault'];
			$array_list       = $fliter['array_list'];
			$field_type       = (int)$fliter['field_type'];			
			if($field_isdefault === 1){
				$column_name = $this->prime_table .".$label_id";				
				$search_val  = $this->input->post("$label_id");
				if($search_val){
					if($field_type === 4){
						$search_val = date('Y-m-d',strtotime($search_val));
						$fliter_query .= " and $column_name = '$search_val'";
					}else
					if(($field_type === 5) || ($field_type === 7)){
						$search_val    = trim(implode(",",$search_val));
						$fliter_query .= " and $column_name in ($search_val)";  
					}else
					if($field_type === 13){
						$search_val = date('Y-m-d H:i:s',strtotime($search_val));
						$fliter_query .= " and $column_name = '$search_val'";
					}else{
						$fliter_query .= " and $column_name LIKE '$search_val%'";
					}
				}
			}
		}
		$common_search = "";
		if($search){
			foreach($this->form_info as $setting){
				$prime_form_id   = $setting->prime_form_id;
				$field_type      = (int)$setting->field_type;
				$pick_list       = $setting->pick_list;
				$pick_table      = $setting->pick_table;
				$pick_list_type  = $setting->pick_list_type;
				$input_view_type = (int)$setting->input_view_type;
				$auto_prime_id      = $setting->auto_prime_id;
				$auto_dispaly_value = $setting->auto_dispaly_value;
				$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
				$field_isdefault    = (int)$setting->field_isdefault;
				if($field_isdefault === 1){
					if(($input_view_type === 1) || ($input_view_type === 2)){
						$search_label = "$this->prime_table.$label_id";
						$search_val   = "";
						if($field_type === 4){ // having issues in date search
							if(strtotime($search)){
								$search_val = date('Y-m-d',strtotime($search));
								$common_search .= ' or '. $search_label .' like "'.$search_val.'%"';

							}
						}else
						if(($field_type === 5) || ($field_type === 7) || ($field_type === 9)){
							$result = array_filter($this->master_pick[$label_id], function ($item) use ($search) {
								if (stripos($item, $search) !== false) {
									return true;
								}
								return false;
							});
							if($result){
								$pick_key   = implode(",",array_keys($result));
								$common_search .= ' or '. $search_label .' in("'.$pick_key.'")';
							}
						}else{
							$common_search .= ' or '. $search_label .' like "%'.$search.'%"';
						}
					}
				}
			}

			if($common_search){
				$common_search = ltrim($common_search,' or ');
				$common_search = " and ($common_search)";
				$common_search = str_replace("(,","(",$common_search);
				$common_search = str_replace("()","(0)",$common_search);
			}
		}
	
		$count_all_query    = str_replace("@SELECT@","count(*) as allcount",$this->base_query);		
		$search_total       = $this->db->query($count_all_query);
		$search_total_info  = $search_total->result();
		$total_count        = $search_total_info[0]->allcount;
		
		$count_query        = str_replace("@SELECT@","count(*) as allcount",$this->base_query);
		$count_query       .= " where $this->prime_table.trans_status = 1 $role_condition $fliter_query $common_search";
		$search_count       = $this->db->query($count_query);
		$search_info        = $search_count->result();
		$filtered_count     = $search_info[0]->allcount;
		
		$search_query      .= " where $this->prime_table.trans_status = 1 and $this->prime_table.prime_employees_id != 1 $role_condition $fliter_query $common_search";
		$search_query      .= " ORDER BY  $order_col $order_sor";
		if((int)$per_page !== -1){
			$search_query  .= " LIMIT  $start,$per_page";
		}
		$search_data        = $this->db->query($search_query);
		$search_result      = $search_data->result();
		echo json_encode(array("draw" => intval($draw),"recordsTotal" => $total_count,"recordsFiltered" => $filtered_count,"data" => $search_result));		
	}
	
	//LOAD MODEL PAGE VIEW WITH DATA
	public function view($form_view_id=-1){
		//VIEW, FORM INPUT
		$data['view_info']      = $this->view_info;
		$data['form_info']      = $this->form_info;	
		
		//VIEW DATA
		$base_query  = str_replace("@SELECT@",$this->view_select,$this->base_query);
		$view_query  = $base_query ." where $this->prime_table.$this->prime_id = $form_view_id and $this->prime_table.trans_status = 1";
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$data['form_view']   = $view_result[0];
		//AUTO COMPLTE,PICK LIST AND CONDITION
		foreach($this->form_info as $from){
			$prime_form_id      = (int)$from->prime_form_id;
			$field_type         = (int)$from->field_type;
			$pick_table         = $from->pick_table;
			$auto_prime_id      = $from->auto_prime_id;
			$auto_dispaly_value = $from->auto_dispaly_value;
			$label_id           = $from->label_name;
			if($field_type === 9){
				if($view_result[0]){
					$get_value = $view_result[0]->$label_id;
					if($get_value){
						$pick_query = 'select '.$auto_dispaly_value.' from '.$pick_table.' where '.$auto_prime_id.' = "'.$get_value.'" and trans_status = 1';
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						$this->all_pick[$prime_form_id] = $pick_result[0]->$auto_dispaly_value;
					}					
				}
			}
		}
		$data['all_pick']       = $this->all_pick;
		$data['condition_list'] = $this->condition_list;
		
		$view_qry    = 'select * from cw_form_view_setting where  prime_view_module_id = "'.$this->control_name.'" and  form_view_type = "3" and trans_status = 1';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_qry')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$row_view_list = array();
		foreach($view_result as $view){
			$prime_form_view_id   = $view->prime_form_view_id;
			$row_set_data = $this->get_row_set_data($prime_form_view_id,$form_view_id);
			$row_view_list[$prime_form_view_id] = $row_set_data;
		}
		$data['row_view_list']   = $row_view_list;
		
		/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
		$arr = array();
		foreach($this->Module->get_all_modules($this->control_name) as $module){
			$module->module_id = $this->xss_clean($module->module_id);
			$module->grant     = $this->xss_clean($this->Module->has_grant($this->control_name,$module->module_id, $form_view_id));
			$module->access    = $this->xss_clean($this->Module->has_access($this->control_name,$module->module_id, $form_view_id));
			//$modules[] = $module;
			$menu = str_replace(" ","_",strtolower($module->menu_name)); //."_".$module->menu_id
			$submenu = str_replace(" ","_",strtolower($module->sub_menu_name));		
			if(!$submenu){
				$submenu = "sub_".$menu;
			}
			$arr[$menu][$submenu][] = $module;	
		}
		$data['all_modules'] = $arr;
		/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
		$data['edit_id']       = $form_view_id;
				
		/*formula label name */
		$formula_qry     = 'select * from cw_form_bind_input where input_cond_module_id = "'.$this->control_name.'" and trans_status = 1';
		$formula_data    = $this->db->query("CALL sp_a_run ('SELECT','$formula_qry')");
		$formula_result  = $formula_data->result();
		$formula_data->next_result();
		$data['formula_result']   = $formula_result;
		
		$emp_details_columns = 'select employee_code,emp_name from `cw_employees` where trans_status =1';
		$emp_details_info   = $this->db->query("CALL sp_a_run ('SELECT','$emp_details_columns')");
		$emp_details_result = $emp_details_info->result();
		$emp_details_info->next_result();
		$emp_details[""] = "---- Select Column ----";
		foreach($emp_details_result as $emp_column){
			$employee_code  = $emp_column->employee_code;
			$emp_name       = $emp_column->emp_name;
			$emp_details[$this->xss_clean($employee_code)] = $this->xss_clean($emp_name);
		}		
		$data['emp_details'] = $emp_details;

		$role_based_query  = 'SELECT * FROM cw_role_base_condition WHERE  role_module_id = "'.$this->control_name.'" and FIND_IN_SET("'.$this->logged_role.'",role_condition_for) and trans_status = 1';
		$role_based_info   = $this->db->query("CALL sp_a_run ('SELECT','$role_based_query')");
		$role_based_result = $role_based_info->result();
		$role_based_info->next_result();

		$role_based_condition = array();
		foreach ($role_based_result as $key => $condition) {
			$role_based_condition[$condition->user_condition_type] = $condition->input_columns;
		}
		$data['role_based_condition'] = $role_based_condition;
		$this->load->view("$this->control_name/form",$data);
	}
	
	//SAVE MODEL DATA TO DATA BASE
	public function save(){
		$unq_chk         = array();
		$prime_qry_key   = "";
		$prime_qry_value = "";
		$prime_upd_query = "";
		$cf_has          = false;
		$form_id         = (int)$this->input->post($this->prime_id);
		$form_post_data  = array();	
		$emp_log         = array();
		$emp_log['prime_employees_id'] = $form_id;
		foreach($this->form_info as $setting){
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
				if($value){
					$value = date('Y-m-d',strtotime($value));
				}
			}
			if(($input_view_type === 1) || ($input_view_type === 2)){
				if((int)$field_isdefault === 1){
					if($label_id !== "employee_code"){
						$prime_qry_key     .= $label_id.",";
						$prime_qry_value   .= '"'.$value.'",';
						$prime_upd_query   .= $label_id.' = "'.$value.'",';
					}					
				}
				$emp_log[$label_id] = $value;
			}
		}
		
		$termination_status = (int)$this->input->post('termination_status');
		if($termination_status === 1){
			$resignation_date = $this->input->post('resignation_date');
			$separation_type  = $this->input->post('separation_type');
		}
		//old application insert query building details start --18JAN2020
		/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
		$user_name = $this->input->post('user_name');
		$password  = $this->input->post('password');
		/*============ BSK EMPLOYEE CODE EXIST CHECK CUSTOME BLOCK START============*/
		$exist     = $this->is_exist($this->input->post('employee_code'));
		$exist     = explode("/",$exist);
		$id        = $exist[0];
		$num_rows  = $exist[1];
		if(((int)$id !== (int)$form_id) && ((int)$num_rows >= 1)){
			$emp_code   = $this->get_digits($this->input->post('role'));
			$user_name  = $emp_code;
			$code_exist = 1;
		}else{			
			$emp_code = $this->input->post('employee_code');
			$code_exist = 2;
		}
		/*============ BSK EMPLOYEE CODE EXIST CHECK CUSTOME BLOCK END============*/
		$prime_qry_key     .="user_name,";
		$prime_qry_value   .= '"'.$user_name.'",';
		$prime_upd_query   .= 'user_name = "'.$user_name.'",';
		if($password !== ''){
			$prime_qry_key     .="password,";
			$prime_qry_value   .= '"'.md5($password).'",';
			$prime_upd_query   .= 'password = "'.md5($password).'",';
		}
		
		$access_data = $this->input->post('access') != NULL ? $this->input->post('access') : array();
		$grants_data = $this->input->post('grants') != NULL ? $this->input->post('grants') : array();		
		/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/		
		$created_on = date("Y-m-d h:i:s");
		$emp_data = array();
		if($this->check_emp_code($code,$form_id)){
			if((int)$form_id === 0){
				$prime_qry_key     .= "employee_code,trans_created_by,trans_created_date";
				$prime_qry_value   .= '"'.$emp_code.'","'.$this->logged_id.'",'.'"'.$created_on.'"';
				$prime_insert_query = "insert into $this->prime_table ($prime_qry_key) values ($prime_qry_value)";
				$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
				$insert_result      = $insert_info->result();
				$insert_info->next_result();
				$insert_id = $insert_result[0]->ins_id;
				/*== UDY CUSTOME BLOCK ==*/
				$this->Module->update_grants($this->control_name,$insert_id,$grants_data,$access_data);

				$emp_data = array("Compcode"=>"C0001","CODE"=>$code,"EMPNAME"=>$empname,"DEPT"=>$department,"DESIG"=>$designation,"DOJ"=>$doj,"DOB"=>$dob,"MARTIAL"=>$marital_status,"SEX"=>$gender,"cCode"=>$oldcategory);
				$this->curl($emp_data);
				if($termination_status === 1){
					$email_status = $this->update_termination_info($resignation_date,$separation_type,$emp_log);
				}
				if($email_status){
					echo json_encode(array('success' => TRUE, 'message' => "Successfully added.. Your Employee Code is $emp_code", 'insert_id' => $insert_id,'code_exist'=>$code_exist,'emp_code'=>$emp_code));
				}else{
					echo json_encode(array('success' => TRUE, 'message' => "Successfully updated But mail not send",'insert_id' => $form_id,'code_exist'=>$code_exist,'emp_code'=>$emp_code));
				}
			}else{
				//log inserted for employee table
				$this->employee_log($form_id,$emp_log);
				$prime_upd_query    .= 'employee_code = "'.$emp_code.'",trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
				$prime_update_query  = 'UPDATE '. $this->prime_table .' SET '. $prime_upd_query .' WHERE '. $this->prime_id .' = "'. $form_id .'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
				/*== UDY CUSTOME BLOCK ==*/ 
				$this->Module->update_grants($this->control_name,$form_id,$grants_data,$access_data);
				/*== UDY CUSTOME BLOCK ==*/
				$emp_data = array("Compcode"=>"C0001","CODE"=>$code,"EMPNAME"=>$empname,"DEPT"=>$department,"DESIG"=>$designation,"DOJ"=>$doj,"DOB"=>$dob,"MARTIAL"=>$marital_status,"SEX"=>$gender,"cCode"=>$oldcategory);
				$this->curl($emp_data);
				$email_status = true;
				if($termination_status === 1){
					$email_status = $this->update_termination_info($resignation_date,$separation_type,$emp_log);
				}
				if($email_status){
					echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $form_id,'code_exist'=>$code_exist,'emp_code'=>$emp_code));
				}else{
					echo json_encode(array('success' => TRUE, 'message' => "Successfully updated But mail not send",'insert_id' => $form_id,'code_exist'=>$code_exist,'emp_code'=>$emp_code));
				}
			}
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Employee Code Already Exists",'category_status'=>TRUE));
		}		
	}
	
	//UPDATE TERMINATION INFORMATION SVK EDIT 01/06/2019
	public function update_termination_info($resignation_date,$separation_type,$emp_array){
		if($this->check_print_design_exists()){
			if((int)$separation_type === 5){
				$status = 'R';
			}else
			if((int)$separation_type === 6){
				$status = 'A';
			}else
			if((int)$separation_type === 7 || (int)$separation_type === 8){
				$status = 'TS';
			}
			return $this->sent_emp_mail($emp_array['emp_name'],$emp_array['employee_code'],$personal_email_id,$status,$status,$emp_array,$resignation_date);
		}else{
			return false;
		}
	}
	public function check_print_design_exists(){
		$design_query  = 'SELECT count(*) rlst_count FROM cw_print_design inner join cw_print_info on cw_print_info.prime_print_info_id = cw_print_design.print_design_for WHERE print_info_module_id = "employees" and cw_print_design.trans_status = 1 and cw_print_info.print_type IN ("4","5","6","7")';
		$design_query   = $this->db->query("CALL sp_a_run ('SELECT','$design_query')");
		$design_result  = $design_query->result();
		$design_query->next_result();
		$rlst_count  = $design_result[0]->rlst_count;
		if((int)$rlst_count === 4){
			return true;
		}else{
			return false;
		}
	}
	public function sent_emp_mail($emp_name,$empcode,$personal_email_id,$status,$leave_status,$emp_array,$unpunched_date){
		$get_email_qry    = 'SELECT * FROM `cw_leave_mail` where FIND_IN_SET("'.$status.'",leave_type) and trans_status = 1';
		$email_info    = $this->db->query("CALL sp_a_run ('SELECT','$get_email_qry')");
		$email_result  = $email_info->result();
		$email_info->next_result();	
		if($email_result){
			$manager_qry      = "SELECT company_email_id FROM `cw_employees` where prime_employees_id = $this->logged_id and trans_status = 1";
			$manager_info     = $this->db->query("CALL sp_a_run ('SELECT','$manager_qry')");
			$manager_result   = $manager_info->row();
			$manager_info->next_result();
			$manger_mail_id   = $manager_result->company_email_id;
			$style            = "green";
			$message          = "Mail Send Successfully";
			$mail_to          = "s.vasanth300@gmail.com";//$email_result[0]->mail_to;
			$sender_mail      = $mail_to;
			$mail_cc          = explode(",",$email_result[0]->mail_cc);
			$mail_subject     = $email_result[0]->mail_subject;
			$mail_content     = $email_result[0]->mail_content;
			$preg_match       = preg_match_all('#\@(.*?)\@#', $mail_content, $match);		
			foreach($match[1] as $for_rslt){
				$find_value      = "@$for_rslt@";
				$for_value       = $emp_array[$for_rslt];						
				$mail_content = str_replace($find_value,$for_value,$mail_content);
			}
			$get_email_qry      = 'select * from cw_mail_configurations where trans_status = 1 and mail_status = 1';
			$email_info         = $this->db->query("CALL sp_a_run ('SELECT','$get_email_qry')");
			$email_result       = $email_info->result();
			$email_info->next_result();
			$smtp_server   = $email_result[0]->smtp_server;
			$port_no       = $email_result[0]->port_no;
			$user_name     = $email_result[0]->mail_username;
			$user_password = $email_result[0]->mail_password;
			require('./phpmailer/class.phpmailer.php');
			$mail = new PHPMailer(); 
			$mail->SMTPDebug = 3;
			$mail->IsSMTP();
			$mail->Host       = $smtp_server; // Your SMTP PArameter
			$mail->Port       = $port_no; // Your Outgoing Port
			$mail->SMTPAuth   = true; // This Must Be True
			$mail->Username   = $user_name; // Your Email Address
			$mail->Password   = $user_password; // Your Password
			$mail->SMTPSecure = 'tls'; // Check Your Server's Connections for TLS or SSL
			$mail->From       = $user_name;//'s.vasanth300@gmail.com';//$manger_mail_id;
			$mail->FromName   = 'Test Mail From Hrms';
			$mail->AddAddress($mail_to);
			$add_content = "";
			if($status === 'R' || $status === 'A' || $status === 'TS' || $status === 'TWS'){
				$add_content = "$empcode -";
				if($personal_email_id){
					//$mail->AddAddress($personal_email_id);
					$mail->AddAddress("sathish@cafsinfotech.in");
					$sender_mail .= ",".$personal_email_id;
				}else{
					$message = "Mail Send only to hr";
					$style   = "red";
				}
				if($status === 'TS'){
					$print_type = 4;
				}else
				if($status === 'TWS'){
					$print_type = 5;
				}else
				if($status === 'A'){
					$print_type = 6;
				}else
				if($status === 'R'){
					$print_type = 7;
				}
				$path_name = $this->load_print_info($emp_array['role'],$empcode,$print_type,$empcode,$unpunched_date);
				if(file_exists($path_name)){
					$mail->addAttachment($path_name);	
				}
			}
			$mail->IsHTML(true);
			$mail->Subject = $add_content.$mail_subject;
			$mail->Body    = $mail_content;
			if((int)count($mail_cc) > 0){
				foreach ($mail_cc as $bcc_key => $bcc_mails){
					$mail->AddCC($bcc_mails);
				}
			}
			if(!$mail = $mail->Send()){
				$message = "Mail Not Send";
				$style   = "red";
			} 
			$insert_values = "(\"$empcode\",\"$sender_mail\",\"$leave_status\",\"$message\")";
			$email_log_query    = 'INSERT into cw_time_office_mail_log (employee_code,mail_id,status,message) values '.$insert_values.'';
			$insert_info    = $this->db->query("CALL sp_a_run ('INSERT','$email_log_query')");
			$insert_result  = $insert_info->result();
			$insert_info->next_result();
			return true;
		}else{
			return false;
		}
	}

	public function load_print_info($category,$emp_code,$print_type,$view_id,$unpunched_date){
		$design_query  = 'SELECT prime_print_info_id,print_design,print_info_for,print_info_module_id,print_type,print_info_name FROM cw_print_design inner join cw_print_info on cw_print_info.prime_print_info_id = cw_print_design.print_design_for WHERE FIND_IN_SET("'.$category.'",print_info_for) and print_info_module_id = "employees" and cw_print_design.trans_status = 1 and cw_print_info.print_type = "'.$print_type.'"';
		$design_query   = $this->db->query("CALL sp_a_run ('SELECT','$design_query')");
		$design_result  = $design_query->result();
		$design_query->next_result();
		$print_doc_id  = $design_result[0]->prime_print_info_id;
		$print_design  = $design_result[0]->print_design;
		$print_type    = $design_result[0]->print_type;
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
				$cf_table_name    = "cw_".$module_name."_cf";
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
			}else{
				$where_condition  = " and cw_employees.employee_code = $emp_code";
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
						$print_design     = str_replace($replace_ytd_val,$ytd_value,$print_design);
					}
				}
			}
			$final_qry = "select $select_query from ".$line_table_query." $pick_query where $where_trans $where_condition";
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
						
						if(isset($split_array[$replace_val])){
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
		}
		$print_design = str_replace("<br>","",$print_design);
		$print_design = str_replace("@today_date@",$unpunched_date,$print_design);
		$table_data = "<!DOCTYPE html><html> <body>".$print_design."</body></html>";
		
		// Load pdf library
        $this->load->library('pdf');
			// Load HTML content 
		$this->dompdf->loadHtml($table_data);
			// Render the HTML as PDF
		$this->dompdf->render();
			// Output the generated PDF (1 = download and 0 = preview)
		$output = $this->dompdf->output();
		
		$design_name = strtolower(str_replace(" ","_",$design_result[0]->print_info_name));
		$folder      = "./time_office_mail/".$design_name;
		//Check Folder Exist
		if (!file_exists($folder)){
			mkdir($folder, 0777, true);
		}
		//Check File Exist
		if(file_exists($folder."/".$emp_code.".pdf")){
			unlink($folder."/".$emp_code.".pdf");
		}
		file_put_contents($folder."/".$emp_code.".pdf" , $output);
		chmod($folder."/".$emp_code.".pdf", 0777);
		return $folder."/".$emp_code.".pdf";
	}
	
	/*============ BSK EMPLOYEE CODE GENERATION CUSTOME BLOCK START============*/
	public function is_exist($employee_code){
		$search_query   = 'select * from cw_employees where employee_code = "'.$employee_code.'"';
		$search_info    = $this->db->query("CALL sp_a_run ('RUN','$search_query')");
		$result  = $search_info->result();		
		$search_info->next_result();
		$num_rows      = $search_info->num_rows();
		$id            = $result[0]->prime_employees_id;
		$data          = "$id/$num_rows";
		return $data;
	}
	
	// public function get_employee_code(){
	// 	$role                  = $this->input->post('role');
	// 	$check_code_gen_qry    = 'select code_type from cw_employee_code_auto where trans_status = 1';
	// 	$check_code_gen_data   = $this->db->query("CALL sp_a_run ('SELECT','$check_code_gen_qry')");
	// 	$check_code_gen_result = $check_code_gen_data->result();
	// 	$check_code_gen_data->next_result();
	// 	$code_gen_mode   = $check_code_gen_result[0]->code_type;
	// 	$sts = 0;
	// 	if((int)$code_gen_mode === 1){
	// 		$sts = 1;
	// 		$result = $this->get_digits($role);
	// 		if($result){
	// 			echo json_encode(array('success' => TRUE, 'sts' => $sts, 'digits' => $result));
	// 		}else{
	// 			echo json_encode(array('success' => FALSE, 'sts' => $sts, 'message' => "Employee Code Auto Not Updated for this role"));
	// 		}
	// 	}else{
	// 		echo json_encode(array('sts' => $sts,'message' => "Manually enter the  Employee code"));
	// 	}
	// }
	
	public function get_digits($role){
		$select_qry    = 'select * from cw_employee_code_auto where (category = "'.$role.'" or category = "All") and trans_status = 1';
		$select_data   = $this->db->query("CALL sp_a_run ('SELECT','$select_qry')");
		$select_result = $select_data->result();
		$select_data->next_result();
		$num_rows      = $select_data->num_rows();
		$prefix        = $select_result[0]->prefix;
		$start_value   = $select_result[0]->start_value;
		$category      = $select_result[0]->category;
		$prefix_count  = 0;
		$prefix_qry = "";
		if($prefix){
			$prefix = strtoupper($prefix);
			$prefix_count = strlen($prefix);
			$prefix_qry = ' and employee_code like "%'.$prefix.'%"';
		}
		if($category === "All"){
			$emp_count_qry = 'select MAX(employee_code) as employee_code from cw_employees where prime_employees_id != 1 '.$prefix_qry;
		}else{
			$emp_count_qry = 'select MAX(employee_code) as employee_code from cw_employees where role = "'.$role.'" and prime_employees_id != 1 and employee_code != ""'.$prefix_qry;
		}		
		$emp_count_data    = $this->db->query("CALL sp_a_run ('SELECT','$emp_count_qry')");
		$emp_count_result  = $emp_count_data->result();
		$emp_count_data->next_result();
		//$emp_count       = $emp_count_data->num_rows();
		$max_count         = $emp_count_result[0]->employee_code;	
		if($max_count){
			if((int)$num_rows > 0){
				/*$digits        = $this->digit_check($prefix,$start_value,$max_count);*/
				$max_count = substr($max_count,$prefix_count);
				$digits    = $max_count +1;
				return $prefix."".$digits;
			}else{
				return false;
			}
		}else{
			return $prefix."".$start_value;
		}
	}
	
  /*public function digit_check($prefix,$start_value,$max_count){
		$number_of_digits = strlen((string)$max_count);
		$new_count        = $max_count +1;		
		$number_of_digits = "-".$number_of_digits;
		$num              =  substr($start_value, 0, $number_of_digits).$new_count;
		if($prefix){
			return $prefix."".$num;
		}else{
			return $num;
		}		
	}*/
	
	/*============ BSK EMPLOYEE CODE GENERATION CUSTOME BLOCK END============*/

	//UPDATE STATUS TO DELETE IN MODULE PRIMARY TABLE
	public function delete(){
		$delete_ids    = implode(",",$this->input->post('delete_ids'));
		$can_process   = TRUE;
		$delete_status = FALSE;
		if($this->check_delete_status()){
			$delete_status = TRUE;
			$check_table_query  = 'SELECT GROUP_CONCAT(prime_module_id) as prime_module_id,GROUP_CONCAT(label_name) as label_name from cw_form_setting WHERE pick_table = "'. $this->prime_table .'" and  trans_status = 1 ';
			$check_table_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_table_query')");
			$check_table_rlst   = $check_table_info->row();
			$check_table_info->next_result();
			if($check_table_rlst->prime_module_id){
				$prime_module_id         = explode(",",$check_table_rlst->prime_module_id);
				$label_name              = explode(",",$check_table_rlst->label_name);
				$i                       = 0;
				$select_table            = '';
				$select_label            = '';
				$select_trans_status     = '';
				$select_where            = '';
				foreach($prime_module_id as $check_modules){
					$table_name            = "cw_".$check_modules;
					$table_rename          = $table_name."_$i";
					$select_table         .= "$table_rename.$label_name[$i],";
					$select_label         .= " $table_name $table_rename,";
					if((int)$i === 0){
						$select_trans_status  .= "( $table_rename.trans_status = 1";
						$select_where         .= " and ($table_rename.$label_name[$i] in ($delete_ids)";
					}else{
						$select_trans_status  .= " and $table_rename.trans_status = 1";
						$select_where         .= " or $table_rename.$label_name[$i] in ($delete_ids)";
					}
					$i++;
				}
				$select_trans_status .= ")";
				$select_where        .= ")";
				$select_table         = rtrim($select_table,',');
				$select_label         = rtrim($select_label,',');
				$check_module_query  .= 'SELECT '.$select_table.' from '.$select_label.' WHERE '.$select_trans_status.' '.$select_where.' LIMIT 0,1'; 
				$check_module_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_module_query')");
				$values_count        = $check_module_info->num_rows();
				$check_module_info->next_result();
				if((int)$values_count > 0){
					$can_process   = False;
					$delete_status = False;
				}
			}
			if($delete_status){
				$delete_query  = 'DELETE FROM '. $this->prime_table .'  WHERE '. $this->prime_id .' in ('. $delete_ids .')';
				if($this->db->query("CALL sp_a_run ('RUN','$delete_query')")){
					$row_set_query   = 'SELECT form_view_label_name from cw_form_view_setting where form_view_type = "3" and prime_view_module_id = "'. $this->control_name .'" and trans_status = 1';
					$row_set_info    = $this->db->query("CALL sp_a_run ('SELECT','$row_set_query')");
					$row_count       = (int)$row_set_info->num_rows();
					$row_set_info->next_result();
					if($row_count !== 0){
						$row_set_result         = $row_set_info->result();
						$delete_table_name      = '';
						$delete_table_condition = '';
						foreach($row_set_result as $row_set){
							$row_set_table_name      = "cw_".$this->control_name."_".$row_set->form_view_label_name;
							$delete_table_name      .= "$row_set_table_name,";
							$delete_table_condition .= " $row_set_table_name.$this->prime_id  in ('$delete_ids') and";
						}
						$delete_table_name           = rtrim($delete_table_name,',');
						$delete_table_condition      = rtrim($delete_table_condition,'and');
						$delete_row_set_query  = 'DELETE FROM '. $delete_table_name .'  WHERE '. $delete_table_condition.'';
						$this->db->query("CALL sp_a_run ('RUN','$delete_row_set_query')");						
					}
					$can_process = False;
				}
				
			}
		}
		if($can_process){
			$created_on = date("Y-m-d h:i:s");
			$prime_upd_query    .= 'trans_deleted_by = "'. $this->logged_id .'",trans_deleted_date = "'.$created_on.'"';
			$prime_update_query  = 'UPDATE '. $this->prime_table .' SET trans_status = 0,'. $prime_upd_query .' WHERE '. $this->prime_id .' in ('. $delete_ids .')';
			if($this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')")){
				echo json_encode(array('success' => TRUE, 'message' => "Successfully Deleted"));
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Unable to delete"));
			}
		}else
		if($delete_status){
			echo json_encode(array('success' => TRUE, 'message' => "Successfully Deleted"));
		}else{
			$modules = ucwords($check_table_rlst->prime_module_id);
			echo json_encode(array('success' => FALSE, 'message' => "Unable to delete, This value is already used in $modules modules"));
		}
	}
	
	//CHECK UNIQUE FIELD STATUS
	public function check_delete_status(){
		$check_delete_query  = 'SELECT GROUP_CONCAT(unique_field) as unique_field from cw_form_setting WHERE prime_module_id = "'. $this->control_name .'" and  trans_status = 1 ';
		$check_delete_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_delete_query')");
		$check_delete_rlst   = $check_delete_info->row();
		$check_delete_info->next_result();
		$unique_info         = explode(",",$check_delete_rlst->unique_field);
		if(in_array('1', $unique_info)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	

	//UPDATE STATUS TO DELETE FOR UPLOAD FILES or DOCUMENTS
	public function remove_file(){
		$prime_id_val  = $this->input->post('prime_id_val');
		$is_defult     = (int)$this->input->post('is_defult');
		$input_name     = $this->input->post('input_name');
		$table_name = '';
		if($is_defult === 1){
			$table_name = $this->prime_table;
		}else
		if($is_defult === 2){
			$table_name = $this->cf_table;
		}
		if($table_name){
			$created_on    = date("Y-m-d h:i:s");
			$set_query     = $input_name .' = "" ,trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
			$update_query  = 'UPDATE '.$table_name .' SET '. $set_query .' WHERE '. $this->prime_id .' = "'. $prime_id_val .'"';
			$this->db->query("CALL sp_a_run ('UPDATE','$update_query')");
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Unable to process your request"));
		}
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

		//Payroll Config settings
	public function payroll_config(){
		$column_qry    = 'select * from cw_form_setting where prime_module_id = "employees" and trans_status = 1 and transaction_type != 4';
		$column_data   = $this->db->query("CALL sp_a_run ('SELECT','$column_qry')");
		$column_result = $column_data->result();
		$column_data->next_result();
		$data['column_list']   = $column_result;
		$this->load->view("$this->control_name/payroll",$data);
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
	
	public function get_permission_list(){
		$role = $this->input->post('user_right');
		/*============ BSK EMPLOYEE CUSTOME BLOCK ============*/
		$modules = array();
		//if($role === "1"){ //if role is super admin
			$controller = "employee_permission";
		/*}else{
			$controller = "employee_permission";
		}*/

		foreach($this->Module->get_all_modules($controller) as $module){
			$module->module_id = $this->xss_clean($module->module_id);
			$module->grant     = $this->xss_clean($this->Module->has_grant($controller,$module->module_id, $role));
			$module->access    = $this->xss_clean($this->Module->has_access($controller,$module->module_id, $role));
			$modules[] = $module;
		}		
		$menu_array         = array();
		$menu_data_array    = array();
		$submenu_data_array = array();
		$menu_sts_array     = array();
		$submenu_sts_array  = array();
		$menu_checked       = array();
		foreach($modules as $module){		
			$access_add         = $module->access[0]['access_add'];
			$access_update      = $module->access[0]['access_update'];
			$access_delete      = $module->access[0]['access_delete'];
			$access_search      = $module->access[0]['access_search'];
			$access_export      = $module->access[0]['access_export'];
			$access_import      = $module->access[0]['access_import'];
			$grants_menu_id     = $module->access[0]['grants_menu_id'];
			$grants_sub_menu_id = $module->access[0]['grants_sub_menu_id'];
			$check_box_input = form_checkbox("grants[]", $module->module_id, $module->grant, "class='module'");		
			$menu_input = form_checkbox("menu_id", $module->menu_id, $grants_menu_id,"id='".str_replace(" ","_",strtolower($module->menu_name))."'", "class='menu_id'");
			$sub_menu_input = form_checkbox("sub_menu_id", $module->sub_menu_id, $grants_sub_menu_id,"id='".str_replace(" ","_",strtolower($module->sub_menu_name."_".$module->menu_id))."'", "class='sub_menu_id'");			
			if((int)$module->menu_id === (int)$grants_menu_id){
				$menu_checked[$module->menu_name] = str_replace(" ","_",strtolower($module->menu_name));
			}
			$menu_name       = $module->menu_name;
			$sub_menu_name   = $module->sub_menu_name;
			$module_name     = $module->module_name;		
			$add_id          = $module->module_id ."::add";
			$add_checkbox    = form_checkbox(array("name" =>'access[]',"value" => $add_id,   "checked" => ($access_add) ? 1 : 0));
			$update_id       = $module->module_id ."::update";
			$update_checkbox = form_checkbox(array("name" =>'access[]',"value" => $update_id, "checked" => ($access_update) ? 1 : 0));
			$delete_id       = $module->module_id ."::delete";
			$delete_checkbox = form_checkbox(array("name" =>'access[]',"value" => $delete_id, "checked" => ($access_delete) ? 1 : 0));
			$search_id       = $module->module_id ."::search";                                
			$search_checkbox = form_checkbox(array("name" =>'access[]',"value" => $search_id, "checked" => ($access_search) ? 1 : 0));
			$export_id       = $module->module_id ."::export";                                
			$export_checkbox = form_checkbox(array("name" =>'access[]',"value" => $export_id, "checked" => ($access_export) ? 1 : 0));
			$import_id       = $module->module_id ."::import";                                
			$import_checkbox = form_checkbox(array("name" =>'access[]',"value" => $import_id, "checked" => ($access_import) ? 1 : 0));
			
			$access_data  = "<div style='padding:8px 15px;border-bottom:1px dashed #CCCCCC;margin-bottom:15px;background-color: #f2f2f2;'>
								<label class='checkbox-inline'> $add_checkbox Add</label>
								<label class='checkbox-inline'> $update_checkbox Update</label>
								<label class='checkbox-inline'> $delete_checkbox Delete</label>
								<label class='checkbox-inline'> $search_checkbox Search</label>
								<label class='checkbox-inline'> $export_checkbox Export Data</label>
								<label class='checkbox-inline'> $import_checkbox Import Data</label>
							</div>"; 
			$grand_data   = "<label class='checkbox-inline' style='margin-bottom:6px;'>
								$check_box_input  <span class='prime_color'><b>$module_name :</b></span> Add, Update, Delete, and Search $module_name
							</label>";
			$menu_data    = "<label class='checkbox-inline' style='margin-bottom:6px;'>
								$menu_input  <span style='color:#000000;Font-size:16px;'><b>$menu_name</b></span> 
							</label>";
			$sub_menu_data    = "<label class='checkbox-inline' style='margin-bottom:6px;'>
								$sub_menu_input  <span style='color:#4DC147;Font-size:14px;'><b>$sub_menu_name</b></span> 
							</label>";		
			if((int)$form_view->role === 1){
				$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
				$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
				$menu_data_array[$menu_name]        = $menu_data;
				$submenu_data_array[$sub_menu_name] = $sub_menu_data;
	
				if((int)$module->menu_id === (int)$grants_menu_id){
					$menu_sts_array[$menu_name] = " ";
				}else{
					$menu_sts_array[$menu_name] = "style='display:none;'";
				}
				if((int)$module->sub_menu_id === (int)$grants_sub_menu_id){
					$submenu_sts_array[$sub_menu_name] = " ";
				}else{
					$submenu_sts_array[$sub_menu_name] = "style='display:none;'";
				}
			}else{
				$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
				$admin_module = array("module_setting"=>true,"tester"=>true,"config"=>true);
				if(!$admin_module[$module->module_id]){
					$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
					$menu_data_array[$menu_name]        = $menu_data;
					$submenu_data_array[$sub_menu_name] = $sub_menu_data;
					if((int)$module->menu_id === (int)$grants_menu_id){
						$menu_sts_array[$menu_name] = " ";
					}else{
						$menu_sts_array[$menu_name] = "style='display:none;'";
					}
					if((int)$module->sub_menu_id === (int)$grants_sub_menu_id){
						$submenu_sts_array[$sub_menu_name] = " ";
					}else{
						$submenu_sts_array[$sub_menu_name] = "style='display:none;'";
					}
				}
			}
		}
		$li_line = "";
		foreach ($menu_array as $menu_name => $value) {
			$menu = $menu_data_array[$menu_name];
			$name = str_replace(" ","_",strtolower($menu_name));
			$menu_sts = $menu_sts_array[$menu_name];
			$sub_line = "";
			foreach ($value as $sub_menu_name => $data) {
				$sub_menu     = $submenu_data_array[$sub_menu_name];
				$sub_menu_sts = $submenu_sts_array[$sub_menu_name];
				$tr_line = "";
				foreach ($data as $key => $tr_value){
					$grand_data  = $tr_value['grand_data'];
					$access_data = $tr_value['access_data'];
					$tr_line .=  "<li>
										$grand_data
										$access_data
									</li>";
				}	
				$tr_line = "<ul id='ul_$sub_menu_name' $sub_menu_sts>$tr_line</ul>";
				$sub_line .= "<li>	
								$sub_menu
								$tr_line
							</li>";
			}	
	
			$sub_line = "<ul id='ul_$name' $menu_sts>$sub_line</ul>";
			$li_line .= "<li>	
							$menu
							$sub_line
						</li>";
		}

		echo json_encode(array("li_line"=>$li_line,"menu_checked"=>$menu_checked)); 
	}
	
	//Manually enter the employee code exit checking
	public function employee_code_exit(){	
		$employee_code  = $this->input->post('employee_code');
		$form_id        = $this->input->post('view_id');
		$emp_code_check_qry    = 'select * from cw_employees where employee_code = "'.$employee_code.'" and prime_employees_id !="'.$form_id.'"';
		$emp_code_check_info   = $this->db->query("CALL sp_a_run ('RUN','$emp_code_check_qry')");
		$emp_code_check_result = $emp_code_check_info->result();
		$emp_code_check_info->next_result();
		$num_rows      = $emp_code_check_info->num_rows();
		if((int)$num_rows > 0){
			echo json_encode(array('success' => FALSE, 'message' => "Employee Code already Exit! please enter another code"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Continue to fill further information"));
		}
	}
	
	public function get_excel_template(){
		$module_id      = $this->input->post('module_id');
		$import_type    = $this->input->post('import_type');
		$excel_format_qry = 'select prime_excel_format_id,excel_name from cw_util_excel_format where excel_module_id = "'.$module_id.'" and import_type="'.$import_type.'" and trans_status = 1';
		$excel_format   = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
		$excel_result    = $excel_format->result();
		$excel_format->next_result();
		$excel_format_drop[0] = "---- Excel Format ----";
		foreach($excel_result as $excel){
			$prime_excel_format_id = $excel->prime_excel_format_id;
			$excel_name            = $excel->excel_name;
			$excel_format_drop[$prime_excel_format_id] = $excel_name;
		}
		echo json_encode(array('success' => TRUE, 'excel_format_drop' => $excel_format_drop));
	}
	
	public function tax_range_check(){
		$tax_loc      = $this->input->post('tax_loc');
		$tax_range_qry = 'select count(*) as rslt_range from cw_professional_tax where trans_status =1 and location = "'.$tax_loc.'"';
		$tax_range_data  = $this->db->query("CALL sp_a_run ('SELECT','$tax_range_qry')");
		$tax_range_data_result = $tax_range_data->result();
		$tax_range_data->next_result();
		$range_count = $tax_range_data_result[0]->rslt_range;
		if((int)$range_count === 0){
			echo json_encode(array('success' => False, 'msg' => "Please set the tax range for this location?"));
		}else{
			echo json_encode(array('success' => TRUE));
		}
	}
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
	public function curl($emp_data){
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
	}
	
	public function offerno_exit(){
		$offer_no      = $this->input->post('offer_no');
		$mobile_number = $this->input->post('mobile_number');
		$offerno_exit_qry  = 'select count(*) as offer_rslt from cw_offer_letter where employee_mobile_number= "'.$mobile_number.'" and offer_reference_number="'.$offer_no.'"';
		$offerno_exit_data  = $this->db->query("CALL sp_a_run ('SELECT','$offerno_exit_qry')");
		$offerno_exit_result = $offerno_exit_data->result();
		$offerno_exit_data->next_result();
		$offer_count = $offerno_exit_result[0]->offer_rslt;
		if((int)$offer_count === 1){
			echo json_encode(array('success' => TRUE, 'message' => "Ok, Proceed!"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Invalid Offer Reference Number!"));
		}
	}
	
	public function check_payroll(){
		$resignation_date  = $this->input->post('resignation_date');
		$role              = $this->input->post('role');
		//Get Month Days start and end date
		$month_day_qry     = 'select category,day_conditions,day_count,day_start,day_end from cw_month_day where cw_month_day.trans_status = 1 and category ="'.$role.'"';	
		$month_day_data    = $this->db->query("CALL sp_a_run ('SELECT','$month_day_qry')");
		$month_day_result  = $month_day_data->result();
		$month_day_data->next_result();
		if($month_day_result){
			$role           = $month_day_result[0]->category;
			$day_conditions = $month_day_result[0]->day_conditions;
			$day_count      = $month_day_result[0]->day_count;
			$day_start      = $month_day_result[0]->day_start;
			$day_end        = $month_day_result[0]->day_end;
			if((int)$day_conditions === 3){
				$prev_month = date("Y-m-".$day_start,strtotime("-1 month", strtotime($resignation_date)));
				$end_month  = date("Y-m-".$day_end, strtotime($resignation_date));
			}else{
				$sal_start  = '01';
				if((int)$day_conditions === 2){
					$day_end = date("t");
				}
				$prev_month = date("Y-m-".$sal_start,strtotime($resignation_date));
				$end_month  = date("Y-m-".$day_end,strtotime($resignation_date));
			}
		}
		$end_date       = strtotime($end_month);
		$start_date     = strtotime($prev_month);
		$resign_date    = strtotime($resignation_date);
		$process_month  = date("m-Y",strtotime($end_month));
		if(($resign_date >= $start_date) && ($resign_date <= $end_date)){
			$payroll_exit_qry  = 'select count(prime_transactions_id) as payroll_rslt from cw_transactions where transactions_month= "'.$process_month.'" and trans_status=1';
			$payroll_exit_data  = $this->db->query("CALL sp_a_run ('SELECT','$payroll_exit_qry')");
			$payroll_exit_result = $payroll_exit_data->result();
			$payroll_exit_data->next_result();
			$payroll_count = $payroll_exit_result[0]->payroll_rslt;
			if((int)$payroll_count > 0){
				echo json_encode(array('success' => FALSE, 'message' => "Already Payroll Proceed! Do you want to proceed?"));
			}else{
				echo json_encode(array('success' => TRUE, 'message' => "Ok Proceed!!!"));
			}
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Ok Proceed!!!"));
		}
	}
	
	public function check_termination_status(){
		$employee_code     = $this->input->post('employee_code');
		$payroll_exit_qry  = 'select count(prime_transactions_id) as payroll_rslt from cw_transactions where employee_code ="'.$employee_code.'" and trans_status=1 and termination_status =1';
		$payroll_exit_data  = $this->db->query("CALL sp_a_run ('SELECT','$payroll_exit_qry')");
		$payroll_exit_result = $payroll_exit_data->result();
		$payroll_exit_data->next_result();
		$payroll_count = $payroll_exit_result[0]->prime_transactions_id;
		if((int)$payroll_count > 0){
			echo json_encode(array('success' => FALSE, 'message' => "Already separation is completed, not possible to release?"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Employee resignation is revoked!!!"));
		}
	}
	
	public function check_emp_code($emp_code,$form_id = -1){
		if($emp_code){
			$select_tl_qry    = 'select * from cw_employees where employee_code = "'.$emp_code.'" and trans_status = 1';
			if((int)$form_id > 0){
				$select_tl_qry    .= " and prime_employees_id != $form_id";
			}
			$select_tl_data   = $this->db->query("CALL sp_a_run ('SELECT','$select_tl_qry')");
			$count = $select_tl_data->num_rows();
			$select_tl_data->next_result();
			if((int)$count > 0){
				return FALSE;
			}else{
				return TRUE;
			}
		}else{
			return TRUE;
		}
	}
	public function check_payroll_exit(){
		$stop_pay_month     = $this->input->post('stop_pay_month');
		$employee_code      = $this->input->post('employee_code');
		$payroll_exit_qry  = 'select count(prime_transactions_id) as payroll_rslt from cw_transactions where employee_code ="'.$employee_code.'" and trans_status=1 and transactions_month ="'.$stop_pay_month.'"';
		$payroll_exit_data  = $this->db->query("CALL sp_a_run ('SELECT','$payroll_exit_qry')");
		$payroll_exit_result = $payroll_exit_data->result();
		$payroll_exit_data->next_result();
		$payroll_count = $payroll_exit_result[0]->payroll_rslt;
		if((int)$payroll_count === 0){
			echo json_encode(array('success' => TRUE, 'message' => "Ok Proceed!!!"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Already Payroll is completed?"));
		}
	}
	
	//EMPLOYEES TABLE LOG FOR EVERY UPFDATE OF THE EMPLOYEES
	public function employee_log($emp_id,$emp_log){
		$created_on        = date("Y-m-d H:i:s");
		$logged_id         = $this->logged_id;
		$emp_label_name    = array_keys($emp_log);
		$emp_label_value   = implode(",",$emp_label_name);
		$emp_data_qry      = "select $emp_label_value from cw_employees where prime_employees_id = $emp_id";
		$emp_data          = $this->db->query("CALL sp_a_run ('SELECT','$emp_data_qry')");
		$emp_result        = $emp_data->result_array();
		$emp_data->next_result();
		$fin_emp_result = $emp_result[0]; 
		$emp_code       = $fin_emp_result['employee_code'];
		$emp_name       = $fin_emp_result['emp_name'];
		$emp_data_dif   = array_diff_assoc($fin_emp_result,$emp_log);
		$prime_qry_value  = "";
		$prime_qry_key    = "prime_employees_id,employee_code,emp_name,label_name,old_value,new_value,trans_created_by,trans_created_date";
		foreach($emp_data_dif as $emp_key=>$emp_value){
			$old_label_val  = $fin_emp_result[$emp_key];
			$new_label_val  = $emp_log[$emp_key];
			if(($old_label_val != $new_label_val) && ($old_label_val !=='0.00' && $new_label_val !='') && ($old_label_val !=='0000-00-00' && $new_label_val !='')){
				$prime_qry_value .= "(\"$emp_id\",\"$emp_code\",\"$emp_name\",\"$emp_key\",\"$old_label_val\",\"$new_label_val\",\"$logged_id\",\"$created_on\"),";
			}
		}
		if(!empty($prime_qry_value)){
			$prime_qry_value = rtrim($prime_qry_value,',');
			$prime_insert_query = "insert into cw_employees_log ($prime_qry_key) values $prime_qry_value";
			$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
			$insert_result      = $insert_info->result();
			$insert_info->next_result();
		}
	}
	
	//CHECK LOAN INSTALLMENT
	public function check_loan_installment(){
		$employee_code     = $this->input->post('employee_code');
		$emp_data_qry      = "select count(*) as rslt_count from  cw_loan_installment where emp_code = $employee_code and paid_status = 0 and trans_status=1";
		$emp_data          = $this->db->query("CALL sp_a_run ('SELECT','$emp_data_qry')");
		$emp_result        = $emp_data->result_array();
		$emp_data->next_result();
		$rslt_count = (int)$emp_result[0]->rslt_count;
		if($rslt_count >= 1){
			echo json_encode(array('success'=>false,'message'=>'Cannot process'));
		}else{
			echo json_encode(array('success'=>true,'message'=>'Can process'));
		}
	}	
	public function check_file_exists(){
		$month        = $this->input->post('month');
		$emp_code     = $this->input->post('emp_code');
		/* $payslip_url  = base_url("/payslip/staff/".$month."_payslip/".$emp_code.".pdf"); */
		$payslip_url  = "./payslip/staff/".$month."_payslip/".$emp_code.".pdf";
		if (file_exists($payslip_url)) {  
			echo json_encode(array('success'=>true,'message'=>'Can process','url'=>$payslip_url));
		}else{
			echo json_encode(array('success'=>false,'message'=>'Cannot process'));
		}
	}
}
?>