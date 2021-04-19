<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Checker_report  extends Action_controller{	
	public function __construct(){
		parent::__construct('checker_report');
		$this->collect_base_info();
		
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;

		$logged_role 		   = $this->session->userdata('logged_role');
		$logged_emp_code 	   = $this->session->userdata('logged_emp_code');
		if((int)$logged_role === 4){
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 4 and employee_code = "'.$logged_emp_code.'" and employee_status = 1 and trans_status = 1';
		}else{
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 4 and employee_status = 1 and trans_status = 1';
		}
		$emp_info   		= $this->db->query("CALL sp_a_run ('SELECT','$emp_qry')");
		$emp_result 		= $emp_info->result();
		$emp_info->next_result();
		$employee_code_list[""] = "---- Select ----";
		foreach($emp_result as $emp_rlst){
			$employee_code  = $emp_rlst->employee_code;
			$emp_name       = $emp_rlst->emp_name;
			$employee_code_list[$employee_code] = $employee_code." - ".$emp_name;
		}
		$data['employee_code_list']  = $employee_code_list;


		$this->load->view("$this->control_name/manage",$data);
	}
	public function excel_export($employee_code,$process_month){
		$emp_team_qry 			= 'select team from cw_employees where employee_code = "'.$employee_code.'" and trans_status = 1';
		$emp_team_info   		= $this->db->query("CALL sp_a_run ('SELECT','$emp_team_qry')");
		$emp_team_result 		= $emp_team_info->result();
		$emp_team_info->next_result();
		$team_id 				= $emp_team_result[0]->team;
		$team_qry 			= 'select team_name from cw_team where FIND_IN_SET(prime_team_id,"'.$team_id.'") and trans_status = 1';
		$team_info   		= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result 		= $team_info->result();
		$team_info->next_result();
		$team_name 			= "";
		foreach ($team_result as $key => $teams) {
			$team_name 	   .= $teams->team_name.",";
		}
		$team_name 			= rtrim($team_name,',');

		$control_name		= $this->control_name;
		$process_month 		= $process_month;
		$get_month 			= explode('-', $process_month);
		$month_name			= $get_month[0];
		$month_name 		= date("F", mktime(null, null, null, $month_name, 1));

		$target_qry     = 'select SUM(cw_team_target_detailer_wise_target.target_value) as target_value from cw_team_target inner join cw_team_target_detailer_wise_target on cw_team_target_detailer_wise_target.prime_team_target_id = cw_team_target.prime_team_target_id inner join cw_employees on cw_employees.employee_code = cw_team_target_detailer_wise_target.detailer_name where cw_team_target.from_date <= "'.$process_month.'" and cw_team_target.to_date >= "'.$process_month.'" and role = 5 and reporting = "'.$employee_code.'" and cw_team_target.trans_status = 1';
		$target_info    = $this->db->query("CALL sp_a_run ('SELECT','$target_qry')");
		$target_result  = $target_info->result();
		$target_info->next_result();
		$credit_target  = $target_result[0]->target_value;
		$checker_qry 		= 'select emp_name,cw_time_sheet.employee_code,entry_date,in_time,out_time,total_time,IF(study>"00:00:00",TIME_FORMAT(study, "%H:%i"),"") as study,IF(checking>"00:00:00", TIME_FORMAT(checking, "%H:%i"), "") as checking,IF(discussion>"00:00:00", TIME_FORMAT(discussion, "%H:%i"), "") as discussion,IF(was>"00:00:00", TIME_FORMAT(was, "%H:%i"), "") as was,IF(correction_time>"00:00:00", TIME_FORMAT(correction_time, "%H:%i"), "") as correction_time,IF(rfi>"00:00:00", TIME_FORMAT(rfi, "%H:%i"), "") as rfi,IF(aec>"00:00:00", TIME_FORMAT(aec, "%H:%i"), "") as aec,IF(billable_hours>"00:00:00", TIME_FORMAT(billable_hours, "%H:%i"), "") as billable_hours,IF(co_checking>"00:00:00", TIME_FORMAT(co_checking, "%H:%i"), "") as co_checking,IF(other_works>"00:00:00", TIME_FORMAT(other_works, "%H:%i"), "") as other_works,IF(bar_listing_time>"00:00:00", TIME_FORMAT(bar_listing_time, "%H:%i"), "") as bar_listing_time,IF(emails>"00:00:00", TIME_FORMAT(emails, "%H:%i"), "") as emails,cw_work_type.work_type,entry_type,client_name,project,drawing_no,work_status,cw_time_sheet_time_line.work_type as work_type_time from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
		$checker_info   	= $this->db->query("CALL sp_a_run ('SELECT','$checker_qry')");
		$checker_result 	= $checker_info->result();
		$checker_info->next_result();
		$emp_name = $checker_result[0]->emp_name;
		$project_qry 	= 'select prime_project_and_drawing_master_id,project_name from cw_project_and_drawing_master where trans_status = 1';
		$project_info   	= $this->db->query("CALL sp_a_run ('SELECT','$project_qry')");
		$project_result 	= $project_info->result_array();
		$project_info->next_result();
		$project_result = array_reduce($project_result, function($result, $arr){			
		    $result[$arr['prime_project_and_drawing_master_id']] = $arr;
		    return $result;
		}, array());

		$drawing_qry 	= 'select prime_project_and_drawing_master_drawings_id,drawing_no from cw_project_and_drawing_master_drawings where trans_status = 1';
		$drawing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$drawing_qry')");
		$drawing_result 	= $drawing_info->result_array();
		$drawing_info->next_result();
		$drawing_result = array_reduce($drawing_result, function($result, $arr){			
		    $result[$arr['prime_project_and_drawing_master_drawings_id']] = $arr;
		    return $result;
		}, array());

		$work_status_qry 	= 'select prime_work_status_id,work_status from cw_work_status where trans_status = 1';
		$work_status_info   	= $this->db->query("CALL sp_a_run ('SELECT','$work_status_qry')");
		$work_status_result 	= $work_status_info->result_array();
		$work_status_info->next_result();
		$work_status_result = array_reduce($work_status_result, function($result, $arr){			
		    $result[$arr['prime_work_status_id']] = $arr;
		    return $result;
		}, array());

		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();
		$excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA');
		$excel[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Emails','STY','CHK','DIS','WAS','COR','RFI','STY','CHK','AEC','COR','WAS','BH','DIS','CO CHK','CHK','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');
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


	    /* FIRST WORK SHEET */

	    for ($x = 0; $x <= 26; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR ".strtoupper($month_name))->mergeCells('A1:AA1')->getStyle('A1:AA1')->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'."2", "Checker Name:".$emp_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('C'."2", "Rebar Checker & 6 Year 7 Months")->mergeCells('C2:D2')->getStyle('C2:D2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'."2", "Target Tons")->getStyle('E2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."2", $credit_target)->getStyle('F2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."2", "New Detailing Work")->mergeCells('G2:K2')->getStyle('G2:K2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'."2", "")->getStyle('L2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."2", "Revision Work")->mergeCells('M2:T2')->getStyle('M2:T2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."2", "Listing")->getStyle('U2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('V'."2", "OTHER WORKS")->getStyle('V2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'."2", "Booking Hours")->getStyle('W2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'."2", "OFFICE HOURS")->mergeCells('X2:Z2')->getStyle('X2:Z2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('AA'."2", " ")->getStyle('AA2')->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($LeftArray);
			}else
			if($excel_column === 'AB'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($RightArray);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($styleArray);
			}
		}
		
		$i = 4;
		$j = 0;
		$k = 0;
		$previous_date = "";
		foreach($checker_result as $key => $time_sheet){
			$work_type_time 		= $time_sheet->work_type_time;
			$sum_value_total_hours  = array();
			$booking_hours 			= array();
			if((int)$work_type_time === 1){
				$study1 			= $time_sheet->study;
				$checking1 			= $time_sheet->checking;
				$discussion1 		= $time_sheet->discussion;
				$was1 				= $time_sheet->was;
				$correction_time1 	= $time_sheet->correction_time;
				$study2				= "";
				$checking2 			= "";
				$discussion2 		= "";
				$was2 				= "";
				$correction_time2 	= "";
			}else
			if((int)$work_type_time === 2){
				$study2				= $time_sheet->study;
				$checking2 			= $time_sheet->checking;
				$discussion2 		= $time_sheet->discussion;
				$was2 				= $time_sheet->was;
				$correction_time2 	= $time_sheet->correction_time;
				$study1 			= "";
				$checking1 			= "";
				$discussion1 		= "";
				$was1 				= "";
				$correction_time1 	= "";
			}else{
				$study1		 		= $time_sheet->study;
				$study2 			= $time_sheet->study;
				$checking1 			= $time_sheet->checking;
				$checking2 			= $time_sheet->checking;
				$discussion1 		= $time_sheet->discussion;
				$discussion2 		= $time_sheet->discussion;
				$was1 				= $time_sheet->was;
				$was2 				= $time_sheet->was;
				$correction_time1 	= $time_sheet->correction_time;
				$correction_time2 	= $time_sheet->correction_time;
			}

			$booking_hours[] = $study1;
			$booking_hours[] = $checking1;
			$booking_hours[] = $discussion1;
			$booking_hours[] = $was1;
			$booking_hours[] = $correction_time1;
			$booking_hours[] = $time_sheet->rfi;
			$booking_hours[] = $study2;
			$booking_hours[] = $checking2;
			$booking_hours[] = $time_sheet->aec;
			$booking_hours[] = $correction_time2;
			$booking_hours[] = $was2;
			$booking_hours[] = $time_sheet->billable_hours;
			$booking_hours[] = $discussion2;
			$booking_hours[] = $time_sheet->co_checking;
			$booking_hours[] = $time_sheet->bar_listing_time;
			$booking_hours[] = $time_sheet->emails;
			$booking_hours[] = $time_sheet->other_works;
			$total_hours 	 = $this->AddPlayTime($booking_hours);
			$sum_total_hours[] 			 = $total_hours;
			$sum_value_total_hours 		 = $this->AddPlayTime($sum_total_hours);

			$entry_date      		= $time_sheet->entry_date;
			$date_only = date('Y-m-d',strtotime($entry_date));
			if($previous_date === $date_only){
				$j ++;
			}else{
				$k = $i;
				$j = 0;
			}
			$range_start 	= $k;
			$range_end 		= $i;
			$project 		= $time_sheet->project;
			$project 		= $project_result[$project]['project_name'];
			$drawing_no 	= $time_sheet->drawing_no;
			$drawing_no 	= $drawing_result[$drawing_no]['drawing_no'];
			$work_status 	= $time_sheet->work_status;
			$work_status 	= $work_status_result[$work_status]['work_status'];

			$time_sheet_value['A']       = $time_sheet->entry_date;
			$time_sheet_value['B']       = $project;
			$time_sheet_value['C']       = $drawing_no;
			$time_sheet_value['D']       = $work_status;
			$time_sheet_value['E']       = $time_sheet->work_description;
			$time_sheet_value['F'] 		 = $time_sheet->emails;
			$time_sheet_value['G'] 		 = $study1;
			$time_sheet_value['H'] 		 = $checking1;
			$time_sheet_value['I'] 		 = $discussion1;
			$time_sheet_value['J']		 = $was1;
			$time_sheet_value['K'] 		 = $correction_time1;
			$time_sheet_value['L'] 		 = $time_sheet->rfi;
			$time_sheet_value['M']		 = $study2;
			$time_sheet_value['N']		 = $checking2;
			$time_sheet_value['O']		 = $time_sheet->aec;
			$time_sheet_value['P'] 		 = $correction_time2;
			$time_sheet_value['Q'] 		 = $was2;
			$time_sheet_value['R'] 		 = $time_sheet->billable_hours;
			$time_sheet_value['S'] 		 = $discussion2;
			$time_sheet_value['T'] 		 = $time_sheet->co_checking;
			$time_sheet_value['U']       = $time_sheet->bar_listing_time;
			$time_sheet_value['V'] 		 = $time_sheet->other_works;
			$time_sheet_value['W'] 		 = $total_hours;
			$time_sheet_value['X'] 		 = $time_sheet->in_time;
			$time_sheet_value['Y'] 		 = $time_sheet->out_time;
			$time_sheet_value['Z'] 		 = $time_sheet->total_time;
			$time_sheet_value['AA'] 	 = "shift";

			
			$sum_study1[]  				 = $study1;
			$sum_study2[]  				 = $study2;
			$sum_checking1[]  		 	 = $checking1;
			$sum_checking2[]  		 	 = $checking2;
			$sum_discussion1[] 			 = $discussion1;
			$sum_discussion2[] 			 = $discussion2;
			$sum_was1[] 				 = $was1;
			$sum_was2[] 				 = $was2;
			$sum_correction_time1[] 	 = $correction_time1;
			$sum_correction_time2[] 	 = $correction_time2;
			$sum_rfi[] 					 = $time_sheet->rfi;
			$sum_aec[] 					 = $time_sheet->aec;
			$sum_billable_hours[] 		 = $time_sheet->billable_hours;
			$sum_co_checking[] 			 = $time_sheet->co_checking;
			$sum_bar_listing_time[] 	 = $time_sheet->bar_listing_time;
			$sum_other_works[] 			 = $time_sheet->other_works;
			$sum_emails[]  				 = $time_sheet->emails;
			
			$sum_value_study1			 = $this->AddPlayTime($sum_study1);
			$sum_value_study2			 = $this->AddPlayTime($sum_study2);
			$sum_value_checking1		 = $this->AddPlayTime($sum_checking1);
			$sum_value_checking2		 = $this->AddPlayTime($sum_checking2);
			$sum_value_discussion1		 = $this->AddPlayTime($sum_discussion1);
			$sum_value_discussion2		 = $this->AddPlayTime($sum_discussion2);
			$sum_value_was1				 = $this->AddPlayTime($sum_was1);
			$sum_value_was2				 = $this->AddPlayTime($sum_was2);
			$sum_value_correction_time1	 = $this->AddPlayTime($sum_correction_time1);
			$sum_value_correction_time2	 = $this->AddPlayTime($sum_correction_time2);
			$sum_value_rfi				 = $this->AddPlayTime($sum_rfi);
			$sum_value_aec				 = $this->AddPlayTime($sum_aec);
			$sum_value_billable_hours 	 = $this->AddPlayTime($sum_billable_hours);
			$sum_value_bar_listing_time  = $this->AddPlayTime($sum_bar_listing_time);
			$sum_value_other_works		 = $this->AddPlayTime($sum_other_works);
			$sum_value_co_checking		 = $this->AddPlayTime($sum_co_checking);
			$sum_value_emails		 	 = $this->AddPlayTime($sum_emails);

			for ($x = 0; $x <= 26; $x++) {
				$excel_column  		= $excel[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				if($excel_column === 'X' || $excel_column === 'Y' || $excel_column === 'Z'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
				}else
				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($LeftBorder);
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'AA'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($RightBorder);
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($RightBorder);
				}
				else{
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);
				}
				
				$counter = $i;
			}
			$i++;
			$previous_date = $date_only;
		}
		$counter = $counter+1;
		$obj->getActiveSheet()->setCellValue('A'.$counter, $total_sum_detail_work)->mergeCells('A'.$counter.':'.'E'.$counter)->getStyle('A'.$counter.':'.'E'.$counter)->applyFromArray($FooterLeftStyle);
		$obj->getActiveSheet()->setCellValue('F'.$counter,$sum_value_emails)->getStyle('F'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$counter,$sum_value_study1)->getStyle('G'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$counter,$sum_value_checking1)->getStyle('H'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$counter,$sum_value_discussion1)->getStyle('I'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$counter,$sum_value_was1)->getStyle('J'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$counter,$sum_value_correction_time1)->getStyle('K'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter,$sum_value_rfi)->getStyle('L'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter,$sum_value_study2)->getStyle('M'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$counter,$sum_value_checking2)->getStyle('N'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$counter,$sum_value_aec)->getStyle('O'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter,$sum_value_correction_time2)->getStyle('P'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter,$sum_value_was2)->getStyle('Q'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter,$sum_value_billable_hours)->getStyle('R'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$counter,$sum_value_discussion2)->getStyle('S'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$counter,$sum_value_co_checking)->getStyle('T'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$counter,$sum_value_bar_listing_time)->getStyle('U'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$counter,$sum_value_other_works)->getStyle('V'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$counter,$sum_value_total_hours)->getStyle('W'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('X'.$counter,"")->getStyle('X'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Y'.$counter,"")->getStyle('Y'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Z'.$counter,"")->getStyle('Z'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AA'.$counter,"")->getStyle('AA'.$counter)->applyFromArray($FooterRightStyle);


		/* SECOND WORK SHEET */


		$cummulative_sheet2 = $counter+3;
		$cummulative_sheet3 = $counter+4;
		$cummulative_sheet4 = $counter+5;
		$cummulative_sheet5 = $counter+6;
		$cummulative_sheet6 = $counter+7;
		$cummulative_detail_count = $counter+8;

		$process_months  			= '01-'.$process_month;
		$process_months  			= date('Y-m',strtotime($process_months));
		$working_days_qry 			= 'SELECT count(*) as working_days,total_time FROM cw_time_sheet where entry_date like "%'.$process_months.'%" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$working_days_info   		= $this->db->query("CALL sp_a_run ('SELECT','$working_days_qry')");
		$working_days_result 		= $working_days_info->result();
		$working_days_info->next_result();
		$working_days 				= $working_days_result[0]->working_days;
		$min_std_work_cummlate 		= $working_days * 8;
		$min_ton_cummlate 			= $credit_target/10;
		$min_ton_cummlate			= $min_ton_cummlate/$working_days;
		$min_ton_cummlate 			= round($min_ton_cummlate,2);

		$project_wise_excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W');
		$project_wise_excel[]['excel_value']= array('Job Category','Project Name','# New dwg','# Rev dwg','Remarks','Emails','STY','CHK','DIS','WAS','COR','RFI','STY','CHK','AEC','COR','WAS','BH','DIS','CO CHK','CHK','OTHER WORK','TOTAL');

		for ($x = 0; $x <= 22; $x++) {
			$excel_column  = $project_wise_excel[0]['excel_column'][$x];
			$excel_value   = $project_wise_excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet2, "Checker Name: ".$emp_name)->mergeCells('A'.$cummulative_sheet2.':W'.$cummulative_sheet2)->getStyle('A'.$cummulative_sheet2.':W'.$cummulative_sheet2)->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet3, "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('A'.$cummulative_sheet3.':W'.$cummulative_sheet3)->getStyle('A'.$cummulative_sheet3.':W'.$cummulative_sheet3)->applyFromArray($LeftrightBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet4, "Working Days")->getStyle('A'.$cummulative_sheet4)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet4, "Min. Standard Working Hours")->getStyle('B'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet4, "Target Tons")->getStyle('C'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet4, "Min Shts/Day")->getStyle('D'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet4, "")->mergeCells('E'.$cummulative_sheet4.':W'.$cummulative_sheet4)->getStyle('E'.$cummulative_sheet4.':W'.$cummulative_sheet4)->applyFromArray($RightBorderHead);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet5, $working_days)->getStyle('A'.$cummulative_sheet5)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet5, $min_std_work_cummlate)->getStyle('B'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet5, $credit_target)->getStyle('C'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet5, $min_ton_cummlate)->getStyle('D'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet5, "Team:".$team_name.", ".$emp_name)->getStyle('E'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, "Emails")->getStyle('F'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'.$cummulative_sheet5, "Detailing Work")->mergeCells('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->getStyle('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'.$cummulative_sheet5, "")->getStyle('L'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'.$cummulative_sheet5, "Revision Work")->mergeCells('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->getStyle('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'.$cummulative_sheet5, "Listing")->getStyle('U'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('V'.$cummulative_sheet5, "OTHER WORKS")->getStyle('V'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'.$cummulative_sheet5, "TOTAL")->getStyle('W'.$cummulative_sheet5)->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($LeftArray);
			}else
			if($excel_column === 'W'){
				$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($RightArray);
			}
			else{
			$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($styleArray);
			}
		}

		$project_wise_qry 			= 'SELECT cw_job_category.job_category,count(cw_time_sheet_time_line.work_type) as work_type_count,cw_time_sheet_time_line.project,cw_project_and_drawing_master.project_name,cw_time_sheet_time_line.work_description,IF(SEC_TO_TIME( SUM(time_to_sec(emails)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(emails))),"%H:%i"),"") as cummulate_emails,IF(SEC_TO_TIME( SUM(time_to_sec(study)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(study))),"%H:%i"),"") as cummulate_study,IF(SEC_TO_TIME( SUM(time_to_sec(checking)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(checking))),"%H:%i"),"") as cummulate_checking,IF(SEC_TO_TIME( SUM(time_to_sec(discussion)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(discussion))),"%H:%i"),"") as cummulate_discussion,IF(SEC_TO_TIME( SUM(time_to_sec(was)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(was))),"%H:%i"),"") as cummulate_was,IF(SEC_TO_TIME( SUM(time_to_sec(correction_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(correction_time))),"%H:%i"),"") as cummulate_correction_time,IF(SEC_TO_TIME( SUM(time_to_sec(rfi)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(rfi))),"%H:%i"),"") as cummulate_rfi,IF(SEC_TO_TIME( SUM(time_to_sec(aec)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(aec))),"%H:%i"),"") as cummulate_aec,IF(SEC_TO_TIME( SUM(time_to_sec(billable_hours)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(billable_hours))),"%H:%i"),"") as cummulate_billable_hours,IF(SEC_TO_TIME( SUM(time_to_sec(co_checking)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(co_checking))),"%H:%i"),"") as cummulate_co_checking,IF(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time))),"%H:%i"),"") as cummulate_bar_listing_time,IF(SEC_TO_TIME( SUM(time_to_sec(other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(other_works))),"%H:%i"),"") as cummulate_other_works,sum(bar_list_quantity) as cummulate_bar_list_quantity,work_type,GROUP_CONCAT(work_description) as work_description FROM cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project inner join cw_job_category on cw_job_category.prime_job_category_id = cw_project_and_drawing_master.job_category where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.project,work_type order by cw_time_sheet_time_line.work_type';
		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result();
		$project_wise_info->next_result();

		$other_work_qry 			= 'select cw_time_sheet_time_line.work_type,work_description,cw_other_works.other_works,IF(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works))),"%H:%i"),"") as cummulate_works,other_work_name,IF(SEC_TO_TIME( SUM(time_to_sec(emails)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(emails))),"%H:%i"),"") as cummulate_emails from cw_time_sheet_time_line inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_other_works on cw_other_works.prime_other_works_id = cw_time_sheet_time_line.other_work_name where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and work_type = 4 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.other_work_name order by cw_time_sheet_time_line.other_work_name';
		$other_work_info   			= $this->db->query("CALL sp_a_run ('SELECT','$other_work_qry')");
		$other_work_result 			= $other_work_info->result();
		$other_work_info->next_result();

		$q = $cummulative_detail_count;
		$r = 0;
		$s = 0;
		$sum_new_detail_count 		= 0;
		$sum_new_rev_count 			= 0;
		$sum_value_bar_list_quantity_cummlate  		 = 0;
		foreach($project_wise_result as $key => $cummulative_time_sheet){
			$sum_value_bar_list_quantity_cummlate  	+= $cummulative_time_sheet->cummulate_bar_list_quantity;
			$work_type_time							 = $cummulative_time_sheet->work_type;
			$cummulate_booking_hours 	 			 = array();
			if((int)$work_type_time === 1){
				$cummulate_study1 						= $cummulative_time_sheet->cummulate_study;
				$cummulate_checking1 					= $cummulative_time_sheet->cummulate_checking;
				$cummulate_discussion1 	 				= $cummulative_time_sheet->cummulate_discussion;
				$cummulate_was1 	 					= $cummulative_time_sheet->cummulate_was;
				$cummulate_correction_time1 			= $cummulative_time_sheet->cummulate_correction_time;
				$cummulate_study2 						= "";
				$cummulate_checking2					= "";
				$cummulate_discussion2 	 				= "";
				$cummulate_was2 	 					= "";
				$cummulate_correction_time2 			= "";
				$cummulate_rfi2							= "";
				$cummulate_aec2							= "";
				$cummulate_billable_hours2 				= "";
				$cummulate_co_checking2 				= "";
				$cummulate_bar_listing_time			 	= "";
				$cummulate_other_works 					= "";
				$new_detail_count 						= $cummulative_time_sheet->work_type_count;
				$new_rev_count 							= "";
			}else
			if((int)$work_type_time === 2){
				$cummulate_study1 						= "";
				$cummulate_checking1 					= "";
				$cummulate_discussion1 	 				= "";
				$cummulate_was1 	 					= "";
				$cummulate_correction_time1 			= "";
				$cummulate_study2 						= $cummulative_time_sheet->cummulate_study;
				$cummulate_checking2					= $cummulative_time_sheet->cummulate_checking;
				$cummulate_discussion2 	 				= $cummulative_time_sheet->cummulate_discussion;
				$cummulate_was2 	 					= $cummulative_time_sheet->cummulate_was;
				$cummulate_correction_time2 			= $cummulative_time_sheet->cummulate_correction_time;
				$cummulate_rfi2 						= $cummulative_time_sheet->cummulate_rfi;
				$cummulate_aec2 						= $cummulative_time_sheet->cummulate_aec;
				$cummulate_billable_hours2 				= $cummulative_time_sheet->cummulate_billable_hours;
				$cummulate_co_checking2					= $cummulative_time_sheet->cummulate_co_checking;
				$cummulate_bar_listing_time			 	= "";
				$cummulate_other_works 					= "";
				$new_rev_count 							= $cummulative_time_sheet->work_type_count;
				$new_detail_count 						= "";
			}else{
				$cummulate_study1 						= "";
				$cummulate_checking1 					= "";
				$cummulate_discussion1 	 				= "";
				$cummulate_was1 	 					= "";
				$cummulate_correction_time1 			= "";
				$cummulate_study2 						= "";
				$cummulate_checking2					= "";
				$cummulate_discussion2 	 				= "";
				$cummulate_was2 	 					= "";
				$cummulate_correction_time2 			= "";
				$cummulate_rfi2							= "";
				$cummulate_aec2							= "";
				$cummulate_billable_hours2 				= "";
				$cummulate_co_checking2 				= "";
				$cummulate_bar_listing_time			 	= $cummulative_time_sheet->cummulate_bar_listing_time;
				$cummulate_other_works 					= "";
				$new_detail_count 						= $cummulative_time_sheet->work_type_count;
				$new_rev_count 							= "";
			}

			$cummulate_booking_hours[] = $cummulate_study1;
			$cummulate_booking_hours[] = $cummulate_checking1;
			$cummulate_booking_hours[] = $cummulate_discussion1;
			$cummulate_booking_hours[] = $cummulate_was1;
			$cummulate_booking_hours[] = $cummulate_correction_time1;
			$cummulate_booking_hours[] = $cummulate_rfi2;
			$cummulate_booking_hours[] = $cummulate_study2;
			$cummulate_booking_hours[] = $cummulate_checking2;
			$cummulate_booking_hours[] = $cummulate_aec2;
			$cummulate_booking_hours[] = $cummulate_correction_time2;
			$cummulate_booking_hours[] = $cummulate_was2;
			$cummulate_booking_hours[] = $cummulate_billable_hours2;
			$cummulate_booking_hours[] = $cummulate_discussion2;
			$cummulate_booking_hours[] = $cummulate_co_checking2;
			$cummulate_booking_hours[] = $cummulate_bar_listing_time;
			$cummulate_booking_hours[] = $cummulate_other_works;
			$cummulate_total_hours 	   = $this->AddPlayTime($cummulate_booking_hours);

			$time_sheet_value['A']       = $cummulative_time_sheet->job_category;
			$time_sheet_value['B']       = $cummulative_time_sheet->project_name;
			$time_sheet_value['C']       = $new_detail_count;
			$time_sheet_value['D']       = $new_rev_count;
			$time_sheet_value['E']       = $cummulative_time_sheet->work_description;
			$time_sheet_value['F'] 		 = $cummulative_time_sheet->cummulate_emails;
			$time_sheet_value['G'] 		 = $cummulate_study1;
			$time_sheet_value['H'] 		 = $cummulate_checking1;
			$time_sheet_value['I'] 		 = $cummulate_discussion1;
			$time_sheet_value['J']		 = $cummulate_was1;
			$time_sheet_value['K'] 		 = $cummulate_correction_time1;
			$time_sheet_value['L'] 		 = $cummulate_rfi2;
			$time_sheet_value['M']		 = $cummulate_study2;
			$time_sheet_value['N']		 = $cummulate_checking2;
			$time_sheet_value['O']		 = $cummulate_aec2;
			$time_sheet_value['P'] 		 = $cummulate_correction_time2;
			$time_sheet_value['Q'] 		 = $cummulate_was2;
			$time_sheet_value['R'] 		 = $cummulate_billable_hours2;
			$time_sheet_value['S'] 		 = $cummulate_discussion2;
			$time_sheet_value['T'] 		 = $cummulate_co_checking2;
			$time_sheet_value['U']       = $cummulate_bar_listing_time;
			$time_sheet_value['V'] 		 = $cummulate_other_works;
			$time_sheet_value['W'] 		 = $cummulate_total_hours;

			$sum_cummulate_study1[]  				 	= $cummulate_study1;
			$sum_value_cummulate_study1			 		= $this->AddPlayTime($sum_cummulate_study1);
			$sum_cummulate_checking1[]  				= $cummulate_checking1;
			$sum_value_cummulate_checking1				= $this->AddPlayTime($sum_cummulate_checking1);
			$sum_cummulate_discussion1[]  				= $cummulate_discussion1;
			$sum_value_cummulate_discussion1			= $this->AddPlayTime($sum_cummulate_discussion1);
			$sum_cummulate_was1[]  						= $cummulate_was1;
			$sum_value_cummulate_was1					= $this->AddPlayTime($sum_cummulate_was1);
			$sum_cummulate_correction_time1[]  			= $cummulate_correction_time1;
			$sum_value_cummulate_correction_time1		= $this->AddPlayTime($sum_cummulate_correction_time1);
			$sum_cummulate_rfi2[]  				 		= $cummulate_rfi2;
			$sum_value_cummulate_rfi2			 		= $this->AddPlayTime($sum_cummulate_rfi2);
			$sum_cummulate_study2[]  				 	= $cummulate_study2;
			$sum_value_cummulate_study2			 		= $this->AddPlayTime($sum_cummulate_study2);
			$sum_cummulate_checking2[]  				= $cummulate_checking2;
			$sum_value_cummulate_checking2			 	= $this->AddPlayTime($sum_cummulate_checking2);
			$sum_cummulate_aec2[]  				 		= $cummulate_aec2;
			$sum_value_cummulate_aec2			 		= $this->AddPlayTime($sum_cummulate_aec2);
			$sum_cummulate_correction_time2[]  			= $cummulate_correction_time2;
			$sum_value_cummulate_correction_time2		= $this->AddPlayTime($sum_cummulate_correction_time2);
			$sum_cummulate_was2[]  						= $cummulate_was2;
			$sum_value_cummulate_was2			 		= $this->AddPlayTime($sum_cummulate_was2);
			$sum_cummulate_billable_hours2[]  			= $cummulate_billable_hours2;
			$sum_value_cummulate_billable_hours2		= $this->AddPlayTime($sum_cummulate_billable_hours2);
			$sum_cummulate_discussion2[]  				= $cummulate_discussion2;
			$sum_value_cummulate_discussion2			= $this->AddPlayTime($sum_cummulate_discussion2);
			$sum_cummulate_co_checking2[]  				= $cummulate_co_checking2;
			$sum_value_cummulate_co_checking2			= $this->AddPlayTime($sum_cummulate_co_checking2);
			$sum_cummulate_bar_listing_time[]  			= $cummulate_bar_listing_time;
			$sum_value_cummulate_bar_listing_time		= $this->AddPlayTime($sum_cummulate_bar_listing_time);
			$sum_cummulate_total_hours[]  				= $cummulate_total_hours;
			$sum_value_cummulate_total_hours			= $this->AddPlayTime($sum_cummulate_total_hours);
			$sum_cummulate_emails1[]					= $cummulative_time_sheet->cummulate_emails;
			$sum_value_cummulate_emails1				= $this->AddPlayTime($sum_cummulate_emails1);
			$sum_new_detail_count 				       += $new_detail_count;
			$sum_new_rev_count 						   += $new_rev_count;
		
			for ($x = 0; $x <= 22; $x++) {
				$excel_column  = $project_wise_excel[0]['excel_column'][$x];
				$excel_value   = $project_wise_excel[1]['excel_value'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'W'){
					$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($RightBorder);
				}else{
					$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($verticalStyle);
				}
				$cummuate_second_count = $q;
			}
			$q++;
		}

		$other_work_count = $cummuate_second_count+1;
		$m = $other_work_count;
		if((int)$work_result_count === 0){
			$cummuate_final_count = $cummuate_second_count;
		}else{
			foreach($other_work_result as $key => $other_work_detail){
				$time_sheet_value['A']       = "";
				$time_sheet_value['B']       = $other_work_detail->other_works;
				$time_sheet_value['C']       = "";
				$time_sheet_value['D']       = "";
				$time_sheet_value['E']       = $other_work_detail->work_description;
				$time_sheet_value['F'] 		 = $other_work_detail->cummulate_emails;
				$time_sheet_value['G'] 		 = "";
				$time_sheet_value['H'] 		 = "";
				$time_sheet_value['I'] 		 = "";
				$time_sheet_value['J']		 = "";
				$time_sheet_value['K'] 		 = "";
				$time_sheet_value['L'] 		 = "";
				$time_sheet_value['M']		 = "";
				$time_sheet_value['N']		 = "";
				$time_sheet_value['O']		 = "";
				$time_sheet_value['P'] 		 = "";
				$time_sheet_value['Q'] 		 = "";
				$time_sheet_value['R'] 		 = "";
				$time_sheet_value['S'] 		 = "";
				$time_sheet_value['T'] 		 = "";
				$time_sheet_value['U']       = "";
				$time_sheet_value['V'] 		 = $other_work_detail->cummulate_works;
				$time_sheet_value['W'] 		 = $other_work_detail->cummulate_works;
				
				$sum_cummulate_works[]  	 = $other_work_detail->cummulate_works;
				$sum_value_cummulate_works   = $this->AddPlayTime($sum_cummulate_works);
				$sum_cummulate_emails2[]					= $other_work_detail->cummulate_emails;
				$sum_value_cummulate_emails2				= $this->AddPlayTime($sum_cummulate_emails2);

				for ($x = 0; $x <= 22; $x++) {
					$excel_column  = $project_wise_excel[0]['excel_column'][$x];
					$excel_value   = $project_wise_excel[1]['excel_value'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];

					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'W'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($verticalStyle);
					}
					$cummuate_final_count = $m;
				}
				$m++;
			}
		}

		$sum_value_cummulate_emails[]   = $sum_value_cummulate_emails1;
		$sum_value_cummulate_emails[]   = $sum_value_cummulate_emails2;
		$sum_value_cummulate_emails     = $this->AddPlayTime($sum_value_cummulate_emails);
		$cummuate_final_sumcount 		= $cummuate_final_count+1;
		$cummuate_final_second_sumcount = $cummuate_final_sumcount+1;
		$final_sum_total 				= array();
		$final_sum_total[] 				= $sum_value_cummulate_works;
		$final_sum_total[] 				= $sum_value_cummulate_total_hours;
		$final_sum_total[] 				= $sum_value_cummulate_emails;
		$final_sum_total		 		= $this->AddPlayTime($final_sum_total);


		$obj->getActiveSheet()->setCellValue('A'.$cummuate_final_sumcount, "")->mergeCells('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->getStyle('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->applyFromArray($FooterLeftStyle);
		$obj->getActiveSheet()->setCellValue('C'.$cummuate_final_sumcount,$sum_new_detail_count)->getStyle('C'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('D'.$cummuate_final_sumcount,$sum_new_rev_count)->getStyle('D'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('E'.$cummuate_final_sumcount,"")->getStyle('E'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('F'.$cummuate_final_sumcount,$sum_value_cummulate_emails)->getStyle('F'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$cummuate_final_sumcount,$sum_value_cummulate_study1)->getStyle('G'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$cummuate_final_sumcount,$sum_value_cummulate_checking1)->getStyle('H'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$cummuate_final_sumcount,$sum_value_cummulate_discussion1)->getStyle('I'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$cummuate_final_sumcount,$sum_value_cummulate_was1)->getStyle('J'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$cummuate_final_sumcount,$sum_value_cummulate_correction_time1)->getStyle('K'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$cummuate_final_sumcount,$sum_value_cummulate_rfi2)->getStyle('L'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$cummuate_final_sumcount,$sum_value_cummulate_study2)->getStyle('M'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$cummuate_final_sumcount,$sum_value_cummulate_checking2)->getStyle('N'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$cummuate_final_sumcount,$sum_value_cummulate_aec2)->getStyle('O'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$cummuate_final_sumcount,$sum_value_cummulate_correction_time2)->getStyle('P'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$cummuate_final_sumcount,$sum_value_cummulate_was2)->getStyle('Q'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$cummuate_final_sumcount,$sum_value_cummulate_billable_hours2)->getStyle('R'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$cummuate_final_sumcount,$sum_value_cummulate_discussion2)->getStyle('S'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$cummuate_final_sumcount,$sum_value_cummulate_co_checking2)->getStyle('T'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$cummuate_final_sumcount,$sum_value_cummulate_bar_listing_time)->getStyle('U'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$cummuate_final_sumcount,$sum_value_cummulate_works)->getStyle('V'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$cummuate_final_sumcount,$final_sum_total)->getStyle('W'.$cummuate_final_sumcount)->applyFromArray($FooterRightStyle);


		/* THIRD WORK SHEET */


		$report_head 	= $cummuate_final_second_sumcount+5;
		$report_inc1 	= $cummuate_final_second_sumcount+6;
		$report_inc2 	= $report_inc1+1;
		$report_inc3 	= $report_inc2+1;
		$report_inc4 	= $report_inc3+1;
		$report_inc5 	= $report_inc4+1;
		$report_inc6 	= $report_inc5+1;
		$report_inc7 	= $report_inc6+1;
		$report_inc8 	= $report_inc7+1;
		$report_inc9 	= $report_inc8+1;
		$report_inc10 	= $report_inc9+1;
		$report_inc11 	= $report_inc10+1;
		$report_inc12 	= $report_inc11+1;
		$report_inc13 	= $report_inc12+1;
		$report_inc14 	= $report_inc13+1;
		$report_inc15 	= $report_inc14+1;
		$report_inc16 	= $report_inc15+1;
		$report_inc17 	= $report_inc16+1;
		$report_inc18 	= $report_inc17+1;
		$report_inc19 	= $report_inc18+1;


		$no_of_holiday 				= 0;
		$no_of_taken_leave 			= 0;
		$no_of_working_days  		= $working_days + $no_of_holiday - $no_of_taken_leave;

		
		$min_different_booking_hrs   = $this->time_to_decimal($final_sum_total);
		$min_different_booking_hrs 	 = $min_different_booking_hrs/24;
		$min_different_booking_hrs 	 = $this->decimalHours($min_different_booking_hrs);


		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));
		$total_time_qry 			= 'SELECT total_time FROM cw_time_sheet where entry_date like "%'.$process_month.'%" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$total_time_info   		= $this->db->query("CALL sp_a_run ('SELECT','$total_time_qry')");
		$total_time_result 		= $total_time_info->result_array();
		$total_time_info->next_result();
		$working_days_time = array();
		foreach ($total_time_result as $key => $working_days) {
			$working_days_time[] 	= $working_days['total_time'];
		}
		$working_days_time 	  		= $this->AddPlayTime($working_days_time);
		$start 			 	  		= $working_days_time;
		$end 						= $min_different_booking_hrs;
		$getnum = function($value) {
		    $pieces = explode(':', $value);
		    if(count($pieces) > 0) {
		        return (intval($pieces[0])*60)+intval($pieces[1]);
		    }
		    return 0;
		};
		$start_num			  		= $getnum->__invoke($start);
		$end_num 			  		= $getnum->__invoke($end);
		$diff 				  		= max($start_num, $end_num) - min($end_num, $start_num);
		$diff_office_office  		= intval($diff / 60).':'.($diff % 60);

		$actual_qry 			= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and team_leader_name = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1';
		$actual_info   		= $this->db->query("CALL sp_a_run ('SELECT','$actual_qry')");
		$actual_result 		= $actual_info->result();
		$actual_info->next_result();

		$actual_tonnage 			= $actual_result[0]->actual_tonnage;
		$actual_billable_time 		= $actual_result[0]->actual_billable_time;
		$actual_billable_time 		= explode(':', $actual_billable_time);
		$actual_billable_time 		= $actual_billable_time[0].':'.$actual_billable_time[1];
		$decimalHours 				= $this->decimalHours($actual_billable_time);
		$decimalHours 				= $decimalHours/24;
		$rev_hrs_tons 				= $decimalHours * 1.5;
		$production_tons 			= $rev_hrs_tons + $actual_tonnage;
		if($production_tons<=$credit_target){
			$target_status  		= "Not Reached";
		}else{
			$target_status  		= "Reached";
		}

		$checked_sheet_new  		= "DOUBT1";
		$checked_sheet_rev  		= "DOUBT2";
		$tons_new_detailing 		= $production_tons/500;
		$team_ton_per_sheet 		= $actual_tonnage/$checked_sheet_new;
		$total_qa_error_count 		= "DOUBT3";
		$avg_qa_error_sheet  		= $total_qa_error_count/$checked_sheet_new;
		$client_reported_error 		= "DOUBT4";


		$min_different_office_hrs   = $this->time_to_decimal($diff_office_office);
		$min_different1 			= $no_of_working_days*8.5/24;
		$min_different2 			= $no_of_working_days*0.75/24;
		$min_different  			= ($min_different_office_hrs * $min_different2)/$min_different1;
		$min_different 				= $this->decimal_to_time($min_different);

		$project_excel[]['excel_column']= array('C'.$report_inc1,'C'.$report_inc2,'C'.$report_inc3,'C'.$report_inc4,'C'.$report_inc5,'C'.$report_inc6,'C'.$report_inc7,'C'.$report_inc8,'C'.$report_inc9,'C'.$report_inc10,'C'.$report_inc11,'C'.$report_inc12,'C'.$report_inc13,'C'.$report_inc14,'C'.$report_inc15,'C'.$report_inc16,'C'.$report_inc17,'C'.$report_inc18,'C'.$report_inc19);
		$project_excel[]['excel_value']= array('No. of Holiday Working Days','No. of Leave taken','No. of Working Days','Total Office hours','Total Booking hours','Difference b/t Booking  & Office Hrs','X','Teams Tons Detailed (Submitted Log)','Teams Rev. hours (Submitted Log)','Teams Rev. hours in Tons','Teams Total Production Tons','Target Reached/Not Reached','Checked Sheets New (Submitted Log)','Checked Sheets Rev (Time Sheet)','Teams Tons per Hour New Detailing only','Teams Tons per Sheet','Total QA Error Count (Submitted Log)','Avg. QA Error Count per Sheet','Client Reported Severe Errors');
		$project_excel[]['end_column']= array('G'.$report_inc1,'G'.$report_inc2,'G'.$report_inc3,'G'.$report_inc4,'G'.$report_inc5,'G'.$report_inc6,'G'.$report_inc7,'G'.$report_inc8,'G'.$report_inc9,'G'.$report_inc10,'G'.$report_inc11,'G'.$report_inc12,'G'.$report_inc13,'G'.$report_inc14,'G'.$report_inc15,'G'.$report_inc16,'G'.$report_inc17,'G'.$report_inc18,'G'.$report_inc19);
		$project_excel[]['column_cell']= array('H'.$report_inc1,'H'.$report_inc2,'H'.$report_inc3,'H'.$report_inc4,'H'.$report_inc5,'H'.$report_inc6,'H'.$report_inc7,'H'.$report_inc8,'H'.$report_inc9,'H'.$report_inc10,'H'.$report_inc11,'H'.$report_inc12,'H'.$report_inc13,'H'.$report_inc14,'H'.$report_inc15,'H'.$report_inc16,'H'.$report_inc17,'H'.$report_inc18,'H'.$report_inc19);

		$project_excel[]['column_value']= array($no_of_holiday,$no_of_taken_leave,$no_of_working_days,$working_days_time,$min_different_booking_hrs,$diff_office_office,"",$actual_tonnage,$actual_billable_time,$rev_hrs_tons,$production_tons,$target_status,$checked_sheet_new,$checked_sheet_rev,$tons_new_detailing,$team_ton_per_sheet,$total_qa_error_count,$avg_qa_error_sheet,$client_reported_error);

		$project_excel[]['column_end']= array('I'.$report_inc1,'I'.$report_inc2,'I'.$report_inc3,'I'.$report_inc4,'I'.$report_inc5,'I'.$report_inc6,'I'.$report_inc7,'I'.$report_inc8,'I'.$report_inc9,'I'.$report_inc10,'I'.$report_inc11,'I'.$report_inc12,'I'.$report_inc13,'I'.$report_inc14,'I'.$report_inc15,'I'.$report_inc16,'I'.$report_inc17,'I'.$report_inc18,'I'.$report_inc19);


		$match_id = 'H'.$report_inc19;
		for ($x = 0; $x <= 18; $x++) {
			$excel_column  		= $project_excel[0]['excel_column'][$x];
			$excel_value   		= $project_excel[1]['excel_value'][$x];
			$end_column   		= $project_excel[2]['end_column'][$x];
			$column_cell   		= $project_excel[3]['column_cell'][$x];
			$column_value   	= $project_excel[4]['column_value'][$x];
			$column_end   		= $project_excel[5]['column_end'][$x];
			$obj->getActiveSheet()->setCellValue('C'.$report_head, "Checker Name: ".$emp_name)->mergeCells('C'.$report_head.':I'.$report_head)->getStyle('C'.$report_head.':I'.$report_head)->applyFromArray($TopBorder);
			if($match_id === $column_cell){
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($FooterLeftStyletwo);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($FooterRightStyletwo);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($LeftBorder);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($RightBordertwo);
			}
			$obj->getActiveSheet()->setCellValue('J'.$report_inc5, "Min Difference")->mergeCells('J'.$report_inc5.':K'.$report_inc5)->getStyle('J'.$report_inc5.':K'.$report_inc5)->applyFromArray($LeftBorder);
			$obj->getActiveSheet()->setCellValue('J'.$report_inc6, $min_different)->mergeCells('J'.$report_inc6.':K'.$report_inc6)->getStyle('J'.$report_inc6.':K'.$report_inc6)->applyFromArray($LeftBorder);
		}

			// die;
			$filename= $control_name."_".$employee_code.".xls"; //save our workbook as this file name
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
			header('Cache-Control: max-age=0'); //no cache
			//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
			//if you want to save it as .XLSX Excel 2007 format
			$objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel5');
			//force user to download the Excel file without writing it to server's HD
			$objWriter->save('php://output');
			echo json_encode(array('success' => TRUE, 'output' => $excelOutput));
		// }
		
	}
	function decimalHours($time)
	{
	    $hms = explode(":", $time);
	    return ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
	}
	function time_to_decimal($time) {
	    $timeArr = explode(':', $time);
	    $decTime = ($timeArr[0]*60) + ($timeArr[1]) + ($timeArr[2]/60);
	 
	    return $decTime;
	}
	function decimal_to_time($decimal) {
	    $hours = floor($decimal / 60);
	    $minutes = floor($decimal % 60);
	    $seconds = $decimal - (int)$decimal;
	    $seconds = round($seconds * 60);
	 
	    return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
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
	public function datacount_check(){
		$employee_code 		= $this->input->post("employee_code");
		$process_month 		= $this->input->post("process_month");
		$checker_qry 		= 'select count(*) as rlst_count from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
		$checker_info   	= $this->db->query("CALL sp_a_run ('SELECT','$checker_qry')");
		$checker_result 	= $checker_info->result();
		$checker_info->next_result();
		$rlst_count 		= $checker_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
}
?>