<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#create_format">Manage Excel Format</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#excel_mapping">Add Excel Mapping</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="create_format">
		<?php echo form_open('utilities_settings/save/' . $prime_module_id,array('id'=>'excel_format_form','class'=>'form-inline')); ?>
			<fieldset id="FundBasicInfo">		
				<?php
					echo form_input( array('name'=>'excel_module_id', 'id'=>'excel_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_input( array('name'=>'prime_excel_format_id', 'id'=>'prime_excel_format_id', 'type'=>'Hidden','value'=>0));
				?>
				<?php if($prime_module_id === "employees"){
						echo '<div class="form-group">';
								$imp_list = array(""=>"---- Select one ----",1=>"New",2=>"Amendment",3=>"Rowset");
								echo form_label("Import Type", 'import_type', array('class' => 'required'))."<br/>";
								echo form_dropdown(array('name' => 'import_type','id' =>'import_type','class' => 'form-control input-sm select2'), $imp_list);
						echo '</div>';
					}
				?>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('excel_name'), 'excel_name', array('class' => 'required'));
						echo form_input(array( 'name' => 'excel_name', 'id' => 'excel_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('excel_name'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('excel_table_name'), 'excel_table_name', array('class' => 'required'))."<br/>";
						echo form_dropdown(array('name' => 'excel_table_name[]','multiple id' =>'excel_table_name','class' => 'form-control input-sm select2'), $table_list,$table_mand_list);
					?>
				</div>		
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('excel_column_name'), 'excel_column_name', array('class' => 'required'))."<br/>";
						echo form_dropdown(array('name' => 'excel_column_name[]','multiple id' =>'excel_column_name','class' => 'form-control input-sm select2'), $column_list,$mandatory_list);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('exist_column_name'), 'exist_column_name', array('class' => 'required'))."<br/>";
						echo form_dropdown(array('name' => 'exist_column_name[]','multiple id' =>'exist_column_name','class' => 'form-control input-sm select2'), $column_list);
					?>
				</div>
				<div class="form-group">
					<button class='btn btn-primary btn-sm' id="excel_format_submit">Add/Update</button>
					<a class='btn btn-danger btn-sm' id="excel_format_cancel">Cancel</a>
				</div>
			</fieldset>
		<?php echo form_close(); ?>
		<div style="padding:15px;" id="excel_view_data">
			<?php 
				echo $excel_content;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="excel_mapping">
		<?php echo form_open('utilities_settings/format_mapping/' . $prime_module_id,array('id'=>'format_mapping','class'=>'form-inline','style'=>'background-color: #f2f2f2; padding: 8px 0px;')); ?>
				<?php
					echo form_input( array('name'=>'prime_excel_format_line_id', 'id'=>'prime_excel_format_line_id', 'type'=>'Hidden','value'=>0));
				?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('excel_format'), 'excel_format', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'excel_format', 'id' => 'excel_format', 'class' => 'form-control input-sm'), $excel_format_list);
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="format_mapping_submit" style='margin-top:20px;'>View</button>
			</div>
		<?php echo form_close(); ?>
		
		<?php echo form_open('utilities_settings/save_map/' . $prime_module_id, array('id'=>'save_map','class'=>'form-inline','style'=>'padding: 8px 0px;'));?>
			<!-- LOAD MAPPING FORM FROM CONTROLLER  -->
		<?php echo form_close(); ?>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	call_select();
	$.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 ]*$/i.test(value);
    }, "Must contain only letters and numbers");
	$.validator.addMethod("space_check", function(value, element) {
        return this.optional(element) || /^(\w+\s?)*\s*$/i.test(value);
    }, "Must contain single space");
	
	/* EXCEL FORMAT FORM VALIDATION - START */	
	$('#excel_format_form').validate($.extend({
		submitHandler: function (form){
			$("#excel_format_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#excel_format_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#excel_format_submit').attr('disabled',false);
					$("#excel_format_submit").html("Submit");
					if(response.success){
						console.log(response.excel_format_list);
						$('#excel_view_data').html(response.excel_content);
						$('.nav-tabs a[href="#excel_mapping"]').tab('show');	
						$('#excel_format').html(response.excel_format_list);
						toastr.success(response.msg);
					}
					$("#excel_name").val("");
					$("#prime_excel_format_id").val(0);
					$("#import_type").val("");
					$("#exist_column_name").val("");
					$('#exist_column_name option:selected').removeAttr('selected');
					call_select();
				},
				dataType: 'json'
			});
		},
		rules:{
			excel_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			<?php
				if($prime_module_id === "employees"){
					echo 'import_type: "required",';
				}
			?>
			"excel_table_name[]": "required",
			"excel_column_name[]": "required",
			"exist_column_name[]": "required"
		}
	}));
	
	/* EXCEL FORMAT FORM CANCEL CLEAR  - START */	
	$("#excel_format_cancel").click(function(){
		excel_module_id = $('#excel_module_id').val();
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/cancel_value"); ?>',
			data: {excel_module_id:excel_module_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$('#excel_name').val("");
				$('#excel_column_name').val("");
				$('#excel_column_name option:selected').removeAttr('selected');	
				for(i = 0; i < rslt.col_mandatory_list.length; i++) {
					value   = rslt.col_mandatory_list[i];
					$("#excel_column_name").find("option[value='"+value+"']").prop("selected", "selected");
				}
				$('#excel_table_name').val("");
				$('#excel_table_name option:selected').removeAttr('selected');
				for(i = 0; i < rslt.table_mand_list.length; i++) {
					value   = rslt.table_mand_list[i];
					$("#excel_table_name").find("option[value='"+value+"']").prop("selected", "selected");
				}
				//$('#excel_row_start').val(1);
				$('#exist_column_name').val("");
				$('#exist_column_name option:selected').removeAttr('selected');	
				$('#import_type').val("");
				call_select();
			},
		});
		
	});
	/* EXCEL FORMAT FORM CANCEL CLEAR  - END */
	
	
	/* EXCEL FORMAT FORM VALIDATION - END */
	
	/*FORMAT MAPPING FORM VALIDATE DATA -START*/
	$('#format_mapping').validate($.extend({
		submitHandler: function (form){
			$("#format_mapping_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#format_mapping_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#format_mapping_submit').attr('disabled',false);
					$("#format_mapping_submit").html("View");
					$("#save_map").html(response.mapping_form_details);
				},
				dataType: 'json'
			});
		},
		rules:{
			excel_format: "required",
		}
	}));	
	/*FORMAT MAPPING FORM VALIDATE DATA - END*/
	
	/*SAVE MAP FORM VALIDATE DATA - START	*/
	$('#save_map').validate({
		submitHandler:function(form) {
			var isValid = true;
			$("select[name='excel_line_value[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			if(isValid) {
				$("#save_map_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#save_map_submit').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){						
						$('#save_map_submit').attr('disabled',false);
						$("#save_map_submit").html("Submit");
						if(response.success){
							toastr.success(response.msg);
						}
						//$('.modal').modal('toggle');
						$('#notify_list_model').modal('hide');
					},
					dataType: 'json'
				});
			}else{
				toastr.error("Column Mapping Error!");
			}
		}
	}); 	
	/*SAVE MAP FORM VALIDATE DATA - END	*/
	//Mandatory Fields Do Not Remove by user
	$("#excel_column_name").change(function(){
		var mandatory_list = '<?php echo json_encode($mandatory_list); ?>';
		var obj = jQuery.parseJSON(mandatory_list);
		var column_name    = [];//array create
		var column_name    = $("#excel_column_name").val();
		var import_type    = $('#import_type').val();
		if(parseInt(import_type) === 1){
			$.each( obj, function(key,value){
				if(jQuery.inArray(value, column_name) === -1){
					$('#excel_column_name option[value="' + value +'"]').prop("selected", true);
				}
			});
		}else
		if((parseInt(import_type) === 2) || (parseInt(import_type) === 3)){//static field manually
			$('#excel_column_name option[value="' + obj[1] +'"]').prop("selected", true);//emp code updated
			$('#excel_column_name option[value="role"]').removeAttr("selected");//role-category updated
		}
	});
	$("#import_type").change(function(){
		var import_type = $('#import_type').val();
		var mandatory_list = '<?php echo json_encode($mandatory_list); ?>';
		var obj = jQuery.parseJSON(mandatory_list);
		var column_name    = [];//array create
		var column_name    = $("#excel_column_name").val();
		if((parseInt(import_type) === 2) || (parseInt(import_type) === 3)){
			$("#excel_column_name option:selected").removeAttr("selected");
			$('#excel_column_name option[value="' + obj[1] +'"]').prop("selected", true);
		}else{
			$.each( obj, function(key,value){
				if(jQuery.inArray(value, column_name) === -1){
					$('#excel_column_name option[value="' + value +'"]').prop("selected", true);
				}
			});
		}
		$('#excel_column_name').select2();
	});
});
	
