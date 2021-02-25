<?php
$send_for = "";
if(isset($_REQUEST['send_for'])){
	$send_for  = $_REQUEST['send_for'];
}
$send_from = "";
if(isset($_REQUEST['send_from'])){
	$send_from  = $_REQUEST['send_from'];
}
$extension = "";
if(isset($_REQUEST['extension'])){
	$file_type_str  = $_REQUEST['extension'];
	$file_type  = explode(",", $file_type_str);
}

if(($send_for !== "") && ($send_from !== "")){	
	if(!file_exists("./$send_from")){
		mkdir("./$send_from", 0777, true);
	}
	if($send_for === "import"){
		if(!file_exists("./$send_from/$send_for")){
			mkdir("./$send_from/$send_for", 0777, true);
		}
		$file_name     = str_replace("'","",$_FILES['excel_select_file']['name']);
		if($file_name){
			$random_digit  = rand(0000,99999999999);
			$new_file_name = $random_digit."_".$file_name;
			move_uploaded_file($_FILES['excel_select_file']['tmp_name'], "$send_from/$send_for/".$new_file_name);
			$path = "upload_files/$send_from/$send_for/".$new_file_name;
			echo json_encode(array('success' => true, 'msg' =>"File has been selected","path"=>$path));
		}else{
			echo json_encode(array('success' => false, 'msg' =>"Please upload valid file"));
		}
	}else{
		$file_name     = $_FILES[$send_for]['name'];
		if($file_name){
			/*$ext_array     = explode(".",$file_name);
			$ext           = $ext_array[1];*/
			$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			if(in_array($ext, $file_type)){
				$random_digit  = rand(0000,99999999999);
				$new_file_name = $random_digit."_".$file_name;
				//echo "BSK $send_from/$new_file_name".$_FILES[$send_for]['tmp_name']; die;
				move_uploaded_file($_FILES[$send_for]['tmp_name'], "$send_from/".$new_file_name);
			$path = "upload_files/$send_from/".$new_file_name;
				echo json_encode(array('success' => true, 'msg' =>"File moved to server","path"=>$path));
			}else{
				echo json_encode(array('success' => false, 'msg' =>"Please upload valid file such as $file_type_str"));
			}
			
		}else{
			echo json_encode(array('success' => false, 'msg' =>"Please upload valid file"));
		}
	}

	
}else{
	echo json_encode(array('success' => false, 'msg' =>"Please refresh page and retry"));
}
?>