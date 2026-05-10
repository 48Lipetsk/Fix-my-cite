<?php
class ControllerExtensionModuleProstoreReviewShop extends Controller {

	public function index($settings) {
		if ($this->config->get('theme_prostore_reviews_status')) {
			$this->load->language('extension/module/prostore_review_shop');
			$this->load->model('extension/module/prostore_review_shop');

			if (isset($settings['title'][$this->config->get('config_language_id')])){ 
				$data['heading_title'] = $settings['title'][$this->config->get('config_language_id')];
			}
			if (isset($settings['text_info'][$this->config->get('config_language_id')])){ 
				$data['text_info'] = $settings['text_info'][$this->config->get('config_language_id')];
			}
			$data['text_all_reviews'] = $this->language->get('text_all_reviews');
			$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

			$data['reviews_href'] = $this->url->link('extension/module/prostore_review_shop/getshopreviews');
			$contacts_href = $this->url->link('information/contact');
			$data['text_initial'] = sprintf($this->language->get('text_initial'),$data['reviews_href'],$contacts_href);
			
			$limit = isset($settings['limit']) ? $settings['limit'] : 3;
			$page = 1;
			$order = 'DESC';

			$average_rating_by_item = $this->model_extension_module_prostore_review_shop->getRatingAverageByItemTotal();
			$data['average_rating'] = $this->model_extension_module_prostore_review_shop->getActiveReviewsAverage($average_rating_by_item);
			
			
				$filter_data = array(
					'review_id'          => 0,
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
				);

			$data['reviews'] = array();

			foreach ($this->model_extension_module_prostore_review_shop->getReviews($filter_data) as $result) {
				
						
				$data['reviews'][] = array(
					'author'     => $result['author'],
					'rating'     => $result['rating'],
					'date_added' => $this->rus_date("j F, Y ", strtotime($result['date_added'])),
					'description' => utf8_substr(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'href'  => $this->url->link('extension/module/prostore_review_shop/getshopreviews') . '#review' . $result['review_id']
				);
			}

			return $this->load->view('extension/module/prostore_review_shop_module', $data);
		}
	}




	public function getshopreviews() {

		$this->load->language('extension/theme/prostore');
		
		$this->load->language('extension/module/prostore_review_shop');

		$this->load->model('extension/module/prostore_review_shop');
			
		if ($this->config->get('theme_prostore_reviews_status')) {
			if ($this->config->get('theme_prostore_reviews_meta_title' . $this->config->get('config_language_id'))) {
				$this->document->setTitle($this->config->get('theme_prostore_reviews_meta_title' . $this->config->get('config_language_id')));
			} else {
				$this->document->setTitle($this->language->get('heading_title'));
			}
			
			if ($this->config->get('theme_prostore_reviews_meta_description' . $this->config->get('config_language_id'))) {
				$this->document->setDescription($this->config->get('theme_prostore_reviews_meta_description' . $this->config->get('config_language_id')));
			}
			
			if ($this->config->get('theme_prostore_reviews_meta_keyword' . $this->config->get('config_language_id'))) {
				$this->document->setKeywords($this->config->get('theme_prostore_reviews_meta_keyword' . $this->config->get('config_language_id')));
			}
			
			if (isset($this->request->get['page'])) {
				$page = (int)$this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['review_id'])) {
				$data['review_id'] = (int)$this->request->get['review_id'];
			} else {
				$data['review_id'] = 0;
			}		

			$data['schema'] = $this->config->get('theme_prostore_schema');
			$data['reviewsStats'] = array();
			
			$limit = $this->config->get('theme_prostore_reviews_limit');
			
			$order = 'DESC';
			
			$filter_data = array(
				'review_id'          => 0,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			
			$this->load->model('catalog/information');
			if ($this->config->get('theme_prostore_review_shop_pdata')) {	
				$review_pdata = $this->model_catalog_information->getInformation($this->config->get('theme_prostore_review_shop_pdata'));
				if ($review_pdata) {
					$data['text_review_pdata'] = sprintf($this->language->get('text_prostore_pdata'), $this->language->get('text_put_review'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('theme_prostore_review_shop_pdata'), true), $review_pdata['title'], $review_pdata['title']);
				} else {
					$data['text_review_pdata'] = '';
				}
			} else {
				$data['text_review_pdata'] = '';
			}

			$data['reviews'] = array();

			$results = $this->model_extension_module_prostore_review_shop->getReviews($filter_data);
			$review_total = $this->model_extension_module_prostore_review_shop->getTotalReviews();

			$data['review_total'] = sprintf($this->language->get('text_total_reviews'),$review_total);
			$data['review_total'] .= $this->prostore->helper->correctForm(array($review_total,'review'));

			$data['average_rating_by_item'] = $this->model_extension_module_prostore_review_shop->getRatingAverageByItemTotal();
			$data['average_rating_total'] = $this->model_extension_module_prostore_review_shop->getActiveReviewsAverage($data['average_rating_by_item']);

			$data['active_ratings_info'] = $this->model_extension_module_prostore_review_shop->getActiveReviewsR();

			foreach ($results as $result) {
				$data['reviews'][] = array(
					'author'     => $result['author'],
					'text'       => nl2br($result['text']),
					'rating'     => $result['rating'],
					'r1'     	 => $result['r1'],
					'r2'     	 => $result['r2'],
					'r3'     	 => $result['r3'],
					'r4'     	 => $result['r4'],
					'r5'      	 => $result['r5'],
					'review_id'     => (int)$result['review_id'], 
					'text_admin_answer'     => nl2br($result['text_admin_answer']), 
					'answer_date_added' => date($this->language->get('date_format_short'), strtotime($result['answer_date_added'])), // prostore
					'date_added_schema' => date('Y-m-d', strtotime($result['date_added'])), // prostore

					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
				);
			}

			$pagination = new Pagination();
			$pagination->total = $review_total;
			$pagination->page = $page;
			
			$pagination->limit = $limit;
				
			$pagination->url = $this->url->link('extension/module/prostore_review_shop/getshopreviews', '&page={page}');

			$data['pagination'] = $pagination->render();
			
			$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($review_total - $limit)) ? $review_total : ((($page - 1) * $limit) + $limit), $review_total, ceil($review_total / $limit));//prostore

			if (!$this->config->get('config_canonical_method')) {
				// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
				if ($page == 1) {
					$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews'), 'canonical');
				} elseif ($page == 2) {
					$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews'), 'prev');
				} else {
					$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews' . '&page=' . ($page - 1)), 'prev');
				}

				if ($limit && ceil($review_total / $limit) > $page) {
					$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews' . '&page=' . ($page + 1)), 'next');
				}
			} else {

				if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
					$server = $this->config->get('config_ssl');
				} else {
					$server = $this->config->get('config_url');
				};

				$request_url = rtrim($server, '/') . $this->request->server['REQUEST_URI'];
				$canonical_url = $this->url->link('extension/module/prostore_review_shop/getshopreviews');

				if (($request_url != $canonical_url) || $this->config->get('config_canonical_self')) {
					$this->document->addLink($canonical_url, 'canonical');
				}

				if ($this->config->get('config_add_prevnext')) {

					if ($page == 2) {
						$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews'), 'prev');
					} elseif ($page > 2)  {
						$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews' . '&page=' . ($page - 1)), 'prev');
					}

					if ($limit && ceil($review_total / $limit) > $page) {
						$this->document->addLink($this->url->link('extension/module/prostore_review_shop/getshopreviews' . '&page=' . ($page + 1)), 'next');
					}
				}
			}
				
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');		
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');        

