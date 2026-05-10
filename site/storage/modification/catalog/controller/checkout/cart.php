<?php
class ControllerCheckoutCart extends Controller {
	public function index() {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('checkout/cart'),
			'text' => $this->language->get('heading_title')
		);


		// prostore
			if ($this->config->get($this->config->get('theme_prostore_config_captcha_fo') . '_status')) {
				$data['captcha_fo'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_config_captcha_fo'));
			} else {
			$data['captcha_fo'] = '';
			}
		$this->load->language('extension/theme/prostore'); 
		$data['schema'] = $this->config->get('theme_prostore_schema');
			$data['button_fastorder_sendorder'] = $this->language->get('button_prostore_sendorder');
			$data['text_prostore_fast_order'] = $this->language->get('text_prostore_fast_order');
			$data['text_fastorder_name'] = $this->language->get('text_prostore_name');
			$data['text_fastorder_phone'] = $this->language->get('text_prostore_phone');
			$data['text_fastorder_comment'] = $this->language->get('text_prostore_comment');
			if ($this->config->get('theme_prostore_buy_click_pdata')) {
				$this->load->language('extension/theme/prostore');
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_buy_click_pdata'));

				if ($information_info) {
					$data['text_prostore_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('button_prostore_sendorder'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_buy_click_pdata'), true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_prostore_pdata'] = '';
				}
			} else {
				$data['text_prostore_pdata'] = '';
			}
			
			$data['buy_click'] = array();
			if($this->config->get('theme_prostore_buy_click')){
				$data['buy_click'] = $this->config->get('theme_prostore_buy_click');
				if ($this->customer->isLogged()) {
					$this->load->model('account/customer');
					$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
				}
			}
			$data['language_id'] = $this->config->get('config_language_id');

			$this->load->language('checkout/checkout');
			$data['entry_firstname'] = $this->language->get('entry_firstname');
			$data['entry_lastname'] = $this->language->get('entry_lastname');
			$data['entry_email'] = $this->language->get('entry_email');
			$data['entry_telephone'] = $this->language->get('entry_telephone');
			$data['entry_comment'] = $this->language->get('entry_comment');
			$data['text_prostore_cart_price'] = $this->language->get('text_prostore_cart_price');
			$data['text_prostore_reward'] = $this->language->get('text_prostore_reward');
			$data['prostore_cart_total'] = $this->currency->format($this->cart->getTotal(), $this->session->data['currency']);
			$data['prostore_cart_count'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
			
		// prostore end
			
		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}


// prostore start
			if ($this->getTotals() < (int)$this->config->get('theme_prostore_checkout_min_order')) {
				$data['error_warning'] = sprintf($this->language->get('error_min_order'),$this->currency->format($this->config->get('theme_prostore_checkout_min_order'), $this->session->data['currency']));
			}
// prostore end
			
			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year')
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],

