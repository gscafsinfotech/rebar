<?php
function transform_headers_readonly($array){
	$result = array();
	foreach($array as $key => $value){
		$result[] = array('field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp)', $value));
	}
	return json_encode($result);
}

function transform_headers($array){
	$result = array();
	$array  = array_merge(array(array('checkbox' => 'select', 'sortable' => FALSE)),
	$array, array(array('edit' => '')));
	foreach($array as $element){
		$result[] = array('field' => key($element), 'title' => current($element), 'switchable' => isset($element['switchable']) ? $element['switchable'] : !preg_match('(^$|&nbsp)', current($element)), 'sortable' => isset($element['sortable']) ?
		$element['sortable'] : current($element) != '', 'checkbox' => isset($element['checkbox']) ? $element['checkbox'] : FALSE, 'class' => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ? 'print_hide' : '');
	}
	return json_encode($result);
}

/* COMMON TABLE HEADER BASED ON SCREEN & SETTING */
function get_dbtable_headers($table_info){
	$CI =&get_instance();
	$access_data      = $CI->session->userdata('access_data');
	$controller_name  = strtolower(get_class($CI));
	$headers = array();
	foreach($table_info as $table){
		$headers[] = array($table->label_name => $table->view_name);
	}
	if($controller_name === "loan"){
		$headers[] = array('view'=>"");
		$headers[] = array('loan'=>"");
	}
	
	$headers[] = array('print'=>"");
	return transform_headers($headers);
}

/* COMMON TABLE ROW BASED ON SCREEN SETTING */
function get_dbdata_row($search_data,$table_info,$print_info,$controller){
	$CI =&get_instance();	
	$access_data      = $CI->session->userdata('access_data');
	$controller_name  = strtolower(get_class($CI));
	$access_update    = (int)$access_data[$controller_name]['access_update'];
	$prime_id         = "prime_".$controller_name."_id";
	$cf_id            = "prime_".$controller_name."_cf_id";	
	$page_name        = ucwords(str_replace("_"," ",$controller_name));
	$data_row         = array();
	$data_row[$prime_id] = $search_data->$prime_id;
	foreach($table_info as $table){
		$label_name  = $table->label_name;
		$field_type  = $table->field_type;
		$value       = $search_data->$label_name;
		if((int)$field_type === 4){
			$value = date('d-m-Y',strtotime($search_data->$label_name));
			if($label_name === "pay_month" || $label_name === "lock_month"){
				$value = $search_data->$label_name;
				if($value === "01-1970"){
					$value = "-";
				}
			}
			if($value === "01-01-1970"){
				$value = "-";
			}
		}else
		if((int)$field_type === 5){
			$ss = $search_data->$label_name;
			if(($label_name === "category") && (!$ss)){
				$value = "All";
			}else{
				$value = $search_data->$label_name;
			}
		}else
		if((int)$field_type === 6){
			$value = "No";
			if((int)$search_data->$label_name === 1){
				$value = "Yes";
			}
		}else
		if((int)$field_type === 7){
			if($search_data->$label_name){
				$value = $search_data->$label_name.",..";
			}else{
				$value = "-";
			}			
		}else
		if((int)$field_type === 10){
			$file_path = $search_data->$label_name;
			$path = base_url().$file_path;
			$value = '<img src="'.$path.'" class="zoom" style="max-width: 30% !important;max-height: 30% !important;"/>';
		}else{
			$value = $search_data->$label_name;
		}
		if($value){
			if($access_update === 1){
				$view_id    = $search_data->$prime_id;
				$data_row[$label_name] = anchor("$controller_name/view/$view_id", $value,array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Click to Update $page_name"));
			}else{
				$data_row[$label_name] = $value;
			}
		}else{
			$data_row[$label_name] = $value;
		}
	}
	$data_row['view']   = "";
	$data_row['loan']  = "";
	if($controller_name === "loan"){
		if($access_update === 1){
			$view_id    = $search_data->$prime_id;
			$data_row['view'] = anchor("$controller_name/installment/$view_id", '<span class="fa fa-building"></span> Installment',array('class'=>'modal-dlg btn btn-xs btn-primary', 'title'=>"View Installment"));
			$data_row['loan'] = anchor("$controller_name/loan_data/$view_id", '<span class="fa fa-clipboard"></span> Foreclose/Reopen',array('class'=>'modal-dlg btn btn-xs btn-info', 'title'=>"Loan Close/Reopen"));
		}
	}
	$data_row['edit'] = "";
	if($access_update === 1){
		$view_id    = $search_data->$prime_id;
		$print_list = "";
		foreach($print_info as $print){
			$print_doc_id    = $print->prime_print_info_id;
			$print_info_name = ucwords($print->print_info_name);
			$print_list     .= anchor($controller_name."/sent_print/$print_doc_id/$view_id", "$print_info_name",array( 'title'=>'Print','style'=>'display: block; padding: 8px;'));
		}
		if($print_list){
			$data_row['print'] = "<div class='dropdown'>
								  <button type='button' class='btn btn-xs btn-edit dropdown-toggle' data-toggle='dropdown'> <span class='fa fa-print fa-lg'></span> Print </button>
								  <div class='dropdown-menu' style='min-width: 130px !important;'>
									$print_list
								  </div>
								</div>";
		}
		if((($controller_name === "category") && ($view_id === "1")) || ($controller_name === "gender") || ($controller_name === "marital_status")){
			$data_row['edit'] = "";
		}else{
			$data_row['edit'] = anchor("$controller_name/view/$view_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Click to Update $page_name"));
		}
	}
	return $data_row;
}
/* FORM SETTING - START */
function get_form_setting_headers(){
	$CI=& get_instance();
	$headers = array(
		array('module_name'=>"Module Name"),
		array('menu_name'=>"Menu"),
		array('module_type'=>"Module Type"),
		array('show_module'=>"Module Status"),
		array('payroll'=>""),
	);
	return transform_headers($headers);
	}

