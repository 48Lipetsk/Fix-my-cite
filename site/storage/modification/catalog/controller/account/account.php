<?php
class ControllerAccountAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/account');

// prostore	
		$this->load->language('extension/theme/prostore');
		$data['compare'] = $this->url->link('product/compare');
		$data['cart'] = $this->url->link('checkout/cart');

		$this->load->model('account/order');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$orders_history = $this->model_account_order->getOrders(0,1);

		$data['last_orders_info'] = array();
		$data['cart_info'] = array();

		if ($orders_history) {
			$products_data = array();
			$order_id = $orders_history[0]['order_id'];
			$last_orders_info = $this->model_account_order->getOrder($order_id);
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($order_id);
			$products = $this->model_account_order->getOrderProducts($order_id);
			foreach ($products as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				$wishCompareData = $this->prostore->helper->getDataWishCompare($product['product_id']);
				if (!empty($product_info) && $product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'],  $this->config->get('theme_prostore_image_cart_width'), $this->config->get('theme_prostore_image_cart_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png',  $this->config->get('theme_prostore_image_cart_width'), $this->config->get('theme_prostore_image_cart_height'));
				}				

				$products_data[] = array(
					'product_id' => $product['product_id'],
					'name'     => $product['name'],
					'model'    => $product['model'],
					'thumb'     => $image,
					'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'wish_compare_data' => $wishCompareData,
				);
			}
			$total_correct =  $this->prostore->helper->correctForm(array($product_total,'product'));
			$data['last_orders_info'] = array(
				'order_id'     => $order_id,
				'text_total'     => sprintf($this->language->get('text_total'), $product_total, $total_correct , $this->currency->format($last_orders_info['total'], $this->session->data['currency'])),
				'products_data'     => $products_data,
				'href'     => $this->url->link('account/order/info', 'order_id=' . $order_id, true)
			);

		}

		$cart_total_product = $this->cart->countProducts();

		if ($cart_total_product) {
			$cart_products = array();
			$cart_total = $this->prostore->helper->get_cart_total();

			foreach ($this->cart->getProducts() as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				$wishCompareData = $this->prostore->helper->getDataWishCompare($product['product_id']);
				if (!empty($product_info) && $product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'],  $this->config->get('theme_prostore_image_cart_width'), $this->config->get('theme_prostore_image_cart_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png',  $this->config->get('theme_prostore_image_cart_width'), $this->config->get('theme_prostore_image_cart_height'));
				}			
	
				$cart_products[] = array(
					'product_id' => $product['product_id'],
					'name'     => $product['name'],
					'model'    => $product['model'],
					'thumb'     => $image,
					'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'wish_compare_data' => $wishCompareData,
				);			
			}
			$total_correct =  $this->prostore->helper->correctForm(array($cart_total_product,'product'));
			$data['cart_info'] = array(
				'text_total'     => sprintf($this->language->get('text_total'), $cart_total_product, $total_correct ,$this->currency->format($cart_total, $this->session->data['currency'])),
				'cart_products'     => $cart_products
			);
		}

//		var_dump($data['cart_info'],$data['last_orders_info']);
		
// prostore end
			

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 
		
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		
		$data['credit_cards'] = array();
		
		$files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');
		
		foreach ($files as $file) {
			$code = basename($file, '.php');
			
			if ($this->config->get('payment_' . $code . '_status') && $this->config->get('payment_' . $code . '_card')) {
				$this->load->language('extension/credit_card/' . $code, 'extension');

				$data['credit_cards'][] = array(
					'name' => $this->language->get('extension')->get('heading_title'),
					'href' => $this->url->link('extension/credit_card/' . $code, '', true)
				);
			}
		}
		
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		
		if ($this->config->get('total_reward_status')) {
			$data['reward'] = $this->url->link('account/reward', '', true);
		} else {
			$data['reward'] = '';
		}		
		
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		
		$this->load->model('account/customer');
		
		$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
		
		if (!$affiliate_info) {	
			$data['affiliate'] = $this->url->link('account/affiliate/add', '', true);
		} else {
			$data['affiliate'] = $this->url->link('account/affiliate/edit', '', true);
		}
		
		if ($affiliate_info) {		
			$data['tracking'] = $this->url->link('account/tracking', '', true);
		} else {
			$data['tracking'] = '';
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('account/account', $data));
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
}
