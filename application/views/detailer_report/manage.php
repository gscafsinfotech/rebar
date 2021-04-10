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
					echo form_label("Employee Code/Name", 'employee_name', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'employee_name', 'id' => 'employee_name', 'class' => 'form-control input-sm select2' ), $employee_code_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Process Month", 'process_month', array('class' => 'required'));
					echo form_input(array( 'name' => 'process_month', 'id' => 'process_month', 'class' => 'form-control input-sm datepicker'));
				?>
			</div>
			<a id="link" style="display: none;" href="#" title='Export All Data'><span class="fa fa-user-exit">&nbsp</span></a>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="detailer_export">Search</button>
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
		var logged_role      = "<?php echo $logged_role;?>";
		var logged_emp_code  = "<?php echo $logged_emp_code;?>";
		if(parseInt(logged_role) === 5){
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
		$('#employee_name').autocomplete({
			source: function(request, response) {
				$.getJSON('<?php echo site_url("$controller_name/emp_suggest");?>',{term:request.term,role:logged_role,emp_code:logged_emp_code},response);
			},
				minChars:3,
				autoFocus: true,
				delay:10,
				scroll: true,
				appendTo: '#append_div',
				select: function(e, ui) {
					$('#employee_name').val(ui.item.value);
					return false;
			}
		});
		$('#detailer_export').click(function(){
			var employee_code 	= $("#employee_name").val();
			var process_month 	= $("#process_month").val();
			if(employee_code === ''){
				toastr.error("Employee Code Required");	
			}
			if(process_month === ''){
				toastr.error("Process Month Required");	
			}

			if(employee_code !== '' && process_month !== ''){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url("$controller_name/datacount_check"); ?>',
					data:{employee_code:employee_code,process_month:process_month,role:logged_role},
					success: function(data) {
						var rslt = JSON.parse(data);
						console.log(rslt.success);
						if(rslt.success){
							var export_excel 	= "<?php echo $excel_export;?>";
							var export_url   	= export_excel+'/'+employee_code+'/'+process_month;
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
</script>
<style>
	.btn-info{
		background: #3a28ac!important;
	}
</style>
<?php $this->load->view("partial/footer"); ?>