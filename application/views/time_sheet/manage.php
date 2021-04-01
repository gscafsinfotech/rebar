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
	$logged_role    = $this->session->userdata('logged_role');
	
	// $process_status_result  = json_decode(json_encode($process_status_result),true);
	$process_status_result = array_reduce($process_status_result, function ($result, $arr) {
		    $result[$arr['prime_time_sheet_id']] = $arr;
		    return $result;
		}, array());
//$process_status_result  = json_encode($process_status_result);
	// echo "<pre>";
	// print_r($process_status_result); die;
	/* PAGE TITLE AND BUTTONS- START */
	$breadcrumb = "";
	if($access_add === 1){
		$breadcrumb .= "<li>
							<a class='btn btn-xs btn-primary add' data-btn-submit='Submit' title='Add $page_name' href='$view_url' data_form='$controller_name'> <span class='fa fa-user-plus'>&nbsp</span>Add $page_name</a>
						</li>";
	}
	if($access_import === 1){
		$breadcrumb .= "<li>
							<a class='btn btn-xs btn-primary import' data-btn-submit= 'Submit' title='Import $page_name' href='$import_url' data_form='$controller_name' > <span class='fa fa-cloud-upload'>&nbsp</span> Import $page_name
							</a>
						</li>";
	}
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
<div class="row" style='margin:0px;overflow:auto;'>	
	<div class='col-md-12' style='padding:8px;min-height: 400px;'>
		<table id="table" class='table table-striped table-hover' style='width:100% !important;'></table>
	</div>
</div>


