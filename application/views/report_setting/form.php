<style>
	.sortable {list-style-type:none;margin:0;padding:0;width: auto;}	
	.sortable li{margin: 2px 20px 15px 0; padding: 8px; width: 100%; height: auto; font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px; cursor: pointer;display: inline-block;}
	.sortable_width li{width: 23% !important;}
</style>
<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#report_base">Basic Information</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#add_column_view">Add Column View</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#total_sum_view">Total Column Sum</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#report_tab_view">Report Table View</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#report_tab_join">Report Table Join</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#report_tab_where">Report Table Where</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="report_base">	
		<?php echo form_open('report_setting/report_save/' . $view_id,array('id'=>'report_save','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_report_setting_id', 'id'=>'prime_report_setting_id', 'type'=>'Hidden','value'=>$report_data->prime_report_setting_id));
					echo form_label("Report Name", 'report_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'report_name', 'id' => 'report_name', 'class' => 'form-control input-sm', 'placeholder'=>"Report Name",'value' => $report_data->report_name));
				?>
			</div>
			<div class="form-group">
				<?php
					$report_for_val = "";
					if($report_data->report_for){
						$report_for_val = explode(",",$report_data->report_for);
					}
					echo form_label("Report For", 'report_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'report_for[]','multiple id' =>'report_for','class' => 'form-control input-sm select2'), $report_for_list,$report_for_val);
					echo "<label><input name='all_category_select' id='all_category_select' type='checkbox'> Select All</label>";
				?> 
			</div>
			<div class="form-group">
				<?php
					$table_info_val = "";
					if($report_data->table_info){
						$table_info_val = explode(",",$report_data->table_info);
					}
					echo form_label("Select Table", 'table_info', array('class' => 'required'));
					echo form_dropdown(array('name' => 'table_info[]','multiple id' =>'table_info','class' => 'form-control input-sm select2'), $table_list,$table_info_val);
				?>
			</div>
			<div class="form-group">
				<?php
					$table_column_val = "";
					if($report_data->table_column){
						$table_column_val = explode(",",$report_data->table_column);
					}
					echo form_label("Select Column", 'table_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'table_column[]','multiple id' =>'table_column','class' => 'form-control input-sm select2'),$columns_list,$table_column_val);
				?>
			</div>
			<div class="form-group">
				<?php
					$group_column_val = "";
					if($report_data->group_column){
						$group_column_val = explode(",",$report_data->group_column);
					}
					echo form_label("Group By", 'group_column', array('class' => ''));
					echo form_dropdown(array('name' => 'group_column[]','multiple id' =>'group_column','class' => 'form-control input-sm select2'),$columns_list,$group_column_val);
				?>
			</div>
			<div class="form-group">
				<?php 
					$sub_tot_show_val = $report_data->sub_tot_show;
				?>
				<input name='sub_tot_show' id='sub_tot_show' type="checkbox"> <b>Show Sub Total</b>
			</div>
			<div class="form-group">
				<?php
					$date_filter_val = $report_data->date_filter;
					$date_filter_list = array(""=>"-- Select Type--",1=>"Required",2=>"Not Required");
					echo form_label("Date Filter", 'date_filter', array('class' => 'required'));
					echo form_dropdown(array('name' => 'date_filter','id' =>'date_filter','class' => 'form-control input-sm'),$date_filter_list,$date_filter_val);
				?>
			</div>
			<div class="form-group">
				<?php
					$date_column_val = "";
					if($report_data->date_column){
						$date_column_val = explode(",",$report_data->date_column);
					}
					echo form_label("Select Date Column", 'date_column', array('class' => ''));
					echo form_dropdown(array('name' => 'date_column[]','multiple id' =>'date_column','class' => 'form-control input-sm select2'),$date_columns_list,$date_column_val);
				?>
			</div>
			<div class="form-group"  style='margin-bottom:10px;'>
				<button class='btn btn-primary btn-sm' id="report_base_submit">Submit</button>
			</div>
		<?php echo form_close(); ?>
	</div>
	<div class="tab-pane fade" id="add_column_view" style="padding-top:10px;">
		<?php echo form_open('report_setting/save_add_column/' . $view_id,array('id'=>'save_add_column','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'report_id', 'id'=>'report_id', 'type'=>'Hidden','value'=>$view_id));
					echo form_input( array('name'=>'add_column_id', 'id'=>'add_column_id', 'type'=>'Hidden','value'=>0));
					echo form_label("Add Name", 'add_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'add_name', 'id' => 'add_name', 'class' => 'form-control input-sm', 'placeholder'=>"Display Name",'value' => ""));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Select Add Column", 'add_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'add_column','id' =>'add_column','class' => 'form-control input-sm'),$columns_list);
				?>
			</div>
			<div class="form-group" style="display:none;">
				<?php 
					echo form_textarea( array('id'=>'hid_add_column_id', 'type'=>'Hidden','value'=>''));
				?>
			</div>
			<div class="form-group" style="width:40% !important;margin-bottom: 0px !important;">
				<textarea name='select_condition' id='select_condition'class='form-control' rows='4'  placeholder='Query Column'></textarea>
			</div>
			<div class="form-group"  style='margin-bottom:0px;'>
				<button class='btn btn-primary btn-sm' id="add_column_submit">Submit</button>
			</div>
		<?php echo form_close(); ?>	
		<!-- Additional Table Column Name View -->
		<div id='add_column_content' style="padding:10px; !important; overflow: auto;">
			<?php
				echo $add_column_content;
			?>
		</div>
	</div>
	<!-- Total Column Calculation Part -->
	<div class="tab-pane fade" id="total_sum_view">
		<?php
			echo form_open('report_setting/save_sum_column/'.$view_id,array('id'=>'save_sum_column','class'=>'form-inline'));
		?>
		<div class="form-group" id="total_sum">
			<?php
				echo form_input( array('name'=>'report_id', 'id'=>'report_id', 'type'=>'Hidden','value'=>$view_id));
				echo form_label("Sum Column Name", 'sum_column_name', array('class' => 'required'))."<br/>";
				echo form_dropdown(array('name' => 'sum_column_name[]','multiple id' =>'sum_column_name','class' => 'form-control input-sm select2'),$sum_column_list);
				echo "<br/><br/><label><input name='all_column_select' id='all_column_select' type='checkbox'> Select All</label>";
			?>
		</div>
		<div class="form-group"  style='margin-bottom:0px;'>
			<button class='btn btn-primary btn-sm' id="sum_column_submit">Submit</button>
		</div>
		<?php
			echo form_close();
		?>
		<div id='sum_column_content' style="padding:10px; !important; overflow: auto;">
			<?php
				echo $sum_column_content;
			?>
		</div>
	</div>
	<!-- Table Sort View -->
	<div class="tab-pane fade" id="report_tab_view" style="padding:15px;background-color: #f2f2f2; overflow: auto !important;">
		<?php 
				$report_tab_view = json_decode($report_tab_view);
				$table_content   = $report_tab_view->table_content;
				$report_id       = $report_tab_view->report_id;
				$table_report_id = $report_tab_view->table_report_id;
				echo $table_content;
		?>
	</div>
	
	<div class="tab-pane fade" id="report_tab_join">
		<?php  
			echo form_open('report_setting/save_join_table/' . $view_id,array('id'=>'save_join_table','class'=>'form-inline','style'=>'padding:15px;'));
			echo $join_list;
			echo form_close();
		?>
	</div>
	<div class="tab-pane fade" id="report_tab_where">
		<?php echo form_open('report_setting/save_table_where/' . $prime_module_id,array('id'=>'save_table_where','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'where_for_id', 'id'=>'where_for_id', 'type'=>'Hidden','value'=>$view_id));
					echo form_input( array('name'=>'query_type', 'id'=>'query_type', 'type'=>'Hidden','value'=>''));
					echo form_label($this->lang->line('query_column_list'), 'pick_list', array('class' => 'required'));
					echo form_dropdown(array('name' => 'query_column_list','id' =>'query_column_list','class' => 'form-control input-sm'),$columns_list);
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
					<textarea name='where_condition' id='where_condition'class='form-control' rows='4'  placeholder='Write Condition with out where' ><?php echo $where_condition; ?></textarea>
				</div>
				<?php 
					if((int)$view_id > 0){
				?>
				<div class="form-group">
					<button class='btn btn-primary btn-sm' id="save_query_btn">Add/Update</button>
				</div>
				<?php 
					}
				?>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#search_submit").click(function(){
		$("#search_filter_div").toggle()
	});
	$("#search_filter_div").hide();
	$("#search_filter").click(function(){
		$("#search_filter_div").toggle();
	});
	$("#clear_search").click(function(){
		$('#search_filter_div').find('input').val('');
		$('option').attr('selected', false);
		$("#search_filter_div").toggle();
	});	
	
	$('#date_column').parent().hide();
	$('#sub_tot_show').parent().hide();
	//$report_for_val,$table_info_val,$table_column_val,$sub_total_row_val,$sub_total_column_val
	var report_for_val    = "#<?php echo $report_for_val;?>";
	if((report_for_val === "") || (report_for_val === "#")){
		$('#report_for option:selected').removeAttr('selected');
	}
	var table_info_val    = "#<?php echo $table_info_val;?>";
	if((table_info_val === "") || (table_info_val === "#")){
		$('#table_info option:selected').removeAttr('selected');
	}
	var table_column_val    = "#<?php echo $table_column_val;?>";
	if((table_column_val === "") || (table_column_val === "#")){
		$('#table_column option:selected').removeAttr('selected');
	}
	/*var sub_total_row_val    = "#<?php echo $sub_total_row_val;?>";
	if((sub_total_row_val === "") || (sub_total_row_val === "#")){
		$('#sub_total_row option:selected').removeAttr('selected');
	}*/
	var group_column_val    = "#<?php echo $group_column_val;?>";
	if((group_column_val === "") || (group_column_val === "#")){
		$('#group_column option:selected').removeAttr('selected');
	}
	var date_column_val    = "#<?php echo $date_column_val;?>";
	if((date_column_val === "") || (date_column_val === "#")){
		$('#date_column option:selected').removeAttr('selected');
	}
	
	var date_filter_val    = "<?php echo $date_filter_val;?>";
	if(parseInt(date_filter_val) === 1){
		$('#date_column').parent().show();
	}
	
	$('#sub_tot_show').prop('checked', false);
	var sub_tot_show_val    = "<?php echo $sub_tot_show_val;?>";
	if(parseInt(sub_tot_show_val) === 1){
		$('#sub_tot_show').prop('checked', true);
		$('#sub_tot_show').parent().show();
	}
	
	call_select();
	$("#all_category_select").click(function(){
		if($("#all_category_select").is(':checked') ){
			$("#report_for > option").prop("selected","selected");
			$("#report_for").trigger("change");
		}else{
			$("#report_for > option").removeAttr("selected");
			$("#report_for").trigger("change");
		}
		$('#report_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
	});
	
	$('#group_column').change(function(e){
		group_column = $('#group_column').val();
		if(group_column == ""){
			$('#sub_tot_show').parent().hide();
		}else{
			$('#sub_tot_show').parent().show();
		}
	});
	
	//Value append in the text area column hidden is used
	$('#add_column').change(function(e){
		var add_name        = $('#add_name').val();
		var add_column_name = $('#add_column').val();
		var check_result = "";
		if(add_name !== ""){
			if(add_column_name !== ""){
				var check_result = "@"+add_column_name+"@";
				$('#hid_add_column_id').append(check_result);
			}
		}
		var as_column_name = add_name.replace(" ","_").toLowerCase();
		var fill_val = $('#hid_add_column_id').val();
		if(fill_val){
			$('#select_condition').val(",("+fill_val+") as "+as_column_name);
		}
	});
	
	$("#all_column_select").click(function(){
		if($("#all_column_select").is(':checked') ){
			$("#sum_column_name > option").prop("selected","selected");
			$("#sum_column_name").trigger("change");
		}else{
			$("#sum_column_name > option").removeAttr("selected");
			$("#sum_column_name").trigger("change");
		}
	});
	
	//Next Tab to Display
	function activaTab(tab){
	  $('.nav-tabs a[href="#' + tab + '"]').tab('show');
	};
	
	$('#report_save').validate($.extend({
		submitHandler: function (form){
			$("#report_base_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#report_base_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#report_base_submit').attr('disabled',false);
					$("#report_base_submit").html("Submit");
					if(response.success){
						toastr.success(response.message);
						table_support.refresh();
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			report_name: "required",
			"table_info[]": "required",
			"table_column[]": "required",
			"report_for[]": "required",
			date_filter: "required",
		}
	}));
	
	$("#date_filter").change(function(){
		date_filter = $("#date_filter").val();
		if(date_filter === "1"){
			$('#date_column').parent().show();
		}else{
			$('#date_column').parent().hide();
		}
		call_select()
	});
	
	$('#save_join_table').validate({
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
				$("#save_join_table_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#save_join_table_btn').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){
						$('#save_join_table_btn').attr('disabled',false);
						$("#save_join_table_btn").html("Save");
						toastr.success(response.message);
					},
					dataType: 'json'
				});
			}else{
				toastr.error("Map all table join");
			}
		}
	});
	jQuery.validator.addMethod("notEqual", function (value, element, param) { // Adding rules for Amount(Not equal to zero)
		return this.optional(element) || value != 'and';
	}, "Please choose query column?");
	$('#save_table_where').validate($.extend({
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
			query_column_list: "required",
			picklist_type: "required",
			where_condition: {
				required:true,
				notEqual: true,
			}
		}
	}));
	
	$('#picklist_type,#pick_list,#session_list').parent().hide();
	$("#query_column_list").change(function(){
		query_column    = $("#query_column_list").val();
		if(query_column){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_column_info"); ?>',
				data: {query_column:query_column},
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
	
	
	
	/* GET TABLE COLUM LIST*/
	$("#table_info").change(function(){
		var table_info = $('#table_info').val();
		if(table_info){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_table_column"); ?>',
				data: {table_info:table_info},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#table_column option:selected').removeAttr('selected');
					$('#group_column option:selected').removeAttr('selected');
					$('#date_column option:selected').removeAttr('selected');
					call_select();
					if(rslt.success){
						if(rslt.table_column){
							$('#table_column,#group_column,#date_column').empty();
							var option = "";
							$.each(rslt.table_column, function (index, value) {
								option += '<option value="' + index + '">' + value + '</option>';
							});
							$('#table_column,#group_column,#date_column').append(option);
						}
						call_select();
					}else{
						toastr.error(rslt.message);
					}
				},
			});
			
		}		
	});
		
	/* TABLE SORTABLE - START */
	default_sortable();
	/* TABLE SORTABLE - END */
	
	//Save Additional Table Column input save functions
	$('#save_add_column').validate($.extend({
		submitHandler: function (form){
			$("#add_column_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#add_column_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#add_column_submit').attr('disabled',false);
					$("#add_column_submit").html("Submit");
					if(response.success){
						toastr.success(response.message);
						$("#add_column_content").html(response.add_column_content);
					}else{
						toastr.error(response.message);
					}
					document.getElementById("save_add_column").reset();
					$("#add_column_id").val(0);
				},
				dataType: 'json'
			});
		},
		rules:{
			add_name: "required",
			add_column: "required",
		}
	}));
	
	//Save total columnwise input function
	$('#save_sum_column').validate($.extend({
		submitHandler: function (form){
			$("#sum_column_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#sum_column_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#sum_column_submit').attr('disabled',false);
					$("#sum_column_submit").html("Submit");
					if(response.success){
						toastr.success(response.message);
						$("#sum_column_content").html(response.sum_column_content);
					}else{
						toastr.error(response.message);
					}
					//$('.modal').modal('toggle');
				},
				dataType: 'json'
			});
		},
		rules:{
			"sum_column_name[]": "required",
		}
	}));
	
	$("#save_filter_query_btn").on('click', function(e){
		var status           = true;
		var report_filter_id         = $('#report_filter_id').val();
		var prime_report_setting_id  = $('#module_id').val();
		var filter_name              = $("#filter_name").val();
		var arr_sts = true;
		var result = {};
		var filter_label     =  $("input[name='filter_label[]']").map(function(){return $(this).val();}).get();
		$.each($("input[name='filter_val[]'],select[name='filter_val[]']"), function() {
			var id = $(this).attr('id');
			result[id]=$(this).val();
			if($(this).val()){
				arr_sts = false;
			}
		});
		if(arr_sts){
			toastr.error('Please select the filter condition and value !');
			status = false;
		}
		if(!filter_name){
			toastr.error('Please enter the filter name');
			status = false;
		}
		var send_url = '<?php echo site_url("$controller_name/filter_save");?>'
		if(status){
			$("#save_filter_query_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_filter_query_btn').attr('disabled','disabled');
			$.ajax({
				type: 'POST',
				url: send_url,
				data:{report_filter_id:report_filter_id,prime_report_setting_id:prime_report_setting_id,filter_name:filter_name,result:result,filter_label:filter_label},
				success: function (data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#filter_report_view").html(rslt.table_view);
					}else{
						toastr.error(rslt.message);
					}
					$('#report_filter_id,#filter_name').val('');
					$("input[name='filter_val[]'],select[name='filter_val[]']").map(function(){return $(this).val('');});
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
					$('#save_filter_query_btn').attr('disabled',false);
					$("#save_filter_query_btn").html("Add/Update");
				}
			});
		}
	});
	
	$("#cancel_filter_query_btn").click(function(){
		$('#report_filter_id,#filter_name').val('');
		$('#search_filter_div').find('input').val('');
		$('#search_filter_div').find('select').val('');
		$("input[name='filter_val[]'],select[name='filter_val[]']").map(function(){
			if($(this).hasClass('select2')){
				$(this).select2({
					placeholder: '---- Select ----',
					allowClear: true,
					dropdownParent: $('.modal-dialog')
				});
			}
		});
		$("#search_filter_div").toggle();
		toastr.success('Data Cleaned');
	});
});

