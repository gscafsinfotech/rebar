<?php
class Module extends CI_Model{
    function __construct(){
        parent::__construct();
    }

	public function get_module_name($module_id){
		$query = $this->db->get_where('modules', array('module_id' => $module_id), 1);
		if($query->num_rows() == 1){
			$row = $query->row();
			return $this->lang->line($row->name_lang_key);
		}
		return $this->lang->line('error_unknown');
	}
	
	public function get_allowed_modules($logged_id){
		if((int)$this->session->userdata('logged_role') === 12){
			$this->db->from('modules');
			$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
			$this->db->join('grants_customer', 'permissions.permission_id = grants_customer.permission_id');
			$this->db->where('prime_customer_id', $logged_id);
			$this->db->order_by('sort', 'asc');
			return $this->db->get();
		}else{
			$this->db->from('modules');
			$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
			$this->db->join('grants', 'permissions.permission_id = grants.permission_id');
			$this->db->where('prime_employees_id', $logged_id);
			$this->db->order_by('sort', 'asc');
			return $this->db->get();
		}		
	}
	public function get_header_menu($logged_id){
		if((int)$this->session->userdata('logged_role') === 12){
			$this->db->select('main_menu.menu_name,modules.module_id,module_name,sub_menu_name');
			$this->db->from('modules');
			$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
			$this->db->join('grants_customer', 'permissions.permission_id = grants_customer.permission_id');
			$this->db->join('main_menu', 'main_menu.prime_menu_id = modules.menu_id');
			$this->db->join('sub_menu', 'sub_menu.prime_sub_menu_id = modules.sub_menu_id','left');
			$this->db->where('prime_customer_id', $logged_id);
			$this->db->where('modules.trans_status',1);
			$this->db->where('modules.show_module',1);
			$this->db->where('main_menu.trans_status',1);
			$this->db->order_by('menu_sort,sort', 'asc');
			$query =  $this->db->get();
			return $query->result();
		}else{
			$this->db->select('main_menu.menu_name,modules.module_id,module_name,sub_menu_name');
			$this->db->from('modules');
			$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
			$this->db->join('grants', 'permissions.permission_id = grants.permission_id');
			$this->db->join('main_menu', 'main_menu.prime_menu_id = modules.menu_id');
			$this->db->join('sub_menu', 'cw_sub_menu.prime_sub_menu_id = modules.sub_menu_id','left');
			$this->db->where('prime_employees_id', $logged_id);
			$this->db->where('modules.trans_status',1);
			$this->db->where('modules.show_module',1);
			$this->db->where('main_menu.trans_status',1);
			$this->db->order_by('menu_sort,sort', 'asc');
			$query =  $this->db->get();
			return $query->result();
		}
	}
	
	//GET REPORT DETAILS FOR ROLE BASED HEADER -- 13MARCH2019
	public function get_report_menu($logged_user){
		$logged_id   = $logged_user->prime_employees_id;
		$logged_role = $logged_user->role;
		$this->db->select('prime_report_setting_id,report_name');
		$this->db->from('report_setting');
		$this->db->where('report_setting.trans_status',1);
		$this->db->where('FIND_IN_SET('.$logged_role.',report_for)!=',0);
		$this->db->order_by('prime_report_setting_id', 'asc');
		$query =  $this->db->get();
		//echo $this->db->last_query();
		return $query->result();
	}
	//GET REPORT DETAILS FOR ROLE BASED HEADER -- 13MARCH2019
/*	public function get_template_menu($logged_user){
		$logged_id   = $logged_user->prime_employees_id;
		$logged_role = $logged_user->role;
		$this->db->select('prime_bank_template_setting_id,template_name');
		$this->db->from('bank_template_setting');
		$this->db->where('bank_template_setting.trans_status',1);
		$this->db->where("template_for LIKE '%".$logged_role."%'");
		//$this->db->where("template_for IN (".$logged_role.")",NULL, false);
		//$this->db->where("FIND_IN_SET('".$logged_role."',template_for)!=",0);
		$this->db->order_by('prime_bank_template_setting_id', 'asc');
		$query =  $this->db->get();
		return $query->result();
	}*/	
	/* USED IN BOTH EMPLOYEE AND CUSTOMER MODULE - START*/
	public function get_all_modules($control_name){
		if(strtoupper($control_name) === "EMPLOYEES"){
			$this->db->from('modules');
			$this->db->join('cw_main_menu', 'cw_main_menu.prime_menu_id = modules.menu_id');
			$this->db->join('sub_menu', 'cw_sub_menu.prime_sub_menu_id = modules.sub_menu_id','left');
			$this->db->order_by('abs(menu_sort)', 'asc');
			$this->db->where('modules.show_module',1);
			$query =  $this->db->get();
			return $query->result();
		}else{
			$query    = $this->db->query("SELECT * FROM cw_modules JOIN `cw_main_menu` ON `cw_main_menu`.`prime_menu_id` = cw_modules.menu_id left join cw_sub_menu on cw_sub_menu.prime_sub_menu_id = cw_modules.sub_menu_id where FIND_IN_SET('2',rights_to) and show_module = 1 ORDER BY abs(menu_sort) ASC");
			return $query->result();
		}		
	}
	
