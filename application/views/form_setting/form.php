<style>
	.sortable {list-style-type:none;margin:0;padding:0;width: auto;}
	.sortable li{margin: 2px 20px 15px 0; padding: 8px; width: 23%; height: auto; font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px; cursor: pointer;display: inline-block;}
	.select2-container--default .select2-selection--single {
		border: 0px;
		border-radius: 0px;
		border-bottom: 1px solid #CCCCCC;
		padding: 4px 5px;
		display: none !important;
	}
	.form-control{
		text-transform: lowercase;
	}	
</style>
<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#form_view">Form view layout</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#input_view">Input view layout</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#table_view">Table view layout</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#manage_formula">Manage Condition & Formula</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#add_formula">Add Condition & Formula</a>
	</li>
	<li  role="presentation">
		<a data-toggle="tab" href="#pick_base_query">Pick Base Query</a>
	</li>
	<li  role="presentation">
		<a data-toggle="tab" href="#role_based_condition">Role Based Condition</a>
	</li>
  <?php if($prime_module_id=="employees") {?>
	<li role="presentation">
		<a data-toggle="tab" href="#monthly_sort">Monthly Input layout</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#payroll_sort">Payroll Table Sort</a>
	</li>
	<?php }?>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="form_view">
		<?php echo form_open('form_setting/add_ui/' . $prime_module_id,array('id'=>'add_ui_form','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('form_view_label_name'), 'form_view_label_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'form_view_label_name', 'id' => 'form_view_label_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('form_view_label_name'),'value' => ''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_form_view_id', 'id'=>'prime_form_view_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'view_previous_val', 'id'=>'view_previous_val', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'prime_view_module_id', 'id'=>'prime_view_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_label($this->lang->line('form_view_type'), 'form_view_type', array('class' => 'required'));
					$form_view_type_array =  array(""=>"---- Form view type ----",1=>"Block View",2=>"Tab View",3=>"Form with table");
					echo form_dropdown(array( 'name' => 'form_view_type', 'id' => 'form_view_type', 'class' => 'form-control input-sm'), $form_view_type_array);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('form_view_type_mode'), 'form_view_type_mode', array('class' => 'required'));
					$form_view_type_mode_array =  array(""=>"---- Show In ----",1=>"Block View",2=>"Tab View");
					echo form_dropdown(array( 'name' => 'form_view_type_mode', 'id' => 'form_view_type_mode', 'class' => 'form-control input-sm'), $form_view_type_mode_array);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('form_view_heading'), 'form_view_heading', array('class' => 'required'));
					echo form_input(array( 'name' => 'form_view_heading', 'id' => 'form_view_heading', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('form_view_heading'),'value' => ''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('form_view_for'), 'field_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'form_view_for[]','multiple id' =>'form_view_for','class' => 'form-control input-sm select2'), $field_for);
					echo "<label><input name='form_view_select' id='form_view_select' type='checkbox'> Select All</label>";
				?>
			</div>
			<div class="form-group">
				<label>
					<input name='form_view_show' id='form_view_show' type="checkbox" checked> Show Form
				</label>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="add_ui_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="add_ui_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style="padding:15px;background-color: #f2f2f2;" id="form_view_data">
			<p class='inline_topic'><i class="fa fa-hand-rock-o fa-2x" aria-hidden="true"></i> Drag and drop for align field postion</p>
			<ul id="view_sortable" class='sortable'>
				<?php
					foreach($view_setting as $setting){
						$count++;
						$prime_form_view_id   = $setting->prime_form_view_id;
						$prime_view_module_id = $setting->prime_view_module_id;
						$form_view_type       = $setting->form_view_type;
						$form_view_label_name = $setting->form_view_label_name;
						$form_view_heading    = ucwords($setting->form_view_heading);
						$form_view_sort       = $setting->form_view_sort;
						$form_view_show       = $setting->form_view_show;
						$form_view_for        = $setting->form_view_for;
						
						$form_view_type = $form_view_type_array[$form_view_type];
						$li_id = "li_".$prime_form_view_id;
						$a_id  = "a_".$prime_form_view_id."_$count";
						$show_icon = "<i class='fa fa-eye-slash' aria-hidden='true'></i>";
						if((int)$form_view_show === 1){
							$show_icon = "<i class='fa fa-eye' aria-hidden='true'></i>";
						}
						echo "<li class='ui-state-default' id='$li_id'>
								<table style='width:100%;'>
									<tr>
										<td style='font-weight:bold'>
											<label>$form_view_heading</label><br/>
											<span style='font-size:13px;font-weight:normal;color:#999999;'> $show_icon $form_view_type </span>
										</td>
										<td style='text-align:right;'>
											<a id='$a_id' class='prime_color' onclick=get_view_info('$prime_form_view_id','$a_id');><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i></a>
										</td>
									</tr>
								</table>
							</li>";
					}
				?>
			</ul>
		</div>
	</div>
	<div class="tab-pane fade" id="input_view">
		<?php echo form_open('form_setting/save/' . $prime_module_id,array('id'=>'form_setting_form','class'=>'form-inline')); ?>
			<fieldset id="FundBasicInfo">
				<?php
					echo form_input( array('name'=>'prime_form_id', 'id'=>'prime_form_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'prime_module_id', 'id'=>'prime_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_input( array('name'=>'common_table', 'id'=>'common_table', 'type'=>'Hidden','value'=>''));
				?>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('input_for'), 'input_for', array('class' => 'required'));
						echo form_dropdown(array( 'name' => 'input_for', 'id' => 'input_for', 'class' => 'form-control input-sm'), $input_for);
					?>
				</div>
				<div class="form-group">
					<?php
						//Used as a hidden input
						echo form_label($this->lang->line('field_isdefault'), 'field_isdefault', array('class' => 'required'));
						$field_isdefault =  array(""=>"---- Is Default ----",1=>"Yes",2=>"No");
						echo form_dropdown(array( 'name' => 'field_isdefault', 'id' => 'field_isdefault', 'class' => 'form-control input-sm'), $field_isdefault,1);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('field_type'), 'field_type', array('class' => 'required'));
						$field_type_array =  array(""=>"---- Field Type ----",1=>"Text",2=>"Decimals",3=>"Integer",4=>"Date",5=>"Picklist",6=>"Checkbox",7=>"Multi Picklist",8=>"summary box",9=>"Auto complete box",10=>"File upload box",11=>"Mobile Number",12=>"Email",13=>"Date & Time",14=>"Read Only");
						echo form_dropdown(array( 'name' => 'field_type', 'id' => 'field_type', 'class' => 'form-control input-sm'), $field_type_array);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('text_type'), 'text_type', array('class' => 'required'));
					$text_type = array(""=>"--- Select Text Type ---","1"=>"Only Text","2"=>"Text With numbers","3"=>"Only Numbers");
					echo form_dropdown(array( 'name' => 'text_type', 'id' =>'text_type', 'class' => 'form-control input-sm'), $text_type);
					?>
				</div>				
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('label_name'), 'label_name', array('class' => 'required'));
						echo form_input(array( 'name' => 'label_name', 'id' => 'label_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('label_name'),'value' => ''));
					?>
				</div>				
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('view_name'), 'view_name', array('class' => 'required'));
						echo form_input(array( 'name' => 'view_name', 'id' => 'view_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('view_name'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('short_name'), 'short_name', array('class' => 'required'));
						echo form_input(array( 'name' => 'short_name', 'id' => 'short_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('short_name'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('field_length'), 'field_length', array('class' => 'required'));
						echo form_input(array( 'name' => 'field_length', 'id' => 'field_length', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('field_length'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('field_decimals'), 'field_decimals', array('class' => 'required'));
						echo form_input(array( 'name' => 'field_decimals', 'id' => 'field_decimals', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('field_decimals'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('pick_list_type'), 'pick_list_type', array('class' => 'required'));
					$pick_list_type = array(""=>"--- Select pick list type ---","1"=>"From table","2"=>"Add new pick list");
					echo form_dropdown(array( 'name' => 'pick_list_type', 'id' =>'pick_list_type', 'class' => 'form-control input-sm'), $pick_list_type);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('pick_table'), 'pick_table', array('class' => 'required'));
						echo form_dropdown(array( 'name' => 'pick_table', 'id' => 'pick_table', 'class' => 'form-control input-sm'), $table_list);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('pick_table_col'), 'pick_table_col', array('class' => 'required'));
					$pick_table_col = array(""=>"--- Pick list value ---");
					echo form_dropdown(array( 'name' => 'pick_table_col[]', 'multiple id' =>'pick_table_col', 'class' => 'form-control input-sm'), $pick_table_col);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('pick_display_value'), 'pick_display_value', array('class' => 'required'));
					$pick_table_col = array(""=>"--- Auto display column ---");
					echo form_dropdown(array( 'name' => 'pick_display_value[]', 'multiple id' =>'pick_display_value', 'class' => 'form-control input-sm select2'), $pick_table_col);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('auto_prime_id'), 'auto_prime_id', array('class' => 'required'));
					$pick_table_col = array(""=>"--- Auto prime id column ---");
					echo form_dropdown(array( 'name' => 'auto_prime_id', 'id' =>'auto_prime_id', 'class' => 'form-control input-sm'), $pick_table_col);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('auto_dispaly_value'), 'auto_dispaly_value', array('class' => 'required'));
					$pick_table_col = array(""=>"--- Auto display column ---");
					echo form_dropdown(array( 'name' => 'auto_dispaly_value', 'id' =>'auto_dispaly_value', 'class' => 'form-control input-sm'), $pick_table_col);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('pick_list'), 'pick_list', array('class' => 'required'));
						$pick_list = array();
						echo form_dropdown(array( 'name' => 'pick_list[]', 'multiple id' =>'pick_list', 'class' => 'form-control input-sm  select2_user'), $pick_list);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('pick_list_import'), 'pick_list_import', array('class' => 'required'));
					$import_value = array(""=>"--- Select Import Value ---","1"=>"Auto Prime Id","2"=>"Display Value");
					echo form_dropdown(array( 'name' => 'pick_list_import', 'id' =>'pick_list_import', 'class' => 'form-control input-sm'), $import_value);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('default_value'), 'default_value', array('class' => ''));
						echo form_input(array( 'name' => 'default_value', 'id' => 'default_value', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('default_value'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('file_type'), 'file_type', array('class' => 'required'));
						$file_type = array(""=> "--- File type ----",".pdf,.xls,.xlsx,.csv,.docx,.txt" => "Document","audio/*" => "Audio","image/*" => "Image");
						echo form_dropdown(array('name' => 'file_type',' id' =>'file_type','class' => 'form-control input-sm'), $file_type);
					?>
				</div>
				<div class="form-group">
					<?php
					echo form_label($this->lang->line('upload_extension'), 'upload_extension', array('class' => 'required'));
					echo form_dropdown(array('name' => 'upload_extension[]','multiple id' =>'upload_extension','class' => 'form-control input-sm select2'), $upload_extension);
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('upload_file_size'), 'default_value', array('class' => ''));
						echo form_input(array( 'name' => 'upload_file_size', 'id' => 'upload_file_size', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('kb_size'),'value' => ''));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label($this->lang->line('field_for'), 'field_for', array('class' => 'required'));
						echo form_dropdown(array('name' => 'field_for[]','multiple id' =>'field_for','class' => 'form-control input-sm select2'), $field_for);
						echo "<label><input name='field_for_select' id='field_for_select' type='checkbox'> Select All</label>";
					?>
				</div>
				<div class="form-group" style='display:inline-grid;' id="checkbox_group_div">
					<label>
						<input name='mandatory_field' id='mandatory_field' type="checkbox"> Mandatory Field
					</label>
					<label>
						<input name='unique_field' id='unique_field' type="checkbox"> Unique Field
					</label>
					<label>
						<input name='field_show' id='field_show' type="checkbox" checked> Show Field
					</label>
					<label>
						<input name='table_show' id='table_show' type="checkbox"> Show Table View
					</label>
					<label>
						<input name='search_show' id='search_show' type="checkbox"> Show Search Filter
					</label>
          <label>
						<input name='edit_read' id='edit_read' type="checkbox"> Edit Readonly
					</label>
					<label>
						<input name='picklist_data' id='picklist_data' type="checkbox"> Picklist Validate
					</label>
					<label>
						<input name='duplicate_data' id='duplicate_data' type="checkbox"> Duplicate Validate
					</label>
				</div>
				<!-- <div class="form-group" <?php if($prime_module_id=="employees") {echo "";} else {echo "style='display:none'";}?>> 
					<?php
						//echo form_label($this->lang->line('transaction_type'), 'transaction_type', array('class' => 'required'));
						//$transaction_list = array(""=> "--- Transaction Type ----","1" => "Basic Information","2" => "Earning Information","3" => "Deduction Information","4" => "None");
						//echo form_dropdown(array('name' => 'transaction_type',' id' =>'transaction_type','class' => 'form-control input-sm'), $transaction_list);
					?>
				</div>
				<div class="form-group" style='display:inline-grid;' id="earnings_div">
					<label>
						<input name='gross_check' id='gross_check' type="checkbox"> Include for Gross Pay
					</label>
					<label>
						<input name='taxable_check' id='taxable_check' type="checkbox"> Taxable (Y/N)
					</label>
					<label>
						<input name='earn_month_check' id='earn_month_check' type="checkbox"> Monthly Input (Y/N)
					</label>
					<label>
						<input name='earn_payroll_check' id='earn_payroll_check' type="checkbox"> Payroll Display (Y/N)
					</label>
					<label>
						<input name='benefit_check' id='benefit_check' type="checkbox"> Benefit (Y/N)
					</label>
					<label>
						<input name='increment_check' id='increment_check' type="checkbox"> Increment (Y/N)
					</label>
					<label>
						<input name='arrear_pf_check' id='arrear_pf_check' type="checkbox"> Arrear PF (Y/N)
					</label>
					<label>
						<input name='fandf_check' id='fandf_check' type="checkbox"> F and F (Y/N)
					</label>
				</div>
				<div class="form-group" style='display:inline-grid;' id="deductions_div">
					<label>
						<input name='deduction_check' id='deduction_check' type="checkbox"> Include for Gross Deduction
					</label>
					<label>
						<input name='deduction_month_check' id='deduction_month_check' type="checkbox"> Monthly Input (Y/N)
					</label>
					<label>
						<input name='ded_payroll_check' id='ded_payroll_check' type="checkbox"> Payroll Display (Y/N)
					</label>
					<label>
						<input name='loan_check' id='loan_check' type="checkbox"> Loan
					</label>
					<label>
						<input name='uniform_check' id='uniform_check' type="checkbox"> Uniform
					</label>
				</div> -->
				<div class="form-group">
					<button class='btn btn-primary btn-sm' id="submit">Add/Update</button>
					<a class='btn btn-danger btn-sm' id="form_setting_cancel">Cancel</a>
				</div>
			</fieldset>
		<?php echo form_close(); ?>
		<div style="padding:15px;background-color: #f2f2f2;" id="input_view_data">
			<?php
				$form_view_rslt    = json_decode($update_form_viewui);
				$view_content      = $form_view_rslt->view_content;
				$id_array          = $form_view_rslt->id_array;
				$view_input_count  = $form_view_rslt->view_input_count;
				echo $view_content;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="table_view">
		<div style="padding:15px;background-color: #f2f2f2; overflow: auto !important;" id="table_view_data">
			<?php
				$table_view_rslt = json_decode($update_table_viewui);
				$table_content   = $table_view_rslt->table_content;
				echo $table_content;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="manage_formula">
		<?php echo form_open('form_setting/condition_formula/' . $prime_module_id,array('id'=>'condition_formula','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_cond_id', 'id'=>'prime_cond_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'cond_module_id', 'id'=>'cond_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					
					echo form_label($this->lang->line('condition_label_name'), 'condition_label_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'condition_label_name', 'id' => 'condition_label_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('condition_label_name'),'value' => ''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('condition_type'), 'condition_type', array('class' => 'required'));
					$condition_type = array(''=>"--- Condition Type ---",1=>"From Table",2=>"Write Condition");
					echo form_dropdown(array('name' => 'condition_type','id' =>'condition_type','class' => 'form-control input-sm'), $condition_type);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('condition_for'), 'condition_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'condition_for[]','multiple id' =>'condition_for','class' => 'form-control input-sm select2'), $field_for);
					echo "<label><input name='condition_for_select' id='condition_for_select' type='checkbox'> Select All</label>";
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('condition_check_form'), 'condition_check_form', array('class' => 'required'));
					echo form_dropdown(array('name' => 'condition_check_form[]','multiple id' =>'condition_check_form','class' => 'form-control input-sm select2'), $column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('condition_bind_to'), 'condition_bind_to', array('class' => 'required'));
					echo form_dropdown(array('name' => 'condition_bind_to[]','multiple id' =>'condition_bind_to','class' => 'form-control input-sm select2'), $column_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('condition_table'), 'condition_table', array('class' => 'required'));
					$condition_table = array(''=>"--- Condition Table ---",1=>"From Table",2=>"Write Condition");
					echo form_dropdown(array('name' => 'condition_table[]','multiple id' =>'condition_table','class' => 'form-control input-sm select2'), $table_list);
				?>
			</div>
			<div class="form-group">
				<label>
					<input name='is_drop_down' id='is_drop_down' type="checkbox">Is Drop down based condition
				</label>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('cond_drop_down'), 'cond_drop_down', array('class' => 'required'));
					echo form_dropdown(array('name' => 'cond_drop_down','id' =>'cond_drop_down','class' => 'form-control input-sm'), $column_list);
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="cond_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="cond_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style="padding:15px;" id="cond_view_data">
			<?php
				echo $cond_content;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="add_formula">
		<?php echo form_open('form_setting/get_add_cond_info/' . $prime_module_id,array('id'=>'get_add_cond_info','class'=>'form-inline','style'=>'background-color: #f2f2f2; padding: 8px 0px;')); ?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('add_cond_content'), 'add_cond_content', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'add_cond_content', 'id' => 'add_cond_content', 'class' => 'form-control input-sm'), $add_cond_content);
					echo form_input( array('name'=>'add_cond_module_id', 'id'=>'add_cond_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="view_cond_submit" style='margin-top:20px;'>View</button>
			</div>
		<?php echo form_close(); ?>
		<?php echo form_open('form_setting/add_condition_formula/' . $prime_module_id,array('id'=>'add_condition_formula','class'=>'form-inline')); ?>
			<!-- LOAD CONTENT FROM CONTROLLER -->
		<?php echo form_close(); ?>
	</div>
	<!-- UDY - START 13-02-2020 -->
	<div class="tab-pane fade" id="pick_base_query">
		<?php echo form_open('form_setting/save_pick_base_query',array('id'=>'save_pick_base_query','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_pick_base_search_id', 'id'=>'prime_pick_base_search_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'pick_module_id', 'id'=>'pick_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_label("Query For", 'pick_query_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'pick_query_for','id' =>'pick_query_for','class' => 'form-control input-sm'), $field_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Pick List", 'query_list_id', array('class' => 'required'));
					echo form_dropdown(array('name' => 'query_list_id','id' =>'query_list_id','class' => 'form-control input-sm'), $query_list_id);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Pick List column", 'query_column_list', array('class' => 'required'));
					echo form_dropdown(array('name' => 'query_column_list','id' =>'query_column_list','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Get Values From", 'values_from', array('class' => 'required'));
					$query_type_array = array(''=>"--- Select Get Value from ---","1"=>"Get From Picklist ","2"=>"Get From Session");
					echo form_dropdown(array('name' => 'values_from','id' =>'values_from','class' => 'form-control input-sm'), $query_type_array);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Table Values", 'table_values', array('class' => 'required'));
					echo form_dropdown(array('name' => 'table_values[]','multiple id' =>'table_values','class' => 'form-control input-sm select2'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Session Values", 'session_values', array('class' => 'required'));
					echo form_dropdown(array('name' => 'session_values','id' =>'session_values','class' => 'form-control input-sm'));
				?>
			</div>
			<div class="form-group">
				<a class='btn btn-edit btn-sm' id="apply_condition">Apply to Condition</a>
			</div>
			<br/>
			<div style='padding: 15px 0px; background-color: #efefef;'>
				<div class="form-group" style="width:75% !important;margin-bottom: 0px !important;">
					<textarea name='pick_where_condition' id='pick_where_condition' style='text-transform: lowercase !important;' class='form-control' rows='4'  placeholder='Write Condition with out where' >and</textarea>
				</div>
				<div class="form-group">
					<button class='btn btn-primary btn-sm' id="save_pick_base_query_btn">Add/Update</button>
				</div>
			</div>
		<?php echo form_close(); ?>
		<div style="padding:15px;" id='pick_query_list'>
			<?php echo $pick_query_list; ?>
		</div>
	</div>	
	<!-- UDY - END 13-02-2020 -->
	<!-- BSK - START 17-06-2020 -->
	<div class="tab-pane fade" id="role_based_condition">
		<?php echo form_open('form_setting/save_role_based_condition',array('id'=>'save_role_based_condition','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_input( array('name'=>'prime_role_based_condition_id', 'id'=>'prime_role_based_condition_id', 'type'=>'Hidden','value'=>0));
					echo form_input( array('name'=>'role_module_id', 'id'=>'role_module_id', 'type'=>'Hidden','value'=>$prime_module_id));
					echo form_label("Condition For", 'role_condition_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'role_condition_for[]','multiple id' =>'role_condition_for','class' => 'form-control input-sm select2'), $user_role_for);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Condition Type", 'user_condition_type', array('class' => 'required'));
					$user_condition_type = array(''=>"--- Condition Type ---","readonly"=>"Non Editable");
					echo form_dropdown(array('name' => 'user_condition_type','id' =>'user_condition_type','class' => 'form-control input-sm'), $user_condition_type);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Input Columns", 'input_columns', array('class' => 'required'));
					echo form_dropdown(array('name' => 'input_columns[]','multiple id' =>'input_columns','class' => 'form-control input-sm select2'), $column_list);
				?>
			</div>
			<div class="form-group">
					<button class='btn btn-primary btn-sm' id="save_user_role_condition_btn">Add/Update</button>
				</div>
			<br/>
		<?php echo form_close(); ?>
		<div style="padding:15px;" id='user_role_cond_list'>
			<?php echo $user_role_cond_list; ?>
		</div>
	</div>	
	<!-- BSK - END 18-06-2020 -->
	<div class="tab-pane fade" id="monthly_sort">
		<div style="padding:15px;background-color: #f2f2f2; overflow: auto !important;" id="monthly_sort_data">
			<?php
				$monthly_input_view_rslt = json_decode($update_monthly_input_viewui);
				$monthly_input_content   = $monthly_input_view_rslt->table_content;
				echo $monthly_input_content;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="payroll_sort">
		<div style="padding:15px;background-color: #f2f2f2; overflow: auto !important;" id="payroll_sort_data">
			<?php
				$payroll_view_rslt = json_decode($update_payroll_viewui);
				$payroll_content   = $payroll_view_rslt->table_content;
				echo $payroll_content;
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
  //Jai Sir Given the Changes Start
	//$("#field_isdefault").find("option[value='"+1+"']").prop("selected", "selected");
	$("#field_isdefault").val(1);
	$("#field_isdefault").parent().hide();
	$("#view_name").change(function(){
		var view_name = $('#view_name').val();
		if(view_name){
			$('#short_name').val(view_name);
		}
	});
	//Jai Sir Given the Changes End
	
	
	$("#earnings_div").hide();
	$("#deductions_div").hide();
	empty_all();
	call_select();
	call_sort();
	$("#form_view_select").click(function(){
		if($("#form_view_select").is(':checked') ){
			$("#form_view_for > option").prop("selected","selected");
			$("#form_view_for").trigger("change");
		}else{
			$("#form_view_for > option").removeAttr("selected");
			$("#form_view_for").trigger("change");
		}
    $('#form_view_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
		//$("#menu_for>option[value='']").removeAttr("selected");
	});
	$("#field_for_select").click(function(){
		if($("#field_for_select").is(':checked') ){
			$("#field_for > option").prop("selected","selected");
			$("#field_for").trigger("change");
		}else{
			$("#field_for > option").removeAttr("selected");
			$("#field_for").trigger("change");
		}
    $('#field_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
		//$("#menu_for>option[value='']").removeAttr("selected");
	});
	$("#condition_for_select").click(function(){
		if($("#condition_for_select").is(':checked') ){
			$("#condition_for > option").prop("selected","selected");
			$("#condition_for").trigger("change");
		}else{
			$("#condition_for > option").removeAttr("selected");
			$("#condition_for").trigger("change");
		}
    $('#condition_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
		//$("#menu_for>option[value='']").removeAttr("selected");
	});
	
	$.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 ]*$/i.test(value);
    }, "Must contain only letters and numbers");
	$.validator.addMethod("space_check", function(value, element) {
        return this.optional(element) || /^(\w+\s?)*\s*$/i.test(value);
    }, "Must contain single space");
		//not allowed single, double quotes in this filed
	$('#short_name').on('keypress', function (e) {
		var ingnore_key_codes = [34, 39];
		if ($.inArray(e.which, ingnore_key_codes) >= 0) {
			e.preventDefault();
		} 
	});
	/*============================*/
	/*-- UDY - START 13-02-2020 --*/
	/*============================*/	
	$("#query_column_list,#values_from,#table_values,#session_values").parent().hide();
	$("#query_list_id").change(function(){
		var query_list_id = $('#query_list_id').val();
		if(query_list_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_query_column_list"); ?>',
				data: {query_list_id:query_list_id},
				success: function(data){
					var rslt = JSON.parse(data);
					$('#query_column_list').empty();
					if(rslt.success){
						var column_option ="";
						$.each(rslt.column_list, function( key, value ) {
						  column_option += '<option value="' + key + '">' + value + '</option>';
						});
						$('#query_column_list').append(column_option);
						$('#query_column_list,#values_from').parent().show();
					}else{
						toastr.error(rslt.msg);
					}
				},
			});
		}
	});
	$("#query_column_list").change(function(){
		$("#values_from").val("");
	});
	$("#values_from").change(function(){
		var query_list_id     = $('#query_list_id').val();
		var query_column_list = $('#query_column_list').val();
		var values_from       = $('#values_from').val();
		$('#table_values').empty();
		$('#session_values').empty();
		$("#table_values,#session_values").parent().hide();
		if((query_column_list)&&(values_from)){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_session_table_value"); ?>',
				data: {query_list_id:query_list_id,query_column_list:query_column_list,values_from:values_from,},
				success: function(data){
					var rslt = JSON.parse(data);					
					if(rslt.success){
						if(values_from === "1"){
							var table_option ="";
							$.each(rslt.value_list, function( key, value ) {
							  table_option += '<option value="' + key + '">' + value + '</option>';
							});
							$('#table_values').append(table_option);
							$('#table_values').parent().show();
						}else
						if(values_from === "2"){
							var session_option ="";
							$.each(rslt.value_list, function( key, value ) {
							  session_option += '<option value="' + key + '">' + value + '</option>';
							});
							$('#session_values').append(session_option);
							$('#session_values').parent().show();
						}else{
							toastr.error(rslt.msg);
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
						toastr.error(rslt.msg);
					}
				},
			});
		}
	});
	
	$("#query_column_list,#values_from,#table_values,#session_values").parent().hide();
	$("#apply_condition").click(function(){
		var query_list_id     = $("#query_list_id").val();
		var query_column_list = $("#query_column_list").val();
		var values_from = $("#values_from").val();
		var fill_val = "";
		if(query_list_id){
			if(values_from === "1"){
				var table_values = $("#table_values").val();
				if(table_values){
					fill_val = query_column_list + " in(^"+table_values+"^) ";
				}else{
					toastr.error("Please select table value");
					return false;
				}
			}else
			if(values_from === "2"){
				session_values = $("#session_values").val();
				session_values = session_values.split('|');
				session_values = session_values[1];
				if(session_values){
					fill_val = query_column_list + " in(^@"+session_values+"@^)";
				}else{
					toastr.error("Please select session value");
					return false;
				}
			}else{
				toastr.error("Please select Pick List");
				return false;
			}
		}else{
			toastr.error("Please select Pick List");
			return false;
		}
		if(fill_val){
			where_condition = $("#pick_where_condition").val();
			fill_val = where_condition +" "+fill_val;
			$("#pick_where_condition").val(fill_val);
		}
	});
	jQuery.validator.addMethod("notEqual", function (value, element, param) { // Adding rules for Amount(Not equal to zero)
		return this.optional(element) || value != 'and';
	}, "Write Condition after and");
	$('#save_pick_base_query').validate($.extend({
		submitHandler: function (form){
			$("#save_pick_base_query_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_pick_base_query_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_pick_base_query_btn').attr('disabled',false);
					$("#save_pick_base_query_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						$("#pick_query_list").html(response.pick_query_list);
						document.getElementById("save_pick_base_query").reset();
						$("#prime_pick_base_search_id").val(0);
						$('#query_column_list').empty();
						$('#table_values').empty();
						$('#session_values').empty();
						$("#query_column_list,#values_from,#table_values,#session_values").parent().hide();
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			pick_query_for: "required",
			query_list_id: "required",
			pick_where_condition: {
				required:true,
				notEqual: true,
			}
		}
	}));
	/*===========================*/
	/*-- UDY - END 13-02-2020 --*/
	/*===========================*/
	/*===========================*/
	/*-- BSK - START 13-02-2020 --*/
	/*===========================*/
	$('#save_role_based_condition').validate($.extend({
		submitHandler: function (form){
			$("#save_user_role_condition_btn").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#save_user_role_condition_btn').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#save_user_role_condition_btn').attr('disabled',false);
					$("#save_user_role_condition_btn").html("Add/Update");
					if(response.success){
						toastr.success(response.message);
						$("#user_role_cond_list").html(response.user_role_list);
						document.getElementById("save_role_based_condition").reset();
						$("#prime_role_based_condition_id").val(0);
						$('#role_condition_for').empty();
						$('#user_condition_type').empty();
						$('#input_columns').empty();
					}else{
						toastr.error(response.message);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			pick_query_for: "required",
			query_list_id: "required",
			pick_where_condition: {
				required:true,
				notEqual: true,
			}
		}
	}));
	/*===========================*/
	/*-- BSK - END 18-06-2020 --*/
	/*===========================*/
	/* INPUT FORM SORTABLE - START */
	<?php
		foreach($id_array as $id){
			$site_url = site_url($controller_name . '/update_sortorder');
			$id_info  = $id;
			$ul_id    = "#$id";
			$li_id    = "#$id li";
			echo "var idsInOrder = [];\n
					$('$ul_id').sortable({
						update: function( event, ui ){
							idsInOrder = [];
							$('$li_id').each(function() {
							  idsInOrder.push($(this).attr('id'));
							});
							if(idsInOrder){
								prime_module_id = $('#prime_module_id').val();
								$.ajax({
									type: 'POST',
									url: '$site_url',
									data: {idsInOrder:idsInOrder,id_info:'$id_info'},
									success: function(data) {
										var rslt = JSON.parse(data);
										if(rslt.success){
											toastr.success(rslt.message);
										}
									},
								});
							}
						}
					});\n";
		}
	?>
	/* INPUT FORM SORTABLE - END */
	
	/* VIEW SORTABLE - START */
	var view_idsInOrder = [];
	$( "#view_sortable" ).sortable({
		update: function( event, ui ){
			view_idsInOrder = [];
			$('#view_sortable li').each(function() {
			  view_idsInOrder.push($(this).attr('id'));
			});
			if(view_idsInOrder){
				prime_view_module_id = $("#prime_view_module_id").val();
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_view_sortorder"); ?>',
					data: {view_idsInOrder:view_idsInOrder,prime_view_module_id:prime_view_module_id},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
						}
					},
				});
			}
		}
	});
	/* VIEW SORTABLE - END */
	
	/* TABLE SORTABLE - START */
		default_sortable();
	/* TABLE SORTABLE - END */
	
	/* MONTHLY INPUT SORTABLE - START */
		monthly_input_table_sortable();
	/* MONTHLY INPUT SORTABLE - END */
	
  	/* PAYROLL SORTABLE - START */
	   payroll_sortable();
	/* PAYROLL SORTABLE - END */
	
	/* VIEW FORM VIDATION - START */
	$('#add_ui_form').validate($.extend({
		submitHandler: function (form){
			$("#add_ui_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#add_ui_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#form_view_label_name').attr('readonly', false);
					$('#add_ui_submit').attr('disabled',false);
					$("#add_ui_submit").html("Add/Update");
					call_sort();
					empty_all_view();
					if(response.success){
						$("#form_view_data").html(response.view_setting);
						toastr.success(response.msg);
						//$('.modal').modal('toggle');
					}else{
						toastr.error(response.msg);
					}
          $('.modal').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			form_view_type: "required",
			form_view_label_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			form_view_heading: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			"form_view_for[]": "required",
		}
	}));
	$("#add_ui_cancel").click(function(){
		$('#form_view_label_name').attr('readonly', false);
		$('#add_ui_submit').attr('disabled',false);
		$("#add_ui_submit").html("Add/Update");
		empty_all_view();
	});
  
	//Earnings and deductions default updates --07MAY2019
	$("#input_for").change(function(){
		var check_val  = $("#input_for option:selected").text();
		if(check_val == "Earnings"){
			var transaction_type = $('#transaction_type').val(2);
			call_div_show(2);
		}else
		if(check_val == "Deductions"){
			var transaction_type = $('#transaction_type').val(3);
			call_div_show(3);
		}else{
			$('#transaction_type').val("");
			call_div_show();
		}
	});
		//mysql keywords is update based on label name changed.
	$("#label_name").change(function(){
		var label_name = $('#label_name').val();
		if(label_name){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/check_reserved_words"); ?>',
				data: {label_name:label_name},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						//toastr.success(rslt.msg);
					}else{
						toastr.error(rslt.msg);
						$('#label_name').val('');
					}
				}
			});
		}
	});

	/* INPUT FORM VIDATION - START */
	$('#form_setting_form').validate($.extend({
		submitHandler: function (form){
			$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#submit').attr('disabled','disabled');
			var field_type = $("#field_type").val();
			if((field_type === "2") || (field_type === "3") || (field_type === "5") || (field_type === "6") || (field_type === "7")){
				var default_value = $("#default_value").val();
				if(default_value === ""){
					$('#submit').attr('disabled',false);
					$("#submit").html("Submit");
					toastr.error("Please enter default value atleast 0");
					return false;
				}
			}			
			$(form).ajaxSubmit({
				success: function(response){
					$('#submit').attr('disabled',false);
					$("#submit").html("Add/Update");
					if(response.success){
						var from_rslt = JSON.parse(response.form_setting);
						$("#input_view_data").html(from_rslt.view_content);
						var table_rslt = JSON.parse(response.table_setting);
						$("#table_view_data").html(table_rslt.table_content);
						toastr.success(response.msg);
						<?php
							/*if((int)$view_input_count === 0){
								echo "$('.modal').modal('toggle');\n";
							}*/
						?>
					}else{
						toastr.error(response.msg);
						if(response.frm !== "exist"){
							$('.modal').modal('toggle');
						}
					}
					$('#field_type').attr('readonly', false);
					$('#label_name').attr('readonly', false);
					$('#field_isdefault').attr('readonly', false);
					$("#input_for").val("");
					
					$("#prime_form_id").val(0);
					$("#field_isdefault").val(1);
					$("#field_type").val("");
          			$("#transaction_type").val("");
					call_select();
					call_sort();
					empty_all();
					default_sortable();
					$('#notify_list_model').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			file_type: "required",
			input_for: "required",
			field_isdefault: "required",
			field_type: "required",
			label_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			view_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},

			pick_list_type: "required",
			pick_list_import: "required",
			"pick_display_value[]": "required",
			pick_table: "required",
			"field_for[]": "required",
			"pick_list[]": "required",
			"upload_extension[]": "required",
			field_length:{
				required: true,
				number: true,
				range:[10,255],
			},
			upload_file_size: "required",
			upload_file_size:{
				required: true,
				number: true,
				range:[1,3072],
			},
			text_type  : "required",
			field_decimals:{
				required: true,
				number: true,
				range:[2,5],
			},
			"pick_table_col[]":{
				required: true,
				minlength: 2,
				maxlength: 4,
			},
      transaction_type: "required",
		},
		messages: {
			"pick_table_col[]": "Select Atleast 2 options",
		}
	}));
	$("#form_setting_cancel").click(function(){
		$('#field_type').attr('readonly', false);
		$('#label_name').attr('readonly', false);
		$('#field_isdefault').attr('readonly', false);
		$("#input_for").val("");
		$('#submit').attr('disabled',false);
		$("#submit").html("Add/Update");
		$("#prime_form_id").val(0);
		$("#field_isdefault").val(1);
		$("#field_type").val("");
   		$("#transaction_type").val("");
		call_select();
		empty_all();
	});
	$('#condition_formula').validate($.extend({
		submitHandler: function (form){
			$("#cond_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#cond_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#cond_submit').attr('disabled',false);
					$("#cond_submit").html("Add/Update");
					update_condition_ui(0);
					empty_all_cond();
					if(response.success){
						$("#cond_view_data").html(response.cond_content);
						toastr.success(response.msg);
						//$('.modal').modal('toggle');
					}else{
						toastr.error(response.msg);
					}
          $('#notify_list_model').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			condition_label_name: "required",
			condition_type: "required",
			"condition_for[]": "required",
			"condition_check_form[]": "required",
			"condition_bind_to[]": "required",
			"condition_table[]": "required",
			cond_drop_down: "required",
			cond_drop_down_val: "required",
		}
	}));
	$('#get_add_cond_info').validate($.extend({
		submitHandler: function (form){
			$("#view_cond_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#view_cond_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$("#add_condition_formula").html(response.load_content);
					$('#view_cond_submit').attr('disabled',false);
					$("#view_cond_submit").html("View");
					$('#notify_list_model').modal('hide');
				},
				dataType: 'json'
			});
		},
		rules:{
			add_cond_content: "required",
		}
	}));
	$('#add_condition_formula').validate({
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
			$("select[name='line_input_for_table[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_input_for_col[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_input_bind_col[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			$("select[name='line_input_bind_col[]']").each(function() {
				if($(this).val() == "" && $(this).val().length < 1) {
					$(this).addClass('error');
					isValid = false;
				} else {
					$(this).removeClass('error');
				}
			});
			if(isValid) {
				$("#add_cond_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#add_cond_submit').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){
						$('#add_cond_submit').attr('disabled',false);
						$("#add_cond_submit").html("Add/Update");
						toastr.success("Mapping successfully added");
						$("#add_condition_formula").html("");
						$("#add_cond_content").val("");
						//$('.modal').modal('toggle');
						$('#notify_list_model').modal('hide');
					},
					dataType: 'json'
				});
			}else{
				toastr.error("Map all input and table");
			}
		}
	});
	$("#cond_cancel").click(function(){
		empty_all_cond();
		update_condition_ui("");
	});
	/* INPUT FORM VIDATION - END */
	
	/* ONCHANGE EVENTS - START */
	$('#form_view_type_mode').parent().hide();
	$("#form_view_type").change(function(){
		view_previous_val  = $("#view_previous_val").val();
		form_view_type     = $("#form_view_type").val();
		prime_form_view_id = $("#prime_form_view_id").val();
		if(form_view_type === "3"){	
			$('#form_view_type_mode').parent().show();
		}else{
			$('#form_view_type_mode').parent().hide();
		}
		if(view_previous_val === "3"){
			if(prime_form_view_id !== 0){
				$('#form_view_type').val(view_previous_val);
				toastr.error("Can't change to Tab or Block view");
			}
		}else
		if((view_previous_val === "1") || (view_previous_val === "2")){
			if(form_view_type === "3"){
				$('#form_view_type').val(view_previous_val);
				toastr.error("Can't change to From with table view");
			}
		}
	});
	
	//insert update search filter disable for upload input
	$("#field_type").change(function(){
		field_type      = $("#field_type").val();
		transaction_val = $("#transaction_type").val();
		update_ui(field_type);
		call_div_show(transaction_val);
		if((parseInt(field_type) === 2) || (parseInt(field_type) === 3) || (parseInt(field_type) === 6)){
			$("#default_value").val(0);
		}else
		if(parseInt(field_type) === 10){//search filter disable MRJ 12FEB2020
			$('#search_show').attr("disabled", true);
		}else{
			$('#search_show').attr("disabled", false);
		}
		if(parseInt(field_type) === 5){
			$('#unique_field').attr("disabled", true);
		}else{
			$('#unique_field').attr("disabled", false);
		}
	});
	
	$("#pick_list_type").change(function(){
		pick_list_type = $("#pick_list_type").val();
		update_pick_ui(pick_list_type);
	});
	
	$("#pick_table").change(function(){
		pick_table = $("#pick_table").val();
		field_type = $("#field_type").val();
		if(pick_table){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/get_table_info"); ?>',
				data: {pick_table:pick_table},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#pick_table_col').empty();
					var option ="";
					for(i = 0; i < rslt.colums_list.length; i++) {
						key   = rslt.colums_list[i].key;
						value = rslt.colums_list[i].value;
						option += '<option value="' + key + '">' + value + '</option>';
					}
					$('#pick_table_col').parent().show();
					$('#pick_table_col').append(option);
					$('#pick_display_value').empty();
					$('#pick_display_value').append(option);
					$('#pick_display_value').parent().show();
					if(field_type === "9"){
						$('#auto_prime_id').empty();
						$('#auto_prime_id').append(option);
						$('#auto_prime_id').parent().show();
				
						$('#auto_dispaly_value').empty();
						$('#auto_dispaly_value').append(option);
						$('#auto_dispaly_value').parent().show();
					}
				},
			});
		}
	});
	
	$('#condition_check_form,#condition_bind_to,#condition_for,#condition_table,#is_drop_down,#cond_drop_down,#cond_drop_down_val,#cond_submit').parent().hide();
	$("#condition_type").change(function(){
		condition_type = $("#condition_type").val();
		update_condition_ui(condition_type);
	});
	
	$('#is_drop_down').change(function () {
		$('#cond_drop_down').parent().hide();
		$("#cond_drop_down").val();
		if($('input[name="is_drop_down"]').is(':checked')){
			$('#cond_drop_down').parent().show();
		}
	});
	
	/* ONCHANGE EVENTS - END */
	//earnings and deductions div hide and show
	$('#transaction_type').change(function () {
		var transaction_val = $('#transaction_type').val();
		call_div_show(transaction_val);
	});
	$('#file_type').change(function(){
		var file_type = $('#file_type').val();
		get_extension_info(file_type);		
	});
	
	/* $('#field_show').click(function(){
		  var module_id  = $('#prime_module_id').val();
		  var label_name = $('#label_name').val();
          if($(this).prop("checked") == false){
			condition_check(module_id,label_name);
          }
     });*/
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

