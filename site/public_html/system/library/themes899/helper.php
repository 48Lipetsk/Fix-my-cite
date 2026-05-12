<?php
namespace themes899;

class Helper
{

    private $registry;
	/**
	 * @var int getEngineType: 0 - OpenCart, 1 - ocStore
	 */
	protected $engine_type = 1;

    public function __construct($registry)
    {
        $this->registry = $registry;
		$this->engineType();
    }

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}	

/**
* WISH-COMPARE HELPERS START
*/
	public function isProductInCompare($product_id) 
	{
		$outData['is_in_compare'] = false;
		$outData['text_in_compare'] = '<br>';
		if (isset($this->session->data['compare'])) {
			$results = $this->session->data['compare'];
			$outData['compare_total'] = count($results);
			if ($outData['compare_total']) {
				foreach ($results as $compare_product_id) {
					if ($compare_product_id == $product_id) {
						$outData['is_in_compare'] = true;
						break;
					}
				}
				$outData['text_in_compare'] = $this->compare_text($outData['compare_total']);
			}
		}

		return $outData;
	}

	public function isProductInWishList($product_id) {
		$outData['is_in_wish'] = false;
		$outData['text_in_wishlist'] = '<br>';
		$this->load->model('account/wishlist');
		$wishesThisUser = $this->model_account_wishlist->getWishlistLb();
		$wishesAllUsers = $this->model_account_wishlist->getTotalWishlistByProduct($product_id);
		if (count($wishesThisUser)) {
			foreach ($wishesThisUser as $wishItem) {
				if ($product_id == $wishItem['product_id']) {
					$outData['is_in_wish'] = true;
					break;
				}
			}
		}
		$outData['wishes_total'] = $wishesAllUsers;
		if ($wishesAllUsers) {
			$outData['text_in_wishlist'] = $this->wishlist_text($wishesAllUsers);
		} 
        return $outData;
	}

	public function getDataWishCompare($product_id){
		$outData['wish_data'] = $this->isProductInWishList($product_id);
		$outData['compare_data'] = $this->isProductInCompare($product_id);
		return $outData;
	}
/**
 * WISH-COMPARE HELPERS END
 */	

/**
 * TEXT HELPERS START
 */

	public function correctForm($number, $type='product') {
		/**
		 * Для использования надо чтобы в extension/theme/prostore были прописаны правильные формы склониния для type (продукт, товар ...)
		 */
		$this->load->language('extension/theme/prostore');

		if (is_array($number)) {
			/**
			 * Приобращении из других классов параметры передаются в массиве $number
			 * Пример вызова $this->load->controller('extension/module/prostore/prostore_theme/correctForm',array($product_total,'product'));
			 */
			$temp = $number;
			$number = $temp[0];
			if (isset($temp[1])) {
				$type=$temp[1];
			}
		}

		if ($type == 'compare') {
			$type = 'model';
		}
		$suffix = array();
		$model_num_1 = $this->language->get('text_prostore_'.$type.'_num_1');
		$model_num_2 = $this->language->get('text_prostore_'.$type.'_num_2');
		$model_num_3 = $this->language->get('text_prostore_'.$type.'_num_3');
		$suffix = array($model_num_1, $model_num_2, $model_num_3);
		$keys = array(2, 0, 1, 1, 1, 2);
		$mod = $number % 100;
		$suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
		return $suffix[$suffix_key];
	}
	/**
	 * Функция для склонения текста в зависимости от количества товаров  в списке желаний
	 */
	public function wishlist_text($total) {
		$this->load->language('extension/theme/prostore');
		$type = 'wish';
		$outText = '';
		$outText = sprintf($this->language->get('text_prostore_in_'.$type), $total) . $this->correctForm($total, $type);
		return $outText;
	}
	/**
	 * Функция для склонения текста в зависимости от количества товаров в списке сравнения
	 */
	public function compare_text($total) { 
		$this->load->language('extension/theme/prostore');
		$type = 'compare';
		$outText = '';
		$outText = sprintf($this->language->get('text_prostore_in_'.$type), $total) . $this->correctForm($total, $type);
		return $outText;
	}

	/**
	 * Функция преобразует числовое значение даты в строковое значение даты
	 */
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
			 if (func_num_args() > 0) {
				$timestamp = func_get_arg(0);
			 return strtr(date('j F, Y', $timestamp), $translate);
			 } else {
			// иначе текущую дату
				return strtr(date('j F, Y'), $translate);
			 }
	}

/**
 * TEXT HELPERS END
 */


