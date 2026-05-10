<?php
class ModelExtensionModuleProstoreReviewShop extends Model {
	public function addReview($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "prostore_review_shop SET author = '" . $this->db->escape($data['name']) . "', email = '" . $this->db->escape($data['email']) . "', ";
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
		$sql .= " date_added = NOW()";
		$this->db->query($sql);			

		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/review');
			$this->load->model('catalog/product');
		
			$rating = 0;
			$activeRInfo = $this->getActiveReviewsR();
			if ($activeRInfo['active_r_total']) {
				$marksSum = 0;
				$marksQty = 0;
				foreach ($activeRInfo['active_r'] as $r_id => $r_name) {
					if (isset($data[$r_id])) {
						$marksSum += $data[$r_id];
						$marksQty++;
					}
				}
				if ($marksQty) {
					$rating = round(($marksSum/$marksQty),1);
				}
			}

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_rating'), $rating) . "\n";
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

		$roundString = '(r1 + r2 + r3 + r4 + r5)/5,1';
		$activeRInfo = $this->getActiveReviewsR();
		if ($activeRInfo['active_r_total']) {
			$roundString = implode("+",array_keys($activeRInfo['active_r']));
			$roundString = '('.$roundString.')/'.$activeRInfo['active_r_total'].',1';
		}



			$sql = "SELECT *,ROUND($roundString) as rating FROM " . DB_PREFIX . "prostore_review_shop ";

			$sql .= " WHERE  status = '1' AND store_id = '" . (int)$this->config->get('config_store_id') . "'";

			if ($data['review_id']) {
				$sql .= " AND review_id='" . (int)$data['review_id'] . "'";
			}

			$sql .= " ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

			$query = $this->db->query($sql);
			$reviews_data = $query->rows;


		return $reviews_data;
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

	public function getActiveReviewsAverage($average_rating_by_item) { 
		$activeReviewsAverage = 0;
		$activeRInfo = $this->getActiveReviewsR();
		if ($activeRInfo['active_r_total']) {
			$ratingsSUM = 0;
			foreach ($activeRInfo['active_r'] as $r_id => $r_name) {
				$ratingsSUM += $average_rating_by_item[$r_id];
			}
			$activeReviewsAverage = round($ratingsSUM / $activeRInfo['active_r_total'],1);
		}

		return $activeReviewsAverage;
	}

	public function getRatingAverageByItemTotal() {

		$sql = "SELECT ROUND(AVG(r1),1) as r1,ROUND(AVG(r2),1) as r2,ROUND(AVG(r3),1) as r3,";
		$sql .= "ROUND(AVG(r4),1) as r4, ROUND(AVG(r5),1) as r5 FROM " . DB_PREFIX . "prostore_review_shop ";
		$sql .= "WHERE store_id = '" . (int)$this->config->get('config_store_id') . "'  AND status = '1'";
		$query = $this->db->query($sql);
		return $query->row;
	}	
			
	public function getTotalReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_review_shop  WHERE store_id = '" . (int)$this->config->get('config_store_id') . "'  AND status = '1'");
		return $query->row['total'];
	}
}