/* COMMON SORTABLE - START */
function call_sort(){
	var id_array = "<?php echo $id_array?>";
	$(function(){
		$( "#view_sortable" ).sortable();
		$( "#view_sortable" ).disableSelection();
		$( "#table_sortable" ).sortable();
		$( "#table_sortable" ).disableSelection();
		<?php
			foreach($id_array as $id){
				echo "$('#$id').sortable();\n $('#$id').disableSelection();\n";
			}
		?>
	});
}

// EMPTY ALL INPUT
function empty_all(){
	call_select();
	$("#prime_form_id").val(0);
	$("#label_name").val("");
	$("#view_name").val("");
	$("#short_name").val("");
	$("#field_length").val("");
	$("#text_type").val("");
	$("#field_decimals").val("");
	$("#pick_list_type").val("");
	$("#pick_list_import").val(2);
	$("#pick_table").val("");
	$("#pick_table_col").val("");
	$("#pick_display_value").val("");
	$("#pick_list").val("");
	$("#default_value").val("");
	$("#file_type").val("");
	$("#auto_prime_id").val("");
	$("#auto_dispaly_value").val("");
	$('#field_for_select').prop('checked', false);
	$('#mandatory_field').prop('checked', false);
	$('#unique_field').prop('checked', false);
	$('#field_show').prop('checked', true);
	$('#table_show').prop('checked', false);
	$('#search_show').prop('checked', false);
	$('#edit_read').prop('checked', false);
	$('#picklist_data').prop('checked', false);
	$('#duplicate_data').prop('checked', false);
	$('#field_for option:selected').removeAttr('selected');
	$('#upload_extension option:selected').removeAttr('selected');
	$('#label_name,#view_name,#short_name,#text_type,#field_length,#field_decimals,#pick_list_type,#pick_list_import,#pick_table,#pick_table_col,#pick_list,#default_value,#field_for,#file_type,#upload_extension,#auto_prime_id,#auto_dispaly_value,#pick_display_value,#upload_file_size').parent().hide();
	$('#checkbox_group_div').hide();
	//$("#transaction_type").val("");
	$('#gross_check').prop('checked', false);
	$('#taxable_check').prop('checked', false);
	$('#earn_month_check').prop('checked', false);
	$('#earn_payroll_check').prop('checked', false);
	$('#ded_payroll_check').prop('checked', false);
	$('#benefit_check').prop('checked', false);
	$('#increment_check').prop('checked', false);
	$('#arrear_pf_check').prop('checked', false);
	$('#fandf_check').prop('checked', false);
	$('#deduction_check').prop('checked', false);
	$('#deduction_month_check').prop('checked', false);
	$('#loan_check').prop('checked', false);
	$('#uniform_check').prop('checked', false);
	$("#earnings_div").hide();
	$("#deductions_div").hide();
}

