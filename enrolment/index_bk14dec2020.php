
<?php
require ("./enrolment_model.php");
$api_model            = new enrolment_model;
$controller_name      = 'candidate_tracker';
$form_view            = $api_model->get_page_info($controller_name);
$view_info            = $form_view['view_info'];
$form_info            = $form_view['field_info'];
$row_view_list        = $form_view['row_view_list'];
$formula_result       = $form_view['formula_result'];
$condition_list       = $form_view['condition_list'];
$prime_id             = "prime_".$controller_name."_id";
$form_id              = $controller_name."_form";
$count                = 0;
$date_exist           = false; 
$drop_exist           = false;
$date_time_exist      = false; 
$view_count           = 0;
$view_content         = "";
$document_load_script = "";
$validation_rule      = "";
$tab_li               = "";
$tab_content          = "";
foreach($view_info as $view){
	$prime_form_view_id   = (int)$view->prime_form_view_id;
	$prime_view_module_id = $view->prime_view_module_id;
	$form_view_type       = (int)$view->form_view_type;
	$form_view_type_mode  = (int)$view->form_view_type_mode;
	$form_view_label_name = $view->form_view_label_name;
	$form_view_heading    = ucwords($view->form_view_heading);
	$input_box    		  = "";
	$row_check_input 	  = "";
	$row_prime_id 		  = "prime_".$controller_name."_".$form_view_label_name."_id";
	$row_send_data        = "view_id:'$prime_form_view_id',module_id:'$prime_view_module_id',row_label_name:'$form_view_label_name',row_prime_id:$('#$row_prime_id').val(),prime_id:$('#$prime_id').val(),";
	$row_clear_data       = "$('#$row_prime_id').val(0);\n";
	foreach($form_info as $setting){
		$prime_form_id    = (int)$setting->prime_form_id;
		$field_type       = $setting->field_type;
		$label_id         = $setting->label_name;
		$label_name       = ucwords($setting->view_name);
		$mandatory_field  = $setting->mandatory_field;
		$input_for        = (int)$setting->input_for;
		$field_isdefault  = (int)$setting->field_isdefault;
		$input_value      =  $setting->default_value;
		$file_type        = $setting->file_type;
		$extension        = $setting->upload_extension;
		$pick_table       = $setting->pick_table;
		$pick_list        = $setting->pick_list;
		$input_view_type  = (int)$setting->input_view_type;
		$input_for        = (int)$setting->input_for;
		$field_length     = $setting->field_length;
		$edit_read        = $setting->edit_read;
		$text_type        = (int)$setting->text_type;
		$accept_ext       = ".".str_replace(",", ",.", $extension);
		$required = "";
		if((int)$mandatory_field === 1){
			$required = "required";
		}
		//$input_value = $default_value;
		
		$color = "";
		foreach($formula_result as $formula){
			$formula_column = $formula->line_input_bind_to;
			if(strcmp($label_id, $formula_column) == 0){
				$color = "textcolor";
			}
		}
		
		if($prime_form_view_id === $input_for){
			/*=================== FORM INPUT PROCESS - START ===================*/
			$form_label = "<label for='$label_id' class='control-label $required $color'>$label_name</label>";
			$valid_class = "alpha";
			if(((int)$field_type === 2) ||((int)$field_type === 3) ||((int)$field_type === 11)){
				$valid_class = "number";
			}
			//TEXT BOX
			if((int)$field_type === 1){
				if($text_type === 1){
					$valid_class = "alpha_text";
				}else
				if($text_type === 2){
					$valid_class = " ";
				}
				if($input_value === "0"){
					$input_value = "";
				}				
				$form_input   =  "<input type='text' class='form-control input-sm $valid_class' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name' >";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//DECIMALS
			if((int)$field_type === 2){
				if($input_value === "0"){
					$input_value = "";
				}
				$form_input   =  "<input type='text' class='form-control input-sm $valid_class' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name' >";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//INTEGER
			if((int)$field_type === 3){
				if($input_value === "0"){
					$input_value = "";
				}
				$form_input   =  "<input type='text' class='form-control input-sm $valid_class' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name' >";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//DATE
			if((int)$field_type === 4){
				$date_exist = true;
				$date = "";
				if($input_value){
					$date = date('d-m-Y',strtotime($input_value));
					if($date === "01-01-1970"){
						$date = date("d-m-Y");
					}
				}
				$form_input =  "<input type='text' class='form-control input-sm datepicker' id='$label_id' name='$label_id' value='$date' placeholder='$label_name'>";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//PICKLIST
			if((int)$field_type === 5){
				$drop_exist = true;
				$option = "<option value=''>-- Select $label_name --</option>";//dropdown design options not present
				//$option = '';//dropdown design options not present
				foreach($form_view['all_pick'][$prime_form_id] as $opt_key =>$opt_value){
					$option .= "<option value='$opt_key'>$opt_value</option>";
				}
				$form_dropdown =  "<select class='form-control input-sm select2' name='$label_id' id='$label_id' style='width:100%'>$option</select>";
				$input_box .= "<div class='form-group'>$form_label $form_dropdown</div>";
			}else
			//CHECKBOX
			if((int)$field_type === 6){
				$form_input  =  "<label class='checkbox-inline'><input type='checkbox' value='1' name='$label_id' id='label_id'> </label>";
				$input_box  .= "<div class='form-group'> <label class='checkbox-inline'> $form_checkbox $form_label </label></div>";
			}else
			//MULTI PICKLIST
			if((int)$field_type === 7){
				$drop_exist = true;
				$multi_name   = $label_id."[]";
				$multi_select = explode(',',$input_value);
				$option = '';
				foreach($form_view['all_pick'][$prime_form_id] as $opt_key =>$opt_value){
					$option .= "<option value='$opt_key'>$opt_value</option>";
				}
				$form_dropdown =  "<select class='form-control input-sm select2' name='$multi_name' multiple  id='$label_id' style='width:100%'>$option</select>";
				$input_box .= "<div class='form-group'> $form_label $form_dropdown</div>";
			}else
			//TEXT AREA
			if((int)$field_type === 8){
				$value = $input_value;
				$input_box .= "<div class='form-group'> $form_label <textarea name='$label_id' id='$label_id' class='form-control' rows='4' placeholder='$label_name'>$value </textarea></div>";
			}else
			//AUTOCOMPLETE
			if((int)$field_type === 9){
				$hidden_id    = $label_id."_hidden_".$prime_form_id;
				$hidden_value = $all_pick[$prime_form_id];
				$form_input =  "<input type='text' class='form-control input-sm' id='$hidden_id' name='$hidden_id' value='$hidden_value' placeholder='$label_name'>";
				$hidden_input =  "<input type='hidden' class='form-control input-sm' id='$label_id' name='$label_id' value='$input_value'>";
				$input_box   .= "<div class='form-group'>$form_label $hidden_input $form_input</div>";
			}else
			//FILE UPLOAD
			if((int)$field_type === 10){
				$value       = $input_value;
				if((int)$value === 0){
					$value = "";
				}
				$upload_id   = "upload_".$label_id;
				$tabel_id   = "table_".$label_id;
				//$proof       = base_url("$value");
				$form_upload = "<input type='file' id='$upload_id' name='$upload_id' class='form-control input-sm' value='$input_value' accept='$accept_ext' />";
				
				$remove_btn = "";
				$view_btn = "";
				if($value){
					$file_name = explode("/",$value);
					$file_name = $file_name[2];
					$remove_btn = "<a onclick=remove_file('$prime_id','$field_isdefault','$label_id'); style='color: red; cursor: pointer;'><i class='fa fa-times' aria-hidden='true'></i></a>";
					$view_btn   = "<a href='$proof' target='_blank' style='cursor: pointer;'>$file_name</a>";
				}
				
				$input_box .= "<div class='form-group'>
								$form_label 
								<input type='hidden' id='$label_id' name='$label_id' value='$value'>
								$form_upload
								<table style='width: 100%;' id='$tabel_id'>
									<tr>
										<td colspan='2'><div class='progress_bar' id='div_$label_id' style='display:none;'><div class='process_percent' id='process_$label_id'>10%</div></div></td></tr>
									<tr>
									<tr>
										<td>$remove_btn</td>
										<td style='text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 100px;'>$view_btn</td>
									</tr>
								</table>
							 </div>";
			}else
			//MOBILE NUMBER
			if((int)$field_type === 11){
				$form_input =  "<input type='text' class='form-control input-sm $valid_class' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name'>";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//EMAIL
			if((int)$field_type === 12){
				$form_input =  "<input type='text' class='form-control input-sm' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name'>";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//DATE & TIME
			if((int)$field_type === 13){
				$date_time_exist = true;
				$date = "";
				if($input_value){
					$date = date('d-m-Y H:i:s',strtotime($input_value));
					if(strpos($date, '01-01-1970') !== false) {
						$date = date("d-m-Y H:i:s");
					}
				}
				$form_input =  "<input type='text' class='form-control input-sm datepicker_time' id='$label_id' name='$label_id' value='$date' placeholder='$label_name'>";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//READ ONLY
			if((int)$field_type === 14){
				$read = '';
				if((int)$form_view->$label_id > 0){
					$read = 'readonly';
				}
				$form_input =  "<input type='text' class='form-control input-sm datepicker_time' id='$label_id' name='$label_id' value='$input_value' placeholder='$label_name' $read = 'true'>";
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}
	
			/*=================== FORM INPUT PROCESS - END ===================*/
			
			/*================ FORM VALIDATION SCRIPT - START ================*/
			$len = "";
			if($field_length){
				$len = "maxlength: $field_length,";
			}
			$required = "";
			if((int)$mandatory_field === 1){
				$required = "required: true,";
			}
			if(($input_view_type === 1) || ($input_view_type === 2)){				
				if((int)$field_type === 1){ //TEXT
					$validation_rule .= "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 2){ //DECIMALS
					$validation_rule .=  "$label_id:{ $required number: true, $len },";
				}else					
				if((int)$field_type === 3){ //INTEGER
					$validation_rule .=  "$label_id:{ $required number: true, $len },";
				}else					
				if((int)$field_type === 4){ //DATE
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 5){ //PICKLIST
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 6){ //CHECKBOX
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 7){ //MULTI PICKLIST
					$multi_name = $label_id."[]";
					$validation_rule .= '"'.$multi_name.'":"required",'."\n";
				}else					
				if((int)$field_type === 8){ //SUMMARY BOX
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 9){ //AUTO COMPLETE BOX
					$hidden_id    = $label_id."_hidden_".$prime_form_id;
					$validation_rule .= "$hidden_id:{ $required $len },";
				}else					
				if((int)$field_type === 10){ //FILE UPLOAD BOX
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 11){ //MOBILE NUMBER
					$validation_rule .=  "$label_id:{ $required number: true, minlength:$field_length, maxlength:$field_length },";
				}else					
				if((int)$field_type === 12){ //EMAIL
					$validation_rule .=  "$label_id:{ $required email: true, $len },";
				}else					
				if((int)$field_type === 13){ //DATE & TIME
					$validation_rule .=  "$label_id:{ $required $len },";
				}else					
				if((int)$field_type === 14){ //READ ONLY
					$validation_rule .=  "$label_id:{ $required $len },";
				}
			}
			/*================= FORM VALIDATION SCRIPT - END =================*/
			
			/*================= DOCUMENT LOAD SCRIPT - START =================*/
			//ON-LOAD SCRIPT MULTI SELECT REMOVE EMPTY
			if((int)$field_type === 7){
				if(!$form_view->$label_id){
					$document_load_script .= "$('#$label_id option:selected').removeAttr('selected');\n";
				}
			}
			//ON-LOAD SCRIPT FOR SINGLE AUTOCOMPLET BOX
			if((int)$field_type === 9){
				$hidden_id = $label_id."_hidden_".$prime_form_id;
				$auto_id  = "#$label_id";
				$auto_url = site_url("$controller_name/suggest?prime_form_id=$prime_form_id");
				$document_load_script .= "$('#$hidden_id').autocomplete({
						source: '$auto_url',
						minChars:2,
						autoFocus: true,
						delay:10,
						appendTo: '.modal-content',
						select: function(e, ui) {
							e.preventDefault();
							value = ui.item.value;
							label = ui.item.label;
							$('$auto_id').val(ui.item.value);
							$('#$hidden_id').val(ui.item.display_name);
						}
					});\n";
			}
			//ON-LOAD SCRIPT FILE UPLOAD INPUT BOX
			/*if((int)$field_type === 10){
				$upload_id   = "upload_".$label_id;
				$send_url	 = "upload_files/upload.php?send_from=$controller_name&send_for=$upload_id&extension=$extension";
				$document_load_script .= "$('#$upload_id').change(function() {
						var file_data = $('#$upload_id').prop('files')[0];
						if(file_data){	
							var form_data = new FormData();
							form_data.append('$upload_id', file_data);
							$.ajax({
								url: '$send_url',
								cache: false,
								contentType: false,
								processData: false,
								data: form_data,
								type: 'post',
								success: function(result_data){
									var rslt = JSON.parse(result_data);
									if(rslt['success']){
										$('#$label_id').val(rslt['path']);
									}else{
										toastr.error(rslt['msg']);
									}
								}
							});
						}else{
							toastr.error('Please select file to upload');
						}
					});\n";
			}*/
			if((int)$field_type === 10){
				$upload_id   = "upload_".$label_id;
				$check_size  = 2;
				$send_url	 = "upload_files/upload.php?send_from=$controller_name&send_for=$upload_id&extension=$extension";
				$document_load_script .= "$('#$upload_id').change(function() {
					var file_data = $('#$upload_id').prop('files')[0];
					var size = file_data.size;
						if(file_data){
							if(check_upload_size(size)){
								var form_data = new FormData();
								form_data.append('$upload_id', file_data);
								$.ajax({
									url: '$send_url',
									cache: false,
									contentType: false,
									processData: false,
									data: form_data,
									beforeSend: function(){
									  $('#div_$label_id').show();
									  $('#submit').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...');
									  $('#submit').attr('disabled','disabled');
									  progress_bar('$label_id');
									},
									type: 'post',
									success: function(result_data){
										var rslt = JSON.parse(result_data);
										if(rslt['success']){
											$('#$label_id').val(rslt['path']);
										}else{
											toastr.error(rslt['msg']);
											$('#$upload_id').val('');
										}
									}
								});
							 }else{
								toastr.error('Please select file size below or equal to 2mb');
								$('#$upload_id').val('');
							}
						}else{
							toastr.error('Please select file to upload');
						}
				});\n";
			}
			//ON-LOAD SCRIPT FOR ROW SET AUTO SAVE PRIMARY FORM			
			if(((int)$input_view_type === 3) && ((int)$form_view->$prime_id === 0) && ((int)$view_count === 0)){
				$view_count++;
				$change_event = "focusout";
				$auto_save_id = $label_id;
				if(((int)$field_type === 4)|| ((int)$field_type === 13)){
					$change_event = "blur"; 
					$auto_save_id = $label_id;					
				}else
				if(((int)$field_type === 5)|| ((int)$field_type === 7)){
					$change_event = "change"; 
					$auto_save_id = $label_id;
				}else
				if((int)$field_type === 9){
					$hidden_id = $label_id."_hidden_".$prime_form_id;
					$auto_save_id = $hidden_id;
				}				
				/*$document_load_script .= "$('#$auto_save_id').bind('$change_event', function(e) {	
							e.preventDefault();
							if($(form_id).valid()){
								$(form_id).submit();
							}else{
								$('#$auto_save_id').val('');
								toastr.clear();
								toastr.error('Please fill all required in previous tab');
								$('.row_btn').hide();
							}
						});\n";*/
			}
			///ON-LOAD SCRIPT FOR ROW SET AUTO SAVE
			if((int)$input_view_type === 3){
				if((int)$mandatory_field === 1){
					$check_input_id = $label_id;
					if((int)$field_type === 9){
						$hidden_id = $label_id."_hidden_".$prime_form_id;
						$check_input_id = $label_id;
					}
					$row_check_input .= "#$check_input_id,";
				}
				$row_send_data  .= "$label_id:$('#$label_id').val(),";
				
				if((int)$field_type === 6){
					$row_clear_data .= "$('#$label_id').prop('checked', false);\n";
				}else
				if((int)$field_type === 7){
					$row_clear_data .= "$('#$label_id option:selected').removeAttr('selected');\n";
				}else{
					$row_clear_data .= "$('#$label_id').val('');\n";
				}
				
				
			}
			/*================== DOCUMENT LOAD SCRIPT - END ==================*/
		}
	}
	if($form_view_type === 1){
		$view_content .= "<h4 class='block_head'>$form_view_heading</h4>
						<div id='$form_view_label_name' class='block_content pd8'>
							$input_box
						</div>";
	}else
	if($form_view_type === 2){
		$count++;
		$tab_active = "";
		$content_active = "";
		if((int)$count === 1){
			$tab_active = "active"; 
			$content_active = "in active"; 
			$view_content .= "<div class='block_content'>
								<ul class='nav nav-tabs' data-tabs='tabs'>
									@TABLI
								</ul>
								<div class='tab-content' style='padding:8px;'>
									@TABCONTENT
								</div>
							</div>";
		}
		$tab_li .= "<li class='$tab_active'>
						<a data-toggle='tab' href='#$form_view_label_name'>$form_view_heading</a>
					</li>";
		$tab_content .= "<div class='tab-pane fade $content_active' id='$form_view_label_name' >
							<h4 class='tab_head'>$form_view_heading</h4>
							$input_box
						</div>";
						
	}else
	if($form_view_type === 3){
		$div_id        = $row_view_list[$prime_form_view_id]['div_id'];
		$table_id      = $row_view_list[$prime_form_view_id]['table_id'];
		$row_set_view  = $row_view_list[$prime_form_view_id]['row_set_view'];
		$style = "";
		/*if((int)$form_view->$prime_id === 0){
			$style = "style='display:none;'";
		}*/
		$submit_btn_id   = "row_save_$prime_form_view_id";
		$cancel_btn_id   = "row_cancel_$prime_form_view_id";
		$row_prime_input =  "<input type='hidden' class='form-control input-sm' id='$row_prime_id' name='$row_prime_id' value='0'>";
		$final_div = "";
		if($input_box){
			$final_div = "$row_prime_input
							$input_box
							<div class='form-group'>
								<a class='btn btn-primary btn-sm row_btn' id='$submit_btn_id' style='margin-top:20px;'>Add/Update</a>
								<a class='btn btn-danger btn-sm row_btn' id='$cancel_btn_id' style='margin-top:20px;'>Cancel</a>
							</div>
							<div id='$div_id' class='row_set_div'>
								$row_set_view
							</div>";
		}
		if($form_view_type_mode === 1){
			$view_content .= "<h4 class='block_head'>$form_view_heading</h4>
						<div class='block_content pd8'>
							$final_div
						</div>";
		}else
		if($form_view_type_mode === 2){
			$tab_active = "active"; 
			$content_active = "in active";
			if((int)$count >= 1){
				$tab_active = "";
				$content_active = "";
			}
			$tab_li .= "<li role='presentation' class='$tab_active' id='li_$form_view_label_name'>
							<a data-toggle='tab' href='#$form_view_label_name'>$form_view_heading</a>
						</li>";
			$tab_content .= "<div class='tab-pane fade $content_active' id='$form_view_label_name' >
								<h4 class='tab_head'>$form_view_heading</h4>
								$final_div
							</div>";
		}
		
		$send_url        = "enrolment_callback.php?frm=rowset_save";//site_url("$this->control_name/rowset_save");
		$row_check_input = rtrim($row_check_input,',');
		$row_clear_data  = rtrim($row_clear_data,',');
		$row_send_data   = "{".rtrim($row_send_data,',')."}";
		$loader = "<i class='fa fa-spinner fa-spin'></i>";
		if($row_check_input){
			$row_check_input = "var isValid = true;
								$('$row_check_input').each(function(){
								  if (($(this).val() === '') && (!$(this).hasClass('ignore'))) {
									isValid = false;
									toastr.error('Please fill all required field');
									$(this).addClass('error');
								  }else{
									 $(this).removeClass('error');
								  }
								});
								if(isValid){
									$('#$submit_btn_id').html('Processing...');
									$('#$submit_btn_id').attr('disabled','disabled');
									$.ajax({
										type: 'POST',
										url: '$send_url',
										data:$row_send_data,
										success: function(data) {
											$('#$submit_btn_id').attr('disabled',false);
											$('#$submit_btn_id').html('Add/Update');
											var rslt = JSON.parse(data);
											if(rslt.success){
												//toastr.success(rslt.message);
												$('#'+rslt.row_set_data.div_id).html(rslt.row_set_data.row_set_view);
												$row_clear_data
												$('#'+rslt.row_set_data.table_id).DataTable();
											}else{
												toastr.error(rslt.message);
											}
										}
									});
								}";
		}else{
			$row_check_input = "$.ajax({
									type: 'POST',
									url: '$send_url',
									data:$row_send_data,
									success: function(data) {
										var rslt = JSON.parse(data);
										if(rslt.success){
											//toastr.success(rslt.message);
											$('#'+rslt.row_set_data.div_id).html(rslt.row_set_data.row_set_view);
											$row_clear_data										
											$('#'+rslt.row_set_data.table_id).DataTable();
										}else{
											toastr.error(rslt.message);
										}
									}
								});";
		}
		
		$document_load_script .= "$('#$submit_btn_id').click(function(){
									$row_check_input
									
								});\n
								$('#$cancel_btn_id').click(function(){
									$row_clear_data
									});\n
								$('#$table_id').DataTable();\n";
	}
}

