<?php 
/**********************************************************
	   Filename: Home
	Description: Chart view and Chart control logic developed, highchart integration based on role.
		 Author: Jaffer Sathik
	 Created on: 10-DEC-2018
	Reviewed by: Udhayakumar Anandhan (REVIEW PENDING)
	Reviewed on:
	Approved by:
	Approved on:
	-------------------------------------------------------
	Modification Details: HIGHCHARTS
	Modification Date: 06/12/2019
	Changed by: SVK AND NEHA
	Change Info: HIGHCHARTS
	-------------------------------------------------------
***********************************************************/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("Action_controller.php");
class Home extends Action_controller {
	public function __construct(){
		parent::__construct();
		$this->load->model("Homemodel");		
		$this->logged_id       = $this->session->userdata('logged_id');
		$this->logged_role     = $this->session->userdata('logged_role');
		$this->collect_base_info();
	}
	
	public function logout(){
		$this->session->sess_destroy();
		redirect('login');
	}
	
	public function index(){
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
		$logged_consultancy = $this->session->userdata('logged_consultancy');		
		$consult_code = base64_encode($logged_consultancy);
		$data['consult_url'] = base_url()."enrolment/?frm=consultancy&consultancy=".$consult_code;
		
		$this->load->view('home',$data);
	}	
	//TOP 10 EMPLOYEES BY LOP DAY
	public function get_candidate_sts_info(){
		$start_date     = date("Y-m-d",strtotime($this->input->post('start_date')));
		$end_date       = date("Y-m-d",strtotime($this->input->post('end_date')));
		$consultancy    = $this->session->userdata('logged_consultancy');
		$tble_line      = '';
		$tble_head      = '';
		if((int)$consultancy === 0){
			$consultancy_query    = 'SELECT count(*) as Total, COUNT(CASE WHEN candidate_status IN (1) THEN 1 END) "Pending", COUNT(CASE WHEN candidate_status IN (2) THEN 1 END) "Hold", COUNT(CASE WHEN candidate_status IN (3) THEN 1 END) "Selected", COUNT(CASE WHEN candidate_status IN (4) THEN 1 END) "Sel. NR", COUNT(CASE WHEN candidate_status IN (5) THEN 1 END) "Rejected", COUNT(CASE WHEN candidate_status IN (6) THEN 1 END) "Yet to", COUNT(CASE WHEN candidate_status IN (7) THEN 1 END) "Shortlisted", COUNT(CASE WHEN candidate_status IN (8) THEN 1 END) "NA", COUNT(CASE WHEN candidate_status IN (9) THEN 1 END) "Dept. Change" FROM cw_candidate_tracker where cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'"';
		}else{
		$consultancy_query    = 'SELECT count(*) as Total, COUNT(CASE WHEN candidate_status IN (1) THEN 1 END) "Pending", COUNT(CASE WHEN candidate_status IN (2) THEN 1 END) "Hold", COUNT(CASE WHEN candidate_status IN (3) THEN 1 END) "Selected", COUNT(CASE WHEN candidate_status IN (4) THEN 1 END) "Sel. NR", COUNT(CASE WHEN candidate_status IN (5) THEN 1 END) "Rejected", COUNT(CASE WHEN candidate_status IN (6) THEN 1 END) "Yet to", COUNT(CASE WHEN candidate_status IN (7) THEN 1 END) "Shortlisted", COUNT(CASE WHEN candidate_status IN (8) THEN 1 END) "NA", COUNT(CASE WHEN candidate_status IN (9) THEN 1 END) "Dept. Change" FROM cw_candidate_tracker where cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" and consultancy = "'.$consultancy.'"';
		}
		$consultancy_info   = $this->db->query("CALL sp_a_run ('SELECT','$consultancy_query')");
		$consultancy_result = $consultancy_info->result_array();
		$consultancy_info->next_result();
		$i =0;
		foreach($consultancy_result[0] as $key => $value){
			$reason     = strtolower(str_replace(" ","_",$key));
			$tble_head .= "<td>$key</td>";
			$tble_line .= "<td onclick = canditate_details('$start_date','$end_date','$i','$consultancy');>$value</td>";
			$i++;
		}
		if($tble_line === ''){
			$tble_line = "<tr><td colspan='3'></td></tr>";
		}
		$table_data = "<table class='table table-striped table-bordered' id='material_info_table'>
					<thead>
					<tr>$tble_head</tr>
					</thead>
					<tbody>
					<tr style='cursor: pointer;'>$tble_line</tr>
					</tbody>
				</table>";			
		echo json_encode(array("success" => TRUE,'message' => $table_data));
   }
   	// GENDER DISTRIBUTION CHART
	public function candidate_sts_chart(){
		$start_date = date("Y-m-d",strtotime($this->input->post('start_date')));
		$end_date   = date("Y-m-d",strtotime($this->input->post('end_date')));
		$consultancy    = $this->session->userdata('logged_consultancy');
		// echo $consultancy;exit;
		if((int)$consultancy === 0){
			$consultancy_query    = 'SELECT IFNULL(count(*),0) as sts_count,cw_candidate_status.candidate_status  FROM cw_candidate_tracker inner join cw_candidate_status on candidate_status_id = cw_candidate_tracker.candidate_status where cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" group by cw_candidate_status.candidate_status';
		}else{
		$consultancy_query    = 'SELECT IFNULL(count(*),0) as sts_count,cw_candidate_status.candidate_status  FROM cw_candidate_tracker inner join cw_candidate_status on candidate_status_id = cw_candidate_tracker.candidate_status where cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" and consultancy = "'.$consultancy.'" group by cw_candidate_status.candidate_status';
		}
		$consultancy_info   = $this->db->query("CALL sp_a_run ('SELECT','$consultancy_query')");
		$consultancy_result = $consultancy_info->result();
		$consultancy_info->next_result();
		//print_r($consultancy_result); die;
		$rows         = array();
		$rows['name'] = "Status";
		foreach($consultancy_result as $key => $rlst){
			$candidate_status  = $rlst->candidate_status;
			$count             = $rlst->sts_count;
			$rows['data'][] = array("name"=>$candidate_status,"y"=>$count);
		}
		$sts_array = array();
		array_push($sts_array,$rows);	
		echo json_encode(array('series' => $sts_array),JSON_NUMERIC_CHECK);
	}
	
