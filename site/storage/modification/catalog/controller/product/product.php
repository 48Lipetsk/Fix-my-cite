<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);


		// prostore
		$this->load->language('extension/theme/prostore');
		$data['text_prostore_points'] = $this->language->get('text_prostore_points');
		$data['schema'] = $this->config->get('theme_prostore_schema');
		$data['soc_share_code'] = html_entity_decode($this->config->get('theme_prostore_soc_share_code'), ENT_QUOTES, 'UTF-8');
		$data['soc_share_prod'] = $this->config->get('theme_prostore_soc_share_prod');
		$data['optMode'] = $this->config->get('theme_prostore_product_opt_select');
		$data['opt_price'] = $this->config->get('theme_prostore_product_opt_price');
		$data['sku_compact'] = $this->config->get('theme_prostore_product_opt_type');
		$data['category_time'] = $this->config->get('theme_prostore_category_time');
		$data['time_text_1'] = $this->language->get('text_time_text_1');
		$data['time_text_2'] = $this->language->get('text_time_text_2');
		$data['text_review'] = $this->language->get('text_review');
		$data['text_review_num_1'] = $this->language->get('text_review_num_1');
		$data['text_review_num_2'] = $this->language->get('text_review_num_2');
		$data['text_review_num_3'] = $this->language->get('text_review_num_3');	
		$data['text_show_more'] = $this->language->get('text_show_more');
		$data['text_review_plus'] = $this->language->get('text_review_plus');
		$data['text_review_minus'] = $this->language->get('text_review_minus');
		$this->load->model('extension/module/prostore');
		$this->load->model('extension/module/prostore_faq');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
		$data['product_in_tab'] = $this->config->get('theme_prostore_product_in_tab');
		$data['product_thumbs'] = $this->config->get('theme_prostore_product_thumbs');
		$isDateTime = false;
		$isRequired = false;
		// prostore end
			
		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			$id = 0;
			

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],

					'breadList' => $this->breadList($id),// prostore
					'cat_id' => $id,// prostore
			
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}

				$id = $path_id;
			
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

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
					'text' => $category_info['name'],

					'breadList' => $this->breadList($id),// prostore
					'cat_id' => $id,// prostore
			
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

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

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode(trim($this->request->get['tag']), ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		//check product page open from cateory page
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
						
			if(empty($this->model_catalog_product->checkProductCategory($product_id, $parts))) {
				$product_info = array();
			}
		}

		//check product page open from manufacturer page
		if (isset($this->request->get['manufacturer_id']) && !empty($product_info)) {
			if($product_info['manufacturer_id'] !=  $this->request->get['manufacturer_id']) {
				$product_info = array();
			}
		}

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode(trim($this->request->get['tag']), ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$this->document->setTitle($product_info['meta_title']);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			
			// prostore
			$data['has_shipping'] = $product_info['shipping'];
			$data['shipping_forecast'] = $this->config->get('theme_prostore_product_ship_forecast');
			$data['faq_status'] = $this->config->get('theme_prostore_product_faq')['status'];
			$data['shipping_address_form'] = $this->load->controller('extension/module/prostore/prostore_theme/shipping_form',$product_id);
			// prostore end
			

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];

			// prostore
			$data['prostore_mpn'] = $product_info['mpn'];
			$data['schema_description'] = str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')));
			// prostore
			
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				
				$data['stock'] = $this->language->get('text_instock') . ': ' . $product_info['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity'); // prostore
			
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