$view_content  = str_replace("@TABLI",$tab_li,$view_content);
$view_content  = str_replace("@TABCONTENT",$tab_content,$view_content);
$form_open     = "<form class='form-inline' id='$form_id' method='post' action='enrolment_callback.php?frm=save'>";
$prime_input   = "<input type='hidden' class='form-control input-sm' id='$prime_id' name='$prime_id' value='0'>";
$form_close    = "</form>";
$form_submit    = "<div class='col-md-12 text-right'><button type='submit' class='btn btn-primary' id='submit'>Submit</button></div>";
$mobile_number = $_SESSION['mobile_number'];
?>

<!-- <!doctype html> -->
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">		
		<title>Employee Enrolment View</title>
		<!-- Latest compiled and minified CSS -->
		<script type="text/javascript" src="../dist/opensourcepos.min.js?rel=20191228"></script>
		<script type="text/javascript" src="../dist/validate.js?rel=20191228"></script>
		<link rel="stylesheet" type="text/css" href="../dist/bootstrap.min.css?rel=20191228"/>
		<link rel="stylesheet" type="text/css" href="../dist/cafs_rms.css?rel=20191228"/>
		<link rel="stylesheet" type="text/css" href="../dist/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="../dist/font-awesome.min.css"/>
		<!-- DATE TIME PICKER -->
		<link rel="stylesheet" type="text/css" href="../dist/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css"/>	
		<script type="text/javascript" src="../dist/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js"></script>
		<!-- DATE TIME PICKER -->
		<!-- MULTI SELECT -->
		<link rel="stylesheet" type="text/css" href="../dist/select2/dist/css/select2.min.css"/>
		<script type="text/javascript" src="../dist/jquery-typeahead/dist/jquery.typeahead.min.js"></script>
		<script type="text/javascript" src="../dist/select2/dist/js/select2.full.min.js"></script>
		<!-- MULTI SELECT -->
		<!-- TOASTR -->
		<script type="text/javascript" src="../dist/toastr/toastr.js"></script>
		<link rel="stylesheet" type="text/css" href="../dist/toastr/toastr.css"/>	
		<!-- TOASTR -->
		<!-- DATA TABLE -->
		<link rel="stylesheet" type="text/css" href="../dist/data_table/datatables.min.css"/>	
		<script type="text/javascript" src="../dist/data_table/datatables.min.js"></script>
		<!-- DATA TABLE -->	
		<!-- Confirm Dialog Start-->
		<link href="../dist/jquery_confirm/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
		<script src="../dist/jquery_confirm/jquery-confirm.min.js" type="text/javascript"></script>
		<script src="https://code.jquery.com/jquery-migrate-1.3.0.js"></script>
