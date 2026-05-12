<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ModelExtensionThemeProstoreFaq extends Model {
	public function addFaq($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "prostore_faq SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . (int)$data['product_id'] . "',";
		$sql .= " text = '" . $this->db->escape(strip_tags($data['text'])) . "', status = '" . (int)$data['status'] . "',";
		$sql .= " date_added = '" . $this->db->escape($data['date_added']) . "', store_id = '" . (int)$this->config->get('config_store_id') . "',";
		$sql .= " text_admin_answer = '" . $this->db->escape(strip_tags($data['text_admin_answer'])) . "' , answer_date_added = NOW() ";
	
		$this->db->query($sql);

		$faq_id = $this->db->getLastId($sql);
        
		$this->cache->delete('product');

		return $faq_id;
	}

	public function editFaq($faq_id, $data) {

		$sql = "UPDATE " . DB_PREFIX . "prostore_faq SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . (int)$data['product_id'] . "',";
		$sql .= " text = '" . $this->db->escape(strip_tags($data['text'])) . "', status = '" . (int)$data['status'] . "',";
		$sql .= " date_added = '" . $this->db->escape($data['date_added']) . "',  text_admin_answer = '" . $this->db->escape(strip_tags($data['text_admin_answer'])) . "'";
		if ($data['text_admin_answer']) {
			$sql .= ", answer_date_added = NOW() ";
		}	
		$sql .= " WHERE faq_id = '" . (int)$faq_id . "'";
		$this->db->query($sql);

		$this->cache->delete('product');
	}

	public function deleteFaq($faq_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_faq WHERE faq_id = '" . (int)$faq_id . "'");

		$this->cache->delete('product');
	}

		
	public function getFaq($faq_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT pd.name FROM " . DB_PREFIX . "product_description pd WHERE pd.product_id = r.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS product FROM " . DB_PREFIX . "prostore_faq r WHERE r.faq_id = '" . (int)$faq_id . "'");
            
		return $query->row;
	}

	public function getFaqs($data = array()) {
		$sql = "SELECT r.faq_id, pd.name, r.author, r.text_admin_answer, r.status, r.date_added FROM " . DB_PREFIX . "prostore_faq r LEFT JOIN " . DB_PREFIX . "product_description pd ON (r.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'pd.name',
			'r.author',
			'r.text_admin_answer',
			'r.status',
			'r.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY r.date_added";
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

	public function getTotalFaqs($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_faq r LEFT JOIN " . DB_PREFIX . "product_description pd ON (r.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.author LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalNewFaqs() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_faq WHERE status = '0'");

		return $query->row['total'];
	}
}