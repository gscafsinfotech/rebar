<?php 
	$this->load->view("partial/header");
	$page_name      = ucwords(str_replace("_"," ",$controller_name));
	$excel_export   = site_url().'/'.$controller_name.'/excel_export';
	$logged_role 	= $this->session->userdata('logged_role');
	$logged_emp_code = $this->session->userdata('logged_emp_code');
?>
<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
</div>
<div class="form-inline" style="margin-top:20px;">
	<div class="row" style='margin-bottom:0px;'>
		<div class="col-md-9">
			<div class="form-group">
				<?php
					echo form_label("Process Month", 'process_month', array('class' => 'required'));
					echo form_input(array( 'name' => 'process_month', 'id' => 'process_month', 'class' => 'form-control input-sm datepicker'));
				?>
			</div>
			<div class="form-group" style="z-index: 999;">
		<?php
			// if($access_search === 1){
		?>
		<a class="btn btn-sm btn-edit" id="search_filter">
			<i class="fa fa-filter" aria-hidden="true"></i> Search filter
			<span class="caret"></span>
		</a>
		<div id="search_filter_div" class='search_filter'>
			<div style="max-height:250px;overflow: auto;">
				<?php
					$filter_cond_array = array('' => '--- Select ---','=' => '=','>' => '>','<' => '<','LIKE' => 'LIKE');
						$tr_line = "";
						foreach($fliter_list as $fliter){
							$label_id         = $fliter['label_id'];
							$field_isdefault  = $fliter['field_isdefault'];
							$array_list       = $fliter['array_list'];
							$field_type       = $fliter['field_type'];
							$label_name = ucwords(strtolower(str_replace("_"," ",$label_id)));
							$fliter_label = form_input(array('type'=>'hidden','name' => 'fliter_label[]', 'class' => 'form-control input-sm','value' => $label_id));
							$fliter_type  = form_input(array('type'=>'hidden','name' => 'fliter_type[]', 'class' => 'form-control input-sm','value' => $field_isdefault));
							$filter_cond  = form_dropdown(array('name' => 'filter_cond[]','class' => 'form-control input-sm'), $filter_cond_array);
							// $input_field_type = form_input(array('type' => 'hidden','name' => 'input_field_type[]','class' => 'form-control input-sm datepicker', 'placeholder'=>'Select Date','value' => $field_type));
							$input_field_type = form_input(array('type' => 'hidden','name' => 'field_type[]','class' => 'form-control input-sm', 'placeholder'=>'','value' => $field_type));
							// echo "input_field_type :: $input_field_type<br>";
							if(((int)$field_type === 5)){
								$fliter_val  = form_dropdown(array('name' => 'fliter_val[]','class' => 'form-control input-sm'), $array_list);
							}else
							if((int)$field_type === 7){
								$fliter_val =  form_dropdown(array( 'name' => 'fliter_val[]', 'multiple id' => 'fliter_val', 'class' => 'form-control input-sm select2'), $array_list);

								$fliter_val_multi = form_input(array('type'=>'hidden','id' => 'fliter_val_multi','name' => 'fliter_val_multi[]','class' => 'form-control input-sm', 'placeholder'=>'','value' => ''));
							}else
							if((int)$field_type === 4){
								$fliter_val   = form_input(array( 'name' => 'fliter_val[]', 'class' => 'form-control input-sm datepicker', 'placeholder'=>'Select Date','value' => ''));
							}else{
								$fliter_val   = form_input(array( 'name' => 'fliter_val[]', 'class' => 'form-control input-sm', 'placeholder'=>'Search value','value' => ''));
							}
							$tr_line .= "<tr>
											<td class='search_td'> $input_field_type $label_name $fliter_label $fliter_type</td>
											<td> $filter_cond $fliter_val_multi</td>
											<td> $fliter_val </td>
										</tr>";
						}
						echo "<table style='width:100%;'>$tr_line</table>";
				?>				
			</div>
			<div style="margin-top:8px;">
				<div class="row">
					<div class="col-md-6" style='text-align:left;'>
						<a class="btn btn-xs btn-danger" id="clear_search"> Clear / Close</a>
					</div>
					<div class="col-md-6" style='text-align:right;'>
						<a class="btn btn-xs btn-primary" id="search_submit">Done</a>
					</div>
				</div>
			</div>
		</div>
		<?php 
			// }
		?>
	</div>
			<a id="link" style="display: none;" href="#" title='Export All Data'><span class="fa fa-user-exit">&nbsp</span></a>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="co_register_export">Search</button>
			</div>
		</div>
	</div>
