<?php
class ControllerInformationSitemap extends Controller {

		// prostore
	protected function getCategoriesLb($parent_id, $current_path = '') {
		$info = array();

		$results = $this->model_extension_module_prostorecatblog->getBlogCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

					$SubBlogs = array();
					$filter_data = array(
						'filter_category_id' => $result['category_id'],
						'start'			  => 0,
						'limit'			  => 100000
					);
			$blogs = $this->model_extension_module_prostoreblog->getBlogs($filter_data);

			foreach ($blogs as $blog) {
				$SubBlogs[] = array(
					'title' => $blog['title'],
					'href'  => $this->url->link('extension/module/prostore_blog/getblog', 'blog_id=' . $blog['blog_id'])
				);
			}


				$info[] = array(
					'name' => $result['name'],
					'href'  => $this->url->link('extension/module/prostorecat_blog/getcat', 'lbpath=' . $new_path),
					'subblogs' => $SubBlogs
				);
			

			$this->getCategoriesLb($result['category_id'], $new_path);
		}

		return $info;
	}
		// prostore end	
			
	public function index() {
		$this->load->language('information/sitemap');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap')
		);

		$this->load->model('catalog/category');


		// prostore
		$this->load->language('extension/theme/prostore');
		$data['schema'] = $this->config->get('theme_prostore_schema');
		$data['text_prostore_news'] = $this->language->get('text_prostore_news');
		$data['text_prostore_blogs'] = $this->language->get('text_prostore_blogs');
		// prostore end
			
		$data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'name' => $category_3['name'],
						'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $category_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'])
				);
			}

			$data['categories'][] = array(
				'name'     => $category_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}

		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['history'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['search'] = $this->url->link('product/search');
		$data['contact'] = $this->url->link('information/contact');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			$data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
			);
		}


		// prostore
		$this->load->model('extension/module/prostorenews');
		$data['newss'] = array();
		if($this->model_extension_module_prostorenews->isModuleSet()){
				$filter_data = array(
					'start'			  => 0,
					'limit'			  => 100000
				);

			foreach ($this->model_extension_module_prostorenews->getNewss($filter_data) as $result) {
				$data['newss'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('extension/module/prostore_news/getnews', 'news_id=' . $result['news_id'])
				);
			}
		}

		$this->load->model('extension/module/prostoreblog');
		$this->load->model('extension/module/prostorecatblog');
		if($this->model_extension_module_prostorecatblog->isModuleSet()){
			$data['blogcategories'] = array();
			$data['blogcategories'] = $this->getCategoriesLb(0);
		}			
		// prostore end
			
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/sitemap', $data));
	}
}