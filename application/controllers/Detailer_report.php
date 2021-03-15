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
	// public function get_single_detailer_report(){
	// 	$employee_code		= $this->input->post("employee_code");
	// 	$from_date			= $this->input->post("from_date");
	// 	$to_date			= $this->input->post("to_date");
	// 	// echo "from_date :: $from_date";
	// 	// echo "to_date :: $to_date";die;
	// 	$time_sheet_qry 	= 'select other_works,cw_time_sheet.trans_created_date,project,cw_project_and_drawing_master_drawings.drawing_no,detailing_time,study,discussion,checking,correction_time,rfi,aec,billable_hours,non_billable_hours,change_order_time,bar_listing_time,bar_list_quantity,project_name,cw_client.client_name,cw_zct_5.cw_zct_5_value,work_type,cw_branch.branch,cw_work_status.work_status from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id=cw_time_sheet.project inner join cw_client on cw_client.prime_client_id=cw_time_sheet.client_name inner join cw_work_status on cw_work_status.prime_work_status_id=cw_time_sheet.work_status inner join cw_zct_5 on cw_zct_5.cw_zct_5_id=cw_time_sheet.work_type inner join cw_branch on cw_branch.prime_branch_id=cw_time_sheet.branch inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id=cw_time_sheet.diagram_no where employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 order by cw_time_sheet.trans_created_date';
	// 	$time_sheet_info   	= $this->db->query("CALL sp_a_run ('SELECT','$time_sheet_qry')");
	// 	$time_sheet_result  = $time_sheet_info->result();
	// 	$time_sheet_info->next_result();


		// $punched_qry    = 'select in_hour,out_hour,entry_date,employee_code from cw_punched_data_details where employee_code ="'.$employee_code.'" and trans_status = 1';
		// $punched_info   = $this->db->query("CALL sp_a_run ('SELECT','$punched_qry')");
		// $punched_result = $punched_info->result();
		// $punched_info->next_result();
		// // echo "<pre>";
		// // print_r($punched_result);die;
		// $emp_result  = json_decode(json_encode($punched_result),true);		
		// $emp_result = array_reduce($emp_result, function($result, $arr){			
		//     $result[$arr['employee_code']][$arr['entry_date']] = $arr;
		//     return $result;
		// }, array());
		// $map_result = array_map(function($rslt){
  //               $return_data['entry_date']     = $rslt;
  //               return $return_data;
  //           }, $emp_result);
	// 	$top_head			= "<tr><td colspan='2' style='border: 1px solid black;border-collapse: separate;'>DETAILER NAME: VISHAL JAGANATHAN.A</td><td colspan='2' style='text-align:center;'></td><td> Target Tons</td><td></td><td colspan='5' style='text-align:center;'>Detailing Work</td><td colspan='8' style='text-align:center'>Revision Work</td><td colspan='2' style='text-align:center;'>BAR LIST</td><td>OTHER WORK</td><td>BOOKING HOURS</td><td colspan='3' style='text-align:center;'>OFFICE HOURS</td><td>SHIFT</td></tr>";

	// 	$table_head			= "<tr><td>Date</td><td>Project Name</td><td>Drawing No</td><td>Drawing Revisin Status</td><td>Work Status</td><td>STY</td><td>DET</td><td>DIS</td><td>CHK</td><td>COR</td><td>RFI</td><td>STY</td><td>AEC</td><td>CHK</td><td>COR</td><td>NBH</td><td>BH</td><td>DIS</td><td>PCO</td><td>QTY</td><td>HOURS</td><td>OTHER WORK</td><td>BOOKING HOURS</td><td>IN</td><td>OUT</td><td>TOTAL</td><td>SHIFT</td></tr>";
	// 	$table_body  = "";
	// 	foreach ($time_sheet_result as $key => $time_sheet) {
			// $booking_hours = array();
			// $booking_hours[] = $time_sheet->study;
			// $booking_hours[] = $time_sheet->detailing_time;
			// $booking_hours[] = $time_sheet->discussion;
			// $booking_hours[] = $time_sheet->checking;
			// $booking_hours[] = $time_sheet->correction_time;
			// $booking_hours[] = $time_sheet->rfi;
			// $booking_hours[] = $time_sheet->study;
			// $booking_hours[] = $time_sheet->aec;
			// $booking_hours[] = $time_sheet->checking;
			// $booking_hours[] = $time_sheet->correction_time;
			// $booking_hours[] = $time_sheet->non_billable_hours;
			// $booking_hours[] = $time_sheet->billable_hours;
			// $booking_hours[] = $time_sheet->discussion;
			// $booking_hours[] = $time_sheet->change_order_time;
			// $booking_hours[] = $time_sheet->bar_listing_time;
			// $booking_hours[] = $time_sheet->other_works;
			// $total_hours 	 = $this->AddPlayTime($booking_hours);
	// 		$trans_date      = $time_sheet->trans_created_date;
	// 		$trans_date_only = date('Y-m-d',strtotime($trans_date));
			// $check_entry_date= $map_result[$employee_code]['entry_date'][$trans_date_only]['entry_date'];
			// $in_hour 		 = $map_result[$employee_code]['entry_date'][$trans_date_only]['in_hour'];
			// $out_hour 		 = $map_result[$employee_code]['entry_date'][$trans_date_only]['out_hour'];
	// 		if($check_entry_date){
	// 			$in_hour  = $in_hour;
	// 			$out_hour = $out_hour;
	// 			$hours_difference = $this->differenceInHours($in_hour,$out_hour);
	// 			$differenceinhours= number_format($hours_difference,2);

	// 		}else{
	// 			$in_hour  = "";
	// 			$out_hour = "";
	// 		}
	// 		$table_body			.= "<tr>
	// 							<td>".$time_sheet->trans_created_date."</td>
	// 							<td>".$time_sheet->project_name."</td>
	// 							<td>".$time_sheet->drawing_no."</td>
	// 							<td>".$time_sheet->cw_zct_5_value."</td>
	// 							<td>".$time_sheet->work_status."</td>
	// 							<td>".$time_sheet->study."</td>
	// 							<td>".$time_sheet->detailing_time."</td>
	// 							<td>".$time_sheet->discussion."</td>
	// 							<td>".$time_sheet->checking."</td>
	// 							<td>".$time_sheet->correction_time."</td>
	// 							<td>".$time_sheet->rfi."</td>
	// 							<td>".$time_sheet->study."</td>
	// 							<td>".$time_sheet->aec."</td>
	// 							<td>".$time_sheet->checking."</td>
	// 							<td>".$time_sheet->correction_time."</td>
	// 							<td>".$time_sheet->non_billable_hours."</td>
	// 							<td>".$time_sheet->billable_hours."</td>
	// 							<td>".$time_sheet->discussion."</td>
	// 							<td>".$time_sheet->change_order_time."</td>
	// 							<td>".$time_sheet->bar_list_quantity."</td>
	// 							<td>".$time_sheet->bar_listing_time."</td>
	// 							<td>".$time_sheet->other_works."</td>
	// 							<td>".$total_hours."</td>
	// 							<td>".$in_hour."</td>
	// 							<td>".$out_hour."</td>
	// 							<td>".$differenceinhours."</td>
	// 							<td>SHIFT</td>
	// 						</tr>";
	// 					}

	// 	$table_content = "<div style='margin:20px;'><table class='table table-striped table-bordered' id='detailer_report'>
	// 			<thead>
	// 				$top_head
	// 				$table_head
	// 			</thead>
	// 			<tbody>
	// 				$table_body
	// 			</tbody>
	// 		</table>
	// 		</div>";
	// 		$title            = "TESTING";
	// 		echo json_encode(array('success' => TRUE, 'message' => "See Unpunched leave details",'table_content'=>$table_content,'title'=>$title));
	// }
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
	public function excel_export($employee_code,$from_date,$to_date,$process_by){
		$control_name		= $this->control_name;
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));
		
		$time_sheet_qry 	= 'select other_works,cw_time_sheet_time_line.trans_created_date,project,cw_project_and_drawing_master_drawings.drawing_no,detailing_time,study,discussion,checking,correction_time,rfi,aec,billable_hours,non_billable_hours,change_order_time,bar_listing_time,bar_list_quantity,project_name,cw_client.client_name,cw_zct_5.cw_zct_5_value,work_type,cw_branch.branch,cw_work_status.work_status,cw_employees.emp_name,cw_project_and_drawing_master.prime_project_and_drawing_master_id from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id=cw_time_sheet.project inner join cw_client on cw_client.prime_client_id=cw_time_sheet.client_name inner join cw_work_status on cw_work_status.prime_work_status_id=cw_time_sheet.work_status inner join cw_zct_5 on cw_zct_5.cw_zct_5_id=cw_time_sheet.work_type inner join cw_branch on cw_branch.prime_branch_id=cw_time_sheet.branch inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id=cw_time_sheet.diagram_no inner join cw_employees on cw_employees.employee_code=cw_time_sheet_time_line.emp_code where cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 5 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 order by cw_time_sheet_time_line.trans_created_date';
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
		if((int)$process_by === 1){
			$i =3;
				$test[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
				$test[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Credit','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','PCO','QTY','HOURS','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');

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
				for ($x = 0; $x <= 27; $x++) {
					$excel_column  = $test[0]['excel_column'][$x];
					$excel_value   = $test[1]['excel_value'][$x];
					$obj->getActiveSheet()->setCellValue('A'."1", "Detailer Name:".$employee_name)->mergeCells('A1:B1')->getStyle('A1:B1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('C'."1", "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('C1:D1')->getStyle('C1:D1')->applyFromArray($styleArray);
					// $obj->getActiveSheet()->setCellValue('D'."1", "")->getStyle('D')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('E'."1", "Target Tons")->getStyle('E1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('F'."1", "Credit")->getStyle('F1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('G'."1", "Detailing Work")->mergeCells('G1:L1')->getStyle('G1:L1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('M'."1", "Revision Work")->mergeCells('M1:T1')->getStyle('M1:T1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('U'."1", "BAR LIST")->mergeCells('U1:V1')->getStyle('U1:V1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('W'."1", "OTHER WORKS")->getStyle('W1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('X'."1", "Booking Hours")->getStyle('X1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('Y'."1", "OFFICE HOURS")->mergeCells('Y1:AA1')->getStyle('Y1:AA1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('AB'."1", " ")->getStyle('AB1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
				}
			$previous_date = "";
			$j = 0;
			$k = 0;
			
			foreach($time_sheet_result as $key => $time_sheet){
				$sum_total_hours = array();
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

				$time_sheet_value['A']       = date('d-m-Y',strtotime($time_sheet->trans_created_date));
				$time_sheet_value['B']       = $time_sheet->project_name;
				$time_sheet_value['C']       = $time_sheet->drawing_no;
				$time_sheet_value['D']       = $time_sheet->cw_zct_5_value;
				$time_sheet_value['E']       = $time_sheet->work_status;
				$time_sheet_value['F'] 		 = 'Credit';
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
				$time_sheet_value['Y'] 		 = $in_hour;
				$time_sheet_value['Z'] 		 = $out_hour;
				$time_sheet_value['AA'] 	 = $differenceinhours;
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
				$sum_total_hours[] 			 = $total_hours;
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
				$sum_value_total_hours 		 = $this->AddPlayTime($sum_total_hours);
				for ($x = 0; $x <= 27; $x++) {
					$excel_column  		= $test[0]['excel_column'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];
					$start_cell 		= $excel_column.$range_start;
					$end_cell 			= $excel_column.$range_end;
					if($excel_column === 'A' || $excel_column === 'X' || $excel_column === 'Y' || $excel_column === 'Z' || $excel_column === 'AA'){
						$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
					}
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel);

					$counter = $i;
				}
				$i++;
				$previous_date = $date_only;
			}	
			$counter = $counter+1;
				$obj->getActiveSheet()->setCellValue('G'.$counter,$sum_value_study)->getStyle('G'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('H'.$counter,$sum_value_detailing_time)->getStyle('H'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('I'.$counter,$sum_value_discussion)->getStyle('I'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('J'.$counter,$sum_value_checking)->getStyle('J'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('k'.$counter,$sum_value_correction_time)->getStyle('K'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('L'.$counter,$sum_value_rfi)->getStyle('L'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('M'.$counter,$sum_value_study)->getStyle('M'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('N'.$counter,$sum_value_aec)->getStyle('N'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('O'.$counter,$sum_value_checking)->getStyle('O'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('P'.$counter,$sum_value_correction_time)->getStyle('P'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('Q'.$counter,$sum_value_non_billable_hours)->getStyle('Q'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('R'.$counter,$sum_value_billable_hours)->getStyle('R'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('S'.$counter,$sum_value_discussion)->getStyle('S'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('T'.$counter,$sum_value_change_order_time)->getStyle('T'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('U'.$counter,$sum_value_bar_list_quantity)->getStyle('U'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('V'.$counter,$sum_value_bar_listing_time)->getStyle('V'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('W'.$counter,$sum_value_other_works)->getStyle('W'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('X'.$counter,$sum_value_total_hours)->getStyle('X'.$counter)->applyFromArray($styleArray);
			// Rename worksheet name
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
		}else{
			$project_wise[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X');
			$project_wise[]['excel_value']= array('Job Category','Project Name','# New dwg','# Rev dwg','Remarks','Credits','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','CO','QTY','HOURS','OTHER WORK','TOTAL');
			$previous_project_name = "";
			$j = 0;
			$k = 0;
			// $sum_study = 0;
			foreach($time_sheet_result as $key => $time_sheet){
				$booking_hours 			= array();
				$trans_date      		= $time_sheet->trans_created_date;
				$project_name      		= $time_sheet->project_name;
				$study                  = $time_sheet->study;
				$project_id 			= $time_sheet->prime_project_and_drawing_master_id;

				if($previous_project_name === $project_name){
					$sum_study[]     		= $time_sheet->study;
					$hours_difference   = $this->AddPlayTime($sum_study);
					$j ++;
				}else{
					$sum_study[]     		= $time_sheet->study;
					$hours_difference   = $this->AddPlayTime($sum_study);
					$k = $i;
					$j = 0;
				}
				
				
			
				
				
				$i++;
				$previous_project_name = $project_name;
			}
			die;
		}
	}
}
?>