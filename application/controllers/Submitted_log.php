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
		$team_qry = 'select prime_team_id,team_name from cw_team where trans_status = 1';
		$team_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result = $team_info->result();
		$team_info->next_result();
		$team_list[""] = "---- Select ----";


		foreach ($team_result as $key => $team) {
			$prime_team_id = $team->prime_team_id;
			$team_name  = $team->team_name;
			$team_list[$prime_team_id] = $team_name;
		}
		$data['team_list'] = $team_list;


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
	public function datacount_check(){
		$process_team 				= $this->input->post("process_team");
		$process_month 				= $this->input->post("process_month");
		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));
		
		$team_wise_detailing_qry	= 'select count(*) as rlst_count from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_team on find_in_set(cw_team.prime_team_id,cw_time_sheet_time_line.team) inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_time_sheet_time_line.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_time_sheet.entry_date like "%'.$process_month.'%" and cw_team.prime_team_id in('.$process_team.') and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1';
		$team_wise_detailing_info   = $this->db->query("CALL sp_a_run ('SELECT','$team_wise_detailing_qry')");
		$team_wise_detailing_result = $team_wise_detailing_info->result();
		$team_wise_detailing_info->next_result();
		$rlst_count 				= $team_wise_detailing_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
	public function excel_export($process_team,$process_month){
		$process_month  			= '01-'.$process_month;
		$process_month  			= date('Y-m',strtotime($process_month));
		$detailing_qry	= 'select cw_project_and_drawing_master.rdd_no,cw_uspm.uspm,cw_client.client_name,cw_project_and_drawing_master.project_name,cw_project_and_drawing_master.received_date,cw_project_and_drawing_master_drawings.drawing_no,cw_project_and_drawing_master_drawings.drawing_description from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_team on find_in_set(cw_team.prime_team_id,cw_time_sheet_time_line.team) inner join cw_project_and_drawing_master on cw_project_and_drawing_master.prime_project_and_drawing_master_id = cw_time_sheet_time_line.project inner join cw_uspm on cw_uspm.prime_uspm_id = cw_project_and_drawing_master.project_manager inner join cw_client on cw_client.prime_client_id = cw_time_sheet_time_line.client_name inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_time_sheet_time_line.work_type = 1 and cw_time_sheet.entry_date like "%'.$process_month.'%" and cw_team.prime_team_id in('.$process_team.') and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1';
		$detailing_info   			= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result 			= $detailing_info->result();
		$detailing_info->next_result();
		// echo "<pre>";
		// print_r($detailing_result);
		// die;






		// $team_qry  	= 'select * from cw_project_and_drawing_master inner join cw_project_and_drawing_master_drawings on cw_project_and_drawing_master_drawings.prime_project_and_drawing_master_id=cw_project_and_drawing_master.prime_project_and_drawing_master_id inner join cw_team on cw_team.prime_team_id = cw_project_and_drawing_master.team group by prime_team_id order by prime_team_id ASC';
		$team_qry  	= 'select * from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id inner join cw_team on find_in_set(cw_team.prime_team_id,cw_time_sheet_time_line.team) where cw_team.prime_team_id = "'.$process_team.'" group by prime_team_id order by prime_team_id ASC';
		$team_info   	= $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result  	= $team_info->result();
		$team_info->next_result();


		$team_result  = json_decode(json_encode($team_result),true);		
		$team_result = array_reduce($team_result, function($result, $arr){			
		    $result[$arr['prime_team_id']] = $arr;
		    return $result;
		}, array());

		// echo "<pre>";
		// print_r($team_result);die;

		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();	

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


		


		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
		$excel_types[]['excel_value']= array('RDA#','US PM','Client','Name of Project','Recd On','Dwg. No','No .OfDwgs','Drawing Description','Sub Date','Tons','Detailer Name','Time','"Checker Name','Time','Total','1st Check Major','1st Check Minor','2nd Check','QA Major','QA Minor','PM','Branch');
		for ($x = 0; $x <= 21; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "US DETAILING PROJECTS - NEW SUBMISSIONS DURING DEC 2020")->mergeCells('A1:V1')->getStyle('A1:V1')->applyFromArray($styleArray);
			$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			$obj->getActiveSheet()->calculateWorksheetDimension();
		}


		// echo "<pre>";
		// print_r($detailing_result);die;

		$i=3;
		foreach ($team_result as $key => $value) {
			$emp_name 		= $value['emp_name'];
			$team_name 		= $value['team_name'];
			$team_id =$value['prime_team_id'];
			// echo "team_id :: $team_id<br>";
			// $arr = $detailing_result[$team_id];
			$time_sheet_value['A']  = $team_name.' >>> '.$emp_name;
			$can_process = false;
			for ($x = 0; $x <= 1; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $time_sheet_value[$excel_column];
				$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->mergeCells($excel_column.$i.':'.'V'.$i)->getStyle($excel_column.$i.':'.'V'.$i)->applyFromArray($styleArray);
				$can_process = true;
			}
			// echo "<pre>";
			// print_r($detailing_result);



			$counter=$i;
			if($can_process){
				foreach ($detailing_result as $key => $details){
					// $project = $details['project'];
					// echo "<pre>";
					// print_r($details->rdd_no);
					// echo $details->drawing_no;echo "<br>";
					
					
					$time_sheet_inside['A']  = $details->rdd_no;
					$time_sheet_inside['B']  = $details->uspm;
					$time_sheet_inside['C']  = $details->client_name;
					$time_sheet_inside['D']  = $details->project_name;
					$time_sheet_inside['E']  = $details->received_date;
					$time_sheet_inside['F']  = $details->drawing_no;
					$time_sheet_inside['G']  = "wait";
					$time_sheet_inside['H']  = $details->drawing_description;
					$time_sheet_inside['I']  = "wait";
					$time_sheet_inside['J']  = $details->estimated_tons;
					$time_sheet_inside['K']  = "wait";
					$time_sheet_inside['L']  = "Time";
					$time_sheet_inside['M']  = "Checker Name";
					$time_sheet_inside['N']  = "Time";
					$time_sheet_inside['O']  = "Total";
				




				// $time_sheet_inside['U']  = "PM";
				// $time_sheet_inside['V']  = "Branch";
				$counter++;					
				for ($y = 0; $y <= 21; $y++) {
					$excel_column  		= $excel_types[0]['excel_column'][$y];					
					$value_of_excel  	= $time_sheet_inside[$excel_column];
					
					$obj->getActiveSheet()->setCellValue($excel_column.$counter, $value_of_excel)->getStyle($excel_column.$counter)->applyFromArray($styleArray);
					$obj->setActiveSheetIndex(0);
					$obj->getActiveSheet()->setTitle('Detailing');
				}					
				$i++;	
			}
			}
			
						
		$i++;	

	}

// die;






















		$control_name		= $this->control_name;
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