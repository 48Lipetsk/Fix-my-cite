<?php
class ControllerCommonContentBottom extends Controller {
	public function index() {
		$this->load->model('design/layout');

		if (isset($this->request->get['route'])) {
			$route = (string)$this->request->get['route'];
		} else {
			$route = 'common/home';
		}

		$layout_id = 0;

		if ($route == 'product/category' && isset($this->request->get['path'])) {
			$this->load->model('catalog/category');

			$path = explode('_', (string)$this->request->get['path']);

			$layout_id = $this->model_catalog_category->getCategoryLayoutId(end($path));
		}

		if ($route == 'product/product' && isset($this->request->get['product_id'])) {
			$this->load->model('catalog/product');

			$layout_id = $this->model_catalog_product->getProductLayoutId($this->request->get['product_id']);
		}


// prostore
		if ($route == 'extension/module/prostore_blog/getblog' && isset($this->request->get['blog_id'])) {
			$this->load->model('extension/module/prostoreblog');

			$layout_id = $this->model_extension_module_prostoreblog->getBlogLayoutId($this->request->get['blog_id']); 
		}
		if ($route == 'extension/module/prostore_news/getnews' && isset($this->request->get['news_id'])) {
			$this->load->model('extension/module/prostorenews');

			$layout_id = $this->model_extension_module_prostorenews->getNewsLayoutId($this->request->get['news_id']); 
		}
		if ($route == 'extension/module/prostorecat_blog/getcat' && isset($this->request->get['lbpath'])) {
			$this->load->model('extension/module/prostorecatblog');
			$lbpath = explode('_', (string)$this->request->get['lbpath']);
			$layout_id = $this->model_extension_module_prostorecatblog->getBlogCategoryLayoutId(end($lbpath));
		}

// prostore	
			
		if ($route == 'information/information' && isset($this->request->get['information_id'])) {
			$this->load->model('catalog/information');

			$layout_id = $this->model_catalog_information->getInformationLayoutId($this->request->get['information_id']);
		}

		if (!$layout_id) {
			$layout_id = $this->model_design_layout->getLayout($route);
		}

		if (!$layout_id) {
			$layout_id = $this->config->get('config_layout_id');
		}

		$this->load->model('setting/module');

		$data['modules'] = array();

		$modules = $this->model_design_layout->getLayoutModules($layout_id, 'content_bottom');

		foreach ($modules as $module) {
			$part = explode('.', $module['code']);

			if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
				$module_data = $this->load->controller('extension/module/' . $part[0]);

				if ($module_data) {
					$data['modules'][] = $module_data;
				}
			}

			if (isset($part[1])) {
				$setting_info = $this->model_setting_module->getModule($part[1]);

				if ($setting_info && $setting_info['status']) {

				$setting_info['layout'] = $module['position'];//prostore
				$setting_info['module_id'] = $part[1];//prostore
			
					$output = $this->load->controller('extension/module/' . $part[0], $setting_info);

					if ($output) {
						$data['modules'][] = $output;
					}
				}
			}
		}

		return $this->load->view('common/content_bottom', $data);
	}
}
