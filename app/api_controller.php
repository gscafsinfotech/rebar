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
			$sql_emp_rslt   = $api_model->get_sql_emp("SELECT CODE,EMPNAME,DOB,SEX,TERMFLAG,TERMDATE FROM EMPLY_MASTER");
			/*echo "<pre>";
			print_r($sql_emp_rslt); die;*/
			$mysql_emp_rslt = $api_model->get_mysql_emp("select employee_code from cw_employees");
			foreach ($sql_emp_rslt as $key => $value){
				$employee_code = $value->CODE;				
				$emp_name      = $value->EMPNAME;	
				$dob           = $value->DOB;
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
				if($term_date){
					$term_date = $term_date->format("Y-m-d");
				}else{
					$term_date = "";
				}
				if($employee_code){
					if($mysql_emp_rslt[$employee_code]['employee_code']){						
						$prime_update_query  = 'UPDATE cw_employees SET emp_name = "'. $emp_name .'",user_name = "'. $employee_code .'",password = "'. md5($employee_code) .'",date_of_birth = "'.$dob.'",gender = "'.$gender.'",employee_status = "'.$term_flag.'",inactive_date = "'.$term_date.'" WHERE employee_code = "'. $employee_code .'"';
						$rslt = $api_model->runQuery($prime_update_query);
					}else{
						$sql = "insert into cw_employees (employee_code,emp_name,user_name,password,date_of_birth,gender,employee_status,inactive_date) values ('".$employee_code."','".$emp_name."','". $employee_code ."','".md5($employee_code)."','". $dob ."','". $gender ."','". $term_flag ."','". $term_date ."')";
						$rslt = $api_model->runQuery($sql);
					}
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