<!-- <script type="text/javascript">
	$(document).ready(function(){
		var ss = Object.keys(jQuery.browser)[0];
	});
</script> -->
		<!-- Confirm Dialog End -->
	</head>
	<body style="background-image: url('./asset/images/employment.jpeg');background-repeat: no-repeat;background-size: cover;">			
		<div class="modal-dialog" id='custom_form'>
			<div style='padding: 15px 20px;'>
				<div class='row'>
					<div class='col-md-3 col-xs-12 en_log'>
						<img class="logo" style="height: 42px; width: auto;" src="https://cafsindia.com/wp-content/uploads/2020/01/CAFS-logo.png" alt="Logo">
					</div>
					<div class='col-md-9 col-xs-12 en_head'>
						Let's get start with CAFS
					</div>
				</div>
			</div>
			<div class="modal-content">
				<div class="modal-header bootstrap-dialog-draggable">
					<div class="bootstrap-dialog-header">
						<div class='row' style="margin:0px;">							
							<?php 
								echo "$form_open
										<fieldset id='FundBasicInfo' style='margin:0px;padding:8px;'>
											$prime_input
											$view_content
										</fieldset>
								    $form_submit
									$form_close";
							?>
						</div>
					</div>
					</div>
			</div>
		</div>
		<div class="modal-dialog" id='completion_form' style="display: none;">			
			<div style='padding: 15px 20px;'>
				<div class='row'>
					<div class='col-md-3 col-xs-12 en_log'>
						<img class="logo" style="height: 42px; width: auto;" src="https://cafsindia.com/wp-content/uploads/2020/01/CAFS-logo.png" alt="Logo">
					</div>
					<div class='col-md-9 col-xs-12 en_head'>
						Let's get start with CAFS
					</div>
				</div>
			</div>
			<div class="modal-content">
				<div class="modal-header bootstrap-dialog-draggable">
					<div class="bootstrap-dialog-header">
						<div class='row' style="margin:0px;text-align: center;">
							<p>Your form is Already Submitted.</p>
							<p>Please Contact our HR.</p>
							<p>for more info,</p>
							<p><a href='http://www.cafsindia.com' target='_blank'>www.cafsindia.com</a></p>
							<p><a href='http://www.cafsinfotech.com' target='_blank'>www.cafsinfotech.com</a></p>
							<p>New Entry?<a class='btn btn-sm' onclick='new_entry()' style="color: blue;">Click Here</a></p>
						</div>
					</div>
					</div>
			</div>
		</div>
		<div class="modal-dialog" id='walkin_form' style="display:none;">
			<div style='padding: 15px 20px;'>
				<div class='row'>
					<div class='col-md-3 col-xs-12 en_log'>
						<img class="logo" style="height: 42px; width: auto;" src="https://cafsindia.com/wp-content/uploads/2020/01/CAFS-logo.png" alt="Logo">
					</div>
					<div class='col-md-9 col-xs-12 en_head'>
						Let's get start with CAFS
					</div>
				</div>
			</div>
			<div class="modal-content">
				<div class="modal-header bootstrap-dialog-draggable">
					<div class="bootstrap-dialog-header">
						<input type="hidden" id="walk_prime_id" name="walk_prime_id" value="0">
						<div class="form-group">
							<label>Reached Status</label>
							<select id="walkin" name="walkin" onchange="update_walkin(this.value)" class="form-control">
								<option value="0">---Reached Status---</option>
								<option value="1">Reached Office</option>
								<option value="2">Yet to Reach</option>
							</select>
						</div>
						<p>New Entry?<a class='btn btn-sm' onclick='new_entry()' style="color: blue;">Click Here</a></p>
					</div>
					</div>
			</div>
		</div>
	</body>
	<!-- <script src="https://maps.googleapis.com/maps/api/js?libraries=places&language=en&key=AIzaSyBTcHFh1Qms8V6ygOlQpaNNzWZ_DGXclGI"  async defer>
