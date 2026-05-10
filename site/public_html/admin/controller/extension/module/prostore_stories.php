<?php
class ControllerExtensionModuleProstoreStories extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/prostore_stories');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addStyle('view/stylesheet/prostore/prostore.css?v' . $this->config->get('theme_prostore_version'));
				
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {  
				$this->model_setting_module->addModule('prostore_stories', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['module_id'] = 0;
		if (isset($this->request->get['module_id'])) {
			$data['module_id'] =$this->request->get['module_id'];
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_remove_col'] = $this->language->get('text_remove_col');
		$data['text_add_col'] = $this->language->get('text_add_col');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_shape'] = $this->language->get('entry_shape');
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_end'] = $this->language->get('entry_date_end');		

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

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

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}
		
		if (isset($this->error['stories_image'])) {
			$data['error_stories_image'] = $this->error['stories_image'];
		} else {
			$data['error_stories_image'] = array();
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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/prostore_stories', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/prostore_stories', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/prostore_stories', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/prostore_stories', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['shapes'] = array();
		$data['shapes'][] = array(
			'shape_id'		=> 'rectangle',
			'name'			=> $this->language->get('entry_rectangle')
		);
		$data['shapes'][] = array(
			'shape_id'		=> 'circle',
			'name'			=> $this->language->get('entry_circle')
		);

		$module_info = array();
		$data['stories_column'] = array();

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
	
		$this->load->model('localisation/language');
		$this->load->model('tool/image');


		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];

		if (!empty($module_info) && isset($module_info['stories_column'])) {
			$data['stories_column'] = $module_info['stories_column'];

			foreach ($module_info['stories_column'] as $numb => $column) {
				foreach($data['languages'] as $language){ 
					if (isset($column['language'][$language['language_id']]['image']) && is_file(DIR_IMAGE . $column['language'][$language['language_id']]['image'])) {
						$data['stories_column'][$numb]['language'][$language['language_id']]['thumb'] = $this->model_tool_image->resize($column['language'][$language['language_id']]['image'], 100, 100);
					}else{
						$data['stories_column'][$numb]['language'][$language['language_id']]['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);					}	
				}
		        $stories_images = $column['stories_image'];

				$data['stories_column'][$numb]['stories_images'] = $stories_images;

				foreach ($stories_images as $key => $stories_image_l) {
					foreach($stories_image_l['language'] as $language_id => $stories_image){ 
						if (isset($stories_image['image']) && is_file(DIR_IMAGE . $stories_image['image'])) {
							$image = $stories_image['image']; 
							$thumb = $stories_image['image'];
						} else {
							$image = '';
							$thumb = 'no_image.png';
						}			
						$data['stories_column'][$numb]['stories_images'][$key]['language'][$language_id]["thumb"] = $this->model_tool_image->resize($thumb, 100, 100);  
						$data['stories_column'][$numb]['stories_images'][$key]['language'][$language_id]["image"] = $image;			
					}

				}
			}
		}


		if (isset($this->request->get['column'])) {
			$data['stories_column'] = array();
			$newrow = 0;
			if(isset($this->request->get['row'])){
				$newrow = ($this->request->get['row']);
			}
			$data['stories_column'][$this->request->get['column']]['stories_images'][$newrow] = array();
//			$data['stories_images'] = array();
//			$data['stories_images'][1] = array();
		}



		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info) && isset($module_info['width'])) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '320';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info) && isset($module_info['height'])) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '480'; 
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '1';
		}
 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/prostore_stories', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/prostore_stories')) {
			$this->error['warning'] = $this->language->get('error_permission');  
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
}