function empty_all_view(){
	$("#form_view_for").val("");
	call_select();
	$("#prime_form_view_id").val(0);
	$("#form_view_type").val("");
	$("#form_view_label_name").val("");
	$("#form_view_heading").val("");
	$("#form_view_sort").val("");
	$('#form_view_show').prop('checked', true);
	$('#form_view_for option:selected').removeAttr('selected');
}


// UPDATE UI BASED ON FIELD TYPE
function update_ui(field_type){
	empty_all();
	//TEXT,INTEGER
	if((field_type === "1") || (field_type === "3") || (field_type === "11") || (field_type === "12") || (field_type === "14")){
		$('#label_name,#view_name,#short_name,#field_length,#default_value,#field_for').parent().show();
		$('#checkbox_group_div').show();
		if(field_type === "1"){
			$('#text_type').parent().show();
			$('#field_length').val(50);
		}else{
			$('#text_type').parent().hide();
			$('#field_length').val(10);
		}
	}else
	//DECIMALS
	if(field_type === "2"){
		$('#label_name,#view_name,#short_name,#field_decimals,#default_value,#field_for').parent().show();
		$('#checkbox_group_div').show();
		$('#field_decimals').val(2);
	}else
	//DATE,CHECKBOX,SUMMARY
	if((field_type === "4") || (field_type === "6") || (field_type === "8")|| (field_type === "13")){
		$('#label_name,#view_name,#short_name,#default_value,#field_for').parent().show();
		$('#checkbox_group_div').show();
	}else
	//DATE,CHECKBOX,SUMMARY
	if((field_type === "5") || (field_type === "7")){
		$('#label_name,#view_name,#short_name,#pick_list_type,#pick_list_import,#default_value,#field_for').parent().show();
		$('#checkbox_group_div').show();
	}else
	//AUTO COMPLETE
	if(field_type === "9"){
		$('#label_name,#view_name,#short_name,#pick_table,#default_value,#field_for').parent().show();
		$('#checkbox_group_div').show();
	}else
	//FILE UPLOAD
	if(field_type === "10"){
		$('#label_name,#view_name,#short_name,#default_value,#field_for,#file_type,#upload_extension,#upload_file_size').parent().show();
		$('#checkbox_group_div').show();
	}else{
		$('#label_name,#view_name,#short_name,#text_type,#field_length,#field_decimals,#pick_list_type,#pick_list_import,#pick_table,#pick_table_col,#pick_list,#default_value,#field_for,#file_type,#upload_extension,#auto_prime_id,#auto_dispaly_value,#pick_display_value,#upload_file_size').parent().hide();
		$('#checkbox_group_div').hide();
	}
	call_select();
}