function get_form_setting_datarows($page_setting,$controller){
	$CI=& get_instance();
	$controller_name=strtolower(get_class($CI));
	$page_name      = ucwords(str_replace("_"," ",$controller_name));
	$access_data    = $CI->session->userdata('access_data');
	$access_update  = (int)$access_data[$controller_name]['access_update'];
	$module_name    = $page_setting->module_id;
	$edit_opt = "";
	$payroll = "";
	if($access_update === 1){
		$title = "Update " . ucwords($page_setting->module_name);
		if($controller_name === "module_setting"){
			$edit_opt = anchor($controller_name."/module_view/$page_setting->module_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'title'=>"$title"));
		}else
		if($controller_name === "form_setting"){
			$edit_opt = anchor($controller_name."/view/$page_setting->module_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'title'=>"$title"));
			if($module_name === "monthly_input"){
				$edit_opt = "";
			}
		}else{
			$edit_opt = anchor($controller_name."/view/$page_setting->module_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'title'=>"$title"));
		}
	}
	$name = ucwords(str_replace("_"," ",$page_setting->module_id));
	$show_module = "Active";
	if((int)$page_setting->show_module === 0){
		$show_module = "In Active";
	}
	
	return array (
		'module_name' => ucwords($page_setting->module_name),
		'menu_name'   => ucwords($page_setting->menu_name),
		'module_type' => ucwords(strtolower($page_setting->module_type)),
		'show_module' => $show_module,
		'payroll'     => $payroll,
		'edit'        => $edit_opt
	);
}
/* FORM SETTING - END */

/* REPORT SETTING - START */
function get_report_setting_headers(){
	$CI=& get_instance();
	$headers = array(
		//array('prime_report_setting_id'=>"Report ID"),
		array('report_name'=>"Report Name"),
	);
	return transform_headers($headers);
}
function get_report_setting_datarows($report_setting,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$page_name           = ucwords(str_replace("_"," ",$controller_name));
	return array (
    'prime_report_setting_id' => $report_setting->prime_report_setting_id,
    'report_name' => $report_setting->report_name,
    'edit' => anchor($controller_name."/view/$report_setting->prime_report_setting_id", '<span class="fa fa-pencil-square-o"></span> Edit',
      array('class'=>'modal-dlg btn btn-xs btn-edit', 'title'=>" Update $page_name")),
	); 
}
/* REPORT SETTING - END */

/* COMMON TABLE HEADER BASED ON REPORT SETTING */
function get_report_headers($table_info){
	$CI =&get_instance();
	$controller_name  = strtolower(get_class($CI));
	$headers = array();
	foreach($table_info as $table){
		$headers[] = array($table->label_name => $table->view_name);
	}
	return transform_headers($headers);
}

/* COMMON TABLE ROW BASED ON REPORT SETTING */
function get_report_row($search_data,$table_info,$controller){
	$CI =&get_instance();
	$access_data      = $CI->session->userdata('access_data');
	$controller_name  = strtolower(get_class($CI));
	$access_update    = (int)$access_data[$controller_name]['access_update'];
	$prime_id         = "prime_".$controller_name."_id";	
	$page_name        = ucwords(str_replace("_"," ",$controller_name));
	$data_row         = array();
	$sub_total_exist  = false;
	$first_label_name = "";
	$count = 0;
	foreach($table_info as $table){
		$count++;
		$label_name         = $table->label_name;
		$field_type         = $table->field_type;
		$value              = $search_data->$label_name;
		if($count === 1){
			$first_label_name = $label_name;
		}
		if((int)$field_type === 4){
			$value = date('d-m-Y',strtotime($search_data->$label_name));
			if($value === "01-01-1970"){
				$value = "-";
			}
		}else
		if((int)$field_type === 6){
			$value = "No";
			if((int)$search_data->$label_name === 1){
				$value = "Yes";
			}
		}else
		if((int)$field_type === 7){
			if($search_data->$label_name){
				$value = $search_data->$label_name.",..";
			}else{
				$value = "-";
			}			
		}else{
			$value = $search_data->$label_name;
		}
		if($search_data->sub_total_exist){
			$data_row[$label_name] = "<span style='font-weight:bold;color:#CC3366;'>$value</span>";
		}else
		if($search_data->total_exist){
			$data_row[$label_name] = "<span style='font-weight:bold;color:#f32828;'>$value</span>";
		}else
		if($search_data->emp_tot_count){
			$data_row[$label_name] = "<span style='font-weight:bold;color:#f32828;'>$value</span>";
		}else{
			$data_row[$label_name] = $value;
		}
	}
	if($search_data->sub_total_exist){
		$data_row[$first_label_name] = "<span style='font-weight:bold;color:#CC3366;'>Sub Total</span>";
	}else
	if($search_data->total_exist){
		$data_row[$first_label_name] = "<span style='font-weight:bold;color:#f32828;'>Final Total</span>";
	}else
	if($search_data->emp_tot_count){
		$data_row[$first_label_name] = "<span style='font-weight:bold;color:#f32828;'>Total Employees count is</span>";
	}
	return $data_row;
}

/* BANK TEMPLATE SETTING - START */
function get_bank_template_setting_headers(){
	$CI=& get_instance();
	$headers = array(
		array('template_name'=>"Template Name"),
	);
	return transform_headers($headers);
}

function get_bank_template_setting_datarows($bank_template_setting,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$page_name           = ucwords(str_replace("_"," ",$controller_name));
	
	return array (
    'bank_template_setting_id' => $bank_template_setting->prime_bank_template_setting_id,
    'template_name' => $bank_template_setting->template_name,
    'edit' => anchor($controller_name."/view/$bank_template_setting->prime_bank_template_setting_id", '<span class="fa fa-pencil-square-o"></span> Edit', array('class'=>'modal-dlg btn btn-xs btn-edit', 'title'=>" Update $page_name")),
	);
}

/* BANK TEMPLATE SETTING - END */

/* COMMON TABLE HEADER BASED ON BANK TEMPLATE SETTING */
function get_bank_template_view_headers($table_info){
	$CI =&get_instance();
	$controller_name  = strtolower(get_class($CI));
	$headers = array();
	foreach($table_info as $table){
		$headers[] = array($table->label_name => $table->view_name);
	}
	return transform_headers($headers);
}

/* BSK EMPLOYEE PERMISSION HEADERS CUSTOM START */
function get_permission_headers(){
	$CI =& get_instance();
	$headers = array(
		array('role_name' => "Role")
	);
	return transform_headers($headers);
}
function get_permission_dbdata_row($permission, $controller){
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));
	return array(
		'role'      => $permission->role,
		'role_name' => $permission->role_name,
		'edit' => anchor("$controller_name/view/$permission->role", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Update Permission")));
}
/* BSK EMPLOYEE PERMISSION HEADERS CUSTOM END */

/* MRJ FANDF HEADERS CUSTOM START */
function get_fandf_headers($table_info){
	$CI =& get_instance();
	$headers = array();
	foreach($table_info as $table){
		$label_name  = $table->label_name;
		$view_name   = $table->view_name;
		if($label_name === "role"){
			$column_name  = "Category";
			$headers[] = array($label_name => $column_name);
		}else
		if($label_name === "employee_code"){
			$column_name  = $view_name;
			$headers[] = array($label_name => $column_name);
		}else
		if($label_name === "emp_name"){
			$column_name  = $view_name;
			$headers[] = array($label_name => $column_name);
		}else
		if($label_name === "resignation_date"){
			$column_name  = $view_name;
			$headers[] = array($label_name => $column_name);
		}else
		if($label_name === "last_working_date"){
			$column_name  = $view_name;
			$headers[] = array($label_name => $column_name);
		}
	}
	
	return transform_headers($headers);
}
function get_fandf_data_row($search_data,$table_info,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$page_name           = ucwords(str_replace("_"," ",$controller_name));
	$data_row            = array();
	$prime_id            = $search_data->employee_code;
	foreach($search_data as $key=>$value){
		$label_name     = $key;
		$view_name      = $value;
		if($label_name === "role"){
			$view_name     = $search_data->category_name;
		}
		if($label_name === "resignation_date"){
			$view_name = date('d-m-Y',strtotime($search_data->resignation_date));
		}
		if($label_name === "last_working_date"){
			$view_name = date('d-m-Y',strtotime($search_data->last_working_date));
		}
		$data_row[$label_name] = $view_name;
	}
	$data_row['edit'] = anchor("$controller_name/view/$prime_id", '<span class="fa fa-cog"></span> FandF Process',array('class'=>'modal-dlg btn btn-xs btn-primary', 'title'=>"Update $page_name"));
	return $data_row;
}

/* CUSTOM MONTHLY INPUT */
function get_monthly_headers($table_info,$lock_result){
	$CI =& get_instance();
	$headers = array();
	foreach($table_info as $table){
		$label_name   = $table->label_name;
		$view_name    = $table->view_name;
		$column_name  = ucwords(str_replace("_"," ",$label_name));
		$headers[$table->label_name]    = array($table->label_name => $view_name);
	}
	foreach($lock_result as $lock_info){
		if((int)$lock_info->column_status === 1){
			unset($headers[$lock_info->label_name]);
		}
	}
	return transform_headers($headers);
}

function get_monthly_row($search_data,$table_info,$controller,$lock_result,$payroll_count){
	$CI =&get_instance();	
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$prime_id            = "prime_".$controller_name."_id";
	$page_name           = ucwords(str_replace("_"," ",$controller_name));
	$data_row            = array();	
	$data_row[$prime_id] = $search_data->$prime_id;
	$input_id            =  $search_data->$prime_id;
	foreach($table_info as $table){
		$label_name  = $table->label_name;
		$value       = $search_data->$label_name;
		$read_only   = " ";
		if((int)$payroll_count > 0){
			$read_only   = "disabled";
		}
		foreach($lock_result as $lock){
			if(($lock->label_name === $label_name) && ((int)$lock->column_status === 2)){
				$read_only   = "disabled";
			}
		}
		if($label_name === "date_of_joining"){
			$doj      = $search_data->$label_name;
			$input_doj_id = "hid_doj_".$input_id;
			$input    =  "<input type='hidden' id='$input_doj_id' value='$doj'>".date('d-m-Y',strtotime($search_data->$label_name));
		}else
		if(($label_name === "process_month") || ($label_name === "employee_code") || ($label_name === "emp_name")){			
			if(($label_name === "process_month")){	
				$input_pro_id = "hid_process_month_".$input_id;
				$process_month = $search_data->$label_name;
				$input = "<input type='hidden' id='$input_pro_id' value='$process_month'>".$search_data->$label_name;
			}else
			if(($label_name === "employee_code")){	
				$input_pro_id = "hid_employee_code_".$input_id;
				$employee_code = $search_data->$label_name;
				$input = "<input type='hidden' id='$input_pro_id' value='$employee_code'>".$search_data->$label_name;
			}else{
				$input = "<input type='hidden' id='$input_pro_id' value='$employee_code'>".$search_data->$label_name;
			}
		}else
		if($label_name === "supplementary_status"){	
			$id = $label_name."_$input_id";
			$function = "onchange='update_table($input_id)'";
			if($value === "1"){
				$checked = "checked"; 
			}else{
				$checked = "";
			}
			$input = "<span style='display:none;'>$value</span><input type='checkbox' class='save_change' name='$label_name' id='$id' $function value='$value' $read_only $checked/>";
		}else{
			$id = $label_name."_$input_id";
			$function = "onchange='update_table($input_id)'";
			$input = "<span style='display:none;'>$value</span><input type='text' class='save_change' name='$label_name' id='$id' $function  value='$value' $read_only/>";
		}		
		$data_row[$label_name] = $input;
	}
	$data_row['edit'] = "";
	return $data_row;
}

function get_delete_column_row($table_info){
	foreach($table_info as $table){
		$label_name    = $table->label_name;
		if($label_name != 'employee_code' && $label_name != 'process_month' && $label_name != 'emp_name' && $label_name != 'date_of_joining'){
			$function    = "onclick='delete_column(\"$label_name\")'";
			$input       = "<button class='btn btn-xs btn-danger fliter' id='delete_btn_$label_name' $function style='margin-top:7px'><i class='fa fa-trash' aria-hidden='true'></i> Delete</button>";
			$data_row[$label_name] = $input;
		}
	}
	$data_row['edit'] = "";
	return $data_row;
}

/* CUSTOM MONTHLY INPUT */
function get_previous_headers(){
	$CI =& get_instance();
	$headers = array(
		array('previous_column'=>"Monthly Column"),
		array('matching_column'=>"Matching Column"),
		array('column_status'  =>"Column Status")
	);
	return transform_headers($headers);
}

function get_previous_data_row($search_data,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$view_id             = $search_data->prime_monthly_input_previous_id;
	if($search_data->column_status === "1"){
		$column_status = "Hide";
	}else
	if($search_data->column_status === "2"){
		$column_status = "Show & Lock";
	}else{
		$column_status = "Show & Edit";
	}
	return array (
		'prime_monthly_input_previous_id' => $search_data->prime_monthly_input_previous_id,
		'previous_column' => $search_data->previous_column,
		'matching_column' => $search_data->matching_column,
		'column_status'	  => $column_status,
		'edit' => anchor("$controller_name/view/$view_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Update $page_name"))
	);	
}

//PF CHallan Setting 
function get_pf_challan_headers(){
	$CI =& get_instance();
	$headers = array(
		array('order_no'=>"Order No"),
		array('column_name'=>"Column Name"),
		array('transaction_type'  =>"Transaction Type"),
		array('matching_field'  =>"Matching Field")
	);
	return transform_headers($headers);
}

function get_pf_challan_data_row($search_data,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$view_id             = $search_data->prime_pf_challan_setting_id;
	if($search_data->transaction_type === "cw_employees"){
		$transaction_type = "Masters";
	}else
	if($search_data->transaction_type === "cw_transactions"){
		$transaction_type = "Transaction";
	}
	return array (
		'prime_pf_challan_setting_id' => $search_data->prime_pf_challan_setting_id,
		'order_no'         => $search_data->order_no,
		'column_name'      => ucwords($search_data->column_name),		
		'transaction_type' => ucwords($transaction_type),
		'matching_field'   => ucwords($search_data->view_name),
		'edit' => anchor("$controller_name/view/$view_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Update $page_name"))
	);	
}

//Increment Header
/*function get_increment_headers($table_info,$lock_result){
	$CI =& get_instance();
	$headers = array(
		array('employee_code'=>"Employee Code"),
		array('apply_on'=>"Apply On"),
		array('column_name'  =>"Column Name"),
		array('current_value'  =>"Current Value"),
		array('new_value'      =>"New Value"),
		array('differences'     =>"Differences")
	);
	return transform_headers($headers);
}

function get_increment_row($search_data,$table_info,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$prime_id            = "prime_".$controller_name."_id";
	$page_name           = ucwords(str_replace("_"," ",$controller_name));

	return  array(
		'prime_increment_id' => $table_info->prime_increment_id,
		'employee_code'    => $table_info->employee_code,
		'apply_on'         => $table_info->apply_on,		
		'column_name'      => $table_info->column_name,
		'current_value'    => $table_info->current_value,
		'new_value'          => "<input type='text' id='new_value_$table_info->prime_increment_id' onchange=update_table($table_info->prime_increment_id,'$table_info->current_value','$table_info->employee_code','$table_info->apply_on','$table_info->column_name','$table_info->effective_date','$table_info->after_day')  value='$table_info->new_value'/>",
		'differences'      => $table_info->difference_value,
		'edit' => ''
	);	
}*/

//Arrear Mapping
function get_arrear_mapping_headers(){
	$CI =& get_instance();
	$headers = array(
		array('transaction_column'=>"Transaction Column"),
		array('arrear_column'=>"Arrear Column"),
		array('cr_inc_column'=>"Current Month Column")
	);
	return transform_headers($headers);
}

function get_arrear_mapping_data_row($search_data,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$view_id             = $search_data->prime_arrear_column_mapping_id;	
	return array (
		'prime_arrear_column_mapping_id' => $search_data->prime_arrear_column_mapping_id,
		'transaction_column' => $search_data->transaction_column,
		'arrear_column' => $search_data->arrear_column,
		'cr_inc_column' => $search_data->cr_inc_column,
		'edit' => anchor("$controller_name/view/$view_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>"Update $page_name"))
	);	
}

//supplementary details

function get_supplementary_headers($table_info){
	$CI =& get_instance();
	$headers = array();
	foreach($table_info as $table){
		$label_name   = $table->label_name;
		if($label_name == "employee_code"){
			$column_name  = ucwords(str_replace("_"," ",$label_name));
			$headers[$table->label_name]    = array($table->label_name => $column_name);
		}else
		if($label_name == "emp_name"){
			$column_name  = ucwords(str_replace("_"," ",$label_name));
			$headers[$table->label_name]    = array($table->label_name => $column_name);
		}else
		if($label_name == "date_of_joining"){
			$column_name  = ucwords(str_replace("_"," ",$label_name));
			$headers[$table->label_name]    = array($table->label_name => $column_name);
		}
	}
	$headers['paid_days']  = array('paid_days' => "Paid Days");
	$headers['lop_days']   = array('lop_days' => "Lop Days");
	return transform_headers($headers);
}

function get_supplementary_row($search_data,$table_info,$controller){
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$access_update       = (int)$access_data[$controller_name]['access_update'];
	$prime_id            = "prime_".$controller_name."_id";
	$page_name           = ucwords(str_replace("_"," ",$controller_name));
	
	return  array(
		'employee_code'      => $table_info->employee_code,
		'emp_name'           => $table_info->emp_name,		
		'date_of_joining'    => date('d-m-Y',strtotime($table_info->date_of_joining)),
		'paid_days'          => $table_info->paid_days,
		'lop_days'           => "<input type='text' value=''/>",
		'edit' => ''
	);
}

//Transaction Process Month Details--23AUG2019 -- MRJ-- Start
function get_process_month_headers(){
	$CI =& get_instance();
	$headers = array(
		array('transactions_month'=>"Payroll Process Month"),
		//array('trans_status'=>"Process Status")
	);
	return transform_headers($headers);
}

function get_process_month_data_row($search_data,$controller){
	//print_r($search_data);
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$sts = $search_data->trans_status;
	$process_month = $search_data->transactions_month;
	$process_month = '01-'.$process_month;
	$process_month = date('F-Y', strtotime($process_month));
	if((int)$sts === 1){
		$value = "YES";
	}
	return array (
		'transactions_month' => $process_month,
		//'trans_status' => $value
	);	
}
//Transaction Process Month Details--23AUG2019 -- MRJ-- End

//Previous company TDS details -- 26AUG2019 -- MRJ-- Start
function get_previous_company_headers(){
	$CI =& get_instance();
	$headers = array(
		array('employee_code'=>"Employee Code"),
		array('emp_name'=>"Employee Name"),
		array('date_of_joining'=>"Date of Joining"),
		array('previous_tax'=>"Tax")
		//array('trans_status'=>"Process Status")
	);
	return transform_headers($headers);
}

function get_previous_company_data_row($search_data,$controller){
	//print_r($search_data);
	
	$CI =&get_instance();
	$access_data         = $CI->session->userdata('access_data');
	$controller_name     = strtolower(get_class($CI));
	$doj     = date('d-m-Y',strtotime($search_data->date_of_joining));
	$pre_company_id     = $search_data->prime_previous_company_income_id;
	$previous_tax       = $search_data->previous_tax;
	return array (
		'employee_code' => $search_data->employee_code,
		'emp_name'      => $search_data->emp_name,
		'date_of_joining' => $doj,
		'previous_tax' => "<span style='display:none;'>$previous_tax</span><input type='text' id='previous_tax_$pre_company_id' value='$previous_tax' onchange='update_table($pre_company_id)'/>",
	);	
}
//Previous company TDS details--26AUG2019 -- MRJ-- End

//detailed reconciliation start svk
function get_detailed_reconciliation_headers(){
	$CI =& get_instance();
	$headers = array(
		array('setting_name'=>"Setting Name"),
		array('option'=>"Option")
	);
	return transform_headers($headers);
}

function get_detailed_reconciliation_data_row($search_data,$controller){
	$CI = &get_instance();
	$access_data              = $CI->session->userdata('access_data');
	$controller_name          = strtolower(get_class($CI));
	$prime_reconciliation_id  = $search_data->prime_reconciliation_id;
	$setting_name             = $search_data->setting_name;
	$process_month            = '01-'.$process_month;
	$process_month            = date('F-Y', strtotime($process_month));
	$view_opt                 = anchor($controller_name."/view/$prime_reconciliation_id", '<span class="fa fa-eye"></span> View',array('class'=>'modal-dlg btn btn-xs btn-primary', 'title'=>"View Products"));
	$generate_xl              = anchor($controller_name."/generate_excel/$prime_reconciliation_id", '<span class="fa fa-file-excel-o"></span> Generate Excel',array('class'=>'modal-dlg btn btn-xs btn-primary', 'title'=>"View Products"));	
	
	return array (
		'setting_name'       => $setting_name,
		'option'             => $view_opt." ".$generate_xl
	);	
}
//detailed reconciliation end--svk

/*EMPLOYEE ENVIRONMENT FORM SETTING - START */
function get_custom_form_setting_headers(){
	$CI=& get_instance();
	$headers = array(
		array('module_name'=>"Module Name"),
	);
	return transform_headers($headers);
	}

function get_custom_form_setting_datarows($page_setting,$controller){
	$CI=& get_instance();
	$controller_name=strtolower(get_class($CI));
	$page_name      = ucwords(str_replace("_"," ",$controller_name));
	$access_data    = $CI->session->userdata('access_data');
	$access_update  = (int)$access_data[$controller_name]['access_update'];
	$module_name    = $page_setting->module_id;
	$edit_opt = "";
	if($access_update === 1){
		$title = "Update " . ucwords($page_setting->module_name);
		$edit_opt = anchor("$controller_name/view/$page_setting->module_id", '<span class="fa fa-pencil-square-o"></span> Edit',array('class'=>'modal-dlg btn btn-xs btn-edit', 'data-btn-submit' => 'Submit', 'title'=>"Update $page_name"));
	}
	return array (
		'module_name' => ucwords($page_setting->module_name),
		'edit'        => $edit_opt
	);
}

/* EMPLOYEE ENVIRONMENT SETTING - END */


/* CUSTOM TABLE HEADER BASED ON SCREEN & SETTING */
function get_customtable_headers($table_info){
	$CI =&get_instance();
	$access_data      = $CI->session->userdata('access_data');
	$controller_name  = strtolower(get_class($CI));
	$headers = array();
	foreach($table_info as $table){
		$headers[] = array($table->label_name => $table->view_name);
	}
	$headers['user_status'] = array('trans_user_status'=>"User Status");
	return transform_headers($headers);
}

/* CUSTOM TABLE ROW BASED ON SCREEN SETTING */
function get_customdata_row($search_data,$table_info,$controller){
	$CI =&get_instance();	
	$access_data      = $CI->session->userdata('access_data');
	$controller_name  = "employees";
	$access_update    = (int)$access_data[$controller_name]['access_update'];
	$prime_id         = "prime_".$controller_name."_id";
	$page_name        = ucwords(str_replace("_"," ",$controller_name));
	$data_row         = array();
	$data_row[$prime_id] = $search_data->$prime_id;
	$user_status         = $search_data->trans_user_status;
	if((int)$user_status === 2){
		$user_sts = "<span style='font-weight:bold;color:red;'>Reject</span>";
	}else{
		$user_sts = "<span style='font-weight:bold;color:blue;'>Pending</span>";
	}
	foreach($table_info as $table){
		$label_name  = $table->label_name;
		$field_type  = $table->field_type;
		$value       = $search_data->$label_name;
		if((int)$field_type === 4){
			$value = date('d-m-Y',strtotime($search_data->$label_name));
			if($value === "01-01-1970"){
				$value = "-";
			}
		}else
		if((int)$field_type === 5){
			$value = $search_data->$label_name;
		}else
		if((int)$field_type === 6){
			$value = "No";
			if((int)$search_data->$label_name === 1){
				$value = "Yes";
			}
		}else
		if((int)$field_type === 7){
			if($search_data->$label_name){
				$value = $search_data->$label_name.",..";
			}else{
				$value = "-";
			}			
		}else
		if((int)$field_type === 10){
			$file_path = $search_data->$label_name;
			$path = base_url().$file_path;
			$value = '<img src="'.$path.'" class="zoom" style="max-width: 30% !important;max-height: 30% !important;"/>';
		}else{
			$value   = $search_data->$label_name;
			$view_id = $search_data->$prime_id;
		}
		$data_row[$label_name] = $value;
	}
	$data_row['trans_user_status'] = $user_sts;
	$data_row['edit'] = anchor("custom_approval/view/$view_id", '<span class="fa fa-check"></span> Approve',array('class'=>'modal-dlg btn btn-xs btn-success', 'data-btn-submit' => 'Submit', 'title'=>"Update User Status"));
	return $data_row;
}
?>
