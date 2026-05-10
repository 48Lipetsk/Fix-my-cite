<?php
class ControllerExtensionModuleProstoreNews extends Controller {
	public function index($settings) {
		$this->load->language('extension/module/prostore_news');
		
		$this->load->model('tool/image');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_all_news'] = $this->language->get('text_all_news');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
		$data['cart_link'] = $this->url->link('checkout/cart');

		$this->load->model('extension/module/prostorenews');
		
		$data['news_href'] = $this->url->link('extension/module/prostore_news/getnewslist');
		$limit = isset($settings['limit']) ? $settings['limit'] : 3;
		$page = 1;
		$order = 'DESC';
		
			$filter_data = array(
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);


		$data['newss'] = array();

		foreach ($this->model_extension_module_prostorenews->getNewss($filter_data) as $result) {
			if ($result['bottom']) {
				$width = $result['width'];
				$height = $result['height'];
			} else {
				$width = round($result['width']/2);
				$height =round($result['height']/2);
			}
			
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $width, $height);
			} else {
				$image = false;
			}
					
			$data['newss'][] = array(
				'title' => $result['title'],
				'badges' => $result['bottom'],
				'background' => $result['background'],
				'thumb'       => $image,
				'width' => $width,
				'height' => $height,
				'date_added' => $this->rus_date("j F, Y ", strtotime($result['date_added'])),
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'href'  => $this->url->link('extension/module/prostore_news/getnews', 'news_id=' . $result['news_id'])
			);
		}
		if(isset($settings['layout']) && strpos($settings['layout'],'column_') !== false){
			return $this->load->view('extension/module/prostore_news_column', $data);
		}else{
			return $this->load->view('extension/module/prostore_news_list', $data);
		}

		
	}
	public function getnews() {
		$this->load->language('extension/module/prostore_news');

		$this->load->model('extension/module/prostorenews');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore_news/getnewslist')
		);

		$data['text_related'] = $this->language->get('text_related');
		
		if (isset($this->request->get['news_id'])) {
			$news_id = (int)$this->request->get['news_id'];
		} else {
			$news_id = 0;
		}

		$news_info = $this->model_extension_module_prostorenews->getNews($news_id);
	
		// prostore
		$data['schema'] = $this->config->get('theme_prostore_schema');
		// prostore end
		
		if ($news_info) {

			if ($news_info['meta_title']) {
				$this->document->setTitle($news_info['meta_title']);
			} else {
				$this->document->setTitle($news_info['title']);
			}

			$this->document->setDescription($news_info['meta_description']);
			$this->document->setKeywords($news_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $news_info['title'],
				'href' => $this->url->link('extension/module/prostore_news/getnews', 'news_id=' .  $news_id)
			);

			if ($news_info['meta_h1']) {
				$data['heading_title'] = $news_info['meta_h1'];
			} else {
				$data['heading_title'] = $news_info['title'];
			}

			if ($news_info['image']) {
				$image =$this->model_tool_image->resize($news_info['image'], $news_info['width'], $news_info['height']);
				if ($this->config->get('theme_prostore_og')) {
					$this->document->setOgImage($image);
				}
			} else {
				$image = false;
			}

			$data['image'] = $image;

			$data['badges'] = $news_info['bottom'];
			$data['background'] = $news_info['background'];
			$data['width'] = $news_info['width'];
			$data['height'] = $news_info['height'];
			$data['subtitle'] = $news_info['subtitle'];
			$data['soc_share_news'] = $this->config->get('theme_prostore_soc_share_news');

			$data['products'] = array();
			
	// prostore
		$this->load->language('extension/theme/prostore');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['mobile_view'] = $this->config->get('theme_prostore_mobile_view');
		$data['cart_link'] = $this->url->link('checkout/cart');
		$data['language_id'] = $this->config->get('config_language_id');
		// labels
			$this->load->model('extension/module/prostore');

			$data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();
	
		// labels	
		// prostore end			
		
			$results = $this->model_extension_module_prostorenews->getProductRelated($this->request->get['news_id']);

			foreach ($results as $result_id) {
				$result = $this->model_catalog_product->getProduct($result_id);

				if ($this->config->get('theme_prostore_image_related_resize')) {
					if ($result['image']) {
						$image = $this->model_tool_image->prostore_resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					} else {
						$image = $this->model_tool_image->prostore_resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					}
				} else {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					}
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
				
				// prostore
				
					$extraImages = array();		
					if ($this->config->get('theme_prostore_images_status')) {
						$images = array_slice($this->model_catalog_product->getProductImages($result['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
							$extraImages[] = $this->config->get('theme_prostore_image_product_resize') ? $this->model_tool_image->prostore_resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')) : $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						}
					}

					$productLabels = $this->prostore->labels->getLabels4Product($result);

					extract($productLabels);				

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
											
					// prostore end
					
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'isnewest'    => $isnewest,// prostore
					'sales'       => $sales,// prostore
					'discount'    => $discount,// prostore
					'catch'       => $catch,// prostore
					'popular'     => $popular,// prostore
					'hit'    	  => $hit,// prostore
					'isincart'	  => $cartProductInfo['isincart'],// prostore
					'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],// prostore
					'nocatch'     => $nocatch,// prostore
					'manufacturer'=> $manufacturer,// prostore
					'quantity'    => $result['quantity'],// prostore
					'reward'      => $result['reward'],// prostore
					'buy_btn'	  => $buy_btn,// prostore
					'stock'	      => $stock,// prostore
					'images'      => $extraImages,// prostore	
					'wish_compare_data' => $wishCompareData,// prostore	
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['button_continue'] = $this->language->get('button_continue');

			$data['description'] = html_entity_decode($news_info['description'], ENT_QUOTES, 'UTF-8');
			
			$data['schema_description'] = str_replace(array("\r\n", "\r", "\n", "\""),' ', strip_tags(html_entity_decode($news_info['description'], ENT_QUOTES, 'UTF-8')));
			$data['schema_date_added'] = $news_info['date_added'];
			$data['schema_name'] = $this->config->get('config_name');
			$data['schema_link'] = $this->url->link('extension/module/prostore_news/getnews', 'news_id=' .  $news_id);
			
			$data['date_added'] = $this->rus_date("j F, Y ", strtotime($news_info['date_added']));
			
			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('extension/module/prostore_news', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'news_id=' . $news_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
	
		$this->response->setOutput($this->load->view('error/404', $data));

		}		
	}
	public function getnewslist() { 
		$this->load->language('extension/module/prostore_news');
		
		$this->load->model('tool/image');

		if ($this->config->get('theme_prostore_news_meta_title' . $this->config->get('config_language_id'))) {
			$this->document->setTitle($this->config->get('theme_prostore_news_meta_title' . $this->config->get('config_language_id')));
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}
		
		if ($this->config->get('theme_prostore_news_meta_description' . $this->config->get('config_language_id'))) {
			$this->document->setDescription($this->config->get('theme_prostore_news_meta_description' . $this->config->get('config_language_id')));
		}
		
		if ($this->config->get('theme_prostore_news_meta_keyword' . $this->config->get('config_language_id'))) {
			$this->document->setKeywords($this->config->get('theme_prostore_news_meta_keyword' . $this->config->get('config_language_id')));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$this->load->model('extension/module/prostorenews');
		
		if(!$this->model_extension_module_prostorenews->isModuleSet()){
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore_news/getnewslist')
		);
		$data['schema'] = $this->config->get('theme_prostore_schema');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$limit = $this->config->get('theme_prostore_news_limit');
		
		$order = 'DESC';
		
			$filter_data = array(
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

		$data['newss'] = array();
		foreach ($this->model_extension_module_prostorenews->getNewss($filter_data) as $result) {
			if ($result['bottom']) {
				$width = $result['width'];
				$height = $result['height'];
			} else {
				$width = round($result['width']/2);
				$height =round($result['height']/2);
			}
			
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $width, $height);
			} else {
				$image = false;
			}
			
			$data['newss'][] = array(
				'title' => $result['title'],
				'badges' => $result['bottom'],
				'background' => $result['background'],
				'width' => $width,
				'height' => $height,
				'thumb'       => $image,
				'date_added' => $this->rus_date("j F, Y ", strtotime($result['date_added'])),
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 150) . '...',
				'href'  => $this->url->link('extension/module/prostore_news/getnews', 'news_id=' . $result['news_id'])
			);
		}	
		
		$totalNews = $this->model_extension_module_prostorenews->getNewssTotal();
			$pagination = new Pagination();
			$pagination->total = $totalNews;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('extension/module/prostore_news/getnewslist&page={page}');

			$data['pagination'] = $pagination->render();
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

//		return $this->load->view('extension/module/prostore_news_list_main', $data);
		$this->response->setOutput($this->load->view('extension/module/prostore_news_list_main', $data));		
	}
	
	public function rus_date() {
		$this->load->language('extension/module/prostore_news');
			// Перевод
			 $translate = array(
					 'am' => $this->language->get('text_am'),
					 'pm' => $this->language->get('text_pm'),
					 'AM' => $this->language->get('text_AM'),
					 'PM' => $this->language->get('text_PM'),
					 'Monday' => $this->language->get('text_Monday'),
					 'Mon' => $this->language->get('text_Mon'),
					 'Tuesday' => $this->language->get('text_Tuesday'),
					 'Tue' => $this->language->get('text_Tue'),
					 'Wednesday' => $this->language->get('text_Wednesday'),
					 'Wed' => $this->language->get('text_Wed'),
					 'Thursday' => $this->language->get('text_Thursday'),
					 'Thu' => $this->language->get('text_Thu'),
					 'Friday' => $this->language->get('text_Friday'),
					 'Fri' => $this->language->get('text_Fri'),
					 'Saturday' => $this->language->get('text_Saturday'),
					 'Sat' => $this->language->get('text_Sat'),
					 'Sunday' => $this->language->get('text_Sunday'),
					 'Sun' => $this->language->get('text_Sun'),
					 'January' => $this->language->get('text_January'),
					 'Jan' => $this->language->get('text_Jan'),
					 'February' => $this->language->get('text_February'),
					 'Feb' => $this->language->get('text_Feb'),
					 'March' => $this->language->get('text_March'),
					 'Mar' => $this->language->get('text_Mar'),
					 'April' => $this->language->get('text_April'),
					 'Apr' => $this->language->get('text_Apr'),
					 'May' => $this->language->get('text_May'),
					 'June' => $this->language->get('text_June'),
					 'Jun' => $this->language->get('text_Jun'),
					 'July' => $this->language->get('text_July'),
					 'Jul' => $this->language->get('text_Jul'),
					 'August' => $this->language->get('text_August'),
					 'Aug' => $this->language->get('text_Aug'),
					 'September' => $this->language->get('text_September'),
					 'Sep' => $this->language->get('text_Sep'),
					 'October' => $this->language->get('text_October'),
					 'Oct' => $this->language->get('text_Oct'),
					 'November' => $this->language->get('text_November'),
					 'Nov' => $this->language->get('text_Nov'),
					 'December' => $this->language->get('text_December'),
					 'Dec' => $this->language->get('text_Dec'),
					 'st' => $this->language->get('text_st'),
					 'nd' => $this->language->get('text_nd'),
					 'rd' => $this->language->get('text_rd'),
					 'th' => $this->language->get('text_th'),
			 );
			 // если передали дату, то переводим ее
			 if (func_num_args() > 1) {
				$timestamp = func_get_arg(1);
			 return strtr(date(func_get_arg(0), $timestamp), $translate);
			 } else {
			// иначе текущую дату
				return strtr(date(func_get_arg(0)), $translate);
			 }
	}
}
