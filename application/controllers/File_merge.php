<?php if ( ! defined('BASEPATH')) exit('No direct script is allowed');
require_once("Base_controller.php");
class File_merge  extends Base_controller{
	
	public function __construct(){
		parent::__construct('file_merge');
		if(!$this->Appconfig->isAppvalid()){
			redirect('config');
		}
	}
	
	public function index(){
		$data['change_log'] = $this->get_change_log();
		$this->load->view("file_merge/manage",$data);
	}
	
	public function process_merge(){
		$this->db->select('module_id,module_name,module_type');
		$this->db->from('modules');
		$this->db->where('trans_status', 1);
		$this->db->where('module_type', 'DYNAMIC');
		$merge_data = $this->db->get();
		$merge_rslt = $merge_data->result();
		$logged_id  = $this->session->userdata('logged_id');
		foreach($merge_rslt as $merge_info){
			$module_id   = $merge_info->module_id;
			$module_name = $merge_info->module_name;
			$module_type = $merge_info->module_type;
			
			$this->creat_file_structure($module_id);
			
			$created_on  = date("Y-m-d H:i:s");
			$insert_data = array('module_id'   => $module_id,'module_name' => $module_name,'module_type' =>$module_type,'trans_created_by' =>$logged_id,'trans_created_date' =>$created_on);
			$this->db->insert('file_merge_log', $insert_data);
		}
		$change_log = $this->get_change_log();
		echo json_encode(array('success' => true,'change_log' => $change_log,'message' => "File merged successfully !!!"));
	}
	
	public function creat_file_structure($module_id){		
		$ucfirst    = ucfirst($module_id);
		$strtolower = strtolower($module_id);
		$controller_file_name = $ucfirst.".php";
		$controller_file_name = $ucfirst.".php";
		
		$controller_file = file_get_contents('module_creation/controllers.php', true);
		$controller_file = str_replace("@MODULE_NAME@",$ucfirst, $controller_file);
		$controller_file = str_replace("@MODULE_NAME_CONSTRUCT@",$strtolower, $controller_file);
		
		fopen("./application/controllers/$controller_file_name", "w");
		file_put_contents("./application/controllers/$controller_file_name",$controller_file);
		
		
		if(!file_exists("./application/views/$strtolower")) {
			mkdir("./application/views/$strtolower", 0777, true);
		}
		$form_file   = file_get_contents('module_creation/form.php', true);
		fopen("./application/views/$strtolower/form.php", "w");
		file_put_contents("./application/views/$strtolower/form.php",$form_file);
		
		$import_file = file_get_contents('module_creation/import.php', true);
		fopen("./application/views/$strtolower/import.php", "w");
		file_put_contents("./application/views/$strtolower/import.php",$import_file);
		
		$manage_file = file_get_contents('module_creation/manage.php', true);
		fopen("./application/views/$strtolower/manage.php", "w");
		file_put_contents("./application/views/$strtolower/manage.php",$manage_file);
		
		$print_file = file_get_contents('module_creation/print.php', true);
		fopen("./application/views/$strtolower/print.php", "w");
		file_put_contents("./application/views/$strtolower/print.php",$print_file);
		return true;
	}
	public function get_change_log(){
		$this->db->select('module_id,module_name,module_type,trans_created_date');
		$this->db->from('file_merge_log');
		$this->db->order_by('trans_created_date','desc');
		$merge_log_data = $this->db->get();
		$merge_log_rslt = $merge_log_data->result();
		$tr_line = "";
		foreach($merge_log_rslt as $log_info){
			$module_id   = $log_info->module_id;
			$module_name = $log_info->module_name;
			$module_type = $log_info->module_type;
			$trans_created_date = $log_info->trans_created_date;
			$trans_created_date = date_create($trans_created_date);
			$trans_created_date = date_format($trans_created_date,"d-m-Y H:i:s");
			$tr_line .= "<tr>
							<td>$module_name</td>
							<td>$module_type</td>
							<td>$trans_created_date</td>
						</tr>";
		}
		$table_info = "<table class='table table-bordered table-striped' id='change_log_table'>
							<thead>
								<tr>
									<th>Module Name</th>
									<th>Module Type</th>
									<th>Log Date & Time</th>
								</tr>
							</thead>
							<tbody>
								$tr_line
							</tbody>
					</table>";
		return $table_info;
	}
}
?>