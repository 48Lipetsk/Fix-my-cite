<?php
class ModelExtensionModuleProstoreblog extends Model {

	public function updateViewed($blog_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "prostore_blog SET viewed = (viewed + 1) WHERE blog_id = '" . (int)$blog_id . "'");
	}

	public function getBlog($blog_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "prostore_blog i LEFT JOIN " . DB_PREFIX . "prostore_blog_description id ON (i.blog_id = id.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store i2s ON (i.blog_id = i2s.blog_id) WHERE i.blog_id = '" . (int)$blog_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}

	public function getBlogs($data) {

		$sql = "SELECT * ";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "prostorecat_blog_path cp LEFT JOIN " . DB_PREFIX . "prostore_blog_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "prostore_blog_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "prostore_blog i ON (p2c.blog_id = i.blog_id)";

		} else {
			$sql .= " FROM " . DB_PREFIX . "prostore_blog i";
		}

		$sql .= "  LEFT JOIN " . DB_PREFIX . "prostore_blog_description id ON (i.blog_id = id.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_tag bt ON (i.blog_id = bt.blog_id)  LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store i2s ON (i.blog_id = i2s.blog_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

		}

		if (!empty($data['filtertag'])) {
				$sql .= " AND bt.tag = '" . $this->db->escape($data['filtertag']) . "'";
		}

		if (!empty($data['filter_name'])) {
				$sql .= " AND id.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= "  GROUP BY i.blog_id ORDER BY i.date_added ".$this->db->escape($data['order'] ?? '')." LIMIT ".(int)$data['start'].",".(int)$data['limit']."";

		$query = $this->db->query($sql);

		return $query->rows;
	}
	public function getBlogsTotal() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_blog  WHERE status = '1'");

		return $query->row['total'];
	}

	public function getBlogLayoutId($blog_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_blog_to_layout WHERE blog_id = '" . (int)$blog_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}
	public function isModuleSet() {
		$isSet = false;
		$query = $this->db->query("SHOW TABLES LIKE  '" . DB_PREFIX . "prostore_blog'");
		if($query->num_rows){
			$isSet = true;
		}

		return $isSet;
	}
	public function getBlogRelated($blog_id) {
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_blog_related pr LEFT JOIN " . DB_PREFIX . "prostore_blog p ON (pr.blog_id = p.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store p2s ON (p.blog_id = p2s.blog_id) WHERE pr.blog_id = '" . (int)$blog_id . "' AND p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $result['related_id'];
		}

		return $product_data;
	}

	public function getBlogRelatedProd($blog_id) {
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_blog_related_prod pr LEFT JOIN " . DB_PREFIX . "prostore_blog p ON (pr.blog_id = p.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store p2s ON (p.blog_id = p2s.blog_id) WHERE pr.blog_id = '" . (int)$blog_id . "' AND p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $result['related_id'];
		}

		return $product_data;
	}

	public function getBlogsRelated2Prod($product_id) {
		$product_blogs = array();

		$query = $this->db->query("SELECT pr.blog_id FROM " . DB_PREFIX . "prostore_blog_related_prod pr LEFT JOIN " . DB_PREFIX . "prostore_blog p ON (pr.blog_id = p.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store p2s ON (p.blog_id = p2s.blog_id) WHERE pr.related_id = '" . (int)$product_id . "' AND p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_blogs[$result['blog_id']] = $this->getBlog($result['blog_id']);
		}

		return $product_blogs;
	}

	public function getBlogsRelated2ProdCat($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_blog_related_cat pr LEFT JOIN " . DB_PREFIX . "prostore_blog p ON (pr.blog_id = p.blog_id) ";

		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_blog_description pbd ON (p.blog_id = pbd.blog_id) ";

		$sql .= "LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store p2s ON (p.blog_id = p2s.blog_id) WHERE pr.related_id = '" . (int)$data['filter_category_id'];

		$sql .= "' AND p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') ;

		$sql .= "' AND pbd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY p.date_added " . $this->db->escape($data['order']) ;

		$sql .= " LIMIT ".(int)$data['limit'] ;

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalBlogs($data = array()) {


		$sql = "SELECT COUNT(DISTINCT p.blog_id) AS total";


		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "prostorecat_blog_path cp LEFT JOIN " . DB_PREFIX . "prostore_blog_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "prostore_blog_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "prostore_blog p ON (p2c.blog_id = p.blog_id)";

		} else {
			$sql .= " FROM " . DB_PREFIX . "prostore_blog p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "prostore_blog_description pd ON (p.blog_id = pd.blog_id) LEFT JOIN " . DB_PREFIX . "prostore_blog_tag bt ON (p.blog_id = bt.blog_id)  LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store p2s ON (p.blog_id = p2s.blog_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

		}

		if (!empty($data['filtertag'])) {
				$sql .= " AND bt.tag = '" . $this->db->escape($data['filtertag']) . "'";
		}

		$query = $this->db->query($sql);

		$total = 0;

		if(isset($query->row['total'])){
			$total = $query->row['total'];
		}

		return $total;
	}
	public function getBlogsTag($data) {

		$sql = "SELECT *,COUNT(i.blog_id) as total  ";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "prostorecat_blog_path cp LEFT JOIN " . DB_PREFIX . "prostore_blog_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "prostore_blog_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "prostore_blog i ON (p2c.blog_id = i.blog_id)";

		} else {
			$sql .= " FROM " . DB_PREFIX . "prostore_blog i";
		}

		$sql .= "   LEFT JOIN " . DB_PREFIX . "prostore_blog_tag bt ON (i.blog_id = bt.blog_id)  LEFT JOIN " . DB_PREFIX . "prostore_blog_to_store i2s ON (i.blog_id = i2s.blog_id) WHERE bt.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND bt.tag IS NOT NULL AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

		}

		$sql .= " GROUP BY bt.tag ORDER BY total  DESC LIMIT ".(int)$data['start'].",".(int)$data['limit']."";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getBlogTag($blog_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_blog_tag  WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'  AND  blog_id = '" . (int)$blog_id . "'  ORDER BY id ASC");

		return $query->rows;
	}

	public function getBlogCat($blog_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prostore_blog_to_category b2c  LEFT JOIN " . DB_PREFIX . "prostorecat_blog_description cbd ON (b2c.category_id  = cbd.category_id ) WHERE cbd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND b2c.blog_id= '" . (int)$blog_id . "'");

		return $query->row;
	}

	public function getTotalReviewsByBlogId($blog_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prostore_blog_comment c LEFT JOIN " . DB_PREFIX . "prostore_blog b ON (c.blog_id = b.blog_id) WHERE b.blog_id = '" . (int)$blog_id . "'  AND c.status = '1'");

		return $query->row['total'];
	}

	protected function multiSort(&$items, $column = 'sort_order', $order = SORT_ASC) {
		if (!is_array($items)) {
			return;
		}
		$setsort  = array_column($items, $column);
		array_multisort($setsort, $order, $items);
	}

	public function getBanner($banner_id) {

		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_blog_banner bb LEFT JOIN " . DB_PREFIX . "prostore_blog_banner_image bbi ON (bb.banner_id = bbi.banner_id) ";
		$sql .= "WHERE bb.banner_id = '" . (int)$banner_id . "' AND bbi.language_id = '" . (int)$this->config->get('config_language_id') . "' ";
		$sql .= " AND bb.status=1 ORDER BY bbi.sort_order ASC";

		$query = $this->db->query($sql);
		$bannerInfo = array();
		$images = array();
		$gallery = array();
		if ($query->num_rows) {
			$this->load->model('tool/image');
			foreach ($query->rows as $banner) {
				$images[] = array(
					'title'      => $banner['title'],
					'link'       => $banner['link'],
					'thumb'      => $this->model_tool_image->resize($banner['image'], $query->row['image_thumb_width'], $query->row['image_thumb_height']),
					'image'      => $this->model_tool_image->resize($banner['image'], $query->row['image_popup_width'], $query->row['image_popup_height']),
					'sort_order' => $banner['sort_order']
				);
				$gallery[] = array(
					'src'		=>	$this->model_tool_image->resize($banner['image'], $query->row['image_popup_width'], $query->row['image_popup_height']),
					'opts'		=> array(
						'thumb'		=> $this->model_tool_image->resize($banner['image'], $query->row['image_thumb_width'], $query->row['image_thumb_height'])
					)
				);
			}

			$bannerInfo = array(
				'name'     				  => $query->row['name'],
				'image_thumb_width'       => $query->row['image_thumb_width'],
				'image_thumb_height'      => $query->row['image_thumb_height'],
				'image_popup_width' 	  => $query->row['image_popup_width'],
				'image_popup_height' 	  => $query->row['image_popup_height'],
				'template' 				  => $query->row['template'],
				'images' 				  => $images,
				'imagescount'			  => count($images),
				'gallery' 				  =>  htmlspecialchars(json_encode($gallery,JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'),
				'status' 				  => $query->row['status']
			);

		}

		return $bannerInfo;
	}

	public function getBanners4blog($blog_id) {
		$output = array();
		$sql = "SELECT * FROM " . DB_PREFIX . "prostore_blog_related_banners brb  LEFT JOIN " . DB_PREFIX . "prostore_blog_banner pbb ON (brb.related_id  = pbb.banner_id ) ";
		$sql .= "WHERE brb.blog_id = '" . (int)$blog_id . "' AND pbb.status=1";
		$query = $this->db->query($sql);

		foreach ($query->rows as $banner) {
			$output[$banner['banner_id']] = $banner;
		}

		return $output;
	}

}
