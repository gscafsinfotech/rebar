<?php
/**********************************************************
	   Filename: Report_controller.php
	Description: Report Controller for all module report is generator.
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
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("Secure_Controller.php");
abstract class Report_controller extends Secure_Controller{
	
	public $control_name;
	public $logged_id;
	public $logged_role;
	public $report_id;
	public $report_name;
	public $date_filter;
	public $group_column;
	public $sum_column;
	public $sum_qry_column;
	public $sub_tot_show;
	public $master_pick;
	
	public function __construct($module_id = NULL){
		parent::__construct($module_id);
		$this->control_name = strtolower($this->router->fetch_class());
		$this->logged_id    = $this->session->userdata('logged_id');
		$this->logged_role  = $this->session->userdata('logged_role');
	}
	
	//USER ACCESS THE REPORT CHECKING
	public function isvalid($report_id){
		$this->report_id = $report_id;
		$valid_user_qry     = 'select * from cw_report_setting where trans_status = 1 and prime_report_setting_id = "'.$report_id.'" and FIND_IN_SET("'.$this->logged_role.'",report_for)';
		$valid_user_info    = $this->db->query("CALL sp_a_run ('SELECT','$valid_user_qry')");
		$valid_user_result  = $valid_user_info->result();
		$user_count   = $valid_user_info->num_rows();
		$valid_user_info->next_result();
		if($user_count){
			$this->collect_base_info();
			return true;
		}else{
			return false;
		}
	}
	
	public function collect_base_info(){
		$this->get_table_filter_info();
		$this->get_base_query();
		$this->total_sum_column();
	}
	
	//BASE QUERY CONSTRUCTIONS
	public function get_base_query(){
		$report_tab_query = 'select * from cw_report_setting where prime_report_setting_id = "'.$this->report_id.'" and trans_status = "1"';
		$report_tab_data   = $this->db->query("CALL sp_a_run ('SELECT','$report_tab_query')");
		$report_tab_result = $report_tab_data->result();
		$report_tab_data->next_result();
		$base_query = "";
		if($report_tab_result){
			$report_setting_id  = $report_tab_result[0]->prime_report_setting_id;
			$report_name        = $report_tab_result[0]->report_name;
			$report_for         = $report_tab_result[0]->report_for;
			$col_per_row        = $report_tab_result[0]->col_per_row;
			$table_info         = $report_tab_result[0]->table_info;
			$table_column       = $report_tab_result[0]->table_column;
			$this->report_name  = $report_name;
			$prime_table        = $report_tab_result[0]->table_info;
			$table_count        = explode(",",$table_info);
			$tab_count          = count($table_count);
			$date_filter        = $report_tab_result[0]->date_filter;
			$this->date_filter  = $date_filter;
			$date_column        = $report_tab_result[0]->date_column;
			$this->date_column  = $date_column;
			$group_column       = $report_tab_result[0]->group_column;
			$this->group_column = $group_column;
			$sub_tot_show       = $report_tab_result[0]->sub_tot_show;
			$this->sub_tot_show = $sub_tot_show;
			if((int)$tab_count > 1){
				//WHERE TABLE JOIN DATA
				$table_query = 'select * from cw_report_table where trans_status = 1 and join_for = "'.$report_setting_id.'" ORDER BY abs(line_sort) asc';
				$table_data   = $this->db->query("CALL sp_a_run ('SELECT','$table_query')");
				$table_result = $table_data->result();
				$table_data->next_result();
				foreach($table_result as $table){
					$line_prime_table      = $table->line_prime_table;
					$line_prime_col        = $table->line_prime_col;
					$line_join_type        = $table->line_join_type;
					$line_join_table       = $table->line_join_table;
					$line_join_col         = $table->line_join_col;
					$line_sort             = $table->line_sort;
					$prime_table           = $table->line_prime_table;
					$join_module_name      = str_replace("cw_","",$line_join_table);
					if((int)$line_sort === 1){
						$line_table_query .= " $line_prime_table $line_join_type join $line_join_table on $line_join_col = $line_prime_col";
					}else{
						$line_table_query .= " $line_join_type join $line_join_table on $line_join_col = $line_prime_col"; 
					}
				}
			}else{
				$line_table_query = " $table_info";
			}
		}
		
		//WHERE CONDITIONS SEARCH
		$where_condition = "";
		$table_search_query = 'select where_condition from cw_report_where where where_for_id = "'.$this->report_id.'" and trans_status = "1"';
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
		$base_query = "select @SELECT from $line_table_query";
		$this->base_query   = $base_query;
		$this->prime_table  = $prime_table;
	}
	
	public function get_table_filter_info(){	
		$table_qry = 'select * from cw_report_table_view left join cw_form_setting on cw_form_setting.prime_module_id = CASE WHEN cw_report_table_view.module_column = "transactions" THEN "employees" ELSE cw_report_table_view.module_column END  and cw_report_table_view.table_column = cw_form_setting.label_name where cw_report_table_view.trans_status = 1 and report_id = "'.$this->report_id.'" order by cw_report_table_view.table_sort asc';
		$table_info   = $this->db->query("CALL sp_a_run ('SELECT','$table_qry')");
		$result       = $table_info->result();		
		$table_info->next_result();
		$table_array = array();
		foreach($result as $rslt){
			$colum_name         = "cw_".$rslt->module_column.".".$rslt->table_column;
			$pattern            = '/^cw_([a-z]+)\.\b/';
			$replacement        = '';
			$label_list         = preg_replace($pattern, $replacement, $colum_name);
			$colum_name         = explode(".",$colum_name);
			$module_name        = $colum_name[0];
			$module_id          = str_replace("cw_","",$module_name);
			$label_name         = $colum_name[1];
			$prime_form_id      = (int)$rslt->prime_form_id;
			$prime_module_id    = $rslt->prime_module_id;
			$input_view_type    = (int)$rslt->input_view_type;
			$input_for          = (int)$rslt->input_for;
			$field_type         = (int)$rslt->field_type;
			$label_id           = $rslt->label_name;
			$label_name         = ucwords($rslt->view_name);
			$field_length       = $rslt->field_length;
			$field_decimals     = $rslt->field_decimals;
			$pick_list_type     = (int)$rslt->pick_list_type;
			$pick_list          = $rslt->pick_list;
			$pick_table         = $rslt->pick_table;
			$auto_prime_id      = $rslt->auto_prime_id;
			$auto_dispaly_value = $rslt->auto_dispaly_value;
			$field_isdefault    = (int)$rslt->field_isdefault;
			$table_column       = $rslt->table_column;
			
			if(($module_id === "transactions") && (!empty($prime_form_id)) && $field_type !== 5){
					$this->select_query  .= "cw_transactions.$label_id,";
					$table_array[]        = array('field_type'=>$field_type,'label_name'=>$label_id,'view_name'=>$label_name);
			}else{
				$table_name     = "cw_".$prime_module_id;
				if($module_id === "transactions"){
					$pick_sel_table = $module_name;
				}else{
					$pick_sel_table = "$table_name";
				}
				if((int)$prime_form_id === 0){
					$label_name = ucwords(str_replace("_"," ",$label_list));
					$label_id   = $label_list;
				}
				//TABLE HEADER
				//if($field_type){
					$table_array[]  = array('field_type'=>$field_type,'label_name'=>$label_id,'view_name'=>$label_name);
				//}
				//SEARCH FILTERS
				if(($field_type === 5) || ($field_type === 7)){
					if($pick_list_type === 1){
						$pick_list_val   = explode(",",$pick_list);
						$pick_list_val_1 = $pick_list_val[0];
						$pick_list_val_2 = $pick_list_val[1];
						
						$pick_query = "select $pick_list from $pick_table where trans_status = 1";
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						if($pick_result){
							$pick_key   = array_column($pick_result, $pick_list_val_1);
							$pick_val   = array_column($pick_result, $pick_list_val_2);
							$final_pick = array_combine( $pick_key, $pick_val);
						}
						$final_pick = array("" => "---- $label_name ----") + $final_pick;
						$array_list   = $final_pick;
						 //array_unshift($final_pick,"---- $label_name ----");
						$this->all_pick[$prime_form_id] = $final_pick;
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
						if($pick_result){
							$pick_key   = array_column($pick_result, $pick_list_val_1);
							$pick_val   = array_column($pick_result, $pick_list_val_2);				
							$final_pick = array_combine( $pick_key, $pick_val);
						}
						$final_pick = array("" => "---- $label_name ----") + $final_pick;
						$array_list   = $final_pick;
						//array_unshift($final_pick,"---- $label_name ----");
						$this->all_pick[$prime_form_id] = $final_pick;
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
							$this->select_query .= "$pick_sel_table.$label_id,";
						}else
						if($field_isdefault === 2){
							$this->select_query .= "$pick_sel_table.$label_id,";
						}
					}
				}
				if((int)$prime_form_id !== 0){
					$this->fliter_list[] = array('label_id'=> $label_id,'label_name'=> $label_name, 'field_isdefault'=> $field_isdefault, 'array_list'=> $array_list, 'field_type'=> $field_type);
				}
				$this->form_info[] = array('prime_form_id'=>$prime_form_id,'prime_module_id'=>$prime_module_id,'field_type'=>$field_type,'pick_list'=>$pick_list,'pick_table'=>$pick_table,'input_view_type'=>$input_view_type,'auto_prime_id'=>$auto_prime_id,'auto_dispaly_value'=>$auto_dispaly_value,'label_id'=>$label_id, 'field_isdefault'=> $field_isdefault,'pick_list_type'=>$pick_list_type);
			}	
		}
		
		
		//get new column search value select query (add two column list values)		
		$add_column_qry    = 'select * from cw_report_add_column where trans_status = 1 and report_id = "'.$this->report_id.'"';
		$add_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$add_column_qry')");
		$add_column_result = $add_column_info->result();
		$add_column_info->next_result();
		$select_qry = "";
		if(!empty($add_column_result)){
			foreach($add_column_result as $result){
				$select_qry .= $result->select_condition;
				$select_qry = str_replace("@","",$select_qry);
			}
			$select_qry = ltrim($select_qry,',');
			$this->select_query .= $select_qry;
		}
		$this->select_query = rtrim($this->select_query,',');
		$this->form_info    = json_decode(json_encode($this->form_info));
		$this->table_info   = json_decode(json_encode($table_array));
	}
	
	//get column wise total record
	public function total_sum_column(){
		$sum_column_qry    = 'select * from cw_report_tot_column where trans_status = 1 and report_id = "'.$this->report_id.'"';
		$sum_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$sum_column_qry')");
		$sum_column_result = $sum_column_info->result();
		$sum_column_info->next_result();
		$sum_column = $sum_column_result[0]->sum_column_name;
		$sum_column = explode(",",$sum_column);
		$sum_count = count($sum_column_result);
		$add_column_qry    = 'select select_condition,add_name from cw_report_add_column where trans_status = 1 and report_id = "'.$this->report_id.'"';
		$add_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$add_column_qry')");
		$add_column_result = $add_column_info->result();
		$add_column_info->next_result();
		$add_column_result = array_column($add_column_result,'select_condition','add_name');
		foreach($sum_column as $sum_info){
			$split = explode(".",$sum_info);
			if($add_column_result){
				if(array_key_exists($split[1],$add_column_result)){
					$select_condition  = $add_column_result[$split[1]];
					$select_condition = ltrim(str_replace("@","",$select_condition),',');
					$this->sum_qry_column  .= "sum".$select_condition.",";
					$this->sum_column	   .= $split[1].",";
				}else{
					$this->sum_qry_column .= "sum($sum_info) as $split[1],"; 
					$this->sum_column	  .=  $split[1].",";
				}
			}else{
				$this->sum_qry_column .= "sum($sum_info) as $split[1],"; 
				$this->sum_column	  .=  $split[1].",";
			}
		}
		if((int)$sum_count === 1){
			$this->sum_column      = rtrim($this->sum_column,',');
			$this->sum_qry_column  = rtrim($this->sum_qry_column,',');
		}else{
			$this->sum_qry_column  = "";
		}
	}
}
?>