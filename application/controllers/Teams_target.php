<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Action_controller.php");
class Teams_target  extends Action_controller{	
	public function __construct(){
		parent::__construct('teams_target');
		$this->collect_base_info();
	}
	
	// LOAD PAGE QUICK LINK,FILTERS AND TABLE HEADERS
	public function index(){
		$data['quick_link']    = $this->quick_link;
		$data['table_head']    = $this->table_head;
		$data['master_pick']   = $this->master_pick;
		$data['fliter_list']   = $this->fliter_list;
		$team_qry 		= 'select prime_team_id,team_name from cw_team where trans_status = 1';
		$team_info      = $this->db->query("CALL sp_a_run ('SELECT','$team_qry')");
		$team_result    = $team_info->result();
		$team_info->next_result();
		$team_drop[""] = "---- Choose Team ----";
		foreach($team_result as $team){
			$team_id 			 = $team->prime_team_id;
			$team_name           = $team->team_name;
			$team_drop[$team_id] = $team_name;
		}
		$data['team_drop'] = $team_drop;
		$this->load->view("$this->control_name/manage",$data);
	}
	public function select_team_employees(){
		$team_id           = (int)$this->input->post("team");
		$emp_qry           = 'select employee_code,emp_name from cw_employees where team = "'.$team_id.'" and employee_status = 1 and trans_status = 1';
		$emp_info          = $this->db->query("CALL sp_a_run ('SELECT','$emp_qry')");
		$emp_result    	   = $emp_info->result();
		$emp_info->next_result();
		$emp_result_count  = count($emp_result);

		$unit_qry          = 'select prime_billing_unit_id,billing_unit from cw_billing_unit where trans_status = 1';
		$unit_info         = $this->db->query("CALL sp_a_run ('SELECT','$unit_qry')");
		$unit_result       = $unit_info->result();
		$unit_info->next_result();
		$table_head        = "<tr>
								<td>Employee Name</td>
								<td>Target Value</td>
								<td>Unit</td>
							  </tr>";
		$table_body        = "";
		$table_body        = "<input type='hidden' value='$emp_result_count' id='total_count'>";
		foreach($emp_result as $rslt){
			$employee_code = $rslt->employee_code;
			$emp_name      = $rslt->emp_name;
		  	$table_body   .= "<tr>
								<td>$employee_code - $emp_name <input type='hidden' value='$employee_code' name='sub_emp_code[]'></td>
								<td><input type='text' name='target_value[]' class ='form-control input-sm'></td>
								<td>
									<select name='target_unit[]' class ='form-control input-sm'>
										<option value=''>Select Unit</option>";
									foreach ($unit_result as $key => $value) {
			$table_body   .=			"<option value ='".$value->prime_billing_unit_id."'>".$value->billing_unit."</option>";
									}
									"</select>
								</td>
							  </tr>";
		}
		$table_body   	  .= "<tr>
								<td colspan='3' style='text-align:center;'>
									<div class='form-group'>
										<button class='btn btn-primary btn-sm' id='submit' style='margin-top: 17px;'>Submit</button>
									</div>
								</td>
							  </tr>";
		$table_content     = "<div style='margin:20px;'>
								<table class='table table-striped table-bordered' id='team_target'>
									<thead>
										$table_head
									</thead>
									<tbody>
										$table_body
									</tbody>
								</table>
							</div>";
		echo json_encode(array('success' => TRUE, 'message' => "Team Target",'table_content'=>$table_content));
	}
	public function team_target_save(){
		$total_count  = $this->input->post("total_count");
		$target_month    = $this->input->post("target_month");
		$team    		 = $this->input->post("team");
		$test[]['subs']    = $this->input->post("sub_emp_code");
		$test[]['vals']    = $this->input->post("target_value");
		$test[]['units']     = $this->input->post("target_unit");
		$total_count   = $total_count -1;
		$insert_value  = "";
		for ($x = 0; $x <= $total_count; $x++) {
		  $sub_id  = $test[0]['subs'][$x];
		  $target_value  = $test[1]['vals'][$x];
		  $target_unit  = $test[2]['units'][$x];

		  $insert_value  .= "('".$sub_id."','".$target_value."','".$target_unit."'),";
		  // echo "insert_value :: $insert_value";
		  // echo "<pre>";
		  // echo "target_value :: $target_value";
		  // echo "<br>";
		  // echo "target_unit :: $target_unit";
		}
		$remove_comma = rtrim($insert_value,',');
		$insert_qry    = "INSERT INTO cw_team_target_details (employee_code, target_value, target_unit) VALUES $remove_comma";
echo $insert_qry;die;
		$insert_info        = $this->db->query("CALL sp_a_run ('INSERT','$insert_qry')");
		echo "insert_info :: $insert_info";die;
				$insert_result      = $insert_info->result();
				$insert_info->next_result();
		// $insert_info->next_result();
		// echo $remove_comma;

		// $testing = "";
		// $i = 0;
		// foreach ($test as $key => $value) {
		// 	// if($key === 0){
		// 	// 	$subs =$value['subs'][$i];
		// 	// 	// print_r($subs);
		// 	// 	// echo "subs :: $subs";
		// 	// }
		// 	// echo $sub_id  = $test[0]['subs'][$i];
		// 	// $vals  = $test[0]['subs'][$key];
		// 	// $sub_id  = $test[0]['subs'][$key];
		// 	// echo "key :: $key";
		// 	// echo "check :: $i";
		// // 	// if($key ===$i){
		// 	// echo "key :: $i";
		// 	// 	// echo "<pre>";
		// 	// $subs =$value['subs'][$i];
		// 	// $vals =$value['vals'][$i];
		// 	// $units =$value['units'][$i];
		// 	// echo "<pre>";
		// 	// echo "subs :: $subs";
		// 	// echo "vals :: $vals";
		// 	// echo "units :: $units";
		// 	// print_r($value['vals'][$key]);
		// 	// print_r($value['units'][$key]);

		// 		// $testing .= $value[$key];
		// 		// echo "test :: $testing";
		// // 	// }else{
		// // 	// 	echo "hh";
		// // 	// }
		// 	$i++;
		// }
		// echo "sub_emp_code :: $sub_emp_code";
		// echo "target_month :: $target_month";
		// echo "target_month :: $target_month";
	}
}
?>