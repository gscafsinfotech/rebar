<?php 
$logged_user_role     = $this->session->userdata('logged_user_role');
$logged_role     	  = $this->session->userdata('logged_role');
$prime_id             = "prime_".$controller_name."_id";
$form_id              = $controller_name."_form";
$count                = 0;
$date_exist           = false; 
$date_time_exist      = false; 
$drop_exist           = false;
$view_count           = 0;
$view_content         = "";
$document_load_script = "";
$validation_rule      = "";
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
		$prime_form_id   = (int)$setting->prime_form_id;
		$field_type      = $setting->field_type;
		$label_id        = $setting->label_name;
		$label_name      = ucwords($setting->view_name);
		$mandatory_field = $setting->mandatory_field;
		$input_for       = (int)$setting->input_for;
		$field_isdefault = (int)$setting->field_isdefault;
		$default_value   =  $setting->default_value;
		$file_type       = $setting->file_type;	
		$extension       = $setting->upload_extension;	
		$pick_table      = $setting->pick_table;
		$pick_list       = $setting->pick_list;
		$input_view_type = (int)$setting->input_view_type;
		$input_for       = (int)$setting->input_for;
		$field_length    = $setting->field_length;
		$text_type       = (int)$setting->text_type;
		$edit_read       = (int)$setting->edit_read;
		
		$required = "";
		if((int)$mandatory_field === 1){
			$required = "required";
		}
		if($form_view->$label_id){
			$input_value = $form_view->$label_id;
		}else{
			$input_value = $default_value;
		}
		
		$read = '';
		if((int)$edit_read === 1){
			if($form_view->$label_id){
				$read = 'readonly';
			}
		}
		if($prime_form_view_id === $input_for){
			/*=================== FORM INPUT PROCESS - START ===================*/
			$form_label = form_label($label_name, $label_id, array('class' => "control-label $required"));	
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
					$valid_class = "alpha";
				}else
				if($text_type === 3){
					$valid_class = "number";
				}
				$input_value = str_replace('^',"'", $input_value);
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm $valid_class"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//DECIMALS
			if((int)$field_type === 2){
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm $valid_class"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//INTEGER
			if((int)$field_type === 3){
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm $valid_class"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//DATE
			if((int)$field_type === 4){
				$date_exist = true;
				$date = "";
				if(($input_value === "0000-00-00") || ($input_value === "0001-11-30") || ($input_value === "")){
					$date = "";
				}else{
					$date = date('d-m-Y',strtotime($input_value));
					if($date === "01-01-1970"){
						$date = date("d-m-Y");
					}
				}
				$form_input =  form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$date,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm datepicker"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//PICKLIST
			if((int)$field_type === 5){
				$drop_exist = true;
				$option_data = $all_pick[$prime_form_id];
				
				$form_dropdown =  form_dropdown(array("name" => $label_id,"id" => $label_id, $read=>true,"class" =>'form-control input-sm select2'),$option_data,$input_value);
				$input_box .= "<div class='form-group'>$form_label $form_dropdown</div>";
			}else
			//CHECKBOX
			if((int)$field_type === 6){
				$form_checkbox = form_checkbox(array("name" => $label_id,"id" => $label_id, "value"=> 1, "checked" => ($input_value) ? 1 : 0));
				$input_box .= "<div class='form-group'> <label class='checkbox-inline'> $form_checkbox $form_label </label></div>";
			}else
			//MULTI PICKLIST
			if((int)$field_type === 7){
				$drop_exist = true;
				$multi_name   = $label_id."[]";
				$multi_select = explode(',',$input_value);
				$form_dropdown = form_dropdown(array("name" => $multi_name,"multiple id" => $label_id,"class" =>'form-control input-sm select2'),$all_pick[$prime_form_id] ,$multi_select);
				$input_box .= "<div class='form-group'> $form_label $form_dropdown</div>";
			}else
			//TEXT AREA
			if((int)$field_type === 8){
				$value = str_replace('^',"'", $input_value);
				$input_box .= "<div class='form-group'> $form_label <textarea name='$label_id' id='$label_id' class='form-control' rows='4' placeholder='$label_name'>$value</textarea></div>";
			}else
			//AUTOCOMPLETE
			if((int)$field_type === 9){
				$hidden_id    = $label_id."_hidden_".$prime_form_id;
				$hidden_value = $all_pick[$prime_form_id];
				$form_input   = form_input(array("name"=>$hidden_id, "id"=>$hidden_id,"value"=>$hidden_value,"placeholder"=>"Search ".$label_name, "class"=>"form-control input-sm"));
				$hidden_input = form_input( array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"type"=>"hidden"));
				$input_box   .= "<div class='form-group'>$form_label $hidden_input $form_input</div>";
			}else
			//FILE UPLOAD
			if((int)$field_type === 10){
				$value       = $input_value;
				$upload_id   = "upload_".$label_id;
				$tabel_id   = "table_".$label_id;
				$proof       = base_url("$value");
				$form_upload = form_upload(array('name' => $upload_id,'id' => $upload_id,'class' => 'form-control input-sm','value' => $input_value,'accept' => $file_type ));
				
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
										<td>$remove_btn</td>
										<td style='text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 100px;'>$view_btn</td>
									</tr>
								</table>
							 </div>";
			}else
			//MOBILE NUMBER
			if((int)$field_type === 11){
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm $valid_class"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//EMAIL
			if((int)$field_type === 12){
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm $valid_class"));
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
				$form_input =  form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$date,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm datepicker_time"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//READ ONLY
			if((int)$field_type === 14){
				$read = '';
				if((int)$form_view->$label_id > 0){
					$read = 'readonly';
				}
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read => 'true',"class"=>"form-control input-sm $valid_class"));
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
				}else
				if((int)$field_type === 15){ //DATE
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
			if((int)$field_type === 10){
				$upload_id   = "upload_".$label_id;
				$check_size  = 2;
				$send_url	 = base_url("upload_files/upload.php?send_from=$controller_name&send_for=$upload_id&extension=$extension");
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
				$document_load_script .= "$('#$auto_save_id').bind('$change_event', function(e) {
							e.preventDefault();
							if($(form_id).valid()){
								$(form_id).submit();
							}else{
								$('#$auto_save_id').val('');
								toastr.clear();
								toastr.error('Please fill all required in previous tab');
								$('.row_btn').hide();
							}
						});\n";
			}
			//ON-LOAD SCRIPT FOR ROW SET AUTO SAVE
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
								<ul class='nav nav-tabs' data-tabs='tabs' id='#tabs'>
									@TABLI
								</ul>
								<div class='tab-content' style='padding:8px;'>
									@TABCONTENT
								</div>
							</div>";
		}
		$tab_li .= "<li role='presentation' class='$tab_active'>
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
		if((int)$form_view->$prime_id === 0){
			$style = "style='display:none;'";
		}
		$submit_btn_id   = "row_save_$prime_form_view_id";
		$cancel_btn_id   = "row_cancel_$prime_form_view_id";
		$row_prime_inupt = form_input( array("name"=>$row_prime_id, "id"=>$row_prime_id,"value"=>0,"type"=>"hidden"));
		$final_div = "";
		if($input_box){
			$final_div = "$row_prime_inupt
							$input_box
							<div class='form-group'>
								<a class='btn btn-primary btn-sm row_btn' id='$submit_btn_id' $style>Add/Update</a>
								<a class='btn btn-danger btn-sm row_btn' id='$cancel_btn_id' $style>Cancel</a>
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
		
		$send_url        = site_url("$this->control_name/rowset_save");
		$row_check_input = rtrim($row_check_input,',');
		$row_clear_data  = rtrim($row_clear_data,',');
		$row_send_data   = "{".rtrim($row_send_data,',')."}";
		
		if($row_check_input){
			$row_check_input = "var isValid = true;
								$('$row_check_input').each(function() {
								  if ($(this).val() === '') {
									isValid = false;
									toastr.error('Please fill all required field');
									$(this).addClass('error');
								  }else{
									 $(this).removeClass('error');
								  }
								});
								if(isValid){
									$.ajax({
										type: 'POST',
										url: '$send_url',
										data:$row_send_data,
										success: function(data) {
											var rslt = JSON.parse(data);
											toastr.success(rslt.message);
											$('#'+rslt.row_set_data.div_id).html(rslt.row_set_data.row_set_view);
											$row_clear_data
											$(function(){
												$('.select2').select2({
													placeholder: '---- Select ----',
													allowClear: true,
													dropdownParent: $('.modal-dialog')
												});
												$('.select2-tags').select2({
													tags: true,
													tokenSeparators: [',']
												});
											});	
											$('#'+rslt.row_set_data.table_id).DataTable();
										}
									});
								}";
		}else{
			$row_check_input = "$.ajax({
									type: 'POST',
									url: '$send_url',
									data:$row_send_data,
									success: function(data){
										var rslt = JSON.parse(data);
										toastr.success(rslt.message);
										$('#'+rslt.row_set_data.div_id).html(rslt.row_set_data.row_set_view);
										$row_clear_data
										$(function(){
											$('.select2').select2({
												placeholder: '---- Select ----',
												allowClear: true,
												dropdownParent: $('.modal-dialog')
											});
											$('.select2-tags').select2({
												tags: true,
												tokenSeparators: [',']
											});
										});
										$('#'+rslt.row_set_data.table_id).DataTable();
									}
								});";
		}
		
		$document_load_script .= "$('#$submit_btn_id').click(function(){
									$row_check_input
									
								});\n
								$('#$cancel_btn_id').click(function(){
									$row_clear_data
									$(function(){
										$('.select2').select2({
											placeholder: '---- Select ----',
											allowClear: true,
											dropdownParent: $('.modal-dialog')
										});
										$('.select2-tags').select2({
											tags: true,
											tokenSeparators: [',']
										});
									});
								});\n
								$('#$table_id').DataTable();\n";
	}
}
/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
	/*============ SATHISH EMPLOYEE CUSTOME BLOCK START============*/
	$li_line = "";
	foreach ($all_modules as $menu_name => $submenu_arr){
		$menu_text = ucwords(str_replace("_"," ",strtolower($menu_name)));
		$has_grant = false;
		$sub_line = "";
		foreach ($submenu_arr as $sub_menu_name => $module_arr){
			$submenu_text = ucwords(str_replace("_"," ",strtolower($sub_menu_name)));
			$has_subgrant = false;		
			$tr_line = "";
			foreach ($module_arr as $module){				
				$access_add         = $module->access[0]['access_add'];
				$access_update      = $module->access[0]['access_update'];
				$access_delete      = $module->access[0]['access_delete'];
				$access_search      = $module->access[0]['access_search'];
				$access_export      = $module->access[0]['access_export'];
				$access_import      = $module->access[0]['access_import'];
				$grants_menu_id     = $module->access[0]['grants_menu_id'];
				$grants_sub_menu_id = $module->access[0]['grants_sub_menu_id'];
				$module_name        = $module->module_name;
				$check_box_input    = form_checkbox("grants[]", $module->module_id, $module->grant, "class='module_$module->module_id'");					
				if((int)$module->menu_id === (int)$grants_menu_id){
					$has_grant = true;							
				}
				if((int)$module->sub_menu_id === (int)$grants_sub_menu_id){
					$has_subgrant = true;							
				}
				if($has_grant){
					$menu_input    = form_checkbox(array("name" =>'menu_id',"class" =>'menu_id',"id" =>$menu_name,"value" => $grants_menu_id, "checked" => true));	
				}else{
					$menu_input    = form_checkbox(array("name" =>'menu_id',"class" =>'menu_id',"id" =>$menu_name,"value" => $grants_menu_id));
				}
				if($has_subgrant){
					$sub_menu_input    = form_checkbox(array("name" =>'sub_menu_id',"class" =>'sub_menu_id',"id" =>$sub_menu_name,"value" => $grants_sub_menu_id,  "checked" => true));
				}else{			
					$sub_menu_input    = form_checkbox(array("name" =>'sub_menu_id',"class" =>'sub_menu_id',"id" =>$sub_menu_name,"value" => $grants_sub_menu_id));
				}
				$add_id          = $module->module_id ."::add";
				$add_checkbox    = form_checkbox(array("name" =>'access[]',"class" =>'module_'.$module->module_id,"value" => $add_id,   "checked" => ($access_add) ? 1 : 0));
				$update_id       = $module->module_id ."::update";
				$update_checkbox = form_checkbox(array("name" =>'access[]',"value" => $update_id, "class" =>'module_'.$module->module_id, "checked" => ($access_update) ? 1 : 0));
				$delete_id       = $module->module_id ."::delete";
				$delete_checkbox = form_checkbox(array("name" =>'access[]',"value" => $delete_id, "class" =>'module_'.$module->module_id, "checked" => ($access_delete) ? 1 : 0));
				$search_id       = $module->module_id ."::search";                                
				$search_checkbox = form_checkbox(array("name" =>'access[]',"value" => $search_id, "class" =>'module_'.$module->module_id, "checked" => ($access_search) ? 1 : 0));
				$export_id       = $module->module_id ."::export";                                
				$export_checkbox = form_checkbox(array("name" =>'access[]',"value" => $export_id, "class" =>'module_'.$module->module_id, "checked" => ($access_export) ? 1 : 0));
				$import_id       = $module->module_id ."::import";                                
				$import_checkbox = form_checkbox(array("name" =>'access[]',"value" => $import_id, "class" =>'module_'.$module->module_id, "checked" => ($access_import) ? 1 : 0));				
				$access_data  = "<div style='padding:8px 15px;border-bottom:1px dashed #CCCCCC;margin-bottom:15px;background-color: #f2f2f2;'>
									<label class='checkbox-inline'> $add_checkbox Add</label>
									<label class='checkbox-inline'> $update_checkbox Update</label>
									<label class='checkbox-inline'> $delete_checkbox Delete</label>
									<label class='checkbox-inline'> $search_checkbox Search</label>
									<label class='checkbox-inline'> $export_checkbox Export Data</label>
									<label class='checkbox-inline'> $import_checkbox Import Data</label>
								 </div>"; 
				$grand_data    = "<label class='checkbox-inline' style='margin-bottom:6px;'>
									$check_box_input  <span class='prime_color'><b>$module_name :</b></span> Add, Update, Delete, and Search $module_name
								</label>";
				$menu_data     = "<label class='checkbox-inline' style='margin-bottom:6px;'>
									$menu_input  <span style='color:#000000;Font-size:16px;'><b>$menu_text</b></span> 
								</label>";
				$sub_menu_data = "<label class='checkbox-inline' style='margin-bottom:6px;'>
									$sub_menu_input  <span style='color:#4DC147;Font-size:14px;'><b>$submenu_text</b></span> 
								</label>";	
				$tr_line .=  "<li>
					$grand_data
					$access_data
				</li>";							
			}
			$tr_line = "<ul id='ul_$sub_menu_name' style='display:none;'>$tr_line</ul>";
			$sub_line .= "<li>	
							$sub_menu_data					
							$tr_line
						</li>";
		}
		$sub_line = "<ul id='ul_$menu_name' style='display:none;'>$sub_line</ul>";		
		$li_line .= "<li>	
						$menu_data					
						$sub_line
					</li>";		
	}
