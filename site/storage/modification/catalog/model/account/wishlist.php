<?php
class ModelAccountWishlist extends Model {
	public function addWishlist($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_wishlist SET customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', date_added = NOW()");
	}

	public function deleteWishlist($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");
	}

	public function getWishlist() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->rows;
	}


// prostore

	public function getWishlistLb() {
		if ($this->customer->isLogged()) {
       		 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			return $query->rows;
		}else{
				$results = array();
				if(isset($_COOKIE["productsWish"])){
					foreach($_COOKIE["productsWish"] as $product_id => $val){
						$cookies[$val] = $product_id;
					}
					krsort($cookies);
					
					foreach($cookies as $product_id){
						$results[]  = array(
							'customer_id' => 0,
							'product_id' => $product_id, 
							'date_added' => ''
						);
					}
				}
				return 	$results ;		
		}
	}

	public function addWishlistLb($product_id) {
		if ($this->customer->isLogged()) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_wishlist SET customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', date_added = NOW()");
		}else{
			if (!isset($_COOKIE["productsWish[" . $product_id . "]"])){
				$_COOKIE["productsWish"][$product_id] = '0';
				SetCookie("productsWish[" . $product_id . "]",time(),time()+3600*24*30*12,"/");
			}		
		}	
	}

	public function getTotalWishlistLb() {
		if ($this->customer->isLogged()) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

			return $query->row['total'];
		}else{
				$results = 0;
				if(isset($_COOKIE["productsWish"])){
					$results = count($_COOKIE["productsWish"]);
				}
				return 	$results ;			
		}
	}

	public function deleteWishlistLb($product_id) {
		if ($this->customer->isLogged()) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");
		}else{
			SetCookie("productsWish[" . $product_id . "]",time(),time()-3600, '/');
			unset($_COOKIE["productsWish"][$product_id]);
		}
		return $this->getTotalWishlistLb();
	}

	public function getTotalWishlistByProduct($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_wishlist WHERE product_id = '" . (int)$product_id . "'");
		$result = $query->row['total'];

		if(isset($_COOKIE["productsWish"][$product_id])){
			$result++;
		}
		return 	$result;

	}

// prostore end
			
	public function getTotalWishlist() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
}
