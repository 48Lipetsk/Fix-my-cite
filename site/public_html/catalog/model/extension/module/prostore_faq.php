<?php
class ModelExtensionModuleProstoreFaq extends Model {
	public function addFaq($product_id, $data) {
		$sql = "INSERT INTO " . DB_PREFIX . "prostore_faq SET author = '" . $this->db->escape($data['faq_name']) . "', product_id = '" . (int)$product_id . "',";
		if ($this->customer->isLogged()) {
			$sql .= " customer_id = " . (int)$this->customer->getId() . ",";
			if (empty($data['faq_email'])) {
				$data['faq_email'] = $this->customer->getEmail();
			}
		}	
		$sql .= " text = '" . $this->db->escape(strip_tags($data['faq_text'])) . "', email = '" . $this->db->escape($data['faq_email']) . "',";
		$sql .= " date_added = NOW()";

		$this->db->query($sql);

		if ($this->config->get('theme_prostore_product_faq')['status'] && $this->config->get('theme_prostore_product_faq')['email_alert']) {
			$this->load->language('mail/faq');
			$this->load->model('catalog/product');
			
			$product_info = $this->model_catalog_product->getProduct($product_id);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_product'), html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_faqer'), html_entity_decode($data['faq_name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= $this->language->get('text_faq') . "\n";
			$message .= html_entity_decode($data['faq_text'], ENT_QUOTES, 'UTF-8') . "\n\n";

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

	public function getFaqsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$sql = "SELECT r.faq_id, r.author, r.text_admin_answer, r.answer_date_added, r.text, p.product_id, pd.name, p.price, p.image, r.date_added, r.text_admin_answer,r.answer_date_added FROM " . DB_PREFIX . "prostore_faq r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)  WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= " ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}



	public function getFaqsStatsByProductId($product_id) {
		$query = $this->db->query("SELECT rating, COUNT(faq_id) AS totall FROM " . DB_PREFIX . "prostore_faq WHERE product_id = '" . (int)$product_id . "' AND status ='1'   GROUP By rating ORDER BY rating DESC");

		return $query->rows;
	}
			
	public function getTotalFaqsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_faq r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
}