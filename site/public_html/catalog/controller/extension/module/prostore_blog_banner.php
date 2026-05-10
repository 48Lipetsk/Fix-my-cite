<?php
class ControllerExtensionModuleProstoreBlogBanner extends Controller {
	public function index() {
		$this->load->model('extension/module/prostoreblog');
		$this->load->model('tool/image');
    }

}