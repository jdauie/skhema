<?php

namespace Jacere;

require_once(__dir__.'/Util.php');

class TemplateManager {
	
	const CACHE_MARKER = 'SKMA';
	const CACHE_VERSION = 1;
	const CACHE_VERSION_CHARS = 4;
	
	const TEMPLATE_EXT = 'tpl';
	const CACHE_FORMAT = '_%s.cache';
	
	const CACHE_MODE_PHP = 1; //(1 << 0);
	const CACHE_MODE_STD = 2; //(1 << 1);
	const CACHE_MODE_STD_GZIP = 6; //CACHE_MODE_STD | (1 << 2);
	
	private static $c_manager;
	
	private $m_templates;
	private $m_cache;
	private $m_mode;
	
	function __construct($dir, $forceUpdate) {
		$this->m_cache = $dir.'/'.sprintf(self::CACHE_FORMAT, self::TEMPLATE_EXT);
		$this->m_mode = self::CACHE_MODE_STD;
		
		self::$c_manager = $this;
		
		if ($forceUpdate || !$this->Deserialize()) {
			require_once(__dir__.'/TemplateGenerator.php');
			TemplateGenerator::Create($dir, $this->m_mode, $this->m_templates);
		}
	}
	
	public static function Create($dir, $forceUpdate = false) {
		$manager = new TemplateManager($dir, $forceUpdate);
		return $manager;
	}
	
	public static function GetTemplate($name) {
		if (isset(self::$c_manager->m_templates[$name])) {
			return self::$c_manager->m_templates[$name];
		}
		return NULL;
	}
	
	private function Deserialize() {
		// todo: check if cache is valid (exists, version, ...?)
		if (($this->m_mode | self::CACHE_MODE_STD) !== 0) {
			$path = $this->m_cache;
			if (file_exists($path)) {
				$data = file_get_contents($path);
				if (StartsWith($data, self::CACHE_MARKER) && intval(substr($data, strlen(self::CACHE_MARKER), self::CACHE_VERSION_CHARS)) === self::CACHE_VERSION) {
					$data = substr($data, strlen(self::CACHE_MARKER) + self::CACHE_VERSION_CHARS);
					if ($this->m_mode === self::CACHE_MODE_STD_GZIP) {
						$data = gzdecode($data);
					}
					$this->m_templates = unserialize($data);
					if ($this->m_templates) {
						return true;
					}
				}
			}
		}
		else if (($this->m_mode | self::CACHE_MODE_PHP) !== 0) {
			$path = $this->m_cache.'.php';
			if (file_exists($path)) {
				require_once($path);
				$this->m_templates = \Jacere\TemplateCache\DeserializeCachedTemplates();
			}
		}
		else {
			die('Invalid cache mode');
		}
		return false;
	}
	
	public function Evaluate($name, $source) {
		if (!isset($this->m_templates[$name])) {
			die('Template does not exist');
		}
		
		ob_start();
		
		$template = $this->m_templates[$name];
		$template->Evaluate($source);
		
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
}
?>
