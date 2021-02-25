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
	/* PAGE TITLE AND BUTTONS- END */
	/* PAGE FILTER - START */
	$filter_tr_line = "";
	$table_map_list = "";
	$input_ids      = "";
	$date_ids       = "";
	foreach($fliter_list as $fliter){
		$label_id         = 'filter_'.$fliter['label_id'];
		$label            = $fliter['label_id'];
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
		$table_map_list .= "var $label_id  = $('#$label_id').val(); \n data.$label = $label_id;\n";
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
	$table_map_list  .= "\n data.start_date = start_date;\n \n data.end_date   = end_date;\n ";
	
?>
<script src="dist/daterangepicker/knockout.js" type="text/javascript"></script>
<link href="dist/daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<script src="dist/daterangepicker/daterangepicker.min.js" type="text/javascript"></script>

<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
	<div class='col-md-10 col-xs-8'>
		<ol class="breadcrumb">
			<?php 		
				$quick_link = explode(",",$link_info[0]->quicklink);
				$link_li_line = "";
				foreach($quick_link as $link){
					if($link){
						$url  = site_url("$link");
						$name = ucwords(str_replace("_"," ",$link));
						$link_li_line .= "<li><a href='$url'> <i class='fa fa-angle-double-right fa-lg' aria-hidden='true'></i> $name</a></li>";
					}
				}
				if($link_li_line){
					echo "<li class='dropdown'>
							<a class='btn btn-xs btn-primary dropdown-toggle' type='button' id='dropdownMenu2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
								<i class='fa fa-plus-circle' aria-hidden='true'></i> Quick Links
							</a>
							<ul class='dropdown-menu dropdown-menu-left' aria-labelledby='dropdownMenu2'>
								$link_li_line
							</ul>
						</li>";
				}				
			?>		
			<li><a href="<?php echo site_url()?>#Home">Home</a></li>
			<li><a href="<?php echo site_url($controller_name)?>#<?php echo "$controller_name";?>"><?php echo "$page_name";?></a></li>
			<li class="active">List</li>
		</ol>
	</div>
</div>
<div id="toolbar" class="form-inline" style="display: flex;">	
	<?php if((int)$date_filter === 1){ ?>
			<div class="" >
				<input name="daterangepicker" class="daterangepicker-field form-control input-sm" data-bind='daterangepicker: dateRange, daterangepickerOptions: { maxDate: moment()}'></input>
			</div>
	<?php }?>
	<?php 
		if($access_search === 1){	 
	?>
		<div class="col-md-4">
		<a class="btn btn-sm btn-edit" id="search_filter">
			<i class="fa fa-filter" aria-hidden="true"></i> Search filter
			<span class="caret"></span>
		</a>
		<div id="search_filter_div" class='search_filter' style='margin-top: 22px;'>
			<div style="max-height:250px;overflow: auto;">
				<?php
					echo $filter_table;
				?>				
			</div>
			<div style="margin-top:8px;">
				<div class="row">
					<div class="col-md-6" style='text-align:left;'>
						<a class="btn btn-xs btn-danger" id="clear_search"> Clear / Close</a>
					</div>
					<div class="col-md-6" style='text-align:right;'>	
						<a class="btn btn-xs btn-primary" id="search_submit"> Search </a>
					</div>
				</div>
			</div>
		</div>
		</div>
	<?php 
		}
	?>
</div>
<div id="table_holder">
   	 <table id="table"></table>
</div>
<script type="text/javascript">
$(document).ready(function (){
	var start_date = "<?php echo date('Y-m-d',strtotime('first day of this month')); ?>";
	var end_date   = "<?php echo date('Y-m-d',strtotime('last day of this month')); ?>";
	$table = $('#table').DataTable( {
		paging: false,
		searching: false,
		processing: true,
		serverSide: true,
		serverMethod: 'post',
		lengthMenu: [[10,25,50,100,500,1000,-1],[10,25,50,100,500,1000,"All"]],
        fixedColumns:{leftColumns: 3},
		scrollX:true,
		language:{
			lengthMenu:"<span style='margin-top:8px;margin-left:10px;'>Display</span> _MENU_ <span style='margin-top:8px;'>Records</span>",
			searchPlaceholder: "Search records",
			search: "",
		},
		ajax:{
			'url': '<?php echo $search_url; ?>',
			'data': function(data){
				<?php echo $table_map_list;?>
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
				foreach($table_headers as $table){
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
					if($field_type === 6){
						echo "{title:'$view_name',data: '$label_name',type: 'date',visible:true,
								render:function(value) {
									send_val = 'No';
									if(value === '1'){ send_val = 'No'; }
									return send_val;
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
			{title:'View',
				data: '<?php echo $prime_id; ?>',
				type: 'html',
				render:function (value) {
					if (value === null) return '';
					<?php 
						if($access_add === 1){
					?>
						return '<a class="btn btn-xs btn-edit view" data-btn-submit="Submit" title="Update <?php echo $page_name;?>" href="<?php echo $view_url;?>'+value+'" data_form="<?php echo $controller_name;?>"> <span class="fa fa-pencil-square-o"></span> Edit</a>';
					<?php 
						}else{
					?>
						return '';
					<?php 
						}
					?>
				}
			}
		],
	});
	
	$("#search_filter_div").hide();
	$("#search_submit").click(function(){
		$("#search_filter_div").toggle();
		$table.draw();
	});
	$("#search_filter").click(function(){			
		$("#search_filter_div").toggle();
	});		
	$("#clear_search").click(function(){
		$('#search_filter_div').find('input').val('');
		$('option').attr('selected', false);
		$("#search_filter_div").toggle();
		table_support.refresh();
		$('.select2').select2({
			placeholder: '---- Select ----',
		});
		$table.draw();
	});
	$('.select2').select2({
		placeholder: '---- Select ----',
	});
	//date picker new model start mrj -28/02/2020	
	$(".daterangepicker-field").daterangepicker({
		locale: { inputFormat: 'DD/MM/YYYY' },
		forceUpdate: true,
		callback: function(startDate, endDate, period){
			var title = startDate.format('DD/MM/YYYY') + ' – ' + endDate.format('DD/MM/YYYY');
			$(this).val(title);
			start_date = startDate.format('YYYY-MM-DD');
			end_date   = endDate.format('YYYY-MM-DD');
			$table.draw();
		}
	});
	//date picker new model end mrj -29/02/2020
});
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
<style>
	.pull-right.search {
		display: none !important;
	}
	.columns.columns-right.btn-group.pull-right {
		display: none !important;
	}
	span.select2-selection.select2-selection--multiple {
		border: 0px;
		border-radius: 0px;
		border-bottom: 1px solid #CCCCCC;
		padding: 0px 5px !important;
		min-height: 35px !important;
	}
	.print_hide{
		display: none !important;
	}
	.fixed-table-pagination{
		display: none !important;
	}
	<?php 
		if($access_search === 1){
			echo ".pull-right.search { display: block !important; }";
		}
		if($access_export === 1){
			echo ".columns.columns-right.btn-group.pull-right{display: block !important;}";
		}
	?>
	.select2-search__field{
		width: 100% !important;
	}
	.search_td{
		width: 100%;
	}
	.select2-hidden-accessible{
		width: 100% !important;
	}
	.select2-container--default{
		width: 100% !important;
	}
	.search_filter{
		right:unset !important;
	}
	.daterangepicker .custom-range-inputs input {
		width:130px !important;
	}
</style>
<?php $this->load->view("partial/footer"); ?>
