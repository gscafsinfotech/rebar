	<?php
/**********************************************************
	   Filename: Module Setting
	Description: Module Setting for creating new Dynamic module and adding main menu.
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
class Module_setting  extends Secure_Controller{
 
	public function __construct(){
		parent::__construct('module_setting');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$data['table_headers']=$this->xss_clean(get_form_setting_headers());
		$this->load->view('module_setting/manage',$data);
	}
	
	/* ==============================================================*/
	/* =================== MENU OPEARTION - START ===================*/
	/* ==============================================================*/
	//MENU VIEW OPEARTION
	public function menu_view($prime_module_id =-1){
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$menu_for[""] = "---- Menu For ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$menu_for[$role_id] = $category_name;
		}
		$data['menu_for']  = $menu_for;
		
		$menu_list_rslt = $this->view_menu_list();
		$menu_list_rslt = json_decode($menu_list_rslt);
		$data['menu_list'] =  $menu_list_rslt->menu_list;
		
		$cw_main_menu_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_main_menu` where trans_status = 1')");
		$cw_main_menu_result = $cw_main_menu_info->result();
		$cw_main_menu_info->next_result();
		$menu_array = array('1','2');
		$main_menu[""] = "---- Main Menu ----";
		foreach($cw_main_menu_result as $menu){
			$prime_menu_id   = $menu->prime_menu_id;
			$menu_name = $menu->menu_name;
			if(!in_array($prime_menu_id, $menu_array)){
				$main_menu[$prime_menu_id] = $menu_name;
			}
		}
		$data['main_menu']  = $main_menu;
		
		$menu_list_rslt = $this->view_sub_menu_list();
		$menu_list_rslt = json_decode($menu_list_rslt);
		$data['sub_menu_list'] = $menu_list_rslt->sub_menu_list;
		
		$menu_sort_order_rslt = $this->menu_sort_order();
		$menu_sort_order_rslt = json_decode($menu_sort_order_rslt);
		$data['menu_sort_order_list'] = $menu_sort_order_rslt;
		$this->load->view("module_setting/menu_view",$data);
	}
	
	//SAVE MENU VIEW OPEARTION
	public function save_menu(){
		$prime_menu_id = $this->input->post('prime_menu_id');
		$menu_name     = $this->input->post('menu_name');
		$menu_for      = implode(",",$this->input->post('menu_for[]'));
		$menu_for      = ltrim($menu_for,',');
		$logged_id        = $this->session->userdata('logged_id');
		$date          = date("Y-m-d h:i:s");
		
		$is_exist_qry  = 'SELECT * FROM `cw_main_menu` where menu_name = "'.$menu_name.'"';
		$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
		$exist_count   = $is_exist_data->num_rows();
		$is_exist_data->next_result();
		$sts = false;
		$msg = "";
		if((int)$exist_count === 0){
			$count_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT count(*) as rslt_count FROM `cw_main_menu` where trans_status = 1')");
			$count_result = $count_data->result();
			$count_data->next_result();
			$menu_sort = (int)$count_result[0]->rslt_count + 1;
			if((int)$prime_menu_id === 0){
				$save_qry  = 'INSERT INTO cw_main_menu (menu_name, menu_for, menu_sort, trans_created_by, trans_created_date) VALUES ("'.$menu_name.'","'.$menu_for.'","'.$menu_sort.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu added successfully";
			}else{
				$save_qry  = 'UPDATE  cw_main_menu SET menu_name = "'.$menu_name.'",menu_for = "'.$menu_for.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_menu_id = "'.$prime_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu updated successfully";
			}
		}else{
			if((int)$prime_menu_id !== 0){
				$save_qry  = 'UPDATE  cw_main_menu SET menu_name = "'.$menu_name.'",menu_for = "'.$menu_for.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_menu_id = "'.$prime_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu updated successfully";
			}else{
				$sts = false;
				$msg = "Menu already exist";
			}
		}
		
		$menu_list_rslt = $this->view_menu_list();
		$menu_list_rslt = json_decode($menu_list_rslt);
		$menu_list      = $menu_list_rslt->menu_list;
		echo json_encode(array('success' => $sts, 'msg' => $msg, 'menu_list' => $menu_list));
	}
	
	//MENU VIEW_MENU_LIST
	public function view_menu_list(){
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_main_menu` where trans_status = 1 order by abs(menu_sort) asc')");
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		$li_list = "";
		$count = 0;
		foreach($menu_result as $menu){
			$count++;
			$prime_menu_id = $menu->prime_menu_id;
			$menu_name     = $menu->menu_name;
			$li_id = "li_".$prime_menu_id;
			$a_id  = "a_".$prime_menu_id."_$count";
			$li_list .= "<li class='ui-state-default' id='$li_id'>
					<table style='width:100%;'>
						<tr>
							<td style='font-weight:bold'>
								<label>$menu_name</label><br/>						
							</td>
							<td style='text-align:right;'>
								<a id='$a_id' class='prime_color' onclick=get_view_menu_list('$prime_menu_id','$a_id');><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i></a>
							</td>
						</tr>
					</table>
				</li>";
		}
		$menu_list = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p><ul id='view_sortable' class='sortable'>$li_list</ul>";
		return json_encode(array('menu_list' => $menu_list));
	}
	
	// MENU SORT
	public function update_menu_sortorder(){
		$view_idsInOrder = $this->input->post('view_idsInOrder');
		$logged_id          = $this->session->userdata('logged_id');
		$sort_order = 0;
		foreach($view_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_menu_id = $order[1];
				$upd_qry  = 'UPDATE  cw_main_menu SET menu_sort = "'.$sort_order.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_menu_id = "'.$prime_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	
	//MENU EDIT GET INFORMATION
	public function get_view_menu_list(){
		$prime_menu_id = $this->input->post('prime_menu_id');
		$menu_qry  = 'SELECT * FROM `cw_main_menu` where prime_menu_id = "'.$prime_menu_id.'"';
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','$menu_qry')");
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		echo json_encode(array('success' => TRUE, 'menu_result' => $menu_result[0]));
	}
	
	// SUB MENU SAVE
	public function save_sub_menu(){
		$prime_sub_menu_id = $this->input->post('prime_sub_menu_id');
		$main_menu         = $this->input->post('main_menu');
		$sub_menu_name     = $this->input->post('sub_menu_name');
		$logged_id         = $this->session->userdata('logged_id');
		$date              = date("Y-m-d h:i:s");
		
		$is_exist_qry  = 'SELECT * FROM `cw_sub_menu` where map_main_menu = "'.$main_menu.'" and sub_menu_name = "'.$sub_menu_name.'"';
		$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
		$exist_count   = $is_exist_data->num_rows();
		$is_exist_data->next_result();
		$sts = false;
		$msg = "";
		if((int)$exist_count === 0){
			$count_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT count(*) as rslt_count FROM `cw_sub_menu` where trans_status = 1')");
			$count_result = $count_data->result();
			$count_data->next_result();
			$menu_sort = (int)$count_result[0]->rslt_count + 1;
			if((int)$prime_sub_menu_id === 0){
				$save_qry  = 'INSERT INTO cw_sub_menu (map_main_menu, sub_menu_name, sub_menu_sort, trans_created_by, trans_created_date) VALUES ("'.$main_menu.'","'.$sub_menu_name.'","'.$menu_sort.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu added successfully";
			}else{
				$save_qry  = 'UPDATE  cw_sub_menu SET map_main_menu = "'.$main_menu.'",sub_menu_name = "'.$sub_menu_name.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_sub_menu_id = "'.$prime_sub_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu updated successfully";
			}
		}else{
			if((int)$prime_sub_menu_id !== 0){
				$save_qry  = 'UPDATE  cw_sub_menu SET map_main_menu = "'.$main_menu.'",sub_menu_name = "'.$sub_menu_name.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_sub_menu_id = "'.$prime_sub_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$sts = true;
				$msg = "Menu updated successfully";
			}else{
				$sts = false;
				$msg = "Menu already exist";
			}
		}
		$menu_list_rslt = $this->view_sub_menu_list();
		$menu_list_rslt = json_decode($menu_list_rslt);
		$sub_menu_list      = $menu_list_rslt->sub_menu_list;
		echo json_encode(array('success' => $sts, 'msg' => $msg, 'sub_menu_list' => $sub_menu_list));
	}
	// SUB MENU LIST
	public function view_sub_menu_list(){
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT cw_sub_menu.prime_sub_menu_id,menu_name,sub_menu_name FROM cw_sub_menu left join cw_main_menu on cw_main_menu.prime_menu_id = cw_sub_menu.map_main_menu where cw_sub_menu.trans_status = 1 order by abs(cw_sub_menu.sub_menu_sort) asc')");
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		
		$li_list = "";
		$count = 0;
		foreach($menu_result as $menu){
			$count++;
			$prime_menu_id = $menu->prime_sub_menu_id;
			$menu_name     = $menu->menu_name;
			$sub_menu_name = $menu->sub_menu_name;
			$li_id = "li_".$prime_menu_id;
			$a_id  = "a_".$prime_menu_id."_$count";
			$li_list .= "<li class='ui-state-default' id='$li_id'>
					<table style='width:100%;'>
						<tr>
							<td style='font-weight:bold'>
								<label>$sub_menu_name</label><br/>
								<span style='font-size:13px;font-weight:normal;color:#999999;'> $menu_name </span>
							</td>
							<td style='text-align:right;'>
								<a id='$a_id' class='prime_color' onclick=get_view_sub_menu_list('$prime_menu_id','$a_id');><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i></a>
							</td>
						</tr>
					</table>
				</li>";
		}
		$sub_menu_list = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p><ul id='sub_menu_sortable' class='sortable'>$li_list</ul>";
		return json_encode(array('sub_menu_list' => $sub_menu_list));
	}
	// SUB MENU SORT
	public function update_sub_menu_sortorder(){
		$view_idsInOrder = $this->input->post('view_idsInOrder');
		$logged_id          = $this->session->userdata('logged_id');
		$sort_order = 0;
		foreach($view_idsInOrder as $order){
			if($order){
				$sort_order++;
				$order = explode("_",$order);
				$prime_menu_id = $order[1];
				$upd_qry  = 'UPDATE  cw_sub_menu SET sub_menu_sort = "'.$sort_order.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_sub_menu_id = "'.$prime_menu_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Sort position updated to database"));
	}
	
	//SBU MENU EDIT GET INFORMATION
	public function get_view_sub_menu_list(){
		$prime_menu_id = $this->input->post('prime_menu_id');
		$menu_qry  = 'SELECT * FROM `cw_sub_menu` where prime_sub_menu_id = "'.$prime_menu_id.'"';
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','$menu_qry')");
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		echo json_encode(array('success' => TRUE, 'sub_menu_result' => $menu_result[0]));
	}
	
	//MENU LIST ORDER VIEW 08MARCH2019
	public function menu_sort_order(){
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT * from cw_main_menu left join cw_sub_menu on cw_sub_menu.map_main_menu = cw_main_menu.prime_menu_id where cw_main_menu.trans_status = 1 and prime_menu_id not in (1,2) group by prime_menu_id ORDER BY menu_sort asc')");//no admin and super admin
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		$menu_sort_order = "<p class='inline_topic'><i class='fa fa-hand-rock-o fa-2x' aria-hidden='true'></i> Drag and drop for align field postion</p>";
		$id_array = array();
		foreach($menu_result as $menu){
			$prime_menu_id = $menu->prime_menu_id;
			$menu_name     = $menu->menu_name;
			$sub_menu_qry  = 'select * from `cw_modules` left join cw_sub_menu on cw_sub_menu.prime_sub_menu_id =  cw_modules.sub_menu_id  where cw_modules.trans_status = 1 and cw_modules.show_module=1 and menu_id = "'.$prime_menu_id.'" order by abs(cw_modules.sort)';
			$module_name_data   = $this->db->query("CALL sp_a_run ('SELECT','$sub_menu_qry')");
			$module_name_result = $module_name_data->result();
			$module_name_data->next_result();
			$ul_li    = "";
			$input_li = "";
			foreach($module_name_result as $module){
				$module_id     = $module->module_id;
				$module_name   = $module->module_name;
				$sub_menu_name = $module->sub_menu_name;
				if($sub_menu_name){
					$sub_menu_name = "(".$sub_menu_name.")";
				}
				$li_id     = "li_".$module_id;
				$input_li .=  "<li class='ui-state-default' id='$li_id'>
								<table style='width:100%;'>
									<tr>
										<td style='font-weight:bold'>
											<span style='font-size:13px;font-weight:bold;'>
												$module_name 
											</span>
											<span style='font-size:13px;font-weight:normal;color:#999999;'>
												$sub_menu_name
											</span>
										</td>
									</tr>
								</table>
							</li>";
			}
			$ul_id      = "menu_sort_order_".$prime_menu_id;
			$id_array[] = $ul_id;
			$ul_li      = "<ul class='sortable' id=$ul_id>$input_li</ul>";
			$menu_sort_order .= "<div style='font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px;padding:8px;margin-bottom:10px;'>
									<h4 class='prime_color'>$menu_name</h4>
									$ul_li
								</div>";
		}
		return json_encode(array('menu_sort_order' => $menu_sort_order,'id_array'=>$id_array));
	}
	
	//MENU LIST ORDER SORT UPDATE 08MARCH2019
	public function update_menu_order(){
		$idsInOrder = $this->input->post('idsInOrder');
		$logged_id  = $this->session->userdata('logged_id');
		$date       = date("Y-m-d H:i:s");
		
		$sort_order = 0;
		foreach($idsInOrder as $order){
			if($order){
				$sort_order++;
				//$module_id = str_replace("li_","",$order); //replace
				$prefix = 'li_';
				if(substr($order, 0, strlen($prefix)) == $prefix){
					$module_id = substr($order, strlen($prefix));
				}
				$upd_qry  = 'UPDATE cw_modules SET sort = "'.$sort_order.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where module_id = "'.$module_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			}
		}
		echo json_encode(array('success' => TRUE, 'message' => "Menu name is successfully sorted."));
	}
	
	/* ==============================================================*/
	/* ==================== MENU OPEARTION - END ====================*/
	/* ==============================================================*/
	
	/* ==============================================================*/
	/* ================== MODULE OPEARTION - START ==================*/
	/* ==============================================================*/
	
	//MODULE SEARCH OPEARTION
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
		$info     = $this->db->query("CALL sp_form_setting_search ('SEARCH','$search','$offset','$limit','$sort','$order')");
		$result   = $info->result();
		$info->next_result();
		$data_rows     = array();
		foreach ($result as $form_setting){
			$data_rows[]=get_form_setting_datarows($form_setting,$this);
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
	public function module_view($prime_module_id){
		if($prime_module_id === '-1'){
			$prime_module_id = 0;
		}
		$data['prime_module_id'] = $prime_module_id;
		$module_qry  = 'SELECT * FROM `cw_modules` where module_id = "'.$prime_module_id.'"';
		$module_data = $this->db->query("CALL sp_a_run ('SELECT','$module_qry')");
		$module_rslt = $module_data->result();
		$module_data->next_result();
		// print_r($module_rslt);die;
		$data['module_info'] = $module_rslt[0];
		
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where  trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$module_for[""] = "---- Module For ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$module_for[$role_id] = $category_name;
		}
		$data['module_for']  = $module_for;
		
		$menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_main_menu` where trans_status = 1 order by abs(menu_sort) asc')");
		$menu_result = $menu_data->result();
		$menu_data->next_result();
		$menu_array = array('1','2');
		$map_menu_to[""] = "---- Map Menu to ----";
		foreach($menu_result as $menu){
			$prime_menu_id   = $menu->prime_menu_id;
			$menu_name       = ucwords($menu->menu_name);
			if(!in_array($prime_menu_id, $menu_array)){
				$map_menu_to[$prime_menu_id] = $menu_name;
			}
		}
		$data['map_menu_to']  = $map_menu_to;
		
		$sub_menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT cw_sub_menu.prime_sub_menu_id,menu_name,sub_menu_name FROM cw_sub_menu left join cw_main_menu on cw_main_menu.prime_menu_id = cw_sub_menu.map_main_menu where cw_sub_menu.trans_status = 1 order by abs(cw_sub_menu.sub_menu_sort) asc')");
		$sub_menu_result = $sub_menu_data->result();
		$sub_menu_data->next_result();
		$sub_map_menu_to[""] = "---- Map Sub Menu to ----";
		foreach($sub_menu_result as $sub_menu){
			$prime_sub_menu_id = $sub_menu->prime_sub_menu_id;
			$menu_name         = $sub_menu->menu_name;
			$sub_menu_name     = ucwords($sub_menu->sub_menu_name);
			$sub_name = $menu_name ." - ".  $sub_menu_name;
			$sub_map_menu_to[$prime_sub_menu_id] = $sub_name;
		}
		$data['sub_map_menu_to']  = $sub_map_menu_to;
		
		$quick_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_modules` where menu_id not in (1,2) order by abs(sort) asc')");
		$quick_result = $quick_data->result();
		$quick_data->next_result();
		$quicklink_list[""] = "---- Quick link  ----";
		foreach($quick_result as $quick){
			$module_id   = $quick->module_id;
			$module_name = ucwords(str_replace("_"," ",$quick->module_id));
			$quicklink_list[$module_id] = $module_name;
		}
		$data['quicklink_list']  = $quicklink_list;
		
		$role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_category` where prime_category_id != 1 and trans_status = 1')");
		$role_result = $role_info->result();
		$role_info->next_result();
		$field_for[""] = "---- Field For ----";
		foreach($role_result as $for){
			$role_id   = $for->prime_category_id;
			$category_name = $for->category_name;
			$field_for[$role_id] = $category_name;
		}
		$data['field_for'] = $field_for;
		
		$user_role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_user_role` where prime_user_role_id != 1 and trans_status = 1')");
		$user_role_result = $user_role_info->result();
		$user_role_info->next_result();
		$user_field_for[""] = "---- Field For ----";
		foreach($user_role_result as $for){
			$user_role_id   = $for->prime_user_role_id;
			$user_role_name = $for->role_name;
			$user_field_for[$user_role_id] = $user_role_name;
		}
		$data['user_field_for'] = $user_field_for;
		
		
		$table_prime    = "cw_".$prime_module_id;
		$table_prime_id = "prime_".$prime_module_id."_id";
		$table_cf       = "cw_".$prime_module_id."_cf";
		$table_cf_id    = "prime_".$prime_module_id."_cf_id";
		$table_names    = '"'.$table_prime.'","'.$table_cf.'"';
		$prime_ids      = '"'.$table_prime_id.'","'.$table_cf_id.'"';
		if($prime_module_id === "custom_approval"){
			$get_colums = 'SELECT TABLE_NAME as table_info,COLUMN_NAME as column_info  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` = "cw_custom_employees" AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME != "prime_custom_employees_id"';
		}else{
			$get_colums = 'SELECT TABLE_NAME as table_info,COLUMN_NAME as column_info  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_names.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ('.$prime_ids.')';
			// echo $get_colums;die;
		}
		//$get_colums = 'SELECT TABLE_NAME as table_info,COLUMN_NAME as column_info  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_names.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ('.$prime_ids.')';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums')");
		$column_result = $column_info->result();
		$column_info->next_result();
		$column_list[""] = "---- Select Column ----";
		foreach($column_result as $column){
			$table_info   = $column->table_info;
			$column_info  = $column->column_info;
			$column_value = $table_info.".".$column_info;
			$column_name  = ucwords(str_replace("_"," ",$column_info));
			$column_list[$column_value] = $column_name;
		}
		$data['column_list']      = $column_list;
		$data['table_query_list'] = $this->get_saved_query_list($prime_module_id);

		//PAYROLL INFO DETAILS
		//EARN COLUMN LIST
		$earn_get_colums = 'SELECT label_name,view_name FROM `cw_form_setting` WHERE trans_status=1 and prime_module_id = "employees" and (transaction_type = "1" or transaction_type = "2")';		
		$earn_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$earn_get_colums')");
		$earn_column_result = $earn_column_info->result();
		$earn_column_info->next_result();
		$earn_column_list[""] = "---- Select Earn Column ----";
		foreach($earn_column_result as $earn_column){
			$earn_column_label  = $earn_column->label_name;
			$earn_column_name   = $earn_column->view_name;
			$earn_column_list[$earn_column_label] = $earn_column_name;
		}
		$data['earn_column_list']      = $earn_column_list;
		
		//DEDUCTION COLUMN LIST	
		$ded_get_colums = 'SELECT label_name,view_name FROM `cw_form_setting` WHERE trans_status= 1 and prime_module_id = "employees" and transaction_type = "3"';
		$ded_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$ded_get_colums')");
		$ded_column_result = $ded_column_info->result();
		$ded_column_info->next_result();
		$ded_column_list[""] = "---- Select Deduction Column ----";
		foreach($ded_column_result as $ded_column){
			$ded_column_label  = $ded_column->label_name;
			$ded_column_name   = $ded_column->view_name;
			
			$ded_column_list[$ded_column_label] = $ded_column_name;
		}
		$data['ded_column_list']      = $ded_column_list;
		
		//TOTAL COLUMN LIST
		$out_get_colums_qry = 'SELECT label_name,view_name FROM `cw_form_setting` WHERE trans_status=1 and prime_module_id = "employees" and (transaction_type = "1" or transaction_type = "2" or transaction_type = "3")';		
		$out_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$out_get_colums_qry')");
		$out_column_result = $out_column_info->result();
		$out_column_info->next_result();
		$out_column_list[""] = "---- Select Column ----";
		foreach($out_column_result as $out_column){
			$out_column_label  = $out_column->label_name;
			$out_column_name   = $out_column->view_name;
			$out_column_list[$out_column_label] = $out_column_name;
		}
		$data['out_column_list']      = $out_column_list;
		
		/*$out_get_colums = 'SELECT COLUMN_NAME as column_info  FROM `INFORMATION_SCHEMA`.`COLUMNS`  WHERE `TABLE_SCHEMA`="'.$this->config->item("db_name").'" AND `TABLE_NAME` IN ('.$table_names.') AND COLUMN_NAME NOT LIKE "%trans%" AND COLUMN_NAME NOT IN ('.$prime_ids.')';
		$out_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$out_get_colums')");
		$out_column_result = $out_column_info->result();
		$out_column_info->next_result();
		$out_column_list[""] = "---- Select Column ----";
		foreach($out_column_result as $out_column){
			$out_column_info  = $out_column->column_info;
			$out_column_name  = ucwords(str_replace("_"," ",$out_column_info));
			$out_column_list[$out_column_info] = $out_column_name;
		}
		$data['out_column_list']      = $out_column_list;*/
		// $formula_content    = $this->get_saved_payroll_formula();	
		// $data['formula_content'] = $formula_content;
		
		$print_qry  = 'SELECT * FROM cw_print_info where trans_status = 1 and print_info_module_id ="'.$prime_module_id.'"';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();
		$print_data->next_result();
		$print_map_list[''] = "--- Select Print Map---";
		foreach($print_rslt as $print){
			$prime_print_info_id = $print->prime_print_info_id;
			$print_info_name     = ucwords($print->print_info_name);
			$print_map_list[$prime_print_info_id] = $print_info_name;
		}
		$data['print_map_list'] = $print_map_list;
		$data['print_info_list'] = $this->get_print_map_list($prime_module_id);

		// -- 01March2019 -- start
		
		//get statutory value name list
		// $statutory_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_statutory_field` where trans_status = 1')");
		// $statutory_result = $statutory_info->result();
		// $statutory_info->next_result();
		// $statutory_name_list[""] = "---- Statutory Name ----";
		// foreach($statutory_result as $statutory){
		// 	$stautory_id   = $statutory->prime_statutory_field_id;
		// 	$stautory_name = $statutory->statutory_field_name;
		// 	$statutory_name_list[$stautory_id] = $stautory_name;
		// }
		// $data['statutory_name_list']  = $statutory_name_list;
		//get statutory function name list
		// $statutory_function_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT * FROM `cw_statutory_function` where trans_status = 1')");
		// $statutory_function_result = $statutory_function_info->result();
		// $statutory_function_info->next_result();
		// $statutory_function_list[""] = "---- Statutory Function ----";
		// foreach($statutory_function_result as $stat_function){
		// 	$statutory_function_id   = $stat_function->prime_statutory_function_id;
		// 	$statutory_function_name = $stat_function->statutory_function_name;
		// 	$statutory_function_list[$statutory_function_id] = $statutory_function_name;
		// }
		// $data['statutory_function_list']  = $statutory_function_list;
		
		//get total transaction table column
		$get_trans_colums = 'SELECT TABLE_NAME as table_info,COLUMN_NAME as column_info  FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`= "'.$this->config->item("db_name").'" AND `TABLE_NAME` = "cw_transactions"
		 AND (COLUMN_NAME NOT LIKE "%trans%" OR COLUMN_NAME = "transactions_month") and COLUMN_NAME NOT IN ('.$prime_ids.')';
		$trans_column_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_trans_colums')");
		$trans_column_result = $trans_column_info->result();
		$trans_column_info->next_result();
		$trans_column_list[""] = "---- Select Column ----";
		foreach($trans_column_result as $column){
			$column_info  = $column->column_info;
			$column_name  = ucwords(str_replace("_"," ",$column_info));
			$trans_column_list[$column_info] = $column_name;
		}
		$data['trans_column_list']      = $trans_column_list;
		//Formula category is displayed
		// $formula_role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT prime_category_id,category_name FROM cw_category inner join cw_payroll_formula on cw_payroll_formula.formula_for=cw_category.prime_category_id WHERE cw_payroll_formula.trans_status = 1 GROUP by prime_category_id')");
		// $formula_role_result = $formula_role_info->result();
		// $formula_role_info->next_result();
		// $formula_role_for[""] = "---- Select Category ----";
		// foreach($formula_role_result as $formula_for){
		// 	$role_id           = $formula_for->prime_category_id;
		// 	$category_name     = $formula_for->category_name;
		// 	$formula_role_for[$role_id] = $category_name;
		// }
		// $data['formula_role_for']  = $formula_role_for;
		//No Formula category is displayed
		// $noformula_role_info   = $this->db->query("CALL sp_a_run ('SELECT','SELECT prime_category_id, category_name FROM cw_category WHERE prime_category_id Not in (SELECT formula_for FROM cw_payroll_formula WHERE trans_status = 1 GROUP by formula_for) and prime_category_id != 1')");
		// $noformula_role_result = $noformula_role_info->result();
		// $noformula_role_info->next_result();
		// $noformula_role_for[""] = "---- Select Category ----";
		// foreach($noformula_role_result as $noformula_for){
		// 	$role_id            = $noformula_for->prime_category_id;
		// 	$category_name      = $noformula_for->category_name;
		// 	$noformula_role_for[$role_id] = $category_name;
		// }
		// $data['noformula_role_for']  = $noformula_role_for;
		
		// $function_list         = $this->payroll_function_list();
		// $data['function_list'] = $function_list;
		
		// $mapping_list          = $this->column_mapping_list();
		// $data['mapping_list']  = $mapping_list;
		$this->load->view("module_setting/module_view",$data);
	}
	
	//SAVE MODULE VIEW OPEARTION
	public function save_module(){
		$prime_module_id  = $this->input->post('prime_module_id');
		$module_id        = $this->input->post('module_id');
		$module_name      = $this->input->post('module_name');
		$rights_to        = implode(",",$this->input->post('rights_to[]'));
		$rights_to        = ltrim($rights_to,',');		
		$module_for       = implode(",",$this->input->post('module_for[]'));
		$module_for       = ltrim($module_for,',');		
		$map_menu_to      = $this->input->post('map_menu_to');
		$sub_map_menu_to  = $this->input->post('sub_map_menu_to');
		$quicklink        = implode(",",$this->input->post('quicklink[]'));
		$quicklink        = ltrim($quicklink,',');		
		$show_module      = $this->input->post('show_module');
		$import_module    = $this->input->post('import_module');
		$pdf_module       = $this->input->post('pdf_module');
		$module_type      = $this->input->post('module_type');
		$custom_module    = $this->input->post('custom_module');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		
		$module_id     = strtolower(str_replace(" ","_",$module_id));
		$module_name   = ucwords($module_name);
		if($prime_module_id === "0"){			
			$is_exist_qry  = 'SELECT count(*) as rslt_count FROM `cw_modules` where module_id = "'.$module_id.'"';
			$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
			$exist_rslt    = $is_exist_data->result();
			$is_exist_data->next_result();			
			if((int)$exist_rslt[0]->rslt_count === 0){
				$sort_qry   = 'SELECT count(*) as rslt_count FROM `cw_modules` where trans_status = 1  and menu_id not in (1,2)';
				$sort_data  = $this->db->query("CALL sp_a_run ('SELECT','$sort_qry')");
				$sort_rslt = $sort_data->result();
				$sort_data->next_result();
				$sort_order    = (int)$sort_rslt[0]->rslt_count + 1;
				
				$module_qry  = 'INSERT INTO cw_modules (sort, module_id,module_name,module_type,menu_id,sub_menu_id,module_for,rights_to,show_module,quicklink,import_module,pdf_module,custom_module,trans_created_by, trans_created_date) VALUES ("'.$sort_order.'","'.$module_id.'","'.$module_name.'","'.$module_type.'","'.$map_menu_to.'","'.$sub_map_menu_to.'","'.$module_for.'","'.$rights_to.'","'.$show_module.'","'.$quicklink.'","'.$import_module.'","'.$pdf_module.'","'.$custom_module.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$module_qry')");
				
				$permissions_qry  = 'INSERT INTO cw_permissions (permission_id, module_id) VALUES ("'.$module_id.'","'.$module_id.'")';
				$this->db->query("CALL sp_a_run ('RUN','$permissions_qry')");
				
				$grants_qry  = 'INSERT INTO cw_grants (permission_id, prime_employees_id, access_add, access_update,access_delete,access_search,access_export,access_import) VALUES ("'.$module_id.'","1","1","1","1","1","1","1")';
				$this->db->query("CALL sp_a_run ('RUN','$grants_qry')");
				
				$this->creat_file_structure($module_id);
				echo json_encode(array('success' => true, 'message' => "New Module successfully added"));				
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Module already exist"));
			}			
		}else{
			$upd_qry  = 'UPDATE  cw_modules SET module_name = "'.$module_name.'",module_type = "'.$module_type.'",menu_id = "'.$map_menu_to.'",sub_menu_id = "'.$sub_map_menu_to.'",module_for = "'.$module_for.'",show_module = "'.$show_module.'",quicklink = "'.$quicklink.'",import_module = "'.$import_module.'",pdf_module = "'.$pdf_module.'",custom_module = "'.$custom_module.'",rights_to = "'.$rights_to.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where module_id = "'.$prime_module_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			echo json_encode(array('success' => true, 'message' => "Module successfully updated"));
		}
	}
	function creat_file_structure($module_id){
		$ucfirst    = ucfirst($module_id);
		$strtolower = strtolower($module_id);
		$controller_file_name = $ucfirst.".php";
		$controller_file_name = $ucfirst.".php";
		
		$controller_file = file_get_contents('module_creation/controllers.php', true);
		$controller_file = str_replace("@MODULE_NAME@",$ucfirst, $controller_file);
		$controller_file = str_replace("@MODULE_NAME_CONSTRUCT@",$strtolower, $controller_file);
		
		fopen("./application/controllers/$controller_file_name", "w");
		file_put_contents("./application/controllers/$controller_file_name",$controller_file);
		
		
		if(!file_exists("./application/views/$strtolower")) {
			mkdir("./application/views/$strtolower", 0777, true);
		}
		$form_file   = file_get_contents('module_creation/form.php', true);
		fopen("./application/views/$strtolower/form.php", "w");
		file_put_contents("./application/views/$strtolower/form.php",$form_file);
		
		$import_file = file_get_contents('module_creation/import.php', true);
		fopen("./application/views/$strtolower/import.php", "w");
		file_put_contents("./application/views/$strtolower/import.php",$import_file);
		
		$manage_file = file_get_contents('module_creation/manage.php', true);
		fopen("./application/views/$strtolower/manage.php", "w");
		file_put_contents("./application/views/$strtolower/manage.php",$manage_file);
		
		$print_file = file_get_contents('module_creation/print.php', true);
		fopen("./application/views/$strtolower/print.php", "w");
		file_put_contents("./application/views/$strtolower/print.php",$print_file);
		return true;
	}
	
	// PROVIDE PICKLIST AND SESSION VALUES
	function get_column_info(){
		$query_module_id  = $this->input->post('query_module_id');
		if($query_module_id === "custom_approval"){
			$query_module_id = "employees";
		}
		$query_column     = $this->input->post('query_column');
		$label_name       = explode(".",$query_column);
		$get_colums_info = 'SELECT * FROM cw_form_setting WHERE  prime_module_id = "'.$query_module_id.'" and label_name = "'.$label_name[1].'"';
		$colums_info   = $this->db->query("CALL sp_a_run ('SELECT','$get_colums_info')");
		$colums_result = $colums_info->result();
		$colums_info->next_result();
		if($colums_result){
			$field_type     = (int)$colums_result[0]->field_type;
			$pick_list_type = (int)$colums_result[0]->pick_list_type;
			$pick_list 	    = $colums_result[0]->pick_list;
			$pick_table 	= $colums_result[0]->pick_table;
			
			$session_val_qry    = 'SELECT * FROM cw_session_value WHERE  trans_status = 1 order by abs(session_for)';
			$get_session_val    = $this->db->query("CALL sp_a_run ('SELECT','$session_val_qry')");
			$session_val_result = $get_session_val->result();
			$get_session_val->next_result();
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
			echo json_encode(array('success' => false,'msg'=>"Invalid column"));
		}
	}
	
	//SAVE BASIC QUERY
	function save_query_info(){
		$prime_table_id   = (int)$this->input->post('prime_table_id');
		$query_module_id  = $this->input->post('query_module_id');
		$query_for        = $this->input->post('query_for');
		$where_condition  = $this->input->post('where_condition');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$exist_query  = 'SELECT * FROM cw_form_table_search WHERE  query_module_id = "'.$query_module_id.'" and query_for = "'.$query_for.'" and trans_status = 1';
		$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_query')");
		$exist_count  = (int)$exist_info->num_rows();
		$exist_result = $exist_info->result();
		$exist_info->next_result();
		if($exist_count === 0){			
			$search_qry  = 'INSERT INTO cw_form_table_search (query_module_id, query_for,where_condition,trans_created_by, trans_created_date) VALUES ("'.$query_module_id.'","'.$query_for.'","'.$where_condition.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$search_qry')");
			$table_query_list = $this->get_saved_query_list($query_module_id);
			echo json_encode(array('success' => true,'message'=>"Basic Query added successfully !!!",'table_query_list'=>$table_query_list));
		}else{
			$db_prime_table_id = (int)$exist_result[0]->prime_table_id;
			if($db_prime_table_id === $prime_table_id){
				$upd_qry  = 'UPDATE  cw_form_table_search SET query_module_id = "'.$query_module_id.'",query_for = "'.$query_for.'",where_condition = "'.$where_condition.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_table_id = "'.$prime_table_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$table_query_list = $this->get_saved_query_list($query_module_id);
				echo json_encode(array('success' => true,'message'=>"Basic Query updated successfully !!!",'table_query_list'=>$table_query_list));
			}else{
				echo json_encode(array('success' => false,'message'=>"Basic Query already exist"));
			}
		}
	}
	function get_saved_query_list($query_module_id){
		$query_list  = 'select prime_table_id,query_module_id,role_name,where_condition from cw_form_table_search inner join cw_user_role on cw_user_role.prime_user_role_id = cw_form_table_search.query_for where cw_form_table_search.query_module_id = "'.$query_module_id.'" and cw_form_table_search.trans_status = 1';
		$query_list_info   = $this->db->query("CALL sp_a_run ('SELECT','$query_list')");
		$query_list_result = $query_list_info->result();
		$query_list_info->next_result();
		foreach($query_list_result as $rslt){
			$prime_table_id  = $rslt->prime_table_id;
			$category_name   = $rslt->role_name;
			$where_condition = $rslt->where_condition;
			$query_tr_line .= "<tr>
								<td>$category_name</td>
								<td>$where_condition</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_query('$prime_table_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
								<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_query('$prime_table_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
							</tr>";
		}
		$table_query_content = "<table class='table table-bordered table-stripted'>
									<tr class='inline_head'>
										<th style='text-align: center;'>Query For</th>
										<th style='text-align:center;'>Condition Information</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
									$query_tr_line
								</table>";
		return $table_query_content;
	}
	public function get_edit_info(){
		$prime_table_id   = (int)$this->input->post('prime_table_id');
		$edit_query  = 'SELECT * FROM cw_form_table_search WHERE  prime_table_id = "'.$prime_table_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_query')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		if($edit_result){
			$prime_table_id  = $edit_result[0]->prime_table_id;
			$query_for       = $edit_result[0]->query_for;
			$where_condition = $edit_result[0]->where_condition;
			echo json_encode(array('success' => true,'prime_table_id'=>$prime_table_id,'query_for'=>$query_for,'where_condition'=>$where_condition));
		}else{
			echo json_encode(array('success' => false,'message'=>"Unable process your request"));
		}
	}
	public function remove_query_info(){
		$prime_table_id   = (int)$this->input->post('prime_table_id');
		$query_module_id  = $this->input->post('query_module_id');
		$logged_id        = $this->session->userdata('logged_id');
		$date             = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE  cw_form_table_search SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_table_id = "'.$prime_table_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$table_query_list = $this->get_saved_query_list($query_module_id);
		echo json_encode(array('success' => true,'message'=>'Table Query Remove Successfully !!!','table_query_list'=>$table_query_list));
	}
	
	//GET SUB MENU FOR MAIN MENU
	public function get_sub_menu(){
		$prime_menu_id   = (int)$this->input->post('prime_menu_id');
		$sub_menu_data = $this->db->query("CALL sp_a_run ('SELECT','SELECT cw_sub_menu.prime_sub_menu_id,sub_menu_name,menu_name FROM cw_main_menu join cw_sub_menu on cw_main_menu.prime_menu_id = cw_sub_menu.map_main_menu where cw_main_menu.prime_menu_id = $prime_menu_id and cw_sub_menu.trans_status = 1 order by abs(cw_sub_menu.sub_menu_sort) asc')");
		$sub_menu_result = $sub_menu_data->result();
		$sub_menu_data->next_result();
		$sub_map_menu_to[""] = "<option value='' disabled>---- Map Sub Menu to ----</option>";
		foreach($sub_menu_result as $sub_menu){
			$prime_sub_menu_id = $sub_menu->prime_sub_menu_id;
			$menu_name         = $sub_menu->menu_name;
			$sub_menu_name     = ucwords($sub_menu->sub_menu_name);
			$sub_name = $menu_name ." - ".  $sub_menu_name;
			$sub_map_menu_to .= "<option value='$prime_sub_menu_id'>$sub_name</option>";
		}
		echo json_encode(array('success' => TRUE,'message'=>$sub_map_menu_to));
	}
	
	/* ==============================================================*/
	/* =================== MODULE OPEARTION - END ===================*/
	/* ==============================================================*/	
	//SAVE FORMULA INFORMATION	
	public function save_payroll_info(){
		$prime_payroll_id  = $this->input->post('prime_payroll_id');
		$formula_for       = $this->input->post('formula_for');
		$formula_type      = $this->input->post('formula_type');
		$out_column        = $this->input->post('out_column');
		//$payroll_formula   = $this->input->post('payroll_formula');
		$payroll_formula   = strtolower($this->input->post('payroll_formula'));
		$formula_mode      = $this->input->post('formula_mode');
		$round_value       = $this->input->post('round_value');
		$fandf_only        = $this->input->post('fandf_only');
		$logged_id         = $this->session->userdata('logged_id');
		$date              = date("Y-m-d H:i:s");
		
		$fandf_only_val    = 0;
		if($fandf_only === "on"){
			$fandf_only_val = 1; 
		}
		
		//Checking start // check input columns should be in out column.
		$preg_match_inputs           = preg_match_all('#\@(.*?)\@#', $payroll_formula,$preg_match_inputsvalue);
		$preg_match_inputsvalue_count = count(array_unique($preg_match_inputsvalue[1]));
		$input_match_column    = implode('","',$preg_match_inputsvalue[1]);
		$input_match_column    ='"'.$input_match_column.'"';
		
		
		$exist_column_qry  = 'select GROUP_CONCAT(out_column) as out_column from `cw_payroll_formula` where trans_status = 1 and formula_for = "'.$formula_for.'" and out_column in ('.$input_match_column.')';
		$is_exist_column_data = $this->db->query("CALL sp_a_run ('SELECT','$exist_column_qry')");
		$exist_column_rslt    = $is_exist_column_data->result_array();
		$exist_column_count	  = $is_exist_column_data->num_rows();
		$is_exist_column_data->next_result();
		$check_out_column   = $exist_column_rslt[0]['out_column'];
		$rslt_out_column    = explode(',',$check_out_column);
		if(empty($check_out_column)){
			$exist_column_count = 0;
		}
		if(((int)$exist_column_count===(int)$preg_match_inputsvalue_count) || ((int)$exist_column_count===0 && (int)$formula_mode===1)){
			$sts = 1; // call to save functions
		}else{
			if(count($exist_column_rslt)>0){//empty checking to match formula
				if(!empty($rslt_out_column)){
					$missing_rslt  = array_diff($preg_match_inputsvalue[1],$rslt_out_column);//check two array differrence.
					if(!empty($missing_rslt)){
						$missing_rslt  = array_unique($missing_rslt);//multiple value uniq
						$missing_rslt  = implode(",",$missing_rslt);
						$missing_value = $missing_rslt. " Column is not exits. Please add this input first";
					}else{
						$sts = 1;
					}
				}else{
					$missing_value = $input_match_column." Columns is not exits. Please add this input first";
				}
			}else{
				$missing_value = $input_match_column." Columns is not exits. Please add this input first";
			}
		}
		(int)$prime_payroll_mode = 0;
		 //FORMULA MODE DIRECT TO FORMULA OR CONDITIONS OLD FORMULA IS DELETED AND NEWLY INSERTED
		if((int)$prime_payroll_id != 0){
			$update_qry_check = 'select formula_mode,formula_order from cw_payroll_formula where prime_payroll_id="'.$prime_payroll_id.'"';
			$is_update_qry_check_count_data = $this->db->query("CALL sp_a_run ('SELECT','$update_qry_check')");
			$count_rslt_count_data     = $is_update_qry_check_count_data->result();
			$is_update_qry_check_count_data->next_result();
			$formula_mode_previous  = $count_rslt_count_data[0]->formula_mode;
			$formula_order_previous = $count_rslt_count_data[0]->formula_order;
			if((int)$formula_mode_previous === 1){
				$update_qry = 'update cw_payroll_formula set trans_status = 0 where prime_payroll_id="'.$prime_payroll_id.'" and formula_mode = 1';
				$rslt = $this->db->query("CALL sp_a_run ('RUN','$update_qry')");
				(int)$prime_payroll_id   = 0;
				(int)$prime_payroll_mode = 1;
			}elseif((int)$formula_mode_previous === 2){
				(int)$prime_payroll_mode  = 2;
				(int)$previous_sort_order = $formula_order_previous;
			}
		}
		
		
		if((int)$sts === 1){
			$formula_rslt = $this->validate_payroll_formula($payroll_formula,$round_value,$formula_mode);
			$sts = $formula_rslt["sts"];
			$msg = $formula_rslt['msg'];
			if(!$sts){
				echo json_encode(array('success' => False, 'message' => $msg));
			}else{
				if((int)$prime_payroll_id === 0){
					$count_query='select IFNULL(MAX(formula_order), 0) as formula_order from cw_payroll_formula where formula_for = "'.$formula_for.'" and formula_mode !=1 order by formula_order desc';
					$is_count_data = $this->db->query("CALL sp_a_run ('SELECT','$count_query')");
					$count_rslt    = $is_count_data->result();
					$is_count_data->next_result();
					if((int)$formula_mode===1){
						$count_rslt_row = 1;
					}else{
						$count_rslt_row = (int)$count_rslt[0]->formula_order+1;
					}
					$is_exist_qry  = 'SELECT count(*) as rslt_count FROM `cw_payroll_formula` where trans_status = 1 and formula_for = "'.$formula_for.'" and out_column = "'.$out_column.'"';
					$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
					$exist_rslt    = $is_exist_data->result();
					$is_exist_data->next_result();
					if((int)$exist_rslt[0]->rslt_count === 0){
						$payroll_qry  = 'INSERT INTO cw_payroll_formula (formula_for,formula_type, out_column,payroll_formula,formula_mode,round_value,formula_order,fandf_only,trans_created_by, trans_created_date) VALUES ("'.$formula_for.'","'.$formula_type.'","'.$out_column.'","'.$payroll_formula.'","'.$formula_mode.'","'.$round_value.'","'.$count_rslt_row.'","'.$fandf_only_val.'","'.$logged_id.'","'.$date.'")';
						$this->db->query("CALL sp_a_run ('RUN','$payroll_qry')");
						$formula_content    = $this->get_saved_payroll_formula();
						if((int)$prime_payroll_mode ===1){
							$this->sort_direct_formula($formula_for,$out_column);
						}
						echo json_encode(array('success' => true, 'message' => "Payroll Formula successfully added",'formula_content'=>$formula_content));
					}else{
						echo json_encode(array('success' => False, 'message' => "Already formula is created for this role and column!"));
					}
				}else{
					$upd_qry  = 'UPDATE cw_payroll_formula SET formula_for = "'.$formula_for.'",formula_type = "'.$formula_type.'",out_column = "'.$out_column.'",payroll_formula = "'.$payroll_formula.'",formula_mode = "'.$formula_mode.'",round_value = "'.$round_value.'",fandf_only = "'.$fandf_only_val.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_payroll_id = "'.$prime_payroll_id.'"';
					$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
					$this->sort_formula($formula_for,$out_column,$payroll_formula,$formula_mode);
					$formula_content    = $this->get_saved_payroll_formula();
					if((int)$prime_payroll_mode === 2 && (int)$formula_mode!==2){
					    $this->sort_formula_direct($formula_for,$previous_sort_order);
					}
					echo json_encode(array('success' => true, 'message' => "Payroll Formula successfully updated",'formula_content'=>$formula_content));
				}
			}
		}else{
			echo json_encode(array('success' => False, 'message' => $missing_value,'formula_content'=>$formula_content));
		}
	}	
	
	//validation for php syntax missing in payroll formula save --start
	public function validate_payroll_formula($payroll_formula,$round_value,$formula_mode){
		$preg_match      = preg_match_all('#\@(.*?)\@#', $payroll_formula, $match);
		foreach($match[1] as $for_rslt){
			$find_value      = "@$for_rslt@";
			$for_value       = '$trans["'.$for_rslt.'"]';
			$payroll_formula = str_replace($find_value,$for_value,$payroll_formula);
		}
		//only conditions input code syntax is checked in the payroll function
		//if((int)$formula_mode === 3){
			$payroll_formula = urlencode ($payroll_formula);
			$check_url  = "http://phpcodechecker.com/api/?code=".$payroll_formula;
			$check_rslt = $this->curl_post($check_url);
			$check_rslt = json_decode($check_rslt);
			if($check_rslt->errors === "TRUE"){
				$syntax  = $check_rslt->syntax;
				$message = $syntax->message;
				return array("sts"=>false,"msg"=>$message);
			}else
			if($check_rslt->errors === "FALSE"){
				return array("sts"=>true, "msg"=>"Can Process");
			}else{
				return array("sts"=>false, "msg"=>"Please contact our admin team");
			}
		/* }else{
			return array("sts"=>true, "msg"=>"Can Process");
		} */
	}
	
	
	public function curl_post($check_url){
		$curl_result = curl_init(); // initialize cURL session
		if($curl_result === false) { 
			echo "Unable to initialize cURL session";// curl session failure
			exit(0);
		}
		curl_setopt($curl_result, CURLOPT_URL,$check_url);
		curl_setopt($curl_result, CURLOPT_RETURNTRANSFER, true);
		$var = curl_exec($curl_result);
		curl_close($curl_result); /* close cURL session */
		return $var;
	}
	
	//sorting formula updates
	public function sort_formula($formula_for,$out_column,$payroll_formula,$formula_mode){
		$preg_match_inputs   = preg_match_all('#\@(.*?)\@#', $payroll_formula,$preg_match_inputsvalue);
		$preg_match_inputsvalue_count = count($preg_match_inputsvalue[1]);
		$input_match_column    = implode('","',$preg_match_inputsvalue[1]);
		$input_match_column    ='"'.$input_match_column.'"';
		
		$qu_find_sortorder='select IFNULL(MIN(formula_order), 0) as formula_order_min, IFNULL(MAX(formula_order), 0) as formula_order_max from cw_payroll_formula where  formula_for = "'.$formula_for.'" and (out_column="'.$out_column.'" or out_column in ('.$input_match_column.')) and trans_status = 1 order by formula_order desc';	
		$max_min_data    = $this->db->query("CALL sp_a_run ('SELECT','$qu_find_sortorder')");
		$max_min_rslt    = $max_min_data->result();
		$max_min_data->next_result();
		
		//echo $qu_find_sortorder;
		//die;
		$outcolum_maxorder = $max_min_rslt[0]->formula_order_max;
		$outcolum_minorder = $max_min_rslt[0]->formula_order_min;
		$outcolum_order    = $outcolum_maxorder;
		$min               = $outcolum_minorder;
				
		$qu_find_sortorder_data='select out_column,formula_order from cw_payroll_formula where  formula_for = "'.$formula_for.'"  and (formula_mode !=1 or out_column="'.$out_column.'") and formula_order between  "'.$min.'" and "'.$outcolum_maxorder.'" and trans_status = 1 order by formula_order asc';
		$max_min_sort_data = $this->db->query("CALL sp_a_run ('SELECT','$qu_find_sortorder_data')");
		$max_min_sort_rslt    = $max_min_sort_data->result();
		$max_min_sort_data->next_result();
		foreach ($max_min_sort_rslt as $result){
			$out_column_db = $result->out_column;
			$formula_order = $result->formula_order;
			if($out_column==$out_column_db){
				if((int)$formula_mode === 1){
					$upd_sort = 'UPDATE cw_payroll_formula SET formula_order = 1 where formula_for = "'.$formula_for.'" and out_column = "'.$out_column.'" and trans_status = 1';
				}else{
					$upd_sort = 'UPDATE cw_payroll_formula SET formula_order = "'.$outcolum_order.'" where formula_for = "'.$formula_for.'" and out_column = "'.$out_column.'" and trans_status = 1';
				}					
				$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
			}else{
				$upd_sort = 'UPDATE cw_payroll_formula SET formula_order = "'.$min.'" where formula_for = "'.$formula_for.'" and out_column="'.$out_column_db.'" and formula_order="'.$formula_order.'" and formula_mode !=1 and trans_status = 1';				
				$this->db->query("CALL sp_a_run ('RUN','$upd_sort')");
				$min++;	
			}
		}
		if((int)$formula_mode !== 1){
			$find_max_order_qry ='select IFNULL(MAX(formula_order), 0) as max_order from cw_payroll_formula where formula_for = "'.$formula_for.'" and trans_status = 1 and out_column != "net_pay" order by formula_order desc';
			$max_order_data    = $this->db->query("CALL sp_a_run ('SELECT','$find_max_order_qry')");
			$max_order_rslt    = $max_order_data->result();
			$max_order_data->next_result();
			$max_order = $max_order_rslt[0]->max_order;
			$i = (int)$max_order + 1;
			if($max_order){
				$upd_sort_net = 'UPDATE cw_payroll_formula SET formula_order = "'.$i.'" where formula_for = "'.$formula_for.'" and out_column = "net_pay"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_sort_net')");
			}
		}
	}
	
	// sort order for direct to formula
	public function sort_direct_formula($formula_for,$out_column){
		$qu_find_sortorder_min='select IFNULL(MIN(formula_order), 0) as formula_order_min_value from cw_payroll_formula where formula_for = "'.$formula_for.'" and trans_status= 1 and payroll_formula like "%@'.$out_column.'@%" order by formula_order desc';
		$sortorder_min_data    = $this->db->query("CALL sp_a_run ('SELECT','$qu_find_sortorder_min')");
		$sortorder_min_rslt    = $sortorder_min_data->result();
		$sortorder_min_row    = $sortorder_min_data->num_rows();
		$sortorder_min_data->next_result();
		$outcolum_minorder = $sortorder_min_rslt[0]->formula_order_min_value;
		$qu_update_sort_minvalue='update cw_payroll_formula set formula_order=formula_order+1  where  formula_for = "'.$formula_for.'" and formula_order >="'.$outcolum_minorder.'" and trans_status=1 and formula_mode !=1';
		
		$qu_update_sort_data    = $this->db->query("CALL sp_a_run ('update','$qu_update_sort_minvalue')");
		$qu_update_sort_outcolumn='update cw_payroll_formula set formula_order="'.$outcolum_minorder.'" where  formula_for = "'.$formula_for.'" and out_column ="'.$out_column.'" and trans_status=1 and formula_mode !=1';
		$qu_update_sort_outcolumn_data    = $this->db->query("CALL sp_a_run ('update','$qu_update_sort_outcolumn')");
		
		
	}
	
	//sort order for formula to direct
	public function sort_formula_direct($formula_for,$previous_sort_order){
		$qu_update_sort_minvalue='update cw_payroll_formula set formula_order=formula_order-1  where  formula_for = "'.$formula_for.'" and formula_order >"'.$previous_sort_order.'" and trans_status=1';
		$qu_update_sort_data    = $this->db->query("CALL sp_a_run ('update','$qu_update_sort_minvalue')");
	}
	
	//DISPLAY FORMULA IN TABLE
	public function get_saved_payroll_formula(){
		//$formula_list        = 'SELECT prime_payroll_id,category_name,out_column,payroll_formula,formula_type,formula_mode,round_value FROM cw_payroll_formula INNER JOIN cw_category on cw_category.prime_category_id = cw_payroll_formula.formula_for WHERE cw_payroll_formula.trans_status = 1 order by cw_payroll_formula.formula_for, abs(cw_payroll_formula.formula_order) asc';
		//only employee module
		$formula_list        = 'select prime_payroll_id,category_name,out_column,view_name,payroll_formula,formula_type,formula_mode,round_value from cw_payroll_formula inner join cw_category on cw_category.prime_category_id = cw_payroll_formula.formula_for inner join cw_form_setting on cw_form_setting.label_name=cw_payroll_formula.out_column where cw_payroll_formula.trans_status = 1 AND prime_module_id= "employees" order by cw_payroll_formula.formula_for, abs(cw_payroll_formula.formula_order) asc';
		$formula_list_info   = $this->db->query("CALL sp_a_run ('SELECT','$formula_list')");
		$formula_list_result = $formula_list_info->result();
		$formula_list_info->next_result();
		$formula_mode_list = array(1=>"Direct Input",2=>"Formula Input",3=>"Conditions Input");
		foreach($formula_list_result as $rslt){
			$prime_payroll_id  = $rslt->prime_payroll_id;
			$formula_for       = $rslt->category_name;
			$out_column        = $rslt->out_column;
			$payroll_formula   = $rslt->payroll_formula;
			$formula_type      = $rslt->formula_type;
			$round_value       = $rslt->round_value;
			$out_column_name   = $rslt->view_name;
			$formula_mode      = $formula_mode_list[$rslt->formula_mode];
			//$out_column_name   = ucwords(str_replace("_"," ",$out_column));
			$formula_type_val = "";
			if((int)$formula_type === 1){
				$formula_type_val = "Earnings";
			}else
			if((int)$formula_type === 2){
				$formula_type_val = "Deductions";
			}
			$rounding_list = array("0.1"=>"Normal","0.5"=>"50 Paise",">0.5"=>"> 50 Paise","<0.5"=>"< 50 Paise","1"=>"1 Rupee",">1"=>"> 1 Rupee","<1"=>"< 1 Rupee","5"=>"5 Rupee","10"=>"10 Rupee","50"=>"50 Rupee","100"=>"100 Rupee");
			
			if(array_key_exists($round_value,$rounding_list)){
				$rounding_val = $rounding_list[$round_value];
			}else{
				$rounding_val = "";
			}
			
			$formula_tr_line .= "<tr>
								<td data-order='$formula_for' data-filter='$formula_for'>$formula_for</td>
								<td>$formula_type_val</td>
								<td>$out_column_name</td>
								<td style='max-width:480px !important;overflow: auto;'>$payroll_formula</td>
								<td>$formula_mode</td>
								<td>$rounding_val</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=get_formula_edit('$prime_payroll_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							</tr>";
		}
		$formula_content = "<table class='table table-bordered table-stripted' id='detail_list'>
								<thead>
									<tr class='inline_head'>
										<th style='text-align: center;'>Formula For </th>
										<th style='text-align: center;'>Formula Type</th>
										<th style='text-align:center;'>Output Column</th>
										<th style='text-align:center;'>Formula Information</th>
										<th style='text-align:center;'>Formula Mode</th>
										<th style='text-align:center;'>Round Value</th>
										<th style='text-align:center;'>Edit</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<td style='text-align: center;'>Formula For </td>
										<td style='text-align: center;'>Formula Type</td>
										<td/>
										<td/>
										<td style='text-align:center;'>Formula Mode</td>
										<td/>
										<td/>
									</tr>
								</tfoot>
								<tbody>
									$formula_tr_line
								</tbody>
								</table>";
		return $formula_content;
	}
	
	//EDIT FORMULA INFORMATION
	public function get_formula_edit_info(){
		$prime_payroll_id   = (int)$this->input->post('prime_payroll_id');
		$edit_formula = 'SELECT * FROM cw_payroll_formula WHERE  prime_payroll_id = "'.$prime_payroll_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_formula')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		if($edit_result){
			$prime_payroll_id  = $edit_result[0]->prime_payroll_id;
			$formula_for       = $edit_result[0]->formula_for;
			$formula_type      = $edit_result[0]->formula_type;
			$out_column        = $edit_result[0]->out_column;
			$payroll_formula   = $edit_result[0]->payroll_formula;
			$formula_mode      = $edit_result[0]->formula_mode;
			$round_value       = $edit_result[0]->round_value;
			$fandf_only        = $edit_result[0]->fandf_only;
			
			echo json_encode(array('success' => true,'prime_payroll_id'=>$prime_payroll_id,'formula_for'=>$formula_for,'formula_type'=>$formula_type,'out_column'=>$out_column,'payroll_formula'=>$payroll_formula,'formula_mode'=>$formula_mode,'round_value'=>$round_value,'fandf_only'=>$fandf_only));
		}else{
			echo json_encode(array('success' => false,'message'=>"Invalid your Request!"));
		}
	}
	
	//DELETE FORMULA INFORMATION
	public function remove_formula_info(){
		$prime_payroll_id  = (int)$this->input->post('prime_payroll_id');
		$logged_id         = $this->session->userdata('logged_id');
		$date              = date("Y-m-d h:i:s");
		$remove_qry  = 'UPDATE cw_payroll_formula SET trans_status = 0,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_payroll_id = "'.$prime_payroll_id.'"';
		$delete  = $this->db->query("CALL sp_a_run ('UPDATE','$remove_qry')");
		$delete->next_result();
		$formula_content    = $this->get_saved_payroll_formula();
		echo json_encode(array('success' => true, 'message'=>'Formula deleted Successfully!!!','formula_content'=>$formula_content));
	}
	
	public function check_column_name(){
		$earn_column  = $this->input->post('earn_column');
		$formula_for  = $this->input->post('formula_for');
		$column_check = 'SELECT count(*) as col_count FROM cw_payroll_formula WHERE cw_payroll_formula.trans_status = 1 and out_column="'.$earn_column.'" and formula_for = "'.$formula_for.'"';
		$column_info   = $this->db->query("CALL sp_a_run ('SELECT','$column_check')");
		$column_result = $column_info->result();
		$column_info->next_result();
		if($column_result){
			$col_count  = $column_result[0]->col_count;
		}
		echo json_encode(array('col_count'=>$col_count));
	}
	
	public function save_print_map(){
		$prime_print_map_id  = (int)$this->input->post('prime_print_map_id');
		$print_map_module_id = $this->input->post('print_map_module_id');
		$print_map_for       = $this->input->post('print_map_for');
		$print_map           = ltrim(implode(",",$this->input->post('print_mapping[]')),",");
		if($prime_print_map_id === 0){
			$is_exist_qry  = 'SELECT * FROM cw_print_map where print_map_module_id = "'.$print_map_module_id.'" and print_map_for = "'.$print_map_for.'" and trans_status = 1 ';
			$is_exist_data = $this->db->query("CALL sp_a_run ('SELECT','$is_exist_qry')");
			$exist_count   = $is_exist_data->num_rows();
			$exist_rslt    = $is_exist_data->result();
			$is_exist_data->next_result();
			if((int)$exist_count === 0){	
				$print_qry  = 'INSERT INTO cw_print_map (print_map_module_id, print_map_for,print_map,trans_created_by, trans_created_date) VALUES ("'.$print_map_module_id.'","'.$print_map_for.'","'.$print_map.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$print_qry')");	
				$print_info_list =  $this->get_print_map_list($print_map_module_id);
				echo json_encode(array('success' => true, 'message' => "Print info successfully added",'print_info_list'=>$print_info_list));
			}else{
				$prime_print_map_id = $exist_rslt[0]->prime_print_map_id;				
				$upd_qry  = 'UPDATE  cw_print_map SET print_map_module_id = "'.$print_map_module_id.'",print_map_for = "'.$print_map_for.'",print_map = "'.$print_map.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_map_id = "'.$prime_print_map_id.'"';
				$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
				$print_info_list =  $this->get_print_map_list($print_map_module_id);
				echo json_encode(array('success' => true, 'message' => "Print info successfully updated",'print_info_list'=>$print_info_list));
			}
		}else{
			$upd_qry  = 'UPDATE  cw_print_map SET print_map_module_id = "'.$print_map_module_id.'",print_map_for = "'.$print_map_for.'",print_map = "'.$print_map.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_print_map_id = "'.$prime_print_map_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$upd_qry')");
			$print_info_list =  $this->get_print_map_list($print_map_module_id);
			echo json_encode(array('success' => true, 'message' => "Print info successfully updated",'print_info_list'=>$print_info_list));
		}
		
	}
	public function get_print_map_list($print_map_module_id){
		$print_qry  = 'SELECT * FROM cw_print_map where print_map_module_id = "'.$print_map_module_id.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();	
		$print_data->next_result();
		
		$tr_line 	  = "";		
		foreach($print_rslt as $rslt){
			$prime_print_map_id   = $rslt->prime_print_map_id;
			$print_map_module_id  = $rslt->print_map_module_id;
			$print_map_for        = $rslt->print_map_for;
			$print_map            = $rslt->print_map;
						
			$print_for_qry  = 'SELECT GROUP_CONCAT(print_info_name) as print_info_name FROM cw_print_info where prime_print_info_id in ('.$print_map.')';
			$print_for_data = $this->db->query("CALL sp_a_run ('SELECT','$print_for_qry')");
			$print_for_rslt = $print_for_data->result();			
			$print_for_data->next_result();
			$print_info_name  = $print_for_rslt[0]->print_info_name;
			$print_map_for_line = array("1"=>"Payslip Formates","2"=>"F&F Payslip Formates","3"=>"Form M","4"=>"PF Challan","5"=>"ESI","6"=>"Professional TAX","7"=>"Bonus");
			$print_map_for = $print_map_for_line[$print_map_for];
			$tr_line .= "<tr>
							<td>$print_map_for</td>
							<td>$print_info_name</td>
							<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=edit_print_map('$prime_print_map_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
							<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_print_map('$prime_print_map_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
						</tr>";
		}
		$print_info_list = "<table class='table table-bordered table-stripted' id='print_info_table'>
							<tr class='inline_head'>
								<th>Print Formate For</th>
								<th>Map Print Formate</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
							$tr_line
						</table>";
		return "$print_info_list";
	}
	
	//EDIT PRINT INFO OPERATION
	public function edit_print_map(){
		$prime_print_map_id  = (int)$this->input->post('prime_print_map_id');
		$print_qry  = 'SELECT * FROM cw_print_map` where prime_print_map_id = "'.$prime_print_map_id.'" and trans_status = 1';
		$print_data = $this->db->query("CALL sp_a_run ('SELECT','$print_qry')");
		$print_rslt = $print_data->result();			
		$print_data->next_result();
		echo json_encode(array('success' => TRUE,'print_info' => $print_rslt[0]));
	}
	
	//MRJ
	/* ==============================================================*/
	/* =================== PAYROLL FUNCTION - START - 01MARCH2019 ===*/
	/* ==============================================================*/
	//Statutory function save method
	public function save_payroll_function(){
		$payroll_function_id  = $this->input->post('payroll_function_id');
		$statutory_name       = $this->input->post('statutory_name');
		$map_column           = $this->input->post('map_column');
		$input_column         = implode(",",$this->input->post('input_column[]'));
		$input_column         = ltrim($input_column,',');
		$function_name        = $this->input->post('function_name');
		$logged_id            = $this->session->userdata('logged_id');
		$date                 = date("Y-m-d H:i:s");
		if($payroll_function_id === "0"){
			$statutory_exist_qry     = 'SELECT count(*) as rslt_count FROM `cw_payroll_function` where trans_status = 1 and statutory_name = "'.$statutory_name.'" and statutory_name != 6';
			$statutory_exist_data   = $this->db->query("CALL sp_a_run ('SELECT','$statutory_exist_qry')");
			$statutory_exist_result = $statutory_exist_data->result();
			$statutory_exist_count  = $statutory_exist_result[0]->rslt_count;
			$statutory_exist_data->next_result();
			if((int)$statutory_exist_count === 0){
				$save_qry  = 'INSERT INTO cw_payroll_function (statutory_name, map_column, input_column, function_name, trans_created_by, trans_created_date) VALUES ("'.$statutory_name.'","'.$map_column.'","'.$input_column.'","'.$function_name.'","'.$logged_id.'","'.$date.'")';
				$this->db->query("CALL sp_a_run ('RUN','$save_qry')");
				$function_list    = $this->payroll_function_list();
				echo json_encode(array('success' => True,'message'=>"Successfully function/column is mapped!",'function_list'=>$function_list));
			}else{
				echo json_encode(array('success' => False,'message' => "Already function is mapped!",'function_list'=>$function_list));
			}
		}else{
			$update_qry  = 'UPDATE cw_payroll_function SET statutory_name = "'.$statutory_name.'",map_column = "'.$map_column.'",input_column = "'.$input_column.'",function_name = "'.$function_name.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where prime_payroll_function_id = "'.$payroll_function_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$update_qry')");
			$function_list    = $this->payroll_function_list();
			echo json_encode(array('success' => True,'message'=>"updated successfully function!",'function_list'=>$function_list));
		}
	}
	
	//table view list out the payroll function
	public function payroll_function_list(){
		$function_list_qry        = 'SELECT prime_payroll_function_id,statutory_field_name, map_column, input_column, statutory_function_name FROM cw_payroll_function inner join cw_statutory_field on cw_statutory_field.prime_statutory_field_id=cw_payroll_function.statutory_name inner join cw_statutory_function on cw_statutory_function.prime_statutory_function_id =cw_payroll_function.function_name WHERE cw_payroll_function.trans_status = 1 order by prime_payroll_function_id desc';
		$function_list_info   = $this->db->query("CALL sp_a_run ('SELECT','$function_list_qry')");
		$function_list_result = $function_list_info->result();
		$function_list_info->next_result();
		foreach($function_list_result as $rslt){
			$payroll_function_id  = $rslt->prime_payroll_function_id;
			$statutory_name       = $rslt->statutory_field_name;
			$map_column           = $rslt->map_column;
			$input_column         = $rslt->input_column;
			$function_name        = $rslt->statutory_function_name;
			$map_column           = ucwords(str_replace("_"," ",$map_column));
			$input_column         = ucwords(str_replace("_"," ",$input_column));
			
			$function_tr_line .= "<tr>
								<td>$statutory_name</td>
								<td>$map_column</td>
								<td>$input_column</td>
								<td>$function_name</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=get_function_edit('$payroll_function_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
								<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_function('$payroll_function_id')> <i class='fa fa-trash-o' aria-hidden='true'></i> Delete</a></td>
							</tr>";
		}
		$function_list = "<table class='table table-bordered table-stripted' id='function_list'>
								<thead>
									<tr class='inline_head'>
										<th style='text-align: center;'>Statutory Name</th>
										<th style='text-align: center;'>Map Column</th>
										<th style='text-align:center;'>Input Column</th>
										<th style='text-align:center;'>Function Name</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
								</thead>
								<tbody>
									$function_tr_line
								</tbody>
								</table>";
		return $function_list;
		
	}
	
	//edit for payroll function
	public function get_function_edit(){
		$payroll_function_id   = (int)$this->input->post('payroll_function_id');
		$edit_query  = 'SELECT * FROM cw_payroll_function WHERE  prime_payroll_function_id = "'.$payroll_function_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_query')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		echo json_encode(array('success' => true,'edit_result'=>$edit_result[0]));
	}
	
	//remove for payroll function
	public function remove_payroll_function(){
		$payroll_function_id = (int)$this->input->post('payroll_function_id');
		$logged_id           = $this->session->userdata('logged_id');
		$date                = date("Y-m-d H:i:s");
		$remove_qry  = 'UPDATE cw_payroll_function SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where prime_payroll_function_id = "'.$payroll_function_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$function_list    = $this->payroll_function_list();
		echo json_encode(array('success' => true,'message'=>'Function mapped successfully removed!!!','function_list'=>$function_list));
	}
	/* ==============================================================*/
	/* =================== PAYROLL FUNCTION - END - 02MARCH2019 =====*/
	/* ==============================================================*/
	
	//Column Mapping Save
	public function save_column_mapping(){
		$payroll_column_map_id  = $this->input->post('payroll_column_map_id');
		$map_statutory_name     = $this->input->post('map_statutory_name');
		$loc_name               = $this->input->post('loc_name');
		$db_column              = $this->input->post('db_column');
		$logged_id              = $this->session->userdata('logged_id');
		$date                   = date("Y-m-d H:i:s");
		if($payroll_column_map_id === "0"){
			$insert_map_qry  = 'INSERT INTO cw_payroll_function_map (map_statutory_name, loc_name, db_column,trans_created_by, trans_created_date) VALUES ("'.$map_statutory_name.'","'.$loc_name.'","'.$db_column.'","'.$logged_id.'","'.$date.'")';
			$this->db->query("CALL sp_a_run ('RUN','$insert_map_qry')");
			$mapping_list    = $this->column_mapping_list();
			echo json_encode(array('success' => True,'message'=>"Successfully column is mapped!",'mapping_list'=>$mapping_list));
		}else{
			$update_qry  = 'UPDATE cw_payroll_function_map SET map_statutory_name = "'.$map_statutory_name.'",loc_name = "'.$loc_name.'",db_column = "'.$db_column.'",trans_updated_by = "'.$logged_id.'",trans_updated_date = "'.$date.'" where payroll_column_map_id = "'.$payroll_column_map_id.'"';
			$this->db->query("CALL sp_a_run ('RUN','$update_qry')");
			$mapping_list    = $this->column_mapping_list();
			echo json_encode(array('success' => True,'message'=>"updated successfully column!",'mapping_list'=>$mapping_list));
		}
	}
	
	//table view list out the mapping column
	public function column_mapping_list(){
		$column_mapping_qry        = 'select payroll_column_map_id,statutory_field_name, loc_name, db_column from cw_payroll_function_map inner join cw_statutory_field on cw_statutory_field.prime_statutory_field_id=cw_payroll_function_map.map_statutory_name where cw_payroll_function_map.trans_status = 1 order by payroll_column_map_id desc';
		$column_mapping_info   = $this->db->query("CALL sp_a_run ('SELECT','$column_mapping_qry')");
		$column_mapping_result = $column_mapping_info->result();
		$column_mapping_info->next_result();
		$mapping_tr_line = "";
		$loc_column_list = array("earned_gross"=>"Earned Gross","paid_days"=>"Paid Days","month_days"=>"Month Days","fixed_basic"=>"Fixed Basic","fixed_gross"=>"Fixed Gross","lop_days"=>"Lop Days","professional_tax_amount"=>"Professional Tax","esi_loc"=>"ESI Location","esi_elig"=>"ESI Eligibility","supp_month_days"=>"Supplementary Month days","supp_paid_days"=>"Supplementary Paid days","arrear_gross"=>"Arrear Gross","arrear_pf_gross"=>"Arrear PF Gross","earned_basic"=>"Earned Basic");
		foreach($column_mapping_result as $rslt){
			$payroll_column_map_id  = $rslt->payroll_column_map_id;
			$statutory_name         = $rslt->statutory_field_name;
			$loc_name               = $rslt->loc_name;
			$db_column              = $rslt->db_column;
			$db_column              = ucwords(str_replace("_"," ",$db_column));
			if(array_key_exists($loc_name, $loc_column_list)){
				$loc_name  = $loc_column_list["$loc_name"];
			}
			$mapping_tr_line .= "<tr>
								<td>$statutory_name</td>
								<td>$loc_name</td>
								<td>$db_column</td>
								<td style='text-align:center;'><a class='btn btn-xs btn-edit' onclick=get_mapping_edit('$payroll_column_map_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a></td>
								<td style='text-align:center;'><a class='btn btn-xs btn-danger' onclick=remove_mapping_function('$payroll_column_map_id')> <i class='fa fa-pencil-square-o' aria-hidden='true'></i> Delete</a></td>
							</tr>";
		}
		$mapping_list = "<table class='table table-bordered table-stripted' id='mapping_list'>
								<thead>
									<tr class='inline_head'>
										<th style='text-align: center;'>Statutory Name</th>
										<th style='text-align: center;'>Local Column</th>
										<th style='text-align:center;'>DP Column</th>
										<th style='text-align:center;'>Edit</th>
										<th style='text-align:center;'>Delete</th>
									</tr>
								</thead>
								<tbody>
									$mapping_tr_line
								</tbody>
								</table>";
		return $mapping_list;
	}
	
	//edit for payroll function
	public function get_mapping_edit(){
		$payroll_column_map_id   = (int)$this->input->post('payroll_column_map_id');
		$edit_query  = 'SELECT * FROM cw_payroll_function_map WHERE  payroll_column_map_id = "'.$payroll_column_map_id.'" and trans_status = 1';
		$edit_info   = $this->db->query("CALL sp_a_run ('SELECT','$edit_query')");
		$edit_result = $edit_info->result();
		$edit_info->next_result();
		echo json_encode(array('success' => true,'edit_result'=>$edit_result[0]));
	}
	
	//remove for payroll function
	public function remove_mapping_function(){
		$payroll_column_map_id = (int)$this->input->post('payroll_column_map_id');
		$logged_id             = $this->session->userdata('logged_id');
		$date                  = date("Y-m-d H:i:s");
		$remove_qry  = 'UPDATE cw_payroll_function_map SET trans_status = 0 ,trans_deleted_by = "'.$logged_id.'",trans_deleted_date = "'.$date.'" where payroll_column_map_id = "'.$payroll_column_map_id.'"';
		$this->db->query("CALL sp_a_run ('SELECT','$remove_qry')");
		$mapping_list    = $this->column_mapping_list();
		echo json_encode(array('success' => true,'message'=>'Mapping column is removed!!!','mapping_list'=>$mapping_list));
	}
	
	/* ==============================================================*/
	/* =================== FORMULA TRANSFER - START - 10APR2019 =====*/
	/* ==============================================================*/
	
	public function save_formula_transfer(){
		$input_formula_role  =  (int)$this->input->post('select_category');
		$trans_formula_role  =  (int)$this->input->post('formula_transfer_to');
		$logged_id           = $this->session->userdata('logged_id');
		$date                = date("Y-m-d H:i:s");
		if($trans_formula_role){
			$exist_qry = 'select count(prime_payroll_id) as rslt_count from  cw_payroll_formula where  formula_for = "'.$trans_formula_role.'" and trans_status = 1';
			$exist_info   = $this->db->query("CALL sp_a_run ('SELECT','$exist_qry')");
			$exist_result = $exist_info->result();
			$exist_info->next_result();
			$exist_count  = $exist_result[0]->rslt_count;
			if((int)$exist_count === 0){
				$insert_qry = 'insert into cw_payroll_formula (formula_for, formula_type, out_column,payroll_formula,formula_mode,round_value,formula_order,trans_created_by,trans_created_date,trans_status) select "'.$trans_formula_role.'", formula_type, out_column,payroll_formula,formula_mode,round_value,formula_order,"'.$logged_id.'","'.$date.'",trans_status from cw_payroll_formula where trans_status = 1 and formula_for = '.$input_formula_role;
				$this->db->query("CALL sp_a_run ('RUN','$insert_qry')");
				echo json_encode(array('success' => true,'message'=>'Successfully formula is transfered!!'));
			}else{
				echo json_encode(array('success' => False,'message'=>'Already Formula is present this category,please delete the formula'));
			}
		}
	}
	
	/* ==============================================================*/
	/* =================== FORMULA TRANSFER - START - 10APR2019 =====*/
	/* ==============================================================*/
}
?>