<?php 
/**********************************************************
	   Filename: Home
	Description: Chart view and Chart control logic developed, highchart integration based on role.
		 Author: Jaffer Sathik
	 Created on: 10-DEC-2018
	Reviewed by: Udhayakumar Anandhan (REVIEW PENDING)
	Reviewed on:
	Approved by:
	Approved on:
	-------------------------------------------------------
	Modification Details: HIGHCHARTS
	Modification Date: 06/12/2019
	Changed by: SVK AND NEHA
	Change Info: HIGHCHARTS
	-------------------------------------------------------
***********************************************************/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("Action_controller.php");
class Home extends Action_controller {
	public function __construct(){		
		parent::__construct();
		$this->load->model("Homemodel");		
		$this->logged_id       = $this->session->userdata('logged_id');
		$this->logged_role     = $this->session->userdata('logged_role');
		$this->collect_base_info();
	}
	
	public function logout(){
		$this->session->sess_destroy();
		redirect('login');
	}
	
	public function index(){		
		if(!$this->Appconfig->isAppvalid()){			
			redirect('config');
		}		
		$data = "";
		$this->load->view('home',$data);
	}	
	
}

?>
