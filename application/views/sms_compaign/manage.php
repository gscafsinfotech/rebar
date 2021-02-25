<?php 
	$this->load->view("partial/header"); 
	$access_data    = $this->session->userdata('access_data');
	$access_add     = (int)$access_data[$controller_name]['access_add'];
	$access_update  = (int)$access_data[$controller_name]['access_update'];
	$access_delete  = (int)$access_data[$controller_name]['access_delete'];
	$access_search  = (int)$access_data[$controller_name]['access_search']; 
	$access_export  = (int)$access_data[$controller_name]['access_export'];
	$access_import  = (int)$access_data[$controller_name]['access_import'];
	$page_name      = ucwords(str_replace("_"," ",$controller_name));
	$prime_id       = "prime_".$controller_name."_id";
	$search_url     = site_url($controller_name ."/search");
	$view_url       = site_url($controller_name ."/view/");
	$import_url     = site_url($controller_name ."/import/");
	
	/* PAGE TITLE AND BUTTONS- START */
	$breadcrumb = "";
	/*if($access_add === 1){
		$breadcrumb .= "<li>
							<a class='btn btn-xs btn-primary add' data-btn-submit='Submit' title='Add $page_name' href='$view_url' data_form='$controller_name'> <span class='fa fa-user-plus'>&nbsp</span>Add $page_name</a>
						</li>";
	}
	if($access_import === 1){
		$breadcrumb .= "<li>
							<a class='btn btn-xs btn-primary import' data-btn-submit= 'Submit' title='Import $page_name' href='$import_url' data_form='$controller_name' > <span class='fa fa-cloud-upload'>&nbsp</span> Import $page_name
							</a>
						</li>";
	}*/
	$quick_link   = explode(",",$quick_link->quicklink);
	$link_li_line = "";
	foreach($quick_link as $link){
		if($link){
			$url  = site_url("$link");
			$name = ucwords(str_replace("_"," ",$link));
			$link_li_line .= "<li><a href='$url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $name</a></li>";
		}
	}
	if($link_li_line){
		$breadcrumb .= "<li class='dropdown'>
							<a class='btn btn-xs btn-primary dropdown-toggle' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
								<i class='fa fa-plus-circle' aria-hidden='true'></i> Quick Links
							</a>
							<ul class='dropdown-menu dropdown-menu-left' aria-labelledby='dropdownMenu2'>
								$link_li_line
							</ul>
						</li>";
	}
	$breadcrumb  .="<li><a href='$site_url#Home'>Home</a></li>
					<li><a href='$site_url/$controller_name#$controller_name'>$page_name</a></li>
					<li class='active'>List</li>";
					
	
	/* PAGE TITLE AND BUTTONS- END */
	/* PAGE FILTER - START */
	$filter_tr_line = "";
	$table_map_list = "";
	$input_ids      = "";
	$date_ids       = "";
	foreach($fliter_list as $fliter){
		$label_id         = "filter_".$fliter['label_id'];
		$lable            = $fliter['label_id'];
		$label_name       = $fliter['label_name'];
		$field_isdefault  = (int)$fliter['field_isdefault'];
		$array_list       = $fliter['array_list'];
		$field_type       = (int)$fliter['field_type'];
		if($field_type === 4){							
			$filter_box =  form_input(array("name"=>$label_id, "id"=>$label_id,"placeholder"=>$label_name, "class"=>"form-control input-sm datepicker"));
			$filter_tr_line .= "<tr>
									<td class='search_td'> $label_name</td>
									<td> $filter_box </td>
								</tr>";
		}else
		if(((int)$field_type === 5) || ((int)$field_type === 7)){
			$filter_box = form_dropdown(array("name" =>$label_id,"multiple id" => $label_id,"class" =>'form-control input-sm select2'),$array_list);
			$filter_tr_line .= "<tr>
									<td class='search_td'> $label_name</td>
									<td>$filter_box</td>
								</tr>";
		}else
		if((int)$field_type === 6){
			$form_checkbox = form_checkbox(array("name" => $label_id,"id" => $label_id, "value"=> 1, "checked" => ($input_value) ? 1 : 0));
			$filter_box .= "<label class='checkbox-inline'> $form_checkbox $form_label </label>";
			$filter_tr_line .= "<tr>
								<td class='search_td'> $label_name</td>
								<td colspan='2'>$filter_box</td>
							</tr>";
		}else
		if($field_type === 13){
			$filter_box =  form_input(array("name"=>$label_id, "id"=>$label_id,"placeholder"=>$label_name, "class"=>"form-control input-sm datepicker_time"));
			$filter_tr_line .= "<tr>
									<td class='search_td'> $label_name</td>
									<td> $filter_box </td>
								</tr>";
		}else{
			if($field_type !== 9){
				$filter_box = form_input(array("name"=>$label_id, "id"=>$label_id,"value"=>'',"placeholder"=>$label_name, "class"=>"form-control input-sm"));
				$filter_tr_line .= "<tr>
										<td class='search_td'> $label_name</td>
										<td> $filter_box </td>
									</tr>";
			}			
		}
		$table_map_list .= "var $label_id  = $('#$label_id').val(); \n data.$lable = $label_id;\n";
		if($field_type === 4){
			$date_ids .= "#".$label_id.",";
		}else{
			$input_ids .= "#".$label_id.",";
		}
	}
	$date_ids     = rtrim($date_ids,",");
	$input_ids    = rtrim($input_ids,",");
	$filter_table = "<table class='fliter_table'>$filter_tr_line</table>";
	/* PAGE FILTER - END */
	$column_count     = count(array_column($table_head, "label_name"))+1;

