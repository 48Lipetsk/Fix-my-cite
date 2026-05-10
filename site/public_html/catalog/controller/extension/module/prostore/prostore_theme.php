<?php

class ControllerExtensionModuleProstoreProstoreTheme extends Controller {

	public function set_address(){
		$this->load->language('extension/total/shipping');
		$json = array();

		if ($this->request->post['country_id'] == '') {
			$json['error']['country'] = $this->language->get('error_country');
		}
		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$json['error']['zone'] = $this->language->get('error_zone');
		}
		$product_id = 0;
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		}		

		if (!$json) {
			$this->tax->setShippingAddress($this->request->post['country_id'], $this->request->post['zone_id']);
			
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);
	
			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}	

			$this->session->data['shipping_address'] = array(
				'firstname'      => '',
				'lastname'       => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city'           => $this->request->post['city'],
				'zone_id'        => $this->request->post['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);			
			$json['html'] = $this->shipping_info($product_id);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	public function shipping_form($product_id=0){
/**
 * Форма для ввода адреса доставки в карточке товара.
 * 
 */		
		$outData = '';

		$this->load->language('extension/total/shipping');
		$this->load->language('extension/theme/prostore');

		if (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['shipping_address']['city'])) {
			$data['city'] = $this->session->data['shipping_address']['city'];
		} else {
			$data['city'] = '';
		}		

		if (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}		

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$data['product_id'] = $product_id;

		if (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->session->data['shipping_method'])) {
			$data['shipping_method'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_method'] = '';
		}

		$data['zone_info'] = $this->getZoneInfo($data);

		$outData = $this->load->view('common/prostore_shipping_address', $data);


		return $outData;
	}

	public function shipping_info($product_id=0){
/**
 * Вывод стоимости доставки в карточке товара.
 * 
 */
		$outData = '';

		$this->load->language('extension/total/shipping');
		$this->load->language('extension/theme/prostore');

		if (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$data['quote_data'] = array();

		if ($product_id) {
			$data['product_id'] = $product_id;
				
			if (isset($this->session->data['shipping_address']['zone_id'])) {
/**
 * Создаем временную сессию с пустой корзиной для получения стоимостей доставки. Start
 */
				$sessionId = $this->session->getId();
				$currency = $this->session->data['currency'];
				$shipping_address = $this->session->data['shipping_address'];

				$customerEmail = '';
				if ($this->customer->isLogged()) {
					$customerEmail = $this->customer->getEmail();
					$this->customer->logout();
				}

				$this->session->close();
				$tempSessionId = $this->session->start(); 
				$this->session->data['currency'] = $currency;
				$this->session->data['shipping_address'] = $shipping_address;
				$this->session->data['payment_address'] = $shipping_address;
				$this->cart->add($product_id); 
/**
 * Создаем временную сессию с пустой корзиной для получения стоимостей доставки . End
 */
				$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];

				$quote_data = array();
				$this->load->model('setting/extension');
				$results = $this->model_setting_extension->getExtensions('shipping');
		
				foreach ($results as $result) {
					if ($this->config->get('shipping_' . $result['code'] . '_status')) {
						$this->load->model('extension/shipping/' . $result['code']);
		
						$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
		
						if ($quote) {
							$quote_data[$result['code']] = array(
								'title'      => $quote['title'],
								'quote'      => $quote['quote'],
								'sort_order' => $quote['sort_order'],
								'error'      => $quote['error']
							);
						}
					}
				}
				$sort_order = array();
				foreach ($quote_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
				array_multisort($sort_order, SORT_ASC, $quote_data);
				$data['quote_data'] = $quote_data;

				$this->cart->clear();
				
/**
 * Удаляем временную сессию . Start
 */
				if (method_exists($this->session,'__destroy')) {
					// For OpenCart 3.0.2
					$this->session->__destroy($tempSessionId);
				}else{
					$this->session->destroy($tempSessionId);
				}
				$this->session->start($sessionId);

				if ($customerEmail) {
					$this->customer->login($customerEmail, '', true);
				}

/**
 * Удаляем временную сессию . Start
 */				
			} else {
				$data['zone_id'] = '';
			}
		}

		if (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->session->data['shipping_method'])) {
			$data['shipping_method'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_method'] = '';
		}

		$data['zone_info'] = $this->getZoneInfo($data);

		$outData = $this->load->view('common/prostore_shipping_info', $data);

		return $outData;
	}

	public function get_shipping_info(){
		if (isset($this->request->get['item_id'])) {
			$product_id = $this->request->get['item_id'];
		}
		$this->response->setOutput($this->shipping_info($product_id));
	}

	protected function getZoneInfo($data) {
		$this->load->model('localisation/zone');
		$outData = $this->language->get('text_prostore_enter_address');
		if ($data['zone_id']) {
			$zoneInfo = $this->model_localisation_zone->getZone($data['zone_id']);
			$countryInfo = $this->model_localisation_country->getCountry($zoneInfo['country_id']);
			$outData = $countryInfo['name'] . ', ' . $zoneInfo['name'];
			if (isset($this->session->data['shipping_address']['city']) && $this->session->data['shipping_address']['city']) {
				$outData .= ', ' . $this->session->data['shipping_address']['city'];
			}
		}
		return $outData;
	}
/**
 *  Секция по работе с функционалом Вопрос-Ответ в карточке товара. Начало
 */
	public function get_faq() {
            
		$this->load->language('product/product');

		$this->load->model('extension/module/prostore_faq');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$paginationRequest = false;

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		}elseif(isset($this->request->get['prod_id'])){
			$product_id = (int)$this->request->get['prod_id'];
			$paginationRequest = true;
		} else {
			$product_id = 0;
		}			

		if ($this->customer->isLogged()) {
			$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			$data['customer_email'] = $this->customer->getEmail();
		} else {
			$data['customer_name'] = '';
			$data['customer_email'] = '';
		}

		$data['schema'] = $this->config->get('theme_prostore_schema');
		$pagination_limit = $this->config->get('theme_prostore_product_review') ? 10 : 5;
		$data['faqs'] = array();

		// Captcha
		if ($this->config->get('theme_prostore_product_faq')['captcha'])  {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_product_faq')['captcha']);
		} else {
			$data['captcha'] = '';
		}
		
		$this->load->language('extension/theme/prostore');
		$this->load->model('catalog/information');
		if ($this->config->get('theme_prostore_faq_pdata')) {	
			$review_pdata = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_faq_pdata'));
			if ($review_pdata) {
				$data['text_review_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('text_prostore_faq_send'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_faq_pdata'), true), $review_pdata['title'], $review_pdata['title']);
			} else {
				$data['text_review_pdata'] = '';
			}
		} else {
			$data['text_review_pdata'] = '';
		}
			
		$results = $this->model_extension_module_prostore_faq->getFaqsByProductId($product_id, ($page - 1) * $pagination_limit, $pagination_limit); //prostore add this
		$faq_total = $this->model_extension_module_prostore_faq->getTotalFaqsByProductId($product_id);
   
		foreach ($results as $result) {
			$data['faqs'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'faq_id'     => (int)$result['faq_id'], 
				'text_admin_answer'     => nl2br($result['text_admin_answer']),
				'answer_date_added' => $this->prostore->helper->rus_date(strtotime($result['answer_date_added'])),
				'date_added_schema' => date('Y-m-d', strtotime($result['date_added'])),
				'date_added' =>  $this->prostore->helper->rus_date(strtotime($result['date_added']))
			);
		}

		$data['captcha'] = '';
		if ($this->config->get('theme_prostore_product_faq')['status']) {
			// Captcha FAQ
			if ($this->config->get('theme_prostore_product_faq')['captcha'])  {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_product_faq')['captcha']);

			}
		}		

		$data['faq_total'] = $faq_total;

		$pagination = new Pagination();
		$pagination->total = $faq_total;
		$pagination->page = $page;
		
		$pagination->limit = $pagination_limit;
            
		$pagination->url = $this->url->link('extension/module/prostore/prostore_theme/get_faq', 'prod_id=' . $product_id . '&page={page}');

		$data['pagination'] = $pagination->render();
		if ($paginationRequest) {
			$this->load->language('extension/theme/prostore');
			$this->response->setOutput($this->load->view('product/prostore_faq', $data));
		}else {
			return $this->load->view('product/prostore_faq', $data);		
		}
            
	}
	
	public function write_faq() {
		if ($this->config->get('theme_prostore_product_faq')['status']) {
			$this->load->language('product/product');
			$this->load->language('extension/theme/prostore');

			$json = array();

			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ((utf8_strlen($this->request->post['faq_name']) < 3) || (utf8_strlen($this->request->post['faq_name']) > 25)) {
					$json['error'] = $this->language->get('error_name');
				}

				if ((utf8_strlen($this->request->post['faq_text']) < 25) || (utf8_strlen($this->request->post['faq_text']) > 1000)) {
					$json['error'] = $this->language->get('error_text_faq');
				}

				// Captcha
				if ($this->config->get('captcha_' . $this->config->get('theme_prostore_product_faq')['captcha'] . '_status')) {
					$captcha = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_product_faq')['captcha'] . '/validate');

					if ($captcha) {
						$json['error'] = $captcha;
					}
				}
			
				if (!isset($json['error'])) {
					$this->load->model('extension/module/prostore_faq');

					$this->model_extension_module_prostore_faq->addFaq($this->request->get['product_id'], $this->request->post);

					$json['success'] = $this->language->get('text_success_faq');
				}
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
/**
 *  Секция по работе с функционалом Вопрос-Ответ в карточке товара. Завершение.
 */

}