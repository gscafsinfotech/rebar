
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <base href="<?php echo base_url(); ?>" />
        <title><?php echo $this->config->item('company') . ' | ' . 'CRM - &copy; CAFS Infotech'. date("Y"); ?></title>
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
            <?php if ($this->input->cookie('debug') == "true" || $this->input->get("debug") == "true") : ?>
                <?php $this->load->view('partial/header_debug'); ?>
            <?php else : ?>
				<!--[if lte IE 8]>
					<link rel="stylesheet" media="print" href="css/print.css" type="text/css" />
				<![endif]-->
				<link rel="stylesheet" type="text/css" href="dist/bootstrap.min.css?rel=<?php echo date('Ymd');?>"/>
				<link rel="stylesheet" type="text/css" href="dist/cafs_rms.css?rel=<?php echo date('Ymd');?>"/>
				<link rel="stylesheet" type="text/css" href="dist/jquery-ui.css"/>
				<link rel="stylesheet" type="text/css" href="dist/font-awesome.min.css"/>				
				
				<script type="text/javascript" src="dist/opensourcepos.min.js?rel=<?php echo date('Ymd');?>"></script>
				<script type="text/javascript" src="dist/validate.js?rel=<?php echo date('Ymd');?>"></script>
				
				<!-- DATE TIME PICKER -->
					<link rel="stylesheet" type="text/css" href="dist/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css"/>	
					<script type="text/javascript" src="dist/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js"></script>
				<!-- DATE TIME PICKER -->
				
				<!-- DATA TABLE -->
					<link rel="stylesheet" type="text/css" href="dist/data_table/datatables.min.css"/>	
					<script type="text/javascript" src="dist/data_table/datatables.min.js"></script>
				<!-- DATA TABLE -->
				
				<!-- MULTI SELECT -->
					<link rel="stylesheet" type="text/css" href="dist/select2/dist/css/select2.min.css"/>
					<script src="dist/jquery-typeahead/dist/jquery.typeahead.min.js"></script>
					<script src="dist/select2/dist/js/select2.full.min.js"></script>
				<!-- MULTI SELECT -->
				
				<!-- TOASTR -->
					<script src="dist/toastr/toastr.js"></script>
					<link rel="stylesheet" type="text/css" href="dist/toastr/toastr.css"/>	
				<!-- TOASTR -->
				
            <?php endif; ?>
            <?php $this->load->view('partial/lang_lines'); ?>
            <?php $this->load->view('partial/header_js'); ?>
    </head>
	<style>
		.form-group {
			width: 100% !important;
			margin-left: 0px !important;
			margin-bottom: 30px !important;
		}
		.jumbotron {
			background-color: #ffffff;
			color: #000000;
			font-weight: bold;
		}
		
	</style>
	<body>		
		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href=""><?php echo $this->config->item('company');?></a>
				</div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">					
					<ul class="nav navbar-nav navbar-right">
						<li ><a href="#" id="liveclock"><?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat')) ?></a></li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="container-fulid cont_bg_login">
			<div class="container" style='margin-top:30px;border-radius:3px;margin-bottom: 15px;'>
				<div class='row' style="box-shadow: 0 1px 6px 0 rgba(32,33,36,0.28); border-color: rgba(223,225,229,0); background-color: #fff;border-radius:11px;">
					<div class='col-md-8'>
						<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
						<!-- Indicators -->
							<ol class="carousel-indicators">
								<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
								<li data-target="#carousel-example-generic" data-slide-to="1"></li>
								<li data-target="#carousel-example-generic" data-slide-to="2"></li>
								<li data-target="#carousel-example-generic" data-slide-to="3"></li>
								<li data-target="#carousel-example-generic" data-slide-to="4"></li>
							</ol>

						<!-- Wrapper for slides -->
							<div class="carousel-inner" role="listbox">
								<div class="item active">
									<img src='./images/smart.png'>
								</div>
								<div class="item">
									<img src='./images/toll.png'>
								</div>
								<div class="item">
									<img src='./images/payroll.png'>
								</div>
								<div class="item">
									<img src='./images/report.png'>
								</div>
								<div class="item">
									<img src='./images/imple.png'>
								</div>
							</div>

						<!-- Controls -->
							<a class="left carousel-control" style="background-image: none;" href="#carousel-example-generic" role="button" data-slide="prev">
								<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
								<span class="sr-only">Previous</span>
							</a>
							<a class="right carousel-control" style="background-image: none;" href="#carousel-example-generic" role="button" data-slide="next">
								<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
								<span class="sr-only">Next</span>
							</a>
						</div>
					</div>
					<div class='col-md-4' style='padding: 0px;margin-top: 96px; border-color: rgba(223,225,229,0); border-radius: 11px;'>
						<div class='tab-content' style='padding: 8px; background-color: #fff; border-radius: 11px;'>
							<div class='tab-pane active' id='corporate' style='padding:0px 9px;'>
								<?php 
								if($company_info[0]->company_logo){
								?>
								<div style='text-align:center;'>
									<img src="<?php echo base_url($company_info[0]->company_logo); ?>" width="149px" height="49px">
								</div>
								<?php 
								}
								else {
									echo $company_info[0]->company_short_name;
								}								
								?>
								<h4 class='login_tab_head'>Corporate Login</h4>
								<?php echo form_open("login/corp_login/",array("id"=>"corp_login","class"=>"form-inline"));?>
									<div align="center" style="color:red"><?php echo validation_errors(); ?></div>
									<div class="form-group">
										<?php echo form_input(array('name'=>'corp_user_name', 'id'=>'corp_user_name', 'class'=>'form-control','placeholder'=> "Enter User Name")); ?>
									</div>
									<div class="form-group">
										<?php echo form_password(array('name'=>'corp_password', 'id' => 'corp_password', 'class'=>'form-control','placeholder'=> "Enter Password")); ?>
									</div>
									<button class="btn btn-block btn-primary log_btn" id='corp_submit'>Submit</button>
								<?php echo form_close(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php $this->load->view("partial/footer"); ?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#cust_login").submit(function(event){ event.preventDefault(); }).validate({
					rules:{
						cust_user_name:'required',
						cust_password:'required',
					},
					submitHandler: function (form){
						$("#cust_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
						$('#cust_submit').attr('disabled','disabled');
						$(form).ajaxSubmit({
							success: function (response){
								$('#cust_submit').attr('disabled',false);
								$("#cust_submit").html("Submit");
								if(response.success){
									toastr.success(response.message);
									location.reload();
								}else{
									toastr.error(response.message);
								}
							},
							dataType: 'json'
						});
					}
				});
				$("#corp_login").submit(function(event){ event.preventDefault(); }).validate({
					rules:{
						corp_user_name:'required',
						corp_password:'required',
					},
					submitHandler: function (form){
						$("#corp_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
						$('#corp_submit').attr('disabled','disabled');
						$(form).ajaxSubmit({
							success: function (response){
								$('#corp_submit').attr('disabled',false);
								$("#corp_submit").html("Submit");
								if(response.success){
									toastr.success(response.message);
									location.reload();
								}else{
									toastr.error(response.message);
								}
							},
							dataType: 'json'
						});
					}
				});
			});
		</script>
	</body>
</html>