// UPDATE UI BASED ON PICK LIST TYPE
function update_pick_ui(pick_list_type){
	if(pick_list_type === "1"){
		$('#pick_table_col,#pick_list').parent().hide();
		$('#pick_table,#pick_display_value').parent().show();
	}else
	if(pick_list_type === "2"){
		$('#pick_table,#pick_table_col,#pick_display_value').parent().hide();
		$('#pick_list').parent().show();
	}
	call_select();
}

// FORM EDIT OPERATION FOR ALL
function get_field_info(prime_form_id,a_id){
	prime_module_id = $("#prime_module_id").val();
	$("#"+a_id).html("<i class='fa fa-spinner fa-spin fa-2x' style='color:#CC3366'></i>");
	$.ajax({
		type: "POST",
		url: '<?php echo site_url($controller_name . "/get_field_info"); ?>',
		data: {prime_module_id:prime_module_id,prime_form_id:prime_form_id},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				update_ui(rslt.field_type);
				update_pick_ui(rslt.pick_list_type);
				update_field_info(rslt);
				
			}
			$("#"+a_id).html("<i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i>");
			var field_type      = $("#field_type").val();
			if(parseInt(field_type) === 5){
				$('#unique_field').attr("disabled", true);
			}else{
				$('#unique_field').attr("disabled", false);
			}
			var label_name      = $("#label_name").val();
			if(label_name === "last_working_date"){
				alert();
				$('#field_show').attr("disabled", true);
			}
		},
	});
}