/*============ SATHISH EMPLOYEE CUSTOME BLOCK END============*/
	$read = '';
	if($form_view->user_name){
		$read = 'readonly';
	}
	if($form_view->password){
		$read = 'readonly';
	}
	
	$user_name_label = form_label("User Name", 'user_name', array('class' => "control-label required"));
	$user_name       = form_input(array('name'=>'user_name','id'=>'user_name','class'=>'form-control input-sm',$read => 'true','value'=>$form_view->user_name));
	$password_label  = form_label("Password", 'password', array('class' => "control-label required"));
	if($form_view->$prime_id){
		$place = "*********";
	}else{
		$place = "";
	}
	$password        = form_password(array('name'=>'password','id'=>'password','class'=>'form-control input-sm','placeholder'=>$place,'value'=>""));
								
	$tab_li     .= "<li role='presentation'>
						<a data-toggle='tab' href='#login'>Login</a>
					</li>";
	$tab_content.= "<div class='tab-pane fade' id='login' >
						<h4 class='tab_head'>Login Information</h4>
						<div class='form-group'>
							$user_name_label
							$user_name
						</div>
						<div class='form-group'>
							$password_label
							$password
						</div>
					</div>";
	$tab_li     .= "<li role='presentation'>
						<a data-toggle='tab' href='#permission'>Permission</a>
					</li>";
	$tab_content.= "<div class='tab-pane fade' id='permission' style='overflow-y: auto; height: 450px;' >
						<h4 class='tab_head'>Permission Information</h4>
						<ul id='permission_list'>
							$li_line
						</ul>
					</div>";
	/*============ UDY EMPLOYEE CUSTOME BLOCK ============*/
