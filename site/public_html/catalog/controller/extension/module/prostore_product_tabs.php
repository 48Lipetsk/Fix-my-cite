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

class ControllerExtensionModuleProstoreProductTabs extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');
		$this->load->language('extension/theme/prostore');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_tax'] = $this->language->get('text_tax');

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');
		$data['text_reward'] = $this->language->get('text_reward');

		$data['language_id'] = $this->config->get('config_language_id');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
		$data['cart_link'] = $this->url->link('checkout/cart');
		$data['category_time'] = $this->config->get('theme_prostore_category_time');
		$data['time_text_1'] = $this->language->get('text_time_text_1');
		$data['time_text_2'] = $this->language->get('text_time_text_2');
		$data['image_product_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
		$data['image_product_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
			
		if (isset($setting['view'])) {
			$data['view'] = $setting['view'];
		} else {
			$data['view'] = '';
		}

		if (isset($setting['tp_limit'])) {
			$data['tp_limit'] = $setting['tp_limit'];
		} else {
			$data['tp_limit'] = 3;
		}

		if (isset($setting['show_more'])) {
			$data['show_more'] = $setting['show_more'];
		} else {
			$data['show_more'] = 1;
		}		

		if (isset($setting['images_status'])) {
			$images_status = $setting['images_status'];
		} else {
			$images_status = 1;
		}
		
		if(isset($setting['title' . $this->config->get('config_language_id')])){ 
			$data['title'] = $setting['title' . $this->config->get('config_language_id')];
		}
		
		$data['module_id'] = isset($setting['module_id']) ? $setting['module_id'] : 0;
		
		// labels
			$labelsInfo = array();
			if($this->config->get('theme_prostore_label')){
				$labelsInfo = $this->config->get('theme_prostore_label');
			}
			$data['labelsinfo'] = $labelsInfo ;
		// labels
		$dataTabs = array();
		
		if (!empty($setting['theme_prostore_product_tabs'])) {
    foreach ($setting['theme_prostore_product_tabs'] as $tab => $product_tab) {
        if (isset($dataTabs[$product_tab['sort']])) {
            $dataTabs[] = $product_tab;
        } else {
            $dataTabs[$product_tab['sort']] = $product_tab;
        }
    }
}
		$dataTabs = array();
		
		$data['product_tabs'] = $dataTabs;
		
		
		foreach($setting['theme_prostore_product_tabs'] as $tab => $product_tab){
			$product_tab['images_status'] = $images_status;
			$tabProducts = $this->getProducts($product_tab);
			if(!empty($tabProducts['products'])){
				if ($setting['show_more'] && count($tabProducts['products']) > $data['tp_limit'] && strpos($setting['layout'],'column_') === false) {
					$tabProducts['show_more'] = 1;
					if ($data['view'] == 'tab_grid' || $data['view'] == 'grid') {
						$tabProducts['products'] = array_slice($tabProducts['products'],0,$data['tp_limit']);
					}
				}

				if (isset($data['products'][$product_tab['sort']])) {
					$data['products'][] = $tabProducts; 
				}else{
					$data['products'][$product_tab['sort']] = $tabProducts;
				}
			}
		}
		

		if (isset($data['products']) && $data['products']) {
			if(isset($setting['layout']) && strpos($setting['layout'],'column_') !== false){
				return $this->load->view('extension/module/prostore_product_tabs_column', $data);
			}else{
				return $this->load->view('extension/module/prostore_product_tabs', $data);
			}
		}
	}

	public function sortProducts($productsInfo,$sort,$order=3) { 
		if (!$order || $order == 'DESC') {
			$order = 3;
		}else{
			$order = 4;
		}
		if ($sort == 'random') {
			shuffle($productsInfo);
		}elseif($sort == 'p.viewed' || $sort == 'p.date_added'){ 
				$sort = explode('.', $sort); 
				$setsort  = array_column($productsInfo, $sort[1]);
				array_multisort($setsort, $order, $productsInfo);
		}
		return $productsInfo;
	}


	public function getProducts($setting) {

			$this->load->model('catalog/product');
			$this->load->model('tool/image');
      		$this->load->model('extension/module/prostore');
			
			$data['products'] = array();
			$data['show_more'] = 0;

			if (!$setting['limit']) {
				$limit = 4;
			}else{
				$limit = $setting['limit'];
			}
			$results = array();
			$order = 'ASC';
			if(isset($setting['sortorder']) && ($setting['sortorder'] == 'discount' || $setting['sortorder'] == 'p.viewed' || $setting['sortorder'] == 'p.date_added')){
				$order = 'DESC';
			}
			if($setting['target'] == 0 || $setting['target'] == 2 ){ //Вывести все продукты
				$page = 1;
				$filter_category_ids = false;
				if($setting['target'] == '2'){					
					if(isset($setting['categories'])){
						$filter_category_ids = $setting['categories'];						
					}else{
						$filter_category_ids = array('-1');
					}						
				}				
				$filter_data = array(
					'filter_category_ids' => $filter_category_ids,
					'sort'               => $setting['sortorder'],
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
				); 
				if($setting['sortorder'] == 'bestseller'){ 
					$results = $this->model_catalog_product->getBestSellerProductsLS($filter_data);
				}elseif($setting['sortorder'] == 'discount'){ 
					$results = $this->model_catalog_product->getProductSpecials($filter_data);
				}else{ 
					if (!$filter_category_ids) { // Make cache for caregoriesless requests
					    $results = $this->cache->get('prodtabs.' . $setting['sortorder'] . '.' . $order . '.'  .  (int)($page - 1) * $limit  . '.'  .  $limit . '.'  . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_customer_group_id') . '.' . (int)$this->config->get('config_store_id'));
					    if (!$results) {
							if ($setting['sortorder'] == 'random') {
								$filter_data['limit'] = 200; // Get more products for random sort
								}
						    $results = $this->model_catalog_product->getProducts($filter_data);
						    $this->cache->set('prodtabs.' . $setting['sortorder'] . '.' . $order . '.'  .  (int)($page - 1) * $limit  . '.'  .  $limit . '.'  . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_customer_group_id') . '.' . (int)$this->config->get('config_store_id'), $results);
					    }
						if (is_array($results) && $setting['sortorder'] == 'random' && count($results)) {
							$results = $this->sortProducts($results,$setting['sortorder']);
							$results = array_slice($results,0,$limit);
						}
					}else{
						$results = $this->model_catalog_product->getProducts($filter_data);
					}
				}
				 
			}elseif($setting['target'] == '1' && isset($setting['products'])){ //Вывести только указанные продукты.
				$resultsTemp = array();
					foreach($setting['products'] as $product_id){
						$product_data = $this->model_catalog_product->getProduct($product_id);
						if ($product_data) {
							$resultsTemp[] = $product_data;
						}
					}
					$results = $this->sortProducts($resultsTemp,$setting['sortorder'],$order);
					$results = array_slice($results, 0, $limit);
			}elseif($setting['target'] == '3'){ //Вывести просмотренные.
				$cookies = array();
				$resultsTemp = array();
				if(isset($_COOKIE["899ProductsVieded"])){
					$i = 0;
					$cookies = explode(',',$_COOKIE['899ProductsVieded']);

					krsort($cookies);
					
					foreach($cookies as $product_id){
						$prodInfo = $this->model_catalog_product->getProduct($product_id);
						if(!$prodInfo){ continue;}
						$resultsTemp[] = $prodInfo;
						if(count($resultsTemp) >= $limit){ break;}
						$i++;
					}
					if (!empty($resultsTemp)) {
						$results = $this->sortProducts($resultsTemp,$setting['sortorder'],$order);
					}
				}
			}elseif ($setting['target'] == '4'){ //Вывести С этим товаром покупают.
				$products_id = array();
				$results = array();
				if (isset($this->request->get['product_id'])) {
					$products_id[] = (int)$this->request->get['product_id'];
					$results = $this->model_catalog_product->getAlsoOrderedProducts($products_id,$setting['sortorder'],$limit);
				} 

				if(empty($results)) { 
					$products = $this->cart->getProducts(); 
					foreach ($products as  $product) {
						$products_id[] =  $product['product_id'];
					}
					$results = $this->model_catalog_product->getAlsoOrderedProducts($products_id,$setting['sortorder'],$limit);
				}
				
			}


		// labels
			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();

		// labels

			$productsLabels = $this->prostore->labels->getLabels4Products($results);

			foreach ($results as $result) {

				if (!isset($result['product_id'])) { continue; }

				if ($result['image']) {
					$image = $this->config->get('theme_prostore_image_product_resize') ? $this->model_tool_image->prostore_resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')) : $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));// prostore

				} else {
					$image = $this->config->get('theme_prostore_image_product_resize') ? $this->model_tool_image->prostore_resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')) : $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));// prostore

				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'] > 0 ? number_format($result['rating'], 1) : 0;
				} else {
					$rating = false;
				}

				$extraImages = array();	
				if ($this->config->get('theme_prostore_images_status')) {
					if ($setting['images_status']) {		
						$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							$extraImages[] = $this->config->get('theme_prostore_image_product_resize') ? $this->model_tool_image->prostore_resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')) : $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						}
					}	
				}	

				extract($productsLabels[$result['product_id']]);					

				$cartProductInfo = $this->prostore->helper->getCartInfo4Product($result);

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
				
				if ($this->config->get('theme_prostore_manufacturer') == 1) {
					$manufacturer = $this->language->get('text_prostore_model') . ' ' . $result['model'];
				} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
					$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $result['manufacturer'];
				} else {
					$manufacturer = false;
				}
				
				$wishCompareData = $this->prostore->helper->getDataWishCompare($result['product_id']);

				if($setting['target'] == 0 && $setting['sortorder'] == 'discount' && !$special){ continue;} //Фильтр - добавлять только акционные товары
					
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					
					'manufacturer'  => $manufacturer,// prostore
					'quantity'        => $result['quantity'],// prostore
					'stock'        => $stock,// prostore
					'images'       => $extraImages,// prostore	
					'isnewest'       => $isnewest,// prostore
					'sales'       => $sales,// prostore
					'discount'       => $discount,// prostore
					'catch'       => $catch,// prostore
					'nocatch'       => $nocatch,// prostore
					'popular'	  => $popular,// prostore
					'isincart'	  => $cartProductInfo['isincart'],// prostore
					'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],// prostore
					'hit'	 	  => $hit,// prostore
					'buy_btn'	  => $buy_btn,// prostore
					'reward'      => $result['reward'],// prostore
					'special_date_end'      => $special_date_end,// prostore
					'wish_compare_data' => $wishCompareData,// prostore		
					
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => ($result['minimum'] > 0) ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'] )
				);
			}
			
		return $data;
	}

	public function showmore(){
		// Функционал кнопки "Показать еще"
		$dataOut = '';

		$this->load->model('setting/module');

		if (!isset($this->request->post['module_id']) || !$this->request->post['module_id']) {
			return;
		}else{
			$module_id = (int)$this->request->post['module_id'];
		}

		$setting_info = $this->model_setting_module->getModule($module_id[0]);
		$setting_info['module_id'] = $module_id[0];
		$setting_info['show_more'] = 0;

		$dataOut = $this->index($setting_info);

		$this->response->setOutput($dataOut);

	}
}