// FORM UPDATE INPUT WITH VALUE FOR ALL
function update_field_info(rslt){
	if(rslt){
		$("#prime_form_id").val(rslt.field_info.prime_form_id);
		$("#prime_module_id").val(rslt.field_info.prime_module_id);
		$("#input_for").val(rslt.field_info.input_for);
		$("#field_type").val(rslt.field_info.field_type);
		$("#label_name").val(rslt.field_info.label_name);
		$("#view_name").val(rslt.field_info.view_name);
		$("#short_name").val(rslt.field_info.short_name);
		$("#field_length").val(rslt.field_info.field_length);
		$("#text_type").val(rslt.field_info.text_type);
		$("#field_decimals").val(rslt.field_info.field_decimals);
		$("#field_isdefault").val(rslt.field_info.field_isdefault);
		$("#default_value").val(rslt.field_info.default_value);
		$("#pick_list_type").val(rslt.field_info.pick_list_type);
		$("#pick_list_import").val(rslt.field_info.pick_list_import);
		$("#common_table").val(rslt.field_info.pick_table);
		$("#file_type").val(rslt.field_info.file_type);
		$("#transaction_type").val(rslt.field_info.transaction_type);
		$("#upload_file_size").val(rslt.field_info.upload_file_size);
				
		$('#mandatory_field').prop('checked', false);
		if(rslt.field_info.mandatory_field === "1"){
			$('#mandatory_field').prop('checked', true);
		}
		$('#unique_field').prop('checked', false);
		if(rslt.field_info.unique_field === "1"){
			$('#unique_field').prop('checked', true);
		}
		$('#field_show').prop('checked', false);
		if(rslt.field_info.field_show === "1"){
			$('#field_show').prop('checked', true);
		}
		$('#table_show').prop('checked', false);
		if(rslt.field_info.table_show === "1"){
			$('#table_show').prop('checked', true);
		}
		
		//search filter disable for upload file type MRJ-12FEB2020 start
		if(parseInt(rslt.field_info.field_type) === 10){
			$('#search_show').attr("disabled", true);
		}else{
			$('#search_show').attr("disabled", false);
		}
		//end MRJ-12FEB2020
		
		$('#search_show').prop('checked', false);
		if(rslt.field_info.search_show === "1"){
			$('#search_show').prop('checked', true);
		}
		
		$('#edit_read').prop('checked', false);
		if(rslt.field_info.edit_read === "1"){
			$('#edit_read').prop('checked', true);
		}
		
		$('#picklist_data').prop('checked', false);
		if(rslt.field_info.picklist_data === "1"){
			$('#picklist_data').prop('checked', true);
		}
		
		$('#duplicate_data').prop('checked', false);
		if(rslt.field_info.duplicate_data === "1"){
			$('#duplicate_data').prop('checked', true);
		}
		
		$('#gross_check').prop('checked', false);
		if(rslt.field_info.gross_check === "1"){
			$('#gross_check').prop('checked', true);
		}
		$('#taxable_check').prop('checked', false);
		if(rslt.field_info.taxable_check === "1"){
			$('#taxable_check').prop('checked', true);
		}
		$('#earn_month_check').prop('checked', false);
		if(rslt.field_info.earn_month_check === "1"){
			$('#earn_month_check').prop('checked', true);
		}
		$('#earn_payroll_check').prop('checked', false);
		if(rslt.field_info.earn_payroll_check === "1"){
			$('#earn_payroll_check').prop('checked', true);
		}
		$('#ded_payroll_check').prop('checked', false);
		if(rslt.field_info.ded_payroll_check === "1"){
			$('#ded_payroll_check').prop('checked', true);
		}
		$('#benefit_check').prop('checked', false);
		if(rslt.field_info.benefit_check === "1"){
			$('#benefit_check').prop('checked', true);
		}
		$('#increment_check').prop('checked', false);
		if(rslt.field_info.increment_check === "1"){
			$('#increment_check').prop('checked', true);
		}
		$('#arrear_pf_check').prop('checked', false);
		if(rslt.field_info.arrear_pf_check === "1"){
			$('#arrear_pf_check').prop('checked', true);
		}
		$('#fandf_check').prop('checked', false);
		if(rslt.field_info.fandf_check === "1"){
			$('#fandf_check').prop('checked', true);
		}
		$('#deduction_check').prop('checked', false);
		if(rslt.field_info.deduction_check === "1"){
			$('#deduction_check').prop('checked', true);
		}
		$('#deduction_month_check').prop('checked', false);
		if(rslt.field_info.deduction_month_check === "1"){
			$('#deduction_month_check').prop('checked', true);
		}
		$('#loan_check').prop('checked', false);
		if(rslt.field_info.loan_check === "1"){
			$('#loan_check').prop('checked', true);
		}
		$('#uniform_check').prop('checked', false);
		if(rslt.field_info.uniform_check === "1"){
			$('#uniform_check').prop('checked', true);
		}	
		
		if((rslt.field_info.pick_list_type === "1") || (rslt.field_info.field_type === "9")){
			$("#pick_table").val(rslt.field_info.pick_table);
			var option = "";
			for(i = 0; i < rslt.colums_list.length; i++) {
				key   = rslt.colums_list[i].key;
				value = rslt.colums_list[i].value;
				option += '<option value="' + key + '">' + value + '</option>';
		
			}
			$('#pick_table_col').empty();
			$('#pick_table_col').append(option);
			$('#pick_table_col').parent().show();
			$('#pick_display_value').empty();
			$('#pick_display_value').append(option);
			$('#pick_display_value').parent().show();
			if(rslt.field_info.field_type === "9"){
				$('#auto_prime_id').empty();
				$('#auto_prime_id').append(option);
				$('#auto_prime_id').parent().show();
				$("#auto_prime_id").val(rslt.field_info.auto_prime_id);
		
				$('#auto_dispaly_value').empty();
				$('#auto_dispaly_value').append(option);
				$('#auto_dispaly_value').parent().show();
				$("#auto_dispaly_value").val(rslt.field_info.auto_dispaly_value);
			}
	
			var selectedOptions = rslt.field_info.pick_list.split(",");
			for(var i in selectedOptions) {
				var optionVal = selectedOptions[i];
				$("#pick_table_col").find("option[value='"+optionVal+"']").prop("selected", "selected");
			}
		}else{
			if(rslt.field_info.pick_list){
				$('#pick_list').empty();
				var selectedOptions = rslt.field_info.pick_list.split(",");
				for(var i in selectedOptions){
					var optionVal = selectedOptions[i];
					$('#pick_list').append('<option value="' + optionVal + '">' + optionVal + '</option>');
					$("#pick_list").find("option[value='"+optionVal+"']").prop("selected", "selected");
				}
			}
		}
		var field_for_options = rslt.field_info.field_for.split(',');
		for(var i in field_for_options) {
			var optionVal = field_for_options[i];
			$("#field_for").find("option[value='"+optionVal+"']").prop("selected", "selected");
		}
		if(rslt.field_info.pick_display_value){
			var pick_display_value_options = rslt.field_info.pick_display_value.split(',');
			for(var i in pick_display_value_options) {
				var optionVal = pick_display_value_options[i];
				$("#pick_display_value").find("option[value='"+optionVal+"']").prop("selected", "selected");
			}
		}
		if(rslt.field_info.upload_extension){
			var upload_extension_options = rslt.field_info.upload_extension.split(',');
			for(var j in upload_extension_options){
				var optionVal = upload_extension_options[j];
				$("#upload_extension").find("option[value='"+optionVal+"']").prop("selected", "selected");
			}
		}
		
		$('#field_isdefault').attr('readonly', 'true');
		$('#field_type').attr('readonly', 'true');
		$('#label_name').attr('readonly', 'true');
		call_select();
		var transaction_val = rslt.field_info.transaction_type;
		if(transaction_val){
			call_div_show(transaction_val);
		}
		
	}
}

