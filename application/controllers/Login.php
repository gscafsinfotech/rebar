<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model('Module');
	}
	
	public function index(){
		$this->login();
	}
	
	public function login(){
		if($this->is_logged_in()){
			redirect('home');
		}else{
			$this->form_validation->set_rules('username', 'lang:login_undername', 'callback_login_check');
    	    $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			if($this->form_validation->run() == FALSE){
				$data['company_info'] = $this->Module->get_company_info();
				$this->load->view('login',$data);
			}else{
				redirect('home');
			}
		}
	}
	
	public function is_logged_in(){
		return ($this->session->userdata('logged_id') != FALSE);
	}
	
	// EMPLOYEE LOGIN
	public function corp_login(){
		$user_name = $this->input->post('corp_user_name');
		$password  = $this->input->post('corp_password');
		$query = $this->db->get_where('employees', array('user_name' => $user_name, 'password' => md5($password), 'trans_status' => 1), 1);
		if($query->num_rows() == 1){
			$logged_user_info = $query->row();
			$this->set_session_value("EMPLOYEE",$logged_user_info);
			echo json_encode(array('success' => TRUE, 'message' => "Login Success !!!"));
		}else{
			echo json_encode(array('success' => false, 'message' => "Invalid user name / password"));
		}
	}
	
	// SET ALL SESSION VALUE FOR EMPLOYEE
	public function set_session_value($logged_type,$logged_user_info){
		if($logged_type === "EMPLOYEE"){
			$this->session->set_userdata('logged_type',$logged_type);
			$this->session->set_userdata('logged_id', $logged_user_info->prime_employees_id);
			$this->session->set_userdata('logged_role', $logged_user_info->role);
			$this->session->set_userdata('logged_user_role', $logged_user_info->user_right);
			$this->session->set_userdata('logged_emp_code', $logged_user_info->employee_code);
			$this->session->set_userdata('logged_branch', $logged_user_info->branch);
			$this->session->set_userdata('logged_reporting', $logged_user_info->reporting);
			$this->session->set_userdata('logged_consultancy', $logged_user_info->consultancy);
			$this->session->set_userdata('logged_dept', $logged_user_info->department);
			$this->session->set_userdata('access_data', $this->get_all_access($logged_type,$logged_user_info->prime_employees_id));
		}
	}
	
	// GET ALL ACCESS FOR EMPLOYEE
	public function get_all_access($logged_type,$logged_id){
		if($logged_type === "EMPLOYEE"){
			$this->db->select('permission_id,access_add,access_update,access_delete,access_search,access_export,access_import');
			$this->db->from('grants');
			$this->db->where('prime_employees_id', $logged_id);
			$access_rslt = $this->db->get()->result();
		}
		$access_info = array();
		if($access_rslt){
			foreach($access_rslt as $key=>$value){
				$permission_id = $value->permission_id;
				$access_add    = $value->access_add;
				$access_update = $value->access_update;
				$access_delete = $value->access_delete;
				$access_search = $value->access_search;
				$access_export = $value->access_export;
				$access_import = $value->access_import;
				$access_info[$permission_id] = array("access_add"=>$access_add,"access_update"=>$access_update,"access_delete"=>$access_delete,"access_search"=>$access_search,"access_export"=>$access_export,"access_import"=>$access_import);
			}
		}
		return 	$access_info;
	}
	
	//PRODUCT KEY IS UPDATED
	public function productkey_save(){
		$company_name     = $this->input->post('company_name');
		$product_key_1    = $this->input->post('product_key_1');
		$product_key_2    = $this->input->post('product_key_2');
		$product_key_3    = $this->input->post('product_key_3');
		$product_key_4    = $this->input->post('product_key_4');
		$product_key      = $product_key_1."".$product_key_2."".$product_key_3."".$product_key_4;
		$activated_date   = date("Y-m-d");
		if($product_key){
			$curl_rslt = $this->curl($company_name,$product_key);
			$product_key     = $curl_rslt[0]['product_key'];
			$activated_date  = $curl_rslt[0]['activated_date'];
			$expire_date     = $curl_rslt[0]['expire_date'];
			$product_info     = array('company_name'=>$company_name,'product_key'=>$product_key,'activated_date'=>$activated_date,'expire_date'=>$expire_date);
			$product_rslt = $this->Module->productkey_save($product_info);
			if($product_rslt){
				echo json_encode(array('success' => TRUE, 'message' => "Product is activated!!!"));
			}else{
				echo json_encode(array('success' => false, 'message' => "Invalid Credential"));
			}
		}else{
			echo json_encode(array('success' => false, 'message' => "Invalid Credential"));
		}
	}
	
	public function curl($company_name,$product_key){
		$product_rslt = $this->Module->get_company_info();
		$product_api  = $product_rslt[0]->product_api;
		$url = $product_api."?reason=generate_key&gen_key=12345&com_info=".$company_name."&key=".$product_key;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result,true);
	}
}
?>