<?php
class ControllerMailForgotten extends Controller {
	public function index(&$route, &$args, &$output) {			            
		$this->load->language('mail/forgotten');

		$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_change'] = $this->language->get('text_change');
		$data['text_ip'] = $this->language->get('text_ip');
		
		$data['reset'] = str_replace('&amp;', '&', $this->url->link('account/reset', 'code=' . $args[1], true));
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		

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

		$mail->setTo($args[0]);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('mail/forgotten', $data));
		$mail->send();
	}
}