$view_content  = str_replace("@TABLI",$tab_li,$view_content);
$view_content  = str_replace("@TABCONTENT",$tab_content,$view_content);
$form_open     = form_open("$controller_name/save/" .$form_view->$prime_id,array("id"=>$form_id,"class"=>"form-inline"));
$form_close    = form_close();
$prime_inupt   = form_input( array("name"=>$prime_id, "id"=>$prime_id,"value"=>$form_view->$prime_id,"type"=>"hidden"));

echo "$form_open
		<fieldset id='FundBasicInfo' style='margin:0px;padding:8px;background-color:#f2f2f2;'>
			$prime_inupt
			$view_content
		</fieldset>
	$form_close";
//User Role Based Condition BSK
$user_read_only = "";
if($role_based_condition){
	foreach ($role_based_condition as $key => $condition) {
		if($key === "readonly"){
			if($condition !== ""){
				$condition = str_replace(",", ",#", $condition);
				$user_read_only = "$('#".$condition."').attr('readonly','readonly');";
			}
			
		}
	}
}
?>
<script type="text/javascript">
$(document).ready(function(){
	get_permission();		
	hide_inputs();	
	var prime_id         = "#<?php echo $prime_id;?>";
	var form_id          = "#<?php echo $form_id;?>";
	var date_exist       = "<?php echo $date_exist;?>";
	var date_time_exist  = "<?php echo $date_time_exist;?>";
	var drop_exist       = "<?php echo $drop_exist;?>";
	var view_id          = "<?php echo $form_view->$prime_id; ?>";
	var user_right       = "<?php echo $logged_user_role; ?>";
	var user_role        = $('#user_right').val();	
	var employee_status  = $('#employee_status').val();
	var logged_role      = "<?php echo $logged_role;?>";
	// if(parseInt(logged_role) !== 1){
	// 	$("#super_admin").hide();
	// 	$("#ul_sub_super_admin").hide();
	// 	$("#super_admin").children().hide(); 		
	// }
	$("#employee_status").change(function(){
		var employee_status  = $(this).val();
		employee_status_hide_show(employee_status);
	});
	employee_status_hide_show(employee_status);
	<?php echo $user_read_only; ?>
	/*if(user_role){
		get_permission_list(user_role);
	}*/
	if(drop_exist === "1"){
		$(function(){
			$('.select2').select2({
				placeholder: '---- Select ----',
				allowClear: true,
				dropdownParent: $('.modal-dialog')
			});
			$('.select2-tags').select2({
				tags: true,
				tokenSeparators: [',']
			});
		});
	}
	
	//username updated
	if(parseInt(view_id) > 0){
		var employee_code = $('#employee_code').val();
		$('#user_name').val(employee_code);
	}else{
		$('#user_name').val('');
	}

	var date = new Date();
	var currentMonth = date.getMonth();
	var currentDate = date.getDate();
	var currentYear = date.getFullYear();
	
	//$('#user_name,#employee_code').attr('readonly', true);
	$('#user_name').attr('readonly', true);
	if(date_exist === "1"){
		$(function () {
			$(".datepicker").datetimepicker({
				format: 'DD-MM-YYYY',
				//debug: true
			});
		});
	}
	$('.only_time').datetimepicker({
        format: 'HH:mm',
    });
	$("#stop_pay_month").datetimepicker({
		format: 'MM-YYYY',
		//debug: true
	});
	
	//DOJ restrictions updated--12-09-2019--only new entry updates
	//DOB and DOJ Between 14 Years difference findout
	<?php 
		$curr_date = date("Y-m-d");
		$dob_date  = date("Y-m-d",strtotime("-14 year"));
	?>
	var today         = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
	<?php if($form_view->$prime_id == "") {?>
		$("#date_of_wedding").datetimepicker({
			format: 'DD-MM-YYYY',
			maxDate: moment(today),
		}).val('');
		
		$("#date_of_joining").datetimepicker({
			format: 'DD-MM-YYYY',
			maxDate: moment(today)
		}).val('');
	<?php } ?>

	$('textarea').on('keyup keypress', function(e) {
		if(e.keyCode === 13) {    
			e.stopPropagation();
		}else
		if(e.shiftKey){
			e.stopPropagation();
		}
	});
	
	$("#entry_time,#exit_time").datetimepicker({
		//format: 'HH:mm'
		format: 'LT'
		//debug: true
	});
	
	$(".number").bind('keyup', function(e){
		this.value = this.value.replace(/[^0-9_.]/g,'');
	});
	$('.alpha').bind('keypress', function (event){
		var regex = new RegExp("^[a-zA-Z0-9\-_.@\/\\s]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
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
	
	
	$.validator.setDefaults({ignore:[]});	
	$.validator.addMethod("alphanumeric", function(value, element){
		return this.optional(element) || /^[a-z0-9\-\s]+$/i.test(value);
	}, "Allow only letters, numbers, or dashes.");
	
	$(form_id).submit(function(event){  event.preventDefault(); }).validate({
        ignore: ".ignore",
       // ignore: ":hidden",
        invalidHandler: function(e, validator){
        	if(validator.errorList.length)
            $('.nav-tabs a[href="#' + $(validator.errorList[0].element).closest(".tab-pane").attr('id') + '"]').tab('show');
        },
		rules:{
			<?php echo $validation_rule; ?>
			user_name: "required",
			password:
			{
				<?php
				if($form_view->$prime_id == "")
				{
				?>
				required:true,
				<?php
				}
				?>
				minlength: 4
			},
		},
		submitHandler: function (form){
			// $("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			// $('#submit').attr('disabled','disabled');			
			$(form).ajaxSubmit({
				success: function (response){
					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					if(response.success){
						$(prime_id).val(response.insert_id);
						if(response.code_exist === 1){
							toastr.success('This Employee code is Already Exist... Your Employee Code is  '+response.emp_code);
						}else{
							toastr.success(response.message);
						}	
						$('.modal').modal('hide');
						$('.row_btn').show();
						$('#table').DataTable().ajax.reload();
					}else{
						if(response.category_status){
							$('#role,#employee_code').val('');
							select_option();
						}
						toastr.error(response.message);
					}	
				},
				dataType: 'json'
			});			
		}

	});
	/* LOAD SCRIPT AND CONDITION LOAD */
	<?php	
		echo "$document_load_script";
		foreach($condition_list as $list){
			echo $list;
		}
	?>
	/* LOAD SCRIPT AND CONDITION LOAD */
	
	/* LOAD SCRIPT AND CONDITION LOAD */
	$('#date_of_joining').on("dp.hide",function (e) {
		prime_id = '<?php echo $form_view->$prime_id; ?>';
		var date_of_joining = $('#date_of_joining').data("DateTimePicker").date().format('DD-MM-YYYY');
		if(date_of_joining){
			$('#password').val(date_of_joining);
			$('#pf_confirm_date').val(date_of_joining);
		}
		var dob = $('#date_of_birth').val();
		var doj = $('#date_of_joining').val();
		date_diff_cal(prime_id,doj,dob);
	});
	
	$('.datepicker').on("dp.hide",function (e) {
		var dob             = $('#date_of_birth').val();
		var doj             = $('#date_of_joining').val();
		var prev_date       = $('#previous_from_date').val();
		var retire_date     = $('#retirement_date').val();
		if(dob === ""){
			$('#emp_age').val('');
			$('#retirement_date').val('');
			$('#retirement_years').val('');
		}
		if(doj === ""){
			$('#confirmation_date').val('');
		}
		if(prev_date === ""){
			$('#past_to_date').val('');
		}
		
		var date_of_joining = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
	});
	
	var old_role = $("#role").val();
	if(old_role){
		consultancy_hide(old_role);
	}	
	// $("#role").change(function(){
	// 	var view_id  = "<?php echo $form_view->$prime_id; ?>";
	// 	var role     = $("#role").val();
	// 	consultancy_hide(role);
	// 	if(view_id === ""){
	// 		get_employee_code(role);
	// 	}else
	// 	if(view_id){
	// 		//if(check_loan_installment()){
	// 			$.confirm({
	// 				content: 'Are you sure you want to change this rights?',
	// 				escapeKey: 'Yes',
	// 				onOpenBefore: function () {
	// 				},
	// 				buttons: {
	// 					Yes: function(){
	// 						get_employee_code(role);
	// 					},
	// 					No: function(){
	// 						$("#role").val(old_role);
	// 						$(function(){
	// 							$('.select2').select2({
	// 								placeholder: '---- Select ----',
	// 								allowClear: true,
	// 								dropdownParent: $('.modal-dialog')
	// 							});
	// 							$('.select2-tags').select2({
	// 								tags: true,
	// 								tokenSeparators: [',']
	// 							});
	// 						});
	// 					}
	// 				}
	// 			});
	// 		/*}else{
	// 			toastr.warning('Please Update the loan foreclose and try again!');
	// 			$("#role").val(old_role);
	// 			$(function(){
	// 				$('.select2').select2({
	// 					placeholder: '---- Select ----',
	// 					allowClear: true,
	// 					dropdownParent: $('.modal-dialog')
	// 				});
	// 				$('.select2-tags').select2({
	// 					tags: true,
	// 					tokenSeparators: [',']
	// 				});
	// 			});
	// 		}*/
	// 	}
	// });
	
	$("#user_right").change(function(){		
		//var view_id    = "<?php echo $form_view->$prime_id; ?>";
		var user_right = $("#user_right").val();
		//if(view_id){
		$.confirm({
			content: 'Are you sure you want to change this rights?',
		    escapeKey: 'Yes',
		    buttons: {
		        Yes: function(){
						get_permission_list(user_right);
		        	},
		        No: function(){
		        	$("#user_right").val('<?php echo $form_view->user_right; ?>');
					$(function(){
						$('.select2').select2({
							placeholder: '---- Select ----',
							allowClear: true,
							dropdownParent: $('.modal-dialog')
						});
						$('.select2-tags').select2({
							tags: true,
							tokenSeparators: [',']
						});
					});
		        }
		    }
		});
		/*}else{
			get_permission_list(user_right);
		}*/
	});
	
	
	//Permission Hide Show
	//$("input[name = 'menu_id']").click(function(){
	$(document).on("click","input[name = 'menu_id']",function() {
		var menu_id = $(this).attr('id');
		var menu_id = 'ul_'+menu_id;
		if ($(this).is(':checked')) {
			$('ul #'+menu_id).show();
		}else{
			$('ul #'+menu_id).hide();
		}
	});
	//$("input[name = 'sub_menu_id']").click(function(){
	$(document).on("click","input[name = 'sub_menu_id']",function(){
		var sub_menu_id = $(this).attr('id');
		var sub_menu_id = 'ul_'+sub_menu_id;
		if ($(this).is(':checked')) {
			$('ul #'+sub_menu_id).show();
			 $('ul #' + sub_menu_id + ' :checkbox').prop('checked', true);
		}else{
			$('ul #'+sub_menu_id).hide();
			$('ul #' + sub_menu_id + ' :checkbox').prop('checked', false);
		}
	});
	$(document).on("click","input[name = 'grants[]']",function() {
		var module_class = $(this).attr('class');
		if ($(this).is(':checked')) {
			$('.'+module_class).prop('checked', true);
		}else{
			$('.'+module_class).prop('checked', false);
		}		
	});
	$('#employee_code').on("focusout", function(){
		var employee_code = $('#employee_code').val();
		$('#user_name').val(employee_code);
		var send_url = '<?php echo site_url("$controller_name/employee_code_exit"); ?>';
		var view_id  = "<?php echo $form_view->$prime_id; ?>";
		if(employee_code){
			$.ajax({
				type: "POST",
				url: send_url,
				data:{employee_code:employee_code,view_id:view_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
						$('#employee_code').val('');
					}
				}
			
			});
		}
    });	
	
	//Date based validations -- end
	//Pincode Empty updated
	<?php if($form_view->$prime_id == ""){ ?>
		$('#pin_code').val('');
		$('#present_pin_code').val('');
	<?php }else{?>
		var pin_code = $('#pin_code').val();
		var present_pin_code = $('#present_pin_code').val();
		if(pin_code == 0){
			$('#pin_code').val('');
		}
		if(present_pin_code == 0){
			$('#present_pin_code').val('');
		}
	<?php } ?>		
	//Disable Auto Fetch
	<?php 
	if($form_view->$prime_id == ""){?>
		$('.datepicker').val('');
	<?php }	?>		
	
	$('#date_of_birth').on("dp.hide",function (e) {
		prime_id = '<?php echo $form_view->$prime_id; ?>';
		var dob = $('#date_of_birth').val();
		var doj = $('#date_of_joining').val();
		date_diff_cal(prime_id,doj,dob);
	});
});

// FILE UPLOAD REMOVE
function remove_file(prime_id,is_defult,input_name){
	var prime_id_val = $("#"+prime_id).val();
	var send_url = '<?php echo site_url("$controller_name/remove_file"); ?>';
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
//PROGRESS STATUS FILE UPLOAD
function progress_bar(id) {
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
			$('#submit').html('Submit');
		} else {
			width++;
			elem.style.width = width + "%";
			elem.innerHTML = width  + "%";
		}
	}
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
function progress_bar(id) {
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
			$('#submit').html('Submit');
		} else {
			width++;
			elem.style.width = width + "%";
			elem.innerHTML = width  + "%";
		}
	}
}
function check_upload_size(size){
	size = (size / 1024 / 1024).toFixed(2);
	if(parseInt(size) <= 2){
		return true;
	}else{
		return false;
	}
}

