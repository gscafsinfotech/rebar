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
			$sql_emp_rslt   = $api_model->get_sql_emp("SELECT CODE,EMPNAME FROM EMPLY_MASTER");
			$mysql_emp_rslt = $api_model->get_mysql_emp("select employee_code from cw_employees");
			foreach ($sql_emp_rslt as $key => $value) {
				$employee_code = $value->CODE;				
				$emp_name      = $value->EMPNAME;	
				if($employee_code){
					//md5($str)
					if($mysql_emp_rslt[$employee_code]['employee_code']){
						$prime_update_query  = 'UPDATE cw_employees SET emp_name = "'. $emp_name .'",user_name = "'. $employee_code .'",password = "'. md5($employee_code) .'" WHERE employee_code = "'. $employee_code .'"';
						$rslt = $api_model->runQuery($prime_update_query);						
					}else{
						$sql = "insert into cw_employees (employee_code,emp_name,user_name,password) values ('".$employee_code."','".$emp_name."','". $employee_code ."','".md5($employee_code)."')";
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