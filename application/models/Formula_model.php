<?php
class Formula_model extends CI_Model{
	public function import_formula($trans_array){ 
		foreach($trans_array as $trans){
			$employee_code = $trans["employee_code"];
			if((int)$trans["role"] === 1){
				$confirm_period = "+ ".$trans['confirmation_period']." months";	
$start_date = new DateTime($trans['date_of_joining']);
$custom_date = $start_date->modify("$confirm_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['confirmation_date']= $custom_date;$reteire_period = "+ ".$trans['retirement_years']." years";	
$start_date = new DateTime($trans['date_of_birth']);
$custom_date = $start_date->modify("$reteire_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['retirement_date']= $custom_date;$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 2){
				$confirm_period = "+ ".$trans['confirmation_period']." months";	
$start_date = new DateTime($trans['date_of_joining']);
$custom_date = $start_date->modify("$confirm_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['confirmation_date']= $custom_date;$reteire_period = "+ ".$trans['retirement_years']." years";	
$start_date = new DateTime($trans['date_of_birth']);
$custom_date = $start_date->modify("$reteire_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['retirement_date']= $custom_date;$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 3){
				$confirm_period = "+ ".$trans['confirmation_period']." months";	
$start_date = new DateTime($trans['date_of_joining']);
$custom_date = $start_date->modify("$confirm_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['confirmation_date']= $custom_date;$reteire_period = "+ ".$trans['retirement_years']." years";	
$start_date = new DateTime($trans['date_of_birth']);
$custom_date = $start_date->modify("$reteire_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['retirement_date']= $custom_date;$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 4){
				$confirm_period = "+ ".$trans['confirmation_period']." months";	
$start_date = new DateTime($trans['date_of_joining']);
$custom_date = $start_date->modify("$confirm_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['confirmation_date']= $custom_date;$reteire_period = "+ ".$trans['retirement_years']." years";	
$start_date = new DateTime($trans['date_of_birth']);
$custom_date = $start_date->modify("$reteire_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['retirement_date']= $custom_date;$date_1 = new DateTime($trans['date_of_birth']);
$date_2 = new DateTime(date('Y-m-d') );
$differ = $date_2->diff($date_1);
$age    =  (string)$differ ->y;
$trans['emp_age']= $age;
			}
			if((int)$trans["role"] === 5){
				$confirm_period = "+ ".$trans['confirmation_period']." months";	
$start_date = new DateTime($trans['date_of_joining']);
$custom_date = $start_date->modify("$confirm_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['confirmation_date']= $custom_date;$reteire_period = "+ ".$trans['retirement_years']." years";	
$start_date = new DateTime($trans['date_of_birth']);
$custom_date = $start_date->modify("$reteire_period");
$custom_date = $custom_date->format("d-m-Y");
$trans['retirement_date']= $custom_date;$date_1 = new DateTime($trans['date_of_birth']);
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