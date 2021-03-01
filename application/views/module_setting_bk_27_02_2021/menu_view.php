<style>
	.sortable {list-style-type:none;margin:0;padding:0;width: auto;}
	.sortable li{margin: 2px 20px 15px 0; padding: 8px; width: 23%; height: auto; font-size: inherit; box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); background-color: #FFFFFF; border: 0px; border-radius: 2px; cursor: pointer;display: inline-block;}
</style>
<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#main_menu_tab">Main Menu</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#sub_menu_tab">Sub Menu</a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#menu_sort">Menu Sort</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade in active" id="main_menu_tab">
		<?php echo form_open('module_setting/save_menu/' . $prime_module_id,array('id'=>'save_menu','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('menu_name'), 'menu_name', array('class' => 'required'));
					echo form_input( array('name'=>'prime_menu_id', 'id'=>'prime_menu_id', 'type'=>'Hidden','value'=>0));
					echo form_input(array( 'name'=> 'menu_name', 'id' => 'menu_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('menu_name'),'value' => ''));
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('menu_for'), 'menu_for', array('class' => 'required'));
					echo form_dropdown(array('name' => 'menu_for[]','multiple id' =>'menu_for','class' => 'form-control input-sm select2'), $menu_for);
					echo "<label><input name='menu_for_select' id='menu_for_select' type='checkbox'> Select All</label>";
				?>
			</div>
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="menu_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="menu_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style="padding:15px;background-color: #f2f2f2;" id="view_menu_list">
			<?php 
				echo $menu_list;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="sub_menu_tab">
		<?php echo form_open('module_setting/save_sub_menu/' . $prime_module_id,array('id'=>'save_sub_menu','class'=>'form-inline')); ?>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('main_menu'), 'main_menu', array('class' => 'required'));
					echo form_dropdown(array('name' => 'main_menu','id' =>'main_menu','class' => 'form-control input-sm'), $main_menu);
				?>
			</div>
			<div class="form-group">
				<?php
					echo form_label($this->lang->line('menu_name'), 'sub_menu_name', array('class' => 'required'));
					echo form_input( array('name'=>'prime_sub_menu_id', 'id'=>'prime_sub_menu_id', 'type'=>'Hidden','value'=>0));
					echo form_input(array( 'name'=> 'sub_menu_name', 'id' => 'sub_menu_name', 'class' => 'form-control input-sm', 'placeholder'=>$this->lang->line('menu_name'),'value' => ''));
				?>
			</div>			
			<div class="form-group">
				<button class='btn btn-primary btn-sm' id="sub_menu_submit">Add/Update</button>
				<a class='btn btn-danger btn-sm' id="sub_menu_cancel">Cancel</a>
			</div>
		<?php echo form_close(); ?>
		<div style="padding:15px;background-color: #f2f2f2;" id="view_sub_menu_list">
			<?php 
				echo $sub_menu_list;
			?>
		</div>
	</div>
	<div class="tab-pane fade" id="menu_sort">
		<div style="padding:15px;background-color: #f2f2f2;" id="menu_sort">
			<?php 
				$menu_sort_order = $menu_sort_order_list->menu_sort_order;
				echo $menu_sort_order;
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$(function(){
		$('.select2').select2();
		$('.select2-tags').select2({
			tags: true,
			tokenSeparators: [',']
		});
		$(".select2_user").select2({
			tags: true
		});
	});
	$(function(){
		$("#view_sortable").sortable();
		$("#view_sortable").disableSelection();
	});
	$("#menu_for_select").click(function(){
		if($("#menu_for_select").is(':checked') ){
			$("#menu_for > option").prop("selected","selected");
			$("#menu_for").trigger("change");
		}else{
			$("#menu_for > option").removeAttr("selected");
			$("#menu_for").trigger("change");
		}
		$('#menu_for option').filter(function(){
			return !this.value || $.trim(this.value).length == 0;
		}).remove();
		//$("#menu_for>option[value='']").removeAttr("selected");
	});
	$("#menu_cancel").click(function(){
		$("#prime_menu_id").val(0);
		$("#menu_name").val("");
		$("#menu_for").val("");
		$('#menu_for option:selected').removeAttr('selected');
		$(function(){
			$('.select2').select2();
			$('.select2-tags').select2({
				tags: true,
				tokenSeparators: [',']
			});
			$(".select2_user").select2({
				tags: true
			});
		});	
	});
	
	$.validator.addMethod("alphanumeric", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 ]*$/i.test(value);
    }, "Must contain only letters and numbers");
	$.validator.addMethod("space_check", function(value, element) {
        return this.optional(element) || /^(\w+\s?)*\s*$/i.test(value);
    }, "Must contain single space");
	
	$('#save_menu').validate($.extend({
		submitHandler: function (form){
			$("#menu_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#menu_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#menu_submit').attr('disabled',false);
					$("#menu_submit").html("Add/Update");
					if(response.success){
						$("#view_menu_list").html(response.menu_list);
						$("#prime_menu_id").val(0);
						$("#menu_name").val("");
						$("#menu_for").val("");
						toastr.success(response.msg);
					}else{
						toastr.error(response.msg);
					}
					$('#menu_for option:selected').removeAttr('selected');
					common_info();	
				},
				dataType: 'json'
			});
		},
		rules:{
			menu_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
			"menu_for[]": "required",
		}
	}));
	$('#save_sub_menu').validate($.extend({
		submitHandler: function (form){
			$("#sub_menu_submit").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$('#sub_menu_submit').attr('disabled','disabled');
			$(form).ajaxSubmit({
				success: function (response){
					$('#sub_menu_submit').attr('disabled',false);
					$("#sub_menu_submit").html("Add/Update");
					if(response.success){
						$("#view_sub_menu_list").html(response.sub_menu_list);
						$("#prime_sub_menu_id").val(0);
						$("#main_menu").val("");
						$("#sub_menu_name").val("");
						toastr.success(response.msg);
						common_info();
					}else{
						toastr.error(response.msg);
					}
				},
				dataType: 'json'
			});
		},
		rules:{
			main_menu: "required",
			sub_menu_name: {
				required: true,
				alphanumeric:true,
				space_check:true,
			},
		}
	}));
	/* MENU SORTABLE - START */
	var view_idsInOrder = [];
	$( "#view_sortable" ).sortable({
		update: function( event, ui ){
			view_idsInOrder = [];
			$('#view_sortable li').each(function() {
			  view_idsInOrder.push($(this).attr('id'));
			});
			if(view_idsInOrder){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_menu_sortorder"); ?>',
					data: {view_idsInOrder:view_idsInOrder},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
						}
					},
				});
			}
		}
	});
	var view_idsInOrder = [];
	$( "#sub_menu_sortable" ).sortable({
		update: function( event, ui ){
			view_idsInOrder = [];
			$('#sub_menu_sortable li').each(function() {
			  view_idsInOrder.push($(this).attr('id'));
			});
			if(view_idsInOrder){
				$.ajax({
					type: "POST",
					url: '<?php echo site_url($controller_name . "/update_sub_menu_sortorder"); ?>',
					data: {view_idsInOrder:view_idsInOrder},
					success: function(data) {
						var rslt = JSON.parse(data);
						if(rslt.success){
							toastr.success(rslt.message);
						}
					},
				});
			}
		}
	});
	/* MENU SORTABLE - END */
	
	/*SUB MENU ORDER SEARCH */
	<?php 
		$site_url = site_url($controller_name . '/update_menu_order');
		$id_array = $menu_sort_order_list->id_array;
		foreach ($id_array as $id){
			$id_info  = $id;
			$ul_id    = "#$id";
			$li_id    = "#$id li";
			echo "var idsInOrder = [];\n
				$('$ul_id').sortable({
					update: function( event, ui ){
						idsInOrder = [];
						$('$li_id').each(function() {
						  idsInOrder.push($(this).attr('id'));
						});
						if(idsInOrder){
							$.ajax({
								type: 'POST',
								url: '$site_url',
								data: {idsInOrder:idsInOrder,id_info:'$id_info'},
								success: function(data) {
									var rslt = JSON.parse(data);
									if(rslt.success){
										toastr.success(rslt.message);
									}
								},
							});
						}
					}
				});\n";
	}?>
	
});

