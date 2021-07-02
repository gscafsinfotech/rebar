<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Submitted_log  extends Action_controller{	
	public function __construct(){
		parent::__construct('submitted_log');
		$this->collect_base_info();
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;
		$team_qry = 'select prime_team_id,team_name from cw_team where trans_status = 1';
		$team_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result = $team_info->result();
		$team_info->next_result();
		$team_list[""] = "---- Select ----";
		foreach ($team_result as $key => $team) {
			$prime_team_id = $team->prime_team_id;
			$team_name  = $team->team_name;
			$team_list[$prime_team_id] = $team_name;
		}
		$data['team_list'] = $team_list;
		$from_query = 'select * from cw_form_setting where  prime_module_id IN("project_and_drawing_master","tonnage_approval") and label_name in("client_name","project_name","project_manager","received_date","detailer_name","team_leader_name") ORDER BY input_for,field_sort asc';
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
					if($db_cond === "LIKE" || $db_cond === "="){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_project_and_drawing_master";
					 }
				}else
				if((int)$field_type === 4){
						$search_val = date('Y-m-d',strtotime($db_value));
						$search_val = $search_val;
						$table_qry = " and cw_project_and_drawing_master";
				}else{
					if($db_name === 'detailer_name' || $db_name === 'team_leader_name'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_tonnage_approval";
					}else{
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master";
					}
					
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}		
		}
		$process_month 				= $this->input->post("process_month");
		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));


		$team_qry  	= 'select GROUP_CONCAT(prime_team_id) as prime_team_id from cw_team where trans_status = 1';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result();
		$team_info->next_result();
		$team_wise_detailing_qry	= 'select count(*) as rlst_count from cw_tonnage_approval inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch where cw_tonnage_approval.trans_status =1 '.$fliter_query.' and cw_project_and_drawing_master.trans_status =1';
		$team_wise_detailing_info   			= $this->db->query("CALL sp_a_run ('SELECT','$team_wise_detailing_qry')");
		$team_wise_detailing_result = $team_wise_detailing_info->result();
		$team_wise_detailing_info->next_result();
		$rlst_count 				= $team_wise_detailing_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
	public function excel_export($process_month,$fliter_label,$fliter_type,$field_type,$filter_cond,$fliter_val,$multipick_val){
		$process_month 		= $process_month;
		$get_month 			= explode('-', $process_month);
		$month_year			= $get_month[1];
		$month_name			= $get_month[0];
		$month_name 		= date("F", mktime(null, null, null, $month_name, 1));
		$multi_val 			 = (int)$multipick_val-1;
		$filter_cond 		 = urldecode($filter_cond);
		$fliter_val 		 = explode(',', $fliter_val);
		$fliter_val_count 	 = count($fliter_val);
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
					if($db_cond === "LIKE" || $db_cond === "="){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_project_and_drawing_master";
					 }
				}else
				if((int)$field_type === 4){
						$search_val = date('Y-m-d',strtotime($db_value));
						$search_val = $search_val;
						$table_qry = " and cw_project_and_drawing_master";
				}else{
					if($db_name === 'detailer_name' || $db_name === 'team_leader_name'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_tonnage_approval";
					}else{
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master";
					}
					
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}				
		}
