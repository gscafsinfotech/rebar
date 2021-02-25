<?php 
/**********************************************************
	   Filename: Report Setting
	Description: Report Setting creating new report to display in under report main menu.
		 Author: Jagufer sathik
	 Created on: ‎13 March ‎2019
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
class Report_setting  extends Secure_Controller{

	public function __construct(){
		parent::__construct('report_setting');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_report_setting_headers());
		$this->load->view('report_setting/manage',$data);
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
			$sort = "report_name";
		}
		if(!$order){
			$order = "asc";
		}
		
		$this->db->select('prime_report_setting_id,report_name');
		$this->db->from('report_setting');
		
		if($search){
			$this->db->group_start();
				$this->db->like('report_name',$search);
			$this->db->group_end();	
		}
		
		$this->db->where('report_setting.trans_status',1);
		$this->db->order_by($sort,$order);
		if($rows>0){
			$this->db->limit($rows, $limit_from);
		}
		$report_info = $this->db->get();
		$report_rslt = $report_info->result();
		$data_rows = array();
		foreach ($report_info->result() as $report_setting){
			$data_rows[]=get_report_setting_datarows($report_setting,$this);
		}
		$data_rows=$this->xss_clean($data_rows);
		
		$num_rows = $report_info->num_rows();
		echo json_encode(array('total'=>$num_rows,'rows'=>$data_rows));
	}
	
	//MODULE VIEW OPEARTION
	public function view($view_id =-1){
		$data['view_id'] = $view_id;
		$report_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM cw_report_setting where trans_status = 1 and prime_report_setting_id = $view_id')");
		$report_result = $report_data->result();
		$report_data->next_result();
		$data['report_data']  = $report_result[0];
		
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$report_for_list[""] = "---- Report For ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$report_for_list[$role_id] = $category_name;
		}
		$data['report_for_list']  = $report_for_list;
		
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','SHOW TABLES')");
		$table_result = $table_info->result();
		$table_info->next_result();	
		
		$tab_array = array("cw_app_config","cw_form_bind_input","cw_form_condition_formula","cw_form_for_input","cw_form_setting","cw_form_table_cond_for","cw_form_table_search","cw_form_view_setting","cw_grants","cw_import","cw_main_menu","cw_payroll_formula","cw_payroll_function","cw_permissions","cw_print_block","cw_print_design","cw_print_info","cw_print_map","cw_print_table","cw_print_table_where","cw_sub_menu","cw_month_day","cw_statutory","cw_professional_tax","cw_professional_tax_tax_range","cw_sessions","cw_util_excel_format","cw_session_value","cw_util_excel_format_line","cw_modules","cw_report_setting","cw_salary_check","cw_state","cw_statutory_field","cw_statutory_function","cw_month","cw_category","cw_country","cw_report_table","cw_report_where","dailyunpunch","monthlyattdata");
		$table_list[""] = "---- Select Table ----";
		foreach($table_result as $table){
			$db_name         = "Tables_in_".$this->config->item("db_name");
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
		$table_info = $report_result[0]->table_info;
		$table_in   = '"'.str_replace(",",'","', $table_info).'"';
		$data['columns_list']       = $this->get_columns($table_in);
		//$data['view_columns_list']  = $this->get_view_name($table_in);
		//print_r($data['view_columns_list']);
		$data['date_columns_list'] = $this->get_date_columns($table_in);
		$data['join_list'] = $this->get_table_join($report_result[0]->prime_report_setting_id);
		
		$where_query  = 'SELECT * FROM cw_report_where WHERE  where_for_id = "'.$report_result[0]->prime_report_setting_id.'" and trans_status = 1';
		$where_info   = $this->db->query("CALL sp_a_run ('SELECT','$where_query')");
		$where_count  = (int)$where_info->num_rows();
		$where_result = $where_info->result();
		$where_info->next_result();
		$where_condition = trim($where_result[0]->where_condition);
		if(!$where_condition){
			$where_condition = "and";
		}
		$data['where_condition'] = $where_condition;
		
		$data['report_tab_view']    = $this->table_view($view_id);
		
		
		$add_column_content         = $this->add_column_table($view_id);
		$data['add_column_content'] = $add_column_content;
		
		$sum_column_query  = 'SELECT * FROM cw_report_table_view WHERE report_id = "'.$report_result[0]->prime_report_setting_id.'" and trans_status = 1';
		$sum_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$sum_column_query')");
		$sum_column_result = $sum_column_info->result();
		$sum_column_info->next_result();
		$sum_column_list[""] = "---- Select Column Name ----";
		foreach($sum_column_result as $sum_column){
			$table_column  = $sum_column->table_column;
			$module_column = "cw_".$sum_column->module_column;
			$pattern       = '/^cw_([a-z]+)\.\b/';
			$replacement   = '';
			$column_list   = preg_replace($pattern, $replacement, $table_column);
			$column_list   = (ucwords(str_replace("_"," ",$column_list)));
			$sum_column_list["$module_column.$table_column"] = $column_list;
		}
		$data['sum_column_list']    = $sum_column_list;
		$sum_column_content         = $this->total_column_table($view_id);
		$data['sum_column_content'] = $sum_column_content;
		
		
		$report_for       = explode(",",$report_result[0]->table_column);
		$prime_module_id  = '';
		$label_name       = '';
		foreach($report_for as $rslt){
			$results_data     = explode(".",$rslt);
			$prime_module_id .= "\"".str_replace("cw_","",$results_data[0])."\",";
			$label_name      .= "\"".$results_data[1]."\",";
		}
		$prime_module_id   = '('.rtrim($prime_module_id,",").')';
		$label_name        = '('.rtrim($label_name,",").')';
		
		$this->load->view("report_setting/form",$data);
	}
	
	public function report_save(){
		$prime_report_setting_id = (int)$this->input->post('prime_report_setting_id');		
		$report_name             = $this->input->post('report_name');		
		$report_for              = ltrim(implode(",",$this->input->post('report_for[]')),",");
		$table_info              = ltrim(implode(",",$this->input->post('table_info[]')),",");
		$table_column            = ltrim(implode(",",$this->input->post('table_column[]')),",");
		$group_column            = ltrim(implode(",",$this->input->post('group_column[]')),",");
		$date_filter             = $this->input->post('date_filter');
		$date_column             = ltrim(implode(",",$this->input->post('date_column[]')),",");
		$logged_id               = $this->session->userdata('logged_id');
		$date                    = date("Y-m-d h:i:s");
		$sub_tot_show            = $this->input->post('sub_tot_show');
		$sub_tot_show_val    = 0;
		if($sub_tot_show === "on"){
			$sub_tot_show_val = 1; 
		}
		if($prime_report_setting_id === 0){
			if(!$this->check_unique_field($report_name,(int)$prime_report_setting_id)){	
				$report_qry  = 'INSERT INTO cw_report_setting (report_name, report_for,table_info,table_column,group_column,date_filter,date_column,sub_tot_show,trans_created_by, trans_created_date) VALUES ("'.$report_name.'","'.$report_for.'","'.$table_info.'","'.$table_column.'","'.$group_column.'","'.$date_filter.'","'.$date_column.'","'.$sub_tot_show_val.'","'.$logged_id.'","'.$date.'")';
				$insert_info    = $this->db->query("CALL sp_a_run ('INSERT','$report_qry')");
				$insert_result  = $insert_info->result();
				$insert_id      = $insert_result[0]->ins_id;
				$insert_info->next_result();
				$this->save_table_view($insert_id);
				echo json_encode(array('success' => true, 'message' => "Report successfully added"));
			}else{
				echo json_encode(array('success' => false, 'message' => "Report Name Already Exists"));
			}
		}else{
			if(!$this->check_unique_field($report_name,$prime_report_setting_id)){	
				$upd_qry  = 'UPDATE cw_report_setting SET report_name = "'.$report_name.'",report_for = "'.$report_for.'",table_info = "'.$table_info.'",table_column = "'.$table_column.'",group_column = "'.$group_column.'",date_filter = "'.$date_filter.'",date_column = "'.$date_column.'",sub_tot_show = "'.$sub_tot_show_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_report_setting_id = "'.$prime_report_setting_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$this->save_table_view($prime_report_setting_id);
				echo json_encode(array('success' => true, 'message' => "Report successfully updated"));
			}else{
				echo json_encode(array('success' => false, 'message' => "Report Name Already Exists"));
			}
		}
	}
	
	//CHECK UNIQUE FEID(REPORT NAME) IN REPORT SETTING (SVK EDIT NEED REVIEW)
	public function check_unique_field($report_name,$prime_report_setting_id = -1){
		$is_exist_qry  = 'SELECT * FROM cw_report_setting where report_name = "'.$report_name.'" and trans_status = 1 ';
		if((int)$prime_report_setting_id > 0){
			$is_exist_qry .= " and prime_report_setting_id != $prime_report_setting_id";
		}
		$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
		$exist_count   = $is_exist_data->num_rows();
		$is_exist_data->next_result();
		if((int)$exist_count > 0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public function get_table_column(){
		$table_info   = ltrim(implode(",",$this->input->post('table_info')),",");
		$prime_in     = '"'.str_replace(",",'","', $table_info);
		$custom_in    = str_replace(",",'_cf","', $table_info).'_cf"';
		$table_in     = $prime_in.'","'.$custom_in;
		$table_column = $this->get_columns($table_in);
		echo json_encode(array('success' => true,'table_column'=>$table_column));
	}
	
	/*public function get_columns($table_in){
		$get_colums = 'select `table_name`,`column_name`  from `information_schema`.`columns`  where `table_schema`="'.$this->config->item("db_name").'" and `table_name` in ('.$table_in.')';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$table_column[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_value  = $column->TABLE_NAME;
			$column_value = $column->COLUMN_NAME;
			if(strpos($column_value, 'trans_') !== false) {
				//echo "UDY :: $table_value - $column_value<br/>"; // UDY CHECK FOR ALTER
			}else{
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$column_name  = ucwords(str_replace("_"," ",$column_value));
				$table_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;				
			}
		}
		return $table_column;
	}*/
	
	//date only not changed
	public function get_date_columns($table_in){
		$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_in.') and data_type in ("varchar","date")';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$table_column[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_value  = $column->TABLE_NAME;
			$column_value = $column->COLUMN_NAME;
			
			if(strpos($column_value, 'trans_') !== false) {
				//echo "UDY :: $table_value - $column_value<br/>"; // UDY CHECK FOR ALTER
			}else{
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$column_name  = ucwords(str_replace("_"," ",$column_value));
				$table_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;				
			}
		}
		return $table_column;
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
				$table_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;
			}
		}
		return $table_column;
	}
	
	/*public function get_date_columns($table_in){
		$module_name    = str_replace("cw_","",$table_in);
		$get_colums = 'select `table_name`,`column_name`  from `information_schema`.`columns`  where `table_schema`="'.$this->config->item("db_name").'" and `table_name` in ('.$table_in.') and data_type in ("varchar","date")';
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
				$table_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;
			}
		}
		return $table_column;
	}*/
	
	/* ==============================================================*/
	/* =================== COMMON OPEARTION - END ===================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================ TABLE JOIN OPEARTION - START ================*/
	/* ==============================================================*/
	
	public function get_table_join($prime_report_setting_id){
		$report_qry  = 'SELECT * FROM cw_report_setting where prime_report_setting_id = "'.$prime_report_setting_id.'" and trans_status = 1';
		$report_data = $this->db->query("CALL sp_a_run ('SELECT','$report_qry')");
		$report_rslt = $report_data->result();			
		$report_data->next_result();
		foreach($report_rslt as $rslt){
			$prime_report_setting_id  = $rslt->prime_report_setting_id;
			$table_info               = explode(",",$rslt->table_info);
			
			$table_list = array();
			$table_list[""] = "---- Select Table ----";
			foreach($table_info as $table_value){
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$table_list[$table_value] = $table_name;
			}
			
			$prime_in   = '"'.str_replace(",",'","', $rslt->table_info);
			$custom_in  = str_replace(",",'_cf","', $rslt->table_info).'_cf"';
			$table_in   = $prime_in.'","'.$custom_in;
			$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`= "'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_in.')';
			$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
			$column_result = $column_info->result();
			$column_info->next_result();
			$column_list = array();
			$column_list[""] = "---- Select Column ----";
			foreach($column_result as $column){
				$table_value  = $column->TABLE_NAME;
				$column_value = $column->COLUMN_NAME;				
				if(strpos($column_value, 'trans_') !== false) {
					//echo "UDY :: $table_value - $column_value<br/>"; // UDY CHECK FOR ALTER
				}else{
					$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
					$column_name  = ucwords(str_replace("_"," ",$column_value));
					$column_list[$table_value.".".$column_value] = $table_name . " - ". $column_name;					
				}
			}
			$join_array = array(""=>"--- Select join type ---","inner" => "inner","left" => "left","right" => "right");	
			$join_query  = 'SELECT * FROM cw_report_table  WHERE join_for = "'.$prime_report_setting_id.'" order by abs(line_sort)';
			$join_info   = $this->db->query("CALL sp_a_run ('SELECT','$join_query')");
			$join_result = $join_info->result();
			$join_info->next_result();
			
			$table_tr_line  = "";
			$table_count    = 0;
			$condition_table_count = count($table_info) - 1; //round(count($condition_table)/2);
			for($i=1;$i<= $condition_table_count;$i++){
				$prime_print_table_id = 0;
				$line_prime_table     = "";
				$line_prime_col       = "";
				$line_join_type       = "";
				$line_join_table      = "";
				$line_join_col        = "";
				
				if($join_result){
					$prime_report_table_id = $join_result[$table_count]->prime_report_table_id;
					$line_prime_table      = $join_result[$table_count]->line_prime_table;
					$line_prime_col        = $join_result[$table_count]->line_prime_col;
					$line_join_type        = $join_result[$table_count]->line_join_type;
					$line_join_table       = $join_result[$table_count]->line_join_table;
					$line_join_col         = $join_result[$table_count]->line_join_col;
				}
				
				$table_cond_for_id = form_input(array( 'name' =>"prime_report_table_id[]",'class' => 'form-control input-sm','value' =>$prime_report_table_id,'type'=>'Hidden'));
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
			$join_for  = form_input(array('name'=>'join_for','id'=>'join_for','class'=>'form-control input-sm','value'=>$prime_report_setting_id,'type'=>'Hidden'));
			$table_content = "$join_for
								<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th>Primary table</th>
										<th>Primary column</th>
										<th>Join type</th>
										<th>Join table</th>
										<th>Join primary column</th>
									</tr>
									$table_tr_line
								</table>
								<div style='text-align:right;padding:8px 0px;'>
									<button class='btn btn-primary btn-sm' id='save_join_table_btn'>Save</button>
								</div>";
		}
		return $table_content;
	}
	
	public function save_join_table(){
		$join_for               = $this->input->post('join_for');
		$prime_report_table_id  = $this->input->post('prime_report_table_id');
		$prime_print_table_id   = $this->input->post('prime_print_table_id[]');
		$line_prime_table       = $this->input->post('line_prime_table[]');
		$line_prime_col         = $this->input->post('line_prime_col[]');
		$line_join_type         = $this->input->post('line_join_type[]');
		$line_join_table        = $this->input->post('line_join_table[]');
		$line_join_col          = $this->input->post('line_join_col[]');
		
		$logged_id     = $this->session->userdata('logged_id');
		$today_date    = date("Y-m-d h:i:s");
		$tab_count     = 0;
		
		$remove_query = 'UPDATE cw_report_table SET trans_status = 0,trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE join_for = "'.$join_for.'"';
		$this->db->query("CALL sp_a_run ('RUN','$remove_query')");
		
		$table_count = count($line_prime_table);
		for($i=1;$i<= $table_count;$i++){
			$prime_report_table_id_val = $prime_report_table_id[$tab_count];
			$line_prime_table_val     = $line_prime_table[$tab_count];
			$line_prime_col_val       = $line_prime_col[$tab_count];
			$line_join_type_val       = $line_join_type[$tab_count];
			$line_join_table_val      = $line_join_table[$tab_count];
			$line_join_col_val        = $line_join_col[$tab_count];
			
			if((int)$prime_print_table_id_val === 0){
				$table_query = 'insert into cw_report_table (join_for,line_prime_table,line_prime_col,line_join_type,line_join_table,line_join_col,line_sort,trans_created_by,trans_created_date) value ("'.$join_for.'","'.$line_prime_table_val.'","'.$line_prime_col_val.'","'.$line_join_type_val.'","'.$line_join_table_val.'","'.$line_join_col_val.'","'.$i.'","'.$logged_id.'","'.$today_date.'")';
			}else{
				$table_query = 'UPDATE cw_report_table SET trans_status = 1, line_prime_table = "'.$line_prime_table_val.'",line_prime_col = "'.$line_prime_col_val.'",line_join_type = "'.$line_join_type_val.'",line_join_table = "'.$line_join_table_val.'",line_join_col = "'.$line_join_col_val.'",line_sort = "'.$i.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_report_table_id = "'.$prime_report_table_id_val.'"';
			}
			$this->db->query("CALL sp_a_run ('RUN','$table_query')");
			$tab_count++;
		}
		echo json_encode(array('success' => true, 'message'=>"Table Join successfully Updated"));
	}
	
	function save_table_where(){
		$where_for_id   = (int)$this->input->post('where_for_id');
		$where_condition  = trim($this->input->post('where_condition'));
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_report_where WHERE  where_for_id = "'.$where_for_id.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			$search_qry  = 'INSERT INTO cw_report_where (where_for_id,where_condition,trans_created_by, trans_created_date) VALUES ("'.$where_for_id.'","'.$where_condition.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
			echo json_encode(array('success' => true,'message'=>"Where added successfully !!!"));
		}else{
			$prime_report_where_id = (int)$exist_result[0]->prime_report_where_id;
			$upd_qry  = 'UPDATE  cw_report_where SET where_for_id = "'.$where_for_id.'",where_condition = "'.$where_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_report_where_id = "'.$prime_report_where_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			echo json_encode(array('success' => true,'message'=>"Where updated successfully !!!"));
		}
	}
	
	// PROVIDE PICKLIST AND SESSION VALUES
	function get_column_info(){
		$query_column     = $this->input->post('query_column');
		$label_name       = explode(".",$query_column);
		$where_module_id  = str_replace("cw_","",$label_name[0]);
		if($where_module_id === "transactions"){
			$where_module_id = "employees";
		}
		
		$get_colums_info = 'SELECT * FROM cw_form_setting WHERE  prime_module_id = "'.$where_module_id.'" and label_name = "'.$label_name[1].'"';
		$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
		$colums_result = $colums_info->result();
		$colums_info->next_result();
		
		$session_val_qry    = 'SELECT * FROM cw_session_value WHERE  trans_status = 1 order by abs(session_for)';
		$get_session_val    = $this->db->query("CALL sp_a_run ('SELECT','$session_val_qry')");
		$session_val_result = $get_session_val->result();
		$get_session_val->next_result();
		$session_list[""] = "--- Select Session Value ---";
		if($session_val_result){
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
		}
		if($colums_result){
			$field_type     = (int)$colums_result[0]->field_type;
			$pick_list_type = (int)$colums_result[0]->pick_list_type;
			$pick_list 	    = $colums_result[0]->pick_list;
			$pick_table 	= $colums_result[0]->pick_table;
							
			if(($field_type === 5) || ($field_type === 7)){
				if($pick_list_type === 1){
					$pick_colum_qry    = 'SELECT '.$pick_list.' FROM '.$pick_table.' WHERE  trans_status = 1';
					$get_pick_colum    = $this->db->query("CALL sp_a_run ('SELECT','$pick_colum_qry')");
					$pick_colum_result = $get_pick_colum->result();
					$get_pick_colum->next_result();
					if($pick_colum_result){
						$colum = explode(",",$pick_list);
						foreach($pick_colum_result as $col){
							$col_id    = $col->$colum[0];
							$col_value = $col->$colum[1];
							$pick_list_info[$col_id] = "$col_id - $col_value";
						}
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
						foreach($pick_colum_result as $col){
							$col_id    = $col->$id;
							$col_value = $col->$value;
							$pick_list_info[$col_id] = "$col_id - $col_value";
						}
					}
				}
				echo json_encode(array('success' => true,'type'=>'pick_list','msg'=>"Pick list value","pick_list"=>$pick_list_info,"session_list"=>$session_list));				
			}else{				
				echo json_encode(array('success' => true,'type'=>'session_list','msg'=>"Session list","session_list"=>$session_list));
			}
		}else{
			echo json_encode(array('success' => true,'type'=>'session_list','msg'=>"Session list","session_list"=>$session_list));
		}
	}
	/* ==============================================================*/
	/* ================= TABLE JOIN OPEARTION - END =================*/
	/* ==============================================================*/
	//MRJ 14MAR2019
	//TABLE VIEW START
	public function table_view($report_id){
		$get_colums_info = 'select cw_report_table_view.table_sort,cw_report_table_view.prime_report_table_view_id,cw_report_table_view.report_id,cw_form_setting.view_name as table_column from cw_report_table_view join cw_form_setting on cw_form_setting.label_name = cw_report_table_view.table_column WHERE  cw_report_table_view.report_id = "'.$report_id.'" and cw_report_table_view.trans_status = 1 group by label_name order by abs(cw_report_table_view.table_sort)';
		$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
		$colums_result = $colums_info->result();
		$colums_info->next_result();
		$input_th    = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field position</p>";
		$input_td    = "";
		$i = 1;
		$tr_line     = '';
		foreach($colums_result as $report){
			$sort_id          = $report->table_sort;
			$table_report_id  = $report->prime_report_table_view_id;
			$report_id        = $report->report_id;
			$table_column     = $report->table_column;
			$th_id            = "th_".$table_report_id;
			//$colum_name       = preg_replace('/(^[a-z0-9_]+)\./', '', $table_column);//regax for remove table name
			//$colum_name       = ucwords(str_replace("_"," ",$colum_name));
			$input_th       .=  "<th class='ui-state-default inner_th' id='$th_id'>
									$table_column
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
		$ul_li = "<table class='table table-hover table-striped' id='report_sortable'>
					<tbody>
						$tr_line
					</tbody>
				</table>";
		return json_encode(array('success'=>true,'table_content' => $ul_li,'report_id'=>$report_id,'table_report_id'=>$table_report_id));
	}
	
	//SAVE TABLE SORT VIEW /*SVK EDIT*/
	public function save_table_view($report_id){
		$logged_id    = $this->session->userdata('logged_id');
		$date         = date("Y-m-d H:i:s");
		$total_column = array();
		
		$get_colums_info = 'select * from cw_report_setting WHERE  prime_report_setting_id = "'.$report_id.'" and trans_status = 1';
		$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
		$colums_result = $colums_info->result();
		$colums_info->next_result();
		$report_name  = $colums_result[0]->report_name;
		$report_for   = $colums_result[0]->report_for;
		$table_column = $colums_result[0]->table_column;
		$table_column = explode(",",$table_column);
		$add_colums_info = 'select GROUP_CONCAT(add_name) as colum_name from cw_report_add_column WHERE report_id = "'.$report_id.'" and trans_status = 1';
		$add_colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$add_colums_info')");
		$add_colums_result = $add_colums_info->result();
		$add_colums_info->next_result();
		$new_column = $add_colums_result[0]->colum_name;
		if(!empty($new_column)){
			$new_column = str_replace(",",",cw_transactions.",$new_column);
			$new_column = "cw_transactions.".$new_column;
			$new_column = explode(",",$new_column);
			$total_column = array_merge($table_column,$new_column);
		}else{
			$total_column = $table_column;
		}
		
		$count = count($total_column);
		$j=1;
		$exits_count_qry = 'select GROUP_CONCAT(table_column) as colum_name from cw_report_table_view where report_id = "'.$report_id.'" and trans_status = 1';
		$exits_count_info   = $this->db->query("CALL sp_a_run ('SELECT','$exits_count_qry')");
		$exits_count_result = $exits_count_info->row();
		$exits_count_info->next_result();
		$exit_col  = $exits_count_result->colum_name;
		if(!empty($exit_col)){
			$exit_col = explode(",",$exit_col);
		}else{
			$exit_col = array();
		}
		$check_column = '';
		if(empty($exit_col)){
			for($i=0;$i<$count;$i++){
				$col_name      = explode(".",$total_column[$i]);
				$module_column = str_replace('cw_','',$col_name[0]);
				$col_name      = $col_name[1];
				$report_qry  = 'INSERT INTO cw_report_table_view (report_id,report_name, report_for,module_column,table_column,table_sort,trans_created_by, trans_created_date) VALUES ("'.$report_id.'","'.$report_name.'","'.$report_for.'","'.$module_column.'","'.$col_name.'","'.$j.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$report_qry')");
				$j++;
				$check_column .= "\"$col_name\",";
			}
		}else{
			for($i=0;$i<$count;$i++){
				$col_name      = explode(".",$total_column[$i]);
				$module_column = str_replace('cw_','',$col_name[0]);
				$col_name      = $col_name[1];
				if(!in_array($col_name, $exit_col)){
					$exits_count_qry = 'select count(*) as rlst_count from cw_report_table_view where report_id = "'.$report_id.'" and trans_status = 1';
					$exits_count_info   = $this->db->query("CALL sp_a_run ('SELECT','$exits_count_qry')");
					$exits_count_result = $exits_count_info->row();
					$exits_count_info->next_result();
					$rlst_count  = (int)$exits_count_result->rlst_count + 1;
					$report_qry  = 'INSERT INTO cw_report_table_view (report_id,report_name, report_for,module_column,table_column,table_sort,trans_created_by, trans_created_date) VALUES ("'.$report_id.'","'.$report_name.'","'.$report_for.'","'.$module_column.'","'.$col_name.'","'.$rlst_count.'","'.$logged_id.'","'.$date.'")';
					$this->db->query("CALL sp_a_run ('RUN','$report_qry')");
				}
				$check_column .= "\"$col_name\",";
			}
		}
		$check_column = rtrim($check_column,',');
		if($check_column){
			$upd_qry  = "DELETE FROM `cw_report_table_view` where report_id = \"$report_id\" and table_column NOT IN ($check_column)";
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			$upd_qry  = "ALTER TABLE cw_report_table_view AUTO_INCREMENT = $count";
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
		}
	}
	
	public function table_sort_update(){
		$table_idsInOrder   = $this->input->post('table_idsInOrder');
		$logged_id          = $this->session->userdata('logged_id');
		$date               = date("Y-m-d H:i:s");
		$sort_order = 0;
		foreach($table_idsInOrder as $order){
			if($order){
				$sort_order++;
				$table_id = str_replace("th_","",$order); //replace
				$upd_qry  = 'UPDATE cw_report_table_view SET table_sort = "'.$sort_order.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_report_table_view_id = "'.$table_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Column Name is successfully sorted."));
	}
	
	//Additional Column input
	public function save_add_column(){
		$add_column_id      = $this->input->post('add_column_id');
		$report_id          = $this->input->post('report_id');
		$add_name           = strtolower(str_replace(" ","_",$this->input->post('add_name')));
		$select_condition   = $this->input->post('select_condition');
		$logged_id          = $this->session->userdata('logged_id');
		$date               = date("Y-m-d H:i:s");
		if($report_id){
			if($add_name){
				$exit_qry = 'select * from cw_report_add_column WHERE report_id = "'.$report_id.'" and trans_status = 1 and add_name = "'.$add_name.'"';
				$exit_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$exit_qry')");
				$exit_column_result = $exit_column_info->result();
				$exit_column_info->next_result();
				$rows_count = $exit_column_info->num_rows();
				if((int)$rows_count === 0){
					$report_qry  = 'insert into cw_report_add_column (report_id,add_name, select_condition,trans_created_by, trans_created_date) values ("'.$report_id.'","'.$add_name.'","'.$select_condition.'","'.$logged_id.'","'.$date.'")';
					$this->db->query("CALL sp_a_run ('RUN','$report_qry')");
					$add_column_content = $this->add_column_table($report_id);
					$this->save_table_view($report_id);
					echo json_encode(array('success' => TRUE, 'message' => "Additional Column Name is Added successfully!!", 'add_column_content'=>$add_column_content));
				}else
				if((int)$add_column_id > 0){
					$upd_qry  = 'UPDATE cw_report_add_column SET add_name = "'.$add_name.'",select_condition = "'.$select_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_report_add_column_id = "'.$add_column_id.'"';
					$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
					$add_column_content = $this->add_column_table($report_id);
					$this->save_table_view($report_id);
					echo json_encode(array('success' => True, 'message' => "Column Name is updated successfully!", 'add_column_content'=>$add_column_content));
				}else{
					$add_column_content = $this->add_column_table($report_id);
					echo json_encode(array('success' => False, 'message' => "Already Column Name is Exits!", 'add_column_content'=>$add_column_content));
				}
			}
		}
	}
	
	public function add_column_table($report_id){
		$select_qry    = 'select * from cw_report_add_column where trans_status = 1 and report_id = "'.$report_id.'"';
		$select_info   = $this->db->query("CALL sp_a_run ('SELECT','$select_qry')");
		$select_result = $select_info->result();
		$select_info->next_result();
		foreach($select_result as $rslt){
			$add_column_id    = $rslt->prime_report_add_column_id;
			$add_column       = $rslt->add_name;
			$select_condition = $rslt->select_condition;
			$tr_line .= "<tr>
							<td>$add_column</td>
							<td>$select_condition</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=get_add_column_edit('$add_column_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_add_column('$add_column_id','$report_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$add_column_content = "<table class='table table-bordered table-stripted' id='add_column_list'>
								<thead>
									<tr class='inline_head'>
										<th style='text-align: center;'>Add Column Name</th>
										<th style='text-align: center;'>Query Name</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
								</thead>
								<tbody>
									$tr_line
								</tbody>
								</table>";
		return $add_column_content;
	}
	
	public function get_add_column_edit(){
		$add_column_id  = $this->input->post('add_column_id');
		$edit_formula = 'SELECT * FROM cw_report_add_column WHERE  prime_report_add_column_id = "'.$add_column_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_formula')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		echo json_encode(array('success' => true,'edit_result'=>$edit_result[0]));
	}
	
	public function remove_add_column(){
		$add_column_id  = $this->input->post('add_column_id');
		$report_id      = $this->input->post('report_id');
		$logged_id      = $this->session->userdata('logged_id');
		$date           = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_report_add_column SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_report_add_column_id = "'.$add_column_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$add_column_content = $this->add_column_table($report_id);
		$this->save_table_view($report_id);
		echo json_encode(array('success' => true,'message'=>'Table Query Remove Successfully !!!','add_column_content'=>$add_column_content));
	}
	
	public function save_sum_column(){
		$report_id        = $this->input->post('report_id');
		$sum_column_name  = ltrim(implode(",",$this->input->post('sum_column_name[]')),",");
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d H:i:s");
		$is_exit_qry = 'SELECT count(*) as rslt_count FROM cw_report_tot_column WHERE report_id = "'.$report_id.'" and trans_status = 1';
		$is_exit_info   = $this->db->query("CALL sp_a_run ('SELECT','$is_exit_qry')");
		$is_exit_result = $is_exit_info->result();
		$is_exit_info->next_result();
		$rslt_count   = $is_exit_result[0]->rslt_count;
		if((int)$rslt_count === 0){
			$sum_column_qry  = 'insert into cw_report_tot_column (report_id, sum_column_name,trans_created_by,trans_created_date) values ("'.$report_id.'","'.$sum_column_name.'","'.$logged_id.'","'.$date.'")';
			$insert_info    = $this->db->query("CALL sp_a_run ('INSERT','$sum_column_qry')");
			$insert_result  = $insert_info->result();
			$insert_info->next_result();
			$insert_id   = $insert_result[0]->ins_id;
			$msg = "Column wise Total is selected!!!";
		}else{
			$sum_column_qry  = 'UPDATE cw_report_tot_column SET sum_column_name = "'.$sum_column_name.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where report_id = "'.$report_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$sum_column_qry')");
			$msg = "Column wise Total is updated!!!";
		}
		$sum_column_content = $this->total_column_table($report_id);
		echo json_encode(array('success' => true,'message'=>$msg,'sum_column_content'=>$sum_column_content));
	}
	
	public function total_column_table($report_id){
		$select_tot_col_qry    = 'select * from cw_report_tot_column where trans_status = 1 and report_id = "'.$report_id.'"';
		$select_tot_col_info   = $this->db->query("CALL sp_a_run ('SELECT','$select_tot_col_qry')");
		$select_tot_col_result = $select_tot_col_info->result();
		$select_tot_col_info->next_result();
		$i = 1;
		foreach($select_tot_col_result as $rslt_col){
			$report_tot_id     = $rslt_col->prime_report_tot_id;
			$report_id         = $rslt_col->report_id;
			$sum_column_name   = $rslt_col->sum_column_name;
			$sum_id = form_input(array( 'name' =>"report_tot_id",'id' =>"report_tot_id",'class' => 'form-control input-sm','value' =>$report_tot_id,'type'=>'Hidden'));
			$tr_line .= "<tr>
							<td>$sum_id$i</td>
							<td>$sum_column_name</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=get_sum_column_edit('$report_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_sum_column('$report_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
			$i++;
		}
		$sum_column_content = "<table class='table table-bordered table-stripted' id='add_column_list'>
								<thead>
									<tr class='inline_head'>
										<th style='text-align: center;'>Si. No.</th>
										<th style='text-align: center;'>Total Column</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
								</thead>
								<tbody>
									$tr_line
								</tbody>
								</table>";
		return $sum_column_content;
	}
	
	public function get_sum_column_edit(){
		$report_id    = $this->input->post('report_id');
		$sum_column_info = 'SELECT * FROM cw_report_tot_column WHERE  report_id = "'.$report_id.'" and trans_status = 1';
		$sum_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$sum_column_info')");
		$sum_column_edit_result = $sum_column_info->result();
		$sum_column_info->next_result();
		echo json_encode(array('success' => true,'sum_column_edit_result'=>$sum_column_edit_result[0]));
	}
	
	public function remove_sum_column(){
		$report_id      = $this->input->post('report_id');
		$logged_id      = $this->session->userdata('logged_id');
		$date           = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_report_tot_column SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where report_id = "'.$report_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$this->total_column_table($report_id);
		echo json_encode(array('success' => true,'message'=>'Total column sum is removed!!!','sum_column_content'=>$sum_column_content));
	}
	
	/* SVK EDIT START */
	
	//GET DEFAULT TABLE UI 
	public function get_table_view_data(){ 
		$report_id  = $this->input->post('report_id');
		if($report_id){
			echo $this->table_view($report_id);
		}else{
			echo json_encode(array('success' => False,'message' => 'Please Contact Admin..!'));
		}
	}
}

?>