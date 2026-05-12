<?php
class ModelExtensionModuleProstoresubscribe extends Model {

	public function addSubscribe($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_subscribe SET email = '" . $this->db->escape($data['email']) . "', status = '" . (int) $data['status'] . "'");
	}

	public function editSubscribe($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "prostore_subscribe SET status = '" . (int) $data['status'] . "' WHERE email = '" . $this->db->escape($data['email']) . "'");
	}

	public function getSubscribers() {
		$query = $this->db->query("SELECT email FROM " . DB_PREFIX . "prostore_subscribe WHERE status = '0'");

		return $query->rows;
	}

	public function checkEmail($email) {
		$query = $this->db->query("SELECT email FROM " . DB_PREFIX . "prostore_subscribe WHERE email ='" . $this->db->escape($email) . "'");

		return isset($query->row['email']) ? $query->row['email'] : 0;
	}

	public function getAuthDescription($language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_subscribe_auth_description WHERE language_id='" . (int)$language_id . "'");

		return isset($query->row['subscribe_authorization']) ? $query->row['subscribe_authorization'] : '';
	}

	public function getEmailDescription($language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_subscribe_email_description WHERE language_id='" . (int)$language_id . "'");

		return isset($query->row['subscribe_descriptions']) ? $query->row['subscribe_descriptions'] : '';
	}

}

?>