</script> -->
	<script type="text/javascript">
	$(document).ready(function(){	
	var browser_name = Object.keys(jQuery.browser)[0];	
	// alert(browser_name);

		var prime_id    = "#<?php echo $prime_id;?>";
		var form_id     = "#<?php echo $form_id;?>";
		var date_exist  = "<?php echo $date_exist;?>";
		var date_time_exist  = "<?php echo $date_time_exist;?>";
		var ses_mobile_number  = "<?php echo $mobile_number;?>";
		var view_id             = $(prime_id).val();
		$('#mobile_number,#candidate_code,#age,#duration').prop('readonly', true);
		$('[href="#educational_qualification"],[href="#working_experience"]').closest('li').hide();
		$('#submit').html("Proceed to Education <i class='fa fa-paper-plane-o' aria-hidden='true'></i>");
		if(date_exist === "1"){
			$(function () {
				$(".datepicker").datetimepicker({
					format: 'DD-MM-YYYY',
				});
			});			
		}
	var date = new Date();
	var currentMonth = date.getMonth();
	var currentDate = date.getDate();
	var currentYear = date.getFullYear();
	$('#date_of_birth').datetimepicker({
		format: 'DD-MM-YYYY',
		maxDate: new Date(currentYear-15, currentMonth, currentDate)
	});
	 if(view_id === ""){
		$('#date_of_birth').val("");
	}
    $('#date_of_available').datetimepicker({
		format: 'DD-MM-YYYY',
		minDate: new Date()
	});
    
	$('#interview_time').datetimepicker({
		format: 'hh:mm A',
		//maxDate:new Date()
	});
	$("#year_of_passing").datetimepicker({
		format: 'YYYY',
	});
		
	if(date_time_exist === "1"){
        $(function(){
             $(".datepicker_time").datetimepicker({
                 format: 'DD-MM-YYYY HH:mm A',
                 //format : 'DD-MM-YYYY g:i A',
                 //debug: true
              });
        });
	}
	var drop_exist = "<?php echo $drop_exist;?>";
	if(drop_exist === "1"){
		/*$(function(){
			$('.select2').select2({
				placeholder: '---- Select ----',
				allowClear: true,
				dropdownParent: $('.modal-dialog')
			});
			$('.select2-tags').select2({
				tags: true,
				tokenSeparators: [',']
			});
		});*/
	}
	
	$('textarea').on('keyup keypress', function(e){
        if(e.keyCode === 13) {    
            e.stopPropagation();
        }else
        if(e.shiftKey){
            e.stopPropagation();
        }
	});
	
	$('.alpha').bind('keypress', function (event) {
		var regex = new RegExp("^[A-Za-z0-9,\-_.@\/\\s]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if(!regex.test(key)){
			event.preventDefault();
			return false;
		}
	});
	
	$(".alpha_text").keypress(function(event){
		var regex = new RegExp("^[a-zA-Z\-.\/\\s]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
			event.preventDefault();
			return false;
		}
	});
	$(".number").bind('keyup', function(e){
		this.value = this.value.replace(/[^0-9_.]/g,'');
		if ($(e.target).closest("#no_of_siblings")[0] || $(e.target).closest("#notice_period")[0]) {
	        return;
	    }
		if(this.value === "0"){
			this.value = "";
		}
	});
	$('#percentage').on('keydown keyup', function(e){
	    if ($(this).val() > 100 
	        && e.keyCode !== 46 // keycode for delete
	        && e.keyCode !== 8 // keycode for backspace
	       ) {
	       e.preventDefault();
	       $(this).val(100);
	    }
	});
		 $('input').keypress(function(e){
	        e = e || event;
	        var s = String.fromCharCode(e.charCode);
	        if(s.match(/[A-Z]/)){
	          toastr.error("CAPS LOCK Disabled");
	          return false;
	        }
	    });
		$.validator.setDefaults({ignore:[]});	
		$.validator.addMethod("alphanumeric", function(value, element) {
			return this.optional(element) || /^[a-z0-9\-\s]+$/i.test(value);
		}, "Allow only letters, numbers, or dashes.");
		
		$(form_id).submit(function(event){ event.preventDefault(); }).validate({
		ignore: ".ignore",
        invalidHandler: function(e, validator){
                if(validator.errorList.length)
            $('.nav-tabs a[href="#' + $(validator.errorList[0].element).closest(".tab-pane").attr('id') + '"]').tab('show');
        },
		rules:{
			<?php echo $validation_rule; ?>
		},
			submitHandler: function (form){
				$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#submit').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){
						$('#submit').attr('disabled',false);
						$("#submit").html("Submit");
						if(response.success){
							$(prime_id).val(response.insert_id);
							toastr.warning(response.message);
							$('#submit').hide();
							var employee_type = $('#employee_type').val();
							if(parseInt(employee_type) === 2){
								var btn_text = "Proceed to Experience";
							}else{
								var btn_text = "Finish";
							}
							btn_info = '<div class="col-md-12" style="background-color:#FFFFFF;padding: 10px 20px; text-align: right; border-top: 1px solid #e5e5e5;" id="modal_btn_div"><button class="btn btn-primary" onclick=show_education_tab() style="margin-left: 15px;"> '+btn_text+' <i class="fa fa-paper-plane-o" aria-hidden="true"></i></button></div>';
							$("#custom_form").append(btn_info);
							$('.row_btn').show();
							$('.nav-tabs a[href="#educational_qualification"]').tab('show');
						}else{
							toastr.error(response.message);
							/*if(response.from === "education"){
								$('.nav-tabs a[href="#educational_qualification"]').tab('show');
							}else
							if(response.from === "work_experience"){
								$('.nav-tabs a[href="#working_experience"]').tab('show');
							}*/
						}						
					},
					dataType: 'json'
				});
			}
		});
		<?php
			echo "$document_load_script";
			print_r($condition_list);
		?>
		if(ses_mobile_number){
			get_candidate_data(ses_mobile_number);
		}else{
			$('#custom_form').hide();
			$('#completion_form').hide();
			$.confirm({
				title: '',
				content: '' +
				"<div class='col-xs-12' style='text-align:center;margin-bottom:30px;'><img class='logo' style='height: 30px; width: auto;' src='https://cafsindia.com/wp-content/uploads/2020/01/CAFS-logo.png' alt='Logo'></div> " +
				"<p> <b>NOTE :</b> Mobile users make sure you are using <span style='color:red'><b>Google Chrome Browser</b></span>. Other browsers will lead to <b>failure in the process...</b> </p>"+
				'<div class="form-group">' +
				'<label>Enter your mobile number</label>' +
				'<input type="text" placeholder="Enter your mobile number" name="mobile_number" id="mobile_number" class="name form-control" required />' +
				'</div>',
				buttons: {
					formSubmit: {
						text: 'Submit',
						btnClass: 'btn-blue',
						action: function () {
							var mobile_number = this.$content.find('.name').val();
							var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
						if(browser_name=='chrome'){
							if(filter.test(mobile_number)) {
								if(mobile_number.length==10){
									var prime = '<?php echo $prime_id;?>';
									var send_url = 'enrolment_callback.php?frm=exit_number';
									$.ajax({			
										type: "POST",
										url: send_url,
										data:{mobile_number:mobile_number},
										success: function(data){
											var rslt = JSON.parse(data);							
											if(rslt.success){
												if(rslt.message === "completed"){
													$('#custom_form').hide();
													$('#completion_form').hide();
													$('#walkin_form').show();
													$('#walk_prime_id').val(rslt.form_rslt);
												}else{
													$("body").removeAttr("style");
													toastr.success(rslt.message);
													$(prime_id).val(rslt.insert_id);
													$('#candidate_code').val(rslt.candidate_code);
													$('#custom_form').show();
													$('#completion_form').hide();
													$.each(rslt, function( index, value ) {
													  $("#"+index).val(value);
													});
												}
												//select_field();
											}else{
												if(rslt.status){
													toastr.warning(rslt.message);
													$('#custom_form').hide();
													$('#completion_form').show();		
												}else{												
													$("body").removeAttr("style");
													toastr.warning(rslt.message);
													$.each(rslt.form_rslt, function(key,value){
														if(value.field_type === "1" || value.field_type === "2" || value.field_type === "3"){
															var val = value.input_value;
															if(key !== "no_of_siblings" && key !== "notice_period"){
																if(value.input_value === "0" || value.input_value === "0.00"){
																	val = "";
																}
															}
															$('#'+key).val(val);
														}else
														if(value.field_type === "6"){
															$('#'+key).prop('checked', false);
															if(value.input_value === "1"){
																$('#'+key).prop('checked', true);
															}
														}else
														if((value.field_type === "5") || (value.field_type === "7")){
															var selectedOptions = value.input_value.split(",");
															for(var i in selectedOptions) {
																var optionVal = selectedOptions[i];
																$("#"+key).find("option[value='"+optionVal+"']").prop("selected", "selected");							
															}
															if(key === "applied_by"){
																hide_show(value.input_value);
															}
															if(key === "employee_type"){
																type_hide_show(value.input_value);
															}
															/*$(function(){
																$('.select2').select2({
																	placeholder: '---- Select ----',
																	dropdownParent: $('.modal-dialog')
																});
																$('.select2-tags').select2({
																	tags: true,
																	tokenSeparators: [',']
																});
															});	*/										
														}else
														if(value.field_type === "10"){
															var url = window.location.href+'/'+value.input_value;
															var tbl_data = "<table style='width: 100%;' id='table_"+key+"'><tbody><tr><td colspan='2'><div class='progress_bar' id='div_sts_resume' style='display: none;'><div class='process_percent' id='process_"+key+"' style='width: 100%;'>100%</div></div></td></tr><tr><td><a href='javascript:void(0);' onclick='remove_file('"+prime+"','1','"+key+"');' style='color: red; cursor: pointer;'><i class='fa fa-times' aria-hidden='true'></i></a></td><td style='text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 100px;'><a href='"+url+"' target='_blank' style='cursor: pointer;''>49258798607_Candidate Onepage Chennai.docx</a></td></tr></tbody></table>";
															if(value.input_value){
																$('#'+key).val(value.input_value);
																$(tbl_data).insertAfter('#'+key).closest('div');
															}
														}else{
															$('#'+key).val(value.input_value);
														}
														$(".datepicker_time").datetimepicker({
										                     format: 'DD-MM-YYYY HH:mm A',
										                     //format : 'DD-MM-YYYY g:i A',
										                     //debug: true
										                  });
													});
													$.each(rslt.row_set_data, function(key,value){
														$('#'+value.div_id).html(value.row_set_view);
														$('#'+value.table_id).DataTable();
													});
													$('#custom_form').show();
													$('#completion_form').hide();
													
												}
											}
												
										}
									});
								}else {
									$.alert('Please put 10 digit mobile number');
									return false;
								}
							}else{
								$.alert('please enter valid number!');
								return false;
							}
						}else{
							$.alert('please Use Chrome!');
							return false;
						}
						}
					},
				},
				onContentReady: function(){
					// bind to events
					var jc = this;
					this.$content.find('form').on('submit', function (e) {
						e.preventDefault();
						jc.$$formSubmit.trigger('click'); // reference the button and click it
					});
				}
			});	
		}	

		$("#emp_age").change(function(){
			var emp_age = $('#emp_age').val();
			if(emp_age < 18 ){
				toastr.error("User age should be above 18 years only?");
				$('#submit').attr('disabled',true);
			}else{
				$('#submit').attr('disabled',false);
			}
		});		
		applied_by = $('#applied_by').val();
		hide_show(applied_by);
		$("#applied_by").change(function(){
			applied_by = $('#applied_by').val();
			hide_show(applied_by);
		});
		$("#standard").change(function(){
			standard = $('#standard').val();
			standard_hide_show(standard);
		});
		$("#resigned").change(function(){
			resigned = $('#resigned').val();
			resigned_hide_show(resigned);
		});
		$("#employee_type").change(function(){
			employee_type = $('#employee_type').val();
			type_hide_show(employee_type);
		});
		$("#department").change(function(){
			var department = $('#department').val();
			get_position(department);
		});

	$('#relived_date').on("dp.hide",function (e) {
		var relived  = $('#relived_date').val();
		var relived_date   = moment(relived, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var joined  = $('#joined_date').val();
		var joined_date   = moment(joined, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(relived_date <= joined_date){
			toastr.error("Relieved date not less than Joined Date, please choose another date");
			$('#relived_date').val('');
		}
	});
	$('#date_of_birth').on("dp.hide",function (e) {
		var prime_id_val = $(prime_id).val();
		var date_of_birth = $('#date_of_birth').val();
		if(date_of_birth !== ""){
			var send_url = 'enrolment_callback.php?frm=check_dob_exist';
			$.ajax({
				type: "POST",
				url: send_url,
				data:{date_of_birth:date_of_birth,prime_id_val:prime_id_val},
				success: function(data) {
					
				}
			});
		}
	});
	/*$("#work_location").keypress(function(event){
		var pacContainerInitialized = false; 
		var input = document.getElementById('work_location');		  
		var autocomplete = new google.maps.places.Autocomplete(input);		   
		autocomplete.addListener('place_changed', function() {
			var place = autocomplete.getPlace();
			$('#work_location').val(place.formatted_address);
		});
		if (!pacContainerInitialized) { 
				$('.pac-container').css('z-index', '9999'); 
				pacContainerInitialized = true; 
		}
	});*/
	/*$('#interview_time').on("dp.hide",function (e) {
		var startTime = '09:30 AM';
    	var endTime = '05:30 PM';
		var interview_time = $('#interview_time').val();
	   if(get24Hr(interview_time) > get24Hr(startTime) && get24Hr(interview_time) < get24Hr(endTime)) {
	      toastr.success("Valid Time");
	    }else{
	      toastr.error("Time Should be between (9 AM to 5.00 PM");
	      $('#interview_time').val('');
	    }
	});*/
	default_hide_inputs();
	//duplicate number checking
	$('#mobile_number,#alternate_number').change(function(){
		var $current = $(this);
		$('#mobile_number,#alternate_number').each(function() {
			if ($(this).val() == $current.val() && $(this).attr('id') != $current.attr('id')){
				toastr.error('Already number is exit!');
				$('#'+$current.attr('id')).val('');
				return false;
			}
		});
	});
	$("#interview_slot").change(function(){
		var date_of_available = $('#date_of_available').val();
		var interview_slot    = $('#interview_slot').val();
		interview_slot_check(date_of_available,interview_slot);
	});
	$('#date_of_available').on("dp.hide",function (e) {
		var date_of_available = $('#date_of_available').val();
		var interview_slot    = $('#interview_slot').val();
		interview_slot_check(date_of_available,interview_slot);
	});
	//DISABLE Inspect Element
	/*document.addEventListener('contextmenu', function(e) {
	  e.preventDefault();
	});
 	$(document).keydown(function (event) {
        if (event.keyCode == 123) {
            return false;
        }
        else if ((event.ctrlKey && event.shiftKey && event.keyCode == 73) || (event.ctrlKey && event.shiftKey && event.keyCode == 74)) {
            return false;
        }
    });
    document.onkeydown = function(e) {
        if (e.ctrlKey && 
            (e.keyCode === 67 || 
             e.keyCode === 86 || 
             e.keyCode === 85 || 
             e.keyCode === 117)) {
            return false;
        }else{
            return true;
        }
	};
	$(document).keypress("u",function(e) {
	  if(e.ctrlKey){ return false; }else{ return true; } 
	});*/
});	

