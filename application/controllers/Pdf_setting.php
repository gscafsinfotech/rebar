<?php
/**********************************************************
	   Filename: PDF Setting
	Description: PDF Setting for creating new Dynamic PDF template.
		 Author: Sathish Kumar
	 Created on: 24 May ‎2019
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
class Pdf_setting  extends Secure_Controller{
 
	public function __construct(){
		parent::__construct('pdf_setting');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_form_setting_headers());
		$this->load->view('pdf_setting/manage',$data);
	}
	/* ==============================================================*/
	/* ================== PDF COMMON OPEARTION - START ============*/
	/* ==============================================================*/	
	//PDF SEARCH OPEARTION
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
	public function view($pdf_info_module_id){
		
		if(!$pdf_info_module_id){
			$pdf_info_module_id = 0;
		}
		$data['pdf_info_module_id'] = $pdf_info_module_id;
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1 and prime_category_id !=1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		
		$print_for[""] = "---- PDF For ----";
		foreach($role_result as $for){
			$roles               .= $for->prime_category_id.",";
			$role_id             = $for->prime_category_id;
			$category_name       = $for->category_name;
			$print_for[$role_id] = $category_name;
		}
		$data['print_for']  = $print_for;

		$table_query = 'select * from cw_print_info where print_info_module_id ="'.$pdf_info_module_id.'" and trans_status = 1 and print_type = 1';
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
		$table_result = $table_info->result();
		$table_info->next_result();

		$table_list[""] = "---- Select Table ----";
		foreach($table_result as $table){
			$table_name = $table->prime_print_info_id;
			$print_info_name = $table->print_info_name;
			$table_list[$table_name] = $print_info_name;
		}
		$data['table_list'] = $table_list;
		
		$data['print_map_list'] = $this->get_pdf_map_list();
		$this->load->view("pdf_setting/pdf_info",$data);
	}
	/* ==============================================================*/
	/* ================== PDF COMMON OPEARTION - END  ===============*/
	/* ==============================================================*/
	public function get_employees_list(){
		$pdf_block_for  = $this->input->post('pdf_block_for');
		$table_query = 'SELECT * FROM cw_employees WHERE role in ('.$pdf_block_for.') and trans_status = 1';
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
		$table_result = $table_info->result();
		$table_info->next_result();
		$table_list = "<option value=''>---- Select Employee ----</option>";
		foreach($table_result as $table){
			$employees_id  = $table->prime_employees_id;
			$employee_code = $table->employee_code;
			$emp_name      = $table->emp_name;
			$table_list .= "<option value='$employees_id'>$employee_code - $emp_name</option>";
		}
		echo $table_list;
	}
	
	public function view_pdf_layout(){
		$print_id           = $this->input->post('print_id');
		$pdf_sheet_per_page = $this->input->post('pdf_sheet_per_page');		
		$pdf_block_type     = $this->input->post('pdf_block_type');
		$pdf_month          = $this->input->post('pdf_month');
		$pdf_block_for      = $this->input->post('pdf_block_for');
		$pdf_type           = $this->input->post('pdf_type');				
		$category_name      = $this->input->post('category_name');	
		$suppressed_check   = $this->input->post('suppressed_check');
		//Get Design Name from Print Design
		$design_query = 'SELECT * FROM cw_print_design inner join cw_print_info on cw_print_info.prime_print_info_id = print_design_for WHERE print_design_for ="'.$print_id.'"';
		$design_info   = $this->db->query("CALL sp_a_run ('SELECT','$design_query')");
		$design_result = $design_info->result();
		$design_info->next_result();	
		$design_name = $design_result[0]->print_info_name;
		
		$final_result = "";
		if($pdf_type === "2"){  // For Combine data
			if($pdf_block_type === "2"){  //For All Employees
				$ids_query = 'SELECT GROUP_CONCAT(DISTINCT prime_employees_id) as ids from cw_employees where role = "'.$pdf_block_for.'" and trans_status = 1 order by prime_employees_id';
				$ids_info   = $this->db->query("CALL sp_a_run ('SELECT','$ids_query')");
				$ids_result = $ids_info->result();
				$ids_info->next_result();
				$ids        = $ids_result[0]->ids;
				$ids        = explode(",",$ids);
			}else{ // For Seperate Employee
				$ids = $this->input->post('pdf_block_employees[]');
			}
			$final_result = "";			
			$i = 1;
			foreach ($ids as $id){
				if((int)$i % (int)$pdf_sheet_per_page === 0){
					$page_break   = "style='page-break-after:always;'";
				}else{
					$page_break   = "";
				}
				$data = $this->load_print_data($print_id,$id,$pdf_month);
				$final_result .= "<div $page_break>".$data."</div><br/><br/>";
				$i++;					
			}
			$pdf_name = "Combined_".$category_name;
			if($suppressed_check === "1"){
				$file = "suppressed_data.html";				
				$myfile = fopen($file, "w") or die("Unable to open file!");
				$final_result.= "<script src='https://code.jquery.com/jquery-1.9.1.min.js'></script>
				<script>  $( document ).ready(function() {
				$('table tr td').each(function() {
							var cellText = $.trim($(this).text());
							if (cellText.length == 0) {
								$(this).closest('td').hide();
							}else
							if(cellText == '0.00'){
								$(this).closest('td').prev('td').empty();
								$(this).closest('td').hide();
							}
						});
					}); </script>";
				$final_result = "<!DOCTYPE html><html>
								<head>
								<style>
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
								</style>
								</head>
				<body>".$final_result."</body></html>";
				fwrite($myfile, $final_result);
				unlink($file);
		}else{
			 $final_result = "<!DOCTYPE html><html>
								<head>
								<style>
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
								</style>
								</head><body>".$final_result."</body></html>";
		}
		
	    echo json_encode(array('success'=>TRUE,'design_name'=>$design_name,'pdf_name'=>$pdf_name,'suppressed_data'=>$suppressed_data,'final_result'=>$final_result));	
		}else{	
		//For Single Sheet for all employee code		
			$id = $this->input->post('Emp_id');
			$emp_query = 'SELECT * FROM cw_employees WHERE prime_employees_id = '.$id.' and trans_status = 1';
			$emp_info   = $this->db->query("CALL sp_a_run ('SELECT','$emp_query')");
			$emp_result = $emp_info->result();
			$emp_info->next_result();
			$pdf_name = $emp_result[0]->employee_code;
			$data = $this->load_print_data($print_id,$id,$pdf_month);
			if($suppressed_check === "1"){
				$file = "suppressed_data_".$id.".html";
				$myfile = fopen($file, "w") or die("Unable to open file!");
				$data.="
					<script src='https://code.jquery.com/jquery-1.9.1.min.js'></script>
					<script>  $( document ).ready(function() {
					$('table tr td').each(function() {
						var cellText = $.trim($(this).text());
						if (cellText.length == 0) {
							$(this).closest('td').hide();
						}else
						if(cellText == '0.00'){
							$(this).closest('td').prev('td').empty();
							$(this).closest('td').hide();
						}
					});
				}); </script>";	
				$final_result = "<!DOCTYPE html><html><head>
								<style>
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
								</style>
								</head>
								<body>".$data."</body></html>";
				fwrite($myfile, $final_result);
				unlink($file);
			}else{
				$final_result = "<!DOCTYPE html><html>
								<head>
								<style>
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
								</style>
								</head>
				<body>".$data."</body></html>";
			}
	    echo json_encode(array('success'=>TRUE,'design_name'=>$design_name,'pdf_name'=>$pdf_name,'suppressed_data'=>$suppressed_data,'final_result'=>$final_result));
		}
	}
	
	public function load_print_data($print_doc_id,$view_id,$pdf_month){		
		$data['print_sts'] = false;

		$design_qry    = 'SELECT * from cw_print_design where print_design_for = "'.$print_doc_id.'" and trans_status = 1';
		$design_data   = $this->db->query("CALL sp_a_run ('SELECT','$design_qry')");
		$design_result = $design_data->result();
		$design_data->next_result();
		$print_design  = $design_result[0]->print_design;

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
				//$module_name      = explode("_",$line_prime_table);
				//$module_name      = $module_name[1];
				$module_name      = str_replace("cw_","",$line_prime_table);
				$prime_id         = "prime_".$module_name."_id";
				$cf_id            = "prime_".$module_name."_cf_id";
				$cf_table_name    = $this->db->dbprefix($module_name."_cf");
				
				//$join_module_name      = explode("_",$line_join_table);
				//$join_module_name      = $join_module_name[1];
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
				//$module_name      = explode("_",$print_block_table);
				//$module_name      = $module_name[1];
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
				$where_condition = str_replace('@input_month@',$pdf_month,$where_condition);			
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
				$select_ytd_query = rtrim($select_ytd_query,',');
				$select_ytd_query = rtrim($select_ytd_query,' , ');
				$final_ytd_qry = "select $select_ytd_query from $line_table_query $pick_query  where $where_trans $where_condition  and transactions_month between \"$start_fin_date\" and \"$pdf_month\"";
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
			
			
			$final_qry = "select $select_query from $line_table_query $pick_query  where $where_trans $where_condition";
			$final_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
			$final_result = $final_data->result();
			$final_data->next_result();
			
			$tr_line = "";
			$th_line = "";
			$count = 0;
			$assign_date_formate_list  = array("DMY"=>"d-m-Y","YMD"=>"Y-m-d","MY"=>"M-Y","YM"=>"Y-M","D"=>"d","M"=>"M","Y"=>"Y");
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
								//echo $print_design; die;
								/*if(($value === "") || ($value === "0.00") || ($value === "0") || (!$value)){
									$value = "<span class='hide_cel'></span>";
									$print_design = str_replace($replace_val,$value,$print_design);
								}else{
*/									
									$print_design = str_replace($replace_val,$value,$print_design);	
								//}						
								foreach($assign_date_formate_list as $key=>$formate){
									$start         = "@".$key."_";
									$end           = "_".$key."@";
									$replace_val   = $start.$column.$end;								
									$date_value    =  date_create($value);
									$replace_value = date_format($date_value,$formate);			
									$print_design  = str_replace($replace_val,$replace_value,$print_design);
								}
							}else
							if($print_block_type === 2){
								/*if(($value === "") || ($value === "0.00") || ($value === "0") || (!$value)){

								}else{*/
									$td_line .= "<td style='text-align:center;'>$value</td>";
								//}									
							}
							
							if($count === 1){
								$head_name = ucwords(str_replace("_"," ",$column));
								if(($value === "") || ($value === "0.00") || ($value === "0") || (!$value)){

								}else{
									$th_line .= "<th style='text-align:center;'>$head_name</th>";
								}
							}
						}
					}
					
					
					if($print_block_type === 2){
						if($count === 1){
							if(($value === "") || ($value === "0.00") || ($value === "0") || (!$value)){

							}else{
								$th_line  = "$th_line";
								$tr_line .= "<tr>$td_line</tr>";
							}
						}				
						
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
		return $print_design;
	}
	
	public function get_loan_value($process_month,$employee_code){
			$process_month = explode("-",$process_month);
			$loan_month    = $process_month[0];
			$loan_year     = $process_month[1];
			$loan_qry = 'select emp_code,install_amount,cw_loan_type.loan_type from cw_loan_installment inner join cw_loan_type on  cw_loan_type.prime_loan_type_id = cw_loan_installment.loan_type where cw_loan_installment.trans_status = 1 and cw_loan_installment.emp_code ="'.$employee_code.'" and cw_loan_installment.install_year ="'.$loan_year.'" and cw_loan_installment.install_month ='.$loan_month;
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
	
	public function pdf(){
		$content            = $this->input->post('content');
		$design_name        = $this->input->post('design_name');
		$pdf_name           = $this->input->post('pdf_name');
		$pdf_paper_size     = $this->input->post('pdf_paper_size');
		$pdf_sheet_type     = $this->input->post('pdf_sheet_type');
		$pdf_month          = $this->input->post('pdf_month');
		$pdf_set_password   = $this->input->post('pdf_set_password');
		$design_name        = strtolower(str_replace(" ","_",$design_name));
		
		// Load pdf library 			
        $this->load->library('pdf');          	
        // Load HTML content 
        $this->dompdf->loadHtml($content);
		//$this->dompdf->setBasePath('/dist/froala/froala_style.min.css');
		
        // (Optional) Setup the paper size and orientation
        $this->dompdf->setPaper($pdf_paper_size, $pdf_sheet_type);
        // Render the HTML as PDF
        $this->dompdf->render();
        if($pdf_set_password === "1"){
        	//SET Production
	        $password = $pdf_name;
	        $this->dompdf->get_canvas()->get_cpdf()->setEncryption($password, $password);
        }        
        // Output the generated PDF (1 = download and 0 = preview)        
        $output = $this->dompdf->output();
        $folder = "./payslip/".$pdf_month."_".$design_name;
        //Check Folder Exist
        if (!file_exists($folder)){
		    mkdir($folder, 0777, true);
		}
		//Check File Exist
        if(file_exists($folder."/".$pdf_name.".pdf")){
		    unlink($folder."/".$pdf_name.".pdf");
		}
		
        file_put_contents($folder."/".$pdf_name.".pdf" , $output);

        echo json_encode(array('success'=>TRUE,'folder'=>$folder));            
	}
	
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
				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
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
		//echo $result . "Rupees  " . $points . " Paise";
		return $result;
	}
	/* ==============================================================*/
	/* =================== PDF BASIC OPEARTION - END  =============*/
	/* ==============================================================*/	
	
	//map pdf settings
	public function save_payroll_map(){
		$prime_pdf_map_id    = $this->input->post('prime_pdf_map_id');
		$pdf_block_for       = $this->input->post('pdf_block_for');
		$pdf_info_name       = $this->input->post('pdf_info_name');
		$pdf_sheet_per_page  = $this->input->post('pdf_sheet_per_page');
		$pdf_set_password    = $this->input->post('pdf_set_password');
		$pdf_paper_size      = $this->input->post('pdf_paper_size');
		$pdf_sheet_type      = $this->input->post('pdf_sheet_type');
		$logged_id           = $this->session->userdata('logged_id');
		$date                = date("Y-m-d h:i:s");
		if((int)$prime_pdf_map_id === 0){
			$is_exist_qry  = 'select count(prime_pdf_map_id) as exit_count from cw_pdf_setting where pdf_block_for = "'.$pdf_block_for.'" and pdf_info_name ="'.$pdf_info_name.'" and trans_status = 1';
			$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
			$is_exist_rslt = $is_exist_data->result();
			$is_exist_data->next_result();
			$is_exist_rslt = $is_exist_rslt[0]->exit_count;
			if((int)$is_exist_rslt === 0){
				$save_pdf_qry  = 'INSERT INTO cw_pdf_setting (pdf_block_for, pdf_info_name, pdf_sheet_per_page, pdf_set_password, pdf_paper_size, pdf_sheet_type, trans_created_by, trans_created_date) VALUES ("'.$pdf_block_for.'","'.$pdf_info_name.'","'.$pdf_sheet_per_page.'","'.$pdf_set_password.'","'.$pdf_paper_size.'","'.$pdf_sheet_type.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$save_pdf_qry')");
				$print_map_list =  $this->get_pdf_map_list();
				echo json_encode(array('success' => true, 'message' => "Successfully Mapped!!!",'print_map_list'=>$print_map_list));
			}else{
				$print_map_list =  $this->get_pdf_map_list();
				echo json_encode(array('success' => False, 'message' => "Already pdf design is mapped, edit to update the values?",'print_map_list'=>$print_map_list));
			}
		}else{
			$upd_qry  = 'UPDATE  cw_pdf_setting SET pdf_block_for = "'.$pdf_block_for.'",pdf_info_name = "'.$pdf_info_name.'",pdf_sheet_per_page = "'.$pdf_sheet_per_page.'",pdf_set_password = "'.$pdf_set_password.'",pdf_paper_size = "'.$pdf_paper_size.'",pdf_sheet_type = "'.$pdf_sheet_type.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_pdf_map_id = "'.$prime_pdf_map_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			$print_map_list =  $this->get_pdf_map_list();
			echo json_encode(array('success' => true, 'message' => "Successfully Mapped!!!",'print_map_list'=>$print_map_list));
		}
	}
	
	public function get_pdf_map_list(){
		$pdf_map_qry  = 'select * from cw_pdf_setting left join cw_category on cw_category.prime_category_id=cw_pdf_setting.pdf_block_for left join cw_print_info on cw_print_info.prime_print_info_id =cw_pdf_setting.pdf_info_name where cw_pdf_setting.trans_status = 1';
		$pdf_map_data = $this->db->query("CALL sp_a_run ('SELECT','$pdf_map_qry')");
		$pdf_map_rslt = $pdf_map_data->result();
		$pdf_map_data->next_result();
		$tr_line 	  = "";		
		foreach($pdf_map_rslt as $map_rslt){
			$prime_pdf_map_id   = $map_rslt->prime_pdf_map_id;
			$pdf_block_for      = $map_rslt->category_name;
			$pdf_info_name      = $map_rslt->print_info_name;
			$tr_line .= "<tr>
							<td>$pdf_block_for</td>
							<td>$pdf_info_name</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_print_map('$prime_pdf_map_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_print_map('$prime_pdf_map_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$print_map_list = "<h5 style='text-align:center;'>Payslip Mapping List</h5>
							<table class='table table-bordered table-stripted' id='print_info_table'>
							<tr class='inline_head'>
								<th>Payslip For</th>
								<th>Map Design</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		return "$print_map_list";
	}
	
	//EDIT PRINT INFO OPERATION
	public function edit_print_map(){
		$prime_pdf_map_id  = (int)$this->input->post('prime_pdf_map_id');
		$print_map_qry  = 'select * from cw_pdf_setting where prime_pdf_map_id = "'.$prime_pdf_map_id.'" and trans_status = 1';
		$print_map_data = $this->db->query("CALL sp_a_run ('SELECT','$print_map_qry')");
		$print_map_rslt = $print_map_data->result();			
		$print_map_data->next_result();
		echo json_encode(array('success' => TRUE,'print_map_rslt' => $print_map_rslt[0]));
	}
	
	public function remove_print_map(){
		$prime_pdf_map_id  = (int)$this->input->post('prime_pdf_map_id');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE  cw_pdf_setting SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_pdf_map_id = "'.$prime_pdf_map_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$print_map_list =  $this->get_pdf_map_list();
		echo json_encode(array('success' => true,'message'=>'Mapping is deleted Successfully !!!','print_map_list'=>$print_map_list));
	}
	
	public function check_map_design(){
		$pdf_block_for     = (int)$this->input->post('pdf_block_for');
		$pdf_module_id     = $this->input->post('pdf_module_id');
		
		//design already exit or not print type 1 is payslip (only generate the pdf)
		$print_design_exit_qry  = 'select count(prime_print_info_id) as design_count,prime_print_info_id from cw_print_info where find_in_set ('.$pdf_block_for.',print_info_for) and print_type = 1 and print_info_module_id ="'.$pdf_module_id.'" and trans_status = 1';
		$print_design_exit_data = $this->db->query("CALL sp_a_run ('SELECT','$print_design_exit_qry')");
		$print_design_exit_rslt = $print_design_exit_data->result();
		$print_design_exit_data->next_result();
		$design_count  = $print_design_exit_rslt[0]->design_count;
		$print_info_id = $print_design_exit_rslt[0]->prime_print_info_id;
		if((int)$design_count > 0){
			$print_pattern_exit_qry  = 'select count(prime_print_design_id) as design_count from cw_print_design where print_design_for ="'.$print_info_id.'" and trans_status = 1';
			$print_pattern_exit_data = $this->db->query("CALL sp_a_run ('SELECT','$print_pattern_exit_qry')");
			$print_pattern_exit_rslt = $print_pattern_exit_data->result();
			$print_pattern_exit_data->next_result();
			$print_patter_count  = $print_pattern_exit_rslt[0]->design_count;
			if((int)$print_patter_count > 0){
				echo json_encode(array('success' => true,'message'=>'Ok Proceed!!!'));
			}else{
				echo json_encode(array('success' => False,'message'=>'Please create the payslip pattern for print design!!!'));
			}
		}else{
			echo json_encode(array('success' => False,'message'=>'Please create the payslip!!!'));
		}
	}
	
	public function map_exit_design(){
		$pdf_block_for     = (int)$this->input->post('pdf_block_for');
		$pdf_info_name     = (int)$this->input->post('pdf_info_name');
		$print_map_check_qry  = 'select count(*) as map_count from cw_pdf_setting where pdf_block_for = "'.$pdf_block_for.'" and pdf_info_name = "'.$pdf_info_name.'" and  trans_status = 1';
		$print_map_check_data = $this->db->query("CALL sp_a_run ('SELECT','$print_map_check_qry')");
		$print_map_check_rslt = $print_map_check_data->result();
		$print_map_check_data->next_result();
		$map_count = $print_map_check_rslt[0]->map_count;
		if($map_count){
			echo json_encode(array('success' => false,'message'=>'Already Mapped, please check it!!!'));
		}else{
			echo json_encode(array('success' => true,'message'=>'Ok Proceed!!!'));
		}
	}
}
?>