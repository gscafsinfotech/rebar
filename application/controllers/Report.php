<?php 
/**********************************************************
	Filename: Report.php
	Description: Report controller for all modules.
		 Author: Jaffer Sathik
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
require_once("Report_controller.php");
class Report extends Report_controller{
	public function __construct(){		
		parent::__construct('report');		
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$method = $this->uri->segment(2);
		if($method === "index"){
			$report_id = $this->uri->segment(3);
			$this->session->set_userdata('report_id',$report_id);
		}else{
			$report_id = $this->session->userdata('report_id');
		}
		if(!$this->isvalid($report_id)){
			redirect('home');
		}
	}
	
	// LOAD PAGE WITH TABLE DATA
	public function index($view_id = -1){
		$data['table_headers']        = $this->table_info;
		$data['fliter_list']          = $this->fliter_list;
		$data['report_name']          = $this->report_name;
		$data['date_filter']          = $this->date_filter;
		$data['date_column']          = $this->date_column;
		$filter_info                  = $this->get_presaved_filter($view_id);
		$data['filter_info']          = $filter_info;
		$data['form_id']              = $view_id;
		$data['company_information']  = $this->company_info(); 
		$this->load->view("$this->control_name/manage",$data);
	}
	
	public function search(){
		$draw         	   = $this->input->post('draw');
		$start        	   = $this->input->post('start');
		$per_page     	   = $this->input->post('length');
		$order        	   = $this->input->post('order');
		$order_col    	   = $this->input->post('columns');
		$search       	   = $this->input->post('search');
		$search       	   = trim($search['value']);
		$filter_label      = $this->input->post('filter_label');
		$filter_type       = $this->input->post('filter_type');
		$field_type_list   = $this->input->post('field_type');
		$filter_cond       = $this->input->post('filter_cond');
		$filter_val        = $this->input->post('filter_val');
		$order_col         = $this->input->post('columns');
		$column            = $order[0]['column'];
		$order_sor         = $order[0]['dir'];
		$order_col         = $order_col[$column]['data'];
		$start_date        = str_replace("/","-",$this->input->post('start_date'));
		$end_date          = str_replace("/","-",$this->input->post('end_date'));
		$from_date         = date('Y-m-d',strtotime($start_date));
		$to_date           = date('Y-m-d',strtotime($end_date));
		$fliter_query      = "";
		$filter_count      = count($filter_label);
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     = $filter_label[$i];
			$table_name  = $filter_type[$i];
			$db_cond     = $filter_cond[$i];
			$db_value    = $filter_val[$i];
			$field_type  = (int)$field_type_list[$i];
			if(($db_cond) && ($db_value)){
				$search_count++;
				if($field_type === 4){
					$search_val = '"'.date('Y-m-d',strtotime($db_value)).'"';
				}else
				if(($field_type === 5) || ($field_type === 7)){
					$search_val    = '('.rtrim($db_value,',').')';
					$db_cond       = 'IN'; 
				}else
				if($field_type === 13){
					$search_val = '"'.date('Y-m-d H:i:s',strtotime($db_value)).'"';
				}else{
					$search_val = $db_value;
				}
				if((int)$table_name === 1){
					$fliter_query .= ' and '. $this->prime_table .".". $db_name ." ". $db_cond .' '.$search_val.''; 
				}
			}			
		}
		
		$common_search = "";
		if($search){
			$count=0;
			foreach($this->form_info as $setting){
				$prime_form_id      = $setting->prime_form_id;
				$prime_module_id    = $setting->prime_module_id;
				$field_type         = $setting->field_type;
				$pick_list          = $setting->pick_list;
				$pick_table         = $setting->pick_table;
				$pick_list_type     = $setting->pick_list_type;
				$input_view_type    = (int)$setting->input_view_type;
				$auto_prime_id      = $setting->auto_prime_id;
				$auto_dispaly_value = $setting->auto_dispaly_value;
				$label_id           = $setting->label_id;
				$field_isdefault    = (int)$setting->field_isdefault;
				
				if(!empty($prime_module_id)){
					$table_name         = "cw_".$prime_module_id;
					$prime_id           = "prime_".$prime_module_id."_id";
				}
				
				$pick_sel_table = $table_name;
				$other_label_id = $pick_sel_table.".".$label_id;
				if(($input_view_type === 1) || ($input_view_type === 2)){
					if((int)$field_type === 4){
						if(strtotime($search)){
							$search_val = date('Y-m-d',strtotime($search));
							$common_search .= ' or '. $other_label_id .' like "'.$search_val.'%"';
						}
					}else
					if(((int)$field_type === 5) || ((int)$field_type === 7)){
						if((int)$pick_list_type === 1){
							$column_name = explode(",",$pick_list);
							$column_name = $column_name[1];
						}else{
							$column_name = $pick_table."_value";
						}
						$pick_query_as  = $pick_table."_".$prime_form_id;
						$label_id       = "$pick_query_as.$column_name";
						$common_search .= ' or '. $label_id .' like "'.$search.'%"';
					}else
					if((int)$field_type === 9){
						$pick_query_as  = $pick_table."_".$prime_form_id;
						$label_id       = "$pick_query_as.$auto_dispaly_value";
						$common_search .= ' or '. $label_id .' like "'.$search.'%"';
					}else{
						if(!strtotime($search)){
							$common_search .= ' or '. $other_label_id .' like "'.$search.'%"';
						}
					}
				}
			}
			if($common_search){
				$common_search = ltrim($common_search,' or ');
				$common_search = " and ($common_search)";
			}
		}
		$expect_id  = "";
		//echo "BSK".$this->form_info[0]->prime_module_id;
		/*if($this->prime_table === "cw_employees"){
			$sort_code = "cw_transactions.role,cw_transactions.employee_code ";
			$expect_id = " cw_employees.prime_employees_id !=1 and ";
		}else
		if($this->prime_table === "cw_monthly_input"){
			$sort_code = $this->prime_table.".prime_monthly_input_id";
		}else
		if($this->prime_table === "cw_loan"){
			$sort_code = $this->prime_table.".prime_loan_id";
		}else{*/
			$sort_code = $this->prime_table."."."prime_".$this->form_info[0]->prime_module_id."_id";
		//}
		
		if($order_col === 'prime_report_id'){
			$order_col = $sort_code;
		}
		
		if(!$order_sor){$order = "asc";}	
		$basic_query = "";
		if($this->table_search_info){
			$basic_query = $this->table_search_info;
		}
		
		//building date filter query record
		$date_filter = $this->date_filter;
		if((int)$date_filter === 1){
			$date_column = explode(",",$this->date_column);
			$date_column_count = count($date_column);
			$date_search = "";
			for($i = 0;$i < $date_column_count; $i++){
				$date_column_search = $date_column[$i];
				$column_name = explode(".",$date_column_search);
				$column_name = $column_name[1];
				if($column_name == "transactions_month"){
					$date_column_search = '(DATE_FORMAT(str_to_date('.$date_column_search.', "%m-%Y") , "%Y-%m-%01")';
					$transaction_sts = "and cw_transactions.trans_status = 1 ";
				}else
				if($column_name == "process_month"){
					$date_column_search = '(DATE_FORMAT(str_to_date('.$date_column_search.', "%m-%Y") , "%Y-%m-%01")';
					$transaction_sts = "and cw_transactions.trans_status = 1 ";
				}else{
					$date_column_search = '(DATE_FORMAT('.$date_column_search.', "%Y-%m-%d")';
				}
				$date_search .= ' and '.$date_column_search.'  BETWEEN "'.$start_date.'" and "'.$end_date.'")';
			}
		}else{
			$date_search = "";
			$transaction_sts = "";
		}
		
		//COMMON QUERY FOR SERACH AND FILTERS
		
		if(strstr($this->select_query,'cw_transactions')){
			$transaction_sts = "and cw_transactions.trans_status = 1 ";
		}else{
			$transaction_sts = "";
		}
		$select_info   = str_replace("@SELECT",$this->select_query,$this->base_query);
		$search_query  = $select_info. $this->pick_query;
		$search_query .= " where $expect_id $this->prime_table.trans_status = 1 $transaction_sts $basic_query $fliter_query $common_search $date_search";
		$search_query .= " ORDER BY  $order_col $order_sor";
		//$search_query .= " LIMIT  $offset,$limit";
		$search_data   = $this->db->query("CALL sp_a_run ('SELECT','$search_query')");
		$search_result = $search_data->result();
		$num_rows      = $search_data->num_rows();
		$search_data->next_result();
		
		// QUERY RESULT FOR SUB TOTAL
		$group_by = "";
		if($this->group_column){
			$group_colum_search = $this->group_column;
			$search_table_name   = 'cw_transactions';
			$pos = strpos($group_colum_search, $search_table_name);
			if($pos !== false){
				$transaction_sts = "and cw_transactions.trans_status = 1 ";
			}else{
				$transaction_sts ="";
			}
			
			$group_by = "group by ".$this->group_column;
			$is_exit  = strstr($this->group_column,$sort);
			if($is_exit){
				$sort = str_replace("$sort,","",$this->group_column);
			}else{
				$sort = $this->group_column;
			}
			if((int)$this->sub_tot_show === 1){
				$replace_select = $this->select_query .",".$this->sum_qry_column;
			}else{
				$replace_select = $this->select_query;
			}
			$sub_tot_info   = str_replace("@SELECT",$replace_select,$this->base_query);
			$group_query    = $sub_tot_info. $this->pick_query;
			$group_query   .= " where $this->prime_table.trans_status = 1 $transaction_sts $basic_query $fliter_query $common_search $date_search";
			
			$group_query   .= " $group_by ORDER BY  $order_col $order_sor";
			//$group_query   .= " LIMIT  $start,$per_page";
			$group_data     = $this->db->query("CALL sp_a_run ('SELECT','$group_query')");
			$group_result   = $group_data->result();
			$group_data->next_result();
			if((int)$this->sub_tot_show === 1){
				foreach(explode(",",$this->sum_column) as $sum){
					$sum_column[$sum] = $sum;
				}		
				$exist_array = explode(",",$this->group_column);
				foreach($exist_array as $exist){
					$column_split   = explode(".",$exist);
					$exist_column[] = $column_split[1];
				}
				
				foreach($group_result as $group_info){
					foreach($exist_column as $column){
						$value = $group_info->$column;
						$check_array[$column] = $value;
					}
					foreach($group_info as $key=>$value){
						if(!$sum_column[$key]){
							$group_info->$key = "-";
						}
					}
					$group_info->sub_total_exist = true;
					$push_keys   = $this->multi_array_search($search_result, $check_array);
					$check_array = array();
					$push_keys   = end($push_keys)+1;
					$push_arrya  = array($push_keys =>$group_info);
					array_splice( $search_result, $push_keys, 0,  $push_arrya);
				}
			}
		}
		// QUERY RESULT FOR FINAL TOTAL
		if($this->sum_qry_column !== ""){
			$sum_colum_search = $this->sum_qry_column;
			$search_table   = 'cw_transactions';
			$pos = strpos($sum_colum_search, $search_table);
			if($pos !== false){
				$transaction_sts = "and cw_transactions.trans_status = 1 ";
			}else{
				$transaction_sts ="";
			}
			$final_sum   = str_replace("@SELECT",$this->sum_qry_column,$this->base_query);
			$final_sum_query  = $final_sum. $this->pick_query;
			$final_sum_query .= " where $this->prime_table.trans_status = 1 $transaction_sts $basic_query $fliter_query $common_search $date_search";
			$final_sum_query .= " ORDER BY  $order_col $order_sor";
			//$final_sum_query .= " LIMIT  $start,$per_page";
			$final_sum_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_sum_query')");
			$final_sum_result = $final_sum_data->result();
			$final_sum_data->next_result();
			if(count($final_sum_result)>0){
				$push_keys   = count($search_result)+1;
				$final_sum_result[0]->total_exist = true;
				$push_arrya  = array($push_keys =>$final_sum_result[0]);
				array_splice($search_result, $push_keys, 0,  $push_arrya);	
			}
		}
		//Total row count data details
		if($this->select_query){
			$emp_column_name = explode(",",$this->select_query);
			$emp_column_name = $emp_column_name[1];
			if(strstr($emp_column_name, ' as ' ) ) {
				  $emp_column_name = explode(".",$emp_column_name);
				  $pattern = '/([a-zA-Z]+(?:_[a-zA-Z]+)*)\w as /';
				  $replacement = '';
				  $emp_column_name   = preg_replace($pattern, $replacement, $emp_column_name[1]);
			}else{
				 $emp_column_name   = explode(".",$emp_column_name);
				 $emp_column_name   = $emp_column_name[1];
			}
		}
		
		if($num_rows){
			$push_emp_count  = count($search_result)+1;
			$emp_count       = (object)[$emp_column_name =>$num_rows,'emp_tot_count'=>1];
			$push_emp_array  = array($push_emp_count =>$emp_count);
			array_splice($search_result, $push_emp_count, 0,  $push_emp_array);	
		}
		$data_rows     = array();
		foreach ($search_result as $search){
			$data_rows[] = get_report_row($search,$this->table_info,$this);
			
		}
		echo json_encode(array("draw" => intval($draw),"recordsTotal" => $num_rows,"data" => $data_rows));		
	}
	
	function multi_array_search($array, $search){
		$array = json_decode(json_encode($array),True);
		$result = array();
		foreach ($array as $key => $value){
		  foreach ($search as $k => $v){
			if (!isset($value[$k]) || $value[$k] != $v){
			  continue 2;
			}
		  }
		  $result[] = $key;
		}
		return $result;
	}
	
	public function edit_filter_report(){
		$report_filter_id  = $this->input->post('report_id');
		$select_qry        = 'select cw_report_filter.report_filter_id,cw_report_filter.filter_name,`filter_id`, `filter_con`, `filter_con`,`field_type`, `filter_val` from cw_report_filter_line join cw_report_filter on cw_report_filter.report_filter_id = cw_report_filter_line.report_filter_id where cw_report_filter_line.trans_status = 1 and cw_report_filter.report_filter_id = "'.$report_filter_id.'"';
		$select_info   = $this->db->query("CALL sp_a_run ('SELECT','$select_qry')");
		$select_result = $select_info->result();
		$select_info->next_result();
		echo json_encode(array('success' => true,'edit_data'=>$select_result));
	}
	
	public function filter_save(){
		$created_on       = date("Y-m-d H:i:s");
		$logged_id        = $this->logged_id;
		$report_filter_id = (int)$this->input->post('report_filter_id');
		$filter_name      = $this->input->post('filter_name');
		$form_id          = $this->input->post('form_id');
		$field_type_list  = $this->input->post('field_type');
		$filter_label     = $this->input->post('filter_label');
		$filter_type      = $this->input->post('filter_type');
		$filter_cond      = $this->input->post('filter_cond');
		$filter_val       = $this->input->post('filter_val');
		$filter_count     = count($filter_label);
		$insert_count     = 0;
		$insert_val_query = "";
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     = $filter_label[$i];
			$table_name  = $filter_type[$i];
			$db_cond     = $filter_cond[$i];
			$db_value    = $filter_val[$i];
			$field_type  = $field_type_list[$i];
			if(($db_cond) && ($db_value)){
				$insert_val_query .= "(\"@report_filter_id@\",\"$db_name\",\"$db_cond\",\"$db_value\",\"$field_type\",\"$logged_id\",\"$created_on\"),";
				$insert_count++;
			}			
		}
		$insert_col_query    = "report_filter_id,filter_id,filter_con,filter_val,field_type,trans_created_by,trans_created_date";
		if($report_filter_id === 0){
			if((int)$insert_count > 0){
				if(!$this->check_filter_exists($form_id,$filter_name)){
					$insert_val_query = rtrim($insert_val_query,',');
					$prime_insert_query = "insert into cw_report_filter (prime_report_id,filter_name,trans_created_by,trans_created_date) values (\"$form_id\",\"$filter_name\",\"$logged_id\",\"$created_on\")";
					$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
					$insert_result      = $insert_info->result();
					$insert_info->next_result();
					$insert_id = $insert_result[0]->ins_id;
					$insert_val_query = str_replace("@report_filter_id@","$insert_id","$insert_val_query");
					$filter_line_query = "insert into cw_report_filter_line ($insert_col_query) values $insert_val_query";
					$filter_line_info  = $this->db->query("CALL sp_a_run ('INSERT','$filter_line_query')");
					$insert_result     = $filter_line_info->result();
					$filter_line_info->next_result();
					$filter_list = $this->get_presaved_filter($form_id);
					echo json_encode(array('success' => true, 'message' => "Report Filter successfully added",'filter_list'=>$filter_list));
				}else{
					echo json_encode(array('success' => FALSE, 'message' => "Filter Report Name already"));
				}
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Filter conditions is not equal to filter values"));
			}
		}else{
			if((int)$insert_count > 0){
				if(!$this->check_filter_exists($form_id,$filter_name,$report_filter_id)){
					$insert_val_query = rtrim($insert_val_query,',');
					$update_qry       = 'UPDATE cw_report_filter SET filter_name = "'.$filter_name.'" ,trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$created_on.'" where report_filter_id = "'.$report_filter_id.'"';
					$this->db->query("CALL sp_a_run ('SELECT','$update_qry')");
					$delete_query  = 'DELETE FROM cw_report_filter_line WHERE report_filter_id = "'.$report_filter_id.'"';
					$this->db->query("CALL sp_a_run ('RUN','$delete_query')");
					$insert_val_query = str_replace("@report_filter_id@","$report_filter_id","$insert_val_query");
					$filter_line_query = "insert into cw_report_filter_line ($insert_col_query) values $insert_val_query";
					$filter_line_info  = $this->db->query("CALL sp_a_run ('INSERT','$filter_line_query')");
					$insert_result     = $filter_line_info->result();
					$filter_line_info->next_result();
					$filter_list = $this->get_presaved_filter($form_id);
					echo json_encode(array('success' => true, 'message' => "Report Filter successfully Updated",'filter_list'=>$filter_list));
				}else{
					echo json_encode(array('success' => FALSE, 'message' => "Filter Name Already Exists..!"));
				}
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Filter conditions is not equal to filter values"));
			}
		}
	}
	
	//CHECK FAULT ALREADY EXISTS
	public function check_filter_exists($prime_report_setting_id,$filter_name,$report_filter_id = -1){
		$search_qry = 'select count(*) as counts from cw_report_filter where prime_report_id = "'.$prime_report_setting_id.'" and filter_name = "'.$filter_name.'" and trans_status = 1';
		if((int)$report_filter_id > 0){
			$search_qry .= ' and report_filter_id != "'.$report_filter_id.'"';
		}
		$select_info   = $this->db->query("CALL sp_a_run ('SELECT','$search_qry')");
		$select_result = $select_info->result();
		$select_info->next_result();
		if((int)($select_result[0]->counts) > 0){
			return TRUE;
		}else{ 
			return FALSE;
		}
	}
	
	public function get_presaved_filter($view_id){
		// PRESAVED FILTER
		$pre_filter_qry     = 'select report_filter_id,filter_name from cw_report_filter where trans_status = 1 and prime_report_id = "'.$view_id.'"';
		$pre_filter_qry    = $this->db->query("CALL sp_a_run ('SELECT','$pre_filter_qry')");
		$pre_filter_data   = $pre_filter_qry->result();
		$pre_filter_qry->next_result();
		$filter_info           = array(''=>'--select--');
		foreach($pre_filter_data as $filter){
			$filter_info[$filter->report_filter_id] = $filter->filter_name;
		}
		return $filter_info;
	}
	
	public function company_info(){
		$company_info             = $this->db->query("CALL sp_a_run ('SELECT','select company_name,company_short_name,mobile_number  from cw_company_information')");
		$company_infomation       = $company_info->result();
		$company_info->next_result();
		return $company_infomation[0];
	}
}
?>