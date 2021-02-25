<?php 
	$this->load->view("partial/header"); 

	$access_data    = $this->session->userdata('access_data');
	$access_add     = (int)$access_data[$controller_name]['access_add'];
	$access_update  = (int)$access_data[$controller_name]['access_update'];
	$access_delete  = (int)$access_data[$controller_name]['access_delete'];
	$access_search  = (int)$access_data[$controller_name]['access_search']; 
	$access_export  = (int)$access_data[$controller_name]['access_export'];
	$page_name      = "Screen Setting";
?>
<script type="text/javascript">
    $(document).ready(function (){
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>
        table_support.init({
            resource: '<?php echo site_url($controller_name); ?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: '<?php echo "prime_".$controller_name."_id";?>',
        });
    });

</script>
<div class='row title_content'>
	<div class='col-md-2 col-xs-4'>
		<h1 class='page_txt'><?php echo $page_name;?></h1>
	</div>
	<div class='col-md-10 col-xs-8'>
		<ol class="breadcrumb">			
			<li><a href="<?php echo site_url()?>#Home">Home</a></li>
			<li><a href="<?php echo site_url($controller_name)?>#<?php echo "$controller_name";?>"><?php echo "$page_name";?></a></li>
			<li class="active">List</li>
		</ol>
	</div>
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