<?php
class ControllerExtensionModuleBestSeller extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/bestseller');

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
			

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}
	
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}


					// prostore
					$cartProductInfo = $this->prostore->helper->getCartInfo4Product($result);
					
					$wishCompareData = $this->prostore->helper->getDataWishCompare($result['product_id']);
					
					$extraImages = array();
					if ($this->config->get('theme_prostore_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							
						$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            
						}
					}

					$productLabels = $this->prostore->labels->getLabels4Product($result);

					extract($productLabels);									
					
					if ($this->config->get('theme_prostore_manufacturer') == 1) {
						$manufacturer = $this->language->get('text_prostore_model') . ' ' . $result['model'];
					} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
						$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $result['manufacturer'];
					} else {
						$manufacturer = false;
					}
					
					if ($result['quantity'] <= 0) {
						$stock = $result['stock_status'];
					} elseif ($this->config->get('config_stock_display')) {
						$stock = $this->language->get('text_instock') . ': ' . $result['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity');
					} else {
						$stock = $this->language->get('text_instock');
					}	
					
					if ($result['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
						$buy_btn = $result['stock_status'];
					} else {
						$buy_btn = '';
					}
				
					// prostore end
			
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,

					// prostore
					'manufacturer'     => $manufacturer,
					'quantity'         => $result['quantity'],
					'minimum'          => $result['minimum'] > 0 ? $result['minimum'] : 1,
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
					'reward'           => $result['reward'],
					'special_date_end' => $special_date_end,
					'wish_compare_data' => $wishCompareData,				
					// prostore
			
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			
					// prostore
			if(isset($setting['layout']) && strpos($setting['layout'],'column_') !== false){
				return $this->load->view('extension/module/bestseller_column', $data);
			}else{
				return $this->load->view('extension/module/bestseller', $data);
			}
					// prostore end
			
		}
	}
}
