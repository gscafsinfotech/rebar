<?php
/**********************************************************
	   Filename: Print Setting
	Description: Print Setting for creating new Dynamic Print template.
		 Author: udhayakumar Anandhan
	 Created on: ‎‎19 FEB ‎2018
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
class Print_setting  extends Secure_Controller{
 
	public function __construct(){
		parent::__construct('print_setting');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_form_setting_headers());
		$this->load->view('print_setting/manage',$data);
	}
	/* ==============================================================*/
	/* ================== PRINT COMMON OPEARTION - START ============*/
	/* ==============================================================*/	
	//PRINT SEARCH OPEARTION
	public function search(){
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
		
		// Fetch Records
		$info     = $this->db->query("CALL sp_print_setting_search ('SEARCH')");
		$result   = $info->result();
		$info->next_result();
		$data_rows     = array();
		foreach ($result as $form_setting){
			$data_rows[]=get_form_setting_datarows($form_setting,$this);
		}
		$data_rows=$this->xss_clean($data_rows);
		
		// Fetch Records Count
		$count_info     = $this->db->query("CALL sp_print_setting_search ('COUNT')");
		$count_result   = $count_info->result();
		$count_info->next_result();
		$num_rows = $count_result[0]->data_count;
		
		echo json_encode(array('total'=>$num_rows,'rows'=>$data_rows));
	}
	
	// MODEL VIEW OPEARTION
	public function view($print_info_module_id){
		if(!$print_info_module_id){
			$print_info_module_id = 0;
		}
		$data['print_info_module_id'] = $print_info_module_id;
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		
		$print_for[""] = "---- Module For ----";
		foreach($role_result as $for){
			$role_id             = $for->prime_category_id;
			$category_name       = $for->category_name;
			$print_for[$role_id] = $category_name;
		}
		$data['print_for']  = $print_for;
		
		$table_query = 'SELECT table_name FROM information_schema.tables WHERE table_schema ="'.$this->config->item("db_name").'" and table_name not like "cw_zct%" and table_name not like "%_cf"';
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
		$table_result = $table_info->result();
		$table_info->next_result();
		$tab_array = array("cw_app_config","cw_form_bind_input","cw_form_condition_formula","cw_form_for_input","cw_form_setting","cw_form_table_cond_for","cw_form_table_search","cw_form_view_setting","cw_grants","cw_import","cw_main_menu","cw_monthly_input","cw_payroll_formula","cw_payroll_function","cw_permissions","cw_print_block","cw_print_design","cw_print_info","cw_print_map","cw_print_table","cw_print_table_where","cw_salary_check","cw_sub_menu","cw_month_day","cw_statutory","cw_professional_tax","cw_professional_tax_tax_range","cw_sessions","cw_util_excel_format","cw_session_value","cw_util_excel_format_line","dailyunpunch","monthlyattdata");
		$table_list[""] = "---- Select Table ----";
		foreach($table_result as $table){
			$table_name = $table->table_name;
			if(!in_array($table_name, $tab_array)){
				$str = substr($str, 1);
				$table_value = substr((ucwords(str_replace("_"," ",$table_name))),3);
				$table_list[$table_name] = $table_value;
			}
		}
		$data['table_list'] = $table_list;
		
		$print_info         = $this->get_print_info_list($print_info_module_id);
		$print_info_rslt    = json_decode($print_info);
		$data['print_info_list'] = $print_info_rslt->print_info_list;
		$data['print_block_for'] = json_decode(json_encode($print_info_rslt->print_block_for), True);
		
		$print_block              = $this->get_print_block_list($print_info_module_id);
		$print_block_rslt         = json_decode($print_block);
		$data['print_block_list'] = $print_block_rslt->print_block_list;
		$data['print_table_list'] = json_decode(json_encode($print_block_rslt->print_table_list), True);
		$data['split_table_list'] = $this->get_split_list($print_info_module_id);
		
		$this->load->view("print_setting/print_info",$data);
	}
	/* ==============================================================*/
	/* ================== PRINT COMMON OPEARTION - END  ============*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================== PRINT BASIC OPEARTION - START  ============*/
	/* ==============================================================*/
	// PRINT BASIC SAVE
	public function save_print_info(){
		$prime_print_info_id   = (int)$this->input->post('prime_print_info_id');
		$print_info_module_id  = $this->input->post('print_info_module_id');
		$print_info_name       = $this->input->post('print_info_name');
		$print_info_for        = ltrim(implode(",",$this->input->post('print_info_for[]')),",");
		$print_type            = $this->input->post('print_type');
		$print_based_on        = $this->input->post('print_based_on');
		$logged_id             = $this->session->userdata('logged_id');
		$date                  = date("Y-m-d h:i:s");
		if($prime_print_info_id === 0){
			if(!$this->check_print_info_for_already_exists($print_info_for,$print_type,$print_info_module_id)){
				$is_exist_qry  = 'SELECT count(*) as rslt_count FROM cw_print_info where print_info_module_id = "'.$print_info_module_id.'" and print_info_name = "'.$print_info_name.'" and trans_status = 1 ';
				$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
				$exist_rslt    = $is_exist_data->result();
				$is_exist_data->next_result();
				if((int)$exist_rslt[0]->rslt_count === 0){	
					$print_qry  = 'INSERT INTO cw_print_info (print_info_module_id, print_info_name,print_info_for,print_type,print_based_on,trans_created_by, trans_created_date) VALUES ("'.$print_info_module_id.'","'.$print_info_name.'","'.$print_info_for.'","'.$print_type.'","'.$print_based_on.'","'.$logged_id.'","'.$date.'")';
					$this->db->query("CALL sp_a_run ('RUN','$print_qry')");
					
					$print_info          = $this->get_print_info_list($print_info_module_id);
					$print_info_rslt     = json_decode($print_info);
					$print_info_list     = $print_info_rslt->print_info_list;
					$print_block_for     = $print_info_rslt->print_block_for;		
					echo json_encode(array('success' => true, 'message' => "Print info successfully added", 'print_info_list' => $print_info_list, 'print_block_for' => $print_block_for));
				}else{
					echo json_encode(array('success' => FALSE, 'message' => "Print info already exist"));
				}
			}else{
			echo json_encode(array('success' => FALSE, 'message' => "Print info already created for this Print For Check it"));
			}
		}else{
			if(!$this->check_print_info_for_already_exists($print_info_for,$print_type,$print_info_module_id,$prime_print_info_id)){
				$upd_qry  = 'UPDATE  cw_print_info SET print_info_module_id = "'.$print_info_module_id.'",print_info_name = "'.$print_info_name.'",print_info_for = "'.$print_info_for.'",print_type = "'.$print_type.'",print_based_on = "'.$print_based_on.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_info_id = "'.$prime_print_info_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$print_info          = $this->get_print_info_list($print_info_module_id);
				$print_info_rslt     = json_decode($print_info);
				$print_info_list     = $print_info_rslt->print_info_list;
				$print_block_for = $print_info_rslt->print_block_for;
				echo json_encode(array('success' => true, 'message' => "Print info successfully updated", 'print_info_list' => $print_info_list, 'print_block_for' => $print_block_for));
			}else{
			echo json_encode(array('success' => FALSE, 'message' => "Print info already created for this Print For Check it"));
			} 
		}
	}
	
	//CHECKING PRINT INFO FOR ALREADY EXISTS
	public function check_print_info_for_already_exists($print_info_for,$print_type,$print_info_module_id,$prime_print_info_id = -1){
		$print_info_for = explode(',', $print_info_for);
		$query ='';
		foreach($print_info_for as $rslt){
			$query .= "print_info_for LIKE \"%".$rslt."%\" OR print_info_for LIKE \"".$rslt."%\" OR print_info_for LIKE \"%".$rslt."\" OR ";
		}
		$query = rtrim($query,"OR ");  
		$exist_query = 'SELECT * FROM cw_print_info where ('.$query.') and print_info_module_id = "'.$print_info_module_id.'" and print_type = "'.$print_type.'" and trans_status = 1';
		
		if((int)$prime_print_info_id > 0){
			$exist_query .=' and prime_print_info_id != "'.$prime_print_info_id.'"';
		}
		$exist_data    = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_rslt    = $exist_data->num_rows();
		$exist_data->next_result();
		if((int)$exist_rslt > 0){
			return TRUE;
		}else{ 
			return FALSE;
		}
	}
	
	//GET PRINT BASIC LIST
	public function get_print_info_list($print_info_module_id){
		$print_qry  = 'SELECT * FROM cw_print_info where print_info_module_id = "'.$print_info_module_id.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();	
		$print_data->next_result();
		
		$tr_line 	  = "";		
		$print_block_for[] = "--- Select Print Info ---";
		foreach($print_rslt as $rslt){
			$prime_print_info_id   = $rslt->prime_print_info_id;
			$print_info_module_id  = $rslt->print_info_module_id;
			$print_info_name       = ucwords($rslt->print_info_name);
			$print_info_for        = $rslt->print_info_for;
			$print_type            = $rslt->print_type;
			$print_based_on        = $rslt->print_based_on;
			$print_type_list  = array(1=>"Payslip",2=>"Form M",3=>"Offer Letter",4=>"Other");
			$print_based_list = array(1=>"Print",2=>"Email");
			$print_type_val  = "";
			if((int)$print_type === 1){
				$print_type_val = "Payslip";
			}else
			if((int)$print_type === 2){
				$print_type_val = "Form M";
			}else
			if((int)$print_type === 3){
				$print_type_val = "Offer Letter";
			}else
			if((int)$print_type === 4){
				$print_type_val = "Terminated without salary";
			}else
			if((int)$print_type === 5){
				$print_type_val = "Terminated with salary";
			}else
			if((int)$print_type === 6){
				$print_type_val = "Abscond";
			}else
			if((int)$print_type === 7){
				$print_type_val = "Resigned";
			}
			$print_based_val = "";
			if((int)$print_based_on === 1){
				$print_based_val = "Print";
			}else
			if((int)$print_based_on === 2){
				$print_based_val = "Email";
			}
			$print_block_for[$prime_print_info_id] = $print_info_name;
						
			$print_for_qry  = 'SELECT GROUP_CONCAT(category_name) as category_name FROM cw_category where prime_category_id in ('.$print_info_for.')';
			$print_for_data = $this->db->query("CALL sp_a_run ('SELECT','$print_for_qry')");
			$print_for_rslt = $print_for_data->result();			
			$print_for_data->next_result();
			$category_name  = $print_for_rslt[0]->category_name;
			
			$tr_line .= "<tr>
							<td>$print_info_name</td>
							<td>$category_name</td>
							<td>$print_type_val</td>
							<td>$print_based_val</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_print_info('$prime_print_info_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_print_info('$prime_print_info_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$print_info_list = "<table class='table table-bordered table-stripted' id='print_info_table'>
							<tr class='inline_head'>
								<th>Print Name</th>
								<th>Print For</th>
								<th>Print Type</th>
								<th>Print Based On</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		return json_encode(array('success' => TRUE,'print_info_list' => $print_info_list,'print_block_for'=>$print_block_for));
	}
	
	//EDIT PRINT INFO OPERATION
	public function edit_print_info(){
		$prime_print_info_id  = (int)$this->input->post('prime_print_info_id');
		$print_qry  = 'SELECT * FROM cw_print_info where prime_print_info_id = "'.$prime_print_info_id.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();
		echo json_encode(array('success' => TRUE,'print_info' => $print_rslt[0]));
	}
	
	//REMOVE PRINT INFO OPERATION
	public function remove_print_info(){
		$prime_print_info_id  = (int)$this->input->post('prime_print_info_id');
		$print_info_module_id = $this->input->post('print_info_module_id');
		$logged_id            = $this->session->userdata('logged_id');
		$date                 = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_print_info SET trans_status = 0,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_print_info_id = "'.$prime_print_info_id.'"';
		$delete  = $this->db->query("CALL sp_a_run ('UPDATE','$remove_qry')");
		$delete->next_result();
		
		$print_info          = $this->get_print_info_list($print_info_module_id);
		$print_info_rslt     = json_decode($print_info);
		$print_info_list     = $print_info_rslt->print_info_list;
		$print_block_for = $print_info_rslt->print_block_for;
		echo json_encode(array('success' => true, 'message'=>'Print Info deleted Successfully!!!','print_info_list'=>$print_info_list,'print_block_for'=>$print_block_for));
	}	
	/* ==============================================================*/
	/* =================== PRINT BASIC OPEARTION - END  =============*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================ PRINT BLOCK OPEARTION - START  ==============*/
	/* ==============================================================*/	
	// PRINT BLOCK SAVE
	public function save_print_block(){
		$prime_print_block_id   = (int)$this->input->post('prime_print_block_id');
		$print_block_module_id  = $this->input->post('print_block_module_id');
		$print_block_name       = $this->input->post('print_block_name');
		$print_block_for        = $this->input->post('print_block_for');
		$print_block_type       = $this->input->post('print_block_type');
		$print_block_table      = ltrim(implode(",",$this->input->post('print_block_table[]')),",");
		$print_block_column     = ltrim(implode(",",$this->input->post('print_block_column[]')),",");
		$suppressed_data        = $this->input->post('suppressed_data');
		$cumulative_data        = $this->input->post('cumulative_data');
		
		$suppressed_data_val    = 0;
		if($suppressed_data === "on"){
			$suppressed_data_val = 1; 
		}
		$cumulative_data_val    = 0;
		if($cumulative_data === "on"){
			$cumulative_data_val = 1; 
		}
		if($prime_print_block_id === 0){
			$is_exist_qry  = 'SELECT count(*) as rslt_count FROM cw_print_block where print_block_for = "'.$print_block_for.'" and print_block_type = "'.$print_block_type.'" and print_block_table = "'.$print_block_table.'" and trans_status = 1 ';
			$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
			$exist_rslt    = $is_exist_data->result();
			$is_exist_data->next_result();
			if((int)$exist_rslt[0]->rslt_count === 0){	
				if(!$this->check_print_block_already_exists($print_block_for)){
					$print_qry  = 'INSERT INTO cw_print_block (print_block_module_id, print_block_name,print_block_for,print_block_type,print_block_table,print_block_column,suppressed_data,cumulative_data,trans_created_by, trans_created_date) VALUES ("'.$print_block_module_id.'","'.$print_block_name.'","'.$print_block_for.'","'.$print_block_type.'","'.$print_block_table.'","'.$print_block_column.'","'.$suppressed_data_val.'","'.$cumulative_data_val.'","'.$logged_id.'","'.$date.'")';
					$this->db->query("CALL sp_a_run ('RUN','$print_qry')");	
					
					$print_block      = $this->get_print_block_list($print_block_module_id);
					$print_block_rslt = json_decode($print_block);
					$print_block_list = $print_block_rslt->print_block_list;		
					$print_table_list = $print_block_rslt->print_table_list;		
					echo json_encode(array('success' => true, 'message' => "Print block successfully added", 'print_block_list' => $print_block_list, 'print_table_list' => $print_table_list));
				}else{
					echo json_encode(array('success' => FALSE, 'message' => "Print block already exist"));
				}
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Print block already exist"));
			}
		}else{
			if(!$this->check_print_block_already_exists($print_block_for,$prime_print_block_id)){
				$upd_qry  = 'UPDATE  cw_print_block SET print_block_for = "'.$print_block_for.'",print_block_type = "'.$print_block_type.'",print_block_name = "'.$print_block_name.'",print_block_for = "'.$print_block_for.'",print_block_table = "'.$print_block_table.'",print_block_column = "'.$print_block_column.'",suppressed_data = "'.$suppressed_data_val.'",cumulative_data = "'.$cumulative_data_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_block_id = "'.$prime_print_block_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				
				$print_block      = $this->get_print_block_list($print_block_module_id);
				$print_block_rslt = json_decode($print_block);
				$print_block_list = $print_block_rslt->print_block_list;
				$print_table_list = $print_block_rslt->print_table_list;
				echo json_encode(array('success' => true, 'message' => "Print block successfully updated", 'print_block_list' => $print_block_list, 'print_table_list' => $print_table_list));
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Print block already exist"));
			}
		}
	}
	
	public function check_print_block_already_exists($print_block_for,$prime_print_block_id = -1){
		$is_exist_qry  = 'SELECT * FROM cw_print_block where  print_block_for = "'.$print_block_for.'" and trans_status = 1 ';
		if((int)$prime_print_block_id > 0){
			$is_exist_qry .= " and prime_print_block_id != $prime_print_block_id";
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
	
	//GET PRINT BLOCK LIST
	public function get_print_block_list($print_block_module_id){
		$print_qry  = 'SELECT * FROM cw_print_info left join cw_print_block on cw_print_info.prime_print_info_id = cw_print_block.print_block_for where cw_print_block.print_block_module_id = "'.$print_block_module_id.'" and cw_print_info.trans_status = 1 and cw_print_block.trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();	
		$print_data->next_result();
		$tr_line 	  = "";		
		$print_table_list[] = "--- Select Print Block ---";
		$print_block_type_list = array("1"=>"Normal View","2"=>"List View",);		
		foreach($print_rslt as $rslt){
			$print_info_name       = ucwords($rslt->print_info_name);
			$prime_print_block_id  = $rslt->prime_print_block_id;
			$print_block_name      = ucwords($rslt->print_block_name);
			$print_block_type      = $rslt->print_block_type;
			$print_block_table     = explode(",",$rslt->print_block_table);
			$print_block_column    = explode(",",$rslt->print_block_column);
			
			$table_td = "";
			foreach($print_block_table as $table){
				$table_td .= "<tr><td>$table</td><tr>"; 
			}
			$table_list = "<table> $table_td </table>";
			
			$column_td = "";
			foreach($print_block_column as $column){
				$column_td .= "<tr><td>$column</td><tr>"; 
			}
			$column_list = "<table> $column_td </table>";
			
			$print_block_type      = $print_block_type_list[$print_block_type];
			
			$print_table_list[$prime_print_block_id] = $print_info_name ." - ".$print_block_name;
			
			$tr_line .= "<tr>
							<td>$print_info_name</td>
							<td>$print_block_name</td>
							<td>$print_block_type</td>
							<td>$table_list</td>
							<td>$column_list</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_print_block('$prime_print_block_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_print_block('$prime_print_block_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$print_block_list = "<table class='table table-bordered table-stripted' id='print_info_table'>
								<tr class='inline_head'>
									<th>Print Info Name</th>
									<th>Block Name</th>
									<th>Block Type</th>
									<th>Block Table</th>
									<th>Block Columns</th>
									<th>Edit</th>
									<th>Delete</th>
								</tr>
								$tr_line
							</table>";
		return json_encode(array('success' => TRUE,'print_block_list' => $print_block_list,'print_table_list' => $print_table_list));
	}
	
	//PRINT BLOCK COLUMNS 
	public function get_print_block_table(){		
		$print_block_table = ltrim(implode(",",$this->input->post('print_block_table')),",");
		$prime_in          = '"'.str_replace(",",'","', $print_block_table);
		$custom_in         = str_replace(",",'_cf","', $print_block_table).'_cf"';
		$table_in          = $prime_in.'","'.$custom_in;
		$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_in.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT LIKE "%prime%"';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$print_block_column[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_value      = $column->TABLE_NAME;
			$column_value     = $column->COLUMN_NAME;
			$module_name      = str_replace("cw_","",$table_value);
			$view_name_qry    = 'select view_name from cw_form_setting where prime_module_id = "'.$module_name.'" and label_name = "'.$column_value.'"  and trans_status = "1"';
			$view_name_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_name_qry')");
			$view_name_result = $view_name_data->result();
			$view_name_data->next_result();	
			$column_name = strtoupper($view_name_result[0]->view_name);
			$table_name  = substr((ucwords(str_replace("_"," ",$table_value))),3);
			$table_name  = strtoupper($table_name);	
			if($column_name){
				$print_block_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;
			}	
		}
		echo json_encode(array('success' => true,'print_block_column'=>$print_block_column));
	}
	
	//EDIT PRINT BLOCK OPERATION
	public function edit_print_block(){
		$prime_print_block_id  = (int)$this->input->post('prime_print_block_id');
		$print_qry  = 'SELECT * FROM cw_print_block where prime_print_block_id = "'.$prime_print_block_id.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();
		
		$print_block_table = $print_rslt[0]->print_block_table;
		$prime_in          = '"'.str_replace(",",'","', $print_block_table);
		$custom_in         = str_replace(",",'_cf","', $print_block_table).'_cf"';
		$table_in          = $prime_in.'","'.$custom_in;
		$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`= "'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_in.')';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$print_block_column[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_value  = $column->TABLE_NAME;
			$column_value = $column->COLUMN_NAME;
			
			if(strpos($column_value, 'trans_') !== false) {
				//echo "UDY :: $table_value - $column_value<br/>"; // UDY CHECK FOR ALTER
			}else{
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$column_name  = ucwords(str_replace("_"," ",$column_value));
				$print_block_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;				
			}
		}
		echo json_encode(array('success' => TRUE,'print_block_column' => $print_block_column,'print_info' => $print_rslt[0]));
	}
	
	//REMOVE PRINT INFO OPERATION
	public function remove_print_block(){
		$prime_print_block_id  = (int)$this->input->post('prime_print_block_id');
		$print_block_module_id = $this->input->post('print_block_module_id');
		$logged_id            = $this->session->userdata('logged_id');
		$date                 = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_print_block SET trans_status = 0,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_print_block_id = "'.$prime_print_block_id.'"';
		$delete  = $this->db->query("CALL sp_a_run ('UPDATE','$remove_qry')");
		$delete->next_result();
		
		$print_block      = $this->get_print_block_list($print_block_module_id);
		$print_block_rslt = json_decode($print_block);
		$print_block_list = $print_block_rslt->print_block_list;
		$print_table_list = $print_block_rslt->print_table_list;
		echo json_encode(array('success' => true, 'message'=>'Print block deleted Successfully!!!','print_block_list'=>$print_block_list,'print_table_list'=>$print_table_list));
	}
	/* ==============================================================*/
	/* ================== PRINT BLOCK OPEARTION - END  ==============*/
	/* ==============================================================*/	
	
	/* ==============================================================*/
	/* ================ PRINT TABLE OPEARTION - START  ==============*/
	/* ==============================================================*/	
	public function get_print_table_info(){
		$print_table_list  = (int)$this->input->post('print_table_list');
		$print_qry  = 'SELECT * FROM cw_print_block where prime_print_block_id = "'.$print_table_list.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();
		
		foreach($print_rslt as $rslt){
			$prime_print_block_id  = $rslt->prime_print_block_id;
			$print_block_module_id = $rslt->print_block_module_id;
			$print_block_for       = $rslt->print_block_for;
			$print_block_name      = $rslt->print_block_name;
			$print_block_type      = $rslt->print_block_type;
			$print_block_table     = explode(",",$rslt->print_block_table);
			$print_block_column    = $rslt->print_block_column;
			
			$table_list = array();
			$table_list[""] = "---- Select Table ----";
			foreach($print_block_table as $table_value){
				$table_name = substr((ucwords(str_replace("_"," ",$table_value))),3);
				$table_list[$table_value] = $table_name;
			}
			
			$prime_in   = '"'.str_replace(",",'","', $rslt->print_block_table);
			$custom_in  = str_replace(",",'_cf","', $rslt->print_block_table).'_cf"';
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
			
			$print_tab_query  = 'SELECT * FROM cw_print_table  WHERE print_table_for_id = "'.$prime_print_block_id.'" AND print_table_module_id = "'.$print_block_module_id.'" order by abs(line_sort)';
			$print_tab_info   = $this->db->query("CALL sp_a_run ('SELECT','$print_tab_query')");
			$print_tab_result = $print_tab_info->result();
			$print_tab_info->next_result();
			//print_r($print_tab_result);
			
			$print_table_for_id  = form_input(array( 'name' =>'print_table_for_id','id' =>'print_table_for_id', 'class' => 'form-control input-sm','value' =>$prime_print_block_id,'type'=>'Hidden'));
			$print_table_module_id = form_input(array( 'name' =>'print_table_module_id','id' =>'print_table_module_id', 'class' => 'form-control input-sm','value' =>$print_block_module_id,'type'=>'Hidden'));
			
			$table_tr_line  = "";
			$table_count    = 0;
			$condition_table_count = count($print_block_table) - 1; //round(count($condition_table)/2);
			for($i=1;$i<= $condition_table_count;$i++){
				$prime_print_table_id = 0;
				$line_prime_table        = "";
				$line_prime_col          = "";
				$line_join_type          = "";
				$line_join_table         = "";
				$line_join_col           = "";
				if($print_tab_result){
					$prime_print_table_id = $print_tab_result[$table_count]->prime_print_table_id;
					$line_prime_table     = $print_tab_result[$table_count]->line_prime_table;
					$line_prime_col       = $print_tab_result[$table_count]->line_prime_col;
					$line_join_type       = $print_tab_result[$table_count]->line_join_type;
					$line_join_table      = $print_tab_result[$table_count]->line_join_table;
					$line_join_col        = $print_tab_result[$table_count]->line_join_col;
				}
				
				$table_cond_for_id = form_input(array( 'name' =>"prime_print_table_id[]",'class' => 'form-control input-sm','value' =>$prime_print_table_id,'type'=>'Hidden'));
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
			$table_content = "$print_table_for_id $print_table_module_id
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
									<button class='btn btn-primary btn-sm' id='save_print_table'>Save</button>
								</div>";
		}
		
		$where_query  = 'SELECT * FROM cw_print_table_where WHERE  where_module_id = "'.$print_block_module_id.'" and where_for_id = "'.$prime_print_block_id.'" and trans_status = 1';
		$where_info   = $this->db->query("CALL sp_a_run ('SELECT','$where_query')");
		$where_result = $where_info->result();
		$where_info->next_result();
		if($where_result){
			$where_condition = $where_result[0]->where_condition;
		}else{
			$where_condition = "and";
		}		
		echo json_encode(array('success' => true, 'prime_print_block_id'=>$prime_print_block_id, 'print_block_module_id'=>$print_block_module_id,'print_table_block'=>$table_content, 'column_list'=>$column_list,'where_condition'=>$where_condition,));
	}
	public function save_print_table(){
		$print_table_for_id     = $this->input->post('print_table_for_id');
		$print_table_module_id  = $this->input->post('print_table_module_id');
		$prime_print_table_id   = $this->input->post('prime_print_table_id[]');
		$line_prime_table       = $this->input->post('line_prime_table[]');
		$line_prime_col         = $this->input->post('line_prime_col[]');
		$line_join_type         = $this->input->post('line_join_type[]');
		$line_join_table        = $this->input->post('line_join_table[]');
		$line_join_col          = $this->input->post('line_join_col[]');
		
		$logged_id     = $this->session->userdata('logged_id');		
		$today_date = date("Y-m-d h:i:s");
		$tab_count  = 0;
		
		$remove_query = 'UPDATE cw_print_table SET trans_status = 0,trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE print_table_for_id = "'.$print_table_for_id.'" and print_table_module_id = "'.$print_table_module_id.'"';
		$this->db->query("CALL sp_a_run ('RUN','$remove_query')");
		
		$table_count = count($line_prime_table);
		for($i=1;$i<= $table_count;$i++){
			$prime_print_table_id_val = $prime_print_table_id[$tab_count];
			$line_prime_table_val     = $line_prime_table[$tab_count];
			$line_prime_col_val       = $line_prime_col[$tab_count];
			$line_join_type_val       = $line_join_type[$tab_count];
			$line_join_table_val      = $line_join_table[$tab_count];
			$line_join_col_val        = $line_join_col[$tab_count];
			
			if((int)$prime_print_table_id_val === 0){
				$table_query = 'insert into cw_print_table (print_table_for_id,print_table_module_id,line_prime_table,line_prime_col,line_join_type,line_join_table,line_join_col,line_sort,trans_created_by,trans_created_date) value ("'.$print_table_for_id.'","'.$print_table_module_id.'","'.$line_prime_table_val.'","'.$line_prime_col_val.'","'.$line_join_type_val.'","'.$line_join_table_val.'","'.$line_join_col_val.'","'.$i.'","'.$logged_id.'","'.$today_date.'")';
			}else{
				$table_query = 'UPDATE cw_print_table SET trans_status = 1, line_prime_table = "'.$line_prime_table_val.'",line_prime_col = "'.$line_prime_col_val.'",line_join_type = "'.$line_join_type_val.'",line_join_table = "'.$line_join_table_val.'",line_join_col = "'.$line_join_col_val.'",line_sort = "'.$i.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$today_date.'" WHERE prime_print_table_id = "'.$prime_print_table_id_val.'"';
			}
			$this->db->query("CALL sp_a_run ('RUN','$table_query')");
			$tab_count++;
		}
		echo json_encode(array('success' => true, 'message'=>"Table Join successfully Updated"));
	}
	function save_print_where(){
		$where_for_id   = (int)$this->input->post('where_for_id');
		$where_module_id  = $this->input->post('where_module_id');
		$where_condition  = $this->input->post('where_condition');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_print_table_where WHERE  where_module_id = "'.$where_module_id.'" and where_for_id = "'.$where_for_id.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			$search_qry  = 'INSERT INTO cw_print_table_where (where_module_id, where_for_id,where_condition,trans_created_by, trans_created_date) VALUES ("'.$where_module_id.'","'.$where_for_id.'","'.$where_condition.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
			echo json_encode(array('success' => true,'message'=>"Where added successfully !!!"));
		}else{
			$prime_print_where_id = (int)$exist_result[0]->prime_print_where_id;
			$upd_qry  = 'UPDATE  cw_print_table_where SET where_module_id = "'.$where_module_id.'",where_for_id = "'.$where_for_id.'",where_condition = "'.$where_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_where_id = "'.$prime_print_where_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			echo json_encode(array('success' => true,'message'=>"Where updated successfully !!!"));
		}
	}
	// PROVIDE PICKLIST AND SESSION VALUES
	function get_column_info(){
		$where_module_id  = $this->input->post('where_module_id');
		$query_column     = $this->input->post('query_column');
		$label_name       = explode(".",$query_column);
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
	/* ================== PRINT TABLE OPEARTION - END  ==============*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================ PRINT DESIGN OPEARTION - START  ==============*/
	/* ==============================================================*/	
	public function save_print_design(){
		$print_design      = $this->input->post('content');
		$print_design      = str_replace('"',"~",$print_design);
		$print_design_for  = $this->input->post('assign_table_info');
		$logged_id         = $this->session->userdata('logged_id');
		$date              = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_print_design WHERE  print_design_for = "'.$print_design_for.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			$search_qry  = 'INSERT INTO cw_print_design (print_design_for, print_design,trans_created_by, trans_created_date) VALUES ("'.$print_design_for.'","'.$print_design.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
			echo json_encode(array('success' => true,'message'=>"Print design added successfully !!!"));
		}else{
			$prime_print_design_id = (int)$exist_result[0]->prime_print_design_id;
			$upd_qry  = 'UPDATE  cw_print_design SET print_design_for = "'.$print_design_for.'",print_design = "'.$print_design.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_design_id = "'.$prime_print_design_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			echo json_encode(array('success' => true,'message'=>"Print design updated successfully !!!"));
		}
	}
	public function assign_table_info(){
		$assign_table_info  = $this->input->post('assign_table_info');
		$print_qry  = 'SELECT * FROM cw_print_block where print_block_for = "'.$assign_table_info.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();	
		$print_data->next_result();
		$assign_table_block[] = "--- Select Print block ---";
		$list_view_value[""]  = "--- Select list View ---";		
		foreach($print_rslt as $rslt){
			$prime_print_block_id   = $rslt->prime_print_block_id;
			$print_block_type       = $rslt->print_block_type;
			$print_block_name       = ucwords($rslt->print_block_name);			
			$assign_table_block[$prime_print_block_id] = $print_block_name;
			if((int)$print_block_type === 2){
				$replce_block = "@".strtolower(str_replace(" ","_",$print_block_name))."@";
				$list_view_value[$replce_block] = $print_block_name;
			}			
		}
		
		$design_query  = 'SELECT * FROM cw_print_design WHERE  print_design_for = "'.$assign_table_info.'" and trans_status = 1';
		$design_info   = $this->db->query("CALL sp_a_run ('SELECT','$design_query')");
		$design_result = $design_info->result();
		$design_info->next_result();
		$print_design = "";
		if($design_result){
			$print_design = $design_result[0]->print_design;
			$print_design = str_replace('~','"',$print_design);
		}
		echo json_encode(array('success' => TRUE,'assign_table_block' => $assign_table_block,'print_design' => $print_design,'list_view_value' => $list_view_value));
	}
	public function assign_table_block(){
		$assign_table_block  = $this->input->post('assign_table_block');
		$print_table_list  = (int)$this->input->post('print_table_list');
		$print_qry  = 'SELECT * FROM cw_print_block where prime_print_block_id = "'.$assign_table_block.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();
		
		$assign_label[""] = "--- Select label ---";
		$assign_short_label[""] = "--- Select Short label ---";
		$assign_value_for[""] = "--- Select Data Base value ---";
		$assign_ytd_label[""] = "--- Select Cumulative value ---";

		foreach($print_rslt as $rslt){
			$prime_print_block_id  = $rslt->prime_print_block_id;
			$print_block_module_id = $rslt->print_block_module_id;
			$print_block_for       = $rslt->print_block_for;
			$print_block_name      = $rslt->print_block_name;
			$print_block_type      = $rslt->print_block_type;
			$print_block_table     = $rslt->print_block_table;
			$print_block_column    = $rslt->print_block_column;
			
			$new_table             = explode(",",$print_block_table);			
			if((int)$print_block_type === 2){
				$assign_type = array(0=>"--- Select assign ---",5=>"List view value");
			}else{
				$assign_type = array(0=>"--- Select assign ---",1=>"Label Name",2=>"Short Label Name",3=>"Database value",4=>"Cumulative value");
			}
			
			foreach($new_table as $table){				
				$replce_table = $table.".";
				if($table === "cw_transactions"){
					$table_list  = "employees";	
					$column_list = str_replace($replce_table,"",$print_block_column);
				}else{
					$table_list  = str_replace("cw_","",$table);
					$column_list = str_replace($replce_table,"",$print_block_column);
				}
				
				$column_list     = str_replace(",",'","', $column_list);
				$get_colums_info = 'SELECT label_name,view_name,short_name FROM cw_form_setting WHERE  prime_module_id = "'.$table_list.'" and label_name in ("'.$column_list.'") and trans_status = 1 order by field_sort';				
				$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
				$colums_result = $colums_info->result();
				$colums_info->next_result();
				
				if(strpos($print_block_column, 'transactions_month') == true) {
					if($table === "cw_transactions"){
						$assign_label["Transactions Month"] = "Transactions Month";
						$assign_short_label["Transactions Month"] = "Transactions Month";
						$replce_column = "@transactions_month@";
						$assign_value_for[$replce_column] =  "Transactions Month - ". $view_name;
					}
				}
				//amount number is changed to in words for net pays
				if(strpos($print_block_column, 'net_pay') == true) {
					$assign_value_for['@net_pay_words@'] =  "net_pay_words - Net Pay Words";
				}else
				if(strpos($print_block_column, 'salary') == true) {
					$assign_value_for['@salary_words@'] =  "salary_words - Salary Words";
				}
				
				foreach($colums_result as $colums){
					$label_name  = $colums->label_name;
					$view_name   = $colums->view_name;
					if($label_name === "process_month"){
						$label_name = "transactions_month";
					}
					if($view_name === "Process Month"){
						$view_name = "Transactions Month";
					}
					$short_name  = "";
					if($colums->short_name){
						$short_name  = $colums->short_name;
					}
					$assign_label[$view_name] = $view_name;
					$assign_short_label[$short_name] = $short_name;
					$replce_column = "@".$label_name."@";
					$assign_value_for[$replce_column] = $label_name." - ".$view_name ;
					$replce_column_ytd = "@".$label_name."_ytd@";
					$assign_ytd_label[$replce_column_ytd] = $view_name;
				}				
			}
		}
		ksort($assign_type);
		ksort($assign_label);
		ksort($assign_short_label);
		ksort($assign_value_for);
		ksort($assign_ytd_label);
		echo json_encode(array('success' => true,'assign_type'=>$assign_type,'assign_label'=>$assign_label,'assign_short_label'=>$assign_short_label,'assign_value_for'=>$assign_value_for,'assign_ytd_label'=>$assign_ytd_label));
	}
	public function split_table_info(){
		$split_table_info  = $this->input->post('split_table_info');
		$split_colum = $this->split_table_info_rslt($split_table_info);
		echo json_encode(array('success' => true,'split_colum'=>$split_colum));
	}
	public function split_table_info_rslt($split_table_info){
		$print_qry  = 'SELECT * FROM cw_print_block where prime_print_block_id = "'.$split_table_info.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();

		$split_colum[""] = "--- Select Data Base value ---";
		foreach($print_rslt as $rslt){
			$prime_print_block_id  = $rslt->prime_print_block_id;
			$print_block_module_id = $rslt->print_block_module_id;
			$print_block_for       = $rslt->print_block_for;
			$print_block_name      = $rslt->print_block_name;
			$print_block_type      = $rslt->print_block_type;
			$print_block_table     = $rslt->print_block_table;
			$print_block_column    = $rslt->print_block_column;
			$new_table             = explode(",",$print_block_table);
			
			foreach($new_table as $table){
				$replce_table = $table.".";
				if($table === "cw_transactions"){
					$table_list  = "employees";
					$column_list = str_replace($replce_table,"",$print_block_column);
				}else{
					$table_list  = str_replace("cw_","",$table);
					$column_list = str_replace($replce_table,"",$print_block_column);
				}
				$column_list     = str_replace(",",'","', $column_list);
				
				$get_colums_info = 'SELECT label_name,view_name,short_name FROM cw_form_setting WHERE  prime_module_id = "'.$table_list.'" and label_name in ("'.$column_list.'") and trans_status = 1 order by field_sort';
				$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
				$colums_result = $colums_info->result();
				$colums_info->next_result();
				if($table === "cw_transactions"){
					$replce_column = "@transactions_month@";
					$split_colum[$replce_column] =  "Transactions Month - ". $view_name;
				}
				foreach($colums_result as $colums){
					$label_name  = $colums->label_name;
					$view_name   = $colums->view_name;
					$short_name  = "";
					if($colums->short_name){
						$short_name  = $colums->short_name;
					}
					$replce_column = "@".$label_name."@";
					$split_colum[$replce_column] = $label_name." - ".$view_name ;
				}				
			}
		}
		ksort($split_colum);
		return $split_colum;
	}
	public function split_save(){
		$split_table_info = $this->input->post('split_table_info');
		$split_info       = $this->input->post('split_info');
		$split_colum      = $this->input->post('split_colum');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		
		$print_qry  = 'SELECT * FROM cw_print_block where prime_print_block_id = "'.$split_table_info.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();
		$print_data->next_result();
		$print_block_module_id = $print_rslt[0]->print_block_module_id;
		
		$exist_query  = 'SELECT * FROM cw_print_split WHERE  split_table_info = "'.$split_table_info.'" and split_info = "'.$split_info.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			$split_qry  = 'INSERT INTO cw_print_split (split_table_info, split_info,split_colum,trans_created_by, trans_created_date) VALUES ("'.$split_table_info.'","'.$split_info.'","'.$split_colum.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$split_qry')");
			$table_info = $this->get_split_list($print_block_module_id);
			echo json_encode(array('success' => true,'message'=>"Split added successfully !!!",'table_info'=>$table_info));
		}else{		
			$prime_print_split_id = (int)$exist_result[0]->prime_print_split_id;
			$split_upd_qry  = 'UPDATE  cw_print_split SET split_table_info = "'.$split_table_info.'",split_info = "'.$split_info.'",split_colum = "'.$split_colum.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_split_id = "'.$prime_print_split_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$split_upd_qry')");
			$table_info = $this->get_split_list($print_block_module_id);
			echo json_encode(array('success' => true,'message'=>"Split updated successfully !!!",'table_info'=>$table_info));
		}
	}
	public function get_split_list($print_block_module_id){
		$get_split_list = 'SELECT * FROM cw_print_block left join cw_print_split on prime_print_block_id = split_table_info WHERE cw_print_block.trans_status = 1 and cw_print_split.trans_status = 1 and print_block_module_id = "'.$print_block_module_id.'"';
		$get_split_info = $this->db->query("CALL sp_a_run ('SELECT','$get_split_list')");
		$split_result   = $get_split_info->result();
		$get_split_info->next_result();
		$tr_line = "";
		$split_info_array = array(1=>"Loan Amount");
		foreach($split_result as $split){
			$prime_print_split_id = $split->prime_print_split_id;
			$split_table_info     = $split->split_table_info;
			$split_info           = $split->split_info;
			$split_colum          = $split->split_colum;
			$print_block_name     = $split->print_block_name;
			$split_info           = $split_info_array[$split_info];
			$tr_line .="<tr>
							<td>$print_block_name</td>
							<td>$split_info</td>
							<td>$split_colum</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_split_info('$prime_print_split_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_split_info('$prime_print_split_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
					</tr>";
		}
		$table_info = "<table class='table table-bordered table-stripted'>
						<thead>
							<tr>
								<th>Block Name</th>
								<th>Split up info</th>
								<th>Split column</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
							$tr_line
						</tbody>
					</table>";
		return $table_info;
	}
	//EDIT PRINT BLOCK OPERATION
	public function edit_split_info(){		
		$prime_print_split_id  = (int)$this->input->post('prime_print_split_id');
		$split_qry  = 'SELECT split_table_info,split_info,split_colum FROM cw_print_split where prime_print_split_id = "'.$prime_print_split_id.'" and trans_status = 1';
		$split_data = $this->db->query("CALL sp_a_run ('SELECT','$split_qry')");
		$split_rslt = $split_data->result();			
		$split_data->next_result();
		$split_table_info = $split_rslt[0]->split_table_info;
		$split_column = $this->split_table_info_rslt($split_table_info);
		echo json_encode(array('success' => TRUE,'split_rslt' => $split_rslt[0],'split_column_rslt' => $split_column));
	}
	public function remove_split_info(){
		$prime_print_split_id  = (int)$this->input->post('prime_print_split_id');
		$print_block_module_id = $this->input->post('print_block_module_id');
		$logged_id             = $this->session->userdata('logged_id');
		$date                  = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_print_split SET trans_status = 0,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_print_split_id = "'.$prime_print_split_id.'"';
		$delete  = $this->db->query("CALL sp_a_run ('UPDATE','$remove_qry')");
		$delete->next_result();		
		$table_info      = $this->get_split_list($print_block_module_id);
		echo json_encode(array('success' => true, 'message'=>'Print block deleted Successfully!!!','table_info'=>$table_info));
	}
	/* ==============================================================*/
	/* ================ PRINT DESIGN OPEARTION - END  ==============*/
	/* ==============================================================*/	
	
	public function get_block_table(){
		$print_block_for  = (int)$this->input->post('print_block_for');
		$print_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_print_info` where prime_print_info_id = \'$print_block_for\' and trans_status = 1')");
		$print_info_rlst  = $print_info->result();
		$print_info->next_result();
		if($print_info_rlst){
			$print_type = $print_info_rlst[0]->print_type;
			$print_block_column[""] = "---- Select Column ----";
			if((int)$print_type === 2){
				$get_colums = 'SELECT `TABLE_NAME`,`COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ("cw_employees","cw_transactions") AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT LIKE "%prime%"';
				$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
				$column_result = $column_info->result();
				$column_info->next_result();
				foreach($column_result as $column){
					$table_value      = $column->TABLE_NAME;
					$column_value     = $column->COLUMN_NAME;
					$module_name      = str_replace("cw_","",$table_value);
					if($table_value !== 'cw_transactions'){
						$view_name_qry    = 'select view_name from cw_form_setting where prime_module_id = "'.$module_name.'" and label_name = "'.$column_value.'"  and trans_status = "1"';
						$view_name_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_name_qry')");
						$view_name_result = $view_name_data->result();
						$view_name_data->next_result();	
						$column_name = strtoupper($view_name_result[0]->view_name);
						$table_name  = substr((ucwords(str_replace("_"," ",$table_value))),3);
						$table_name  = strtoupper($table_name);	
						if($column_name){
							$print_block_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;
						}
					}else{
						$table_name  = substr((ucwords(str_replace("_"," ",$table_value))),3);
						$table_name  = strtoupper($table_name);	
						$column_name = strtoupper(str_replace("_"," ",$column_value));
						$print_block_column[$table_value.".".$column_value] = $table_name . " - ". $column_name;
						
					}						
				}
			}
			echo json_encode(array('success' => TRUE ,'print_type'=>$print_type,'print_block_column'=>$print_block_column));
		}else{
			echo json_encode(array('success' => FALSE ,'message'=>'Contact Admin...!'));
		}
	}
}
?>