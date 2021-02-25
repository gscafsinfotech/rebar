<?php
/*============ BSK EMPLOYEE PERMISSION CUSTOME BLOCK ============*/
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
	$view_content .= "<ul id='permission_list'>
						$li_line
					</ul>";
?>
<?php 
echo form_open('employee_permission/save/' . $role_id,array('id'=>'employee_permission_form','class'=>'form-inline'));
?>
<fieldset>
	<div class="form-group">
		<?php
		echo form_label("User Right", 'User Right', array('class' => 'required'));
		echo form_dropdown(array('name' => 'role','id' => 'role','class' => 'form-control'), $role_info, $role_id);
		?>
	</div>
	<div class="form-group"> 
		<label class="checkbox-inline"> 
			<input type="checkbox" name="update_for_all_employees" value="1" id="update_for_all_employees">
 			<label for="update_for_all_employees" class="control-label ">Update For All Employees</label> 
 		</label>
 	</div>
 	<?php echo $view_content; ?>
</fieldset>
<?php echo form_close(); ?>	
<script type="text/javascript">
$(document).ready(function(){	
	var view_id  = "<?php echo $role_id; ?>";
	if(view_id > 0){
		get_permission();
		$('#role').attr('readonly', true);  
		$("#role").change(function(){
		 	$("#role").val(view_id);
		}); 
	}
	
	$('input').keypress(function(e){ 
		e = e || event;
		var s = String.fromCharCode(e.charCode);
		if(s.match(/[A-Z]/)){
			toastr.clear();
			toastr.error('Capital letters disabled');
			return false;
		}
	});
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
	$('.alpha').bind('keypress', function (event){
		var regex = new RegExp("^[a-z0-9\-_.@\/\\s]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
		   event.preventDefault();
		   return false;
		}
	});
	$.validator.setDefaults({ignore:[]});	
	$.validator.addMethod("alphanumeric", function(value, element) {
		return this.optional(element) || /^[a-z0-9\-\s]+$/i.test(value);
	}, "Allow only letters, numbers, or dashes.");
	
	$('#employee_permission_form').submit(function(event){ event.preventDefault(); }).validate({
		rules:{
			role :"required",
		},
		submitHandler: function (form){
			$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					if(response.success){
						dialog_support.hide();
		            	table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
		            	table_support.refresh();
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
	
	/* Hide Show */
	$("#role").change(function(){
		var role = $("#role").val();
		if(role > 0){
			get_permission_list(role);
		}	
	});
	
	/* Hide Show */
	//Jquery Confirm Box
	//Permission Hide Show
	$(document).on("click","input[name = 'menu_id']",function() {
		var menu_id = $(this).attr('id');
		var menu_id = 'ul_'+menu_id;
		if ($(this).is(':checked')) {
			$('ul #'+menu_id).show();
		}else{
			$('ul #'+menu_id).hide();
		}
	});
	
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
});

function get_permission_list(role){
	var send_url = '<?php echo site_url("$controller_name/get_permission_list"); ?>';
	$.ajax({
		type: "POST",
		url: send_url,
		data:{role:role},
		success: function(data) {						
			if(data){
				$('#permission_list').html(data);
			}			
		}
	});
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
</script>