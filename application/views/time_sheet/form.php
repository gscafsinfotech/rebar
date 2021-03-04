<?php 
$logged_user_role     = $this->session->userdata('logged_user_role');
$logged_role 		  = $this->session->userdata('logged_role');
$logged_branch		  = $this->session->userdata('logged_branch');
$logged_reporting	  =	$this->session->userdata('logged_reporting');
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
$form_view_id 		  = $form_view_id;
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
		
		$read = '';
		if(((int)$edit_read === 1) && ((int)$form_view->$prime_id)){
			//if($setting->$label_id){
				$read = 'readonly';
			//}
		}
			
		$required = "";
		if((int)$mandatory_field === 1){
			$required = "required";
		}
		if($form_view->$label_id){
			$input_value = $form_view->$label_id;
		}else{
			$input_value = $default_value;
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
				$input_value = str_replace('^',"'",$input_value);
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name, $read=>true,"class"=>"form-control input-sm $valid_class"));
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
				if($input_value){
					$date = date('d-m-Y',strtotime($input_value));
					if($date === "01-01-1970"){
						//$date = date("d-m-Y");
						$date = '';
					}
				}
				$form_input =  form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$date,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm datepicker"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//PICKLIST
			if((int)$field_type === 5){
				if($label_id === "branch"){
					$input_value  = $logged_branch;
				}else
				if($label_id === "reporting"){
					$input_value  = $logged_reporting;
				}
				$drop_exist = true;
				$drop_down_array = array("name" => $label_id,"id" => $label_id,"class" =>'form-control input-sm select2');
				if($read){
					$drop_down_array['readonly'] = 'readonly';
				}
				$form_dropdown =  form_dropdown($drop_down_array,$all_pick[$prime_form_id] ,$input_value);
				$input_box .= "<div class='form-group'>$form_label $form_dropdown</div>";
			}else
			//CHECKBOX
			if((int)$field_type === 6){
				$checkbox_array = array("name" => $label_id,"id" => $label_id, "value"=> 1, "checked" => ($input_value) ? 1 : 0);
				if($read){
					$checkbox_array['disabled'] = 'true';
				}
				$form_checkbox = form_checkbox($checkbox_array);
				$input_box .= "<div class='form-group'> <label class='checkbox-inline'> $form_checkbox $form_label </label></div>";
			}else
			//MULTI PICKLIST
			if((int)$field_type === 7){
				$drop_exist = true;
				$multi_name   = $label_id."[]";
				$multi_select = explode(',',$input_value);
				$drop_down_array = array("name" => $multi_name,"multiple id" => $label_id,"class" =>'form-control input-sm select2');
				if($read){
					$drop_down_array['readonly'] = 'readonly';
				}
				$form_dropdown = form_dropdown($drop_down_array,$all_pick[$prime_form_id] ,$multi_select);
				$input_box .= "<div class='form-group'> $form_label $form_dropdown</div>";
			}else
			//TEXT AREA
			if((int)$field_type === 8){
				$value = str_replace('^',"'",$input_value);
				$input_box .= "<div class='form-group'> $form_label <textarea name='$label_id' id='$label_id' class='form-control' rows='4' placeholder='$label_name' $read = true>$value</textarea></div>";
			}else
			//AUTOCOMPLETE
			if((int)$field_type === 9){
				$hidden_id    = $label_id."_hidden_".$prime_form_id;
				$hidden_value = $all_pick[$prime_form_id];
				$autocomplete_array = array("name"=>$hidden_id, "id"=>$hidden_id,"value"=>$hidden_value,"placeholder"=>"Search ".$label_name, "class"=>"form-control input-sm");
				if($read){
					$autocomplete_array['readonly'] = 'readonly';
				}
				$form_input   = form_input($autocomplete_array);
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
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name,$read=>true,"class"=>"form-control input-sm $valid_class"));
				$input_box .= "<div class='form-group'>$form_label $form_input</div>";
			}else
			//EMAIL
			if((int)$field_type === 12){
				$form_input = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$input_value,"placeholder"=>$label_name,$read=>true, "class"=>"form-control input-sm $valid_class"));
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
				$form_input =  form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$date,"placeholder"=>$label_name,$read=>true, "class"=>"form-control input-sm datepicker_time"));
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
			}else
			// TIME ONLY
			if((int)$field_type === 15){
				if($input_value){
					$time = $input_value;
				}else{
					$time = "00:00";
				}
				$form_input =  form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>$time,"placeholder"=>$label_name, $read=>true, "class"=>"form-control input-sm only_time"));
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
								<ul class='nav nav-tabs' data-tabs='tabs'>
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
								  if ($(this).val() === '' && (!$(this).hasClass('ignore'))) {
									isValid = false;
									toastr.error('Please fill all required field');
									$(this).addClass('ignore');
								  }else{
									 $(this).removeClass('ignore');
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
	var prime_id         = "#<?php echo $prime_id;?>";
	var form_id          = "#<?php echo $form_id;?>";
	var date_exist       = "<?php echo $date_exist;?>";
	var date_time_exist  = "<?php echo $date_time_exist;?>";
	var logged_role 	 = "<?php echo $logged_role;?>";
	var form_view_id     = "<?php echo $form_view_id;?>";
	default_hide();
	var work_type = $("#work_type").val();

	$("#work_type").change(function(){
		var work_type 		 = $(this).val();
		if(parseInt(logged_role) === 5){
			show_detailer_worktype(work_type);
		}else
		if(parseInt(logged_role) === 4){
			show_teamleader_worktype(work_type);
		}else
		if(parseInt(logged_role) === 3){
			show_projectmanager_worktype(work_type);
		}
	});
	if(parseInt(form_view_id) !== -1){
		if(parseInt(logged_role) === 5){
			show_detailer_worktype(work_type);
		}else
		if(parseInt(logged_role) === 4){
			show_teamleader_worktype(work_type);
		}else
		if(parseInt(logged_role) === 3){
			show_projectmanager_worktype(work_type);
		}
	}
	var client_name 	 = $("#client_name").val();
	$("#client_name").change(function(){
		var client_name  = $(this).val();
		select_clientname(client_name);
	});
	select_clientname(client_name);
	var project_name 	 = $("#project").val();
	$("#project").change(function(){
		var project_name = $(this).val();
		select_project(project_name);
	});
	select_project(project_name);
	<?php echo $user_read_only; ?>
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
	if(date_time_exist === "1"){
		$(function () {
			$(".datepicker_time").datetimepicker({
				format: 'DD-MM-YYYY HH:mm:ss',
				//debug: true
			});
		});
	}
	var drop_exist = "<?php echo $drop_exist;?>";
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
	/*
	$('input').keypress(function(e){ 
		e = e || event;
		var s = String.fromCharCode(e.charCode);
		if(s.match(/[A-Z]/)){
			toastr.clear();
			toastr.error('Capital letters disabled');
			return false;
		}
	});
	*/
	$('textarea').on('keyup keypress', function(e) {
		if(e.keyCode === 13) {    
			e.stopPropagation();
		}else
		if(e.shiftKey){
			e.stopPropagation();
		}
	});
	$(".number").bind('keyup', function(e) {
		this.value = this.value.replace(/[^0-9_.]/g,'');
	});
	$('.alpha').bind('keypress', function (event) {
		var regex = new RegExp("^[a-zA-Z0-9\-_.@\/\\s]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
		   event.preventDefault();
		   return false;
		}
	});
	$(".alpha_text").keypress(function(event){
	     var inputValue = event.charCode;
	     if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
	         event.preventDefault();
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
			$('.nav-tabs a[href="#time_line"]').tab('show');
			$(form).ajaxSubmit({
				success: function (response){
					console.log(response);

					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					if(response.success){
						$(prime_id).val(response.insert_id);
						//table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
						//table_support.refresh();
						$('.row_btn').show();
						// $('.modal').modal('hide');
						//$('#table').DataTable.reload();
						toastr.success(response.message);
						$('#table').DataTable().ajax.reload();
					}else{
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
});
// DEFAULT HIDE
function default_hide(){
	$('#detailing_time,#non_detailing_time,#study,#discussion,#rfi,#checking,#other_works,#correction_time,#work_status,#work_description,#co_number,#revision_time,#non_revision_time,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#non_billable_hours,#qa_minor,#tonnage,#change_order_time,#bar_listing_time,#bar_list_quantity,#billable,#billable_hours,#non_billable_hours,#emails,#was,#actual_billable_time,#co_checking,#qa_checking,#monitoring,#tonnage_change,#actual_tonnage,#aec,#bar_listing_checking').parent().hide();
	$('#detailing_time,#non_detailing_time,#study,#discussion,#rfi,#checking,#other_works,#correction_time,#work_status,#work_description,#co_number,#revision_time,#non_revision_time,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#non_billable_hours,#qa_minor,#tonnage,#change_order_time,#bar_listing_time,#bar_list_quantity,#billable,#billable_hours,#non_billable_hours,#emails,#was,#actual_billable_time,#co_checking,#qa_checking,#monitoring,#tonnage_change,#actual_tonnage,#aec,#bar_listing_checking').addClass('ignore');
}
//DETAILER SHOW
function show_detailer_worktype(work_type){
	if(parseInt(work_type) === 1){
		$('#detailing_time,#non_detailing_time,#study,#discussion,#rfi,#checking,#other_works,#correction_time,#work_status,#work_description,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#qa_minor,#tonnage').parent().show();
		$('#detailing_time,#non_detailing_time,#study,#discussion,#rfi,#checking,#other_works,#correction_time,#work_status,#work_description,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#qa_minor,#tonnage').removeClass('ignore');
		$('#co_number,#revision_time,#non_revision_time,#change_order_time,#bar_listing_time,#bar_list_quantity,#billable,#billable_hours,#non_billable_hours').parent().hide();
		$('#co_number,#revision_time,#non_revision_time,#change_order_time,#bar_listing_time,#bar_list_quantity,#billable,#billable_hours,#non_billable_hours').addClass('ignore');
		// $('#detailing_time,#non_detailing_time,#study,#discussion,#rfi,#checking,#other_works,#correction_time,#work_status,#work_description,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#qa_minor,#tonnage').val('');
	}else{
		$('#co_number,#revision_time,#non_revision_time,#correction_time,#checking,#study,#discussion,#rfi,#change_order_time,#bar_listing_time,#bar_list_quantity,#other_works,#work_status,#work_description,#billable,#billable_hours,#non_billable_hours').parent().show();
		$('#co_number,#revision_time,#non_revision_time,#correction_time,#checking,#study,#discussion,#rfi,#change_order_time,#bar_listing_time,#bar_list_quantity,#other_works,#work_status,#work_description,#billable,#billable_hours,#non_billable_hours').removeClass('ignore');
		$('#detailing_time,#non_detailing_time,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#qa_minor,#tonnage').parent().hide();
		$('#detailing_time,#non_detailing_time,#first_check_major,#first_check_minor,#second_check_major,#second_check_minor,#qa_major,#qa_minor,#tonnage').addClass('ignore');
		// $('#co_number,#revision_time,#non_revision_time,#correction_time,#checking,#study,#discussion,#rfi,#change_order_time,#bar_listing_time,#bar_list_quantity,#other_works,#work_status,#work_description,#billable,#billable_hours,#non_billable_hours').val('');
	}
	
}
//TEAM LEADER SHOW
function show_teamleader_worktype(work_type){
	if(parseInt(work_type) === 1){
		$('#emails,#study,#checking,#discussion,#was,#correction_time,#tonnage_change,#actual_tonnage').parent().show();
		$('#emails,#study,#checking,#discussion,#was,#correction_time,#tonnage_change,#actual_tonnage').removeClass('ignore');
		$('#rfi,#study,#aec,#billable_hours,#co_checking,#bar_listing_checking,#other_works,#non_billable_hours,#actual_billable_time').parent().hide();
		$('#rfi,#study,#aec,#billable_hours,#co_checking,#bar_listing_checking,#other_works,#non_billable_hours,#actual_billable_time').addClass('ignore');
		// $('#emails,#study,#checking,#discussion,#was,#correction_time,#tonnage_change,#actual_tonnage').val('');
	}else{
		$('#rfi,#study,#checking,#aec,#correction_time,#was,#emails,#discussion,#billable_hours,#co_checking,#bar_listing_checking,#other_works,#non_billable_hours,#actual_billable_time').parent().show();
		$('#rfi,#study,#checking,#aec,#correction_time,#was,#emails,#discussion,#billable_hours,#co_checking,#bar_listing_checking,#other_works,#non_billable_hours,#actual_billable_time').parent().removeClass('ignore');
		$('#tonnage_change,#actual_tonnage').parent().hide();
		$('#tonnage_change,#actual_tonnage').addClass('ignore');
		// $('#rfi,#study,#checking,#aec,#correction_time,#was,#emails,#discussion,#billable_hours,#co_checking,#bar_listing_checking,#other_works,#non_billable_hours,#actual_billable_time').val('');
	}
}
//PROJECT MANAGER SHOW
function show_projectmanager_worktype(work_type){
	if(parseInt(work_type) === 1){
		$('#emails,#study,#qa_checking,#discussion,#was,#monitoring').parent().show();
		$('#emails,#study,#qa_checking,#discussion,#was,#monitoring').removeClass('ignore');
		$('#rfi,#other_works,#co_checking,#bar_listing_checking').parent().hide();
		$('#rfi,#other_works,#co_checking,#bar_listing_checking').addClass('ignore');
		// $('#rfi,#study,#qa_checking,#monitoring,#was,#discussion,#other_works,#co_checking,#bar_listing_checking').val('');
	}else{
		$('#rfi,#study,#qa_checking,#monitoring,#was,#discussion,#other_works,#co_checking,#bar_listing_checking').parent().show();
		$('#rfi,#study,#qa_checking,#monitoring,#was,#discussion,#other_works,#co_checking,#bar_listing_checking').removeClass('ignore');
		$('#emails').parent().hide();
		$('#emails').addClass('ignore');
		// $('#emails,#study,#qa_checking,#discussion,#was,#monitoring').val('');
	}
}
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
function select_clientname(client_name){
	var project_name 	 = $("#project").val();
	var send_url 	     = '<?php echo site_url("$controller_name/select_clientname"); ?>';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{client_name:client_name,project_name:project_name},
		success: function(data){
			$('#project').html(data);
		}
	});
}
function select_project(project_name){	
	var diagram_no   = $("#diagram_no").val();
	var send_url 	 = '<?php echo site_url("$controller_name/select_project"); ?>';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{project_name:project_name,diagram_no:diagram_no},
		success: function(data){
			console.log(data);
			$('#diagram_no').html(data);
		}
	});
}
</script>