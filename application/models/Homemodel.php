<?php
class Homemodel extends CI_Model{
	public function today_lead(){
	  $this->db->from('lead_info');
	  $this->db->where('lead_info.status', 1); 
	  return $this->db->get();
	}
	public function open_lead(){
	  $this->db->from('lead_info');
	  $this->db->where('lead_info.status', 1); 
	  return $this->db->get();
	}		
}
?>