// prostore
			$data['currency'] = 'RUB';
			if (isset($this->session->data['currency'])) {
				$data['currency'] = $this->session->data['currency'];
			}
			$this->load->language('checkout/checkout');
			$data['entry_firstname'] = $this->language->get('entry_firstname');
			$data['entry_lastname'] = $this->language->get('entry_lastname');
			$data['entry_email'] = $this->language->get('entry_email');
			$data['entry_telephone'] = $this->language->get('entry_telephone');
			$data['entry_comment'] = $this->language->get('entry_comment');
			$this->load->language('extension/theme/prostore');
			$this->load->model('catalog/information');
			if ($this->config->get('theme_prostore_review_pdata')) {	
				$review_pdata = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_review_pdata'));
				if ($review_pdata) {
					$data['text_review_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('button_continue'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_review_pdata'), true), $review_pdata['title'], $review_pdata['title']);
				} else {
					$data['text_review_pdata'] = '';
				}
			} else {
				$data['text_review_pdata'] = '';
			}

			$data['button_fastorder_sendorder'] = $this->language->get('button_prostore_sendorder');

			$data['text_prostore_products_text_more'] = $this->language->get('text_prostore_products_text_more');
			$data['text_prostore_short_descr'] = $this->language->get('text_prostore_short_descr');
			$data['text_prostore_products_review'] = $this->language->get('text_prostore_products_review');
			
			$tempDesc = $this->descriptionExtra($data['description']);
			$data['description'] = $tempDesc['fulldesc'];	
			if (!$this->config->get('theme_prostore_product_short_descr')) {
				$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			}
			$data['short_descr'] = $this->config->get('theme_prostore_product_short_descr');
			if ($this->config->get('theme_prostore_product_zoom_ezplus')) {
				$this->document->addScript('catalog/view/javascript/prostore/plugins/jquery.ez-plus.min.js');
			}
			$data['zoom_ezplus'] = $this->config->get('theme_prostore_product_zoom_ezplus');
			$data['zoom_fancybox'] = $this->config->get('theme_prostore_product_zoom_fancybox');
			$data['p_related_view'] = $this->config->get('theme_prostore_p_related_view');
			$data['shortdescription'] = $tempDesc['shortdesc'];			
			$data['typeOptAtt'] = $this->config->get('theme_prostore_product_att_select');
			$data['typeOptSelect'] = $this->config->get('theme_prostore_product_opt_select');
			$data['typeOptCheckImg'] = $this->config->get('theme_prostore_product_opt_checkbox_img');
			$data['typeOptRadioImg'] = $this->config->get('theme_prostore_product_opt_radio_img');
			$data['att_right'] = $this->config->get('theme_prostore_product_att_right');
			$data['store'] = $this->config->get('config_name');
			$data['image_thumb_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width');
			$data['image_thumb_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height');
			$data['image_additional_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width');
			$data['image_additional_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height');
			$data['image_related_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width');
			$data['image_related_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height');
			$faq_total = $this->model_extension_module_prostore_faq->getTotalFaqsByProductId($product_id);
			$data['faq_total'] = $faq_total;
			if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
				$data['buy_btn'] = $product_info['stock_status'];
			} else {
				$data['buy_btn'] = '';
			}
			

			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				
			$data['popup'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));

				if ($this->config->get('theme_prostore_og')) { //Prostore added this
					$this->document->setOgImage($data['thumb']);
				} //Prostore added this
			
			} else {
				
				$data['thumb'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			
			}


			if ($product_info['image']) {
				$data['additional'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
			} else {
				$data['additional'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
			}		
				
			$tab_video = array();
			$data['count_videos'] = 0;
			$data['customTabs'] = $this->model_extension_module_prostore->getFields4Product($this->request->get['product_id']);
			if (isset($data['customTabs']['video'])) {
				$tab_video = $data['customTabs']['video'];
				$data['count_videos'] = count($tab_video);
			}

			$data['video'] = $tab_video;
			
			$data['images'] = false;

			$wishCompareData = $this->prostore->helper->getDataWishCompare($product_id);
			$data['compare_data'] = $wishCompareData['compare_data'];
			$data['wish_data'] = $wishCompareData['wish_data'];

			if($data['compare_data']['is_in_compare']){
				$data['text_prostore_to_comp'] = $this->language->get('text_prostore_in_comp');
			}
		
			if($data['wish_data']['is_in_wish']){
				$data['text_prostore_to_wishlist'] = $this->language->get('text_prostore_in_wishlist');
			}			
// prostore end
			
			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					
			'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height')),
			'additional' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
			
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				$data['price_schema'] = number_format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), 0, '', '');// prostore
			
			} else {
				$data['price'] = false;

				$data['price_schema'] = false;// prostore
			
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				$data['special_schema'] = number_format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), 0, '', '');// prostore
				$data['economy'] = $this->currency->format($this->tax->calculate(($product_info['price'] - $product_info['special']), $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);// prostore
			
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;

				$data['special_schema'] = false;// prostore
				$data['economy'] = false;// prostore
			
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],

					'date_end' => $discount['date_end'],// prostore
			
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				$isImage = false; //prostore
			

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}


//prostore
						if ($option_value['image']) {
							$isImage = true;
						}
//prostore
			
						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix'],
'is_default'            => $option_value['is_default'],
							'link'                    => $option_value['link']
						);
					}
				}


