<script src="dist/jquery.progressBarTimer.js" type="text/javascript" charset="utf-8"></script>
<?php  echo form_open("$controller_name/save_import/",array("id"=>'save_import',"class"=>"form-inline")); ?>
	<fieldset id='FundBasicInfo' style='margin:0px;padding:8px;'>
	
		<div class="form-group">
			<?php
				echo form_input( array('name'=>'module_id', 'id'=>'module_id', 'type'=>'Hidden','value'=>$module_id));
				$imp_list = array(""=>"---- Select one ----",1=>"New",2=>"Amendment",3=>"Rowset");
				echo form_label("Import Type", 'import_type', array('class' => 'required')); 
				echo form_dropdown(array('name' => 'import_type','id' =>'import_type','class' => 'form-control input-sm select2'), $imp_list);
			?>
		</div>
		<div class="form-group">
			<?php
				/*echo form_input( array('name'=>'module_id', 'id'=>'module_id', 'type'=>'Hidden','value'=>$module_id));*/
				echo form_label($this->lang->line('mod_excel_format'), 'excel_format', array('class' => 'required')); 
				echo form_dropdown(array( 'name' => 'excel_format', 'id' => 'excel_format', 'class' => 'form-control input-sm'));
			?>
		</div>
		<div class="form-group">
			<?php
				echo form_label($this->lang->line('excel_select_file'), 'excel_select_file', array('class' => 'required')); 
				echo form_upload(array('name' => 'excel_select_file','id' => 'excel_select_file','class' => 'form-control input-sm','value' =>'','accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel,text/comma-separated-values, text/csv, application/csv' ));
			?>
			<a id="link" style="display: none;" href="#" title='Export All Data'><span class="fa fa-user-exit">&nbsp</span> Export Mapping Format </a>
			<input type='hidden' id='excel_file_path' name='excel_file_path' value=''>
			<span id='loader' style='color:#CC3366'></span>
		</div>
		<div class="form-group">
			<?php
				echo form_label("Excel Sheet Name", 'excel_sheet_name', array('class' => 'required')); 
				echo form_dropdown(array( 'name' => 'excel_sheet_name', 'id' => 'excel_sheet_name', 'class' => 'form-control input-sm'), $excel_sheet_name);
			?>
		</div>
		<div class="form-group">
			<?php
				echo form_label("Excel Start Row", 'excel_start_row', array('class' => 'required')); 
				echo form_input(array( 'name' => 'excel_start_row', 'id' => 'excel_start_row', 'class' => 'form-control input-sm number', 'value' => '1'));
			?>
		</div>
		<div class="form-group">
			<?php
				echo form_label("Excel End Row", 'excel_end_row', array('class' => '')); 
				echo form_input(array( 'name' => 'excel_end_row', 'id' => 'excel_end_row', 'class' => 'form-control input-sm number', 'value' => ''));
			?>
		</div>
	</fieldset>
<?php echo form_close();?>
<div class="myProgress" style ="z-index: 10000; text-align: center;display:none;padding:50px;color:#4b6fa2;">
		<i class="fa fa-spinner fa-spin fa-2x fa-fw" ></i>Please wait processing....
	</div>
<hr class="left"/>
<p style="color:blue;margin-left:10px;">Please map the date format like this (DD-MM-YYYY) only...</p>
<div id='imp_table_info' style='padding:8px;overflow: auto;'>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#imp_loader').hide();
	var send_url = '<?php echo base_url("upload_files/upload.php?send_from=$controller_name&send_for=import");?>'
	$('#save_import').validate($.extend({
		submitHandler: function (form){
			$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#submit').attr('disabled','disabled');
			$('#imp_loader').show();
			$('.myProgress').show();
			$(form).ajaxSubmit({
				success: function (response){
					$('.myProgress').hide();
					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					if(response.success){
						toastr.success(response.message);
						$("#imp_table_info").html(response.table_info);
					}else{
					console.log(response.table_info);
						toastr.error(response.message);
						$("#imp_table_info").html(response.table_info);
						//$('.modal').modal('toggle');
					}
					$('#imp_loader').hide();
				},
				dataType: 'json'
			});
		},
		rules:{
			excel_format: "required",
			excel_select_file: "required",
			excel_sheet_name: "required",
			excel_start_row:{
				required: true,
				min:1,
				number:true,
			},
		}
	}));
	
	$('#excel_select_file').change(function() {
		$("#loader").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
		$('#submit').attr('disabled','disabled');
		var file_data = $('#excel_select_file').prop('files')[0];
		if(file_data){
			var form_data = new FormData();
			form_data.append('excel_select_file', file_data);
			$.ajax({
				url: send_url,
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(result_data){
					$("#loader").html("");
					$('#submit').attr('disabled',false);
					var rslt = JSON.parse(result_data);
					if(rslt['success']){
						$('#excel_file_path').val(rslt['path']);
						get_excel();
					}else{
						toastr.error(rslt['msg']);
					}
				}
			});
		}else{
			toastr.error('Please select file to upload');
			$("#loader").html("");
			$('#submit').attr('disabled',false);
		}
	});
	
	//End row checking with valid row end number
	$('#excel_end_row').change(function(){
		var start_row = $('#excel_start_row').val();
		var end_row   = $('#excel_end_row').val();
		if(parseInt(end_row) < parseInt(start_row)){
			toastr.error('End rows always higherthen or equal start rows?');
		}
	});
	
	$('#import_type').change(function(){
		var import_type = $('#import_type').val();
		var module_id   = $('#module_id').val();
		if(import_type){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_excel_template"); ?>',
				data: {module_id:module_id,import_type:import_type},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#excel_format').empty();
					var option = "";
					$.each(rslt.excel_format_drop, function( key, value ) {
						option += '<option value="' + key + '">' + value + '</option>';
					});
					$('#excel_format').append(option);
				}
			});
		}
	});
	
	$('#excel_format').change(function(){
		var module_id    = $('#module_id').val();
		var excel_format = $('#excel_format').val();
		var controller_name = '<?php echo $controller_name; ?>';
		if(excel_format){
			$('#link').show();
			$('#link').attr("href","index.php/<?php echo $controller_name; ?>/excel/"+module_id+"/"+excel_format);
		}else{
			$('#link').hide();
		}	
	});	
});

function get_excel(){
	file_path = $('#excel_file_path').val();
	var import_url = '<?php echo site_url("$controller_name/sheet_name");?>'
	if(file_path){
		$.ajax({
			type: 'POST',
			url: import_url,
			data:{file_path:file_path},
			success: function(data) {
				var rslt = JSON.parse(data);
				var option = "<option value=''>-- Select Sheet Name --</option>";
				for(i = 0; i < rslt.sheet_name.length; i++) {
					sheet_name = rslt.sheet_name[i];
					option += "<option value='"+i+"'>"+sheet_name+"</option>";
				}
				$("#excel_sheet_name").html(option);
			}
		});
	}
}
</script>
<style>
hr.left {
	text-align: left;
	margin-left:10px;
	width: 5%;
	border: 0.5px solid blue;
	margin-bottom: 5px;
}
</style>
