<?php
$this->load->view("partial/header"); ?>
<style type="text/css">
	.form-group{
		margin-top:4px;
		display: inline-block;
	}
	.form-control, input {
		text-transform: none;
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
</div>

<?php $this->load->view('partial/footer'); ?>

<script src="./dist/highcharts/highcharts.js"></script>
<!-- <script src="./dist/highcharts/highcharts-3d.js"></script> -->
<script src="./dist/highcharts/data.js"></script>
<script src="./dist/highcharts/drilldown.js"></script>
<script src="./dist/highcharts/exporting.js"></script>
<script src="./dist/highcharts/dashboard.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$(function () {
		 $(".datepicker").datetimepicker({
		  format: 'DD-MM-YYYY', 
		});
	});
});  
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
