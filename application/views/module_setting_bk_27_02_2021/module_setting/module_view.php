<style>
	.sortable {list-style-type:none;margin:0;padding:0;width: auto;}
	.sortable li{margin: 2px 20px 15px 0; padding: 8px; width: 23%; height: auto; font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px; cursor: pointer;display: inline-block;}	
	button.dt-button {
		color: #FFFFFF;
		font-weight: 500;
		background-color:#fd8a13 !important;
	}
	.select2-container {
		min-width: 100% !important;
	}
	table tfoot {
		display: table-header-group;
	}
</style>
<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#module_info">Module Information</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#query_info">Basic Search Query</a>
	</li>
	<?php if($prime_module_id === "employees") {?>
	<li role="presentation">
		<a data-toggle="tab" href="#payroll_info">Payroll Formula</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#payroll_function">Payroll Function</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#column_mapping">Function Column Mapping</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#formula_transfer">Formula Transfer</a>
	</li>
	<?php }?>
	<li role="presentation">
		<a data-toggle="tab" href="#print_map">Print Map</a>
	</li>
	</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="module_info">
		<?php echo form_open('module_setting/save_module/' . $prime_module_id,array('id'=>'save_module','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php			
					echo form_label($this->lang->line('module_id'), 'module_id', array('class' => 'required'));
					echo form_input( array('name'=>'prime_module_id', 'id'=>'prime_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					$module_type = "DYNAMIC";
					if($module_info->module_type){
						$module_type = $module_info->module_type;
					}
					echo form_input( array('name'=>'module_type', 'id'=>'module_type', 'type'=>'Hidden','value'=>$module_type));
					echo form_input(array('name'=> 'module_id', 'id' => 'module_id', 'class' => 'form-control input-sm', "placeholder"=>$this->lang->line('module_id'),'value' => ucwords(str_replace("_"," ",$module_info->module_id))));
				?>
			</div>
			<div class="form-group">
				<?php			
					echo form_label($this->lang->line('module_name'), 'module_name', array('class' => 'required'));
					echo form_input( array('name'=>'prime_module_id', 'id'=>'prime_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_input(array('name'=> 'module_name', 'id' => 'module_name', 'class' => 'form-control input-sm', "placeholder"=>$this->lang->line('module_name'),'value' => ucwords(str_replace("_"," ",$module_info->module_name))));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('rights_to'), 'rights_to', array('class' => 'required'));
					$rights_to_val = "";
					if($module_info->rights_to){
						$rights_to_val = explode(",",$module_info->rights_to);
					}
					$rights_to_array =  array(""=>"---- Rights to ----",1=>"Admin Module",2=>"Customer Module");
					echo form_dropdown(array('name' => 'rights_to[]','multiple id' =>'rights_to','class' => 'form-control input-sm select2'), $rights_to_array,$rights_to_val);
					echo "<label><input name='rights_to_select' id='rights_to_select' type='checkbox'> Select All</label>";
				?>
			</div>	
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('module_for'), 'module_for', array('class' => 'required'));
					$module_for_val = "";
					if($module_info->module_for){
						$module_for_val = explode(",",$module_info->module_for);
					}
					echo form_dropdown(array('name' => 'module_for[]','multiple id' =>'module_for','class' => 'form-control input-sm select2'), $module_for,$module_for_val);
					echo "<label><input name='module_for_select' id='module_for_select' type='checkbox'> Select All</label>";
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('map_menu_to'), 'map_menu_to', array('class' => 'required'));
					echo form_dropdown(array('name' => 'map_menu_to','id' =>'map_menu_to','class' => 'form-control input-sm'), $map_menu_to,$module_info->menu_id);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('sub_map_menu_to'), 'sub_map_menu_to', array('class' => 'required'));
					echo form_dropdown(array('name' => 'sub_map_menu_to','id' =>'sub_map_menu_to','class' => 'form-control input-sm'), $sub_map_menu_to,$module_info->sub_menu_id);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('quicklink'), 'quicklink', array('class' => ''));
					$quicklink_val = "";
					if($module_info->quicklink){
						$quicklink_val = explode(",",$module_info->quicklink);
					}
					echo form_dropdown(array('name' => 'quicklink[]','multiple id' =>'quicklink','class' => 'form-control input-sm select2'), $quicklink_list,$quicklink_val);
				?>
			</div>
			<div class="form-group">
				<label class="checkbox-inline">
					<?php
						echo form_checkbox(array(
						'name' => 'show_module',
						'id' => 'show_module',
						'value' => 1,
						'checked' => ($module_info->show_module) ? 1 : 0)
						);
					?>
					<?php echo form_label("Show Module", 'show_module', array('class' => '')); ?>
				</label>
			</div>
			<div class="form-group">
				<label class="checkbox-inline">
					<?php
						echo form_checkbox(array(
						'name' => 'import_module',
						'id' => 'import_module',
						'value' => 1,
						'checked' => ($module_info->import_module) ? 1 : 0)
						);
					?>
					<?php echo form_label("Import for Module", 'import_module', array('class' => '')); ?>
				</label>
			</div>
			<div class="form-group">
				<label class="checkbox-inline">
					<?php
						echo form_checkbox(array(
						'name' => 'pdf_module',
						'id' => 'pdf_module',
						'value' => 1,
						'checked' => ($module_info->pdf_module) ? 1 : 0)
						);
					?>
					<?php echo form_label("PDF for Module", 'pdf_module', array('class' => '')); ?>
				</label>
			</div>
			<div class="form-group">
				<label class="checkbox-inline">
					<?php
						echo form_checkbox(array(
						'name' => 'custom_module',
						'id' => 'custom_module',
						'value' => 1,
						'checked' => ($module_info->custom_module) ? 1 : 0)
						);
					?>
					<?php echo form_label("Custom Design", 'custom_module', array('class' => '')); ?>
				</label>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="module_submit">Add/Update</button>
			</div>
		<?php echo form_close(); ?>
	</div>
	<div class="tab-pane fade" id="query_info">
		<?php echo form_open('module_setting/save_query_info/' . $prime_module_id,array('id'=>'save_query_info','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_table_id', 'id'=>'prime_table_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'query_module_id', 'id'=>'query_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_input( array('name'=>'query_type', 'id'=>'query_type', 'type'=>'Hidden','value'=>''));
					echo form_label($this->lang->line('query_for'), 'field_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'query_for','id' =>'query_for','class' => 'form-control input-sm'), $user_field_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('query_column_list'), 'pick_list', array('class' => 'required'));
					echo form_dropdown(array('name' => 'query_column_list','id' =>'query_column_list','class' => 'form-control input-sm'), $column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('picklist_type'), 'query_type', array('class' => 'required'));
					$query_type_array = array(''=>"--- Select Get Value from ---","1"=>"Get From Picklist ","2"=>"Get From Session");
					echo form_dropdown(array('name' => 'picklist_type','id' =>'picklist_type','class' => 'form-control input-sm'), $query_type_array);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('pick_list'), 'pick_list', array('class' => 'required'))."<br/>";
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
					<textarea name='where_condition' id='where_condition' style='text-transform: lowercase !important;' class='form-control' rows='4'  placeholder='Write Condition with out where' >and</textarea>
				</div>
				<div class="form-group">
					<button class='btn btn-primary btn-sm' id="save_query_btn">Add/Update</button>
				</div>
			</div>
		<?php echo form_close(); ?>
		<div id='table_query_list'>
			<?php
				echo $table_query_list;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="payroll_info">
		<?php echo form_open('module_setting/save_payroll_info/' . $prime_module_id,array('id'=>'save_payroll_info','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_payroll_id', 'id'=>'prime_payroll_id', 'type'=>'Hidden','value'=>0));
					echo form_label($this->lang->line('formula_for'), 'formula_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'formula_for','id' =>'formula_for','class' => 'form-control input-sm select2'), $field_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('formula_type'), 'formula_type', array('class' => 'required'));
					$formula_type_list = array(""=>"--- Select Type ---",1=>"Earnings",2=>"Deductions");
					echo form_dropdown(array('name' => 'formula_type','id' =>'formula_type','class' => 'form-control input-sm select2'), $formula_type_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('formula_mode'), 'formula_mode', array('class' => 'required'));
					$formula_mode_list = array(""=>"--- Formula Mode ---","1"=>"Direct Input","2"=>"Formula Input","3"=>"Condition Input");
					echo form_dropdown(array('name' => 'formula_mode','id' =>'formula_mode','class' => 'form-control input-sm select2'), $formula_mode_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('out_column'), 'out_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'out_column','id' =>'out_column','class' => 'form-control input-sm select2'), $out_column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('earn_column'), 'earn_column', array('class' => ''));
					echo form_dropdown(array('name' => 'earn_column','id' =>'earn_column','class' => 'form-control input-sm select2'), $earn_column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('ded_column'), 'ded_column', array('class' => ''));
					echo form_dropdown(array('name' => 'ded_column','id' =>'ded_column','class' => 'form-control input-sm select2'), $ded_column_list);
				?>
			</div>
			<div class="form-group">
					<textarea name='payroll_formula' style='text-transform: lowercase !important;' id='payroll_formula' class='form-control' rows='4'  placeholder='Write Condition'></textarea>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('round_value'), 'round_value', array('class' => ''));
					$rounding_list = array(""=>"--- Rounding Value ---","0.1"=>"Normal","0.5"=>"50 Paise",">0.5"=>"> 50 Paise","<0.5"=>"< 50 Paise","1"=>"1 Rupee",">1"=>"> 1 Rupee","<1"=>"< 1 Rupee","5"=>"5 Rupee","10"=>"10 Rupee","50"=>"50 Rupee","100"=>"100 Rupee");
					echo form_dropdown(array('name' => 'round_value','id' =>'round_value','class' => 'form-control input-sm select2'), $rounding_list);
				?>
			</div>
			<div class="form-group">
				<label>
					<input name='fandf_only' id='fandf_only' type="checkbox"> F and F Only
				</label>
			</div>
			<div class="form-group">
					<button class='btn btn-primary btn-sm' id="save_payroll_btn">Add/Update</button>
					<a class='btn btn-danger btn-sm' id="payroll_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div id='formula_content' style="padding:10px; height: 450px !important; overflow: auto;">
			<?php
				echo $formula_content;
			?>
		</div>
	</div>
	<!-- 01MARCH2018--MRJ start -->
	<div class="tab-pane fade" id="payroll_function">
		<?php echo form_open('module_setting/save_payroll_function/' . $prime_module_id,array('id'=>'save_payroll_function','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
				echo form_input( array('name'=>'input_count', 'id'=>'input_count', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'payroll_function_id', 'id'=>'payroll_function_id', 'type'=>'Hidden','value'=>0));
					echo form_label($this->lang->line('statutory_name'), 'statutory_name', array('class' => 'required'));
					echo form_dropdown(array('name' => 'statutory_name','id' =>'statutory_name','class' => 'form-control input-sm select2'), $statutory_name_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('map_column'), 'map_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'map_column','id' =>'map_column','class' => 'form-control input-sm select2'), $trans_column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('input_column'), 'input_column', array('class' => 'required'));
					echo "<br/>";
					echo form_dropdown(array('name' => 'input_column[]','multiple id' =>'input_column','class' => 'form-control input-sm select2'), $trans_column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('function_name'), 'function_name', array('class' => 'required'));
					echo form_dropdown(array('name' => 'function_name','id' =>'function_name','class' => 'form-control input-sm select2'), $statutory_function_list);
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="save_payroll_function_btn">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="function_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div class='row' style="margin-left: 30px;"><h5 style="color:red; font-weight: bold;">Note : Input Column Order </h5> <p class='info_tag'></p>
		</div>
		
		<div id='function_list'  style='padding:20px;'>
			<?php
				echo $function_list;
			?>
		</div>
	</div>
	<!-- 01MARCH2018--MRJ start -->
	<div class="tab-pane fade" id="column_mapping">
		<?php echo form_open('module_setting/save_column_mapping/' . $prime_module_id,array('id'=>'save_column_mapping','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'payroll_column_map_id', 'id'=>'payroll_column_map_id', 'type'=>'Hidden','value'=>0));
					echo form_label("Statutory Name", 'map_statutory_name', array('class' => 'required'));
					echo form_dropdown(array('name' => 'map_statutory_name','id' =>'map_statutory_name','class' => 'form-control input-sm select2'), $statutory_name_list);
				?>
			</div>
			<div class="form-group">
				<?php
					$loc_column_list = array(""=>"-- Select Column --","earned_gross"=>"Earned Gross","fixed_gross"=>"Fixed Gross","fixed_basic"=>"Fixed Basic","paid_days"=>"Paid Days","month_days"=>"Month Days","lop_days"=>"Lop Days","professional_tax_amount"=>"Professional Tax","esi_loc"=>"ESI Location","esi_elig"=>"ESI Eligibility","supp_month_days"=>"Supplementary Month days","supp_paid_days"=>"Supplementary Paid days","arrear_gross"=>"Arrear Gross","arrear_pf_gross"=>"Arrear PF Gross","earned_basic"=>"Earned Basic");
					echo form_label("Column Name", 'loc_name', array('class' => 'required'));
					echo form_dropdown(array('name' => 'loc_name','id' =>'loc_name','class' => 'form-control input-sm select2'), $loc_column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("DB Column", 'db_column', array('class' => 'required'));
					echo form_dropdown(array('name' => 'db_column','id' =>'db_column','class' => 'form-control input-sm select2'), $trans_column_list);
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="save_column_mapping_btn">Add/Update</button>
			</div>
		<?php echo form_close(); ?>
		<div id='mapping_list'  style='padding:20px;'>
			<?php
				echo $mapping_list;
			?>
		</div>
	</div>
	<!-- 01MARCH2018--MRJ end -->
	<!-- 10APRIL2019--MRJ start -->
	<div class="tab-pane fade" id="formula_transfer">
	<?php echo form_open('module_setting/save_formula_transfer/' . $prime_module_id,array('id'=>'save_formula_transfer','class'=>'form-inline')); ?>
		<div class="form-group">
			<?php
				echo form_label('Select Category', 'select_category', array('class' => 'required'));
				echo form_dropdown(array('name' => 'select_category','id' =>'select_category','class' => 'form-control input-sm'), $formula_role_for);
			?>
		</div>		
		<div class="form-group">
			<?php
				echo form_label('Formula Transfer', 'formula_transfer_to', array('class' => 'required'));
				echo form_dropdown(array('name' => 'formula_transfer_to','id' =>'formula_transfer_to','class' => 'form-control input-sm'), $noformula_role_for);
			?>
		</div>
		<div class="form-group">
			<button class='btn btn-primary btn-sm' id="save_formula_transfer_btn">Submit</button>
		</div>
	<?php echo form_close(); ?>
	</div>
	<!-- 10APRIL2019--MRJ end -->
	<div class="tab-pane fade" id="print_map">
		<?php echo form_open('module_setting/save_print_map/' . $prime_module_id,array('id'=>'save_print_map','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_print_map_id', 'id'=>'prime_print_map_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'print_map_module_id', 'id'=>'print_map_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_label($this->lang->line('print_map_for'), 'print_map_for', array('class' => 'required'));
					$print_map_for_line = array("0"=>"-- Select Print For --","1"=>"Payslip Formates","2"=>"F&F Payslip Formates","3"=>"Form M","4"=>"PF Challan","5"=>"ESI","6"=>"Professional TAX","7"=>"Bonus");
					echo form_dropdown(array('name' => 'print_map_for','id' =>'print_map_for','class'=>'form-control input-sm'), $print_map_for_line);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('print_mapping'), 'print_mapping', array('class' => 'required'));
					echo form_dropdown(array('name' => 'print_mapping[]','multiple id' =>'print_mapping','class'=>'form-control input-sm select2'), $print_map_list);
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="save_print_map_btn">Add/Update</button>
			</div>
		<?php echo form_close(); ?>
		<div id='print_info_list' style="padding:10px;">
			<?php echo $print_info_list;?>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	//sorting word in ascending order
	$("#out_column").html($('#out_column option').sort(function(x, y) {
		 return $(x).text().toUpperCase() < $(y).text().toUpperCase() ? -1 : 1;
	}));
	$("#earn_column").html($('#earn_column option').sort(function(x, y) {
		 return $(x).text().toUpperCase() < $(y).text().toUpperCase() ? -1 : 1;
	}));
	$("#ded_column").html($('#ded_column option').sort(function(x, y) {
		 return $(x).text().toUpperCase() < $(y).text().toUpperCase() ? -1 : 1;
	}));
	$("#map_column").html($('#map_column option').sort(function(x, y) {
		 return $(x).text().toUpperCase() < $(y).text().toUpperCase() ? -1 : 1;
	}));
	$("#input_column").html($('#input_column option').sort(function(x, y) {
		 return $(x).text().toUpperCase() < $(y).text().toUpperCase() ? -1 : 1;
	}));
	
	empty_all();
	call_select();
	$("#rights_to_select").click(function(){
		if($("#rights_to_select").is(':checked') ){
			$("#rights_to > option").prop("selected","selected");
			$("#rights_to").trigger("change");
		}else{
			$("#rights_to > option").removeAttr("selected");
			$("#rights_to").trigger("change");
		}
		$('#rights_to option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
	});
	$("#module_for_select").click(function(){
		if($("#module_for_select").is(':checked') ){
			$("#module_for > option").prop("selected","selected");
			$("#module_for").trigger("change");
		}else{
			$("#module_for > option").removeAttr("selected");
			$("#module_for").trigger("change");
		}
		$('#module_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
	});
	var prime_module_id = '<?php echo $prime_module_id; ?>';
	if(prime_module_id !== "0"){
		$('#module_id').attr('readonly', true);
	}
	var rights_to_val  = '<?php echo $rights_to_val; ?>';
	if(rights_to_val === ""){
		$('#rights_to option:selected').removeAttr('selected');
	}
	var pick_list  = $("#pick_list").val();
	if(pick_list === ""){
		$('#pick_list option:selected').removeAttr('selected');
	}
	var module_for_val  = '<?php echo $module_for_val; ?>';
	if(module_for_val === ""){
		$('#module_for option:selected').removeAttr('selected');
	}
	var quicklink_val   = '<?php echo $quicklink_val; ?>';
	if(quicklink_val === ""){
		$('#quicklink option:selected').removeAttr('selected');
	}	
	
	$('#where_condition').bind('keyup blur change', function(e) {
		where_condition = $("#where_condition").val();
		if(where_condition === ""){
			$("#where_condition").val("and");
		}
	});
	
	//GET SUB MENU FOR MAIN MENU
	$('#map_menu_to').on('change',function(){
		var prime_menu_id = parseInt($('#map_menu_to').val());
		if(prime_menu_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_sub_menu"); ?>',
				data: {prime_menu_id:prime_menu_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$('#sub_map_menu_to').html(rslt.message);
						$('#sub_map_menu_to').select2({
							placeholder: '---- Select ----',
							allowClear: true,
							dropdownParent: $('.modal-dialog')
						});
					}
				}
			});
		}
	});
	
	$('#picklist_type,#pick_list,#session_list').parent().hide();
	$("#query_column_list").change(function(){
		query_column    = $("#query_column_list").val();
		query_module_id = $("#query_module_id").val();
		if(query_column){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_column_info"); ?>',
				data: {query_column:query_column,query_module_id:query_module_id},
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
	
	$.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 ]*$/i.test(value);
    }, "Must contain only letters and numbers");
	$.validator.addMethod("space_check", function(value, element) {
        return this.optional(element) || /^(\w+\s?)*\s*$/i.test(value);
    }, "Must contain single space");
	
	jQuery.validator.addMethod("notEqual", function (value, element, param) { // Adding rules for Amount(Not equal to zero)
		return this.optional(element) || value != 'and';
	}, "Write Condition after and");
	
	$('#save_module').validate($.extend({
		submitHandler: function (form){
			$("#module_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#module_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#module_submit').attr('disabled',false);
					$("#module_submit").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
					}else{
						toastr.error(response.message);
					}
					table_support.refresh();
					$('.modal').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			module_id: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			module_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			map_menu_to: "required",
			"module_for[]": "required",
			"rights_to[]": "required",
		}
	}));
	
	$('#save_query_info').validate($.extend({
		submitHandler: function (form){
			$("#save_query_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_query_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_query_btn').attr('disabled',false);
					$("#save_query_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						$("#table_query_list").html(response.table_query_list);
						document.getElementById("save_query_info").reset();
						$("#prime_table_id").val(0);
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			query_for: "required",
			where_condition: {
				required:true,
				notEqual: true,
			}
		}
	}));
	
	//08MAY2019--MRJ and GS Updates
	//Front validation start
	//1. outcolumn not equal to formula input formula_mode(formula,condition)
	
	var formula_mode_check_value = 1;
	$('#earn_column,#ded_column').change(function(event){
		var formula_mode = $('#formula_mode').val();
		if((parseInt(formula_mode) === 2) || (parseInt(formula_mode) === 3)){			
			if($(this).val()==$("#out_column").val()){
				formula_mode_check_value=0;
				toastr.error("Your Out Column should not be Input");
			}else{
				formula_mode_check_value=1;
			}
		}
	});

	//formula and conditions checking for front end validations.
	$("#payroll_formula").on('input propertychange paste', function(){
		var formula_mode    = $('#formula_mode').val();
		var payroll_formula = $.trim($('#payroll_formula').val());
		if((parseInt(formula_mode) === 1) || (parseInt(formula_mode) === 2)){
			if((payroll_formula.indexOf("if(") >= 0) || (payroll_formula.indexOf("if (") >= 0)){
				toastr.error("Direct and Formula input is not allowed if functions?");
				$('#payroll_formula').val('');
			}
		}
     });
	 
	 
	 //@@ error throwing validations and direct formula field is validated
	 $('#save_payroll_btn').click(function(){
		 var formula_mode    = $("#formula_mode").val();
		 var payroll_formula = $.trim($("#payroll_formula").val());
		 var at_count        = (payroll_formula.match(/@/g) || []).length;
		 if(payroll_formula.indexOf("@@") != -1){
			toastr.error("Please check formula double at(@@) is present, check it?");
			return false;
		 }
		 
		 if(parseInt(formula_mode) === 1){
			if(parseInt(at_count) > 2){
				toastr.error("Direct formula allowed one field only?");
				return false;
			}
		 }
		 
		 if((parseInt(at_count) === 0) || (parseInt(at_count) === 1)){//@ count checking
			toastr.error("Please check your formula?");
			return false;
		 }
		 
		//Payroll formula error checking
		var s = payroll_formula.replace(/@/g, "");	
		var res = balanced(s);
		if(res){
			return true;
		}else{
			toastr.error("Error");
			return false;
		}
	});
	
	$('#round_value').parent().show();
	$('#formula_mode').change(function(event){
		var formula_mode = $('#formula_mode').val();
		if(parseInt(formula_mode) === 3){
			$('#round_value').parent().hide();
		}else{
			$('#round_value').parent().show();
		}
	});
	
	//payroll formula save
	$('#save_payroll_info').validate($.extend({
		submitHandler: function (form){
			$("#save_payroll_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_payroll_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_payroll_btn').attr('disabled',false);
					$("#save_payroll_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						$("#formula_content").html(response.formula_content);
						//document.getElementById("save_payroll_info").reset();
						$("#prime_payroll_id").val(0);
						var table = $('#detail_list').DataTable({
							dom: 'Bfrtip',
							"lengthChange": false,
							"order": [[ 1, "desc" ]],
							"stateSave": true,
							buttons: [{
								extend: 'excelHtml5',
							}],
						});
						empty_all();
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			formula_for: "required",
			formula_type: "required",
			out_column: "required",
			payroll_formula: "required",
			formula_mode: "required",
		}
	}));
	
	//Table string Check with column for variable and table data
	//08MAY2019--MRJ and GS Updates Start
	$('#ded_column').change(function(event){
		if(formula_mode_check_value===1){
			var ded_column       = titleCase($(this).find("option:selected").text());
			var ded_column_name  = $('#ded_column').val();
			if(ded_column_name !== ""){
				check_result = "@"+ded_column_name+"@";
				$("#payroll_formula").val(function(i, val) {
						return val += check_result;
				});
			}
		}
	});
	
	$('#earn_column').change(function(event){
		if(formula_mode_check_value===1){
			var earn_column      = titleCase($(this).find("option:selected").text());
			var earn_column_name = $('#earn_column').val();
			if(earn_column_name !== ""){
				check_result = "@"+earn_column_name+"@";
				$("#payroll_formula").val(function(i, val) {
						return val += check_result;
				});
			}
		}
	});
	
	
	//08MAY2019--MRJ and GS Updates End
	
	//01MARCH2019--MRJ
	//Statutory function name save details
	$('#save_payroll_function').validate($.extend({
		submitHandler: function (form){
			$("#save_payroll_function_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_payroll_function_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_payroll_function_btn').attr('disabled',false);
					$("#save_payroll_function_btn").html("Add/Update");
					empty_all();
					if(response.success){
						toastr.success(response.message);
						$("#function_list").html(response.function_list);
						document.getElementById("save_payroll_function").reset();
						$("#payroll_function_id").val(0);
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			statutory_name: "required",
			map_column: "required",
			input_column: "required",
			function_name: "required"
		}
	}));
	
	$('#save_column_mapping').validate($.extend({
		submitHandler: function (form){
			$("#save_column_mapping_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_column_mapping_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_column_mapping_btn').attr('disabled',false);
					$("#save_column_mapping_btn").html("Add/Update");
					if(response.success){
						$("#mapping_list").html(response.mapping_list);
						//document.getElementById("save_column_mapping").reset();
						$("#payroll_column_map_id").val(0);
						toastr.success(response.message);
					}else{
						toastr.error(response.message);
					}
					empty_all();
				},
				dataType: 'json'
			});
		},
		rules:{
			map_statutory_name: "required",
			loc_name: "required",
			db_column: "required",
		}
	}));
	
	$('#save_print_map').validate($.extend({
		submitHandler: function (form){
			$("#save_print_map_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_print_map_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_print_map_btn').attr('disabled',false);
					$("#save_print_map_btn").html("Add/Update");					
					if(response.success){
						document.getElementById("save_print_map").reset();
						$("#prime_print_map_id").val(0);
						$('#print_map option:selected').removeAttr('selected');
						call_select();
						$("#print_info_list").html(response.print_info_list);
						toastr.success(response.message);
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			print_map_for:{
				required: true,
				min:1,
			},
			"print_mapping[]": "required",
		}
	}));
	
	//Save Formula Transfer call
	$('#save_formula_transfer').validate($.extend({
		submitHandler: function (form){
			$("#save_formula_transfer_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_formula_transfer_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_formula_transfer_btn').attr('disabled',false);
					$("#save_formula_transfer_btn").html("Submit");
					if(response.success){
						toastr.success(response.message);
					}else{
						toastr.error(response.message);
					}
					$('.modal').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			select_category:"required",
			formula_transfer_to:"required",
		}
	}));
	
	$("#payroll_cancel").click(function(){
		empty_all();
	});
	
	$("#function_cancel").click(function(){
		empty_all();
	});
	
	$("#search_filter").on('change',function(){
		var search_filter = $('#search_filter').val();
	});
	
	//Data filter search dropdown dynamically updates
	 var table = $('#detail_list').DataTable({
		 dom: 'Bfrtip',
		 "lengthChange": false,
		"order": [[ 1, "desc" ]],
		"stateSave": true,
		buttons: [{
			extend: 'excelHtml5',
		}],
	});
	
    $("#detail_list tfoot td").each(function(i){
		if ($(this).text() !== '') {
	        var isStatusColumn = (($(this).text() == 'Status') ? true : false);
			var select = $('<select><option value="">-- Select One --</option></select>')
	            .appendTo( $(this).empty())
	            .on( 'change', function (){
	                var val = $(this).val();
	                table.column( i )
	                    .search( val ? '^'+$(this).val()+'$' : val, true, false )
	                    .draw();
	            });
			if (isStatusColumn){
				var statusItems = [];
				table.column(i).nodes().to$().each( function(d, j){
					var thisStatus = $(j).attr("data-filter");
					if($.inArray(thisStatus, statusItems) === -1) statusItems.push(thisStatus);
				});
				statusItems.sort();			
				$.each( statusItems, function(i, item){
				    select.append( '<option value="'+item+'">'+item+'</option>' );
				});
			}
			else {
				table.column(i).data().unique().sort().each(function (d, j) {  
					select.append('<option value="'+d+'">'+d+'</option>');
		        });	
			}
		}
    });
	
	//Default setting for all functions --18JULY2019
	$("#statutory_name").change(function(){
		var statutory_name   = $('#statutory_name').val();
		var default_arr = [];
		$("#input_column").val("");
		$("#function_name").val("");		
		$('#input_column option:selected').removeAttr('selected');
		var th_line = "";
		var input_count = 0;
			if(parseInt(statutory_name) === 8){//total works default settings
				default_arr = ["role","transactions_month"];
				$("#function_name").val(5);
				input_count = default_arr.length;
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});				
			}else
			if(parseInt(statutory_name) === 3){//PT Amount default settings
				default_arr = ["employee_code","professional_tax_location","pt_projection","total_earnings","transactions_month"];
				$("#function_name").val(3);
				input_count = default_arr.length;
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});
			}else
			if(parseInt(statutory_name) === 7){//Staff Loan amount default settings
				default_arr = ["employee_code","transactions_month"];
				input_count = default_arr.length;
				$("#function_name").val(4);
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});
			}else
			if(parseInt(statutory_name) === 9){//Difference Date default settings
				default_arr = ["employee_code","role","transactions_month"];
				input_count = default_arr.length;
				$("#function_name").val(8);
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});
			}else
			if(parseInt(statutory_name) === 10){//Separation date default settings
				default_arr = ["employee_code","role","transactions_month"];
				input_count = default_arr.length;
				$("#function_name").val(9);
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});
			}else
			if(parseInt(statutory_name) === 11){//Gratuity default settings
				default_arr = ["employee_code","role"];
				input_count = default_arr.length;
				$("#function_name").val(10);
				$.each(default_arr, function(i, val){
					$("#input_column").find("option[value='"+val+"']").prop("selected", "selected");
					th_line += "<span>"+val+",</span>";
				});
			}			
			$('#input_count').val(input_count);
			$('.info_tag').html(th_line);
			$('#input_column').select2({allowClear: false});
			$('#function_name').select2({allowClear: false});
			$('.select2').on("select2:select", function (evt) {
			  var element = evt.params.data.element;
			  var $element = $(element); 			  
			  $element.detach();
			  $(this).append($element);
			  $(this).trigger("change");
			});
	});	
	//Jai Sir Changes
	$("#out_column").change(function(){
		var formula_type = $('#formula_type').val();
		var out_column   = $('#out_column').val();
		var formula_mode = $('#formula_mode').val();
		if(formula_mode === '1'){
			if(formula_type === '1'){
				$("#earn_column").find("option[value='"+out_column+"']").prop("selected", "selected");
				$('#earn_column').trigger('change');
				$('#ded_column').val('');
			}else{
				$("#ded_column").find("option[value='"+out_column+"']").prop("selected", "selected");
				$('#earn_column').val('');
				$('#ded_column').trigger('change');
			}	
			call_select();
		}		
	});
});
 //Payroll formula error checking - START
function balanced(s){
	var pairs = {
	'}':'{',
	']':'[',
	')':'(',
	};
	var stack = [];
	for(var i = 0;i < s.length;++i){
		switch(s.charAt(i)){
			case '[': case '{':case '(':
				stack.push(s.charAt(i));
			break;
			case ']': case '}':case ')':
				if(isStackEmpty(stack) || peek(stack) !== pairs[s.charAt(i)]) return false;
				stack.pop();
			break;
			case '"':
				if(isStackEmpty(stack) || peek(stack) !== s.charAt(i)){
					stack.push(s.charAt(i));
				}else{
					stack.pop();
				}
		}
	}
	return isStackEmpty(stack);
}
function isStackEmpty(s){
	return s.length === 0;
}
function peek(s){
	return s[s.length-1];
}
 //Payroll formula error checking END
function edit_print_map(prime_print_map_id){
	if(prime_print_map_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/edit_print_map"); ?>',
			data: {prime_print_map_id:prime_print_map_id},
			success: function(data) {
				$('#print_mapping option:selected').removeAttr('selected');
				call_select();
				var rslt = JSON.parse(data);
				if(rslt.success){					
					$("#prime_print_map_id").val(rslt.print_info.prime_print_map_id);
					$("#print_map_module_id").val(rslt.print_info.print_map_module_id);
					$("#print_map_for").val(rslt.print_info.print_map_for);
					if(rslt.print_info.print_map){
						var print_map = rslt.print_info.print_map.split(",");
						for(var i in print_map) {
							var print_map_val = print_map[i];
							$("#print_mapping").find("option[value='"+print_map_val+"']").prop("selected", "selected");
						}
						call_select();
					}
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}

//Title Case Changed out column
function titleCase(str) {
	var splitStr = str.toLowerCase().split(' ');
	for (var i = 0; i < splitStr.length; i++) {
		splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
	}
	return splitStr.join(' '); 
}

function edit_query(prime_table_id){
	if(prime_table_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_edit_info"); ?>',
			data: {prime_table_id:prime_table_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#query_for").val(rslt.query_for);
					$("#where_condition").val(rslt.where_condition);
					$("#prime_table_id").val(rslt.prime_table_id);
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}

function remove_query(prime_table_id){
	query_module_id = $("#query_module_id").val();
	if(confirm("Are you sure to delete!")){
		if(prime_table_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_query_info"); ?>',
				data: {prime_table_id:prime_table_id,query_module_id:query_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#table_query_list").html(rslt.table_query_list);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}

//MRJ Start 07-01-2018
//edit formula
function get_formula_edit(prime_payroll_id){
	if(prime_payroll_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_formula_edit_info"); ?>',
			data: {prime_payroll_id:prime_payroll_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#formula_for").val(rslt.formula_for);
					$("#formula_type").val(rslt.formula_type);
					$("#out_column").val(rslt.out_column);
					$("#payroll_formula").val(rslt.payroll_formula);
					$("#formula_mode").val(rslt.formula_mode);
					$("#round_value").val(rslt.round_value);
					if(rslt.fandf_only === "1"){
						$('#fandf_only').prop('checked', true);
					}else{
						$('#fandf_only').prop('checked', false);
					}
					$("#prime_payroll_id").val(rslt.prime_payroll_id);
					call_select();
					if(parseInt(rslt.formula_mode) === 3){
						$('#round_value').parent().hide();
					}
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}

//edit mapping column
function get_mapping_edit(payroll_column_map_id){
	if(payroll_column_map_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_mapping_edit"); ?>',
			data: {payroll_column_map_id:payroll_column_map_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#map_statutory_name").val(rslt.edit_result.map_statutory_name);
					$("#loc_name").val(rslt.edit_result.loc_name);
					$("#db_column").val(rslt.edit_result.db_column);
					$("#payroll_column_map_id").val(rslt.edit_result.payroll_column_map_id);
				}else{
					toastr.error(rslt.message);
				}
				call_select();
			},
		});
	}
}

function remove_mapping_function(payroll_column_map_id){
	if(confirm("Are you sure to delete!")){
		if(prime_table_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_mapping_function"); ?>',
				data: {payroll_column_map_id:payroll_column_map_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#mapping_list").html(rslt.mapping_list);
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
	//$("#formula_for").val("");
	//$("#formula_type").val("");
	$("#out_column").val("");
	$("#earn_column").val("");
	$("#ded_column").val("");
	$("#payroll_formula").val("");
	$("#formula_mode").val("");
	$("#round_value").val("");
	$("#statutory_name").val("");
	$("#map_column").val("");
	$("#input_column").val("");
	$("#function_name").val("");
	$('#input_column option:selected').removeAttr('selected');
	$("#map_statutory_name").val("");
	$("#loc_name").val("");
	$("#db_column").val("");
	call_select();
}

//payroll function edit part
function get_function_edit(payroll_function_id){
	empty_all();
	if(payroll_function_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_function_edit"); ?>',
			data: {payroll_function_id:payroll_function_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$("#payroll_function_id").val(rslt.edit_result.prime_payroll_function_id);
				$("#statutory_name").val(rslt.edit_result.statutory_name);
				$("#map_column").val(rslt.edit_result.map_column);
				$("#function_name").val(rslt.edit_result.function_name);
				if(rslt.edit_result.input_column){
					var input_column_options = rslt.edit_result.input_column.split(',');
					for(var i in input_column_options) {
						var optionVal = input_column_options[i];
						$("#input_column").find("option[value='"+optionVal+"']").prop("selected", "selected");
					}
				}
				call_select();
				$('.select2').on("select2:select", function (evt) {
				  var element = evt.params.data.element;
				  var $element = $(element); 			  
				  $element.detach();
				  $(this).append($element);
				  $(this).trigger("change");
				});
			}
		});
	}
}

//payroll function remove part
//delete formula
function remove_function(payroll_function_id){
	if(confirm("Are you sure to delete!")){
		if(payroll_function_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_payroll_function"); ?>',
				data: {payroll_function_id:payroll_function_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
						$("#function_list").html(rslt.function_list);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}

//allowclear is added to update to deselect of select.
// SELECT2 UI UPDATE
function call_select(){
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
//MRJ End 08-01-2018
</script>