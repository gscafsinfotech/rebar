<?php 
/**********************************************************
	   Filename: Utilities Setting
	Description: Utilities Setting for adding new excel formate,print layout and other operation.
		 Author: Jaffer Sathik
	 Created on: 26 ‎November ‎2018
	Reviewed by: Udhayakumar Anandhan (REVIEW PENDING)
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
class Utilities_settings  extends Secure_Controller{
	
	public function __construct(){
		parent::__construct('utilities_settings');
		
	}
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_form_setting_headers());
		$this->load->view('utilities_settings/manage',$data);
	}
	
	/* ==============================================================*/
	/* ================== COMMON OPEARTION - START ==================*/
	/* ==============================================================*/
	
	//MODULE SEARCH OPEARTION
	public function search(){ /*=== UDY REVIEW DONE ===*/
		$search       = $this->input->get('search');
		$limit        = $this->input->get('limit');
		$offset       = $this->input->get('offset');
		$sort         = $this->input->get('sort');
		$order        = $this->input->get('order');
		if(!$sort){
			$sort = "abs(menu_sort)";
		}
		if(!$order){
			$order = "asc";
		}
		$admin_module = array("report"=>true,"category"=>true);
		
		// Fetch Records		
		$info     = $this->db->query("CALL sp_utilities_setting_search ('SEARCH','$search')");
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
		$count_info     = $this->db->query("CALL sp_utilities_setting_search ('COUNT','$search')");
		$count_result   = $count_info->result();
		$count_info->next_result();
		$num_rows = $count_result[0]->data_count;
		echo json_encode(array('total'=>$num_rows,'rows'=>$data_rows));
	}
	
	//MODULE VIEW OPEARTION	
	public function view($prime_module_id =-1){	 /*=== UDY REVIEW DONE ===*/
		/*if($prime_module_id === 'process_payroll'){
			$prime_module_id = "transactions";
			$table_prime    = "cw_".$prime_module_id;
			$table_prime_id = "prime_".$prime_module_id."_id";
			$table_name     = ucwords(str_replace("_"," ",$table_prime));
			$table_list = array(''=>'---- Select Table ----',$table_prime=>$table_name);
			$data['table_list'] = $table_list;
			$table_mand_list = array($table_prime);
			$data['table_mand_list'] = $table_mand_list;
		}else*/
		if($prime_module_id === 'employees'){
			$table_prime    = "cw_".$prime_module_id;
			$table_prime_id = "prime_".$prime_module_id."_id";
			$table_name    = ucwords(str_replace("_"," ",$table_prime));
			$table_info_qry = 'select table_name from information_schema.tables where TABLE_SCHEMA="'.$this->config->item("db_name").'" and table_name like "%employees%"';
			$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_info_qry')");
			$table_result = $table_info->result();
			$table_info->next_result();
			$table_list[""] = '---- Select Table ----';
			foreach($table_result as $tab_name){
				$table_name = $tab_name->table_name;
				$table_view = ucwords(str_replace("_"," ",$table_name));
				$table_view = str_replace("Cw ","",$table_view);
				$table_list[$table_name] = $table_view;
				$table_details .= $table_name.'","';
			}
			$table_details = '"'.$table_details;
			$table_details = rtrim($table_details,'"');
			$table_details = rtrim($table_details,',');
			$data['table_list'] = $table_list;
			$table_mand_list = array($table_prime);
			$data['table_mand_list'] = $table_mand_list;
		}else{
			$table_prime    = "cw_".$prime_module_id;
			$table_prime_id = "prime_".$prime_module_id."_id";
			$table_name    = ucwords(str_replace("_"," ",$table_prime));
			$table_list = array(''=>'---- Select Table ----',$table_prime=>$table_name);
			$data['table_list'] = $table_list;
			$table_mand_list = array($table_prime);
			$data['table_mand_list'] = $table_mand_list;
		}
		$data['prime_module_id'] = $prime_module_id;
		
		/*if($prime_module_id === 'transactions'){
			$get_colums = 'SELECT `table_name`,`column_name`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ("'.$table_prime.'") AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ("'.$table_prime_id.'")';
			$module_name    = str_replace("cw_","",$table_prime);
		}else*/
		if($prime_module_id === 'employees'){
			$get_colums = 'SELECT `table_name`,`column_name`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_details.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ("'.$table_prime_id.'")';
			$module_name    = str_replace("cw_","",$table_details);
		}else{
			$get_colums = 'SELECT `table_name`,`column_name`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ("'.$table_prime.'") AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ("'.$table_prime_id.'")';
			$module_name    = str_replace("cw_","",$table_prime);
		}
		if($module_name === 'transactions' || $module_name === 'increment' || $module_name === 'monthly_input'){
			$module_name = '"employees"';
		}else
		if(strpos( $module_name, '"' ) === false)
		{
			$module_name = '\"'.$module_name.'\"';
		}
		
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$column_result = array_map(function($column){
			$column->table_name      = strtoupper(substr((ucwords(str_replace("_"," ",$column->table_name))),3));
			$column->get_column_name = $column->column_name;
			$column->column_val      = '"'.$column->column_name.'"';
			return $column;
		}, $column_result);
		$column_value     = implode(",",array_column($column_result,'column_val'));
		if($column_value){
			$view_name_qry    = 'select UPPER(view_name) AS view_name,label_name from cw_form_setting where prime_module_id in ('.$module_name.') and label_name in ('.$column_value.')  and trans_status = "1"';
			$view_name_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_name_qry')");
			$view_name_result = $view_name_data->result();
			$view_name_data->next_result();	
			$view_name_result = array_column($view_name_result,'view_name','label_name');	
			$table_column[""] = "---- Select Column ----";
			foreach($column_result as $column){
				$table_value   = $column->table_name;
				$column_value  = $column->column_name;
				$view_name     = $view_name_result[$column->column_name];
				$table_column[$column_value] = $table_value . " - ". $view_name;
			}
			$data['column_list']   = $table_column;
		}
		
		$get_mandatory_colums = 'SELECT prime_form_id,prime_module_id,label_name FROM `cw_form_setting` WHERE prime_module_id = "'.$prime_module_id.'" and mandatory_field = 1 and input_view_type !=3';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_mandatory_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		foreach($column_result as $column){
			$column_value = $column->label_name;
			$column_name = strtolower(str_replace(" ","_",$column_value));
			$mandatory_list[] = $column_name;
		}
		$data['mandatory_list'] = $mandatory_list;
				
		$get_excel_name = 'SELECT prime_excel_format_id,excel_name FROM `cw_util_excel_format` WHERE excel_module_id = "'.$prime_module_id.'"  AND trans_status = 1';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_excel_name')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$excel_format_list[""] = "-- Select Format --";
		foreach($column_result as $column){
			$column_value     = $column->excel_name;
			$key              = $column->prime_excel_format_id;
			$excel_format_list[$key] = $column_value;
		}
		$data['excel_format_list'] = $excel_format_list;
		$excel_view            = $this->excel_view($prime_module_id);
		$excel_content_rslt    = json_decode($excel_view);
		$data['excel_content'] = $excel_content_rslt->excel_content;
		
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1 and prime_category_id !=1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$category_list[""] = "---- Select Category ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$category_list[$role_id] = $category_name;
		}
		$data['category_list']    = $category_list;
		
		//only for increment and declaration entry modules...
		/*if(($prime_module_id === "increment") || ($prime_module_id === "declartion_entry")){
			$temp_name_qry   = 'select * from `cw_inc_temp_setting` where trans_status = 1 and module_id = "'.$prime_module_id.'"';
			$temp_name_info    = $this->db->query("CALL sp_a_run ('SELECT','$temp_name_qry')");
			$temp_name_result = $temp_name_info->result();
			$temp_name_info->next_result();
			$temp_name_list[""] = "---- Select Template ----";
			foreach($temp_name_result as $temp){
				$temp_setting_id   = $temp->prime_inc_temp_setting_id;
				$template_name     = $temp->template_name;
				$temp_name_list[$temp_setting_id] = $template_name;
			}
			$data['temp_name_list']    = $temp_name_list;
		}*/
		
		$data['excel_cell_value'] = $this->get_excel_value(100);
		/*$template_content_list = $this->template_table_view($prime_module_id);
		$template_content_list = json_decode($template_content_list);
		$template_content      = $template_content_list->template_content;
		$data['template_content'] = $template_content;		*/
		$this->load->view("utilities_settings/form",$data);
	}
	/* ==============================================================*/
	/* ================== COMMON OPEARTION - END ====================*/
	/* ==============================================================*/
	
	
	/* ==============================================================*/
	/* =============== EXCEL FORMAT  OPEARTION  START ===============*/
	/* ==============================================================*/
	//FORM INPUT SAVE OPEARTION
	public function save(){ /*=== UDY REVIEW DONE ===*/
		//prime_module_id name only changed excel_module_id
		$prime_module_id         = $this->input->post('excel_module_id');
		$prime_excel_format_id   = $this->input->post('prime_excel_format_id');
		$import_type             = $this->input->post('import_type');
		$excel_name              = $this->input->post('excel_name');		
		$excel_table_name        = ltrim(implode(",",$this->input->post('excel_table_name')),",");
		$excel_column_name       = ltrim(implode(",",$this->input->post('excel_column_name')),",");
		$exist_column_name       = ltrim(implode(",",$this->input->post('exist_column_name')),",");;
		$logged_id               = $this->session->userdata('logged_id');
		$today_date              = date("Y-m-d h:i:s");
		if($prime_module_id !== 'employees'){
			$import_type = 1;
		}
		$excel_name_qry  = 'SELECT COUNT(*) as counts FROM cw_util_excel_format WHERE excel_module_id = "'. $prime_module_id .'" AND excel_name = "'. $excel_name .'" AND trans_status = 1';
		$excel_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$excel_name_qry')");
		$excel_tab_result = $excel_tab_info->result();
		$excel_tab_info->next_result();
		$count = $excel_tab_result[0]->counts;
		if((int)$count === 0){
			if((int)$prime_excel_format_id === 0){
				$table_query = 'insert into cw_util_excel_format (excel_module_id,import_type,excel_name,excel_table_name,excel_column_name,exist_column_name,trans_created_by,trans_created_date) value ("'.$prime_module_id.'","'.$import_type.'","'.$excel_name.'","'.$excel_table_name.'","'.$excel_column_name.'","'.$exist_column_name.'","'.$logged_id.'","'.$today_date.'")';
				$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$table_query')");
				$insert_result = $insert_info->result();
				$insert_info->next_result();
			}else{
				$upd_excel_qry  = 'UPDATE cw_util_excel_format SET import_type = "'.$import_type.'", excel_name = "'.$excel_name.'", excel_table_name = "'.$excel_table_name.'", excel_column_name = "'.$excel_column_name.'", exist_column_name = "'.$exist_column_name.'", trans_updated_by = "'.$logged_id.'", 	trans_updated_date = "'.$today_date.'" WHERE prime_excel_format_id = "'. $prime_excel_format_id .'"';
				$info   = $this->db->query("CALL sp_a_run ('UPDATE','$upd_excel_qry')");
				$info->next_result();
			}
			$get_excel_name = 'SELECT prime_excel_format_id,excel_name FROM `cw_util_excel_format` WHERE excel_module_id = "'.$prime_module_id.'"  AND trans_status = 1';
			$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_excel_name')");
			$column_result = $column_info->result();
			$column_info->next_result();
			$excel_format_list     = $this->get_excel_fromat($prime_module_id);
			$excel_view            = $this->excel_view($prime_module_id);
			$excel_content_rslt    = json_decode($excel_view);
			$excel_content         = $excel_content_rslt->excel_content;
			echo json_encode(array('success' => TRUE,  'msg' => "Excel Format Saved Successfully!",'excel_content'=>$excel_content,'excel_format_list'=>$excel_format_list));
		}else
		if((int)$count === 1){
			$upd_excel_qry  = 'UPDATE cw_util_excel_format SET import_type = "'.$import_type.'", excel_name = "'.$excel_name.'", excel_table_name = "'.$excel_table_name.'", excel_column_name = "'.$excel_column_name.'",  exist_column_name = "'.$exist_column_name.'", trans_updated_by = "'.$logged_id.'", 	trans_updated_date = "'.$today_date.'" WHERE prime_excel_format_id = "'. $prime_excel_format_id .'"';
			$info   = $this->db->query("CALL sp_a_run ('UPDATE','$upd_excel_qry')");
			$info->next_result();
			$excel_format_list     = $this->get_excel_fromat($prime_module_id);
			$excel_view            = $this->excel_view($prime_module_id);
			$excel_content_rslt    = json_decode($excel_view);
			$excel_content         = $excel_content_rslt->excel_content;
			echo json_encode(array('success' => TRUE, 'msg' => "Update Successfully Your Format!",'excel_content'=>$excel_content,'excel_format_list'=>$excel_format_list));
		}
	}
	
	//GET EXCEL FORMAT
	public function get_excel_fromat($prime_module_id){
		$get_excel_name = 'SELECT prime_excel_format_id,excel_name FROM `cw_util_excel_format` WHERE excel_module_id = "'.$prime_module_id.'"  AND trans_status = 1';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_excel_name')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$excel_format_list     = array();
		$excel_format_list[] = "<option value=''>-- Select Format --</option>";
		foreach($column_result as $column){
			$column_value     = $column->excel_name;
			$key              = $column->prime_excel_format_id;
			$excel_format_list[] = "<option value='$key'>$column_value</option>";
		}
		return $excel_format_list;
	}
	
	//Bottom Table View Settings
	public function excel_view($prime_module_id){ /*=== UDY REVIEW DONE ===*/
		if(!$prime_module_id){
			return json_encode(array('success' => false,'msg' => "Invalid module information"));
		}
		$excel_view_qry   = 'SELECT prime_excel_format_id,excel_name FROM cw_util_excel_format WHERE excel_module_id = "'. $prime_module_id .'"  and trans_status = 1';
		$excel_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$excel_view_qry')");
		$excel_tab_result = $excel_tab_info->result();
		$excel_tab_info->next_result();	
			
		$tr_line = "";
		foreach($excel_tab_result as $rslt){
			$prime_excel_format_id = $rslt->prime_excel_format_id;
			$excel_name            = $rslt->excel_name;
			$excel_name    = ucwords(str_replace("_"," ",$excel_name));
			$tr_line .= "<tr>
							<td>$excel_name</td>
							<td><a class='btn btn-xs btn-edit' onclick=get_excel_info('$prime_excel_format_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td><a class='btn btn-xs btn-danger' onclick=get_delete_info('$prime_excel_format_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$excel_content = "<table class='table table-bordered table-stripted'>
							<tr class='inline_head'>
								<th>Excel Name</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		
		return json_encode(array('success' => TRUE,'excel_content' => $excel_content));
	}
	//Edit call function no needed module id
	public function get_excel_info(){ /*=== UDY REVIEW DONE ===*/
		$logged_id                  = $this->session->userdata('logged_id');
		$prime_excel_format_id   = $this->input->post('prime_excel_format_id');
		$excel_view_qry   = 'SELECT * FROM cw_util_excel_format WHERE prime_excel_format_id = "'.$prime_excel_format_id.'" AND trans_status = 1';
		$excel_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$excel_view_qry')");
		$result = $excel_tab_info->result();
		$excel_tab_info->next_result();
		echo json_encode(array('success' => TRUE,'excel_info' => $result[0]));
	}
	//Delete call function module id for excel view settings
	public function get_delete_info(){/*=== UDY REVIEW DONE ===*/
		$today_date = date("Y-m-d h:i:s");
		$logged_id                  = $this->session->userdata('logged_id');
		$prime_excel_format_id   = $this->input->post('prime_excel_format_id');
		$prime_module_id         = $this->input->post('excel_module_id');
		$del_excel_qry  = 'UPDATE cw_util_excel_format SET trans_deleted_by = "'.$logged_id.'", trans_deleted_date = "'.$today_date.'", trans_status = "0" WHERE prime_excel_format_id = "'. $prime_excel_format_id .'"';
		$excel_info   = $this->db->query("CALL sp_a_run ('UPDATE','$del_excel_qry')");
		$excel_info->next_result();
		$excel_view            = $this->excel_view($prime_module_id);
		$excel_content_rslt    = json_decode($excel_view);
		$excel_content         = $excel_content_rslt->excel_content;
		$excel_format_list     = $this->get_excel_fromat($prime_module_id);
		echo json_encode(array('success' => TRUE,  'msg' => "Deleted Your Excel Format!",'excel_content'=>$excel_content,'excel_format_list'=>$excel_format_list));
	}
	
	/* ==============================================================*/
	/* ============== EXCEL FORMAT  OPEARTION - END =================*/
	/* ==============================================================*/
	
	
	/* ==============================================================*/
	/* ================== EXCEL MAPPING OPEARTION -  START ==========*/
	/* ==============================================================*/
	
	public function format_mapping(){ /*=== UDY REVIEW DONE ===*/
		$excel_format_id     = $this->input->post('excel_format');
		$excel_view_qry      = 'SELECT * FROM cw_util_excel_format WHERE prime_excel_format_id = "'.$excel_format_id.'" AND trans_status = 1';
		$excel_tab_info      = $this->db->query("CALL sp_a_run ('SELECT','$excel_view_qry')");
		$result              = $excel_tab_info->result();
		$excel_tab_info->next_result();
		$prime_excel_format_id = $result[0]->prime_excel_format_id;
		$excel_module_id       = $result[0]->excel_module_id;
		$tab_col               = explode(",",$result[0]->excel_column_name);
		$excel_cell_value      = $this->get_excel_value(100);
		$excel_format          = form_input(array( 'name' =>"prime_excel_id",'class' => 'form-control input-sm','value' =>$prime_excel_format_id,'type'=>'Hidden')); 
		$excel_line_module_id  = form_input(array( 'name' =>"excel_line_module_id",'class' => 'form-control input-sm','value' =>$excel_module_id,'type'=>'Hidden'));
		foreach($tab_col as $name){
			$cell_value_query  = 'SELECT * FROM cw_util_excel_format_line  WHERE prime_excel_format_id  = "'.$prime_excel_format_id.'" and excel_line_module_id = "'.$excel_module_id.'" AND excel_line_column_name = "'.$name.'"';
			$cell_value_info   = $this->db->query("CALL sp_a_run ('SELECT','$cell_value_query')");
			$cell_value_result = $cell_value_info->result();
			$cell_value_info->next_result();
			$prime_excel_format_line_id = 0;
			$excel_line_value           = "";
			$view_name                  = "";
			if($cell_value_result){
				$prime_excel_format_line_id = $cell_value_result[0]->prime_excel_format_line_id;
				$excel_line_value           = $cell_value_result[0]->excel_line_value;
				$excel_line_module_id       = $cell_value_result[0]->excel_line_module_id;
				$excel_line_column_name     = $cell_value_result[0]->excel_line_column_name;
				if($excel_line_module_id === 'transactions' || $excel_line_module_id === 'increment' || $excel_line_module_id === 'monthly_input'){
					$excel_line_module_id = 'employees';
				}
				$get_view_name_query        = 'SELECT view_name FROM `cw_form_setting` WHERE prime_module_id = "'.$excel_line_module_id.'" and label_name = "'.$excel_line_column_name.'" and trans_status = 1';
				$column_info                = $this->db->query("CALL sp_a_run ('SELECT','$get_view_name_query')");
				$column_result              = $column_info->row();
				$column_info->next_result();
				$view_name                  = $column_result->view_name;
			}
			if(empty($view_name)){
				$view_name  = ucwords(str_replace("_"," ",$name));
			}
			$line_id     = form_input(array( 'name' =>"prime_excel_format_line_id[]",'class' => 'form-control input-sm','value' =>$prime_excel_format_line_id,'type'=>'Hidden'));
			$column_name = form_input(array( 'name' =>"excel_line_column_name[]",'class' => 'form-control input-sm','value' =>$name,'type'=>'Hidden'));
			$excel_cell_input = form_dropdown(array('onchange = map_check(this); name' =>"excel_line_value[]",'class' => 'form-control input-sm map_check'), $excel_cell_value,$excel_line_value);
			$tr_line .= "<tr>
							<td>$column_name $line_id $view_name $excel_line_module_id</td>
							<td>$excel_cell_input</td>
						</tr>";
		}
		
		$mapping_screen = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th>Table Column</th>
										<th>Excel Column</th>
									</tr>
									$tr_line
							</table>";

		$mapping_form_details = "<div style='padding:8px;'>
									$excel_format
									$mapping_screen
								</div>
								<div style='text-align:right;padding: 20px 15px;padding-top:0px;'>
									<button class='btn btn-primary btn-sm' id='save_map_submit'>Submit</button>
								</div>";
								
		echo json_encode(array('success' => TRUE,'mapping_form_details' => $mapping_form_details));
	}
	
	/* ==============================================================*/
	/* ================== SAVE MAPPING OPEARTION -  START ===========*/
	/* ==============================================================*/
	
	public function save_map(){
		$logged_id  = $this->session->userdata('logged_id');
		$today_date = date("Y-m-d h:i:s");
		$prime_excel_format_line_id   = $this->input->post('prime_excel_format_line_id[]');
		$prime_excel_format_id        = $this->input->post('prime_excel_id');
		$excel_line_module_id         = $this->input->post('excel_line_module_id');
		$excel_line_column_name       = $this->input->post('excel_line_column_name[]');
		$excel_line_value             = $this->input->post('excel_line_value[]');
		
		$col_count  = count($excel_line_column_name);
		$excel_count_qry = 'select count(*) as count_rslt FROM `cw_util_excel_format_line` WHERE prime_excel_format_id = "'.$prime_excel_format_id.'" and trans_status = 1';
		$excel_count_data   = $this->db->query("CALL sp_a_run ('SELECT','$excel_count_qry')");
		$excel_count_result = $excel_count_data->result();
		$excel_count_data->next_result();
		$tot_count = $excel_count_result[0]->count_rslt;
		if($tot_count){
			$delete_query = 'UPDATE cw_util_excel_format_line SET trans_status = 0,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$today_date.'" WHERE prime_excel_format_id = "'.$prime_excel_format_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$delete_query')");
		}
		
		$count = 0;
		for($i=1;$i<= $col_count;$i++){
			$prime_excel_format_line_id_val     = $prime_excel_format_line_id[$count];
			$excel_line_column_name_val         = $excel_line_column_name[$count];
			$excel_line_value_val               = $excel_line_value[$count];					
			if((int)$prime_excel_format_line_id_val === 0){
				$for_map_query = 'insert into cw_util_excel_format_line (prime_excel_format_id,excel_line_module_id,excel_line_column_name,excel_line_value,trans_created_by,trans_created_date) value ("'.$prime_excel_format_id.'","'.$excel_line_module_id.'","'.$excel_line_column_name_val.'","'.$excel_line_value_val.'","'.$logged_id.'","'.$today_date.'")';
			}else{
				$for_map_query = 'UPDATE cw_util_excel_format_line SET trans_status= 1, excel_line_value = "'.$excel_line_value_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_excel_format_line_id = "'.$prime_excel_format_line_id_val.'"';
			}
			$this->db->query("CALL sp_a_run ('RUN','$for_map_query')");
			$count++;
		}
		
		echo json_encode(array('success' => TRUE,'msg' =>"Mapping successfully Added"));
	}
	
	/* ==============================================================*/
	/* ================== SAVE MAPPING OPEARTION -  END =============*/
	/* ==============================================================*/
	
	public function cancel_value(){
		$prime_module_id        = $this->input->post('excel_module_id');
		$table_prime            = "cw_".$prime_module_id; 
		$table_cf               = "cw_".$prime_module_id."_cf";
		$table_mand_list        = array($table_prime,$table_cf);
		
		$get_mandatory_colums = 'select prime_form_id,prime_module_id,label_name from `cw_form_setting` inner join cw_form_view_setting on cw_form_view_setting.prime_form_view_id = input_for where prime_module_id = "'.$prime_module_id.'" and mandatory_field = 1 and input_view_type !=3 and form_view_show = 1';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_mandatory_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$col_mandatory_list  = array();
		foreach($column_result as $column){
			$column_value = $column->label_name;
			$column_name  = strtolower(str_replace(" ","_",$column_value));
			$col_mandatory_list[] = $column_name;
		}
		echo json_encode(array('success' => TRUE,'table_mand_list' =>$table_mand_list, 'col_mandatory_list' => $col_mandatory_list));
	}
	//Excel ABC Generator Function
	public function get_excel_value($tot_cell){
		$excel = array(''=>'--- Excel cell value ---');
		if((int)$tot_cell > 0){
			for($i=0;$i<=$tot_cell;$i++){
				$letter = $this->getNameFromNumber($i);
				$excel[$letter] = $letter;
			}
		}
		return $excel;
	}
	
	public function getNameFromNumber($num) {
		$numeric = $num % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval($num / 26);
		if ($num2 > 0) {
			return $this->getNameFromNumber($num2 - 1) . $letter;
		} else {
			return $letter;
		}
	}
	
	//Get Columns Data
	public function get_columns_data(){
		$inc_column_qry = 'select prime_form_id,label_name,view_name from cw_form_setting where prime_module_id = "employees" and increment_check = 1 and trans_status = 1';
		$inc_column_data   = $this->db->query("CALL sp_a_run ('SELECT','$inc_column_qry')");
		$inc_column_result = $inc_column_data->result();
		$inc_column_data->next_result();
		foreach($inc_column_result as $column){
			$label_name = $column->label_name;
			$view_name  = $column->view_name;	
			$excel_cell_value      = $this->get_excel_value(100);
			$column_name = form_input(array( 'name' =>"excel_line_column_name[]",'class' => 'form-control input-sm','value' =>$label_name,'type'=>'Hidden'));
			$excel_cell_input = form_dropdown(array('onchange = map_check(this); name' =>$label_name,'id'=>'excel_line_value[]','class' => 'form-control input-sm map_check'), $excel_cell_value,$excel_line_value);
			$tr_line .= "<tr>
							<td>$column_name $view_name</td>
							<td>$excel_cell_input</td>
						</tr>";
		}
		echo "<table class='table table-bordered table-stripted'>
						<tr class='inline_head'>
							<th>Label Name</th>
							<th>Excel Column</th>
						</tr>
						$tr_line
				</table>";
	}
	
	//Increment Template Name setting start -- 01JULY2019
/*	public function save_template(){
		$module_id         = $this->input->post('module_id');
		$temp_setting_id   = $this->input->post('temp_setting_id');
		$template_name     = $this->input->post('template_name');
		$logged_id         = $this->session->userdata('logged_id');
		$create_date       = date('Y-m-d H:i:s');
		if((int)$temp_setting_id === 0){
			$exist_qry = 'select * from cw_inc_temp_setting where template_name = "'.$template_name.'" and module_id = "'.$module_id.'" and trans_status = 1';
			$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
			$exist_result = $exist_data->result();
			$exist_data->next_result();
			$exist_rows = $exist_data->num_rows();
			if((int)$exist_rows === 0){
				$insert_query = 'insert into cw_inc_temp_setting(module_id,template_name,trans_created_by,trans_created_date) value ("'.$module_id.'","'.$template_name.'","'.$logged_id.'","'.$create_date.'")';
				$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$insert_query')");
				$insert_result = $insert_info->result();
				$insert_info->next_result();
				$template_content_list = $this->template_table_view($module_id);
				$template_content_list = json_decode($template_content_list);
				$template_content      = $template_content_list->template_content;
				$template_format       = $this->get_template_format($module_id);
				echo json_encode(array('success' => TRUE,  'msg' => "Increment template Saved Successfully!",'template_content'=>$template_content,'template_format' => $template_format));
			}else{
				echo json_encode(array('success' => False,  'msg' => "Already template Exits!"));
			}
		}else{
			$exist_qry = 'select * from cw_inc_temp_setting where template_name = "'.$template_name.'" and module_id = "'.$module_id.'" and trans_status = 1';
			$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
			$exist_result = $exist_data->result();
			$exist_data->next_result();
			$exist_rows = $exist_data->num_rows();
			if((int)$exist_rows === 0){
				$upd_qry  = 'UPDATE  cw_inc_temp_setting SET template_name = "'.$template_name.'",module_id = "'.$module_id.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$create_date.'" where prime_inc_temp_setting_id = "'.$temp_setting_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$template_content_list = $this->template_table_view($module_id);
				$template_content_list = json_decode($template_content_list);
				$template_content      = $template_content_list->template_content;
				$template_format       = $this->get_template_format($module_id);
				echo json_encode(array('success' => TRUE,  'msg' => "Updated Successfully!",'template_content'=>$template_content,'template_format' => $template_format));
			}else{
				echo json_encode(array('success' => False,  'msg' => "Already template Exits!"));
			}
		}
	}*/
	
	//GET TEMPLATE FORMAT
/*	public function get_template_format($prime_module_id){
		$temp_name_qry   = 'select * from `cw_inc_temp_setting` where trans_status = 1 and module_id = "'.$prime_module_id.'"';
		$temp_name_info    = $this->db->query("CALL sp_a_run ('SELECT','$temp_name_qry')");
		$temp_name_result = $temp_name_info->result();
		$temp_name_info->next_result();
		$temp_name_list[] = "<option value=''>---- Select Template ----</option>";
		foreach($temp_name_result as $temp){
			$temp_setting_id   = $temp->prime_inc_temp_setting_id;
			$template_name     = $temp->template_name;
			$temp_name_list[] = "<option value='$temp_setting_id'>$template_name</option>";
		}
		return $temp_name_list;
	}*/
	
/*	public function template_table_view($module_id){
		$template_view_qry   = 'SELECT * FROM cw_inc_temp_setting WHERE trans_status = 1 and module_id = "'.$module_id.'"';
		$template_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$template_view_qry')");
		$template_tab_result = $template_tab_info->result();
		$template_tab_info->next_result();	
		$template_content = "";	
		$tr_line = "";
		foreach($template_tab_result as $rslt){
			$temp_setting_id  = $rslt->prime_inc_temp_setting_id;
			$module_id        = $rslt->module_id;
			$template_name    = $rslt->template_name;
			$tr_line .= "<tr>
							<td>$template_name</td>
							<td><a class='btn btn-xs btn-edit' onclick=get_template_edit_info('$temp_setting_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td><a class='btn btn-xs btn-danger' onclick=get_template_delete_info('$temp_setting_id','$module_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$template_content = "<table class='table table-bordered table-stripted'>
							<tr class='inline_head'>
								<th>Template Name</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		
		return json_encode(array('success' => TRUE,'template_content' => $template_content));
	}*/
	
/*	public function get_template_edit_info(){
		$temp_setting_id    = $this->input->post('temp_setting_id');
		$template_qry  = 'select * from `cw_inc_temp_setting` where prime_inc_temp_setting_id = "'.$temp_setting_id.'"';
		$template_data = $this->db->query("CALL sp_a_run ('SELECT','$template_qry')");
		$template_result = $template_data->result();
		$template_data->next_result();
		echo json_encode(array('success' => TRUE, 'template_result' => $template_result[0]));
	}*/
	
	/*public function get_template_delete_info(){
		$temp_setting_id  = $this->input->post('temp_setting_id');
		$module_id        = $this->input->post('module_id');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d H:i:s");
		$remove_qry  = 'UPDATE cw_inc_temp_setting SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_inc_temp_setting_id = "'.$temp_setting_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$template_content_list = $this->template_table_view($module_id);
		$template_content_list = json_decode($template_content_list);
		$template_content      = $template_content_list->template_content;
		$template_format       = $this->get_template_format($module_id);
		echo json_encode(array('success' => true,'message'=>'Template Name Remove Successfully !!!','template_content'=>$template_content,'template_format' => $template_format));
	}*/
	//Increment Template Name setting end --01JULY2019
	
	//Increment Template Excel Mapping setting start --01JULY2019-
	public function save_increment_temp(){
		$temp_name              = $this->input->post('temp_name');
		$category               = $this->input->post('category');
		$employee_code_column   = $this->input->post('employee_code_column');
		$effective_date_column  = $this->input->post('effective_date_column');
		$before_days_column     = $this->input->post('before_days_column');
		$after_days_column      = $this->input->post('after_days_column');
		$excel_line_column_name = $this->input->post('excel_line_column_name[]');
		$logged_id              = $this->session->userdata('logged_id');
		$create_date            = date('Y-m-d H:i:s');
		
		$exist_qry = 'select * from cw_increment_template where temp_name = "'.$temp_name.'"  and trans_status = 1';
		$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
		$exist_result = $exist_data->result();
		$exist_data->next_result();
		$exist_rows = $exist_data->num_rows();
		if((int)$exist_rows === 0){
			foreach($excel_line_column_name as $col_key => $col_value){
				$excel_column = $this->input->post("$col_value");
				$insert_query = 'insert into cw_increment_template(temp_name,category,employee_code,effective_date,before_day,after_day,column_name,column_map,trans_created_by,trans_created_date) value ("'.$temp_name.'","'.$category.'","'.$employee_code_column.'","'.$effective_date_column.'","'.$before_days_column.'","'.$after_days_column.'","'.$col_value.'","'.$excel_column.'","'.$logged_id.'","'.$create_date.'")';
				$insert_info   = $this->db->query("CALL sp_a_run ('INSERT','$insert_query')");
				$insert_result = $insert_info->result();
				$insert_info->next_result();
			}
			echo json_encode(array('success' => TRUE,  'msg' => "Mapping successfully Added!"));
		}else{
			foreach($excel_line_column_name as $col_key => $col_value){
				$excel_column = $this->input->post("$col_value");
				$upd_query = 'UPDATE cw_increment_template SET category = "'.$category.'",employee_code="'.$employee_code_column.'",effective_date = "'.$effective_date_column.'",before_day = "'.$before_days_column.'",after_day = "'.$after_days_column.'",column_map = "'.$excel_column.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.date('Y-m-d H:i:s').'" where temp_name = "'.$temp_name.'"  and column_name = "'.$col_value.'" and trans_status = 1';
				$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
			}
			echo json_encode(array('success' => TRUE,  'msg' => "Template is updated!"));
		}
	}
	
	public function check_template(){
		$temp_name = $this->input->post('temp_name');
		$exist_qry = 'select * from cw_increment_template where temp_name = "'.$temp_name.'" and trans_status = 1';
		$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
		$exist_result = $exist_data->result();
		$exist_data->next_result();
		$exist_rows = $exist_data->num_rows();
		if((int)$exist_rows === 0){
			echo json_encode(array('success' => TRUE,  'msg' => "Create Mapping"));
		}else{
			$mapping_list = array();
			$select_template_qry = 'select employee_code,category,effective_date,before_day,after_day,GROUP_CONCAT(column_name) as column_name, GROUP_CONCAT(column_map) as column_map from cw_increment_template where temp_name = "'.$temp_name.'" and trans_status = 1';
			$select_template_data   = $this->db->query("CALL sp_a_run ('SELECT','$select_template_qry')");
			$select_template_result = $select_template_data->result();
			$select_template_data->next_result();
			$select_template_rows = $select_template_data->num_rows();
			$employee_code_column   = $select_template_result[0]->employee_code;
			$category               = $select_template_result[0]->category;
			$effective_date_column  = $select_template_result[0]->effective_date;
			$before_day_column      = $select_template_result[0]->before_day;
			$after_day_column       = $select_template_result[0]->after_day;
			$column_name            = explode(",",$select_template_result[0]->column_name);
			$column_map             = explode(",",$select_template_result[0]->column_map);
			$column_value           = array_combine($column_name,$column_map);
			$mapping_list     = array('category'=>$category,'employee_code_column'=>$employee_code_column,'effective_date_column'=>$effective_date_column,'before_day_column'=>$before_day_column,'after_day_column'=>$after_day_column);
			$result_map = array_merge($mapping_list,$column_value);
			echo json_encode(array('success' => FALSE,  'msg' => "Update Mapping",'template_list'=>$result_map,'column_value'=>$column_value));
		}
	}
	
	public function get_columns($table_in){
		$module_name    = str_replace("cw_","",$table_in);
		$get_colums = 'select `table_name`,`column_name`  from `information_schema`.`columns`  where `table_schema`="'.$this->config->item("db_name").'" and `table_name` in ('.$table_in.')';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$table_column[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_value  = $column->table_name;
			$column_value = $column->column_name;
			$view_name_qry    = 'select view_name from cw_form_setting where prime_module_id in ('.$module_name.') and label_name = "'.$column_value.'"  and trans_status = "1"';
			$view_name_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_name_qry')");
			$view_name_result = $view_name_data->result();
			$view_name_data->next_result();
			$column_name = strtoupper($view_name_result[0]->view_name);
			$table_name  = substr((ucwords(str_replace("_"," ",$table_value))),3);
			$table_name  = strtoupper($table_name);
			if($column_name){
				$table_column[$column_value] = $table_name . " - ". $column_name;
			}
		}
		return $table_column;
	}
	
	
	/* ==============================================================*/
	/* ================== DECLARATION IMPORT - START ================*/
	/* ==============================================================*/
	
	//section and subsection details ---- 06SEP2019
	public function get_declation_entry(){
		$tax_section_qry  = 'select cw_tax_section.tax_section,tax_act_details,tax_subsection_column from cw_tax_section inner join cw_tax_sub_section on cw_tax_sub_section.tax_section =cw_tax_section.prime_tax_section_id where cw_tax_sub_section.trans_status = 1 and ((cw_tax_sub_section.tax_section = 1 AND bill_required = 1) OR (cw_tax_sub_section.tax_section != 1 or prime_tax_sub_section_id IN (1,5))) and prime_tax_sub_section_id not in (select tax_sub_section from cw_section_matching where cw_section_matching.trans_status = 1) order by cw_tax_section.tax_order';
		$tax_section_info   = $this->db->query("CALL sp_a_run ('SELECT','$tax_section_qry')");
		$tax_section_result = $tax_section_info->result();
		$tax_section_info->next_result();
		$tr_line = "";
		foreach($tax_section_result as $tax_rslt){
				$subsec_column_name   =  $tax_rslt->tax_subsection_column;
				$tax_section_head     =  $tax_rslt->tax_section;
				$tax_subsection_head  =  $tax_rslt->tax_act_details;
				$excel_cell_value      = $this->get_excel_value(100);
				
				$house_column_name = form_input(array( 'name' =>"excel_line_column_name[]",'class' => 'form-control input-sm','value' =>'tax_house_rent','type'=>'Hidden'));
				$childran_column_name = form_input(array( 'name' =>"excel_line_column_name[]",'class' => 'form-control input-sm','value' =>'childran_elig','type'=>'Hidden'));
				
				$tax_subsection_head_input = form_input(array( 'name' =>"excel_line_column_name[]",'class' => 'form-control input-sm','value' =>$subsec_column_name,'type'=>'Hidden'));
				
				$excel_cell_input = form_dropdown(array('onchange = map_check(this); name' =>$subsec_column_name,'id'=>'excel_line_value[]','class' => 'form-control input-sm map_check'), $excel_cell_value,$excel_line_value);
				
				$tax_house_rent_input = form_dropdown(array('onchange = map_check(this); name' =>'tax_house_rent','id'=>'excel_line_value[]','class' => 'form-control input-sm map_check'), $excel_cell_value,$excel_line_value);
				
				$childran_elig_input = form_dropdown(array('onchange = map_check(this); name' =>'childran_elig','id'=>'excel_line_value[]','class' => 'form-control input-sm map_check'), $excel_cell_value,$excel_line_value);
				
				$tr_line .= "<tr><td>".$tax_section_head."</td><td>".$tax_subsection_head."</td><td>$tax_subsection_head_input $excel_cell_input</td></tr>";
		}
		$table_info = "<table class='table table-bordered'>
								<thead>
									<tr>
										<th>Tax Section</th>
										<th>Tax Subsection</th>
										<th>Matching Column</th>
									</tr>
								</thead>
								<tbody>
								<tr>
									<td>$house_column_name House Rent Paid (Annual)</td>
									<td></td>
									<td>$tax_house_rent_input</td>
								</tr>
								<tr>
									<td>$childran_column_name No of Children Eligible for Education</td>
									<td></td>
									<td>$childran_elig_input</td>
								</tr>
									$tr_line
								</tbody>
						   </table>";
		echo $table_info;
	}
	
	//Declaration Entry Save start --07SEP2019
	public function save_dec_entry(){
		$temp_name              = $this->input->post('temp_name');
		$category               = $this->input->post('category');
		$employee_code          = $this->input->post('employee_code');
		$excel_line_column_name = $this->input->post('excel_line_column_name[]');
		$logged_id              = $this->session->userdata('logged_id');
		$create_date            = date('Y-m-d H:i:s');
		
		$exist_qry = 'select * from cw_declaration_template where temp_name = "'.$temp_name.'"  and trans_status = 1';
		$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
		$exist_result = $exist_data->result();
		$exist_data->next_result();
		$exist_rows = $exist_data->num_rows();
		if((int)$exist_rows === 0){
			foreach($excel_line_column_name as $col_key => $col_value){
				$excel_column = $this->input->post("$col_value");
				$insert_dec_query = 'insert into cw_declaration_template(temp_name,category,employee_code,column_name,column_map,trans_created_by,trans_created_date) value ("'.$temp_name.'","'.$category.'","'.$employee_code.'","'.$col_value.'","'.$excel_column.'","'.$logged_id.'","'.$create_date.'")';
				$insert_dec_info   = $this->db->query("CALL sp_a_run ('INSERT','$insert_dec_query')");
				$insert_dec_result = $insert_dec_info->result();
				$insert_dec_info->next_result();
			}
			echo json_encode(array('success' => TRUE,  'msg' => "Mapping successfully Added!"));
		}else{
			foreach($excel_line_column_name as $col_key => $col_value){
				$excel_column = $this->input->post("$col_value");
				$upd_query = 'UPDATE cw_declaration_template SET category = "'.$category.'",employee_code="'.$employee_code.'",column_map = "'.$excel_column.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.date('Y-m-d H:i:s').'" where temp_name = "'.$temp_name.'"  and column_name = "'.$col_value.'" and trans_status = 1';
				$this->db->query("CALL sp_a_run ('RUN','$upd_query')");
			}
			echo json_encode(array('success' => TRUE,  'msg' => "Your template is updated!"));
		}
	}
	//Declaration Entry Save end --07SEP2019
	public function check_dec_template(){
		$temp_name = $this->input->post('temp_name');
		$exist_qry = 'select * from cw_declaration_template where temp_name = "'.$temp_name.'" and trans_status = 1';
		$exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
		$exist_result = $exist_data->result();
		$exist_data->next_result();
		$exist_rows = $exist_data->num_rows();
		if((int)$exist_rows === 0){
			echo json_encode(array('success' => TRUE,  'msg' => "Create Mapping"));
		}else{
			$mapping_list = array();
			$select_template_qry = 'select employee_code,category,GROUP_CONCAT(column_name) as column_name, GROUP_CONCAT(column_map) as column_map from cw_declaration_template where temp_name = "'.$temp_name.'" and trans_status = 1';
			$select_template_data   = $this->db->query("CALL sp_a_run ('SELECT','$select_template_qry')");
			$select_template_result = $select_template_data->result();
			$select_template_data->next_result();
			$select_template_rows  = $select_template_data->num_rows();
			$employee_code         = $select_template_result[0]->employee_code;
			$category              = $select_template_result[0]->category;
			$column_name           = explode(",",$select_template_result[0]->column_name);
			$column_map            = explode(",",$select_template_result[0]->column_map);
			$column_name_value     = array_combine($column_name,$column_map);
			$mapping_list          = array('category'=>$category,'employee_code'=>$employee_code);
			$result_map = array_merge($mapping_list,$column_name_value);
			echo json_encode(array('success' => FALSE,  'msg' => "Update Mapping",'template_list'=>$result_map,'column_name_value'=>$column_name_value));
		}
	}
	/* ==============================================================*/
	/* ================== DECLARATION IMPORT - END   ================*/
	/* ==============================================================*/
}
?>