	public function candiate_reason(){
		$start_date   = date("Y-m-d",strtotime($this->input->post('start_date')));
		$end_date     = date("Y-m-d",strtotime($this->input->post('end_date')));
		$reason       = $this->input->post('reason');
		$consultancy  = $this->input->post('consultancy');
		$reason_qry  = "";
		if((int)$reason > 0){
			$reason_qry = " and cw_candidate_tracker.candidate_status = $reason";
		}
		$rem_head = "";
		$rm_empty = "";
		if((int)$reason === 3) {
			$rem_head  = "<td>Date of Joining</td><td>Selected Status</td><td>Abscond/Ter. Date</td><td>Remark</td>";
			$rm_empty = "<td/><td/><td/>";
		}else
		if((int)$reason === 5) {
			$rem_head  = "<td>Remark</td>";
			$rm_empty = "<td/>";
		}
		$candiate_info_query    = 'select candidate_name, mobile_number, candidate_code, cw_department.department as department_name,position_name,cw_candidate_status.candidate_status as candidate_status,cw_zct_8_value as employee_type,cw_zct_10_value as selected_status,abs_or_ter_date,interviewer_remarks,date_of_joining from cw_candidate_tracker inner join cw_department on cw_department.prime_department_id = cw_candidate_tracker.department inner join cw_position on cw_position.prime_position_id = post_applied_for inner join cw_zct_8 on cw_zct_8.cw_zct_8_id = cw_candidate_tracker.employee_type left join cw_zct_10 on cw_zct_10.cw_zct_10_id = cw_candidate_tracker.selected_status inner join cw_candidate_status on cw_candidate_status.candidate_status_id = cw_candidate_tracker.candidate_status  where cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" and consultancy = "'.$consultancy.'"' .$reason_qry;
		$candiate_info   = $this->db->query("CALL sp_a_run ('SELECT','$candiate_info_query')");
		$candiate_result = $candiate_info->result();
		$candiate_info->next_result();
		if($candiate_result){
			$tr_line = "";
			$i = 1;
			$rem_td ="";
			foreach($candiate_result as $rslt){
				$candiate_name     = ucwords(strtolower($rslt->candidate_name));
				$mobile_number     = $rslt->mobile_number;
				$department        = $rslt->department_name;
				$post_for          = $rslt->position_name;
				$candidate_code    = $rslt->candidate_code;
				$candidate_status  = $rslt->candidate_status;
				$employee_type     = $rslt->employee_type;
				if((int)$reason === 3){
					$remarks           = $rslt->interviewer_remarks;
					$selected_sts      = $rslt->selected_status;
					$doj               = date("d-m-Y",strtotime($rslt->date_of_joining));
					$abs_or_ter_date   = date("d-m-Y",strtotime($rslt->abs_or_ter_date));
					if($abs_or_ter_date==="01-01-1970")
					$abs_or_ter_date="-";

					$rem_td            = "<td>".$doj."</td><td>".$selected_sts."</td><td>".$abs_or_ter_date."</td><td style='width:20%'>".$remarks."</td>";
				}else
				if((int)$reason === 5){
					$remarks           = $rslt->interviewer_remarks;
					$rem_td            = "<td style='width:20%'>".$remarks."</td>";
				}
				$tr_line .="<tr><td>".$i."</td><td>".$candiate_name."</td><td>".$mobile_number."</td><td>".$department."</td><td>".$post_for."</td><td>".$candidate_code."</td><td>".$employee_type."</td><td>".$candidate_status."</td>$rem_td</tr>";
				$i++;
			}
		}else{
			$tr_line = "<tr><td>No data found!</td><td/><td/><td/><td/><td/><td/><td/>$rm_empty</tr>";
		}
		$table_info = "<table class='table table-bordered' id='detail_list'>
							<thead>
								<tr>
									<td>Si. No</td>
									<td>Candiate Name</td>
									<td>Mobile</td>
									<td>Department</td>
									<td>Post For</td>
									<td>Job Code</td>
									<td>Employee Type</td>
									<td>Candidate Status</td>
									$rem_head
								</tr>
							</thead>
							<tbody>
								$tr_line
							</tbody>
						</table>";
		echo json_encode(array('table_info' => $table_info));
	}
	public function drilldown_chartss(){
		$start_date   = date("Y-m-d",strtotime($this->input->post('start_date')));
		$end_date     = date("Y-m-d",strtotime($this->input->post('end_date')));
		$consultancy  = $this->session->userdata('logged_consultancy');
		$consultancy_query    = 'SELECT IFNULL(count(*),0) as sts_count,cw_zct_5.cw_zct_5_value,cw_zct_5.cw_zct_5_id,cw_candidate_tracker.applied_by,cw_candidate_tracker.prime_candidate_tracker_id  FROM cw_candidate_tracker inner join cw_zct_5 on cw_zct_5_id = cw_candidate_tracker.applied_by where cw_candidate_tracker.trans_status = 1 and cw_zct_5.cw_zct_5_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" group by cw_zct_5.cw_zct_5_value';
		$consultancy_info   = $this->db->query("CALL sp_a_run ('SELECT','$consultancy_query')");
		$consultancy_result = $consultancy_info->result();
		$consultancy_info->next_result();
		$drill_list         = array();
		$rows['name'] = "Status";
		foreach($consultancy_result as $key => $rlst){
			$cw_zct_5_value = $rlst->cw_zct_5_value;
			$cw_zct_5_id = $rlst->cw_zct_5_id;
			$count             = $rlst->sts_count;
			$rows['data'][] = array("name"=>$cw_zct_5_value,"y"=>$count,"drilldown"=>$cw_zct_5_value);
		$consultancy_results = $this->get_consultancy_result($cw_zct_5_id,$start_date,$end_date);
		$drill = array();
		$drill['name'] = $cw_zct_5_value;
		$drill['id']   = $cw_zct_5_value;
		foreach ($consultancy_results as $key => $value){				
			$consultancy_name       = $value->consultancy_name;
			$sts_counts     = $value->sts_counts;
			$consultancy_id = $value->prime_consultancy_id;
			
			$drill['data'][] = array($consultancy_name,$sts_counts);
			array_push($drill_list,$drill);
		}
	}
		$rslt = array();		
		array_push($rslt,$rows);	
		echo json_encode(array('series' => $rslt, 'drill_list' => $drill_list),JSON_NUMERIC_CHECK);



	}
	public function get_consultancy_result($cw_zct_5_id,$start_date,$end_date){
		$consultancy_querys    = 'SELECT IFNULL(count(*),0) as sts_counts,cw_consultancy.consultancy_name FROM cw_candidate_tracker inner join cw_consultancy on prime_consultancy_id = cw_candidate_tracker.consultancy where cw_candidate_tracker.applied_by = "'.$cw_zct_5_id.'" and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" and cw_candidate_tracker.trans_status = 1  group by cw_consultancy.consultancy_name';
		$consultancy_infos   = $this->db->query("CALL sp_a_run ('SELECT','$consultancy_querys')");
		$consultancy_results = $consultancy_infos->result();
		$consultancy_infos->next_result();
		return $consultancy_results;
	}
	public function department_drilldown_charts(){
		$start_date   = date("Y-m-d",strtotime($this->input->post('start_date')));
		$end_date     = date("Y-m-d",strtotime($this->input->post('end_date')));

		$department_query    = 'SELECT IFNULL(count(*),0) as sts_count,cw_department.department,cw_candidate_tracker.post_applied_for,cw_department.prime_department_id FROM cw_candidate_tracker inner join cw_department on prime_department_id = cw_candidate_tracker.post_applied_for where cw_candidate_tracker.trans_status = 1 and cw_candidate_tracker.trans_status = 1 and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" group by cw_department.department';
		$department_info   = $this->db->query("CALL sp_a_run ('SELECT','$department_query')");
		$department_result = $department_info->result();
		$department_info->next_result();
		$drill_list         = array();
		$rows['name'] = "Status";
		foreach($department_result as $key => $rlst){
			$department 	= $rlst->department;
			$department_id  = $rlst->prime_department_id;
			$count 			= $rlst->sts_count;
			$rows['data'][] = array("name"=>$department,"y"=>$count,"drilldown"=>$department_id);
			$department_results = $this->get_department_result($department_id,$start_date,$end_date);
			$drill = array();
			$drill['name'] = $department_id;
			$drill['id']   = $department_id;
			foreach ($department_results as $key => $value){
				$position_name       = $value->position_name;
				$sts_counts       	 = $value->sts_counts;
				$drill['data'][] = array($position_name,$sts_counts);
				array_push($drill_list,$drill);
			}
		}
		$rslt = array();		
		array_push($rslt,$rows);	
		echo json_encode(array('series' => $rslt, 'drill_list' => $drill_list),JSON_NUMERIC_CHECK);
	}
	public function get_department_result($department_id,$start_date,$end_date){
		$department_querys    = 'SELECT IFNULL(count(*),0) as sts_counts,cw_position.position_name FROM cw_candidate_tracker inner join cw_position on prime_position_id = cw_candidate_tracker.department where cw_candidate_tracker.post_applied_for = "'.$department_id.'" and DATE_FORMAT(date_of_available, "%Y-%m-%d") between "'.$start_date.'" and "'.$end_date.'" and cw_candidate_tracker.trans_status = 1  group by cw_position.position_name';
			$department_infos   = $this->db->query("CALL sp_a_run ('SELECT','$department_querys')");
			$department_results = $department_infos->result();
			$department_infos->next_result();
		return $department_results;
	}
}

?>
