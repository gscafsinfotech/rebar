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
		$from_query = 'select * from cw_form_setting where prime_module_id = "project_and_drawing_master" and search_show = "1" ORDER BY input_for,field_sort asc';
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
	public function excel_export($process_by,$from_date,$to_date){
		$control_name		= $this->control_name;
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));

		$team_qry  	= 'select * from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_team on cw_team.prime_team_id = cw_project_and_drawing_master.team group by prime_team_id order by prime_team_id ASC';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result();
		$team_info->next_result();


		$emp_result  = json_decode(json_encode($team_result),true);		
		$emp_result = array_reduce($emp_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());

		$detailing_qry	= 'select cw_project_and_drawing_master.prime_project_and_drawing_master_id,rdd_no,uspm,cw_client.client_name,project_name,cw_project_and_drawing_master.received_date,GROUP_CONCAT(drawing_no) AS drawing_no,prime_team_id,count(drawing_no) as count_drawing_no,GROUP_CONCAT(drawing_description) AS drawing_description,cw_project_and_drawing_master.trans_created_date,estimated_tons,cw_employees.emp_name,reporting_code,project,first_check_major from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_team on cw_team.prime_team_id = cw_project_and_drawing_master.team inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_time_sheet on cw_time_sheet.project = cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_employees on cw_employees.employee_code = cw_time_sheet_time_line.emp_code where work_type = 1 and cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" group by cw_project_and_drawing_master.prime_project_and_drawing_master_id order by prime_team_id ASC';

		$detailing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		// echo $detailing_qry;die;
		$detailing_result  	= $detailing_info->result_array();
		$detailing_info->next_result();
		
		 $detailing_result = array_reduce($detailing_result, function($result, $arr){			
		    $result[$arr['prime_team_id']][] = $arr;
		    return $result;
		}, array());
		 // echo "<pre>";
		 // print_r($detailing_result);die;

		 $detail_count_query  = 'select prime_team_id,count(qa_minor) as qa_minor,count(qa_major) as qa_major,count(second_check_major) as second_check_major,count(first_check_minor) as first_check_minor,count(first_check_major) as first_check_major,work_type,detailing_time,project from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_team on cw_team.prime_team_id = cw_project_and_drawing_master.team inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_time_sheet on cw_time_sheet.project = cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_employees on cw_employees.employee_code = cw_time_sheet_time_line.emp_code where cw_time_sheet.work_type=1 and cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1 and cw_project_and_drawing_master_drawings.trans_status = 1 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 GROUP BY cw_time_sheet.project order by prime_team_id ASC';
		 // echo $detail_count_query;die;
		$detail_count_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detail_count_query')");
		$detail_count_result  = $detail_count_info->result();
		$detail_count_info->next_result();
		$detailing_count =array();
		foreach ($detail_count_result as $key => $detail_count) {
			$project = $detail_count->project;
			$detailing_count['first_check_minor'][$project] = $detail_count->first_check_minor;
			$detailing_count['first_check_major'][$project] = $detail_count->first_check_major;
			$detailing_count['second_check_major'][$project]= $detail_count->second_check_major;
			$detailing_count['qa_major'][$project] 			= $detail_count->qa_major;
			$detailing_count['qa_minor'][$project] 			= $detail_count->qa_minor;
		}

		$team_pm_qry  = 'select prime_team_id,cw_branch.branch,cw_employees.team,cw_employees.emp_name,cw_project_and_drawing_master.prime_project_and_drawing_master_id ,cw_employees.role,cw_project_and_drawing_master.team from cw_project_and_drawing_master inner join cw_employees on FIND_IN_SET(cw_project_and_drawing_master.team,cw_employees.team) inner join cw_team on FIND_IN_SET(cw_team.prime_team_id,cw_project_and_drawing_master.team) inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch where cw_employees.role = 3 and cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1 group by cw_project_and_drawing_master.prime_project_and_drawing_master_id order by prime_team_id ASC';
		$team_pm_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_pm_qry')");
		$team_pm_result  = $team_pm_info->result();
		$team_pm_info->next_result();
// 		echo "<pre>";
// print_r($team_pm_result);die;

		$team_pm_result  = json_decode(json_encode($team_pm_result),true);		
		$team_pm_result = array_reduce($team_pm_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());