					'minimum'   => $product['minimum'],// prostore add
					'reward_num'=> $product['reward'],// prostore add
					'product_id'=> $product['product_id'],// prostore add
			
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)
					);
				}
			}

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(

'value' => $total['value'],//Prostore added this
			
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}

			$data['continue'] = $this->url->link('common/home');

			$data['checkout'] = $this->url->link('checkout/checkout', '', true);

			$this->load->model('setting/extension');

			$data['modules'] = array();
			
			$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

			if ($files) {
				foreach ($files as $file) {
					$result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
					if ($result) {
						$data['modules'][] = $result;
					}
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');


			$data['buyclick_form'] = $this->load->view('product/buyclick_form', $data);//Prostore added this
			
			$this->response->setOutput($this->load->view('checkout/cart', $data));
		} else {
			$data['text_error'] = $this->language->get('text_empty');
			
			$data['continue'] = $this->url->link('common/home');

			unset($this->session->data['success']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			
// prostore
		$this->load->language('extension/theme/prostore');
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_logo_404'))) {
			$data['logo_404'] = (isset($this->request->server['HTTPS']) ? HTTPS_SERVER : HTTP_SERVER) . 'image/' . $this->config->get('theme_prostore_logo_404');
		} else {
			$data['logo_404'] = '';
		}
		$data['text_404'] = sprintf($this->language->get('text_404'), $this->url->link('information/contact', '', true), $this->url->link('product/search', '', true), $this->url->link('common/home', '', true));
		$this->response->setOutput($this->load->view('error/404', $data));
// prostore end																										
			
		}
	}

	public function add() {
		$this->load->language('checkout/cart');

		$json = array();


		// prostore
		$this->load->language('extension/theme/prostore'); 
		if (isset($this->request->post['prod_id_quantity']) && !isset($this->request->post['quantity'])) {
			$this->request->post['quantity'] = current($this->request->post['prod_id_quantity']);
		}
		// prostore	
			
		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);



            $default_options = [];
            
			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					
                   

                    foreach($product_option['product_option_value'] as $row => $val)
                    {
                        if(1 == $val['is_default'])
                        {
                            if('checkbox' == $product_option['type'])
                            {
                                $_default_val = [$val['product_option_value_id']];
                            }
                            else
                            {
                                $_default_val = (int)$val['product_option_value_id'];
                            }

                            $default_options[$product_option['product_option_id']] = $_default_val;
                        }
                    }

                    if(0 === count($default_options))
                    {
                        $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                    }

            

				}
			}





            if(count($default_options) > 0)
            {
                $filtered_default_options   = array_filter($default_options);
                $new_option                 = array_merge($option, $filtered_default_options);
                //$option                   = array_filter($new_option);
                $option                     = $default_options;
            }

            
			if (isset($this->request->post['recurring_id'])) {
				$recurring_id = $this->request->post['recurring_id'];
			} else {
				$recurring_id = 0;
			}

			$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

			if ($recurrings) {
				$recurring_ids = array();

				foreach ($recurrings as $recurring) {
					$recurring_ids[] = $recurring['recurring_id'];
				}

				if (!in_array($recurring_id, $recurring_ids)) {
					$json['error']['recurring'] = $this->language->get('error_recurring_required');
				}
			}

			if (!$json) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

				// Unset all shipping and payment methods
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;
		
				// Because __call can not keep var references so we put them into an array. 			
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				}

				$json['total'] = sprintf($this->language->get('text_prostore_shopping_cart'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('checkout/cart');

		$json = array();

		// Update

// prostore
		$this->load->language('extension/theme/prostore'); 
		if (isset($this->request->post['prod_id_quantity'])) {
			$json['deleted_items'] = $this->convert2cartId();
		}
		$cartProducts = $this->prostore->helper->getCartIdsAsKey();
// prostore
			
		if (!empty($this->request->post['quantity'])) {

			$deletedItems = [];// prostore
			
			foreach ($this->request->post['quantity'] as $key => $value) {

// prostore
				if ($value < $cartProducts[$key]['minimum']) {
					$value = 0;
					$deletedItems[] = $cartProducts[$key]['product_id'];
				}
// prostore
			
				$this->cart->update($key, $value);
			}


// prostore
			if ($deletedItems) {
				$json['deleted_items'] = $deletedItems;
			}
// prostore
			
			$this->session->data['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			
		}

// prostore
$total = $this->getTotals();
$json['total'] = sprintf($this->language->get('text_prostore_shopping_cart'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
// prostore end
			

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


// prostore
	public function getTotals(){
/**
 * Get cart totals include vouchers
 */
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array. 			
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		return $total;
	}


	public function getProductsIdAsKey(){
		$cartProducts = array();
		foreach ($this->cart->getProducts() as $product) {
			$cartProducts[$product['product_id']] = $product;
		}
		return $cartProducts;
	}
/**
 * Преобразование product_id в cart_id если такой продукт есть в корзине.
 * Используется для апдейта количества товаров на странице категорий.
 */
	public function convert2cartId() {
		$deletedItems = array();
		if (empty($this->request->post['prod_id_quantity'])) {
			return $deletedItems;
		}
		$cartProducts = $this->getProductsIdAsKey();
		foreach ($this->request->post['prod_id_quantity'] as $product_id => $quantity) {
			if (array_key_exists($product_id,$cartProducts)) {
				if ($quantity < $cartProducts[$product_id]['minimum']) {
					$quantity = 0;
					$deletedItems[] = $product_id;
				}
				$cart_id = $cartProducts[$product_id]['cart_id'];
				$this->request->post['quantity'][$cart_id] = $quantity;
			}
		}
		unset($this->request->post['prod_id_quantity']);
		return $deletedItems;
	}

	public function clear() {
		$this->load->language('checkout/cart');
		$this->load->language('extension/theme/prostore'); 

		$json = array();
		$cart_products = $this->prostore->helper->getProductsIdAsKey();
		if (!empty($cart_products)) {
			$json['deleted_items'] = array_keys($cart_products);
		}
		$json['total'] = sprintf($this->language->get('text_prostore_shopping_cart'), 0, $this->currency->format(0, $this->session->data['currency']));
		$this->cart->clear();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function setadd() {
		$json = array();
		$this->load->language('checkout/cart');
		$this->load->language('extension/theme/prostore'); 

		if (isset($this->request->post['setproducts'])) {
			$product_ids = $this->request->post['setproducts'];
		} else {
			$product_ids = array();
		}	

		if (isset($this->request->post['setid'])) {
			$setid = $this->request->post['setid'];
		} else {
			$setid = 0;
		}	

		$this->session->data['prostoresetid'][] = $setid;

		$option = array();


		foreach ($product_ids as $product_id => $quantity) {
			$this->cart->add($product_id, $quantity, $option);
		}

		$total = $this->getTotals();

		$json['total'] = sprintf($this->language->get('text_prostore_shopping_cart'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
		$json['success'] = $this->language->get('text_set_success');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	// prostore end
			
	public function remove() {
		$this->load->language('checkout/cart');

		$json = array();

		// Remove

		$this->load->language('extension/theme/prostore'); // prostore
		$cart_products = $this->prostore->helper->getCartIdsAsKey(); // prostore
			
		if (isset($this->request->post['key'])) {
			$this->cart->remove($this->request->post['key']);

			unset($this->session->data['vouchers'][$this->request->post['key']]);

			$json['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);

						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}


			// prostore
			if (isset($cart_products[$this->request->post['key']])) {
				$json['deleted_items'] = array((int)$cart_products[$this->request->post['key']]['product_id']);
			}
			// prostore end
			
			$json['total'] = sprintf($this->language->get('text_prostore_shopping_cart'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