//prostore
				if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$isDateTime = true;
				}
				if ($option['required']) {
					$isRequired = true;
				}
//prostore
			
				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],

					'isimage'			  => $isImage,  //prostore
			
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}


			// prostore

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['item_price'] = $this->currency->format((float)$product_info['special'],$this->session->data['currency'],'',false);
			} else {
				$data['item_price'] = $this->currency->format((float)$product_info['price'],$this->session->data['currency'],'',false);
			}

			$cartProductInfo = $this->prostore->helper->getCartInfo4Product($product_info);

			$data['isincart'] = $cartProductInfo['isincart'];

			$data['to_cart_quantity'] = $cartProductInfo['to_cart_quantity'];

			$data['cart_link'] = $this->url->link('checkout/cart');		

			$data['isRequired'] = $isRequired;

			if ($isDateTime) {
				if ($this->config->get('theme_prostore_bootstrap_ver')) { //bootstrap5
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
				$data['datepicker'] = $this->language->get('code');// prostore
				$data['date_format'] = $this->language->get('text_prostore_date_format');// prostore
				$data['datetime_format'] = $this->language->get('text_prostore_datetime_format');// prostore
				$data['time_format'] = $this->language->get('text_prostore_time_format');// prostore
			}
			

			$data['images_for_schema'] = '';

			if ($data['schema']) {
				$s_images = array();
				if ($data['thumb']) {
					$s_images[] = $data['popup'];
				}				
				foreach ($data['images'] as  $s_image) { 
					if (!isset($s_image['isvideo']) || (isset($s_image['isvideo']) && !$s_image['isvideo'])) {
						$s_images[] = $s_image['popup'];
					}
				}
				$data['images_for_schema'] = '"' . implode('","', $s_images) . '"';
			}

			$data['prep_options'] = $this->doOptionColumns($data['options']);

			$data['quantity'] = $product_info['quantity'];
			$data['special_date_end'] = $data['discount_date_end'] = 0;

			if ($product_info['discount_date_end'] && $product_info['discount_date_end'] != '0000-00-00' ) {
				$data['discount_date_end'] = $product_info['discount_date_end'];
			}

			$data['reviews_num'] = (int)$product_info['reviews'];

			$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);
			
			$review_num_1 = $this->language->get('text_review_num_1');
			$review_num_2 = $this->language->get('text_review_num_2');
			$review_num_3 = $this->language->get('text_review_num_3');
			$review_num = array($review_num_1, $review_num_2, $review_num_3);
			if ($review_total) {
				$data['review_num'] = $review_total .' '. $this->correctForm($review_total, $review_num);
			} else {
				$data['review_num'] = false;
			}

			
			$resultsLs = $this->model_catalog_review->getReviewsStatsByProductId($this->request->get['product_id']);
			for ($x=1; $x<6; $x++){
				$data['reviewsStats'][$x] = array(
					'rating'     => $x,
					'count'     => 0,
					'percent'     => 0,
					'text'     => $this->language->get('text_review_num_2'),
					'link'     => 'product_id=' . $this->request->get['product_id'] . '&rating=' . $x
				);
			}

			foreach ($resultsLs as  $ratingCount) {
				if ($review_total) {
					$percent = round(($ratingCount['totall']*100)/$review_total);
				}else{
					$percent = 0;
				}

				if ($ratingCount['totall'] < 5) {
					$text = $this->language->get('text_review_num_2'); 
				}else{
					$text = $this->language->get('text_review_num_2');
				}

				$data['reviewsStats'][$ratingCount['rating']] = array(
					'rating'     => $ratingCount['rating'],
					'count'     => $ratingCount['totall'],
					'percent'     => $percent,
					'text'     => $text,
					'link'     => 'product_id=' . $this->request->get['product_id'] . '&rating=' . $ratingCount['rating']
				);
			}
			krsort($data['reviewsStats']);
			// prostore end
			
			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = $product_info['rating'] > 0 ? number_format($product_info['rating'] , 1) : 0;

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);


			// prostore
			
			$attributes = array();
			$i = 0;
			foreach ($data['attribute_groups'] as $attribute_group) {
				foreach ($attribute_group['attribute'] as $key => $attribute) {
					if ($i > 8) { break; }
					$attributes[] = $attribute;
					$i++;
				}
			}
			$data['attributes'] = array_chunk($attributes,3);

			if ($this->config->get($this->config->get('theme_prostore_config_captcha_fo') . '_status')) {
				$data['captcha_fo'] = $this->load->controller('extension/captcha/' . $this->config->get('theme_prostore_config_captcha_fo'));
			} else {
				$data['captcha_fo'] = '';
			}
			$data['language_id'] = $this->config->get('config_language_id');
			
			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();

			/**
			 * Получаем стикеры для продукта
			 * getLabels4Product($product_info) возвращает массив стикеров для продукта
			 * @param bool $isnewest
			 * @param str $discount
			 * @param str $special_date_end
	 		 * @param bool $sales
			 * @param bool $catch
			 * @param bool $nocatch
			 * @param str $popular
			 * @param str $hit
			 */			
			$productLabels = $this->prostore->labels->getLabels4Product($product_info);

			foreach ($productLabels as $labelName => $labelValue) {
				$data[$labelName] = $labelValue;
			}

			$data['buy_click'] = array();
			if($this->config->get('theme_prostore_buy_click')){
				$data['buy_click'] = $this->config->get('theme_prostore_buy_click');

				if ($this->customer->isLogged()) {
					$this->load->model('account/customer');
					$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
				}
			}

			$data['found_cheaper_config'] = $this->config->get('theme_prostore_found_cheaper');
			$data['found_cheaper'] = $this->load->controller('extension/module/found_cheaper',['product_id' => $product_id]);
			
			$data['text_prostore_buy_click'] = $this->language->get('text_prostore_buy_click');			
			$data['blogs_related'] = '';

			if ($this->config->get('theme_prostore_blog_product')) {

				$this->load->model('extension/module/prostoreblog');
				$blogs = array();		
				$results = $this->model_extension_module_prostoreblog->getBlogsRelated2Prod($this->request->get['product_id']);

				foreach ($results as $result) {
					
					if ($result['image_preview']) {
						$image = $this->model_tool_image->resize($result['image_preview'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_height'));
					} elseif ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_blog_cat_height'));
					}

					$blogs[] = array(
						'title' => $result['title'],
						'image'       => $image,
						'date_added' => $this->prostore->helper->rus_date(strtotime($result['date_added'])),
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 120) . '...',
						'href'  => $this->url->link('extension/module/prostore_blog/getblog', 'blog_id=' . $result['blog_id'])
					);
				}
				$datablog['heading_title'] = $this->language->get('text_related_blogs');
				$datablog['blogs'] = $blogs;
				$data['blogs_related'] = $this->load->view('extension/module/prostore_blog_mod', $datablog);

		    }
			
			$data['image_product_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width');
			$data['image_product_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height');
			// prostore end
			
			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