// VIEW EDIT OPERATION FOR ALL
function get_view_info(prime_form_view_id,a_id){
	prime_view_module_id = $("#prime_view_module_id").val();
	$("#"+a_id).html("<i class='fa fa-spinner fa-spin fa-2x' style='color:#CC3366'></i>");
	$.ajax({
		type: "POST",
		url: '<?php echo site_url($controller_name . "/get_view_info"); ?>',
		data: {prime_view_module_id:prime_view_module_id,prime_form_view_id:prime_form_view_id},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				update_view_info(rslt);
				form_view_type     = $("#form_view_type").val();
				if(form_view_type === "3"){	
					$('#form_view_type_mode').parent().show();
				}else{
					$('#form_view_type_mode').parent().hide();
				}
			}
			$("#"+a_id).html("<i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i>");
		},
	});
}

// UPDATE CONDITION UI
function empty_all_cond(){
	$("#prime_cond_id").val(0);
	$("#condition_label_name").val("");
	$("#condition_type").val("");
	$("#condition_for").val("");
	$("#condition_check_form").val("");
	$("#condition_bind_to").val("");
	$("#condition_table").val("");
	$('#condition_for option:selected').removeAttr('selected');
	$('#condition_check_form option:selected').removeAttr('selected');
	$('#condition_bind_to option:selected').removeAttr('selected');
	$('#condition_table option:selected').removeAttr('selected');
	$('#is_drop_down').prop('checked', true);
	$("#cond_drop_down").val("");
	call_select();
}
function update_condition_ui(condition_type){
	$('#condition_check_form,#condition_bind_to,#condition_for,#condition_table,#is_drop_down,#cond_drop_down,#cond_submit').parent().hide();
	if(condition_type === "1"){
		$('#condition_check_form,#condition_bind_to,#condition_for,#condition_table,#cond_submit').parent().show();
	}else
	if(condition_type === "2"){
		$('#condition_check_form,#condition_bind_to,#condition_for,#is_drop_down,#cond_submit').parent().show();
	}
	call_select();
}

