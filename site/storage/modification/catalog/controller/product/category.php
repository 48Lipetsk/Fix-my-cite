<?php
class ControllerProductCategory extends Controller {

// prostore
	public function breadList($category_id) {
		$this->load->model('catalog/category');
		$data = array();
		$categories = $this->model_catalog_category->getCategories($category_id);
		foreach($categories as $category){
			$data[] = array(
				'name'		=> $category['name'],
				'href'       => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}
		return $data;
	}

	public function doOptionColumns($options){
		$optionColumns = array();
		$column = $this->config->get('theme_prostore_product_opt_select');
		if (!$column) {
			$column = 2;
		}

		$qtyPerColumn = (int)(count($options)/$column);
        $i = 0;
        $k = 0;
 
		foreach ($options as $key => $option) {
			$optionColumns[$k][$i] = $option;
	        $i++;
	        if ($i == $column) {  
	            $i = 0;
	            $k++;
	        }    				
		}

		return $optionColumns;
	}

	public function totalproducts() {
		$this->load->language('extension/theme/prostore');
		$this->load->model('extension/module/prostore');
		$this->load->model('catalog/product');
		$json = array();


		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['min_price'])) {
			$min_price = $this->request->get['min_price'];
		} else {
			$min_price = '';
		}

		if (isset($this->request->get['max_price'])) {
			$max_price = $this->request->get['max_price'];
		} else {
			$max_price = '';
		}

		if (isset($this->request->get['filter_category_id'])) {
			$category_id = (int)$this->request->get['filter_category_id'];
		} else {
			$category_id = 0;
		}

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_sub_category' => $this->config->get('theme_prostore_subcategory'),//prostore
				'min_price'    		 => $min_price,
				'max_price'    		 => $max_price,
				'filter_filter'      => $filter,
				'limit'     		 => '10000'
			);
			
			if($this->config->get('theme_prostore_subcategory')){
				$filter_data['filter_sub_category'] = true;
			}
			
	        if ($this->config->get('config_tax')) {
	            $tax_data = $this->model_extension_module_prostore->gettax_class_id($filter_data);
		        if (isset($tax_data['tax_class_id'])) {
		        	$filter_data['filter_tax_class_id'] = $tax_data['tax_class_id'];
		        }       
			}

		$json['total'] = $this->model_catalog_product->getTotalProducts($filter_data);
		$json['text_show'] = $this->language->get('text_prostore_show');
		$json['text_products'] = $this->language->get('text_prostore_products');
		$json['id'] = rand();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
// prostore end
			

// prostore
	public function isMobile() {
		$user_agent = '';

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$user_agent = $this->request->server['HTTP_USER_AGENT'];
		}
	    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $user_agent);
	}
// prostore end
			
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

//prostore start
		$this->load->model('setting/setting');
		$this->load->language('extension/theme/prostore');

		if (isset($this->session->data['change_currency'])) {
			unset($this->session->data['change_currency']);
			unset($this->request->get['min_price']);
			unset($this->request->get['max_price']);
		}

		if (isset($this->request->get['min_price'])) {
			$min_price = $this->request->get['min_price'];
		} else {
			$min_price = '';
		}

		if (isset($this->request->get['max_price'])) {
			$max_price = $this->request->get['max_price'];
		} else {
			$max_price = '';
		}
		$data['language_id'] = $this->config->get('config_language_id');
//prostore stop
			

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit']) && (int)$this->request->get['limit'] > 0) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}


//prostore start
			if (isset($this->request->get['min_price'])) {
				$url .= '&min_price=' . $this->request->get['min_price'];
			} 

			if (isset($this->request->get['max_price'])) {
				$url .= '&max_price=' . $this->request->get['max_price'];
			} 
			$cat_url = '';
//prostore stop
			
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

$id = 0;//prostore
			

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],

						'breadList' => $this->breadList($id),// prostore
						'cat_id' => $id,// prostore
			
						
'href' => $this->url->link('product/category', 'path=' . $path ) // prostore
			
					);
				}

$id = $path_id;// prostore
			
			}

//prostore start
			$cat_url = $this->url->link('product/category', 'path=' . $path . '_' . $category_id);
