<?php
class ModelCatalogReview extends Model {
	public function addReview($product_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		//prostore
		$this->db->query("INSERT INTO " . DB_PREFIX . "review_extls SET review_id = '" . (int)$review_id . "', text_plus  = '" . $this->db->escape($data['text_plus']) . "', text_minus = '" . $this->db->escape($data['text_minus']) . "'");
		// prostore end			
			

		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/review');
			$this->load->model('catalog/product');
			
			$product_info = $this->model_catalog_product->getProduct($product_id);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_product'), html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_rating'), $data['rating']) . "\n";
			$message .= $this->language->get('text_review') . "\n";
			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}


// prostore

	public function getTotalReviewsByProductIdLs($product_id, $rating = 0) {

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if ($rating) {
			$sql .= " AND r.rating = '" . (int)$rating . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getReviewsByProductIdLs($product_id, $start = 0, $limit = 20, $rating = 0) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$sql = "SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added, re.text_plus, re.text_minus, re.text_admin_answer,re.answer_date_added, re.count_bad, re.count_good FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "review_extls re ON (r.review_id = re.review_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if ($rating) {
			$sql .= " AND r.rating = '" . (int)$rating . "'";
		}

		$sql .= " ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}



	public function getReviewsStatsByProductId($product_id) {
		$query = $this->db->query("SELECT rating, COUNT(review_id) AS totall FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "' AND status ='1'   GROUP By rating ORDER BY rating DESC");

		return $query->rows;
	}

	public function getReviews($data) {
		if ($data['start'] < 0) {
			$start = 0;
		}else{
			$start = $data['start'];
		}

		if ($data['limit'] < 1) {
			$limit = 20;
		}else{
			$limit = $data['limit'];
		}

		$reviews_data = $this->cache->get('899.reviews.' . (int)$data['filter_category_id'] . '.' . (int)$data['filter_sub_category'] . '.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit);

		if (!$reviews_data) {


			$sql = "SELECT DISTINCT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added, re.text_plus, re.text_minus, re.count_bad, re.count_good FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "review_extls re ON (r.review_id = re.review_id) ";

			if ($data['filter_category_id']) {
				if ($data['filter_sub_category']) {
					$sql .= "  LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id)";
				} else {
					$sql .= " LEFT JOIN  " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
				}
			}

			$sql .= " WHERE p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if ($data['filter_category_id']) {
				if ($data['filter_sub_category']) {
					$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}

			$sql .= " ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

$query = $this->db->query($sql);
			$reviews_data = $query->rows;

		    $this->cache->set('899.reviews.' . (int)$data['filter_category_id'] . '.' . (int)$data['filter_sub_category'] . '.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit, $reviews_data);

		}

		return $reviews_data;
	}


// prostore end			
			
	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}