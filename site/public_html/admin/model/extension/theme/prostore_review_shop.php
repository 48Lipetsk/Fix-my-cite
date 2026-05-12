<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ModelExtensionThemeProstoreReviewShop extends Model {
	public function addReview($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "prostore_review_shop SET author = '" . $this->db->escape($data['author']) . "', email = '" . $this->db->escape($data['email']) . "', ";
		$sql .= "customer_id = '" . (int)$this->customer->getId() . "', text = '" . $this->db->escape($data['text']) . "', store_id = '" . (int)$data['store_id'] . "',";
		if (isset($data['r1'])) {
			$sql .= "r1 = '" . (int)$data['r1'] . "', ";
		}
		if (isset($data['r2'])) {
			$sql .= "r2 = '" . (int)$data['r2'] . "', ";
		}	
		if (isset($data['r3'])) {
			$sql .= "r3 = '" . (int)$data['r3'] . "', ";
		}
		if (isset($data['r4'])) {
			$sql .= "r4 = '" . (int)$data['r4'] . "', ";
		}
		if (isset($data['r5'])) {
			$sql .= "r5 = '" . (int)$data['r5'] . "', ";
		}
		if ($data['date_added']) {
			$sql .= " date_added = '" . $this->db->escape($data['date_added']) . "', ";
		}else {
			$sql .= " date_added = NOW(), ";
		} 							
		$sql .= " status = '" . (int)$data['status'] . "', text_admin_answer = '" . $this->db->escape($data['text_admin_answer']) . "'";
		$this->db->query($sql);	

		$this->cache->delete('product');

	}

	public function editReview($review_id, $data) { 
		$sql = "UPDATE " . DB_PREFIX . "prostore_review_shop SET author = '" . $this->db->escape($data['author']) . "', email = '" . $this->db->escape($data['email']) . "', ";
		$sql .= "customer_id = '" . (int)$this->customer->getId() . "', text = '" . $this->db->escape($data['text']) . "', ";
		if (isset($data['r1'])) {
			$sql .= "r1 = '" . (int)$data['r1'] . "', ";
		}
		if (isset($data['r2'])) {
			$sql .= "r2 = '" . (int)$data['r2'] . "', ";
		}	
		if (isset($data['r3'])) {
			$sql .= "r3 = '" . (int)$data['r3'] . "', ";
		}
		if (isset($data['r4'])) {
			$sql .= "r4 = '" . (int)$data['r4'] . "', ";
		}
		if (isset($data['r5'])) {
			$sql .= "r5 = '" . (int)$data['r5'] . "', ";
		}							

		$sql .= " status = '" . (int)$data['status'] . "', store_id = '" . (int)$data['store_id'] . "',";
		if ($data['text_admin_answer']) {
			$sql .= " answer_date_added = NOW(), ";
		}else{
			$sql .= " date_modified = NOW(), ";
		}		
		$sql .= " text_admin_answer = '" . $this->db->escape($data['text_admin_answer']) . "' , date_added = '" . $this->db->escape($data['date_added']) . "' WHERE review_id = '" . (int)$review_id . "'";
		$this->db->query($sql);

		$this->cache->delete('product');
	}

	public function deleteReview($review_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_review_shop WHERE review_id = '" . (int)$review_id . "'");

		$this->cache->delete('product');
	}

		
	public function getReview($review_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_review_shop WHERE review_id=" . (int)$review_id);
            
		return $query->row;
	}

	public function getReviews($data = array()) {
		$roundString = '(r1 + r2 + r3 + r4 + r5)/5,1';
		$activeRInfo = $this->getActiveReviewsR();
		if ($activeRInfo['active_r_total']) {
			$roundString = implode("+",array_keys($activeRInfo['active_r']));
			$roundString = '('.$roundString.')/'.$activeRInfo['active_r_total'].',1';
		}

		$sql = "SELECT *,ROUND($roundString) as rating FROM " . DB_PREFIX . "prostore_review_shop WHERE 1";

		if (!empty($data['filter_store'])) {
			$sql .= " AND store_id = '" . (int)$data['filter_store'] . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'store_id',
			'author',
			'rating',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
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
	}

	public function getActiveReviewsR() {
		$activeRTotal = 0;
		$activeR = array();
		foreach ($this->config->get('theme_prostore_reviews') as $key => $value) {
			if ($value['status']) {
				$activeR[$key] = $value['name'][$this->config->get('config_language_id')];
				$activeRTotal++;
			}
		}
		$activeRInfo = array(
			'active_r'		 => $activeR,
			'active_r_total' => $activeRTotal
		);
		return $activeRInfo;
	}

	public function getTotalReviews($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_review_shop WHERE 1 ";

		if (!empty($data['filter_store'])) {
			$sql .= " AND store_id = '" . (int)$data['filter_store'] . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND `status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalReviewsAwaitingApproval() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_review_shop WHERE status = '0'");

		return $query->row['total'];
	}

	public function getTotalNewReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_review_shop WHERE status = '0'");

		return $query->row['total'];
	}	

}