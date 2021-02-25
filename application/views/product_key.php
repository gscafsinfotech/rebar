
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
		.tab_head {
			margin: 15px 0px !important;
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
		<div class="container-fulid" style='margin-top:0px;'>
		<div class="container">
			<div class='row'>
				<div class='card' style='margin-top:11%;'>
						<div style='text-align:center;'><img src="./images/cafs_logo_gif.gif" class="login_logo" style='max-width:150px !important;'></div>
						<h4 class='tab_head' style='text-align:center;margin-top:0px;'>SMART HRMS</h4>
						<?php echo form_open("login/productkey_save/",array("id"=>"productkey_save","class"=>"form-inline",'autocomplete'=>'off'));?>
						<div align="center" style="color:red"><?php echo validation_errors(); ?></div>
						<div class="form-group">
							<div class="col-md-12">
								<?php echo form_input(array('name'=>'company_name', 'id'=>'company_name', 'class'=>'form-control','placeholder'=> "Company Name")); ?>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-3">
								<?php echo form_input(array('name'=>'product_key_1', 'id' => 'product_key_1', 'class'=>'form-control inputs','placeholder'=> "Enter Key", 'maxlength'=>4)); ?>
							</div>
							<div class="col-md-3">
								<?php echo form_input(array('name'=>'product_key_2', 'id' => 'product_key_2', 'class'=>'form-control inputs','placeholder'=> "Enter Key", 'maxlength'=>4)); ?>
							</div>
							<div class="col-md-3">
								<?php echo form_input(array('name'=>'product_key_3', 'id' => 'product_key_3', 'class'=>'form-control inputs','placeholder'=> "Enter Key", 'maxlength'=>4)); ?>
							</div>
							<div class="col-md-3">
								<?php echo form_input(array('name'=>'product_key_4', 'id' => 'product_key_4', 'class'=>'form-control inputs','placeholder'=> "Enter Key",'maxlength'=>4)); ?>
							</div>
						</div>
						<button class="btn btn-primary center-block" id='key_submit'>Activate</button>
						<?php echo form_close(); ?>
				</div>
			</div>
		</div>
	</div>
		<?php $this->load->view("partial/footer"); ?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#productkey_save").submit(function(event){ event.preventDefault(); }).validate({
					rules:{
						company_name:'required',
						product_key_1: {
							required: true,
							minlength: 4,
							maxlength: 4
						},
						product_key_2: {
							required: true,
							minlength: 4,
							maxlength: 4
						},
						product_key_3: {
							required: true,
							minlength: 4,
							maxlength: 4
						},
						product_key_4: {
							required: true,
							minlength: 4,
							maxlength: 4
						}
					},
					submitHandler: function (form){
						$("#key_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
						$('#key_submit').attr('disabled','disabled');
						$(form).ajaxSubmit({
							success: function (response){
								$('#key_submit').attr('disabled',false);
								$("#key_submit").html("Submit");
								if(response.success){
									toastr.success(response.message);
									 window.location.href = "<?php  echo site_url('login/login'); ?>";
								}else{
									toastr.error(response.message);
								}
							},
							dataType: 'json'
						});
					}
				});
				$(':input').keyup(function (e) {
					if($(this).val().length == $(this).attr('maxlength')) {
						$(this).closest('div').next().find(':input').first().focus();
					}
				})
			});
		</script>
		<style>
			.card{
				background: #ffffff;
				border-radius: 9px;
				max-height: 499px;
				box-shadow: 0 0.5px 6px 0 rgba(32,33,36,0.28);
				border-color: rgba(223,225,229,0);
				border: 7px solid #00000017;
				margin-top: 20px;
				width: 450px;
				margin-right: auto;
				margin-left: auto;
				padding: 20px;
			}
		</style>
	</body>
</html>