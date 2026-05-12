<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('setting/setting');
		$this->load->model('extension/module/prostorenews');//!!!
			

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}


// prostore
		if (! function_exists('array_column')) {
		    function array_column(array $input, $columnKey, $indexKey = null) {
		        $array = array();
		        foreach ($input as $value) {
		            if ( !array_key_exists($columnKey, $value)) {
		                trigger_error("Key \"$columnKey\" does not exist in array");
		                return false;
		            }
		            if (is_null($indexKey)) {
		                $array[] = $value[$columnKey];
		            }
		            else {
		                if ( !array_key_exists($indexKey, $value)) {
		                    trigger_error("Key \"$indexKey\" does not exist in array");
		                    return false;
		                }
		                if ( ! is_scalar($value[$indexKey])) {
		                    trigger_error("Key \"$indexKey\" does not contain scalar value");
		                    return false;
		                }
		                $array[$value[$indexKey]] = $value[$columnKey];
		            }
		        }
		        return $array;
		    }
		}
// prostore
			
		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');

		// prostore
		$data['language_id'] = $this->config->get('config_language_id');
		$data['buy_click'] = array();
		if($this->config->get('theme_prostore_buy_click')){
			$this->load->language('extension/theme/prostore');
			$this->load->language('checkout/checkout');
			$data['entry_firstname'] = $this->language->get('entry_firstname');
			$data['entry_lastname'] = $this->language->get('entry_lastname');
			$data['entry_email'] = $this->language->get('entry_email');
			$data['entry_telephone'] = $this->language->get('entry_telephone');
			$data['entry_comment'] = $this->language->get('entry_comment');
			$data['text_prostore_buy_click'] = $this->language->get('text_prostore_buy_click');
			$data['button_fastorder_sendorder'] = $this->language->get('button_prostore_sendorder');
			$data['buy_click'] = $this->config->get('theme_prostore_buy_click');

			// Captcha
			if ($this->config->get('theme_prostore_config_captcha_fo'))  {
				$data['captcha_fo'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_config_captcha_fo'));

			} else {
				$data['captcha_fo'] = '';
			}
						
			if ($this->customer->isLogged()) {
				$this->load->model('account/customer');
				$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
			}
			$data['buyclick_form'] = $this->load->view('product/buyclick_form', $data);
		}	

		$this->load->language('extension/theme/prostore');
		$data['js_codes'] = $this->document->getScripts('js_code'); 
		$data['styles'] = $this->document->getStyles();

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$data['version'] = $this->config->get('theme_prostore_version');

		$data['top_links'] = array();
		if($this->config->get('theme_prostorelinks_array')){
			$data['top_links'] = $this->config->get('theme_prostorelinks_array');
			$data['top_links'] = $data['top_links'][$this->config->get('config_language_id')];
			foreach($data['top_links'] as $key => $link){ 
				if(strpos($key,'e') === 0 ){ continue;}
				$data['top_links'][$key][key($link)] = $this->url->link(current($link));	
			}
		}

		$this->load->model('extension/module/prostore');

					
		$data['footer_navs'] = array();
		$footer_navs = $this->config->get('theme_prostore_footer_nav');
		if(isset($footer_navs)){
			foreach($footer_navs as $footer_nav){
				
				if(isset($footer_nav['type'][0]['links'])){
					foreach($footer_nav['type'][0]['links'] as $key => $link){
						if (isset($data['top_links'][$key]['target'])) {
							$footer_nav['target'][$key] = $data['top_links'][$key]['target'];
						}else{
							$footer_nav['target'][$key] = '';
						}
						$footer_nav['type'][0]['names'][$key] = $this->model_extension_module_prostore->getName4MenuItem($key,$data);
						if(strpos($key,'e') === 0 ){ // Extralinks do not change
							if (isset($data['top_links'][$key])) {
								$footer_nav['type'][0]['links'][$key] = current($data['top_links'][$key]);
							  }else{
								$footer_nav['type'][0]['links'][$key] = '';
							  } 							
							continue;
						}
						$footer_nav['type'][0]['links'][$key] = $this->url->link($link);
					}					
				}
				if(isset($footer_nav['type'][1]['links']['html'])){
					if ($footer_nav['type'][1]['links']['html']) {
						$footer_nav['type'][1]['links']['html'] = html_entity_decode($footer_nav['type'][1]['links']['html'][$data['language_id']]);
					}
				}

				$data['footer_navs'][$footer_nav['sort']] = $footer_nav;
			}
		}
		ksort($data['footer_navs']);

		//Социальные сети
		$data['social_navs'] = array();
		$social_links = $this->model_setting_setting->getSetting('theme_prostoresoclinks');
		$data['social_links'] = $social_links['theme_prostoresoclinks_array'];
		$data['social_navs'] = $this->config->get('theme_prostore_social_nav');	

		$data['messenger_navs'] = array();
		$messenger_links = $this->model_setting_setting->getSetting('theme_prostoremeslinks');
		$data['messenger_links'] = $messenger_links['theme_prostoremeslinks_array'];
		$data['messenger_navs'] = $this->config->get('theme_prostore_messenger_nav');		
		$data['messenger_status'] = $this->config->get('theme_prostore_messenger_status');
		$data['messenger_pos'] = $this->config->get('theme_prostore_messenger_pos');

		$data['payment_icons'] = array();
		if($this->config->get('theme_prostore_vidg_payicons')){
			$payment_icons = $this->config->get('theme_prostore_vidg_payicons');
			foreach ($payment_icons as $key => $payment_icon) {
				if (!is_file(DIR_IMAGE . $payment_icon['image'])) {
					$payment_icon['image'] = 'no_image.png';
				}
				if ($this->request->server['HTTPS']) {
					$payment_icons[$key]['thumb'] = $this->config->get('config_ssl') . 'image/' . $payment_icon['image'];
				} else {
					$payment_icons[$key]['thumb'] = $this->config->get('config_url') . 'image/' . $payment_icon['image'];
				}
			}
			$paysort  = array_column($payment_icons, 'sort');
			array_multisort($paysort, SORT_ASC, $payment_icons);
			$data['payment_icons'] = $payment_icons;
		}

		$data['text_newsletter'] = $this->language->get('text_newsletter');
		$data['text_footer_subscribe_email'] = $this->language->get('text_footer_subscribe_email');
		$data['text_header_callback'] = $this->language->get('text_header_callback');
		$data['text_social_navs'] = $this->language->get('text_social_navs');
		$data['callback_status'] = $this->config->get('theme_prostore_callback_status');
		$data['soc_stat'] = $this->config->get('theme_prostore_footer_soc_stat');
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

		$data['theme_color'] = $this->config->get('theme_prostore_color');
		$data['theme_fc_color'] = $this->config->get('theme_prostore_color_2');
		$data['fontawesome'] = $this->config->get('theme_prostore_fontawesome');
		$data['bootstrap_ver'] = $this->config->get('theme_prostore_bootstrap_ver');
		$data['theme_color_1'] = $this->config->get('theme_prostore_custom_color_1');
		$data['theme_color_2'] = $this->config->get('theme_prostore_custom_color_2');
		$data['theme_fc_color_1'] = $this->config->get('theme_prostore_custom_fc_color_1');
		$data['theme_fc_color_2'] = $this->config->get('theme_prostore_custom_fc_color_2');
		$data['footer_type'] = $this->config->get('theme_prostore_footer_type');
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'checkout/checkout') {
			$data['footer_type'] = 'checkout';
		}
		if ($this->config->get('config_maintenance')) {
			$this->user = new Cart\User($this->registry);
			if (!$this->user->isLogged()) {
				$data['footer_type'] = 'maintenance';
			}
		}
		$data['subscribe_status'] = $this->config->get('theme_prostore_subscribe_status');
		$data['subscribe_title'] = $this->config->get('theme_prostore_subscribe_title' . $this->config->get('config_language_id'));
		$data['subscribe_subtitle'] = $this->config->get('theme_prostore_subscribe_subtitle' . $this->config->get('config_language_id'));
		$data['js_footorhead'] = $this->config->get('theme_prostore_js_footorhead');
		$data['footer_copyright'] = html_entity_decode($this->config->get('theme_prostore_footer_copyright' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');
		$data['footer_copyright'] = str_replace('{{year}}',date('Y'),$data['footer_copyright']);
		$data['footer_text'] = html_entity_decode($this->config->get('theme_prostore_footer_text' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');
		$data['footer_t_logo'] = $this->config->get('theme_prostore_footer_t_logo');
		$data['text_logo'] = html_entity_decode($this->config->get('theme_prostore_footer_text_logo'), ENT_QUOTES, 'UTF-8');
		$data['shop_email'] = $this->config->get('config_email');
		$data['config_address'] = $this->config->get('config_address');

		$data['footer_categories'] = $this->config->get('theme_prostore_footer_categories');
		$data['text_prostore_subscribe_btn'] = $this->language->get('text_prostore_subscribe_btn');
		$data['text_footer_questions'] = $this->language->get('text_footer_questions');
		$data['text_footer_link'] = $this->language->get('text_footer_link');
		$data['text_footer_pay'] = $this->language->get('text_footer_pay');
		$data['text_prostore_maintenance_phone'] = $this->language->get('text_prostore_maintenance_phone');
		$data['text_prostore_maintenance_email'] = $this->language->get('text_prostore_maintenance_email');
		$data['text_prostore_con_soc2'] = $this->language->get('text_prostore_con_soc2');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['config_open'] = html_entity_decode($this->config->get('config_open'), ENT_QUOTES, 'UTF-8');

		$data['geocode'] = $this->config->get('config_geocode');		
		$data['schema'] = $this->config->get('theme_prostore_schema');
		$data['address_schema'] = str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($data['config_address'], ENT_QUOTES, 'UTF-8')));
		$data['meta_description'] = $this->config->get('config_meta_description');
		$data['telephone'] = $this->config->get('config_telephone');
		if ($this->config->get('config_country_id')) {
			$this->load->model('localisation/country');
			$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
			$data['country_name'] = $country['name'];
		}
		if ($this->config->get('config_zone_id')) {
			$this->load->model('localisation/zone');
			$zone = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));
			$data['zone_name'] = $zone['name'];
		}
		if ($data['geocode']) {
			$tempcode = explode(',', $data['geocode']);
			if (isset($tempcode[1])) {
				$data['latitude'] = $tempcode[0]; 
				$data['longitude'] = $tempcode[1]; 
			}
		}
		$data['currency'] = $this->config->get('config_currency');

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
		if ($this->config->get('theme_prostore_buy_click_pdata')) {
			$click_pdata = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_buy_click_pdata'));
			if ($click_pdata) {
				$data['text_click_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('button_prostore_sendorder'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_buy_click_pdata'), true), $click_pdata['title'], $click_pdata['title']);
			} else {
				$data['text_click_pdata'] = '';
			}
		} else {
			$data['text_click_pdata'] = '';
		}

		$checkout_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
		if ($checkout_info) {
			$data['checkout_info_title'] = $checkout_info['title'];
			$data['checkout_info_link'] = $this->url->link('information/information', 'information_id=' . $this->config->get('config_checkout_id'), true);
		} else {
			$data['checkout_info_title'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_footer_logo'))) {
			$data['footer_logo'] = $server . 'image/' . $this->config->get('theme_prostore_footer_logo');
		} else {
			$data['footer_logo'] = '';
		}

		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_subscribe_email_logo'))) {
			$data['subscribe_email_logo'] = $server . 'image/' . $this->config->get('theme_prostore_subscribe_email_logo');
		} else {
			$data['subscribe_email_logo'] = '';
		}

		$data['custom_css'] = $this->config->get('theme_prostore_css');
		$data['custom_js'] = html_entity_decode($this->config->get('theme_prostore_js'), ENT_QUOTES, 'UTF-8');
		$host = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;

		if ($this->request->server['REQUEST_URI'] == '/') {
		  $data['og_url'] = $this->url->link('common/home');
		} else {
		  $data['og_url'] = $host . substr($this->request->server['REQUEST_URI'], 1, (strlen($this->request->server['REQUEST_URI'])-1));
		}

		$data['name'] = $this->config->get('config_name');
		$data['callback'] = $this->load->controller('extension/module/callback');
		$data['islogged'] = $this->customer->isLogged();
		$data['cookieagry'] = 0;

		if (isset($_COOKIE["cookieagry"])){
			$data['cookieagry'] = 1;
		}
		
		if ($this->config->get('theme_prostore_cookies_pdata')) {
			$information_cookies = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_cookies_pdata'));
			if ($information_cookies) {
				$data['text_prostore_cookieagry'] = sprintf($this->language->get('text_prostore_cookie_required'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_cookies_pdata'), true), $information_cookies['title'], $information_cookies['title'], ENT_QUOTES, 'UTF-8');
			} else {
				$data['text_prostore_cookieagry'] = '';
			}
		} else {
			$data['text_prostore_cookieagry'] = '';
		}
		$data['home'] = $this->url->link('common/home');
		// prostore end
		
		$data['styles'] = $this->document->getStyles('footer');
		
		return $this->load->view('common/footer', $data);
	}
}
