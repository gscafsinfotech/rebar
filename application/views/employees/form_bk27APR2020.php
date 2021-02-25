<?php 
$logged_user_role     = $this->session->userdata('logged_user_role');
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
			//if($setting->$label_id){
				$read = 'readonly';
			//}
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
	$menu_array             = array();
	$menu_data_array        = array();
	$submenu_data_array     = array();
	$menu_checked           = array(); 
	foreach($all_modules as $module){		
		$access_add         = $module->access[0]['access_add'];
		$access_update      = $module->access[0]['access_update'];
		$access_delete      = $module->access[0]['access_delete'];
		$access_search      = $module->access[0]['access_search'];
		$access_export      = $module->access[0]['access_export'];
		$access_import      = $module->access[0]['access_import'];
		$grants_menu_id     = $module->access[0]['grants_menu_id'];
		$grants_sub_menu_id = $module->access[0]['grants_sub_menu_id'];
		$check_box_input    = form_checkbox("grants[]", $module->module_id, $module->grant, "class='module'");
		$menu_input = form_checkbox("menu_id", $module->menu_id, $grants_menu_id,"id='".str_replace(" ","_",strtolower($module->menu_name))."'", "class='menu_id'");
		$sub_menu_input  = form_checkbox("sub_menu_id", $module->sub_menu_id, $grants_sub_menu_id,"id='".str_replace(" ","_",strtolower($module->sub_menu_name."_".$module->menu_id))."'", "class='sub_menu_id'");
		if((int)$module->menu_id === (int)$grants_menu_id){
			$menu_checked[$module->menu_name] = str_replace(" ","_",strtolower($module->menu_name));
		}
		$menu_name       = $module->menu_name;
		$sub_menu_name   = $module->sub_menu_name;
		$module_name     = $module->module_name;		
		$add_id          = $module->module_id ."::add";
		$add_checkbox    = form_checkbox(array("name" =>'access[]',"value" => $add_id,   "checked" => ($access_add) ? 1 : 0));
		$update_id       = $module->module_id ."::update";
		$update_checkbox = form_checkbox(array("name" =>'access[]',"value" => $update_id, "checked" => ($access_update) ? 1 : 0));
		$delete_id       = $module->module_id ."::delete";
		$delete_checkbox = form_checkbox(array("name" =>'access[]',"value" => $delete_id, "checked" => ($access_delete) ? 1 : 0));
		$search_id       = $module->module_id ."::search";                                
		$search_checkbox = form_checkbox(array("name" =>'access[]',"value" => $search_id, "checked" => ($access_search) ? 1 : 0));
		$export_id       = $module->module_id ."::export";                                
		$export_checkbox = form_checkbox(array("name" =>'access[]',"value" => $export_id, "checked" => ($access_export) ? 1 : 0));
		$import_id       = $module->module_id ."::import";                                
		$import_checkbox = form_checkbox(array("name" =>'access[]',"value" => $import_id, "checked" => ($access_import) ? 1 : 0));
		
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
							$menu_input  <span style='color:#000000;Font-size:16px;'><b>$menu_name</b></span> 
						</label>";
		$sub_menu_data = "<label class='checkbox-inline' style='margin-bottom:6px;'>
							$sub_menu_input  <span style='color:#4DC147;Font-size:14px;'><b>$sub_menu_name</b></span> 
						</label>";		
		if((int)$form_view->role === 1){
			$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
			$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
			$menu_data_array[$menu_name]        = $menu_data;
			$submenu_data_array[$sub_menu_name] = $sub_menu_data;
		}else{
			$sub_menu_name = str_replace(" ","_",strtolower($sub_menu_name."_".$module->menu_id));
			$admin_module = array("module_setting"=>true,"tester"=>true,"config"=>true);
			if(!$admin_module[$module->module_id]){
				$menu_array[$menu_name][$sub_menu_name][] = array("access_data"=>$access_data,"grand_data"=>$grand_data);
				$menu_data_array[$menu_name]        = $menu_data;
				$submenu_data_array[$sub_menu_name] = $sub_menu_data;
			}
		}
	}
	$li_line = "";
	foreach ($menu_array as $menu_name => $value){
		$menu = $menu_data_array[$menu_name];		
		$name = str_replace(" ","_",strtolower($menu_name));
		$sub_line = "";
		foreach ($value as $sub_menu_name => $data) {
			$sub_menu = $submenu_data_array[$sub_menu_name];
			$tr_line = "";
			foreach ($data as $key => $tr_value){
				$grand_data  = $tr_value['grand_data'];
				$access_data = $tr_value['access_data'];
				$tr_line .=  "<li>
									$grand_data
									$access_data
								</li>";				
			}	
			$tr_line = "<ul id='ul_$sub_menu_name' style='display:none;'>$tr_line</ul>";
			$sub_line .= "<li>	
							$sub_menu					
							$tr_line
						</li>";		
		}
		$sub_line = "<ul id='ul_$name' style='display:none;'>$sub_line</ul>";
		$li_line .= "<li>	
						$menu					
						$sub_line
					</li>";
	}
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