$productsLabels = $this->prostore->labels->getLabels4Products($results); // prostore
			

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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


// prostore
				$extraImages = array();
				
					if ($this->config->get('theme_prostore_related_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
						}
					}

					extract($productsLabels[$result['product_id']]);

					if ($result['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
						$buy_btn = $result['stock_status'];
					} else {
						$buy_btn = '';
					}

					$cartProductInfo = $this->prostore->helper->getCartInfo4Product($result);
					
					
					if ($result['quantity'] <= 0) {
						$stock = $result['stock_status'];
					} elseif ($this->config->get('config_stock_display')) {
						$stock = $this->language->get('text_instock') . ': ' . $result['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity');
					} else {
						$stock = $this->language->get('text_instock');
					}		
					
					if ($this->config->get('theme_prostore_manufacturer') == 1) {
						$manufacturer = $this->language->get('text_prostore_model') . ' ' . $result['model'];
					} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
						$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $result['manufacturer'];
					} else {
						$manufacturer = false;
					}
					
					$wishCompareData = $this->prostore->helper->getDataWishCompare($result['product_id']);

// prostore end
			
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,

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
					'isincart'	  => $cartProductInfo['isincart'],
					'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],
					'hit'	 	  => $hit,
					'buy_btn'	  => $buy_btn,
					'reward'      => $result['reward'],
					'special_date_end'      => $special_date_end,
					'wish_compare_data' => $wishCompareData,				
					// prostore end
			
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . urlencode(html_entity_decode(trim($tag), ENT_QUOTES, 'UTF-8')))
					);
				}
			}