<script type="text/javascript">
$(document).ready(function (){	
	$('.modal-dialog').draggable({ handle: ".modal-header" });	
	var a = <?php echo json_encode($master_pick); ?>;
	$table = $('#table').DataTable( {
		processing: true,
		serverSide: true,
		serverMethod: 'post',
		lengthMenu: [[10,25,50,100,500,1000,-1],[10,25,50,100,500,1000,"All"]],
        fixedColumns:{leftColumns: 3},
		scrollX:true,
		//fixedHeader: true,
		language:{
			lengthMenu:"<span style='margin-top:8px;margin-left:10px;'>Display</span> _MENU_ <span style='margin-top:8px;'>Records</span>",
			searchPlaceholder: "Search records",
			search: "",
			//processing: '<div style="text-align: center; padding: 50px;color:#4b6fa2;z-index:999999999;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Loading...</div>',
		},
		ajax:{
			'url': '<?php echo $search_url; ?>',
			'data': function(data){
				<?php echo $table_map_list;?>
				console.log(data);
			},
			 beforeSend: function(){
			  $('.dataTables_processing').html('<span style="color:#CC3366;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Loading...</span>');
			},
		},
		columns: [{
				title:'<input type="checkbox" name="select_all" class="select_all">',
				data: '<?php echo $prime_id; ?>',
				type: 'html',
				orderable: false,
				className: 'select-checkbox',
				searchable:false,
				width:'1%',
				render:function (value) {
					return '<input type="checkbox" value="'+value+'" name="select_one" class="select_one">';
				}
			},			
			<?php 
				foreach($table_head as $table){
					$label_name  = $table->label_name;
					$view_name   = $table->view_name;
					$field_type  = (int)$table->field_type;		
					if($field_type === 4){
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,
								render:function(value) {
									if (value === null) return '';
									return moment(value).format('DD/MM/YYYY');
								}
							},\n";
					}else
					if(($field_type === 5)||($field_type === 9)){
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,
								render:function(value) {
									if(value in a['$label_name']){
										if(value === '0' || value === ''){
											return '';
										}else{
											return a['$label_name'][value];
										}
									} else {
										return value;
									}
								}
							},\n";
					}else
					if($field_type === 6){
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,
								render:function(value) {
									send_val = 'No';
									if(value === '1'){ send_val = 'No'; }
									return send_val;
								}
							},\n";
					}else
					if($field_type === 7){
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,
								render:function(value) {
									if(value !== ''){
										var rslt = '';
										var multi_val = value.split(',');
										var count = 0
										$.each(multi_val,function(i){
											count++;
											var multi_key = multi_val[i];
											if(multi_key in a['$label_name']){
												if(count === 1){
												   rslt += a['$label_name'][multi_key];
											   }else{
												   rslt += ' , '+a['$label_name'][multi_key];
											   }
											}
										});
										return rslt;
									}else {
										return '-';
									}
								}
							},\n";
					}else
					if($field_type === 10){
						$img = '<img src="@URL@" alt="img" height="30" width="30">';
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,sClass: 'center',
								render:function(value) {
									if(value !== ''){
										var image = '$img';	
										image     = image.replace('@URL@', value);										
										return image;
									}else{
										return '';
									}
									
								}
							},\n";
					}else{
						echo "{title:'$view_name',data:'$label_name',visible:true,},\n";
					}
				}
			?>
			{title:'Status',
				data: '<?php echo $prime_id; ?>',
				type: 'html',
				render:function (value) {
					// alert(value + '<?php echo $prime_id; ?>');
					if (value === null) return '';
					<?php 
						if($access_update === 1){
							$inp_status = "";
							$com_status = "";
					?>	
						var completed_status  = <?php echo json_encode($process_status_result); ?>;
						if(value){
							console.log(completed_status);
							var completed_status  = completed_status[value]['completed_status'];
						}
							if(parseInt(completed_status) === 1){
								return '<select name="process_status" id="process_status'+value+'" class="form-control input-sm select2 process_status" tabindex="-1" aria-hidden="true" onchange=processStatus(this,"'+value+'")><option value="">---- Status ----</option ><option value="1" selected>Inprogress</option><option value="2">Completed</option></select>';
							}else 
							if(parseInt(completed_status) === 2){
								var logged_role = "<?php echo $logged_role;?>";
								if(parseInt(logged_role) === 5){
									$('#process_status'+value).attr("disabled", true); 
									return '<select name="process_status" id="process_status'+value+'" class="form-control input-sm select2 process_status" tabindex="-1" aria-hidden="true" onchange=processStatus(this,"'+value+'")><option value="">---- Status ----</option ><option value="1">Inprogress</option><option value="2" selected>Completed</option></select>';
								}else{
									return '<select name="process_status" id="process_status'+value+'" class="form-control input-sm select2 process_status" tabindex="-1" aria-hidden="true" onchange=processStatus(this,"'+value+'")><option value="">---- Status ----</option ><option value="1">Inprogress</option><option value="2" selected>Completed</option></select>';
								}
							}else{
								return '<select name="process_status" id="process_status'+value+'" class="form-control input-sm select2 process_status" tabindex="-1" aria-hidden="true" onchange=processStatus(this,"'+value+'")><option value="">---- Status ----</option ><option value="1">Inprogress</option><option value="2">Completed</option></select>';
							}
				<?php 
					}else{
				?>
						return '<select name="process_status" id="process_status" class="form-control input-sm select2 process_status" tabindex="-1" aria-hidden="true" onchange=processStatus("'+value+'")><option value="">---- Status ----</option><option value="1">Inprogress</option><option value="2">Completed</option></select>';
					<?php 
						}
					?>
				}
			},
			{title:'View',
				data: '<?php echo $prime_id; ?>',
				type: 'html',
				render:function (value) {
					//alert(value + '<?php echo $prime_id; ?>');
					if (value === null) return '';
					<?php 
						if($access_update === 1){
					?>
						var completed_status  = <?php echo json_encode($process_status_result); ?>;
						var completed_status  = completed_status[value]['completed_status'];
						if(parseInt(completed_status) === 2){
							var logged_role = "<?php echo $logged_role;?>";
							if(parseInt(logged_role) === 5){
								return "";
							}else{
								return '<a class="btn btn-xs btn-edit view" data-btn-submit="Submit" title="Update <?php echo $page_name;?>" href="<?php echo $view_url;?>'+value+'" data_form="<?php echo $controller_name;?>"> <span class="fa fa-pencil-square-o"></span> Edit</a>';
							}
						}else{
							return '<a class="btn btn-xs btn-edit view" data-btn-submit="Submit" title="Update <?php echo $page_name;?>" href="<?php echo $view_url;?>'+value+'" data_form="<?php echo $controller_name;?>"> <span class="fa fa-pencil-square-o"></span> Edit</a>';
						}
					<?php 
						}else{
					?>
						return '<a class="btn btn-xs btn-edit view" title="View <?php echo $page_name;?>" href="<?php echo $view_url;?>'+value+'" data_form="<?php echo $controller_name;?>"> <span class="fa fa-eye"></span> View</a>';
					<?php 
						}
					?>
				}
			}
		],
	});
	$("<?php echo $input_ids;?>").bind('keyup change', function(e) {
		$table.draw();
	});
	$("<?php echo $date_ids;?>").on("dp.hide",function (e) {
		$table.draw();
	});
				
	var table_option = "<table><tr><td id='filters' style='padding:8px 2px;'></td><td id='export' style='padding:8px 2px;'></td></tr></table>";
	$("#table_filter").append(table_option);		
	var buttons = new $.fn.dataTable.Buttons(table, {
		 buttons: [{
			extend: 'collection',
			text: 'Export',
			buttons: [
				{extend:'copy',exportOptions:{modifier :{order:'index',page:'all',search:'none'},columns:':visible'}},
				{extend:'csv',exportOptions:{modifier:{order:'index',page:'all',search:'none'},columns:':visible'}},
				{extend:'excel',exportOptions:{modifier:{order :'index',page: 'all',search:'none'},columns:':visible'}},
				{extend:'pdf',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'}},
				{extend:'print',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible',}},
			]
		}]
	}).container().appendTo($('#export'));
	var custom_filter = "<button class='btn btn-xs btn-edit fliter' id='search_filter'>Filter <i class='fa fa-filter' aria-hidden='true'></i></button>";
	$("#filters").append(custom_filter);
	$(".buttons-collection").addClass("btn btn-xs btn-edit");
	$('input[type=search]').addClass('form-control input-sm');
	$("select[name='table_length']" ).addClass('form-control input-sm');
  
	$("a.add").click(function(event){
		event.preventDefault();
		var action      = $(this).attr('data-btn-submit');
		var title       = $(this).attr('title');
		var control     = $(this).attr('href');
		var form_id     = $(this).attr('data_form')+"_form";
		$('.modal').modal({backdrop: 'static', keyboard: false});
		$('.modal-body').html('<div style="text-align: center;padding:50px;color:#4b6fa2;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Please wait processing....</div>');
		$.ajax({
			type: 'POST',
			url: control,
			dataType: "html",
			success: function (response){					
				$('.modal-title').html('<h4 class="modal-title">'+title+'</h4>');
				$('.modal-body').html(response);
				var btn_info = '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>';
				if(action === "Submit"){
					btn_info += '<button class="btn btn-primary" id="submit" style="margin-left: 15px;">Submit</button>';
				}
				btn_info = '<div class="col-md-12" style="background-color:#FFFFFF;padding: 10px 20px; text-align: right; border-top: 1px solid #e5e5e5;">'+btn_info+'</div>';
				$('#'+form_id).append(btn_info);
			}
		});
	});
  
	$("a.import").click(function(event){
		event.preventDefault();
		var action      = $(this).attr('data-btn-submit');
		var title       = $(this).attr('title');
		var control     = $(this).attr('href');
		var form_id     = "save_import";
		$('.modal').modal({backdrop: 'static', keyboard: false});
		$('.modal-body').html('<div style="text-align: center;padding:50px;color:#4b6fa2;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Please wait processing....</div>');
		$.ajax({
			type: 'POST',
			url: control,
			dataType: "html",
			success: function (response){					
				$('.modal-title').html('<h4 class="modal-title">'+title+'</h4>');
				$('.modal-body').html(response);
				var btn_info = '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>';
				if(action === "Submit"){
					btn_info += '<button class="btn btn-primary" id="submit" style="margin-left: 15px;">Submit</button>';
				}
				btn_info = '<div class="col-md-12" style="background-color:#FFFFFF;padding: 10px 20px; text-align: right; border-top: 1px solid #e5e5e5;">'+btn_info+'</div>';
				$('#'+form_id).append(btn_info);
			}
		});
	});
	$table.on('click','a.view',function(event){
		event.preventDefault();		
		var action      = $(this).attr('data-btn-submit');
		var title       = $(this).attr('title');
		var control     = $(this).attr('href');
		var form_id     = $(this).attr('data_form')+"_form";			
		$('.modal').modal({backdrop: 'static', keyboard: false});
		$('.modal-body').html('<div style="text-align: center;padding:50px;color:#4b6fa2;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Please wait processing....</div>');
		$.ajax({
			type: 'POST',
			url: control,
			dataType: "html",
			success: function (response){					
				$('.modal-title').html('<h4 class="modal-title">'+title+'</h4>');
				$('.modal-body').html(response);
				var btn_info = '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>';
				if(action === "Submit"){
					btn_info += '<button class="btn btn-primary" id="submit" style="margin-left: 15px;">Submit</button>';
				}
				btn_info = '<div class="col-md-12" style="background-color:#FFFFFF;padding: 10px 20px; text-align: right; border-top: 1px solid #e5e5e5;">'+btn_info+'</div>';
				$('#'+form_id).append(btn_info);
				$table.draw();
			}
		});

	});
	$table.on('click','a.view',function(event){
		event.preventDefault();		
		var action      = $(this).attr('data-btn-submit');
		var title       = $(this).attr('title');
		var control     = $(this).attr('href');
		var form_id     = $(this).attr('data_form')+"_form";			
		view_form_data(action,title,control,form_id);
	});
	/* DELETE PROCESS - START*/
	var delete_btn = "<button class='btn btn-xs btn-danger fliter disabled' id='delete_btn' style='margin-top:7px'><i class='fa fa-trash' aria-hidden='true'></i> Delete</button>";
	$("#table_length").prepend(delete_btn);
	$("#table_length").css("display", "-webkit-inline-box");
	$('.select-checkbox').on('click', "input[name='select_all']", function(){    
		if(this.checked) {
			$('.select_one').prop('checked', true);
			$("#delete_btn").removeClass("disabled");
		}else{
			$('.select_one').prop('checked', false);
			$("#delete_btn").addClass("disabled");
		}
	});
	$table.on('change','.select_one',function(event){		
		var delete_ids = [];
		$.each($("input[name='select_one']:checked"), function(){
			delete_ids.push($(this).val());
		});		
		if(delete_ids.length > 0) {
			$("#delete_btn").removeClass("disabled");
		}else{
			$("#delete_btn").addClass("disabled");
		}	
	});
	
	$("#delete_btn").click(function(event){
		if(confirm("Are you sure. you want delete select records?")){
			var delete_ids = [];
			$.each($("input[name='select_one']:checked"), function(){
				delete_ids.push($(this).val());
			});
			//do ajax process
			if(delete_ids){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url("$controller_name/delete"); ?>',
					data:{delete_ids:delete_ids},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);							
							$('.select_all').prop('checked', false);
							$('.select_one').prop('checked', false);
							$("#delete_btn").addClass("disabled");
							$table.draw();
						}else{
							toastr.error(rslt.message);							
						}
					}
				
				});
			}
						
		}else{
			$('.select_all').prop('checked', false);
			$('.select_one').prop('checked', false);
			$("#delete_btn").addClass("disabled");
		}
	});
	/* DELETE PROCESS - END*/
	// $table.on('click','tr td:not(:first-child,:nth-child(7))',function() {
 //        var closest_row = $(this).closest('tr');
 //        var data        = $table.row(closest_row).data();
 //        var prime_id    = data['<?php echo $prime_id; ?>'];
 //       	var action      = $("td > a").attr('data-btn-submit');
	// 	var title       = $("td > a").attr('title');
	// 	var control     = '<?php echo $view_url; ?>'+prime_id;
	// 	var form_id     = $("td > a").attr('data_form')+"_form";
	// 	view_form_data(action,title,control,form_id);	
 //    });
	$("#search_filter_div").hide();
	$("#search_filter").click(function(){
		$("#search_filter_div").toggle();
	});
	$("#search_close").click(function(){
		$("#search_filter_div").toggle();
	});
	$("#clear_search").click(function(){
		$('input').val('');
		$('option').attr('selected', false);
		$("#search_filter_div").toggle();
		$table.draw();
		$('.select2').select2({placeholder: '---- Select ----',});
	});
	$(function (){$(".datepicker").datetimepicker({format: 'DD-MM-YYYY',});});
	$(".datepicker_time").datetimepicker({format: 'DD-MM-YYYY HH:mm:ss',});
	$('.select2').select2({placeholder: '---- Select ----',});
});
function processStatus(process_status,row_id){
	var process_status = process_status.value; 
	var send_url = '<?php echo site_url("$controller_name/process_status"); ?>'; 
	$.confirm({
		title: 'Confirm!',
		content: 'Are you sure. you want change select records?',
		type: 'red',
		typeAnimated: true,
		buttons: {
			tryAgain: {
				text: 'Ok',
				btnClass: 'btn-red',
				action: function(){
					$.ajax({
						type: "POST",
						url: send_url,
						data:{row_id:row_id,process_status:process_status},
						success: function(data) {
							var rslt = JSON.parse(data);
							toastr.success(rslt.message);
							$table.draw();
							location.reload();
						}
					});
				}
			},
			close: function () {
				$('#submit').attr('disabled',false);
				$("#submit").html("Submit");
				$table.draw();
			}
		}
	});
}
function view_form_data(action,title,control,form_id){
	$('.modal').modal({backdrop: 'static', keyboard: false});
	$('.modal-body').html('<div style="text-align: center;padding:50px;color:#4b6fa2;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Please wait processing....</div>');
	$.ajax({
		type: 'POST',
		url: control,
		dataType: "html",
		success: function (response){					
			$('.modal-title').html('<h4 class="modal-title">'+title+'</h4>');
			$('.modal-body').html(response);
			var btn_info = '<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>';
			if(action === "Submit"){
				btn_info += '<button class="btn btn-primary" id="submit" style="margin-left: 15px;">Submit</button>';
			}
			btn_info = '<div class="col-md-12" style="background-color:#FFFFFF;padding: 10px 20px; text-align: right; border-top: 1px solid #e5e5e5;">'+btn_info+'</div>';
			$('#'+form_id).append(btn_info);
		}
	});
}
</script>
<?php $this->load->view("partial/footer"); ?>