function row_set_edit(row_id,table_name,view_id){
	if((row_id !== "") && (table_name !== "")){
		var send_url = '<?php echo site_url("$controller_name/row_set_edit"); ?>'; 
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
						$(function(){
							$('.select2').select2({
								placeholder: '---- Select ----',
								allowClear: true,
								dropdownParent: $('.modal-dialog')
							});
							$('.select2-tags').select2({
								tags: true,
								tokenSeparators: [',']
							});
						});
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
			var send_url = '<?php echo site_url("$controller_name/row_set_remove"); ?>'; 
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

/* Get Employee Auto Generation Code */
function get_employee_code(role){
	var send_url = '<?php echo site_url("$controller_name/get_employee_code"); ?>';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{role:role},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				$('#employee_code').val(rslt.digits);
				$('#user_name').val(rslt.digits);
			}else
			if(rslt.sts = 0){
				toastr.success(rslt.message);
			}else{
				toastr.warning(rslt.message);
				//$('#employee_code').val('');
				//$('#user_name').val('');
			}
		}
	});
}
function get_permission_list(user_right){
	var send_url = '<?php echo site_url("$controller_name/get_permission_list"); ?>';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{user_right:user_right},
		success: function(data){
			var rslt = JSON.parse(data);
			if(rslt.li_line){
				$('#permission_list').html(rslt.li_line);
				get_permission_list_up(rslt.menu_checked);
			}
		}
	});
}

