<?php
class Formula_model extends CI_Model{
	public function import_formula($trans_array){ 
		foreach($trans_array as $trans){
			$employee_code = $trans["employee_code"];
			if((int)$trans["role"] === 1){
				$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 2){
				$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 3){
				$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 4){
				$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 5){
				$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
		 $trans_array[$employee_code] = $trans;
		} 
		 
		return $trans_array;
	}
}
?>