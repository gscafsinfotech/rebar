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
<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
</div>
<div class="row" style="margin:0px;padding:10px;">
	<div class='col-md-12' style='box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2); padding: 15px; border-radius: 3px;margin-bottom:15px;'>
		<button class='btn btn-sm btn-primary' id='process_merge'>Merge Files</button>
		<div class='row' style='margin:0px;padding:8px 0px;' id='change_log'>
			<?php echo $change_log;?>
		</div>
	</div>
</div>
<script>
$(document).ready(function() {
	$('#change_log_table').DataTable({
		"lengthChange": true,
		"pageLength": 50,
		"order": [[ 2, "desc" ],[ 0, "desc" ],[ 1, "desc" ]]
	});		
	$('#process_merge').click(function(){
		if(confirm("Are you sure you want to Merge File?")){
			$("#process_merge").html("<i class='fa fa-spinner fa-spin'></i> Processing...");
			$("#process_merge").attr('disabled','disabled');
			$.ajax({
				type: "POST",
				url: '<?php echo site_url("file_merge/process_merge"); ?>',
				success: function(data){
					$("#process_merge").attr('disabled',false);
					$("#process_merge").html("Merge Files");
					var rslt = JSON.parse(data);
					if(rslt.success){
						$("#change_log").html(rslt.change_log);
						$('#change_log_table').DataTable({
							"lengthChange": true,
							"pageLength": 50,
							"order": [[ 2, "desc" ]]
						});
						toastr.success(rslt.message);
					}else{
						toastr.error(rslt.message);
					}
				}
			});
		}
		else{
			toastr.error("Merge process treminated...");
		}		
	});
});	
</script>
<?php $this->load->view("partial/footer"); ?>