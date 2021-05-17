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
		$process_team 				= $this->input->post("process_team");
		$process_month 				= $this->input->post("process_month");
		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));


		$team_qry  	= 'select GROUP_CONCAT(prime_team_id) as prime_team_id from cw_team where trans_status = 1';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result();
		$team_info->next_result();
		$process_team 	= $team_result[0]->prime_team_id;
		
		$team_wise_detailing_qry	= 'select count(*) as rlst_count from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_team on find_in_set(cw_team.prime_team_id,cw_time_sheet_time_line.team) inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_time_sheet_time_line.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_time_sheet.entry_date like "%'.$process_month.'%" and cw_team.prime_team_id in('.$process_team.') and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1';
		$team_wise_detailing_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_wise_detailing_qry')");
		$team_wise_detailing_result = $team_wise_detailing_info->result();
		$team_wise_detailing_info->next_result();
		$rlst_count 				= $team_wise_detailing_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
	public function excel_export($process_month){
		// $process_month  			= '01-'.$process_month;
		// $process_month  			= date('Y-m',strtotime($process_month));

		$all_team_qry  	= 'select GROUP_CONCAT(prime_team_id) as prime_team_id from cw_team where trans_status = 1';
		$all_team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$all_team_qry')");
		$all_team_result  	= $all_team_info->result();
		$all_team_info->next_result();
		$process_team 	= $all_team_result[0]->prime_team_id;

		$detailing_qry = 'select cw_project_and_drawing_master.rdd_no,cw_project_and_drawing_master.project_name,cw_uspm.uspm,cw_client.client_name,cw_project_and_drawing_master.received_date,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master_drawings.drawing_description,cw_tonnage_approval.trans_created_date,cw_tonnage_approval.actual_tonnage,cw_tonnage_approval.team as team_id,cw_tonnage_approval.project,cw_employees.emp_name as detailer_name,prime_team_id,cw_tonnage_approval.team_leader_name,cw_tonnage_approval.project_manager_name,cw_time_sheet_time_line.first_check_minor,cw_time_sheet_time_line.first_check_major,cw_time_sheet_time_line.second_check_major,cw_time_sheet_time_line.second_check_minor,cw_time_sheet_time_line.qa_major,cw_time_sheet_time_line.qa_minor,cw_branch.branch,detailing_time,study,discussion,rfi,checking,correction_time,other_works,bar_listing_time,revision_time,change_order_time,cw_time_sheet_time_line.billable_hours,cw_time_sheet_time_line.non_billable_hours,emails,was,co_checking,cw_time_sheet_time_line.actual_billable_time,qa_checking,monitoring,bar_listing_checking,aec,credit from cw_tonnage_approval inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch where cw_tonnage_approval.work_type = 1 and cw_tonnage_approval.trans_status =1 and cw_project_and_drawing_master.trans_status =1';
		$detailing_info   			= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result 			= $detailing_info->result();
		$detailing_info->next_result();
		$team_qry  	= 'select prime_team_id,team_name from cw_team where cw_team.prime_team_id in('.$process_team.') and trans_status = 1';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result();
		$team_info->next_result();

		$team_emp_name_qry  	= 'select prime_team_id,team_name,GROUP_CONCAT(emp_name) as team_emp_name from cw_team inner join cw_employees on find_in_set(cw_team.prime_team_id,cw_employees.team) where cw_team.prime_team_id in('.$process_team.') and cw_employees.role = 5 and cw_team.trans_status = 1 group by prime_team_id';
		$team_emp_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_emp_name_qry')");
		$team_emp_name_result  	= $team_emp_name_info->result();
		$team_emp_name_info->next_result();
		// echo "<pre>";
		// print_r($team_emp_name_result);die;

		$checker_time_qry = 'select cw_project_and_drawing_master.rdd_no,cw_project_and_drawing_master.project_name,cw_uspm.uspm,cw_client.client_name,cw_project_and_drawing_master.received_date,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master_drawings.drawing_description,cw_tonnage_approval.trans_created_date,cw_tonnage_approval.actual_tonnage,cw_tonnage_approval.team as team_id,cw_tonnage_approval.project,cw_employees.emp_name as team_leader_name,prime_team_id,cw_tonnage_approval.detailer_name,cw_tonnage_approval.project_manager_name,cw_time_sheet_time_line.first_check_minor,cw_time_sheet_time_line.first_check_major,cw_time_sheet_time_line.second_check_major,cw_time_sheet_time_line.second_check_minor,cw_time_sheet_time_line.qa_major,cw_time_sheet_time_line.qa_minor,cw_branch.branch,detailing_time,study,discussion,rfi,checking,correction_time,other_works,bar_listing_time,revision_time,change_order_time,cw_time_sheet_time_line.billable_hours,cw_time_sheet_time_line.non_billable_hours,emails,was,co_checking,cw_time_sheet_time_line.actual_billable_time,qa_checking,monitoring,bar_listing_checking,aec,credit from cw_tonnage_approval inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.team_leader_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch where cw_tonnage_approval.work_type = 1 and cw_tonnage_approval.approval_status  and cw_tonnage_approval.trans_status =1 and cw_project_and_drawing_master.trans_status =1';
		$checker_time_info   			= $this->db->query("CALL sp_a_run ('SELECT','$checker_time_qry')");
		$checker_time_result 			= $checker_time_info->result();
		$checker_time_info->next_result();

		$employee_team_qry  	= 'select GROUP_CONCAT(emp_name),team_name,prime_team_id from cw_team inner join cw_employees on cw_employees.team in('.$process_team.')  where  cw_employees.role = 5 and cw_employees.employee_status =1 and cw_team.trans_status = 1 and cw_employees.trans_status = 1 group by prime_team_id order by prime_team_id ASC';
		$employee_team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$employee_team_qry')");
		$employee_team_result  	= $employee_team_info->result();
		$employee_team_info->next_result();

		$team_result  = json_decode(json_encode($team_result),true);		
		$team_result = array_reduce($team_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());
		$team_emp_name_result  = json_decode(json_encode($team_emp_name_result),true);		
		$team_emp_name_result = array_reduce($team_emp_name_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());

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


	    $checker_name_qry = 'select cw_tonnage_approval.team,cw_employees.emp_name,prime_team_id,cw_employees.employee_code from cw_tonnage_approval inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.team_leader_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.trans_status = 1 and cw_employees.trans_status = 1 and cw_tonnage_approval.approval_status = 2 and cw_employees.trans_status = 1';
	    $checker_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$checker_name_qry')");
		$checker_name_result  	= $checker_name_info->result();
		$checker_name_info->next_result();

		$pm_name_qry = 'select cw_tonnage_approval.team,cw_employees.emp_name,prime_team_id,cw_employees.employee_code from cw_tonnage_approval inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.project_manager_name inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.trans_status = 1 and cw_employees.trans_status = 1 and cw_tonnage_approval.approval_status = 2 and cw_employees.trans_status = 1';
	    $pm_name_info   	= $this->db->query("CALL sp_a_run ('SELECT','$pm_name_qry')");
		$pm_name_result  	= $pm_name_info->result();
		$pm_name_info->next_result();


		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
		$excel_types[]['excel_value']= array('RDA#','US PM','Client','Name of Project','Recd On','Dwg. No','No .OfDwgs','Drawing Description','Sub Date','Tons','Detailer Name','Time','"Checker Name','Time','Total','1st Check Major','1st Check Minor','2nd Check','QA Major','QA Minor','PM','Branch');
		for ($x = 0; $x <= 21; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "US DETAILING PROJECTS - NEW SUBMISSIONS DURING DEC 2020")->mergeCells('A1:V1')->getStyle('A1:V1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->calculateWorksheetDimension();
		}

		$i=3;
		foreach ($team_result as $key => $value) {
			$team_name 		= $value['team_name'];
			$prime_team_id  = $value['prime_team_id'];
			$team_emp_name  = $value['team_emp_name'];
			$team_emp_name  = $team_emp_name_result[$prime_team_id]['team_emp_name'];
					$time_sheet_value['A']  = $team_name.' >>> '.$team_emp_name;
			$can_process = false;
			for ($x = 0; $x <= 1; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($excel_column.$i.':'.'V'.$i)->getStyle($excel_column.$i.':'.'V'.$i)->applyFromArray($styleArray);
				$can_process = true;
			}
			$counter=$i;
			if($can_process){
				foreach ($detailing_result as $key => $details){
					$cummulate_booking_hours =array();
					$cummulate_booking_hours[] = $details->detailing_time;
					$cummulate_booking_hours[] = $details->study;
					$cummulate_booking_hours[] = $details->discussion;
					$cummulate_booking_hours[] = $details->rfi;
					$cummulate_booking_hours[] = $details->checking;
					$cummulate_booking_hours[] = $details->correction_time;
					$cummulate_booking_hours[] = $details->other_works;
					$cummulate_booking_hours[] = $details->bar_listing_time;
					$cummulate_booking_hours[] = $details->revision_time;
					$cummulate_booking_hours[] = $details->change_order_time;
					$cummulate_booking_hours[] = $details->billable_hours;
					$cummulate_booking_hours[] = $details->non_billable_hours;
					$cummulate_booking_hours[] = $details->emails;
					$cummulate_booking_hours[] = $details->was;
					$cummulate_booking_hours[] = $details->co_checking;
					$cummulate_booking_hours[] = $details->actual_billable_time;
					$cummulate_booking_hours[] = $details->qa_checking;
					$cummulate_booking_hours[] = $details->monitoring;
					$cummulate_booking_hours[] = $details->bar_listing_checking;
					$cummulate_booking_hours[] = $details->aec;
					$cummulate_booking_hours[] = $details->credit;
					$cummulate_booking_hours[] = $details->first_check_minor;
					$cummulate_booking_hours[] = $details->first_check_major;
					$cummulate_booking_hours[] = $details->second_check_major;
					$cummulate_booking_hours[] = $details->second_check_minor;
					$cummulate_booking_hours[] = $details->qa_major;
					$cummulate_booking_hours[] = $details->qa_minor;
					$cummulate_total_hours 	   = $this->AddPlayTime($cummulate_booking_hours);

					$team_id = $details->team_id;
					$team_leader_name = $details->team_leader_name;
					$project_manager_name = $details->project_manager_name;

					if($prime_team_id === $team_id){
						$time_sheet_inside['A']  = $details->rdd_no;
						$time_sheet_inside['B']  = $details->uspm;
						$time_sheet_inside['C']  = $details->client_name;
						$time_sheet_inside['D']  = $details->project_name;
						$time_sheet_inside['E']  = $details->received_date;
						$time_sheet_inside['F']  = $details->drawing_no;
						$time_sheet_inside['G']  = "1";
						$time_sheet_inside['H']  = $details->drawing_description;
						$time_sheet_inside['I']  = date('d-m-Y',strtotime($details->trans_created_date));
						$time_sheet_inside['J']  = $details->actual_tonnage;
						$time_sheet_inside['K']  = $details->detailer_name;
						$time_sheet_inside['L']  = $cummulate_total_hours;
						foreach ($checker_name_result as $key => $checker_rlst) {
							$checker_id_team = $checker_rlst->team;
							$emps_code = $checker_rlst->employee_code;


							if($team_leader_name === $emps_code){
								$time_sheet_inside['M']  = $checker_rlst->emp_name;
							}
						}

						foreach ($checker_time_result as $key => $chk_rlst) {
							$checker_booking_hours =array();
							$checker_booking_hours[] = $chk_rlst->detailing_time;
							$checker_booking_hours[] = $chk_rlst->study;
							$checker_booking_hours[] = $chk_rlst->discussion;
							$checker_booking_hours[] = $chk_rlst->rfi;
							$checker_booking_hours[] = $chk_rlst->checking;
							$checker_booking_hours[] = $chk_rlst->correction_time;
							$checker_booking_hours[] = $chk_rlst->other_works;
							$checker_booking_hours[] = $chk_rlst->bar_listing_time;
							$checker_booking_hours[] = $chk_rlst->revision_time;
							$checker_booking_hours[] = $chk_rlst->change_order_time;
							$checker_booking_hours[] = $chk_rlst->billable_hours;
							$checker_booking_hours[] = $chk_rlst->non_billable_hours;
							$checker_booking_hours[] = $chk_rlst->emails;
							$checker_booking_hours[] = $chk_rlst->was;
							$checker_booking_hours[] = $chk_rlst->co_checking;
							$checker_booking_hours[] = $chk_rlst->actual_billable_time;
							$checker_booking_hours[] = $chk_rlst->qa_checking;
							$checker_booking_hours[] = $chk_rlst->monitoring;
							$checker_booking_hours[] = $chk_rlst->bar_listing_checking;
							$checker_booking_hours[] = $chk_rlst->aec;
							$checker_booking_hours[] = $chk_rlst->credit;
							$checker_booking_hours[] = $chk_rlst->first_check_minor;
							$checker_booking_hours[] = $chk_rlst->first_check_major;
							$checker_booking_hours[] = $chk_rlst->second_check_major;
							$checker_booking_hours[] = $chk_rlst->second_check_minor;
							$checker_booking_hours[] = $chk_rlst->qa_major;
							$checker_booking_hours[] = $chk_rlst->qa_minor;
							$checker_bookhours 	   	 = $this->AddPlayTime($checker_booking_hours);
							$time_sheet_inside['N']  = $checker_bookhours;

							$total_times 			 = array();
							$total_times[] 			 = $cummulate_total_hours;
							$total_times[] 			 = $checker_bookhours;
							$total_times 	   	 	 = $this->AddPlayTime($total_times);
							$time_sheet_inside['O']  = $total_times;
						}
						
						$time_sheet_inside['P']  = $details->first_check_major;
						$time_sheet_inside['Q']  = $details->first_check_minor;
						$time_sheet_inside['R']  = $details->second_check_major;
						$time_sheet_inside['S']  = $details->qa_major;
						$time_sheet_inside['T']  = $details->qa_minor;
						foreach ($pm_name_result as $key => $pm_rlst) {
							$pm_id_team = $pm_rlst->team;
							$pm_emp_code = $pm_rlst->employee_code;
							if($project_manager_name === $pm_emp_code){
								$time_sheet_inside['U']  = $pm_rlst->emp_name;
							}
						}
						$time_sheet_inside['V']  = $details->branch;
						$counter++;	
						for ($y = 0; $y <= 21; $y++) {
							$excel_column  		= $excel_types[0]['excel_column'][$y];					
							$value_of_excel  	= $time_sheet_inside[$excel_column];
							
							$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($verticalStyle);
							$obj->setActiveSheetIndex(0);
							$obj->getActiveSheet()->setTitle('Detailing');
						}					
						$i++;	
				
					}
				}
			}		
		$i++;	
	}

	//Revision Sheet

	$obj->createSheet();
	$obj->setActiveSheetIndex(1);
	$obj->getActiveSheet(1)->setTitle('Revision');


	$revision_types[]['excel_column']= array('A','B','C','D','E','F','G','H');
	$revision_types[]['excel_value']= array('CO Number','RDA####','Client','Name of Project','Drawing number','Date','Hours','Name of Detailer');
	for ($x = 0; $x <= 7; $x++) {
		$excel_column  = $revision_types[0]['excel_column'][$x];
		$excel_value   = $revision_types[1]['excel_value'][$x];
		$obj->getActiveSheet(1)->setCellValue('A'."1", "US DETAILING PROJECTS - REVISIONS DURING DEC 2020")->mergeCells('A1:H1')->getStyle('A1:H1')->applyFromArray($styleArray);
		$obj->getActiveSheet(1)->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
	}

	$revision_qry = 'select cw_co_register.co_number,cw_project_and_drawing_master.rdd_no,cw_project_and_drawing_master.project_name,cw_client.client_name,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master.received_date,cw_tonnage_approval.actual_billable_time,cw_employees.emp_name as detailer_name,prime_team_id,cw_tonnage_approval.team as team_id from cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_co_register on cw_co_register.prime_co_register_id = cw_time_sheet_time_line.co_number inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_client on cw_client.prime_client_id = cw_tonnage_approval.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) where cw_tonnage_approval.work_type = 2 and cw_tonnage_approval.approval_status = 2 and cw_tonnage_approval.trans_status = 1';
	$revision_info   			= $this->db->query("CALL sp_a_run ('SELECT','$revision_qry')");
	$revision_result 			= $revision_info->result();
	$revision_info->next_result();
	// echo "<pre>";
	// print_r($revision_result);die;



	$m=3;
		foreach ($team_result as $key => $value) {
			$team_name 		= $value['team_name'];
			$prime_team_id  = $value['prime_team_id'];
			$team_emp_name  = $team_emp_name_result[$prime_team_id]['team_emp_name'];
			$time_sheet_value['A']  = $team_name.' >>> '.$team_emp_name;
			$can_process = false;
			for ($x = 0; $x <= 7; $x++) {
				$excel_column  		= $revision_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet(1)->setCellValue($excel_column.$m, $value_of_excel)->mergeCells($excel_column.$m.':'.'H'.$m)->getStyle($excel_column.$m.':'.'H'.$m)->applyFromArray($styleArray);
				$can_process = true;
			}
			$counter_rev=$m;
			if($can_process){
				foreach ($revision_result as $key => $details){
					$rev_team_id = $details->team_id;

					if($prime_team_id === $rev_team_id){
						$time_sheet_inside['A']  = $details->co_number;
						$time_sheet_inside['B']  = $details->rdd_no;
						$time_sheet_inside['C']  = $details->client_name;
						$time_sheet_inside['D']  = $details->project_name;
						$time_sheet_inside['E']  = $details->drawing_no;
						$time_sheet_inside['F']  = $details->received_date;
						$time_sheet_inside['G']  = $details->actual_billable_time;
						$time_sheet_inside['H']  = $details->detailer_name;
						$counter_rev++;	
						for ($y = 0; $y <= 7; $y++) {
							$excel_column  		= $revision_types[0]['excel_column'][$y];					
							$value_of_excel  	= $time_sheet_inside[$excel_column];
							// echo "$counter_rev :: $value_of_excel<br>";
							
							$obj->getActiveSheet(1)->setCellValue($excel_column.$counter_rev, $value_of_excel)->getStyle($excel_column.$counter_rev)->applyFromArray($verticalStyle);
							$obj->setActiveSheetIndex(1);
						}					
						$m++;	
				
					}
				}
			}		
		$m++;	
	}
$obj->setActiveSheetIndex(0);






// die;






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