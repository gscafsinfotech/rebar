<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">		
		<title>Employee Feedback</title>
		<!-- Latest compiled and minified CSS -->
		<script type="text/javascript" src="../dist/opensourcepos.min.js?rel=20191228"></script>
		<script type="text/javascript" src="../dist/validate.js?rel=20191228"></script>
		<link rel="stylesheet" type="text/css" href="../dist/bootstrap.min.css?rel=20191228"/>
		<link rel="stylesheet" type="text/css" href="../dist/cafs_rms.css?rel=20191228"/>
		<link rel="stylesheet" type="text/css" href="../dist/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="../dist/font-awesome.min.css"/>
		<!-- DATE TIME PICKER -->
		<link rel="stylesheet" type="text/css" href="../dist/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css"/>	
		<script type="text/javascript" src="../dist/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js"></script>
		<!-- DATE TIME PICKER -->
		<!-- MULTI SELECT -->
		<link rel="stylesheet" type="text/css" href="../dist/select2/dist/css/select2.min.css"/>
		<script type="text/javascript" src="../dist/jquery-typeahead/dist/jquery.typeahead.min.js"></script>
		<script type="text/javascript" src="../dist/select2/dist/js/select2.full.min.js"></script>
		<!-- MULTI SELECT -->
		<!-- TOASTR -->
		<script type="text/javascript" src="../dist/toastr/toastr.js"></script>
		<link rel="stylesheet" type="text/css" href="../dist/toastr/toastr.css"/>	
		<!-- TOASTR -->
		<!-- DATA TABLE -->
		<link rel="stylesheet" type="text/css" href="../dist/data_table/datatables.min.css"/>	
		<script type="text/javascript" src="../dist/data_table/datatables.min.js"></script>
		<!-- DATA TABLE -->	
		<!-- Confirm Dialog Start-->
		<link href="../dist/jquery_confirm/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
		<script src="../dist/jquery_confirm/jquery-confirm.min.js" type="text/javascript"></script>
		<!-- Confirm Dialog End -->
       <style>
		.form-group {
			width: 100% !important;
			margin-left: 0px !important;
			margin-bottom: 30px !important;
		}
		.tab_head {
			margin: 15px 0px !important;
		}
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
		select.form-control{
			width: 40% !important;
		}
		</style>
	</head>