			$this->response->setOutput($this->load->view('extension/module/prostore_review_shop', $data));
			
		} else {
			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			
			if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_logo_404'))) {
				$data['logo_404'] = (isset($this->request->server['HTTPS']) ? HTTPS_SERVER : HTTP_SERVER) . 'image/' . $this->config->get('theme_prostore_logo_404');
				} else {
				$data['logo_404'] = '';
			}
			
			$data['text_404'] = sprintf($this->language->get('text_404'), $this->url->link('information/contact', '', true), $this->url->link('product/search', '', true), $this->url->link('common/home', '', true));
		
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
	
			$this->response->setOutput($this->load->view('error/404', $data));

		}	
    }

	public function write() {
		$this->load->language('product/product');
		$this->load->model('extension/module/prostore_review_shop');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			$activeReviewsR = $this->model_extension_module_prostore_review_shop->getActiveReviewsR();
			if ($activeReviewsR['active_r_total']) {
				foreach ($activeReviewsR['active_r'] as $r_id => $r_name) {
					if (empty($this->request->post[$r_id]) || $this->request->post[$r_id] < 0 || $this->request->post[$r_id] > 5) {
						$json['error'] = $this->language->get('error_rating');
					}
				}
			}					
			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('extension/module/prostore_review_shop');

				$this->model_extension_module_prostore_review_shop->addReview($this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	

	public function rus_date() {
		$this->load->language('extension/module/prostore_news');
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
?>
