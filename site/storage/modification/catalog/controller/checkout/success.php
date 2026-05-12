<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');

		$data['order_id'] = 0;// prostore
			

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			$data['order_id'] = $this->session->data['order_id'];// prostore
			

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);

			unset($this->session->data['prostoresetid']);// prostore
			
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);


		// prostore
		$this->load->language('extension/theme/prostore');
		$data['text_prostore_con_soc2'] = $this->language->get('text_prostore_con_soc');
		
		$this->load->model('setting/setting');
		$this->load->model('extension/module/prostore');
		$data['social_navs'] = array();
		$social_links = $this->model_setting_setting->getSetting('theme_prostoresoclinks');
		$data['social_links'] = $social_links['theme_prostoresoclinks_array'];
		$data['social_navs'] = $this->config->get('theme_prostore_social_nav');	
		$data['soc_stat'] = $this->config->get('theme_prostore_success_soc_stat');	
		
		if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_logo_success'))) {
			$data['logo_success'] = (isset($this->request->server['HTTPS']) ? HTTPS_SERVER : HTTP_SERVER) . 'image/' . $this->config->get('theme_prostore_logo_success');
		} else {
			$data['logo_success'] = '';
		}
	    // prostore end
			
		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}