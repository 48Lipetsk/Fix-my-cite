<?php
class ControllerExtensionModuleProstoreStories extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->language('extension/module/prostore_stories');
		
		$this->load->model('extension/module/prostore');
		$this->load->model('tool/image');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		$data['banners'] = array();
		$images = array();
		$cookies = array();

		$width =  $setting['width'];
		$height =   $setting['height'];
		
		$data['width'] = $width;
		$data['height'] = $height;

		$data['module_id'] = $this->model_extension_module_prostore->getStorysModId($setting['name']);

		if (isset($setting['stories_column'])) {
			$module_id = $data['module_id'];

			if (isset($_COOKIE['899StoriesViewed-'.$module_id])) { 
				$cookies = explode(',',$_COOKIE['899StoriesViewed-'.$module_id]);

			}

			foreach ($setting['stories_column'] as $key => $stories_column) { 
				if(!isset($stories_column['language'][$this->config->get('config_language_id')])){ continue; }
				$stories_column_info = $stories_column['language'][$this->config->get('config_language_id')];

				if ($stories_column_info['date_start'] && $stories_column_info['date_end']) {
					$curDate = strtotime(date("d-m-Y"));
					$storyDateBegin = strtotime($stories_column_info['date_start']);
					$storyDateEnd = strtotime($stories_column_info['date_end']);
					if ($curDate <= $storyDateBegin || $curDate >= $storyDateEnd) {
						continue;
					}					
				}	

				$data['banners'][$key]['thumb'] = ''; 
				if (is_file(DIR_IMAGE . $stories_column_info['image'])) { 
					$data['banners'][$key]['thumb'] = $this->model_tool_image->resize($stories_column_info['image'], $width, $height);//preview
				}else{
					$firstImage = reset($stories_column['stories_image']);
					$firstImage = $firstImage['language'][$this->config->get('config_language_id')]['image'];
					if (is_file(DIR_IMAGE . $firstImage)) {
						$data['banners'][$key]['thumb'] = $this->model_tool_image->resize($firstImage, $width, $height);//preview
					}
				}
				if (!$data['banners'][$key]['thumb']) {
					unset($data['banners'][$key]);
					continue;
				}
				$data['banners'][$key]['sort_order'] = $stories_column['language'][$this->config->get('config_language_id')]['sort_order'];
				$data['banners'][$key]['isviewed'] = 0;
				if (in_array($key, $cookies)) { 
					$data['banners'][$key]['isviewed'] = 1;
				}					
			}
		}
		$sort_order = array();
		foreach ($data['banners'] as $key => $banner) {
			$sort_order[$key] = $banner['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['banners']);		
		

		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_stories', $data);
		
	}

	public function getOffers() {
		$this->load->model('setting/module');

		$this->load->model('tool/image');

		$data = array();
		$data['banners'] = array();
		if (!isset($this->request->get['module_id']) || !$this->request->get['module_id']) {
			return;
		}else{
			$module_id = $this->request->get['module_id'];
		}

		if (isset($this->request->get['story_id']) && $this->request->get['story_id']) {
			$story_id = $this->request->get['story_id'];
		}else{
			$story_id = 0;
		}

		$setting = $this->model_setting_module->getModule($module_id);
		$width =  $setting['width'];
		$height =   $setting['height'];
		
		if (isset($setting['stories_column'])) {
			foreach ($setting['stories_column'] as $key => $stories_column) {
		
				$images = array();
				if(isset($stories_column['stories_image'])){

					foreach ($stories_column['stories_image'] as  $result) {
						if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
						$result = $result['language'][$this->config->get('config_language_id')];
						
						if ($result['text_position'] == 1) {
							$position = 'top';
						} elseif ($result['text_position'] == 2) {
							$position = 'bottom';
						}
						
						if ($result['text_color'] == 2) {
							$color = 'white';
						}else {
							$color = 'primary';
						}
						
						if (is_file(DIR_IMAGE . $result['image'])) { 
							$images[] = array(
							'title' 			=> $result['title'],
							'link'  			=> $result['link'],
							'text_big' 		    => html_entity_decode($result['text_big'], ENT_QUOTES, 'UTF-8'),
							'text_small'  		=> html_entity_decode($result['text_small'], ENT_QUOTES, 'UTF-8'),
							'width'  		    => $width,
							'position'      	=> $position,
							'color' 	        => $color,
							'sort_order'      	=> $result['sort_order'],
							'image' 			=> $this->model_tool_image->resize($result['image'], $width, $height)
							);
						}
					}			
				}
				if (!empty($images)) {
					$sort_order = array();
					foreach ($images as $keyImg => $image) {
						$sort_order[$keyImg] = $image['sort_order'];
					}
					array_multisort($sort_order, SORT_ASC, $images);
				}
	
				$data['banners'][$key]['images'] = $images;
				$data['banners'][$key]['thumb'] = '';
				$data['banners'][$key]['sort_order'] = $stories_column['language'][$this->config->get('config_language_id')]['sort_order'];
				if (is_file(DIR_IMAGE . $stories_column['language'][$this->config->get('config_language_id')]['image'])) { 
					$data['banners'][$key]['thumb'] = $this->model_tool_image->resize($stories_column['language'][$this->config->get('config_language_id')]['image'], $width, $height);//preview
				}else{
					$firstImage = reset($stories_column['stories_image']);
					$firstImage = $firstImage['language'][$this->config->get('config_language_id')]['image'];
					if (is_file(DIR_IMAGE . $firstImage)) {
						$data['banners'][$key]['thumb'] = $this->model_tool_image->resize($firstImage, $width, $height);//preview
					}
				}	
#				$data['banners'][$key]['column_width'] = $stories_column["language"][$this->config->get('config_language_id')]["column_width"];
				$data['banners'][$key]['column_width'] = '7';				
			}
		}

		if (!isset($_COOKIE['899StoriesViewed-'.$module_id])) { 
			$cookies = array();
			$cookies[] = (string)$story_id;
			SetCookie('899StoriesViewed-'.$module_id,implode(',',$cookies),time()+3600*24*30*1,"/");
		}else{
			$cookies = explode(',',$_COOKIE['899StoriesViewed-'.$module_id]);
			if (!in_array($story_id, $cookies)) { 
			if (count($cookies) < 50) {
				$cookies[] = $story_id;
			}else{
				array_shift($cookies);
				$cookies[] = $story_id; 
			}
			}else{
			foreach ($cookies as $key => $cookie) {
				if ($cookie == $story_id) {
				unset($cookies[$key]); 
				}
			}
			$cookies[] = $story_id; 
			}
			SetCookie('899StoriesViewed-'.$module_id,implode(',',$cookies),time()+3600*24*30*1,"/");
		}     
				
		$sort_order = array();
		foreach ($data['banners'] as $key => $banner) {
			$sort_order[$key] = $banner['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $data['banners']);

		// Adapt for webp for AJAX loads
		$_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
		
		$this->response->setOutput($this->load->view('extension/module/prostore_stories_gal', $data));
	}

}
?>
