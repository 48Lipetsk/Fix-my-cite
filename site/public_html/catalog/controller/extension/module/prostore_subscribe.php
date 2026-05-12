<?php
class ControllerExtensionModuleProstoreSubscribe extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/theme/prostore');

		$this->load->model('catalog/information');

		$data['text_prostore_subscribe_btn'] = $this->language->get('text_prostore_subscribe_btn');
		$data['text_footer_subscribe_email'] = $this->language->get('text_footer_subscribe_email');
		
		$data['subscribe_status'] = $this->language->get('theme_prostore_subscribe_status');
		$data['subscribe_title'] = $this->config->get('theme_prostore_subscribe_title' . $this->config->get('config_language_id'));
		$data['subscribe_subtitle'] = $this->config->get('theme_prostore_subscribe_subtitle' . $this->config->get('config_language_id'));

		if ($this->config->get('theme_prostore_subscribe_pdata')) {
			$information_info = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_subscribe_pdata'));

			if ($information_info) {
				$data['text_prostore_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('text_prostore_subscribe_btn'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_subscribe_pdata'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_prostore_pdata'] = '';
			}
		} else {
			$data['text_prostore_pdata'] = '';
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_subscribe_email_logo'))) {
			$data['subscribe_email_logo'] = $server . 'image/' . $this->config->get('theme_prostore_subscribe_email_logo');
		} else {
			$data['subscribe_email_logo'] = '';
		}
		
		return $this->load->view('extension/module/prostore_subscribe', $data);
	}

	public function addsubscribe() {
		$json = array();
  
		$this->load->model('extension/module/prostoresubscribe');
//		$this->load->model('extension/module');

		$this->load->language('extension/module/subscribe');

		$email_subscriber = '';
		if(isset($this->request->post['email'])){
			$email_subscriber = $this->request->post['email'];
		}

		if ((utf8_strlen($email_subscriber) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email_subscriber)) {
			$json['error'] = $this->language->get('error_email');
		} elseif ($this->model_extension_module_prostoresubscribe->checkEmail($email_subscriber)) {
			$json['error'] = $this->language->get('error_customer');
		} 

		if (!$json ) {

			$email_alert = $this->config->get('theme_prostore_subscribe_email_alert');
			$subscribe_confirm = 0;

			if ($subscribe_confirm) {
				$json['success'] = $this->language->get('text_subscribe_confirm');
				$status = 0;
			} else {
				$json['success'] = $this->language->get('text_success');
				$status = 1;
			}

			$dataF = array(
				'email' => $email_subscriber,
				'status' => $status
			);


			$this->model_extension_module_prostoresubscribe->addSubscribe($dataF);

			$subject = sprintf($this->language->get('text_hello_subscriber'), $this->config->get('config_name'));

			/* client software does not support HTML email - derive the minimum information - $text */

			if ($subscribe_confirm) {
				$link = $this->url->link('module/subscribe/confirmSubscribe&key=' . $this->keyCoding($email_subscriber), '', true);

				$text = $this->language->get('text_subscribe_active') . $link . "\n\n";
				$html_message = sprintf($this->language->get('text_subscribe_active_html'), $link);
			} else {
				$text = sprintf($this->language->get('text_hello_subscriber'), $this->config->get('config_name')) . "\n\n";
				$html_message = sprintf($this->language->get('text_hello_subscriber'), $this->config->get('config_name'));
			}

			$text_mail = $this->model_extension_module_prostoresubscribe->getEmailDescription((int) $this->config->get('config_language_id'));

			$html = '<html dir="ltr" lang="en">' . "\n";
			$html .= '  <head>' . "\n";
			$html .= '    <title>' . $subject . '</title>' . "\n";
			$html .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$html .= '  </head>' . "\n";
			$html .= '  <body><div>' .  html_entity_decode($text_mail, ENT_QUOTES, 'UTF-8') . '</div></body>' . "\n";
			$html .= '</html>' . "\n";

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			$mail->setTo($email_subscriber);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($subject);
			$mail->setHtml($html);
			$mail->setText($text);
			$mail->send();

			if ($email_alert) {
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($this->config->get('config_name'));
				$mail->setSubject(sprintf($this->language->get('text_email_subject_shop'), $this->config->get('config_name', $email_subscriber)));
				$mail->setText(sprintf($this->language->get('text_email_text'), $email_subscriber));
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

		$this->response->setOutput(json_encode($json));
	}

}

?>