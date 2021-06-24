<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Project_manager_report  extends Action_controller{	
	public function __construct(){
		parent::__construct('project_manager_report');
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
		if((int)$logged_role === 3){
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 3 and employee_code = "'.$logged_emp_code.'" and employee_status = 1 and trans_status = 1';
		}else{
			$emp_qry 		= 'SELECT employee_code,emp_name FROM cw_employees where role = 3 and employee_status = 1 and trans_status = 1';
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
		$emp_qry 			= 'SELECT employee_code,emp_name FROM cw_employees where role = 4 and reporting = "'.$employee_code.'" and employee_status = 1 and trans_status = 1';
		$emp_info   		= $this->db->query("CALL sp_a_run ('SELECT','$emp_qry')");
		$emp_result 		= $emp_info->result_array();
		$emp_info->next_result();
		$emp_result = array_reduce($emp_result, function($result, $arr){			
		    $result[$arr['employee_code']] = $arr;
		    return $result;
		}, array());

		$tl_employee_code      = "";
		foreach ($emp_result as $key => $value) {
			$tl_employee_code .= $value['employee_code'].",";
		}
		$tl_employee_code 	   = rtrim($tl_employee_code,",");
		$target_qry     = 'select SUM(cw_team_target_detailer_wise_target.target_value) as target_value from cw_team_target inner join cw_team_target_detailer_wise_target on cw_team_target_detailer_wise_target.prime_team_target_id = cw_team_target.prime_team_target_id inner join cw_employees on cw_employees.employee_code = cw_team_target_detailer_wise_target.detailer_name where cw_team_target.from_date <= "'.$process_month.'" and cw_team_target.to_date >= "'.$process_month.'" and role = 5 and FIND_IN_SET(reporting,"'.$tl_employee_code.'") and cw_team_target.trans_status = 1';
		$target_info    = $this->db->query("CALL sp_a_run ('SELECT','$target_qry')");
		$target_result  = $target_info->result();
		$target_info->next_result();
		$credit_target  = $target_result[0]->target_value;

		$checker_qry 		= 'select emp_name,cw_time_sheet.employee_code,entry_date,in_time,out_time,total_time,IF(study>"00:00:00",TIME_FORMAT(study, "%H:%i"),"") as study,IF(qa_checking>"00:00:00", TIME_FORMAT(qa_checking, "%H:%i"), "") as qa_checking,IF(discussion>"00:00:00", TIME_FORMAT(discussion, "%H:%i"), "") as discussion,IF(was>"00:00:00", TIME_FORMAT(was, "%H:%i"), "") as was,IF(monitoring>"00:00:00", TIME_FORMAT(monitoring, "%H:%i"), "") as monitoring,IF(rfi>"00:00:00", TIME_FORMAT(rfi, "%H:%i"), "") as rfi,IF(co_checking>"00:00:00", TIME_FORMAT(co_checking, "%H:%i"), "") as co_checking,IF(other_works>"00:00:00", TIME_FORMAT(other_works, "%H:%i"), "") as other_works,IF(bar_listing_time>"00:00:00", TIME_FORMAT(bar_listing_time, "%H:%i"), "") as bar_listing_time,IF(emails>"00:00:00", TIME_FORMAT(emails, "%H:%i"), "") as emails,cw_work_type.work_type,entry_type,client_name,project,drawing_no,work_status,cw_time_sheet_time_line.work_type as work_type_time from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_work_type on cw_work_type.prime_work_type_id = cw_time_sheet_time_line.work_type inner join cw_employees on cw_employees.employee_code = cw_time_sheet.employee_code where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 and DATE_FORMAT(`entry_date`, "%m-%Y") = "'.$process_month.'" order by entry_date';
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

		$other_work_qry 			= 'select count(*) as work_result_count,cw_time_sheet_time_line.work_type,work_description,cw_other_works.other_works,IF(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(cw_time_sheet_time_line.other_works))),"%H:%i"),"") as cummulate_works,other_work_name,IF(SEC_TO_TIME( SUM(time_to_sec(emails)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(emails))),"%H:%i"),"") as cummulate_emails from cw_time_sheet_time_line inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id inner join cw_other_works on cw_other_works.prime_other_works_id = cw_time_sheet_time_line.other_work_name where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and work_type = 4 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.other_work_name order by cw_time_sheet_time_line.other_work_name';
		$other_work_info   			= $this->db->query("CALL sp_a_run ('SELECT','$other_work_qry')");
		$other_work_result 			= $other_work_info->result();
		$other_work_info->next_result();
		$work_result_count			= $other_work_result[0]->work_result_count;

		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();
		$excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y');
		$excel[]['excel_value']= array('Date','Project Name','Drawing No','Drawing Revisin Status','Work Status','Emails','STY','QA CHK','DIS','WAS','MOR','RFI','STY','QA CHK','DIS','WAS','MOR','CO CHK','CHK','OTHER WORK','BOOKING HOURS','IN','OUT','TOTAL','SHIFT');
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

	    for ($x = 0; $x <= 24; $x++) {
			$excel_column  = $excel[0]['excel_column'][$x];
			$excel_value   = $excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "TIME SHEET LOG FOR ".strtoupper($month_name))->mergeCells('A1:Y1')->getStyle('A1:Y1')->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'."2", "Project Manager Name:".$emp_name)->mergeCells('A2:B2')->getStyle('A2:B2')->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('C'."2", "Team's Target Tons")->mergeCells('C2:E2')->getStyle('C2:E2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'."2", $credit_target)->getStyle('F2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'."2", "New Detailing Work")->mergeCells('G2:K2')->getStyle('G2:K2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'."2", "")->getStyle('L2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'."2", "Revision Work")->mergeCells('M2:R2')->getStyle('M2:R2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('S'."2", "Listing")->getStyle('S2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('T'."2", "OTHER WORKS")->getStyle('T2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'."2", "Booking Hours")->getStyle('U2')->applyFromArray($doubleColumnStyle);
			$obj->getActiveSheet()->setCellValue('V'."2", "OFFICE HOURS")->mergeCells('V2:X2')->getStyle('V2:X2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('Y'."2", " ")->getStyle('Y2')->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($LeftArray);
			}else
			if($excel_column === 'AB'){
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($RightArray);
			}else
			if($excel_column === 'T'){
				$obj->getActiveSheet()->setCellValue('T'."2", $excel_value)->mergeCells('T2:'.$excel_column.'3')->getStyle('T2:'.$excel_column.'3')->applyFromArray($doubleColumnStyle);
			}else
			if($excel_column === 'U'){
				$obj->getActiveSheet()->setCellValue('U'."2", $excel_value)->mergeCells('U2:'.$excel_column.'3')->getStyle('U2:'.$excel_column.'3')->applyFromArray($doubleColumnStyle);
			}
			else{
				$obj->getActiveSheet()->setCellValue($excel_column."3", $excel_value)->getStyle($excel_column.'3')->applyFromArray($styleArray);
			}
		}
		
		$i = 4;
		$j = 0;
		$k = 0;
		$previous_date = "";
		$time_total = array();
		foreach($checker_result as $key => $time_sheet){
			$sum_value_total_hours  = array();
			$booking_hours 	 		= array();
			$work_type_time 		= $time_sheet->work_type_time;
			if((int)$work_type_time === 1){
				$study1				= $time_sheet->study;
				$qa_checking1  		= $time_sheet->qa_checking;
				$discussion1  		= $time_sheet->discussion;
				$was1 				= $time_sheet->was;
				$monitoring1  		= $time_sheet->monitoring;
				$study2				= "";
				$qa_checking2  		= "";
				$discussion2  		= "";
				$was2 				= "";
				$monitoring2  		= "";
			}else
			if((int)$work_type_time === 2){
				$study2				= $time_sheet->study;
				$qa_checking2  		= $time_sheet->qa_checking;
				$discussion2  		= $time_sheet->discussion;
				$was2 				= $time_sheet->was;
				$monitoring2  		= $time_sheet->monitoring;
				$study1				= "";
				$qa_checking1  		= "";
				$discussion1  		= "";
				$was1  				= "";
				$monitoring1  		= "";
			}else{
				$study1				= $time_sheet->study;
				$study2				= $time_sheet->study;
				$qa_checking1  		= $time_sheet->qa_checking;
				$qa_checking2  		= $time_sheet->qa_checking;
				$discussion1  		= $time_sheet->discussion;
				$discussion2  		= $time_sheet->discussion;
				$was1  				= $time_sheet->was;
				$was2 				= $time_sheet->was;
				$monitoring1  		= $time_sheet->monitoring;
				$monitoring2  		= $time_sheet->monitoring;
			}

			$booking_hours[] = $study1;
			$booking_hours[] = $qa_checking1;
			$booking_hours[] = $discussion1;
			$booking_hours[] = $was1;
			$booking_hours[] = $monitoring1;
			$booking_hours[] = $time_sheet->rfi;
			$booking_hours[] = $study2;
			$booking_hours[] = $qa_checking2;
			$booking_hours[] = $discussion2;
			$booking_hours[] = $was2;
			$booking_hours[] = $monitoring2;
			$booking_hours[] = $time_sheet->co_checking;
			$booking_hours[] = $time_sheet->bar_listing_time;
			$booking_hours[] = $time_sheet->other_works;
			$booking_hours[] = $time_sheet->emails;
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
			$project 		= $time_sheet->project;
			$project 		= $project_result[$project]['project_name'];
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
			$time_sheet_value['C']       = $drawing_no;
			$time_sheet_value['D']       = $work_status;
			$time_sheet_value['E']       = $time_sheet->work_description;
			$time_sheet_value['F'] 		 = $time_sheet->emails;
			$time_sheet_value['G'] 		 = $study1;
			$time_sheet_value['H'] 		 = $qa_checking1;
			$time_sheet_value['I'] 		 = $discussion1;
			$time_sheet_value['J']		 = $was1;
			$time_sheet_value['K'] 		 = $monitoring1;
			$time_sheet_value['L'] 		 = $time_sheet->rfi;
			$time_sheet_value['M']		 = $study2;
			$time_sheet_value['N']		 = $qa_checking2;
			$time_sheet_value['O']		 = $discussion2;
			$time_sheet_value['P'] 		 = $was2;
			$time_sheet_value['Q'] 		 = $monitoring2;
			$time_sheet_value['R'] 		 = $time_sheet->co_checking;
			$time_sheet_value['S'] 		 = $time_sheet->bar_listing_time;
			$time_sheet_value['T'] 		 = $time_sheet->other_works;
			$time_sheet_value['U']       = $total_hours;
			$time_sheet_value['V'] 		 = $time_sheet->in_time;
			$time_sheet_value['W'] 		 = $time_sheet->out_time;
			$time_sheet_value['X'] 		 = $time_sheet->total_time;
			$time_sheet_value['Y'] 		 = "shift";
			
			$sum_study1[]  				 = $study1;
			$sum_study2[]  				 = $study2;
			$sum_qa_checking1[]  		 = $qa_checking1;
			$sum_qa_checking2[]  		 = $qa_checking2;
			$sum_discussion1[] 			 = $discussion1;
			$sum_discussion2[] 			 = $discussion2;
			$sum_was1[] 				 = $was1;
			$sum_was2[] 				 = $was2;
			$sum_monitoring1[] 			 = $monitoring1;
			$sum_monitoring2[] 			 = $monitoring2;
			$sum_rfi[] 					 = $time_sheet->rfi;
			$sum_co_checking[] 			 = $time_sheet->co_checking;
			$sum_bar_listing_time[] 	 = $time_sheet->bar_listing_time;
			$sum_other_works[] 			 = $time_sheet->other_works;
			$sum_emails[]  				 = $time_sheet->emails;
			
			$sum_value_study1			 = $this->AddPlayTime($sum_study1);
			$sum_value_study2			 = $this->AddPlayTime($sum_study2);
			$sum_value_qa_checking1		 = $this->AddPlayTime($sum_qa_checking1);
			$sum_value_qa_checking2		 = $this->AddPlayTime($sum_qa_checking2);
			$sum_value_discussion1		 = $this->AddPlayTime($sum_discussion1);
			$sum_value_discussion2		 = $this->AddPlayTime($sum_discussion2);
			$sum_value_was1				 = $this->AddPlayTime($sum_was1);
			$sum_value_was2				 = $this->AddPlayTime($sum_was2);
			$sum_value_monitoring1		 = $this->AddPlayTime($sum_monitoring1);
			$sum_value_monitoring2		 = $this->AddPlayTime($sum_monitoring2);
			$sum_value_rfi				 = $this->AddPlayTime($sum_rfi);
			$sum_value_bar_listing_time  = $this->AddPlayTime($sum_bar_listing_time);
			$sum_value_other_works		 = $this->AddPlayTime($sum_other_works);
			$sum_value_co_checking		 = $this->AddPlayTime($sum_co_checking);
			$sum_value_emails		 	 = $this->AddPlayTime($sum_emails);

			for ($x = 0; $x <= 24; $x++) {
				$excel_column  		= $excel[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				if($excel_column === 'V' || $excel_column === 'W' || $excel_column === 'X'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($verticalStyle);
				}else
				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($start_cell.':'.$end_cell)->getStyle($start_cell.':'.$end_cell)->applyFromArray($LeftBorder);
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'Y'){
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
		$obj->getActiveSheet()->setCellValue('F'.$counter,$sum_value_emails)->getStyle('F'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$counter,$sum_value_study1)->getStyle('G'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$counter,$sum_value_qa_checking1)->getStyle('H'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$counter,$sum_value_discussion1)->getStyle('I'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$counter,$sum_value_was1)->getStyle('J'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$counter,$sum_value_monitoring1)->getStyle('K'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter,$sum_value_rfi)->getStyle('L'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter,$sum_value_study2)->getStyle('M'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$counter,$sum_value_qa_checking2)->getStyle('N'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$counter,$sum_value_discussion2)->getStyle('O'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter,$sum_value_was2)->getStyle('P'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter,$sum_value_monitoring2)->getStyle('Q'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter,$sum_value_co_checking)->getStyle('R'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$counter,$sum_value_bar_listing_time)->getStyle('S'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$counter,$sum_value_other_works)->getStyle('T'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$counter,$sum_value_total_hours)->getStyle('U'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('V'.$counter,"")->getStyle('V'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('W'.$counter,"")->getStyle('W'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('X'.$counter,$total_time_date_wise)->getStyle('X'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Y'.$counter,"")->getStyle('Y'.$counter)->applyFromArray($FooterRightStyle);


		/* SECOND WORK SHEET */


		$cummulative_sheet2 = $counter+3;
		$cummulative_sheet3 = $counter+4;
		$cummulative_sheet4 = $counter+5;
		$cummulative_sheet5 = $counter+6;
		$cummulative_sheet6 = $counter+7;
		$cummulative_detail_count = $counter+8;

		$process_months  			= '01-'.$process_month;
		$process_months  			= date('Y-m',strtotime($process_months));
		$working_days_qry 			= 'SELECT count(*) as working_days,SEC_TO_TIME(SUM(TIME_TO_SEC(total_time))) as total_time FROM cw_time_sheet where entry_date like "%'.$process_months.'%" and employee_code = "'.$employee_code.'" and trans_status = 1';
		$working_days_info   		= $this->db->query("CALL sp_a_run ('SELECT','$working_days_qry')");
		$working_days_result 		= $working_days_info->result();
		$working_days_info->next_result();
		$working_days 				= $working_days_result[0]->working_days;
		$min_std_work_cummlate 		= $working_days * 8;
		$min_ton_cummlate 			= $credit_target/$min_std_work_cummlate;
		$min_ton_cummlate 			= round($min_ton_cummlate,2);

		$project_wise_excel[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U');
		$project_wise_excel[]['excel_value']= array('Job Category','Project Name','# New dwg','# Rev dwg','Remarks','Emails','STY','QA CHK','DIS','WAS','MOR','RFI','STY','QA CHK','DIS','WAS','MOR','CO CHK','CHK','OTHER WORK','TOTAL');

			for ($x = 0; $x <= 20; $x++) {
			$excel_column  = $project_wise_excel[0]['excel_column'][$x];
			$excel_value   = $project_wise_excel[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet2, "Project Manager Name: ".$emp_name)->mergeCells('A'.$cummulative_sheet2.':U'.$cummulative_sheet2)->getStyle('A'.$cummulative_sheet2.':U'.$cummulative_sheet2)->applyFromArray($TopBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet3, "Designation & Experience: ".$calculate_date_month)->mergeCells('A'.$cummulative_sheet3.':U'.$cummulative_sheet3)->getStyle('A'.$cummulative_sheet3.':U'.$cummulative_sheet3)->applyFromArray($LeftrightBorder);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet4, "Working Days")->getStyle('A'.$cummulative_sheet4)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet4, "Min. Standard Working Hours")->getStyle('B'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet4, "Target Tons")->getStyle('C'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet4, "Min Shts/Day")->getStyle('D'.$cummulative_sheet4)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet4, "")->mergeCells('E'.$cummulative_sheet4.':U'.$cummulative_sheet4)->getStyle('E'.$cummulative_sheet4.':U'.$cummulative_sheet4)->applyFromArray($RightBorderHead);
			$obj->getActiveSheet()->setCellValue('A'.$cummulative_sheet5, $working_days)->getStyle('A'.$cummulative_sheet5)->applyFromArray($LeftArray);
			$obj->getActiveSheet()->setCellValue('B'.$cummulative_sheet5, $min_std_work_cummlate)->getStyle('B'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('C'.$cummulative_sheet5, $credit_target)->getStyle('C'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('D'.$cummulative_sheet5, $min_ton_cummlate)->getStyle('D'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('E'.$cummulative_sheet5, "Team:".$team_name.", ".$emp_name)->getStyle('E'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, "Emails")->getStyle('F'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('G'.$cummulative_sheet5, "Detailing Work")->mergeCells('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->getStyle('G'.$cummulative_sheet5.':K'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('L'.$cummulative_sheet5, "")->getStyle('L'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('M'.$cummulative_sheet5, "Revision Work")->mergeCells('M'.$cummulative_sheet5.':R'.$cummulative_sheet5)->getStyle('M'.$cummulative_sheet5.':R'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('S'.$cummulative_sheet5, "Listing")->getStyle('S'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('T'.$cummulative_sheet5, "OTHER WORKS")->getStyle('T'.$cummulative_sheet5)->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue('U'.$cummulative_sheet5, "TOTAL")->getStyle('U'.$cummulative_sheet5)->applyFromArray($RightArray);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($LeftArray);
			}else
			if($excel_column === 'U'){
				$obj->getActiveSheet()->setCellValue('U'.$cummulative_sheet5, $excel_value)->mergeCells('U'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('U'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyleRight);
			}else
			if($excel_column === 'F'){
				$obj->getActiveSheet()->setCellValue('F'.$cummulative_sheet5, $excel_value)->mergeCells('F'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('F'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyle);
			}else
			if($excel_column === 'T'){
				$obj->getActiveSheet()->setCellValue('T'.$cummulative_sheet5, $excel_value)->mergeCells('T'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->getStyle('T'.$cummulative_sheet5.':'.$excel_column.$cummulative_sheet6)->applyFromArray($doubleColumnStyle);
			}
			else{
			$obj->getActiveSheet()->setCellValue($excel_column.$cummulative_sheet6, $excel_value)->getStyle($excel_column.$cummulative_sheet6)->applyFromArray($styleArray);
			}
		}

		$project_wise_qry 			= 'SELECT count(*) as project_wise_count,count(*) as count_project_wise,cw_job_category.job_category,count(cw_time_sheet_time_line.work_type) as work_type_count,cw_time_sheet_time_line.project,cw_project_and_drawing_master.project_name,cw_time_sheet_time_line.work_description,IF(SEC_TO_TIME( SUM(time_to_sec(emails)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(emails))),"%H:%i"),"") as cummulate_emails,IF(SEC_TO_TIME( SUM(time_to_sec(study)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(study))),"%H:%i"),"") as cummulate_study,IF(SEC_TO_TIME( SUM(time_to_sec(qa_checking)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(qa_checking))),"%H:%i"),"") as cummulate_qa_checking,IF(SEC_TO_TIME( SUM(time_to_sec(discussion)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(discussion))),"%H:%i"),"") as cummulate_discussion,IF(SEC_TO_TIME( SUM(time_to_sec(was)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(was))),"%H:%i"),"") as cummulate_was,IF(SEC_TO_TIME( SUM(time_to_sec(monitoring)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(monitoring))),"%H:%i"),"") as cummulate_monitoring,IF(SEC_TO_TIME( SUM(time_to_sec(rfi)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(rfi))),"%H:%i"),"") as cummulate_rfi,IF(SEC_TO_TIME( SUM(time_to_sec(co_checking)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(co_checking))),"%H:%i"),"") as cummulate_co_checking,IF(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(bar_listing_time))),"%H:%i"),"") as cummulate_bar_listing_time,IF(SEC_TO_TIME( SUM(time_to_sec(other_works)))>"00:00:00",TIME_FORMAT(SEC_TO_TIME( SUM(time_to_sec(other_works))),"%H:%i"),"") as cummulate_other_works,sum(bar_list_quantity) as cummulate_bar_list_quantity,work_type,GROUP_CONCAT(work_description) as work_description FROM cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project left join cw_job_category on cw_job_category.prime_job_category_id = cw_project_and_drawing_master.job_category where cw_time_sheet.employee_code = "'.$employee_code.'" and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.project,work_type order by cw_time_sheet_time_line.work_type';
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
		

		$q = $cummulative_detail_count;
		$r = 0;
		$s = 0;
		$sum_new_detail_count 		= 0;
		$sum_new_rev_count 			= 0;
		$sum_value_bar_list_quantity_cummlate  		 = 0;
		if((int)$project_wise_count === 0){
			$cummuate_second_count  = $cummulative_detail_count;
		}else{
			$total_credit_project_wise 	= array();
			$total_detailing_count 		= 0;
			$total_revision_count 		= 0;
			foreach($project_wise_result as $key => $cummulative_time_sheet){
				$emails_cummulate 		= array();
				foreach ($cummulative_time_sheet as $aa => $value) {
					$cummulate_emails1 	= array();
					$project_name = array();
					$cummulate_emails1[$key] = $value['cummulate_emails'];
					$project_name[$key] = $value['project_name'];
					// echo "project_name :: $project_name<br>";
					$emails_cummulate[] 		= $cummulate_emails1[$key];
					$emails_project_wise 	    = $this->AddPlayTime($emails_cummulate);
					$project_name1 = $project_name[$key];
				}
				$cummulate_booking_hours 		= array();
				$work_type1 					= $cummulative_time_sheet[1];
				$work_type2 					= $cummulative_time_sheet[2];
				$work_type3 					= $cummulative_time_sheet[3];
				$job_category1 					= $work_type1['job_category'];
				$work_description1 				= $work_type1['work_description'];
				$cummulate_study1 				= $work_type1['cummulate_study'];
				$cummulate_qa_checking1 		= $work_type1['cummulate_qa_checking'];
				$cummulate_discussion1 			= $work_type1['cummulate_discussion'];
				$cummulate_was1 				= $work_type1['cummulate_was'];
				$cummulate_monitoring1 			= $work_type1['cummulate_monitoring'];
				$cummulate_rfi2 				= $work_type2['cummulate_rfi'];
				$cummulate_study2 				= $work_type2['cummulate_study'];
				$cummulate_qa_checking2 		= $work_type2['cummulate_qa_checking'];
				$cummulate_discussion2 			= $work_type2['cummulate_discussion'];
				$cummulate_was2 				= $work_type2['cummulate_was'];
				$cummulate_monitoring2 			= $work_type2['cummulate_monitoring'];
				$cummulate_co_checking2 		= $work_type2['cummulate_co_checking'];
				$cummulate_bar_listing_time3 	= $work_type3['cummulate_bar_listing_time'];
				$detailing_count 				= $work_type1['count_project_wise'];
				$revision_count 				= $work_type2['count_project_wise'];
				$total_emails_project_wise[] 	= $emails_project_wise; 
				$emails_total     				= $this->AddPlayTime($total_emails_project_wise);
				$total_detailing_count 		   += $detailing_count;
				$total_revision_count 		   += $revision_count;


				$cummulate_booking_hours[] = $cummulate_study1;
				$cummulate_booking_hours[] = $cummulate_qa_checking1;
				$cummulate_booking_hours[] = $cummulate_discussion1;
				$cummulate_booking_hours[] = $cummulate_was1;
				$cummulate_booking_hours[] = $cummulate_monitoring1;
				$cummulate_booking_hours[] = $cummulate_rfi2;
				$cummulate_booking_hours[] = $cummulate_study2;
				$cummulate_booking_hours[] = $cummulate_qa_checking2;
				$cummulate_booking_hours[] = $cummulate_discussion2;
				$cummulate_booking_hours[] = $cummulate_was2;
				$cummulate_booking_hours[] = $cummulate_monitoring2;
				$cummulate_booking_hours[] = $cummulate_co_checking2;
				$cummulate_booking_hours[] = $cummulate_bar_listing_time3;
				$cummulate_booking_hours[] = $cummulate_other_works;
				$cummulate_booking_hours[] = $emails_project_wise;
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
				$time_sheet_value['F'] 		 = $emails_project_wise;
				$time_sheet_value['G'] 		 = $cummulate_study1;
				$time_sheet_value['H'] 		 = $cummulate_qa_checking1;
				$time_sheet_value['I'] 		 = $cummulate_discussion1;
				$time_sheet_value['J']		 = $cummulate_was1;
				$time_sheet_value['K'] 		 = $cummulate_monitoring1;
				$time_sheet_value['L'] 		 = $cummulate_rfi2;
				$time_sheet_value['M']		 = $cummulate_study2;
				$time_sheet_value['N']		 = $cummulate_qa_checking2;
				$time_sheet_value['O']		 = $cummulate_discussion2;
				$time_sheet_value['P'] 		 = $cummulate_was2;
				$time_sheet_value['Q'] 		 = $cummulate_monitoring2;
				$time_sheet_value['R'] 		 = $cummulate_co_checking2;
				$time_sheet_value['S'] 		 = $cummulate_bar_listing_time3;
				$time_sheet_value['T'] 		 = $cummulate_other_works;
				$time_sheet_value['U']       = $cummulate_total_hours;

				$sum_cummulate_study1[]  				 	= $cummulate_study1;
				$sum_value_cummulate_study1			 		= $this->AddPlayTime($sum_cummulate_study1);
				$sum_cummulate_qa_checking1[]  				= $cummulate_qa_checking1;
				$sum_value_cummulate_qa_checking1			= $this->AddPlayTime($sum_cummulate_qa_checking1);
				$sum_cummulate_discussion1[]  				= $cummulate_discussion1;
				$sum_value_cummulate_discussion1			= $this->AddPlayTime($sum_cummulate_discussion1);
				$sum_cummulate_was1[]  						= $cummulate_was1;
				$sum_value_cummulate_was1					= $this->AddPlayTime($sum_cummulate_was1);
				$sum_cummulate_monitoring1[]  				= $cummulate_monitoring1;
				$sum_value_cummulate_monitoring1			= $this->AddPlayTime($sum_cummulate_monitoring1);
				$sum_cummulate_rfi2[]  				 		= $cummulate_rfi2;
				$sum_value_cummulate_rfi2			 		= $this->AddPlayTime($sum_cummulate_rfi2);
				$sum_cummulate_study2[]  				 	= $cummulate_study2;
				$sum_value_cummulate_study2			 		= $this->AddPlayTime($sum_cummulate_study2);
				$sum_cummulate_qa_checking2[]  				= $cummulate_qa_checking2;
				$sum_value_cummulate_qa_checking2			= $this->AddPlayTime($sum_cummulate_qa_checking2);
				$sum_cummulate_discussion2[]  				= $cummulate_discussion2;
				$sum_value_cummulate_discussion2			= $this->AddPlayTime($sum_cummulate_discussion2);
				$sum_cummulate_was2[]  						= $cummulate_was2;
				$sum_value_cummulate_was2			 		= $this->AddPlayTime($sum_cummulate_was2);
				$sum_cummulate_monitoring2[]  				= $cummulate_monitoring2;
				$sum_value_cummulate_monitoring2			= $this->AddPlayTime($sum_cummulate_monitoring2);
				$sum_cummulate_co_checking2[]  				= $cummulate_co_checking2;
				$sum_value_cummulate_co_checking2			= $this->AddPlayTime($sum_cummulate_co_checking2);
				$sum_cummulate_bar_listing_time[]  			= $cummulate_bar_listing_time3;
				$sum_value_cummulate_bar_listing_time		= $this->AddPlayTime($sum_cummulate_bar_listing_time);
				$sum_cummulate_total_hours[]  				= $cummulate_total_hours;
				$sum_value_cummulate_total_hours			= $this->AddPlayTime($sum_cummulate_total_hours);
				$sum_cummulate_emails1[]					= $cummulative_time_sheet->cummulate_emails;
				$sum_value_cummulate_emails1				= $this->AddPlayTime($sum_cummulate_emails1);
				$sum_new_detail_count 				       += $new_detail_count;
				$sum_new_rev_count 						   += $new_rev_count;
			
				for ($x = 0; $x <= 20; $x++) {
					$excel_column  = $project_wise_excel[0]['excel_column'][$x];
					$excel_value   = $project_wise_excel[1]['excel_value'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];
					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$q, $value_of_excel)->getStyle($excel_column.$q)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'U'){
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
				$time_sheet_value['T'] 		 = $other_work_detail->cummulate_works;
				$time_sheet_value['U']       = $other_work_detail->cummulate_works;
				
				
				$sum_cummulate_works[]  	 = $other_work_detail->cummulate_works;
				$sum_value_cummulate_works   = $this->AddPlayTime($sum_cummulate_works);
				$sum_cummulate_emails2[]					= $other_work_detail->cummulate_emails;
				$sum_value_cummulate_emails2				= $this->AddPlayTime($sum_cummulate_emails2);

				for ($x = 0; $x <= 20; $x++) {
					$excel_column  = $project_wise_excel[0]['excel_column'][$x];
					$excel_value   = $project_wise_excel[1]['excel_value'][$x];
					$value_of_excel  	= $time_sheet_value[$excel_column];

					if($excel_column === 'A'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($LeftBorder);
					}else
					if($excel_column === 'U'){
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($RightBorder);
					}else{
						$obj->getActiveSheet()->setCellValue($excel_column.$m, $value_of_excel)->getStyle($excel_column.$m)->applyFromArray($verticalStyle);
					}
					$cummuate_final_count = $m;
				}
				$m++;
			}
		}
		

		$sum_value_cummulate_emails[]   = $emails_total;
		$sum_value_cummulate_emails[]   = $sum_value_cummulate_emails2;
		$sum_value_cummulate_emails     = $this->AddPlayTime($sum_value_cummulate_emails);
		$cummuate_final_sumcount 		= $cummuate_final_count+1;
		$cummuate_final_second_sumcount = $cummuate_final_sumcount+1;
		$final_sum_total 				= array();
		$final_sum_total[] 				= $sum_value_cummulate_works;
		$final_sum_total[] 				= $sum_value_cummulate_total_hours;
		$final_sum_total[] 				= $sum_value_cummulate_emails2;
		$final_sum_total		 		= $this->AddPlayTime($final_sum_total);

		$obj->getActiveSheet()->setCellValue('A'.$cummuate_final_sumcount, "")->mergeCells('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->getStyle('A'.$cummuate_final_sumcount.':'.'B'.$cummuate_final_sumcount)->applyFromArray($FooterLeftStyle);
		$obj->getActiveSheet()->setCellValue('C'.$cummuate_final_sumcount,$total_detailing_count)->getStyle('C'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('D'.$cummuate_final_sumcount,$total_revision_count)->getStyle('D'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('E'.$cummuate_final_sumcount,"")->getStyle('E'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('F'.$cummuate_final_sumcount,$sum_value_cummulate_emails)->getStyle('F'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('G'.$cummuate_final_sumcount,$sum_value_cummulate_study1)->getStyle('G'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('H'.$cummuate_final_sumcount,$sum_value_cummulate_qa_checking1)->getStyle('H'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('I'.$cummuate_final_sumcount,$sum_value_cummulate_discussion1)->getStyle('I'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('J'.$cummuate_final_sumcount,$sum_value_cummulate_was1)->getStyle('J'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('k'.$cummuate_final_sumcount,$sum_value_cummulate_monitoring1)->getStyle('K'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$cummuate_final_sumcount,$sum_value_cummulate_rfi2)->getStyle('L'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$cummuate_final_sumcount,$sum_value_cummulate_study2)->getStyle('M'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$cummuate_final_sumcount,$sum_value_cummulate_qa_checking2)->getStyle('N'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$cummuate_final_sumcount,$sum_value_cummulate_discussion2)->getStyle('O'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$cummuate_final_sumcount,$sum_value_cummulate_was2)->getStyle('P'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$cummuate_final_sumcount,$sum_value_cummulate_monitoring2)->getStyle('Q'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$cummuate_final_sumcount,$sum_value_cummulate_co_checking2)->getStyle('R'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('S'.$cummuate_final_sumcount,$sum_value_cummulate_bar_listing_time)->getStyle('S'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('T'.$cummuate_final_sumcount,$sum_value_cummulate_works)->getStyle('T'.$cummuate_final_sumcount)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('U'.$cummuate_final_sumcount,$final_sum_total)->getStyle('U'.$cummuate_final_sumcount)->applyFromArray($FooterRightStyle);
		



		/* THIRD WORK SHEET */



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

		$no_of_holiday 				= 0;
		$no_of_taken_leave 			= 0;
		$no_of_working_days  		= $working_days + $no_of_holiday - $no_of_taken_leave;

		$min_different_booking_hrs   = $this->time_to_decimal($final_sum_total);
		$min_different_booking_hrs 	 = $min_different_booking_hrs/24;
		$min_different_booking_hrs 	 = $this->decimal_to_time($min_different_booking_hrs);

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
		$start_num			  		= $getnum->__invoke($total_time_date_wise);
		$end_num 			  		= $getnum->__invoke($sum_value_total_hours);
		$diff 				  		= max($start_num, $end_num) - min($end_num, $start_num);
		$diff_office_office  		= intval($diff / 60).':'.($diff % 60);

		$actual_qry 			= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and project_manager_name = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1';
		$actual_info   		= $this->db->query("CALL sp_a_run ('SELECT','$actual_qry')");
		$actual_result 		= $actual_info->result();
		$actual_info->next_result();

		$actual_tonnage 			= $actual_result[0]->actual_tonnage;

		$actual_billable_time 		= $actual_result[0]->actual_billable_time;
		$actual_billable_time 		= explode(':', $actual_billable_time);
		$actual_billable_time 		= $actual_billable_time[0].':'.$actual_billable_time[1];
		$decimalHours 				= $this->decimal_to_time($actual_billable_time);
		$decimalHours 				= $decimalHours/24;
		$rev_hrs_tons 				= $decimalHours * 1.5;
		$rev_hrs_tons 	  			= round($rev_hrs_tons, 2);
		$production_tons 			= $rev_hrs_tons + $actual_tonnage;
		$production_tons 	   		= round($production_tons, 2);
		if($production_tons<=$credit_target){
			$target_status  		= "Not Reached";
		}else{
			$target_status  		= "Reached";
		}
		$submitted_log_approval_qry 			= 'SELECT sum(cw_tonnage_approval.actual_tonnage) as submitted_log_actual_tonnage,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as submitted_log_actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id 	= cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and project_manager_name = "'.$employee_code.'" and cw_tonnage_approval.trans_status = 1 and cw_tonnage_approval.approval_status=2';
		$submitted_log_approval_info   			= $this->db->query("CALL sp_a_run ('SELECT','$submitted_log_approval_qry')");
		$submitted_log_approval_result 			= $submitted_log_approval_info->result();
		$submitted_log_approval_info->next_result();
		$submitted_log_actual_tonnage 			= $submitted_log_approval_result[0]->submitted_log_actual_tonnage;
		$submitted_log_actual_billable_time 	= $submitted_log_approval_result[0]->submitted_log_actual_billable_time;
		if((int)$submitted_log_actual_tonnage === 0 ){
			$submitted_log_actual_tonnage = 0;
		}else{
			$submitted_log_actual_tonnage;
		}
		$submitted_log_actual_billable_time 		= $actual_result[0]->submitted_log_actual_billable_time;
		$submitted_log_actual_billable_time 		= explode(':', $submitted_log_actual_billable_time);
		$submitted_log_actual_billable_time 		= $submitted_log_actual_billable_time[0].':'.$submitted_log_actual_billable_time[1];
		if($submitted_log_actual_billable_time === ':'){
			$submitted_log_actual_billable_time = 0;
		}else{
			$submitted_log_actual_billable_time;
		}


		$checker_new_detailing_qry 			= 'SELECT qa_major,qa_minor FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and project_manager_name = "'.$employee_code.'" and cw_tonnage_approval.work_type = 1 and cw_tonnage_approval.approval_status = 2 and cw_tonnage_approval.trans_status = 1';
		$checker_new_detailing_info   		= $this->db->query("CALL sp_a_run ('SELECT','$checker_new_detailing_qry')");
		$checker_new_detailing_result 		= $checker_new_detailing_info->result();
		$checker_new_detailing_info->next_result();
		$checked_sheet_new_details = count($checker_new_detailing_result);
		$qa_error_count = 0;
		foreach ($checker_new_detailing_result as $key => $value) {
			$qa_error_count += $value->qa_major;
			$qa_error_count += $value->qa_minor;
		}


		$checker_rev_detailing_qry 			= 'SELECT count(*) as actual_billable_time FROM cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where entry_date like "%'.$process_month.'%"  and project_manager_name = "'.$employee_code.'" and cw_tonnage_approval.work_type = 2 and cw_tonnage_approval.approval_status = 2 and cw_tonnage_approval.trans_status = 1';
		$checker_rev_detailing_info   		= $this->db->query("CALL sp_a_run ('SELECT','$checker_rev_detailing_qry')");
		$checker_rev_detailing_result 		= $checker_rev_detailing_info->result();
		$checker_rev_detailing_info->next_result();
		$checked_sheet_rev_details = $checker_rev_detailing_result[0]->actual_billable_time;

		$avg_qa_error = $qa_error_count/$checked_sheet_new_details;
		if ((int)$avg_qa_error >0) {
		    $avg_qa_error = $avg_qa_error;
		}else{
		    $avg_qa_error = 0;
		} 

		$tons_new_detailing 		= $production_tons/500;
		$team_ton_per_sheet 		= $actual_tonnage/$checked_sheet_new_details;
		if ((int)$team_ton_per_sheet > 0) {
		    $team_ton_per_sheet = $team_ton_per_sheet;
		}else{
		    $team_ton_per_sheet = 0;
		} 

		$team_rev_hours_tons 		= $this->time_to_decimal($submitted_log_actual_billable_time);
		$team_tot_production_tons	= $team_rev_hours_tons + $submitted_log_actual_tonnage;
		$team_tot_production_tons 	= round($team_tot_production_tons,2);
		$team_rev_hours_tons	 	= $team_rev_hours_tons * 1.5;
		$team_rev_hours_tons 		= round($team_rev_hours_tons,2);
		$team_ton_per_hrs_detail 	= $team_tot_production_tons/1000;
		$team_ton_per_hrs_detail	= round($team_ton_per_hrs_detail,2);


		// $min_different_office_hrs   = $this->time_to_decimal($diff_office_office);
		// $min_different1 			= $no_of_working_days*8.5/24;
		// $min_different2 			= $no_of_working_days*0.75/24;
		// $min_different  			= ($min_different_office_hrs * $min_different2)/$min_different1;
		// $min_different 				= $this->decimal_to_time($min_different);


		$off_hours = $this->time_to_decimal('08:30');
		$off_hours = $no_of_working_days * $off_hours;
		$off_hours = $this->decimal_to_time($off_hours);

		$off_break = $this->time_to_decimal('00:45');
		$off_break = $no_of_working_days * $off_break;
		$off_break = $this->decimal_to_time($off_break);
		$off_total[] = $off_hours;
		$off_total[] = $off_break;
		$off_total_hours			= $this->AddPlayTime($off_total);


		$office_total_hour    = $this->time_to_min($off_total_hours);
		$bk_totals = $this->time_to_min($sum_value_total_hours);
		$res3          = $office_total_hour-$bk_totals;
		$balance_time = intdiv($res3, 60).':'. ($res3 % 60);





		$project_excel[]['excel_column']= array('C'.$report_inc3,'C'.$report_inc4,'C'.$report_inc5,'C'.$report_inc6,'C'.$report_inc7,'C'.$report_inc8,'C'.$report_inc9,'C'.$report_inc10,'C'.$report_inc11,'C'.$report_inc12,'C'.$report_inc13,'C'.$report_inc14,'C'.$report_inc15,'C'.$report_inc16,'C'.$report_inc17,'C'.$report_inc18);
		$project_excel[]['excel_value']= array('No. of Working Days','Total Office hours','Total Booking hours','Difference b/t Booking  & Office Hrs','X','Teams Tons Detailed (Submitted Log)','Teams Rev. hours (Submitted Log)','Teams Rev. hours in Tons','Teams Total Production Tons','Target Reached/Not Reached','Checked Sheets New (Submitted Log)','Checked Sheets Rev (Time Sheet)','Teams Tons per Hour New Detailing only','Teams Tons per Sheet','Total QA Error Count (Submitted Log)','Avg. QA Error Count per Sheet');
		$project_excel[]['end_column']= array('G'.$report_inc3,'G'.$report_inc4,'G'.$report_inc5,'G'.$report_inc6,'G'.$report_inc7,'G'.$report_inc8,'G'.$report_inc9,'G'.$report_inc10,'G'.$report_inc11,'G'.$report_inc12,'G'.$report_inc13,'G'.$report_inc14,'G'.$report_inc15,'G'.$report_inc16,'G'.$report_inc17,'G'.$report_inc18);
		$project_excel[]['column_cell']= array('H'.$report_inc3,'H'.$report_inc4,'H'.$report_inc5,'H'.$report_inc6,'H'.$report_inc7,'H'.$report_inc8,'H'.$report_inc9,'H'.$report_inc10,'H'.$report_inc11,'H'.$report_inc12,'H'.$report_inc13,'H'.$report_inc14,'H'.$report_inc15,'H'.$report_inc16,'H'.$report_inc17,'H'.$report_inc18);

		$project_excel[]['column_value']= array($no_of_working_days,$total_time_date_wise,$sum_value_total_hours,$diff_office_office,"",$submitted_log_actual_tonnage,$submitted_log_actual_billable_time,$team_rev_hours_tons,$team_tot_production_tons,$target_status,$checked_sheet_new_details,$checked_sheet_rev_details,$tons_new_detailing,$team_ton_per_sheet,$qa_error_count,$avg_qa_error);

		$project_excel[]['column_end']= array('I'.$report_inc3,'I'.$report_inc4,'I'.$report_inc5,'I'.$report_inc6,'I'.$report_inc7,'I'.$report_inc8,'I'.$report_inc9,'I'.$report_inc10,'I'.$report_inc11,'I'.$report_inc12,'I'.$report_inc13,'I'.$report_inc14,'I'.$report_inc15,'I'.$report_inc16,'I'.$report_inc17,'I'.$report_inc18);


		$match_id = 'H'.$report_inc18;
		for ($x = 0; $x <= 15; $x++) {
			$excel_column  		= $project_excel[0]['excel_column'][$x];
			$excel_value   		= $project_excel[1]['excel_value'][$x];
			$end_column   		= $project_excel[2]['end_column'][$x];
			$column_cell   		= $project_excel[3]['column_cell'][$x];
			$column_value   	= $project_excel[4]['column_value'][$x];
			$column_end   		= $project_excel[5]['column_end'][$x];
			$obj->getActiveSheet()->setCellValue('C'.$report_head, "Project Manager Name: ".$emp_name)->mergeCells('C'.$report_head.':I'.$report_head)->getStyle('C'.$report_head.':I'.$report_head)->applyFromArray($TopBorder);
			if($match_id === $column_cell){
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($FooterLeftStyletwo);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($FooterRightStyletwo);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column, $excel_value)->mergeCells($excel_column.':'.$end_column)->getStyle($excel_column.':'.$column_end)->applyFromArray($LeftBorder);
				$obj->getActiveSheet()->setCellValue($column_cell, $column_value)->mergeCells($column_cell.':'.$column_end)->getStyle($column_cell.':'.$column_end)->applyFromArray($RightBordertwo);
			}
			$obj->getActiveSheet()->setCellValue('J'.$report_inc5, "Min Difference")->mergeCells('J'.$report_inc5.':K'.$report_inc5)->getStyle('J'.$report_inc5.':K'.$report_inc5)->applyFromArray($LeftBorder);
			$obj->getActiveSheet()->setCellValue('J'.$report_inc6, $balance_time)->mergeCells('J'.$report_inc6.':K'.$report_inc6)->getStyle('J'.$report_inc6.':K'.$report_inc6)->applyFromArray($LeftBorder);
		}

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