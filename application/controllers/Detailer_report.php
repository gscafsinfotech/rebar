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
		$this->load->view("$this->control_name/manage",$data);
	}
	public function emp_suggest(){
		$search_term  = $this->input->post_get('term');
		$final_qry = 'select employee_code,emp_name from cw_employees where role = 5 and trans_status = 1 and employee_code like "'.$search_term.'%"';
		$final_data   = $this->db->query("CALL sp_a_run ('SELECT','$final_qry')");
		$final_result = $final_data->result();
		$final_data->next_result();
		foreach($final_result as $rslt){
			$employee_code = $rslt->employee_code;
			$emp_name      = $rslt->emp_name;
			$suggestions[] = array('value' => $employee_code, 'label' => "$employee_code - $emp_name");
		}
		if(empty($suggestions)){
			$suggestions[] = array('value' => "0", 'label' => "No data found for this search");
		}
		echo json_encode($suggestions);
	}
	public function excel_export($employee_code,$process_month,$process_by){
		$control_name		= $this->control_name;
		$process_month 		= $process_month;
		$get_month 			= explode('-', $process_month);
		$month_name			= $get_month[0];
		$month_name 		= date("F", mktime(null, null, null, $month_name, 1));
		$detailer_qry 		= 'select emp_name,cw_time_sheet.employee_code,entry_date,in_time,out_time,total_time,IF(credit>"00:00:00", credit, "") as credit,IF(detailing_time>"00:00:00", detailing_time, "") as detailing_time,IF(study>"00:00:00", study, "") as study,IF(discussion>"00:00:00", discussion, "") as discussion,IF(rfi>"00:00:00", rfi, "") as rfi,IF(checking>"00:00:00", checking, "") as checking,IF(correction_time>"00:00:00", correction_time, "") as correction_time,IF(first_check_minor>"00:00:00", first_check_minor, "") as first_check_minor,IF(first_check_major>"00:00:00", first_check_major, "") as first_check_major,IF(second_check_major>"00:00:00", second_check_major, "") as second_check_major,IF(second_check_minor>"00:00:00", second_check_minor, "") as second_check_minor,IF(qa_major>"00:00:00", qa_major, "") as qa_major,IF(qa_minor>"00:00:00", qa_minor, "") as qa_minor,work_description,tonnage,IF(other_works>"00:00:00", other_works, "") as other_works,bar_list_quantity,IF(bar_listing_time>"00:00:00", bar_listing_time, "") as bar_listing_time,IF(revision_time>"00:00:00", revision_time, "") as revision_time,IF(change_order_time>"00:00:00", change_order_time, "") as change_order_time,IF(billable>"00:00:00", billable, "") as billable,IF(billable_hours>"00:00:00", billable_hours, "") as billable_hours,IF(non_billable_hours>"00:00:00", non_billable_hours, "") as non_billable_hours,cw_work_type.work_type,entry_type,client_name,project,drawing_no,work_status,IF(aec>"00:00:00", aec, "") as aec from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
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
		$excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
		$excel[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Credit','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','PCO','QTY','HOURS','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');
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
	    $FooterStyle  = array(
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
	            'color' => array('rgb' => 'FFFF00')
	        ),
	    	'alignment' => array(
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );

	    for ($x = 0; $x <= 27; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR ".strtoupper($month_name))->mergeCells('A1:AB1')->getStyle('A1:AB1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('A'."2", "Detailer Name:".$emp_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'."2", "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('C2:D2')->getStyle('C2:D2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'."2", "Target Tons")->getStyle('E2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."2", "Credit")->getStyle('F2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."2", "Detailing Work")->mergeCells('G2:L2')->getStyle('G2:L2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."2", "Revision Work")->mergeCells('M2:T2')->getStyle('M2:T2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."2", "BAR LIST")->mergeCells('U2:V2')->getStyle('U2:V2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'."2", "OTHER WORKS")->getStyle('W2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'."2", "Booking Hours")->getStyle('X2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('Y'."2", "OFFICE HOURS")->mergeCells('Y2:AA2')->getStyle('Y2:AA2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('AB'."2", " ")->getStyle('AB2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($styleArray);
		}
		
		$i = 4;
		$j = 0;
		$k = 0;
		$previous_date = "";
		foreach($detailer_result as $key => $time_sheet){
			$sum_value_total_hours  = array();
			$booking_hours 	 = array();
			$booking_hours[] = $time_sheet->study;
			$booking_hours[] = $time_sheet->detailing_time;
			$booking_hours[] = $time_sheet->discussion;
			$booking_hours[] = $time_sheet->checking;
			$booking_hours[] = $time_sheet->correction_time;
			$booking_hours[] = $time_sheet->rfi;
			$booking_hours[] = $time_sheet->study;
			$booking_hours[] = $time_sheet->aec;
			$booking_hours[] = $time_sheet->checking;
			$booking_hours[] = $time_sheet->correction_time;
			$booking_hours[] = $time_sheet->non_billable_hours;
			$booking_hours[] = $time_sheet->billable_hours;
			$booking_hours[] = $time_sheet->discussion;
			$booking_hours[] = $time_sheet->change_order_time;
			$booking_hours[] = $time_sheet->bar_listing_time;
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
			$time_sheet_value['F'] 		 = $time_sheet->credit;
			$time_sheet_value['G'] 		 = $time_sheet->study;
			$time_sheet_value['H'] 		 = $time_sheet->detailing_time;
			$time_sheet_value['I'] 		 = $time_sheet->discussion;
			$time_sheet_value['J']		 = $time_sheet->checking;
			$time_sheet_value['K'] 		 = $time_sheet->correction_time;
			$time_sheet_value['L'] 		 = $time_sheet->rfi;
			$time_sheet_value['M']		 = $time_sheet->study;
			$time_sheet_value['N']		 = $time_sheet->aec;
			$time_sheet_value['O']		 = $time_sheet->checking;
			$time_sheet_value['P'] 		 = $time_sheet->correction_time;
			$time_sheet_value['Q'] 		 = $time_sheet->non_billable_hours;
			$time_sheet_value['R'] 		 = $time_sheet->billable_hours;
			$time_sheet_value['S'] 		 = $time_sheet->discussion;
			$time_sheet_value['T'] 		 = $time_sheet->change_order_time;
			$time_sheet_value['U']       = $time_sheet->bar_list_quantity;
			$time_sheet_value['V'] 		 = $time_sheet->bar_listing_time;
			$time_sheet_value['W'] 		 = $time_sheet->other_works;
			$time_sheet_value['X'] 		 = $total_hours;
			$time_sheet_value['Y'] 		 = $time_sheet->in_time;
			$time_sheet_value['Z'] 		 = $time_sheet->out_time;
			$time_sheet_value['AA'] 	 = $time_sheet->total_time;
			$time_sheet_value['AB'] 	 = "shift";

			$sum_study[]  				 = $time_sheet->study;
			$sum_detailing_time[]  		 = $time_sheet->detailing_time;
			$sum_discussion[] 			 = $time_sheet->discussion;
			$sum_checking[] 			 = $time_sheet->checking;
			$sum_correction_time[] 		 = $time_sheet->correction_time;
			$sum_rfi[] 					 = $time_sheet->rfi;
			$sum_aec[] 					 = $time_sheet->aec;
			$sum_non_billable_hours[] 	 = $time_sheet->non_billable_hours;
			$sum_billable_hours[] 		 = $time_sheet->billable_hours;
			$sum_change_order_time[]  	 = $time_sheet->change_order_time;
			$sum_bar_list_quantity[] 	 = $time_sheet->bar_list_quantity;
			$sum_bar_listing_time[] 	 = $time_sheet->bar_listing_time;
			$sum_other_works[] 			 = $time_sheet->other_works;
			$sum_credit[]  				 = $time_sheet->credit;
			
			$sum_value_study			 = $this->AddPlayTime($sum_study);
			$sum_value_detailing_time	 = $this->AddPlayTime($sum_detailing_time);
			$sum_value_discussion		 = $this->AddPlayTime($sum_discussion);
			$sum_value_checking			 = $this->AddPlayTime($sum_checking);
			$sum_value_correction_time	 = $this->AddPlayTime($sum_correction_time);
			$sum_value_rfi				 = $this->AddPlayTime($sum_rfi);
			$sum_value_aec				 = $this->AddPlayTime($sum_aec);
			$sum_value_non_billable_hours= $this->AddPlayTime($sum_non_billable_hours);
			$sum_value_billable_hours 	 = $this->AddPlayTime($sum_billable_hours);
			$sum_value_change_order_time = $this->AddPlayTime($sum_change_order_time);
			$sum_value_bar_list_quantity = $this->AddPlayTime($sum_bar_list_quantity);
			$sum_value_bar_listing_time  = $this->AddPlayTime($sum_bar_listing_time);
			$sum_value_other_works		 = $this->AddPlayTime($sum_other_works);
			$sum_value_credit		 	 = $this->AddPlayTime($sum_credit);

			for ($x = 0; $x <= 27; $x++) {
				$excel_column  		= $excel[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				if($excel_column === 'A' || $excel_column === 'Y' || $excel_column === 'Z' || $excel_column === 'AA' || $excel_column === 'AB'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
				}
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);
				$counter = $i;
			}
			$i++;
			$previous_date = $date_only;
		}
		$counter = $counter+1;
		$obj->getActiveSheet()->setCellValue('A'.$counter, $total_sum_detail_work)->mergeCells('A'.$counter.':'.'F'.$counter)->getStyle('A'.$counter.':'.'E'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('F'.$counter,$sum_value_credit)->getStyle('F'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$counter,$sum_value_study)->getStyle('G'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$counter,$sum_value_detailing_time)->getStyle('H'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$counter,$sum_value_discussion)->getStyle('I'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$counter,$sum_value_checking)->getStyle('J'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$counter,$sum_value_correction_time)->getStyle('K'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter,$sum_value_rfi)->getStyle('L'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter,$sum_value_study)->getStyle('M'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$counter,$sum_value_aec)->getStyle('N'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$counter,$sum_value_checking)->getStyle('O'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter,$sum_value_correction_time)->getStyle('P'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter,$sum_value_non_billable_hours)->getStyle('Q'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter,$sum_value_billable_hours)->getStyle('R'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$counter,$sum_value_discussion)->getStyle('S'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$counter,$sum_value_change_order_time)->getStyle('T'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$counter,$sum_value_bar_list_quantity)->getStyle('U'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$counter,$sum_value_bar_listing_time)->getStyle('V'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$counter,$sum_value_other_works)->getStyle('W'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('X'.$counter,$sum_value_total_hours)->getStyle('X'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Y'.$counter,"")->getStyle('Y'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Z'.$counter,"")->getStyle('Z'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AA'.$counter,"")->getStyle('AA'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AB'.$counter,"")->getStyle('AB'.$counter)->applyFromArray($FooterStyle);
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