<style>
	.sortable {list-style-type:none;margin:0;padding:0;width: auto;}	
	.sortable li{margin: 2px 20px 15px 0; padding: 8px; width: 100%; height: auto; font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px; cursor: pointer;display: inline-block;}
	.sortable_width li{width: 23% !important;}
</style>
<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#print_info">Print Info</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#print_block">Print Block</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#print_table">Print Table</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#print_split_up">Print split up</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#print_design">Print Design</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="print_info">
		<?php echo form_open('print_setting/save_print_info/' . $print_info_module_id,array('id'=>'save_print_info','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php			
					echo form_input( array('name'=>'prime_print_info_id', 'id'=>'prime_print_info_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'print_info_module_id', 'id'=>'print_info_module_id', 'type'=>'Hidden','value'=>$print_info_module_id));
					echo form_label($this->lang->line('print_info_name'), 'print_info_name', array('class' => 'required'));
					echo form_input(array('name'=> 'print_info_name', 'id' => 'print_info_name', 'class' => 'form-control input-sm', "placeholder"=>$this->lang->line('print_info_name'),'value' =>''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_info_for'), 'print_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_info_for[]','multiple id' =>'print_info_for','class' => 'form-control input-sm select2'), $print_for);
					echo "<label><input name='print_info_for_select' id='print_info_for_select' type='checkbox'> Select All</label>";
				?>
			</div>
			<div class="form-group">
				<?php
					$print_type_list = array(""=>"-- Select Print Type --",1=>"Payslip",2=>"Form M",3=>"Offer Letter",4=>"Terminated without salary",5=>"Terminated with salary",6=>"Abscond",7=>"Resigned");
					echo form_label("Print Type", 'print_type', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_type','id' =>'print_type','class' => 'form-control input-sm select2'), $print_type_list);
				?>
			</div>
			<div class="form-group">
				<?php
					$print_based_list = array(""=>"-- Select Print Based On --",1=>"Print",2=>"Email");
					echo form_label("Print Based On", 'print_based_on', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_based_on','id' =>'print_based_on','class' => 'form-control input-sm select2'), $print_based_list);
				?>
			</div>
			<div class="form-group"  style='margin-bottom:0px;'>
				<button class='btn btn-primary btn-sm' id="print_info_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="print_info_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style='padding:15px;' id='print_info_list'>
			<?php 
				print_r($print_info_list);
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="print_block">
		<?php echo form_open('print_setting/save_print_block/' . $print_info_module_id,array('id'=>'save_print_block','class'=>'form-inline' ,'style'=>'background-color: #f2f2f2;')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_print_block_id', 'id'=>'prime_print_block_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'print_block_module_id', 'id'=>'print_block_module_id', 'type'=>'Hidden','value'=>$print_info_module_id));
					echo form_label($this->lang->line('print_block_for'), 'print_block_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_block_for','id' =>'print_block_for','class' => 'form-control input-sm'), $print_block_for);
				?>
			</div> 
			<div class="form-group">
				<?php			
					echo form_label($this->lang->line('print_block_name'), 'print_block_name', array('class' => 'required'));
					echo form_input(array('name'=> 'print_block_name', 'id' => 'print_block_name', 'class' => 'form-control input-sm', "placeholder"=>$this->lang->line('print_block_name'),'value' =>''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_block_type'), 'print_block_type', array('class' => 'required'));
					$print_block_type_list = array("0"=>"--- Select Block Type ---","1"=>"Normal View","2"=>"List View",);
					echo form_dropdown(array('name' => 'print_block_type','id' =>'print_block_type','class' => 'form-control input-sm'), $print_block_type_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_block_table'), 'print_block_table', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_block_table[]','multiple id' =>'print_block_table','class' => 'form-control input-sm select2'), $table_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_block_column'), 'print_block_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_block_column[]','multiple id' =>'print_block_column','class' => 'form-control input-sm select2'));
				?>
			</div>
			<div class="form-group">
				<label>
					<input name='suppressed_data' id='suppressed_data' type='checkbox'>Suppressed Page</input>
				</label>
			</div>
			<div class="form-group">
				<label>
					<input name='cumulative_data' id='cumulative_data' type='checkbox'>Is Cumulative</input>
				</label>
			</div>
			<div class="form-group"  style='margin-bottom:0px;'>
				<button class='btn btn-primary btn-sm' id="print_block_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="print_block_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style='padding:15px;' id='print_block_list'>
			<?php 
				print_r($print_block_list);
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="print_table">
		<?php echo form_open('print_setting/get_print_table_info/' . $print_info_module_id,array('id'=>'get_print_table_info','class'=>'form-inline' ,'style'=>'background-color: #f2f2f2;')); ?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_table_list'), 'print_table_list', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_table_list','id' =>'print_table_list','class' => 'form-control input-sm'), $print_table_list);
				?>
			</div> 
			<div class="form-group"  style='margin-bottom:0px;'>
				<button class='btn btn-primary btn-sm' id="print_table_info_view">View</button>
			</div>
		<?php echo form_close(); ?>
		<div id="print_table_content">
			<ul class="nav nav-tabs" data-tabs="tabs">
				<li class="active" role="presentation">
					<a data-toggle="tab" href="#print_map_table">Map Table</a>
				</li>
				<li role="presentation">
					<a data-toggle="tab" href="#print_table_where">Map Where</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade in active" id="print_map_table" style='padding:15px;'>
					<?php echo form_open('print_setting/save_print_table/' . $prime_module_id,array('id'=>'save_print_table','class'=>'form-inline')); ?>
					<!-- LOAD CONTENT FROM CONTROLLER -->
					<?php echo form_close(); ?>
				</div>
				<div class="tab-pane fade" id="print_table_where" style='padding:15px;'>
					<?php echo form_open('print_setting/save_print_where/' . $prime_module_id,array('id'=>'save_print_where','class'=>'form-inline')); ?>
						<div class="form-group">
							<?php
								echo form_input( array('name'=>'where_for_id', 'id'=>'where_for_id', 'type'=>'Hidden','value'=>0));
								echo form_input( array('name'=>'where_module_id', 'id'=>'where_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
								echo form_input( array('name'=>'query_type', 'id'=>'query_type', 'type'=>'Hidden','value'=>''));
								echo form_label($this->lang->line('query_column_list'), 'pick_list', array('class' => 'required'));
								echo form_dropdown(array('name' => 'query_column_list','id' =>'query_column_list','class' => 'form-control input-sm'));
							?>
						</div>
						<div class="form-group">
							<?php
								echo form_label($this->lang->line('picklist_type'), 'picklist_type', array('class' => 'required'));
								$query_type_array = array(''=>"--- Select Get Value from ---","1"=>"Get From Picklist ","2"=>"Get From Session");
								echo form_dropdown(array('name' => 'picklist_type','id' =>'picklist_type','class' => 'form-control input-sm'), $query_type_array);
							?>
						</div>
						<div class="form-group">
							<?php
								echo form_label($this->lang->line('pick_list'), 'pick_list', array('class' => 'required'));
								echo form_dropdown(array('name' => 'pick_list[]','multiple id' =>'pick_list','class' => 'form-control input-sm select2'));
							?>
						</div>
						<div class="form-group">
							<?php
								echo form_label($this->lang->line('session_list'), 'session_list', array('class' => 'required'));
								echo form_dropdown(array('name' => 'session_list','id' =>'session_list','class' => 'form-control input-sm'));
							?>
						</div>
						<div class="form-group">
							<a class='btn btn-edit btn-sm' id="apply_condition">Apply to Condition</a>
						</div>
						<br/>
						<div style='padding: 15px 0px; background-color: #efefef;'>
							<div class="form-group" style="width:75% !important;margin-bottom: 0px !important;">
								<textarea name='where_condition' id='where_condition'class='form-control' rows='4'  placeholder='Write Condition with out where' >and</textarea>
							</div>
							<div class="form-group">
								<button class='btn btn-primary btn-sm' id="save_query_btn">Add/Update</button>
							</div>
						</div>
					<?php echo form_close(); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade" id="print_split_up">
		<form class="form-inline">
			<div class="form-group">
				<?php
					echo form_label("Split Information", 'split_table_info', array('class' => 'required'));
					echo form_dropdown(array('name' => 'split_table_info','id' =>'split_table_info','class' => 'form-control input-sm'), $print_block_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Select Info", 'split_info', array('class' => 'required'));
					$split_info = array(""=>"--- Select split_info ---",1=>"Loan Amount");
					echo form_dropdown(array('name' => 'split_info','id' =>'split_info','class' => 'form-control input-sm'),$split_info);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Select split column", 'split_colum', array('class' => 'required'));
					echo form_dropdown(array('name' => 'split_colum','id' =>'split_colum','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<a class='btn btn-primary btn-sm' id="split_save_btn">Add/Update</a>
				<a class='btn btn-danger btn-sm' id="split_cancel_btn">Cancel</a>
			</div>
		</form>
		<div style='padding:15px;' id='split_table_list'>
			<?php 
				print_r($split_table_list);
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="print_design">
		<form class="form-inline">
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_table_info'), 'assign_table_info', array('class' => 'required'));
					echo form_dropdown(array('name' => 'assign_table_info','id' =>'assign_table_info','class' => 'form-control input-sm'), $print_block_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_table_block'), 'assign_table_block', array('class' => 'required'));
					echo form_dropdown(array('name' => 'assign_table_block','id' =>'assign_table_block','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_type'), 'assign_type', array('class' => ''));					
					echo form_dropdown(array('name' => 'assign_type','id' =>'assign_type','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_label'), 'assign_label', array('class' => ''));
					echo form_dropdown(array('name' => 'assign_label','id' =>'assign_label','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_short_label'), 'assign_short_label', array('class' => ''));
					echo form_dropdown(array('name' => 'assign_short_label','id' =>'assign_short_label','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_value_for'), 'assign_value_for', array('class' => ''));
					echo form_dropdown(array('name' => 'assign_value_for','id' =>'assign_value_for','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('assign_ytd_label'), 'assign_ytd_label', array('class' => ''));
					echo form_dropdown(array('name' => 'assign_ytd_label','id' =>'assign_ytd_label','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('list_view_value'), 'list_view_value', array('class' => ''));
					echo form_dropdown(array('name' => 'list_view_value','id' =>'list_view_value','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group"> 
				<?php
					echo form_label($this->lang->line('assign_date_formate'), 'assign_date_formate', array('class' => ''));
					$assign_date_formate_list  = array(""=>"-- Select Format --","DMY"=>"(DD-MM-YYYY)","YMD"=>"(YYYY-MM-DD)","DFY"=>"(DD-FF-YYYY)","MY"=>"(MM-YYYY)","YM"=>"(YYYY-MM)","D"=>"(DD)","M"=>"(MM)","Y"=>"(YYYY)"); 
					echo form_dropdown(array('name' => 'assign_date_formate','id' =>'assign_date_formate','class' => 'form-control input-sm'),$assign_date_formate_list);
				?>
				<span style='font-size:11px;color:green;'>Select Date formate if mapping is date</span>
			</div>
			<div class="form-group">
				<a class='btn btn-primary btn-sm' id="assign_btn">Assign</a>
			</div>
		</form>
		<div id="froala-editor" style="padding:0px 15px;margin-bottom:15px;"> </div>
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
	
	jQuery.validator.addMethod("notEqual", function (value, element, param) { // ADDING RULES FOR AMOUNT(NOT EQUAL TO ZERO)
		return this.optional(element) || value != 'and';
	}, "Write Condition after and");
	
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	  var target = $(e.target).attr("href");
	  call_select();
	});
	$("#print_table_content").hide();
	/* PRINT BASE INFO START*/
	$("#print_info_for_select").click(function(){
		if($("#print_info_for_select").is(':checked') ){
			$("#print_info_for > option").prop("selected","selected");
			$("#print_info_for").trigger("change");
		}else{
			$("#print_info_for > option").removeAttr("selected");
			$("#print_info_for").trigger("change");
		}
	});
	var print_type_id = parseInt($('#print_type_id').val());
	if(print_type_id === 2){
		$("select[name='print_block_table[]']").attr('readonly','readonly');
	}
	
	$('#save_print_info').validate($.extend({
		submitHandler: function (form){
			$("#print_info_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#print_info_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#print_info_submit').attr('disabled',false);
					$("#print_info_submit").html("Add/Update");
					if(response.success){
						var print_type = parseInt($('#print_type').val());
						document.getElementById("save_print_info").reset();
						$("#prime_print_info_id").val(0);
						$('#print_info_for option:selected').removeAttr('selected');
						$("#print_info_list").html(response.print_info_list); 
						$('#print_block_for,#assign_table_info').empty();
						var option ="";
						$.each(response.print_block_for, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#print_block_for,#assign_table_info').append(option);
						/*$('#print_type_id').val(print_type);
						if(print_type === 2){
							$("#print_block_table").val(["cw_employees","cw_transactions"]);
							$("select[name='print_block_table[]']").attr('readonly','readonly');
						}*/
						call_select();
						toastr.success(response.message);
						activaTab('print_block');
					}else{
						toastr.error(response.message);
					}
					
				},
				dataType: 'json'
			});
		},
		rules:{
			print_block_for: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			"print_info_for[]": "required",
		}
	}));
	$("#print_info_cancel").click(function(){
		document.getElementById("save_print_info").reset();
		$("#prime_print_info_id").val(0);
		$('#print_info_for option:selected').removeAttr('selected');
		$('#print_info_submit').attr('disabled',false);
		$("#print_info_submit").html("Add/Update");
		call_select();
	});
	/* PRINT BASE INFO END*/
	
	/* PRINT BLOCK INFO START*/
	$("#print_block_table").change(function(){
		var print_block_table = $('#print_block_table').val();
		if(print_block_table){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_print_block_table"); ?>',
				data: {print_block_table:print_block_table},
				success: function(data) {
					var rslt = JSON.parse(data);
					var print_block_column = $('#print_block_column').val();
					if((print_block_column == null) || (print_block_column == "NULL")){
						$('#print_block_column option:selected').removeAttr('selected');
					}
					call_select();		
					if(rslt.success){
						if(rslt.print_block_column){
							/*if((print_block_column == null) || (print_block_column == "NULL")){
								$('#print_block_column').empty();
							}*/
							$('#print_block_column').empty();
							var option = "";
							$.each(rslt.print_block_column, function (index, value) {
								option += '<option value="' + index + '">' + value + '</option>';
							});
							$('#print_block_column').html(option);
						}
						call_select();
					}else{
						toastr.error(rslt.message);
					}
				},
			});
			
		}		
	});
	
	$('#save_print_block').validate($.extend({
		submitHandler: function (form){
			$("#print_block_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#print_block_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#print_block_submit').attr('disabled',false);
					$("#print_block_submit").html("Add/Update");
					if(response.success){
						$('#print_block_table option:selected').removeAttr('selected');
						$('#print_block_column option:selected').removeAttr('selected');
						document.getElementById("save_print_block").reset();
						$("#prime_print_block_id").val(0);
						call_select();
						$("#print_block_list").html(response.print_block_list); 
						$('#print_table_list,#assign_table_block').empty();
						var option ="";
						$.each(response.print_table_list, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#print_table_list,#assign_table_block').append(option);
						toastr.success(response.message);
						activaTab('print_table');
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			print_block_for: {
				required: true,
				min:1,
			},
			print_block_type: {
				required: true,
				min:1,
			},
			print_block_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			"print_block_table[]": "required",
			"print_block_column[]": "required",
		}
	}));
	$("#print_block_cancel").click(function(){
		document.getElementById("save_print_block").reset();
		$("#prime_print_block_id").val(0);
		$('#print_block_submit').attr('disabled',false);
		$("#print_block_submit").html("Add/Update");
		call_select();
	});
	/* PRINT BLOCK INFO END*/
	
	/* PRINT TABLE INFO START*/
	$('#get_print_table_info').validate($.extend({
		submitHandler: function (form){
			$("#print_table_info_view").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#print_table_info_view').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#print_table_info_view').attr('disabled',false);
					$("#print_table_info_view").html("View");
					if(response.success){
						$("#save_print_table").html(response.print_table_block); 
						$('#query_column_list').empty();
						var option ="";
						$.each(response.column_list, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#query_column_list').append(option);
						$("#where_for_id").val(response.prime_print_block_id); 
						$("#where_module_id").val(response.print_block_module_id); 						
						$("#where_condition").val(response.where_condition);
						$("#print_table_content").show();
						
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			print_table_list: {
				required: true,
				min:1,
			},
		}
	}));
	$('#save_print_table').validate({
		submitHandler:function(form) {
			var isValid = true;
			$("select[name='line_prime_table[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_prime_col[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_join_type[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_join_table[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_join_col[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			if(isValid) {
				$("#save_print_table_save").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#save_print_table_save').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){
						$('#save_print_table_save').attr('disabled',false);
						$("#save_print_table_save").html("Save");
						toastr.success(response.message);
					},
					dataType: 'json'
				});
			}else{
				toastr.error("Map all table join");
			}
		}
	});
	/* PRINT TABLE INFO END*/
	/* PRINT WHERE INFO START*/
	$('#save_print_where').validate($.extend({
		submitHandler: function (form){
			$("#save_query_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_query_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_query_btn').attr('disabled',false);
					$("#save_query_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						//document.getElementById("save_print_where").reset();
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			where_condition: {
				required:true,
				/*notEqual: true,*/
			}
		}
	}));
	/*
	$('#where_condition').bind('keyup blur change', function(e) {
		where_condition = $("#where_condition").val();
		if(where_condition === ""){
			$("#where_condition").val("and");
		}
	});
	*/
	
	$('#picklist_type,#pick_list,#session_list').parent().hide();
	$("#query_column_list").change(function(){
		query_column    = $("#query_column_list").val();
		where_module_id = $("#where_module_id").val();
		if(query_column){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_column_info"); ?>',
				data: {query_column:query_column,where_module_id:where_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#pick_list').empty();
					$('#session_list').empty();
					$('#picklist_type,#pick_list,#session_list').parent().hide();
					if(rslt.success){
						if(rslt.type === "pick_list"){
							var pick_option ="";
							$.each(rslt.pick_list, function( key, value ) {
							  pick_option += '<option value="' + key + '">' + value + '</option>';
							});
							$('#pick_list').append(pick_option);
							
							var session_option ="";
							$.each(rslt.session_list, function( key, value ) {
							  session_option += '<option value="' + key + '">' + value + '</option>';
							});
							$('#session_list').append(session_option);
							$('#picklist_type').parent().show();
						}else
						if(rslt.type === "session_list"){
							var option ="";
							$.each(rslt.session_list, function( key, value ) {
							  option += '<option value="' + key + '">' + value + '</option>';
							});
							$('#session_list').append(option);
							$('#session_list').parent().show();
						}					
						$("#query_type").val(rslt.type);
					}else{
						toastr.error(rslt.msg);
					}
				},
			});
		}
	});
	$("#picklist_type").change(function(){
		$('#pick_list,#session_list').parent().hide();
		picklist_type = $("#picklist_type").val();
		if(picklist_type === "1"){
			$('#pick_list').parent().show();
		}else
		if(picklist_type === "2"){
			$('#session_list').parent().show();
		}
		call_select()
	});
	$("#apply_condition").click(function(){
		query_column = $("#query_column_list").val();
		query_type   = $("#query_type").val();
		var fill_val = "";
		if(query_column){
			if(query_type === "pick_list"){
				picklist_type = $("#picklist_type").val();
				if(picklist_type === "1"){
					sub_value     = $("#pick_list").val();
					if(sub_value){
						fill_val = query_column + " in(^"+sub_value+"^) ";
					}else{
						toastr.error("Please select pick list value");
						return false;
					}
				}else
				if(picklist_type === "2"){
					sub_value = $("#session_list").val();
					sub_value = sub_value.split('|');
					sub_value = sub_value[1];
					if(sub_value){
						fill_val = query_column + " in(^@"+sub_value+"@^)";
					}else{
						toastr.error("Please select session value");
						return false;
					}
				}
			}else
			if(query_type === "session_list"){
				sub_value = $("#session_list").val();
				sub_value = sub_value.split('|');
				sub_value = sub_value[1];
				if(sub_value){
					fill_val = query_column + " = ^@"+sub_value+"@^";
				}else{
					toastr.error("Please select session value");
					return false;
				}
			}else{
				toastr.error("Invalid column");
				return false;
			}
		}else{
			toastr.error("Please select column value");
			return false;
		}
		if(fill_val){
			where_condition = $("#where_condition").val();
			fill_val = where_condition +" "+fill_val;
			$("#where_condition").val(fill_val);
		}
	});
	/* PRINT WHERE INFO END*/
	
	/* PRINT DESIGN START*/
	$("#assign_table_block,#assign_type,#assign_label,#assign_short_label,#assign_value_for,#list_view_value,#assign_date_formate,#assign_btn,#assign_ytd_label").parent().hide();
	$("#assign_table_info").change(function(){
		assign_table_info = $("#assign_table_info").val();
		$("#assign_table_block,#assign_type,#assign_label,#assign_short_label,#assign_value_for,#list_view_value,#assign_date_formate,#assign_btn,#assign_ytd_label").parent().hide();
		if(assign_table_info){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/assign_table_info"); ?>',
				data: {assign_table_info:assign_table_info},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#assign_table_block,#list_view_value').empty();
						var option ="";
						$.each(rslt.assign_table_block, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_table_block').append(option);
						var option ="";
						$.each(rslt.list_view_value, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#list_view_value').append(option);
						$("#assign_table_block").parent().show();
						// PAGE BUILDER WITH DATA IF EXIST.
						call_print_builder(rslt.print_design);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}else{
			toastr.error("Please select print info");
		}		
	});
	$("#assign_table_block").change(function(){
		assign_table_block = $("#assign_table_block").val();
		$("#assign_type,#assign_label,#assign_short_label,#assign_value_for,#list_view_value,#assign_date_formate,#assign_btn,#assign_ytd_label").parent().hide();
		if(assign_table_block){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/assign_table_block"); ?>',
				data: {assign_table_block:assign_table_block},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#assign_label,#assign_short_label,#assign_value_for,#assign_type,#assign_ytd_label').empty();
						var option ="";
						$.each(rslt.assign_type, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_type').append(option);						
						var option ="";
						$.each(rslt.assign_label, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_label').append(option);						
						var option ="";
						$.each(rslt.assign_short_label, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_short_label').append(option);
						var option ="";
						$.each(rslt.assign_ytd_label, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_ytd_label').append(option);
						var option ="";
						$.each(rslt.assign_value_for, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#assign_value_for').append(option);	
						$("#assign_type").parent().show();
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}else{
			toastr.error("Please select print block");
		}
	});	
	$("#assign_type").change(function(){
		assign_type = $("#assign_type").val();
		$("#assign_label,#assign_short_label,#assign_value_for,#list_view_value,#assign_date_formate,#assign_btn,#assign_ytd_label").parent().hide();
		if(assign_type === "1"){
			$("#assign_label,#assign_btn").parent().show();
		}else
		if(assign_type === "2"){
			$("#assign_short_label,#assign_btn").parent().show();
		}else
		if(assign_type === "3"){
			$("#assign_value_for,#assign_btn").parent().show();
		}else
		if(assign_type === "4"){
			$("#assign_ytd_label,#assign_btn").parent().show();
		}else
		if(assign_type === "5"){
			$("#list_view_value,#assign_btn").parent().show();
		}
	});
	//assign_value_for assign_date_formate
	$("#assign_value_for").change(function(){
		assign_value_for = $("#assign_value_for").val();		
		if(assign_value_for){
			$("#assign_date_formate").parent().show();
		}else{
			$("#assign_date_formate").parent().hide();
			$("#assign_date_formate").val("");
		}
	});
	$("#save_print_design").click(function(){
		$('#froala-editor').froalaEditor('save.save');
	});
	$("#split_table_info").change(function(){
		split_table_info = $("#split_table_info").val();
		if(split_table_info){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/split_table_info"); ?>',
				data: {split_table_info:split_table_info},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#split_colum').empty();
						var option ="";
						$.each(rslt.split_colum, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#split_colum').append(option);
					}
				},
			});
		}else{
			toastr.error("Please select print block");
		}
	});	
	$("#split_save_btn").click(function(){
		var split_table_info = $("#split_table_info").val();
		var split_info       = $("#split_info").val();
		var split_colum      = $("#split_colum").val();		
		if((split_table_info === "") || (split_info === "") || (split_colum === "")){
			toastr.error("Please select all required input");
		}else{
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/split_save"); ?>',
				data: {split_table_info:split_table_info,split_info:split_info,split_colum:split_colum},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#split_table_list').html(rslt.table_info);
					}
					$("#split_table_info").val(0);
					$("#split_info").val("");
					$("#split_colum").val("");
				},
			});
		}
	});	
	
	$("#split_cancel_btn").click(function(){
		$("#split_table_info").val(0);
		$("#split_info").val('');
		$("#split_colum").val('');
		$('#split_save_btn').attr('disabled',false);
		$("#split_save_btn").html("Add/Update");
		call_select();
	});
	/* PRINT DESIGN INFO END*/
	$("#print_block_for").change(function(){
		get_block_table();
	});
	
	//HIDE SHOW FOR PRINT BASED ON(OFFER LETTER)
	print_based_on();
	$('#print_type').on('change',function(){
		print_based_on();
	});
});

function print_based_on(){
	$('#print_based_on').parent().hide();
	var print_type = $('#print_type').val();	
	if(parseInt(print_type) === 3){
		$('#print_based_on').parent().show();
	}else{
		$('#print_based_on').parent().hide();
	} 
} 

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

function call_print_builder(assign_table_info){
	$('div#froala-editor').froalaEditor('destroy');
	$('div#froala-editor').html("");
	if(assign_table_info){
		$('div#froala-editor').html(assign_table_info);
	}	
	$(function(){		
		$('div#froala-editor').on('froalaEditor.initialized', function (e, editor){
			editor.events.bindClick($('body'), '#assign_btn', function () {
				assign_type = $("#assign_type").val();
				var assign_value = "";
				if(assign_type === "1"){
					assign_value = $("#assign_label").val();
				}else
				if(assign_type === "2"){
					assign_value = $("#assign_short_label").val();
				}else
				if(assign_type === "3"){
					assign_value = $("#assign_value_for").val();
					date_formte  = $("#assign_date_formate").val();
					if(date_formte){
						assign_value = assign_value.replace(/\@/g,"");
						assign_value = "@"+date_formte+"_"+assign_value+"_"+date_formte+"@";
					}
				}else
				if(assign_type === "4"){
					assign_value = $("#assign_ytd_label").val();
				}else
				if(assign_type === "5"){
					assign_value = $("#list_view_value").val();
				}
				editor.html.insert(assign_value);
				editor.undo.saveStep();
			});
		}).froalaEditor({
			toolbarButtons: ['fullscreen', '|','bold', '|','fontFamily', '|','fontSize', '|','color', '|','align','|','insertTable', '|','insertImage', '|','insertHR', '|', 'print', '|', 'html', '|', 'undo','|', 'redo'],
			//pluginsEnabled: ['image', 'link', 'draggable'],
			saveInterval: 500,
			heightMin: 150,
			heightMax: 300,
			imageUploadURL: './upload_image.php',
			imageMaxSize: 5 * 1024 * 1024,
			imageAllowedTypes: ['jpeg', 'jpg', 'png'],
			saveParam: 'content',
			saveURL: '<?php echo site_url($controller_name . "/save_print_design"); ?>',
			saveMethod: 'POST',
		}).on('froalaEditor.save.before', function (e, editor){
			//ACTION BEFORE SAVE
			assign_table_info = $("#assign_table_info").val();
			if(assign_table_info === "0"){
				toastr.error("Please select Print info");
				return false;
			}
			var newOpts = {saveParams: {assign_table_info: assign_table_info}}
			$.extend(editor.opts, newOpts)
		}).on('froalaEditor.save.after', function (e, editor, response) {
			//ACTION AFETR SAVE
			var rslt = JSON.parse(response);
			//toastr.remove();
			toastr.success(rslt.message);
		}).on('froalaEditor.save.error', function (e, editor, error) {
			//SAVE ERROR
			toastr.error("Something went wrong please retry");
		});
	});
}

/* PRINT BASE INFO START*/
// EDIT SPLIT INFO
function edit_split_info(prime_print_split_id){
	if(prime_print_split_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_split_info"); ?>',
			data: {prime_print_split_id:prime_print_split_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$('#split_colum').empty();
					var option ="";
					$.each(rslt.split_column_rslt, function (index, value) {
						option += '<option value="' + index + '">' + value + '</option>';
					});
					$('#split_colum').append(option);
					$.each(rslt.split_rslt, function (index, value){
						$("#"+index).val(value);
					});
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
// REMOVE PRINT INFO
function remove_split_info(prime_print_split_id){
	if(confirm("Are you sure to delete!")){
		if(prime_print_split_id){
			print_info_module_id = $("#print_info_module_id").val();
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_split_info"); ?>',
				data: {prime_print_split_id:prime_print_split_id,print_info_module_id:print_info_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#split_table_list").html(rslt.table_info);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}
// EDIT PRINT INFO
function edit_print_info(prime_print_info_id){
	if(prime_print_info_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_print_info"); ?>',
			data: {prime_print_info_id:prime_print_info_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$('#print_for option:selected').removeAttr('selected');
				call_select();
				if(rslt.success){
					$("#prime_print_info_id").val(rslt.print_info.prime_print_info_id);
					$("#print_info_name").val(rslt.print_info.print_info_name);
					$("#print_type").val(rslt.print_info.print_type);
					$("#print_based_on").val(rslt.print_info.print_based_on);
					if(rslt.print_info.print_info_for){
						var print_info_for = rslt.print_info.print_info_for.split(",");
						for(var i in print_info_for) {
							var print_info_for_val = print_info_for[i];
							$("#print_info_for").find("option[value='"+print_info_for_val+"']").prop("selected", "selected");
							print_based_on();
						}
					}					
					call_select();
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
// REMOVE PRINT INFO
function remove_print_info(prime_print_info_id){
	if(confirm("Are you sure to delete!")){
		if(prime_print_info_id){
			print_info_module_id = $("#print_info_module_id").val();
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_print_info"); ?>',
				data: {prime_print_info_id:prime_print_info_id,print_info_module_id:print_info_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#print_info_list").html(rslt.print_info_list);
						$('#print_block_for,#assign_table_info').empty();
						var option ="";
						$.each(rslt.print_block_for, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#print_block_for,#assign_table_info').append(option);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}
/* PRINT BASE INFO END*/

/* PRINT BASE INFO START*/
// EDIT PRINT INFO
function edit_print_block(prime_print_block_id){
	if(prime_print_block_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_print_block"); ?>',
			data: {prime_print_block_id:prime_print_block_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$('#print_block_table option:selected').removeAttr('selected');
				call_select();
				if(rslt.success){
					if(rslt.print_block_column){
						$('#print_block_column').empty();
						var option = "";
						$.each(rslt.print_block_column, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#print_block_column').append(option);
					}						
					$("#prime_print_block_id").val(rslt.print_info.prime_print_block_id);
					$("#print_block_name").val(rslt.print_info.print_block_name);
					$("#print_block_for").val(rslt.print_info.print_block_for);
					$("#assign_table_info").val(rslt.print_info.print_block_for);
					$("#print_block_type").val(rslt.print_info.print_block_type);
					if(rslt.print_info.suppressed_data == 1){
						$('#suppressed_data').prop('checked', true);
					}
					if(rslt.print_info.cumulative_data == 1){
						$('#cumulative_data').prop('checked', true);
					}
					if(rslt.print_info.print_block_table){
						var print_block_table = rslt.print_info.print_block_table.split(",");
						for(var i in print_block_table) {
							var print_block_table_val = print_block_table[i];
							$("#print_block_table").find("option[value='"+print_block_table_val+"']").prop("selected", "selected");
						}
					}
					if(rslt.print_info.print_block_column){
						var print_block_column = rslt.print_info.print_block_column.split(",");
						for(var i in print_block_column) {
							var print_block_column_val = print_block_column[i];
							$("#print_block_column").find("option[value='"+print_block_column_val+"']").prop("selected", "selected");
						}
					}					
					call_select();
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
// REMOVE PRINT INFO
function remove_print_block(prime_print_block_id){
	if(confirm("Are you sure to delete!")){
		if(prime_print_block_id){
			print_block_module_id = $("#print_block_module_id").val();
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_print_block"); ?>',
				data: {prime_print_block_id:prime_print_block_id,print_block_module_id:print_block_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#print_block_list").html(rslt.print_block_list);
						$('#print_table_list,#assign_table_block').empty();
						var option ="";
						$.each(rslt.print_table_list, function (index, value) {
							option += '<option value="' + index + '">' + value + '</option>';
						});
						$('#print_table_list,#assign_table_block').append(option);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}
/* PRINT BASE INFO END*/

//Next Tab to Display
function activaTab(tab){
  $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

//
function get_block_table(){
	var print_block_for = parseInt($('#print_block_for').val());
	if(print_block_for > 0){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_block_table"); ?>',
			data: {print_block_for:print_block_for},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					if(parseInt(rslt.print_type) === 2){
						$("#print_block_table").val(["cw_employees","cw_transactions"]);
						$("select[name='print_block_table[]']").attr('readonly','readonly');
						if(rslt.print_block_column){
							/*if((print_block_column == null) || (print_block_column == "NULL")){
								$('#print_block_column').empty();
							}*/
							$('#print_block_column').empty();
							var option = "";
							$.each(rslt.print_block_column, function (index, value) {
								option += '<option value="' + index + '">' + value + '</option>';
							});
							$('#print_block_column').html(option);
						}
					}else{
						$("#print_block_table").val('');
						$("select[name='print_block_table[]']").attr('readonly',false);
					}
					call_select();
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
</script>
<style>
select[readonly].select2 + .select2-container {
  pointer-events: none;
  touch-action: none;
}
</style>