</div>
<div class="row" style='margin: 0px;'>	
	<div class='col-md-12' style='margin:10px;padding:10px;' id="rslt_info">
	</div>
</div>
<script src="dist/daterangepicker/knockout.js" type="text/javascript"></script>
<link href="dist/daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<script src="dist/daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function (){
		$("#search_submit").click(function(){
			$("#search_filter_div").toggle();
		});
		select();
		$("#pdf_block_employees").parent().hide();
		$("#search_filter_div").hide();
		$("#search_filter").click(function(){
			$("#search_filter_div").toggle();
		});	
		$("#clear_search").click(function(){
			$('#search_filter_div').find('input').val('');
			$('#search_filter_div').find('option').attr('selected', false);
			$("#search_filter_div").toggle();
			select();
		});

		var logged_role      = "<?php echo $logged_role;?>";
		var logged_emp_code  = "<?php echo $logged_emp_code;?>";
		if(parseInt(logged_role) === 4){
			$("#employee_name").find("option[value='"+logged_emp_code+"']").prop("selected", "selected");
			$("#employee_name").prop('readonly', true);
		}
		$(function(){
			$(".datepicker").datetimepicker({
				format: 'MM-YYYY',
			});
		});
		$(function(){
			$('.select2').select2({
				placeholder: '---- Select ----',
				allowClear: true,
			});
		});
		
		$(".daterangepicker-field").daterangepicker({
			locale: { inputFormat: 'DD/MM/YYYY' },
			forceUpdate: true,
			callback: function(startDate, endDate, period){
				var title = startDate.format('DD/MM/YYYY') + ' – ' + endDate.format('DD/MM/YYYY');
				$(this).val(title);
				start_date = startDate.format('YYYY-MM-DD');
				end_date   = endDate.format('YYYY-MM-DD');
			}
		});
		$(".select2").on('change', function (e) {
			var fliter_val_multi = $(this).val().length;
			console.log(fliter_val_multi);
			$("#fliter_val_multi").val(fliter_val_multi);

		});
		$('#co_register_export').click(function(){
			var employee_code 	= $("#employee_name").val();
			var process_month 	= $("#process_month").val();
			if(employee_code === ''){
				toastr.error("Employee Code Required");	
			}
			if(process_month === ''){
				toastr.error("Process Month Required");	
			}


			var fliter_label       =  $("input[name='fliter_label[]']").map(function(){return $(this).val();}).get();
			var fliter_type        =  $("input[name='fliter_type[]']").map(function(){return $(this).val();}).get();
			var field_type   	   =  $("input[name='field_type[]']").map(function(){return $(this).val();}).get();
			var filter_cond        =  $("select[name='filter_cond[]']").map(function(){return $(this).val();}).get();
			var fliter_val         =  $("input[name='fliter_val[]'],select[name='fliter_val[]']").map(function(){return $(this).val();}).get();
			var multipick_val 	   = $("#fliter_val_multi").val();
			var multipick_val = parseInt(multipick_val);


			if(employee_code !== '' && process_month !== ''){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url("$controller_name/datacount_check"); ?>',
					data:{employee_code:employee_code,process_month:process_month,role:logged_role,fliter_label:fliter_label,fliter_type:fliter_type,field_type:field_type,filter_cond:filter_cond,fliter_val:fliter_val,multipick_val:multipick_val},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							var export_excel 	= "<?php echo $excel_export;?>";
							var export_url   	= export_excel+'/'+process_month+'/'+fliter_label+'/'+fliter_type+'/'+field_type+'/'+filter_cond+'/'+fliter_val+'/'+multipick_val;
							$('#link').attr("href",export_url);
							window.location = $('#link').attr('href');
						}else{
							toastr.error(rslt.message);							
						}
					}
				
				});
			}
		});
	});
	function empty_all(){
		$('.select2').select2({
			placeholder: '---- Select ----',
			allowClear: true,
		});
	}
	function select(){
		$(".datepicker").datetimepicker({
			format: 'MM-YYYY'
		});
		$("#payslip_month").datetimepicker({
			format: 'MM-YYYY'
		});
		$('.select2').select2({
			placeholder: '---- Select ----',
			allowClear: true,
		});
		$('.select2-tags').select2({
			tags: true,
			tokenSeparators: [',']
		});
	}
</script>
<style>
	.btn-info{
		background: #3a28ac!important;
	}
</style>
<?php $this->load->view("partial/footer"); ?>