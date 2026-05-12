<?php
class ControllerExtensionModuleProstoreCategory extends Controller {
	public function index($settings) {
		$this->load->language('extension/module/prostore_category');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		foreach ($parts as $part) {
			$data['path_ids'][] = $part;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['categories'] = array();

		if ($this->config->get('developer_theme')) {
			$data['categories'] = $this->cache->get('899.prostore.category.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0') ? : [];
		}

		if (!$data['categories']) {

			$categories = $this->model_catalog_category->getCategories(0);

			foreach ($categories as $category) {
				$children_data = array();

					$children = $this->model_catalog_category->getCategories($category['category_id']);

					foreach ($children as $child) {  // Level 2       
					$children2_data = array();
					$children2 = $this->model_catalog_category->getCategories($child['category_id']);
					foreach ($children2 as $child2) {    // Level 3 
			//Level 4 start
						$children3_data = array();
						$children3 = $this->model_catalog_category->getCategories($child2['category_id']);               
						foreach ($children3 as $child3) { 
							$filter_data = array(
							'filter_category_id'  => $child3['category_id'],
							'filter_sub_category' => true
							);

							$children3_data[$child3['category_id']] = array(
							'category_id' => $child3['category_id'],
							'name'  => $child3['name'],
							'totalitems'  => $this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '0',
							'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child2['category_id'] . '_' . $child3['category_id'])
							);
						}
			//Level 4 end
						$filter_data = array(
						'filter_category_id'  => $child2['category_id'],
						'filter_sub_category' => true
						);
						// Level 3	

						$children2_data[$child2['category_id']] = array(
						'category_id' => $child2['category_id'],
						'name'  => $child2['name'],
						'totalitems'  => $this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '0',
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child2['category_id']),
						'children' => $children3_data
						);
					}         
							
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);
					// Level 2	
			
					$children_data[$child['category_id']] = array(// <--prostore change this
						'category_id' => $child['category_id'],
						'name'  => $child['name'],
						'totalitems'  => $this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '0',
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
						'children' => $children2_data // <--prostore add this
					);

					}

				//}

				$filter_data = array(
					'filter_category_id'  => $category['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'],
					'totalitems'  => $this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '0',
					'children'    => $children_data,
					'thumb'       => $this->config->get('theme_prostore_image_category_resize') ? $this->model_tool_image->prostore_resize(($category['image'] == '' ? 'no_image.png' : $category['image']), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),  $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')) : $this->model_tool_image->resize(($category['image'] == '' ? 'no_image.png' : $category['image']), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),  $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')),
					'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}

			$this->cache->set('899.prostore.category.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0', $data['categories']);

		}

		$data['showcount'] = $this->config->get('config_product_count');
		
		$data['image_category_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width');
		
		$data['image_category_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height');
		
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		$data['title'] = '';
	
		if(isset($settings['title' . $this->config->get('config_language_id')])){ 
			$data['title'] = $settings['title' . $this->config->get('config_language_id')];
		}

		$data['view'] = $settings['view'];
		
			if(isset($settings['layout']) && strpos($settings['layout'],'column_') !== false){
				return $this->load->view('extension/module/prostore_category_column', $data);
			}else{
				return $this->load->view('extension/module/prostore_category', $data);
			}

					
	}
}