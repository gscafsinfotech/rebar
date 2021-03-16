<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Detailing_report  extends Action_controller{	
	public function __construct(){
		parent::__construct('detailing_report');
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
		$final_qry = 'select employee_code,emp_name from cw_employees where trans_status = 1 and employee_code like "'.$search_term.'%"';
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
	public function excel_export($from_date,$to_date){
		// echo "string";die;
		$control_name		= $this->control_name;
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));
		// $detailing_qry 	= 'select client_no,rdd_no,purchase_order,project_name,cw_client.client_name,cw_general_contractor.general_contractor,cw_branch.branch,cw_team.team_name,cw_employees.emp_name,received_date,estimated_tons from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_client on cw_client.prime_client_id=cw_project_and_drawing_master.client_name inner join cw_branch on cw_branch.prime_branch_id=cw_project_and_drawing_master.branch inner join cw_general_contractor on cw_general_contractor.prime_general_contractor_id=cw_project_and_drawing_master.general_contractor inner join cw_team on cw_team.prime_team_id=cw_project_and_drawing_master.team inner join cw_employees on cw_employees.employee_code=cw_project_and_drawing_master.project_manager where cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1';
		$detailing_qry 	= 'select client_no,rdd_no,purchase_order,project_name,cw_client.client_name,cw_general_contractor.general_contractor,cw_branch.branch,cw_team.team_name,cw_employees.emp_name,received_date,estimated_tons from cw_project_and_drawing_master inner join cw_client on cw_client.prime_client_id=cw_project_and_drawing_master.client_name inner join cw_branch on cw_branch.prime_branch_id=cw_project_and_drawing_master.branch inner join cw_general_contractor on cw_general_contractor.prime_general_contractor_id=cw_project_and_drawing_master.general_contractor inner join cw_team on cw_team.prime_team_id=cw_project_and_drawing_master.team inner join cw_employees on cw_employees.employee_code=cw_project_and_drawing_master.project_manager where cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1';
		$detailing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result  = $detailing_info->result();
		$detailing_info->next_result();
		// echo "<pre>";
		// print_r($detailing_qry);die;
		
		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();		
		//Set the first row as the header row
		$i =3;
		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K');
		$excel_types[]['excel_value']= array('Client No','RDD No','PO#',' Project Name','Client','General Contractor','Office','Team','US PM','Received Date','Estimated Tons');
// echo "latha";
		$styleArray = array(
	        'font' => array(
	            'bold' => true,
	            'color' => array('rgb' => '#01060b'),
	        ),
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => '#168cf3')
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
		for ($x = 0; $x <= 10; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "US Detailing Projects - Detailing & Billing Status as on January 2021")->mergeCells('A1:K1')->getStyle('A1:K1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
		}
		foreach($detailing_result as $key => $detailing_sheet){
			$detailing_value['A']       = $detailing_sheet->client_no;
			$detailing_value['B']       = $detailing_sheet->rdd_no;
			$detailing_value['C']       = $detailing_sheet->purchase_order;
			$detailing_value['D']       = $detailing_sheet->project_name;
			$detailing_value['E']       = $detailing_sheet->client_name;
			$detailing_value['F'] 		= $detailing_sheet->general_contractor;
			$detailing_value['G'] 		= $detailing_sheet->branch;
			$detailing_value['H'] 		= $detailing_sheet->team_name;
			$detailing_value['I']		= $detailing_sheet->emp_name;
			$detailing_value['J'] 		= $detailing_sheet->received_date;
			$detailing_value['K'] 		= $detailing_sheet->estimated_tons;
			
			for ($x = 0; $x <= 10; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $detailing_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel);
			}
			$i++;
		}	
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