<?php
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		// prostore
		$this->load->language('extension/theme/prostore');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
		$data['cart_link'] = $this->url->link('checkout/cart');
		$data['category_time'] = $this->config->get('theme_prostore_category_time');
		$data['time_text_1'] = $this->language->get('text_time_text_1');
		$data['time_text_2'] = $this->language->get('text_time_text_2');
		$data['language_id'] = $this->config->get('config_language_id');
		// labels
			$this->load->model('extension/module/prostore');

			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();
		// labels	
		// prostore end												
			

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$tax_price = (float)$product_info['special'];
					} else {
						$special = false;
						$tax_price = (float)$product_info['price'];
					}
		
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format($tax_price, $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}


					// prostore
					$cartProductInfo = $this->prostore->helper->getCartInfo4Product($product_info);
					
					$wishCompareData = $this->prostore->helper->getDataWishCompare($product_info['product_id']);
					
					$extraImages = array();
					if ($this->config->get('theme_prostore_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($product_info['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							
						$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            
						}
					}

					$productLabels = $this->prostore->labels->getLabels4Product($product_info);

					extract($productLabels);									
					
					if ($this->config->get('theme_prostore_manufacturer') == 1) {
						$manufacturer = $this->language->get('text_prostore_model') . ' ' . $product_info['model'];
					} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
						$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $product_info['manufacturer'];
					} else {
						$manufacturer = false;
					}
					
					if ($product_info['quantity'] <= 0) {
						$stock = $product_info['stock_status'];
					} elseif ($this->config->get('config_stock_display')) {
						$stock = $this->language->get('text_instock') . ': ' . $product_info['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity');
					} else {
						$stock = $this->language->get('text_instock');
					}	
					
					if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
						$buy_btn = $product_info['stock_status'];
					} else {
						$buy_btn = '';
					}
				
					// prostore end
			
					$data['products'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,

					// prostore
					'manufacturer'     => $manufacturer,
					'quantity'         => $product_info['quantity'],
					'minimum'          => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
					'stock'            => $stock,
					'images'           => $extraImages,	
					'isnewest'         => $isnewest,
					'sales'       	   => $sales,
					'discount'         => $discount,
					'catch'       	   => $catch,
					'nocatch'          => $nocatch,
					'popular'	       => $popular,
					'hit'	 	       => $hit,
					'isincart'	       => $cartProductInfo['isincart'],
					'to_cart_quantity' => $cartProductInfo['to_cart_quantity'],
					'buy_btn'	       => $buy_btn,
					'reward'           => $product_info['reward'],
					'special_date_end' => $special_date_end,
					'wish_compare_data' => $wishCompareData,				
					// prostore
			
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			
					// prostore
			if(isset($setting['layout']) && strpos($setting['layout'],'column_') !== false){
				return $this->load->view('extension/module/featured_column', $data);
			}else{
				return $this->load->view('extension/module/featured', $data);
			}
					// prostore end

			
		}
	}
}