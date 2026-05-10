<?php
class ControllerCommonHeader extends Controller {

// prostore
  public function getwish() {
    if ($this->customer->isLogged() || $this->config->get('theme_prostore_wishlist')) {
		$this->load->model('account/wishlist');
		$data['wishlist_count'] = $this->model_account_wishlist->getTotalWishlistLb();
    } else {
		$data['wishlist_count'] = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
	}
    $this->response->setOutput($this->load->view('common/header_wishlist', $data));
  }
  
  public function getcompare() {
    $this->load->language('extension/theme/prostore');
    $data['compare_count'] = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;
    $this->response->setOutput($this->load->view('common/header_compare', $data));
  }

  public function createNavMobiView($data,$type = '') {
    $view = array();
    $view = $this->cache->get('navviewmobi'.$type.'.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id')) ?: [];
	if (!$this->config->get('developer_theme')) {
		$view = [];
	}
    if (!$view) {
        $orientTypes = array(
              'main_navs_v' => 'header_mobi',  // FOR VERTICAL MENU
              'main_navs' => 'header_mobi',     // FOR HORISONTAL MENU
              'header_navs' => 'header_mobi'     // FOR HORISONTAL MENU
        );
        
        $data['nav_icons'] = $this->prepareSvgIcon(); 
        $data['header_nav_icons'] = $this->prepareHeaderSvgIcon();

		if ($this->config->get('theme_prostore_footer_mobile_menu') && $this->config->get('theme_prostore_footer_mobile_menu_array')['catalog']) {
			$mobile_menu = true;
		} else {
			$mobile_menu = false;
		}

        if (!$this->config->get('theme_prostore_mobmenu_h_status')) {
            unset($orientTypes['main_navs']);
        }
        if (!$this->config->get('theme_prostore_mobmenu_v_status') || $mobile_menu) {
            unset($orientTypes['main_navs_v']);
        }

        if ($type == '_fix_menu') {
          $orientTypes = array(
                'main_navs_v' => 'header_mobi'  // FOR VERTICAL MENU
          );     
        }        

        $data['orientTypes'] = $orientTypes;
        foreach ($orientTypes as $index => $typeName) { 

            foreach($data[$index] as $key => $main_nav){
                  if(!isset($main_nav['type'][0]['links'])){
                    $data[$index][$key]['type'][0]['links'] = $data['topcat'];
                  }

                  if ($main_nav['settype'] == 1 || $main_nav['settype'] == 2) { 
                    if (isset($main_nav['type'][$main_nav['settype']])) {
                        $data[$index][$key]['type'][$main_nav['settype']] = $this->prepCustLinks($data,$main_nav['type'][$main_nav['settype']],$key);
                    }else{
                        $data[$index][$key]['type'][$main_nav['settype']] = array();
                    }
                  }
            }        
        }

        $view = $this->load->view('common/header_mobi', $data);
        $this->cache->set('navviewmobi'.$type.'.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $view);       
    }


    return $view;
  }

  public function prepareSvgIcon(){
    $outData = array();
    if ($this->config->get('theme_prostore_nav_icons') && is_array($this->config->get('theme_prostore_nav_icons'))) {
      foreach ($this->config->get('theme_prostore_nav_icons') as $id => $entityIcons) {
        foreach ($entityIcons['image'] as $type => $icons) {
          foreach ($icons as $key => $icon) {
            if (is_file(DIR_IMAGE . $icon)) {
              $outData[$id]['image'][$type][$key] = html_entity_decode(file_get_contents(DIR_IMAGE . $icon));
            }else{
              $outData[$id]['image'][$type][$key] = '';
            }
          }
        }
      }
    } 
    return $outData;     
  }


  public function prepareHeaderSvgIcon(){
    $outData = array();
    if ($this->config->get('theme_prostore_header_nav_icons') && is_array($this->config->get('theme_prostore_header_nav_icons'))) {
      foreach ($this->config->get('theme_prostore_header_nav_icons') as $id => $entityIcons) {
        foreach ($entityIcons['image'] as $key => $icon) {
            if (is_file(DIR_IMAGE . $icon)) {
              $outData[$id]['image']['h'][$key] = html_entity_decode(file_get_contents(DIR_IMAGE . $icon));
            }else{
              $outData[$id]['image'][][$key] = '';
            }
        }
      } 
    }
    return $outData;     
  }


