<?php 
/**********************************************************
	   Filename: Form Setting / Screen Setting
	Description: Form Setting / Screen Setting for creating new view,input and manage conditions.
		 Author: udhayakumar Anandhan
	 Created on: ‎‎26 ‎November ‎2018
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
if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Secure_Controller.php");
class Form_setting  extends Secure_Controller{
	public function __construct(){
		parent::__construct('form_setting');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_form_setting_headers());
		$this->load->view('form_setting/manage',$data);
	}
	/* ==============================================================*/
	/* ================== COMMON OPEARTION - START ==================*/
	/* ==============================================================*/
	//MODULE SEARCH OPEARTION
	public function search(){
		$search       = $this->input->get('search');
		$limit        = $this->input->get('limit');
		$offset       = $this->input->get('offset');
		$sort         = $this->input->get('sort');
		$order        = $this->input->get('order');
		
		if(!$sort){
			$sort = "abs(menu_sort),abs(sort)";
		}
		if(!$order){
			$order = "asc";
		}

		// Fetch Records
		$info     = $this->db->query("CALL sp_form_setting_search ('SEARCH','$search','$offset','$limit','$sort','$order')");
		$result   = $info->result();
		$info->next_result();
		$data_rows     = array();
		foreach ($result as $form_setting){
			$prime_module_id = $form_setting->module_id;
			if(!$admin_module[$form_setting->module_id]){ 
				$data_rows[]=get_form_setting_datarows($form_setting,$this);
			}
		}
		$data_rows=$this->xss_clean($data_rows);
		
		// Fetch Records Count
		$count_info     = $this->db->query("CALL sp_form_setting_search ('COUNT','$search','$offset','$limit','$sort','$order')");
		$count_result   = $count_info->result();
		$count_info->next_result();
		$num_rows = $count_result[0]->data_count;
		echo json_encode(array('total'=>$num_rows,'rows'=>$data_rows));
	}
	
	//MODULE VIEW OPEARTION
	public function view($prime_module_id =-1){
		$data['prime_module_id'] = $prime_module_id;
		$logged_id  = $this->session->userdata('logged_id');
		
		$view_table_data = array(
			'prime_view_module_id' => $prime_module_id,
		);
		$view_table_data = json_encode($view_table_data);
		$view_info   = $this->db->query("CALL sp_form_view_setting_crud ('VIEW', '$view_table_data',$logged_id)");
		$view_setting = $view_info->result();
		$view_info->next_result();
		$data['view_setting'] = $view_setting;
		
		$data['update_form_viewui']  = $this->update_form_viewui($prime_module_id);
		$data['update_table_viewui'] = $this->update_table_viewui($prime_module_id);
		$data['update_monthly_input_viewui'] = $this->update_monthly_input_viewui($prime_module_id);
		$data['update_payroll_viewui'] = $this->update_payroll_viewui($prime_module_id);
		$input_for[""] = "---- Input For ----";
		foreach($view_setting as $for){
			$prime_form_view_id = $for->prime_form_view_id;
			$form_view_heading  = $for->form_view_heading;
			$input_for[$prime_form_view_id] = $form_view_heading;
		}
		$data['input_for'] = $input_for;
		
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$field_for[""] = "---- Field For ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$field_for[$role_id] = $category_name;
		}
		$data['field_for'] = $field_for;

		$user_role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_user_role` where trans_status = 1')");
		$user_role_result = $user_role_info->result();
		$user_role_info->next_result();
		$user_role_for[""] = "---- Field For ----";
		foreach($user_role_result as $user_for){
			$role_id   = $user_for->prime_user_role_id;
			$role_name = $user_for->role_name;
			$user_role_for[$role_id] = $role_name;
		}
		$data['user_role_for'] = $user_role_for;
		
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','SHOW TABLES')");
		$table_result = $table_info->result();
		$table_info->next_result();
		$tab_array = array("cw_app_config","cw_form_bind_input","cw_form_condition_formula","cw_form_for_input","cw_form_setting","cw_form_table_cond_for","cw_form_table_search","cw_form_view_setting","cw_grants","cw_import","cw_main_menu","cw_monthly_input","cw_payroll_function","cw_permissions","cw_print_block","cw_print_design","cw_print_info","cw_print_map","cw_print_table","cw_print_table_where","cw_sub_menu","cw_month_day","cw_statutory","cw_professional_tax","cw_professional_tax_tax_range","cw_transactions","cw_sessions","cw_util_excel_format","cw_session_value","cw_util_excel_format_line","cw_modules","cw_report_setting","cw_statutory_field","cw_statutory_function","cw_report_table","cw_report_where","cw_bank_template_char_setting","cw_bank_template_setting","cw_bank_template_table","cw_bank_template_tab_view","cw_file_merge_log","cw_inc_temp_setting","cw_inc_import","cw_increment_template","cw_pf_challan_setting","dailyunpunch","monthlyattdata");
		$table_list[""] = "---- Select Table ----";
		foreach($table_result as $table){
			$db_name = "Tables_in_".$this->config->item('db_name');
			$table_name = $table->$db_name;
			if((strpos($table_name, "cw_zct_") === false)&&(strpos($table_name, "_cf") === false)){
				if(!in_array($table_name, $tab_array)){
					$str = substr($str, 1);
					$table_value = substr((ucwords(str_replace("_"," ",$table_name))),3);
					$table_list[$table_name] = $table_value;
				}
			}
		}

		$data['table_list'] = $table_list;
		
		$table_prime    = "cw_".$prime_module_id;
		$table_prime_id = "prime_".$prime_module_id."_id";
		//$table_cf       = "cw_".$prime_module_id."_cf";
		//$table_cf_id    = "prime_".$prime_module_id."_cf_id";
		$table_names    = "$table_prime,$table_cf,";
		$prime_ids      = "$table_prime_id,$table_cf_id,";
		$view_qry    = 'select * from cw_form_view_setting where prime_view_module_id = "'.$prime_module_id.'" and  form_view_type = "3" and trans_status = 1';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_qry')");
		$view_result = $view_data->result();
		$view_data->next_result();
		foreach($view_result as $view){
			$form_view_label_name = $view->form_view_label_name;
			$table_names .= "cw_".$prime_module_id."_".$form_view_label_name.",";
			$prime_ids  .= "prime_".$prime_module_id."_".$form_view_label_name."_id,";
		}
		$table_names = rtrim($table_names,',');
		$table_names = '"'.str_replace(",",'","',$table_names).'"';
		$prime_ids = rtrim($prime_ids,',');
		$prime_ids = '"'.str_replace(",",'","',$prime_ids).'"';
		
		$form_input_list = $this->get_form_input_array($prime_module_id); //ADDED by BSK

		$get_colums = 'SELECT `COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_names.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ('.$prime_ids.')';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();									
		$column_list[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$column_value = $column->COLUMN_NAME;
			//$column_name = ucwords(str_replace("_"," ",$column_value));
			$column_name  = $form_input_list[$column_value]; //ADDED by BSK
			if($column_name){ //ADDED by BSK
				$column_list[$column_value] = $column_name;
			}			
		}
		$data['column_list'] = $column_list;
		
		$cond_view = $this->condition_formula_view($prime_module_id);
		$cond_content_rslt = json_decode($cond_view);
		$cond_content      = $cond_content_rslt->cond_content;
		$data['cond_content'] = $cond_content;
		
		$cond_table_data = array( 'cond_module_id' => $prime_module_id);
		$cond_table_data = json_encode($cond_table_data);
		$cond_info = $this->db->query("CALL sp_cond_crud ('VIEW', '$cond_table_data',$logged_id)");
		$cond_rslt = $cond_info->result();
		$cond_info->next_result();
		//print_r($cond_rslt); die;
		$add_cond_content[""] = "---- Select Condition / Formula ----";
		foreach($cond_rslt as $rslt){
			$prime_cond_id        = $rslt->prime_cond_id;
			$cond_module_id       = $rslt->cond_module_id;
			$condition_label_name = $rslt->condition_label_name;
			$add_cond_content[$prime_cond_id] = $condition_label_name;
		}
		$data['add_cond_content'] = $add_cond_content;
		/*============================*/
		/*-- UDY - START 13-02-2020 --*/
		/*============================*/		
		$pick_base_qry    = 'select prime_form_id,label_name,view_name,prime_module_id,pick_list_type,pick_list,pick_table from cw_form_setting where prime_module_id = "'.$prime_module_id.'" and  field_type in ("5","7") and trans_status = 1';
		$pick_base_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_base_qry')");
		$pick_base_result = $pick_base_data->result();
		$pick_base_data->next_result();
		$pick_key      = array_column($pick_base_result, "prime_form_id");
		$pick_val      = array_column($pick_base_result, "view_name");
		$query_list_id = array_combine($pick_key, $pick_val);
		$query_list_id = array("" => "---- Select Pick list ----") + $query_list_id;
		$data['query_list_id'] = $query_list_id;
		$data['pick_query_list'] = $this->get_pick_base_query_list($prime_module_id);
		$data['user_role_cond_list'] = $this->get_role_base_cond_list($prime_module_id);
		/*===========================*/
		/*-- UDY - END 13-02-2020 --*/
		/*===========================*/		
		$file_type_query  = 'SELECT prime_upload_extension_id,file_type,LOWER(extension) as extension FROM cw_upload_extension WHERE cw_upload_extension.trans_status = 1';
		$file_type_info   = $this->db->query("CALL sp_a_run ('SELECT','$file_type_query')");
		$file_type_result = $file_type_info->result();
		$file_type_info->next_result();
		if($file_type_result){
			$pick_key   = array_column($file_type_result, 'extension');
			$pick_val   = array_column($file_type_result, 'extension');
			$file_type_pick = array_combine( $pick_key, $pick_val);
			$file_type_pick = array("" => "---- Select Extension ----") + $file_type_pick;
			$data['upload_extension'] = $file_type_pick;
		}
		
		$this->load->view("form_setting/form",$data);
	}
	/* ==============================================================*/
	/* =================== COMMON OPEARTION - END ===================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* =================== FORM OPEARTION - START ===================*/
	/* ==============================================================*/
	//FORM INPUT SAVE OPEARTION
	public function save(){
		$prime_form_id         = $this->input->post('prime_form_id');
		$prime_module_id       = $this->input->post('prime_module_id');
		$mandatory_field       = $this->input->post('mandatory_field');
		$unique_field          = $this->input->post('unique_field');
		$field_show            = $this->input->post('field_show');
		$table_show            = $this->input->post('table_show');
		$search_show           = $this->input->post('search_show');
		$field_type            = $this->input->post('field_type');
		$transaction_type      = $this->input->post('transaction_type');
		$gross_check           = $this->input->post('gross_check');
		$taxable_check         = $this->input->post('taxable_check');
		$earn_month_check      = $this->input->post('earn_month_check');
		$earn_payroll_check    = $this->input->post('earn_payroll_check');
		$ded_payroll_check     = $this->input->post('ded_payroll_check');
		$benefit_check         = $this->input->post('benefit_check');
		$increment_check       = $this->input->post('increment_check');
		$arrear_pf_check       = $this->input->post('arrear_pf_check');
		$fandf_check           = $this->input->post('fandf_check');
		$deduction_check       = $this->input->post('deduction_check');
		$deduction_month_check = $this->input->post('deduction_month_check');
		$loan_check            = $this->input->post('loan_check');
		$uniform_check         = $this->input->post('uniform_check');
		$pick_list_type        = $this->input->post('pick_list_type');
		$pick_list_import      = $this->input->post('pick_list_import');
		$picklist_data         = $this->input->post('picklist_data');
		$duplicate_data        = $this->input->post('duplicate_data');
		$label_name            = strtolower(str_replace(" ","_",trim($this->input->post('label_name'))));
		$view_name             = ucwords(trim($this->input->post('view_name')));
		$short_name            = trim($this->input->post('short_name'));
		$logged_id             = $this->session->userdata('logged_id');
		$edit_read             = $this->input->post('edit_read');
		
		$mandatory_value       = 0;
		$unique_value          = 0;
		$show_value            = 0;
		$table_show_val        = 0;
		$search_show_val       = 0;
		$edit_read_val         = 0;
		$picklist_data_val     = 0;
		$duplicate_data_val    = 0;
		if($mandatory_field === "on"){ $mandatory_value = 1; }
		if($unique_field === "on"){ $unique_value = 1; }
		if($field_show === "on"){ $show_value = 1; }
		if($table_show === "on"){ $table_show_val = 1; }
		if($search_show === "on"){ $search_show_val = 1; }
		if($edit_read === "on"){ $edit_read_val = 1; }
		if($picklist_data === "on"){ $picklist_data_val = 1; }
		if($duplicate_data === "on"){ $duplicate_data_val = 1; }
		
		
		$gross_check_val           = 0;
		$taxable_check_val         = 0;
		$earn_month_check_val      = 0;
		$earn_payroll_check_val    = 0;
		$ded_payroll_check_val     = 0;
		$benefit_check_val         = 0;
		$increment_check_val       = 0;
		$arrear_pf_check_val       = 0;
		$fandf_check_val           = 0;
		$deduction_check_val       = 0;
		$deduction_month_check_val = 0;
		$loan_check_val            = 0;
		$uniform_check_val         = 0;
		
		if($gross_check === "on"){
			$gross_check_val = 1; 
		}
		if($taxable_check === "on"){
			$taxable_check_val = 1; 
		}
		if($earn_month_check === "on"){
			$earn_month_check_val = 1; 
		}
		if($earn_payroll_check === "on"){
			$earn_payroll_check_val = 1; 
		}
		if($ded_payroll_check === "on"){
			$ded_payroll_check_val = 1; 
		}
		if($benefit_check === "on"){
			$benefit_check_val = 1; 
		}
		if($increment_check === "on"){
			$increment_check_val = 1;
		} 
		if($arrear_pf_check === "on"){
			$arrear_pf_check_val = 1; 
		}
		if($fandf_check === "on"){
			$fandf_check_val = 1; 
		}
		if($deduction_check === "on"){
			$deduction_check_val = 1; 
		}
		if($deduction_month_check === "on"){
			$deduction_month_check_val = 1; 
		}
		if($loan_check === "on"){
			$loan_check_val = 1; 
		}
		if($uniform_check === "on"){
			$uniform_check_val = 1; 
		}		
		
		$input_for = $this->input->post('input_for');
		$view_query  = 'SELECT form_view_type,form_view_label_name FROM `cw_form_view_setting` where prime_form_view_id = "'.$input_for.'"';
		$view_info   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
		$view_result = $view_info->result();
		$view_info->next_result();
		$input_view_type      = $view_result[0]->form_view_type;
		$form_view_label_name = $view_result[0]->form_view_label_name;
		
		$table_data = array(
			'prime_form_id'   => $this->input->post('prime_form_id'),
			'prime_module_id' => $this->input->post('prime_module_id'),
			'input_view_type' => $input_view_type,
			'input_for'       => $this->input->post('input_for'),
			'field_type'      => $this->input->post('field_type'),
			'label_name'      => $label_name,
			'view_name'       => $view_name,
			'short_name'      => $short_name,
			'field_length'    => $this->input->post('field_length'),
			'text_type'       => $this->input->post('text_type'),
			'field_decimals'  => $this->input->post('field_decimals'),
			'pick_list_type'  => $pick_list_type,
			'pick_list_import' => $pick_list_import,
			'field_isdefault' => $this->input->post('field_isdefault'),
			'default_value'   => $this->input->post('default_value'),
			'mandatory_field' => $mandatory_value,
			'unique_field'    => $unique_value,
			'field_show'      => $show_value,
			'table_show'      => $table_show_val,
			'search_show'     => $search_show_val,
			'field_for'       => ltrim(implode(",",$this->input->post('field_for[]')),","),
			'upload_extension' => ltrim(implode(",",$this->input->post('upload_extension[]')),","),
			'file_type'       => $this->input->post('file_type'),
			'transaction_type'      => $this->input->post('transaction_type'),
			'gross_check'           => $gross_check_val,
			'taxable_check'         => $taxable_check_val,
			'earn_month_check'      => $earn_month_check_val,
			'earn_payroll_check'    => $earn_payroll_check_val,
			'ded_payroll_check'     => $ded_payroll_check_val,
			'benefit_check'         => $benefit_check_val,
			'increment_check'       => $increment_check_val,
			'arrear_pf_check'       => $arrear_pf_check_val,
			'fandf_check'           => $fandf_check_val,
			'deduction_check'       => $deduction_check_val,
			'deduction_month_check' => $deduction_month_check_val,
			'loan_check'            => $loan_check_val,
			'uniform_check'         => $uniform_check_val,
			'edit_read'             => $edit_read_val,
			'picklist_data'         => $picklist_data_val,
			'duplicate_data'        => $duplicate_data_val,
			'table_sort'            => 0,
		);
		/*SVK EDIT NEED REVIEW */
		if((int)$table_show_val === 1){
			$check_sort_query  = "SELECT max(table_sort) as table_sort FROM  cw_form_setting WHERE prime_module_id = \"$prime_module_id\" AND input_view_type IN (\"1\",\"2\")  AND table_show = \"1\" AND trans_status = \"1\"";
			$check_sort_query   = $this->db->query("CALL sp_a_run ('SELECT','$check_sort_query')");
			$check_sort_info    = $check_sort_query->row();
			$check_sort_query->next_result();
			$table_sort         = (int)$check_sort_info->table_sort;
			if($table_sort > 0){
				$table_data['table_sort'] = $table_sort + 1;
			}
		}
		/*SVK EDIT NEED REVIEW */
		//echo "BSK $field_type :: $pick_list_type :: $field_type "; die;
		if((((int)$field_type === 5) && ((int)$pick_list_type === 1)) || (((int)$field_type === 7) && ((int)$pick_list_type === 1))){
			$table_data['pick_list']  = ltrim(implode(",",$this->input->post('pick_table_col[]')),",");
			$table_data['pick_table'] = $this->input->post('pick_table');
		}else
		if((((int)$field_type === 5) && ((int)$pick_list_type === 2))|| (((int)$field_type === 7) && ((int)$pick_list_type === 2))){
			$table_data['pick_list']  = ltrim(implode(",",$this->input->post('pick_list[]')),",");
			$table_data['pick_table'] = $this->input->post('common_table');
			//echo $table_data['pick_list']."::". $table_data['pick_table']; die;
		}
		if((int)$field_type === 9){
			$table_data['pick_list']  = ltrim(implode(",",$this->input->post('pick_table_col[]')),",");
			$table_data['pick_table'] = $this->input->post('pick_table');
			$table_data['auto_prime_id'] = $this->input->post('auto_prime_id');
			$table_data['auto_dispaly_value'] = $this->input->post('auto_dispaly_value');
		}

		$table_data = json_encode($table_data);
		if((int)$prime_form_id === 0){
			$count_info   = $this->db->query("CALL sp_form_setting_crud ('SORT_COUNT', '$table_data','$logged_id')");
			$count_result = $count_info->result();
			$count_info->next_result();
			$table_data = json_decode($table_data,true);
			$field_sort = (int)$count_result[0]->sort_count + 1;
			$table_data['field_sort'] = $field_sort;
			$table_data = json_encode($table_data);
		}
		
		$can_process  = true;
		if((int)$prime_form_id === 0){
			$exist_query  = 'SELECT count(*) as exist_rslt FROM `cw_form_setting` where prime_module_id = "'.$prime_module_id.'" and label_name = "'.$label_name.'"';
			$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
			$exist_result = $exist_info->result();
			$exist_info->next_result();
			if((int)$exist_result[0]->exist_rslt !== 0){
				$can_process  = false;
			}
		}
		
		if($can_process){
			$info   = $this->db->query("CALL sp_form_setting_crud ('SAVE', '$table_data','$logged_id')");
			$result = $info->result();
			$info->next_result();
			$form_setting  = $this->update_form_viewui($prime_module_id);
			$table_setting = $this->update_table_viewui($prime_module_id);
			if($this->save_table($prime_module_id,$input_view_type,$input_for,$form_view_label_name)){
				echo json_encode(array('success' => TRUE,'form_setting'=>$form_setting,'table_setting'=>$table_setting,'msg' => "Input added Successfully"));
			}else{
				echo json_encode(array('success' => false,'msg'=>"Unable to process your request"));
			}
		}else{
			echo json_encode(array('success' => false, 'frm'=>'exist','msg' => "Input name already exist"));
		}
	}
	
	//FORM INPUT GET OPEARTION FOR EDIT
	public function get_field_info(){
		$table_data = array(
			'prime_module_id' => $this->input->post('prime_module_id'),
			'prime_form_id' => $this->input->post('prime_form_id'),
		);
		$table_data = json_encode($table_data);
		$info   = $this->db->query("CALL sp_form_setting_crud ('EDIT', '$table_data',null)");
		$result = $info->result();
		$info->next_result();
		$colums_list[] = array("key"=>"","value"=>"-- Pick list value ---");
		$field_type     = $result[0]->field_type;
		$pick_list_type = $result[0]->pick_list_type;
		$pick_table     = $result[0]->pick_table;
		if(((int)$pick_list_type === 1) || ((int)$field_type === 9)){
			$get_colums = 'SELECT COLUMN_NAME AS col_name FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND  TABLE_NAME = "'.$pick_table.'" and COLUMN_NAME not like "%trans%"';
			$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
			$colums_result = $colums_info->result();
			$colums_info->next_result();
			//print_r($colums_result); die;
			foreach($colums_result as $colums){
				$key   = $colums->col_name;
				$value = ucwords(str_replace("_"," ",$colums->col_name));
				$colums_list[] = array("key"=>"$key","value"=>"$value");
			}
		}
		echo json_encode(array('success' => TRUE, 'field_info' => $result[0], 'colums_list' => $colums_list ,'field_type' => $field_type ,'pick_list_type' => $pick_list_type));
	}
	
	//FORM INPUT ONCHANGE OPERATION
	public function get_table_info(){
		$pick_table = $this->input->post('pick_table');
		$get_colums = 'SELECT COLUMN_NAME AS col_name FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA`= "'.$this->config->item("db_name").'" AND TABLE_NAME = "'.$pick_table.'" and COLUMN_NAME not like "%trans%"';
		$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$colums_result = $colums_info->result();
		$colums_info->next_result();		
		$colums_list[] = array("key"=>"","value"=>"-- Pick list value ---");
		foreach($colums_result as $colums){
			$key   = $colums->col_name;
			$value = ucwords(str_replace("_"," ",$colums->col_name));
			$colums_list[] = array("key"=>"$key","value"=>"$value");
		}
		echo json_encode(array('success' => TRUE, 'colums_list' => $colums_list));
	}
	
	//FORM INPUT SORT OPEARTION
	public function update_sortorder(){
		$idsInOrder      = $this->input->post('idsInOrder');
		$id_info         = $this->input->post('id_info');
		$logged_id       = $this->session->userdata('logged_id');
		$id_info = explode("-",$id_info);
		$prime_module_id = $id_info[0];
		$input_for       = $id_info[1];
		$sort_order = 0;
		foreach($idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_form_id = $order[1];
				$table_data = array(
					'prime_form_id'   => $prime_form_id,
					'prime_module_id' => $prime_module_id,
					'input_for'       => $input_for,
					'sort_order'      => $sort_order,
				);
				$table_data = json_encode($table_data);
				$info   = $this->db->query("CALL sp_form_setting_crud ('UPD_SORT', '$table_data','$logged_id')");
				$result = $info->result();
				$info->next_result();
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	
	//TABLE SORT OPEARTION
	public function update_table_sortorder(){
		$table_idsInOrder  = $this->input->post('table_idsInOrder');
		$prime_module_id   = $this->input->post('prime_module_id');
		$logged_id         = $this->session->userdata('logged_id');
		$sort_order = 0;
		$upd_query  = 'UPDATE cw_form_setting SET table_sort = 0 WHERE prime_module_id = "'. $prime_module_id .'"';
		$info   = $this->db->query("CALL sp_a_run ('UPDATE','$upd_query')");
		$info->next_result();
		
		foreach($table_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_form_id = $order[1];
				$table_data = array(
					'prime_form_id'   => $prime_form_id,
					'prime_module_id' => $prime_module_id,
					'table_sort'      => $sort_order,
				);
				$table_data = json_encode($table_data);
				$info   = $this->db->query("CALL sp_form_setting_crud ('UPD_TABLE_SORT', '$table_data','$logged_id')");
				$result = $info->result();
				$info->next_result();
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	//Monthly Input Sort Order
	public function update_monthly_sortorder(){
		$table_idsInOrder  = $this->input->post('table_idsInOrder');
		$prime_module_id   = $this->input->post('prime_module_id');
		$logged_id         = $this->session->userdata('logged_id');
		$sort_order = 0;
		$upd_query  = 'UPDATE cw_form_setting SET monthly_input_sort = 0 WHERE prime_module_id = "'. $prime_module_id .'" and (earn_month_check = 1 or deduction_month_check = 1)';
		$info   = $this->db->query("CALL sp_a_run ('UPDATE','$upd_query')");
		$info->next_result();
		
		foreach($table_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_form_id = $order[1];
				$table_data = array(
					'prime_form_id'      => $prime_form_id,
					'prime_module_id'    => $prime_module_id,
					'monthly_input_sort' => $sort_order,
				);
				$table_data = json_encode($table_data);
				$info   = $this->db->query("CALL sp_form_setting_crud ('UPD_MONTHLY_SORT', '$table_data','$logged_id')");
				$result = $info->result();
				$info->next_result();
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	//Payroll Sort Order
	public function update_payroll_sortorder(){
		$table_idsInOrder  = $this->input->post('table_idsInOrder');
		$prime_module_id   = $this->input->post('prime_module_id');
		$logged_id         = $this->session->userdata('logged_id');
		$sort_order = 0;
		$upd_query  = 'UPDATE cw_form_setting SET payroll_sort = 0 WHERE prime_module_id = "'. $prime_module_id .'" and (earn_payroll_check = 1 or ded_payroll_check = 1)';
		$info   = $this->db->query("CALL sp_a_run ('UPDATE','$upd_query')");
		$info->next_result();		
		foreach($table_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_form_id = $order[1];
				$table_data = array(
					'prime_form_id'      => $prime_form_id,
					'prime_module_id'    => $prime_module_id,
					'payroll_sort' => $sort_order,
				);
				$table_data = json_encode($table_data);
				$info   = $this->db->query("CALL sp_form_setting_crud ('UPD_PAYROLL_SORT', '$table_data','$logged_id')");
				$result = $info->result();
				$info->next_result();
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	public function update_form_viewui($prime_module_id){
		$view_table_data    = array( 'prime_view_module_id' => $prime_module_id,);
		$view_table_data    = json_encode($view_table_data);
		$logged_id          = $this->session->userdata('logged_id');		
		$view_info    = $this->db->query("CALL sp_form_view_setting_crud ('VIEW', '$view_table_data',$logged_id)");
		$view_setting = $view_info->result();
		$view_info->next_result();
		$view_content = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p>";
		$count = 0;
		$id_array = array();
		$view_input_count = 0;
		foreach($view_setting as $view){
			$prime_form_view_id   = $view->prime_form_view_id;
			$prime_view_module_id = $view->prime_view_module_id;
			$form_view_type       = (int)$view->form_view_type;
			$form_view_label_name = $view->form_view_label_name;
			$form_view_heading    = ucwords($view->form_view_heading);
			
			$table_data = array(
				'prime_module_id' => $prime_module_id,
				'input_for'       => $prime_form_view_id,
			);
			$table_data = json_encode($table_data);
			$input_info   = $this->db->query("CALL sp_form_setting_crud ('VIEW_INPUT', '$table_data',$logged_id)");
			$input_result = $input_info->result();
			$view_input_count += $input_info->num_rows();
			$input_info->next_result();
			$input_li = "";
			$input_count = 0;
			$field_type_array =  array(1=>"Text",2=>"Decimals",3=>"Integer",4=>"Date",5=>"Picklist",6=>"Checkbox",7=>"Multi Picklist",8=>"summary box",9=>"Auto complete box",10=>"File upload box",11=>"Mobile Number",12=>"Email",13=>"Date & Time",14=>"Read Only");
			foreach($input_result as $setting){ 
				$input_count++;
				$prime_form_id   = $setting->prime_form_id;
				$field_type      = $setting->field_type;
				$view_name       = ucwords($setting->view_name);
				$short_name      = $setting->short_name;
				$mandatory_field = $setting->mandatory_field;
				$unique_field    = $setting->unique_field;
				$field_show      = $setting->field_show;
				$table_show      = $setting->table_show;
				$search_show     = $setting->search_show;
				$field_for       = $setting->field_for;
				$earn_month_check         = $setting->earn_month_check;
				$deduction_month_check    = $setting->deduction_month_check;
				$edit_read                = $setting->edit_read;
				$li_id = "li_".$prime_form_id;
				$a_id  = "a_".$prime_form_id."_$input_count";
				$field_type_name = $field_type_array[$field_type];
				$mandatory = "";
				if((int)$mandatory_field === 1){
					$mandatory = "required";
				}
				$show_icon = "<i class='fa fa-eye-slash' aria-hidden='true'></i>";
				if((int)$field_show === 1){
					$show_icon = "<i class='fa fa-eye' aria-hidden='true'></i>";
				}
				$table_icon = "";
				if((int)$table_show === 1){
					$table_icon = "<i class='fa fa-table' aria-hidden='true'></i>";
				}
				$search_icon = "";
				if((int)$search_show === 1){
					$search_icon = "<i class='fa fa-filter' aria-hidden='true'></i>";
				}
				$unique_icon = "";
				if((int)$unique_field === 1){
					$unique_icon = "<i class='fa fa-key' aria-hidden='true'></i>";
				}
				$read_icon = "";
				if((int)$edit_read === 1){
					$read_icon = "<i class='fa fa-exclamation' aria-hidden='true'></i>";
				}				
				$month_icon = "";
				if(((int)$earn_month_check === 1) || ((int)$deduction_month_check === 1)){
					$month_icon = "<i class='fa fa-calendar-plus-o' aria-hidden='true'></i>";
				}
				
				
				
				$input_li .=  "<li class='ui-state-default' id='$li_id'>
								<table style='width:100%;'>
									<tr>
										<td style='font-weight:bold'>
											<label class='$mandatory'>$view_name</label><br/>
											<span style='font-size:13px;font-weight:normal;color:#999999;'>
												$field_type_name &nbsp; $show_icon &nbsp; $unique_icon &nbsp; $search_icon &nbsp; $table_icon &nbsp; $month_icon &nbsp; $read_icon
											</span>
										</td>
										<td style='text-align:right;'>
											<a id='$a_id' class='prime_color' onclick=get_field_info('$prime_form_id','$a_id');><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i></a>
										</td>
									</tr>
								</table>
							</li>";
			}
			$ui_id = $prime_module_id."-".$prime_form_view_id;
			$id_array[] = $ui_id;
			$ul_li = "<ul id='$ui_id' class='sortable'>$input_li</ul>";
			if($form_view_type === 1){
				$view_content .= "<div id='$form_view_label_name' style='font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px;padding:8px;margin-bottom:10px;'>
									<h4 class='prime_color'>$form_view_heading</h4>
									$ul_li
								</div>";
			}else
			if($form_view_type === 2){
				$count++;
				$tab_active = "";
				$content_active = "";
				if((int)$count === 1){
					$tab_active = "active";
					$content_active = "in active";
					$view_content .= "<div style='font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px;margin-bottom:10px;'>
										<ul class='nav nav-tabs' data-tabs='tabs'>
											@TABLI
										</ul>
										<div class='tab-content'>
											@TABCONTENT
										</div>
									</div>";
				}
				$tab_li .= "<li role='presentation' class='$tab_active'>
								<a data-toggle='tab' href='#$form_view_label_name'>$form_view_heading</a>
							</li>";
				$tab_content .= "<div class='tab-pane fade $content_active' id='$form_view_label_name' style='padding:8px;'>
									<h4 class='prime_color'>$form_view_heading</h4>
									$ul_li
								</div>";
			}else
			if($form_view_type === 3){
				$view_content .= "<div id='$form_view_label_name' style='font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px;padding:8px;margin-bottom:10px;'>
									<h4 class='prime_color'>$form_view_heading</h4>
									$ul_li
								</div>";
			}
		}
		$ul_li = "";
		$view_content  = str_replace("@TABLI",$tab_li,$view_content);
		$view_content  = str_replace("@TABCONTENT",$tab_content,$view_content);
		return json_encode(array('success' => TRUE,'view_content' => $view_content,'id_array'=>$id_array,'view_input_count'=>$view_input_count));
	}
	
	public function update_table_viewui($prime_module_id){
		$logged_id    = $this->session->userdata('logged_id');
		$table_data   = array( 'prime_module_id' => $prime_module_id);
		$table_data   = json_encode($table_data);
		$input_info   = $this->db->query("CALL sp_form_setting_crud ('TAB_VIEW', '$table_data',$logged_id)");
		$input_result = $input_info->result();
		$input_info->next_result();
		$input_count = 0;
		$input_th    = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p>";
		$input_td    = "";
		$i = 1;
		$tr_line     = '';
		foreach($input_result as $setting){
			$input_count++;
			$prime_form_id   = $setting->prime_form_id;
			$view_name       = ucwords($setting->view_name);
			$short_name      = $setting->short_name;
			$table_show      = $setting->table_show;
			$th_id           = "th_".$prime_form_id;
			$input_th       .=  "<th class='ui-state-default inner_th' id='$th_id'>
								$view_name
							</th>";	
			$input_td  .= "<td style='border-right:1px solid #CCCCCC;'></td>";	
			if((int)$i === 8)
			{
				$tr_line .="<tr class='sortable default_table'>$input_th </tr><tr>$input_td</tr>";
				$i = 0;
				$input_th = '';
				$input_td = '';
			}
			$i++;
		}
		if($input_th){
			$tr_line .="<tr  class='sortable default_table' > $input_th </tr><tr>$input_td</tr>";
		}
		$ul_li = "<table class='table table-hover table-striped' id='table_sortable'>
					<tbody>
						$tr_line
					</tbody>
				</table>";
		return json_encode(array('success' => TRUE,'table_content' => $ul_li));
	}

	public function update_monthly_input_viewui($prime_module_id){
		$logged_id    = $this->session->userdata('logged_id');
		$table_data   = array( 'prime_module_id' => $prime_module_id,'earn_month_check' => '1','deduction_month_check' => '1');
		$table_data   = json_encode($table_data);
		$input_info   = $this->db->query("CALL sp_form_setting_crud ('MONTH_VIEW', '$table_data',$logged_id)");
		$input_result = $input_info->result();
		$input_info->next_result();

		$input_count = 0;
		$input_th    = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p>";
		$input_td    = "";
		$i = 1;
		$tr_line     = '';
		foreach($input_result as $setting){
			$input_count++;
			$prime_form_id   = $setting->prime_form_id;
			$view_name       = ucwords($setting->view_name);
			$table_show      = $setting->table_show;
			$th_id           = "th_".$prime_form_id;
			$input_th .=  "<th class='ui-state-default inner_th' id='$th_id'>
								$view_name
							</th>";
			$input_td  .= "<td style='border-right:1px solid #CCCCCC;'></td>";
			if((int)$i === 8)
			{
				$tr_line .="<tr class='sortable monthly_input'>$input_th </tr><tr>$input_td</tr>";
				$i = 0;
				$input_th = '';
				$input_td = '';
			}
			$i++;
		}
		if($input_th){
			$tr_line .="<tr  class='sortable monthly_input' > $input_th </tr><tr>$input_td</tr>";
		}
		$ul_li    = "<table class='table table-hover table-striped' id='monthly_sortable'>
						$tr_line
					</table>";
		return json_encode(array('success' => TRUE,'table_content' => $ul_li));
	}
	
	public function update_payroll_viewui($prime_module_id){
		$logged_id    = $this->session->userdata('logged_id');
		$table_data   = array( 'prime_module_id' => $prime_module_id,'earn_payroll_check' => '1','ded_payroll_check' => '1');
		$table_data   = json_encode($table_data);
		$input_info   = $this->db->query("CALL sp_form_setting_crud ('PAYROLL_VIEW', '$table_data',$logged_id)");
		$input_result = $input_info->result();
		$input_info->next_result();

		$input_count = 0;
		$input_th    = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p>";
		$input_td    = "";
		$i = 1;
		$tr_line     = '';
		foreach($input_result as $setting){
			$input_count++;
			$prime_form_id   = $setting->prime_form_id;
			$view_name       = ucwords($setting->view_name);
			$table_show      = $setting->table_show;
			$th_id           = "th_".$prime_form_id;
			$input_th .=  "<th class='ui-state-default inner_th' id='$th_id'>
								$view_name
							</th>";
			$input_td .= "<td style='border-right:1px solid #CCCCCC;'></td>";
			if((int)$i === 8)
			{
				$tr_line .="<tr class='sortable payroll_table'>$input_th </tr><tr>$input_td</tr>";
				$i = 0;
				$input_th = '';
				$input_td = '';
			}
			$i++;
		}
		if($input_th){
			$tr_line .="<tr  class='sortable payroll_table' > $input_th </tr><tr>$input_td</tr>";
		}
		$ul_li    = "<table class='table table-hover table-striped' id='payroll_sortable'>
						$tr_line
					</table>";
		return json_encode(array('success' => TRUE,'table_content' => $ul_li));
	}
	/* ==============================================================*/
	/* ==================== FORM OPEARTION - END ====================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* =================== VIEW OPEARTION - START ===================*/
	/* ==============================================================*/
	//FORM VIEW SAVE OPEARTION
	public function add_ui(){
		$logged_id            = $this->session->userdata('logged_id');
		$prime_form_view_id   = $this->input->post('prime_form_view_id');
		$form_view_show       = $this->input->post('form_view_show');
		$prime_view_module_id = trim($this->input->post('prime_view_module_id'));
		$form_view_label_name = strtolower(str_replace(" ","_",trim($this->input->post('form_view_label_name'))));
		$show_value = 0;
		if($form_view_show === "on"){
			$show_value = 1;
		}
		$table_data = array(
			'prime_form_view_id'   => $this->input->post('prime_form_view_id'),
			'prime_view_module_id' => $prime_view_module_id,
			'form_view_type'       => $this->input->post('form_view_type'),
			'form_view_type_mode'  => $this->input->post('form_view_type_mode'),
			'form_view_label_name' => $form_view_label_name,
			'form_view_heading'    => $this->input->post('form_view_heading'),
			'form_view_for'        => ltrim(implode(",",$this->input->post('form_view_for[]')),","),
			'form_view_show'       => $show_value,
		);
		$table_data = json_encode($table_data);
		if((int)$prime_form_view_id === 0){
			$count_info   = $this->db->query("CALL sp_form_view_setting_crud ('SORT_COUNT', '$table_data','$logged_id')");
			$count_result = $count_info->result();
			$count_info->next_result();
			$table_data = json_decode($table_data,true);
			$field_sort = (int)$count_result[0]->sort_count + 1;
			$table_data['form_view_sort'] = $field_sort;
			$table_data = json_encode($table_data);
		}
		
		$table_data = json_encode($table_data);
		
		$can_process  = true;
		if((int)$prime_form_view_id === 0){
			$viewexist_query  = 'SELECT count(*) as exist_rslt FROM `cw_form_view_setting` where prime_view_module_id = "'.$prime_view_module_id.'" and form_view_label_name = "'.$form_view_label_name.'"';
			$viewexist_info   = $this->db->query("CALL sp_a_run ('SELECT','$viewexist_query')");
			$viewexist_result = $viewexist_info->result();
			$viewexist_info->next_result();
			if((int)$viewexist_result[0]->exist_rslt !== 0){
				$can_process  = false;
			}
		}
		
		if($can_process){
			$info   = $this->db->query("CALL sp_form_view_setting_crud ('SAVE', '$table_data','$logged_id')");
			$result = $info->result();
			$info->next_result();
			
			$view_info   = $this->db->query("CALL sp_form_view_setting_crud ('VIEW', '$table_data',$logged_id)");
			$view_setting = $view_info->result();
			$view_info->next_result();
			$li_list = "";
			$form_view_type_array =  array(""=>"---- Form view type ----",1=>"Block",2=>"Tab");
			$count = 0;
			foreach($view_setting as $setting){
				$count++;
				$prime_form_view_id   = $setting->prime_form_view_id;
				$prime_view_module_id = $setting->prime_view_module_id;
				$form_view_type       = $setting->form_view_type;
				$form_view_label_name = $setting->form_view_label_name;
				$form_view_heading    = $setting->form_view_heading;
				$form_view_sort       = $setting->form_view_sort;
				$form_view_show       = $setting->form_view_show;
				$form_view_for        = $setting->form_view_for;
				
				$form_view_type = $form_view_type_array[$form_view_type];
				$li_id = "li_".$prime_form_view_id;
				$a_id  = "a_".$prime_form_view_id."_$count";
				$show_icon = "<i class='fa fa-eye-slash' aria-hidden='true'></i>";
				if((int)$form_view_show === 1){
					$show_icon = "<i class='fa fa-eye' aria-hidden='true'></i>";
				}
				$li_list .= "<li class='ui-state-default' id='$li_id'>
						<table style='width:100%;'>
							<tr>
								<td style='font-weight:bold'>
									<label>$form_view_heading</label><br/>
									<span style='font-size:13px;font-weight:normal;color:#999999;'> $show_icon $form_view_type </span>
								</td>
								<td style='text-align:right;'>
									<a id='$a_id' class='prime_color' onclick=get_view_info('$prime_form_view_id','$a_id');><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i></a>
								</td>
							</tr>
						</table>
					</li>";
				/* UDY ONLY FOR FORM WITH TABLE DB CREATION - START*/
				$form_view_type = (int)$this->input->post('form_view_type');
				if($form_view_type === 3){
					$prime_view_module_id = trim($this->input->post('prime_view_module_id'));
					$row_table_name       = strtolower(str_replace(" ","_",trim($this->input->post('form_view_label_name'))));
					$custrow_table_name      = strtolower(str_replace(" ","_",trim($this->input->post('form_view_label_name'))));
					$prime_id              = "prime_".$prime_view_module_id."_".$row_table_name."_id";
					$module_id             = "prime_".$prime_view_module_id."_id";
					$row_table_name        = $this->db->dbprefix($prime_view_module_id."_".$row_table_name);
					$custom_row_table_name = "cw_custom_".$prime_view_module_id."_".$custrow_table_name;
					$prime_line            = "$prime_id int(11) NOT NULL AUTO_INCREMENT,$module_id INT(11) NULL DEFAULT '0',trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$prime_id`)";
					$prime_table_query = "CREATE TABLE IF NOT EXISTS $row_table_name($prime_line)";
					$this->db->query($prime_table_query);
					if($prime_view_module_id === "employees"){//custom rowset table creations
						$custom_prime_id      = "prime_custom_".$prime_view_module_id."_".$custrow_table_name."_id";
						$custom_module_id     = "prime_custom_".$prime_view_module_id."_id";
						$custom_prime_line    = "$custom_prime_id int(11) NOT NULL AUTO_INCREMENT,$custom_module_id INT(11) NULL DEFAULT '0',trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$custom_prime_id`)";
						$prime_custom_table_query = "CREATE TABLE IF NOT EXISTS $custom_row_table_name($custom_prime_line)";
						$this->db->query($prime_custom_table_query);
					}
				}
				/* UDY ONLY FOR FORM WITH TABLE DB CREATION  - START*/
			}
			$view_data = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p><ul id='view_sortable' class='sortable'>$li_list</ul>";
			
			echo json_encode(array('success' => TRUE, 'view_setting' => $view_data, 'msg' => "View setting successfully added"));
		}else{
			echo json_encode(array('success' => false, 'msg' => "View already exist for this module"));
		}
	}
	// VIEW SORT ORDER UPDATE
	public function update_view_sortorder(){
		$view_idsInOrder      = $this->input->post('view_idsInOrder');
		$prime_view_module_id = $this->input->post('prime_view_module_id');
		$logged_id          = $this->session->userdata('logged_id');
		$sort_order = 0;
		foreach($view_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_form_view_id = $order[1];
				$table_data = array(
					'prime_form_view_id'   => $prime_form_view_id,
					'prime_view_module_id' => $prime_view_module_id,
					'form_view_sort'       => $sort_order,
				);
				$table_data = json_encode($table_data);
				$info   = $this->db->query("CALL sp_form_view_setting_crud ('UPD_SORT', '$table_data','$logged_id')");
				$result = $info->result();
				$info->next_result();
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	//VIEW FORM EDIT OPERATION
	public function get_view_info(){
		$table_data = array(
			'prime_view_module_id' => $this->input->post('prime_view_module_id'),
			'prime_form_view_id' => $this->input->post('prime_form_view_id'),
		);
		$table_data = json_encode($table_data);
		$info   = $this->db->query("CALL sp_form_view_setting_crud ('EDIT', '$table_data',null)");
		$result = $info->result();
		$info->next_result();
		echo json_encode(array('success' => TRUE, 'view_info' => $result[0]));
	}
	/* ==============================================================*/
	/* ==================== VIEW OPEARTION - END ====================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ==================== FORMULA TBALE - START ===================*/
	/* ==============================================================*/
	public function condition_formula(){
		$logged_id                = $this->session->userdata('logged_id');
		$cond_module_id        = $this->input->post('cond_module_id');
		$is_drop_down          = $this->input->post('is_drop_down');
		$prime_cond_id         = (int)$this->input->post('prime_cond_id');
		$condition_label_name  = $this->input->post('condition_label_name');
		$is_drop_down_value = 0;
		if($is_drop_down === "on"){
			$is_drop_down_value = 1;
		}
		
		$table_data = array(
			'prime_cond_id'         => $prime_cond_id,
			'cond_module_id'        => $cond_module_id,
			'condition_label_name'  => $condition_label_name,
			'condition_type'        => $this->input->post('condition_type'),
			'condition_for'         => ltrim(implode(",",$this->input->post('condition_for[]')),","),
			'condition_check_form'  => ltrim(implode(",",$this->input->post('condition_check_form[]')),","),
			'condition_bind_to'     => ltrim(implode(",",$this->input->post('condition_bind_to[]')),","),
			'condition_table'       => ltrim(implode(",",$this->input->post('condition_table[]')),","),
			'is_drop_down'          => $is_drop_down_value,
			'cond_drop_down'        => $this->input->post('cond_drop_down'),
		);
		$table_data = json_encode($table_data);
		$can_process  = true;
		if((int)$prime_cond_id === 0){
			$exist_query  = 'SELECT count(*) as exist_rslt FROM `cw_form_condition_formula` where cond_module_id = "'.$cond_module_id.'" and condition_label_name = "'.$condition_label_name.'"';
			$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
			$exist_result = $exist_info->result();
			$exist_info->next_result();
			if((int)$exist_result[0]->exist_rslt !== 0){
				$can_process  = false;
			}
		}
		
		if($can_process){
			$info   = $this->db->query("CALL sp_cond_crud ('SAVE', '$table_data','$logged_id')");
			$result = $info->result();
			$info->next_result();
			if((int)$prime_cond_id === 0){
				$prime_cond_id = $result[0]->ins_id;
			}
			
			$from_remove = ltrim(implode(",",$this->input->post('condition_check_form[]')),",");
			$from_remove = '"'.str_replace(",",'","',$from_remove).'"';
			$from_query  = 'UPDATE cw_form_for_input SET trans_status = 0 , trans_deleted_by = "'.$logged_id.'",trans_deleted_date = DATE_FORMAT(NOW(), "%Y-%m-%d %H:%i:%S") WHERE input_for_cond_id = "'.$prime_cond_id.'" and input_for_cond_module_id = "'.$cond_module_id.'" and line_input_for NOT IN('.$from_remove.')';
			$this->db->query("CALL sp_a_run ('RUN','$from_query')");

			$to_remove = ltrim(implode(",",$this->input->post('condition_bind_to[]')),",");
			$to_remove = '"'.str_replace(",",'","',$to_remove).'"';
			$to_query  = 'UPDATE cw_form_bind_input SET trans_status = 0 , trans_deleted_by = "'.$logged_id.'",trans_deleted_date = DATE_FORMAT(NOW(), "%Y-%m-%d %H:%i:%S") WHERE input_cond_id = "'.$prime_cond_id.'" and input_cond_module_id = "'.$cond_module_id.'" and line_input_bind_to NOT IN('.$to_remove.')';
			$this->db->query("CALL sp_a_run ('RUN','$to_query')");
			
			$cond_view = $this->condition_formula_view($cond_module_id);
			$cond_content_rslt = json_decode($cond_view);
			$cond_content      = $cond_content_rslt->cond_content;
			$this->sort_formula($cond_module_id);
			echo json_encode(array('success' => TRUE,'cond_content' => $cond_content,'msg'=>"Condition & Formula added successfully"));
		}else{
			$this->sort_formula($cond_module_id);
			echo json_encode(array('success' =>false,'msg'=>"Condition & Formula name already exist"));
		}
	}
	
	//Manage conditions and formula order -- 23APR2019
	public function sort_formula($cond_module_id){
		$bind_sort_qry  = 'select condition_bind_to,condition_for,condition_check_form,tab_sort from `cw_form_condition_formula` where trans_status = 1 and cond_module_id = "'.$cond_module_id.'"';
		$bind_sort_info   = $this->db->query("CALL sp_a_run ('SELECT','$bind_sort_qry')");
		$bind_sort_result = $bind_sort_info->result();
		$bind_sort_info->next_result();	
		$count = 0;
		$sort_array = array();		
		foreach($bind_sort_result as $bind_list){
			$count++;
			$output_column         = $bind_list->condition_bind_to;
			$condition_for         = $bind_list->condition_for;
			$condition_check_form  = $bind_list->condition_check_form;
			$tab_sort              = $bind_list->tab_sort;
			$tab_sort_query   = 'select condition_bind_to,condition_for,condition_check_form,(CASE condition_bind_to WHEN FIND_IN_SET("'.$output_column.'", condition_check_form) THEN 1 ELSE 2 END) AS rank from `cw_form_condition_formula` where trans_status = 1 and cond_module_id = "'.$cond_module_id.'" ORDER BY rank';			
			$tab_sort_info   = $this->db->query("CALL sp_a_run ('SELECT','$tab_sort_query')");
			$tab_sort_result = $tab_sort_info->result();
			$tab_sort_info->next_result();
			$sort_array[$condition_for][$output_column] = $count;
		}		
		foreach($sort_array as $sort_for => $sort_list){
			foreach($sort_list as $sort_out => $sort){
				$upd_sort = 'UPDATE `cw_form_condition_formula` SET tab_sort = "'.$sort.'" where trans_status = 1 and cond_module_id = "'.$cond_module_id.'" and condition_bind_to = "'.$sort_out.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
			}
		}
		//$this->import_formula_reorder($cond_module_id,$output_column,$count);
	}
	
	/*public function import_formula_reorder($cond_module_id,$output_column,$tab_sort){
		
		$find_sort_maxorder='select IFNULL(MAX(tab_sort), 0) as max_order, IFNULL(MIN(tab_sort), 0) as min_order from cw_form_condition_formula where trans_status = 1 and  cond_module_id = "'.$cond_module_id.'" and FIND_IN_SET("'.$output_column.'", condition_check_form) order by tab_sort desc';
		$max_order_data  = $this->db->query("CALL sp_a_run ('SELECT','$find_sort_maxorder')");
		$max_order_rslt  = $max_order_data->result();
		$max_order_data->next_result();
		$max_order = $max_order_rslt[0]->max_order;
		$min_order = $max_order_rslt[0]->min_order;
		
		$bind_sort_qry  = 'select condition_bind_to,condition_for,condition_check_form,tab_sort from `cw_form_condition_formula` where trans_status = 1 and cond_module_id = "'.$cond_module_id.'" and tab_sort  between  "'.$min_order.'" and "'.$max_order.'" order by tab_sort asc';
		$bind_sort_info   = $this->db->query("CALL sp_a_run ('SELECT','$bind_sort_qry')");
		$bind_sort_result = $bind_sort_info->result();
		$bind_sort_info->next_result();
		$sort_order	 = ++$tab_sort;
		foreach($bind_sort_result as $result){
			$condition_bind_to = $result->condition_bind_to;
			$tab_sort          = $result->tab_sort;
			$upd_sort = 'UPDATE cw_form_condition_formula SET tab_sort = "'.$sort_order.'" where cond_module_id = "'.$cond_module_id.'" and condition_bind_to="'.$condition_bind_to.'" and trans_status = 1';				
			$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
			$sort_order++;	
		}
		return true;
	}*/

	public function get_cond_info(){
		$logged_id         = $this->session->userdata('logged_id');
		$table_data = array(
			'prime_cond_id'         => (int)$this->input->post('prime_cond_id'),
			'cond_module_id'        => $this->input->post('cond_module_id'),
		);
		$table_data = json_encode($table_data);
		$info   = $this->db->query("CALL sp_cond_crud ('EDIT', '$table_data','$logged_id')");
		$result = $info->result();
		$info->next_result();
		echo json_encode(array('success' => TRUE,'cond_info' => $result[0]));
	}
	
	public function remove_cond(){
		$logged_id         = $this->session->userdata('logged_id');
		$cond_module_id = $this->input->post('cond_module_id');
		$table_data = array(
			'prime_cond_id'         => (int)$this->input->post('prime_cond_id'),
			'cond_module_id'        => $this->input->post('cond_module_id'),
		);
		$table_data = json_encode($table_data);
		$info   = $this->db->query("CALL sp_cond_crud ('REMOVE', '$table_data','$logged_id')");
		
		$cond_view         = $this->condition_formula_view($cond_module_id);
		$cond_content_rslt = json_decode($cond_view);
		$cond_content      = $cond_content_rslt->cond_content;
		echo json_encode(array('success' => TRUE,'cond_content' => $cond_content));
	}
	public function condition_formula_view($prime_module_id){
		$logged_id          = $this->session->userdata('logged_id');
		$cond_table_data = array( 'cond_module_id' => $prime_module_id);
		$cond_table_data = json_encode($cond_table_data);
		$cond_info = $this->db->query("CALL sp_cond_crud ('VIEW', '$cond_table_data',$logged_id)");
		$cond_rslt = $cond_info->result();
		$cond_info->next_result();
		$tr_line = "";
		foreach($cond_rslt as $rslt){
			$prime_cond_id        = $rslt->prime_cond_id;
			$cond_module_id       = $rslt->cond_module_id;
			$condition_label_name = $rslt->condition_label_name;
			$tr_line .= "<tr>
							<td>$condition_label_name</td>
							<td><a class='btn btn-xs btn-edit' onclick=get_cond_info('$prime_cond_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td><a class='btn btn-xs btn-danger' onclick=remove_cond('$prime_cond_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$cond_content = "<table class='table table-bordered table-stripted'>
							<tr class='inline_head'>
								<th>Condition / Formula Name</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		return json_encode(array('success' => TRUE,'cond_content' => $cond_content));
	}
	public function get_add_cond_info(){
		$logged_id         = $this->session->userdata('logged_id');
		$table_data = array(
			'prime_cond_id'         => (int)$this->input->post('add_cond_content'),
			'cond_module_id'        => $this->input->post('add_cond_module_id'),
		);
		$table_data = json_encode($table_data);
		$info   = $this->db->query("CALL sp_cond_crud ('EDIT', '$table_data','$logged_id')");
		$result = $info->result();
		$info->next_result();
		
		$prime_cond_id        = $result[0]->prime_cond_id;
		$cond_module_id       = $result[0]->cond_module_id;
		$condition_label_name = $result[0]->condition_label_name;
		$condition_type       = (int)$result[0]->condition_type;
		$condition_for        = explode(",",$result[0]->condition_for);
		$condition_check_form = explode(",",$result[0]->condition_check_form);
		$condition_bind_to    = explode(",",$result[0]->condition_bind_to);
		$condition_table      = explode(",",$result[0]->condition_table);
		$is_drop_down         = (int)$result[0]->is_drop_down;
		$cond_drop_down       = $result[0]->cond_drop_down;
		
		$line_prime_cond_id  = form_input(array( 'name' =>'line_prime_cond_id','id' =>'line_prime_cond_id', 'class' => 'form-control input-sm','value' =>$prime_cond_id,'type'=>'Hidden'));
		$line_cond_module_id = form_input(array( 'name' =>'line_cond_module_id','id' =>'line_cond_module_id', 'class' => 'form-control input-sm','value' =>$cond_module_id,'type'=>'Hidden'));
		$line_cond_type = form_input(array( 'name' =>'line_cond_type','id' =>'line_cond_type', 'class' => 'form-control input-sm','value' =>$condition_type,'type'=>'Hidden'));
				
		$final_content     = "";
		//BIND FORM TABLE
		//echo "BSK $condition_type";die;
		//print_r($condition_table); 
		if($condition_type === 1){
			$table_list[""] = "---- Select Table ----";
			foreach($condition_table as $table_value){
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$table_list[$table_value] = $table_name;
			}
			$prime_in  = '"'.str_replace(",",'","', $result[0]->condition_table);
			$custom_in = str_replace(",",'_cf","', $result[0]->condition_table).'_cf"';
			$table_in  = $prime_in.'","'.$custom_in;
			$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`= "'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_in.') AND COLUMN_NAME NOT LIKE "%trans%"';
			$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
			$column_result = $column_info->result();
			$column_info->next_result();
			$column_list[""] = "---- Select Column ----";
			foreach($column_result as $column){
				$table_value  = $column->TABLE_NAME;
				$column_value = $column->COLUMN_NAME;
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$column_name  = ucwords(str_replace("_"," ",$column_value));
				$column_list[$table_value.".".$column_value] = $table_name . " - ". $column_name;
			}
			
			$condition_tab_query  = 'SELECT * FROM cw_form_table_cond_for  WHERE table_cond_id = "'.$prime_cond_id.'" AND table_cond_module_id = "'.$cond_module_id.'" order by abs(line_sort)';
			$condition_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$condition_tab_query')");
			$condition_tab_result = $condition_tab_info->result();
			$condition_tab_info->next_result();
			$join_array = array(""=>"--- Select join type ---","inner" => "inner","left" => "left","right" => "right");			
			
			$table_content  = "";
			$table_tr_line  = "";
			$table_count    = 0;
			$condition_table_count = count($condition_table) - 1; //round(count($condition_table)/2);
			for($i=1;$i<= $condition_table_count;$i++){
				$prime_table_cond_for_id = 0;
				$line_prime_table        = "";
				$line_prime_col          = "";
				$line_join_type          = "";
				$line_join_table         = "";
				$line_join_col           = "";
				if($condition_tab_result){
					$prime_table_cond_for_id = $condition_tab_result[$table_count]->prime_table_cond_for_id;
					$line_prime_table        = $condition_tab_result[$table_count]->line_prime_table;
					$line_prime_col          = $condition_tab_result[$table_count]->line_prime_col;
					$line_join_type          = $condition_tab_result[$table_count]->line_join_type;
					$line_join_table         = $condition_tab_result[$table_count]->line_join_table;
					$line_join_col           = $condition_tab_result[$table_count]->line_join_col;
				}
				
				$table_cond_for_id = form_input(array( 'name' =>"prime_table_cond_for_id[]",'class' => 'form-control input-sm','value' =>$prime_table_cond_for_id,'type'=>'Hidden'));
				$prime_table_data  = form_dropdown(array('name' =>"line_prime_table[]",'class' => 'form-control input-sm'), $table_list,$line_prime_table);
				$prime_col_data    = form_dropdown(array('name' =>"line_prime_col[]",'class' => 'form-control input-sm'),$column_list,$line_prime_col);
				$join_data         = form_dropdown(array('name' =>"line_join_type[]",'class' => 'form-control input-sm'),$join_array,$line_join_type);
				$join_table_data   = form_dropdown(array('name' =>"line_join_table[]",'class' => 'form-control input-sm'), $table_list,$line_join_table);
				$join_col_data     = form_dropdown(array('name' =>"line_join_col[]",'class' => 'form-control input-sm'),$column_list,$line_join_col);
				$table_tr_line .= "<tr>
										<td>$table_cond_for_id $prime_table_data</td>
										<td>$prime_col_data</td>
										<td>$join_data</td>
										<td>$join_table_data</td>
										<td>$join_col_data</td>
									</tr>";
				$table_count++;
			}
			$table_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th>Primary table</th>
										<th>Primary column</th>
										<th>Join type</th>
										<th>Join table</th>
										<th>Join primary column</th>
									</tr>
									$table_tr_line
								</table>";
			$for_input_content = "";
			$for_input_tr_line = "";
			foreach($condition_check_form as $check_form){
				$condition_for_input_query  = 'SELECT * FROM cw_form_for_input  WHERE input_for_cond_id = "'.$prime_cond_id.'" AND input_for_cond_module_id = "'.$cond_module_id.'" AND line_input_for = "'.$check_form.'"';
				$condition_for_input_info   = $this->db->query("CALL sp_a_run ('SELECT','$condition_for_input_query')");
				$condition_for_input_result = $condition_for_input_info->result();
				$condition_for_input_info->next_result();
				
				$prime_for_input_id   = 0;
				$line_input_for_table = "";
				$line_input_for_col   = "";
				if($condition_for_input_result){
					$prime_for_input_id   = $condition_for_input_result[0]->prime_for_input_id;
					$line_input_for_table = $condition_for_input_result[0]->line_input_for_table;
					$line_input_for_col   = $condition_for_input_result[0]->line_input_for_col;
				}
				$for_col_name   = ucwords(str_replace("_"," ",$check_form));
				$input_for_id   = form_input(array( 'name' =>"prime_for_input_id[]",'class' => 'form-control input-sm','value' =>$prime_for_input_id,'type'=>'Hidden'));
				$line_for_col   = form_input(array( 'name' =>"line_input_for[]",'class' => 'form-control input-sm','value' =>$check_form,'type'=>'Hidden'));
				$for_table_data = form_dropdown(array('name' =>"line_input_for_table[]",'class' => 'form-control input-sm'), $table_list,$line_input_for_table);
				$for_col_data   = form_dropdown(array('name' =>"line_input_for_col[]",'class' => 'form-control input-sm'),$column_list,$line_input_for_col);
				$for_input_tr_line .= "<tr>
										<td>$for_col_name $line_for_col $input_for_id</td>
										<td>$for_table_data</td>
										<td>$for_col_data</td>
									</tr>";
			}
			$for_input_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th>From input</th>
										<th>From table</th>
										<th>From table column</th>
									</tr>
									$for_input_tr_line
								</table>";
			
			$input_content = "";
			$input_tr_line = "";
			foreach($condition_bind_to as $bind_col){
				$condition_input_query  = 'SELECT * FROM cw_form_bind_input  WHERE input_cond_id = "'.$prime_cond_id.'" AND input_cond_module_id = "'.$cond_module_id.'" AND line_input_bind_to = "'.$bind_col.'"';
				$condition_input_info   = $this->db->query("CALL sp_a_run ('SELECT','$condition_input_query')");
				$condition_input_result = $condition_input_info->result();
				$condition_input_info->next_result();
				$prime_input_cond_for_id = 0;
				$line_input_bind_table   = "";
				$line_input_bind_col     = "";
				if($condition_input_result){
					$prime_input_cond_for_id = $condition_input_result[0]->prime_input_cond_for_id;
					$line_input_bind_table   = $condition_input_result[0]->line_input_bind_table;
					$line_input_bind_col     = $condition_input_result[0]->line_input_bind_col;
				}
				
				$bind_col_name       = ucwords(str_replace("_"," ",$bind_col));
				$input_cond_for_id   = form_input(array( 'name' =>"prime_input_cond_for_id[]",'class' => 'form-control input-sm','value' =>$prime_input_cond_for_id,'type'=>'Hidden'));
				$line_bind_col       = form_input(array( 'name' =>"line_input_bind_to[]",'class' => 'form-control input-sm','value' =>$bind_col,'type'=>'Hidden'));
				$table_data          = form_dropdown(array('name' =>"line_input_bind_table[]",'class' => 'form-control input-sm'), $table_list,$line_input_bind_table);
				$col_data            = form_dropdown(array('name' =>"line_input_bind_col[]",'class' => 'form-control input-sm'),$column_list,$line_input_bind_col);
				$input_tr_line .= "<tr>
										<td>$bind_col_name $line_bind_col $input_cond_for_id $line_cond_type</td>
										<td>$table_data</td>
										<td>$col_data</td>
									</tr>";
			}
			$input_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th>Bind input</th>
										<th>From table</th>
										<th>From table column</th>
									</tr>
									$input_tr_line
								</table>";
			$final_content = "$line_prime_cond_id  $line_cond_module_id 
								<ul class='nav nav-tabs' data-tabs='tabs'>
									<li class='active' role='presentation'>
										<a data-toggle='tab' href='#table_details'>Table details</a>
									</li>
									<li role='presentation'>
										<a data-toggle='tab' href='#for_input_details'>From input details</a>
									</li>
									<li role='presentation'>
										<a data-toggle='tab' href='#bind_input_details'>Bind input details</a>
									</li>
								</ul>
								<div class='tab-content' style='padding:20px 15px;'>
									<div class='tab-pane fade in active' id='table_details'>
										$table_content
									</div>
									<div class='tab-pane fade' id='for_input_details'>
										$for_input_content
									</div>
									<div class='tab-pane fade' id='bind_input_details'>
										$input_content
									</div>
								</div>
								<div style='text-align:right;padding: 20px 15px;padding-top:0px;'>
									<button class='btn btn-primary btn-sm' id='add_cond_submit'>Submit</button>
								</div>";
		}else
		//ONLY CONDITION
		if($condition_type === 2){
			//CONDITION BASED ON DROP DOWN
			$drop_tr_line       = "";
			$drop_input_content = "";
			$con_column_list[""] = "---- Select column ----";
			foreach($condition_check_form as $check_form){
				$check_form_label       = ucwords(str_replace("_"," ",$check_form));
				$con_column_list[$check_form] = $check_form_label;
			}
			$drop_count = 0;
			$drop_line_count = 0;
			if($is_drop_down === 1){
				$drop_down_list = $this->get_drop_down_info($cond_drop_down,$cond_module_id);
				foreach($drop_down_list as $key=>$value){
					if($key){
						$drop_count++;
						$cond_drop_down  = form_dropdown(array('name' =>"cond_drop_down[]",'class' => 'form-control input-sm'),$drop_down_list,$key);
						$drop_in_line = "";
						foreach($condition_bind_to as $bind_col){
							$drop_line_count++;
							$con_column_input     = "con_column_input_".$drop_line_count;
							$line_input_bind_cond = "line_input_bind_col_".$drop_line_count;
							
							$condition_input_query  = 'SELECT * FROM cw_form_bind_input  WHERE input_cond_id = "'.$prime_cond_id.'" AND input_cond_module_id = "'.$cond_module_id.'" AND line_input_bind_to = "'.$bind_col.'"AND line_input_bind_table = "'.$key.'"';
							$condition_input_info   = $this->db->query("CALL sp_a_run ('SELECT','$condition_input_query')");
							$condition_input_result = $condition_input_info->result();
							$condition_input_info->next_result();
							$prime_input_cond_for_id = 0;
							$line_input_bind_table   = "";
							$line_input_bind_col     = "";
							if($condition_input_result){
								$prime_input_cond_for_id = $condition_input_result[0]->prime_input_cond_for_id;
								$line_input_bind_table   = $condition_input_result[0]->line_input_bind_table;
								$line_input_bind_col     = $condition_input_result[0]->line_input_bind_col;
							}
							
							$bind_col_name         = ucwords(str_replace("_"," ",$bind_col));
							$input_cond_for_id     = form_input(array( 'name' =>"prime_input_cond_for_id[]",'class' => 'form-control input-sm','value' =>$prime_input_cond_for_id,'type'=>'Hidden'));
							$line_input_bind_table = form_input(array( 'name' =>"line_input_bind_table[]",'class' => 'form-control input-sm','value' =>$key,'type'=>'Hidden'));
							$line_bind_col         = form_input(array( 'name' =>"line_input_bind_to[]",'class' => 'form-control input-sm','value' =>$bind_col,'type'=>'Hidden'));
							$con_column_input      = form_dropdown(array("onchange = get_id('$drop_line_count') id" =>$con_column_input,' class' => 'form-control input-sm'), $con_column_list);
							$drop_in_line .= "<tr>
												<td style='vertical-align:middle;text-align: center;'>$bind_col_name $input_cond_for_id $line_input_bind_table $line_bind_col </td>
												<td style='vertical-align:middle;'>$con_column_input</td>
												<td>
													<textarea  placeholder='Write Condition' name='line_input_bind_col[]' class='form-control' rows='6' id='$line_input_bind_cond'>$line_input_bind_col</textarea>
												</td>
											</tr>";
						}
						
						$drop_tr_line .= "<tr>
												<td style='vertical-align:middle;background-color: #f9f9f9;'>
													<h4 style='text-align:center;font-size:16px;font-weight:bold;margin:4px;'>$value</h4>
													$cond_drop_down
												</td>
												<td colspan='4' style='padding:8px!important;'>
													<table class='table table-bordered table-stripted'>
														<tr class='inline_head'>
															<th style='width:15%;'>Bind input</th>
															<th style='width:20%;'>Pick columns</th>
															<th>Condition</th>
														</tr>
														$drop_in_line
													</table>
												</td>
											</tr>";
					}
				}
				$drop_input_content = "<table class='table table-bordered table-stripted'>
											<tr class='inline_head'>
												<th style='width:16%;text-align: center;'>Condition drop down value</th>
												<th colspan='4' style='text-align:center;'>Condition Info</th>
											</tr>
											$drop_tr_line
										</table>";
			}else{
				//NORMAL CONDITION
				foreach($condition_bind_to as $bind_col){
					$drop_line_count++;
					$con_column_input     = "con_column_input_".$drop_line_count;
					$line_input_bind_cond = "line_input_bind_col_".$drop_line_count;
					
					$condition_input_query  = 'SELECT * FROM cw_form_bind_input  WHERE input_cond_id = "'.$prime_cond_id.'" AND input_cond_module_id = "'.$cond_module_id.'" AND line_input_bind_to = "'.$bind_col.'"AND line_input_bind_table = "'.$key.'"';
					$condition_input_info   = $this->db->query("CALL sp_a_run ('SELECT','$condition_input_query')");
					$condition_input_result = $condition_input_info->result();
					$condition_input_info->next_result();
					$prime_input_cond_for_id = 0;
					$line_input_bind_table   = "";
					$line_input_bind_col     = "";
					if($condition_input_result){
						$prime_input_cond_for_id = $condition_input_result[0]->prime_input_cond_for_id;
						$line_input_bind_table   = $condition_input_result[0]->line_input_bind_table;
						$line_input_bind_col     = $condition_input_result[0]->line_input_bind_col;
					}
					
					$bind_col_name         = ucwords(str_replace("_"," ",$bind_col));
					$input_cond_for_id     = form_input(array( 'name' =>"prime_input_cond_for_id[]",'class' => 'form-control input-sm','value' =>$prime_input_cond_for_id,'type'=>'Hidden'));
					$line_input_bind_table = form_input(array( 'name' =>"line_input_bind_table[]",'class' => 'form-control input-sm','value' =>$key,'type'=>'Hidden'));
					$line_bind_col         = form_input(array( 'name' =>"line_input_bind_to[]",'class' => 'form-control input-sm','value' =>$bind_col,'type'=>'Hidden'));
					$con_column_input      = form_dropdown(array("onchange = get_id('$drop_line_count') id" =>$con_column_input,' class' => 'form-control input-sm'), $con_column_list);
					$drop_in_line .= "<tr>
										<td style='vertical-align:middle;text-align: center;'>$bind_col_name $input_cond_for_id $line_input_bind_table $line_bind_col </td>
										<td style='vertical-align:middle;'>$con_column_input</td>
										<td>
											<textarea  placeholder='Write Condition' name='line_input_bind_col[]' class='form-control' rows='6' id='$line_input_bind_cond'>$line_input_bind_col</textarea>
										</td>
									</tr>";
				}
				$drop_input_content .= "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th style='width:15%;'>Bind input</th>
										<th style='width:20%;'>Pick columns</th>
										<th>Condition</th>
									</tr>
									$drop_in_line
								</table>";
			}
			$final_content = "$line_prime_cond_id  $line_cond_module_id  $line_cond_type
								<div style='padding:8px;'>
									$drop_input_content
								</div>
								<div style='text-align:right;padding: 20px 15px;padding-top:0px;'>
									<button class='btn btn-primary btn-sm' id='add_cond_submit'>Submit</button>
								</div>";
		}
		echo json_encode(array('success' => TRUE,'load_content' => $final_content));
	}
	public function add_condition_formula(){
		$line_prime_cond_id    = $this->input->post('line_prime_cond_id');
		$line_cond_module_id   = $this->input->post('line_cond_module_id');
		$line_cond_type        = (int)$this->input->post('line_cond_type');
		
		$table_cond_for_id     = $this->input->post('prime_table_cond_for_id[]');
		$line_prime_table      = $this->input->post('line_prime_table[]');
		$line_prime_col        = $this->input->post('line_prime_col[]');
		$line_join_type        = $this->input->post('line_join_type[]');
		$line_join_table       = $this->input->post('line_join_table[]');
		$line_join_col         = $this->input->post('line_join_col[]');
		
		$prime_for_input_id    = $this->input->post('prime_for_input_id[]');
		$line_input_for        = $this->input->post('line_input_for[]');
		$line_input_for_table  = $this->input->post('line_input_for_table[]');
		$line_input_for_col    = $this->input->post('line_input_for_col[]');
		
		$input_cond_for_id     = $this->input->post('prime_input_cond_for_id[]');
		$line_input_bind_to    = $this->input->post('line_input_bind_to[]');
		$line_input_bind_table = $this->input->post('line_input_bind_table[]');
		//$line_input_bind_col   = $this->input->post('line_input_bind_col[]');
		if($line_cond_type === 2){
			$line_input_bind_col  = $this->input->post('line_input_bind_col[]');
			$line_input_bind_col  = str_replace("'","~",$line_input_bind_col);
			$line_input_bind_col  = str_replace('"',"!",$line_input_bind_col);
		}else{
			$line_input_bind_col   = $this->input->post('line_input_bind_col[]');
		}
		$logged_id     = $this->session->userdata('logged_id');
		
		$today_date = date("Y-m-d h:i:s");
		$tab_count  = 0;
		$table_count = count($line_prime_table);
		for($i=1;$i<= $table_count;$i++){
			$table_cond_for_id_val = $table_cond_for_id[$tab_count];
			$line_prime_table_val  = $line_prime_table[$tab_count];
			$line_prime_col_val    = $line_prime_col[$tab_count];
			$line_join_type_val    = $line_join_type[$tab_count];
			$line_join_table_val   = $line_join_table[$tab_count];
			$line_join_col_val     = $line_join_col[$tab_count];
			
			if((int)$table_cond_for_id_val === 0){
				$table_query = 'insert into cw_form_table_cond_for (table_cond_id,table_cond_module_id,line_prime_table,line_prime_col,line_join_type,line_join_table,line_join_col,line_sort,trans_created_by,trans_created_date) value ("'.$line_prime_cond_id.'","'.$line_cond_module_id.'","'.$line_prime_table_val.'","'.$line_prime_col_val.'","'.$line_join_type_val.'","'.$line_join_table_val.'","'.$line_join_col_val.'","'.$i.'","'.$logged_id.'","'.$today_date.'")';
			}else{
				$table_query = 'UPDATE cw_form_table_cond_for SET line_prime_table = "'.$line_prime_table_val.'",line_prime_col = "'.$line_prime_col_val.'",line_join_type = "'.$line_join_type_val.'",line_join_table = "'.$line_join_table_val.'",line_join_col = "'.$line_join_col_val.'",line_sort = "'.$i.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_table_cond_for_id = "'.$table_cond_for_id_val.'"';
			}
			$this->db->query("CALL sp_a_run ('RUN','$table_query')");
			$tab_count++;
		}
		
		$for_in_count = 0;
		$for_input_count = count($line_input_for);
		for($i=1;$i<= $for_input_count;$i++){
			$prime_for_input_id_val   = $prime_for_input_id[$for_in_count];
			$line_input_for_val       = $line_input_for[$for_in_count];
			$line_input_for_table_val = $line_input_for_table[$for_in_count];
			$line_input_for_col_val   = $line_input_for_col[$for_in_count];
			if((int)$prime_for_input_id_val === 0){
				$for_input_query = 'insert into cw_form_for_input (input_for_cond_id,input_for_cond_module_id,line_input_for,line_input_for_table,line_input_for_col,trans_created_by,trans_created_date) value ("'.$line_prime_cond_id.'","'.$line_cond_module_id.'","'.$line_input_for_val.'","'.$line_input_for_table_val.'","'.$line_input_for_col_val.'","'.$logged_id.'","'.$today_date.'")';
			}else{
				$for_input_query = 'UPDATE cw_form_for_input SET line_input_for = "'.$line_input_for_val.'",line_input_for_table = "'.$line_input_for_table_val.'",line_input_for_col = "'.$line_input_for_col_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_for_input_id = "'.$prime_for_input_id_val.'"';
			}
			$this->db->query("CALL sp_a_run ('RUN','$for_input_query')");
			$for_in_count++;
		}
		
		$in_count = 0;
		$input_count = count($line_input_bind_to);
		for($i=1;$i<= $input_count;$i++){
			$input_cond_for_id_val     = $input_cond_for_id[$in_count];
			$line_input_bind_to_val    = $line_input_bind_to[$in_count];
			$line_input_bind_table_val = $line_input_bind_table[$in_count];
			$line_input_bind_col_val   = $line_input_bind_col[$in_count];
			
			$count_query='select IFNULL(MAX(cond_order), 0) as cond_order from cw_form_bind_input where trans_status = 1 order by cond_order desc';
			$is_count_data = $this->db->query("CALL sp_a_run ('SELECT','$count_query')");
			$count_rslt    = $is_count_data->result();
			$is_count_data->next_result();
			$is_count = (int)$count_rslt[0]->cond_order + 1;
			if((int)$input_cond_for_id_val === 0){
				$input_query = 'insert into cw_form_bind_input (input_cond_id,input_cond_module_id,line_input_bind_to,line_input_bind_table,line_input_bind_col,cond_order,trans_created_by,trans_created_date) value ("'.$line_prime_cond_id.'","'.$line_cond_module_id.'","'.$line_input_bind_to_val.'","'.$line_input_bind_table_val.'","'.$line_input_bind_col_val.'","'.$is_count++.'","'.$logged_id.'","'.$today_date.'")';
				$result_info = $this->db->query("CALL sp_a_run ('INSERT','$input_query')");
				$ins_result  = $result_info->result();
				$result_info->next_result();
				$ins_id = $ins_result[0]->ins_id;
			}else{
				$update_query = 'UPDATE cw_form_bind_input SET line_input_bind_to = "'.$line_input_bind_to_val.'",line_input_bind_table = "'.$line_input_bind_table_val.'",line_input_bind_col = "'.$line_input_bind_col_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_input_cond_for_id = "'.$input_cond_for_id_val.'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$update_query')");
				$ins_id = $input_cond_for_id_val;
			}
			$in_count++;
		}
		$this->sort_order_conditions($line_prime_cond_id);
		echo json_encode(array('success' => TRUE,'msg' =>"Mapping successfully Added"));
	}
	public function get_drop_down_info($cond_drop_down,$cond_module_id){
		//$cond_drop_down = ucwords(str_replace("_"," ",$cond_drop_down));
		$get_colums = 'SELECT * FROM `cw_form_setting` where prime_module_id = "'.$cond_module_id.'" and label_name = "'.$cond_drop_down.'"';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		
		$pick_list_type = $column_result[0]->pick_list_type;
		$pick_list      = $column_result[0]->pick_list;
		$pick_table     = $column_result[0]->pick_table;
		$drop_down_list = array();
		if((int)$pick_list_type === 1){
			$table_query = 'SELECT '.$pick_list.' FROM '.$pick_table.' where trans_status = 1';
			$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
			$table_result = $table_info->result();
			$table_info->next_result();
			$pick_list = explode(",",$pick_list);
			$pick_id    = $pick_list[0];
			$pick_value = $pick_list[1];
			$drop_down_list[""] = "---- Select Drop Down Value ----";
			foreach($table_result as $rslt){
				$drop_down_list[$rslt->$pick_id] = $rslt->$pick_value;
			}
		}
		if((int)$pick_list_type === 2){
			$table_query = 'SELECT * FROM '.$pick_table.' where '.$pick_table.'_status = 1';
			$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
			$table_result = $table_info->result();
			$table_info->next_result();
			$pick_id    = $pick_table."_id";
			$pick_value = $pick_table."_value";
			$drop_down_list[""] = "---- Select Drop Down Value ----";
			foreach($table_result as $rslt){
				$drop_down_list[$rslt->$pick_id] = $rslt->$pick_value;
			}
		}
		return $drop_down_list;
	}
	
	//CONDITION SORT
	public function sort_order_conditions($line_prime_cond_id){
		$bind_sort_qry  = 'select line_input_bind_table, line_input_bind_to,line_input_bind_col from cw_form_bind_input inner join cw_form_condition_formula on cw_form_condition_formula.prime_cond_id = cw_form_bind_input.input_cond_id where input_cond_module_id ="employees" and cond_drop_down ="role" and line_input_bind_col !="" and input_cond_id = "'.$line_prime_cond_id.'"';
		$bind_sort_info   = $this->db->query("CALL sp_a_run ('SELECT','$bind_sort_qry')");
		$bind_sort_result = $bind_sort_info->result();
		$bind_sort_info->next_result();
		foreach($bind_sort_result as $bind_sort){
			$out_column        = $bind_sort->line_input_bind_to;
			$input_column      = $bind_sort->line_input_bind_col;
			$role_for          = $bind_sort->line_input_bind_table;
			$input_cond_id     = $bind_sort->input_cond_id;
			
			$preg_match_inputs            = preg_match_all('#\@(.*?)\@#', $input_column,$preg_match_inputsvalue);
			$preg_match_inputsvalue_count = count($preg_match_inputsvalue[1]);
			$input_match_column           = implode('","',$preg_match_inputsvalue[1]);
			$input_match_column           ='"'.$input_match_column.'"';
			
			$qu_find_sortorder ='select IFNULL(MIN(cond_order), 0) as cond_order_min, IFNULL(MAX(cond_order), 0) as cond_order_max from cw_form_bind_input where line_input_bind_table = "'.$role_for.'" and line_input_bind_col like "%'.$out_column.'%" order by cond_order desc';$max_min_data    = $this->db->query("CALL sp_a_run ('SELECT','$qu_find_sortorder')");
			$max_min_rslt    = $max_min_data->result();
			$max_min_data->next_result();
			$outcolum_maxorder = $max_min_rslt[0]->cond_order_max;
			$outcolum_minorder = $max_min_rslt[0]->cond_order_min;
			$min = $outcolum_minorder;
			$qu_find_sortorder_data='select prime_input_cond_for_id,line_input_bind_to,line_input_bind_col,cond_order from cw_form_bind_input where line_input_bind_table = "'.$role_for.'"  and line_input_bind_col like "%'.$out_column.'%" and cond_order between  "'.$outcolum_minorder.'" and "'.$outcolum_maxorder.'" order by cond_order asc';
			$max_min_sort_data = $this->db->query("CALL sp_a_run ('SELECT','$qu_find_sortorder_data')");
			$max_min_sort_rslt = $max_min_sort_data->result();
			$max_min_sort_data->next_result();
			foreach ($max_min_sort_rslt as $result){
				$out_column_db  = $result->line_input_bind_to;
				$cond_order     = $result->cond_order;
				$input_cond_id  = $result->prime_input_cond_for_id;
				if($out_column == $out_column_db){
					$upd_sort = 'UPDATE cw_form_bind_input SET cond_order = "'.$min.'" where line_input_bind_table = "'.$role_for.'" and line_input_bind_to = "'.$out_column.'" and prime_input_cond_for_id = "'.$input_cond_id.'"';
					$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
				}else{
					$upd_sort = 'UPDATE cw_form_bind_input SET cond_order = "'.$outcolum_maxorder.'" where line_input_bind_table = "'.$role_for.'" and line_input_bind_to="'.$out_column_db.'" and cond_order="'.$cond_order.'" and prime_input_cond_for_id="'.$input_cond_id.'"';
					$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
					$outcolum_maxorder--;
				}
				
			}
		}
	}
	
	/* ==============================================================*/
	/* ===================== FORMULA TBALE - END ====================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ==================== DYNAMIC TBALE - START ===================*/
	/* ==============================================================*/
	//SAVE
	public function save_table($prime_module_id,$input_view_type,$input_for,$form_view_label_name){
		if(!$prime_module_id){
			return false;
		}		
		if((int)$input_view_type === 3){
			$table_data = array( 'prime_module_id' => $prime_module_id ,'input_view_type' => '3');
			$table_data = json_encode($table_data);
			$table_name = $prime_module_id."_".$form_view_label_name;
			return $this->save_rowset_table($table_data,$input_for,$table_name);
		}else{
			$table_data = array( 'prime_module_id' => $prime_module_id ,'input_view_type' => '1');
			$table_data = json_encode($table_data);
      /*if((int)$increment_check_val === 1){
				//increment table save
				$prime_module_id = "increment";
				$table_data = array( 'prime_module_id' => $prime_module_i d ,'input_view_type' => '1');
				$table_data = json_encode($table_data);
				$this->save_common_table($table_data);
			}*/
			return $this->save_common_table($table_data);
		}
	}
	
	// SAVE COMMON PRIME AND CUSTOM TABLE
	public function save_common_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;	
		$db_name         = $this->config->item("db_name");
		$info         = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting = $info->result();
		$info->next_result();
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL",11=>"varchar(@LENGTH) NULL DEFAULT NULL",12=>"varchar(@LENGTH) NULL DEFAULT NULL",13=>"datetime NULL DEFAULT NULL",14=>"varchar(@LENGTH) NULL DEFAULT NULL");
		
		$prime_table_name  = $this->db->dbprefix($prime_module_id);
		//$cf_table_name     = $prime_module_id."_cf";
		//$cf_table_name     = $this->db->dbprefix($cf_table_name);
		$query             = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = '$prime_table_name'");
		$result_info       = $query->result();
		$table_count       = $result_info[0]->data_count;
		
		$prime_table_col = "";
		//$cf_table_col    = "";
		if((int)$table_count === 1){
			$prime_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$prime_table_name' and COLUMN_NAME not like '%trans%'");
			$prime_table_col   = $prime_table_query->result_array();
			//$cf_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$cf_table_name' and COLUMN_NAME not like '%trans%'");
			//$cf_table_col   = $cf_table_query->result_array();
		}
		$prime_id          = "prime_".$prime_module_id."_id";
		//$cf_id             = "prime_".$prime_module_id."_cf_id";
		
		$prime_line        = "$prime_id int(11) NOT NULL AUTO_INCREMENT,";
		//$cf_line           = "$cf_id int(11) NOT NULL AUTO_INCREMENT,$prime_id INT(11) NULL DEFAULT '0',";
		$prime_line_alt    = "CHANGE $prime_id $prime_id int(11) NOT NULL AUTO_INCREMENT,";
		//$cf_line_alt       = "CHANGE $cf_id $cf_id int(11) NOT NULL AUTO_INCREMENT,CHANGE $prime_id $prime_id INT(11) NULL DEFAULT '0',";
		//echo "<pre>";
		//print_r($form_setting); die;
		foreach($form_setting as $setting){
			$prime_form_id   = $setting->prime_form_id;
			$prime_module_id = $setting->prime_module_id;
			$field_type      = $setting->field_type;
			$label_name      = $setting->label_name;
			$field_length    = $setting->field_length;
			$field_decimals  = $setting->field_decimals;
			$pick_list       = $setting->pick_list;
			$field_isdefault = $setting->field_isdefault;
			$default_value   = $setting->default_value;
			$mandatory_field = $setting->mandatory_field;
			$unique_field    = $setting->unique_field;
			$field_sort      = $setting->field_sort;
			$field_show      = $setting->field_show;
			$loan_check      = $setting->loan_check;
			
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			if($default_value === ""){
				$default_value = null;
			}
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			
			if((int)$field_isdefault === 1){
				if($prime_table_col){
					$prime_uniq = "";
					if((int)$unique_field === 1){
						$result_key = array_keys($prime_table_col,[ 'col_name' => $label_name,'key_exist'=>'UNI']);
						if(empty($result_key)){
							$prime_uniq = " ADD UNIQUE($label_name),";
						}
					}
					
					if(array_search($label_name, array_column($prime_table_col, 'col_name'))){
						$prime_line_alt .= "CHANGE $label_name $label_name $data_type, $prime_uniq";
						/*if((int)$loan_check === 1){
							$prime_line_alt .= "CHANGE $label_name"."_total"." $label_name"."_total	decimal(15,2) NULL DEFAULT '0' AFTER $label_name,CHANGE $label_name"."_installments"." $label_name"."_installments decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_total ,CHANGE $label_name"."_instal_count"." $label_name"."_instal_count decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_installments,CHANGE $label_name"."_balance"." $label_name"."_balance decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_instal_count,";
						}*/
					}else{
						$last_col = end($prime_table_col);
						$prime_last_col = $last_col['col_name'];
						$prime_line_alt .= "ADD $label_name $data_type AFTER $prime_last_col, $prime_uniq";
						/*if((int)$loan_check === 1){ // For Create Loan Fields
							$trans_line_alt .= "ADD $label_name"."_total"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name,ADD $label_name"."_installments"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_total,ADD $label_name"."_instal_count"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_installments,ADD $label_name"."_balance"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_instal_count,";
						}*/
					}
				}else{
					$prime_uniq = "";
					if((int)$unique_field === 1){
						$prime_uniq = " UNIQUE($label_name),";
					}
					$prime_line .= "$label_name $data_type, $prime_uniq ";
				}
				//echo "BSK $prime_line_alt <br/>"; 
			}
			/*else{
				if($cf_table_col){
					$cf_uniq = "";
					if((int)$unique_field === 1){
						$result_key = array_keys($prime_table_col,[ 'col_name' => $label_name,'key_exist'=>'UNI']);
						if(empty($result_key)){
							$cf_uniq = " ADD UNIQUE($label_name),";
						}
					}
					
					if(array_search($label_name, array_column($cf_table_col, 'col_name'))){
						$cf_line_alt .= "CHANGE $label_name $label_name $data_type, $cf_uniq";
					}else{
						$last_col    = end($cf_table_col);
						$cf_last_col = $last_col['col_name'];
						$cf_line_alt .= "ADD $label_name $data_type AFTER $cf_last_col, $cf_uniq";
					}
				}else{
					$cf_uniq = "";
					if((int)$unique_field === 1){
						$cf_uniq = " UNIQUE($label_name),";
					}
					$cf_line .= "$label_name $data_type, $cf_uniq ";
				}
			}*/

		}

		$prime_line_alt = rtrim($prime_line_alt,", ");
		$prime_line_alt = rtrim($prime_line_alt,",");

		//echo "BSK $prime_line_alt"; die;
		//$cf_line_alt    = rtrim($cf_line_alt,", ");
		//$cf_line_alt    = rtrim($cf_line_alt,",");
		$prime_line .= "trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME NULL DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$prime_id`)";
		
		//$cf_line .= "trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME NULL DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$cf_id`)";
		//CREATE AND ALTER TABLE
		if((int)$table_count === 0){
			$prime_table_query = "CREATE TABLE IF NOT EXISTS $prime_table_name($prime_line)";
			$this->db->query($prime_table_query);
			/*if($prime_module_id !== 'employees'){
				$cf_table_query = "CREATE TABLE IF NOT EXISTS $cf_table_name($cf_line)";
				$this->db->query($cf_table_query);
			}*/			
			$this->update_picklist($prime_module_id);
			if($prime_module_id === "candidate_tracker"){
				$this->save_custom_log_table($table_data);
			}
			return true;
		}else{
			$prime_table_query_alt  = "ALTER TABLE $prime_table_name $prime_line_alt";
			$this->db->query($prime_table_query_alt);
			/*if($prime_module_id !== 'employees'){
				$cf_table_query_alt  = "ALTER TABLE $cf_table_name $cf_line_alt";
				$this->db->query($cf_table_query_alt);
			}*/
			$this->update_picklist($prime_module_id);
			//$this->save_custom_table($table_data);
			if($prime_module_id === "candidate_tracker"){
				$this->save_custom_log_table($table_data);
			}
			//$this->save_custom_log_table($table_data);
			return true;
		}
	}
	
	// SAVE ROWSET TABLE
	public function save_rowset_table($table_data,$view_input_for,$table_name){
		$info         = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting = $info->result();
		$info->next_result();
		$db_name         = $this->config->item("db_name");
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL");
		
		$row_table_name  = $this->db->dbprefix($table_name);
		$query           = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = '$row_table_name'");
		$result_info     = $query->result();
		$table_count     = $result_info[0]->data_count;
		
		$row_table_col = "";
		if((int)$table_count === 1){
			$row_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$row_table_name' and COLUMN_NAME not like '%trans%'");
			$row_table_col   = $row_table_query->result_array();
		}
		
		$rowset_line     = "";
		$rowset_line_alt = "";
		foreach($form_setting as $setting){
			$prime_form_id   = $setting->prime_form_id;
			$prime_module_id = $setting->prime_module_id;
			$input_for       = (int)$setting->input_for;
			$field_type      = $setting->field_type;
			$label_name      = $setting->label_name;
			$field_length    = $setting->field_length;
			$field_decimals  = $setting->field_decimals;
			$pick_list       = $setting->pick_list;
			$field_isdefault = $setting->field_isdefault;
			$default_value   = $setting->default_value;
			$mandatory_field = $setting->mandatory_field;
			$unique_field    = $setting->unique_field;
			$field_sort      = $setting->field_sort;
			$field_show      = $setting->field_show;
			if((int)$view_input_for === (int)$input_for){
				if((int)$field_type === 2){
					$field_length = $field_decimals;
				}
				if(!$field_length){
					$field_length = 100;
				}
				if($default_value === ""){
					$default_value = null;
				}
				$data_type  = $field_type_array[$field_type];
				$data_type  = str_replace("@LENGTH",$field_length,$data_type);
				$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
				
				if($row_table_col){
					$prime_uniq = "";
					if((int)$unique_field === 1){
						$result_key = array_keys($row_table_col,[ 'col_name' => $label_name,'key_exist'=>'UNI']);
						if(empty($result_key)){
							$prime_uniq = " ADD UNIQUE($label_name),";
						}
					}
					
					if(array_search($label_name, array_column($row_table_col, 'col_name'))){
						$rowset_line_alt .= "CHANGE $label_name $label_name $data_type, $prime_uniq";
					}else{
						$last_col = end($row_table_col);
						$prime_last_col = $last_col['col_name'];
						$rowset_line_alt .= "ADD $label_name $data_type AFTER $prime_last_col, $prime_uniq";
					}
				}else{
					$prime_uniq = "";
					if((int)$unique_field === 1){
						$prime_uniq = " UNIQUE($label_name),";
					}
					$rowset_line .= "$label_name $data_type, $prime_uniq ";
				}
			}
		}
		$rowset_line      = rtrim($rowset_line,", ");
		$rowset_line      = rtrim($rowset_line,",");
		$rowset_line_alt  = rtrim($rowset_line_alt,", ");
		$rowset_line_alt  = rtrim($rowset_line_alt,",");
		//CREATE AND ALTER TABLE
		if((int)$table_count === 0){
			if($rowset_line){
				$row_table_query = "CREATE TABLE IF NOT EXISTS $row_table_name($rowset_line)";
				$this->db->query($row_table_query);
				$this->update_picklist($prime_module_id);
			}
			$this->save_custom_rowset_table($table_data,$view_input_for,$table_name);
			return true;
		}else{
			$row_table_query_alt  = "ALTER TABLE $row_table_name $rowset_line_alt";
			if($rowset_line_alt){
				$this->db->query($row_table_query_alt);
				$this->update_picklist($prime_module_id);
			}
			$this->save_custom_rowset_table($table_data,$view_input_for,$table_name);
			return true;
		}
	}

	// CUSTOM PICK LIST TABLE
	public function update_picklist($prime_module_id){
		if(!$prime_module_id){
			return false;
		}
		$pick_query      = $this->db->query("SELECT * FROM cw_form_setting where FIND_IN_SET (field_type,'5,7') and pick_list_type = '2' and prime_module_id = '$prime_module_id'");
		$pick_rslt       = $pick_query->result();
		foreach($pick_rslt as $pick){
			$label_name      = $pick->label_name;
			$prime_form_id   = $pick->prime_form_id;
			$pick_list       = $pick->pick_list;
			$pick_table      = $pick->pick_table;
			$pick_list_array = explode(",",$pick_list);
			
			//$count_query = $this->db->query("SELECT count(*) as ct_count FROM cw_form_setting where FIND_IN_SET (field_type,'5,7') and pick_list_type = '2' and pick_table != ''");

			//Changed by Sathish BSK on 06Feb2020
			$count_query = $this->db->query("SELECT MAX(CAST(SUBSTRING(pick_table,8) AS UNSIGNED)) as ct_count FROM cw_form_setting where FIND_IN_SET (field_type,'5,7') and pick_list_type = '2' and pick_table != ''");
			
			$count_rslt  = $count_query->result();
			$ct_count    = $count_rslt[0]->ct_count;
			if($ct_count > 0){
				$ct_count = $ct_count+ 1;
			}else{
				$ct_count = 1;
			}
			
			if($pick_table){
				$table_value  = $pick_table."_value";
				$table_status = $pick_table."_status";
				$check_name   = $this->db->dbprefix("zct_");
				// Update required only for custom tabel
				if(strpos($pick_table, $check_name) !== false) {
					$this->db->query("UPDATE $pick_table SET $table_status = '0'");
					foreach($pick_list_array as $list){
						$exist_query = $this->db->query("SELECT count(*) as exist_count FROM $pick_table where $table_value = '$list'");
						$exist_rslt  = $exist_query->result();
						if((int)$exist_rslt[0]->exist_count === 0){
							$this->db->query("INSERT INTO $pick_table ($table_value) VALUES ('$list')");
						}else{
							$this->db->query("UPDATE $pick_table SET $table_status = '1' WHERE FIND_IN_SET($table_value, '$pick_list')");
						}
					}
				}
			}else{
				$table_name   = "zct_$ct_count";
				$table_name   = $this->db->dbprefix($table_name);
				$table_id     = $table_name."_id";
				$table_value  = $table_name."_value";
				$table_status = $table_name."_status";
				$tabel_col    = "$table_id int(11) NOT NULL AUTO_INCREMENT,$table_value varchar(150) NULL DEFAULT '0',$table_status INT(11) NULL DEFAULT '1', PRIMARY KEY (`$table_id`)";
				$table_query = "CREATE TABLE IF NOT EXISTS $table_name($tabel_col)";
				if($this->db->query($table_query)){
					foreach($pick_list_array as $list){
						$this->db->query("INSERT INTO $table_name ($table_value) VALUES ('$list')");
					}
				}
				$this->db->query("UPDATE cw_form_setting SET pick_table = '$table_name' WHERE prime_form_id = '$prime_form_id'");
			}
		}
	}
	/* ==============================================================*/
	/* ===================== DYNAMIC TBALE - END ====================*/
	/* ==============================================================*/
	
	
	/* ==============================================================*/
	/* =========== TRANSACTION TABLE SAVE DATA - START ==============*/
	/* ==============================================================*/
	//Start date 24-12-2018 Jaffer
	public function save_transactions_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;
		$db_name         = $this->config->item("db_name");
		//only employees table based transaction table
		if($prime_module_id != "employees"){
			return false;
		}
		
		$info            = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting    = $info->result();
		$info->next_result();
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL");
		
		$query           = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = 'cw_transactions'");
		$result_info     = $query->result();
		$table_count     = $result_info[0]->data_count;
		
		$trans_table_col = "";
		if((int)$table_count === 1){
			$trans_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = 'cw_transactions' and COLUMN_NAME not like '%trans%'");
			$trans_table_col   = $trans_table_query->result_array();
		}
		
		$trans_line     = "";
		$trans_line_alt = "";
		//print_r($form_setting); die;
		foreach($form_setting as $setting){
			$prime_form_id      = $setting->prime_form_id;
			$prime_module_id    = $setting->prime_module_id;
			$field_type         = $setting->field_type;
			$label_name         = $setting->label_name;
			$field_length       = $setting->field_length;
			$field_decimals     = $setting->field_decimals;
			$pick_list          = $setting->pick_list;
			$field_isdefault    = $setting->field_isdefault;
			$default_value      = $setting->default_value;
			$mandatory_field    = $setting->mandatory_field;
			$unique_field       = $setting->unique_field;
			$field_sort         = $setting->field_sort;
			$field_show         = $setting->field_show;
			$transaction_type   = $setting->transaction_type;
			$loan_check         = $setting->loan_check;
			
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			if($default_value === ""){
				$default_value = null;
			}
			
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			
			if(((int)$transaction_type === 1) || ((int)$transaction_type === 2) || ((int)$transaction_type === 3)){
				if($trans_table_col){
					$trans_uniq = "";
					if((int)$unique_field === 1){
						$result_key = array_keys($trans_table_col,[ 'col_name' => $label_name,'key_exist'=>'UNI']);
						
						if(empty($result_key)){
							$trans_uniq = "";//" ADD UNIQUE($label_name),";
						}
					}
					
					if(array_search($label_name, array_column($trans_table_col, 'col_name'))){
						$trans_line_alt .= "CHANGE $label_name $label_name $data_type, $trans_uniq";
						if((int)$loan_check === 1){
							$trans_line_alt .= "CHANGE $label_name"."_total"." $label_name"."_total	decimal(15,2) NULL DEFAULT '0' AFTER $label_name,CHANGE $label_name"."_installments"." $label_name"."_installments decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_total ,CHANGE $label_name"."_instal_count"." $label_name"."_instal_count decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_installments,CHANGE $label_name"."_balance"." $label_name"."_balance decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_instal_count,";
						}
					}else{
						$last_col = end($trans_table_col);
						$trans_last_col = $last_col['col_name'];
						$trans_line_alt .= "ADD $label_name $data_type AFTER $trans_last_col, $trans_uniq";
						if((int)$loan_check === 1){ // For Create Loan Fields
							$trans_line_alt .= "ADD $label_name"."_total"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name,ADD $label_name"."_installments"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_total,ADD $label_name"."_instal_count"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_installments,ADD $label_name"."_balance"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name"."_instal_count,";
						}
					}					
				}else{
					$trans_uniq = "";
					if((int)$unique_field === 1){
						$trans_uniq = "";//" UNIQUE($label_name),";
					}
					$trans_line .= "$label_name $data_type, $trans_uniq ";
				}
			}
		}
		
		$trans_line     = rtrim($trans_line,", ");
		$trans_line     = rtrim($trans_line,",");
		$trans_line_alt = rtrim($trans_line_alt,", ");
		$trans_line_alt = rtrim($trans_line_alt,",");

		//ADD AND CHANGE ALTER TABLE
		if((int)$table_count === 0){
			if($trans_line){
				$trans_table_query = "CREATE TABLE IF NOT EXISTS cw_transactions($trans_line)";
				$this->db->query($trans_table_query);
			}
			return true;
		}else{
			if($trans_line_alt){
				$trans_table_query_alt  = "ALTER TABLE cw_transactions $trans_line_alt";
				$this->db->query($trans_table_query_alt);
			}
			return true;
		}
	}
	/* ==============================================================*/
	/* =========== TRANSACTION TABLE SAVE DATA - END ================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* =========== MONTHLY TABLE SAVE DATA - START ==================*/
	/* ==============================================================*/
	//Start date 27-12-2018 Jaffer
	public function save_monthly_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;
		$db_name         = $this->config->item("db_name");
		//only monthly input checked based monthly table
		if($prime_module_id != "employees"){
			return false;
		}
		
		$info            = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting    = $info->result();
		$info->next_result();
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL");
		
		$query           = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = 'cw_monthly_input'");
		$result_info     = $query->result();
		$table_count     = $result_info[0]->data_count;
		
		$month_table_col = "";
		if((int)$table_count === 1){
			$month_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = 'cw_monthly_input' and COLUMN_NAME not like '%trans%'");
			$month_table_col   = $month_table_query->result_array();
		}
		
		$month_line     = "";
		$month_line_alt = "";
		
		foreach($form_setting as $setting){
			$prime_form_id            = $setting->prime_form_id;
			$prime_module_id          = $setting->prime_module_id;
			$field_type               = $setting->field_type;
			$label_name               = $setting->label_name;
			$field_length             = $setting->field_length;
			$field_decimals           = $setting->field_decimals;
			$pick_list                = $setting->pick_list;
			$field_isdefault          = $setting->field_isdefault;
			$default_value            = $setting->default_value;
			$mandatory_field          = $setting->mandatory_field;
			$unique_field             = $setting->unique_field;
			$field_sort               = $setting->field_sort;
			$field_show               = $setting->field_show;
			$earn_month_check         = $setting->earn_month_check;
			$deduction_month_check    = $setting->deduction_month_check;
			$loan_check               = $setting->loan_check;
			
			
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			if($default_value === ""){
				$default_value = null;
			}
			
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			
			if(((int)$earn_month_check === 1) || ((int)$deduction_month_check === 1)){		
				if($month_table_col){
					$month_uniq = "";
					if((int)$unique_field === 1){
						$result_key = array_keys($month_table_col,[ 'col_name' => $label_name,'key_exist'=>'UNI']);
						
						if(empty($result_key)){
							$month_uniq = "";//" ADD UNIQUE($label_name),";
						}
					}
					
					if(array_search($label_name, array_column($month_table_col, 'col_name'))){
						$month_line_alt .= "CHANGE $label_name $label_name $data_type, $month_uniq";
						/*if((int)$loan_check === 1){
							$month_line_alt .= "CHANGE $label_name"."_balance"." $label_name"."_balance	decimal(15,2) NULL DEFAULT '0' AFTER $label_name,";
						}*/
					}else{
						$last_col = end($month_table_col);
						$month_last_col = $last_col['col_name'];
						$month_line_alt .= "ADD $label_name $data_type AFTER $month_last_col, $month_uniq";
						/*if((int)$loan_check === 1){ // For Create Loan Fields
							$trans_line_alt .= "ADD $label_name"."_balance"." decimal(15,2) NULL DEFAULT '0' AFTER $label_name,";
						}*/
					}
				}else{
					$month_uniq = "";
					if((int)$unique_field === 1){
						$month_uniq = "";//" UNIQUE($label_name),";
					}
					$month_line .= "$label_name $data_type, $month_uniq ";
				}
			}
		}
		$month_line     = rtrim($month_line,", ");
		$month_line     = rtrim($month_line,",");
		$month_line_alt = rtrim($month_line_alt,", ");
		$month_line_alt = rtrim($month_line_alt,",");
		
		
		//ADD AND CHANGE ALTER TABLE
		if((int)$table_count === 0){
			if($month_line){
				$month_table_query = "CREATE TABLE IF NOT EXISTS cw_monthly_input($month_line)";
				$this->db->query($month_table_query);
			}
			return true;
		}else{
			if($month_line_alt){
				$month_table_query_alt  = "ALTER TABLE cw_monthly_input $month_line_alt";
				$this->db->query($month_table_query_alt);
			}
			return true;
		}
	}
	/* ==============================================================*/
	/* =========== MONTHLY TABLE SAVE DATA - END ====================*/
	/* ==============================================================*/
	
	//18JUNE2019 Update keywords and label name
	//checking reserved word and keywords check label name
	public function check_reserved_words(){
		$label_name  = $this->input->post('label_name');
		$reserved_qry  = 'select count(*) as exist_count from `cw_reserved_words` where reserved_word = "'.$label_name.'"';
		$reserved_info   = $this->db->query("CALL sp_a_run ('SELECT','$reserved_qry')");
		$reserved_result = $reserved_info->result();
		$reserved_info->next_result();
		$reserved_count = $reserved_result[0]->exist_count;
		if((int)$reserved_count > 0){
			echo json_encode(array('success' => FALSE,'msg' =>"Mysql Reserved keywords not allowed to lable name"));
		}else{
			echo json_encode(array('success' => TRUE,'msg' =>"Ok Proceed!"));
		}
	}
	
	//21SEP2019  TO TAX CALCULATOR
	/* ==============================================================*/
	/* =========== INDIVIDUAL  TABLE DATA - START  ==================*/
	/* ==============================================================*/
	public function save_ind_tax_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;
		$db_name         = $this->config->item("db_name");
		//only monthly input checked based monthly table
		if($prime_module_id != "employees"){
			return false;
		}
		
		$info            = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting    = $info->result();
		$info->next_result();
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL");
		
		$query           = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = 'cw_tax_ind_cal'");
		$result_info     = $query->result();
		$table_count     = $result_info[0]->data_count;
		
		$tax_table_col = "";
		if((int)$table_count === 1){
			$tax_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = 'cw_tax_ind_cal' and COLUMN_NAME not like '%trans%'");
			$tax_table_col   = $tax_table_query->result_array();
		}
		$tax_line     = "";
		$tax_line_alt = "";
		foreach($form_setting as $setting){
			$field_type               = $setting->field_type;
			$label_name               = $setting->label_name;
			$field_length             = $setting->field_length;
			$field_decimals           = $setting->field_decimals;
			$field_isdefault          = $setting->field_isdefault;
			$default_value            = $setting->default_value;
			$taxable_check            = $setting->taxable_check;
						
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			
			if($default_value === ""){
				$default_value = null;
			}
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			if((int)$taxable_check === 1){
				if($tax_table_col){
					if(array_search($label_name, array_column($tax_table_col, 'col_name'))){
						$tax_line_alt .= "CHANGE $label_name $label_name $data_type,";
					}else{
						$last_col = end($tax_table_col);
						$tax_last_col = $last_col['col_name'];
						$tax_line_alt .= "ADD $label_name $data_type AFTER $tax_last_col,";
					}
				}else{
					$tax_line .= "$label_name $data_type,";
				}
			}
		}
		$tax_line     = rtrim($tax_line,", ");
		$tax_line     = rtrim($tax_line,",");
		$tax_line_alt = rtrim($tax_line_alt,", ");
		$tax_line_alt = rtrim($tax_line_alt,",");
		
		//ADD AND CHANGE ALTER TABLE
		if((int)$table_count === 0){
			if($tax_line){
				$tax_table_query = "CREATE TABLE IF NOT EXISTS cw_tax_ind_cal($tax_line)";
				$this->db->query($tax_table_query);
			}
			return true;
		}else{
			if($tax_line_alt){
				$tax_table_query_alt  = "ALTER TABLE cw_tax_ind_cal $tax_line_alt";
				$this->db->query($tax_table_query_alt);
			}
			return true;
		}
	}
	/* ==============================================================*/
	/* =========== INDIVIDUAL TAX TABLE SAVE DATA - END =============*/
	/* ==============================================================*/
/* ==============================================================*/
	/* =========== INDIVIDUAL TAX TABLE SAVE DATA - END =============*/
	/* ==============================================================*/
	/* ==============================================================*/
	/* =========== CUSTOM ENROLMENT TABLE DATA - START  =============*/
	/* ==============================================================*/
	//SAVE CUSTOM TABLE AFTER EMPLOYEE MODULE 25JAN2020
	public function save_custom_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;		
		$db_name         = $this->config->item("db_name");
		$info         = $this->db->query("select * from  cw_form_setting where prime_module_id = 'employees' and input_view_type in (1,2) and trans_status = '1' order by prime_form_id");
		$form_setting = $info->result();
		$info->next_result();
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL",11=>"varchar(@LENGTH) NULL DEFAULT NULL",12=>"varchar(@LENGTH) NULL DEFAULT NULL",13=>"datetime NULL DEFAULT NULL",14=>"varchar(@LENGTH) NULL DEFAULT NULL");
		$prime_table_name  = "cw_custom_".$prime_module_id;
		$query             = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = '$prime_table_name'");
		$result_info       = $query->result();
		$table_count       = $result_info[0]->data_count;
		$prime_table_col = "";
		if((int)$table_count === 1){
			$prime_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$prime_table_name' and COLUMN_NAME not like '%trans%'");
			$prime_table_col   = $prime_table_query->result_array();
		}
		$prime_id          = "prime_custom_".$prime_module_id."_id";		
		$prime_line        = "$prime_id int(11) NOT NULL AUTO_INCREMENT,";
		$prime_line_alt    = "CHANGE $prime_id $prime_id int(11) NOT NULL AUTO_INCREMENT,";
		foreach($form_setting as $setting){
			$prime_form_id   = $setting->prime_form_id;
			$prime_module_id = $setting->prime_module_id;
			$field_type      = $setting->field_type;
			$label_name      = $setting->label_name;
			$field_length    = $setting->field_length;
			$field_decimals  = $setting->field_decimals;
			$pick_list       = $setting->pick_list;
			$field_isdefault = $setting->field_isdefault;
			$default_value   = $setting->default_value;
			
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			if($default_value === ""){
				$default_value = null;
			}
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			if((int)$field_isdefault === 1){
				if($prime_table_col){
					if(array_search($label_name, array_column($prime_table_col, 'col_name'))){
						$prime_line_alt .= "CHANGE $label_name $label_name $data_type,";
					}else{
						$last_col = end($prime_table_col);
						$prime_last_col = $last_col['col_name'];
						$prime_line_alt .= "ADD $label_name $data_type AFTER $prime_last_col";
					}
				}else{
					$prime_line .= "$label_name $data_type,";
				}
			}
		}
		$prime_line_alt = rtrim($prime_line_alt,", ");
		$prime_line_alt = rtrim($prime_line_alt,",");
		$prime_line .= "trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME NULL DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$prime_id`)";
		//CREATE AND ALTER TABLE
		if((int)$table_count === 0){
			$prime_table_query = "CREATE TABLE IF NOT EXISTS $prime_table_name($prime_line)";
			$this->db->query($prime_table_query);
			return true;
		}else{
			$prime_table_query_alt  = "ALTER TABLE $prime_table_name $prime_line_alt";
			$this->db->query($prime_table_query_alt);
			return true;
		}
	}
	
	// SAVE CUSTOM ROWSET TABLE
	public function save_custom_rowset_table($table_data,$view_input_for,$table_name){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;
		if($prime_module_id != "employees"){
			return false;
		}
		
		$info         = $this->db->query("CALL sp_form_setting_crud ('QUERY_VIEW', '$table_data',null)");
		$form_setting = $info->result();
		$info->next_result();
		$db_name         = $this->config->item("db_name");
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL",11=>"varchar(@LENGTH) NULL DEFAULT NULL",12=>"varchar(@LENGTH) NULL DEFAULT NULL",13=>"datetime NULL DEFAULT NULL",14=>"varchar(@LENGTH) NULL DEFAULT NULL");
		$row_table_name  = "cw_custom_".$table_name;
		$query           = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = '$row_table_name'");
		$result_info     = $query->result();
		$table_count     = $result_info[0]->data_count;
		$row_table_col = "";
		if((int)$table_count === 1){
			$row_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$row_table_name' and COLUMN_NAME not like '%trans%'");
			$row_table_col   = $row_table_query->result_array();
		}
		$rowset_line     = "";
		$rowset_line_alt = "";
		foreach($form_setting as $setting){
			$prime_form_id   = $setting->prime_form_id;
			$prime_module_id = $setting->prime_module_id;
			$input_for       = (int)$setting->input_for;
			$field_type      = $setting->field_type;
			$label_name      = $setting->label_name;
			$field_length    = $setting->field_length;
			$field_decimals  = $setting->field_decimals;
			$field_isdefault = $setting->field_isdefault;
			$default_value   = $setting->default_value;
			$field_sort      = $setting->field_sort;
			if((int)$view_input_for === (int)$input_for){
				if((int)$field_type === 2){
					$field_length = $field_decimals;
				}
				if(!$field_length){
					$field_length = 100;
				}
				if($default_value === ""){
					$default_value = null;
				}
				$data_type  = $field_type_array[$field_type];
				$data_type  = str_replace("@LENGTH",$field_length,$data_type);
				$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
				if($row_table_col){
					if(array_search($label_name, array_column($row_table_col, 'col_name'))){
						$rowset_line_alt .= "CHANGE $label_name $label_name $data_type,";
					}else{
						$last_col = end($row_table_col);
						$prime_last_col = $last_col['col_name'];
						$rowset_line_alt .= "ADD $label_name $data_type AFTER $prime_last_col,";
					}
				}else{
					$rowset_line .= "$label_name $data_type,";
				}
			}
		}
		$rowset_line      = rtrim($rowset_line,", ");
		$rowset_line      = rtrim($rowset_line,",");
		$rowset_line_alt  = rtrim($rowset_line_alt,", ");
		$rowset_line_alt  = rtrim($rowset_line_alt,",");
		//CREATE AND ALTER TABLE
		if((int)$table_count === 0){
			$row_table_query = "CREATE TABLE IF NOT EXISTS $row_table_name($rowset_line)";
			$this->db->query($row_table_query);
			return true;
		}else{
			$row_table_query_alt  = "ALTER TABLE $row_table_name $rowset_line_alt";
			$this->db->query($row_table_query_alt);
			return true;
		}
	}
	
	//SAVE CUSTOM TABLE AFTER EMPLOYEE MODULE 25JAN2020
	public function save_custom_log_table($table_data){
		$data_info       = json_decode($table_data);
		$prime_module_id = $data_info->prime_module_id;		
		$db_name         = $this->config->item("db_name");
		$info         = $this->db->query("select * from  cw_form_setting where prime_module_id = 'employees' and input_view_type in (1,2) and trans_status = '1' order by prime_form_id");
		$form_setting = $info->result();
		$info->next_result();
		
		$field_type_array =  array(1=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",2=>"decimal(15,@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",3=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",4=>"date NULL DEFAULT NULL",5=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",6=>"int(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",7=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",8=>"TEXT NULL",9=>"varchar(@LENGTH) NULL DEFAULT '@DEFAULTVALUE'",10=>"TEXT NULL",11=>"varchar(@LENGTH) NULL DEFAULT NULL",12=>"varchar(@LENGTH) NULL DEFAULT NULL",13=>"datetime NULL DEFAULT NULL",14=>"varchar(@LENGTH) NULL DEFAULT NULL");
		$prime_table_name  = "cw_".$prime_module_id."_log";
		$query             = $this->db->query("SELECT COUNT(*)AS data_count FROM information_schema.tables WHERE  TABLE_SCHEMA ='$db_name' AND table_name = '$prime_table_name'");
		$result_info       = $query->result();
		$table_count       = $result_info[0]->data_count;
		$prime_table_col = "";
		if((int)$table_count === 1){
			$prime_table_query = $this->db->query("SELECT COLUMN_NAME AS col_name, COLUMN_KEY as key_exist  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='$db_name' and TABLE_NAME = '$prime_table_name' and COLUMN_NAME not like '%trans%'");
			$prime_table_col   = $prime_table_query->result_array();
		}
		$prime_log_id      = "prime_".$prime_module_id."_log_id";
		$prime_id          = $prime_module_id."_id";
		$prime_line        = "$prime_log_id int(11) NOT NULL AUTO_INCREMENT, $prime_id int(11) NOT NULL,";
		$prime_line_alt    = "CHANGE $prime_log_id $prime_log_id int(11) NOT NULL AUTO_INCREMENT,";
		foreach($form_setting as $setting){
			$prime_form_id   = $setting->prime_form_id;
			$prime_module_id = $setting->prime_module_id;
			$field_type      = $setting->field_type;
			$label_name      = $setting->label_name;
			$field_length    = $setting->field_length;
			$field_decimals  = $setting->field_decimals;
			$pick_list       = $setting->pick_list;
			$field_isdefault = $setting->field_isdefault;
			$default_value   = $setting->default_value;
			
			if((int)$field_type === 2){
				$field_length = $field_decimals;
			}
			if(!$field_length){
				$field_length = 100;
			}
			if($default_value === ""){
				$default_value = null;
			}
			$data_type  = $field_type_array[$field_type];
			$data_type  = str_replace("@LENGTH",$field_length,$data_type);
			$data_type  = str_replace("@DEFAULTVALUE",$default_value,$data_type);
			if((int)$field_isdefault === 1){
				if($prime_table_col){
					if(array_search($label_name, array_column($prime_table_col, 'col_name'))){
						$prime_line_alt .= "CHANGE $label_name $label_name $data_type,";
					}else{
						$last_col = end($prime_table_col);
						$prime_last_col = $last_col['col_name'];
						$prime_line_alt .= "ADD $label_name $data_type AFTER $prime_last_col";
					}
				}else{
					$prime_line .= "$label_name $data_type,";
				}
			}
		}
		$prime_line_alt = rtrim($prime_line_alt,", ");
		$prime_line_alt = rtrim($prime_line_alt,",");
		$prime_line .= "trans_created_by INT(11) NULL DEFAULT '0', trans_created_date DATETIME NULL DEFAULT NULL, trans_updated_by INT(11) NULL DEFAULT '0', trans_updated_date DATETIME NULL DEFAULT NULL, trans_deleted_by INT(11) NULL DEFAULT '0', trans_deleted_date DATETIME NULL DEFAULT NULL,trans_status INT(11) NULL DEFAULT '1',PRIMARY KEY (`$prime_log_id`)";
		//CREATE AND ALTER TABLE
		if((int)$table_count === 0){
			$prime_table_query = "CREATE TABLE IF NOT EXISTS $prime_table_name($prime_line)";
			$this->db->query($prime_table_query);
			return true;
		}else{
			$prime_table_query_alt  = "ALTER TABLE $prime_table_name $prime_line_alt";
			$this->db->query($prime_table_query_alt);
			return true;
		}
	}
	/* ==============================================================*/
	/* =========== CUSTOM ENROLMENT TABLE DATA - END    =============*/
	/* ==============================================================*/

	
	/* ==========================================================*/
	/* =========== PICK LIST BASE SEARCH - START    =============*/
	/* ==========================================================*/
	
	function get_query_column_list(){
		$query_list_id  = $this->input->post('query_list_id');
		$pick_base_qry    = 'select * from cw_form_setting where prime_form_id = "'.$query_list_id.'"';		
		$pick_base_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_base_qry')");
		$pick_base_result = $pick_base_data->result();
		$pick_base_data->next_result();
		if($pick_base_result){
			$pick_table      = $pick_base_result[0]->pick_table;	
			$get_colums = 'SELECT `COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` = "'.$pick_table.'" AND COLUMN_NAME NOT LIKE "%trans%"';
			$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
			$column_result = $column_info->result();
			$column_info->next_result();

			$column_list[""] = "---- Select Column ----";			
			foreach($column_result as $column){
				$column_value = $column->COLUMN_NAME;
				$column_name  = ucwords(str_replace("_"," ",$column_value));
				$column_list[$column_value] = $column_name;
			}
			echo json_encode(array('success'=> true,'msg'=>"Column list","column_list"=>$column_list));
		}else{
			echo json_encode(array('success'=> false,'msg'=>"Unable to get Column list","column_list"=>""));
		}
	}
	function get_session_table_value(){
		$query_list_id      = $this->input->post('query_list_id');
		$query_column_list  = $this->input->post('query_column_list');
		$values_from        = (int)$this->input->post('values_from');
		$check_from         = (int)$this->input->post('check_from');
		if($values_from === 1){			
			$pick_base_qry    = 'select * from cw_form_setting where prime_form_id = "'.$query_list_id.'"';
			$pick_base_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_base_qry')");
			$pick_base_result = $pick_base_data->result();
			$pick_base_data->next_result();
			if($pick_base_result){
				$prime_module_id = $pick_base_result[0]->prime_module_id;
				$view_name       = $pick_base_result[0]->view_name;
				$field_type      = (int)$pick_base_result[0]->field_type;
				$pick_list_type  = (int)$pick_base_result[0]->pick_list_type;
				$pick_list 	     = $pick_base_result[0]->pick_list;
				$pick_table 	 = $pick_base_result[0]->pick_table;
				$same_table = "cw_".$prime_module_id;
				if($pick_table === $same_table){
					$get_qry    = 'select * from cw_form_setting where label_name = "'.$query_column_list.'" and prime_module_id = "'.$prime_module_id.'"';
					$get_data   = $this->db->query("CALL sp_a_run ('SELECT','$get_qry')");
					$get_result = $get_data->result();
					$get_data->next_result();
					if(count($get_result) === 1){
						$view_name      = $get_result[0]->view_name;
						$field_type     = (int)$get_result[0]->field_type;
						$pick_list_type = (int)$get_result[0]->pick_list_type;
						$pick_list 	    = $get_result[0]->pick_list;
						$pick_table 	= $get_result[0]->pick_table;
						$pick_list_info = array();
						if(($field_type === 5) || ($field_type === 7)){
							$final_pick = array();
							if($pick_list_type === 1){
								$pick_colum_qry    = 'SELECT '.$pick_list.' FROM '.$pick_table.' WHERE  trans_status = 1';
								$get_pick_colum    = $this->db->query("CALL sp_a_run ('SELECT','$pick_colum_qry')");
								$pick_colum_result = $get_pick_colum->result();
								$get_pick_colum->next_result();							
								if($pick_colum_result){
									$pick_list_val   = explode(",",$pick_list);
									$pick_list_val_1 = $pick_list_val[0];
									$pick_list_val_2 = $pick_list_val[1];
									$pick_key   = array_column($pick_colum_result, $pick_list_val_1);
									$pick_val   = array_column($pick_colum_result, $pick_list_val_2);
									$final_pick = array_combine( $pick_key, $pick_val);
									$final_pick = array("" => "---- $view_name ----") + $final_pick;
								}
							}else
							if($pick_list_type === 2){
								$id     = $pick_table."_id";
								$value  = $pick_table."_value";
								$status = $pick_table."_status";
								$select_info = "$id,$value";
								$pick_colum_qry    = 'SELECT '.$select_info.' FROM '.$pick_table.' WHERE  '.$status.' = 1';
								$get_pick_colum    = $this->db->query("CALL sp_a_run ('SELECT','$pick_colum_qry')");
								$pick_colum_result = $get_pick_colum->result();
								$get_pick_colum->next_result();
								if($pick_colum_result){
									$pick_key   = array_column($pick_colum_result, $id);
									$pick_val   = array_column($pick_colum_result, $value);
									$final_pick = array_combine( $pick_key, $pick_val);
									$final_pick = array("" => "---- $view_name ----") + $final_pick;
								}
							}
							echo json_encode(array('success'=> true,'msg'=>"Session list","value_list"=>$final_pick));
						}else{
							echo json_encode(array('success'=> false,'msg'=>"Not Picklist column","value_list"=>""));
						}
					}else{
						echo json_encode(array('success'=> false,'msg'=>"label_name found - ".count($get_result),"value_list"=>""));
					}
				}else{
					$pick_list_info  = array();
					if(($field_type === 5) || ($field_type === 7)){
						$final_pick = array();
						if($pick_list_type === 1){
							$pick_colum_qry    = 'SELECT '.$pick_list.' FROM '.$pick_table.' WHERE  trans_status = 1';
							$get_pick_colum    = $this->db->query("CALL sp_a_run ('SELECT','$pick_colum_qry')");
							$pick_colum_result = $get_pick_colum->result();
							$get_pick_colum->next_result();							
							if($pick_colum_result){
								$pick_list_val   = explode(",",$pick_list);
								$pick_list_val_1 = $pick_list_val[0];
								$pick_list_val_2 = $pick_list_val[1];
								$pick_key   = array_column($pick_colum_result, $pick_list_val_1);
								$pick_val   = array_column($pick_colum_result, $pick_list_val_2);
								$final_pick = array_combine( $pick_key, $pick_val);
								$final_pick = array("" => "---- $view_name ----") + $final_pick;
							}
						}else
						if($pick_list_type === 2){
							$id     = $pick_table."_id";
							$value  = $pick_table."_value";
							$status = $pick_table."_status";
							$select_info = "$id,$value";
							$pick_colum_qry    = 'SELECT '.$select_info.' FROM '.$pick_table.' WHERE  '.$status.' = 1';
							$get_pick_colum    = $this->db->query("CALL sp_a_run ('SELECT','$pick_colum_qry')");
							$pick_colum_result = $get_pick_colum->result();
							$get_pick_colum->next_result();
							if($pick_colum_result){
								$pick_key   = array_column($pick_colum_result, $id);
								$pick_val   = array_column($pick_colum_result, $value);
								$final_pick = array_combine( $pick_key, $pick_val);
								$final_pick = array("" => "---- $view_name ----") + $final_pick;
							}
						}
						echo json_encode(array('success'=> true,'msg'=>"Session list","value_list"=>$final_pick));
					}else{
						echo json_encode(array('success'=> false,'msg'=>"Not Picklist column","value_list"=>""));
					}
				}		
			}else{
				echo json_encode(array('success'=> false,'msg'=>"Invalid picklist","value_list"=>""));
			}				
		}else
		if($values_from === 2){
			$session_val_qry    = 'SELECT * FROM cw_session_value WHERE  trans_status = 1 order by abs(session_for)';
			$get_session_val    = $this->db->query("CALL sp_a_run ('SELECT','$session_val_qry')");
			$session_val_result = $get_session_val->result();
			$get_session_val->next_result();
			foreach($session_val_result as $col){
				$col_id    = (int)$col->session_for;
				$session_for = "Employee";
				if($col_id === 2){
					$session_for = "Customer";
				}
				$col_value = $col->session_value;
				$key_value = $col_id."|".$col_value;
				$session_list[$key_value] = "$session_for - $col_value";
			}
			echo json_encode(array('success'=> true,'msg'=>"Session list","value_list"=>$session_list));
		}else{
			echo json_encode(array('success'=> false,'msg'=>"Unable get values","value_list"=>""));
		}
	}
	function save_pick_base_query(){
		$prime_pick_base_search_id = (int)$this->input->post('prime_pick_base_search_id');
		$pick_module_id            = $this->input->post('pick_module_id');
		$pick_query_for            = $this->input->post('pick_query_for');
		$query_list_id             = $this->input->post('query_list_id');
		$pick_where_condition      = $this->input->post('pick_where_condition');
		$logged_id                 = $this->session->userdata('logged_id');
		$date                      = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_pick_base_search WHERE  pick_module_id = "'.$pick_module_id.'" and pick_query_for = "'.$pick_query_for.'" and query_list_id = "'.$query_list_id.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			if($prime_pick_base_search_id  > 0){
				$upd_qry  = 'UPDATE  cw_pick_base_search SET pick_module_id = "'.$pick_module_id.'",pick_query_for = "'.$pick_query_for.'",query_list_id = "'.$query_list_id.'",pick_where_condition = "'.$pick_where_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_pick_base_search_id = "'.$prime_pick_base_search_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$pick_query_list = $this->get_pick_base_query_list($pick_module_id);
				echo json_encode(array('success' => true,'message'=>"Basic Query updated successfully !!!",'pick_query_list'=>$pick_query_list));
			}else{
				$search_qry  = 'INSERT INTO cw_pick_base_search (pick_module_id, pick_query_for, query_list_id,pick_where_condition,trans_created_by, trans_created_date) VALUES ("'.$pick_module_id.'","'.$pick_query_for.'","'.$query_list_id.'","'.$pick_where_condition.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
				$pick_query_list = $this->get_pick_base_query_list($pick_module_id);
				echo json_encode(array('success' => true,'message'=>"Basic Query added successfully !!!",'pick_query_list'=>$pick_query_list));
			}
		}else{
			$db_prime_table_id = (int)$exist_result[0]->prime_pick_base_search_id;
			if($db_prime_table_id === $prime_pick_base_search_id){
				$upd_qry  = 'UPDATE  cw_pick_base_search SET pick_module_id = "'.$pick_module_id.'",pick_query_for = "'.$pick_query_for.'",query_list_id = "'.$query_list_id.'",pick_where_condition = "'.$pick_where_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_pick_base_search_id = "'.$prime_pick_base_search_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$pick_query_list = $this->get_pick_base_query_list($pick_module_id);
				echo json_encode(array('success' => true,'message'=>"Basic Query updated successfully !!!",'pick_query_list'=>$pick_query_list));
			}else{
				echo json_encode(array('success' => false,'message'=>"Basic Query already exist"));
			}
		}
	}
	function get_pick_base_query_list($pick_module_id){
		$query_list  = 'SELECT prime_pick_base_search_id,pick_module_id,category_name,view_name,pick_where_condition FROM cw_pick_base_search INNER join cw_category on cw_category.prime_category_id = cw_pick_base_search.pick_query_for INNER join cw_form_setting on cw_form_setting.prime_form_id = cw_pick_base_search.query_list_id WHERE  cw_pick_base_search.pick_module_id = "'.$pick_module_id.'" and cw_pick_base_search.trans_status = 1';
		$query_list_info   = $this->db->query("CALL sp_a_run ('SELECT','$query_list')");
		$query_list_result = $query_list_info->result();
		$query_list_info->next_result();
		foreach($query_list_result as $rslt){
			$prime_pick_base_search_id  = $rslt->prime_pick_base_search_id;
			$pick_module_id             = $rslt->pick_module_id;
			$category_name              = $rslt->category_name;
			$view_name                  = $rslt->view_name;
			$pick_where_condition       = $rslt->pick_where_condition;
			$query_tr_line .= "<tr>
								<td>$category_name</td>
								<td>$view_name</td>
								<td>$pick_where_condition</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_pick_query('$prime_pick_base_search_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
								<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_pick_query('$prime_pick_base_search_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
							</tr>";
		}
		$table_query_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th style='text-align: center;'>Query For</th>
										<th style='text-align: center;'>Pick List</th>
										<th style='text-align:center;'>Condition Information</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
									$query_tr_line
								</table>";
		return $table_query_content;
	}
	function get_edit_pick_query(){
		$prime_pick_base_search_id   = (int)$this->input->post('prime_pick_base_search_id');
		$edit_query  = 'SELECT * FROM cw_pick_base_search WHERE  prime_pick_base_search_id = "'.$prime_pick_base_search_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_query')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		if($edit_result){
			$prime_pick_base_search_id  = $edit_result[0]->prime_pick_base_search_id;
			$pick_query_for             = $edit_result[0]->pick_query_for;
			$query_list_id              = $edit_result[0]->query_list_id;
			$pick_where_condition       = $edit_result[0]->pick_where_condition;
			
			$pick_base_qry    = 'select * from cw_form_setting where prime_form_id = "'.$query_list_id.'"';
			$pick_base_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_base_qry')");
			$pick_base_result = $pick_base_data->result();
			$pick_base_data->next_result();
			$column_list[""] = "---- Select Column ----";
			if($pick_base_result){
				$pick_table = $pick_base_result[0]->pick_table;			
				$get_colums = 'SELECT `COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` = "'.$pick_table.'" AND COLUMN_NAME NOT LIKE "%trans%"';
				$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
				$column_result = $column_info->result();
				$column_info->next_result();	
				//$field_array_qry = 'SELECT ';						
				foreach($column_result as $column){
					$column_value = $column->COLUMN_NAME;
					$column_name = ucwords(str_replace("_"," ",$column_value));
					$column_list[$column_value] = $column_name;
				}
			}		
			echo json_encode(array('success' => true,'prime_pick_base_search_id'=>$prime_pick_base_search_id,'pick_query_for'=>$pick_query_for,'query_list_id'=>$query_list_id,'pick_where_condition'=>$pick_where_condition,'column_list'=>$column_list));
		}else{
			echo json_encode(array('success' => false,'message'=>"Unable process your request"));
		}
	}
	public function remove_pick_query(){
		$prime_pick_base_search_id   = (int)$this->input->post('prime_pick_base_search_id');
		$pick_module_id  = $this->input->post('pick_module_id');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE  cw_pick_base_search SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_pick_base_search_id = "'.$prime_pick_base_search_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$pick_query_list = $this->get_pick_base_query_list($pick_module_id);
		echo json_encode(array('success' => true,'message'=>'Table Query Remove Successfully !!!','pick_query_list'=>$pick_query_list));
	}

	/* ========================================================*/
	/* =========== PICK LIST BASE SEARCH - END    =============*/
	/* ========================================================*/
	public function get_extension_info(){
		$file_type = $this->input->post('file_type');
		if($file_type === "image/*"){
			$file_type = 3;
		}else
		if($file_type === "audio/*"){
			$file_type = 2;
		}else{
			$file_type = 1;
		}		
		$file_type_query  = 'SELECT file_type,extension FROM cw_upload_extension WHERE  file_type = "'.$file_type.'" and cw_upload_extension.trans_status = 1';
		$file_type_info   = $this->db->query("CALL sp_a_run ('SELECT','$file_type_query')");
		$file_type_result = $file_type_info->result();
		$file_type_info->next_result();
		$option = "<option value ='' >--- Select Extension ---</option>";
		foreach($file_type_result as $column){
			$prime_form_id = strtolower($column->extension);
			$column_value  = $column->extension;
			$option .= "<option value ='$prime_form_id' >$column_value</option>";
		}		
		echo $option;
	}
	
	/* (SVK EDIT START) */
	//GET DEFAULT TABLE UI 
	public function get_table_view_data(){ 
		$prime_module_id  = $this->input->post('prime_module_id');
		if($prime_module_id){
			echo $this->update_table_viewui($prime_module_id);
		}else{
			echo json_encode(array('success' => False,'message' => 'Please Contact Admin..!'));
		}
	}
	
	//GET MONTHLY INPUT TABLE UI
	public function get_monthly_input_table_view_data(){ 
		$prime_module_id  = $this->input->post('prime_module_id');
		if($prime_module_id){
			echo $this->update_monthly_input_viewui($prime_module_id);
		}else{
			echo json_encode(array('success' => False,'message' => 'Please Contact Admin..!'));
		}
	}
	
	//GET PAYROLL TABLE UI 
	public function get_payroll_table_view_data(){ 
		$prime_module_id  = $this->input->post('prime_module_id');
		if($prime_module_id){
			echo $this->update_payroll_viewui($prime_module_id);
		}else{
			echo json_encode(array('success' => False,'message' => 'Please Contact Admin..!'));
		}
	}	
	/* (SVK EDIT END) */

	/* SATHISH View Name array Form Settings START */
	public function get_form_input_array($prime_module_id){
		$form_qry    = 'select label_name,view_name from cw_form_setting where prime_module_id = "'.$prime_module_id.'" and trans_status = 1';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$form_qry')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$pick_key      = array_column($form_result, "label_name");
		$pick_val      = array_column($form_result, "view_name");
		$form_list = array_combine($pick_key, $pick_val);
		return $form_list;
	}
	/* SATHISH View Name array Form Settings END */

	/* BSK START Role Based Condition */
	public function save_role_based_condition(){
		$prime_role_based_cond_id  = (int)$this->input->post('prime_role_based_condition_id');
		$role_module_id            = $this->input->post('role_module_id');
		$role_condition_for        = ltrim(implode(",",$this->input->post('role_condition_for[]')),",");
		$user_condition_type       = $this->input->post('user_condition_type');
		$input_columns             = ltrim(implode(",",$this->input->post('input_columns[]')),",");
		$logged_id                 = $this->session->userdata('logged_id');
		$date                      = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_role_base_condition WHERE  role_module_id = "'.$role_module_id.'" and user_condition_type = "'.$user_condition_type.'" and role_condition_for in ('.$role_condition_for.') and prime_role_base_condition_id != "'.$prime_role_based_cond_id.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			if($prime_role_based_cond_id  > 0){
				$upd_qry  = 'UPDATE  cw_role_base_condition SET role_module_id = "'.$role_module_id.'",role_condition_for = "'.$role_condition_for.'",user_condition_type = "'.$user_condition_type.'",input_columns = "'.$input_columns.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_role_base_condition_id = "'.$prime_role_based_cond_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$user_role_list = $this->get_role_base_cond_list($role_module_id);
				echo json_encode(array('success' => true,'message'=>"Condition updated successfully !!!",'user_role_list'=>$user_role_list));
			}else{
				$search_qry  = 'INSERT INTO cw_role_base_condition (role_module_id, role_condition_for, user_condition_type,input_columns,trans_created_by, trans_created_date) VALUES ("'.$role_module_id.'","'.$role_condition_for.'","'.$user_condition_type.'","'.$input_columns.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
				$user_role_list = $this->get_role_base_cond_list($role_module_id);
				echo json_encode(array('success' => true,'message'=>"Condition added successfully !!!",'user_role_list'=>$user_role_list));
			}
		}else{
			echo json_encode(array('success' => false,'message'=>"User Role Already Exist !!!"));
		}
	}
	function get_role_base_cond_list($role_module_id){
		$user_role_qry  = 'SELECT prime_user_role_id,role_name FROM cw_user_role WHERE cw_user_role.trans_status = 1';
		$user_role_info   = $this->db->query("CALL sp_a_run ('SELECT','$user_role_qry')");
		$user_role_result = $user_role_info->result_array();
		$user_role_info->next_result();
		$role_array = array_reduce($user_role_result, function ($result, $arr) {
		    $result[$arr['prime_user_role_id']] = $arr['role_name'];
		    return $result;
		}, array());
		$query_list  = 'SELECT prime_role_base_condition_id,role_module_id,role_condition_for,user_condition_type,input_columns FROM cw_role_base_condition WHERE cw_role_base_condition.role_module_id = "'.$role_module_id.'" and cw_role_base_condition.trans_status = 1';
		$query_list_info   = $this->db->query("CALL sp_a_run ('SELECT','$query_list')");
		$query_list_result = $query_list_info->result();
		$query_list_info->next_result();
		foreach($query_list_result as $rslt){
			$prime_role_base_condition_id  = $rslt->prime_role_base_condition_id;
			$role_module_id                = $rslt->role_module_id;
			$role_condition_for            = explode(",",$rslt->role_condition_for);
			$user_condition_type           = $rslt->user_condition_type;
			$input_columns                 = $rslt->input_columns;
			$cond_for = array();
			foreach ($role_condition_for as $value) {				
				$cond_for[] = $role_array[$value];
			}
			$query_tr_line .= "<tr>
								<td>".implode(',',$cond_for)."</td>
								<td>$user_condition_type</td>
								<td>$input_columns</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_role_based('$prime_role_base_condition_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
								<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_role_based('$prime_role_base_condition_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
							</tr>";
		}
		$table_query_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th style='text-align: center;'>Condition For</th>
										<th style='text-align: center;'>Condition type</th>
										<th style='text-align:center;'>Input Columns</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
									$query_tr_line
								</table>";
		return $table_query_content;
	}
	public function get_edit_role_based(){
		$prime_role_base_condition_id   = (int)$this->input->post('prime_role_base_condition_id');
		$edit_query  = 'SELECT * FROM cw_role_base_condition WHERE  prime_role_base_condition_id = "'.$prime_role_base_condition_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_query')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		if($edit_result){
			$prime_role_base_condition_id = $edit_result[0]->prime_role_base_condition_id;
			$role_condition_for           = $edit_result[0]->role_condition_for;
			$user_condition_type          = $edit_result[0]->user_condition_type;
			$input_columns                = $edit_result[0]->input_columns;
					
			echo json_encode(array('success' => true,'prime_role_base_condition_id'=>$prime_role_base_condition_id,'role_condition_for'=>$role_condition_for,'user_condition_type'=>$user_condition_type,'input_columns'=>$input_columns));
		}else{
			echo json_encode(array('success' => false,'message'=>"Unable process your request"));
		}
	}

	public function remove_role_based(){
		$prime_role_base_condition_id   = (int)$this->input->post('prime_role_base_condition_id');
		$role_module_id                 = $this->input->post('role_module_id');
		$logged_id                      = $this->session->userdata('logged_id');
		$date                           = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE  cw_role_base_condition SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_role_base_condition_id = "'.$prime_role_base_condition_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$user_role_list = $this->get_role_base_cond_list($role_module_id);
		echo json_encode(array('success' => true,'message'=>'Condition Remove Successfully !!!','user_role_list'=>$user_role_list));
	}
}
?>