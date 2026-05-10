<?php
class ModelExtensionThemeFoundCheaper extends Model {

	public function editCallCheaper($callcheaper_id,$data) {
      	$this->db->query("UPDATE " . DB_PREFIX . "prostore_callcheaper SET status_id = '" . (int)$data['status_id'] . "', date_modified = NOW() WHERE call_id = '" . (int)$callcheaper_id . "'");	
		}

	
	public function editCallCheapers($callcheaper_id) {
      	$this->db->query("UPDATE " . DB_PREFIX . "prostore_callcheaper SET status_id = '1', date_modified = NOW() WHERE call_id = '" . (int)$callcheaper_id . "'");


	}
	
	public function deleteCallCheaper($callcheaper_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_callcheaper WHERE call_id = '" . (int)$callcheaper_id . "'");

			
		$this->cache->delete('callcheaper');
	}	
	
	public function getCallCheaper($callcheaper_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_callcheaper WHERE call_id = '" . (int)$callcheaper_id . "'");
		
		return $query->row;
	}
	
	public function getCallCheaperNew() {

		$query = $this->db->query("SELECT COUNT(status_id) as total FROM " . DB_PREFIX . "prostore_callcheaper WHERE status_id = '0'");
		
		return $query->row['total'];
	}
	
	public function getCallCheapers($data = array()) {

		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "prostore_callcheaper";
			
			$sort_data = array(
				'call_id',
				'store_id',
				'name',
				'telephone'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY call_id";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}					

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}				
			
			$query = $this->db->query($sql);
		
			return $query->rows;
		} else {
			$callcheaper_data = $this->cache->get('callcheaper');
		
			if (!$callcheaper_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_callcheaper ORDER BY call_id");
	
				$callcheaper_data = $query->rows;
			
				$this->cache->set('callcheaper', $callcheaper_data);
			}
		 
			return $callcheaper_data;
		}
	}

	public function getTotalCallCheapers() {

      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_callcheaper");

      	if ($query->row['total']) { // Change table to new format if need
      	 	$queryCheck = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_callcheaper");
      	 	if (!isset($queryCheck->row['store_id'])) {
      	 		$this->db->query("ALTER TABLE " . DB_PREFIX . "prostore_callcheaper ADD `store_id` INT(2) NOT NULL DEFAULT '0' AFTER `status_id`");
      	 	}
      	 } 
		
		return $query->row['total'];
	}	
}
?>
