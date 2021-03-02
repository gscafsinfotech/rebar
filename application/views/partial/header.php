<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
				<link rel="stylesheet" type="text/css" href="dist/rebar.css?rel=<?php echo date('Ymd');?>"/>
				<script type="text/javascript" src="dist/opensourcepos.min.js?rel=<?php echo date('Ymd');?>"></script>
				<script type="text/javascript" src="dist/validate.js?rel=<?php echo date('Ymd');?>"></script>
				
				<link rel="stylesheet" type="text/css" href="dist/jquery-ui.css"/>
				<link rel="stylesheet" type="text/css" href="dist/font-awesome.min.css"/>
				<link rel="stylesheet" type="text/css" href="dist/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css"/>	
				<link rel="stylesheet" type="text/css" href="dist/select2/dist/css/select2.min.css"/>
				<link rel="stylesheet" type="text/css" href="dist/toastr/toastr.css"/>
				<link rel="stylesheet" type="text/css" href="dist/froala/froala_editor.pkgd.min.css" >
				<link rel="stylesheet" type="text/css" href="dist/froala/froala_style.min.css" >
				<link rel="stylesheet" type="text/css" href="dist/jquery_confirm/jquery-confirm.min.css"  />				
				<link rel="stylesheet" type="text/css" href="dist/data_table/css/data_tables.min.css"/>
				<link rel="stylesheet" type="text/css" href="dist/bootstrap-colorpicker.css"/>
            <?php endif; ?>
            <?php $this->load->view('partial/lang_lines'); ?>
            <?php $this->load->view('partial/header_js'); ?>
	<style>
		.numbercircle {
			border-radius: 55%;
			width: 8px;
			height: 8px;
			padding: 6px;
			border: 2px solid #666;
			color: #666;
			text-align: center;
		}
	</style>
    </head>
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
					<a class="navbar-brand" href="">
					<?php 
						if($company_info[0]->company_logo){
					?>
					<img src="<?php echo base_url($company_info[0]->company_logo); ?>" width="75px" height="19px">
					<?php 
						}
						else {
							echo $company_info[0]->company_short_name;
						}								
					?>
					</a>
				</div>
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">					
					<ul class="nav navbar-nav">						
						<?php
							$show_home = true;
							$header_list = array();
							foreach($header_menu as $menu){
								if(strtoupper($menu->menu_name) === "HOME"){
									$show_home = false;
								}
								if($menu->sub_menu_name){
									$header_list[$menu->menu_name][$menu->sub_menu_name][] = array('module_id'=>$menu->module_id,'module_name'=>$menu->module_name);
								}else{
									$header_list[$menu->menu_name][$menu->module_id][] = array('module_id'=>$menu->module_id,'module_name'=>$menu->module_name);
								}
							}
							if($show_home){
								$site_url = site_url();
								echo "<li ><a href='$site_url'> <i class='fa fa-home fa-lg' aria-hidden='true'></i> Home</a></li>";
							}
							foreach($header_list as $main_key=>$main_menu){
								foreach($main_menu as $key=>$sub_menu){
									$menu_length = count($sub_menu);
									if((int)$menu_length === 1){
										$module_id   = $sub_menu[0]['module_id'];
										$module_name = $sub_menu[0]['module_name'];
										$url = site_url("$module_id");
										
										//echo $module_name."::".$module_id;
										
										if($module_id === "report"){
											foreach($report_menu as $report_rslt){
												$report_id   = $report_rslt->prime_report_setting_id;
												$report_name = $report_rslt->report_name;
												$report_url  = site_url("$module_id/index")."/".$report_id;
												$menu_li_list .= "<li><a href='$report_url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $report_name</a></li>";
											}
										}else
											if($module_id === "bank_template"){
												foreach($template_menu as $template_rslt){
												$template_id     = $template_rslt->prime_bank_template_setting_id;
												$template_name   = $template_rslt->template_name;
												$template_url  = site_url("$module_id/index")."/".$template_id;
												$menu_li_list .= "<li><a href='$template_url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $template_name</a></li>";
												}
										}else{
												$menu_li_list .= "<li><a href='$url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $module_name</a></li>";
											}
									}else{
										$ul_style = "";
										$li_style = "";
										$sub_menu_length = count($sub_menu);
										if($sub_menu_length < 7){
											$ul_style = "style='width: 160px !important;'";
											$li_style = "style='display:block;width: auto;border:0px !important;'";
										}
										if($sub_menu_length > 7){
											$ul_style = "style='width: 325px !important;'";
											$li_style = "style='width: 49.3% !important;'";
										}
										if($sub_menu_length > 14){
											$ul_style = "style='width: 630px !important;'";
											$li_style = "style='width: 33.3% !important;'";
										}
										for($i=0;$i<$sub_menu_length;$i++){
											$module_id   = $sub_menu[$i]['module_id'];
											$module_name = $sub_menu[$i]['module_name'];
											$url = site_url("$module_id");
											$sub_li_list .= "<li $li_style><a href='$url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $module_name</a></li>";
											}

										$sub_menu_li_list .= "<li class='dropdown-submenu'>
																<a href='$url' class='dropdown-toggle' data-toggle='dropdown'>$key</a>
																<ul class='dropdown-menu master_menu' $ul_style>
																	$sub_li_list
																</ul>
															</li>";
										$menu_li_list .= $sub_menu_li_list;
										$sub_menu_li_list = "";
										$sub_li_list = "";
									}
								}
								echo "<li>
											<a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button'> $main_key <span class='caret'></span>
											</a>
											<ul class='dropdown-menu'>
												$menu_li_list
											</ul>
										</li>";
								$menu_li_list = "";
							}	
						?>
					</ul>
					<?php
					 foreach($notification_menu as $notify_val){
						$remainder_head   = $notify_val['remainder_head'];
						$remainder_count  = $notify_val['remainder_count'];
						$column_name      = $notify_val['remainder_column'];
						$days_before      = $notify_val['days_before'];
						$notification_li    .="<li><a style='cursor:pointer;' onclick='get_notify(\"$column_name\",$days_before,\"$remainder_head\");'>$remainder_head <span class='numbercircle'>$remainder_count</span></a></li>";
					 }
					?>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button'  aria-expanded='true'><span class="fa fa-bell"></span></a>
							<ul class='dropdown-menu'>
								<?php echo $notification_li; ?>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?php echo strtoupper("$user_info->user_name")." - $user_info->category_name"; ?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><?php echo anchor("home/logout", $this->lang->line("common_logout")); ?></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>
		<div class="container-fulid" style='margin-top:0px;'>