function month_year(){
	$(".datepicker").datetimepicker({
		format: 'MM-YYYY',
	});
}
function hide_inputs(){
	$('#consultancy').parent().hide();
	$('#consultancy').addClass('ignore');
}
function consultancy_hide(role){
	if(parseInt(role) === 4){
		$('#consultancy').parent().show();
		$('#consultancy').removeClass('ignore');
	}else{
		$('#consultancy').parent().hide();
		$('#consultancy').addClass('ignore');
	}
}
function get_permission(){	
	var menu_checked = '<?php echo json_encode($menu_checked); ?>';
	console.log(menu_checked);
	var obj = jQuery.parseJSON(menu_checked);
	$.each(obj, function(key,value) {
	  $('#'+value).prop('checked', true);
	}); 
	$('input:checkbox[name="menu_id"]:checked').each(function(){
		var menu_id = $(this).attr("id");
		var menu_id = 'ul_'+menu_id;   	 
		if ($(this).is(':checked')) {
			$('ul #'+menu_id).show();
		}else{
			$('ul #'+menu_id).hide();
		}
	});
	$('input:checkbox[name="sub_menu_id"]:checked').each(function(){
	   	var sub_menu_id = $(this).attr('id');
		var sub_menu_id = 'ul_'+sub_menu_id;
		if ($(this).is(':checked')) {
			$('ul #'+sub_menu_id).show();
		}else{
			$('ul #'+sub_menu_id).hide();
		}
	});
}
function get_permission_list_up(menu_checked){
	$.each(menu_checked, function(key,value) {	
	  $('#'+value).prop('checked', true);
	  var menu_id = 'ul_'+value;
	  $('ul #'+menu_id).show();
	}); 
}

