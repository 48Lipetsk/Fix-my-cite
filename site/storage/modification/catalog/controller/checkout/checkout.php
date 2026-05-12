<?php
class ControllerCheckoutCheckout extends Controller {
	public function index() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirements.

// prostore start
		if ($this->load->controller('checkout/cart/getTotals') < (int)$this->config->get('theme_prostore_checkout_min_order')) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
// prostore stop
			
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		
			// prostore
			if ($this->config->get('theme_prostore_bootstrap_ver')) { // bootstrap5
				$this->document->addScript('catalog/view/javascript/prostore/datetimepicker/moment.min.js');
				$this->document->addScript('catalog/view/javascript/prostore/datetimepicker/moment-with-locales.min.js');
				$this->document->addScript('catalog/view/javascript/prostore/datetimepicker/daterangepicker.js');
				$this->document->addStyle('catalog/view/javascript/prostore/datetimepicker/daterangepicker.css');
			} else { //bootstrap3
				$this->document->addScript("catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js");
				$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
				$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
				$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
			}
			$data['datepicker'] = $this->language->get('code');
			$this->load->language('extension/theme/prostore');
			$data['date_format'] = $this->language->get('text_prostore_date_format');
			$data['datetime_format'] = $this->language->get('text_prostore_datetime_format');
			$data['time_format'] = $this->language->get('text_prostore_time_format');
			// prostore
			

		// Required by klarna
		if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
			$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
		

		// prostore
		$this->load->language('extension/theme/prostore');
		$data['text_prostore_checkout_s1'] = $this->language->get('text_prostore_checkout_s1');
		$data['text_prostore_checkout_s2'] = $this->language->get('text_prostore_checkout_s2');
		$data['text_prostore_checkout_s3'] = $this->language->get('text_prostore_checkout_s3');
		$data['text_prostore_checkout_s4'] = $this->language->get('text_prostore_checkout_s4');

		$data['checkout_st3_sa'] = $this->config->get('theme_prostore_checkout_st3_sa');

		$this->load->model('catalog/information');
		$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
		if ($information_info) {
			$data['error_agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
		} else {
			$data['error_agree'] = '';
		}
		
		$data['show_checkout_shipping'] = $this->config->get('theme_prostore_checkout_shipping');
		$data['fixed_cart'] = $this->config->get('theme_prostore_checkout_fixed_cart');
		$data['fixed_header'] = $this->config->get('theme_prostore_fixed_header');
		// prostore end
			
		if ($this->cart->hasShipping()) {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
		} else {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
		}

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$data['logged'] = $this->customer->isLogged();

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = '';
		}

		$data['shipping_required'] = $this->cart->hasShipping();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}


		//prostore start
		$this->load->model('extension/module/prostore');
		$activeFields = $this->model_extension_module_prostore->getFields($customer_group_id);
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
			// В регистрации всегда требовать email
			if($field['name'] == 'email'){
				$field['required'] = 1;
				$field['is_show'] = 1;
			}

			if($field['is_show']){
				$json[] = array(
					'custom_field_id' => 'ls-'.$field['custom_field_id'],
					'required'        => (int)$field['required']
				);				
			}			
		}
		//prostore end	
			
		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}