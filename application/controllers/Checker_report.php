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
		$this->load->view("$this->control_name/manage",$data);
	}
	public function emp_suggest(){
		$search_term  = $this->input->post_get('term');
		$final_qry = 'select employee_code,emp_name from cw_employees where role = 4 and trans_status = 1 and employee_code like "'.$search_term.'%"';
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
	function differenceInHours($startdate,$enddate){
		$starttimestamp = strtotime($startdate);
		$endtimestamp = strtotime($enddate);
		$difference = abs($endtimestamp - $starttimestamp)/3600;
		return $difference;
	}
	public function excel_export($employee_code,$from_date,$to_date){
		$control_name		= $this->control_name;
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));
		$time_sheet_qry 	= 'select other_works,cw_time_sheet_time_line.trans_created_date,project,cw_project_and_drawing_master_drawings.drawing_no,detailing_time,study,discussion,co_checking,was,emails,checking,correction_time,rfi,aec,billable_hours,non_billable_hours,change_order_time,bar_listing_time,bar_list_quantity,project_name,cw_client.client_name,cw_zct_5.cw_zct_5_value,work_type,cw_branch.branch,cw_work_status.work_status,cw_employees.emp_name from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id=cw_time_sheet.project inner join cw_client on cw_client.prime_client_id=cw_time_sheet.client_name inner join cw_work_status on cw_work_status.prime_work_status_id=cw_time_sheet.work_status inner join cw_zct_5 on cw_zct_5.cw_zct_5_id=cw_time_sheet.work_type inner join cw_branch on cw_branch.prime_branch_id=cw_time_sheet.branch inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id=cw_time_sheet.diagram_no inner join cw_employees on cw_employees.employee_code=cw_time_sheet_time_line.emp_code where cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 4 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 order by cw_time_sheet_time_line.trans_created_date';
		$time_sheet_info   	= $this->db->query("CALL sp_a_run ('SELECT','$time_sheet_qry')");
		$time_sheet_result  = $time_sheet_info->result();
		$time_sheet_info->next_result();
		$employee_name = $time_sheet_result[0]->emp_name;
		$punched_qry    = 'select in_hour,out_hour,entry_date,employee_code from cw_punched_data_details where employee_code ="'.$employee_code.'" and trans_status = 1';
		$punched_info   = $this->db->query("CALL sp_a_run ('SELECT','$punched_qry')");
		$punched_result = $punched_info->result();
		$punched_info->next_result();
		$emp_result  = json_decode(json_encode($punched_result),true);		
		$emp_result = array_reduce($emp_result, function($result, $arr){			
		    $result[$arr['employee_code']][$arr['entry_date']] = $arr;
		    return $result;
		}, array());
		$map_result = array_map(function($rslt){
                $return_data['entry_date']     = $rslt;
                return $return_data;
            }, $emp_result);
		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();		
		//Set the first row as the header row
		$i =3;
		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA');
		$excel_types[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Emails','STY','CHK','DIS','WAS','COR','RFI','STY','CHK','AEC','COR','WAS','BH','DIS','CO CHK','CHK','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');

		$styleArray = array(
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
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
		for ($x = 0; $x <= 26; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "Checker Name".$employee_name)->mergeCells('A1:B1')->getStyle('A1:B1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'."1", "Rebar Checker & 6 Year 7 Months")->mergeCells('C1:D1')->getStyle('C1:D1')->applyFromArray($styleArray);
			// $obj->getActiveSheet()->setCellValue('D'."1", "")->getStyle('D')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'."1", " Team's Target Tons")->getStyle('E1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."1", "Emails")->getStyle('F1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."1", "New Detailing Work")->mergeCells('G1:K1')->getStyle('G1:K1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'."1", "RFI")->getStyle('L1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."1", "Revision Work")->mergeCells('M1:T1')->getStyle('M1:T1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."1", "Listing")->getStyle('U1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('V'."1", "OTHER WORKS")->getStyle('V1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'."1", "BOOKING WORKS")->getStyle('W1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'."1", "OFFICE HOURS")->mergeCells('X1:Z1')->getStyle('X1:Z1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('AA'."1", " ")->getStyle('AA1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
		}
		$previous_date = "";
		$j = 0;
		$k = 0;
		foreach($time_sheet_result as $key => $time_sheet){
			$booking_hours 			= array();
			$trans_date      		= $time_sheet->trans_created_date;
			$date_only = date('Y-m-d',strtotime($trans_date));
			if($previous_date === $date_only){
				$j ++;
			}else{
				$k = $i;
				$j = 0;
			}
			$range_start 			= $k;
			$range_end 				= $i;
			$trans_date_only 		= date('Y-m-d',strtotime($trans_date));
			$trans_created_date   	= $time_sheet->trans_created_date;
			$check_entry_date		= $map_result[$employee_code]['entry_date'][$trans_date_only]['entry_date'];
			$in_hour 		 		= $map_result[$employee_code]['entry_date'][$trans_date_only]['in_hour'];
			$out_hour 		 		= $map_result[$employee_code]['entry_date'][$trans_date_only]['out_hour'];
			if($check_entry_date){
				$in_hour  			= $in_hour;
				$out_hour 			= $out_hour;
				$hours_difference   = $this->differenceInHours($in_hour,$out_hour);
				$differenceinhours  = number_format($hours_difference,2);
			}else{
				$in_hour  			= "";
				$out_hour 			= "";
			}


			$booking_hours 	 = array();
			$booking_hours[] = $time_sheet->study;
			$booking_hours[] = $time_sheet->checking;
			$booking_hours[] = $time_sheet->discussion;
			$booking_hours[] = $time_sheet->was;
			$booking_hours[] = $time_sheet->correction_time;
			$booking_hours[] = $time_sheet->rfi;
			$booking_hours[] = $time_sheet->study;
			$booking_hours[] = $time_sheet->aec;
			$booking_hours[] = $time_sheet->checking;
			$booking_hours[] = $time_sheet->was;
			$booking_hours[] = $time_sheet->billable_hours;
			$booking_hours[] = $time_sheet->discussion;
			$booking_hours[] = $time_sheet->co_checking;
			$booking_hours[] = $time_sheet->checking;
			$booking_hours[] = $time_sheet->other_works;
			$total_hours 	 = $this->AddPlayTime($booking_hours);


			$time_sheet_value['A']       = date('d-m-Y',strtotime($time_sheet->trans_created_date));
			$time_sheet_value['B']       = $time_sheet->project_name;
			$time_sheet_value['C']       = $time_sheet->drawing_no;
			$time_sheet_value['D']       = $time_sheet->cw_zct_5_value;
			$time_sheet_value['E']       = $time_sheet->work_status;
			$time_sheet_value['F'] 		 = $time_sheet->emails;
			$time_sheet_value['G'] 		 = $time_sheet->study;
			$time_sheet_value['H'] 		 = $time_sheet->checking;
			$time_sheet_value['I']		 = $time_sheet->discussion;
			$time_sheet_value['J'] 		 = $time_sheet->was;
			$time_sheet_value['K'] 		 = $time_sheet->correction_time;
			$time_sheet_value['L']		 = $time_sheet->rfi;
			$time_sheet_value['M']		 = $time_sheet->study;
			$time_sheet_value['N']		 = $time_sheet->checking;
			$time_sheet_value['O'] 		 = $time_sheet->aec;
			$time_sheet_value['P'] 		 = $time_sheet->correction_time;
			$time_sheet_value['Q'] 		 = $time_sheet->was;
			$time_sheet_value['R'] 		 = $time_sheet->billable_hours;
			$time_sheet_value['S'] 		 = $time_sheet->discussion;
			$time_sheet_value['T']       = $time_sheet->co_checking;
			$time_sheet_value['U'] 		 = $time_sheet->checking;
			$time_sheet_value['V'] 		 = $time_sheet->other_works;
			$time_sheet_value['W'] 		 = $total_hours;
			$time_sheet_value['X'] 		 = $in_hour;
			$time_sheet_value['Y'] 		 = $out_hour;
			$time_sheet_value['Z'] 		 = $differenceinhours;
			$time_sheet_value['AA'] 	 = "shift";
			
			for ($x = 0; $x <= 26; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				if($excel_column === 'A' || $excel_column === 'X' || $excel_column === 'Y' || $excel_column === 'Z' || $excel_column === 'AA'){
					
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
				}
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel);
			}
			$i++;
			$previous_date = $date_only;
		}	
		 // die;
		// Rename worksheet name
		 $filename = $control_name."_".$employee_code.".xls"; //save our workbook as this file name
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