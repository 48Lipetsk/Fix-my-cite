<?php // ==========================================  seo_url.php v.140618 opencart-russia.ru ===============================
class ControllerStartupSeoUrl extends Controller {
	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
      // OCFilter start
      if ($this->registry->has('ocfilter')) {
  			$this->url->addRewrite($this->ocfilter);
  		}
      // OCFilter end
      
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}


//prostore start
					if ($url[0] == 'prostorecatblog_id') { 
						$this->request->get['lbpath'] = $url[1];
					}

					if ($url[0] == 'news_id') {
						$this->request->get['news_id'] = $url[1];
					}

					if ($url[0] == 'blog_id') {
						$this->request->get['blog_id'] = $url[1];
					}
//prostore end
			
					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'blog_id' && $url[0] != 'news_id' && $url[0] != 'prostorecatblog_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';

				} elseif (isset($this->request->get['news_id'])) {//prostore
					$this->request->get['route'] = 'extension/module/prostore_news/getnews';//prostore
				} elseif (isset($this->request->get['blog_id'])) {//prostore
					unset($this->request->get['lbpath']);
					$this->request->get['route'] = 'extension/module/prostore_blog/getblog';//prostore
				} elseif (isset($this->request->get['lbpath'])) {//prostore

					$this->request->get['route'] = 'extension/module/prostorecat_blog/getcat';//prostore
			
				}
			}
		// Redirect 301   
		} elseif (isset($this->request->get['route']) && empty($this->request->post) && !isset($this->request->get['token']) && $this->config->get('config_seo_url')) {
			$arg = '';
			$cat_path = false;
			$route = $this->request->get['route'];

			if ($this->request->get['route'] == 'product/product' && isset($this->request->get['product_id'])) {
				$route = 'product_id=' . (int)$this->request->get['product_id'];
			} elseif ($this->request->get['route'] == 'product/category' && isset($this->request->get['path'])) {
				$categorys_id = explode('_', $this->request->get['path']);
				$cat_path = '';
				foreach ($categorys_id as $category_id) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'category_id=" . (int)$category_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");   
					if ($query->num_rows && $query->row['keyword'] /**/ ) {
						$cat_path .= '/' . $query->row['keyword'];
					} else {
						$cat_path = false;
						break;
					}
				}
				$arg = trim($cat_path, '/');
				if (isset($this->request->get['page'])) $arg = $arg . '?page=' . (int)$this->request->get['page'];
			} elseif ($this->request->get['route'] == 'product/manufacturer/info' && isset($this->request->get['manufacturer_id'])) {
				$route = 'manufacturer_id=' . (int)$this->request->get['manufacturer_id'];
				if (isset($this->request->get['page'])) $arg = $arg . '?page=' . (int)$this->request->get['page'];
			} elseif ($this->request->get['route'] == 'information/information' && isset($this->request->get['information_id'])) {
				$route = 'information_id=' . (int)$this->request->get['information_id'];

			//prostore add this. For RS
			} elseif ($this->request->get['route'] == 'extension/module/prostore_blog/getblog' && isset($this->request->get['blog_id'])) {
				$route = 'blog_id=' . (int)$this->request->get['blog_id'];
			} elseif ($this->request->get['route'] == 'extension/module/prostorecat_blog/getcat' && isset($this->request->get['lbpath'])) {
				$route = 'prostorecatblog_id=' . (int)$this->request->get['lbpath'];				
			//prostore add this. For RS
			
			} elseif (sizeof($this->request->get) > 1) {
				$args = '?' . str_replace("route=" . $this->request->get['route'].'&amp;', "", $this->request->server['QUERY_STRING']);
				$arg = str_replace('&amp;', '&', $args);
			}

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($route) . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

			if (!empty($query->num_rows) && !empty($query->row['keyword']) && $route) {
				$this->response->redirect($query->row['keyword'] . $arg, 301);
			} elseif ($cat_path) {
				$this->response->redirect($arg, 301);
			} elseif ($this->request->get['route'] == 'common/home') {
				$this->response->redirect(HTTP_SERVER . $arg, 301);
			}
		}
	}


//prostore start
	public function getPathByBlog($blog_id) {
		$blog_id = (int)$blog_id;
		if ($blog_id < 1) return false;

		static $path = null;
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "prostore_blog_to_category WHERE blog_id = '" . (int)$blog_id . "' ORDER BY main_category DESC LIMIT 1");

		$path[$blog_id] = $this->getPathByCategoryLb($query->num_rows ? (int)$query->row['category_id'] : 0);

		return $path[$blog_id];
	}

	private function getPathByCategoryLb($category_id) {
		$category_id = (int)$category_id;
		if ($category_id < 1) return false;

		static $path = null;

			$max_level = 10;

			$sql = "SELECT CONCAT_WS('_'";
			for ($i = $max_level-1; $i >= 0; --$i) {
				$sql .= ",t$i.category_id";
			}
			$sql .= ") AS path FROM " . DB_PREFIX . "prostorecat_blog t0";
			for ($i = 1; $i < $max_level; ++$i) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "prostorecat_blog t$i ON (t$i.category_id = t" . ($i-1) . ".parent_id)";
			}
			$sql .= " WHERE t0.category_id = '" . (int)$category_id . "'";

			$query = $this->db->query($sql);

			$path[$category_id] = $query->num_rows ? $query->row['path'] : false;

		return $path[$category_id];
	}
//prostore end
			
	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || ($data['route'] == 'extension/module/prostore_news/getnews' && $key == 'news_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}

//prostore start
				} elseif ($data['route'] == 'extension/module/prostore_blog/getblog' && $key == 'blog_id') {
					if ($this->config->get('config_seo_pro') === null) {  // Check - This section for Russian build OCStore only

						if ($this->config->get('theme_prostore_blog_path') ) { 
							$catlessblog = false;
							$categories = $this->getPathByBlog($value); 
							if (!$categories) $catlessblog = true;
							if ($catlessblog) {
								$categories = 0;
							}
							$categories = explode('_', $categories);
							if ($categories[0]) {
								array_unshift($categories, "0");
							}

							foreach ($categories as $category) {
								$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'prostorecatblog_id=" . (int)$category . "'");

								if ($query->num_rows && $query->row['keyword']) {
									$url .= '/' . $query->row['keyword'];
								} else {
									$url = null;

									break;
								}
							}

						}
					}
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}

				} elseif ($key == 'lbpath') {


					$categories = explode('_', $value);
					if ($categories[0]) {
						array_unshift($categories, "0");
					}

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'prostorecatblog_id=" . (int)$category . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = null;

							break;
						}
					}

					unset($data[$key]);	
				}elseif (($data['route'] == 'extension/module/prostore_news/getnewslist')) {	
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($value) . "'");

					if ($query->num_rows && $query->row['keyword'] && (strpos(urldecode($url), $query->row['keyword']) === false)) {
						//&& (strpos($url, $query->row['keyword']) === false) - uses for new version SEO-PRO to avoid doubling
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}				

//prostore end
			
				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				} elseif ($key == 'route') {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($data['route']) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
					if ($query->num_rows) /**/ {
						$url .= '/' . $query->row['keyword'];
					}
				}
			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}
