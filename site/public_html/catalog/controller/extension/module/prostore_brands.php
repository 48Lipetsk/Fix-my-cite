<?php
class ControllerExtensionModuleProstoreBrands extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->language('extension/module/prostore_brands');
		
		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		if(!isset($setting['title' . $this->config->get('config_language_id')])){ return; }
		$data['title'] = $setting['title' . $this->config->get('config_language_id')];
		

		$data['width'] = $setting['width'];
		$data['height'] = $setting['height'];
		$data['all_brands'] = $this->url->link('product/manufacturer');
		
		$data['banners'] = array();

		if(isset($setting['brands_image'])){
			foreach ($setting['brands_image'] as  $result) {
				if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
				$result = $result['language'][$this->config->get('config_language_id')];
				if (is_file(DIR_IMAGE . $result['image'])) { 
					$data['banners'][$result['sort_order']][] = array(
					'title' 			=> $result['title'],
					'link'  			=> $result['link'],
					'image' 			=> $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
				}
			}			
		}
		
		ksort($data['banners']);
		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_brands', $data);
		
	}
}
?>