?>
<script type="text/javascript">
$(document).ready(function(){
	get_permission();		
	var prime_id         = "#<?php echo $prime_id;?>";
	var form_id          = "#<?php echo $form_id;?>";
	var date_exist       = "<?php echo $date_exist;?>";
	var date_time_exist  = "<?php echo $date_time_exist;?>";
	var drop_exist       = "<?php echo $drop_exist;?>";
	var view_id          = "<?php echo $form_view->$prime_id; ?>";
	var user_right       = "<?php echo $logged_user_role; ?>";
	
	if(user_right !== '1'){
		$('#offer_reference_no,#emp_name,#employee_code,#role,#date_of_joining,#resignation_date,#separation_type,#termination_status,#stop_pay_month,#stop_payment_type').attr('readonly','readonly');
		select_option();
	}
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
	/*if(date_exist === "1"){
		$(function () {
			$(".datepicker").datetimepicker({
				format: 'DD-MM-YYYY',
				//debug: true
			});
		});
	}
	if(date_time_exist === "1"){
		$(function () {
			$(".datepicker_time").datetimepicker({
				format: 'DD-MM-YYYY HH:mm:ss',
				//debug: true
			});
		});
	}*/
	
	/*$("#work_duration_start").datetimepicker({
		format: 'MM-YYYY',
	});
	$("#work_duration_end").datetimepicker({
		format: 'MM-YYYY',
	});*/
		
	$("#separation_type").change(function(){
		var role 			= $('#role').val();
		var separation_type = $('#separation_type').val();
		if(separation_type){
			var resignation_date = $('#resignation_date').val();
			if(resignation_date){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url("$controller_name/get_last_working"); ?>',
					data:{resignation_date:resignation_date,role:role},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							$('#last_working_date').val(rslt.notice_day);
							$('#termination_status').val(1);
						}else{
							toastr.error(rslt.msg);
							$('#last_working_date').val(" ");
							$('#separation_type').val('');							
						}
						select_option();
					}				
				});
			}else{
				toastr.error("Resignation Details Should Not be Empty");
				$('#separation_type').val('');
			}
		}
	});
	
	if($.trim($('#last_working_date').val()) !== ''){
		$('#termination_status').val(1);
		select_option();
	}
	
	$("#stop_payment_type").change(function(){
		stop_payment = $('#stop_payment_type').val();
		if(parseInt(stop_payment) != 3){
			$('#stop_pay_status').val(1);
		}else{
			$('#stop_pay_status').val(0);
		}
	});
	
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
			$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#submit').attr('disabled','disabled');			
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

	$('#retirement_years').on("change",function(e){
		var dob     = $('#date_of_birth').val();
		if(dob === ""){
			toastr.error("Date of Birth Should not Empty");
			$('#emp_age').val('');
			$('#retirement_date').val('');
			$('#retirement_years').val('');
		}
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
		var retirement_date   = moment(retire_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(retirement_date){
			if(date_of_joining > retirement_date){
			var retirement_years = $('#retirement_years').val();
				if(parseInt(retirement_years) !==0){
					toastr.error("Date of joining is less than Retirement Year, change the date?");
				}
			}
		}
	});
	
	
	$("#role").change(function(){
		var view_id  = "<?php echo $form_view->$prime_id; ?>";
		var role     = $("#role").val();
		if(view_id === ""){
			get_employee_code(role);
		}
	});
	
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
	
	//remove --select -- options
	$('#language_proficiency option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
	}).remove();
	
	
	//Date based validations -- start
		
	//resignation date check with validations -- 13SEP2019
	$('#resignation_date').on("dp.hide",function (e) {
		var today             = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		var doj  = $('#date_of_joining').val();
		var date_of_joining   = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var resignation_date = $('#resignation_date').val();
		var resign_date   = moment(resignation_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var role     = $('#role').val();
		if(resign_date <= date_of_joining){
			toastr.error("Resignation date not less than date of joining, please choose another date?");
			$('#resignation_date').val('');
		}
		if(today < resign_date){
			toastr.error("Resignation date not allowed greater than today date?");
			$('#resignation_date').val('');
		}
		if(resignation_date){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url("$controller_name/check_payroll"); ?>',
				data:{resignation_date:resignation_date,role:role},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
					}else{
						$('#resignation_date').val('');
						toastr.warning(rslt.message);
					}
				}
			});
		}
	});
	
	
	//seperation date check with validations -- 13SEP2019
	$('#last_working_date').on("dp.hide",function (e) {
		var today             = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		var doj  = $('#date_of_joining').val();
		var date_of_joining   = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var last_working_date = $('#last_working_date').val();
		var last_date   = moment(last_working_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(last_date <= date_of_joining){
			toastr.error("Seperation date not less than date of joining, please choose another date?");
			$('#last_working_date').val('');
		}
		if(today < last_date){
			toastr.error("Last working date not allowed greater than today date?");
			$('#last_working_date').val('');
		}
	});
	
	
	//family date check and validations  -- 13SEP2019
	$('#family_date_of_birth').on("dp.hide",function (e) {
		var family_birth_date = $('#family_date_of_birth').val();
		var today             = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		var family_date       = moment(family_birth_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(today < family_date){
			toastr.error("Date of Birth not allowed greater than today date?");
			$('#family_date_of_birth').val('');
		}
	});
	
	
	//course date check and validations -- 13SEP2019
	$('#course_year_of_passing').on("dp.hide",function (e){
		var course_year = $('#course_year_of_passing').val();
		var today       = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		let course_date = moment(course_year, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(today < course_date){
			toastr.error("Course Passing year is not allowed greater than today date?");
			$('#course_year_of_passing').val('');
		}
	});
	
	//Training date check and validations -- 13SEP2019
	$('#training_date').on("dp.hide",function (e){
		var training_date = $('#training_date').val();
		var today         = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		let train_dt      = moment(training_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(today < train_dt){
			toastr.error("Training Period is not allowed greater than today date?");
			$('#training_date').val('');
		}
	});
	
	
	//From date check and validations -- 13SEP2019 -- rechecked
	$('#previous_from_date,#past_to_date').on("dp.hide",function (e){
		var doj           = $('#date_of_joining').val();
		var previous_date = $('#previous_from_date').val();
		var past_to_date  = $('#past_to_date').val();
		var today         = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		let pre_date      = moment(previous_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var past_date     = moment(past_to_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var doj_date      = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(previous_date.length !==0){
			if(pre_date >=doj_date){
				toastr.error("From date is not allowed greater than date of joining date?");
				$('#previous_from_date').val('');
			}
		}
		if(past_to_date.length !==0){
			if(past_date >=doj_date){
				toastr.error("To date is not allowed greater than date of joining date?");
				$('#past_to_date').val('');
			}
		}
		
		if(previous_date.length !==0 && past_to_date.length !==0){
			if(pre_date > today){
				toastr.error("Start date is not allowed greater than today date?");
				$('#previous_from_date').val('');
			}
			if(past_date >= today){
				toastr.error("End date is not allowed greater than today date?");
				$('#past_to_date').val('');
			}
			if(pre_date >= past_date){
				toastr.error("End date is not allowed less than start date?");
				$('#past_to_date').val('');
			}
		}
	});
	
	//End date check and validations -- 13SEP2019  -- rechecked
	/*$('#past_to_date').on("dp.hide",function (e){
		var doj           = $('#date_of_joining').val();
		var past_to_date  = $('#past_to_date').val();
		var pre_from_date = $('#previous_from_date').val();
		var today         = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		var past_date     = moment(past_to_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var pre_date      = moment(pre_from_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var doj_date      = moment(doj, 'DD-MM-YYYY').format('YYYY-MM-DD');
		
		if(past_date >= today){
			toastr.error("End date is not allowed greater than today date?");
			$('#past_to_date').val('');
		}else
		if(past_date >=doj_date){
			toastr.error("End date is not allowed greater than date of joining date?");
			$('#past_to_date').val('');
		}
		
		if(pre_from_date){
			if(pre_date >= past_date){
				toastr.error("End date is not allowed less than start date?");
				$('#past_to_date').val('');
			}
		}
	});*/
	
//Stop pay month check with validations
	/*$('#stop_pay_month').on("dp.hide",function (e) {
		var stop_pay_month  = $('#stop_pay_month').val();
		var employee_code   = $('#employee_code').val();
		if(stop_pay_month){
			$.ajax({
				type: "POST",
				url: '<?php //echo site_url("$controller_name/check_payroll_exit"); ?>',
				data:{employee_code:employee_code,stop_pay_month:stop_pay_month},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						if(rslt.payroll_status){	
							toastr.success(rslt.payroll_message);
						}else{
							toastr.error(rslt.payroll_message);
							$('#stop_pay_month').val('');
						}
						if(rslt.stop_pay_month_status){
							toastr.success(rslt.stop_pay_month_message);
						}else{
							toastr.error(rslt.stop_pay_month_message);
							$('#stop_pay_month').val('');
						}
					}
				}
			});
		}
	});*/
	
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
	
	//release date always 
	//release date restriction also done--16SEP2019
	$('#release_date').on("dp.hide",function (e) {
		var today         = moment(new Date(), 'DD-MM-YYYY').format('YYYY-MM-DD');
		var last_working       = $('#last_working_date').val();
		var release_date       = $('#release_date').val();
		var last_date          = moment(last_working, 'DD-MM-YYYY').format('YYYY-MM-DD');
		var release_check_date = moment(release_date, 'DD-MM-YYYY').format('YYYY-MM-DD');
		if(release_check_date <= last_date){
			toastr.error("Release Date not less than separation date, please change the date?");
			$('#release_date').val('');
		}
		if(today < release_check_date){
			toastr.error("Release date less than today date, please choose another date?");
			$('#release_date').val('');
		}
		var employee_code  = $('#employee_code').val();
		if(release_date && employee_code){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url("$controller_name/check_termination_status"); ?>',
				data:{employee_code:employee_code},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
						empty_seperation();
					}else{
						toastr.error(rslt.message);
						$('#release_date').val('');
						$('#release_reason').val('');
					}
				}
			});
		}
	});
	
	
	//Min Date Current date for Release date
	/*var last_working       = $('#last_working_date').val();
	if(last_working !=''){
		$("#release_date").datetimepicker({
			format: 'DD-MM-YYYY',
			minDate: moment(last_working),
		});
	}*/
	
	//Disable Auto Fetch
	<?php 
	if($form_view->$prime_id == ""){?>
		$('.datepicker').val('');
	<?php }	?>
	
	$("#pf_eligibility").change(function(){
		var pf_eligibility = $('#pf_eligibility').val();
		if(parseInt(pf_eligibility) === 1){
			pf_acc_show_all();
		}else{
			pf_acc_hide_all();
		}
	});
	
	/*esi_loc_show();
	$("#esi_eligibility").change(function(){
		var esi_eligibility = $('#esi_eligibility').val();
		if(parseInt(esi_eligibility) === 1){
			esi_loc_show();
		}else{
			esi_loc_hide();			
		}
	});*/
	
	//professional tax location based range is checking
	$("#professional_tax_location").change(function(){
		var tax_loc = $('#professional_tax_location').val();
		if(tax_loc){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/tax_range_check"); ?>',
				data: {tax_loc:tax_loc},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(!rslt.success){
						toastr.error(rslt.msg);
						$('#professional_tax_location').val('0');
						select_option();
					}
				},
			});
		}
	});
	
	//date of wedding restrictions start date of birth and martial status based to updates
	$('#date_of_wedding').on("dp.hide",function (e) {
		var martial_sts = $('#marital_status').val();
		var dob         = $('#date_of_birth').val();
		var dow         = $('#date_of_wedding').val();	
		if(parseInt(martial_sts) === 1 && dob.length !=0){
			var dob_date          = moment(dob, 'DD-MM-YYYY').format('YYYY-MM-DD');
			var dow_date          = moment(dow, 'DD-MM-YYYY').format('YYYY-MM-DD');
			if(dow_date <= dob_date){
				toastr.error("Date of wedding is not less than date of birth?");
				$('#date_of_wedding').val('');
			}
		}else
		if(dow.length !=0){
			toastr.warning("Please check martial status and date of birth of employee?");
		}
	});
	
	$('#date_of_birth').on("dp.hide",function (e) {
		prime_id = '<?php echo $form_view->$prime_id; ?>';
		var dob = $('#date_of_birth').val();
		var doj = $('#date_of_joining').val();
		date_diff_cal(prime_id,doj,dob);
	});
	
	/*$('#offer_reference_no').on("focusout", function(){
		var offer_no        = $('#offer_reference_no').val();
		var mobile_number   = $('#mobile_number').val();
		if(offer_no){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/offerno_exit"); ?>',
				data:{offer_no:offer_no,mobile_number:mobile_number},
				success: function(data){
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
						$('#offer_reference_no').val('');
					}
				}
			});
		}
	});*/

	//Maritial status hide and show --MRJ-start 18FEB2020
	var marital_status = $('#marital_status').val();
	if(parseInt(marital_status) === 1){
		show_all();
	}else{
		hide_all();
	}
	$('#marital_status').on("change",function(e){
		var marital_status = $('#marital_status').val();
		if((parseInt(marital_status) === 2) || (marital_status == '')){
			hide_all();
		}else{
			show_all();
		}
	});
	//Maritial status hide and show --MRJ-end 18FEB2020
	
	//work experience  hide and show --MRJ-start 19FEB2020
	var experience_val = $('#fresher_or_experience').val();
	if((parseInt(experience_val) === 1) || (experience_val == '')){
		expere_hide_all();
	}else{
		expere_show_all();
	}
	$('#fresher_or_experience').on("change",function(e){
		var experience_val = $('#fresher_or_experience').val();
		if((parseInt(experience_val) === 1) || (experience_val == '')){
			expere_hide_all();
		}else{
			expere_show_all();
		}
	});
	//work experience  hide and show --MRJ-start 20FEB2020
	
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
function hide_all(){
	$('#date_of_wedding,#spouse_name,#spouse_contact_no').parent().hide();
	$('#date_of_wedding,#spouse_name,#spouse_contact_no').addClass('ignore');
}
function show_all(){
	$('#date_of_wedding,#spouse_name,#spouse_contact_no').parent().show();
	$('#date_of_wedding,#spouse_name,#spouse_contact_no').removeClass('ignore');
}