// 		echo "<pre>";
// print_r($team_pm_result);die;
		$team_pm_count =array();
		foreach ($team_pm_result as $key => $detail_count) {
			// print_r($detail_count);
			$project = $detail_count['prime_project_and_drawing_master_id'];
			$team_id_pm = $detail_count['prime_team_id'];
			// echo $project;
			$team_pm_count['branch'][$team_id_pm]   = $detail_count['branch'];
			$team_pm_count['emp_name'][$team_id_pm] = $detail_count['emp_name'];
			// echo $detail_count['barnch'];
		}

 // print_r($team_pm_count);die;


		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();	

		$styleArray = array(
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
	            'color' => array('rgb' => '#ffffff'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '46b10a')
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
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	


		//Set the first row as the header row
		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
		$excel_types[]['excel_value']= array('RDA#','US PM','Client','Name of Project','Recd On','Dwg. No','No .OfDwgs','Drawing Description','Sub Date','Tons','Detailer Name','Time','"Checker Name','Time','Total','1st Check Major','1st Check Minor','2nd Check','QA Major','QA Minor','PM','Branch');
		for ($x = 0; $x <= 21; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue($excel_column."1", $excel_value)->getStyle($excel_column.'1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setAutoFilter(
			    $obj->getActiveSheet()->calculateWorksheetDimension()
			);
		}

		$i=2;
		foreach ($emp_result as $key => $value) {
			$emp_name 		= $value['emp_name'];
			$team_name 		= $value['team_name'];
			$team_id =$value['prime_team_id'];
			$arr = $detailing_result[$team_id];
			$time_sheet_value['A']  = $team_name.' >>> '.$emp_name;
			$can_process = false;
			for ($x = 0; $x <= 1; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);
				$can_process = true;
			}
			// echo "<pre>";
			// print_r($arr);
			$counter=$i;
			if($can_process){
				foreach ($arr as $key => $details){
					$project = $details['project'];
					
				$team_test_id 	         = $details['prime_team_id'];
				// echo "team_test_id :: $team_test_id";
				$time_sheet_inside['A']  = $details['rdd_no'];
				$time_sheet_inside['B']  = $details['uspm'];
				$time_sheet_inside['C']  = $details['client_name'];
				$time_sheet_inside['D']  = $details['project_name'];
				$time_sheet_inside['E']  = $details['received_date'];
				$time_sheet_inside['F']  = $details['drawing_no'];
				$time_sheet_inside['G']  = $details['count_drawing_no'];
				$time_sheet_inside['H']  = $details['drawing_description'];
				$time_sheet_inside['I']  = date('d-m-Y',strtotime($details['trans_created_date']));
				$time_sheet_inside['J']  = $details['estimated_tons'];
				$time_sheet_inside['K']  = $details['emp_name'];
				$time_sheet_inside['L']  = "Time";
				$time_sheet_inside['M']  = "Checker Name";
				$time_sheet_inside['N']  = "Time";
				$time_sheet_inside['O']  = "Total";
				
				if(array_key_exists($project, $detailing_count['first_check_major'])){
					$time_sheet_value['P'] 	 	="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['P']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['P']  = "";
				}
				if(array_key_exists($project, $detailing_count['first_check_minor'])){
					$time_sheet_value['Q'] 	 	="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['Q']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['Q']  = "";
				}
				if(array_key_exists($project, $detailing_count['second_check_major'])){
					$time_sheet_value['R'] 	 	="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['R']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['R']  = "";
				}
				if(array_key_exists($project, $detailing_count['qa_major'])){
					$time_sheet_value['S'] 	 	="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['S']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['S']  = "";
				}
				if(array_key_exists($project, $detailing_count['qa_minor'])){
					$time_sheet_value['T'] 	 	="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['T']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['T']  = "";
				}
				// echo "<pre>";
				// print_r($team_pm_count);
				if(array_key_exists($project, $team_pm_count['emp_name'])){
					$time_sheet_value['U'] 	 	="";
					foreach ($team_pm_count as $key => $count_detail) {
						$projectwise_detail  	= $count_detail[$project];
						$time_sheet_inside['U']  = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_inside['U']  = "";
				}
				// echo "<pre>";
				// print_r($team_pm_count['branch']);
				if(array_key_exists($project, $team_pm_count['branch'])){
					$time_sheet_value['V'] 	 	="";
					foreach ($team_pm_count as $key => $count_detail) {
						// print_r($count_detail);
						$projectwise_branch  	= $team_pm_count['branch'][$project];
						$time_sheet_inside['V']  = $projectwise_branch;
					}
				}else{
					$projectwise_detail = 0;
					$projectwise_branch['V']  = "";
				}




				// $time_sheet_inside['U']  = "PM";
				// $time_sheet_inside['V']  = "Branch";
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
						
		$i++;	

	}




	
	$obj->createSheet();
	$obj->setActiveSheetIndex(1);
	$excel_types_revision[]['excel_column']= array('A','B','C','D','E','F','G','H');
		$excel_types_revision[]['excel_value']= array('CO Number','RDA####','Client','Name of Project','Drawing number','Date','Hours','Name of Detailer');
	for ($x = 0; $x <= 7; $x++) {
		$excel_column  = $excel_types_revision[0]['excel_column'][$x];
		$excel_value   = $excel_types_revision[1]['excel_value'][$x];
		$obj->getActiveSheet()->setCellValue($excel_column."1", $excel_value)->getStyle($excel_column.'1')->applyFromArray($styleArray);
	}

	$revision_qry	= 'select co_number,rdd_no,uspm,cw_client.client_name,project_name,cw_project_and_drawing_master.received_date,GROUP_CONCAT(drawing_no) AS drawing_no,prime_team_id,count(drawing_no) as count_drawing_no,GROUP_CONCAT(drawing_description) AS drawing_description,cw_project_and_drawing_master.trans_created_date,estimated_tons,cw_employees.emp_name,reporting_code,project,first_check_major from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_team on cw_team.prime_team_id = cw_project_and_drawing_master.team inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_time_sheet on cw_time_sheet.project = cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_employees on cw_employees.employee_code = cw_time_sheet_time_line.emp_code where work_type = 2 and cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" group by cw_project_and_drawing_master.prime_project_and_drawing_master_id order by prime_team_id ASC';

		$revision_info   	= $this->db->query("CALL sp_a_run ('SELECT','$revision_qry')");
		// echo $revision_qry;die;
		$revision_result  	= $revision_info->result_array();
		$revision_info->next_result();

		$revision_result = array_reduce($revision_result, function($result, $arr){			
		    $result[$arr['prime_team_id']][] = $arr;
		    return $result;
		}, array());

		$j=2;
		foreach ($emp_result as $key => $value) {
			$emp_name 		= $value['emp_name'];
			$team_name 		= $value['team_name'];
			$team_id =$value['prime_team_id'];
			$arr_revision = $revision_result[$team_id];
			$time_sheet_value['A']  = $team_name.' >>> '.$emp_name;
			$can_process = false;
			for ($x = 0; $x <= 7; $x++) {
				$excel_column  		= $excel_types_revision[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet()->setCellValue($excel_column.$j, $value_of_excel)->getStyle($excel_column.$j)->applyFromArray($verticalStyle);
				// $autoFilter = $obj->getActiveSheet()->getAutoFilter('B1:D1');
				$obj->getActiveSheet()->setAutoFilter(
				    $obj->getActiveSheet()->calculateWorksheetDimension()
				);
				$can_process = true;
			}
			$counter=$j;

			if($can_process){
				foreach ($arr_revision as $key => $revision){
					$project = $revision['project'];
				$team_test_id 	         = $revision['prime_team_id'];
				$time_sheet_inside_rev['A']  = $revision['co_number'];
				$time_sheet_inside_rev['B']  = $revision['rdd_no'];
				$time_sheet_inside_rev['C']  = $revision['client_name'];
				$time_sheet_inside_rev['D']  = $revision['project_name'];
				$time_sheet_inside_rev['E']  = $revision['drawing_no'];
				$time_sheet_inside_rev['F']  = $revision['received_date'];
				$time_sheet_inside_rev['G']  = "Hour";
				$time_sheet_inside_rev['H']  = $revision['emp_name'];
				$counter++;					
				for ($y = 0; $y <= 7; $y++) {
					$excel_column  		= $excel_types_revision[0]['excel_column'][$y];					
					$value_of_excel  	= $time_sheet_inside_rev[$excel_column];
					$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($verticalStyle);
					// $columnFilter	= $obj->getActiveSheet()->getAutoFilter();
					// $columnFilter = $autoFilter->getColumn('C');
					// $obj->setActiveSheetIndex(0);
				}					
				$j++;	
			}
			}
						
		$j++;	

	}

	$obj->getActiveSheet()->setTitle('Revision');
		




// die;
		// Rename worksheet name
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
}
?>