<?php
class ControllerAccountWishList extends Controller {
	public function index() {

		$this->load->language('extension/theme/prostore'); // prostore
			
		
		if (!$this->customer->isLogged() && !$this->config->get('theme_prostore_wishlist')) {
			
			$this->session->data['redirect'] = $this->url->link('account/wishlist', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/wishlist');

		$this->load->model('account/wishlist');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['remove'])) {
			// Remove Wishlist
			$this->model_account_wishlist->deleteWishlistLb($this->request->get['remove']);

			$this->session->data['success'] = $this->language->get('text_remove');

			$this->response->redirect($this->url->link('account/wishlist'));
		}

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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}


		$data['islogged'] = $this->customer->isLogged();//prostore
		$data['button_compare'] = $this->language->get('button_compare');//prostore
			
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
			

		$results = $this->model_account_wishlist->getWishlistLb();

		foreach ($results as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
				} else {
					
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
			
				}

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $product_info['quantity'];
				} else {
					$stock = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
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
			

// prostore
				if ($this->config->get('config_review_status')) {
					$rating = $product_info['rating'] > 0 ? number_format($product_info['rating'], 1) : 0;
				} else {
					$rating = false;
				}
// prostore
			
				$data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'stock'      => $stock,
					'price'      => $price,

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
			

'rating'      => $rating,// prostore
			
					'special'    => $special,
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
				);
			} else {
				$this->model_account_wishlist->deleteWishlistLb($result['product_id']);
			}
		}

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}


	public function remove() {
		$this->load->language('account/wishlist');
		$this->load->language('extension/theme/prostore'); // prostore
            
		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if ($this->customer->isLogged() || $this->config->get('theme_prostore_wishlist')) {
            
				// Edit customers cart
				$this->load->model('account/wishlist');
				$total = $this->model_account_wishlist->deleteWishlistLb($product_id);
				$totalForThisProduct =  $this->model_account_wishlist->getTotalWishlistByProduct($product_id);
				$json['success'] = sprintf($this->language->get('text_prostore_success_remove_wish'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
				$json['total'] = sprintf($this->language->get('text_wishlist'), $total);
				$json['button_text'] = sprintf($this->language->get('text_prostore_to_wishlist2') , $this->prostore->helper->wishlist_text($totalForThisProduct) );

			} else {
				$json['error'] = '1';
				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
		
	public function add() {

		$this->load->language('extension/theme/prostore'); // prostore
			
		$this->load->language('account/wishlist');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			
			if ($this->customer->isLogged() || $this->config->get('theme_prostore_wishlist')) {
			
				// Edit customers cart
				$this->load->model('account/wishlist');

				$this->model_account_wishlist->addWishlistLb($this->request->post['product_id']);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));


				$totalForThisProduct =  $this->model_account_wishlist->getTotalWishlistByProduct($product_id);
				$json['button_text'] = sprintf($this->language->get('text_prostore_in_wishlist2') , $this->prostore->helper->wishlist_text($totalForThisProduct) );
		
				$json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlistLb());
			} else {
				
				$json['button_text'] = '';
				$json['error'] = '1';
		
				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
