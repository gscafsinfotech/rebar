<?php 
$frm = "";
if(isset($_REQUEST["frm"])){
	$frm = $_REQUEST['frm'];
	require ("./emp_feedback_model.php");
	$emp_model = new emp_feedback_model;
}

if($frm === "verify_save"){
	$mobile_number = $_POST['mobile_number'];
	$offer_ref_no  = $_POST['offer_ref_no'];
	$emp_offer_exist    = $emp_model->check_employee_exist($mobile_number,$offer_ref_no);
	$emp_feedback_exist = $emp_model->check_feedback_exist($mobile_number,$offer_ref_no);
	if($emp_offer_exist){
		if($emp_feedback_exist){
			echo json_encode(array('success'=>false,'message'=>'Already Submited the Feedback','result'=>$emp_offer_exist));
		}else{
			echo json_encode(array('success'=>true,'message'=>'Exist','result'=>$emp_offer_exist));
		}		
	}else{
		echo json_encode(array('success'=>false,'message'=>'Please Enter the Valid Mobile Number or Reference Number'));
	}
}else
if($frm === "submit_feedback"){
	$fdata = json_decode($_POST['fdata']);
	$mobile_number = $_POST['mobile_number'];
	$offer_ref_no  = $_POST['offer_ref_no'];
	$rslt = $emp_model->save_feedback($mobile_number,$offer_ref_no,$fdata);
	if($rslt){
		echo json_encode(array('success'=>true,'message'=>'Feedback Submitted Successfully'));
	}else{
		echo json_encode(array('success'=>false,'message'=>'Please Try After Sometime'));
	}
}else{
	echo json_encode(array('success' => False, 'sts' =>"Invalid Request"));
}

?>