?>
<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
	<div class='col-md-10 col-xs-8'>
		<ol class="breadcrumb">
			<?php  echo $breadcrumb; ?>	
		</ol>
	</div>
</div>
<div id="search_filter_div" class='search_filter' style="display:none;">
	<div style="max-height:250px;overflow: auto;">
		<?php echo $filter_table;?>				
	</div>
	<div class="row" style="margin:0px;margin-top:15px;">
		<div class="col-md-6" style='text-align:left;'>
			<a class="btn btn-xs btn-danger" id="clear_search"> Clear All</a>
		</div>
		<div class="col-md-6" style='text-align:right;'>	
			<a class="btn btn-xs btn-primary" id="search_close"> Close </a>
		</div>
	</div>
</div>
<div id="holder" class="form-inline" style="margin-top:20px;">
	<div class="row" style='margin-bottom:0px;'>
		<div class="col-md-12">
			<?php echo form_open('sms_compaign/sms_send',array('id'=>'sms_form','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_label('Designation', 'designation', array('class' => 'required'));
					echo form_dropdown(array( 'name' => 'designation', 'id' => 'designation', 'class' => 'form-control input-sm select2'), $designation_list);
				?>
			</div>
			<!--<div class="form-group">
				<?php
					//echo form_label("Portfolio", 'portfolio', array('class' => 'required'));
					//echo form_dropdown(array( 'name' => 'portfolio', 'id' => 'portfolio', 'class' => 'form-control input-sm select2'), $portfolio_list);
				?>
			</div>-->
			<div class="form-group">
			<?php
				echo form_label("Mobile Number", 'mobile_no', array('class' => '')); echo "<br/>";
				echo form_dropdown(array( 'name' => 'mobile_no[]', 'multiple id' => 'mobile_no', 'class' => 'form-control input-sm select2'));
			?>
			</div>
			<div class="form-group">
				<?php echo form_label("Number List", 'mobile_list', array('class' => 'required')); echo "<br/>";?>
				<textarea name="mobile_list[]" id="mobile_list" class="form-control" rows="4" readonly></textarea>
			</div>
			<div class="form-group">
				<?php echo form_label("SMS Content", 'sms_content', array('class' => 'required'));?>
				<textarea  name="sms_content" id="sms_content" class="form-control" rows="4"></textarea>
			</div>
			<div class="form-group">
				<button class="btn btn-primary btn-sm" id="send_message" > Send Message</button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){	
	select();
	$("#designation").change(function () {
		designation = $("#designation").val();
		$.ajax({
			url: '<?php echo site_url($controller_name . "/get_employees_mobile"); ?>',
			type: "POST",
			data: {designation:designation},
			async: false,
				success: function (data) {
					var rslt   = JSON.parse(data);
					var option = "<option>-- Select One --</option>";
					option += "<option value='0'>All</option>";
					for(i = 0; i < rslt.emp_list.length; i++) {
						mobile_number = rslt.emp_list[i].mobile_number;
						emp_name      = rslt.emp_list[i].emp_name;
						option += "<option value='"+mobile_number+"'>"+mobile_number+"-"+emp_name+"</option>";
					}
					$("#mobile_no").html(option);
					select();
				},
		});
    });
	
	$('#mobile_no').change(function() {
		var all = $(this).val();  
		if(all == 0){
			var mobile = [];
				$('select#mobile_no option').each(function(index, element) {
				mobile.push($(this).val());
				data = mobile.slice(1);
			});
		}else{
			var mobile = [];
			$.each($("#mobile_no option[value!=0]:selected"), function(){
				mobile.push($(this).val());
			});
			data = mobile.join(", ");
		}
		$('#mobile_list').val(data);
	});
	
	$('#sms_form').validate($.extend({
        submitHandler: function (form){
          $('#submit').attr('disabled','disabled');
            $(form).ajaxSubmit({
                success: function (response){
					if(response.success){
						toastr.success(response.msg);
					}else{
						toastr.error(response.msg);
					}
					setTimeout(function(){
						location.reload();
					},500);
                },
                dataType: 'json'
            });
        },
        rules:{
			designation: "required",
			mobile_no: "required",
			sms_content: "required",
		}
    }));
	$("#send_message").click(function(){
		if($('#mobile_list').val().trim().length > 0){
			return true;
		}else{
			toastr.error("Choose Mobile Number");
			return false;
		}
	});
});

function select(){
	$('.select2').select2({
		placeholder: '---- Select ----',
		allowClear: true,
	});
	$('.select2-tags').select2({
		tags: true,
		tokenSeparators: [',']
	});
}
</script>
<?php $this->load->view("partial/footer"); ?>