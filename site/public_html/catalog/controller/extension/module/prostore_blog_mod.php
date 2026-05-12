<?php
class ControllerExtensionModuleProstoreBlogMod extends Controller {
	public function index($settings) {
		$this->load->language('extension/module/prostore_blog_mod');

		$data['text_all_blogs'] = $this->language->get('text_all_blogs');

		if (isset($settings['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($settings['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
		}

		$this->load->model('extension/module/prostoreblog');
		$this->load->model('tool/image');
		$this->load->model('catalog/category');

		$main_category_id = $settings["main_category_id"] ?? 0;

		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');
		$data['blog_href'] = $this->url->link('extension/module/prostorecat_blog/getcat&lbpath='.$main_category_id);
		$data['width'] = $settings['width'];
		$data['height'] = $settings['height'];

		$limit = $settings['limit'];
		$page = 1;
		$order = 'DESC';

		$category_id = 0;
		if($settings['main_category_id']){
			$category_id = $settings['main_category_id'];
		}

		$prod_category_id = 0;
		if(isset($this->request->get['path'])){
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$prod_category_id = (int)array_pop($parts);
		}

		$filter_data = array(
			'filter_category_id'  => $category_id,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		if ($prod_category_id) {
			// If this module is in product category - show related to one articles only.
			$filter_data['filter_category_id'] = $prod_category_id;
			$results = $this->model_extension_module_prostoreblog->getBlogsRelated2ProdCat($filter_data);
			$prod_category_data = $this->model_catalog_category->getCategory($prod_category_id);
			if ($prod_category_data) {
				$data['heading_title'] = sprintf($this->language->get('heading_title_prod_cat'), $prod_category_data['name']);
			}
			$data['text_all_blogs'] = $this->language->get('text_all_blogs_prod_cat');
		}else{
			$results = $this->model_extension_module_prostoreblog->getBlogs($filter_data);
			$data['text_all_blogs'] = $this->language->get('text_all_blogs_1');
		}

		$data['blogs'] = array();

		foreach ($results as $result) {

			if ($result['image_preview']) {
				$image = $this->model_tool_image->resize($result['image_preview'], $settings['width'], $settings['height']);
			} elseif ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $settings['width'], $settings['height']);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $settings['width'], $settings['height']);
			}

			$data['blogs'][] = array(
				'title' => $result['title'],
				'image'       => $image,
				'date_added' => $this->rus_date("j F, Y ", strtotime($result['date_added'])),
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 120) . '...',
				'href'  => $this->url->link('extension/module/prostore_blog/getblog', 'blog_id=' . $result['blog_id'])
			);
		}

		if(isset($settings['layout']) && strpos($settings['layout'],'column_') !== false){
			return $this->load->view('extension/module/prostore_blog_mod_column', $data);
		}else{
			return $this->load->view('extension/module/prostore_blog_mod', $data);
		}


	}


	public function rus_date() {
		$this->load->language('extension/module/prostore_blog_mod');
			// Перевод
			 $translate = array(
					 'am' => $this->language->get('text_am'),
					 'pm' => $this->language->get('text_pm'),
					 'AM' => $this->language->get('text_AM'),
					 'PM' => $this->language->get('text_PM'),
					 'Monday' => $this->language->get('text_Monday'),
					 'Mon' => $this->language->get('text_Mon'),
					 'Tuesday' => $this->language->get('text_Tuesday'),
					 'Tue' => $this->language->get('text_Tue'),
					 'Wednesday' => $this->language->get('text_Wednesday'),
					 'Wed' => $this->language->get('text_Wed'),
					 'Thursday' => $this->language->get('text_Thursday'),
					 'Thu' => $this->language->get('text_Thu'),
					 'Friday' => $this->language->get('text_Friday'),
					 'Fri' => $this->language->get('text_Fri'),
					 'Saturday' => $this->language->get('text_Saturday'),
					 'Sat' => $this->language->get('text_Sat'),
					 'Sunday' => $this->language->get('text_Sunday'),
					 'Sun' => $this->language->get('text_Sun'),
					 'January' => $this->language->get('text_January'),
					 'Jan' => $this->language->get('text_Jan'),
					 'February' => $this->language->get('text_February'),
					 'Feb' => $this->language->get('text_Feb'),
					 'March' => $this->language->get('text_March'),
					 'Mar' => $this->language->get('text_Mar'),
					 'April' => $this->language->get('text_April'),
					 'Apr' => $this->language->get('text_Apr'),
					 'May' => $this->language->get('text_May'),
					 'June' => $this->language->get('text_June'),
					 'Jun' => $this->language->get('text_Jun'),
					 'July' => $this->language->get('text_July'),
					 'Jul' => $this->language->get('text_Jul'),
					 'August' => $this->language->get('text_August'),
					 'Aug' => $this->language->get('text_Aug'),
					 'September' => $this->language->get('text_September'),
					 'Sep' => $this->language->get('text_Sep'),
					 'October' => $this->language->get('text_October'),
					 'Oct' => $this->language->get('text_Oct'),
					 'November' => $this->language->get('text_November'),
					 'Nov' => $this->language->get('text_Nov'),
					 'December' => $this->language->get('text_December'),
					 'Dec' => $this->language->get('text_Dec'),
					 'st' => $this->language->get('text_st'),
					 'nd' => $this->language->get('text_nd'),
					 'rd' => $this->language->get('text_rd'),
					 'th' => $this->language->get('text_th'),
			 );
			 // если передали дату, то переводим ее
			 if (func_num_args() > 1) {
				$timestamp = func_get_arg(1);
			 return strtr(date(func_get_arg(0), $timestamp), $translate);
			 } else {
			// иначе текущую дату
				return strtr(date(func_get_arg(0)), $translate);
			 }
	}


}