	public function has_grant($control_name,$permission_id, $logged_id){
		if($permission_id == null){
			return TRUE;
		}
		if(strtoupper($control_name) === "EMPLOYEES"){
			$query = $this->db->get_where('grants', array('prime_employees_id' => $logged_id, 'permission_id' => $permission_id), 1);			
		}else  
		if(strtoupper($control_name) === "EMPLOYEE_PERMISSION"){			
			$query = $this->db->get_where('employee_permission', array('role' => $logged_id, 'permission_id' => $permission_id), 1);			
		}else{
			$query = $this->db->get_where('grants_customer', array('prime_customer_id' => $logged_id, 'permission_id' => $permission_id), 1);
		}
		return((int)$query->num_rows() === 1);
	}
	public function has_access($control_name,$permission_id, $logged_id){
		$this->db->select('access_add,access_update,access_delete,access_search,access_export,access_import,grants_menu_id,grants_sub_menu_id');
		if(strtoupper($control_name) === "EMPLOYEES"){
			$this->db->from('grants');
			$this->db->where('prime_employees_id', $logged_id);			
		}else  
		if(strtoupper($control_name) === "EMPLOYEE_PERMISSION"){
			$this->db->from('employee_permission');
			$this->db->where('role', $logged_id);			
		}else{
			$this->db->from('grants_customer');
			$this->db->where('prime_customer_id', $logged_id);
		}
		$this->db->where('permission_id', $permission_id);
		return $this->db->get()->result_array();
	}
	
	public function update_grants($control_name,$logged_id,$grants_data,$access_data){
		if(strtoupper($control_name) === "EMPLOYEES"){
			$success = $this->db->delete('grants', array('prime_employees_id' => $logged_id));	
		}else{
			$success = $this->db->delete('grants_customer', array('prime_customer_id' => $logged_id));
		}
		if($success){
			foreach($grants_data as $permission_id){					
				$add = 0;
				if (in_array("$permission_id::add", $access_data)){
					$add = 1;
				}
				$update = 0;
				if (in_array("$permission_id::update", $access_data)){
					$update = 1;
				}
				$delete = 0;
				if (in_array("$permission_id::delete", $access_data)){
					$delete = 1;
				}
				$search = 0;
				if (in_array("$permission_id::search", $access_data)){
					$search = 1;
				}
				$export = 0;
				if (in_array("$permission_id::export", $access_data)){
					$export = 1;
				}
				$import = 0;
				if (in_array("$permission_id::import", $access_data)){
					$import = 1;
				}
				$this->db->select('menu_id,sub_menu_id');
				$this->db->from('modules');
				$this->db->where('module_id', $permission_id);		
				$menu_data      = $this->db->get()->row();
				$menu_id        = $menu_data->menu_id;
				$sub_menu_id    = $menu_data->sub_menu_id;
				if(strtoupper($control_name) === "EMPLOYEES"){
					$insert_values .= "(\"$permission_id\",\"$logged_id\",\"$menu_id\",\"$sub_menu_id\",\"$add\",\"$update\",\"$delete\",\"$search\",\"$export\",\"$import\"),";
				}else{
					$insert_values .= "(\"$permission_id\",\"$logged_id\",\"$add\",\"$update\",\"$delete\",\"$search\",\"$export\",\"$import\"),";
				}
			}
			if(isset($insert_values)){
				$insert_values = rtrim($insert_values,",");
				if(strtoupper($control_name) === "EMPLOYEES"){
					$insert_query  = "INSERT INTO cw_grants (`permission_id`, `prime_employees_id`, `grants_menu_id`, `grants_sub_menu_id`, `access_add`, `access_update`, `access_delete`, `access_search`, `access_export`, `access_import`) VALUES $insert_values";
					$this->db->query("$insert_query");
				}else{
					$insert_query  = "INSERT INTO cw_grants_customer (`permission_id`, `prime_customer_id`, `access_add`, `access_update`, `access_delete`, `access_search`, `access_export`, `access_import`) VALUES $insert_values";
					$this->db->query("$insert_query"); 
				}
			}
		}
	}
	/* USED IN BOTH EMPLOYEE AND CUSTOMER MODULE - END*/
	
	//get notification details about fileds
	
	/*public function get_notification(){
		$remainder_query    = $this->db->query("select * from cw_payroll_remainder where cw_payroll_remainder.trans_status =1 order by cw_payroll_remainder.prime_payroll_remainder_id asc");
		return $remainder_query->result();
	}*/
	
	//notification list and details MRJ --updates
	/*public function get_notification_count(){
		$remainder_query    = $this->db->query("select * from cw_payroll_remainder where cw_payroll_remainder.trans_status =1 order by cw_payroll_remainder.prime_payroll_remainder_id asc");
		$remainter_rslt = $remainder_query->result();
		$remainder_name = array();
		foreach($remainter_rslt as $remainder){
			$remainder_column  = $remainder->remainder_field;
			$days_before       = $remainder->number_of_days;
			$remainder_head    = $remainder->remainder_heading;
			$start_date        = date('m-d');
			$end_date          = date("m-d", strtotime("+$days_before day"));
			$employees_data_qry = 'select '.$remainder_column.',employee_code,emp_name from cw_employees where trans_status = 1 and role !=1 and DATE_FORMAT('.$remainder_column.', "%m-%d") BETWEEN "'.$start_date.'" and "'.$end_date.'"';
			$employees_data_info = $this->db->query("CALL sp_a_run ('SELECT','$employees_data_qry')");
			$employees_result    = $employees_data_info->result();
			$employees_data_info->next_result();
			$employees_count   = $employees_data_info->num_rows();
			$remainder_name[$remainder_column] = array('remainder_column' => $remainder_column,'days_before' => $days_before,'remainder_head' => $remainder_head,'remainder_count'=>$employees_count);
		}
		return $remainder_name;
	}*/
		
	//GETTING COMPANY INFORMATION
	public function get_company_info(){
		$company_info_query    = $this->db->query("select * from cw_company_information where cw_company_information.trans_status = 1");
		$company_info_rslt = $company_info_query->result();
		return $company_info_rslt;
	}
	
	public function productkey_save($product_info){
		if($product_info){
			return $this->db->insert('product_info', $product_info);
		}
	}
	
}
?>