/**
 * CART HELPERS START
 */

	/**
	 * Функция возвращает общую стоимость всех товаров в корзине
	 */
	public function get_cart_total(){
		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
			
		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}	
		
		return $total;
	}

	public function getCartIdsAsKey(){
		$cartProducts = array();
		foreach ($this->cart->getProducts() as $product) {
			$cartProducts[$product['cart_id']] = $product;
		}
		return $cartProducts;
	}

	public function getProductsIdAsKey(){
		$cartProducts = array();
		foreach ($this->cart->getProducts() as $product) {
			$cartProducts[$product['product_id']] = $product;
		}
		return $cartProducts;
	}
	/**
	 * Преобразование product_id в cart_id если такой продукт есть в корзине.
	 * Используется для апдейта количества товаров на странице категорий.
	 * Проверяет минимальное количество товара для заказа по каждой позициив корзине.
	 * Если новое количество позиции менее минимально указанного в конфиге, товар удаляется изкорзины (массив $deletedItems).
	 * @return array $deletedItems - массив товаров, которые следует удалить из корзины.
	 */
	public function convert2cartId() {
		$deletedItems = array();
		if (empty($this->request->post['prod_id_quantity'])) {
			return $deletedItems;
		}
		$cartProducts = $this->getProductsIdAsKey();
		foreach ($this->request->post['prod_id_quantity'] as $product_id => $quantity) {
			if (array_key_exists($product_id,$cartProducts)) {
				if ($quantity < $cartProducts[$product_id]['minimum']) {
					$quantity = 0;
					$deletedItems[] = $product_id;
				}
				$cart_id = $cartProducts[$product_id]['cart_id'];
				$this->request->post['quantity'][$cart_id] = $quantity;
			}
		}
		unset($this->request->post['prod_id_quantity']);
		return $deletedItems;
	}
/**
 * 
 * Функция возвращает массив:
 * @return array $outData:
 * @param bool|int $isincart
 * @param int $to_cart_quantity
 */
	public function getCartInfo4Product($product_info) {
		$outData = array();
		$productsInCart = $this->getProductsIdAsKey();
		$isInCart = false;
		$outData['to_cart_quantity'] = $product_info['minimum'] ? $product_info['minimum'] : 1;

		if (array_key_exists($product_info['product_id'],$productsInCart)) {
			if (!empty($productsInCart[$product_info['product_id']]['option'])) {
				$outData['isincart'] = false;
				return $outData;
			}
			$isInCart = $productsInCart[$product_info['product_id']]['quantity'];
			if ($isInCart > $product_info['minimum']) {
				$outData['to_cart_quantity'] = $isInCart;
			}
		}
		$outData['isincart'] = $isInCart;

		return $outData;
	}


/**
 * CART HELPERS END
 */

	/**
	 * Delete all other filter_ids which are in the same filter_group as $filter_id
	 * For correct total count of products for this $filter_id
	 * @param int $filter_id
	 * @param array $filters
	 */
	public function correctFiltersForGroup($filter_id, &$filters){
		$sql = "SELECT f.filter_id, f.filter_group_id FROM " . DB_PREFIX . "filter_group fg LEFT JOIN " . DB_PREFIX . "filter f 
			ON (fg.filter_group_id = f.filter_group_id) where f.filter_id IN(" . $this->db->escape($filters) . ")";
		$query = $this->db->query($sql);
		if (!$query->num_rows) {
			return;
		}
		$filtersArray = $query->rows;
		$correctedFilters = [];
		$currentFilterGroup = 0;
		foreach ($filtersArray as $filter) {
			if ($filter_id == $filter['filter_id']) {
				$currentFilterGroup = $filter['filter_group_id'];
				break;
			}
		}
		if (!$currentFilterGroup) {
			return;
		}
		foreach ($filtersArray as $key => $filter) {
			if ($filter['filter_group_id'] == $currentFilterGroup && $filter_id != $filter['filter_id']) {
				continue;
			}
			$correctedFilters[] = $filter['filter_id'];
		}
		if ($correctedFilters) {
			$filters = implode(',', $correctedFilters);
		}
	}
 
	public function getEngineType(){
		return $this->engine_type;
	}

	/**
	 * This method returns type of this engine (OpenCart/ocStore)
	 * @return int $engineType (1 - ocStore , 0 - OpenCart)
	 */
	private function engineType(){
		/**
		 * Attempt 1
		 * Detect engine type by footer text in admin panel
		 */
		$dirAdminLanguage = DIR_LANGUAGE;
		$fileContent = '';

		if (!strpos($dirAdminLanguage,'admin')) {
			$dirAdminLanguage .= 'admin/';
		}

		$file = $dirAdminLanguage . $this->default . '/common/footer.php';
		if (is_file($file)) {
			$fileContent = file_get_contents($file);
		}

		if ($fileContent) {
			$fileContent = strtolower($fileContent);
			if (strpos($fileContent, 'ocstore')) {
				$this->engine_type = 1;
				return;
			}elseif (strpos($fileContent, 'opencart')) {
				$this->engine_type = 0;
				return;
			}
		}
		/**
		 * Attempt 2
		 * Detect engine type by field noindex in table oc_category
		 * If not exists - it's OpenCart
		 */
		$query = $this->db->query("SELECT count(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='" . DB_DATABASE . "' AND TABLE_NAME='" . DB_PREFIX . "category' AND COLUMN_NAME='noindex'");
		if (!$query->row['c']){ //opencart
			$this->engine_type = 0;
			return;
		}

	}

}
