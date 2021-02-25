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
	$uniqueId       = "prime_".$controller_name."_id";
	$filter_cond_array = array('' => '--- Select ---','=' => '=','>' => '>','<' => '<','LIKE' => 'LIKE');
	$filter_tr_line = "";
	$table_map_list = "";
	foreach($fliter_list as $fliter){
		$label_id         = $fliter['label_id'];
		$label_name       = $fliter['label_name'];
		$field_isdefault  = (int)$fliter['field_isdefault'];
		$array_list       = $fliter['array_list'];
		$field_type       = (int)$fliter['field_type'];			
		$cond_id          = $label_id."_cond";		
		$label_id         = "search_".$label_id."_cond";	
		$filter_cond      = form_dropdown(array('name' => $cond_id,"id"=>$label_id,'class' => 'form-control input-sm'), $filter_cond_array);
		$multi_name       = $label_id."[]";
		$table_map_input  = "input[name='$multi_name']";
		if($field_type === 4){							
			$filter_box =  form_input(array("name"=>$multi_name, "id"=>$label_id,"placeholder"=>$label_name, "class"=>"form-control input-sm datepicker"));
			$filter_tr_line .= "<tr>
						<td class='search_td'> $label_name</td>
						<td class='cond_td'>$filter_cond</td>
						<td> $filter_box </td>
					</tr>";
		}else
		if(((int)$field_type === 5) || ((int)$field_type === 7)){
			$filter_box = form_dropdown(array("name" =>$multi_name,"multiple id" => $label_id,"class" =>'form-control input-sm select2'),$array_list);
			$filter_tr_line .= "<tr>
						<td class='search_td'> $label_name</td>
						<td colspan='2'>$filter_box</td>
					</tr>";
			$table_map_input  = "select[name='$multi_name']";
		}else
		if((int)$field_type === 6){
			$form_checkbox = form_checkbox(array("name" => $multi_name,"id" => $label_id, "value"=> 1, "checked" => ($input_value) ? 1 : 0));
			$filter_box .= "<label class='checkbox-inline'> $form_checkbox $form_label </label>";
			$filter_tr_line .= "<tr>
						<td class='search_td'> $label_name</td>
						<td colspan='2'>$filter_box</td>
					</tr>";
		}else
		if($field_type === 13){							
			$filter_box =  form_input(array("name"=>$multi_name, "id"=>$label_id,"placeholder"=>$label_name, "class"=>"form-control input-sm datepicker_time"));
			$filter_tr_line .= "<tr>
						<td class='search_td'> $label_name</td>
						<td class='cond_td'>$filter_cond</td>
						<td> $filter_box </td>
					</tr>";
		}else{
			$filter_box = form_input(array("name"=>$multi_name, "id"=>$label_id,"value"=>'',"placeholder"=>$label_name, "class"=>"form-control input-sm"));
			$filter_tr_line .= "<tr>
						<td class='search_td'> $label_name</td>
						<td class='cond_td'>$filter_cond</td>
						<td> $filter_box </td>
					</tr>";
			
		}
		$cond_map_input  = "select[name='$cond_id']";
		$table_map_list .= $cond_id.': $("'.$cond_map_input.'").map(function(){return $(this).val();}).get() || [""],'."\n";
		$table_map_list .= $label_id.': $("'.$table_map_input.'").map(function(){return $(this).val();}).get() || [""],'."\n";
	}
	$filter_table = "<table class='fliter_table'>$filter_tr_line</table>";
	$table_map    = "return $.extend(arguments[0], {
							start_date: start_date,
							end_date: end_date,
						$table_map_list
					});";
?>
<script src="dist/daterangepicker/knockout.js" type="text/javascript"></script>
<link href="dist/daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<script src="dist/daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function (){
	$("#search_filter_div").hide();
	$("#search_submit").click(function(){
		$("#search_filter_div").toggle()
		table_support.refresh();
	});
	table_support.init({
        resource: '<?php echo site_url($controller_name); ?>',
        headers: <?php echo $table_headers; ?>,
        pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
        uniqueId: "<?php echo $uniqueId;?>",
		queryParams: function(){
            <?php echo $table_map; ?>
        }
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
	});
	$('.select2').select2({
		placeholder: '---- Select ----',
	});
	//date picker new model start mrj -28/02/2020
	var start_date = "<?php echo date('Y-m-d',strtotime('first day of this month')); ?>";
	var end_date   = "<?php echo date('Y-m-d',strtotime('last day of this month')); ?>";	
	$(".daterangepicker-field").daterangepicker({
	locale: { inputFormat: 'DD/MM/YYYY' },
	forceUpdate: true,
	callback: function(startDate, endDate, period){
		var title = startDate.format('DD/MM/YYYY') + ' – ' + endDate.format('DD/MM/YYYY');
		$(this).val(title);
		start_date = startDate.format('YYYY-MM-DD');
		end_date   = endDate.format('YYYY-MM-DD');
		table_support.refresh();
	}
	});
	//date picker new model end mrj -29/02/2020
});
</script>

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
	<?php if((int)$date_filter === 1){ ?>
			<div class="col-md-4" style="width:60% !important;">
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
