<?php
class ControllerExtensionTotalVoucher extends Controller {
	public function index() {
		if ($this->config->get('total_voucher_status')) {
			$this->load->language('extension/total/voucher');

			if (isset($this->session->data['voucher'])) {
				$data['voucher'] = $this->session->data['voucher'];
			} else {
				$data['voucher'] = '';
			}


// prostore
			$this->load->language('extension/theme/prostore');
// prostore end		
			
			return $this->load->view('extension/total/voucher', $data);
		}
	}

	public function voucher() {
		$this->load->language('extension/total/voucher');

		$json = array();

		$this->load->model('extension/total/voucher');

		if (isset($this->request->post['voucher'])) {
			$voucher = $this->request->post['voucher'];
		} else {
			$voucher = '';
		}

		$voucher_info = $this->model_extension_total_voucher->getVoucher($voucher);

		if (empty($this->request->post['voucher'])) {
			$json['error'] = $this->language->get('error_empty');
		} elseif ($voucher_info) {
			$this->session->data['voucher'] = $this->request->post['voucher'];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->language->get('error_voucher');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function send($route, $args, $output) {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($args[0]);

		// If order status in the complete range create any vouchers that where in the order need to be made available.
		if (in_array($order_info['order_status_id'], $this->config->get('config_complete_status'))) {
			$voucher_query = $this->db->query("SELECT *, vtd.name AS theme FROM `" . DB_PREFIX . "voucher` v LEFT JOIN " . DB_PREFIX . "voucher_theme vt ON (v.voucher_theme_id = vt.voucher_theme_id) LEFT JOIN " . DB_PREFIX . "voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) WHERE v.order_id = '" . (int)$order_info['order_id'] . "' AND vtd.language_id = '" . (int)$order_info['language_id'] . "'");

			if ($voucher_query->num_rows) {
				// Send out any gift voucher mails
				$language = new Language($order_info['language_code']);
				$language->load($order_info['language_code']);
				$language->load('mail/voucher');

				foreach ($voucher_query->rows as $voucher) {
					// HTML Mail
					$data = array();

					$data['title'] = sprintf($language->get('text_subject'), $voucher['from_name']);

					$data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']));
					$data['text_from'] = sprintf($language->get('text_from'), $voucher['from_name']);
					$data['text_message'] = $language->get('text_message');
					$data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher['code']);
					$data['text_footer'] = $language->get('text_footer');

					if (is_file(DIR_IMAGE . $voucher['image'])) {
						$data['image'] = $this->config->get('config_url') . 'image/' . $voucher['image'];
					} else {
						$data['image'] = '';
					}

					$data['store_name'] = $order_info['store_name'];
					$data['store_url'] = $order_info['store_url'];
					$data['message'] = nl2br($voucher['message']);


		//prostore
		$language->load('extension/theme/prostore');
		
		$data['text_prostore_mail'] = $language->get('text_prostore_mail');
		$data['text_prostore_mail_title'] = $language->get('text_prostore_mail_title');
		$data['text_prostore_mail_customer'] = $language->get('text_prostore_mail_customer');
		$data['text_prostore_mail_footer'] = $language->get('text_prostore_mail_footer');
		$data['text_prostore_support'] = $language->get('text_prostore_support');
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_mail_logo'))) {
			$data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('theme_prostore_mail_logo');
		} else {
			$data['logo'] = '';
		}
		$data['header_text_logo'] = html_entity_decode($this->config->get('theme_prostore_header_text_logo'), ENT_QUOTES, 'UTF-8');
		$data['prostore_mail_text'] = html_entity_decode($this->config->get('theme_prostore_mail_text' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');
		$data['prostore_mail_about'] = html_entity_decode($this->config->get('theme_prostore_mail_about' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');
		$data['theme_color'] = $this->config->get('theme_prostore_color');
		if ($this->config->get('theme_prostore_color')) {
			if ($this->config->get('theme_prostore_color') == 'custom') {
				$data['theme_color'] = $this->config->get('theme_prostore_custom_color_1');
				//$data['theme_color_2'] = $this->config->get('theme_prostore_custom_color_2');
				//$data['theme_color_3'] = $this->config->get('theme_prostore_custom_color_3');
			}else{
				$colors = explode(' ', $this->config->get('theme_prostore_color'));
				$data['theme_color'] = $colors[0];
				//$data['theme_color_2'] = $colors[1];
				//$data['theme_color_3'] = $colors[2];
			}
		}

		$data['theme_prostore_checkout_st3'] = $this->config->get('theme_prostore_checkout_st3_sa');
		
		$data['prostore_phones'] = array();
		$data['prostore_phones_main'] = array();
		$prostore_phones = $this->config->get('theme_prostore_phones');

		if ($prostore_phones) {
		  foreach ($prostore_phones as $key => $prostore_phone) {
			$data['prostore_phones'][$prostore_phone['sort']] = $prostore_phone;
		  }
		  ksort($data['prostore_phones']);
		  $data['prostore_phones_main'] = current($data['prostore_phones']);
		}
		
		$data['config_email'] =  $this->config->get('config_email');
		$data['language_id'] = $this->config->get('config_language_id');
		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		//prostore end
			
					$mail = new Mail($this->config->get('config_mail_engine'));
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

					$mail->setTo($voucher['to_email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $voucher['from_name']), ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($this->load->view('mail/voucher', $data));
					$mail->send();
				}
			}
		}
	}
}