//prostore stop
			
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// prostore
			$this->load->language('extension/theme/prostore');
			$data['schema'] = $this->config->get('theme_prostore_schema');
			$data['product_detail'] = $this->config->get('theme_prostore_product_detail');
			$data['category_time'] = $this->config->get('theme_prostore_category_time');
			$data['category_sorts'] = $this->config->get('theme_prostore_category_sorts');
			$data['category_limits'] = $this->config->get('theme_prostore_category_limits');
			$data['category_categories'] = $this->config->get('theme_prostore_category_categories');
			
			$data['button_fastorder_sendorder'] = $this->language->get('button_prostore_sendorder');
			$data['text_prostore_buy_click'] = $this->language->get('text_prostore_buy_click');
			$data['text_product_view_btn'] = $this->language->get('text_product_view_btn');
			$data['time_text_1'] = $this->language->get('text_time_text_1');
			$data['time_text_2'] = $this->language->get('text_time_text_2');
			$data['text_attributes'] = $this->language->get('text_attributes');
			$data['text_description'] = $this->language->get('text_description');
			$data['image_product_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
			$data['image_product_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

			$data['pc_view'] = $this->config->get('theme_prostore_pc_view');
			$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
			$data['cart_link'] = $this->url->link('checkout/cart');
			if ($this->config->get('theme_prostore_buy_click_pdata')) {
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
			$this->load->language('checkout/checkout');
			$data['entry_firstname'] = $this->language->get('entry_firstname');
			$data['entry_lastname'] = $this->language->get('entry_lastname');
			$data['entry_email'] = $this->language->get('entry_email');
			$data['entry_telephone'] = $this->language->get('entry_telephone');
			$data['entry_comment'] = $this->language->get('entry_comment');
			$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
			$data['button_to_cart'] = $this->language->get('text_to_cart');
			$data['descriptions'] = $this->config->get('theme_prostore_descriptions');
			// prostore end
			

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if ($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {
				$data['thumb'] = '';
			}


//prostore start
$data['schema_description'] = str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'))); 
$data['image_category_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width');
$data['image_category_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height');
//prostore stop
			
			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare');

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

//prostore start
			if (isset($this->request->get['min_price'])) {
				$url .= '&min_price=' . $this->request->get['min_price'];
			} 

			if (isset($this->request->get['max_price'])) {
				$url .= '&max_price=' . $this->request->get['max_price'];
			} 
//prostore stop
			

			$data['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);


//prostore start
				if(!$this->config->get('theme_prostore_subcategory')){
					unset($filter_data['filter_sub_category']);
				}
//prostore stop
			
				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),

					// prostore
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')),
					// prostore end
			
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'])
				);
			}

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,

				'min_price'			 => $min_price,//prostore
				'max_price'			 => $max_price,//prostore
			
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

//prostore start
			$this->load->model('extension/module/prostore');

			if($this->config->get('theme_prostore_subcategory')){
				$filter_data['filter_sub_category'] = true;
			}
				if (count($results) == 1) {
					$colmd = 8; 
				}elseif ($this->config->get('theme_prostore_category_categories') == 4) {
					$colmd = 4;
				}else{
					$colmd = 6;
				}
				$data['colmd'] = $colmd;

			if ($this->config->get('config_tax')) {
				$tax_data = $this->model_extension_module_prostore->gettax_class_id($filter_data);
				if (isset($tax_data['tax_class_id'])) {
					$filter_data['filter_tax_class_id'] = $tax_data['tax_class_id'];
				}
			}
//prostore stop
			

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			// prostore
			$currR = $this->currency->getSymbolRight($this->session->data['currency']);
			$currL = $this->currency->getSymbolLeft($this->session->data['currency']);

			if($currR){
				$data['currencydata'] = "R_" . $currR ;
			}else{
				$data['currencydata'] = "L_" . $currL ;
			}
		
			$data['url'] = $url;
			if (isset($this->request->get['page'])) {
				$data['url'] .= '&page=' . $this->request->get['page'];
			}
			$data['path'] = $this->request->get['path'];
			$data['category_id'] = $category_id;
			$data['cat_url'] = $cat_url;
			$data['product_total'] = $product_total;
			$data['currency'] = $this->session->data['currency'];
			// prostore
			


			// prostore

			$this->load->model('extension/module/prostore');

			$data['language_id'] = $this->config->get('config_language_id');

			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();

			$productsLabels = $this->prostore->labels->getLabels4Products($results);

			// prostore end
			
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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
					$rating = $result['rating'] > 0 ? number_format($result['rating'] , 1) : 0;
				} else {
					$rating = false;
				}


                $productPath = $this->request->get['path'];
                if($this->config->get('theme_prostore_subcategory') && !$this->config->get('config_seo_pro')){
                    $pathInfo = $this->model_extension_module_prostore->getProductPath($result['product_id'],$this->request->get['path']);
                    if($pathInfo){
                        $productPath = $pathInfo;
                    }
                }   
            

				// prostore
				$extraImages = array();
				if ($this->config->get('theme_prostore_images_status')) {
					$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
					foreach($images as $imageX){
						$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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

				$attribute_groups = $this->model_catalog_product->getProductAttributes($result['product_id']);

				// prostore end
			
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,

					// prostore
					'manufacturer'  => $manufacturer,
					'quantity'        => $result['quantity'],
					'stock'        => $stock,
					'images'       => $extraImages,	
					'isnewest'       => $isnewest,
					'sales'       => $sales,
					'discount'       => $discount,
					'catch'       => $catch,
					'nocatch'       => $nocatch,
					'popular'	  => $popular,
					'hit'	 	  => $hit,
					'isincart'	  => $cartProductInfo['isincart'],
					'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],
					'attribute_groups'	 	  => $attribute_groups,
					'buy_btn'	  => $buy_btn,
					'reward'      => $result['reward'],
					'special_date_end'      => $special_date_end,
					'wish_compare_data' => $wishCompareData,
					// prostore
			
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      =>  $result['rating'] > 0 ? number_format($result['rating'], 1) : 0,
					'href'        => $this->url->link('product/product', 'path=' . $productPath . '&product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

//prostore start
			if (isset($this->request->get['min_price'])) {
				$url .= '&min_price=' . $this->request->get['min_price'];
			} 

			if (isset($this->request->get['max_price'])) {
				$url .= '&max_price=' . $this->request->get['max_price'];
			} 
//prostore stop
			


//prostore start
			if ($product_total) {
				$data['product_num'] = $product_total .' '. $this->prostore->helper->correctForm(array($product_total,'product'));
			} else {
				$data['product_num'] = false;
			}

			$data['nextPageUrl'] = '';
			$num_pages = ceil($product_total / $limit);
			if ($page < $num_pages) {
				$data['nextPageUrl'] = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page='.($page+1) );
			}
//prostore stop
			
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
			}
			
			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

// prostore
			$data['sort_title'] = $this->language->get('text_default');
			foreach ($data['sorts'] as $value) {
				if ($value['value'] == $sort . '-' . $order) {
					$data['sort_title'] = $value['text'];
				}
			}

			if ($this->config->get($this->config->get('theme_prostore_config_captcha_fo') . '_status')) {
				$data['captcha_fo'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_config_captcha_fo'));
			} else {
				$data['captcha_fo'] = '';
			}
			$data['viewSub'] = '3';	
			if ($this->config->get('theme_prostore_pc_view')) {
				$viewSub = explode('-',$this->config->get('theme_prostore_pc_view'));          	
				if(isset($viewSub[1])){
					$viewSub = $viewSub[1];
					$data['viewSub'] = $viewSub; 
				}
			}

			if (isset($this->request->post['view'])) {
				$view = $this->request->post['view'];
				$this->session->data['view'] = $view;
			}elseif(isset($this->session->data['view'])){
				$view = $this->session->data['view'];
			} else {
				if ($this->ismobile()) {
					$view = $this->config->get('theme_prostore_mobile_view');
				}else{
					$view = $this->config->get('theme_prostore_pc_view');
				}
				$this->session->data['view'] = $view;
			}

			$data['viewLayer'] = $view;
			$data['viewLayerM'] = $view;
			$data['view'] = $view;

			if(strpos($data['view'],'grid') !== false){	
				$viewSub = explode('-',$view);
				$data['view'] = 'grid';		          	
				if(isset($viewSub[1])){
					$viewSub = $viewSub[1];
					$data['viewSub'] = $viewSub; 
				}
			}

			if (isset($this->request->post['view'])) {
				$this->response->setOutput($this->load->view('product/category_'.$data['view'], $data));
				return ;
			}elseif(isset($this->request->get['popupdetail'])){
				$this->response->setOutput($this->load->view('product/category_popup', $data));
				return ;			
			}
			$data['buyclick_form'] = $this->load->view('product/buyclick_form', $data);
			$data['productsview'] = $this->load->view('product/category_'.$data['view'], $data);
// prostore end
			

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

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
}
