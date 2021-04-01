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
		if((int)$process_by === 1){
			$time_sheet_qry 	= 'select other_works,cw_time_sheet_time_line.trans_created_date,project,cw_project_and_drawing_master_drawings.drawing_no,detailing_time,study,discussion,checking,correction_time,rfi,aec,billable_hours,non_billable_hours,change_order_time,bar_listing_time,bar_list_quantity,project_name,cw_client.client_name,cw_zct_5.cw_zct_5_value,work_type,cw_branch.branch,cw_work_status.work_status,cw_employees.emp_name,cw_project_and_drawing_master.prime_project_and_drawing_master_id from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id=cw_time_sheet.project inner join cw_client on cw_client.prime_client_id=cw_time_sheet.client_name inner join cw_work_status on cw_work_status.prime_work_status_id=cw_time_sheet.work_status inner join cw_zct_5 on cw_zct_5.cw_zct_5_id=cw_time_sheet.work_type inner join cw_branch on cw_branch.prime_branch_id=cw_time_sheet.branch inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id=cw_time_sheet.diagram_no inner join cw_employees on cw_employees.employee_code=cw_time_sheet_time_line.emp_code where cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 5 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 order by cw_time_sheet_time_line.trans_created_date';
		}else{
			$time_sheet_qry 	= 'select  GROUP_CONCAT(work_description) AS work_description,emp_name,COUNT(revision_time) AS revision_time_count,COUNT(detailing_time) AS detailing_time_count, SEC_TO_TIME(SUM(TIME_TO_SEC(detailing_time))) AS detailing_time,SEC_TO_TIME(SUM(TIME_TO_SEC(other_works))) AS other_works,SEC_TO_TIME(SUM(TIME_TO_SEC(study))) AS study,SEC_TO_TIME(SUM(TIME_TO_SEC(discussion))) AS discussion,SEC_TO_TIME(SUM(TIME_TO_SEC(checking))) AS checking,SEC_TO_TIME(SUM(TIME_TO_SEC(correction_time))) AS correction_time,SEC_TO_TIME(SUM(TIME_TO_SEC(rfi))) AS rfi,SEC_TO_TIME(SUM(TIME_TO_SEC(aec))) AS aec,SEC_TO_TIME(SUM(TIME_TO_SEC(billable_hours))) AS billable_hours,SEC_TO_TIME(SUM(TIME_TO_SEC(non_billable_hours))) AS non_billable_hours,SEC_TO_TIME(SUM(TIME_TO_SEC(change_order_time))) AS change_order_time,SEC_TO_TIME(SUM(TIME_TO_SEC(bar_listing_time))) AS bar_listing_time,SEC_TO_TIME(SUM(TIME_TO_SEC(bar_list_quantity))) AS bar_list_quantity,project_name AS project_name,cw_time_sheet.project from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id=cw_time_sheet.project inner join cw_client on cw_client.prime_client_id=cw_time_sheet.client_name inner join cw_work_status on cw_work_status.prime_work_status_id=cw_time_sheet.work_status inner join cw_zct_5 on cw_zct_5.cw_zct_5_id=cw_time_sheet.work_type inner join cw_branch on cw_branch.prime_branch_id=cw_time_sheet.branch inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id=cw_time_sheet.diagram_no inner join cw_employees on cw_employees.employee_code=cw_time_sheet_time_line.emp_code where cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 5 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet.project order by cw_time_sheet.project';


			$detail_count_query  = 'select count(detailing_time) as detailing_count,work_type,detailing_time,project from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id where cw_time_sheet.work_type=1 and cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 5 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 GROUP BY cw_time_sheet.project order by cw_time_sheet.project';
			$detail_count_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detail_count_query')");
			$detail_count_result  = $detail_count_info->result();
			$detail_count_info->next_result();
			$detailing_count =array();
			foreach ($detail_count_result as $key => $detail_count) {
				$project = $detail_count->project;
				$detailing_count['detail_count'][$project] = $detail_count->detailing_count;
			}


			$revision_count_query  = 'select count(detailing_time) as revision_count,work_type,detailing_time,project from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id=cw_time_sheet.prime_time_sheet_id where cw_time_sheet.work_type=2 and cw_time_sheet_time_line.emp_code = "'.$employee_code.'" and emp_role = 5 and cw_time_sheet_time_line.trans_created_date >= "'.$from_date.'" and cw_time_sheet_time_line.trans_created_date <= "'.$to_date.'" and cw_time_sheet.trans_status = 1 GROUP BY cw_time_sheet.project order by cw_time_sheet.project';
			$revision_count_info   	= $this->db->query("CALL sp_a_run ('SELECT','$revision_count_query')");
			$revision_count_result  = $revision_count_info->result();
			$revision_count_info->next_result();
			$count_revision_time =array();
			foreach ($revision_count_result as $key => $rev_count) {
				$project = $rev_count->project;
				$count_revision_time['revision_count'][$project] = $rev_count->revision_count;
			}
			
		}
		$time_sheet_info   	= $this->db->query("CALL sp_a_run ('SELECT','$time_sheet_qry')");
		$time_sheet_result  = $time_sheet_info->result();
		$time_sheet_info->next_result();
		$employee_name = $time_sheet_result[0]->emp_name;
		$punched_qry    = 'select timediff(out_hour, in_hour) as times_total,in_hour,out_hour,entry_date,employee_code from cw_punched_data_details where employee_code ="'.$employee_code.'" and trans_status = 1';
		$punched_info   = $this->db->query("CALL sp_a_run ('SELECT','$punched_qry')");
		$punched_result = $punched_info->result();
		$punched_info->next_result();

		$time_count_only	= 'select SEC_TO_TIME(SUM(TIME_TO_SEC(timediff(out_hour, in_hour)))) total_hours,in_hour,out_hour,entry_date,employee_code from cw_punched_data_details where employee_code ="'.$employee_code.'" and trans_status = 1';
		$time_count_info   = $this->db->query("CALL sp_a_run ('SELECT','$time_count_only')");
		$time_count_result = $time_count_info->result();
		$time_count_info->next_result();
		$total_timing	   = date('H:i' ,strtotime($time_count_result[0]->total_hours));

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
			$i =4;
				$test[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
				$test[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Credit','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','PCO','QTY','HOURS','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');

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
				for ($x = 0; $x <= 27; $x++) {
					$excel_column  = $test[0]['excel_column'][$x];
					$excel_value   = $test[1]['excel_value'][$x];
					$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR".date('d-m-Y',strtotime($from_date))."-".date('d-m-Y',strtotime($to_date)))->mergeCells('A1:AB1')->getStyle('A1:AB1')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('A'."2", "Detailer Name:".$employee_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($styleArray);
					$obj->getActiveSheet()->setCellValue('C'."2", "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('C2:D2')->getStyle('C2:D2')->applyFromArray($styleArray);
					// $obj->getActiveSheet()->setCellValue('D'."1", "")->getStyle('D')->applyFromArray($styleArray);
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
			$previous_date = "";
			$j = 0;
			$k = 0;
			foreach($time_sheet_result as $key => $time_sheet){
				$sum_value_total_hours = array();
				$sum_total_hours = array();
				$booking_hours 			= array();
				$trans_date      		= $time_sheet->trans_created_date;
				$date_only = date('Y-m-d',strtotime($trans_date));
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
				$time_difference 	    = $map_result[$employee_code]['entry_date'][$trans_date_only]['times_total'];
				if($check_entry_date){
					$in_hour  			= $in_hour;
					$out_hour 			= $out_hour;
					$hours_difference   = $this->differenceInHours($in_hour,$out_hour);
					$differenceinhours  = number_format($hours_difference,2);

				}else{
					$in_hour  			= "";
					$out_hour 			= "";
				}

				
				// $sum_value_total_hours_final[]	= $this->AddPlayTime($sum_value_total_hours);
				// $sum_value_total_hours	= $this->AddPlayTime($sum_value_total_hours_final);
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
				$time_sheet_value['AA'] 	 = $time_difference;
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
				
				
				for ($x = 0; $x <= 27; $x++) {
					$excel_column  		= $test[0]['excel_column'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];
					$start_cell 		= $excel_column.$range_start;
					$end_cell 			= $excel_column.$range_end;
					if($excel_column === 'A' || $excel_column === 'Y' || $excel_column === 'Z' || $excel_column === 'AA'){
						$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
					}
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);

					$counter = $i;
				}
				$i++;
				$previous_date = $date_only;
			}	
			$counter = $counter+1;
			$obj->getActiveSheet()->setCellValue('A'.$counter, $total_sum_detail_work)->mergeCells('A'.$counter.':'.'F'.$counter)->getStyle('A'.$counter.':'.'F'.$counter)->applyFromArray($styleArray);
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
				$obj->getActiveSheet()->setCellValue('Y'.$counter,"")->getStyle('Y'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('Z'.$counter,"")->getStyle('Z'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('AA'.$counter,$total_timing)->getStyle('AA'.$counter)->applyFromArray($styleArray);
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
		    $valueStyle  = array(
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

		    for ($x = 0; $x <= 23; $x++) {
				$excel_column  = $project_wise[0]['excel_column'][$x];
				$excel_value   = $project_wise[1]['excel_value'][$x];
				$obj->getActiveSheet()->setCellValue('A'."1", "Detailer Name:".$employee_name)->mergeCells('A1:D1')->getStyle('A1:D1')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('E'."2", "Period : 1st to 31st January")->getStyle('E2')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('A'."2", "Designation & Experience: Cad Designer & 3 Year 7 Months")->mergeCells('A2:D2')->getStyle('A2:D2')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('E'."2", " ")->getStyle('E2')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('A'."3", "Working Days")->getStyle('A3')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('B'."3", "Min. Standard Working Hours")->getStyle('B3')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('C'."3", "Target Tons")->getStyle('C3')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('D'."3", "Min Tons/Hrs")->getStyle('D3')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('E'."3", " ")->getStyle('E3')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('A'."4", "21")->getStyle('A4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('B'."4", "168")->getStyle('B4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('C'."4", "100")->getStyle('C4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('D'."4", "0.06")->getStyle('D4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('E'."4", "Team: CT10 Dhanalakshmi")->getStyle('E4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('F'."4", "Credits")->getStyle('F4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('G'."4", "Detailing Work")->mergeCells('G4:K4')->getStyle('G4:K4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('L'."4", " ")->getStyle('L4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('M'."4", "Revision Work")->mergeCells('M4:T4')->getStyle('M4:T4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('U'."4", "BAR LIST")->mergeCells('U4:V4')->getStyle('U4:V4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('W'."4", "OTHER WORKS")->getStyle('W4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('X'."4", "BOOKING WORKS")->getStyle('X4')->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue($excel_column."5", $excel_value)->getStyle($excel_column.'5')->applyFromArray($styleArray);
			}

			$previous_project_id = "";
			$sum_revision_time_count  = 0;
			$sum_detailing_time_count = 0;
			$i = 6;
			foreach($time_sheet_result as $key => $time_sheet){
				$sum_value_total_hours  = array();
				$project 				= $time_sheet->project;
				$revision_time_count 	= $time_sheet->revision_time_count;
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

				$time_sheet_value['A']       = "";
				$time_sheet_value['B']       = $time_sheet->project_name;
				if(array_key_exists($project, $detailing_count['detail_count'])){
					$time_sheet_value['C'] ="";
					foreach ($detailing_count as $key => $count_detail) {
						$projectwise_detail = $count_detail[$project];
						$time_sheet_value['C']       = $projectwise_detail;
					}
				}else{
					$projectwise_detail = 0;
					$time_sheet_value['C']       = "";
				}

				if(array_key_exists($project, $count_revision_time['revision_count'])){
					$time_sheet_value['D'] ="";
					foreach ($count_revision_time as $key => $count_revision) {
						$projectwise_revision = $count_revision[$project];
						$time_sheet_value['D']       = $projectwise_revision;
					}
				}else{
					$projectwise_revision = 0;
					$time_sheet_value['D']       = "";
				}

				$time_sheet_value['E']       = $time_sheet->work_description;
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
				$sum_revision_time_count 	+= $projectwise_revision;
				$sum_detailing_time_count   += $projectwise_detail;
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
				// $sum_value_total_hours 		 = $this->AddPlayTime($sum_total_hours);

				for ($x = 0; $x <= 23; $x++) {
					$excel_column  		= $project_wise[0]['excel_column'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($valueStyle);
					$counter = $i;
				}
				$i++;
			}
			$sum_detail_work[]			= $sum_value_study;
			$sum_detail_work[]	 		= $sum_value_detailing_time;
			$sum_detail_work[]		    = $sum_value_discussion;
			$sum_detail_work[]			= $sum_value_checking;
			$sum_detail_work[]	 		= $sum_value_correction_time;
			$total_sum_detail_work 		= $this->AddPlayTime($sum_detail_work);
			$sum_revision_work[]		= $sum_value_study;
			$sum_revision_work[] 		= $time_sheet->aec;
			$sum_revision_work[]		= $sum_value_checking;
			$sum_revision_work[]	 	= $sum_value_correction_time;
			$sum_revision_work[] 	 	= $time_sheet->non_billable_hours;
			$sum_revision_work[] 		= $time_sheet->billable_hours;
			$sum_revision_work[]		= $sum_value_discussion;
			$total_sum_revision_work 	= $this->AddPlayTime($sum_revision_work);
			$counter_count  			= $counter;
			$counter 					= $counter+1;
			$second_counter 			= $counter+1;
			if((int)$counter_count !== 0){
				$obj->getActiveSheet()->setCellValue('A'.$counter,"")->getStyle('A'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('B'.$counter,"")->getStyle('B'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('C'.$counter,$sum_detailing_time_count)->getStyle('C'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('D'.$counter,$sum_revision_time_count)->getStyle('D'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('E'.$counter,"")->getStyle('E'.$counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('F'.$counter,"")->getStyle('F'.$counter)->applyFromArray($styleArray);
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
				$obj->getActiveSheet()->setCellValue('A'.$second_counter, "")->mergeCells('A'.$second_counter.':'.'F'.$second_counter)->getStyle('A'.$second_counter.':'.'F'.$second_counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('G'.$second_counter, $total_sum_detail_work)->mergeCells('G'.$second_counter.':'.'K'.$second_counter)->getStyle('G'.$second_counter.':'.'K'.$second_counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('L'.$second_counter," ")->getStyle('L'.$second_counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('M'.$second_counter, $total_sum_revision_work)->mergeCells('M'.$second_counter.':'.'S'.$second_counter)->getStyle('M'.$second_counter.':'.'S'.$second_counter)->applyFromArray($styleArray);
				$obj->getActiveSheet()->setCellValue('T'.$second_counter, "")->mergeCells('T'.$second_counter.':'.'X'.$second_counter)->getStyle('T'.$second_counter.':'.'X'.$second_counter)->applyFromArray($styleArray);
			}
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
}
?>