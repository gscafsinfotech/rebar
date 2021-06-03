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

		$from_query = 'select * from cw_form_setting where prime_module_id = "co_register" and field_show = "1" and label_name in("team","uspm","rdd_no","client_name") ORDER BY input_for,field_sort asc';
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
						
						$pick_query = "select $pick_list from $pick_table where trans_status = 1 $qry";
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						
						$array_list[""] = "---- $label_name ----";
						foreach($pick_result as $pick){
							$pick_key = $pick->$pick_list_val_1;
							$pick_val = $pick->$pick_list_val_2;
							$array_list[$pick_key] = $pick_val;
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
		$multipick_val = $this->input->post("multipick_val");
		$multi_val 			 = (int)$multipick_val-1;
		$filter_cond 		 = urldecode($filter_cond);
		$fliter_val 		 = $this->input->post("fliter_val");
		$fliter_val_count 	 = count($fliter_val);
		$first_value 		 = array_splice($fliter_val,0,$multipick_val);
		$second_value 		 = array_splice($fliter_val,0,$fliter_val_count);
		$first_value 		 = implode(',', $first_value);
		$first_value 		 = rtrim($first_value,',');
		$first_value 		 = array($first_value);
		$fliter_val 		 = array_merge($first_value,$second_value);
		$filter_count 		 = $this->input->post("filter_count");
		$fliter_label 		 = $this->input->post("fliter_label");
		$fliter_type 		 = $this->input->post("fliter_type");
		$filter_cond 		 = $this->input->post("filter_cond");
		$field_types 		 = $this->input->post("field_type");
		$filter_count        = count($fliter_label);
		$fliter_query        = "";
		$search_count        = 0;
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     	 = $fliter_label[$i];
			$table_name  	 = $fliter_type[$i];
			$db_cond     	 = $filter_cond[$i];
			$db_value    	 = $fliter_val[$i];
			$field_type  	 = $field_types[$i];
			if(($db_cond) && ($db_value)){
				if((int)$field_type === 7){
					$search_val    = $db_value;
					if($db_cond === "LIKE"){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_co_register";
					 }
				}else{
					$search_val = $db_value;
					if($db_cond === "LIKE"){ $search_val = "$db_value%";}
					$table_qry = " and cw_co_register";
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}		
		}

		$co_reg_qry = 'select count(*) as rlst_count from cw_co_register inner join cw_uspm on cw_uspm.prime_uspm_id = cw_co_register.uspm inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_co_register.rdd_no inner join cw_client on cw_client.prime_client_id = cw_co_register.client_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_co_register.team) where cw_co_register.trans_status = 1 '.$fliter_query.' group by cw_co_register.prime_co_register_id order by cw_co_register.prime_co_register_id';
		$co_reg_info   = $this->db->query("CALL sp_a_run ('SELECT','$co_reg_qry')");
		$co_reg_result = $co_reg_info->result();
		$co_reg_info->next_result();
		$rlst_count    = $co_reg_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
	public function excel_export($process_month,$fliter_label,$fliter_type,$field_type,$filter_cond,$fliter_val,$multipick_val){
		$control_name		= $this->control_name;
		$process_month 		= $process_month;
		$get_month 			= explode('-', $process_month);
		$month_name			= $get_month[0];
		$month_name 		= date("F", mktime(null, null, null, $month_name, 1));
		$multi_val 			 = (int)$multipick_val-1;
		$filter_cond 		 = urldecode($filter_cond);
		$fliter_val 		 = explode(',', $fliter_val);
		$fliter_val_count 	 = count($fliter_val);
		$first_value 		 = array_splice($fliter_val,0,$multipick_val);
		$second_value 		 = array_splice($fliter_val,0,$fliter_val_count);
		$first_value 		 = implode(',', $first_value);
		$first_value 		 = rtrim($first_value,',');
		$first_value 		 = array($first_value);
		$fliter_val 		 = array_merge($first_value,$second_value);
		$filter_cond 		 = explode(',', $filter_cond);
		$field_types 	 	 = explode(',', $field_type);
		$fliter_type 		 = explode(',', $fliter_type);
		$fliter_label 		 = explode(',', $fliter_label);
		$filter_count        = count($fliter_label);
		$fliter_query        = "";
		$search_count        = 0;
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     = $fliter_label[$i];
			$table_name  = $fliter_type[$i];
			$db_cond     = $filter_cond[$i];
			$db_value    = $fliter_val[$i];
			$field_type  = $field_types[$i];
			if(($db_cond) && ($db_value)){
				if((int)$field_type === 7){
					$search_val    = $db_value;
					if($db_cond === "LIKE"){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_co_register";
					 }
				}else{
					$search_val = $db_value;
					if($db_cond === "LIKE"){ $search_val = "$db_value%";}
					$table_qry = " and cw_co_register";
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}			
		}
		$co_reg_qry = 'select cw_co_register.prime_co_register_id,co_number,cw_co_register.team,cw_uspm.uspm,cw_project_and_drawing_master.rdd_no,cw_client.client_name,cw_project_and_drawing_master.project_name,GROUP_CONCAT(cw_project_and_drawing_master_drawings.drawing_no) as drawing_no,cw_co_register.drawing_description,cw_co_register.estimation_hours from cw_co_register inner join cw_uspm on cw_uspm.prime_uspm_id = cw_co_register.uspm inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_co_register.rdd_no inner join cw_client on cw_client.prime_client_id = cw_co_register.client_name left join cw_project_and_drawing_master_drawings on FIND_IN_SET(cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id,cw_co_register.drawing_no) inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_co_register.team) where cw_co_register.trans_status = 1 '.$fliter_query.' group by cw_co_register.prime_co_register_id order by cw_co_register.prime_co_register_id';
		$co_reg_info   = $this->db->query("CALL sp_a_run ('SELECT','$co_reg_qry')");
		$co_reg_result = $co_reg_info->result();
		$co_reg_info->next_result();

		$team_qry = 'select GROUP_CONCAT(cw_team.team_name) as team_name,cw_team.prime_team_id,cw_co_register.team from cw_co_register inner join cw_uspm on cw_uspm.prime_uspm_id = cw_co_register.uspm inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_co_register.rdd_no inner join cw_client on cw_client.prime_client_id = cw_co_register.client_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_co_register.team) where cw_co_register.trans_status = 1 '.$fliter_query.' group by cw_co_register.prime_co_register_id order by cw_co_register.prime_co_register_id';
		$team_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result = $team_info->result_array();
		$team_info->next_result();

		$team_result = array_reduce($team_result, function($result, $arr){			
		    $result[$arr['team']] = $arr;
		    return $result;
		}, array());

		$billable_hrs_qry = 'select cw_co_register.prime_co_register_id,cw_co_register.co_number,IF(SEC_TO_TIME( SUM(time_to_sec(cw_tonnage_approval.actual_billable_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(cw_tonnage_approval.actual_billable_time))),"%H:%i"),"") as actual_billable_time,cw_tonnage_approval.trans_updated_date,submitted_date from cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_co_register on cw_co_register.prime_co_register_id = cw_time_sheet_time_line.co_number where cw_tonnage_approval.approval_status = 2 and cw_tonnage_approval.work_type = 2 and cw_tonnage_approval.trans_status = 1 group by cw_time_sheet_time_line.co_number';
		$billable_hrs_info   	= $this->db->query("CALL sp_a_run ('SELECT','$billable_hrs_qry')");
		$billable_hrs_result 	= $billable_hrs_info->result_array();
		$billable_hrs_info->next_result();
		$billable_hrs_result = array_reduce($billable_hrs_result, function($result, $arr){			
		    $result[$arr['prime_co_register_id']] = $arr;
		    return $result;
		}, array());

		$revision_qry = 'select submitted_date,prime_co_register_id from cw_co_register inner join cw_time_sheet_time_line on cw_time_sheet_time_line.co_number = cw_co_register.prime_co_register_id where cw_co_register.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 order by cw_time_sheet_time_line.prime_time_sheet_time_line_id ASC';
		$revision_qry   	= $this->db->query("CALL sp_a_run ('SELECT','$revision_qry')");
		$revision_result 	= $revision_qry->result_array();
		$revision_qry->next_result();
		
		$revision_result = array_reduce($revision_result, function($result, $arr){			
		    $result[$arr['prime_co_register_id']] = $arr;
		    return $result;
		}, array());

		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();
		$LeftBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $LeftrightBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $RightBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $RightBordertwo  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $LeftArray  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $RightArray  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
		$TopBorder = array(
			'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			  ),
	        'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
		$styleArray = array(
			'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			  ),
	        'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $verticalStyle  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $FooterStyle  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	    	'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFFF00')
	        ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $FooterLeftStyle  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	    	'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFFF00')
	        ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $FooterLeftStyletwo  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  )
	    );
	    $FooterRightStyle  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	    	'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFFF00')
	        ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $FooterRightStyletwo  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	     $RightBorderHead  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	      $FooterRightStyle  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	    	'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFFF00')
	        ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $LeftrightBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '000'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '99CC00')
	        ),
	        'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );

	    $excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K');
		$excel[]['excel_value']= array('CO#','Team','US PM','RDD No','Client',' Project Name',' Dwg No','Description of Revisions','Revision Hours','Estimation hours','Submitted date');
		for ($x = 0; $x <= 10; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "CO Register For All Clients - 2021 - Status as on ".strtoupper($month_name))->mergeCells('A1:K1')->getStyle('A1:K1')->applyFromArray($TopBorder);
			
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($LeftArray);
			}else
			if($excel_column === 'K'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($RightArray);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			}
		}

		$control_name		= $this->control_name;
		$i = 3;
		foreach($co_reg_result as $key => $co_register_sheet){
			$team 							 = $co_register_sheet->team;
			$co_number_id 					 = $co_register_sheet->prime_co_register_id;
			$team_name 						 = $team_result[$team]['team_name'];
			$revision_actual_billable_hours  = $billable_hrs_result[$co_number_id]['actual_billable_time'];
			$revision_submitted_date 	     = $revision_result[$co_number_id]['submitted_date'];
			if($revision_submitted_date){
				$revision_submitted_date		 = date('d-m-Y',strtotime($revision_submitted_date));
			}
			$co_reg_sheet_value['A']         = $co_register_sheet->co_number;
			$co_reg_sheet_value['B']         = $team_name;
			$co_reg_sheet_value['C']         = $co_register_sheet->uspm;
			$co_reg_sheet_value['D']         = $co_register_sheet->rdd_no;
			$co_reg_sheet_value['E']         = $co_register_sheet->client_name;
			$co_reg_sheet_value['F'] 		 = $co_register_sheet->project_name;
			$co_reg_sheet_value['G'] 		 = $co_register_sheet->drawing_no;
			$co_reg_sheet_value['H'] 		 = $co_register_sheet->drawing_description;
			$co_reg_sheet_value['I'] 		 = $revision_actual_billable_hours;
			$co_reg_sheet_value['J']		 = $co_register_sheet->estimation_hours;
			$co_reg_sheet_value['K'] 		 = $revision_submitted_date;

			for ($x = 0; $x <= 10; $x++) {
				$excel_column  		= $excel[0]['excel_column'][$x];
				$value_of_excel  	= $co_reg_sheet_value[$excel_column];
				
				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'K'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($RightBorder);
				}
				else{
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);
				}
				$counter = $i;
			}
			$i++;
		}

	    $filename= $control_name.".xls"; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
			//if you want to save it as .XLSX Excel 2007 format
			$objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel5');
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
			echo json_encode(array('success' => TRUE, 'output' => $excelOutput));
	}
}
?>