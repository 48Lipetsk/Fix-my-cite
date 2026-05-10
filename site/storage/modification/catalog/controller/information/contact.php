<?php
class ControllerInformationContact extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		
		// prostore
		
		if ($this->config->get('theme_prostore_contact_meta_title' . $this->config->get('config_language_id'))) {
			$this->document->setTitle($this->config->get('theme_prostore_contact_meta_title' . $this->config->get('config_language_id')));
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}
		
		if ($this->config->get('theme_prostore_contact_meta_description' . $this->config->get('config_language_id'))) {
			$this->document->setDescription($this->config->get('theme_prostore_contact_meta_description' . $this->config->get('config_language_id')));
		}
		
		if ($this->config->get('theme_prostore_contact_meta_keyword' . $this->config->get('config_language_id'))) {
			$this->document->setKeywords($this->config->get('theme_prostore_contact_meta_keyword' . $this->config->get('config_language_id')));
		}
		
		// prostore end

			

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['email']);
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->request->post['enquiry']);
			$mail->send();

			$this->response->redirect($this->url->link('information/contact/success'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		

		// prostore
		$this->load->language('extension/theme/prostore');
		$data['schema'] = $this->config->get('theme_prostore_schema');
		$data['shop_email'] = $this->config->get('config_email');
		$data['text_prostore_support'] = $this->language->get('text_prostore_support');
		$data['text_prostore_con_soc'] = $this->language->get('text_prostore_con_soc');
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

		$data['our_details'] = array();
		$our_details = $this->config->get('theme_prostore_our_details');
		if ($our_details) {
			$detailSort  = array_column($our_details, 'sort');
			array_multisort($detailSort, SORT_ASC, $our_details);
			$data['our_details'] = $our_details;
		}

		$this->load->model('setting/setting');
		$data['social_navs'] = array();
		$social_links = $this->model_setting_setting->getSetting('theme_prostoresoclinks');
		$data['social_links'] = $social_links['theme_prostoresoclinks_array'];
		$data['social_navs'] = $this->config->get('theme_prostore_social_nav');	
		$data['zoom'] = $this->config->get('theme_prostore_contact_zoom');
		$data['zoom_control'] = $this->config->get('theme_prostore_contact_zoom_control');
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_contact_map_icon'))) {
			$data['contact_map_icon'] = (isset($this->request->server['HTTPS']) ? HTTPS_SERVER : HTTP_SERVER) . 'image/' . $this->config->get('theme_prostore_contact_map_icon');
		} else {
			$data['contact_map_icon'] = '';
		}
		$data['api_key'] = $this->config->get('theme_prostore_contact_api_key');
		$data['contact_map'] = $this->config->get('theme_prostore_contact_map');
		if ($this->config->get('theme_prostore_contact_pdata')) {
			$this->load->language('extension/theme/prostore');
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_contact_pdata'));

			if ($information_info) {
				$data['text_prostore_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('button_prostore_sendbutton'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_contact_pdata'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_prostore_pdata'] = '';
			}
		} else {
			$data['text_prostore_pdata'] = '';
		}
		$data['language_id'] = $this->config->get('config_language_id');
		$data['address_schema'] = str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($this->config->get('config_address'), ENT_QUOTES, 'UTF-8')));
		$data['open_html'] = html_entity_decode($this->config->get('config_open'), ENT_QUOTES, 'UTF-8');
		// prostore end
			
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}

		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = $this->url->link('information/contact', '', true);

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}
		


		// prostore
		$data['geocodeY']  = $data['geocode'];
		if ($data['contact_map'] == 'yandex_map' && $data['geocode']) {
			$tempcode = explode(',', $data['geocode']);
			if (isset($tempcode[1])) {
				$data['geocodeY'] = $tempcode[1] . ',' . $tempcode[0]; 
			}
		}
		// prostore end
			
		$data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
				} else {
					$image = false;
				}


		// prostore
					if ($data['contact_map'] == 'yandex_map' && $location_info['geocode']) {
						$tempcode = explode(',', $location_info['geocode']);
						if (isset($tempcode[1])) {
							$location_info['geocode'] = $tempcode[1] . ',' . $tempcode[0]; 
						}
					}
		// prostore end
			
				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),

'open_html'        => html_entity_decode($location_info['open']), // prostore add
'address_schema'     => str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($location_info['address'], ENT_QUOTES, 'UTF-8'))), // prostore add
			
					'comment'     => $location_info['comment']
				);
			}
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/contact', $data));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}


		// Agree to terms
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		
		return !$this->error;
	}

	public function success() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

 		$data['text_message'] = $this->language->get('text_message'); 

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
