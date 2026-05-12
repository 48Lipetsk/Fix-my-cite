<?php
class ControllerExtensionModuleProstoreMainSlider extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		
		$data['autoplay'] = $setting['autoplay'];
		$data['speed'] = $setting['speed'];
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		if (isset($setting['img_link'])) {
			$data['img_link'] = $setting['img_link'];
		} else {
			$data['img_link'] = '';
		}
		if (isset($setting['resizable'])) {
			$data['resizable'] = $setting['resizable'];
		} else {
			$data['resizable'] = '';
		}

		$data['slide_width'] = $setting['slide_width'];
		$data['slide_fade'] = $setting['slide_fade'];
		$data['banners'] = array();

		if(isset($setting['slider_image'])){
			$isRightImage = 0;
			$i = 0;
			foreach ($setting['slider_image'] as  $result) {
				
				if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
				$result = $result['language'][$this->config->get('config_language_id')];
				if (is_file(DIR_IMAGE . $result['image'])) { 
				
					if ($result['position'] == 2) {
						$isRightImage = 1;
					} else {
						$i++;
					}
					$fillWholeSlide = 0;
					if (isset($result['full'])) {
						$fillWholeSlide = $result['full'];
					}

					$data['banners'][$result['sort_order']][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'full'  => $fillWholeSlide,
						'position'  => $result['position'],
						'slider_text'  => html_entity_decode($result['slider_text'], ENT_QUOTES, 'UTF-8'),
						'btn_text'  => html_entity_decode($result['btn_text'], ENT_QUOTES, 'UTF-8'),
						'text_color'  => $result['text_color'],
						'width_pc'  => $result['width_pc'],
						'height_pc'  => $result['height_pc'],
						'image' => $this->model_tool_image->resize($result['image'], $result['width_pc'], $result['height_pc']),
						'image2' => ((!empty($result['image2']) && isset($result['image2'])) && is_file(DIR_IMAGE . $result['image2'])) ? $this->model_tool_image->resize($result['image2'], $result['width_mobile'], $result['height_mobile']) : '',
					);
				}
			}			
		}
		
		ksort($data['banners']);
		
		$data['left_image_count'] = $i;
		$data['isrightimage'] = $isRightImage;
		if (!$isRightImage && !empty($data['banners']) ) {
			$firstBanner = current($data['banners']);
			$this->document->addLink($firstBanner[0]['image'], 'preload_as_image');
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_main_slider', $data);
		
	}
}
?>
