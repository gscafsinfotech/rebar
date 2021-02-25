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
?>
<script type="text/javascript">
    $(document).ready(function (){
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>
		$("#search_submit").click(function(){
			$("#search_filter_div").toggle()
			table_support.refresh();
		});
		
        table_support.init({
            resource: '<?php echo site_url($controller_name); ?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: "<?php echo $uniqueId;?>",
			queryParams: function () {
                return $.extend(arguments[0], {
                    fliter_label: $("input[name='fliter_label[]']").map(function(){return $(this).val();}).get() || [""],
                    fliter_type: $("input[name='fliter_type[]']").map(function(){return $(this).val();}).get() || [""],
                    input_field_type: $("input[name='input_field_type[]']").map(function(){return $(this).val();}).get() || [""],
                    filter_cond: $("select[name='filter_cond[]']").map(function(){return $(this).val();}).get() || [""],
                    fliter_val: $("input[name='fliter_val[]'],select[name='fliter_val[]']").map(function(){return $(this).val();}).get() || [""],
                });
            }
        });
		//
		$("#search_filter_div").hide();
		$("#search_filter").click(function(){			
			$("#search_filter_div").toggle();
		});		
		$("#clear_search").click(function(){
			$('input').val('');
			$('option').attr('selected', false);
			$("#search_filter_div").toggle();
			table_support.refresh();
		});
		//Date Picker
		$(function () {
			$(".datepicker").datetimepicker({
				format: 'DD-MM-YYYY',
				//debug: true
			});
		});
    });
</script>
<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
	<div class='col-md-10 col-xs-8'>
		<ol class="breadcrumb">
			<?php 
				if($access_add === 1){
			?>
			<li>
				<a class='btn btn-xs btn-primary modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name . "/view"); ?>'
					title='<?php echo " Add ".$page_name; ?>'> <span class="fa fa-user-plus">&nbsp</span><?php echo " Add ". $page_name; ?>
				</a>
			</li>
			<?php 
				}
				
				if($access_import === 1){
					$import_url = site_url($controller_name."/import");
					$submit = $this->lang->line('common_submit');
					echo "<li>
							<a class='btn btn-xs btn-primary modal-dlg' data-btn-submit= '$submit' data-href='$import_url'
								title='Import $page_name'> <span class='fa fa-cloud-upload'>&nbsp</span> Import $page_name
							</a>
						</li>";
				}
				
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
<div id="toolbar" class="form-inline">	
	<?php 
		if($access_delete === 1){
	?>
		<button id="delete" class="btn btn-default btn-sm print_hide">
			<span class="fa fa-trash-o">&nbsp;</span><?php echo $this->lang->line("common_delete"); ?>
		</button>
	<?php 
		}
	?>
	<?php 
		if($access_search === 1){		 
	?>
		<a class="btn btn-sm btn-edit" id="search_filter">
			<i class="fa fa-filter" aria-hidden="true"></i> Search filter
			<span class="caret"></span>
		</a>
		<div id="search_filter_div" class='search_filter'>
			<div style="max-height:250px;overflow: auto;">
				<?php
					$filter_cond_array = array('' => '--- Select ---','=' => '=','>' => '>','<' => '<','LIKE' => 'LIKE');
					$tr_line = "";
					foreach($fliter_list as $fliter){
						$label_id         = $fliter['label_id'];
						$field_isdefault  = $fliter['field_isdefault'];
						$array_list       = $fliter['array_list'];
						$field_type       = $fliter['field_type'];
						
						$label_name = ucwords(strtolower(str_replace("_"," ",$label_id)));
						$fliter_label = form_input(array('type'=>'hidden','name' => 'fliter_label[]', 'class' => 'form-control input-sm','value' => $label_id));
						$fliter_type  = form_input(array('type'=>'hidden','name' => 'fliter_type[]', 'class' => 'form-control input-sm','value' => $field_isdefault));
						$filter_cond  = form_dropdown(array('name' => 'filter_cond[]','class' => 'form-control input-sm'), $filter_cond_array);
						$input_field_type = form_input(array('type' => 'hidden','name' => 'input_field_type[]','class' => 'form-control input-sm datepicker', 'placeholder'=>'Select Date','value' => $field_type));
						if(((int)$field_type === 5) || ((int)$field_type === 7)){
							$fliter_val  = form_dropdown(array('name' => 'fliter_val[]','class' => 'form-control input-sm'), $array_list);
						}else
						if((int)$field_type === 4){
							$fliter_val   = form_input(array( 'name' => 'fliter_val[]', 'class' => 'form-control input-sm datepicker', 'placeholder'=>'Select Date','value' => ''));
						}else{
							$fliter_val   = form_input(array( 'name' => 'fliter_val[]', 'class' => 'form-control input-sm', 'placeholder'=>'Search value','value' => ''));
						}						
						$tr_line .= "<tr>
										<td class='search_td'> $label_name $fliter_label $fliter_type</td>
										<td> $filter_cond</td>
										<td> $fliter_val </td>
									</tr>";
					}
					echo "<table style='width:100%;'>$tr_line</table>";
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
	<?php 
		if($access_search === 1){
			echo ".pull-right.search { display: block !important; }";
		}
		if($access_export === 1){
			echo ".columns.columns-right.btn-group.pull-right{display: block !important;}";
		}
	?>
</style>
<?php $this->load->view("partial/footer"); ?>