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
		$("#db_config").submit(function(event){ event.preventDefault(); }).validate({
			rules:{
				hostname:'required',
				username:'required',
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
<div class='row title_content'>
	<div class='col-md-4 col-xs-4'>
		<h1 class='page_txt'>Dynamic DB Configuration</h1>
	</div>
</div>
<div id="holder" class="form-inline" style="margin-top:20px;">
	<div class='col-md-12'style='margin:15px 0px;border-radius:2px;box-shadow:0 2px 2px 0 rgba(0,0,0,0.14),0 3px 1px -2px rgba(0,0,0,0.12),0 1px 5px 0 rgba(0,0,0,0.2);padding:15px 0px;'>
		<?php  echo  form_open("$controller_name/db_config/",array("id"=>"db_config","class"=>"form-inline")); ?>
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
			<button class='btn btn-primary btn-sm' id="submit" style='margin-top:20px;'>Submit</button>
		</div>
		<?php echo form_close(); ?>
	</div>
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