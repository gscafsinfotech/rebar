<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	$path = '<script type="text/javascript" src="./dist/opensourcepos.min.js"></script>
			<script type="text/javascript" src="./dist/validate.js"></script>
			<link rel="stylesheet" type="text/css" href="./dist/bootstrap.min.css"/>
			<link rel="stylesheet" type="text/css" href="./dist/smart_hrms.css"/>
			<script src="./dist/toastr/toastr.js"></script>
			<link rel="stylesheet" type="text/css" href="./dist/toastr/toastr.css"/>';
	include('application/config/database.php');
	$sts = 0;
	if(empty($db['default']['hostname'])){
		$style = "";
	}else
	if(empty($db['default']['username'])){
		$style = "";
	}else
	if(empty($db['default']['database'])){
		$style = "";	
	}else{
		$db_info = array('hostname'=>$db['default']['hostname'],'username'=>$db['default']['username'], 'password'=>$db['default']['password'], 'database'=>$db['default']['database']);
		$link = mysqli_connect($db_info['hostname'], $db_info['username'], $db_info['password'], $db_info['database']);
		if(mysqli_connect_errno()){
			$sts = 0;
			$style = "";
		}else{
			$sts = 1;
			$style = "style='padding:25px;display:none;'";
		}
	}
	

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Database Error</title>
<?php
	if($sts == 0){
		echo $path;
	}
	?>
<style type="text/css">
::selection { background-color: #E13300; color: white; }
::-moz-selection { background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	box-shadow: 0 0 8px #D0D0D0;
}

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
	<div id="container">
		<h1><?php echo $heading; ?></h1>
		<?php
			if($sts == 1){
				echo $message;
			}
		 ?>
		<div class='row' <?php echo $style;  ?>>
		<h4 style='margin-left:30px;'>Set DB Connection</h4>
			<?php
				$file_name   =  base_url()."db_setting.php";
				echo form_open($file_name,array("id"=>"db_config","class"=>"form-inline"));?>
				<div class="form-group">
					<?php 
						echo form_label('Hostname', 'hostname', array('class' => "control-label required"))."<br/>";
						echo form_input(array('name'=>'hostname', 'id'=>'hostname', 'class'=>'form-control','placeholder'=> "Host Name")); 
						?>
				</div>
				<div class="form-group">
					<?php 
						echo form_label('Username', 'username', array('class' => "control-label required"))."<br/>";
						echo form_input(array('name'=>'username', 'id' => 'username', 'class'=>'form-control','placeholder'=> "User Name"));
						?>
				</div>
				<div class="form-group">
					<?php
						echo form_label('Password', 'Password', array('class' => "control-label"))."<br/>";
						echo form_password(array('name'=>'password', 'id' => 'password', 'class'=>'form-control','placeholder'=> "Password")); 
						?>
				</div>
				<div class="form-group">
					<?php
						echo form_label('Confirm Password', 'Confirm Password', array('class' => "control-label"))."<br/>";
						echo form_password(array('name'=>'confirm_password', 'id' => 'confirm_password', 'class'=>'form-control','placeholder'=> "Confirm Password"));
						?>
				</div>
				<div class="form-group">
					<?php
						echo form_label('Database', 'database', array('class' => "control-label required"))."<br/>";
						echo form_input(array('name'=>'database', 'id' => 'database', 'class'=>'form-control','placeholder'=> "Database Name"));
						?>
				</div>
				<div class="form-group">
					<button type='submit' id='submit' class='btn btn-primary' style='margin-top:20px;'>Submit</button>
				</div>
			</form>
		</div>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function(){
		$("#db_config").submit(function(event){
		event.preventDefault();
		}).validate({
			rules:{
				hostname:'required',
				username:'required',
				//password:'required',
				database:'required',
				confirm_password:{
 					equalTo: "#password"
				},
			},
			submitHandler: function (form){
				$("#submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#submit').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){
						$('#submit').attr('disabled',false);
						$("#submit").html("Submit");
						if(response.success){
							toastr.success(response.msg);
							location.reload();
						}else{
							toastr.error(response.msg);
							$('#db_config')[0].reset();
						}
						
					},
					dataType: 'json'
				});
			}
		});
	});
</script>
</html>