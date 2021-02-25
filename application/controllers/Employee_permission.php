<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Base_controller.php");
class Employee_permission  extends Base_controller{
	
	public function __construct(){
		parent::__construct('employee_permission');
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$this->collect_base_info();
	}
	
	// LOAD PAGE WITH TABLE DATA
	public function index(){
		$data['table_headers']= $this->xss_clean(get_permission_headers());
		$this->load->view("$this->control_name/manage",$data);
	}
	
	//LOAD MODEL PAGE VIEW WITH DATA
	public function view($form_view_id=-1){
		$role_info[""] = "---- Rights For ----";
		$role_data = $this->get_role();
		foreach($role_data as $row){
		     $role_info[$this->xss_clean($row['prime_user_role_id'])] = $this->xss_clean($row['role_name']);
		}
		$data['role_info'] = $role_info;
		$permissin_info = array();
		$permision_data = $this->get_permision_data();
		foreach($permision_data as $row){
		     $permissin_info[$row['role']] = $row['permission_id'];
		}
		$data['permissin_info'] = $permissin_info;
		
		/*============ BSK EMPLOYEE CUSTOME BLOCK ============*/		
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
		$data['role_id']     = $form_view_id;
		/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
		$this->load->view("$this->control_name/form",$data);
	}	
	public function get_role(){
		$this->db->from('user_role');
		$this->db->where('trans_status',1);
		$this->db->order_by('prime_user_role_id', 'asc');
		return $this->db->get()->result_array();
	}
	
	public function get_permision_data(){
		$this->db->from('employee_permission');
		$this->db->order_by('prime_employee_permission_id', 'asc');
		return $this->db->get()->result_array();
	}
	
	//LOAD PAGE TABLE VIEW WITH DATA BASED ON SEARCH FILTERS
	public function search(){
		$search       = $this->input->get('search');
		$limit        = $this->input->get('limit');
		$offset       = $this->input->get('offset');
		$sort         = $this->input->get('sort');
		$order        = $this->input->get('order');
		$fliter_label = $this->input->get('fliter_label');
		$fliter_type  = $this->input->get('fliter_type');
		$filter_cond  = $this->input->get('filter_cond');
		$fliter_val   = $this->input->get('fliter_val');
						
		if(!$sort){ $sort = $this->prime_table.".".$this->prime_id; }
		if(!$order){ $order = "asc";  }
		/* BSK CUSTOM BLOCK START */
		$common_search = "";
		if($search){
			$common_search = 'and cw_user_role.role_name like "'.$search.'%"';
		}
		$query = "select role_name,cw_employee_permission.role from cw_employee_permission inner join cw_user_role on cw_user_role.prime_user_role_id = cw_employee_permission.role";
		$query .= " where $this->prime_table.trans_status = 1 $common_search group by cw_employee_permission.role";
		$query .= " ORDER BY  cw_employee_permission.role ASC";
		$query .= " LIMIT  $offset,$limit";
		/* BSK CUSTOM BLOCK END */		
		//FETCH RECORDS DATA
		$search_data   = $this->db->query("CALL sp_a_run ('SELECT','$query')");
		$search_result = $search_data->result();
		$num_rows      = $search_data->num_rows();
		$search_data->next_result();
		$data_rows     = array();
		foreach ($search_result as $search){
			$data_rows[]=get_permission_dbdata_row($search,$this);
		}
		$data_rows=$this->xss_clean($data_rows);
		echo json_encode(array('total'=>$num_rows,'rows'=>$data_rows));
	}
	
