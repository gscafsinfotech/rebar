 <?php
	$frm = "";
	/* Module Initilization */
	if (isset($_REQUEST["frm"])){
	    $frm = $_REQUEST['frm']; 
	    require("./api_model.php");
	    $api_model = new api_model;
	}
	if($frm === "get_employees"){
		$rslt ="";
		$sql_emp_rslt   = $api_model->get_sql_emp("SELECT CODE,EMPNAME,DOB,SEX,TERMFLAG,TERMDATE,DOJ FROM EMPLY_MASTER");			
		$mysql_emp_rslt = $api_model->get_mysql_emp("select employee_code from cw_employees");
		foreach ($sql_emp_rslt as $key => $value){
			$employee_code = $value->CODE;				
			$emp_name      = $value->EMPNAME;	
			$dob           = $value->DOB;
			$doj           = $value->DOJ;
			$gender        = $value->SEX;
			$term_flag     = $value->TERMFLAG;
			$term_date     = $value->TERMDATE;
			 
			if($gender === 'M'){
				$gender = 1;
			}else
			if($gender === 'F'){
				$gender = 2;
			}else{
				$gender = 0;
			}				
			if($term_flag === "L"){
				$term_flag = 1;
			}else{
				$term_flag = 2;
			}
			if($dob){
				$dob  = $dob->format("Y-m-d");
			}else{
				$dob  = "";
			}
			if($doj){
				$doj  = $doj->format("Y-m-d");
			}else{
				$doj  = "";
			}
			if($term_date){
				$term_date = $term_date->format("Y-m-d");
			}else{
				$term_date = "";
			}
			if($employee_code){
				if($mysql_emp_rslt[$employee_code]['employee_code']){
					$prime_update_query  = 'UPDATE cw_employees SET emp_name = "'. $emp_name .'",user_name = "'. $employee_code .'",password = "'. md5($employee_code) .'",date_of_birth = "'.$dob.'",date_of_joining = "'.$doj.'",gender = "'.$gender.'",employee_status = "'.$term_flag.'",inactive_date = "'.$term_date.'",trans_updated_by = "'.$term_date.'",inactive_date = "'.$term_date.'" WHERE employee_code = "'. $employee_code .'"';
					$rslt = $api_model->runQuery($prime_update_query);
				}else{
					$sql = "insert into cw_employees (employee_code,emp_name,user_name,password,date_of_birth,date_of_joining,gender,employee_status,inactive_date) values ('".$employee_code."','".$emp_name."','". $employee_code ."','".md5($employee_code)."','". $dob ."','". $doj ."','". $gender ."','". $term_flag ."','". $term_date ."')";
					$rslt = $api_model->runQuery($sql);
				}
			}			
		}			
		return_rslt($frm,$rslt);
	}else
	if($frm === "get_punched_data"){
		$date = new DateTime('2021-05-01');
		$period = new DatePeriod(
		     new DateTime('2021-05-01'),
		     new DateInterval('P1D'),
		     new DateTime('2021-05-31')
		);
		$punched_qry = "";
		foreach ($period as $key => $value) {
		    $punch_date = $value->format('Y-m-d');       
		    $rslt ="";
			$sql_emp_rslt   = $api_model->get_sql_emp("SELECT CODE,R_CODE,ENTRY_DATE,IN_TIME,IN_HOUR,OUT_TIME,OUT_HOUR,IN_DATE,OUT_DATE,VALID_DATA,ENTRY_TYPE,ENTRY_METHOD,HFDAY_TYPE,ENTRY_DAYS FROM TIME_ENTRY where ENTRY_DATE = '".$punch_date."'");	
			if($sql_emp_rslt){
				$mysql_emp_rslt = $api_model->get_mysql_emp("select employee_code from cw_punched_data_details where entry_date = '".$punch_date."'");				
				foreach ($sql_emp_rslt as $key => $value){
					$employee_code = $value->CODE;				
					$rcode         = $value->R_CODE;	
					$entry_date    = $value->ENTRY_DATE;
					$in_time       = $value->IN_TIME;
					$in_hour       = $value->IN_HOUR;
					$out_time      = $value->OUT_TIME;
					$out_hour      = $value->OUT_HOUR;
					$in_date       = $value->IN_DATE;
					$out_date      = $value->OUT_DATE;
					$valid_data    = $value->VALID_DATA;
					$entry_type    = $value->ENTRY_TYPE;
					$entry_method  = $value->ENTRY_METHOD;
					$hfday_type    = $value->HFDAY_TYPE;
					$entry_days    = $value->ENTRY_DAYS;
					//Get Total Time
					if($entry_date){
						$entry_date  = $entry_date->format("Y-m-d");
					}else{
						$entry_date  = "";
					}
					if($in_date){
						$in_date = $in_date->format("Y-m-d");
					}else{
						$in_date = "";
					}
					if($out_date){
						$out_date = $out_date->format("Y-m-d");
					}else{
						$out_date = "";
					}
					$time1         = new DateTime("$out_date $out_hour");
					$time2         = new DateTime("$in_date $in_hour");
					$timediff      = $time1->diff($time2);					
					$total_time    = $timediff->format('%H: %I');	
					//echo "BSK $employee_code :: $in_date :: $in_hour :: $out_date :: $out_hour :: $total_time <br/>";
					if($employee_code){
						if($mysql_emp_rslt[$employee_code]['employee_code']){
							$prime_update_query  = 'UPDATE cw_punched_data_details SET entry_date = "'. $entry_date .'",in_time = "'. $in_time .'",in_hour = "'. $in_hour .'",out_time = "'.$out_time.'",out_hour = "'.$out_hour.'",in_date = "'.$in_date.'",out_date = "'.$out_date.'",valid_data = "'.$valid_data.'",entry_type = "'.$entry_type.'",entry_method = "'.$entry_method.'",half_day_type = "'.$halfday_type.'",entry_days = "'.$entry_days.'",trans_updated_by = "1",trans_updated_date = "'.date("Y-m-d H:i:s").'" WHERE employee_code = "'. $employee_code .'" and entry_date = "'. $entry_date .'"';
							$rslt = $api_model->runQuery($prime_update_query);
							if($rslt){
								$prime_update_query  = 'UPDATE cw_time_sheet SET entry_date = "'. $entry_date .'",in_time = "'. $in_hour .'",out_time = "'.$out_hour.'",total_time = "'.$total_time.'",trans_updated_by = "1",trans_updated_date = "'.date("Y-m-d H:i:s").'" WHERE employee_code = "'. $employee_code .'" and entry_date = "'. $entry_date .'"';
								$rslt = $api_model->runQuery($prime_update_query);
							}
						}else{
							$punched_qry .= "('".$employee_code."','".$entry_date."','". $in_time ."','".$in_hour."','". $out_time ."','". $out_hour ."','". $in_date ."','". $out_date ."','".$valid_data."','". $entry_type ."','". $entry_method ."','". $halfday_type ."','". $entry_days ."','1','".date("Y-m-d H:i:s") ."'),";	
							$emp_qry .= "('".$employee_code."','".$entry_date."','". $in_hour ."','".$out_hour."','".$total_time."','1','".date("Y-m-d H:i:s") ."'),";											
						}
					}			
				}
			}	
		}
	$punched_qry = rtrim($punched_qry,",");
	$emp_qry     = rtrim($emp_qry,",");
	if($punched_qry){
		$sql = "insert into cw_punched_data_details(employee_code,entry_date,in_time,in_hour,out_time,out_hour,in_date,out_date,valid_data,entry_type,entry_method,half_day_type,entry_days,trans_created_by,trans_created_date) values $punched_qry";
		$punched_rslt = $api_model->runQuery($sql);
	}		
	if($punched_rslt){
		$sql = "insert into cw_time_sheet(employee_code,entry_date,in_time,out_time,total_time,trans_created_by,trans_created_date) values $emp_qry";
		$rslt = $api_model->runQuery($sql);
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