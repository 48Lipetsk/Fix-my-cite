<?php
namespace themes899;

class Labels
{

    private $registry;

	protected $productLabelsConfig = array();

	protected $productLabels;

    public function __construct($registry){
        $this->registry = $registry;
		$this->init();
    }

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}	

	protected function init()
	{
		$this->setProductLabelsConfig();
		$this->setProductLabels();
	}
	
	protected function setProductLabelsConfig()
	{
		if($this->config->get('theme_prostore_label')){
			$this->productLabelsConfig = $this->config->get('theme_prostore_label');
		}
	}	

	public function getProductLabelsConfig() {
		return $this->productLabelsConfig;
	}

	/**
	 * Получаем общую информацию на основании которой будут формироваться стикеры для продуктов
	 * @return array of:
	 * @param array $labels_config
	 * @param int $language_id
	 * @param array $newest
	 * @param bool $sales
	 * @param array $hits
	 */
	protected function setProductLabels() {
		$data = array();
		$this->load->model('catalog/product');
		$this->load->model('extension/module/prostore');

		$data = $this->cache->get('899.labelsinfo.' . (int)$this->config->get('config_store_id'));
		if (!$data) {$data = [];}		
		if (!$data) {

			$data['labels_config'] = $this->getProductLabelsConfig();
			
			$data['language_id'] = $this->config->get('config_language_id');

			$data['newest'] = array();
			if(isset($this->productLabelsConfig['new']['period']) && $this->productLabelsConfig['new']['status']){
				$data['newest'] = $this->model_catalog_product->getNewestProducts($this->productLabelsConfig['new']['period']);				
			}
			$data['sales'] = false;
			if(isset($this->productLabelsConfig['sale']['status']) && $this->productLabelsConfig['sale']['status']){
				$data['sales'] = true;				
			}

			$data['hits'] = array();
			if (isset($this->productLabelsConfig['hit']) && $this->productLabelsConfig['hit']['status']) {
				$data['hits'] = $this->model_extension_module_prostore->getHitProducts($this->productLabelsConfig['hit']['period'],$this->productLabelsConfig['hit']['qty']);
			}

			$this->cache->set('899.labelsinfo.' . (int)$this->config->get('config_store_id') , $data);
		}
		$this->productLabels = $data;
	}

	/**
	 * Общая информация по стикерам
	 */
	public function getProductLabels() {
		return $this->productLabels;
	}
	/**
	 * Получаем стикеры для продукта
	 * @var array $product_info массив информации о продукте
	 * @return array of:
	 * @return bool $isnewest
	 * @return str $discount
	 * @return str $special_date_end
	 * @return bool $sales
	 * @return bool $catch
	 * @return bool $nocatch
	 * @return str $popular
	 * @return str $hit
	 */
	public function getLabels4Product($product_info){
		$this->load->model('catalog/product');

		$outdata = array(
			'isnewest' => false,
			'discount' => '',
			'special_date_end' => '',
			'sales' => false,
			'catch' => false,
			'nocatch' => false,
			'popular' => '',
			'hit' => '',
		);

		if (!isset($product_info['product_id'])) {
			return $outdata;
		}

		$product_id = $product_info['product_id'];
		$labelsInfo = $this->getProductLabels();

		if (in_array($product_id, $labelsInfo['newest'])) {
			$outdata['isnewest'] = true;
		}

		$outdata['sales'] = $labelsInfo['sales'];

		$outdata['special_date_end'] = false;
		if($labelsInfo['sales'] && (!is_null($product_info['special']) && (float)$product_info['special'] >= 0)){
			$action = $this->model_catalog_product->getProductActions($product_info['product_id']);
			if (isset($action['date_end']) && $action['date_end'] != '0000-00-00') {
				if (ini_get('date.timezone')) {
					$serverTimezone = new \DateTimeZone(ini_get('date.timezone'));
				}else {
					$serverTimezone = new \DateTimeZone(date_default_timezone_get());
				}
				$specialDateEnd = new \DateTime($action['date_end'],$serverTimezone);					
				$outdata['special_date_end'] = $specialDateEnd->format('Y-m-d\TH:i:sO');

			}				
			if($this->productLabelsConfig['sale']['extra'] == 1 ){
				if($product_info['price'] != 0){
					$discount = round((($product_info['price'] - $product_info['special'])/$product_info['price'])*100);
					$outdata['discount'] = $discount. ' %';
				}else{
					$outdata['discount'] = '';
				}
			}
			if($this->productLabelsConfig['sale']['extra'] == 2){
				$outdata['discount'] = $this->currency->format($this->tax->calculate(($product_info['price'] - $product_info['special']), $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			}					
		}

		if (isset($this->productLabelsConfig['catch']) && $this->productLabelsConfig['catch']['status'] && $product_info['quantity'] <= $this->productLabelsConfig['catch']['qty']) {
		  if($product_info['quantity'] > 0){
			$outdata['catch'] = $this->productLabelsConfig['catch']['name'][$this->config->get('config_language_id')];
		  }else{
			$outdata['catch'] = $this->productLabelsConfig['catch']['name1'][$this->config->get('config_language_id')];
			$outdata['nocatch'] = true;
		  }
		}

		if (isset($this->productLabelsConfig['popular']) && $this->productLabelsConfig['popular']['status'] && $product_info['viewed'] >= $this->productLabelsConfig['popular']['views']) {
		  $outdata['popular'] = $this->productLabelsConfig['popular']['name'][$this->config->get('config_language_id')];
		}

		if (isset($this->productLabelsConfig['hit']) && $this->productLabelsConfig['hit']['status']) {
		  if (isset($labelsInfo['hits'][$product_id])) {
			$outdata['hit'] = $this->productLabelsConfig['hit']['name'][$this->config->get('config_language_id')];
		  }
		}		

		return $outdata;
	}
	/**
	 * Получаем стикеры для массива продуктов
	 * @var array $inputArray - result of $this->model_catalog_product->getProduct*()
	 * @return array of labels for products
	 */
	public function getLabels4Products($products){
		$outdata = array();
		
		if (is_array($products) && count($products) > 0) {
			foreach ($products as $product_info) {
				if (isset($product_info['product_id'])) {
					$outdata[$product_info['product_id']] = $this->getLabels4Product($product_info);
				}
			}
		}

		return $outdata;
	}

}
