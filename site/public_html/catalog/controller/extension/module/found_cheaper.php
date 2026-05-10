<?php 
class ControllerExtensionModuleFoundCheaper extends Controller { 
	private $error = array();
 
	public function index($inputData = array()) {
   		$this->language->load('extension/module/found_cheaper');
		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['action']) && $this->config->get('theme_prostore_found_cheaper')['status']) {
			if ($this->validate()) {
				$data = array();
				if (isset($this->request->post['name'])) {
  		    		$data['name'] = $this->request->post['name'];
				} else {
      				$data['name'] = '';
    			}
				if (isset($this->request->post['phone'])) {
      				$data['phone'] = $this->request->post['phone'];
				} else {
      				$data['phone'] = '';
    			}
				if (isset($this->request->post['email'])) {
      				$data['email'] = $this->request->post['email'];
				} else {
      				$data['email'] = '';
    			}
				if (isset($this->request->post['link'])) {
					$data['link'] = $this->request->post['link'];
		  		} else {
					$data['link'] = '';
		 		}  
				 if (isset($this->request->post['product_id'])) {
					$data['product_id'] = $this->request->post['product_id'];
		  		} else {
					$data['product_id'] = '0';
		 		}  				
				
				$this->load->model('extension/module/prostore');
				$results = $this->model_extension_module_prostore->addCallCheaper($data);
				if ($this->config->get('theme_prostore_found_cheaper')['email_alert']) {
					$this->sendMail($data);
				}
				$json['success'] = $this->language->get('success');
			}else{
				$json['warning'] = $this->error;
			}

			return $this->response->setOutput(json_encode($json));
		}

		if ($this->customer->isLogged()) {
			$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			$data['customer_email'] = $this->customer->getEmail();
			$data['customer_phone'] = $this->customer->getTelephone();
		} else {
			$data['customer_name'] = '';
			$data['customer_email'] = '';
			$data['customer_phone'] = '';
		}

		$data['product_id'] = $inputData['product_id'];
		
		// Captcha
		if ($this->config->get('theme_prostore_found_cheaper')['captcha'])  {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_found_cheaper')['captcha']);

		} else {
			$data['captcha'] = '';
		}

		return $this->load->view('extension/module/found_cheaper', $data);		
				
  	}

  	private function validate() {
   		$this->language->load('extension/module/found_cheaper');
   	 	if ((strlen(utf8_decode($this->request->post['name'])) < 1) || (strlen(utf8_decode($this->request->post['name'])) > 32)) {
      			$this->error['name'] = $this->language->get('mister');
    		}
    		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
      			$this->error['email'] = $this->language->get('wrong_email');
    		}
			$regex = '%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';
			if (!preg_match($regex,$this->request->post['link'])) {
				$this->error['link'] = $this->language->get('wrong_link');
		  	}			
		
			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('theme_prostore_found_cheaper')['captcha'] . '_status')) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_found_cheaper')['captcha'] . '/validate');

				if ($captcha) {
					$this->error['captcha'] = $captcha;
				}
			}

    		if (!$this->error) {
     	 		return true;
    		} else {
     			return false;
   	 	}
	}
  	private function sendMail($data) {
		$subject = $this->language->get('subject');
		$text 	= $this->language->get('text_1');
		$text 	.= $this->language->get('subject') . ":\n";
		$text 	.= $this->language->get('firstname_1') . $data['name'] . "\n";
		$text 	.= $this->language->get('email') . $data['email'] . "\n";
		if ($data['phone']) {
			$text 	.= $this->language->get('phone') . $data['phone'] . "\n";
		}
		$text 	.= $this->language->get('link') . $data['link'] . "\n";

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
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
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
?>
