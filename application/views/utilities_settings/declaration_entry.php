<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#template_view">Template Name</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#template_mapping">Template Mapping</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="template_view">
		<?php  echo form_open("$controller_name/save_template/",array("id"=>'save_template',"class"=>"form-inline")); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'module_id', 'id'=>'module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_input( array('name'=>'temp_setting_id', 'id'=>'temp_setting_id', 'type'=>'Hidden','value'=>0));
					echo form_label("Template Name", 'template_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'template_name', 'id' => 'template_name', 'class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="template_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="template_cancel">Cancel</a>
			</div>
		<?php echo form_close();?>
		<div style="padding:15px;background-color: #f2f2f2;" id="template_list">
			<?php echo $template_content; ?>
		</div>
	</div>
	<div class="tab-pane fade" id="template_mapping">
	<?php  echo form_open("$controller_name/save_dec_entry/",array("id"=>'save_dec_entry',"class"=>"form-inline")); ?>
		<fieldset id='FundBasicInfo' style='margin:0px;padding:8px;'>
			<div class="form-group">
				<?php
					echo form_label("Template Name", 'temp_name', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'temp_name', 'id' => 'temp_name', 'class' => 'form-control input-sm'), $temp_name_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('category'), 'category', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'category', 'id' => 'category', 'class' => 'form-control input-sm'), $category_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Employee Code", 'employee_code', array('class' => 'required')); 
					echo form_dropdown(array( 'onchange = map_check(this); name' => 'employee_code', 'id' => 'excel_line_value[]', 'class' => 'form-control input-sm'), $excel_cell_value);
				?>
			</div>
			<div id="save_map" style="padding:15px;background-color: #f2f2f2;"></div>
			<div class="form-group" style="float:right">
				<button class='btn btn-primary btn-sm' id="dec_submit" style='margin-top:20px; '>Submit</button>
			</div>
		</fieldset>
	<?php echo form_close();?>
	<div id='table_info' style='padding:8px;overflow: auto;'>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#template_cancel").click(function(){
		$("#template_name").val("");
	});
	
	$('#save_template').validate($.extend({
		submitHandler: function (form){
			$("#template_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#template_submit').attr('disabled','disabled');
			
			$(form).ajaxSubmit({
				success: function (response){
					$('#template_submit').attr('disabled',false);
					$("#template_submit").html("Submit");
					if(response.success){
						$("#template_list").html(response.template_content);
						$('.nav-tabs a[href="#template_mapping"]').tab('show');	
						$('#temp_name').html(response.template_format);
					}else{
						toastr.error(response.msg);
					}
					$("#template_name").val("");
				},
				dataType: 'json'
			});
		},
		rules:{
			template_name : "required",
		}
	}));
	
	$('#category').change(function() {
		$.ajax({
			url: '<?php echo site_url("$controller_name/get_declation_entry");?>',
			type: 'post',
			success: function(data){
				$("#save_map").html(data);
			}
		});
	});
	
	$('#save_dec_entry').validate($.extend({
		submitHandler: function (form){
			$("#dec_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#dec_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#dec_submit').attr('disabled',false);
					$("#dec_submit").html("Submit");
					if(response.success){
						toastr.success(response.msg);
						$('.modal').modal('hide');
					}else{
						toastr.error(response.msg);
					}					
				},
				dataType: 'json'
			});
		},
		rules:{
			temp_name : "required",
			employee_code  : "required",
			category: "required",
			'excel_line_column_name[]': "required",
		}
	}));
	
	//cell validation counting to import
	$('#dec_submit').click(function(){
		var mapped_vals = $("select[id='excel_line_value[]']").map(function(){return $(this).val();}).get();
		var filtered = mapped_vals.filter(function(el) { return el; });
		if(parseInt(filtered.length) < 2){
			toastr.error("Atleast update one tax column");
			return false;
		}else{
			return true;
		}
	});
	
	//already template there edit the values
	$('#temp_name').change(function() {
		var temp_name = $('#temp_name').val();
		$.ajax({
			url: '<?php echo site_url("$controller_name/check_dec_template");?>',
			type: 'post',
			data: {temp_name:temp_name},
			success: function(data){
				var rslt = JSON.parse(data);
				var column_name_value = rslt.column_name_value
				if(rslt.success){
					toastr.success(rslt.msg);
					empty_all();
				}else{
					toastr.warning(rslt.msg);
					$("#category").val(rslt.template_list.category);
					get_column_values(column_name_value);
					$("select[name='employee_code']").val(rslt.template_list.employee_code);
				}
			}
		});
	});
});

/* CHECK CELL VALUES ALREADY EXIT END */
function get_template_edit_info(temp_setting_id){
	if(temp_setting_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_template_edit_info"); ?>',
			data: {temp_setting_id:temp_setting_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#temp_setting_id").val(rslt.template_result.prime_inc_temp_setting_id);
					$("#template_name").val(rslt.template_result.template_name);		
				}
			},
		});
	}
}

function get_template_delete_info(temp_setting_id,module_id){
	if(confirm("Are you sure to delete!")){
		if(temp_setting_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_template_delete_info"); ?>',
				data: {temp_setting_id:temp_setting_id,module_id:module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					$("#template_list").html(rslt.template_content);
					$("#temp_name").html(rslt.template_format);
				},
			});
		}
	}
}

/* CHECK CELL VALUES ALREADY EXIT START */
function map_check(sel_val){
	var mapped_vals = $("select[id='excel_line_value[]']").map(function(){return $(this).val();}).get();
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

//dynamic column updates
function get_column_values(column_name_value){
	$.ajax({
		url: '<?php echo site_url("$controller_name/get_declation_entry");?>',
		type: 'post',
		success: function(data){
			$("#save_map").html(data);
			for (name in column_name_value) {
				$("select[name='"+name+"']").val(column_name_value[name]);
			}
		}
	});
}

function empty_all(){
	$('#category').val("");
	$("select[name='employee_code']").val("");
	$("#save_map").html("");
}
</script>
<style>
.bootstrap-dialog-footer{
	display: none !important;
}
</style>