// FORM UPDATE INPUT WITH VALUE FOR ALL
function update_view_info(rslt){
	if(rslt){
		$("#prime_form_view_id").val(rslt.view_info.prime_form_view_id);
		$("#prime_view_module_id").val(rslt.view_info.prime_view_module_id);
		$("#form_view_type").val(rslt.view_info.form_view_type);
		$("#form_view_type_mode").val(rslt.view_info.form_view_type_mode);
		$("#view_previous_val").val(rslt.view_info.form_view_type);
		$("#form_view_label_name").val(rslt.view_info.form_view_label_name);
		$("#form_view_heading").val(rslt.view_info.form_view_heading);
		$("#form_view_sort").val(rslt.view_info.form_view_sort);
		$('#form_view_show').prop('checked', false);
		if(rslt.view_info.form_view_show === "1"){
			$('#form_view_show').prop('checked', true);
		}
		var field_for_options = rslt.view_info.form_view_for.split(',');
		for(var i in field_for_options) {
			var optionVal = field_for_options[i];
			$("#form_view_for").find("option[value='"+optionVal+"']").prop("selected", "selected");
		}
		$('#form_view_label_name').attr('readonly', 'true');
		call_select();
	}
}
function get_cond_info(prime_cond_id){
	cond_module_id = $("#cond_module_id").val();
	if(prime_cond_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_cond_info"); ?>',
			data: {prime_cond_id:prime_cond_id,cond_module_id:cond_module_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				$("#prime_cond_id").val(rslt.cond_info.prime_cond_id);
				$("#cond_module_id").val(rslt.cond_info.cond_module_id);
				$("#condition_label_name").val(rslt.cond_info.condition_label_name);
				$("#condition_type").val(rslt.cond_info.condition_type);
				update_condition_ui(rslt.cond_info.condition_type);
				if(rslt.cond_info.condition_for){
					var condition_for = rslt.cond_info.condition_for.split(",");
					for(var i in condition_for) {
						var condition_for_val = condition_for[i];
						$("#condition_for").find("option[value='"+condition_for_val+"']").prop("selected", "selected");
					}
				}
				if(rslt.cond_info.condition_check_form){
					var condition_check_form = rslt.cond_info.condition_check_form.split(",");
					for(var i in condition_check_form) {
						var condition_check_form_val = condition_check_form[i];
						$("#condition_check_form").find("option[value='"+condition_check_form_val+"']").prop("selected", "selected");
					}
				}
				if(rslt.cond_info.condition_bind_to){
					var condition_bind_to = rslt.cond_info.condition_bind_to.split(",");
					for(var i in condition_bind_to) {
						var condition_bind_to_val = condition_bind_to[i];
						$("#condition_bind_to").find("option[value='"+condition_bind_to_val+"']").prop("selected", "selected");
					}
				}
				if(rslt.cond_info.condition_table){
					var condition_table = rslt.cond_info.condition_table.split(",");
					for(var i in condition_table) {
						var condition_table_val = condition_table[i];
						$("#condition_table").find("option[value='"+condition_table_val+"']").prop("selected", "selected");
					}
				}
				if(rslt.cond_info.is_drop_down === "1"){
					$('#is_drop_down').prop('checked', true);
					$('#cond_drop_down').parent().show();
				}else{
					$('#cond_drop_down').parent().hide();
					$('#is_drop_down').prop('checked', false);
					$("#cond_drop_down").val();
				}
				$("#cond_drop_down").val(rslt.cond_info.cond_drop_down);
				call_select();
			},
		});
	}
}
function remove_cond(prime_cond_id){
	if(confirm("Are you sure to delete!")){
		cond_module_id = $("#cond_module_id").val();
		if(prime_cond_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_cond"); ?>',
				data: {prime_cond_id:prime_cond_id,cond_module_id:cond_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					$("#cond_view_data").html(rslt.cond_content);
					update_condition_ui(0);
					empty_all_cond();
				},
			});
		}
	}
}
//NEHA EDIT 27JUNE2020
 function get_id(val){
	con_column_input        = "con_column_input_"+val;
	line_input_bind_col     = "line_input_bind_col_"+val;
	con_column_input_val    = $("#"+con_column_input).val();
	line_input_bind_col_val = $("#"+line_input_bind_col).val();
	check_result            = "@"+con_column_input_val+"@";
	start                   = $("#"+line_input_bind_col).prop("selectionStart");
	output                  = [line_input_bind_col_val.slice(0, start), check_result, line_input_bind_col_val.slice(start)].join('');
	$("#"+line_input_bind_col).val(output);
} 