//SORTABLE DEFAULT TABLE 
function default_sortable(){
	var table_idsInOrder = [];
	$( ".default_table" ).sortable({
		update: function( event, ui ){
			table_idsInOrder = [];
			$('#report_sortable tr > th').each(function() {
				table_idsInOrder.push($(this).attr('id'));
			});
			if(table_idsInOrder){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/table_sort_update"); ?>',
					data: {table_idsInOrder:table_idsInOrder},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
						}
					},
				});
				get_table_view_data();
			}
		},connectWith: '.default_table'
	});
}

//GET DEFAULT TABLE UI
function get_table_view_data(){
	var report_id = '<?php echo $view_id; ?>';
	if(report_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_table_view_data"); ?>',
			data: {report_id:report_id},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){
					$('#report_tab_view').html(rslt.table_content);
					default_sortable();
				}					
			}
		});
	}
}

function call_select(){
	$(function(){
		$('.select2').select2({
			placeholder: '---- Select ----',
			allowClear: true,
			//dropdownParent: $('.modal-dialog')
		});
		$('.select2-tags').select2({
			tags: true,
			tokenSeparators: [',']
		});
	});
}

function get_add_column_edit(add_column_id){
	if(add_column_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_add_column_edit"); ?>',
			data: {add_column_id:add_column_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					ad_name = rslt.edit_result.add_name;
					ad_name = ad_name.replace("_"," ").toUpperCase();
					$("#add_column_id").val(rslt.edit_result.prime_report_add_column_id);
					$("#add_name").val(ad_name);
					$("#select_condition").val(rslt.edit_result.select_condition);
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}

function remove_add_column(add_column_id,report_id){
	if(confirm("Are you sure to delete!")){
		if(add_column_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_add_column"); ?>',
				data: {add_column_id:add_column_id,report_id:report_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#add_column_content").html(rslt.add_column_content);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}

function get_sum_column_edit(report_id){
	if(report_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_sum_column_edit"); ?>',
			data: {report_id:report_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#report_id").val(rslt.sum_column_edit_result.report_id);
					if(rslt.sum_column_edit_result.sum_column_name){
					var sum_column_options = rslt.sum_column_edit_result.sum_column_name.split(',');
					for(var i in sum_column_options) {
						var optionVal = sum_column_options[i];
						$("#sum_column_name").find("option[value='"+optionVal+"']").prop("selected", "selected");
						}
					}
					call_select();
				}
			},
		});
	}
}

function remove_sum_column(report_id){
	if(report_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/remove_sum_column"); ?>',
			data: {report_id:report_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#sum_column_content").html(rslt.sum_column_content);
					toastr.success(rslt.message);
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
function edit_filter_report(report_id){
	if(report_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_filter_report"); ?>',
			data: {report_id:report_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				var filter_val   = (rslt.edit_data['filter_label']).split(",");
				i = 0;
				$("input[name='filter_label[]']").map(function(){
					$(this).val(filter_val[i]);
					i++;
				}).get();
				var map                      = (rslt.edit_data['map']);
				var report_filter_id         = $('#report_filter_id').val(rslt.edit_data['report_filter_id']);
				var prime_report_setting_id  = $('#module_id').val(rslt.edit_data['prime_report_setting_id']);
				var filter_name              = $("#filter_name").val(rslt.edit_data['filter_name']);
				$.each( map, function(key,value){
					if(value){
						$("#"+key).val(value);
						if($("#"+key).hasClass('select2')){
							$("#"+key).select2({
								placeholder: '---- Select ----',
								allowClear: true,
								dropdownParent: $('.modal-dialog')
							});
						}
					}
				});	
			}
		});
	}
}
function delete_filter_report(report_id,prime_report_setting_id){
	if(report_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/delete_filter_report"); ?>',
			data: {report_id:report_id,prime_report_setting_id:prime_report_setting_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#filter_report_view").html(rslt.table_view);
					toastr.success(rslt.message);
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
</script>
<style>
div#total_sum > span{
	width: 200px !important;
}
/*ul.select2-selection__rendered{
    overflow: auto !important;height: 180px;
}*/
.search_filter{
	right: 56.5%;
}
</style>