// prostore
			if (!isset($_COOKIE['899ProductsVieded'])) { 
				$cookies = array();
				$cookies[] = $product_id; 
				SetCookie("899ProductsVieded",implode(',',$cookies),time()+3600*24*30*1,"/");
			}else{
				$cookies = explode(',',$_COOKIE['899ProductsVieded']);
				if (!in_array($product_id, $cookies)) { 
					if (count($cookies) < 50) {
						$cookies[] = $product_id;
					}else{
						array_shift($cookies);
						$cookies[] = $product_id; 
					}
				}else{
					foreach ($cookies as $key => $cookie) {
						if ($cookie == $product_id) {
							unset($cookies[$key]); 
						}
					}
					$cookies[] = $product_id; 
				}
				SetCookie("899ProductsVieded",implode(',',$cookies),time()+3600*24*30*1,"/");
			}			
			$data['buyclick_form'] = $this->load->view('product/buyclick_form', $data);
			$data['buy_in_credit'] = $this->config->get('theme_prostore_buy_in_credit');
			if ($data['buy_in_credit']['status']) {
				$this->document->addScript('https://forma.tinkoff.ru/static/onlineScript.js');
				$tokenString = $data['buy_in_credit']['showcase_id'].':'.$data['buy_in_credit']['password'];
				if ($data['buy_in_credit']['demo_mode']) {
					$tokenString = 'demo-' . $tokenString;
				}
				$data['buy_in_credit_token'] = base64_encode($tokenString);
				$data['buy_in_credit_price'] =  sprintf($this->language->get('buy_in_credit_price_text'), $this->currency->format($this->tax->calculate($data['item_price']/($data['buy_in_credit']['price'] && (float)$data['buy_in_credit']['price'] != 0 ? (float)$data['buy_in_credit']['price'] : 17.89), $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']));
				$data['buy_in_credit_help'] = $this->config->get('theme_prostore_buy_in_credit_help' . $this->config->get('config_language_id'));
			}

			if ($this->config->get('theme_prostore_product_review')) {  
				$data['reviewsdata'] = $this->review(1);
			} else {
				$data['reviewsdata'] = false;
			}

			$data['faqs_content'] = $this->load->controller('extension/module/prostore/prostore_theme/get_faq');
			$data['faqs_total'] = $this->model_extension_module_prostore_faq->getTotalFaqsByProductId($this->request->get['product_id']);

// prostore end
			
			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');


// prostore start
			$data['href'] = '';
			$data['buy_click_typefrom'] = 'product';
			if (isset($this->request->get['popup'])) {
				$data['column_left'] = '';
				$data['column_right'] = '';
				$data['content_top'] = '';
				$data['content_bottom'] = '';
				$data['footer'] = '';
				$data['header'] = '';
				$data['href'] = $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id']);
				$data['buy_click_typefrom'] = 'category-popup';
			}
// prostore end
			
			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode(trim($this->request->get['tag']), ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
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

	
// prostore
	public function options_info() {
		$this->load->model('extension/module/prostore');
		$this->load->model('catalog/product');
		$this->load->language('checkout/cart');
		$product_option_value_data = array();
		$error = array();
		$totalOptionsPrice = 0;

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		if (isset($this->request->post['option'])) {
			$option = array_filter($this->request->post['option']);
		} else {
			$option = array();
		}

		$product_options = $this->model_catalog_product->getProductOptions($product_id);

		foreach ($product_options as $product_option) {
			if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
				$optionsInfo['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
			}
		}
		$optionsInfo['discount_option'] = false;
		if (!empty($option) && empty($error)) {
			foreach ($this->request->post['option'] as $product_option_value_id) {
				if (!$product_option_value_id) {
					continue;
				}
				$option_value = $this->model_extension_module_prostore->getProductOptionValueData($product_option_value_id);
				$price = $this->currency->format($option_value['price'], $this->session->data['currency'],'',false);
				$product_option_value_data[] = array(
					'product_option_value_id' => $option_value['product_option_value_id'],
					'option_value_id'         => $option_value['option_value_id'],
					'name'                    => $option_value['name'],
					'price'                   => $price,
					'price_prefix'            => $option_value['price_prefix']
				);
				if ($option_value['price_prefix'] == '+') {
					$totalOptionsPrice += $price;
				}else{
					$totalOptionsPrice -= $price;
					/*
					* if at lest one option with negative price is selected
					* - provide to tinkoff summary item price only. Will be done on twig.
					*/
					$optionsInfo['discount_option'] = true;
				}			
			}
			
		}
		$optionsInfo['product_option_value_data'] = $product_option_value_data;
		$optionsInfo['total_options_price'] = $totalOptionsPrice;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($optionsInfo));	
	}



	public function show_in_popup() {
		if (isset($this->request->get['prod_id'])) {
			$this->request->get['product_id'] = $this->request->get['prod_id'];
			return $this->index();
		}
	}

	public function like() {

		$this->load->model('extension/module/prostore');

		$json = array();
		$data = array();
		$likes = 0;

		$data['review_id'] = $this->request->get['review_id'];

		$likesInfo = $this->model_extension_module_prostore->getLikes4review($this->request->get['review_id']);

		$data['count_good'] = 0;
		$data['count_bad'] = 0;
		if ($likesInfo) {
			$data['count_good'] = $likesInfo['count_good'];
			$data['count_bad'] = $likesInfo['count_bad'];
		}

		if (!isset($_COOKIE["likesls"][(int)$data['review_id']] )){
			if ($this->request->get['islike']) {
				$likes = (int)$data['count_good'];
				$likes++;
				$data['count_good'] = $likes;
			}else{
				$likes = (int)$data['count_bad'];
				$likes++;
				$data['count_bad'] = $likes;
			}

			SetCookie("likesls[" . $data['review_id'] . "]",time(),time()+3600*24*30*12,"/");
			$this->model_extension_module_prostore->setLikes4review($data);
			$json['success']['likes'] = $likes;
		}			

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
// prostore end	
	/**
	 * @param int|bool	$type Mark purpose. If 1 - first 10 reviews will be directly outputed on page (without AJAX).For SEO purposes.
	 * 										If false - usual behaviour
	 */

	public function review($type = false) // prostore
			 {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}


		// prostore
		if ($type) {
			$page = 1;
		}		
		$data['schema'] = $this->config->get('theme_prostore_schema');
		$data['reviewsStats'] = array();
		if (isset($this->request->get['rating'])) {
			$rating = $this->request->get['rating'];
		}else{
			$rating = 0;
		}
		$pagination_limit = $this->config->get('theme_prostore_product_review') ? 10 : 5;
		// prostore end
			
		$data['reviews'] = array();

		
		// prostore
		$results = $this->model_catalog_review->getReviewsByProductIdLs($this->request->get['product_id'], ($page - 1) * $pagination_limit, $pagination_limit, $rating); //prostore add this
		$review_total = $this->model_catalog_review->getTotalReviewsByProductIdLs($this->request->get['product_id'],$rating);
		// prostore end
			
		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => $result['rating'] > 0 ? number_format($result['rating'] , 1) : 0,
				
				// prostore
				'review_id'     => (int)$result['review_id'], 
				'count_good'     => (int)$result['count_good'], 
				'count_bad'     => (int)$result['count_bad'], 
				'text_plus'     => nl2br($result['text_plus']), 
				'text_minus'     => nl2br($result['text_minus']), 
				'text_admin_answer'     => nl2br($result['text_admin_answer']), 
				'answer_date_added' => $this->prostore->helper->rus_date(strtotime($result['answer_date_added'])),
				'date_added_schema' => date('Y-m-d', strtotime($result['date_added'])), 
				// prostore
				'date_added' => $this->prostore->helper->rus_date(strtotime($result['date_added'])),// prostore
			
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		
$pagination->limit = $pagination_limit;//prostore
			
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		
$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $pagination_limit) + 1 : 0, ((($page - 1) * $pagination_limit) > ($review_total - $pagination_limit)) ? $review_total : ((($page - 1) * $pagination_limit) + $pagination_limit), $review_total, ceil($review_total / $pagination_limit));//prostore
			

		
		//prostore
			if ($type) {
				return $this->load->view('product/review', $data);
			}else{
			    $this->load->language('extension/theme/prostore');
				$this->response->setOutput($this->load->view('product/review', $data));
			}			
		// prostore end
			
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
					$json['error'] = $this->language->get('error_name');
				}

				if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
					$json['error'] = $this->language->get('error_text');
				}
			
				if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
					$json['error'] = $this->language->get('error_rating');
				}

				// Captcha
				if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
					$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

					if ($captcha) {
						$json['error'] = $captcha;
					}
				}

				if (!isset($json['error'])) {
					$this->load->model('catalog/review');

					$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

					$json['success'] = $this->language->get('text_success');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_product');
		} 

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


