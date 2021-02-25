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
	$filter_cond_array = array('' => '--- Select ---','=' => '=','>' => '>','<' => '<','LIKE' => 'LIKE');
	$tr_line = "";
	foreach($fliter_list as $fliter){
		$label_id           = $fliter['label_id'];
		$field_isdefault    = $fliter['field_isdefault'];
		$array_list         = $fliter['array_list'];
		$field_type         = $fliter['field_type'];
		$label_name         = ucwords(strtolower(str_replace("_"," ",$label_id)));
		$filter_label       = form_input(array('type'=>'hidden','name' => 'filter_label[]', 'class' => 'form-control input-sm','value' => $label_id));
		$filter_type        = form_input(array('type'=>'hidden','name' => 'filter_type[]', 'class' => 'form-control input-sm','value' => $field_isdefault));
		$field_type_input   = form_input(array('type'=>'hidden','name' => 'field_type[]', 'class' => 'form-control input-sm','value' => $field_type));
		$filter_cond        = form_dropdown(array('name' => 'filter_cond[]', "id"=>$label_id."_con",'class' => 'form-control input-sm'), $filter_cond_array);
		if($field_type === 4){							
			$filter_val     = form_input(array( 'name' => 'filter_val[]', "id"=>$label_id, 'class' => 'form-control input-sm datepicker', 'placeholder'=>'Search value','value' => ''));
		}else
		if(((int)$field_type === 5) || ((int)$field_type === 7)){
			$filter_val  = form_dropdown(array('name' => 'filter_val[]', "id"=>$label_id,'multiple class' => 'form-control input-sm select2'), $array_list);
			$readonly = 'readonly';
		}else{
			$filter_val   = form_input(array( 'name' => 'filter_val[]', "id"=>$label_id, 'class' => 'form-control input-sm', 'placeholder'=>'Search value','value' => ''));
			$readonly = '';
		}
		$filter_cond        = form_dropdown(array('name' => 'filter_cond[]', "id"=>$label_id."_con",'class' => 'form-control input-sm',$readonly=>true), $filter_cond_array);
		$tr_line .= "<tr>
						<td class='search_td'>$field_type_input $label_name $filter_label $filter_type</td>
						<td> $filter_cond </td>
						<td> $filter_val </td>
					</tr>";
	}
	$report_filter   = form_input(array('type'=>'hidden','name' => 'report_filter_id','id' => 'report_filter_id', 'class' => 'form-control input-sm','value' => ''));
	$filter_name   = form_input(array('type'=>'hidden','id' => 'filter_name', 'class' => 'form-control input-sm','value' => ''));
	$form_id         = form_input(array( 'name' => 'form_id','id' => 'form_id','type'=>'hidden','class' => 'form-control input-sm','value' => "$form_id"));
	$filter_table    = "$filter_name $report_filter $form_id<table class='fliter_table' style='width:100%;'>$tr_line</table>";
	$table_map_list .= "
						var filter_label     =  $(\"input[name='filter_label[]']\").map(function(){return $(this).val();}).get();\n
						var field_type     =  $(\"input[name='field_type[]']\").map(function(){return $(this).val();}).get();\n
						var filter_type      =  $(\"input[name='filter_type[]']\").map(function(){return $(this).val();}).get();
						var filter_cond      =  $(\"select[name='filter_cond[]']\").map(function(){return $(this).val();}).get();
						var filter_val       =  $(\"input[name='filter_val[]'],select[name='filter_val[]']\").map(function(){
								if($(this).val()){
									var return_data = ($(this).val()).toString();;
									return return_data;
								}else{
									return '';
								}
								}).get();
					";
	/* PAGE FILTER - END */
	$column_count     = count(array_column($table_head, "label_name"))+1;
	$table_map_list  .= "\n data.field_type = field_type;\n\n data.filter_label = filter_label;\n \n data.filter_type = filter_type;\n \n data.filter_cond = filter_cond;\n \n data.filter_val = filter_val;\n \n data.start_date = start_date;\n \n data.end_date   = end_date;\n ";	
?>
<script src="dist/daterangepicker/knockout.js" type="text/javascript"></script>
<link href="dist/daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<script src="dist/daterangepicker/daterangepicker.min.js" type="text/javascript"></script>

<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo strtoupper($report_name) ?></h1>
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
	<div class="form-group" style='width:12% !important;'>
	<?php if((int)$date_filter === 1){ ?>
			<div class="" >
				<input name="daterangepicker" class="daterangepicker-field form-control input-sm" data-bind='daterangepicker: dateRange, daterangepickerOptions: { maxDate: moment()}'></input>
			</div>
	<?php }?>
	</div>
	<?php 
		if($access_search === 1){	 
	?>
		<div id="search_filter_div" class='search_filter' style="display:none;">
			<div style="max-height:250px;overflow: auto;">
				<?php echo $filter_table;?>				
			</div>
			<div class="row" style="margin:0px;margin-top:15px;">
				<div class="col-md-4" style='text-align:left;'>
					<a class="btn btn-xs btn-danger" id="clear_search"> Clear All</a>
				</div>
				<div class="col-md-4" style='text-align:left;'>
					<a class="btn btn-xs btn-warning" id="save_filter">Save Filter</a>
				</div>
				<div class="col-md-4" style='text-align:right;'>	
					<a class="btn btn-xs btn-primary" id="search_submit"> Submit </a>
				</div>
			</div>
		</div>
	<?php 
		}
	?>
	<div class='form-inline col-md-12'>	
		<div class='form-group'>
		<?php
			echo form_dropdown(array( 'name' => 'pre_filter', ' id' => 'pre_filter', 'class' => 'form-control input-sm select2'), $filter_info);
		?>
		</div>
	</div>
</div>
<div id="table_holder">
   	 <table id="table"></table>
</div>
<script type="text/javascript">
$(document).ready(function (){
	var start_date = "<?php echo date('Y-m-d',strtotime('first day of this month')); ?>";
	var end_date   = "<?php echo date('Y-m-d',strtotime('last day of this month')); ?>";
	
	$table = $('#table').DataTable({
		paging: false,
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
									return (value);
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
		],
	});	
	var table_option = "<table><tr><td id='filters' style='padding:8px 2px;'></td><td id='export' style='padding:8px 2px;'></td></tr></table>";
	$("#table_filter").append(table_option);
	var company_name = '<?php echo $company_information->company_name;?>';
	var report_name  = '<?php echo $report_name;?>';
	
	var buttons = new $.fn.dataTable.Buttons(table, {
		 buttons: [{
			extend: 'collection',
			text: 'Export',
			buttons: [
				{extend:'copy',exportOptions:{modifier :{order:'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+moment().format('MMMM-YYYY'),filename: report_name+"-"+moment().format('MMMM-YYYY')},
				{extend:'csv',exportOptions:{modifier:{order:'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+moment().format('MMMM-YYYY'),filename: report_name+"-"+moment().format('MMMM-YYYY')},

				{extend:'excel',exportOptions:{modifier:{order :'index',page: 'all',search:'none'},columns:':visible'},title:'<h2>company_name</h2>',messageTop: report_name+"-"+moment().format('MMMM-YYYY'),filename: report_name+"-"+moment().format('MMMM-YYYY')},

				{extend:'pdf',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+moment().format('MMMM-YYYY'),filename: report_name+"-"+moment().format('MMMM-YYYY')},
				{extend:'print',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+moment().format('MMMM-YYYY'),filename: report_name+"-"+moment().format('MMMM-YYYY')},
			]
		}]
	}).container().appendTo($('#export'));
	var custom_filter = "<button class='btn btn-xs btn-edit fliter' id='search_filter'>Filter <i class='fa fa-filter' aria-hidden='true'></i></button>";
	$("#filters").append(custom_filter);
	$(".buttons-collection").addClass("btn btn-xs btn-edit");
	$('input[type=search]').addClass('form-control input-sm');
	$("select[name='table_length']" ).addClass('form-control input-sm');
	
	
	$("#search_filter_div").hide();
	$("#search_submit").click(function(){
		$("#search_filter_div").toggle();
		$table.draw();
	});
	$("#save_filter").click(function(){
		$("#save_filter").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
		$('#save_filter').attr('disabled','disabled');
		$.confirm({
			title: 'Save Filter',
			content: '' +
			'<form action="" class="formName">' +
			'<div class="form-group">' +
			'<label>Filter Name</label>' +
			'<input type="text" placeholder="Filter Name" name="filter_name"  class="name form-control" required />' +
			'</div>' +
			'</form>',
			buttons: {
				formSubmit: {
					text: 'Add/Update',
					btnClass: 'btn-blue',
					action: function () {
						var filter_name = this.$content.find('.name').val();
						if(!filter_name){
							$.alert('provide a valid filter name');
							return false;
						}
						var form_id          = $('#form_id').val();
						var report_filter_id = $('#report_filter_id').val();
						var filter_label     =  $("input[name='filter_label[]']").map(function(){return $(this).val();}).get();
						var filter_type      =  $("input[name='filter_type[]']").map(function(){return $(this).val();}).get();
						var field_type      =  $("input[name='field_type[]']").map(function(){return $(this).val();}).get();
						var filter_cond      =  $("select[name='filter_cond[]']").map(function(){return $(this).val();}).get();
						var filter_val       =  $("input[name='filter_val[]'],select[name='filter_val[]']").map(function(){
								if($(this).val()){
									var return_data = ($(this).val()).toString();;
									return return_data;
								}else{
									return '';
								}
								}).get();
						var status = false;
						var i= 0;
						$.each($("input[name='filter_val[]'],select[name='filter_val[]']"), function() {
							if($(this).val() && filter_cond[i]){
								status = true;
							}
							i++;
						});
						if(status){
							$.ajax({
								type: "POST",
								url: '<?php echo site_url($controller_name . "/filter_save"); ?>',
								data: {report_filter_id:report_filter_id,filter_name:filter_name,form_id:form_id,filter_label:filter_label,field_type:field_type,filter_type:filter_type,filter_cond:filter_cond,filter_val:filter_val},
								success: function(response){
									var rslt = JSON.parse(response);
									if(rslt.success){
										var pre_filter = "";
										$.each(rslt.filter_list, function( key, value ) {
										  pre_filter += '<option value="' + key + '">' + value + '</option>';
										});
										$('#pre_filter').html(pre_filter);
										$('#pre_filter').val('');
										$('#pre_filter').select2({
											placeholder: '---- Select ----',
											allowClear: true
										});
										toastr.success(rslt.message);
									}else{
										toastr.error(rslt.message);
									}
									$('#save_filter').attr('disabled',false);
									$("#save_filter").html("Save Filter");
								}
							});
						}else{
							$('#save_filter').attr('disabled',false);
							$("#save_filter").html("Save Filter");
							toastr.error('Filter condition and value is not equal');
						}
					}
				},
				cancel: function () {
					$('#save_filter').attr('disabled',false);
					$("#save_filter").html("Save Filter");
				},
			},
			onContentReady: function () {
				this.$content.find('.name').val($('#filter_name').val());
				// bind to events
				var jc = this;
				this.$content.find('form').on('submit', function (e) {
					// if the user submits the form by pressing enter in the field.
					e.preventDefault();
					jc.$$formSubmit.trigger('click'); // reference the button and click it
				});
			}
		});
	});

	$("#pre_filter").on('change', function(e){
		var pre_filter = $('#pre_filter').val();
		if(pre_filter){
			$.ajax({
				type: "POST",
				url: '<?php echo site_url($controller_name . "/edit_filter_report"); ?>',
				data: {report_id:pre_filter},
				 beforeSend: function(){
				  $('.dataTables_processing').html('<span style="color:#CC3366;"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><br/>Loading...</span>');
				},
				success: function(data) {
					var rslt = JSON.parse(data);
					$("select[name='filter_cond[]']").val('');
					$("input[name='filter_val[]'],select[name='filter_val[]']").val('');
					$("select[name='filter_val[]']").find("option").prop("selected", false);
					$("select[name='filter_val[]']").select2({
						placeholder: '---- Select ----',
						allowClear: true,
						dropdownParent: $('.modal-dialog')
					});
					$.each( rslt.edit_data, function(key,value){
						if(parseInt(value.field_type) === 5 || parseInt(value.field_type) === 7){
							var selectedOptions = (value.filter_val).split(",");
							for(var i in selectedOptions) {
								var val = selectedOptions[i];
								$("#"+value.filter_id).find("option[value="+val+"]").prop("selected", "selected");
							}
							$("#"+value.filter_id).select2({
								placeholder: '---- Select ----',
								allowClear: true,
								dropdownParent: $('.modal-dialog')
							});
						}else{
							$("#"+value.filter_id).val(value.filter_val);
						} 
						$("#"+value.filter_id+"_con").find("option[value='"+value.filter_con+"']").prop("selected", "selected");
						$("#report_filter_id").val(value.report_filter_id);
						$("#filter_name").val(value.filter_name);
					});
					$table.draw();
				}
			});
		}
	});	
	$("#search_filter").click(function(){			
		$("#search_filter_div").toggle();
	});		
	$("#clear_search").click(function(){
		$("select[name='filter_cond[]']").val('');
		$("#pre_filter").val('');
		$("input[name='filter_val[]'],select[name='filter_val[]']").val('');
		$('option').attr('selected', false);
		$("#search_filter_div").toggle();
		$('.select2').select2({
			placeholder: '---- Select ----',
		});
		$table.draw();
	});
	$('.select2').select2({
		placeholder: '---- Select ----',
	});
	$(".datepicker").datetimepicker({
		format: 'DD-MM-YYYY',
		//debug: true
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
			var date = startDate.format('MMMM-YYYY').toUpperCase();

			$('#export').html('');
			var buttons = new $.fn.dataTable.Buttons(table, {
				 buttons: [{
					extend: 'collection',
					text: 'Export',
					buttons: [
						{extend:'copy',exportOptions:{modifier :{order:'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+date,filename: report_name+"-"+date},
						{extend:'csv',exportOptions:{modifier:{order:'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+date,filename: report_name+"-"+date},

						{extend:'excel',
						customize: function ( xlsx ) {
							var sheet = xlsx.xl.worksheets['sheet1.xml'];
							$('c[r=A1] t', sheet).text(company_name);
							$('row:first c', sheet).attr( 's', '7' ); // first row is bold background gray
							$('c[r="A2"]', sheet).attr( 's', '7' ); // second row is bold background gray
						},		
						exportOptions:{modifier:{order :'index',page: 'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+date,filename: report_name+"-"+date},

						{extend:'pdf',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+date,filename: report_name+"-"+date},
						{extend:'print',exportOptions:{modifier:{order :'index',page:'all',search:'none'},columns:':visible'},title:company_name,messageTop: report_name+"-"+date,filename: report_name+"-"+date},
					]
				}]
			}).container().appendTo($('#export'));
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
	.daterangepicker .custom-range-inputs input {
		width:130px !important;
	}
	select[readonly].select2 + .select2-container {
		  pointer-events: none;
		  touch-action: none;
		}
		input[readonly] {
		  pointer-events: none;
		  touch-action: none;
		}
	}
</style>
<?php $this->load->view("partial/footer"); ?>
