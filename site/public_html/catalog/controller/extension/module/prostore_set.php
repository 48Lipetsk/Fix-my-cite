<?php
	if (! function_exists('array_column')) {  // To compatibility with PHP < 5.5 
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
	
	class ControllerExtensionModuleProstoreSet extends Controller {
		private $setting;
		public function index($setting) {
			$this->load->language('extension/module/prostore_set');

			$this->setting = $setting;
			if (!isset($this->setting['discount_status'])) {
				$this->setting['discount_status'] = 1;
			}
			
			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_sale'] = $this->language->get('text_sale');
			$data['text_quantity'] = $this->language->get('text_quantity');
			
			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['image_product_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
			$data['image_product_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
			
			$this->load->model('catalog/product');
			
			$this->load->model('extension/module/prostore');
			
			$this->load->model('tool/image');
			
			$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
			
			$products = array();
			$data['sets'] = array();
			$total = 0;
			
			
			if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
				$product_id = $this->request->get['product_id'];
				}else{
				$product_id = 0;
			}
			
			if (isset($setting['limit'])) {
				$limit = $setting['limit'];
				}else{
				$limit = 10;
			}
			
			
			
			$sets = $this->model_extension_module_prostore->getSets4Product($product_id,$limit);
			
			if (!empty($sets)) {
				
				foreach ($sets as $set_id => $set) {
					
					
					$productsInfo = $this->getProductsInfo($set['products'], $set_id);
					$products = $productsInfo['products'];
					$total = $productsInfo['total'];
					
					$setInfo = $this->model_extension_module_prostore->getSetInfo($set_id);
					
					if ($set['mode']) {
						$discount = $total/100*(int)$set['discount'];
						}else{
						$discount = (int)$set['discount'];
					}
					
					$totalf = $this->currency->format($total - $discount , $this->session->data['currency']);
					$discountf = $this->currency->format($discount, $this->session->data['currency']);
					
					if (count($products) < 2) { // If count of products is less then 2 , the set will not be outputed
						continue;
					}
					
					$data['sets'][$set_id] = array(
					'products'   => $products,
					'total'		 => $this->currency->format($total, $this->session->data['currency']),
					'totalf'		 => $totalf,
					'discount'	 => $discountf,
					'heading_title'	 => $setInfo['title'],
					'mode'		 => $set['mode']
					);
					
				}
				
				$data['labelsinfo'] = $productsInfo['labelsinfo'];
				$data['language_id'] = $productsInfo['language_id'];
				

			}
			if ($data['sets']) {
				
				return $this->load->view('extension/module/prostore_set', $data);
				
			}
		}
		
		public function getvariants() {
			$this->load->language('extension/module/prostore_set');
			
			$this->load->model('catalog/product');
			
			$this->load->model('extension/module/prostore');
			
			$this->load->model('tool/image');
			
			$data = array();
			$data['products'] = array();
			
			if ((!isset($this->request->get['setproduct_id']) || !$this->request->get['setproduct_id']) || (!isset($this->request->get['set_id']) || !$this->request->get['set_id'])) {
				return;
				}else{
				$product_id = $this->request->get['setproduct_id'];
				$set_id = $this->request->get['set_id'];
			}
			
			$products = $this->model_extension_module_prostore->getVar4SetProduct($set_id,$product_id);
			
			$productsInfo = $this->getProductsInfo($products);
			
			$data = array(
        	'set_id'	=> $set_id,
        	'products'  => $productsInfo['products'],
        	'text_quantity'  => $this->language->get('text_quantity'),
        	'text_popup_package_title'  => $this->language->get('text_popup_package_title'),
        	'text_item_add'  => $this->language->get('text_item_add'),
        	'for_product' => $product_id,
        	'labelsinfo' => $productsInfo['labelsinfo'],
        	'language_id' => $productsInfo['language_id']
			);
			
			
			
			$this->response->setOutput($this->load->view('extension/module/prostore_set_var', $data));
		}
		
		
		public function getproduct() {
			$this->load->language('extension/module/prostore_set');
			
			$this->load->model('catalog/product');
			
			$this->load->model('extension/module/prostore');
			
			$this->load->model('tool/image');
			
			$data = array();
			$data['products'] = array();
			
			if (!isset($this->request->get['setproduct_id']) || !$this->request->get['setproduct_id']) {
				return;
				}else{
				$product_id = $this->request->get['setproduct_id'];
			}
			
			if ($this->request->get['qty']) {
				$quantity = $this->request->get['qty'];
				}else{
				$quantity = 1;
			}
			
			$products[] = array(
			'product_id' => $this->request->get['setproduct_id'],
			'quantity'   => $quantity,
			'sort_order' => 1
			);
			
			
			$data = $this->getProductsInfo($products);
			$data['text_quantity'] = $this->language->get('text_quantity');
			
			//        $data['products'] = $productsInfo['products'];
			
			
			
			$this->response->setOutput($this->load->view('extension/module/prostore_set_prod', $data));
		}
		
		
		public function refreshtotal() {
			$this->load->language('extension/module/prostore_set');
			
			$this->load->model('catalog/product');
			
			$this->load->model('extension/module/prostore');
			
			$this->load->model('tool/image');
			
			$data = array();
			$json = array();
			$data['products'] = array();
			$product_ids = array();
			
			
			if (!isset($this->request->post['setproducts']) || !$this->request->post['setproducts']) {
				return;
				}else{
				$product_ids = $this->request->post['setproducts'];
			}
			
			$set_id = $this->request->post['setid'];
			
			foreach ($product_ids as $id => $quantity) {
				$products[] = array(
				'product_id' => $id,
				'quantity'   => $quantity,
				'sort_order' => 1
				);
			}
			
			
			
			$productsInfo = $this->getProductsInfo($products);
			
			$total = $productsInfo['total'];
			$setInfo = $this->model_extension_module_prostore->getSetInfo($set_id);
			
			if ($setInfo['mode']) {
				$discount = $total/100*(int)$setInfo['discount'];
				}else{
				$discount = (int)$setInfo['discount'];
			}
			
			$totalf = $this->currency->format($total , $this->session->data['currency']);
			$actualPricef = $this->currency->format($total - $discount , $this->session->data['currency']);
			$discountf = 'Скидка '.$this->currency->format($discount, $this->session->data['currency']);
			
			$data = array(
			'total'		 => $totalf,
			'actualprice'=> $actualPricef,
			'discount'	 => $discountf
			);
			
			$json['success'] = $data;
			
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			
		}
		
		
		public function getProductsInfo($items, $set_id = 0) {  
			
			
			$this->load->model('catalog/product');
			
			$this->load->model('extension/module/prostore');
			
			$this->load->model('tool/image');
			
			$products = array();
			$data = array();
			
			$total = 0;
			
			$this->load->language('extension/theme/prostore');
			
			if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
				$product_id = $this->request->get['product_id'];
				}else{
				$product_id = 0;
			}
			
			// labels
			$labelsInfo = $this->prostore->labels->getProductLabelsConfig();
	
			// labels	
			
			foreach ($items as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				
				if ($product_info) {
					
					$countVariants = 0;
					
					if ($set_id) {
						$variantProducts = $this->model_extension_module_prostore->getVar4SetProduct($set_id,$product['product_id']);
						$countVariants = count($variantProducts);
					}
					
					
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
					}
					
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
						$price = false;
					}
					
					if ((float)$product_info['special'] && isset($this->setting['discount_status']) && $this->setting['discount_status']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
						$special = false;
					}

					$product_discount = $this->getProductDiscount($product,$product_info);
					if (!empty($product_discount)) {
						$price = $product_discount['price_format'];
						$product_info['price'] = $product_discount['price'];
					}

					
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
						$tax = false;
					}
					
					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'] > 0 ? number_format($product_info['rating'], 1) : 0;
						} else {
						$rating = false;
					}
					
					$extraImages = array();	
					if ($this->config->get('theme_prostore_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($product_info['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						}
					}

					$productLabels = $this->prostore->labels->getLabels4Product($product_info);

					extract($productLabels);
										
					$wishCompareData = $this->prostore->helper->getDataWishCompare($product_info['product_id']);
					
					if ($this->config->get('theme_prostore_manufacturer') == 1) {
						$manufacturer = $this->language->get('text_prostore_model') . ' ' . $product_info['model'];
						} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
						$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $product_info['manufacturer'];
						} else {
						$manufacturer = false;
					}

					$cartProductInfo = $this->prostore->helper->getCartInfo4Product($product_info);

					if ($product_info['quantity'] <= 0) {
						$stock = $product_info['stock_status'];
						} elseif ($this->config->get('config_stock_display')) {
						$stock = $product_info['quantity'];
						} else {
						$stock = $this->language->get('text_instock');
					}	
					
					if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
						$buy_btn = $product_info['stock_status'];
						} else {
						$buy_btn = '';
					}
					
					$row_id = 0;
					if (isset($product['row_id'])) {
						$row_id = $product['row_id'];
					}	
					
					
					
					if($special){
						$total += $product_info['special']*$product['quantity'];
						}else{
						$total += $product_info['price']*$product['quantity'];
					}	
					
					if ($product_info['product_id'] == $product_id) {
						$sort_order = 0;
						}else{
						$sort_order = $product['sort_order'];
					}				
					
					
					
					$products[] = array(
					'product_id' 			 	 => $product_info['product_id'],
					'thumb'      				 => $image,
					'name'        				 => $product_info['name'],
					'description'				 => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'     		 		 => $price,
					'isnewest'     			  	 => $isnewest,
					'sales'      			 	 => $sales,
					'discount'     			 	 => $discount,
					'buy_btn'	 			 	 => $buy_btn,
					'reward'     			 	 => $product_info['reward'],
					'manufacturer' 			 	 => $manufacturer,
					'stock'       				 => $stock,
					'images'     			 	 => $extraImages,	
					'catch'      				 => $catch,
					'popular'  					 => $popular,
					'hit'    				 	 => $hit,
					'isincart'	      			 => $cartProductInfo['isincart'],
					'to_cart_quantity' 			 => $cartProductInfo['to_cart_quantity'],
					'nocatch'      				 => $nocatch,
					'minimum'      		         => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
					'row_id'	 			     => $row_id,
					'special'                    => $special,
					'wish_compare_data'          => $wishCompareData,
					'countvariants'			     => $countVariants,
					'tax'        				 => $tax,
					'rating'      				 => $rating,
					'href'       				 => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'quantity'		 			 => $product['quantity'],
					'sort_order'		 		 => $sort_order
					);
				}
			}
			
			$setsort  = array_column($products, 'sort_order');
			array_multisort($setsort, SORT_ASC, $products);
			
			$data = array(
				'products' => $products,
				'total'	   => $total,
				'labelsinfo'         => $labelsInfo,
				'language_id' => $this->config->get('config_language_id')
			);
			
			return $data;
		}

		protected function getProductDiscount($product,$product_info){
			$outData = [];
			if (isset($this->setting) && !$this->setting['discount_status']) {
				return $outData;
			}
			$discountPrice = false;
			$this->load->model('catalog/product');
			$product_discounts = $this->model_catalog_product->getProductDiscounts($product['product_id']);
			if (empty($product_discounts)) {
				return $outData;
			}
			foreach ($product_discounts as $discount) {
				if ($product['quantity'] >= $discount['quantity']) {
					$discountPrice = $discount['price'];
				}
			}
			if ($discountPrice) {
				$discountPriceFormat = $this->currency->format($this->tax->calculate($discountPrice, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$outData = array(
					'price' => $discountPrice,
					'price_format' => $discountPriceFormat
				);
			}
			return $outData;
		}		
		
	}	