// echo "fliter_query ::$fliter_query";die;
		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();	

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
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $LeftBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    )
			  ),
	        'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $RightBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	        'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $HeaderRightBorder  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $HeaderLeftBorder  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $teamStyle  = array(
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
	    $LeftBorderFoot  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $RightBorderFoot  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $header_first  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );

		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
		$excel_types[]['excel_value']= array('RDA#','US PM','Client','Name of Project','Recd On','Dwg. No','No .OfDwgs','Drawing Description','Sub Date','Tons','Detailer Name','Time','"Checker Name','Time','Total','1st Check Major','1st Check Minor','2nd Check','QA Major','QA Minor','PM','Branch');
		for ($x = 0; $x <= 21; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "US DETAILING PROJECTS - NEW SUBMISSIONS DURING ".strtoupper($month_name)."-".$month_year)->mergeCells('A1:V1')->getStyle('A1:V1')->applyFromArray($header_first);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderLeftBorder);
			}else
			if($excel_column === 'V'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderRightBorder);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			}
			$obj->getActiveSheet()->calculateWorksheetDimension();
		}
		$detailing_qry = 'select count(*) as total_drawing_count,cw_time_sheet.employee_code,cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id,cw_time_sheet.entry_date,cw_project_and_drawing_master.rdd_no,cw_project_and_drawing_master.project_name,cw_uspm.uspm,cw_client.client_name,cw_project_and_drawing_master.received_date,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master_drawings.drawing_description,cw_tonnage_approval.trans_created_date,cw_tonnage_approval.actual_tonnage,cw_tonnage_approval.team as team_id,cw_tonnage_approval.project,cw_employees.emp_name as detailer_name,prime_team_id,team_name,cw_tonnage_approval.team_leader_name,cw_tonnage_approval.project_manager_name,cw_time_sheet_time_line.first_check_minor,cw_time_sheet_time_line.first_check_major,cw_time_sheet_time_line.second_check_major,cw_time_sheet_time_line.second_check_minor,cw_time_sheet_time_line.qa_major,cw_time_sheet_time_line.qa_minor,cw_branch.branch,detailing_time,study,discussion,rfi,checking,correction_time,other_works,bar_listing_time,revision_time,change_order_time,cw_time_sheet_time_line.billable_hours,cw_time_sheet_time_line.non_billable_hours,emails,was,co_checking,cw_time_sheet_time_line.actual_billable_time,qa_checking,monitoring,bar_listing_checking,aec,credit from cw_tonnage_approval inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where cw_tonnage_approval.work_type = 1 and cw_tonnage_approval.trans_status =1 and cw_project_and_drawing_master.trans_status =1 '.$fliter_query.' group by cw_tonnage_approval.drawing_no,entry_date order by entry_date';
		$detailing_info   			= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result 			= $detailing_info->result_array();
		$detailing_info->next_result();
		$detailing_result = array_reduce($detailing_result, function($result, $arr){			
		    $result[$arr['prime_team_id']][] = $arr;
		    return $result;
		}, array());


		$checker_name_qry = 'select cw_tonnage_approval.team,cw_employees.emp_name,prime_team_id,cw_employees.employee_code,cw_tonnage_approval.drawing_no from cw_tonnage_approval inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.team_leader_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.trans_status = 1 and cw_employees.trans_status = 1 and cw_tonnage_approval.approval_status = 2 and cw_employees.trans_status = 1';
	    $checker_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$checker_name_qry')");
		$checker_name_result  	= $checker_name_info->result_array();
		$checker_name_info->next_result();
		$checker_name_result = array_reduce($checker_name_result, function($result, $arr){			
		    $result[$arr['drawing_no']] = $arr;
		    return $result;
		}, array());
		/*echo "<pre>";
		print_r($checker_name_result);die;*/

		$pm_name_qry = 'select cw_tonnage_approval.team,cw_employees.emp_name,prime_team_id,cw_employees.employee_code from cw_tonnage_approval inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.project_manager_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.trans_status = 1 and cw_employees.trans_status = 1 and cw_tonnage_approval.approval_status = 2 and cw_employees.trans_status = 1';
	    $pm_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$pm_name_qry')");
		$pm_name_result  	= $pm_name_info->result();
		$pm_name_info->next_result();



		$checker_time_qry = 'select cw_project_and_drawing_master_drawings.drawing_no as drawing_name,employee_code,cw_time_sheet_time_line.drawing_no,discussion,correction_time,SEC_TO_TIME(SUM(TIME_TO_SEC(discussion))+SUM(TIME_TO_SEC(correction_time))+SUM(TIME_TO_SEC(detailing_time))+SUM(TIME_TO_SEC(study))+SUM(TIME_TO_SEC(rfi))+SUM(TIME_TO_SEC(checking))+SUM(TIME_TO_SEC(other_works))+SUM(TIME_TO_SEC(bar_listing_time))+SUM(TIME_TO_SEC(change_order_time))+SUM(TIME_TO_SEC(billable_hours))+SUM(TIME_TO_SEC(non_billable_hours))+SUM(TIME_TO_SEC(emails))+SUM(TIME_TO_SEC(was))+SUM(TIME_TO_SEC(co_checking))+SUM(TIME_TO_SEC(actual_billable_time))+SUM(TIME_TO_SEC(qa_checking))+SUM(TIME_TO_SEC(monitoring))+SUM(TIME_TO_SEC(bar_listing_checking))+SUM(TIME_TO_SEC(aec))+SUM(TIME_TO_SEC(credit))+SUM(TIME_TO_SEC(revision_time))) as checkers_time from `cw_time_sheet_time_line` inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_time_sheet_time_line.drawing_no WHERE cw_time_sheet_time_line.trans_status = 1 and cw_time_sheet.trans_status =1 and cw_project_and_drawing_master_drawings.trans_status =1 and cw_time_sheet_time_line.work_type = 1 and cw_time_sheet_time_line.drawing_no !="" and emp_role =4 GROUP by cw_time_sheet_time_line.drawing_no,employee_code';
		$checker_time_info   			= $this->db->query("CALL sp_a_run ('SELECT','$checker_time_qry')");
		$checker_time_result 			= $checker_time_info->result_array();
		$checker_time_info->next_result();
		$checker_time_result = array_reduce($checker_time_result, function($result, $arr){			
		    $result[$arr['employee_code']][$arr['drawing_no']] = $arr;
		    return $result;
		}, array());
		$detailer_time_qry = 'select cw_project_and_drawing_master_drawings.drawing_no as drawing_name,employee_code,cw_time_sheet_time_line.drawing_no,discussion,correction_time,SEC_TO_TIME(SUM(TIME_TO_SEC(discussion))+SUM(TIME_TO_SEC(correction_time))+SUM(TIME_TO_SEC(detailing_time))+SUM(TIME_TO_SEC(study))+SUM(TIME_TO_SEC(rfi))+SUM(TIME_TO_SEC(checking))+SUM(TIME_TO_SEC(other_works))+SUM(TIME_TO_SEC(bar_listing_time))+SUM(TIME_TO_SEC(change_order_time))+SUM(TIME_TO_SEC(billable_hours))+SUM(TIME_TO_SEC(non_billable_hours))+SUM(TIME_TO_SEC(emails))+SUM(TIME_TO_SEC(was))+SUM(TIME_TO_SEC(co_checking))+SUM(TIME_TO_SEC(actual_billable_time))+SUM(TIME_TO_SEC(qa_checking))+SUM(TIME_TO_SEC(monitoring))+SUM(TIME_TO_SEC(bar_listing_checking))+SUM(TIME_TO_SEC(aec))+SUM(TIME_TO_SEC(credit))+SUM(TIME_TO_SEC(revision_time))) as detailers_time from `cw_time_sheet_time_line` inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_time_sheet_time_line.drawing_no WHERE cw_time_sheet_time_line.trans_status = 1 and cw_time_sheet.trans_status =1 and cw_project_and_drawing_master_drawings.trans_status =1 and cw_time_sheet_time_line.work_type = 1 and cw_time_sheet_time_line.drawing_no !="" and emp_role =5 GROUP by cw_time_sheet_time_line.drawing_no,employee_code';
		$detailer_time_info   			= $this->db->query("CALL sp_a_run ('SELECT','$detailer_time_qry')");
		$detailer_time_rslt 			= $detailer_time_info->result_array();
		$detailer_time_info->next_result();

		$detailer_time_rslt = array_reduce($detailer_time_rslt, function($result, $arr){			
		    $result[$arr['employee_code']][$arr['drawing_no']] = $arr;
		    return $result;
		}, array());

		$team_qry  	= 'select prime_team_id,team_name from cw_team where trans_status = 1';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result_array();
		$team_info->next_result();
		$team_result = array_reduce($team_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());

		$team_all_qry  		= 'select GROUP_CONCAT(prime_team_id) as prime_team_id from cw_team where trans_status = 1';
		$team_all_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_all_qry')");
		$team_all_result  	= $team_all_info->result();
		$team_all_info->next_result();
		$process_team 		= $team_all_result[0]->prime_team_id;

		$team_emp_name_qry  	= 'select prime_team_id,team_name,GROUP_CONCAT(emp_name) as team_emp_name from cw_team inner join cw_employees on find_in_set(cw_team.prime_team_id,cw_employees.team) where cw_team.prime_team_id in('.$process_team.') and cw_employees.role = 5 and cw_team.trans_status = 1 group by prime_team_id';
		$team_emp_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_emp_name_qry')");
		$team_emp_name_result  	= $team_emp_name_info->result_array();
		$team_emp_name_info->next_result();

		$team_emp_name_result = array_reduce($team_emp_name_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());
		$i = 3;
		foreach ($detailing_result as $team_id => $value) {
			
			for ($x = 0; $x <= 1; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$team_emp_name  = $team_emp_name_result[$team_id]['team_emp_name'];
				foreach ($team_result as $team_name_id => $team_details) {
					if($team_id === $team_name_id){
						$team_id_with_name = $team_details['team_name'].">>>".$team_emp_name;
						$obj->getActiveSheet()->setCellValue($excel_column.$i, $team_id_with_name)->mergeCells($excel_column.$i.':'.'V'.$i)->getStyle($excel_column.$i.':'.'V'.$i)->applyFromArray($teamStyle);
					}
				}
			}
			$counter 	= $i;
			$team_total = "";
			$i++;
			$total_first_check_minor  = 0;
			$total_first_check_major  = 0;
			$total_second_check_major = 0;
			$total_qa_major			  = 0;
			$total_qa_minor			  = 0;
			$total_actual_tons 		  = 0;
			$no_of_draw				  = 0;
			foreach ($value as $team_data) {
				$employee_code = $team_data['employee_code'];
				$total_first_check_minor  += $team_data['first_check_minor'];
				$total_first_check_major  += $team_data['first_check_major'];
				$total_second_check_major += $team_data['second_check_major'];
				$total_qa_major  		  += $team_data['qa_major'];
				$total_qa_minor  		  += $team_data['qa_minor'];
				$total_actual_tons 		  += $team_data['actual_tonnage'];
				$no_of_draw				  += 1;
				$drawing_id 			   = $team_data['prime_project_and_drawing_master_drawings_id'];
				
				$detailer_time 			   = $detailer_time_rslt[$employee_code][$drawing_id]['detailers_time'];
				$cummulate_booking_hours   = array();
				$cummulate_booking_hours[] = $team_data['detailing_time'];
				$cummulate_booking_hours[] = $team_data['study'];
				$cummulate_booking_hours[] = $team_data['discussion'];
				$cummulate_booking_hours[] = $team_data['rfi'];
				$cummulate_booking_hours[] = $team_data['checking'];
				$cummulate_booking_hours[] = $team_data['correction_time'];
				$cummulate_booking_hours[] = $team_data['other_works'];
				$cummulate_booking_hours[] = $team_data['bar_listing_time'];
				$cummulate_booking_hours[] = $team_data['revision_time'];
				$cummulate_booking_hours[] = $team_data['change_order_time'];
				$cummulate_booking_hours[] = $team_data['billable_hours'];
				$cummulate_booking_hours[] = $team_data['non_billable_hours'];
				$cummulate_booking_hours[] = $team_data['emails'];
				$cummulate_booking_hours[] = $team_data['was'];
				$cummulate_booking_hours[] = $team_data['co_checking'];
				$cummulate_booking_hours[] = $team_data['actual_billable_time'];
				$cummulate_booking_hours[] = $team_data['qa_checking'];
				$cummulate_booking_hours[] = $team_data['monitoring'];
				$cummulate_booking_hours[] = $team_data['bar_listing_checking'];
				$cummulate_booking_hours[] = $team_data['aec'];
				$cummulate_booking_hours[] = $team_data['credit'];
				$cummulate_total_hours 	   = $this->AddPlayTime($cummulate_booking_hours);
				$team_leader_name 		   = $team_data['team_leader_name'];
				$project_manager_name 	   = $team_data['project_manager_name'];
				

				$time_sheet_inside['A']  = $team_data['rdd_no'];
				$time_sheet_inside['B']  = $team_data['uspm'];
				$time_sheet_inside['C']  = $team_data['client_name'];
				$time_sheet_inside['D']  = $team_data['project_name'];
				$time_sheet_inside['E']  = $team_data['received_date'];
				$time_sheet_inside['F']  = $team_data['drawing_no'];
				$time_sheet_inside['G']  = $team_data['total_drawing_count'];
				$time_sheet_inside['H']  = $team_data['drawing_description'];
				$time_sheet_inside['I']  = date('d-m-Y',strtotime($team_data['entry_date']));
				$time_sheet_inside['J']  = $team_data['actual_tonnage'];
				$time_sheet_inside['K']  = $team_data['detailer_name'];
				$time_sheet_inside['L']  = $detailer_time;
				$checker_name  			 = $checker_name_result[$drawing_id]['emp_name'];
				$checker_code  			 = $checker_name_result[$drawing_id]['employee_code'];
				$checker_time  			 = $checker_time_result[$checker_code][$drawing_id]['checkers_time'];
				$total_times 			 = array();
				$total_times[] 			 = $detailer_time;
				$total_times[] 			 = $checker_time;
				$total_for_time 	   	 = $this->AddPlayTime($total_times);

				$time_sheet_inside['M']  = $checker_name;
				$time_sheet_inside['N']  = $checker_time;
				$time_sheet_inside['O']  = $total_for_time;
				$time_sheet_inside['P']  = $team_data['first_check_major'];
				$time_sheet_inside['Q']  = $team_data['first_check_minor'];
				$time_sheet_inside['R']  = $team_data['second_check_major'];
				$time_sheet_inside['S']  = $team_data['qa_major'];
				$time_sheet_inside['T']  = $team_data['qa_minor'];
				foreach ($pm_name_result as $key => $pm_rlst) {
					$pm_id_team 		 = $pm_rlst->team;
					$pm_emp_code 		 = $pm_rlst->employee_code;
					if($project_manager_name === $pm_emp_code){
						$time_sheet_inside['U']  = $pm_rlst->emp_name;
					}
				}
				$time_sheet_inside['V']  = $team_data['branch'];

				$counter++;
				for ($y = 0; $y <= 21; $y++) {
					$excel_column  		= $excel_types[0]['excel_column'][$y];
					$value_of_excel  	= $time_sheet_inside[$excel_column];
					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'V'){
						$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($verticalStyle);
					}
				}
				$i++;
			}
			$team_total = $counter+1;
			for ($z = 0; $z <= 1; $z++) {
				$excel_column  		= $excel_types[0]['excel_column'][$z];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet()->setCellValue("A".$team_total, "")->mergeCells("A".$team_total.":F".$team_total)->getStyle("A".$team_total.":F".$team_total)->applyFromArray($LeftBorderFoot);
				$obj->getActiveSheet()->setCellValue("H".$team_total, "")->mergeCells("H".$team_total.":I".$team_total)->getStyle("H".$team_total.":I".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("K".$team_total, "")->mergeCells("K".$team_total.":O".$team_total)->getStyle("K".$team_total.":O".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("G".$team_total, $no_of_draw)->getStyle("G".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("J".$team_total, $total_actual_tons)->getStyle("J".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("P".$team_total, $total_first_check_major)->getStyle("P".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("Q".$team_total, $total_first_check_minor)->getStyle("Q".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("R".$team_total, $total_second_check_major)->getStyle("R".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("S".$team_total, $total_qa_major)->getStyle("S".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("T".$team_total, $total_qa_minor)->getStyle("T".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet()->setCellValue("U".$team_total, "")->mergeCells("U".$team_total.":V".$team_total)->getStyle("U".$team_total.":V".$team_total)->applyFromArray($RightBorderFoot);
			}
			$obj->setActiveSheetIndex(0);
							$obj->getActiveSheet()->setTitle('Detailing');
			$i++;
		}


// die;

		/* REVISION SHEET */


		$obj->createSheet();
		$obj->setActiveSheetIndex(1);
		$obj->getActiveSheet(1)->setTitle('Revision');

		$revision_types[]['excel_column']= array('A','B','C','D','E','F','G','H');
		$revision_types[]['excel_value']= array('CO Number','RDA####','Client','Name of Project','Drawing number','Date','Hours','Name of Detailer');
		for ($x = 0; $x <= 7; $x++) {
			$excel_column  = $revision_types[0]['excel_column'][$x];
			$excel_value   = $revision_types[1]['excel_value'][$x];
			$obj->getActiveSheet(1)->setCellValue('A'."1", "US DETAILING PROJECTS - REVISIONS DURING ".strtoupper($month_name)."-".$month_year)->mergeCells('A1:H1')->getStyle('A1:H1')->applyFromArray($header_first);
			if($excel_column === 'A'){
				$obj->getActiveSheet(1)->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderLeftBorder);
			}else
			if($excel_column === 'H'){
				$obj->getActiveSheet(1)->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderRightBorder);
			}
			else{
				$obj->getActiveSheet(1)->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			}
			
		}

		$revision_qry = 'select cw_time_sheet.entry_date,cw_co_register.co_number,cw_project_and_drawing_master.rdd_no,cw_project_and_drawing_master.project_name,cw_client.client_name,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master.received_date,cw_tonnage_approval.actual_billable_time,cw_employees.emp_name as detailer_name,prime_team_id,cw_tonnage_approval.team as team_id from cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_co_register on cw_co_register.prime_co_register_id = cw_time_sheet_time_line.co_number inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_client on cw_client.prime_client_id = cw_tonnage_approval.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.work_type = 2 and cw_tonnage_approval.approval_status = 2 and cw_time_sheet.trans_status = 1 and cw_tonnage_approval.trans_status = 1 '.$fliter_query.'';
		$revision_info   			= $this->db->query("CALL sp_a_run ('SELECT','$revision_qry')");
		$revision_result 			= $revision_info->result_array();
		$revision_info->next_result();
		$revision_result = array_reduce($revision_result, function($result, $arr){			
		    $result[$arr['prime_team_id']][] = $arr;
		    return $result;
		}, array());
		$m=3;
		foreach ($revision_result as $team_id => $revisionData) {
			for ($x = 0; $x <= 1; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$team_emp_name  = $team_emp_name_result[$team_id]['team_emp_name'];
				foreach ($team_result as $team_name_id => $team_details) {
					if($team_id === $team_name_id){
						$team_id_with_name = $team_details['team_name'].">>>".$team_emp_name;
						$obj->getActiveSheet(1)->setCellValue($excel_column.$m, $team_id_with_name)->mergeCells($excel_column.$m.':'.'H'.$m)->getStyle($excel_column.$m.':'.'H'.$m)->applyFromArray($teamStyle);
					}
				}
			}
			$counter_rev 	= $m;
			$m++;
			$total_billable_hrs 	 = array();
			foreach ($revisionData as $revData) {
				$total_billable_hrs[] 	 = $revData['actual_billable_time'];
				$total_hours_billable 	 = $this->AddPlayTime($total_billable_hrs);

				$time_sheet_inside['A']  = $revData['co_number'];
				$time_sheet_inside['B']  = $revData['rdd_no'];
				$time_sheet_inside['C']  = $revData['client_name'];
				$time_sheet_inside['D']  = $revData['project_name'];
				$time_sheet_inside['E']  = $revData['drawing_no'];
				$time_sheet_inside['F']  = $revData['entry_date'];
				$time_sheet_inside['G']  = $revData['actual_billable_time'];
				$time_sheet_inside['H']  = $revData['detailer_name'];

				$counter_rev++;
				for ($y = 0; $y <= 7; $y++) {
					$excel_column  		= $revision_types[0]['excel_column'][$y];					
					$value_of_excel  	= $time_sheet_inside[$excel_column];
					if($excel_column === 'A'){
						$obj->getActiveSheet(1)->setCellValue($excel_column.$counter_rev, $value_of_excel)->getStyle($excel_column.$counter_rev)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'H'){
						$obj->getActiveSheet(1)->setCellValue($excel_column.$counter_rev, $value_of_excel)->getStyle($excel_column.$counter_rev)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet(1)->setCellValue($excel_column.$counter_rev, $value_of_excel)->getStyle($excel_column.$counter_rev)->applyFromArray($verticalStyle);
					}
					$obj->setActiveSheetIndex(1);
				}	
				$m++;
			}
			$team_total = $counter_rev+1;
			for ($z = 0; $z <= 1; $z++) {
				$excel_column  		= $excel_types[0]['excel_column'][$z];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet(1)->setCellValue("A".$team_total, "")->mergeCells("A".$team_total.":E".$team_total)->getStyle("A".$team_total.":E".$team_total)->applyFromArray($LeftBorderFoot);
				$obj->getActiveSheet(1)->setCellValue("F".$team_total, "TOTAl")->getStyle("F".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet(1)->setCellValue("G".$team_total, $total_hours_billable)->getStyle("G".$team_total)->applyFromArray($FooterStyle);
				$obj->getActiveSheet(1)->setCellValue("H".$team_total, "")->getStyle("H".$team_total)->applyFromArray($RightBorderFoot);
			}
			$m++;
		}
		$obj->setActiveSheetIndex(0);

		
		$control_name		= $this->control_name;
		$filename = $control_name.".xls"; //save our workbook as this file name
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

	function AddPlayTime($times) {
	    $minutes = 0; //declare minutes either it gives Notice: Undefined variable
	    // loop throught all the times
	    foreach ($times as $time) {
	        list($hour, $minute) = explode(':', $time);
	        $minutes += $hour * 60;
	        $minutes += $minute;
	    }
	    $hours = floor($minutes / 60);
	    $minutes -= $hours * 60;
	    // returns the time already formatted
	    return sprintf('%02d:%02d', $hours, $minutes);
	}
	
	
}
?>