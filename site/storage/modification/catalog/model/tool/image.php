<?php
class ModelToolImage extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);


	// prostore
		if($extension == 'svg') {
            if ($this->request->server['HTTPS']) {
                return HTTPS_SERVER . 'image/' . $filename;
            } else {
                return HTTP_SERVER . 'image/' . $filename;
            }
    	}
	// prostore end
			
		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

//WebP Images, author agatha65 agatha65.com, link www.opencart.com/index.php?route=marketplace/extension/info&extension_id=38025
//"This extension is based on a similar one with another library by my colleague from stick-design.ru"
//"All credits goes to V. Bulgakov"
//WebP Images start
$image_new_webp = 'cachewebp/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.webp';
//WebP Images end
			

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) { 
				if ($this->request->server['HTTPS']) {
					return $this->config->get('config_ssl') . 'image/' . $image_old;
 				} else {
					return $this->config->get('config_url') . 'image/' . $image_old;
				}
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		

//WebP Images start
		$gd = gd_info();
		if ($gd['WebP Support']) {
			if (!is_file(DIR_IMAGE . $image_new_webp) || (filectime(DIR_IMAGE . $image_new) > filectime(DIR_IMAGE . $image_new_webp))) {
									
				$path = '';

				$directories = explode('/', dirname($image_new_webp));

				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;

					if (!is_dir(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}
				
				$image_webp = new Image(DIR_IMAGE . $image_old);
				$image_webp->resize($width, $height);
				$image_webp->save_webp(DIR_IMAGE . $image_new_webp);
			}
		}
//WebP Images end
			
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_old;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_old;
		}
	}
}
