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

		$from_query = 'select * from cw_form_setting where  prime_module_id IN("project_and_drawing_master","tonnage_approval") and label_name in("rdd_no","client_name","branch","team","project_manager","received_date","detailing_status","revision_status","release_status","billing_unit","billing_unit_revision","billing_rate_revision","billing_rate_detailing") ORDER BY input_for,field_sort asc';
		$form_data   = $this->db->query("CALL sp_a_run ('SELECT','$from_query')");
		$form_result = $form_data->result();
		$form_data->next_result();
		$fliter_list = $this->get_filter_data($form_result);
		$data['fliter_list']  = $fliter_list;
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
						
						$pick_query = "select $pick_list from $pick_table where trans_status = 1 $qry";
						$pick_data   = $this->db->query("CALL sp_a_run ('SELECT','$pick_query')");
						$pick_result = $pick_data->result();
						$pick_data->next_result();
						
						$array_list[""] = "---- $label_name ----";
						foreach($pick_result as $pick){
							$pick_key = $pick->$pick_list_val_1;
							$pick_val = $pick->$pick_list_val_2;
							$array_list[$pick_key] = $pick_val;
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
				if(($input_view_type === 1) || ($input_view_type === 2) || ($input_view_type === 3)){
					$filter[] = array('label_id'=> $label_id, 'field_isdefault'=> $field_isdefault, 'array_list'=> $array_list, 'field_type'=> $field_type);
				}
			}
		}
		return $filter;
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
	public function excel_export($from_date,$to_date,$fliter_label,$fliter_type,$field_type,$filter_cond,$fliter_val,$multipick_val){

		$multi_val 			 = (int)$multipick_val-1;
		$filter_cond 		 = urldecode($filter_cond);
		$fliter_val 		 = explode(',', $fliter_val);
		$fliter_val_count 	 = count($fliter_val);
		$first_value 		 = array_splice($fliter_val,2,$multipick_val);
		$second_value 		 = array_splice($fliter_val,2,$fliter_val_count);
		$first_value 		 = implode(',', $first_value);
		$first_value 		 = rtrim($first_value,',');
		$first_value 		 = array($first_value);
		$first_value = array_reduce($first_value, function($result, $arr){			
		    $result[2] = $arr;
		    return $result;
		}, array());
		$first_value 	 	 = array_replace($fliter_val, $first_value, $first_value);
		$fliter_val 		 = array_merge($first_value,$second_value);
		$filter_cond 		 = explode(',', $filter_cond);
		$field_types 	 	 = explode(',', $field_type);
		$fliter_type 		 = explode(',', $fliter_type);
		$fliter_label 		 = explode(',', $fliter_label);
		$filter_count        = count($fliter_label);
		$fliter_query        = "";
		$search_count        = 0;
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     	 = $fliter_label[$i];
			$table_name  	 = $fliter_type[$i];
			$db_cond     	 = $filter_cond[$i];
			$db_value    	 = $fliter_val[$i];
			$field_type  	 = $field_types[$i];
			// echo "db_name ::$db_name<br>";
			if(($db_cond) && ($db_value)){
				if((int)$field_type === 7){
					$search_val    = $db_value;
					if($db_cond === "LIKE" || $db_cond === "="){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_project_and_drawing_master";
					 }
				}else
				if((int)$field_type === 4){
						$search_val = date('Y-m-d',strtotime($db_value));
						$search_val = $search_val;
						$table_qry = " and cw_project_and_drawing_master";
				}else{
					if($db_name === 'detailer_name' || $db_name === 'team_leader_name'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_tonnage_approval";
					}else
					if($db_name === 'billing_rate_revision' || $db_name === 'billing_rate_detailing'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master_invoice_details";
					}
					else{
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master";
					}
					
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}		
		}

		$control_name		= $this->control_name;
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));
		$detailing_qry 		= 'select cw_project_and_drawing_master.billing_unit_revision,cw_project_and_drawing_master.billing_unit,cw_project_and_drawing_master.detailing_status,cw_project_and_drawing_master.revision_status,cw_project_and_drawing_master.release_status,cw_project_and_drawing_master.prime_project_and_drawing_master_id,cw_project_and_drawing_master.client_no,rdd_no,purchase_order,project_name,cw_client.client_name,cw_general_contractor.general_contractor,cw_branch.branch,cw_team.team_name,received_date,estimated_tons,cw_uspm.uspm from cw_project_and_drawing_master inner join cw_client on cw_client.prime_client_id=cw_project_and_drawing_master.client_name inner join cw_branch on cw_branch.prime_branch_id=cw_project_and_drawing_master.branch inner join cw_general_contractor on cw_general_contractor.prime_general_contractor_id=cw_project_and_drawing_master.general_contractor inner join cw_team on cw_team.prime_team_id=cw_project_and_drawing_master.team join cw_uspm on cw_uspm.prime_uspm_id=cw_project_and_drawing_master.project_manager left join cw_project_and_drawing_master_invoice_details on cw_project_and_drawing_master_invoice_details.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1 '.$fliter_query.' group by cw_project_and_drawing_master.project_name';
		$detailing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result  	= $detailing_info->result();
		$detailing_info->next_result();

		$tons_project_wise_qry 		= 'select cw_tonnage_approval.work_type,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_tonnage_approval.actual_billable_time))) as actual_billable_time,cw_time_sheet.entry_date,count(cw_tonnage_approval.actual_tonnage) as count_sheet,sum(cw_tonnage_approval.actual_tonnage) as actual_tonnage,cw_tonnage_approval.project from cw_tonnage_approval inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_time_line_id = cw_tonnage_approval.prime_time_sheet_time_line_id inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where cw_tonnage_approval.approval_status = 2 and cw_tonnage_approval.trans_status = 1 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_tonnage_approval.project,cw_tonnage_approval.work_type order by cw_time_sheet.entry_date desc';
		$tons_project_wise_info   	= $this->db->query("CALL sp_a_run ('SELECT','$tons_project_wise_qry')");
		$tons_project_wise_result  	= $tons_project_wise_info->result_array();
		$tons_project_wise_info->next_result();
		$tons_project_wise_result = array_reduce($tons_project_wise_result, function($result, $arr){	
		    $result[$arr['project']][$arr['work_type']] = $arr;
		    return $result;
		}, array());

		$submission_date_wise_qry 		= 'select cw_time_sheet.entry_date,cw_time_sheet_time_line.project from cw_time_sheet_time_line inner join cw_time_sheet on cw_time_sheet.prime_time_sheet_id = cw_time_sheet_time_line.prime_time_sheet_id where cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by cw_time_sheet_time_line.project order by cw_time_sheet.entry_date desc';
		$submission_date_wise_info   	= $this->db->query("CALL sp_a_run ('SELECT','$submission_date_wise_qry')");
		$submission_date_wise_result  	= $submission_date_wise_info->result_array();
		$submission_date_wise_info->next_result();
		$submission_date_wise_result = array_reduce($submission_date_wise_result, function($result, $arr){	
		    $result[$arr['project']] = $arr;
		    return $result;
		}, array());

		$detailing_status_qry  		= 'select prime_detailing_status_id,detailing_status from cw_detailing_status where trans_status = 1';
		$detailing_status_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_status_qry')");
		$detailing_status_result  	= $detailing_status_info->result_array();
		$detailing_status_info->next_result();
		$detailing_status_result = array_reduce($detailing_status_result, function($result, $arr){	
		    $result[$arr['prime_detailing_status_id']] = $arr;
		    return $result;
		}, array());

		$revision_status_qry  		= 'select prime_revision_status_id,revision_status from cw_revision_status where trans_status = 1';
		$revision_status_info   	= $this->db->query("CALL sp_a_run ('SELECT','$revision_status_qry')");
		$revision_status_result  	= $revision_status_info->result_array();
		$revision_status_info->next_result();
		$revision_status_result = array_reduce($revision_status_result, function($result, $arr){	
		    $result[$arr['prime_revision_status_id']] = $arr;
		    return $result;
		}, array());

		$release_status_qry  		= 'select prime_release_status_id,release_status from cw_release_status where trans_status = 1';
		$release_status_info   		= $this->db->query("CALL sp_a_run ('SELECT','$release_status_qry')");
		$release_status_result  	= $release_status_info->result_array();
		$release_status_info->next_result();
		$release_status_result = array_reduce($release_status_result, function($result, $arr){	
		    $result[$arr['prime_release_status_id']] = $arr;
		    return $result;
		}, array());

		$billing_unit_qry  		= 'select prime_billing_unit_id,billing_unit from cw_billing_unit where trans_status = 1';
		$billing_unit_info   	= $this->db->query("CALL sp_a_run ('SELECT','$billing_unit_qry')");
		$billing_unit_result  	= $billing_unit_info->result_array();
		$billing_unit_info->next_result();
		$billing_unit_result  	= array_reduce($billing_unit_result, function($result, $arr){	
		    $result[$arr['prime_billing_unit_id']] = $arr;
		    return $result;
		}, array());

		$invoice_detail_qry   	= 'select sum(invoiced_ton) as invoiced_ton,sum(cw_project_and_drawing_master_invoice_details.billing_rate_detailing) as billing_rate_detailing,SEC_TO_TIME(SUM(TIME_TO_SEC(cw_project_and_drawing_master_invoice_details.invoiced_hours))) as invoiced_hours,sum(cw_project_and_drawing_master_invoice_details.billing_rate_revision) as billing_rate_revision,  cw_project_and_drawing_master.prime_project_and_drawing_master_id from cw_project_and_drawing_master inner join cw_project_and_drawing_master_invoice_details on cw_project_and_drawing_master_invoice_details.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_project_and_drawing_master.trans_status = 1 and cw_project_and_drawing_master_invoice_details.trans_status = 1 group by cw_project_and_drawing_master.prime_project_and_drawing_master_id';
		$invoice_detail_info   	= $this->db->query("CALL sp_a_run ('SELECT','$invoice_detail_qry')");
		$invoice_detail_result  = $invoice_detail_info->result_array();
		$invoice_detail_info->next_result();
		$invoice_detail_result  = array_reduce($invoice_detail_result, function($result, $arr){	
		    $result[$arr['prime_project_and_drawing_master_id']] = $arr;
		    return $result;
		}, array());

		$detailing_hours_qry 		= 'select project,SEC_TO_TIME(SUM(TIME_TO_SEC(detailing_time))+SUM(TIME_TO_SEC(study))+SUM(TIME_TO_SEC(discussion))+SUM(TIME_TO_SEC(rfi))+SUM(TIME_TO_SEC(checking))+SUM(TIME_TO_SEC(correction_time))+SUM(TIME_TO_SEC(other_works))+SUM(TIME_TO_SEC(bar_listing_time))+SUM(TIME_TO_SEC(revision_time))+SUM(TIME_TO_SEC(change_order_time))+SUM(TIME_TO_SEC(billable_hours))+SUM(TIME_TO_SEC(non_billable_hours))+SUM(TIME_TO_SEC(emails))+SUM(TIME_TO_SEC(was))+SUM(TIME_TO_SEC(co_checking))+SUM(TIME_TO_SEC(actual_billable_time))+SUM(TIME_TO_SEC(qa_checking))+SUM(TIME_TO_SEC(monitoring))+SUM(TIME_TO_SEC(bar_listing_checking))+SUM(TIME_TO_SEC(aec))+SUM(TIME_TO_SEC(credit))) as detailing_hours from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id where emp_role = 5 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by project';
		$detailing_hours_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_hours_qry')");
		$detailing_hours_result  	= $detailing_hours_info->result_array();
		$detailing_hours_info->next_result();
		$detailing_hours_result  = array_reduce($detailing_hours_result, function($result, $arr){	
		    $result[$arr['project']] = $arr;
		    return $result;
		}, array());

		$checking_hours_qry 		= 'select project,SEC_TO_TIME(SUM(TIME_TO_SEC(detailing_time))+SUM(TIME_TO_SEC(study))+SUM(TIME_TO_SEC(discussion))+SUM(TIME_TO_SEC(rfi))+SUM(TIME_TO_SEC(checking))+SUM(TIME_TO_SEC(correction_time))+SUM(TIME_TO_SEC(other_works))+SUM(TIME_TO_SEC(bar_listing_time))+SUM(TIME_TO_SEC(revision_time))+SUM(TIME_TO_SEC(change_order_time))+SUM(TIME_TO_SEC(billable_hours))+SUM(TIME_TO_SEC(non_billable_hours))+SUM(TIME_TO_SEC(emails))+SUM(TIME_TO_SEC(was))+SUM(TIME_TO_SEC(co_checking))+SUM(TIME_TO_SEC(actual_billable_time))+SUM(TIME_TO_SEC(qa_checking))+SUM(TIME_TO_SEC(monitoring))+SUM(TIME_TO_SEC(bar_listing_checking))+SUM(TIME_TO_SEC(aec))+SUM(TIME_TO_SEC(credit))) as checking_hours from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id where emp_role = 5 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by project';
		$checking_hours_info   		= $this->db->query("CALL sp_a_run ('SELECT','$checking_hours_qry')");
		$checking_hours_result  	= $checking_hours_info->result_array();
		$checking_hours_info->next_result();
		$checking_hours_result  	= array_reduce($checking_hours_result, function($result, $arr){	
		    $result[$arr['project']] = $arr;
		    return $result;
		}, array());

		$pm_hours_qry 			= 'select project,SEC_TO_TIME(SUM(TIME_TO_SEC(detailing_time))+SUM(TIME_TO_SEC(study))+SUM(TIME_TO_SEC(discussion))+SUM(TIME_TO_SEC(rfi))+SUM(TIME_TO_SEC(checking))+SUM(TIME_TO_SEC(correction_time))+SUM(TIME_TO_SEC(other_works))+SUM(TIME_TO_SEC(bar_listing_time))+SUM(TIME_TO_SEC(revision_time))+SUM(TIME_TO_SEC(change_order_time))+SUM(TIME_TO_SEC(billable_hours))+SUM(TIME_TO_SEC(non_billable_hours))+SUM(TIME_TO_SEC(emails))+SUM(TIME_TO_SEC(was))+SUM(TIME_TO_SEC(co_checking))+SUM(TIME_TO_SEC(actual_billable_time))+SUM(TIME_TO_SEC(qa_checking))+SUM(TIME_TO_SEC(monitoring))+SUM(TIME_TO_SEC(bar_listing_checking))+SUM(TIME_TO_SEC(aec))+SUM(TIME_TO_SEC(credit))) as pm_hours from cw_time_sheet inner join cw_time_sheet_time_line on cw_time_sheet_time_line.prime_time_sheet_id = cw_time_sheet.prime_time_sheet_id where emp_role = 5 and cw_time_sheet.trans_status = 1 and cw_time_sheet_time_line.trans_status = 1 group by project';
		$pm_hours_info   		= $this->db->query("CALL sp_a_run ('SELECT','$pm_hours_qry')");
		$pm_hours_result  		= $pm_hours_info->result_array();
		$pm_hours_info->next_result();
		$pm_hours_result  		= array_reduce($pm_hours_result, function($result, $arr){	
		    $result[$arr['project']] = $arr;
		    return $result;
		}, array());
		
		require_once APPPATH."/third_party/PHPExcel.php";
		$obj = new PHPExcel();		
		//Set the first row as the header row
		$i =3;
		$excel_types[]['excel_column']= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE');
		$excel_types[]['excel_value']= array('Client No','RDD No','PO#',' Project Name','Client','General Contractor','Office','Team','US PM','Received Date','Estimated Tons','Detailed Tons','Balance Tons','# Sheets Detailed','Last Submission','Detailing Status','Revision Status','Release Status','Billing Rate Detailing','Billing Unit','Invoiced ton','Balance to invoice','Detailing Hours','Checking Hours','QA + QC Hours','CO Hours','Invoiced CO Hours','Balance to Invoice CO','Billing Rate Revision','Billing Unit','COST OF JOB');
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
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    )
			  ),
	    	'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );
	    $LeftBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    )
			  ),
	        'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $RightBorder  = array(
	    	'borders' => array(
			    'bottom' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'top' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'left' => array(
			      'style' => PHPExcel_Style_Border::BORDER_DOTTED
			    ),
			    'right' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THICK
			    )
			  ),
	        'alignment' => array(
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $HeaderRightBorder  = array(
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
	        )
	    );
	    $HeaderLeftBorder  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
	    $LeftBorderFoot  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $RightBorderFoot  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        )
	    );
	    $header_first  = array(
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
	            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        )
	    );

		for ($x = 0; $x <= 30; $x++) {
			$excel_column  = $excel_types[0]['excel_column'][$x];
			$excel_value   = $excel_types[1]['excel_value'][$x];
			$obj->getActiveSheet()->setCellValue('A'."1", "US Detailing Projects - Detailing & Billing Status as on January 2021")->mergeCells('A1:AE1')->getStyle('A1:AE1')->applyFromArray($header_first);
			if($excel_column === 'A'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderLeftBorder);
			}else
			if($excel_column === 'AE'){
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($HeaderRightBorder);
			}else{
				$obj->getActiveSheet()->setCellValue($excel_column."2", $excel_value)->getStyle($excel_column.'2')->applyFromArray($styleArray);
			}
		}
		$estimated_tons_total = 0;
		$sum_of_tonnage_total = 0;
		$balance_tons_total   = 0;
		foreach($detailing_result as $key => $detailing_sheet){
			$project_id 				= $detailing_sheet->prime_project_and_drawing_master_id;
			$estimated_tons 			= $detailing_sheet->estimated_tons;
			$estimated_tons_total		+= $estimated_tons; 
			$detailing_status_id 		= $detailing_sheet->detailing_status;
			$revision_status_id 		= $detailing_sheet->revision_status;
			$billing_unit_id 			= $detailing_sheet->billing_unit;
			$billing_unit_revision_id 	= $detailing_sheet->billing_unit_revision;
			$release_status_id 			= $detailing_sheet->release_status;
			$actual_billable_time 		= $tons_project_wise_result[$project_id][2]['actual_billable_time'];
			$sum_of_tonnage 			= $tons_project_wise_result[$project_id][1]['actual_tonnage'];
			$count_sheet1 				= $tons_project_wise_result[$project_id][1]['count_sheet'];
			$count_sheet2 				= $tons_project_wise_result[$project_id][2]['count_sheet'];
			$sum_of_tonnage_count 		= $count_sheet1 +$count_sheet2;
			$balance_tons 				= $estimated_tons-$sum_of_tonnage;
			$last_submission 			= $submission_date_wise_result[$project_id]['entry_date'];
			$detailing_status 			= $detailing_status_result[$detailing_status_id]['detailing_status'];
			$revision_status 			= $revision_status_result[$revision_status_id]['revision_status'];
			$release_status 			= $release_status_result[$release_status_id]['release_status'];
			$billing_unit 				= $billing_unit_result[$billing_unit_id]['billing_unit'];
			$billing_unit_revision 		= $billing_unit_result[$billing_unit_revision_id]['billing_unit'];
			$invoice_tons 				= $invoice_detail_result[$project_id]['invoiced_ton'];
			$billing_rate_detailing 	= $invoice_detail_result[$project_id]['billing_rate_detailing'];
			$balance_invoice_tons 		= $sum_of_tonnage-$invoice_tons;
			$invoiced_hours 			= $invoice_detail_result[$project_id]['invoiced_hours'];
			$billing_rate_revision 		= $invoice_detail_result[$project_id]['billing_rate_revision'];
			$sum_of_tonnage_total 	   += $sum_of_tonnage;
			$balance_tons_total 	   += $balance_tons;

			$time1         				= new DateTime($actual_billable_time);
			$time2         				= new DateTime($invoiced_hours);
			$timediff      				= $time1->diff($time2);
			$balance_invoice_time     	= $timediff->format('%H: %I');
			$multiple_tons 				= $sum_of_tonnage * $billing_rate_detailing;
			$time_to_hrs				= $this->time_to_min($actual_billable_time);
			$time_to_hrs				= $time_to_hrs/60;
			$time_to_hrs 				= round($time_to_hrs, 2);
			$multiple_hours 			= $time_to_hrs * $billing_rate_revision;
			$cost_of_job 				= $multiple_tons + $multiple_hours;
			$detailing_hours 			= $detailing_hours_result[$project_id]['detailing_hours'];
			$checking_hours 			= $checking_hours_result[$project_id]['checking_hours'];
			$pm_hours 					= $pm_hours_result[$project_id]['pm_hours'];

			$detailing_value['A']       = $detailing_sheet->client_no;
			$detailing_value['B']       = $detailing_sheet->rdd_no;
			$detailing_value['C']       = $detailing_sheet->purchase_order;
			$detailing_value['D']       = $detailing_sheet->project_name;
			$detailing_value['E']       = $detailing_sheet->client_name;
			$detailing_value['F'] 		= $detailing_sheet->general_contractor;
			$detailing_value['G'] 		= $detailing_sheet->branch;
			$detailing_value['H'] 		= $detailing_sheet->team_name;
			$detailing_value['I']		= $detailing_sheet->uspm;
			$detailing_value['J'] 		= $detailing_sheet->received_date;
			$detailing_value['K'] 		= $estimated_tons;
			$detailing_value['L'] 		= $sum_of_tonnage;
			$detailing_value['M'] 		= $balance_tons;
			$detailing_value['N'] 		= $sum_of_tonnage_count;
			$detailing_value['O'] 		= $last_submission;
			$detailing_value['P'] 		= $detailing_status;
			$detailing_value['Q'] 		= $revision_status;
			$detailing_value['R'] 		= $release_status;
			$detailing_value['S'] 		= $billing_rate_detailing;
			$detailing_value['T'] 		= $billing_unit;
			$detailing_value['U'] 		= $invoice_tons;
			$detailing_value['V'] 		= $balance_invoice_tons;
			$detailing_value['W'] 		= $detailing_hours;
			$detailing_value['X'] 		= $checking_hours;
			$detailing_value['Y'] 		= $pm_hours;
			$detailing_value['Z'] 		= $actual_billable_time;
			$detailing_value['AA'] 		= $invoiced_hours;
			$detailing_value['AB'] 		= $balance_invoice_time;
			$detailing_value['AC'] 		= $billing_rate_revision;
			$detailing_value['AD'] 		= $billing_unit_revision;
			$detailing_value['AE'] 		= $cost_of_job;

			for ($x = 0; $x <= 30; $x++) {
				$excel_column  		= $excel_types[0]['excel_column'][$x];
				$value_of_excel  	= $detailing_value[$excel_column];
				$start_cell 		= $excel_column.$range_start;
				$end_cell 			= $excel_column.$range_end;

				if($excel_column === 'A'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($LeftBorder);
				}else
				if($excel_column === 'AE'){
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($RightBorder);
				}else{
					$obj->getActiveSheet()->setCellValue($excel_column.$i, $value_of_excel)->getStyle($excel_column.$i)->applyFromArray($verticalStyle);
				}
				$counter = $i;
			}
			$i++;
		}	
		$detailing_result_count  	= count($detailing_result);
		$divide_estimate_tons 		= $estimated_tons_total/$detailing_result_count;
		$counter 					= $counter + 1;
		$counter2 					= $counter + 1;
		$counter3 					= $counter2 + 1;
		$final_total 				= $sum_of_tonnage_total/$sum_of_tonnage_total;
		$final_total 				= round($final_total, 2);

		$obj->getActiveSheet()->setCellValue('A'.$counter, " 01 January Booking Total =")->mergeCells('A'.$counter.':'.'J'.$counter)->getStyle('A'.$counter.':'.'J'.$counter)->applyFromArray($LeftBorderFoot);
		$obj->getActiveSheet()->setCellValue('K'.$counter,$estimated_tons_total)->getStyle('K'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter, "No Jobs =")->mergeCells('L'.$counter.':'.'O'.$counter)->getStyle('L'.$counter.':'.'O'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter,$detailing_result_count)->getStyle('P'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter,$divide_estimate_tons)->getStyle('Q'.$counter)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter, " ")->mergeCells('R'.$counter.':'.'AE'.$counter)->getStyle('R'.$counter.':'.'AE'.$counter)->applyFromArray($RightBorderFoot);
		$obj->getActiveSheet()->setCellValue('A'.$counter2, "Total")->mergeCells('A'.$counter2.':'.'J'.$counter2)->getStyle('A'.$counter2.':'.'J'.$counter2)->applyFromArray($LeftBorderFoot);
		$obj->getActiveSheet()->setCellValue('K'.$counter2,$estimated_tons_total)->getStyle('K'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter2,$sum_of_tonnage_total)->getStyle('L'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter2,$balance_tons_total)->getStyle('M'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('N'.$counter2,"")->getStyle('N'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('O'.$counter2,$detailing_result_count)->getStyle('O'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('P'.$counter2,"")->getStyle('P'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('Q'.$counter2,$divide_estimate_tons)->getStyle('Q'.$counter2)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('R'.$counter2, " ")->mergeCells('R'.$counter2.':'.'AE'.$counter2)->getStyle('R'.$counter2.':'.'AE'.$counter2)->applyFromArray($RightBorderFoot);
		$obj->getActiveSheet()->setCellValue('A'.$counter3, "")->mergeCells('A'.$counter3.':'.'J'.$counter3)->getStyle('A'.$counter3.':'.'J'.$counter3)->applyFromArray($LeftBorderFoot);
		$obj->getActiveSheet()->setCellValue('K'.$counter3,"")->getStyle('K'.$counter3)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('L'.$counter3,$final_total."%")->getStyle('L'.$counter3)->applyFromArray($FooterStyle);
		$obj->getActiveSheet()->setCellValue('M'.$counter3, "")->mergeCells('M'.$counter3.':'.'AE'.$counter3)->getStyle('M'.$counter3.':'.'AE'.$counter3)->applyFromArray($RightBorderFoot);

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
	public function time_to_min($time){
		$timeArr = explode(':', $time);
		$decTime = ($timeArr[0]*60) + ($timeArr[1]) + ($timeArr[2]/60);
		return $decTime;
	}
	public function datacount_check(){
		$multipick_val = $this->input->post("multipick_val");
		$multi_val 			 = (int)$multipick_val-1;
		$filter_cond 		 = urldecode($filter_cond);
		$fliter_val 		 = $this->input->post("fliter_val");
		$fliter_val_count 	 = count($fliter_val);
		$first_value 		 = array_splice($fliter_val,3,$multipick_val);
		$second_value 		 = array_splice($fliter_val,3,$fliter_val_count);
		$first_value 		 = implode(',', $first_value);
		$first_value 		 = rtrim($first_value,',');
		$first_value 		 = array($first_value);
		$first_value = array_reduce($first_value, function($result, $arr){			
		    $result[3] = $arr;
		    return $result;
		}, array());
		$first_value 	 	 = array_replace($fliter_val, $first_value, $first_value);
		$fliter_val 		 = array_merge($first_value,$second_value);
		$filter_count 		 = $this->input->post("filter_count");
		$fliter_label 		 = $this->input->post("fliter_label");
		$fliter_type 		 = $this->input->post("fliter_type");
		$filter_cond 		 = $this->input->post("filter_cond");
		$field_types 		 = $this->input->post("field_type");
		$filter_count        = count($fliter_label);
		$fliter_query        = "";
		$search_count        = 0;
		for($i=0;$i<=(int)$filter_count;$i++){
			$db_name     	 = $fliter_label[$i];
			$table_name  	 = $fliter_type[$i];
			$db_cond     	 = $filter_cond[$i];
			$db_value    	 = $fliter_val[$i];
			$field_type  	 = $field_types[$i];
			// echo "db_name ::$db_name<br>";
			if(($db_cond) && ($db_value)){
				if((int)$field_type === 7){
					$search_val    = $db_value;
					if($db_cond === "LIKE" || $db_cond === "="){ $search_val = "IN($db_value)";
						$db_cond = "";
						$db_name = "prime_team_id";
						$table_qry = " and cw_team";
					 }else{
					 	$table_qry = " and cw_project_and_drawing_master";
					 }
				}else
				if((int)$field_type === 4){
						$search_val = date('Y-m-d',strtotime($db_value));
						$search_val = $search_val;
						$table_qry = " and cw_project_and_drawing_master";
				}else{
					if($db_name === 'detailer_name' || $db_name === 'team_leader_name'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_tonnage_approval";
					}else
					if($db_name === 'billing_rate_revision' || $db_name === 'billing_rate_detailing'){
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master_invoice_details";
					}
					else{
						$search_val = $db_value;
						if($db_cond === "LIKE"){ $search_val = "$db_value%";}
						$table_qry = " and cw_project_and_drawing_master";
					}
					
				}
				if((int)$table_name === 1){ $fliter_query .= $table_qry.".". $db_name ." ". $db_cond .' '.$search_val.''; }
			}		
		}

		$control_name		= $this->control_name;
		$from_date 			= $this->input->post("from_date");
		$to_date 			= $this->input->post("to_date");
		$from_date 			= date('Y-m-d',strtotime($from_date));
		$to_date 			= date('Y-m-d',strtotime($to_date));
		$detailing_qry 		= 'select count(*) as rlst_count from cw_project_and_drawing_master inner join cw_client on cw_client.prime_client_id=cw_project_and_drawing_master.client_name inner join cw_branch on cw_branch.prime_branch_id=cw_project_and_drawing_master.branch inner join cw_general_contractor on cw_general_contractor.prime_general_contractor_id=cw_project_and_drawing_master.general_contractor inner join cw_team on cw_team.prime_team_id=cw_project_and_drawing_master.team join cw_uspm on cw_uspm.prime_uspm_id=cw_project_and_drawing_master.project_manager left join cw_project_and_drawing_master_invoice_details on cw_project_and_drawing_master_invoice_details.prime_project_and_drawing_master_id = cw_project_and_drawing_master.prime_project_and_drawing_master_id where cw_project_and_drawing_master.trans_created_date >= "'.$from_date.'" and cw_project_and_drawing_master.trans_created_date <= "'.$to_date.'" and cw_project_and_drawing_master.trans_status = 1 '.$fliter_query.' group by cw_project_and_drawing_master.project_name';
		$detailing_info   	= $this->db->query("CALL sp_a_run ('SELECT','$detailing_qry')");
		$detailing_result  	= $detailing_info->result();
		$detailing_info->next_result();
		$rlst_count 		= $detailing_result[0]->rlst_count;
		if((int)$rlst_count === 0){
			echo json_encode(array('success' => FALSE, 'message' => "No Data"));
		}else{
			echo json_encode(array('success' => TRUE, 'message' => "Data Available"));
		}
	}
}
?>