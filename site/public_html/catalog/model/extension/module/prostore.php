<?php
class ModelExtensionModuleProstore extends Model {

	public function getLikes4review($review_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "review_extls WHERE review_id = '" . (int)$review_id . "'";

		$query = $this->db->query($sql);

		return $query->row;
	}

    public function getProductPath ($product_id, $currentPath = '') {
        $path = '';
        $sql = "SELECT pc.category_id FROM " . DB_PREFIX . "product_to_category pc LEFT JOIN " . DB_PREFIX . "category_path cp ON (pc.category_id = cp.category_id)";
        $sql .= " WHERE pc.product_id = '" . (int)$product_id . "' AND cp.path_id = '" . (int)$currentPath . "'";
        // Simle way
//      $sql = "SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'";
        $query = $this->db->query($sql);
        if (!$query->num_rows) {
            return $path;
        }
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$query->row['category_id'] . "' ORDER BY level ASC");
        if (!$query->num_rows) {
            return $path;
        }
        $categories = [];
        foreach ($query->rows as $key => $row) {
            $categories[] = $row['path_id'];
        }
        $path = implode('_', $categories);
        
        return $path;
    }  

	public function setLikes4review($data) {

		if ($this->getLikes4review($data['review_id'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "review_extls SET count_good = '" . (int)$data['count_good'] . "', count_bad = '" . (int)$data['count_bad'] . "' WHERE review_id = '" . (int)$data['review_id'] . "'");
		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "review_extls SET review_id = '" . (int)$data['review_id'] . "', count_good = '" . (int)$data['count_good'] . "', count_bad = '" . (int)$data['count_bad'] . "', answer_date_added = NOW() ");
		}
	}

	public function getStorysModId($name) {
		$module_id = 0;
		$query = $this->db->query("SELECT module_id FROM  " . DB_PREFIX . "module WHERE name = '" . $this->db->escape($name) . "' AND code='prostore_stories'");
		if ($query->num_rows) {
			$module_id = $query->row['module_id'];
		}
		return $module_id;
	}

	public function getFields($customer_group_id,$type = 0) {
		if(!isset($customer_group_id)){ $customer_group_id = $this->config->get('config_customer_group_id') ;}
		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_custom_field cf  LEFT JOIN " . DB_PREFIX . "prostore_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= "WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "'";
		$sql .= " AND cfd.store_id = '" . (int)$this->config->get('config_store_id') . "' AND cfcg.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		if($type){
			$sql .= " AND  cf.type = '" . (int)$type . "'";			
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getIgnoredFields($customer_group_id = 0,$type = 0) {
		if(!isset($customer_group_id)){ $customer_group_id = $this->config->get('config_customer_group_id') ;}
		$data = array();
		$sql = "SELECT name FROM " . DB_PREFIX . "prostore_custom_field cf  LEFT JOIN " . DB_PREFIX . "prostore_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= "WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "'";
		$sql .= " AND cfd.store_id = '" . (int)$this->config->get('config_store_id') . "' AND cfcg.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		if($type){
			$sql .= " AND (cfcg.required  = 0 OR cfcg.is_show  = 0 OR cf.type  != '" . (int)$type . "') ";			
		}else{
			$sql .= " AND (cfcg.required  = 0 OR cfcg.is_show  = 0) ";
		}	
		$query = $this->db->query($sql);

		foreach($query->rows as $fieldName){
			$data[] = $fieldName['name'];
		}

		return $data;
	}

	public function getAllFields($type = 0) {
		$data = array();
		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_custom_field cf  LEFT JOIN " . DB_PREFIX . "prostore_custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_custom_field_customer_group cfcg ON (cf.custom_field_id = cfcg.custom_field_id)";
		$sql .= " WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$sql .= " AND cfd.store_id = '" . (int)$this->config->get('config_store_id') . "' AND cfcg.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$sql .= " GROUP BY cf.name  ORDER BY cf.sort_order ASC";
		$query = $this->db->query($sql);

		foreach($query->rows as $fieldName){
			$data[] = $fieldName['name'];
		}
		return $data;
	}

	public function getFields4Product($product_id) {
		$tabs = array();
		$output = array();

		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_custom_tabs ct  LEFT JOIN " . DB_PREFIX . "prostore_custom_tabs_description ctd ON (ct.cust_tab_id = ctd.cust_tab_id) ";
		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_custom_tabs_to_store ct2s ON (ct.cust_tab_id = ct2s.cust_tab_id) ";
		$sql .= "WHERE ctd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ct2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND ct.status = '1' ORDER BY ct.sort_order ASC";
		$query = $this->db->query($sql);

		$tabs = $query->rows;

		foreach ($tabs as $key => $tab) { 
			$tab['description'] = html_entity_decode($tab['description'], ENT_QUOTES, 'UTF-8');
			$inTab = false;
			$query = $this->db->query("SELECT instanses FROM " . DB_PREFIX . "prostore_custom_tabs_instanses WHERE cust_tab_id='" . (int) $tab["cust_tab_id"] . "'");
			if($query->num_rows){ 
				$instanses = array();
				foreach ($query->rows as $instanse) {
					$instanses[] =  $instanse['instanses'];
				}			
				if($tab["mode"] == 'categories'){ 
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id) WHERE cp.path_id  IN (".implode(',', $instanses).") AND product_id='" . (int) $product_id . "'");

					if($query->num_rows){ $inTab = true; }
                }
                if($tab["mode"] == 'brands'){
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE manufacturer_id  IN (".implode(',', $instanses).") AND product_id='" . (int) $product_id . "'");					
					if($query->num_rows){ $inTab = true; }
				}
				if($tab["mode"] == 'products'){
					if(in_array($product_id, $instanses)){ $inTab = true;}
				}
			}else{
				$inTab = true;  //Если нет инстансов - таб применяется ко всем продуктам
			}
			if($inTab){
				$output[$tab["view"]][] = $tab;
			}			
		}
		return $output;
	}

	public function createCatLinks($language_id ) {
		$data = array();	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$language_id  . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY c.parent_id, c.sort_order, cd.name");
		$results = $query->rows;
		foreach ($results as $result) {
			$data['c'.$result['category_id']] = array(
				$result['name']  => 'product/category&path=' .  $result['category_id']
			);			
		}	
		return 	$data ;
	}

	public function checkLCustomFields($error) {

		$this->load->language('account/register');
		$this->load->language('extension/theme/prostore');

		$fields = array();
		foreach ($this->getFields($this->config->get('config_customer_group_id')) as $key => $field) {
			if($field['description']){
				$fields[$field['name']] = $field['description'];
			}else{
				$fields[$field['name']] = $this->language->get('entry_'.$field['name']);
			}			
		}

		if(isset($this->request->post['firstname'])){
			if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 64)) {
				$error['firstname'] = sprintf($this->language->get('error_firstname'),$fields['firstname']);
			}else{
				unset($error['firstname']);
			}
		}

		if(isset($this->request->post['lastname'])){
			if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 64)) {
				$error['lastname'] = sprintf($this->language->get('error_lastname'),$fields['lastname']);
			}else{
				unset($error['lastname']);
			}
		}

		if($this->config->get('config_mail_regexp')){
			$mail_regexp = $this->config->get('config_mail_regexp');
		}else{
			$mail_regexp = '/^[^@]+@.*.[a-z]{2,15}$/i';
		}

		if(isset($this->request->post['email'])){
			if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match($mail_regexp, $this->request->post['email'])) {
				$error['email'] = sprintf($this->language->get('error_email'),$fields['email']);
			}else{
				unset($error['email']);
			}
		}

		if(isset($this->request->post['telephone'])){
			if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
				$error['telephone'] = sprintf($this->language->get('error_telephone'),$fields['telephone']);
			}else{
				unset($error['telephone']);
			}
		}

		if(isset($this->request->post['address_1'])){		
			if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
				$error['address_1'] = sprintf($this->language->get('error_address_1'),$fields['address_1']);
			}else{
				unset($error['address_1']);
			}
		}

		if(isset($this->request->post['address_2'])){		
			if ((utf8_strlen(trim($this->request->post['address_2'])) < 3) || (utf8_strlen(trim($this->request->post['address_2'])) > 128)) {
				$error['address_2'] = sprintf($this->language->get('error_address_1'),$fields['address_2']);
			}else{
				unset($error['address_2']);
			}
		}

		if(isset($this->request->post['company'])){
			if ((utf8_strlen($this->request->post['company']) < 3) || (utf8_strlen($this->request->post['company']) > 32)) {
				$error['company'] = sprintf($this->language->get('error_company'),$fields['company']);
			}else{
				unset($error['company']);
			}
		}

		if(isset($this->request->post['city'])){
			if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
				$error['city'] = sprintf($this->language->get('error_city'),$fields['city']);
			}else{
				unset($error['city']);
			}
		}

		if(isset($this->request->post['fax'])){
			if ((utf8_strlen($this->request->post['fax']) < 3) || (utf8_strlen($this->request->post['fax']) > 32)) {
				$error['fax'] = sprintf($this->language->get('error_fax'),$fields['fax']);
			}else{
				unset($error['fax']);
			}
		}
		

		if(isset($this->request->post['postcode'])){
			if (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10) {
				$error['postcode'] = sprintf($this->language->get('error_postcode'),$fields['postcode']);
			}else{
				unset($error['postcode']);
			}
		}

		if(isset($this->request->post['country_id'])){
			if (!isset($this->request->post['country_id']) || $this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
				$error['country'] = sprintf($this->language->get('error_country'),$fields['country']);
			}else{
				unset($error['country']);
			}
		}

		if(isset($this->request->post['zone_id'])){  
			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '0' || !is_numeric($this->request->post['zone_id'])) {
				$error['zone'] = sprintf($this->language->get('error_zone'),$fields['zone']);
			}else{
				unset($error['zone']);
			}
		}


		return $error ;
	}

	public function getPriceLimits($data) {

		$customer_group_id = $this->getCustomerGroup();

		$sql = "SELECT max(coalesce((SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1), " .
			   "(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), " .
			   "p.price) ) AS max_price, min(coalesce((SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1), " .
			   "(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1), " .
			   "p.price) ) AS min_price  FROM ";

		if ($this->config->get('theme_prostore_subcategory')) {
			$sql .=  DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id) LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
		}else{
			$sql .=  DB_PREFIX . "product p ";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id=p.product_id) ";

		if (!$this->config->get('theme_prostore_subcategory')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_id=p.product_id) ";
		}


		$sql .= " WHERE p.status = '1' AND p.date_available <= NOW( ) AND p2s.store_id = " . (int)$this->config->get('config_store_id');

		if($data['category_id']) {
			if ($this->config->get('theme_prostore_subcategory')) {
				$sql .= " AND cp.path_id = '" . (int)$data['category_id'] . "'";
			}else{
				$sql .= " AND p2c.category_id = '" . (int)$data['category_id'] . "'";
			}
			
		}

		$query = $this->db->query($sql);

		$tax_class_id = '';
	    if ($this->config->get('config_tax')) {
	    	$data['filter_category_id'] = $data['category_id'];
	        $tax_data = $this->gettax_class_id($data);
		    if (isset($tax_data['tax_class_id'])) {
		        $tax_class_id = $tax_data['tax_class_id'];
		    }
	    }	
	    	
		if(isset($query->row)){
			$prices = $query->row;
      		$tempMax = $this->currency->format($this->tax->calculate( $prices['max_price'], $tax_class_id, $this->config->get('config_tax') ), $this->session->data['currency'],'',false);
     		 $tempMin = $this->currency->format($this->tax->calculate( $prices['min_price'], $tax_class_id, $this->config->get('config_tax') ), $this->session->data['currency'],'',false);
			$query->row['max_price'] = $tempMax;
			$query->row['min_price'] = $tempMin;
		}

		return $query->row;
	}

	private function getCustomerGroup() {
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
			return $customer_group_id;
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
			return $customer_group_id;
		}
	}

	public function getHitProducts($period,$qty) {
		$data = array();
		$query = $this->db->query("SELECT * FROM (SELECT op.product_id,o.date_added,SUM(op.quantity) AS totall FROM " . DB_PREFIX . "order_product op  LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) WHERE o.order_status_id != 0 AND o.date_added > CURDATE()-INTERVAL " . (int)$period . " DAY GROUP by op.product_id) src WHERE src.totall >= " . (int)$qty );
		if ($query->num_rows) {
			foreach ($query->rows as  $value) {
				$data[$value['product_id']] = $value;
			}
		}
		return 	$data ;
	}

	public function getSets4Product($product_id,$limit = 10) {  
		$sets = array();	
		$sql = "SELECT tp.set_id FROM " . DB_PREFIX . "prostore_product_to_set  tp LEFT JOIN " . DB_PREFIX . "prostore_set_to_store ts2s ON (tp.set_id = ts2s.set_id) WHERE tp.product_id = '" . (int)$product_id . "' AND ts2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP by tp.set_id"; 
		if (!$product_id) {
			$sql = "SELECT tp.set_id FROM " . DB_PREFIX . "prostore_product_to_set tp LEFT JOIN " . DB_PREFIX . "prostore_set_to_store ts2s ON (tp.set_id = ts2s.set_id) WHERE ts2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  GROUP by tp.set_id";
			if ($limit) {
				$sql .= " LIMIT " . (int)$limit;
			}
		}
		$query = $this->db->query($sql); 
		if ($query->num_rows) {
			foreach ($query->rows as  $set) { 
				$products = array();
				$sql = "SELECT * FROM " . DB_PREFIX . "prostore_product_to_set  WHERE set_id = '" . (int)$set['set_id'] . "' GROUP BY row_id ORDER by `set_product_id` ASC";
				$queryP = $this->db->query($sql);
				$set_rows = $queryP->num_rows;
				$row_not_in_stock = false;
				if ($queryP->num_rows) {
					foreach ($queryP->rows as $row) { 
						if ($this->config->get('config_stock_checkout')) {
							$sql = "SELECT * FROM " . DB_PREFIX . "prostore_product_to_set  WHERE set_id = '" . (int)$set['set_id'] . "' AND row_id = '" . (int)$row['row_id'] . "' ORDER by `set_product_id` ASC";
						}else {
							$sql = "SELECT * FROM " . DB_PREFIX . "prostore_product_to_set pts LEFT JOIN " . DB_PREFIX . "product p  ON(pts.product_id = p.product_id) ";
							$sql .= " WHERE  pts.set_id = '" . (int)$set['set_id'] . "' AND  pts.row_id = '" . (int)$row['row_id'] . "' AND p.quantity > 0 ";						
							$sql .= " ORDER by  pts.set_product_id ASC";
						}	

						$queryR = $this->db->query($sql);

						if (!$queryR->num_rows) {
							$row_not_in_stock = true;
							continue;
						}
						unset($addproduct_id);
						foreach ($queryR->rows as $key => $row_product) {
							if ($row_product['product_id'] == $product_id) {
								$addproduct_id = $product_id; break; 
							}
						}

						$products[] = array(
							'row_id'	 => $row['row_id'],
							'product_id' => $addproduct_id ?? $queryR->row['product_id'],
							'quantity' => $row['quantity'],
							'sort_order' => $row['sort_order']
						);
					}

				}
				$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set  WHERE set_id = '" . (int)$set['set_id'] . "' AND status=1";
				$queryP = $this->db->query($sql);
				if ($queryP->num_rows) {
					$notinstock = $queryP->row['notinstock'] ?? 0;
					if (!$notinstock && $row_not_in_stock) {
						// Do not show set if one of the rows is not in stock
						continue;
					}
					$sets[$set['set_id']] = array(
						'mode'			=> $queryP->row['mode'],
						'discount'		=> $queryP->row['discount'],
						'notinstock'	=> $queryP->row['notinstock'],
						'sort_order'	=> $queryP->row['sort_order'],
						'products'		=> $products
					);
				}
			}
		}
		return 	$sets ;
	}

	public function getSetInfo($set_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set ls LEFT JOIN " . DB_PREFIX . "prostore_set_description lsd ON(ls.set_id = lsd.set_id) WHERE ls.set_id = '" . (int)$set_id . "' AND lsd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql); 
		return $query->row;
	}

	public function getSetDiscount($total,$setInfo,$cartProducts) {
		$data = array();
		$total = 0;
		$chekset = false;
		$setid = $setInfo['set_id'];

		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set ls LEFT JOIN " . DB_PREFIX . "prostore_product_to_set lps ON(ls.set_id = lps.set_id)  WHERE ls.set_id = '" . (int)$setid . "' GROUP by lps.row_id";
		$queryP = $this->db->query($sql);
		$chekset = true;

		if ($queryP->num_rows) {
			foreach ($queryP->rows as  $row) {

				$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set ls LEFT JOIN " . DB_PREFIX . "prostore_product_to_set lps ON(ls.set_id = lps.set_id)  WHERE ls.set_id = '" . (int)$setid . "' AND lps.row_id = '" . (int)$row['row_id'] . "'";
				$query = $this->db->query($sql); 

				$cheksetrow = false;
				if ($query->num_rows) {				

					foreach ($query->rows as  $product) {
						if (isset($cartProducts[$product['product_id']]) && $cartProducts[$product['product_id']]['quantity'] >= $product['quantity']) {
							$cheksetrow = true;
							$total += $cartProducts[$product['product_id']]['price'] * $product['quantity'];
							$cartProducts[$product['product_id']]['quantity'] =  $cartProducts[$product['product_id']]['quantity'] - $product['quantity']; // Отнимаем товар набора от товара в корзине для того чтоб проверить оставшийся на соответствие другим наборам
							break;
						}

					}

				}
				// If the row's product is not in the cart, we check if discount is allowed when one of position not in stock
				$notinstock = $setInfo['notinstock'] ?? 0;
				if (!$cheksetrow && $notinstock) {
					if ($this->inStockQtyProduct($product['product_id']) < 1) {
						// If an absent product is not in stock, we allow discount for this set
						$cheksetrow = true;
					}
				}
				$chekset *= $cheksetrow;

			}

			if ($queryP->row['mode']) {
				$data['discount']  = $total/100*(int)$queryP->row['discount'];
			}else{
				$data['discount'] = (int)$queryP->row['discount'];
			}
		}else{
			$chekset = false;
		}

		$data['cartproducts'] = $cartProducts;

		if (!$chekset) { //Если кол товаров меньше допустимого анулируем скидку.
			$data['discount'] = 0;
		}

		return 	$data ;
	}

	public function inStockQtyProduct($product_id) {

		$sql = "SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'";
		$query = $this->db->query($sql); 

		return $query->row['quantity'] ?? 0;
	}

	public function getVar4SetProduct($set_id,$product_id) {

		if ($this->config->get('config_stock_checkout')) {
			$sql = "SELECT * FROM " . DB_PREFIX . "prostore_product_to_set WHERE  set_id = '" . (int)$set_id . "' ";
			$sql .= " AND `row_id` = (SELECT row_id FROM " . DB_PREFIX . "prostore_product_to_set  WHERE product_id = '" . (int)$product_id . "' ";
			$sql .= " AND set_id = '" . (int)$set_id . "' GROUP by product_id) ORDER by `set_product_id` ASC";
		}else {
			$sql = "SELECT * FROM " . DB_PREFIX . "prostore_product_to_set pts LEFT JOIN " . DB_PREFIX . "product p  ON(pts.product_id = p.product_id) ";
			$sql .= " WHERE pts.set_id = '" . (int)$set_id . "' AND p.quantity > 0 ";
			$sql .= " AND pts.row_id = (SELECT row_id FROM " . DB_PREFIX . "prostore_product_to_set  WHERE product_id = '" . (int)$product_id . "' ";
			$sql .= " AND set_id = '" . (int)$set_id . "' GROUP by product_id) ORDER by pts.set_product_id ASC";
		}		
		$query = $this->db->query($sql); 

		return $query->rows;
	}

	public function getOrderProducts($order_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "order_product WHERE  order_id = '" . (int)$order_id . "'";
		$query = $this->db->query($sql); 

		return $query->rows;
	}

	public function gettax_class_id($filter_data) {

		$sql = "SELECT p.tax_class_id FROM ";

		if ($this->config->get('theme_prostore_subcategory')) {
			$sql .=  DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id) LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) ";
		}else{
			$sql .=  DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
		}

		$sql .= "LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE ";

		if ($this->config->get('theme_prostore_subcategory')) {
			$sql .= " cp.path_id = '" . (int)$filter_data['filter_category_id'] . "'";
		}else{
			$sql .= " p2c.category_id = '" . (int)$filter_data['filter_category_id'] . "'";
		}

		$sql .= " AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.tax_class_id DESC LIMIT 20";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getName4MenuItem($id,$data) {
	    $this->load->model('extension/module/prostorecatblog');
	    $this->load->model('catalog/information');
	    $this->load->model('catalog/category');
	    $name = '';
	    if (strpos($id, 'c') !== false) {
	        $cat_id = substr($id, 1);
	        $catInfo = $this->model_catalog_category->getCategory($cat_id); 
	        if (isset($catInfo['name'])) {
	        	$name = $catInfo['name'];
	        }
	    }elseif(strpos($id, 'i') !== false){
	        $inf_id = substr($id, 1);
	        $infInfo = $this->model_catalog_information->getInformation($inf_id);
	        if (isset($infInfo['title'])) {
	        	$name = $infInfo['title'];
	        }
	    }elseif(strpos($id, 'b') !== false){
	        $blogCat_id = substr($id, 1);
	        $blogCatInfo = $this->model_extension_module_prostorecatblog->getBlogCategory($blogCat_id);
	        if (isset($blogCatInfo['name'])) {
	        	$name = $blogCatInfo['name'];      
	        }
	    }else{
			if (isset($data['top_links'][$id])) {
				$name = key($data['top_links'][$id]);
			}
	    }

	    return $name;
	}

	public function addCallCheaper($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "prostore_callcheaper SET name = '" .  $this->db->escape($data['name'])  . "',";
		$sql .= "telephone = '" .  $this->db->escape($data['phone']) . "', date_added = NOW(), date_modified = NOW(),";
		$sql .= "status_id = '0', store_id = '" . (int)$this->config->get('config_store_id') . "' ,";
		$sql .= "email = '" . $this->db->escape($data['email']) . "', link = '" . $this->db->escape($data['link']) . "'";
		$sql .= " ,comment = '" . $this->db->escape($data['product_id']) . "'";
		$query = $this->db->query($sql);

  		return $this->db->getLastId();
	}	
	
	public function getProductOptionValueData($product_option_value_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ";
		$sql .= "ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ";
		$sql .= "ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' ";
		$sql .= "AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order";
		$query = $this->db->query($sql);

  		return $query->row;
	}	

	public function getCategoriesByName($data = array()) {
		$sql = "SELECT c1.category_id AS category_id, cd1.name AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category c1 LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c1.category_id = cd1.category_id)  WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd1.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " AND c1.status = '1' GROUP BY c1.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	
}

?>
