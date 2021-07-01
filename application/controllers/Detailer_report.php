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
	public function excel_export($employee_code,$process_month){
		$emp_team_qry 			= 'select team,date_of_joining from cw_employees where employee_code = "'.$employee_code.'" and trans_status = 1';
		$emp_team_info   		= $this->db->query("CALL sp_a_run ('SELECT','$emp_team_qry')");
		$emp_team_result 		= $emp_team_info->result();
		$emp_team_info->next_result();
		$team_id 				= $emp_team_result[0]->team;
		$date_of_joining 		= $emp_team_result[0]->date_of_joining;
		$current_date 			= date('Y-m-d');
		$diff 					= abs(strtotime($current_date) - strtotime($date_of_joining));
		$years 					= floor($diff / (365*60*60*24));
		$months 				= floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days 					= floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		$calculate_date_month 	= $years." Years,".$months." Months";

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

		$target_qry     	= 'select cw_team_target_detailer_wise_target.target_value from cw_team_target inner join cw_team_target_detailer_wise_target on cw_team_target_detailer_wise_target.prime_team_target_id = cw_team_target.prime_team_target_id where cw_team_target.from_date <= "'.$process_month.'" and cw_team_target.to_date >= "'.$process_month.'" and cw_team_target_detailer_wise_target.detailer_name = "'.$employee_code.'" and cw_team_target.trans_status = 1';
		$target_info    	= $this->db->query("CALL sp_a_run ('SELECT','$target_qry')");
		$target_result  	= $target_info->result();
		$target_info->next_result();
		$credit_target  	= $target_result[0]->target_value;
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

		$drawing_qry 		= 'select prime_project_and_drawing_master_drawings_id,drawing_no from cw_project_and_drawing_master_drawings where trans_status = 1';
		$drawing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$drawing_qry')");
		$drawing_result 	= $drawing_info->result_array();
		$drawing_info->next_result();
		$drawing_result = array_reduce($drawing_result, function($result, $arr){			
		    $result[$arr['prime_project_and_drawing_master_drawings_id']] = $arr;
		    return $result;
		}, array());

		$work_status_qry 		= 'select prime_work_status_id,work_status from cw_work_status where trans_status = 1';
		$work_status_info   	= $this->db->query("CALL sp_a_run ('SELECT','$work_status_qry')");
		$work_status_result 	= $work_status_info->result_array();
		$work_status_info->next_result();
		$work_status_result = array_reduce($work_status_result, function($result, $arr){			
		    $result[$arr['prime_work_status_id']] = $arr;
		    return $result;
		}, array());

		$other_work_qry 			= 'select count(*) as work_result_count,cw_time_sheet_time_line.work_type,work_description,cw_other_works.other_works,IF(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works))),"%H:%i"),"") as cummulate_works,other_work_name,IF(SEC_TO_TIME( SUM(time_to_sec(credit)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(credit))),"%H:%i"),"") as cummulate_credit from cw_time_sheet_time_line inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_other_works on cw_other_works.prime_other_works_id = cw_time_sheet_time_line.other_work_name where cw_time_sheet.employee_code = "'.$employee_code.'" and work_type = 4 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.other_work_name order by cw_time_sheet_time_line.other_work_name';
		$other_work_info   			= $this->db->query("CALL sp_a_run ('SELECT','$other_work_qry')");
		$other_work_result 			= $other_work_info->result();
		$other_work_info->next_result();
		$work_result_count			= $other_work_result[0]->work_result_count;

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

	    $doubleColumnStyle  = array(
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
	            // 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $doubleColumnStyleRight  = array(
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
	            // 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );

	    /* FIRST WORK SHEET */


	    $excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB');
		$excel[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revision Status','Work Status','Credit','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','CO','QTY','HOURS','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');
	    for ($x = 0; $x <= 27; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR ".strtoupper($month_name))->mergeCells('A1:AB1')->getStyle('A1:AB1')->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'."2", "Detailer Name:".$emp_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('C'."2", "Designation & Experience: ".$calculate_date_month)->mergeCells('C2:D2')->getStyle('C2:D2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'."2", "Target Tons")->getStyle('E2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."2", $credit_target)->getStyle('F2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."2", "Detailing Work")->mergeCells('G2:L2')->getStyle('G2:L2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."2", "Revision Work")->mergeCells('M2:T2')->getStyle('M2:T2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."2", "BAR LIST")->mergeCells('U2:V2')->getStyle('U2:V2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'."2", "OTHER WORKS")->getStyle('W2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'."2", "Booking Hours")->getStyle('X2')->applyFromArray($doubleColumnStyle);
			$obj->getActiveSheet()->setCellValue('Y'."2", "OFFICE HOURS")->mergeCells('Y2:AA2')->getStyle('Y2:AA2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('AB'."2", " ")->getStyle('AB2')->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($LeftArray);
			}else
			if($excel_column === 'AB'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($RightArray);
			}else
			if($excel_column === 'W'){
				$obj->getActiveSheet()->setCellValue('W'."2", $excel_value)->mergeCells('W2:'.$excel_column.'3')->getStyle('W2:'.$excel_column.'3')->applyFromArray($doubleColumnStyle);
			}else
			if($excel_column === 'X'){
				$obj->getActiveSheet()->setCellValue('X'."2", $excel_value)->mergeCells('X2:'.$excel_column.'3')->getStyle('X2:'.$excel_column.'3')->applyFromArray($doubleColumnStyle);
			}
			else{
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($styleArray);
			}
		}
		
		$i = 4;
		$j = 0;
		$k = 0;
		$previous_date = "";
		$sum_value_bar_list_quantity  = 0;
		$time_total = array();
		foreach($detailer_result as $key => $time_sheet){
			$sum_value_total_hours  = array();
			$booking_hours 	 		= array();
			$work_type_time  		= $time_sheet->work_type_time;
			$sum_value_bar_list_quantity  	+= $time_sheet->bar_list_quantity;
			if((int)$work_type_time === 1){
				$detailing_time 	= $time_sheet->detailing_time;
				$study1 			= $time_sheet->study;
				$checking1 			= $time_sheet->checking;
				$correction_time1 	= $time_sheet->correction_time;
				$discussion1 	 	= $time_sheet->discussion;
				$study2 			= "";
				$checking2 			= "";
				$correction_time2 	= "";
				$discussion2  		= "";
				$aec 				= "";
				$rfi 				= "";
				$non_billable_hours = "";
				$billable_hours 	= "";
				$change_order_time 	= "";
				$other_works 		= "";
				$bar_listing_time 	= "";
			}else
			if((int)$work_type_time === 2){
				$detailing_time 	= "";
				$study2 			= $time_sheet->study;
				$checking2 			= $time_sheet->checking;
				$correction_time2 	= $time_sheet->correction_time;
				$discussion2  		= $time_sheet->discussion;
				$study1 			= "";
				$checking1 			= "";
				$correction_time1 	= "";
				$discussion1 	 	= "";
				$aec 				= $time_sheet->aec;
				$rfi 				= $time_sheet->rfi;
				$non_billable_hours = $time_sheet->non_billable_hours;
				$billable_hours 	= $time_sheet->billable_hours;
				$change_order_time 	= $time_sheet->change_order_time;
				$bar_listing_time 	= "";
				$other_works 		= "";
			}else{
				$detailing_time 	= "";
				$study1 			= "";
				$study2 			= "";
				$checking1 			= "";
				$checking2 			= "";
				$correction_time1 	= "";
				$correction_time2 	= "";
				$discussion1  		= "";
				$discussion2  		= "";
				$aec 				= "";
				$rfi 				= "";
				$non_billable_hours = "";
				$billable_hours 	= "";
				$change_order_time 	= "";
				$bar_listing_time 	= $time_sheet->bar_listing_time;
				$other_works 		= $time_sheet->other_works;
			}
			$booking_hours[] = $study1;
			$booking_hours[] = $time_sheet->detailing_time;
			$booking_hours[] = $discussion1;
			$booking_hours[] = $checking1;
			$booking_hours[] = $correction_time1;
			$booking_hours[] = $study2;
			$booking_hours[] = $aec;
			$booking_hours[] = $checking2;
			$booking_hours[] = $correction_time2;
			$booking_hours[] = $non_billable_hours;
			$booking_hours[] = $billable_hours;
			$booking_hours[] = $discussion2;
			$booking_hours[] = $change_order_time;
			$booking_hours[] = $bar_listing_time;
			$booking_hours[] = $other_works;
			$booking_hours[] = $rfi;
			$booking_hours[] = $time_sheet->credit;
			$total_hours 	 = $this->AddPlayTime($booking_hours);
			$sum_total_hours[] 			 = $total_hours;
			$sum_value_total_hours 		 = $this->AddPlayTime($sum_total_hours);

			$entry_date      		= $time_sheet->entry_date;
			$date_only = date('Y-m-d',strtotime($entry_date));
			if($previous_date === $date_only){
				$j ++;
			}else{
				$time_total[] = $time_sheet->total_time;
				$k = $i;
				$j = 0;
			}

			$range_start 	= $k;
			$range_end 		= $i;
			$projects_id 	= $time_sheet->project;
			$project 		= $project_result[$projects_id]['project_name'];
			$drawing_no 	= $time_sheet->drawing_no;
			$drawing_no 	= $drawing_result[$drawing_no]['drawing_no'];
			$work_status 	= $time_sheet->work_status;
			$work_status 	= $work_status_result[$work_status]['work_status'];
			$time_sheet_value['A']       = $time_sheet->entry_date;
			if($project){
				$time_sheet_value['B']       = $project;
			}else{
				foreach ($other_work_result as $key => $other_work_only) {
					$time_sheet_value['B']       = $other_work_only->other_works;
				}
			}

			if($drawing_no){
				$all_cummulate[$projects_id][$work_type_time][] = $drawing_no;
			}
			
			$time_sheet_value['C']       = $drawing_no;
			$time_sheet_value['D']       = $work_status;
			$time_sheet_value['E']       = $time_sheet->work_description;
			$time_sheet_value['F'] 		 = $time_sheet->credit;
			$time_sheet_value['G'] 		 = $study1;
			$time_sheet_value['H'] 		 = $time_sheet->detailing_time;
			$time_sheet_value['I'] 		 = $discussion1;
			$time_sheet_value['J']		 = $checking1;
			$time_sheet_value['K'] 		 = $correction_time1;
			$time_sheet_value['L'] 		 = $rfi;
			$time_sheet_value['M']		 = $study2;
			$time_sheet_value['N']		 = $aec;
			$time_sheet_value['O']		 = $checking2;
			$time_sheet_value['P'] 		 = $correction_time2;
			$time_sheet_value['Q'] 		 = $non_billable_hours;
			$time_sheet_value['R'] 		 = $billable_hours;
			$time_sheet_value['S'] 		 = $discussion2;
			$time_sheet_value['T'] 		 = $change_order_time;
			$time_sheet_value['U']       = $time_sheet->bar_list_quantity;
			$time_sheet_value['V'] 		 = $bar_listing_time;
			$time_sheet_value['W'] 		 = $other_works;
			$time_sheet_value['X'] 		 = $total_hours;
			$time_sheet_value['Y'] 		 = $time_sheet->in_time;
			$time_sheet_value['Z'] 		 = $time_sheet->out_time;
			$time_sheet_value['AA'] 	 = $time_sheet->total_time;
			$time_sheet_value['AB'] 	 = "shift";

			$sum_study1[]  				 = $study1;
			$sum_study2[]  				 = $study2;
			$sum_detailing_time1[]  	 = $time_sheet->detailing_time;
			$sum_detailing_time2[]  	 = $time_sheet->detailing_time;
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
			$sum_value_detailing_time1	 = $this->AddPlayTime($sum_detailing_time1);
			$sum_value_detailing_time2	 = $this->AddPlayTime($sum_detailing_time2);
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
		
		$total_time_date_wise			 = $this->AddPlayTime($time_total);
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
		$obj->getActiveSheet()->setCellValue('AA'.$counter,$total_time_date_wise)->getStyle('AA'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('AB'.$counter,"")->getStyle('AB'.$counter)->applyFromArray($FooterRightStyle);


		/* SECOND WORK SHEET */


		$cummulative_sheet2 = $counter+3;
		$cummulative_sheet3 = $counter+4;
		$cummulative_sheet4 = $counter+5;
		$cummulative_sheet5 = $counter+6;
		$cummulative_sheet6 = $counter+7;
		$cummulative_detail_count = $counter+8;
		// echo $cummulative_sheet6;die;

		$project_wise_excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X');
		$project_wise_excel[]['excel_value']= array('Job Category','Project Name','# New dwg','# Rev dwg','Remarks','Credits','STY','DET','DIS','CHK','COR','RFI','STY','AEC','CHK','COR','NBH','BH','DIS','CO','QTY','HOURS','OTHER WORK','TOTAL');

		$process_months  			= '01-'.$process_month;
		$process_months  			= date('Y-m',strtotime($process_months));
		$working_days_qry 			= 'SELECT count(*) as working_days,SEC_TO_TIME(SUM(TIME_TO_SEC(total_time))) as total_time FROM cw_time_sheet where entry_date like "%'.$process_months.'%" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$working_days_info   		= $this->db->query("CALL sp_a_run ('SELECT','$working_days_qry')");
		$working_days_result 		= $working_days_info->result();
		$working_days_info->next_result();
		$working_days_cummlate 		= $working_days_result[0]->working_days;
		$min_std_work_cummlate 		= $working_days_cummlate * 8;
		$min_ton_cummlate 			= $credit_target/$min_std_work_cummlate;
		$min_ton_cummlate 			= round($min_ton_cummlate,2);

		for ($x = 0; $x <= 23; $x++) {
			$excel_column  = $project_wise_excel[0]['excel_column'][$x];
			$excel_value   = $project_wise_excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet2, "Detailer Name: ".$emp_name)->mergeCells('A'.$cummulative_sheet2.':X'.$cummulative_sheet2)->getStyle('A'.$cummulative_sheet2.':X'.$cummulative_sheet2)->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet3, "Designation & Experience: ".$calculate_date_month)->mergeCells('A'.$cummulative_sheet3.':X'.$cummulative_sheet3)->getStyle('A'.$cummulative_sheet3.':X'.$cummulative_sheet3)->applyFromArray($LeftrightBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet4, "Working Days")->getStyle('A'.$cummulative_sheet4)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet4, "Min. Standard Working Hours")->getStyle('B'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet4, "Target Tons")->getStyle('C'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet4, "Min Tons/Hrs")->getStyle('D'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet4, "")->mergeCells('E'.$cummulative_sheet4.':X'.$cummulative_sheet4)->getStyle('E'.$cummulative_sheet4.':X'.$cummulative_sheet4)->applyFromArray($RightBorderHead);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet5, $working_days_cummlate)->getStyle('A'.$cummulative_sheet5)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet5, $min_std_work_cummlate)->getStyle('B'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet5, $credit_target)->getStyle('C'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet5, $min_ton_cummlate)->getStyle('D'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet5, "Team:".$team_name.", ".$emp_name)->getStyle('E'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, "Credit")->getStyle('F'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'.$cummulative_sheet5, "Detailing Work")->mergeCells('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->getStyle('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'.$cummulative_sheet5, "")->getStyle('L'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'.$cummulative_sheet5, "Revision Work")->mergeCells('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->getStyle('M'.$cummulative_sheet5.':T'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'.$cummulative_sheet5, "Revision Work")->mergeCells('U'.$cummulative_sheet5.':V'.$cummulative_sheet5)->getStyle('U'.$cummulative_sheet5.':V'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('W'.$cummulative_sheet5, "OTHER WORKS")->getStyle('W'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('X'.$cummulative_sheet5, "TOTAL")->getStyle('X'.$cummulative_sheet5)->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($LeftArray);
			}else
			if($excel_column === 'X'){
				$obj->getActiveSheet()->setCellValue('X'.$cummulative_sheet5, $excel_value)->mergeCells('X'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('X'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyleRight);
			}else
			if($excel_column === 'F'){
				$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, $excel_value)->mergeCells('F'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('F'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyle);
			}else
			if($excel_column === 'W'){
				$obj->getActiveSheet()->setCellValue('W'.$cummulative_sheet5, $excel_value)->mergeCells('W'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('F'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyle);
			}
			else{
			$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($styleArray);
			}
		}

		$project_wise_qry 			= 'SELECT work_status,count(*) as project_wise_count,count(*) as count_project_wise,count(cw_time_sheet_time_line.work_type) as work_type_count,cw_time_sheet_time_line.project,cw_project_and_drawing_master.project_name,cw_time_sheet_time_line.work_description,IF(SEC_TO_TIME( SUM(time_to_sec(credit)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(credit))),"%H:%i"),"") as cummulate_credit,IF(SEC_TO_TIME( SUM(time_to_sec(study)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(study))),"%H:%i"),"") as cummulate_study,IF(SEC_TO_TIME( SUM(time_to_sec(detailing_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(detailing_time))),"%H:%i"),"") as cummulate_detailing_time,IF(SEC_TO_TIME( SUM(time_to_sec(discussion)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(discussion))),"%H:%i"),"") as cummulate_discussion,IF(SEC_TO_TIME( SUM(time_to_sec(checking)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(checking))),"%H:%i"),"") as cummulate_checking,IF(SEC_TO_TIME( SUM(time_to_sec(correction_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(correction_time))),"%H:%i"),"") as cummulate_correction_time,IF(SEC_TO_TIME( SUM(time_to_sec(rfi)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(rfi))),"%H:%i"),"") as cummulate_rfi,IF(SEC_TO_TIME( SUM(time_to_sec(aec)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(aec))),"%H:%i"),"") as cummulate_aec,IF(SEC_TO_TIME( SUM(time_to_sec(non_billable_hours)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(non_billable_hours))),"%H:%i"),"") as cummulate_non_billable_hours,IF(SEC_TO_TIME( SUM(time_to_sec(billable_hours)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(billable_hours))),"%H:%i"),"") as cummulate_billable_hours,IF(SEC_TO_TIME( SUM(time_to_sec(change_order_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(change_order_time))),"%H:%i"),"") as cummulate_change_order_time,IF(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time))),"%H:%i"),"") as cummulate_bar_listing_time,IF(SEC_TO_TIME( SUM(time_to_sec(other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(other_works))),"%H:%i"),"") as cummulate_other_works,sum(bar_list_quantity) as cummulate_bar_list_quantity,work_type,GROUP_CONCAT(work_description) as work_description,drawing_no FROM cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.project,work_type order by cw_time_sheet_time_line.work_type';

		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result_array();
		$project_wise_info->next_result();
		$project_wise_count 		= $project_wise_result[0]['project_wise_count'];

		$project_wise_result = array_reduce($project_wise_result, function($result, $arr){			
		    $result[$arr['project']][$arr['work_type']] = $arr;
		    return $result;
		}, array());


		$complete_pending_qry 			= 'SELECT count(*) as work_status_count,work_status as work_status,cw_time_sheet_time_line.project FROM cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by project,work_status';
		$complete_pending_info   		= $this->db->query("CALL sp_a_run ('SELECT','$complete_pending_qry')");
		$complete_pending_result 		= $complete_pending_info->result_array();
		$complete_pending_info->next_result();
		$complete_pending_result = array_reduce($complete_pending_result, function($result, $arr){			
		    $result[$arr['project']][$arr['work_status']] = $arr;
		    return $result;
		}, array());

		$get_work_type_qry 			= 'select prime_work_type_id from cw_work_type where trans_status = 1';
		$get_work_type_info   		= $this->db->query("CALL sp_a_run ('SELECT','$get_work_type_qry')");
		$get_work_type_result 		= $get_work_type_info->result();
		$get_work_type_info->next_result();

		$q = $cummulative_detail_count;
		$r = 0;
		$s = 0;
		$sum_new_detail_count 		= 0;
		$sum_new_rev_count 			= 0;
		$sum_value_bar_list_quantity_cummlate  = 0;


		$count_qry 		= 'select count(*) as count,project,cw_time_sheet_time_line.work_type,cw_time_sheet_time_line.drawing_no from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" and drawing_no !="" group by project,cw_time_sheet_time_line.work_type,cw_time_sheet_time_line.drawing_no';
		$count_info   		= $this->db->query("CALL sp_a_run ('SELECT','$count_qry')");
		$count_rslt 		= $count_info->result_array();
		$count_info->next_result();
		$count_rslt = array_reduce($count_rslt, function($result, $arr){			
		    $result[$arr['work_type']][$arr['project']][]= $arr;
		    return $result;
		}, array());


		if((int)$project_wise_count === 0){
			$cummuate_second_count  = $cummulative_detail_count;
		}else{
			$total_credit_project_wise 	= array();
			$total_detailing_count 		= 0;
			$total_revision_count 		= 0;
			$revision_count = 0;
			$detailing_count = 0;
			foreach($project_wise_result as $key => $cummulative_time_sheet){
				$credit_cummulate 		= array();
				foreach ($cummulative_time_sheet as $aa => $value) {
					$cummulate_credit1 	= array();
					$project_name = array();
					$cummulate_credit1[$key] = $value['cummulate_credit'];
					$project_name[$key] = $value['project_name'];
					$credit_cummulate[] 		= $cummulate_credit1[$key];
					$credit_project_wise 	    = $this->AddPlayTime($credit_cummulate);
					$project_name1 = $project_name[$key];
				}
				
				$cummulate_booking_hours = array();
					$work_type1 					= $cummulative_time_sheet[1];
					$work_type2 					= $cummulative_time_sheet[2];
					$work_type3 					= $cummulative_time_sheet[3];
					$drawing_no1 					= $work_type1['drawing_no'];
					$job_category1 					= $work_type1['job_category'];
					$work_description1 				= $work_type1['work_description'];
					$cummulate_study1 				= $work_type1['cummulate_study'];
					$cummulate_detailing_time1 		= $work_type1['cummulate_detailing_time'];
					$cummulate_discussion1 			= $work_type1['cummulate_discussion'];
					$cummulate_checking1 			= $work_type1['cummulate_checking'];
					$cummulate_correction_time1 	= $work_type1['cummulate_correction_time'];
					$drawing_no2 					= $work_type2['drawing_no'];
					// $detailing_count 				= $work_type1['count_project_wise'];
					$cummulate_study2 				= $work_type2['cummulate_study'];
					$cummulate_rfi2 				= $work_type2['cummulate_rfi'];
					$cummulate_aec2 				= $work_type2['cummulate_aec'];
					$cummulate_checking2 			= $work_type2['cummulate_checking'];
					$cummulate_correction_time2 	= $work_type2['cummulate_correction_time'];
					$cummulate_non_billable_hours2 	= $work_type2['cummulate_non_billable_hours'];
					$cummulate_billable_hours2 		= $work_type2['cummulate_billable_hours'];
					$cummulate_discussion2 			= $work_type2['cummulate_discussion'];
					$cummulate_change_order_time2 	= $work_type2['cummulate_change_order_time'];
					$cummulate_credit2 				= $work_type2['cummulate_credit'];
					// $revision_count 				= $work_type2['count_project_wise'];
					$cummulate_bar_listing_time3 	= $work_type3['cummulate_bar_listing_time'];
					$cummulate_bar_list_quantity3 	= $work_type3['cummulate_bar_list_quantity'];
					$cummulate_credit3 				= $work_type3['cummulate_credit'];
					$sum_value_bar_list_quantity_cummlate  	+= $cummulate_bar_list_quantity3;
					$total_credit_project_wise[] 			 = $credit_project_wise; 
					$credit_total     						 = $this->AddPlayTime($total_credit_project_wise);
					$detailing_count1 		= $count_rslt[1][$key];
					$revision_count1 		= $count_rslt[2][$key];
					$detailing_count 		= count($detailing_count1);
					$revision_count 		= count($revision_count1);
					$total_detailing_count += $detailing_count;
					$total_revision_count  += $revision_count;

					$cummulate_booking_hours[] = $cummulate_study1;
					$cummulate_booking_hours[] = $cummulate_detailing_time1;
					$cummulate_booking_hours[] = $cummulate_discussion1;
					$cummulate_booking_hours[] = $cummulate_checking1;
					$cummulate_booking_hours[] = $cummulate_correction_time1;
					$cummulate_booking_hours[] = $cummulate_rfi2;
					$cummulate_booking_hours[] = $cummulate_study2;
					$cummulate_booking_hours[] = $cummulate_aec2;
					$cummulate_booking_hours[] = $cummulate_checking2;
					$cummulate_booking_hours[] = $cummulate_correction_time2;
					$cummulate_booking_hours[] = $cummulate_non_billable_hours2;
					$cummulate_booking_hours[] = $cummulate_billable_hours2;
					$cummulate_booking_hours[] = $cummulate_discussion2;
					$cummulate_booking_hours[] = $cummulate_change_order_time2;
					$cummulate_booking_hours[] = $cummulate_bar_listing_time3;
					$cummulate_booking_hours[]   = $value['cummulate_credit'];
					$cummulate_total_hours 	   = $this->AddPlayTime($cummulate_booking_hours);

					$pending_status_counting = $complete_pending_result[$key][1]['work_status_count'];
					$partial_status_counting = $complete_pending_result[$key][2]['work_status_count'];
					$complete_status_counting = $complete_pending_result[$key][3]['work_status_count'];
					if((int)$pending_status_counting ===0){
						$pending_status_counting = 0;
					}else{
						$pending_status_counting = $pending_status_counting;
					}
					if((int)$partial_status_counting ===0){
						$partial_status_counting = 0;
					}else{
						$partial_status_counting = $partial_status_counting;
					}
					if((int)$complete_status_counting ===0){
						$complete_status_counting = 0;
					}else{
						$complete_status_counting = $complete_status_counting;
					}
					$total_pending = $partial_status_counting + $pending_status_counting;
					$total_works_status = "(".$complete_status_counting.") Completed - (".$total_pending.") Inprogress";

					$time_sheet_value['A']       = $job_category1;
					$time_sheet_value['B']       = $project_name1;
					$time_sheet_value['C']       = $detailing_count;
					$time_sheet_value['D']       = $revision_count;
					$time_sheet_value['E']       = $total_works_status;
					$time_sheet_value['F'] 		 = $credit_project_wise;
					$time_sheet_value['G'] 		 = $cummulate_study1;
					$time_sheet_value['H'] 		 = $cummulate_detailing_time1;
					$time_sheet_value['I'] 		 = $cummulate_discussion1;
					$time_sheet_value['J']		 = $cummulate_checking1;
					$time_sheet_value['K'] 		 = $cummulate_correction_time1;
					$time_sheet_value['L'] 		 = $cummulate_rfi2;
					$time_sheet_value['M']		 = $cummulate_study2;
					$time_sheet_value['N']		 = $cummulate_aec2;
					$time_sheet_value['O']		 = $cummulate_checking2;
					$time_sheet_value['P'] 		 = $cummulate_correction_time2;
					$time_sheet_value['Q'] 		 = $cummulate_non_billable_hours2;
					$time_sheet_value['R'] 		 = $cummulate_billable_hours2;
					$time_sheet_value['S'] 		 = $cummulate_discussion2;
					$time_sheet_value['T'] 		 = $cummulate_change_order_time2;
					$time_sheet_value['U']       = $cummulate_bar_list_quantity3;
					$time_sheet_value['V'] 		 = $cummulate_bar_listing_time3;
					$time_sheet_value['W'] 		 = $cummulate_other_works;
					$time_sheet_value['X'] 		 = $cummulate_total_hours;

					$sum_cummulate_study1[]  				 	= $cummulate_study1;
					$sum_value_cummulate_study1			 		= $this->AddPlayTime($sum_cummulate_study1);
					$sum_cummulate_detailing_time1[]  			= $cummulate_detailing_time1;
					$sum_value_cummulate_detailing_time1		= $this->AddPlayTime($sum_cummulate_detailing_time1);
					$sum_cummulate_discussion1[]  				= $cummulate_discussion1;
					$sum_value_cummulate_discussion1			= $this->AddPlayTime($sum_cummulate_discussion1);
					$sum_cummulate_checking1[]  				= $cummulate_checking1;
					$sum_value_cummulate_checking1				= $this->AddPlayTime($sum_cummulate_checking1);
					$sum_cummulate_correction_time1[]  			= $cummulate_correction_time1;
					$sum_value_cummulate_correction_time1		= $this->AddPlayTime($sum_cummulate_correction_time1);
					$sum_cummulate_study2[]  				 	= $cummulate_study2;
					$sum_value_cummulate_study2			 		= $this->AddPlayTime($sum_cummulate_study2);
					$sum_cummulate_discussion2[]  				= $cummulate_discussion2;
					$sum_value_cummulate_discussion2			= $this->AddPlayTime($sum_cummulate_discussion2);
					$sum_cummulate_checking2[]  				= $cummulate_checking2;
					$sum_value_cummulate_checking2			 	= $this->AddPlayTime($sum_cummulate_checking2);
					$sum_cummulate_correction_time2[]  			= $cummulate_correction_time2;
					$sum_value_cummulate_correction_time2		= $this->AddPlayTime($sum_cummulate_correction_time2);
					$sum_cummulate_rfi2[]  				 		= $cummulate_rfi2;
					$sum_value_cummulate_rfi2			 		= $this->AddPlayTime($sum_cummulate_rfi2);
					$sum_cummulate_aec2[]  				 		= $cummulate_aec2;
					$sum_value_cummulate_aec2			 		= $this->AddPlayTime($sum_cummulate_aec2);
					$sum_cummulate_non_billable_hours2[]  		= $cummulate_non_billable_hours2;
					$sum_value_cummulate_non_billable_hours2	= $this->AddPlayTime($sum_cummulate_non_billable_hours2);
					$sum_cummulate_billable_hours2[]  			= $cummulate_billable_hours2;
					$sum_value_cummulate_billable_hours2		= $this->AddPlayTime($sum_cummulate_billable_hours2);
					$sum_cummulate_change_order_time2[]  		= $cummulate_change_order_time2;
					$sum_value_cummulate_change_order_time2		= $this->AddPlayTime($sum_cummulate_change_order_time2);
					$sum_cummulate_bar_listing_time[]  			= $cummulate_bar_listing_time3;
					$sum_value_cummulate_bar_listing_time		= $this->AddPlayTime($sum_cummulate_bar_listing_time);
					$sum_cummulate_total_hours[]  				= $cummulate_total_hours;
					$sum_value_cummulate_total_hours			= $this->AddPlayTime($sum_cummulate_total_hours);
					$sum_cummulate_credit1[]					= $cummulate_credit1;
					$sum_value_cummulate_total_hours1			= $this->AddPlayTime($sum_cummulate_credit1);
					$sum_new_detail_count 				       += $new_detail_count;
					$sum_new_rev_count 						   += $new_rev_count;

				for ($x = 0; $x <= 23; $x++) {
					$excel_column  = $project_wise_excel[0]['excel_column'][$x];
					$excel_value   = $project_wise_excel[1]['excel_value'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];
					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'X'){
						$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($verticalStyle);
					}
					$cummuate_second_count = $q;
				}
				$q++;
			}
		}
		if((int)$work_result_count === 0){
			$m 						= $cummuate_second_count;
			$cummuate_final_count 	= $cummuate_second_count;
		}else{
			$other_work_count   = $cummuate_second_count+1;
			$m 					= $other_work_count;
			foreach($other_work_result as $key => $other_work_detail){
				$others_time = array();
				$others_time[] = $other_work_detail->cummulate_works;
				$others_time[] = $other_work_detail->cummulate_credit;
				$others_time_total 	   = $this->AddPlayTime($others_time);
				$cummulate_booking_hours[] = $cummulate_detailing_time1;
				$time_sheet_value['A']       = "";
				$time_sheet_value['B']       = $other_work_detail->other_works;
				$time_sheet_value['C']       = "";
				$time_sheet_value['D']       = "";
				$time_sheet_value['E']       = $other_work_detail->work_description;
				$time_sheet_value['F'] 		 = $other_work_detail->cummulate_credit;
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
				$time_sheet_value['V'] 		 = "";
				$time_sheet_value['W'] 		 = $other_work_detail->cummulate_works;
				$time_sheet_value['X'] 		 = $others_time_total;
				$sum_cummulate_works[]  	 = $other_work_detail->cummulate_works;
				// $sum_cummulate_works[]  	 = $other_work_detail->cummulate_credit;
				$cum_credut[] = $others_time_total;
				$final_credit_tot = $this->AddPlayTime($cum_credut);
				$sum_value_cummulate_works   = $this->AddPlayTime($sum_cummulate_works);
				$test   = $this->AddPlayTime($others_time_total);
				$sum_cummulate_credit2[]					= $other_work_detail->cummulate_credit;
				$sum_value_cummulate_total_hours2			= $this->AddPlayTime($sum_cummulate_credit2);

				for ($x = 0; $x <= 23; $x++) {
					$excel_column  = $project_wise_excel[0]['excel_column'][$x];
					$excel_value   = $project_wise_excel[1]['excel_value'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];

					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'X'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($verticalStyle);
					}
					$cummuate_final_count = $m;
				}
				$m++;
			}
		}
		// die;
		
		$sum_value_cummulate_credit[]   = $credit_total;
		$sum_value_cummulate_credit[]   = $sum_value_cummulate_total_hours2;
		$sum_value_cummulate_credit     = $this->AddPlayTime($sum_value_cummulate_credit);

		$cummuate_final_sumcount 		= $cummuate_final_count+1;
		$cummuate_final_second_sumcount = $cummuate_final_sumcount+1;
		$final_sum_total 				= array();
		$final_sum_total[] 				= $final_credit_tot;
		$final_sum_total[] 				= $sum_value_cummulate_total_hours;
		$final_sum_total		 		= $this->AddPlayTime($final_sum_total);
		$detail_total_time 				= array();
		$detail_total_time[] 			= $sum_value_cummulate_study1;
		$detail_total_time[] 			= $sum_value_cummulate_detailing_time1;
		$detail_total_time[] 			= $sum_value_cummulate_discussion1;
		$detail_total_time[] 			= $sum_value_cummulate_checking1;
		$detail_total_time[] 			= $sum_value_cummulate_correction_time1;
		$detail_total_time		 		= $this->AddPlayTime($detail_total_time);

		$rev_total_time 				= array();
		$rev_total_time[] 				= $sum_value_cummulate_study2;
		$rev_total_time[] 				= $sum_value_cummulate_aec2;
		$rev_total_time[] 				= $sum_value_cummulate_checking2;
		$rev_total_time[] 				= $sum_value_cummulate_correction_time2;
		$rev_total_time[] 				= $sum_value_cummulate_non_billable_hours2;
		$rev_total_time[] 				= $sum_value_cummulate_billable_hours2;
		$rev_total_time[] 				= $sum_value_cummulate_discussion2;
		$rev_total_time		 			= $this->AddPlayTime($rev_total_time);

		$obj->getActiveSheet()->setCellValue('A'.$cummuate_final_sumcount, $total_sum_detail_work)->mergeCells('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->getStyle('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->applyFromArray($FooterLeftStyle);
		$obj->getActiveSheet()->setCellValue('C'.$cummuate_final_sumcount,$total_detailing_count)->getStyle('C'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('D'.$cummuate_final_sumcount,$total_revision_count)->getStyle('D'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('E'.$cummuate_final_sumcount,"")->getStyle('E'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('F'.$cummuate_final_sumcount,$sum_value_cummulate_credit)->getStyle('F'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$cummuate_final_sumcount,$sum_value_cummulate_study1)->getStyle('G'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$cummuate_final_sumcount,$sum_value_cummulate_detailing_time1)->getStyle('H'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$cummuate_final_sumcount,$sum_value_cummulate_discussion1)->getStyle('I'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$cummuate_final_sumcount,$sum_value_cummulate_checking1)->getStyle('J'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$cummuate_final_sumcount,$sum_value_cummulate_correction_time1)->getStyle('K'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$cummuate_final_sumcount,$sum_value_cummulate_rfi2)->getStyle('L'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$cummuate_final_sumcount,$sum_value_cummulate_study2)->getStyle('M'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$cummuate_final_sumcount,$sum_value_cummulate_aec2)->getStyle('N'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$cummuate_final_sumcount,$sum_value_cummulate_checking2)->getStyle('O'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$cummuate_final_sumcount,$sum_value_cummulate_correction_time2)->getStyle('P'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$cummuate_final_sumcount,$sum_value_cummulate_non_billable_hours2)->getStyle('Q'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$cummuate_final_sumcount,$sum_value_cummulate_billable_hours2)->getStyle('R'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$cummuate_final_sumcount,$sum_value_cummulate_discussion2)->getStyle('S'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$cummuate_final_sumcount,$sum_value_cummulate_change_order_time2)->getStyle('T'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$cummuate_final_sumcount,$sum_value_bar_list_quantity_cummlate)->getStyle('U'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$cummuate_final_sumcount,$sum_value_cummulate_bar_listing_time)->getStyle('V'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$cummuate_final_sumcount,$sum_value_cummulate_works)->getStyle('W'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('X'.$cummuate_final_sumcount,$sum_value_total_hours)->getStyle('X'.$cummuate_final_sumcount)->applyFromArray($FooterRightStyle);
		$obj->getActiveSheet()->setCellValue('A'.$cummuate_final_second_sumcount, "")->mergeCells('A'.$cummuate_final_second_sumcount.':'.'F'.$cummuate_final_second_sumcount)->getStyle('A'.$cummuate_final_second_sumcount.':'.'F'.$cummuate_final_second_sumcount);
		$obj->getActiveSheet()->setCellValue('G'.$cummuate_final_second_sumcount, $detail_total_time)->mergeCells('G'.$cummuate_final_second_sumcount.':'.'K'.$cummuate_final_second_sumcount)->getStyle('G'.$cummuate_final_second_sumcount.':'.'K'.$cummuate_final_second_sumcount)->applyFromArray($verticalStyle);
		$obj->getActiveSheet()->setCellValue('M'.$cummuate_final_second_sumcount,$rev_total_time)->mergeCells('M'.$cummuate_final_second_sumcount.':'.'S'.$cummuate_final_second_sumcount)->getStyle('M'.$cummuate_final_second_sumcount.':'.'S'.$cummuate_final_second_sumcount)->applyFromArray($verticalStyle);


		/* THIRD WORK SHEET */

		
		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));
		$project_wise_qry 			= 'SELECT count(*) as working_days,SEC_TO_TIME(SUM(TIME_TO_SEC(total_time))) as total_time FROM cw_time_sheet where entry_date like "%'.$process_month.'%" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result();
		$project_wise_info->next_result();
		$working_days 				= $project_wise_result[0]->working_days;
		$total_time 				= $project_wise_result[0]->total_time;
		$total_time 				=explode(':', $total_time);
		$total_time 				= $total_time[0].':'.$total_time[1];

		$start_t 					= new DateTime();
		$current_t 					= new DateTime();
		$difference 				= $start_t ->diff($current_t );
		$differ_booking_office 		= $difference ->format('%H:%I');

		$project_wise_qry 			= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and employee_code = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1';
		$project_wise_info   		= $this->db->query("CALL sp_a_run ('SELECT','$project_wise_qry')");
		$project_wise_result 		= $project_wise_info->result();
		$project_wise_info->next_result();

		$actual_tonnage 			= $project_wise_result[0]->actual_tonnage;
		if((int)$actual_tonnage === 0 || $actual_tonnage === ''){
			$actual_tonnage = 0;
		}else{
			$actual_tonnage = $actual_tonnage;
		}
		$actual_billable_time 		= $project_wise_result[0]->actual_billable_time;
		if($actual_billable_time){
			if($actual_billable_time === '00:00:00'){
				$actual_billable_time = 0;
			}else{
				$actual_billable_time 		= explode(':', $actual_billable_time);
				$actual_billable_time 		= $actual_billable_time[0].':'.$actual_billable_time[1];
			}
		}else{
			$actual_billable_time = 0;
		}
		
		$decimalHours 				= $this->decimalHours($actual_billable_time);
		// $decimalHours 				= $decimalHours/24;
		$rev_hrs_tons 				= $decimalHours * 1.5;
		$production_tons 			= $rev_hrs_tons + $actual_tonnage;

		if($production_tons<=$credit_target){
			$target_status  = "Not Reached";
		}else{
			$target_status  = "Reached";
		}


		$actual_ton_sum1   = array();
		$actual_ton_sum2   = array();
		$actual_ton_sum1 []= $sum_value_study1;
		$actual_ton_sum1 []= $sum_value_detailing_time1;
		$actual_ton_sum1 []= $sum_value_discussion1;
		$actual_ton_sum1 []= $sum_value_checking1;
		$actual_ton_sum1 []= $sum_value_correction_time1;

		$actual_ton_sum2 []= $sum_value_study2;
		$actual_ton_sum2 []= $sum_value_aec;
		$actual_ton_sum2 []= $sum_value_correction_time2;
		$actual_ton_sum2 []= $sum_value_checking2;
		$actual_ton_sum2 []= $sum_value_non_billable_hours;
		$actual_ton_sum2 []= $sum_value_billable_hours;
		$actual_ton_sum2 []= $sum_value_discussion2;
		$detailing_tons	   = $this->AddPlayTime($actual_ton_sum1);
		$revision_tons     = $this->AddPlayTime($actual_ton_sum2);


		$detail_rev []= $detailing_tons;
		$detail_rev []= $revision_tons;
		$detail_rev 	   = $this->AddPlayTime($detail_rev);
		$tons_actual 	   = $detail_total_time+$rev_total_time;
		$tons_actual 	   = $production_tons/$tons_actual;
		$actual_tons 	   = round($tons_actual, 2);
		$detail_ton_perHour         = $actual_tonnage/$detail_total_time;
		$detail_ton_perHour 	  	= round($detail_ton_perHour, 2);
		$pers_correction1 	  		= $this->time_to_min($sum_value_cummulate_correction_time1);
		$pers_detailing1 	  		= $this->time_to_min($sum_value_cummulate_detailing_time1);
		
		$det_vs_clr 				= $pers_correction1/$pers_detailing1;
		$det_vs_clr     			= $det_vs_clr * 100;
		$det_vs_clr 				= round((int)$det_vs_clr);



		$min_std_working  			= $working_days * 8;
		$min_std_working  			= $credit_target/$min_std_working;
		$min_std_working 			= round($min_std_working *100);

		$productivity 				= $actual_tons/$min_ton_cummlate;
		$productivity 				= round($productivity *100);
		if ((int)$productivity >0) {
		    $productivity = $productivity;
		}else{
		    $productivity =0;
		} 

		$per_productivity   = array();
		$per_productivity []= $sum_value_detailing_time1;
		$per_productivity []= $sum_value_checking1;
		$per_productivity []= $sum_value_aec;
		$per_productivity []= $sum_value_checking2;
		$per_productivity []= $sum_value_non_billable_hours;
		$per_productivity []= $sum_value_billable_hours;
		$per_productivitys 	= $this->AddPlayTime($per_productivity);

		$total_entry_time   = array();
		$total_entry_time[]   = $detail_total_time;
		$total_entry_time[]   = $rev_total_time;
		$total_entry_times 	= $this->AddPlayTime($total_entry_time);
		$per_productivitys 	  		= $this->time_to_min($per_productivitys);
		$total_entry_times 	  	  		= $this->time_to_min($total_entry_times);
		$total_productivity 		= $per_productivitys/$total_entry_times;
		$total_productivity 		= round($total_productivity * 100);

		$claim_hrs   = array();
		$claim_hrs []= $sum_value_billable_hours;
		$claim_hrs []= $sum_value_non_billable_hours;
		$claim_hrs 					= $this->AddPlayTime($claim_hrs);
		$claim_hrs 	  				= $this->time_to_min($claim_hrs);
		$revision_tons 	  			= $this->time_to_min($rev_total_time);
		$claimed_hours  			= $claim_hrs/$revision_tons;
		$claimed_hours  			= round($claimed_hours * 100);
		if ((int)$claimed_hours > 0) {
		    $claimed_hours = $claimed_hours;
		}else{
		    $claimed_hours = 0;
		} 

		$no_of_holiday 				= 0;
		$no_of_leave_taken 			= 0;
		$no_of_working 				= $working_days + $no_of_holiday - $no_of_leave_taken;

		$off_hours = $this->time_to_decimal('08:30');
		// echo "$off_hours<br>";
		$off_hours = $no_of_working * $off_hours;
		$off_hours = $this->decimal_to_time($off_hours);

		$off_break = $this->time_to_decimal('00:45');
		$off_break = $no_of_working * $off_break;
		$off_break = $this->decimal_to_time($off_break);
		$offs_hours = $this->time_to_min($off_hours);
		$off_breaks = $this->time_to_min($off_break);
		$off_diff  = $offs_hours-$off_breaks;
		$off_total_hours = intdiv($off_diff, 60).':'. ($off_diff % 60);

		$office_total_hour    = $this->time_to_min($off_total_hours);
		$bk_totals = $this->time_to_min($sum_value_total_hours);
		$res3          = $office_total_hour-$bk_totals;
		$balance_time = intdiv($res3, 60).':'. ($res3 % 60);

		$submit_log_qry = 'select count(*) as detailed_sheet_count from cw_tonnage_approval inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_tonnage_approval.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_project_and_drawing_master.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_drawings_id = cw_tonnage_approval.drawing_no inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name inner join cw_team on find_in_set(cw_team.prime_team_id,cw_tonnage_approval.team) inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_branch on cw_branch.prime_branch_id = cw_employees.branch inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where cw_tonnage_approval.work_type = 1 and cw_tonnage_approval.trans_status =1 and cw_project_and_drawing_master.trans_status =1 and cw_tonnage_approval.detailer_name = "'.$employee_code.'" group by cw_tonnage_approval.drawing_no';
		$submit_log_info   			= $this->db->query("CALL sp_a_run ('SELECT','$submit_log_qry')");
		$submit_log_result 			= $submit_log_info->result();
		$submit_log_info->next_result();
		$detailed_sheet_count 		= count($submit_log_result);
		$detail_ton_per_sheet 		= $actual_tonnage/(int)$detailed_sheet_count;
		if ((int)$detail_ton_per_sheet > 0) {
		    $detail_ton_per_sheet = round($detail_ton_per_sheet, 2);
		}else{
		    $detail_ton_per_sheet = 0;
		} 
		$logged_team      			= $this->session->userdata('logged_team');

		$team_submit_log_qry 			= 'SELECT count(*) as sheet_count,count(DISTINCT detailer_name) as detailer_count FROM cw_time_sheet_time_line inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_tonnage_approval on cw_tonnage_approval.prime_time_sheet_time_line_id = cw_time_sheet_time_line.prime_time_sheet_time_line_id inner join cw_employees on cw_employees.employee_code = cw_tonnage_approval.detailer_name where cw_employees.role = 5 and entry_date like "%'.$process_month.'%" and cw_tonnage_approval.trans_status = 1 and cw_tonnage_approval.approval_status = 2';
		$team_submit_log_info   		= $this->db->query("CALL sp_a_run ('SELECT','$team_submit_log_qry')");
		$team_submit_log_result 		= $team_submit_log_info->result();
		$team_submit_log_info->next_result();
		$sheet_count  					= $team_submit_log_result[0]->sheet_count;
		$detailer_count  				= $team_submit_log_result[0]->detailer_count;
		$team_ton_per_sheet 			= $sheet_count/$detailer_count;
		$team_ton_per_sheet 	   		= round((int)$team_ton_per_sheet, 2);
		if((int)$team_ton_per_sheet > 0){
			$team_ton_per_sheet = $team_ton_per_sheet;
		}else{
			$team_ton_per_sheet = 0;
		}
		$detail_team_tons 	  			= $this->time_to_decimal($detailing_tons);
		$hrs_dwg 						= $detail_team_tons/$detailed_sheet_count;
		$hrs_dwg 	   					= round((int)$hrs_dwg, 2);
		
		$from_years 					= explode('-', $process_month);
		$from_year	 					= $from_years[0];
		$divide_month 					= $from_years[1];
		$a_date 						= $from_year."-".$divide_month."-01";
		$to_month_date 					= date("Y-m-t", strtotime($a_date));
		$from_month_date 				= $from_year.'-01-01';

		$productivity_qry 				= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as productivitiy_actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as productivitiy_actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date >= "'.$from_month_date.'" and entry_date <= "'.$to_month_date.'" and employee_code = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1';
		$productivity_info   			= $this->db->query("CALL sp_a_run ('SELECT','$productivity_qry')");
		$productivity_result 			= $productivity_info->result();
		$productivity_info->next_result();
		$productivitiy_actual_tonnage 	= $productivity_result[0]->productivitiy_actual_tonnage;
		$productivitiy_actual_tonnage   = $this->time_to_decimal($productivitiy_actual_tonnage);
		$productivitiy_actual_tonnage 	= $productivitiy_actual_tonnage/$divide_month;
		if((int)$productivitiy_actual_tonnage > 0) {
		    $productivitiy_actual_tonnage = round($productivitiy_actual_tonnage * 100);
		}else{
		    $productivitiy_actual_tonnage = 0;
		} 

		$rev_hrs_tons = round($rev_hrs_tons * 1000)/1000;
		$production_tons = round($production_tons * 1000)/1000;
		$total_min     = $this->time_to_min($total_time_date_wise);
		$final_sum_min = $this->time_to_min($sum_value_total_hours);
		$res3          = $total_min-$final_sum_min;
		$different_bk_hrs = intdiv($res3, 60).':'. ($res3 % 60);
		
		$report_head 	= $cummuate_final_second_sumcount+5;
		$report_inc3 	= $cummuate_final_second_sumcount+6;
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

		$project_excel[]['excel_column']= array('C'.$report_inc3,'C'.$report_inc4,'C'.$report_inc5,'C'.$report_inc6,'C'.$report_inc7,'C'.$report_inc8,'C'.$report_inc9,'C'.$report_inc10,'C'.$report_inc11,'C'.$report_inc12,'C'.$report_inc13,'C'.$report_inc14,'C'.$report_inc15,'C'.$report_inc16,'C'.$report_inc17,'C'.$report_inc18,'C'.$report_inc19,'C'.$report_inc20,'C'.$report_inc21,'C'.$report_inc22);
		$project_excel[]['excel_value']= array('No. of Working Days','Total Office hours','Total Booking hours','Difference b/t Booking  & Off Hrs','Detailed Tons (Submitted Log)','Rev. hours (Submitted Log)','Rev.hours in Tons','Total Production Tons','Target Reached/Not Reached','Actual Tons per Hour','Tons per Hour New Detailing only','Detailed Sheets (Submitted Log)','Team Tons per Sheet (From Sub. Log)','Detailed Tons per Sheet','Hrs/Dwg','Det vs Cor','Productivity %','Cumulative Productivity %','Productivity hours %','Claimed Hours %');
		$project_excel[]['end_column']= array('G'.$report_inc3,'G'.$report_inc4,'G'.$report_inc5,'G'.$report_inc6,'G'.$report_inc7,'G'.$report_inc8,'G'.$report_inc9,'G'.$report_inc10,'G'.$report_inc11,'G'.$report_inc12,'G'.$report_inc13,'G'.$report_inc14,'G'.$report_inc15,'G'.$report_inc16,'G'.$report_inc17,'G'.$report_inc18,'G'.$report_inc19,'G'.$report_inc20,'G'.$report_inc21,'G'.$report_inc22);
		$project_excel[]['column_cell']= array('H'.$report_inc3,'H'.$report_inc4,'H'.$report_inc5,'H'.$report_inc6,'H'.$report_inc7,'H'.$report_inc8,'H'.$report_inc9,'H'.$report_inc10,'H'.$report_inc11,'H'.$report_inc12,'H'.$report_inc13,'H'.$report_inc14,'H'.$report_inc15,'H'.$report_inc16,'H'.$report_inc17,'H'.$report_inc18,'H'.$report_inc19,'H'.$report_inc20,'H'.$report_inc21,'H'.$report_inc22);

		$project_excel[]['column_value']= array($no_of_working,$total_time_date_wise,$sum_value_total_hours,$different_bk_hrs,$actual_tonnage,$actual_billable_time,$rev_hrs_tons,$production_tons,$target_status,$actual_tons,$detail_ton_perHour,$detailed_sheet_count,$team_ton_per_sheet,$detail_ton_per_sheet,$hrs_dwg,$det_vs_clr.'%',$productivity.'%',$productivitiy_actual_tonnage.'%',$total_productivity.'%',$claimed_hours.'%');

		$project_excel[]['column_end']= array('I'.$report_inc3,'I'.$report_inc4,'I'.$report_inc5,'I'.$report_inc6,'I'.$report_inc7,'I'.$report_inc8,'I'.$report_inc9,'I'.$report_inc10,'I'.$report_inc11,'I'.$report_inc12,'I'.$report_inc13,'I'.$report_inc14,'I'.$report_inc15,'I'.$report_inc16,'I'.$report_inc17,'I'.$report_inc18,'I'.$report_inc19,'I'.$report_inc20,'I'.$report_inc21,'I'.$report_inc22);

		$match_id = 'H'.$report_inc22;
		for ($x = 0; $x <= 19; $x++) {
			$excel_column  		= $project_excel[0]['excel_column'][$x];
			$excel_value   		= $project_excel[1]['excel_value'][$x];
			$end_column   		= $project_excel[2]['end_column'][$x];
			$column_cell   		= $project_excel[3]['column_cell'][$x];
			$column_value   	= $project_excel[4]['column_value'][$x];
			$column_end   		= $project_excel[5]['column_end'][$x];
			$obj->getActiveSheet()->setCellValue('C'.$report_head, "Detailer Name: ".$emp_name)->mergeCells('C'.$report_head.':I'.$report_head)->getStyle('C'.$report_head.':I'.$report_head)->applyFromArray($TopBorder);
			
			if($match_id === $column_cell){
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($FooterLeftStyletwo);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($FooterRightStyletwo);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($LeftBorder);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($RightBordertwo);
			}
			$obj->getActiveSheet()->setCellValue('J'.$report_inc5, "Min Difference")->mergeCells('J'.$report_inc5.':K'.$report_inc5)->getStyle('J'.$report_inc5.':K'.$report_inc5)->applyFromArray($LeftBorder);
			$obj->getActiveSheet()->setCellValue('J'.$report_head, "Office Hrs")->getStyle('J'.$report_head)->applyFromArray($verticalStyle);
			$obj->getActiveSheet()->setCellValue('K'.$report_head, "Break Hrs")->getStyle('K'.$report_head)->applyFromArray($verticalStyle);
			$obj->getActiveSheet()->setCellValue('L'.$report_head, "Diff Hrs")->getStyle('L'.$report_head)->applyFromArray($verticalStyle);
			$obj->getActiveSheet()->setCellValue('J'.$report_inc3, $off_hours)->getStyle('J'.$report_inc3)->applyFromArray($verticalStyle);
			$obj->getActiveSheet()->setCellValue('K'.$report_inc3, $off_break)->getStyle('K'.$report_inc3)->applyFromArray($verticalStyle);
				$obj->getActiveSheet()->setCellValue('L'.$report_inc3, $off_total_hours)->getStyle('L'.$report_inc3)->applyFromArray($verticalStyle);

			$obj->getActiveSheet()->setCellValue('J'.$report_inc6, $balance_time)->mergeCells('J'.$report_inc6.':K'.$report_inc6)->getStyle('J'.$report_inc6.':K'.$report_inc6)->applyFromArray($LeftBorder);
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
	public function time_to_min($time){
		$timeArr = explode(':', $time);
		$decTime = ($timeArr[0]*60) + ($timeArr[1]) + ($timeArr[2]/60);
		return $decTime;
	}
}
?>