/* function get_id(val){
	con_column_input        = "con_column_input_"+val;
	line_input_bind_col     = "line_input_bind_col_"+val;
	con_column_input_val    = $("#"+con_column_input).val();
	line_input_bind_col_val = $("#"+line_input_bind_col).val();
	fill_val = line_input_bind_col_val + "@" +con_column_input_val+ "@";
	$("#"+line_input_bind_col).val(fill_val);
} */ 

function call_div_show(transaction_val){
	if((parseInt(transaction_val) === 1) || (parseInt(transaction_val) === 2)){
		$('#earnings_div').show();
		$('#deductions_div').hide();
		$('#earn_payroll_check').prop('checked', true);
	}else
	if(parseInt(transaction_val) === 3){
		$('#deductions_div').show();
		$('#earnings_div').hide();
		$('#ded_payroll_check').prop('checked', true);
	}else{
		$('#earn_payroll_check').prop('checked', false);		
		$('#ded_payroll_check').prop('checked', false);		
		$('#earnings_div').hide();
		$('#deductions_div').hide();
	}
}
/*============================*/
/*-- UDY - START 13-02-2020 --*/
/*============================*/
function edit_pick_query(prime_pick_base_search_id){
	if(prime_pick_base_search_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_edit_pick_query"); ?>',
			data: {prime_pick_base_search_id:prime_pick_base_search_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#prime_pick_base_search_id").val(rslt.prime_pick_base_search_id);
					$("#pick_query_for").val(rslt.pick_query_for);
					$("#query_list_id").val(rslt.query_list_id);
					$("#pick_where_condition").val(rslt.pick_where_condition);
					
					$('#query_column_list').empty();
					var column_option ="";
					$.each(rslt.column_list, function( key, value ) {
					  column_option += '<option value="' + key + '">' + value + '</option>';
					});
					$('#query_column_list').append(column_option);
					$('#query_column_list,#values_from').parent().show();
				}else{
					toastr.error(rslt.message);
				}
			},
		});
	}
}
function remove_pick_query(prime_pick_base_search_id){
	pick_module_id = $("#pick_module_id").val();
	if(confirm("Are you sure to delete! uday")){
		if(prime_pick_base_search_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_pick_query"); ?>',
				data: {prime_pick_base_search_id:prime_pick_base_search_id,pick_module_id:pick_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#pick_query_list").html(rslt.pick_query_list);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}
/*==========================*/
/*-- UDY - END 13-02-2020 --*/
/*==========================*/
/*==========================*/
/*-- BSK - START 17-06-2020 --*/
/*==========================*/
function edit_role_based(prime_role_base_condition_id){
	if(prime_role_base_condition_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_edit_role_based"); ?>',
			data: {prime_role_base_condition_id:prime_role_base_condition_id},
			success: function(data) {
				var rslt = JSON.parse(data);
				if(rslt.success){
					$("#prime_role_based_condition_id").val(rslt.prime_role_base_condition_id);
					if(rslt.role_condition_for){
						var condition_for = rslt.role_condition_for.split(",");
						for(var i in condition_for) {
							var condition_for_val = condition_for[i];
							$("#role_condition_for").find("option[value='"+condition_for_val+"']").prop("selected", "selected");
						}
					}
					$("#user_condition_type").val(rslt.user_condition_type);
					if(rslt.input_columns){
						var input_columns = rslt.input_columns.split(",");
						for(var i in input_columns) {
							var input_columns_val = input_columns[i];
							$("#input_columns").find("option[value='"+input_columns_val+"']").prop("selected", "selected");
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
function remove_role_based(prime_role_base_condition_id){
	role_module_id = $("#role_module_id").val();
	if(confirm("Are you sure to delete!")){
		if(prime_role_base_condition_id){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/remove_role_based"); ?>',
				data: {prime_role_base_condition_id:prime_role_base_condition_id,role_module_id:role_module_id},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#user_role_cond_list").html(rslt.user_role_list);
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				},
			});
		}
	}
}
/*==========================*/
/*-- BSK - END 18-06-2020 --*/
/*==========================*/
//BSK - START
function get_extension_info(file_type){
	if(file_type){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_extension_info"); ?>',
			data: {file_type:file_type},
			success: function(data){
				$('#upload_extension').html(data);						
			},
		});
	}
}

//SORTABLE DEFAULT TABLE 
function default_sortable(){
	var table_idsInOrder = [];
	$( ".default_table" ).sortable({
		update: function( event, ui ){
			table_idsInOrder = [];
			$('#table_sortable tr > th').each(function() {
				table_idsInOrder.push($(this).attr('id'));
			});
			if(table_idsInOrder){
				prime_module_id = $("#prime_module_id").val();
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_table_sortorder"); ?>',
					data: {table_idsInOrder:table_idsInOrder,prime_module_id:prime_module_id},
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
	var prime_module_id = '<?php echo $prime_module_id; ?>';
	if(prime_module_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_table_view_data"); ?>',
			data: {prime_module_id:prime_module_id},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){
					$('#table_view_data').html(rslt.table_content);
					default_sortable();
				}					
			}
		});
	}
}

//PAYROLL TABLE SORTABLE
function payroll_sortable(){
	var table_idsInOrder = [];
	$( ".payroll_table" ).sortable({
		update: function( event, ui ){
			table_idsInOrder = [];
			$('#payroll_sortable th').each(function() {
			  table_idsInOrder.push($(this).attr('id'));
			});			
			if(table_idsInOrder){
				prime_module_id = $("#prime_module_id").val();
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_payroll_sortorder"); ?>',
					data: {table_idsInOrder:table_idsInOrder,prime_module_id:prime_module_id},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
							get_payroll_table_view_data();
						}
					},
				});
			}
		},connectWith: '.payroll_table'
	});
}

//GET PAYROLL TABLE UI
function get_payroll_table_view_data(){
	var prime_module_id = '<?php echo $prime_module_id; ?>';
	if(prime_module_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_payroll_table_view_data"); ?>',
			data: {prime_module_id:prime_module_id},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){
					$('#payroll_sort_data').html(rslt.table_content);
					payroll_sortable();
				}					
			}
		});
	}
}

//MONTHLY INPUT TABLE SORTABLE
function monthly_input_table_sortable(){
	var table_idsInOrder = [];
	$( ".monthly_input" ).sortable({
		update: function( event, ui ){
			table_idsInOrder = [];
			$('#monthly_sortable th').each(function() {
			  table_idsInOrder.push($(this).attr('id'));
			});
			
			if(table_idsInOrder){
				prime_module_id = $("#prime_module_id").val();
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_monthly_sortorder"); ?>',
					data: {table_idsInOrder:table_idsInOrder,prime_module_id:prime_module_id},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
							get_monthly_input_table_view_data();
						}
					},
				});
			}
		},connectWith: '.monthly_input'
	});
}

//GET MONTHLY INPUT TABLE UI
function get_monthly_input_table_view_data(){
	var prime_module_id = '<?php echo $prime_module_id; ?>';
	if(prime_module_id){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/get_monthly_input_table_view_data"); ?>',
			data: {prime_module_id:prime_module_id},
			success: function(data){
				var rslt = JSON.parse(data);
				if(rslt.success){
					$('#monthly_sort_data').html(rslt.table_content);
					monthly_input_table_sortable();
				}					
			}
		});
	}
}

/*function condition_check(module_id,label_name){
	if(label_name){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url($controller_name . "/check_condition_exit"); ?>',
			data: {module_id:module_id,label_name:label_name},
			success: function(data){
				var rslt = JSON.parse(data);
									
			}
		});	
	}
}*/
</script>
<style type="text/css">
	td{
		word-break: break-word;
	}
</style>