function row_set_edit(row_id,table_name,view_id){
	if((row_id !== "") && (table_name !== "")){
		var send_url = 'enrolment_callback.php?frm=row_set_edit'; 
		$.ajax({			
			type: "POST",
			url: send_url,
			data:{row_id:row_id,table_name:table_name,view_id:view_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$.each( rslt.row_result, function(key,value){
					if(value.field_type === "6"){
						$('#'+key).prop('checked', false);
						if(value.input_value === "1"){
							$('#'+key).prop('checked', true);
						}
					}else
					if((value.field_type === "5") || (value.field_type === "7")){
						var selectedOptions = value.input_value.split(",");
						for(var i in selectedOptions) {
							var optionVal = selectedOptions[i];
							$("#"+key).find("option[value='"+optionVal+"']").prop("selected", "selected");
						}												
						if(key === "resigned"){
							resigned_hide_show(value.input_value);
						}
						if(key === "standard"){
							standard_hide_show(value.input_value);
						}
						/*$(function(){
							$('.select2').select2({
								placeholder: '---- Select ----',
								dropdownParent: $('.modal-dialog')
							});
							$('.select2-tags').select2({
								tags: true,
								tokenSeparators: [',']
							});
						});*/
					}else{
						$('#'+key).val(value.input_value);
					}
				});
			}
		});
	}
}

