<?php
	$this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale, 'selected_printer'=>'invoice_printer'));
	$this->load->view("partial/header");
?>	
<div id='table_holder' style='min-height:500px;'>
	<div class="print_hide" style="text-align:right;padding: 8px;">
		<a href="<?php echo base_url().'index.php/'.$control_name; ?>"class="btn btn-info btn-sm", id="show_booking"><i class="fa fa-tags fa-lg" aria-hidden="true"></i> Go Back</a>
		<a href="javascript:printdoc();" class="btn btn-info btn-sm", id="show_print_button"> <i class="fa fa-print fa-lg"></i> Print</a>
	</div>
	<?php
		if((int)$print_sts !== 1){
			echo "<div class='container' style='text-align:center;font-size:15px;background-color:#f2f2f2;margin-top:21px;padding:15px;border-radius:4px;box-shadow:0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.12), 0 1px 5px 0 rgba(0,0,0,0.2);'>
					Print view not available as of now, Please check with your admin team.
				</div>";
		}else{
			echo $print_design; 
		}
	?>
</div>