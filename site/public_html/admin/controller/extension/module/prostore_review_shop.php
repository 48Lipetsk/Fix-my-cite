<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerExtensionModuleProstoreReviewShop extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/prostore_review_shop');
		$this->load->model('localisation/language');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('prostore_review_shop', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['review_name'])) {
			$data['error_review_name'] = $this->error['review_name'];
		} else {
			$data['error_review_name'] = '';
		}	
		
		if (isset($this->error['review_title'])) {
			$data['error_review_title'] = $this->error['review_title'];
		} else {
			$data['error_review_title'] = '';
		}	
		
		if (isset($this->error['review_limit'])) {
			$data['error_reviews_limit'] = $this->error['review_limit'];
		} else {
			$data['error_reviews_limit'] = '';
		}			

		$data['breadcrumbs'] = array(); 

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore_review_shop', 'user_token=' . $this->session->data['user_token'], true)
		);
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}	

		$data['languages'] = $this->model_localisation_language->getLanguages();		
		
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		} 	
		
		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info) && isset($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 3;
		} 

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info) && isset($module_info['title'])) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = array();
		} 

		if (isset($this->request->post['text_info'])) {
			$data['text_info'] = $this->request->post['text_info'];
		} elseif (!empty($module_info) && isset($module_info['title'])) {
			$data['text_info'] = $module_info['text_info'];
		} else {
			foreach ($this->model_localisation_language->getLanguages() as $language) {
				$data['text_info'][$language['language_id']] = $this->language->get('text_info');
			}			
		} 		
				
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/prostore_review_shop', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/prostore_review_shop', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 0;
		} 

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostore_review_shop', $data));

	}

	public function show() {
		$this->load->language('extension/module/prostore_review_shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostore_review_shop');

		$this->getList();
	}

	public function add() {
		$this->load->language('extension/module/prostore_review_shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostore_review_shop');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_theme_prostore_review_shop->addReview($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_store'])) {
				$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_author'])) {
				$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/module/prostore_review_shop');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostore_review_shop');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_theme_prostore_review_shop->editReview($this->request->get['review_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_store'])) {
				$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_author'])) {
				$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		
		$this->load->model('extension/theme/prostore_review_shop');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $review_id) {
				$this->model_extension_theme_prostore_review_shop->deleteReview($review_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_store'])) {
				$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_author'])) {
				$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {

		$this->load->model('setting/store');


		if (isset($this->request->get['filter_store'])) {
			$filter_store = $this->request->get['filter_store'];
		} else {
			$filter_store = '';
		}

		if (isset($this->request->get['filter_author'])) {
			$filter_author = $this->request->get['filter_author'];
		} else {
			$filter_author = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_store'])) {
			$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/module/prostore_review_shop/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/prostore_review_shop/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('extension/module/prostore_review_shop/enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('extension/module/prostore_review_shop/disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['reviews'] = array();

		$filter_data = array(
			'filter_store'      => $filter_store,
			'filter_author'     => $filter_author,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$review_total = $this->model_extension_theme_prostore_review_shop->getTotalReviews($filter_data);

		$results = $this->model_extension_theme_prostore_review_shop->getReviews($filter_data);

		foreach ($results as $result) { 
			$data['reviews'][] = array(
				'review_id'  => $result['review_id'],
				'store'       => $result['store_id'],
				'author'     => $result['author'],
				'rating'     => $result['rating'],
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'       => $this->url->link('extension/module/prostore_review_shop/edit', 'user_token=' . $this->session->data['user_token'] . '&review_id=' . $result['review_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_store'])) {
			$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_product'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_author'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . '&sort=r.author' . $url, true);
		$data['sort_rating'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . '&sort=r.rating' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . '&sort=r.status' . $url, true);
		$data['sort_date_added'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_store'])) {
			$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['stores'] = array();

		$data['stores'][0] = array(
			'store_id' => '0',
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
			'url'      => $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG,
			'edit'     => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true)
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][$result['store_id']] = array(
				'store_id' => strval($result['store_id']),
				'name'     => $result['name'],
				'url'      => $result['url'],
				'edit'     => $this->url->link('setting/store/edit', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $result['store_id'], true)
			);
		}		

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_total - $this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_total, ceil($review_total / $this->config->get('config_limit_admin')));

		$data['filter_store'] = $filter_store;
		$data['filter_author'] = $filter_author;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostore_review_shop_list', $data));
	}

	protected function getForm() {

		$this->load->model('setting/store');

		$data['text_form'] = !isset($this->request->get['review_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['product'])) {
			$data['error_product'] = $this->error['product'];
		} else {
			$data['error_product'] = '';
		}

		if (isset($this->error['author'])) {
			$data['error_author'] = $this->error['author'];
		} else {
			$data['error_author'] = '';
		}

		if (isset($this->error['text'])) {
			$data['error_text'] = $this->error['text'];
		} else {
			$data['error_text'] = '';
		}

		if (isset($this->error['rating_services'])) {
			$data['error_rating_services'] = $this->error['rating_services'];
		} else {
			$data['error_rating_services'] = '';
		}
		if (isset($this->error['rating_delivery'])) {
			$data['error_rating_delivery'] = $this->error['rating_delivery'];
		} else {
			$data['error_rating_delivery'] = '';
		}
		if (isset($this->error['rating_price'])) {
			$data['error_rating_price'] = $this->error['rating_price'];
		} else {
			$data['error_rating_price'] = '';
		}
		if (isset($this->error['rating_quality'])) {
			$data['error_rating_quality'] = $this->error['rating_quality'];
		} else {
			$data['error_rating_quality'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_store'])) {
			$url .= '&filter_store=' . urlencode(html_entity_decode($this->request->get['filter_store'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['review_id'])) {
			$data['action'] = $this->url->link('extension/module/prostore_review_shop/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/prostore_review_shop/edit', 'user_token=' . $this->session->data['user_token'] . '&review_id=' . $this->request->get['review_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_info = $this->model_extension_theme_prostore_review_shop->getReview($this->request->get['review_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['stores'] = array();

		$data['stores'][0] = array(
			'store_id' => '0',
			'name'     => $this->config->get('config_name') . $this->language->get('text_default')
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][$result['store_id']] = array(
				'store_id' => strval($result['store_id']),
				'name'     => $result['name']
			);
		}		

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($review_info)) {
			$data['store_id'] = $review_info['store_id'];
		} else {
			$data['store_id'] = '';
		}


		if (isset($this->request->post['author'])) {
			$data['author'] = $this->request->post['author'];
		} elseif (!empty($review_info)) {
			$data['author'] = $review_info['author'];
		} else {
			$data['author'] = '';
		}

		if (isset($this->request->post['text'])) {
			$data['text'] = $this->request->post['text'];
		} elseif (!empty($review_info)) {
			$data['text'] = $review_info['text'];
		} else {
			$data['text'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($review_info)) {
			$data['email'] = $review_info['email'];
		} else {
			$data['email'] = '';
		}

		$data['ratings_range'] = array(1,2,3,4,5);

	    if (isset($this->request->post['r1'])) {
	      	$data['rating_by_item']['r1'] = $this->request->post['r1'];
	    } elseif (!empty($review_info)) {
			$data['rating_by_item']['r1']  = $review_info['r1'];
	    } else {
			$data['rating_by_item']['r1']  = '';
	    }
	
	    if (isset($this->request->post['r2'])) {
			$data['rating_by_item']['r2'] = $this->request->post['r2'];
	 	} elseif (!empty($review_info)) {
		    $data['rating_by_item']['r2']  = $review_info['r2'];
	    } else {
		    $data['rating_by_item']['r2']  = '';
	    }

	    if (isset($this->request->post['r3'])) {
			$data['rating_by_item']['r3'] = $this->request->post['r3'];
	 	} elseif (!empty($review_info)) {
		    $data['rating_by_item']['r3']  = $review_info['r3'];
	    } else {
		    $data['rating_by_item']['r3']  = '';
	    }
		
	    if (isset($this->request->post['r4'])) {
			$data['rating_by_item']['r4'] = $this->request->post['r4'];
	 	} elseif (!empty($review_info)) {
		    $data['rating_by_item']['r4']  = $review_info['r4'];
	    } else {
		    $data['rating_by_item']['r4']  = '';
	    }	
		
	    if (isset($this->request->post['r5'])) {
			$data['rating_by_item']['r5'] = $this->request->post['r5'];
	 	} elseif (!empty($review_info)) {
		    $data['rating_by_item']['r5']  = $review_info['r5'];
	    } else {
		    $data['rating_by_item']['r5']  = '';
	    }		

	    if (isset($this->request->post['text_admin_answer'])) {
			$data['text_admin_answer'] = $this->request->post['text_admin_answer'];
		} elseif (!empty($review_info)) {
			$data['text_admin_answer'] = $review_info['text_admin_answer'];
		} else {
			$data['text_admin_answer'] = '';
		}		
        

		if (isset($this->request->post['date_added'])) {
			$data['date_added'] = $this->request->post['date_added'];
		} elseif (!empty($review_info)) {
			$data['date_added'] = ($review_info['date_added'] != '0000-00-00 00:00' ? $review_info['date_added'] : '');
		} else {
			$data['date_added'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($review_info)) {
			$data['status'] = $review_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['active_ratings_info'] = $this->model_extension_theme_prostore_review_shop->getActiveReviewsR();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostore_review_shop_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_review_shop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 64)) {
			$this->error['author'] = $this->language->get('error_author');
		}

		if (utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->language->get('error_text');
		}

		$activeReviewsR = $this->model_extension_theme_prostore_review_shop->getActiveReviewsR();
		if ($activeReviewsR['active_r_total']) {
			foreach ($activeReviewsR['active_r'] as $r_id => $r_name) {
				if (empty($this->request->post[$r_id]) || $this->request->post[$r_id] < 0 || $this->request->post[$r_id] > 5) {
					$json['error'] = $this->language->get('error_rating');
				}
			}
		}			

		return !$this->error;
	}
	
		public function enable() {
        $this->load->language('extension/module/prostore_review_shop');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/theme/prostore_review_shop');
        if (isset($this->request->post['selected']) && $this->validateEnable()) {
            foreach ($this->request->post['selected'] as $review_id) {
                $data = array();
                $result = $this->model_extension_theme_prostore_review_shop->getReview($review_id);
                foreach ($result as $key => $value) {
                    $data[$key] = $value;
                }
                $data['status'] = 1;
                $this->model_extension_theme_prostore_review_shop->editReview($review_id, $data);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            $this->response->redirect($this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        $this->getList();
    }
    public function disable() {
        $this->load->language('extension/module/prostore_review_shop');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/theme/prostore_review_shop');
        if (isset($this->request->post['selected']) && $this->validateDisable()) {
            foreach ($this->request->post['selected'] as $review_id) {
                $data = array();
                $result = $this->model_extension_theme_prostore_review_shop->getReview($review_id);
                foreach ($result as $key => $value) {
                    $data[$key] = $value;
                }
                $data['status'] = 0;
                $this->model_extension_theme_prostore_review_shop->editReview($review_id, $data);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            $this->response->redirect($this->url->link('extension/module/prostore_review_shop/show', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        $this->getList();
    }
	
	protected function validateEnable() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_review_shop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	protected function validateDisable() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_review_shop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_review_shop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_review_shop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['name']) {
			$this->error['review_name'] = $this->language->get('error_name');
		}
		
		if (!$this->request->post['limit']) {
			$this->error['review_limit'] = $this->language->get('error_limit');
		}

		foreach ($this->model_localisation_language->getLanguages() as $language) {
			if (!$this->request->post['title'][$language['language_id']]) {
				$this->error['review_title'][$language['language_id']] = $this->language->get('error_title');
			}
		}


		return !$this->error;
	}	

	public function install() {

	}

	public function uninstall() {

	}


}