function row_set_remove(row_id,table_name,view_id,prime_id){
	if((row_id !== "") && (table_name !== "")){
		if (confirm('Are you sure want to delete this record?')) {
			var send_url = 'enrolment_callback.php?frm=row_set_remove';
			$.ajax({
				type: "POST",
				url: send_url,
				data:{row_id:row_id,table_name:table_name,view_id:view_id,prime_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					toastr.success(rslt.msg);
					$('#'+rslt.row_set_data.div_id).html(rslt.row_set_data.row_set_view);
					$('#'+rslt.row_set_data.table_id).DataTable();
				}
			});
		}
	}
}
// FILE UPLOAD REMOVE
function remove_file(prime_id,is_defult,input_name){
	var prime_id_val = $("#"+prime_id).val();
	var send_url = 'enrolment_callback.php?frm=remove_file';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{prime_id:prime_id,prime_id_val:prime_id_val,is_defult:is_defult,input_name:input_name},
		success: function(data) {
			var rslt = JSON.parse(data);
			$("#"+input_name).val("");
			$("#table_"+input_name).attr("style", "display:none");
		}
	});
}
function hide_show(applied_by){
	if(parseInt(applied_by) === 2){
		$('#consultancy').parent().show();
		$('#consultancy').removeClass('ignore');	
		$('#employee_code').parent().hide();
		$('#employee_code').addClass('ignore');		
		$('#employee_code').val('');
	}else
	if(parseInt(applied_by) === 1){
		$('#employee_code').parent().show();
		$('#employee_code').removeClass('ignore');
		$('#consultancy').parent().hide();
		$('#consultancy').addClass('ignore');
		$('#consultancy').val('');
	}else{
		$('#employee_code').parent().hide();
		$('#employee_code').addClass('ignore');
		$('#consultancy').parent().hide();
		$('#consultancy').addClass('ignore');
		$('#consultancy').val('');
		$('#employee_code').val('');
	}
}
function standard_hide_show(standard){
	if(parseInt(standard) === 1 || parseInt(standard) === 2){		
		$('#degree').parent().hide();
		$('#degree').addClass('ignore');			
	}else{
		$('#degree').parent().show();
		$('#degree').removeClass('ignore');
	}
}
function resigned_hide_show(resigned){
	if(parseInt(resigned) === 1 ){		
		$('#relived_date').parent().show();
		$('#relived_date').removeClass('ignore');			
	}else{
		$('#relived_date').parent().hide();
		$('#relived_date').addClass('ignore');		
	}
}
function type_hide_show(employee_type){
	if(parseInt(employee_type) === 2){
		$('#current_salary,#notice_period').parent().show();
		$('#current_salary,#notice_period').removeClass('ignore');
	    $('#current_salary,#notice_period').val(0);			
	}else{
		$('#current_salary,#notice_period').parent().hide();
		$('#current_salary,#notice_period').addClass('ignore');	
		$('#current_salary,#notice_period').val(0);		
	}
}

