<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Co_register_report  extends Action_controller{	
	public function __construct(){
		parent::__construct('co_register_report');
		$this->collect_base_info();
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;

		$from_query = 'select * from cw_form_setting where prime_module_id = "co_register" and field_show = "1" and search_show = 1 ORDER BY input_for,field_sort asc';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$from_query')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$fliter_list = $this->get_filter_data($form_result);
		$data['fliter_list']  = $fliter_list;
		$this->load->view("$this->control_name/manage",$data);
	}
	public function get_filter_data($form_result){
		$filter = array();
		foreach($form_result as $setting){
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
			if($label_id != 'role' && $label_id != 'employee_code' && $label_id != 'emp_name'){
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
					}
				}		
				if(($input_view_type === 1) || ($input_view_type === 2)){
					$filter[] = array('label_id'=> $label_id, 'field_isdefault'=> $field_isdefault, 'array_list'=> $array_list, 'field_type'=> $field_type);
				}
			}
		}
		return $filter;
	}
	public function datacount_check(){
			$co_reg_qry = 'select co_number,cw_uspm.uspm,cw_project_and_drawing_master.rdd_no,cw_client.client_name,cw_project_and_drawing_master.project_name,GROUP_CONCAT(cw_project_and_drawing_master_drawings.drawing_no) as drawing_no,cw_co_register.drawing_description from cw_co_register inner join cw_uspm on cw_uspm.prime_uspm_id = cw_co_register.uspm inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_co_register.rdd_no inner join cw_client on cw_client.prime_client_id = cw_co_register.client_name inner join cw_project_and_drawing_master_drawings on find_in_set(cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id,cw_co_register.drawing_no) where cw_co_register.trans_status = 1 group by co_number order by co_number';
	}














	// public function datacount_check(){
	// 	$co_reg_qry = 'select co_number,cw_uspm.uspm,cw_project_and_drawing_master.rdd_no,cw_client.client_name,cw_project_and_drawing_master.project_name,GROUP_CONCAT(cw_project_and_drawing_master_drawings.drawing_no) as drawing_no,cw_co_register.drawing_description from cw_co_register inner join cw_uspm on cw_uspm.prime_uspm_id = cw_co_register.uspm inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_co_register.rdd_no inner join cw_client on cw_client.prime_client_id = cw_co_register.client_name inner join cw_project_and_drawing_master_drawings on find_in_set(cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id,cw_co_register.drawing_no) where cw_co_register.trans_status = 1 group by co_number order by co_number';
	// 	$co_reg_info   = $this->db->query("CALL sp_a_run ('SELECT','$co_reg_qry')");
	// 	$co_reg_result = $co_reg_info->result();
	// 	$co_reg_info->next_result();

	// 	$team_qry = 'select prime_team_id,cw_team.team_name from cw_co_register inner join cw_team on find_in_set(cw_team.prime_team_id,cw_co_register.team) where cw_co_register.trans_status = 1 group by co_number order by co_number';
	// 	$team_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
	// 	$team_result = $team_info->result();
	// 	$team_info->next_result();




	// 	echo "<pre>";
	// 	print_r($team_result);die;
	// }
}
?>