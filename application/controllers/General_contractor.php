<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class General_contractor  extends Action_controller{	
	public function __construct(){
		parent::__construct('general_contractor');
		$this->collect_base_info();
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;
		$this->load->view("$this->control_name/manage",$data);
	}
	
	//LOAD TABEL WITH FILTERS
	public function search(){
		$draw         = $this->input->post('draw');
		$start        = $this->input->post('start');
		$per_page     = $this->input->post('length');
		$order        = $this->input->post('order');
		$order_col    = $this->input->post('columns');
		$search       = $this->input->post('search');
		$column       = $order[0]['column'];
		$order_sor    = $order[0]['dir'];
		$order_col    = $order_col[$column]['data'];
		$search       = trim($search['value']);
		$search_query = str_replace("@SELECT@",$this->select_query,$this->base_query);
		
		//ADDED BASIC,FILTER,COMMON QUERY HERE 
		$role_condition   = "";
		if($this->role_condition){
			$role_condition = $this->role_condition;
		}
		
		$fliter_query = "";
		foreach($this->fliter_list as $fliter){
			$label_id         = $fliter['label_id'];
			$label_name       = $fliter['label_name'];
			$field_isdefault  = (int)$fliter['field_isdefault'];
			$array_list       = $fliter['array_list'];
			$field_type       = (int)$fliter['field_type'];			
			if($field_isdefault === 1){
				$column_name = $this->prime_table .".$label_id";				
				$search_val  = $this->input->post("$label_id");
				if($search_val){
					if($field_type === 4){
						$search_val = date('Y-m-d',strtotime($search_val));
						$fliter_query .= " and $column_name = '$search_val'";
					}else
					if(($field_type === 5) || ($field_type === 7)){
						$search_val    = trim(implode(",",$search_val));
						$fliter_query .= " and $column_name in ($search_val)";  
					}else
					if($field_type === 13){
						$search_val = date('Y-m-d H:i:s',strtotime($search_val));
						$fliter_query .= " and $column_name = '$search_val'";
					}else{
						$fliter_query .= " and $column_name LIKE '$search_val%'";
					}
				}
			}
		}
		
		$common_search = "";
		if($search){
			foreach($this->form_info as $setting){
				$prime_form_id   = $setting->prime_form_id;
				$field_type      = (int)$setting->field_type;
				$pick_list       = $setting->pick_list;
				$pick_table      = $setting->pick_table;
				$pick_list_type  = $setting->pick_list_type;
				$input_view_type = (int)$setting->input_view_type;
				$auto_prime_id      = $setting->auto_prime_id;
				$auto_dispaly_value = $setting->auto_dispaly_value;
				$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
				$field_isdefault    = (int)$setting->field_isdefault;
				if($field_isdefault === 1){					
					if(($input_view_type === 1) || ($input_view_type === 2)){
						$search_label = "$this->prime_table.$label_id";
						$search_val   = "";
						if($field_type === 4){ // having issues in date search
							if(strtotime($search)){
								$search_val = date('Y-m-d',strtotime($search));
								$common_search .= ' or '. $search_label .' like "'.$search_val.'%"';
							}
						}else
						if(($field_type === 5) || ($field_type === 7) || ($field_type === 9)){							
							$result = array_filter($this->master_pick[$label_id], function ($item) use ($search) {
								if (stripos($item, $search) !== false) {
									return true;
								}
								return false;
							});
							if($result){
								$pick_key   = implode(",",array_keys($result));
								$common_search .= ' or '. $search_label .' in("'.$pick_key.'")';
							}
						}else{
							$common_search .= ' or '. $search_label .' like "%'.$search.'%"';
						}
					}
				}
			}
		
                        if($common_search){
                                $common_search = ltrim($common_search,' or ');
                                $common_search = " and ($common_search)";
				$common_search = str_replace("(,","(",$common_search);
				$common_search = str_replace("()","(0)",$common_search);
                        }
		}
		$count_all_query    = str_replace("@SELECT@","count(*) as allcount",$this->base_query);		
		$search_total       = $this->db->query($count_all_query);
		$search_total_info  = $search_total->result();
		$total_count        = $search_total_info[0]->allcount;
		
		$count_query        = str_replace("@SELECT@","count(*) as allcount",$this->base_query);
		$count_query       .= " where $this->prime_table.trans_status = 1 $role_condition $fliter_query $common_search";
		$search_count       = $this->db->query($count_query);
		$search_info        = $search_count->result();
		$filtered_count     = $search_info[0]->allcount;
		
		$search_query      .= " where $this->prime_table.trans_status = 1 $role_condition $fliter_query $common_search";
		$search_query      .= " ORDER BY  $order_col $order_sor";
		if((int)$per_page !== -1){
			$search_query  .= " LIMIT  $start,$per_page";
		}		
		$search_data        = $this->db->query($search_query);
		$search_result      = $search_data->result();
		//echo "search_query :: \n$search_query\n";		
		echo json_encode(array("draw" => intval($draw),"recordsTotal" => $total_count,"recordsFiltered" => $filtered_count,"data" => $search_result));		
	}
	
	//LOAD MODEL PAGE VIEW WITH DATA
	public function view($form_view_id=-1){
		//VIEW, FORM INPUT
		$data['view_info']      = $this->view_info;
		$data['form_info']      = $this->form_info;	
		
		//VIEW DATA
		$base_query  = str_replace("@SELECT@",$this->view_select,$this->base_query);
		$view_query  = $base_query ." where $this->prime_table.$this->prime_id = $form_view_id and $this->prime_table.trans_status = 1";
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_query')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$data['form_view']   = $view_result[0];	
		
		//AUTO COMPLTE,PICK LIST AND CONDITION
		foreach($this->form_info as $from){
			$prime_form_id      = (int)$from->prime_form_id;
			$field_type         = (int)$from->field_type;
			$pick_table         = $from->pick_table;
			$auto_prime_id      = $from->auto_prime_id;
			$auto_dispaly_value = $from->auto_dispaly_value;
			$label_id           = $from->label_name;
			if($field_type === 9){
				if($view_result[0]){
					$get_value = $view_result[0]->$label_id;
					if($get_value){
						$pick_query = 'select '.$auto_dispaly_value.' from '.$pick_table.' where '.$auto_prime_id.' = "'.$get_value.'" and trans_status = 1';
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						$this->all_pick[$prime_form_id] = $pick_result[0]->$auto_dispaly_value;
					}					
				}
			}
		}
		$data['all_pick']       = $this->all_pick;
		$data['condition_list'] = $this->condition_list;
		
		$view_qry    = 'select * from cw_form_view_setting where  prime_view_module_id = "'.$this->control_name.'" and  form_view_type = "3" and trans_status = 1';
		$view_data   = $this->db->query("CALL sp_a_run ('SELECT','$view_qry')");
		$view_result = $view_data->result();
		$view_data->next_result();
		$row_view_list = array();
		foreach($view_result as $view){
			$prime_form_view_id   = $view->prime_form_view_id;
			$row_set_data = $this->get_row_set_data($prime_form_view_id,$form_view_id);
			$row_view_list[$prime_form_view_id] = $row_set_data;
		}
		$data['row_view_list']   = $row_view_list;
		//Role Based Condition
		$role_based_query  = 'SELECT * FROM cw_role_base_condition WHERE  role_module_id = "'.$this->control_name.'" and FIND_IN_SET("'.$this->logged_user_role.'",role_condition_for) and trans_status = 1';
		$role_based_info   = $this->db->query("CALL sp_a_run ('SELECT','$role_based_query')");
		$role_based_result = $role_based_info->result();
		$role_based_info->next_result();

		$role_based_condition = array();
		foreach ($role_based_result as $key => $condition) {
			$role_based_condition[$condition->user_condition_type] = $condition->input_columns;
		}
		$data['role_based_condition'] = $role_based_condition;
		$this->load->view("$this->control_name/form",$data);
	}
	
	//SAVE MODEL DATA TO DATA BASE
	public function save(){
		$unq_chk         = array();
		$prime_qry_key   = "";
		$prime_qry_value = "";
		$prime_upd_query = "";
		$cf_qry_key      = "";
		$cf_qry_value    = "";
		$cf_upd_query    = "";	
		$cf_has          = false;
		$form_id         = (int)$this->input->post($this->prime_id);
		$form_post_data  = array();	
		foreach($this->form_info as $setting){
			$field_type      = $setting->field_type;
			$input_view_type = (int)$setting->input_view_type;
			$label_id        = strtolower(str_replace(" ","_",$setting->label_name));
			$field_isdefault = $setting->field_isdefault;
			$unique_field    = (int)$setting->unique_field;
			$view_name       = $setting->view_name;
			
			
			if((int)$field_type === 7){
				$multi_name = $label_id."[]";
				$value = trim(implode(",",$this->input->post($multi_name)));
			}else{
				$value = trim($this->input->post($label_id));
			}
			
			if((int)$field_type === 4){
				$value = date('Y-m-d',strtotime($value));
			}else
			if((int)$field_type === 13){
				$value = date('Y-m-d H:i:s',strtotime($value));
			}	
			
			if(($input_view_type === 1) || ($input_view_type === 2)){
				if((int)$field_isdefault === 1){
					$prime_qry_key     .= $label_id.",";
					$prime_qry_value   .= '"'.$value.'",';
					$prime_upd_query   .= $label_id.' = "'.$value.'",';
					if($unique_field === 1){
						$prime_unq_chk = $label_id.'= "'.$value.'"';
						$query = "select count(*) as rslt_count from $this->prime_table where $this->prime_id != $form_id and $prime_unq_chk";
						$unq_chk[] = array('label_id'=>$label_id,'view_name'=>$view_name,'query'=>$query,);
					}
				}
			}
		}
		$rslt_count = 0;
		$can_process = array();
		foreach($unq_chk as $unq_rslt){
			$query       = $unq_rslt['query'];
			$label_id    = $unq_rslt['label_id'];
			$view_name   = $unq_rslt['view_name'];
			$unq_info    = $this->db->query("CALL sp_a_run ('RUN','$query')");
			$unq_result  = $unq_info->result();
			$unq_info->next_result();
			if($unq_result){
				$rslt_count = (int)$unq_result[0]->rslt_count;
				if($rslt_count !== 0){
					$can_process[] = $view_name." already exist";
				}
			}
		}
		if(count($can_process) > 0){
			$can_process  = array_values($can_process);			
			$can_process  = implode(",<br/>", $can_process);
			echo json_encode(array('success' => false, 'message' => $can_process,));
		}else{
			$created_on = date("Y-m-d h:i:s");
			if((int)$form_id === 0){
				$prime_qry_key     .= "trans_created_by,trans_created_date";
				$prime_qry_value   .= '"'.$this->logged_id.'",'.'"'.$created_on.'"';
				$prime_insert_query = "insert into $this->prime_table ($prime_qry_key) values ($prime_qry_value)";
				$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$prime_insert_query')");
				$insert_result      = $insert_info->result();
				$insert_info->next_result();
				$insert_id = $insert_result[0]->ins_id;				
				echo json_encode(array('success' => TRUE, 'message' => "Successfully added", 'insert_id' => $insert_id));
			}else{
				$prime_upd_query    .= 'trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
				$prime_update_query  = 'UPDATE '. $this->prime_table .' SET '. $prime_upd_query .' WHERE '. $this->prime_id .' = "'. $form_id .'"';
				$this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')");
				echo json_encode(array('success' => TRUE, 'message' => "Successfully updated",'insert_id' => $form_id));
			}
		}
	}
	
	//UPDATE STATUS TO DELETE IN MODULE PRIMARY TABLE
	public function delete(){
		$delete_ids    = implode(",",$this->input->post('delete_ids'));
		$can_process   = TRUE;
		$delete_status = FALSE;
		if($this->check_delete_status()){
			$delete_status = TRUE;
			$check_table_query  = 'SELECT GROUP_CONCAT(prime_module_id) as prime_module_id,GROUP_CONCAT(label_name) as label_name from cw_form_setting WHERE pick_table = "'. $this->prime_table .'" and  trans_status = 1 ';
			$check_table_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_table_query')");
			$check_table_rlst   = $check_table_info->row();
			$check_table_info->next_result();
			if($check_table_rlst->prime_module_id){
				$prime_module_id         = explode(",",$check_table_rlst->prime_module_id);
				$label_name              = explode(",",$check_table_rlst->label_name);
				$i                       = 0;
				$select_table            = '';
				$select_label            = '';
				$select_trans_status     = '';
				$select_where            = '';
				foreach($prime_module_id as $check_modules){
					$table_name            = "cw_".$check_modules;
					$table_rename          = $table_name."_$i";
					$select_table         .= "$table_rename.$label_name[$i],";
					$select_label         .= " $table_name $table_rename,";
					if((int)$i === 0){
						$select_trans_status  .= "( $table_rename.trans_status = 1";
						$select_where         .= " and ($table_rename.$label_name[$i] in ($delete_ids)";
					}else{
						$select_trans_status  .= " and $table_rename.trans_status = 1";
						$select_where         .= " or $table_rename.$label_name[$i] in ($delete_ids)";
					}
					$i++;
				}
				$select_trans_status .= ")";
				$select_where        .= ")";
				$select_table         = rtrim($select_table,',');
				$select_label         = rtrim($select_label,',');
				$check_module_query  .= 'SELECT '.$select_table.' from '.$select_label.' WHERE '.$select_trans_status.' '.$select_where.' LIMIT 0,1'; 
				$check_module_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_module_query')");
				$values_count        = $check_module_info->num_rows();
				$check_module_info->next_result();
				if((int)$values_count > 0){
					$can_process   = False;
					$delete_status = False;
				}
			}
			if($delete_status){
				$delete_query  = 'DELETE FROM '. $this->prime_table .'  WHERE '. $this->prime_id .' in ('. $delete_ids .')';
				if($this->db->query("CALL sp_a_run ('RUN','$delete_query')")){
					$row_set_query   = 'SELECT form_view_label_name from cw_form_view_setting where form_view_type = "3" and prime_view_module_id = "'. $this->control_name .'" and trans_status = 1';
					$row_set_info    = $this->db->query("CALL sp_a_run ('SELECT','$row_set_query')");
					$row_count       = (int)$row_set_info->num_rows();
					$row_set_info->next_result();
					if($row_count !== 0){
						$row_set_result         = $row_set_info->result();
						$delete_table_name      = '';
						$delete_table_condition = '';
						foreach($row_set_result as $row_set){
							$row_set_table_name      = "cw_".$this->control_name."_".$row_set->form_view_label_name;
							$delete_table_name      .= "$row_set_table_name,";
							$delete_table_condition .= " $row_set_table_name.$this->prime_id  in ('$delete_ids') and";
						}
						$delete_table_name           = rtrim($delete_table_name,',');
						$delete_table_condition      = rtrim($delete_table_condition,'and');
						$delete_row_set_query  = 'DELETE FROM '. $delete_table_name .'  WHERE '. $delete_table_condition.'';
						$this->db->query("CALL sp_a_run ('RUN','$delete_row_set_query')");						
					}
					$can_process = False;
				}
				
			}
		}
		if($can_process){
			$created_on = date("Y-m-d h:i:s");
			$prime_upd_query    .= 'trans_deleted_by = "'. $this->logged_id .'",trans_deleted_date = "'.$created_on.'"';
			$prime_update_query  = 'UPDATE '. $this->prime_table .' SET trans_status = 0,'. $prime_upd_query .' WHERE '. $this->prime_id .' in ('. $delete_ids .')';
			if($this->db->query("CALL sp_a_run ('UPDATE','$prime_update_query')")){
				echo json_encode(array('success' => TRUE, 'message' => "Successfully Deleted"));
			}else{
				echo json_encode(array('success' => FALSE, 'message' => "Unable to delete"));
			}
		}else
		if($delete_status){
			echo json_encode(array('success' => TRUE, 'message' => "Successfully Deleted"));
		}else{
			$modules = ucwords($check_table_rlst->prime_module_id);
			echo json_encode(array('success' => FALSE, 'message' => "Unable to delete, This value is already used in $modules modules"));
		}
	}
	
	//CHECK UNIQUE FIELD STATUS
	public function check_delete_status(){
		$check_delete_query  = 'SELECT GROUP_CONCAT(unique_field) as unique_field from cw_form_setting WHERE prime_module_id = "'. $this->control_name .'" and  trans_status = 1 ';
		$check_delete_info   = $this->db->query("CALL sp_a_run ('SELECT','$check_delete_query')");
		$check_delete_rlst   = $check_delete_info->row();
		$check_delete_info->next_result();
		$unique_info         = explode(",",$check_delete_rlst->unique_field);
		if(in_array('1', $unique_info)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//UPDATE STATUS TO DELETE FOR UPLOAD FILES or DOCUMENTS
	public function remove_file(){
		$prime_id_val  = $this->input->post('prime_id_val');
		$is_defult     = (int)$this->input->post('is_defult');
		$input_name     = $this->input->post('input_name');
		$table_name = '';
		if($is_defult === 1){
			$table_name = $this->prime_table;
		}else
		if($is_defult === 2){
			$table_name = $this->cf_table;
		}
		if($table_name){
			$created_on    = date("Y-m-d h:i:s");
			$set_query     = $input_name .' = "" ,trans_updated_by = "'. $this->logged_id .'",trans_updated_date = "'.$created_on.'"';
			$update_query  = 'UPDATE '.$table_name .' SET '. $set_query .' WHERE '. $this->prime_id .' = "'. $prime_id_val .'"';
			$this->db->query("CALL sp_a_run ('UPDATE','$update_query')");
			echo json_encode(array('success' => TRUE, 'message' => "Successfully updated"));
		}else{
			echo json_encode(array('success' => FALSE, 'message' => "Unable to process your request"));
		}
	}
	
	//IMPORT FILE VIEW INFORMATION
	public function import(){
		$data['module_id']     = $this->control_name;		
		$excel_format_qry = 'select prime_excel_format_id,excel_name from cw_util_excel_format where excel_module_id = "'.$this->control_name.'" and trans_status = 1';
		$excel_format   = $this->db->query("CALL sp_a_run ('SELECT','$excel_format_qry')");
		$excel_result    = $excel_format->result();
		$excel_format->next_result();
		$excel_format_drop[""] = "---- Excel Format ----";
		foreach($excel_result as $excel){
			$prime_excel_format_id = $excel->prime_excel_format_id;
			$excel_name            = $excel->excel_name;
			$excel_format_drop[$prime_excel_format_id] = $excel_name;
		}
		$data['excel_format_drop'] = $excel_format_drop;
		
		$this->load->view("$this->control_name/import",$data);
	}
}
?>