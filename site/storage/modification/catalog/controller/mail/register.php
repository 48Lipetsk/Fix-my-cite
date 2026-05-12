<?php
class ControllerMailRegister extends Controller {
	public function index(&$route, &$args, &$output) {
		$this->load->language('mail/register');

		$data['text_welcome'] = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_login'] = $this->language->get('text_login');
		$data['text_approval'] = $this->language->get('text_approval');
		$data['text_service'] = $this->language->get('text_service');
		$data['text_thanks'] = $this->language->get('text_thanks');

		$this->load->model('account/customer_group');
			
		if (isset($args[0]['customer_group_id'])) {
			$customer_group_id = $args[0]['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
					
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
		
		if ($customer_group_info) {
			$data['approval'] = $customer_group_info['approval'];
		} else {
			$data['approval'] = '';
		}
			
		$data['login'] = $this->url->link('account/login', '', true);		
		$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');


		//prostore
		$this->load->language('extension/theme/prostore');
		if ($this->config->get('config_store_id')) {
			$store_url = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$store_url = HTTPS_SERVER;
			} else {
				$store_url = HTTP_SERVER;
			}
		}
		$data['text_prostore_mail'] = $this->language->get('text_prostore_mail');
		$data['text_prostore_mail_title'] = $this->language->get('text_prostore_mail_title');
		$data['text_prostore_mail_customer'] = $this->language->get('text_prostore_mail_customer');
		$data['text_prostore_mail_footer'] = $this->language->get('text_prostore_mail_footer');
		$data['text_prostore_support'] = $this->language->get('text_prostore_support');
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_mail_logo'))) {
			$data['logo'] = $store_url . 'image/' . $this->config->get('theme_prostore_mail_logo');
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
		$data['store_name'] = $this->config->get('config_name');
		$data['store_url'] = $store_url;
		//prostore end
			
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($args[0]['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
		$mail->setHtml($this->load->view('mail/register', $data));
		$mail->send(); 
	}
	
	public function alert(&$route, &$args, &$output) {
		// Send to main admin email if new account email is enabled
		if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/register');
			
			$data['text_signup'] = $this->language->get('text_signup');
			$data['text_firstname'] = $this->language->get('text_firstname');
			$data['text_lastname'] = $this->language->get('text_lastname');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_telephone'] = $this->language->get('text_telephone');
			
			$data['firstname'] = $args[0]['firstname'];
			$data['lastname'] = $args[0]['lastname'];
			
			$this->load->model('account/customer_group');
			
			if (isset($args[0]['customer_group_id'])) {
				$customer_group_id = $args[0]['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}
			
			$data['email'] = $args[0]['email'];
			$data['telephone'] = $args[0]['telephone'];


		//prostore
		$this->load->language('extension/theme/prostore');
		if ($this->config->get('config_store_id')) {
			$store_url = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$store_url = HTTPS_SERVER;
			} else {
				$store_url = HTTP_SERVER;
			}
		}
		$data['text_prostore_mail'] = $this->language->get('text_prostore_mail');
		$data['text_prostore_mail_title'] = $this->language->get('text_prostore_mail_title');
		$data['text_prostore_mail_customer'] = $this->language->get('text_prostore_mail_customer');
		$data['text_prostore_mail_footer'] = $this->language->get('text_prostore_mail_footer');
		$data['text_prostore_support'] = $this->language->get('text_prostore_support');
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_mail_logo'))) {
			$data['logo'] = $store_url . 'image/' . $this->config->get('theme_prostore_mail_logo');
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
		$data['store_name'] = $this->config->get('config_name');
		$data['store_url'] = $store_url;
		//prostore end
			
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
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/register_alert', $data));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}	
	}
}		