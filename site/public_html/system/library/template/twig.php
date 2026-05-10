<?php
namespace Template;
final class Twig {
	private $data = array();

	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function render($filename, $code = '') {
		if (!$code) {
			$file = DIR_TEMPLATE . $filename . '.twig';

			if (is_file($file)) {
				$code = file_get_contents($file);
			} else {
				throw new \Exception('Error: Could not load template ' . $file . '!');
				exit();
			}
		}

		// initialize Twig environment
		$config = array(
			'autoescape'  => false,
			'debug'       => false,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/'
		);

		try {
			// ArrayLoader — for the current template content passed as $code
			$arrayLoader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));

			// FilesystemLoader — resolves {% include %} directives from theme files
			// This is the FIX for ocfilter (and any other module using {% include %})
			$paths = array();

			if (defined('DIR_TEMPLATE')) {
				$paths[] = DIR_TEMPLATE;
			}

			if (defined('DIR_MODIFICATION') && is_dir(DIR_MODIFICATION . 'catalog/view/theme/')) {
				$paths[] = DIR_MODIFICATION . 'catalog/view/theme/';
			}

			$filesystemLoader = new \Twig\Loader\FilesystemLoader($paths);

			// ChainLoader tries ArrayLoader first, then FilesystemLoader
			$loader = new \Twig\Loader\ChainLoader(array($arrayLoader, $filesystemLoader));

			$twig = new \Twig\Environment($loader, $config);

			return $twig->render($filename . '.twig', $this->data);
		} catch (\Exception $e) {
			trigger_error('Error: Could not load template ' . $filename . '!');
			exit();
		}	
	}	
}
