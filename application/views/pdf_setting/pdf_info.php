<fieldset id="FundBasicInfo" style="margin:0px;padding:8px;background-color:#f2f2f2;">
	<div class="tab-content">
	<?php echo form_open('pdf_setting/save_payroll_map/',array('id'=>'save_payroll_map','class'=>'form-inline')); ?>
		<div class="form-inline">
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_pdf_map_id', 'id'=>'prime_pdf_map_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'pdf_module_id', 'id'=>'pdf_module_id', 'type'=>'Hidden','value'=>$pdf_info_module_id));
					echo form_label($this->lang->line('pdf_block_for'), 'print_block_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'pdf_block_for','id' =>'pdf_block_for','class' => 'form-control input-sm select2'), $print_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pdf_info_name'), 'pdf_info_name', array('class' => 'required'));
					echo form_dropdown(array('name' => 'pdf_info_name','id' =>'pdf_info_name','class' => 'form-control input-sm select2'), $table_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pdf_sheet_per_page'), 'pdf_sheet_per_page', array('class' => 'required'));
					$pdf_sheet_per_page = array(""=>"--- Select Sheets Per Page ---","1"=>"One","2"=>"Two","3"=>"Three");
					echo form_dropdown(array('name' => 'pdf_sheet_per_page','id' =>'pdf_sheet_per_page','class' => 'form-control input-sm select2'), $pdf_sheet_per_page);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pdf_set_password'), 'pdf_set_password', array('class' => 'required'));
					$pdf_set_password = array(""=>"--- Select Set Password ---","1"=>"Yes","2"=>"No");
					echo form_dropdown(array('name' => 'pdf_set_password','id' =>'pdf_set_password','class' => 'form-control input-sm select2'), $pdf_set_password);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pdf_paper_size'), 'pdf_paper_size', array('class' => 'required'));
					$pdf_paper_size = array(""=>"--- Select Paper Size ---","A3"=>"A3","A4"=>"A4","A5"=>"A5");
					echo form_dropdown(array('name' => 'pdf_paper_size','id' =>'pdf_paper_size','class' => 'form-control input-sm select2'), $pdf_paper_size);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pdf_sheet_type'), 'pdf_sheet_type', array('class' => 'required'));
					$pdf_sheet_type = array(""=>"--- Select Sheet Type ---","portrait"=>"Portrait","landscape"=>"Landscape");
					echo form_dropdown(array('name' => 'pdf_sheet_type','id' =>'pdf_sheet_type','class' => 'form-control input-sm select2'), $pdf_sheet_type);
				?>
			</div>
			<div class="form-group"  style='margin-bottom:0px;'>
				<button class='btn btn-primary btn-sm' id="payroll_map_btn">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="payroll_map_cancel">Cancel</a>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</fieldset>
<div id='print_map_list' style="padding: 20px;">
	<?php echo $print_map_list;?>
</div>

<script type="text/javascript">
$(document).ready(function(){	
	call_select();
	$('.datepicker').datetimepicker({
		format:'MM-YYYY',
	});
	$.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 ]*$/i.test(value);
    }, "Must contain only letters and numbers");
	$.validator.addMethod("space_check", function(value, element) {
        return this.optional(element) || /^(\w+\s?)*\s*$/i.test(value);
    }, "Must contain single space");
	
	$("#pdf_block_for").change(function(){
		var pdf_block_for    = $('#pdf_block_for').val();
		var pdf_module_id    = $('#pdf_module_id').val();
		if(pdf_block_for && pdf_module_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/check_map_design"); ?>',
				data: {pdf_block_for:pdf_block_for,pdf_module_id:pdf_module_id},
				success: function(data){
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
						empty_all();
					}
				},
			});
		}
	});
	
	$("#pdf_info_name").change(function(){
		var pdf_block_for    = $('#pdf_block_for').val();
		var pdf_info_name    = $('#pdf_info_name').val();
		var prime_pdf_map_id = $('#prime_pdf_map_id').val();
		var pdf_module_id    = $('#pdf_module_id').val();
		if((pdf_block_for) && (pdf_info_name !== 0) && (prime_pdf_map_id == 0)){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/map_exit_design"); ?>',
				data: {pdf_block_for:pdf_block_for,pdf_info_name:pdf_info_name},
				success: function(data){
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});	
		}
	});
	
	$('#save_payroll_map').validate($.extend({
		submitHandler: function (form){
			$("#payroll_map_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#payroll_map_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#payroll_map_btn').attr('disabled',false);
					$("#payroll_map_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						$("#print_map_list").html(response.print_map_list);
					}else{
						toastr.error(response.message);
					}
					empty_all();
					
				},
				dataType: 'json'
			});
		},
		rules:{
			pdf_block_for: "required",
			pdf_info_name: "required",
			pdf_sheet_per_page: "required",
			pdf_set_password: "required",
			pdf_paper_size: "required",
			pdf_sheet_type: "required",
		}
	}));
	
	$("#payroll_map_cancel").click(function(){
		$("#pdf_block_for").val('');
		$("#pdf_info_name").val('');
		$("#pdf_sheet_per_page").val('');
		$("#pdf_set_password").val('');
		$("#pdf_sheet_type").val('');
		$("#pdf_paper_size").val('');
		$('#payroll_map_btn').attr('disabled',false);
		$("#payroll_map_btn").html("Add/Update");
		call_select();
	});
	
});

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


//EDIT OPERATION

function edit_print_map(prime_pdf_map_id){
	if(prime_pdf_map_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_print_map"); ?>',
			data: {prime_pdf_map_id:prime_pdf_map_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#prime_pdf_map_id").val(rslt.print_map_rslt.prime_pdf_map_id);
					$("#pdf_block_for").val(rslt.print_map_rslt.pdf_block_for);
					$("#pdf_info_name").val(rslt.print_map_rslt.pdf_info_name);
					$("#pdf_sheet_per_page").val(rslt.print_map_rslt.pdf_sheet_per_page);
					$("#pdf_set_password").val(rslt.print_map_rslt.pdf_set_password);
					$("#pdf_paper_size").val(rslt.print_map_rslt.pdf_paper_size);
					$("#pdf_sheet_type").val(rslt.print_map_rslt.pdf_sheet_type);
				}else{
					toastr.error(rslt.message);
				}
				call_select();
			},
		});
	}
}

function remove_print_map(prime_pdf_map_id){
	if(confirm("Are you sure to delete!")){
		if(prime_pdf_map_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_print_map"); ?>',
				data: {prime_pdf_map_id:prime_pdf_map_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#print_map_list").html(rslt.print_map_list);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}

function empty_all(){
	$("#pdf_block_for").val('');
	$("#pdf_info_name").val('');
	$("#pdf_sheet_per_page").val('');
	$("#pdf_set_password").val('');
	$("#pdf_sheet_type").val('');
	$("#pdf_paper_size").val('');
	call_select();
}
/* PDF BASE INFO END*/
</script>