function select_option(){
	$('.select2').select2({
		placeholder: '---- Select ----',
		allowClear: true,
		dropdownParent: $('.modal-dialog')
	});
	$('.select2-tags').select2({
		tags: true,
		tokenSeparators: [',']
	});
}

//Doj and dob based age restrictions
function date_diff_cal(prime_id,doj,dob){
	if(doj.length !=0 && dob.length !=0){
		var doj_date   = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var dob_diff   = moment(doj, 'DD-MM-YYYY').add("-14", 'Y').format('YYYY-MM-DD');//date and year based updates
		var dob_date   = moment(dob, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(dob_date >= dob_diff){
			toastr.error("Date of joining and date of birth minimum difference is 14 years, please change the date?");
			if(!prime_id){
				$('#date_of_birth').val('');
			}
		}
	}
}

function empty_seperation(){
	$('#resignation_date').val('');
	$('#resignation_letter_upload').val('');
	$('#separation_type').val('');
	$('#last_working_date').val('');
	$('#separation_reason').val('');
	$('#termination_status').val(0);
	select_option();
}

//CHECK LOAN INSTALLMENT
function check_loan_installment(){
	var employee_code = $("#employee_code").val();
	$.ajax({
		type: "POST",async: false,
		url: '<?php echo site_url("$controller_name/check_loan_installment"); ?>',
		data:{employee_code:employee_code},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				return true;
			}else{
				return false;
			}
		}
	});
}
function employee_status_hide_show(employee_status){
		if(parseInt(employee_status) === 1){
			$("#inactive_date").parent().hide();
			$("#inactive_date").addClass('ignore');
			$("#inactive_date").val('');
		}else
		if(parseInt(employee_status) === 2){
			$("#inactive_date").parent().show();
			$("#inactive_date").removeClass('ignore');
		}
	}
</script>
<style>
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
}
</style>