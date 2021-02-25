 <?php 
	$frm = "";
	/* Module Initilization */
	if (isset($_POST["frm"])){
	    $frm = $_POST['frm']; 
	    require("./api_model.php");
	    $api_model = new api_model;
	}
	if($frm === "update_data"){
		$rms_code ="";
		if(isset($_POST['rms_code'])){
			$rms_code = $_POST['rms_code'];
		}
		$candidate_status ="";
		if(isset($_POST['candidate_status'])){
			$candidate_status = $_POST['candidate_status'];
		}
		$date ="";
		if(isset($_POST['act_join_date'])){
			$date = $_POST['act_join_date'];
		}
		$status_array = array("5"=>2);
		$candidate_status = $status_array[$candidate_status];
		$rslt ="";
		if($rms_code){
			$exit_qry  = 'select count(*) as rslt from cw_candidate_tracker where candidate_code ="'.$rms_code.'"';
			$exit_rslt =  $api_model->is_exit_data($exit_qry);
			if((int)$exit_rslt > 0){
				$prime_update_query  = 'UPDATE cw_candidate_tracker SET selected_status = "'. $candidate_status .'",date_of_joining = "'. $date .'" WHERE candidate_code = "'. $rms_code .'"';
				$rslt = $api_model->rms_update($prime_update_query);
			}
		}
		return_rslt($frm,$rslt);
	}else{
	    echo json_encode(array(
			'Status' => 400,
	        'success' => False,
	        'data' => "Bad Request"
	    ));
	}
	function return_rslt($frm,$rslt){
		if(!$rslt){
			echo json_encode(array('success' => FALSE, 'sts' =>"No Record found"));
		}else{
			echo json_encode(array('success' => TRUE, "$frm" => $rslt));
		}
	}
?>