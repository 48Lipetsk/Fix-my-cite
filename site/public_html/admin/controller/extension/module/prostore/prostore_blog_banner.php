<?php
class ControllerExtensionModuleProstoreProstoreBlogBanner extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/prostore/prostore_blog_banner');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostoreblogbanner');

		$this->getList();
	}

	public function add() {
		$this->load->language('extension/module/prostore/prostore_blog_banner');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostoreblogbanner');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_theme_prostoreblogbanner->addBanner($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/module/prostore/prostore_blog_banner');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostoreblogbanner');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_theme_prostoreblogbanner->editBanner($this->request->get['banner_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/prostore/prostore_blog_banner');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/theme/prostoreblogbanner');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $banner_id) {
				$this->model_extension_theme_prostoreblogbanner->deleteBanner($banner_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/module/prostore/prostore_blog_banner/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/prostore/prostore_blog_banner/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['banners'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$banner_total = $this->model_extension_theme_prostoreblogbanner->getTotalBanners();

		$results = $this->model_extension_theme_prostoreblogbanner->getBanners($filter_data);

		foreach ($results as $result) {
			$data['banners'][] = array(
				'banner_id' => $result['banner_id'],
				'name'      => $result['name'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link('extension/module/prostore/prostore_blog_banner/edit', 'user_token=' . $this->session->data['user_token'] . '&banner_id=' . $result['banner_id'] . $url, true)
			);
		}

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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $banner_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($banner_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($banner_total - $this->config->get('config_limit_admin'))) ? $banner_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $banner_total, ceil($banner_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostorecatalog/prostore_blog_banner_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['banner_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['banner_image'])) {
			$data['error_banner_image'] = $this->error['banner_image'];
		} else {
			$data['error_banner_image'] = array();
		}

		if (isset($this->error['image_thumb'])) {
			$data['error_image_thumb'] = $this->error['image_thumb'];
		} else {
			$data['error_image_thumb'] = '';
		}

		if (isset($this->error['image_popup'])) {
			$data['error_image_popup'] = $this->error['image_popup'];
		} else {
			$data['error_image_popup'] = '';
		}
		
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['banner_id'])) {
			$data['action'] = $this->url->link('extension/module/prostore/prostore_blog_banner/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/prostore/prostore_blog_banner/edit', 'user_token=' . $this->session->data['user_token'] . '&banner_id=' . $this->request->get['banner_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/module/prostore/prostore_blog_banner', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['banner_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$banner_info = $this->model_extension_theme_prostoreblogbanner->getBanner($this->request->get['banner_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($banner_info)) {
			$data['name'] = $banner_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($banner_info)) {
			$data['status'] = $banner_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['image_thumb_width'])) {
			$data['image_thumb_width'] = $this->request->post['image_thumb_width'];
		} elseif (!empty($banner_info['image_thumb_width'])) {
			$data['image_thumb_width'] = $banner_info['image_thumb_width'];
		} else {
			$data['image_thumb_width'] = 620;
		}
		
		if (isset($this->request->post['image_thumb_height'])) {
			$data['image_thumb_height'] = $this->request->post['image_thumb_height'];
		} elseif (!empty($banner_info['image_thumb_height'])) {
			$data['image_thumb_height'] = $banner_info['image_thumb_height'];
		} else {
			$data['image_thumb_height'] = 350;		
		}
		
		if (isset($this->request->post['image_popup_width'])) {
			$data['image_popup_width'] = $this->request->post['image_popup_width'];
		} elseif (!empty($banner_info['image_popup_width'])) {
			$data['image_popup_width'] = $banner_info['image_popup_width'];
		} else {
			$data['image_popup_width'] = 1000;
		}
		
		if (isset($this->request->post['image_popup_height'])) {
			$data['image_popup_height'] = $this->request->post['image_popup_height'];
		} elseif (!empty($banner_info['image_popup_height'])) {
			$data['image_popup_height'] = $banner_info['image_popup_height'];
		} else {
			$data['image_popup_height'] = 1000;
		}
		
		if (isset($this->request->post['template'])) {
			$data['template'] = $this->request->post['template'];
		} elseif (!empty($banner_info['template'])) {
			$data['template'] = $banner_info['template'];
		} else {
			$data['template'] = '1';
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->post['banner_image'])) {
			$banner_images = $this->request->post['banner_image'];
		} elseif (isset($this->request->get['banner_id'])) {
			$banner_images = $this->model_extension_theme_prostoreblogbanner->getBannerImages($this->request->get['banner_id']);
		} else {
			$banner_images = array();
		}

		$data['banner_images'] = array();

		foreach ($banner_images as $key => $value) {
			foreach ($value as $banner_image) {
				if (is_file(DIR_IMAGE . $banner_image['image'])) {
					$image = $banner_image['image'];
					$thumb = $banner_image['image'];
				} else {
					$image = '';
					$thumb = 'no_image.png';
				}

				$data['banner_images'][$key][] = array(
					'title'      => $banner_image['title'],
					'link'       => $banner_image['link'],
					'image'      => $image,
					'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
					'sort_order' => $banner_image['sort_order']
				);
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostorecatalog/prostore_blog_banner_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore/prostore_blog_banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if (!$this->request->post['image_thumb_height']) {
			$this->error['image_thumb'] = $this->language->get('error_image_thumb');
		}

		if (!$this->request->post['image_popup_height']) {
			$this->error['image_popup'] = $this->language->get('error_image_popup');
		}
		
		if (isset($this->request->post['banner_image'])) {
			foreach ($this->request->post['banner_image'] as $language_id => $value) {
				foreach ($value as $banner_image_id => $banner_image) {
					if ((utf8_strlen($banner_image['title']) < 2) || (utf8_strlen($banner_image['title']) > 64)) {
						$this->error['banner_image'][$language_id][$banner_image_id] = $this->language->get('error_title');
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore/prostore_blog_banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$this->load->language('extension/module/prostore_blog');

		$json = array();
	
		if (isset($this->request->get['filter_name'])) {
		  $this->load->model('extension/theme/prostoreblogbanner');
	
		  if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		  } else {
			$filter_name = '';
		  }
	
		  $filter_data = array(
			'filter_name'  => $filter_name,
			'start'        => 0,
			'limit'        => 20
		  );
	
		  $results = $this->model_extension_theme_prostoreblogbanner->getBanners($filter_data);
		  
		  foreach ($results as $result) {
	
			$json[] = array(
			  'product_id' => $result['banner_id'],
			  'name'       => strip_tags(html_entity_decode($result['name'] , ENT_QUOTES, 'UTF-8')),
			  'comment'	   => sprintf($this->language->get('text_banner_id'),$result['banner_id'])
			);
		  }
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
