<?php
class ModelExtensionThemeProstoreSets extends Model {
	public function addSet($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_set SET sort_order = 0, 
							mode = '" . (isset($data['mode']) ? (int)$data['mode'] : 0) . "', 
							notinstock = '" . ($data['notinstock'] ?? 0) . "', 
							discount = '" . (isset($data['discount']) ? (int)$data['discount'] : 0) . "', 
							status = '" . (int)$data['status'] . "', date_added = NOW()"
						);

		$set_id = $this->db->getLastId();

		foreach ($data['set_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_set_description SET set_id = '" . (int)$set_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "'");
		}

		if (isset($data['set_store'])) {
			foreach ($data['set_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_set_to_store SET set_id = '" . (int)$set_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product'])) {
			foreach ($data['product'] as $key => $rowProducts) { 
				if(!isset($rowProducts['items'])){ continue; }
				foreach ($rowProducts['items'] as $product) {

					$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_product_to_set SET set_id = '" . (int)$set_id . "', row_id = '" . (int)$key . "', product_id = '" . (int)$product['id'] . "', quantity = '" . (int)$rowProducts['qty'] . "', sort_order = " . (int)$rowProducts['sort_order'] . "");
				}
			}

		}


		$this->cache->delete('set');

		return $set_id;
	}

	public function editSet($set_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "prostore_set SET  mode = '" . (isset($data['mode']) ? (int)$data['mode'] : 0) . "', 
							notinstock = '" . ($data['notinstock'] ?? 0) . "', 
							discount = '" . (isset($data['discount']) ? (int)$data['discount'] : 0) . "', 
							status = '" . (int)$data['status'] . "' WHERE set_id = '" . (int)$set_id . "'"
						);

		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set_description WHERE set_id = '" . (int)$set_id . "'");

		foreach ($data['set_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_set_description SET set_id = '" . (int)$set_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set_to_store WHERE set_id = '" . (int)$set_id . "'");

		if (isset($data['set_store'])) {
			foreach ($data['set_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_set_to_store SET set_id = '" . (int)$set_id . "', store_id = '" . (int)$store_id . "'");
			}
		}


		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_product_to_set WHERE set_id = '" . (int)$set_id . "'");


		if (isset($data['product'])) {
			foreach ($data['product'] as $key => $rowProducts) { 
				if(!isset($rowProducts['items'])){ continue; }
				foreach ($rowProducts['items'] as $product) {

					$this->db->query("INSERT INTO " . DB_PREFIX . "prostore_product_to_set SET set_id = '" . (int)$set_id . "', row_id = '" . (int)$key . "', product_id = '" . (int)$product['id'] . "', quantity = '" . (int)$rowProducts['qty'] . "', sort_order = " . (int)$rowProducts['sort_order'] . "");
				}
			}

		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'set_id=" . (int)$set_id . "'");


		$this->cache->delete('set');
	}

	public function deleteSet($set_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set WHERE set_id = '" . (int)$set_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set_description WHERE set_id = '" . (int)$set_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set_to_store WHERE set_id = '" . (int)$set_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_set_to_layout WHERE set_id = '" . (int)$set_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "prostore_product_to_set WHERE set_id = '" . (int)$set_id . "'");

		$this->cache->delete('set');
	}

	public function getSet($set_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set i LEFT JOIN " . DB_PREFIX . "prostore_set_description id ON (i.set_id = id.set_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.set_id = '" . (int)$set_id . "'";

		$query = $this->db->query($sql);

		return $query->row;

	}

	public function getSetProduct($set_id) {
		$products = array();

		$sql = "SELECT *  FROM " . DB_PREFIX . "prostore_product_to_set WHERE set_id = '" . (int)$set_id . "'  ORDER BY row_id,set_product_id ASC";

		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $product) {
			$products[$product['row_id']][] = array(
				'id' 	=> $product['product_id'],
				'qty' 	=> $product['quantity'],
				'sort_order' 	=> $product['sort_order']
			);
		}

		return $products;

	}

	public function getSets($data = array()) {
				
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "prostore_set i LEFT JOIN " . DB_PREFIX . "prostore_set_description id ON (i.set_id = id.set_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'id.title',
				'i.sort_order',
				'i.date_added'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY i.date_added";
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
		} else {
			$set_data = $this->cache->get('prostoreset.' . (int)$this->config->get('config_language_id'));

			if (!$set_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_set i LEFT JOIN " . DB_PREFIX . "prostore_set_description id ON (i.set_id = id.set_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$set_data = $query->rows;

				$this->cache->set('prostoreset.' . (int)$this->config->get('config_language_id'), $set_data);
			}

			return $set_data;
		}
	}

	public function getSetDescriptions($set_id) {
		$set_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_set_description WHERE set_id = '" . (int)$set_id . "'");

		foreach ($query->rows as $result) {
			$set_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
			);
		}

		return $set_description_data;
	}

	public function getSetStores($set_id) {
		$set_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_set_to_store WHERE set_id = '" . (int)$set_id . "'");

		foreach ($query->rows as $result) {
			$set_store_data[] = $result['store_id'];
		}

		return $set_store_data;
	}


	public function getTotalSets() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_set");

		return $query->row['total'];
	}

	public function getTotalNewssByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_news_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

	public function getProductRelated($news_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_news_related WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}
		
}