	//SAVE MODEL DATA TO DATA BASE
	public function save(){		
		/*============ BSK GRANTS CUSTOME BLOCK ============*/		
		$access_data = $this->input->post('access') != NULL ? $this->input->post('access') : array();
		$grants_data = $this->input->post('grants') != NULL ? $this->input->post('grants') : array();
		$role = $this->input->post('role');		
		/*============ BSK GRANTS CUSTOME BLOCK ============*/	
		$update_for_all_employees = $this->input->post('update_for_all_employees');
		if(!$update_for_all_employees){
			$update_for_all_employees = 0;
		}
		if((int)$update_for_all_employees === 1){			
			//Update Grants for Other Roles except Candidate Role
			$query = 'SELECT GROUP_CONCAT(prime_employees_id) as ids from cw_employees where cw_employees.user_right = "'.$this->input->post('role').'"';
			$ids_info    = $this->db->query("CALL sp_a_run ('SELECT','$query')");
			$ids_result = $ids_info->result();
			$ids_info->next_result();
			$ids = explode(',',$ids_result[0]->ids);

			foreach ($ids as $logged_id) {
				$success = $this->db->delete('grants', array('prime_employees_id' => $logged_id));
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
						$insert_values .= "(\"$permission_id\",\"$logged_id\",\"$menu_id\",\"$sub_menu_id\",\"$add\",\"$update\",\"$delete\",\"$search\",\"$export\",\"$import\"),";
					}					
				}
			}
			if(isset($insert_values)){
				$insert_values = rtrim($insert_values,",");
				$insert_query  = "INSERT INTO cw_grants (`permission_id`, `prime_employees_id`, `grants_menu_id`, `grants_sub_menu_id`, `access_add`, `access_update`, `access_delete`, `access_search`, `access_export`, `access_import`) VALUES $insert_values";
				$this->db->query("$insert_query");	
			}			
		}
		$delete_success = $this->db->delete('employee_permission', array('role' => $this->input->post('role')));
		if($delete_success){
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
				$menu_data = $this->db->get()->row();
				$menu_id     = $menu_data->menu_id;
				$sub_menu_id = $menu_data->sub_menu_id;				
				//$this->db->insert('employee_permission', array('permission_id' => $permission_id, 'role' => $this->input->post('role'), 'grants_menu_id' => $menu_id, 'grants_sub_menu_id' => $sub_menu_id, 'access_add' => $add, 'access_update' => $update, 'access_delete' => $delete, 'access_search' => $search, 'access_export' => $export, 'access_import' => $import));
				$permission_values .= "(\"$permission_id\",\"$role\",\"$menu_id\",\"$sub_menu_id\",\"$add\",\"$update\",\"$delete\",\"$search\",\"$export\",\"$import\"),";
			}
			if(isset($permission_values)){
				$permission_values = rtrim($permission_values,",");
				$insert_query  = "INSERT INTO cw_employee_permission (`permission_id`, `role`, `grants_menu_id`, `grants_sub_menu_id`, `access_add`, `access_update`, `access_delete`, `access_search`, `access_export`, `access_import`) VALUES $permission_values";
				$this->db->query("$insert_query");		
			}
			echo json_encode(array('success' => TRUE, 'message' => "Successfully Updated"));
		}
	}
	//UPDATE STATUS TO DELETE IN MODULE PRIMARY TABLE
	public function delete(){
		$delete_ids = implode(",",$this->xss_clean($this->input->post('ids')));
		$created_on = date("Y-m-d h:i:s");
		$prime_upd_query    .= 'trans_deleted_by = "'. $this->logged_id .'",trans_deleted_date = "'.$created_on.'"';
		$prime_update_query  = 'UPDATE cw_employee_permission SET trans_status = 0,'. $prime_upd_query .' WHERE cw_employee_permission.role in ('. $delete_ids .')';
		if($this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')")){
			echo json_encode(array('success' => TRUE, 'message' => "Successfully Deleted"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Unable to delete"));
		}
	}
	
	public function get_permission_list(){
		$role = $this->input->post('role');
		/*============ BSK EMPLOYEE CUSTOME BLOCK ============*/
		$modules = array();
		foreach($this->Module->get_all_modules('EMPLOYEES') as $module){
			$module->module_id = $this->xss_clean($module->module_id);
			$module->grant     = $this->xss_clean($this->Module->has_grant('employee_permission',$module->module_id, $role));
			$module->access    = $this->xss_clean($this->Module->has_access('employee_permission',$module->module_id, $role));
			$modules[] = $module;
		}
		$menu_array         = array();
		$menu_data_array    = array();
		$submenu_data_array = array();
		foreach($modules as $module){
			$access_add    = $module->access[0]['access_add'];
			$access_update = $module->access[0]['access_update'];
			$access_delete = $module->access[0]['access_delete'];
			$access_search = $module->access[0]['access_search'];
			$access_export = $module->access[0]['access_export'];
			$access_import = $module->access[0]['access_import'];
			$grants_menu_id = $module->access[0]['grants_menu_id'];
			$grants_sub_menu_id = $module->access[0]['grants_sub_menu_id'];
			$check_box_input = form_checkbox("grants[]", $module->module_id, $module->grant, "class='module'");
			$menu_input = form_checkbox("menu_id", $module->menu_id, $grants_menu_id,"id='".str_replace(" ","_",strtolower($module->menu_name))."'", "class='menu_id'");
			$sub_menu_input = form_checkbox("sub_menu_id", $module->sub_menu_id, $grants_sub_menu_id,"id='".str_replace(" ","_",strtolower($module->sub_menu_name."_".$module->menu_id))."'", "class='sub_menu_id'");
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
								$sub_menu_input  <span style='color:#4dc147;Font-size:14px;'><b>$sub_menu_name</b></span> 
							</label>";
			if((int)$form_view->role === 1){
				$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
	
				$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
				$menu_data_array[$menu_name]        = $menu_data;
				$submenu_data_array[$sub_menu_name] = $sub_menu_data;
			}else{
				$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
				$admin_module = array("module_setting"=>true,"tester"=>true,"config"=>true);
				if(!$admin_module[$module->module_id]){
					$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
					$menu_data_array[$menu_name]        = $menu_data;
					$submenu_data_array[$sub_menu_name] = $sub_menu_data;
				}
			}
		}
		$li_line = "";
		foreach ($menu_array as $menu_name => $value) {
			$menu = $menu_data_array[$menu_name];
			$name = str_replace(" ","_",strtolower($menu_name));
			$sub_line = "";
			foreach ($value as $sub_menu_name => $data) {
				$sub_menu = $submenu_data_array[$sub_menu_name];
				$tr_line = "";
				foreach ($data as $key => $tr_value) {
					$grand_data  = $tr_value['grand_data'];
					$access_data = $tr_value['access_data'];
					$tr_line .=  "<li>
										$grand_data
										$access_data
									</li>";
				}	
				$tr_line = "<ul id='ul_$sub_menu_name' style='display:none;'>$tr_line</ul>";
				$sub_line .= "<li>	
								$sub_menu
								$tr_line
							</li>";
			}	
	
			$sub_line = "<ul id='ul_$name' style='display:none;'>$sub_line</ul>";
			$li_line .= "<li>	
							$menu
							$sub_line
						</li>";
		}
		echo $li_line;
	}
}
?>