<?php
	error_reporting(E_ERROR);
	$dir = dirname(__FILE__);
	chmod($dir, 0777, true);
	$file_name    = $dir."/application/config/database.php";
	$file_name_bk = $dir."/application/config/database_".date('Y-m-d').".php";
	$app_db_filename    = $dir."/app/dbconnect.php";
	$app_db_filename_bk = $dir."/app/dbconnect_".date('Y-m-d').".php";
	copy($file_name,$file_name_bk);
	copy($app_db_filename,$app_db_filename_bk);
	$hostname  = '';
	$username  = '';
	$password  = '';
	$database  = '';
	if(isset($_POST['hostname'])){
		$hostname = $_POST['hostname'];
	}
	if(isset($_POST['username'])){
		$username = $_POST['username'];
	}
	if(isset($_POST['password'])){
		$password = $_POST['password'];
	}
	if(isset($_POST['database'])){
		$database = $_POST['database'];
	}
	$db_info = array('hostname'=>$hostname,'username'=>$username, 'password'=>$password, 'database'=>$database);
	$link = mysqli_connect($db_info['hostname'], $db_info['username'], $db_info['password'], $db_info['database']);
	if(mysqli_connect_errno()){
		$message = mysqli_connect_error();
		echo json_encode(array('success'=>False,'msg'=>$message));
		exit(0);
	}else{
		$write_text = "<?php\n\t defined('BASEPATH') OR exit('No direct script access allowed');\n\t";
		$write_text .= "\$active_group = 'default';\n\t";
		$write_text .= "\$query_builder = TRUE;\n\t";
		$write_text .= "\$db['default'] = array(
						'dsn'	=> '',
						'hostname' => '$hostname',
						'username' => '$username',
						'password' => '$password',
						'database' => '$database',
						'dbdriver' => 'mysqli',
						'dbprefix' => 'cw_',
						'pconnect' => FALSE,
						'db_debug' => (ENVIRONMENT !== 'production'),
						'cache_on' => FALSE,
						'cachedir' => '',
						'char_set' => 'utf8',
						'dbcollat' => 'utf8_general_ci',
						'swap_pre' => '',
						'encrypt' => FALSE,
						'compress' => FALSE,
						'stricton' => FALSE,
						'failover' => array(),
						'save_queries' => TRUE
					);";
		/** SATHISH START **/
		$app_db_text = "<?php \n\t class dbconnect {	
			protected $db;	
			private \$host     = '$hostname';
			private \$username = '$username';
			private \$password = '$password';
			private \$database = '$database';
			
			public function open_db(){
				\$this->db = new mysqli(\$this->host, \$this->username, \$this->password, \$this->database);
				if(mysqli_connect_errno()){
					return false;
				}else{
					return true;
				}
			}
			
			public function runQuery(\$query) {
				\$result = mysqli_query(\$this->db,\$query);
				if(!\$result){
					echo('Error description: '.mysqli_error(\$this->db).'<br/>');
					return false;
				}else{
					return $result;
				}		
			}
			
			public function result(\$result){
				\$data = array();
				while (\$obj = mysqli_fetch_object(\$result)) {
					if(\$obj){				
						\$data[] = \$obj;
					}
				}
				return \$data;
			}
			
			public function runQuery_insert_id(\$query) {
				$result    = mysqli_query(\$this->db,\$query);
				$insert_id = \$this->db->insert_id;
				if(!$result){
					echo('Error description: '.mysqli_error(\$this->db).'<br/>');
					return false;
				}else{
					return $insert_id;
				}
			}
			
			public function num_rows(\$result){
				return mysqli_num_rows(\$result);
			}
			
			public function close_db(){
				mysqli_close(\$this->db);
			}
		} \n\t ?>";
		
		chmod($app_db_filename, 0777, true);
		$app_file = fopen($app_db_filename, "w");
		fwrite($app_file, $app_db_text);
		fclose($app_file);
		chmod($app_file, 0755);		
		/** SATHISH END **/

		$file = fopen($file_name, "w");
		fwrite($file, $write_text);
		fclose($file);
		chmod($file, 0755);
		$message ="DB update successfully!!!";
		echo json_encode(array('success'=>True,'msg'=>$message));
	}
?>