function pf_acc_show_all(){
	$('#pf_account_number').parent().show();
}

function pf_acc_hide_all(){
	$('#pf_account_number').parent().hide();
}

function expere_hide_all(){
	$('#total_experience,#organization_name,#desigantion,#manager_name,#work_exp_start_month,#work_exp_start_year,#work_exp_end_month,#work_exp_end_year').parent().hide();
	$('#total_experience,#organization_name,#desigantion,#manager_name,#work_exp_start_month,#work_exp_start_year,#work_exp_end_month,#work_exp_end_year').addClass('ignore');
}

function expere_show_all(){
	$('#total_experience,#organization_name,#desigantion,#manager_name,#work_exp_start_month,#work_exp_start_year,#work_exp_end_month,#work_exp_end_year').parent().show();
	$('#total_experience,#organization_name,#desigantion,#manager_name,#work_exp_start_month,#work_exp_start_year,#work_exp_end_month,#work_exp_end_year').removeClass('ignore');
}

function get_permission(){	
	var menu_checked = '<?php echo json_encode($menu_checked); ?>';
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

/*function esi_loc_show(){
	$('#esi_location').parent().show();
	$('#esi_dispensary').parent().show();
}
function esi_loc_hide(){
	$('#esi_location').parent().hide();
	$('#esi_dispensary').parent().hide();
}
*/

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
</style>