// SELECT2 UI UPDATE
function call_select(){
	$(function(){
		$('.select2').select2();
		$('.select2-tags').select2({
			tags: true,
			tokenSeparators: [',']
		});
		$(".select2_user").select2({
			tags: true
		});
	});
}

/* UPDATE FUNCTION FOR EXCEL FORMAT - START*/
function get_excel_info(prime_excel_format_id){
	if(prime_excel_format_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_excel_info"); ?>',
			data: {prime_excel_format_id:prime_excel_format_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$("#prime_excel_format_id").val(rslt.excel_info.prime_excel_format_id);
				$("#import_type").val(rslt.excel_info.import_type);
				$("#excel_name").val(rslt.excel_info.excel_name);
				if(rslt.excel_info.table_name){
					$("#excel_table_name").val("");
					$('#excel_table_name option:selected').removeAttr('selected');	
					var excel_table_name = rslt.excel_info.excel_table_name.split(",");
					for(var i in excel_table_name) {
						var excel_table_name_val = excel_table_name[i];
						$("#excel_table_name").find("option[value='"+excel_table_name_val+"']").prop("selected", "selected");
					}
				}
				if(rslt.excel_info.excel_column_name){
					$("#excel_column_name").val("");
					$('#excel_column_name option:selected').removeAttr('selected');	
					var excel_column_name = rslt.excel_info.excel_column_name.split(",");
					for(var i in excel_column_name) {
						var excel_column_name_val = excel_column_name[i];
						$("#excel_column_name").find("option[value='"+excel_column_name_val+"']").prop("selected", "selected");
					}
				}
				//$("#excel_row_start").val(rslt.excel_info.excel_row_start);
				if(rslt.excel_info.exist_column_name){
					$("#exist_column_name").val("");
					$('#exist_column_name option:selected').removeAttr('selected');	
					var exist_column_name = rslt.excel_info.exist_column_name.split(",");
					for(var i in exist_column_name) {
						var exist_column_name_val = exist_column_name[i];
						$("#exist_column_name").find("option[value='"+exist_column_name_val+"']").prop("selected", "selected");
					}
				}
				call_select();
			},
		});
	}
}
/* UPDATE FUNCTION FOR EXCEL FORMAT - END*/

/* DELETE FUNCTION FOR EXCEL FORMAT */
function get_delete_info(prime_excel_format_id){
	var excel_module_id = $('#excel_module_id').val();
	if(confirm("Are you sure to delete!")){
		if(prime_excel_format_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_delete_info"); ?>',
				data: {prime_excel_format_id:prime_excel_format_id,excel_module_id:excel_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#excel_view_data').html(rslt.excel_content);
						$('#excel_format').html(rslt.excel_format_list);
						toastr.success(rslt.msg);
					}
					$("#excel_name").val("");
					$("#prime_excel_format_id").val(0);
				},
			});
		}
	}	
}
/* DELETE FUNCTION FOR EXCEL FORMAT - END*/

/* CHECK CELL VALUES ALREADY EXIT START */
function map_check(sel_val){
	var mapped_vals = $("select[name='excel_line_value[]']").map(function(){return $(this).val();}).get();
	var count = 0;
	$.each(mapped_vals,function(i){
		if(sel_val.value === mapped_vals[i]){
			count ++;
		}
	});
	if(count > 1){
		toastr.error("Cell value Already Exist");
		$(sel_val).val('');
	}
}
/* CHECK CELL VALUES ALREADY EXIT END */
</script>