// prostore

	public function custtabload() {
		$this->load->model('extension/module/prostore');

		$customTabs = $this->model_extension_module_prostore->getFields4Product($this->request->get['product_ids']);
		$data['customTab'] = $customTabs["popup"][$this->request->get['tab']];

		$this->response->setOutput($this->load->view('product/custtab_popup',$data));
	}

	public function autocomplete() {
		$this->load->language('extension/theme/prostore');
		
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
//			$this->load->model('catalog/option');
			$this->load->model('tool/image');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 6;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_tag'   => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);


			$href_search = '';
			$show_all = '';

			if (count($results) > 5) { 
				$results = array_slice($results, 0, 5);
				$href_search = str_replace('&amp;', '&', $this->url->link('product/search', 'search=' . $filter_name));
				$show_all = $this->language->get('text_prostore_show_all_products');				
			}

			$this->load->model('extension/module/prostore');

			if (isset($this->request->get['filter_name'])) {
				$filter_data_cat = $filter_data;
				if (!$filter_name) {
					$filter_data_cat['limit'] = 1;
				}else{
					$filter_data_cat['limit'] = 3;
				}
				$categoryResults = $this->model_extension_module_prostore->getCategoriesByName($filter_data_cat);
				foreach ($categoryResults as $categoryResult) {
					$json[] = array(
						'category_id'          => $categoryResult['category_id'],
						'name'                => strip_tags(html_entity_decode($categoryResult['name'], ENT_QUOTES, 'UTF-8')),
						'href' 				  => str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $categoryResult['category_id'])),
						'href_search' 		 	  => $href_search,
						'text_reward' 	      => $this->language->get('text_search_category'),
						'show_all' 		 	  => $show_all
					);
				}				
			}

			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();
			$data['language_id'] = $this->config->get('config_language_id');

			$productsLabels = $this->prostore->labels->getLabels4Products($results);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));// prostore
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));// prostore
				}
				
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = '';
				}

				if ((float)$result['special'] && ($this->customer->isLogged() || !$this->config->get('config_customer_price'))) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = '';
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = round($result['rating'], 1);
				} else {
					$rating = false;
				}

				$wishCompareData = $this->prostore->helper->getDataWishCompare($result['product_id']);// prostore
				
				$extraImages = array();
				
					if ($this->config->get('theme_prostore_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
						}
					}

					extract($productsLabels[$result['product_id']]);

					if(isset($sales) && $sales){
						$sales =$data['labelsinfo']['sale']['name'][$this->config->get('config_language_id')];				
					}

					if ($result['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
						$buy_btn = $result['stock_status'];
					} else {
						$buy_btn = '';
					}

					$cartProductInfo = $this->prostore->helper->getCartInfo4Product($result);
					
					
					if ($result['quantity'] <= 0) {
						$stock = $result['stock_status'];
					} elseif ($this->config->get('config_stock_display')) {
						$stock = $this->language->get('text_instock') . ': ' . $result['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity');
					} else {
						$stock = $this->language->get('text_instock');
					}		
					if ($this->config->get('theme_prostore_manufacturer') == 1) {
						$manufacturer = $this->language->get('text_prostore_model') . ' ' . $result['model'];
					} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
						$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $result['manufacturer'];
					} else {
						$manufacturer = false;
					}


				$json[] = array(
					'product_id'          => $result['product_id'],
					'name'                => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'               => $result['model'],
					'image'               => $image,
					'price'               => $price,
					'special'             => $special,
					'manufacturer'        => $manufacturer,
					'quantity'            => $result['quantity'],
					'stock'       		  => $stock,
					'images'      		  => $extraImages,	
					'isnewest'      	  => $isnewest,
					'sales'      		  => $sales,
					'discount'     	  	  => $discount,
					'catch'       		  => $catch,
					'nocatch'     	 	  => $nocatch,
					'popular'	 		  => $popular,
					'hit'	 	 		  => $hit,
					'isincart'	          => $cartProductInfo['isincart'],// prostore
					'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],// prostore
					'wish_compare_data'   => $wishCompareData,// prostore
					'buy_btn'	 		  => $buy_btn,
					'reward'     		  => $result['reward'],
					'minimum'             => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'              => $rating,
					'href' 				  => str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $result['product_id'])),
					'href_search' 		  => $href_search,
					'show_all' 		 	  => $show_all,
					'button_to_cart' 	  => $this->language->get('text_prostore_in_cart'),
					'button_cart' 	      => $this->language->get('button_cart'),
					'text_reward' 	      => $this->language->get('text_reward'),
					'cart_link' 		  => $this->url->link('checkout/cart')
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
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
	public function breadlistcr() {

		$this->load->model('catalog/category');
		$category_id = $this->request->get['cat_id'];
		$data['breadLists'] = array();
		$categories = $this->model_catalog_category->getCategories($category_id);
		foreach($categories as $category){
			$data['breadLists'][] = array(
				'name'		=> $category['name'],
				'href'       => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}
		$this->response->setOutput($this->load->view('product/bread_popup',$data));
	}
	public function descriptionExtra($description) {
		$data['fulldesc'] = $description;
		$data['shortdesc'] = '';
		$tag = html_entity_decode($this->config->get('theme_prostore_product_short_tag'), ENT_QUOTES, 'UTF-8');
	    if ($tag) {
	      $temp = explode($tag,$description);
	    }
		if(isset($temp[0])){
			$data['shortdesc'] = $temp[0];
			$data['fulldesc'] = str_replace($data['shortdesc'].$tag, "", $description);
		}
		return $data;
	}
	public function analystdataorder() {
		$json = array();
		
		if (isset($this->request->server['SERVER_NAME']) 
			&& strpos(HTTP_SERVER,$this->request->server['SERVER_NAME']) !== False 
			&& isset($this->request->post['order_id']) 
			&& $this->request->post['order_id'] == $this->session->data['last_order_id'])
		{

			$this->load->model('catalog/product');
			$this->load->model('catalog/category');
			$this->load->model('extension/module/prostore');
			$this->load->model('checkout/order');

			$json['transaction_id'] = 0;

			$order_id = (int)$this->request->post['order_id'];
			$products = $this->model_extension_module_prostore->getOrderProducts($order_id);
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$json['value'] = $order_info['total'] ?? '0';
			$json['transaction_id'] = $order_id;


			$cartSubTotal = $this->cart->getSubTotal();

			foreach ($products as $product) {

				$product_categories = $this->model_catalog_product->getCategories($product['product_id']);
				if($product_categories){
					$category_info = $this->model_catalog_category->getCategory($product_categories[0]['category_id']);
					$category_name = $category_info['name'] ?? "";
				}else{
					$category_name = "";
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				$json['items'][] = array(
					'id' => $product['product_id'],
					'name'       => strip_tags(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
					'price' => $product['price'],
					'quantity' => $product['quantity'],
					'category'       => strip_tags(html_entity_decode($category_name, ENT_QUOTES, 'UTF-8')),
					'brand' => strip_tags(html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8'))
				);		
			}
		}else {
			$json['error']['warning'] = 'Error permission';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function analystdata() {
		$json = array();
	
		if (isset($this->request->server['SERVER_NAME']) && strpos(HTTP_SERVER,$this->request->server['SERVER_NAME']) !== False) {

			if (isset($this->request->post['product_id'])) {
				$product_id = $this->request->post['product_id'];
			} else {
				$product_id = 0;
			}

			if (isset($this->request->post['qty'])) {
				$quantity = $this->request->post['qty'];
			} else {
				$quantity = 1;
			}



			$this->load->model('catalog/product');
			$this->load->model('catalog/category');

			$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$price = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
					}

			$product_categories = $this->model_catalog_product->getCategories($product_id);
			if($product_categories){
				$category_info = $this->model_catalog_category->getCategory($product_categories[0]['category_id']);
				$category_name = $category_info['name'] ?? "";
			}else{
				$category_name = "";
			}
					$json['currency'] = $this->session->data['currency'];
					$json['items'][] = array(
						'id' => $product_info['product_id'],
						'name'       => strip_tags(html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')),
						'price' => $price,
						'quantity' => $quantity,
						'category'       => strip_tags(html_entity_decode($category_name, ENT_QUOTES, 'UTF-8')),
						'brand' => strip_tags(html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8'))
					);
		}else {
			$json['error']['warning'] = 'Error permission';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function doOptionColumns($options){
		$optionColumns = array();
		$column = $this->config->get('theme_prostore_product_opt_select');
		if (!$column) {
			$column = 2;
		}


//		if ($column == 2) {
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

//		}else{
//			$optionColumns[] = $options;
//		}

		return $optionColumns;
	}
	
	public function correctForm($number, $suffix) {
		$keys = array(2, 0, 1, 1, 1, 2);
		$mod = $number % 100;
		$suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
		return $suffix[$suffix_key];
	}
// prostore end
			
	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