function get_candidate_data(mobile_number){
	var prime = '<?php echo $prime_id;?>';
	var send_url = 'enrolment_callback.php?frm=session_exist';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{mobile_number:mobile_number},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				if(rslt.message === "completed"){
					$('#custom_form').hide();
					$('#completion_form').hide();
					$('#walkin_form').show();
					$('#walk_prime_id').val(rslt.form_rslt);
				}else{			
					$("body").removeAttr("style");
					toastr.warning(rslt.message);
					$.each(rslt.form_rslt, function(key,value){
						if(key === prime){
							prime_id = value.input_value;
						}
						if(value.field_type === "1" || value.field_type === "2" || value.field_type === "3"){
							var val = value.input_value;
							if((key !== "no_of_siblings") && (key !== "notice_period")){
								if(value.input_value === "0" || value.input_value === "0.00"){
									val = "";
								}
							}						
							$('#'+key).val(val);
						}else					
						if(value.field_type === "6"){
							$('#'+key).prop('checked', false);
							if(value.input_value === "1"){
								$('#'+key).prop('checked', true);
							}
						}else
						if((value.field_type === "5") || (value.field_type === "7")){
							var selectedOptions = value.input_value.split(",");
							for(var i in selectedOptions) {
								var optionVal = selectedOptions[i];
								$("#"+key).find("option[value='"+optionVal+"']").prop("selected", "selected");							
							}
							if(key === "applied_by"){
								hide_show(value.input_value);
							}
							if(key === "employee_type"){
								type_hide_show(value.input_value);
							}
							/*$(function(){
								$('.select2').select2({
									placeholder: '---- Select ----',
									dropdownParent: $('.modal-dialog')
								});
								$('.select2-tags').select2({
									tags: true,
									tokenSeparators: [',']
								});
							});*/
						}else
						if(value.field_type === "10"){						
							var url = window.location.href+'/'+value.input_value;						
							var tbl_data = "<table style='width: 100%;' id='table_"+key+"'><tbody><tr><td colspan='2'><div class='progress_bar' id='div_sts_resume' style='display: none;'><div class='process_percent' id='process_"+key+"' style='width: 100%;'>100%</div></div></td></tr><tr><td><a href='javascript:void(0);' onclick='remove_file('"+prime+"','1','"+key+"');' style='color: red; cursor: pointer;'><i class='fa fa-times' aria-hidden='true'></i></a></td><td style='text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 100px;'><a href='"+url+"' target='_blank' style='cursor: pointer;''>49258798607_Candidate Onepage Chennai.docx</a></td></tr></tbody></table>";
							if(value.input_value){
								$('#'+key).val(value.input_value);
								$(tbl_data).insertAfter('#'+key).closest('div');
							}						
						}else{
							$('#'+key).val(value.input_value);
						}
						$(".datepicker_time").datetimepicker({
	                     format: 'DD-MM-YYYY HH:mm A',
	                     //format : 'DD-MM-YYYY g:i A',
	                     //debug: true
	                  });
					});
					$.each(rslt.row_set_data, function(key,value){
						$('#'+value.div_id).html(value.row_set_view);
						$('#'+value.table_id).DataTable();
					});

					if(rslt.success=='true'){
						$("#walk_label").show();
						$('#custom_form').hide();
						$('#completion_form').hide();
					}else{
						$('#custom_form').show();
						$('#completion_form').hide();
					}
				}
			}else{
				toastr.warning(rslt.message);
				$('#custom_form').hide();
				$('#completion_form').show();
			}
		}
	});
}
function default_hide_inputs(){
	$('#degree,#employee_code,#relived_date,#current_salary,#notice_period').parent().hide();
	$('#degree,#employee_code,#relived_date,#current_salary,#notice_period,#email_id').addClass('ignore');
	$('#email_id').parent().find('label').removeClass('required');
}
function show_education_tab(){
	var prime_id_val = $("#prime_candidate_tracker_id").val();
	var employee_type = $('#employee_type').val();	
		var send_url = 'enrolment_callback.php?frm=show_education_tab';
		$.ajax({
			type: "POST",
			url: send_url,
			data:{prime_id_val:prime_id_val,employee_type:employee_type},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){		
					if(parseInt(employee_type) === 2){			
						$('#modal_btn_div').html("");
						btn_info = '<button class="btn btn-primary" onclick=show_experience_tab() style="margin-left: 15px;">Finish <i class="fa fa-paper-plane-o" aria-hidden="true"></i></button>';
						$('#modal_btn_div').html(btn_info);
						$('.nav-tabs a[href="#working_experience"]').tab('show');
					}else{
						new_entry();
						toastr.success("Successfully Updated your Profile");
					}
				}else{
					toastr.error(rslt.message);
				}
			}
		});
	
}
function show_experience_tab(){
	var prime_id_val = $("#prime_candidate_tracker_id").val();
	var send_url = 'enrolment_callback.php?frm=show_experience_tab';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{prime_id_val:prime_id_val},
		success: function(data){
			var rslt = JSON.parse(data);
			if(rslt.success){
				toastr.success(rslt.message);
				setTimeout(function(){
					window.location.reload();
				},500);
			}else{
				toastr.error(rslt.message);
			}
		}
	});	
}
function interview_slot_check(date_of_available,interview_slot){
	var send_url = 'enrolment_callback.php?frm=interview_slot_check'; 
	if(date_of_available != "" && interview_slot){
		$.ajax({
			type: "POST",
			url: send_url,
			data:{date_of_available:date_of_available,interview_slot:interview_slot},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){
					toastr.success(rslt.message);
				}else{
					$('#interview_slot').val('');
					toastr.error(rslt.message);
				}
			}
		});	
	}
}
//Google API Call
/*function get_current_location(){
	var pacContainerInitialized = false; 
	var input = document.getElementById('current_location');		  
	var autocomplete = new google.maps.places.Autocomplete(input);		   
	autocomplete.addListener('place_changed', function() {
		var place = autocomplete.getPlace();
		$('#current_location').val(place.formatted_address);
	});
	if (!pacContainerInitialized) { 
			$('.pac-container').css('z-index', '9999'); 
			pacContainerInitialized = true; 
	}	
}
function get_permanent_location() {
	var pacContainerInitialized = false; 
	var input = document.getElementById('permanent_location');		  
	var autocomplete = new google.maps.places.Autocomplete(input);		   
	autocomplete.addListener('place_changed', function() {
		var place = autocomplete.getPlace();
		$('#permanent_location').val(place.formatted_address);
	});
	if (!pacContainerInitialized) { 
			$('.pac-container').css('z-index', '9999'); 
			pacContainerInitialized = true; 
	}
	
} */
function new_entry(){
	var send_url = 'enrolment_callback.php?frm=clear_session';
	$.ajax({
		type: "POST",
		url: send_url,
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){				
				setTimeout(function(){
					window.location.reload();
				},500);
			}
		}
	});
}
function get24Hr(time){
  var hours = Number(time.match(/^(\d+)/)[1]);
  var AMPM = time.match(/\s(.*)$/)[1];
  if(AMPM == "PM" && hours<12) hours = hours+12;
  if(AMPM == "AM" && hours==12) hours = hours-12;

  var minutes = Number(time.match(/:(\d+)/)[1]);
  hours = hours*100+minutes;
  return hours;
 }
 function get_position(department){
 	var send_url = 'enrolment_callback.php?frm=get_position';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{department:department},
		success: function(data) {
			$('#post_applied_for').html(data);
		}
	});
 }
 //CHECK FILE SIZE FOR UPLOAD