//COMMON
function common_info(){
	$(function(){
		$('.select2').select2();
		$('.select2-tags').select2({
			tags: true,
			tokenSeparators: [',']
		});
		$(".select2_user").select2({
			tags: true
		});
	});
	$(function(){
		$("#view_sortable").sortable();
		$("#view_sortable").disableSelection();
	});
	
	//update for sub menu sort 09MARCH2019
	$(function(){
		var id_array = "<?php echo $id_array?>";
		$("#sub_menu_sortable").sortable();
		$("#sub_menu_sortable").disableSelection();
		<?php
			foreach($id_array as $id){
				echo "$('#$id').sortable();\n $('#$id').disableSelection();\n";
			}
		?>
	});
}

// VIEW EDIT OPERATION FOR ALL
function get_view_menu_list(prime_menu_id,a_id){
	$("#"+a_id).html("<i class='fa fa-spinner fa-spin fa-2x' style='color:#CC3366'></i>");
	$.ajax({
		type: "POST",
		url: '<?php echo site_url($controller_name . "/get_view_menu_list"); ?>',
		data: {prime_menu_id:prime_menu_id},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				$("#prime_menu_id").val(rslt.menu_result.prime_menu_id);
				$("#menu_name").val(rslt.menu_result.menu_name);
				if(rslt.menu_result.menu_for){
					var menu_for_options = rslt.menu_result.menu_for.split(',');
					for(var i in menu_for_options) {
						var optionVal = menu_for_options[i];
						$("#menu_for").find("option[value='"+optionVal+"']").prop("selected", "selected");
					}
				}
				common_info();
			}			
			$("#"+a_id).html("<i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i>");
		},
	});
}

// VIEW EDIT OPERATION FOR ALL
function get_view_sub_menu_list(prime_menu_id,a_id){
	$("#"+a_id).html("<i class='fa fa-spinner fa-spin fa-2x' style='color:#CC3366'></i>");
	$.ajax({
		type: "POST",
		url: '<?php echo site_url($controller_name . "/get_view_sub_menu_list"); ?>',
		data: {prime_menu_id:prime_menu_id},
		success: function(data) {
			var rslt = JSON.parse(data);
			if(rslt.success){
				$("#prime_sub_menu_id").val(rslt.sub_menu_result.prime_sub_menu_id);
				$("#main_menu").val(rslt.sub_menu_result.map_main_menu);
				$("#sub_menu_name").val(rslt.sub_menu_result.sub_menu_name);				
			}			
			$("#"+a_id).html("<i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i>");
		},
	});
}
</script>