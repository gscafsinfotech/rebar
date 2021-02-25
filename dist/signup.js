$(document).ready(function(){

	$(".number").keydown(function (e) {
		// Allow: backspace, delete, tab, escape, enter and .
		if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
			 // Allow: Ctrl+A
			(e.keyCode == 65 && e.ctrlKey === true) ||
			 // Allow: Ctrl+C
			(e.keyCode == 67 && e.ctrlKey === true) ||
			 // Allow: Ctrl+X
			(e.keyCode == 88 && e.ctrlKey === true) ||
			 // Allow: Ctrl+V
			(e.keyCode == 86 && e.ctrlKey === true) ||
			 // Allow: home, end, left, right
			(e.keyCode >= 35 && e.keyCode <= 39)) {
				 // let it happen, don't do anything
				 return;
		}
		// Ensure that it is a number and stop the keypress
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault();
		}
	});

	//Sign up 
	$("#signupButton").click(function(){
		eml = $("#signup_emil").val();
		mob = $("#signup_mobile").val();
		/*
		if(!checkEmail(eml)){
			$("#errors").html("Invalid Email");
			//$("#errors").innerHTML = "Invalid Email";
			return false;
		}else
		*/
		if(!checkMobile(mob)){
			$("#errors").html("Invalid Mobile");
			return false;
		}
		$("#errors").html("");

		urlLink = "<?php echo base_url('signup/signup_check'); ?>";
		params  = "eml=" +eml + "&mob=" +mob;
		ajaxPostJQ(urlLink, params, signup_Callback);
	});
	$("#otpbtn").click(function(){
		otp   = $("#otp_box").val();
		pwd   = $("#sign_pwd").val();
		repwd = $("#sign_repwd").val();
		if((otp === "") || (pwd === "") || (repwd === "")){
			$("#errors").html("please enter valid information");
			return false;
		}else
		if(pwd !== repwd){
			$("#errors").html("Password Mismatched");
			return false;
		}				
		urlLink = "<?php echo base_url('signup/verify_otp'); ?>";
		params  = "otp=" +otp + "&pwd=" +pwd;
		ajaxPostJQ(urlLink, params, verifyotp_Callback);
	});
});

// Signup call back and show otp
function signup_Callback(data){
	Rslt = JSON.parse(data);
	if(Rslt.sts === "1"){
		alert(data);
		$("#signup_form").hide();
		$("#signup_otp").show();
	}else
	if(Rslt.sts === "2"){
		window.location.replace('<?php echo base_url('Login'); ?>');
	}
}

//verify otp call back 
function verifyotp_Callback(data){
	//alert(data);
	Rslt = JSON.parse(data);
	if(Rslt.sts === "0"){
		$("#errors").html("Invalid OTP");
		$("#otp_box").val()    = "";
		$("#sign_pwd").val()   = "";
		$("#sign_repwd").val() = "";
	}else{
		window.location.replace('<?php echo base_url('Login'); ?>');
	}
}

// Ajax POST
function ajaxPostJQ(urlLink, params, callbackfn) {
	 $.ajax({
		type: 'POST',
		url: urlLink,
		data: params,
		dataType: 'html',
		traditional: true,
		async: true,
		success: callbackfn
	});
}

// Function For Email Verification
function checkEmail(email){
	if(email === ""){
		return false;
	}
	if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)){
		return true;
	}
	return false;
}

//Function use to chaeck valid mobile number
function checkMobile(mob){
	var mobLen = mob.length;
	if(mob === ""){
		return false;
	}
	if(mob.charAt(0) === '0' || mob.charAt(0) === '1'|| mob.charAt(0) === '2' || mob.charAt(0) === '3' || mob.charAt(0) === '4' || mob.charAt(0) === '5' || mob.charAt(0) === '6'){
		return false;
	}
	if(mobLen !== 10){
		return false;
	}
	return true;
}