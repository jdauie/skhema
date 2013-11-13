<?php

namespace Jacere;

class TemplateManager {
	
	const TEMPLATE_EXT = 'tpl';
	const CACHE_FORMAT = '_%s.cache';
	
	private static $c_manager;
	
	private $m_templates;
	private $m_cache;
	
	function __construct($dir, $forceUpdate) {
		$this->m_cache = $dir.'/'.sprintf(self::CACHE_FORMAT, self::TEMPLATE_EXT);
		
		if ($forceUpdate || !$this->Deserialize()) {
			$this->m_templates = TemplateGenerator::Create($dir);
		}
		self::$c_manager = $this;
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
		$path = $this->m_dir.'/'.$this->m_cache;
		if (file_exists($path)) {
			if (true) {
				$this->m_templates = unserialize(file_get_contents($path));
			}
			else {
				include($path.'.php');
				$this->m_templates = \Jacere\TemplateCache\DeserializeCachedTemplates();
			}
			return true;
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