  public function createNavView($data) {

    $view = $this->cache->get('navview.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id')) ?: []; 
	if (!$this->config->get('developer_theme')) {
		  $view = [];
	}     
    if (!$view) { 
        $view['main_navs'] = array();
        $view['main_navs_v'] = array();
        $view['header_navs'] = array();

        $orientTypes = array(
              'main_navs' => 'header_nav_',     // FOR HORISONTAL MENU
              'main_navs_v' => 'header_nav_v_',  // FOR VERTICAL MENU
              'header_navs' => 'header_top_'     // FOR HEADER MENU
        );
   
        $data['nav_icons'] = $this->prepareSvgIcon();
        $data['header_nav_icons'] = $this->prepareHeaderSvgIcon();

        foreach ($orientTypes as $index => $type) {

            foreach($data[$index] as $key => $main_nav){  
                  if(!isset($main_nav['type'][0]['links'])){
                    $data[$index][$key]['type'][0]['links'] = $data['topcat'];
                  }
                  $data['number'] = $key;
				  $data['naw_row_id'] = $main_nav['id'];

                switch ($main_nav['settype']) {
                  case '0': // Category (Mega menu)

                    

                    $data[$index][$key]['type'][0]['cattoview'] = $this->doColumns($data,$main_nav,$key); 
                    if (isset($main_nav['addelem']['settype'])) {
                      $adElSetType = $main_nav['addelem']['settype']; 
                      if ($adElSetType == 2) {
                          $adElSetType = 0;
                      }
                      if (isset($main_nav['addelem']['type'][$adElSetType]) && !empty($main_nav['addelem']['type'][$adElSetType])) {

                        $data[$index][$key]['addelem']['content'] = $this->righElemPrepare($main_nav['addelem']);

                      }
                    }


                    $view[$index][$key]['view'] = $this->load->view('common/' .  $type . 'mega', $data); 
                    if ($type == 'header_nav_v_') { 
//                      $data[$index][$key]['type'][0]['toplinks'] = $this->prepCustLinks($data,$main_nav['type'][0]['toplinks'],$key);
//                      var_dump($this->model_extension_module_prostore->getName4MenuItem('o4',$data));
                      $view[$index][$key]['view_content'] = $this->load->view('common/' .  $type . 'mega_content', $data); 
                    }         
                    break;

                  case '1': // DropDown. Custom list

                    $data[$index][$key]['type'][1] = $this->prepCustLinks($data,$main_nav['type'][1],$key);

                    $view[$index][$key]['view'] = $this->load->view('common/' .  $type . 'cust_drop', $data);

                    break;

                  case '2': // Simple custom list
                    if (isset($main_nav['type'][2])) {
                        $data[$index][$key]['type'][2] = $this->prepCustLinks($data,$main_nav['type'][2],$key);
                    }else{
                        $data[$index][$key]['type'][2] = array();
                    }

                    $view[$index][$key]['view'] = $this->load->view('common/' .  $type . 'cust', $data);

                    break;

                  case '3': // Category. DropDown.
                            
                    $view[$index][$key]['view'] = $this->load->view('common/' .  $type . 'drop', $data);

                    break;

                }

            }
            if ($index == 'main_navs_v' && !empty($view['main_navs_v'])) {
              $n = $this->config->get('theme_prostore_main_nav_v_header');
              $view['main_navs_v']['header'] = $n[$this->config->get('config_language_id')];
            }

        }

        $this->cache->set('navview.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $view);
    }
 
    return $view;
  }

  private function prepCustLinks($data,$main_nav,$key) {

          if(!isset($main_nav['links'])) { 
            $main_nav['links'] = array();
          }
         
          if (isset($main_nav['language']) && !$main_nav['language'][$data['language_id']]['href']) {
            $main_nav['language'][$data['language_id']]['href'] = '#';
          }
          foreach ($main_nav['links'] as $id => $link) {
            if (isset($data['top_links'][$id]['target'])) {
              $main_nav['target'][$id] = $data['top_links'][$id]['target'];
            }else{
              $main_nav['target'][$id] = '';
            }
            $main_nav['name'][$id] = $this->model_extension_module_prostore->getName4MenuItem($id,$data);
          }

          return $main_nav; 
  }

