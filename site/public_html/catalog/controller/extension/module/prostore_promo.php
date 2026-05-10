<?php

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


class ControllerExtensionModuleProstorePromo extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$data['lazyload'] = $this->config->get('theme_prostore_lazyload');

		$data['banners'] = array();
		$images = array();

		if (isset($setting['promo_column'])) {
			$grid_template_columns = ''; // 58.33% 41.66%
			$grid_template_areas = array();
			$areaNumber =1;
			$maxRows = 1;

			$promo_columns = $setting['promo_column'];
			foreach ($promo_columns as $key => $promo_column) {
				$promo_columns[$key]['sort_order'] = 0;
				if (isset($promo_column['language'][$this->config->get('config_language_id')]['column_sort_order'])) {
					$promo_columns[$key]['sort_order'] = $promo_column['language'][$this->config->get('config_language_id')]['column_sort_order'];
					$this->sortSubImages($promo_columns[$key]['promo_image']);
				}
			}
			$setsort  = array_column($promo_columns, 'sort_order');
			array_multisort($setsort, SORT_ASC, $promo_columns);


			foreach ($promo_columns as $key => $promo_column) {
				$images = array();
				if(isset($promo_column['promo_image'])){
					foreach ($promo_column['promo_image'] as $keyImage => $result) {
						if(!isset($result['language'][$this->config->get('config_language_id')])){ continue; }
						$result = $result['language'][$this->config->get('config_language_id')];

						if ($result['width']) {
							$width = $result['width'];
						}else{
							$width =  $this->config->get('theme_prostore_image_category_width');
						}

						if ($result['height']) {
							$height = $result['height'];
						}else{
							$height =  $this->config->get('theme_prostore_image_category_height');
						}

						if (is_file(DIR_IMAGE . $result['image'])) {
							$images[(int)$result['sort_order']] = array(
							'title' 			=> html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
							'link'  			=> $result['link'],
							'text_big' 		    => html_entity_decode($result['text_big'], ENT_QUOTES, 'UTF-8'),
							'text_small'  		=> html_entity_decode($result['text_small'], ENT_QUOTES, 'UTF-8'),
							'width'  		    => $width,
							'height'  		    => $height,
							'position'      	=> $result['text_position'],
							'color' 	        => $result['text_color'],
							'image' 			=> $this->model_tool_image->resize($result['image'], $width, $height)
							);

							$grid_template_areas[$keyImage][$key] = 'area_0'.$areaNumber;
							if ($keyImage > $maxRows) {
								$maxRows = $keyImage;
							}
							$areaNumber ++;
						}
					}
				}
				$data['banners'][$key]['images'] = $images;

				$data['banners'][$key]['sort_order'] = 0;
				if (isset($promo_column["language"][$this->config->get('config_language_id')]["column_sort_order"])) {
					$data['banners'][$key]['sort_order'] = $promo_column["language"][$this->config->get('config_language_id')]["column_sort_order"];
				}
				if (isset($promo_column['language'][$this->config->get('config_language_id')])){
					$data['banners'][$key]['column_width'] = $promo_column["language"][$this->config->get('config_language_id')]["column_width"];
					$grid_template_columns .= ($data['banners'][$key]['column_width']*100/12).'% ';
				}
			}
		}

// Подготовка данных для CSS grid-template

		// Каждая строка массива должна иметь одинаковое количество колонок.
		// Дополняем значением из нулевой строки если это не так.
		foreach ($grid_template_areas as $keyRow => $row) {
			foreach ($grid_template_areas[0] as $keyCol => $column) {
				if (!isset($row[$keyCol])) {
					if (isset($grid_template_areas[$keyRow-1][$keyCol])) {
						$grid_template_areas[$keyRow][$keyCol] = $grid_template_areas[$keyRow-1][$keyCol];
						$grid_template_areas[$keyRow-1][$keyCol] = $grid_template_areas[0][$keyCol];
					} else {
						$grid_template_areas[$keyRow][$keyCol] = $column;
					}
				}
			}
			ksort($grid_template_areas[$keyRow]);
		}

		$grid_template_areas_str = ''; // "area_01 area_02" "area_01 area_03"
		$grid_template_rows_str = '';  // 1fr 1fr
		foreach ($grid_template_areas as $key => $row) {
			$grid_template_areas_str .= '"'.implode(' ',$row).'"';
			$grid_template_rows_str .= '1fr ';
		}

		$data['grid_data'] = array(
			'grid-template-areas' => $grid_template_areas_str,
			'grid-template-columns'	=> $grid_template_columns,
			'grid-template-rows' => $grid_template_rows_str
		);


		$data['module'] = $module++;

		return $this->load->view('extension/module/prostore_promo', $data);

	}

	protected function sortSubImages(&$images) {
		if (!is_array($images) || count($images) < 2) {
			return;
		}
		foreach ($images as $key => $image) {
			$images[$key]['sort_order'] = 0;
			if (isset($image['language'][$this->config->get('config_language_id')]['sort_order'])) {
				$images[$key]['sort_order'] = $image['language'][$this->config->get('config_language_id')]['sort_order'];
			}
		}
		$setsort  = array_column($images, 'sort_order');
		array_multisort($setsort, SORT_ASC, $images);
	}

}
