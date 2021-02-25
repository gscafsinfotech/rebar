<?php
$this->load->view("partial/header"); ?>
<style type="text/css">
	.form-group{
		margin-top:4px;
		display: inline-block;
	}
</style>
<?php
	$company_name      = $this->config->item('company');
	$logged_user_role  = $this->session->userdata('logged_user_role');
	$start_date        = date('d-m-Y');
	$end_date          = date('d-m-Y');
	
?>
<div class="container-fluid" style="background-color: #f2f2f2;">
	<h4 class="center bold mrgb15" style="color:#5C5C61;">DASHBOARD</h4>
	<div class='col-md-12'>
	  <div class='form-group'>
		 <input type='text' id='start_date' value='<?php echo $start_date; ?>' class='datepicker form-control input-sm '>
	  </div>
	  <div class='form-group'>
		 <input type='text' id='end_date' value='<?php echo $end_date; ?>' class='datepicker form-control input-sm '>
	  </div>
	  <div class='form-group'>
		 <button id='search' class='btn btn-info btn-sm' onclick='dashboard_search()'>search</button>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="dash_layer" style="padding: 1px 9px 0px 9px;overflow-y:auto;height: 400px;">
				<h4 style="margin-left:9px;">Candidate Status</h4>
				<div id='table_view_candidate_sts_info'></div>
				<div id="consult_candidate_sts_chart" style="height:275px;"></div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="dash_layer" style="padding: 1px 9px 0px 9px;overflow-y:auto;height: 400px;">
				<h4 style="margin-left:9px;">Invoice Status</h4>
				<p style="text-align:center;font-weight:bold;">Coming Soon!!!</p>
			</div>
		</div>
	</div>	
</div>
<div id="candiate_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Candidate Lists</h4>
      </div>
      <div class="modal-body">
		<div id="candiate_list" style="margin:20px !important; padding:15px;"></div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('partial/footer'); ?>
<script src="./dist/highcharts/highcharts.js"></script>
<script src="./dist/highcharts/exporting.js"></script>
<script src="./dist/highcharts/dashboard.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$(function () {
		 $(".datepicker").datetimepicker({
		  format: 'DD-MM-YYYY', 
		});
	});
	dashboard_search();
});

function dashboard_search(){
	var start_date = $('#start_date').val();
	var end_date   = $('#end_date').val();
	var user_role  = '<?php echo $logged_user_role ?>';
	console.log(user_role);
	if(parseInt(user_role) === 4){ //Consultancy
		get_candidate_sts_info(start_date,end_date);
		candidate_sts_chart(start_date,end_date);
	}	
}

function canditate_details(start_date,end_date,reason,consultancy){
	$('#candiate_modal').modal('show');
	$('#candiate_list').empty();
	$('#detail_list').DataTable().clear().destroy();
	if(reason){
		$.ajax({
			type: "POST",
			url: '<?php echo site_url("home/candiate_reason"); ?>',
			data:{start_date:start_date,end_date:end_date,reason:reason,consultancy:consultancy},
			success: function(data){
				var rslt = JSON.parse(data);
				$('#candiate_list').html(rslt.table_info);
				$('#detail_list').DataTable({
					dom: 'Bfrtip',
					buttons: [{
							extend: 'excelHtml5',
							className: 'blue_btn',
							title: 'Candidate_list'+ moment(new Date()).format('DD-MM-YYYY'),
					}],
					language:{
						searchPlaceholder: "Search",
						search: "",
					},
				});
			}
		});
	}
}

</script>
<style>
	button.dt-button {
		box-shadow: unset !important;
		border: 0px !important;
		border-bottom: 1px solid #CCCCCC !important;
		border-radius: 0px;
		background-color: #3498db;
		padding: 4px;
	}
	.blue_btn{
		color: #ffffff;
		background-color: #3498db;
		border-color: #3498db;
	}
</style>
