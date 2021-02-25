<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Secure_Controller extends CI_Controller{
	
	public function __construct($module_id = NULL, $submodule_id = NULL){
		parent::__construct();

		if(!$this->is_logged_in()){
			redirect('login');
		}
		
		$logged_user = $this->get_logged_user_info();		
		if((int)$this->session->userdata('logged_role') === 12){
			$logged_id   = $logged_user->prime_cumstomer_id;
		}else{
			$logged_id   = $logged_user->prime_employees_id;
		}
		
		if(!$this->has_module_grant($module_id, $logged_id)){
			redirect('no_access/' . $module_id . '/' . $submodule_id);
		}
		
		$data['allowed_modules']     = $this->Module->get_allowed_modules($logged_id);
		$data['header_menu']         = $this->Module->get_header_menu($logged_id);
		$data['report_menu']         = $this->Module->get_report_menu($logged_user);
		//$data['template_menu']       = $this->Module->get_template_menu($logged_user);
		//$data['notification_menu']   = $this->Module->get_notification_count();
		$data['company_info']        = $this->Module->get_company_info();
		$data['user_info']       = $logged_user;
		$data['controller_name'] = $module_id;
		$this->load->vars($data);
	}
	
	public function is_logged_in(){
		return ($this->session->userdata('logged_id') != FALSE);
	}

	public function get_logged_user_info(){
		if($this->is_logged_in()){
			return $this->get_info($this->session->userdata('logged_id'));
		}
		return FALSE;
	}
	
	public function get_info($logged_id){
		if((int)$this->session->userdata('logged_role') === 12){
			$this->db->from('cumstomer');			
			$this->db->join('cumstomer_cf', 'cumstomer_cf.prime_cumstomer_id = cumstomer.prime_cumstomer_id');
			$this->db->join('category', 'category.prime_category_id = 12');
			$this->db->where('cumstomer.prime_cumstomer_id', $logged_id);
			$query = $this->db->get();
		}else{
			$this->db->from('employees');
			$this->db->where('employees.prime_employees_id', $logged_id);
			$this->db->join('category', 'category.prime_category_id = employees.role');
			$query = $this->db->get();
		}
		if((int)$query->num_rows() === 1){
			return $query->row();
		}else{
			$person_obj = "";
			return $person_obj;
		}
	}
	
	public function has_module_grant($permission_id, $logged_id){
		if((int)$this->session->userdata('logged_role') === 12){
			$this->db->from('grants_customer');
			$this->db->like('permission_id', $permission_id, 'after');
			$this->db->where('prime_customer_id', $logged_id);
			$query = $this->db->get();
		}else{
			$this->db->from('grants');
			$this->db->like('permission_id', $permission_id, 'after');
			$this->db->where('prime_employees_id', $logged_id);
			$query = $this->db->get();
		}
		if((int)$query->num_rows() > 0){
			return true;
		}else{
			return false;
		}
	}
	
	protected function xss_clean($str, $is_image = FALSE){
		if($this->config->item('cw_xss_clean') == FALSE){
			return $str;
		}else{
			return $this->security->xss_clean($str, $is_image);
		}
	}
	
	public function index() { return FALSE; }
	public function search() { return FALSE; }
	public function suggest_search() { return FALSE; }
	public function view($data_item_id = -1) { return FALSE; }
	public function save($data_item_id = -1) { return FALSE; }
	public function delete() { return FALSE; }
}
?>