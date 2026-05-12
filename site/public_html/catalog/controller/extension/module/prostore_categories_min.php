	<?php
class ControllerExtensionModuleProstoreCategoriesMin extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$this->load->model('catalog/category');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		if(!isset($setting['title' . $this->config->get('config_language_id')])){ return; }
		$data['title'] = $setting['title' . $this->config->get('config_language_id')];
		
		$data['column'] = $setting['column'];
				
		$data['banners'] = array();

		if(isset($setting['categories_min_image'])){
			foreach ($setting['categories_min_image'] as $row => $result) {
				$title = '';
				$subCategories = '';
				if (isset($setting['category_id'][$row])) {
					$category_id = $setting['category_id'][$row];
					$categoryInfo = $this->model_catalog_category->getCategory($category_id);// var_dump($categoryInfo);
					if (isset($categoryInfo['name'])) {
						$title = $categoryInfo['name'];
						$subCategories = $this->model_catalog_category->getCategoriesChildrenNextLevel($category_id);
					}
				}
				if (is_file(DIR_IMAGE . $result['image'])) { 
					$data['banners'][$result['sort_order']][] = array(
					'title' 		    => $title,
					'background'  		=> $result['background'],
					'width'  		    => $result['width'],
					'height'  		    => $result['height'],
					'full'  		    => isset($result['full']),
					'subcategories'     => $subCategories,
					'link'      		 => $this->url->link('product/category', 'path=' . $category_id),
					'image' 			=> $this->model_tool_image->resize($result['image'], $result['width'], $result['height'])
				);
				}
			}			
		}
		
		ksort($data['banners']);
		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_categories_min', $data);
		
	}
}
?>
