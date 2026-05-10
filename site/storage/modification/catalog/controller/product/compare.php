<?php
class ControllerProductCompare extends Controller {
	public function index() {
		$this->load->language('product/compare');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (!isset($this->session->data['compare'])) {
			$this->session->data['compare'] = array();
		}

		if (isset($this->request->get['remove'])) {
			$key = array_search($this->request->get['remove'], $this->session->data['compare']);

			if ($key !== false) {
				unset($this->session->data['compare'][$key]);

				$this->session->data['success'] = $this->language->get('text_remove');
			}

			$this->response->redirect($this->url->link('product/compare'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/compare')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['review_status'] = $this->config->get('config_review_status');

		// prostore
		$compare_total = count($this->session->data['compare']);
		$data['compare_total'] = $compare_total;
		$this->load->language('extension/theme/prostore');
		if ($compare_total) {
			$data['model_num'] = $compare_total .' '. $this->prostore->helper->correctForm(array($compare_total,'compare'));
		} else {
			$data['model_num'] = false;
		}
		$this->document->addScript('catalog/view/javascript/prostore/plugins/jquery.matchHeight.min.js');
		// prostore end
			

		$data['products'] = array();

		$data['attribute_groups'] = array();

		foreach ($this->session->data['compare'] as $key => $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_height'));
				} else {
					$image = false;
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($product_info['quantity'] <= 0) {
					$availability = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$availability = $product_info['quantity'];
				} else {
					$availability = $this->language->get('text_instock');
				}

				$attribute_data = array();

				$attribute_groups = $this->model_catalog_product->getProductAttributes($product_id);

				foreach ($attribute_groups as $attribute_group) {
					foreach ($attribute_group['attribute'] as $attribute) {
						$attribute_data[$attribute['attribute_id']] = $attribute['text'];
					}
				}


				// prostore
				$wishCompareData = $this->prostore->helper->getDataWishCompare($product_info['product_id']);

				if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$buy_btn = $product_info['stock_status'];
				} else {
					$buy_btn = '';
				}
				$productLabels = $this->prostore->labels->getLabels4Product($product_info);

				extract($productLabels);
				// prostore end
			
				$data['products'][$product_id] = array(
					'product_id'   => $product_info['product_id'],
					'name'         => $product_info['name'],
					'thumb'        => $image,
					'price'        => $price,
					'special'      => $special,
					'description'  => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 200) . '..',
					'model'        => $product_info['model'],
					'manufacturer' => $product_info['manufacturer'],
					'availability' => $availability,

					// prostore
					'buy_btn'	   => $buy_btn,
					'discount'       => $discount,
					'wish_compare_data' => $wishCompareData,
					// prostore
			
					'minimum'      => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
					'rating'       => round($product_info['rating'], 1),
					'reviews'      => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
					'weight'       => $this->weight->format($product_info['weight'], $product_info['weight_class_id']),
					'length'       => $this->length->format($product_info['length'], $product_info['length_class_id']),
					'width'        => $this->length->format($product_info['width'], $product_info['length_class_id']),
					'height'       => $this->length->format($product_info['height'], $product_info['length_class_id']),
					'attribute'    => $attribute_data,
					'href'         => $this->url->link('product/product', 'product_id=' . $product_id),
					'remove'       => $this->url->link('product/compare', 'remove=' . $product_id)
				);

				foreach ($attribute_groups as $attribute_group) {
					$data['attribute_groups'][$attribute_group['attribute_group_id']]['name'] = $attribute_group['name'];

					foreach ($attribute_group['attribute'] as $attribute) {
						$data['attribute_groups'][$attribute_group['attribute_group_id']]['attribute'][$attribute['attribute_id']]['name'] = $attribute['name'];
					}
				}
			} else {
				unset($this->session->data['compare'][$key]);
			}
		}


$data['diff_keys'] = $this->getDiff4Products($data); //prostore
			
		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/compare', $data));
	}


//prostore start
	public function getDiff4Products($data) {
		$diffKeys = array();
		if (isset($data['products']) && count($data['products']) > 1) {
			$diffKeys = $this->getDiff($data['products']);

			$tempAttr = array();
			foreach ($data['products'] as $productId => $productData) {
				$tempAttr[$productId] = $productData['attribute'];
			}
			$diffKeys['attribute'] = $this->getDiff($tempAttr);
	
			$diffKeys['dimensions'] = 0;
			if ($diffKeys['length'] || $diffKeys['width'] || $diffKeys['height']) {
				$diffKeys['dimensions'] = 1;
			}
		}

		return $diffKeys;
	}

/**
 * Формируем массив ключей содержащих количество уникальных значений этого ключа у товаров.
 * 0 - нет отличий. 1 - есть отличия.
 */

	public function getDiff($data) {
		$rowsMassive = array();
		$productCounts = count($data);
		foreach ($data as $productId => $productData) {
			foreach ($productData as $key => $value) {
				if ( is_array($value) ) {
					continue;
				}
				$rowsMassive[$key][] = $value;
			}
		}
		foreach ($rowsMassive as $key => $value) {
			$isDiff = 0;
			if ( $productCounts != count($value) || count(array_unique($value)) > 1) {
				$isDiff = 1;
			}
			$rowsMassive[$key] = $isDiff;
		}

		return $rowsMassive;
	}

	public function remove() {
		$this->load->language('product/compare');
		$this->load->language('extension/theme/prostore');

		$json = array();

		if (!isset($this->session->data['compare'])) {
			$this->session->data['compare'] = array();
		}

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (in_array($product_id, $this->session->data['compare'])) {

				$key = array_search($product_id, $this->session->data['compare']); 
				unset($this->session->data['compare'][$key]);
				
			}

			$json['success'] = sprintf($this->language->get('text_prostore_success_remove_comp'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('product/compare'));
			$json['button_text'] = sprintf($this->language->get('text_prostore_to_comp_full') , $this->prostore->helper->compare_text((isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)));

			$json['total'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
		
		
//prostore stop
			
	public function add() {

$this->load->language('extension/theme/prostore'); //prostore
			
		$this->load->language('product/compare');

		$json = array();

		if (!isset($this->session->data['compare'])) {
			$this->session->data['compare'] = array();
		}

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (!in_array($this->request->post['product_id'], $this->session->data['compare'])) {
				if (count($this->session->data['compare']) >= 20) {
					array_shift($this->session->data['compare']);
				}

				$this->session->data['compare'][] = $this->request->post['product_id'];
			}

			$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('product/compare'));


$json['button_text'] = sprintf($this->language->get('text_prostore_in_comp_full') , $this->prostore->helper->compare_text((isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)));
			
			$json['total'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