<body style="background-size: cover;">
	<div class="container-fulid">
		<div class="row">			
			<div id='login_info'>
				<div class="card" style="margin-top:11%;">
					<div style="text-align:center;">
						<img src="../images/collman_logo.jpg" class="login_logo" style="max-width:150px !important;">
					</div>
					<h4 class="tab_head" style="text-align:center;margin-top:0px;">Induction Feedback Form</h4>
					<form action="emp_feedback.php?frm=verify_save" id="verify_save" class="form-inline" autocomplete="off" method="post" accept-charset="utf-8" novalidate="novalidate">
					<div align="center" style="color:red"></div>
					<div class="form-group">
						<input type="text" name="mobile_number" id="mobile_number" class="form-control number" placeholder="Mobile Number">
					</div>
					<div class="form-group">
						<input type="text" name="offer_ref_no" id="offer_ref_no" class="form-control" placeholder="Offer Reference No">
					</div>
					<button class="btn btn-primary center-block" id="verify_submit">Submit</button>
					</form>
				</div>
			</div>
			<div id='feedback_form' class="col-md-12" style='padding:35px;display:none;'>
				<form id="submit_feedback" class="form-inline" autocomplete="off" method="post" accept-charset="utf-8" novalidate="novalidate">
					<h4 class="tab_head" style="text-align:center;margin-top:0px;">Induction Feedback Form</h4>
					<table class='table table-hover'>
						<thead>
							<tr>
								<th>Questions</th>
								<th>Answers</th>
							</tr>
						</thead>
						<tbody>
							<tr><td><label class="required">1.) Induction Class is useful to you. </label></td><td><select class="form-control" id="induction_useful" name="induction_useful"><option value="">--- Select ---</option><option value="1">Yes</option><option value="0">No</option></select></td></tr>
							<tr><td><label class="required">2.) Rate the Induction Class.</label></td><td><select class="form-control" id="induction_rate" name="induction_rate"><option value="">--- Select ---</option><option value="1">Good</option><option value="2">Neutral</option><option value="3">Bad</option></select></td></tr>
							<tr><td><label class="required">3.)  How many marks do you given for all the Trainers.</label></td><td><select class="form-control" id="marks_given" name="marks_given"><option value="">--- Select ---</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></select></td></tr>
							<tr><td><label class="required">4.)  Things that you like.</label></td><td><textarea id="things_like" name="things_like" class="form-control"></textarea></td></tr>
							<tr><td><label class="required">5.) Things that you  did not like.  </label></td><td><textarea id="things_not_like" name="things_not_like" class="form-control"></textarea></td></tr>
							<tr><td><label class="required">6.) Any Suggestion.</label></td><td><textarea id="suggestion" name="suggestion" class="form-control"></textarea></td></tr>
							<tr><td><label class="required">7.) Any Changes.</label></td><td><textarea id="changes" name="changes" class="form-control"></textarea></td></tr>
						</tbody>
					</table>
					<button type="submit" class="btn btn-primary center-block" id='feedback_submit'>Submit Feedback</button>
				</form>
			</div>			
		</div>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function(){
		$("#verify_save").submit(function(event){ event.preventDefault(); }).validate({
			rules:{
				mobile_number: {
					required: true,
					minlength: 10,
					maxlength: 10
				},
				offer_ref_no     :'required'
			},
			submitHandler: function (form){
				$("#verify_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
				$('#verify_submit').attr('disabled','disabled');
				$(form).ajaxSubmit({
					success: function (response){						
						$('#verify_submit').attr('disabled',false);
						$("#verify_submit").html("Submit");
						if(response.success){
							$('#login_info').hide();
							$('#feedback_form').show();
						}else{
							toastr.error(response.message);
						}
					},
					dataType: 'json'
				});
			}
		});
	$("#feedback_submit").click(function(e){		
		var can_process = true;
		$('select,textarea', '#submit_feedback').each(function(){
		    if($(this).val() === ""){
		    	can_process = false;
		    }
		});
		if(can_process){
			var mobile_number = $("#mobile_number").val();
			var offer_ref_no  = $("#offer_ref_no").val();
			var fdata         = JSON.stringify($("#submit_feedback").serializeArray());
			$.ajax({
				type: "POST",
				url: "emp_feedback.php?frm=submit_feedback",
				data:{offer_ref_no:offer_ref_no,mobile_number:mobile_number,fdata:fdata},
				success: function (data) {
					var rslt = JSON.parse(data);
					if(rslt.success){
						toastr.success(rslt.message);
						setTimeout(function(){
							location.reload();	
						}, 1000);										
					}else{
						toastr.error(rslt.message);
					}
				}
			});
		}else{
			toastr.error("Please Fill All the Fileds");
		}
		e.preventDefault();
	});
/*	$("#feedback_submit").submit(function(event){ event.preventDefault(); }).validate({
		rules:{
			induction_useful     :'required'
		},
		submitHandler: function (form){
			alert();
			$("#otp_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#otp_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#otp_submit').attr('disabled',false);
					$("#otp_submit").html("Verify Otp");
					if(parseInt(response.verify_otp['mode']) === 1){
						toastr.success("Verify employee details");
						//window.location.replace("http://localhost/collman_hrms/emp_verify/tl_emp_list.php");
					}else{
						toastr.error(response.verify_otp['msg']);
						get_employee_list();
						//window.location.replace("http://localhost/collman_hrms/emp_verify/tl_emp_list.php");
					}
				},
				dataType: 'json'
			});
		}
	});*/
});
</script>
</html>