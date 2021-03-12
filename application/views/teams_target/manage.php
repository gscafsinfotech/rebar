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
				<?php  //echo  form_open("$controller_name/team_target_save/",array("id"=>"target_form","class"=>"form-inline")); ?>
				<div class="form-group">
					<?php
						echo form_label("Month", 'target_month', array('class' => 'required'));
						echo form_input(array( 'name' => 'target_month', 'id' => 'target_month', 'class' => 'form-control input-sm datepicker'));
					?>
				</div>
				<div class="form-group">
					<?php
						echo form_label("Team", 'team', array('class' => 'required'));
						echo form_dropdown(array( 'name' => 'team', 'id' => 'team', 'class' => 'form-control input-sm select2'),$team_drop);
					?>
				</div>
			</div>
	</div>
</div>
<div class="row" style='margin: 0px;'>	
	<div class='col-md-12' style='margin:10px;padding:10px;' id="rslt_info">
	</div>
</div>
<?php  //echo form_close(); ?>
<script src="dist/daterangepicker/knockout.js" type="text/javascript"></script>
<link href="dist/daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<script src="dist/daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function (){
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
		$("#team").change(function(){
			var team = $(this).val();
			var send_url = '<?php echo site_url("$controller_name/select_team_employees");?>'
			$.ajax({
				type: 'POST',
				url: send_url,
				data:{team:team},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#rslt_info').html(rslt.table_content);
				}
			});
		});
		$(document).on('click','#submit',function(){
		// 	 var send_url     = '<?php echo site_url("$controller_name/team_target_save");?>';
		// 	 var target_form = JSON.stringify($("#target_form").serializeArray());
		// 	 alert(target_form);
		// 	 $.ajax({

  //             type:"POST",
  //             dataType:"json",
  //             url:send_url,
  //             data:target_form,
  //             success: function(data) {
  //                  // $("#data").html(data);


  //              },

  //          }); 
		// });
			// alert("hh");
			// $('#target_form').validate($.extend({
			// 	submitHandler: function (form){
			// 		$(form).ajaxSubmit({
			// 			success: function (response){
			// 			},
			// 			dataType: 'json'
			// 		});
			// 	},
			// }));
		// });


			var total_count = $("#total_count").val();
			var target_month = $("#target_month").val();
			var team 		 = $("#team").val();
			var sub_emp_code = $('input[name^=sub_emp_code]').map(function(idx, elem) {
			    return $(elem).val();
			  }).get();
			var target_value = $('input[name^=target_value]').map(function(idx, elem) {
			    return $(elem).val();
			  }).get();
			var target_unit = $('select[name="target_unit[]"]').map(function(idx, elem) {
				return $(elem).val();
			 }).get();
			var send_url     = '<?php echo site_url("$controller_name/team_target_save");?>'
			$.ajax({
				type: 'POST',
				url: send_url,
				data:{target_month:target_month,team:team,sub_emp_code:sub_emp_code,target_value:target_value,target_unit:target_unit,total_count:total_count},
				success: function(data) {
					var rslt = JSON.parse(data);
					$('#rslt_info').html(rslt.table_content);
				}
			});
		});
	});
</script>
<style>
	.btn-info{
		background: #3a28ac!important;
	}
</style>
<?php $this->load->view("partial/footer"); ?>