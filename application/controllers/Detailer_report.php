<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Detailer_report  extends Action_controller{	
	public function __construct(){
		parent::__construct('detailer_report');
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
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 5 and reporting = "'.$logged_emp_code.'" and employee_status = 1 and trans_status = 1';
		}else
		if((int)$logged_role === 5){
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 5 and employee_code = "'.$logged_emp_code.'" and employee_status = 1 and trans_status = 1';
		}else{
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 5 and employee_status = 1 and trans_status = 1';
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
		$control_name		= $this->control_name;
		$process_month 		= $process_month;
		$get_month 			= explode('-', $process_month);
		$month_name			= $get_month[0];
		$month_name 		= date("F", mktime(null, null, null, $month_name, 1));

		$target_qry     = 'select cw_team_target_detailer_wise_target.target_value from cw_team_target inner join cw_team_target_detailer_wise_target on cw_team_target_detailer_wise_target.prime_team_target_id = cw_team_target.prime_team_target_id where cw_team_target.from_date <= "'.$process_month.'" and cw_team_target.to_date >= "'.$process_month.'" and cw_team_target_detailer_wise_target.detailer_name = "'.$employee_code.'" and cw_team_target.trans_status = 1';
		$target_info    = $this->db->query("CALL sp_a_run ('SELECT','$target_qry')");
		$target_result  = $target_info->result();
		$target_info->next_result();
		$credit_target  = $target_result[0]->target_value;
		$detailer_qry 		= 'select emp_name,cw_time_sheet.employee_code,entry_date,in_time,out_time,total_time,IF(credit>"00:00:00", TIME_FORMAT(credit, "%H:%i"), "") as credit,IF(detailing_time>"00:00:00", TIME_FORMAT(detailing_time, "%H:%i"), "") as detailing_time,IF(study>"00:00:00",TIME_FORMAT(study, "%H:%i"),"") as study,IF(discussion>"00:00:00", TIME_FORMAT(discussion, "%H:%i"), "") as discussion,IF(rfi>"00:00:00", TIME_FORMAT(rfi, "%H:%i"), "") as rfi,IF(checking>"00:00:00", TIME_FORMAT(checking, "%H:%i"), "") as checking,IF(correction_time>"00:00:00", TIME_FORMAT(correction_time, "%H:%i"), "") as correction_time,IF(first_check_minor>"00:00:00", TIME_FORMAT(first_check_minor, "%H:%i"), "") as first_check_minor,IF(first_check_major>"00:00:00", TIME_FORMAT(first_check_major, "%H:%i"), "") as first_check_major,IF(second_check_major>"00:00:00", TIME_FORMAT(second_check_major, "%H:%i"), "") as second_check_major,IF(second_check_minor>"00:00:00", TIME_FORMAT(second_check_minor, "%H:%i"), "") as second_check_minor,IF(qa_major>"00:00:00", TIME_FORMAT(qa_major, "%H:%i"), "") as qa_major,IF(qa_minor>"00:00:00", TIME_FORMAT(qa_minor, "%H:%i"), "") as qa_minor,work_description,tonnage,IF(other_works>"00:00:00", TIME_FORMAT(other_works, "%H:%i"), "") as other_works,IF(bar_list_quantity>"0", bar_list_quantity, "") as bar_list_quantity,IF(bar_listing_time>"00:00:00", TIME_FORMAT(bar_listing_time, "%H:%i"), "") as bar_listing_time,IF(revision_time>"00:00:00", TIME_FORMAT(revision_time, "%H:%i"), "") as revision_time,IF(change_order_time>"00:00:00", TIME_FORMAT(change_order_time, "%H:%i"), "") as change_order_time,IF(billable>"00:00:00", TIME_FORMAT(billable, "%H:%i"), "") as billable,IF(billable_hours>"00:00:00", TIME_FORMAT(billable_hours, "%H:%i"), "") as billable_hours,IF(non_billable_hours>"00:00:00", TIME_FORMAT(non_billable_hours, "%H:%i"), "") as non_billable_hours,cw_work_type.work_type,entry_type,client_name,project,drawing_no,work_status,IF(aec>"00:00:00", TIME_FORMAT(aec, "%H:%i"), "") as aec,cw_time_sheet_time_line.work_type as work_type_time from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
		$detailer_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailer_qry')");
		$detailer_result 	= $detailer_info->result();
		$detailer_info->next_result();
		$emp_name = $detailer_result[0]->emp_name;
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
	    $excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
		$excel[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Credit','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','PCO','QTY','HOURS','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');
	    for ($x = 0; $x <= 27; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR ".strtoupper($month_name))->mergeCells('A1:AB1')->getStyle('A1:AB1')->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'."2", "Detailer Name:".$emp_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('C'."2", "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('C2:D2')->getStyle('C2:D2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'."2", "Target Tons")->getStyle('E2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."2", $credit_target)->getStyle('F2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."2", "Detailing Work")->mergeCells('G2:L2')->getStyle('G2:L2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."2", "Revision Work")->mergeCells('M2:T2')->getStyle('M2:T2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."2", "BAR LIST")->mergeCells('U2:V2')->getStyle('U2:V2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'."2", "OTHER WORKS")->getStyle('W2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'."2", "Booking Hours")->getStyle('X2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('Y'."2", "OFFICE HOURS")->mergeCells('Y2:AA2')->getStyle('Y2:AA2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('AB'."2", " ")->getStyle('AB2')->applyFromArray($RightArray);
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
		$sum_value_bar_list_quantity  = 0;
		foreach($detailer_result as $key => $time_sheet){
			$sum_value_total_hours  = array();
			$booking_hours 	 		= array();
			$work_type_time  		= $time_sheet->work_type_time;
			$sum_value_bar_list_quantity  	+= $time_sheet->bar_list_quantity;
			if((int)$work_type_time === 1){
				$study1 			= $time_sheet->study;
				$checking1 			= $time_sheet->checking;
				$correction_time1 	= $time_sheet->correction_time;
				$discussion1 	 	= $time_sheet->discussion;
				$study2 			= "";
				$checking2 			= "";
				$correction_time2 	= "";
				$discussion2  		= "";
			}else
			if((int)$work_type_time === 2){
				$study2 			= $time_sheet->study;
				$checking2 			= $time_sheet->checking;
				$correction_time2 	= $time_sheet->correction_time;
				$discussion2  		= $time_sheet->discussion;
				$study1 			= "";
				$checking1 			= "";
				$correction_time1 	= "";
				$discussion1 	 	= "";
			}else{
				$study1 			= $time_sheet->study;
				$study2 			= $time_sheet->study;
				$checking1 			= $time_sheet->checking;
				$checking2 			= $time_sheet->checking;
				$correction_time1 	= $time_sheet->correction_time;
				$correction_time2 	= $time_sheet->correction_time;
				$discussion1  		= $time_sheet->discussion;
				$discussion2  		= $time_sheet->discussion;
			}
			$booking_hours[] = $study1;
			$booking_hours[] = $time_sheet->detailing_time;
			$booking_hours[] = $discussion1;
			$booking_hours[] = $checking1;
			$booking_hours[] = $correction_time1;
			$booking_hours[] = $study2;
			$booking_hours[] = $time_sheet->aec;
			$booking_hours[] = $checking2;
			$booking_hours[] = $correction_time2;
			$booking_hours[] = $time_sheet->non_billable_hours;
			$booking_hours[] = $time_sheet->billable_hours;
			$booking_hours[] = $discussion2;
			$booking_hours[] = $time_sheet->change_order_time;
			$booking_hours[] = $time_sheet->bar_listing_time;
			$booking_hours[] = $time_sheet->other_works;
			$booking_hours[] = $time_sheet->credit;
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
			$time_sheet_value['F'] 		 = $time_sheet->credit;
			$time_sheet_value['G'] 		 = $study1;
			$time_sheet_value['H'] 		 = $time_sheet->detailing_time;
			$time_sheet_value['I'] 		 = $discussion1;
			$time_sheet_value['J']		 = $checking1;
			$time_sheet_value['K'] 		 = $correction_time1;
			$time_sheet_value['L'] 		 = $time_sheet->rfi;
			$time_sheet_value['M']		 = $study2;
			$time_sheet_value['N']		 = $time_sheet->aec;
			$time_sheet_value['O']		 = $checking2;
			$time_sheet_value['P'] 		 = $correction_time2;
			$time_sheet_value['Q'] 		 = $time_sheet->non_billable_hours;
			$time_sheet_value['R'] 		 = $time_sheet->billable_hours;
			$time_sheet_value['S'] 		 = $discussion2;
			$time_sheet_value['T'] 		 = $time_sheet->change_order_time;
			$time_sheet_value['U']       = $time_sheet->bar_list_quantity;
			$time_sheet_value['V'] 		 = $time_sheet->bar_listing_time;
			$time_sheet_value['W'] 		 = $time_sheet->other_works;
			$time_sheet_value['X'] 		 = $total_hours;
			$time_sheet_value['Y'] 		 = $time_sheet->in_time;
			$time_sheet_value['Z'] 		 = $time_sheet->out_time;
			$time_sheet_value['AA'] 	 = $time_sheet->total_time;
			$time_sheet_value['AB'] 	 = "shift";

			$sum_study1[]  				 = $study1;
			$sum_study2[]  				 = $study2;
			$sum_detailing_time[]  		 = $time_sheet->detailing_time;
			$sum_discussion1[] 			 = $discussion1;
			$sum_discussion2[] 			 = $discussion2;
			$sum_checking1[] 			 = $checking1;
			$sum_checking2[] 			 = $checking2;
			$sum_correction_time1[] 	 = $correction_time1;
			$sum_correction_time2[] 	 = $correction_time2;
			$sum_rfi[] 					 = $time_sheet->rfi;
			$sum_aec[] 					 = $time_sheet->aec;
			$sum_non_billable_hours[] 	 = $time_sheet->non_billable_hours;
			$sum_billable_hours[] 		 = $time_sheet->billable_hours;
			$sum_change_order_time[]  	 = $time_sheet->change_order_time;
			$sum_bar_listing_time[] 	 = $time_sheet->bar_listing_time;
			$sum_other_works[] 			 = $time_sheet->other_works;
			$sum_credit[]  				 = $time_sheet->credit;
			
			$sum_value_study1			 = $this->AddPlayTime($sum_study1);
			$sum_value_study2			 = $this->AddPlayTime($sum_study2);
			$sum_value_detailing_time	 = $this->AddPlayTime($sum_detailing_time);
			$sum_value_discussion1		 = $this->AddPlayTime($sum_discussion1);
			$sum_value_discussion2		 = $this->AddPlayTime($sum_discussion2);
			$sum_value_checking1		 = $this->AddPlayTime($sum_checking1);
			$sum_value_checking2		 = $this->AddPlayTime($sum_checking2);
			$sum_value_correction_time1	 = $this->AddPlayTime($sum_correction_time1);
			$sum_value_correction_time2	 = $this->AddPlayTime($sum_correction_time2);
			$sum_value_rfi				 = $this->AddPlayTime($sum_rfi);
			$sum_value_aec				 = $this->AddPlayTime($sum_aec);
			$sum_value_non_billable_hours= $this->AddPlayTime($sum_non_billable_hours);
			$sum_value_billable_hours 	 = $this->AddPlayTime($sum_billable_hours);
			$sum_value_change_order_time = $this->AddPlayTime($sum_change_order_time);
			$sum_value_bar_listing_time  = $this->AddPlayTime($sum_bar_listing_time);
			$sum_value_other_works		 = $this->AddPlayTime($sum_other_works);
			$sum_value_credit		 	 = $this->AddPlayTime($sum_credit);


			for ($x = 0; $x <= 27; $x++) {
				$excel_column  		= $excel[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				if($excel_column === 'Y' || $excel_column === 'Z' || $excel_column === 'AA'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
				}else
				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($LeftBorder);
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'AB'){
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
		// die;
		$counter = $counter+1;
		$obj->getActiveSheet()->setCellValue('A'.$counter, $total_sum_detail_work)->mergeCells('A'.$counter.':'.'E'.$counter)->getStyle('A'.$counter.':'.'E'.$counter)->applyFromArray($FooterLeftStyle);
		$obj->getActiveSheet()->setCellValue('F'.$counter,$sum_value_credit)->getStyle('F'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$counter,$sum_value_study1)->getStyle('G'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$counter,$sum_value_detailing_time1)->getStyle('H'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$counter,$sum_value_discussion1)->getStyle('I'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$counter,$sum_value_checking1)->getStyle('J'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$counter,$sum_value_correction_time1)->getStyle('K'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter,$sum_value_rfi)->getStyle('L'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter,$sum_value_study2)->getStyle('M'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$counter,$sum_value_aec)->getStyle('N'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$counter,$sum_value_checking2)->getStyle('O'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter,$sum_value_correction_time2)->getStyle('P'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter,$sum_value_non_billable_hours)->getStyle('Q'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter,$sum_value_billable_hours)->getStyle('R'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$counter,$sum_value_discussion2)->getStyle('S'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$counter,$sum_value_change_order_time)->getStyle('T'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$counter,$sum_value_bar_list_quantity)->getStyle('U'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$counter,$sum_value_bar_listing_time)->getStyle('V'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$counter,$sum_value_other_works)->getStyle('W'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('X'.$counter,$sum_value_total_hours)->getStyle('X'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Y'.$counter,"")->getStyle('Y'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Z'.$counter,"")->getStyle('Z'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AA'.$counter,"")->getStyle('AA'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AB'.$counter,"")->getStyle('AB'.$counter)->applyFromArray($FooterRightStyle);




		// $cummulative_sheet2 = $counter+3;
		// $cummulative_sheet3 = $counter+4;
		// $cummulative_sheet4 = $counter+5;
		// $cummulative_sheet5 = $counter+6;
		// $cummulative_sheet6 = $counter+7;
		// echo $cummulative_sheet6;die;

		// $project_excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X');
		// $project_excel[]['excel_value']= array('Job Category','Project Name','# New dwg','# Rev dwg','Remarks','Credits','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','CO','QTY','HOURS','OTHER WORK','TOTAL');

		// for ($x = 0; $x <= 23; $x++) {
		// 	$excel_column  = $project_excel[0]['excel_column'][$x];
		// 	$excel_value   = $project_excel[1]['excel_value'][$x];
		// 	$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet2, "Detailer Name: Vishal Jaganathan.A")->mergeCells('A'.$cummulative_sheet2.':X'.$cummulative_sheet2)->getStyle('A'.$cummulative_sheet2.':X'.$cummulative_sheet2)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet3, "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('A'.$cummulative_sheet3.':X'.$cummulative_sheet3)->getStyle('A'.$cummulative_sheet3.':X'.$cummulative_sheet3)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet4, "Working Days")->getStyle('A'.$cummulative_sheet4)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet4, "Min. Standard Working Hours")->getStyle('B'.$cummulative_sheet4)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet4, "Target Tons")->getStyle('C'.$cummulative_sheet4)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet4, "Min Tons/Hrs")->getStyle('D'.$cummulative_sheet4)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet4, "")->mergeCells('E'.$cummulative_sheet4.':X'.$cummulative_sheet4)->getStyle('E'.$cummulative_sheet4.':X'.$cummulative_sheet4)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet5, "21")->getStyle('A'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet5, "168")->getStyle('B'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet5, "100")->getStyle('C'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet5, "006")->getStyle('D'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet5, "Team: CT10 Dhanalakshmi")->getStyle('E'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, "Credit")->getStyle('F'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('G'.$cummulative_sheet5, "Detailing Work")->mergeCells('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->getStyle('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('L'.$cummulative_sheet5, "")->getStyle('L'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('M'.$cummulative_sheet5, "Revision Work")->mergeCells('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->getStyle('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('U'.$cummulative_sheet5, "Revision Work")->mergeCells('U'.$cummulative_sheet5.':V'.$cummulative_sheet5)->getStyle('U'.$cummulative_sheet5.':V'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('W'.$cummulative_sheet5, "OTHER WORKS")->getStyle('W'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	$obj->getActiveSheet()->setCellValue('X'.$cummulative_sheet5, "TOTAL")->getStyle('X'.$cummulative_sheet5)->applyFromArray($styleArray);
		// 	if($excel_column === 'A'){
		// 		$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($LeftArray);
		// 	}else
		// 	if($excel_column === 'X'){
		// 		$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($RightArray);
		// 	}
		// 	else{
		// 	$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($styleArray);
		// 	}
		// }



		// $project_wise_qry 			= 'SELECT employee_code,emp_name FROM cw_employees where role = 5 and employee_status = 1 and trans_status = 1';
		// $project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		// $project_wise_result 		= $project_wise_info->result();
		// $project_wise_info->next_result();








		
		$start_date  = '01-'.$process_month;
		$start_date  = date('Y-m-d',strtotime($start_date));
		$end_date  = '31-'.$process_month;
		$end_date  = date('Y-m-d',strtotime($end_date));

		$project_wise_qry 			= 'SELECT count(*) as working_days,SEC_TO_TIME(SUM(TIME_TO_SEC(total_time))) as total_time FROM cw_time_sheet where entry_date >= "'.$start_date.'" and entry_date <= "'.$end_date.'" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result();
		$project_wise_info->next_result();
		// echo "<pre>";
		// print_r($project_wise_result);
		$working_days 				= $project_wise_result[0]->working_days;
		$total_time 				= $project_wise_result[0]->total_time;
		$total_time 				=explode(':', $total_time);
		$total_time 				= $total_time[0].':'.$total_time[1];

		$start_t = new DateTime($total_time);
		$current_t = new DateTime($sum_value_total_hours);
		$difference = $start_t ->diff($current_t );
		$differ_booking_office = $difference ->format('%H:%I');



		// $project_wise_qry 			= 'SELECT sum(tonnage) as total_tonnage FROM cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id where entry_date >= "'.$start_date.'" and entry_date <= "'.$end_date.'" and employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1';
		// $project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		// $project_wise_result 		= $project_wise_info->result();
		// $project_wise_info->next_result();

		// $total_tonnage = $project_wise_result[0]->total_tonnage;




		$project_wise_qry 			= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date >= "'.$start_date.'" and entry_date <= "'.$end_date.'" and employee_code = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1';
		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result();
		$project_wise_info->next_result();
		// echo "<pre>";
		// print_r($project_wise_result);die;


		$actual_tonnage = $project_wise_result[0]->actual_tonnage;
		$actual_billable_time = $project_wise_result[0]->actual_billable_time;
		$actual_billable_time 				=explode(':', $actual_billable_time);
		$actual_billable_time 				= $actual_billable_time[0].':'.$actual_billable_time[1];
		// echo $actual_billable_time;echo"<br>";
		$time=$actual_billable_time;
		$mult=1.5;
		$datetime=DateTime::createFromFormat('H:i',$time,new DateTimeZone('America/Sao_Paulo'));
		$minute=$datetime->format('i');
		$datetime->modify('+' . ($minute * $mult) . ' minutes');
		$datetime->modify('-' . $minute  . ' minutes');
		$rev_hrs_tons = $datetime->format('H:i');


		$production_tons = $actual_billable_time + $actual_tonnage;

		if($production_tons<=$credit_target){
			$target_status  = "Not Reached";
		}else{
			$target_status  = "Reached";
		}

	
		// echo $production_tons;


		// die;


		// $sum_value_study1+$sum_value_detailing_time1+$sum_value_discussion1+$sum_value_checking1+$sum_value_correction_time1

		// $sum_value_study2+$sum_value_aec+$sum_value_checking2+$sum_value_correction_time2+$sum_value_non_billable_hours+$sum_value_billable_hours+$sum_value_discussion2






		$report_head 	= $counter+5;
		$report_inc1 	= $counter+6;
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
		$report_inc20 	= $report_inc19+1;
		$report_inc21 	= $report_inc20+1;
		$report_inc22 	= $report_inc21+1;

		$project_excel[]['excel_column']= array('C'.$report_inc1,'C'.$report_inc2,'C'.$report_inc3,'C'.$report_inc4,'C'.$report_inc5,'C'.$report_inc6,'C'.$report_inc7,'C'.$report_inc8,'C'.$report_inc9,'C'.$report_inc10,'C'.$report_inc11,'C'.$report_inc12,'C'.$report_inc13,'C'.$report_inc14,'C'.$report_inc15,'C'.$report_inc16,'C'.$report_inc17,'C'.$report_inc18,'C'.$report_inc19,'C'.$report_inc20,'C'.$report_inc21,'C'.$report_inc22);
		$project_excel[]['excel_value']= array('No. of Holiday Working Days','No. of Leave taken','No. of Working Days','Total Office hours','Total Booking hours','Difference b/t Booking  & Off Hrs','Detailed Tons (Submitted Log)','Rev. hours (Submitted Log)','Rev.hours in Tons','Total Production Tons','Target Reached/Not Reached','Actual Tons per Hour','Tons per Hour New Detailing only','Detailed Sheets (Submitted Log)','Team Tons per Sheet (From Sub. Log)','Detailed Tons per Sheet','Hrs/Dwg','Det vs Cor','Productivity %','Cumulative Productivity %','Productivity hours %','Claimed Hours %');
		$project_excel[]['end_column']= array('G'.$report_inc1,'G'.$report_inc2,'G'.$report_inc3,'G'.$report_inc4,'G'.$report_inc5,'G'.$report_inc6,'G'.$report_inc7,'G'.$report_inc8,'G'.$report_inc9,'G'.$report_inc10,'G'.$report_inc11,'G'.$report_inc12,'G'.$report_inc13,'G'.$report_inc14,'G'.$report_inc15,'G'.$report_inc16,'G'.$report_inc17,'G'.$report_inc18,'G'.$report_inc19,'G'.$report_inc20,'G'.$report_inc21,'G'.$report_inc22);
		$project_excel[]['column_cell']= array('H'.$report_inc1,'H'.$report_inc2,'H'.$report_inc3,'H'.$report_inc4,'H'.$report_inc5,'H'.$report_inc6,'H'.$report_inc7,'H'.$report_inc8,'H'.$report_inc9,'H'.$report_inc10,'H'.$report_inc11,'H'.$report_inc12,'H'.$report_inc13,'H'.$report_inc14,'H'.$report_inc15,'H'.$report_inc16,'H'.$report_inc17,'H'.$report_inc18,'H'.$report_inc19,'H'.$report_inc20,'H'.$report_inc21,'H'.$report_inc22);

		$project_excel[]['column_value']= array('0','0',$working_days,$total_time,$sum_value_total_hours,$differ_booking_office,$actual_tonnage,$actual_billable_time,$rev_hrs_tons,$target_status,$report_inc11,$report_inc12,$report_inc13,$report_inc14,$report_inc15,$report_inc16,$report_inc17,$report_inc18,$report_inc19,$report_inc20,$report_inc21,$report_inc22);
		$project_excel[]['column_end']= array('I'.$report_inc1,'I'.$report_inc2,'I'.$report_inc3,'I'.$report_inc4,'I'.$report_inc5,'I'.$report_inc6,'I'.$report_inc7,'I'.$report_inc8,'I'.$report_inc9,'I'.$report_inc10,'I'.$report_inc11,'I'.$report_inc12,'I'.$report_inc13,'I'.$report_inc14,'I'.$report_inc15,'I'.$report_inc16,'I'.$report_inc17,'I'.$report_inc18,'I'.$report_inc19,'I'.$report_inc20,'I'.$report_inc21,'I'.$report_inc22);

		$match_id = 'H'.$report_inc22;
		for ($x = 0; $x <= 21; $x++) {
			$excel_column  = $project_excel[0]['excel_column'][$x];
			$excel_value   = $project_excel[1]['excel_value'][$x];
			$end_column   = $project_excel[2]['end_column'][$x];
			$column_cell   = $project_excel[3]['column_cell'][$x];
			$column_value   = $project_excel[4]['column_value'][$x];
			$column_end   = $project_excel[5]['column_end'][$x];
			$obj->getActiveSheet()->setCellValue('C'.$report_head, "Detailer Name: ".$emp_name)->mergeCells('C'.$report_head.':I'.$report_head)->getStyle('C'.$report_head.':I'.$report_head)->applyFromArray($TopBorder);
			
			if($match_id === $column_cell){
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($FooterLeftStyletwo);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($FooterRightStyletwo);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($LeftBorder);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($RightBordertwo);
			}
			

		}

// echo "column_end :: $column_end";








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
		$detailer_qry 		= 'select count(*) as rlst_count from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
		$detailer_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailer_qry')");
		$detailer_result 	= $detailer_info->result();
		$detailer_info->next_result();
		$rlst_count 		= $detailer_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
}
?>