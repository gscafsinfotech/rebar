<?php  echo form_open("$controller_name/save_import/",array("id"=>'save_import',"class"=>"form-inline")); ?>
	<fieldset id='FundBasicInfo' style='margin:0px;padding:8px;'>
		<div class="form-group">
			<?php
				echo form_input( array('name'=>'module_id', 'id'=>'module_id', 'type'=>'Hidden','value'=>$module_id));
				echo form_label($this->lang->line('mod_excel_format'), 'excel_format', array('class' => 'required')); 
				echo form_dropdown(array( 'name' => 'excel_format', 'id' => 'excel_format', 'class' => 'form-control input-sm'), $excel_format_drop);
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

<div id='table_info' style='padding:8px;overflow: auto;'>
</div>
<script type="text/javascript">
$(document).ready(function(){
	var send_url = '<?php echo base_url("upload_files/upload.php?send_from=$controller_name&send_for=import");?>'
	$('#save_import').validate($.extend({
		submitHandler: function (form){
			$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					if(response.success){
						toastr.success(response.message);
						toastr.warning(response.warning);
						$("#table_info").html(response.table_info);
					}else{
						toastr.error(response.message);
						$('.modal').modal('toggle');
					}					
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