  private function doColumns($data,$main_nav,$key) {
          if(!isset($main_nav['type'][0]['links'])){
            $main_nav['type'][0]['links'] = array();
            if ($data['topcat']) {
              $main_nav['type'][0]['links'] = $data['topcat'];
            }
          }
         
          $catToView = array();
          foreach ($main_nav['type'][0]['links'] as $cat => $link) {
            $cat_id = substr($cat, 1); 
            if(!isset($data['categories']['categoriesls'][$cat_id])){continue;}
            $subCatsInfo = $data['categories']['categoriesls'][$cat_id];
             
            if (isset($subCatsInfo['column']) && $subCatsInfo['column']) {
              $columnNumb = $subCatsInfo['column']; 
            }else{
              $tempColNum = $this->model_catalog_category->getCategory($cat_id);
              if ($tempColNum ['column']) {
                  $columnNumb = $tempColNum ['column'];
              }else{
                  $columnNumb = 1;  //Default columnNumb
              }
            }

            $subCatNumb = $subCatsInfo['childrencount']; 
            $subCatInColumnNumb = round($subCatNumb/$columnNumb); 
            $i = 0;
            $k = 0;

            foreach ($subCatsInfo['children'] as $subCat) {
              $catToView[$cat_id]['columns'][$k][$i] = $subCat;
              $i++;
              if ($i == $subCatInColumnNumb && $k != $columnNumb-1) { 
                $i = 0;
                $k++;
              }              
            }

            $catToView[$cat_id]['width'] = 100/$columnNumb;
          }

          if (isset($main_nav['type'][0]['toplinks'])) {
            $catToView['main_toplinks'] = array();
            foreach ($main_nav['type'][0]['toplinks'] as $id => $toplink) {
              if (strpos($id,'e') === 0) {
                $fullLink = $toplink;
              }else{
                $fullLink = $this->url->link($toplink);
              }
 
              if (isset($data['top_links'][$id]['target'])) {
                $target = $data['top_links'][$id]['target'];
                $catToView['main_toplinks'][] = array(
                  'name'  => $this->model_extension_module_prostore->getName4MenuItem($id,$data),
                  'link'  => $fullLink,
                  'target'  => $target
                );
              }else{
                $catToView['main_toplinks'][] = array(
                  'name'  => $this->model_extension_module_prostore->getName4MenuItem($id,$data),
                  'link'  => $fullLink
                );
              }
            }
          }


      return $catToView;
  }