function check_upload_size(size){
	size = (size / 1024 / 1024).toFixed(2);
	if(parseInt(size) <= 2){
		return true;
	}else{
		return false;
	}
}
//PROGRESS STATUS FILE UPLOAD
function progress_bar(id){
    i = 0;
	var elem  = document.getElementById("process_"+id+"");
	var width = 0;
	var internal    = setInterval(frame, 10);
	function frame() {
		if (width >= 100) {
			clearInterval(internal);
			i = 0;
			$('#div_'+id+'').hide();
			$('#submit').attr('disabled',false);
			$('#submit').html("Proceed to Education <i class='fa fa-paper-plane-o' aria-hidden='true'></i>");
		} else {
			width++;
			elem.style.width = width + "%";
			elem.innerHTML = width  + "%";
		}
	}
}
function update_walkin(reached_status){
	var walk_prime_id = $('#walk_prime_id').val();
	if(walk_prime_id){
		var send_url = 'enrolment_callback.php?frm=upd_candidate_status';
		$.ajax({
			type: "POST",
			url: send_url,
			data:{walk_prime_id:walk_prime_id,reached_status:reached_status},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){	
					toastr.success(rslt.message);			
					setTimeout(function(){
						window.location.reload();
					},500);
				}
			}
		});
	}	
}

/* function select_field(){
	$('.select2').select2({
		placeholder: '---- Select ----',
		allowClear: true,
		//dropdownParent: $('.modal-dialog')
	});
	$('.select2-tags').select2({
		tags: true,
		tokenSeparators: [',']
	});
}*/
</script>
	<style>
		body{
			font-size: 14px;
		}
		.nav-tabs>li.active>a, .nav-tabs>li.active>a:hover, .nav-tabs>li.active>a:focus {
			color: #2c3e50;
			background-color: #ffffff;
			border: 1px solid #ecf0f1;
			border-bottom-color: transparent;
			cursor: default;
		}
		.nav-tabs>li>a {
			line-height: 1.42857143;
			border: 0px solid transparent;
			border-radius: 4px 4px 0 0;
			color: #FFFFFF;
		}
		.form-inline .form-group {
			display: inline-block;
			vertical-align: middle;
			margin-left: 30px;
			margin-bottom: 10px;
			width: 16.5%;
		}
		.form-inline .control-label {
			margin-bottom: 0;
			vertical-align: middle;
			display: inline-block;
			cursor: pointer;
			font-weight: bold;
			font-size: 11px;
		}
		.form-inline .form-control {
			display: inline-block;
			width: -webkit-fill-available;
			vertical-align: middle;
		}
		
		/*.form-control {
			display: block;
			width: 100%;
			height: 35px;
			padding: 4px 5px;
			font-size: inherit;
			line-height: 1.42857143;
			color: #2c3e50;
			background-color: #ffffff;
			background-image: none;
			border: 0px solid #dce4ec;
			border-radius: 0px;
			-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
			-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			border-bottom: 1px solid #CCCCCC;
			font-size: inherit;
		}*/
		
		.block_content {
			font-size: inherit;
			box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2);
			background-color: #FFFFFF;
			border: 0px;
			border-radius: 2px;
			margin-top: 15px;
			margin-bottom: 15px;
			padding: 0px;
		}
		.textcolor{
			color: blue;
		}
		select[readonly].select2 + .select2-container {
			pointer-events: none;
			touch-action: none;
		}
		input[readonly] {
			pointer-events: none;
			touch-action: none;
		}
	</style>
	<!-- UDY STARTS --->
	<style>
		.row_set_div {
			padding: 0px !important;
			background-color: #f1f1f1bf;
			border-radius: 0px !important;
			box-shadow: none !important;
			margin-bottom: 0px !important;
		}
		.en_head{
			color: #001630;
			font-size: 30px;
			font-weight: bold;
			text-align: right;
		}
		.jconfirm .jconfirm-box .jconfirm-buttons button.btn-blue {
			background-color: #f75940;
			color: #FFF;
			text-shadow: none;
			-webkit-transition: background .2s;
			transition: background .2s;
		}
		.jconfirm .jconfirm-box .jconfirm-buttons button.btn-blue:hover {
			background-color: #f75940;
			color: #FFF;
		}
		.nav-tabs {
			border-bottom: 1px solid #ecf0f1;
			background-color: #f75940;
			display: flex;
			white-space: nowrap;
			overflow-x: auto;
			overflow-y: hidden;
		}
		.tab_head {
			color: #f75940;
			margin: 15px 13px;
			text-align: left;
			font-size: 15px;
			font-weight: bold;
		}
		.btn-primary {
			color: #ffffff;
			background-color: #001630;
			border-color: #001630;
			box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
			min-width: 100px;
			border-radius: 3px;
		}
		.btn-primary:active, .btn-primary.active, .open>.dropdown-toggle.btn-primary {
			color: #ffffff;
			background-color: #001630 !important;
			border-color: #001630 !important;
		}
		table.dataTable thead th, table.dataTable thead td {
			background-color: #f75940!important;
			color: #FFFFFF !important;
			border: 0px !important;
			white-space: nowrap;
			vertical-align: middle !important;
			max-width: 200px !important;
			border-bottom: 1px solid #CCCCCC;
		}
		.pagination>.disabled>span, .pagination>.disabled>span:hover, .pagination>.disabled>span:focus, .pagination>.disabled>a, .pagination>.disabled>a:hover, .pagination>.disabled>a:focus {
			color: #ecf0f1;
			background-color: #001630;
			border-color: transparent;
			cursor: not-allowed;
		}
		.pagination>li>a, .pagination>li>span {
			position: relative;
			float: left;
			padding: 8px 15px;
			line-height: 1.42857143;
			text-decoration: none;
			color: #ffffff;
			background-color: #001630;
			border: 1px solid transparent;
			margin-left: -1px;
			font-size: inherit;
			border-left: 1px solid #001630;
			border-right: 1px solid #001630;
		}
		.btn-primary:focus, .btn-primary.focus {
			color: #ffffff;
			background-color: #001630 !important;
			border-color: #001630 !important;
		}
		@media (min-width:992px) and (max-width:3500px){ 
			.form-inline .form-group {
				display: inline-block;
				vertical-align: middle;
				margin-left: 30px;
				margin-bottom: 10px;
				width: 16.5%;
			}
		}
		@media (min-width:768px) and (max-width:992px){
			.form-inline .form-group {
				display: inline-block;
				vertical-align: middle;
				padding: 10px;
				width: 100%;
				margin: 0px !important;
			}
			.en_log{
				text-align: center;
				margin-bottom: 4px;
			}
			.en_head{
				text-align: center !important;
				margin-bottom: 4px;
			}
		}
		@media (min-width:200px) and (max-width:768px){
			.form-inline .form-group {
				display: inline-block;
				vertical-align: middle;
				padding: 10px;
				width: 100%;
				margin: 0px !important;
			}
			.en_log{
				text-align: center;
				margin-bottom: 4px;
			}
			.en_head{
				text-align: center !important;
				margin-bottom: 4px;
			}			
		}
		#email_id{
		text-transform: lowercase;
	}
	</style>
	<!-- UDY END --->
</html>
