 <?php 
	$frm = "";
	/* Module Initilization */
	if (isset($_POST["frm"])){
	    $frm = $_POST['frm']; 
	    require("./api_model.php");
	    $api_model = new api_model;
	}
	if($frm === "update_data"){
		$qry = "";
		$date ="";
		$source ="";
		if(isset($_POST['source'])){
			$source = $_POST['source'];
		}
		$rms_code ="";
		if(isset($_POST['rms_code'])){
			$rms_code = $_POST['rms_code'];
		}
		$candidate_status ="";
		if(isset($_POST['candidate_status'])){
			$candidate_status = $_POST['candidate_status'];
		}	

		if($source === "offer"){
			if(isset($_POST['act_join_date'])){
				$date = date("Y-m-d",strtotime($_POST['act_join_date']));
			}
			$status_array = array("5"=>2,"3"=>7);
			$qry = ',date_of_joining = "'. $date .'"';
		}else
		if($source === "custom"){
			if(isset($_POST['act_join_date'])){
				$date = date("Y-m-d",strtotime($_POST['act_join_date']));
			}
			$status_array = array("1"=>2,"4"=>2);
			$qry = ',date_of_joining = "'. $date .'"';
		}else
		if($source === "custom_resigned"){
			if(isset($_POST['manager_reason_date'])){
				$date = date("Y-m-d",strtotime($_POST['manager_reason_date']));
			}
			$status_array = array("4"=>6,"5"=>3,"6"=>4);
			$qry = ',abs_or_ter_date = "'. $date .'"';
		}else
		if($source === "employee"){			
			$status_array = array("1"=>6,"2"=>3,"3"=>4);
			if(isset($_POST['resignation_date'])){
				$date = date("Y-m-d",strtotime($_POST['resignation_date']));
			}			
			$qry = ',abs_or_ter_date = "'. $date .'"';
		}	
		$candidate_status = $status_array[$candidate_status];
		$rslt ="";
		if($rms_code){
			$exist_qry  = 'select count(*) as rslt from cw_candidate_tracker where candidate_code ="'.$rms_code.'"';
			$exist_rslt =  $api_model->is_exit_data($exist_qry);
			if((int)$exist_rslt > 0){
				$prime_update_query  = 'UPDATE cw_candidate_tracker SET selected_status = "'. $candidate_status .'" '.$qry.' WHERE candidate_code = "'. $rms_code .'"';
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