  private function righElemPrepare($elemData){ 

    $this->load->model('catalog/product');
    $this->load->model('catalog/category');
    $this->load->model('catalog/manufacturer');
    $this->load->model('tool/image');   
    $data['main_recs'] = array();
    $data['main_prods'] = array();
    $data['main_manf'] = array();
    $data['main_banners'] = array();

    if (!empty($elemData['type'][0]['banners'])) {
      $this->load->model('design/banner');
      foreach ($elemData['type'][0]['banners'] as $banner_id => $banner) {
        $bannerData = $this->model_design_banner->getBanner($banner_id);
        if (!$bannerData) {
          continue;
        }
        $images = array();
        foreach ($bannerData as $imageData) {
          $images[] = array(
            'title' => html_entity_decode($imageData['title'], ENT_QUOTES, 'UTF-8'),
            'link'  => $imageData['link'] ? $imageData['link'] : '#',
            'image' => $this->model_tool_image->resize($imageData['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_header_banner_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_header_banner_height'))
          );
        }
        $data['main_banners'][]['images'] = $images;
      }
    }

            if(!$elemData['settype']){

                $this->load->model('extension/module/prostore');

                $data['labelsinfo'] = $this->prostore->labels->getProductLabelsConfig();
                $data['language_id'] = $this->config->get('config_language_id');
				$data['cart_link'] = $this->url->link('checkout/cart');

                if (!isset($elemData['type'][0]['links'])) {
                    $elemData['type'][0]['links'] = array();
                }
                
                foreach($elemData['type'][0]['links'] as $prodId => $product){ 
                  $product_info = $this->model_catalog_product->getProduct($prodId);

                  if(empty($product_info)){ continue; }                 

                  if ($product_info['image']) { 
                    $thumb = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_main_rec_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_main_rec_height')); 
                  } else {
                    $thumb = '';
                  }
                  if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                  } else {
                    $price = false;
                  }
                  if ((float)$product_info['special']) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                  } else {
                    $special = false;
                  }

				if ($this->config->get('config_review_status')) {
					$rating = $product_info['rating'] > 0 ? number_format($product_info['rating'], 1) : 0;
				} else {
					$rating = false;
				}

				$extraImages = array();			
				if ($this->config->get('theme_prostore_images_status')) {
					$images = array_slice($this->model_catalog_product->getProductImages($product_info['product_id']), 0, (int)$this->config->get('theme_prostore_images_array'));
						foreach($images as $imageX){
						$extraImages[] = $this->model_tool_image->resize($imageX['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
					}
				}

        $productLabels = $this->prostore->labels->getLabels4Product($product_info);
        
        extract($productLabels);

				$cartProductInfo = $this->prostore->helper->getCartInfo4Product($product_info);

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $this->language->get('text_instock') . ': ' . $product_info['quantity'] . ' ' . $this->language->get('text_prostore_cart_quantity');
				} else {
					$stock = $this->language->get('text_instock');
				}	
				
				if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$buy_btn = $product_info['stock_status'];
				} else {
					$buy_btn = '';
				}
				
				if ($this->config->get('theme_prostore_manufacturer') == 1) {
					$manufacturer = $this->language->get('text_prostore_model') . ' ' . $product_info['model'];
				} elseif ($this->config->get('theme_prostore_manufacturer') == 2) {
					$manufacturer =  $this->language->get('text_prostore_manufacturer') . ' ' . $product_info['manufacturer'];
				} else {
					$manufacturer = false;
				}
				
				$wishCompareData = $this->prostore->helper->getDataWishCompare($product_info['product_id']);


                  $prodCatInfo = array();
                  $prodCat = array();
                  $prodCats = $this->model_catalog_product->getCategories($prodId); 
                  if ($prodCats) {
                    $prodCatid = array_pop($prodCats); 
                    $prodCatid = $prodCatid['category_id']; 
                    $prodCat = $this->model_catalog_category->getCategory($prodCatid); 
                    if ($prodCat) {
                        $prodCatInfo['name'] = $prodCat['name'];
                        $prodCatInfo['href'] = $this->url->link('product/category', 'category_id=' . $prodCatid);
                    }
                  }
                  $data['main_prods'][] = array(
                                'thumb'       => $thumb,
                                'name'        => $product_info['name'], 
                                'category'        => $prodCat, 
                                'price'       => $price,
                                'special'     => $special,

								'product_id'  => $product_info['product_id'],
								'manufacturer'  => $manufacturer,
								'quantity'        => $product_info['quantity'],
								'stock'        => $stock,
								'images'       => $extraImages,	
								'isnewest'       => $isnewest,
								'sales'       => $sales,
								'discount'       => $discount,
								'catch'       => $catch,
								'nocatch'       => $nocatch,
								'popular'	  => $popular,
								'hit'	 	  => $hit,
								'isincart'	  => $cartProductInfo['isincart'],
								'to_cart_quantity'	  => $cartProductInfo['to_cart_quantity'],
								'buy_btn'	  => $buy_btn,
								'reward'      => $product_info['reward'],
								'special_date_end'      => $special_date_end,
								'wish_compare_data' => $wishCompareData,	
								'image_product_width' => $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
								'image_product_height' => $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'),

                                'minimum'     => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
								'rating'      => $rating,
								'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])             
                  );
                }
            }elseif($elemData['settype'] == 2){
                if(isset($elemData['type'][0]['manf'])){
                    foreach($elemData['type'][0]['manf'] as $manfId => $product){
                      $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manfId);
					  
					  if(empty($manufacturer_info)){ continue; }

                            if ($manufacturer_info['image']) {
                                $image = $this->model_tool_image->resize($manufacturer_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_height'));
                            } else {
                                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_height'));
                            }

                      $data['main_manf'][] = array(
                                    'name'        => $manufacturer_info['name'],
                                    'href'        => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']),
                                    'image'        => $image              
                      );
                    }             
                }             
            }else{
                $data['main_html'] = html_entity_decode($elemData['type'][1]['html'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
            }   
      return $data;
  }

// prostore end
			
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

// add Open Graph by ocStore
$host = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;
if ($this->request->server['REQUEST_URI'] == '/') {
	$data['og_url'] = $this->url->link('common/home');
} else {
	$data['og_url'] = $host . substr($this->request->server['REQUEST_URI'], 1, (strlen($this->request->server['REQUEST_URI'])-1));
}
$data['og_image'] = $this->document->getOgImage();
// add Open Graph by ocStore
				

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');

 // prostore
    $data['yandex_metrika_counter'] = false;
    if ($this->config->get('analytics_yandex_metrika_counter') && $this->config->get('analytics_yandex_metrika_status')) {
      $data['yandex_metrika_counter'] = $this->config->get('analytics_yandex_metrika_counter');
    }
    $data['dataLayer_isneed'] = 1;
    if($this->config->get('analytics_google_status')){
      // If Google Analytics is enabled - we don't need new dataLayer (already contained in Google Analytics)
      $data['dataLayer_isneed'] = 0;
    }
	$data['analytics_ya_status'] = $this->config->get('theme_prostore_analytics_ya_status');
    $this->load->language('extension/theme/prostore');
    $data['text_manufacturers'] = $this->language->get('text_manufacturers');
    $data['text_loader'] = $this->language->get('text_loader');
    $data['text_ls_logged'] = sprintf($this->language->get('text_ls_logged'), $this->customer->getFirstName());
    $data['text_register_account'] = $this->language->get('text_register_account');
    $data['text_prostore_account'] = $this->language->get('text_prostore_account');
    $data['text_prostore_email'] = $this->language->get('text_prostore_email');
    $data['outdated_browser'] = $this->language->get('outdated_browser');
    $data['text_search'] = $this->language->get('text_search');
    $data['text_forgotten'] = $this->language->get('text_forgotten');
    $data['text_prostore_maintenance_phone'] = $this->language->get('text_prostore_maintenance_phone');
    $data['text_prostore_maintenance_email'] = $this->language->get('text_prostore_maintenance_email');
	$data['product_detail'] = $this->config->get('theme_prostore_product_detail');
	$data['image_main_rec_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_main_rec_width');
	$data['image_main_rec_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_main_rec_height');
	$data['image_manufacturer_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_width');
	$data['image_manufacturer_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_height');
	$data['image_header_banner_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_header_banner_width');
	$data['image_header_banner_height'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_header_banner_height');
	$data['config_open'] = html_entity_decode($this->config->get('config_open'), ENT_QUOTES, 'UTF-8');
	$this->load->model('setting/setting');
	$data['messenger_navs'] = array();
	$messenger_links = $this->model_setting_setting->getSetting('theme_prostoremeslinks');
	$data['messenger_links'] = $messenger_links['theme_prostoremeslinks_array'];
	$data['messenger_navs'] = $this->config->get('theme_prostore_messenger_nav');	
	$data['messenger_status'] = $this->config->get('theme_prostore_messenger_status');
	$data['messenger_pos'] = $this->config->get('theme_prostore_messenger_pos');
    $data['forgotten'] = $this->url->link('account/forgotten', '', true);
    $data['header_type'] = $this->config->get('theme_prostore_header_type');
    $data['footer_type'] = $this->config->get('theme_prostore_footer_type');
    $data['js_footorhead'] = $this->config->get('theme_prostore_js_footorhead');
    $data['open_graph'] = $this->config->get('theme_prostore_og');
    $data['preloader'] = $this->config->get('theme_prostore_preloader');
    $data['google_site_verification'] = $this->config->get('theme_prostore_google_site_verification');
    $data['yandex_verification'] = $this->config->get('theme_prostore_yandex_verification');
	$data['shop_email'] = $this->config->get('config_email');
    $data['text_search_placeholder'] = $this->config->get('text_search_placeholder');
    $data['prostore_phones'] = array();
    $data['prostore_phones_main'] = array();
    $prostore_phones = $this->config->get('theme_prostore_phones');

    $languages = $this->model_localisation_language->getLanguages();
    if ($prostore_phones) {
      foreach ($prostore_phones as $key => $prostore_phone) { 
        foreach ($languages as $language) {
            $prostore_phone[$language['language_id']] = html_entity_decode($prostore_phone[$language['language_id']], ENT_QUOTES, 'UTF-8');
        }
        $data['prostore_phones'][$prostore_phone['sort']] = $prostore_phone;
      }
      ksort($data['prostore_phones']);
      $data['prostore_phones_main'] = current($data['prostore_phones']);
    }

	$data['scroll_to_top'] = $this->config->get('theme_prostore_scrolltt_status');
	$data['scroll_to_top_pos'] = $this->config->get('theme_prostore_scrolltt_pos');
    $data['bootstrap'] = $this->config->get('theme_prostore_bootstrap');
    $data['custom_css'] = html_entity_decode($this->config->get('theme_prostore_css'), ENT_QUOTES, 'UTF-8');
	
	$data['theme_color'] = $this->config->get('theme_prostore_color');
	$data['fixed_header'] = $this->config->get('theme_prostore_fixed_header');
	$data['fonts'] = $this->config->get('theme_prostore_fonts');
	$data['container_width'] = $this->config->get('theme_prostore_container_width');
	$data['mobile_buttons'] = $this->config->get('theme_prostore_mobile_buttons');
	$data['mobile_menu'] = $this->config->get('theme_prostore_footer_mobile_menu');
	$data['mobile_menu_array'] = $this->config->get('theme_prostore_footer_mobile_menu_array');

	if ($data['theme_color']) {
		if ($data['theme_color'] == 'custom') {
			$data['theme_color_1'] = $this->config->get('theme_prostore_custom_color_1');
			$data['theme_color_2'] = $this->config->get('theme_prostore_custom_color_2');
		}else{
			$colors = explode(' ', $data['theme_color']);
			$data['theme_color_1'] = $colors[0];
			$data['theme_color_2'] = $colors[1];
		}
	}
	$data['theme_fc_color'] = $this->config->get('theme_prostore_color_2');
    $data['theme_fc_color_1'] = $this->config->get('theme_prostore_custom_fc_color_1');
    $data['theme_fc_color_2'] = $this->config->get('theme_prostore_custom_fc_color_2');

    $data['custom_js'] = html_entity_decode($this->config->get('theme_prostore_js'), ENT_QUOTES, 'UTF-8');
    $data['fontawesome'] = $this->config->get('theme_prostore_fontawesome');
    $data['bootstrap_ver'] = $this->config->get('theme_prostore_bootstrap_ver');
    $data['type3_logo'] = $this->config->get('theme_prostore_header_type3_logo');
    $data['type3_menu'] = $this->config->get('theme_prostore_header_type3_menu');
    $data['header_text_logo'] = html_entity_decode($this->config->get('theme_prostore_header_text_logo'), ENT_QUOTES, 'UTF-8');
	$total = $this->cart->getTotal();
	$data['text_items'] = sprintf($this->language->get('text_prostore_shopping_cart'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
    $data['text_islogged'] = sprintf($this->language->get('text_islogged'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('account/wishlist'));
    $data['text_empty_wish'] = $this->language->get('text_empty_wish');
    $data['text_prostore_wish_head'] = $this->language->get('text_prostore_wish_head');
    $data['text_prostore_comp_head'] = $this->language->get('text_prostore_comp_head');
    $data['text_empty_compare'] = $this->language->get('text_empty_compare');
    $data['text_compare_href'] = $this->url->link('product/compare');
    $data['text_wish_href'] = $this->url->link('account/wishlist');
    $data['text_prostore_show_all'] = $this->language->get('text_prostore_show_all');
    $data['text_header_category'] = $this->language->get('text_header_category');
    $data['text_prostore_information'] = $this->language->get('text_prostore_information');
    $data['text_prostore_menu'] = $this->language->get('text_prostore_menu');
    $data['text_show_more'] = $this->language->get('text_show_more');
    $data['text_prostore_back'] = $this->language->get('text_prostore_back');
    $data['text_nav_more'] = $this->language->get('text_nav_more');
    $data['text_account_title'] = $this->language->get('text_account_title');
    $data['text_account_login'] = $this->language->get('text_account_login');
    $data['text_account_register'] = $this->language->get('text_account_register');
    $data['text_account_check'] = $this->language->get('text_account_check');
    $data['text_account_submit'] = $this->language->get('text_account_submit');
    $data['text_account_password'] = $this->language->get('text_account_password');
    $data['text_header_callback'] = $this->language->get('text_header_callback');
    $data['address'] = nl2br($this->config->get('config_address'));
    $data['version'] = $this->config->get('theme_prostore_version');
    $data['language_id'] = $this->config->get('config_language_id');

    $data['top_links'] = array();
    if($this->config->get('theme_prostorelinks_array')){
      $data['top_links'] = $this->config->get('theme_prostorelinks_array');
      $data['top_links'] = $data['top_links'][$this->config->get('config_language_id')];
    }
   
    $this->load->model('extension/module/prostore');
//    $catLinks = $this->model_extension_module_prostore->createCatLinks($this->config->get('config_language_id'));
//    $data['top_links'] = array_merge ($data['top_links'],$catLinks);

    foreach($data['top_links'] as $key => $link){
      if(strpos($key,'e') === 0 ){ continue;}
      $data['top_links'][$key][key($link)] = $this->url->link(current($link));  
    } 

    $data['header_navs'] = array();
    $header_navs = $this->config->get('theme_prostore_header_nav');

    //Основное меню
    $data['main_navs'] = array();
    $main_navs = $this->config->get('theme_prostore_main_nav');
    $data['main_navs_v'] = array();
    $main_navs_v = $this->config->get('theme_prostore_main_nav_v');

        if (! function_exists('array_column')) {  // To compatibility with PHP < 5.5 
            function array_column(array $input, $columnKey, $indexKey = null) {
                $array = array();
                foreach ($input as $value) {
                    if ( !array_key_exists($columnKey, $value)) {
                        trigger_error("Key \"$columnKey\" does not exist in array");
                        return false;
                    }
                    if (is_null($indexKey)) {
                        $array[] = $value[$columnKey];
                    }
                    else {
                        if ( !array_key_exists($indexKey, $value)) {
                            trigger_error("Key \"$indexKey\" does not exist in array");
                            return false;
                        }
                        if ( ! is_scalar($value[$indexKey])) {
                            trigger_error("Key \"$indexKey\" does not contain scalar value");
                            return false;
                        }
                        $array[$value[$indexKey]] = $value[$columnKey];
                    }
                }
                return $array;
            }
        }

        $data['outdated_browser'] = $this->language->get('outdated_browser');
        $data['text_search'] = $this->language->get('text_search');
        $data['text_forgotten'] = $this->language->get('text_forgotten');
        $data['forgotten'] = $this->url->link('account/forgotten', '', true);
        $data['js_footorhead'] = $this->config->get('theme_prostore_js_footorhead');
        $data['open_graph'] = $this->config->get('theme_prostore_og');

    $navTypes = array(
          '0' => 'main_navs',     // FOR HORISONTAL MENU
          '1' => 'main_navs_v',  // FOR VERTICAL MENU
          '2' => 'header_navs'     // FOR HEADER MENU
    );


    foreach ($navTypes as $navType) {
            if(isset($$navType)){
                $data[$navType] = $this->cache->get('navview.navtypes.'.$navType.'.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'));
                if (!$this->config->get('developer_theme')) {
                  $data[$navType] = false;                     
                }
                if (!$data[$navType]) {
                    $navsort  = array_column($$navType, 'sort');
                    array_multisort($navsort, SORT_ASC, $$navType);
                    $data[$navType] = $$navType;

                    foreach($data[$navType] as $key => $main_nav){  

                            foreach($main_nav['type'] as $key1 => $typeLink){  
                                if (isset($main_nav['type'][$key1]['links'])){
                                    foreach($main_nav['type'][$key1]['links'] as $key2 => $link){ 
                                        if(strpos($key2,'e') === 0 ){ // Extralinks do not change
                                          if (isset($data['top_links'][$key2])) {
                                            $data[$navType][$key]['type'][$key1]['links'][$key2] = current($data['top_links'][$key2]);
                                          }else{
                                            $data[$navType][$key]['type'][$key1]['links'][$key2] = '';
                                          } 
                                          continue;
                                        }
                                        $data[$navType][$key]['type'][$key1]['links'][$key2] = $this->url->link($link); 
                                    }                       
                                }
                            }
                    } 
                    $this->cache->set('navview.navtypes.'.$navType.'.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $data[$navType]);

                }      
            }
    }

    //ПРАВЫЙ ЭЛЕМЕНТ ПОДМЕНЮ КАТЕГОРИИ

    $data['manufacturerlinks'] = $this->url->link('product/manufacturer');

    $this->load->language('product/compare');
    $data['compare_count'] = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;

    if ($this->customer->isLogged() || $this->config->get('theme_prostore_wishlist')) {
		$this->load->model('account/wishlist');
		$data['wishlist_count'] = $this->model_account_wishlist->getTotalWishlistLb();
    } else {
		$data['wishlist_count'] = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
	}
	
    if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_header_logo'))) {
      $data['header_logo'] = $server . 'image/' . $this->config->get('theme_prostore_header_logo');
    } else {
      $data['header_logo'] = '';
    }
    if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_fav_16'))) {
      $data['fav_16'] = $server . 'image/' . $this->config->get('theme_prostore_fav_16');
    } else {
      $data['fav_16'] = '';
    }
    if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_fav_32'))) {
      $data['fav_32'] = $server . 'image/' . $this->config->get('theme_prostore_fav_32');
    } else {
      $data['fav_32'] = '';
    }
    if (is_file(DIR_IMAGE . $this->config->get('theme_prostore_fav_180'))) {
      $data['fav_180'] = $server . 'image/' . $this->config->get('theme_prostore_fav_180');
    } else {
      $data['fav_180'] = '';
    }
    $data['callback_status'] = $this->config->get('theme_prostore_callback_status');
    $data['schema'] = $this->config->get('theme_prostore_schema');
    
    $data['max_subcat'] = $this->config->get('theme_prostore_max_subcat');
    $data['max_subcat_v'] = 4;
    if ($this->config->get('theme_prostore_max_subcat_v')) {
        $data['max_subcat_v'] = $this->config->get('theme_prostore_max_subcat_v');
    }

    // Menu
    $this->load->model('catalog/category');

    $this->load->model('catalog/product');

    $data['topcat'] = array();

    $data['categories'] = array();

    if ($this->config->get('developer_theme')) {
      $data['categories'] = $this->cache->get('category.categoriesdata.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0') ?: [];
	    $data['topcat'] = $this->cache->get('category.categoriestopcat.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0') ?: [];
    }

  if (!$data['categories']) {
    $categoriesLs = array();
    $categories = $this->model_catalog_category->getCategories(0);
      
  

    foreach ($categories as $category) {//Level 1
      if ($category['top']) {
        // Level 2
        $children_data = array();

        $children = $this->model_catalog_category->getCategories($category['category_id']);
        $L3_chid = false;// <--prostore add this
        foreach ($children as $child) {  // Level 2       
          $children2_data = array();
          $children2 = $this->model_catalog_category->getCategories($child['category_id']);
          foreach ($children2 as $child2) {    // Level 3 
//Level 4 start
            $children3_data = array();
            $children3 = $this->model_catalog_category->getCategories($child2['category_id']);               
              foreach ($children3 as $child3) { 
                $filter_data = array(
                  'filter_category_id'  => $child3['category_id'],
                  'filter_sub_category' => true
                );

                $children3_data[$child3['category_id']] = array(
                  'name'  => $child3['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                  'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child2['category_id'] . '_' . $child3['category_id'])
                );
                $categoriesLs[$child3['category_id']] = $children3_data[$child3['category_id']];
                $categoriesLs[$child3['category_id']]['childrencount'] = 0;
//                $categoriesLs[$child['category_id']]['have_L3'] = false;
              }
//Level 4 end
            $filter_data = array(
              'filter_category_id'  => $child2['category_id'],
              'filter_sub_category' => true
            );
            // Level 3
            $children2_data[$child2['category_id']] = array(
              'name'  => $child2['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
              'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child2['category_id']),
              'children' => $children3_data
            );
            $categoriesLs[$child2['category_id']] = $children2_data[$child2['category_id']];
            $categoriesLs[$child2['category_id']]['childrencount'] = count($children3_data);
            $categoriesLs[$child['category_id']]['have_L3'] = false;
          }         
                  
          $filter_data = array(
            'filter_category_id'  => $child['category_id'],
            'filter_sub_category' => true
          );
          // Level 2
          if($children2_data){$L3_chid = true;}// <--prostore add this
          
          $children_data[$child['category_id']] = array(// <--prostore change this
            'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
            'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
            'children' => $children2_data // <--prostore add this
          );

          $categoriesLs[$child['category_id']] = $children_data[$child['category_id']];
          $categoriesLs[$child['category_id']]['childrencount'] = count($children2_data);
          $categoriesLs[$child['category_id']]['have_L3'] = false;          
        }

        // Level 1
        $data['categories'][$category['category_id']] = array(// <--prostore change this
          'name'     => $category['name'],
          'column'     => $category['column'],
          'children' => $children_data,
          'have_L3' => $L3_chid,// <--prostore add this
          'column'   => $category['column'] ? $category['column'] : 1,
          'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
        );
        $tid = 'c'.$category['category_id'];
        $data['topcat'][$tid] = $this->url->link('product/category', 'path=' . $category['category_id']);// <--prostore change this
        $categoriesLs[$category['category_id']] = $data['categories'][$category['category_id']];
        $categoriesLs[$category['category_id']]['childrencount'] = count($children_data);
      }
    }

    $data['categories']['categoriesls'] = $categoriesLs;

  $this->cache->set('category.categoriesdata.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0', $data['categories']);
  $this->cache->set('category.categoriestopcat.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.0', $data['topcat']);
    }



    $data['wish_head'] = $this->load->view('common/header_wishlist', $data);
    $data['compare_head'] = $this->load->view('common/header_compare', $data); 

    if (isset($this->request->get['mobiheader'])){
/**
 * $this->request->get['mobiheader'] = '' - вывод стандартного мобильного меню
 * $this->request->get['mobiheader'] = '_fix_menu' - вывод только вертикального меню для фиксированного меню
 */

      $data['mobiview'] = $this->createNavMobiView($data,$this->request->get['mobiheader']);
    }else{
     
      $viewNav = $this->createNavView($data);

      $data['main_navs'] = $viewNav['main_navs'];
  
      $data['main_navs_v'] = $viewNav['main_navs_v'];
  
      $data['header_navs'] = $viewNav['header_navs'];
    }

	$data['adult_banner'] = '';
	if ($this->config->get('theme_prostore_18_status') && !isset($_COOKIE['899_is_adult'])) { 
		$data['text_prostore_adult_warn'] = $this->config->get('theme_prostore_text_18'.$this->config->get('config_language_id'));
		$data['text_prostore_adult_yes'] = $this->config->get('theme_prostore_btn2_18'.$this->config->get('config_language_id'));
		$data['text_prostore_adult_no'] = $this->config->get('theme_prostore_btn1_18'.$this->config->get('config_language_id'));
		$data['text_prostore_adult_title'] = $this->config->get('theme_prostore_title_18'.$this->config->get('config_language_id'));
		$data['adult_banner'] = $this->load->view('common/header_adult_banner', $data);
	}

//prostore end  
			
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		
// prostore
    $data['callback'] = $this->load->controller('extension/module/callback');  
	
	$data['header_checkout'] = false;
	if (isset($this->request->get['route']) && $this->request->get['route'] == 'checkout/checkout') {
		$data['header_view'] = $this->load->view('common/header_checkout', $data);
		$data['header_checkout'] = true;
	} else {
		$data['header_view'] = $this->load->view('common/header_' . $data['header_type'], $data);
	}
	
	$data['maintenance'] = false;
	if ($this->config->get('config_maintenance')) {
		$this->user = new Cart\User($this->registry);
		if (!$this->user->isLogged()) {
			$data['header_view'] = $this->load->view('common/header_maintenance', $data);
			$data['maintenance'] = true;
		}
	}
    if (isset($this->request->get['mobiheader'])){
      $this->response->setOutput($data['mobiview']);
    }else{
      return $this->load->view('common/header', $data);
    }
// prostore end 
			
	}
}
