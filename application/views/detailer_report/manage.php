<?php 
	$this->load->view("partial/header");
	$page_name      = ucwords(str_replace("_"," ",$controller_name));

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
					$process_by_list = array(''=>"---- Select ----",'1'=>"Employee wise",'2'=>"All");
					echo form_label("Process By", 'process_by', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'process_by', 'id' => 'process_by', 'class' => 'form-control input-sm select2'), $process_by_list);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("Employee Name", 'employee_name', array('class' => 'required'));
					echo form_input(array( 'name' => 'employee_name', 'id' => 'employee_name', 'class' => 'form-control input-sm'));
				?>
				<div id='append_div'></div>
			</div>
			<div class="form-group">
				<?php
					echo form_label("From Date", 'from_date', array('class' => 'required'));
					echo form_input(array( 'name' => 'from_date', 'id' => 'from_date', 'class' => 'form-control input-sm datepicker'));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label("To Date", 'to_date', array('class' => 'required'));
					echo form_input(array( 'name' => 'to_date', 'id' => 'to_date', 'class' => 'form-control input-sm datepicker'));
				?>
			</div>

			<!-- <div class="form-group" style='display:none;'>
				<?php
					echo form_label("Process Role", 'process_role', array('class' => 'required'));
					echo form_dropdown(array("name" =>'process_role',"id" =>'process_role',"class" =>'form-control input-sm select2'),$process_role);
				?>
			</div> -->
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="detailer_search">Search</button>
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
		$(function(){
			$(".datepicker").datetimepicker({
				format: 'DD-MM-YYYY',
			});
		});
		hide_all();
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
		
		$('#process_by').change(function(){
			var process_by = $('#process_by').val();
			if(parseInt(process_by) === 1){
				$('#employee_name').parent().show();
				$("#rslt_info").html('');
			}else{
				$('#employee_name').parent().hide();
				$("#rslt_info").html('');
			}
		});
		$('#employee_name').autocomplete({
			source: function(request, response) {
				$.getJSON('<?php echo site_url("$controller_name/emp_suggest");?>',{term:request.term},response);
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
		$('#detailer_search').click(function(){
			var employee_code 	= $("#employee_name").val();
			var from_date 		= $("#from_date").val();
			var to_date		 	= $("#to_date").val();
			var send_url = '<?php echo site_url("$controller_name/get_single_detailer_report");?>'
			$.ajax({
				type: 'POST',
				url: send_url,
				data:{employee_code:employee_code,from_date:from_date,to_date:to_date},
				success: function(data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
							$('#rslt_info').html(rslt.table_content);
									$table = $('#detailer_report').DataTable({
										 paging: false,
										 ordering: false,
										 scrollX:true,
										 "scrollY": 400
									});
									var table_option = "<div class='dataTables_length' id='detailer_report_length'><table><tr><td id='export' style='padding:8px 2px;'></td></tr></table></div>";
									$("#detailer_report_wrapper").prepend(table_option);
									var buttons = new $.fn.dataTable.Buttons($table, {
									 buttons: [{
										extend: 'collection',
										text: 'Export',
										buttons: [
											{
												extend:'copy',
												exportOptions:{modifier :{order:'index',page:'all',search:'none'},columns:':visible'}
												,title: rslt.title
											},
											{extend:'csv',exportOptions:{modifier:{order:'index',page:'all',search:'none'},columns:':visible'}
												,title: rslt.title
											},
											{extend:'excel',
											exportOptions:{modifier:{order :'index',page: 'all',search:'none'},columns:':visible'}
												,title: rslt.title},
											{extend:'pdf',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'}
												,title: rslt.title},
											{extend:'print',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible',}
												,title: rslt.title}
										]
									}]
								}).container().appendTo($('#export'));
							$(".buttons-collection").addClass("btn btn-xs btn-edit");
							$('input[type=search]').addClass('form-control input-sm');
						}else{
							toastr.error(rslt.message);
						}
					// empty_all();
				}
			});
		});
	});
	
	function hide_all(){
		$('#employee_name').parent().hide();
	}
	function empty_all(){
		$('#employee_name').val('');
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