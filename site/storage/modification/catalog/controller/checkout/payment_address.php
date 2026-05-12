<?php
class ControllerCheckoutPaymentAddress extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');

		if (isset($this->session->data['payment_address']['address_id'])) {
			$data['address_id'] = $this->session->data['payment_address']['address_id'];
		} else {
			$data['address_id'] = $this->customer->getAddressId();
		}

		$this->load->model('account/address');

//prostore start
		$data['datepicker'] = $this->language->get('code');// prostore
		$this->load->language('extension/theme/prostore');
		$data['date_format'] = $this->language->get('text_prostore_date_format');// prostore
		$data['datetime_format'] = $this->language->get('text_prostore_datetime_format');// prostore
		$data['time_format'] = $this->language->get('text_prostore_time_format');// prostore
		$data['entry_telephone'] = $this->language->get('entry_telephone');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_fax'] = $this->language->get('entry_fax');

		if (isset($this->request->post['customer_group_id'])) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} elseif(isset($this->session->data['guest']['customer_group_id'])) {
			$customer_group_id  = $this->session->data['guest']['customer_group_id'];
		} else {
			$customer_group_id  = $this->config->get('config_customer_group_id');
		}
		
		$this->load->model('extension/module/prostore');
		$activeFields = $this->model_extension_module_prostore->getFields($customer_group_id,0);
		$allFields = $this->model_extension_module_prostore->getAllFields();

		$data['allCustomFields'] = $allFields;
		foreach($allFields as $field){
			$data['entry_'.$field.'_required'] = 0;
			$data['entry_'.$field.'_show'] = 0;

			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} else {
				$data[$field] = '';
			}

			if (isset($this->error[$field])) {
				$data['error_'.$field] = $this->error[$field];
			} else {
				$data['error_'.$field] = '';
			}
			
		}

		foreach($activeFields as $field){
			if($field['description']){
				$data['entry_'.$field['name']] = $field['description'];
			}
			$data['entry_'.$field['name'].'_id'] = 'ls-'.$field['custom_field_id'];
			$data['entry_'.$field['name'].'_required'] = $field['required'];
			$data['entry_'.$field['name'].'_show'] = $field['is_show'];
		}
//prostore end
			

		$data['addresses'] = $this->model_account_address->getAddresses();

		if (isset($this->session->data['payment_address']['country_id'])) {
			$data['country_id'] = $this->session->data['payment_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['payment_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['payment_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		// Custom Fields
		$data['custom_fields'] = array();
		
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'address') {
				$data['custom_fields'][] = $custom_field;
			}
		}

		if (isset($this->session->data['payment_address']['custom_field'])) {
			$data['payment_address_custom_field'] = $this->session->data['payment_address']['custom_field'];
		} else {
			$data['payment_address_custom_field'] = array();
		}

		$this->response->setOutput($this->load->view('checkout/payment_address', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!$json) {
			$this->load->model('account/address');

//prostore start
		$data['datepicker'] = $this->language->get('code');// prostore
		$this->load->language('extension/theme/prostore');
		$data['date_format'] = $this->language->get('text_prostore_date_format');// prostore
		$data['datetime_format'] = $this->language->get('text_prostore_datetime_format');// prostore
		$data['time_format'] = $this->language->get('text_prostore_time_format');// prostore
		$data['entry_telephone'] = $this->language->get('entry_telephone');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_fax'] = $this->language->get('entry_fax');

		if (isset($this->request->post['customer_group_id'])) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} elseif(isset($this->session->data['guest']['customer_group_id'])) {
			$customer_group_id  = $this->session->data['guest']['customer_group_id'];
		} else {
			$customer_group_id  = $this->config->get('config_customer_group_id');
		}
		
		$this->load->model('extension/module/prostore');
		$activeFields = $this->model_extension_module_prostore->getFields($customer_group_id,0);
		$allFields = $this->model_extension_module_prostore->getAllFields();

		$data['allCustomFields'] = $allFields;
		foreach($allFields as $field){
			$data['entry_'.$field.'_required'] = 0;
			$data['entry_'.$field.'_show'] = 0;

			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} else {
				$data[$field] = '';
			}

			if (isset($this->error[$field])) {
				$data['error_'.$field] = $this->error[$field];
			} else {
				$data['error_'.$field] = '';
			}
			
		}

		foreach($activeFields as $field){
			if($field['description']){
				$data['entry_'.$field['name']] = $field['description'];
			}
			$data['entry_'.$field['name'].'_id'] = 'ls-'.$field['custom_field_id'];
			$data['entry_'.$field['name'].'_required'] = $field['required'];
			$data['entry_'.$field['name'].'_show'] = $field['is_show'];
		}
//prostore end
			
							
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'existing') {
				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->language->get('error_address');
				}

				if (!$json) {
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->post['address_id']);

                    // Если включена настройка шаблона что адрес оплаты и доставки одинаковый
                    if($this->config->get('theme_prostore_checkout_st3_sa')){
                        $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->request->post['address_id']);
                    }         
            

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			} else {
				if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
					$json['error']['firstname'] = $this->language->get('error_firstname');
				}

				if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
					$json['error']['lastname'] = $this->language->get('error_lastname');
				}

				if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
					$json['error']['address_1'] = $this->language->get('error_address_1');
				}


//prostore start
				if ((utf8_strlen(trim($this->request->post['telephone'])) < 2) || (utf8_strlen(trim($this->request->post['telephone'])) > 128)) {
					$json['error']['telephone'] = $this->language->get('error_telephone');
				}
//prostore end
			
				if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
					$json['error']['city'] = $this->language->get('error_city');
				}

				$this->load->model('localisation/country');

				$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

				if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
					$json['error']['postcode'] = $this->language->get('error_postcode');
				}

				if ($this->request->post['country_id'] == '') {
					$json['error']['country'] = $this->language->get('error_country');
				}

				if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
					$json['error']['zone'] = $this->language->get('error_zone');
				}

				// Custom field validation
				$this->load->model('account/custom_field');

				$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

				foreach ($custom_fields as $custom_field) {
					if ($custom_field['location'] == 'address') {
						if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
							$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
						} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
							$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
						}
					}
				}


//prostore start	
				if (isset($json['error'])) {
					$this->load->model('extension/module/prostore');
					$json['error'] = $this->model_extension_module_prostore->checkLCustomFields($json['error']);
					$ignoredFields = $this->model_extension_module_prostore->getIgnoredFields($this->config->get('config_customer_group_id'),0);
				
					if(isset($json['error'])){
						foreach($json['error'] as $key => $fieldsdata){
							if(in_array($key,$ignoredFields)){
								unset($json['error'][$key]);
							}
						}
						if(!count($json['error'])){
							unset($json['error']);
						}
					}
				}			
//prostore end	
			
				if (!$json) {
					$address_id = $this->model_account_address->addAddress($this->customer->getId(), $this->request->post);

					$this->session->data['payment_address'] = $this->model_account_address->getAddress($address_id);

                    // Если включена настройка шаблона что адрес оплаты и доставки одинаковый
                    if($this->config->get('theme_prostore_checkout_st3_sa')){
                        $this->session->data['shipping_address'] = $this->model_account_address->getAddress($address_id);
                    }        
            

					// If no default address ID set we use the last address
					if (!$this->customer->getAddressId()) {
						$this->load->model('account/customer');
						
						$this->model_account_customer->editAddressId($this->customer->getId(), $address_id);
					}

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}