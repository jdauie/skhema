<?php

namespace Jacere\Skhema;

class TemplateManager {
	
	const CACHE_MARKER = 'SKMA';
	const CACHE_VERSION = 1;
	const CACHE_VERSION_CHARS = 4;
	
	const TEMPLATE_EXT = 'tpl';
	const CACHE_FORMAT = '.%s.%d.cache%s';
	
	const CACHE_MODE_PHP = 1; //(1 << 0);
	const CACHE_MODE_STD = 2; //(1 << 1);
	const CACHE_MODE_STD_GZIP = 6; //CACHE_MODE_STD | (1 << 2);
	
	private static $c_manager;
	private static $c_functions;
	
	private $m_templates;
	private $m_cache;
	private $m_mode;
	
	function __construct($dir, $forceUpdate, $cacheMode) {
		$extensions = [
			self::CACHE_MODE_PHP => '.php',
			self::CACHE_MODE_STD_GZIP => '.gz',
		];
		
		$ext = '';
		if (isset($extensions[$cacheMode])) {
			$ext = $extensions[$cacheMode];
		}
		
		$this->m_cache = $dir.'/'.sprintf(self::CACHE_FORMAT, self::TEMPLATE_EXT, self::CACHE_VERSION, $ext);
		$this->m_mode = $cacheMode;
		
		self::$c_manager = $this;
		
		if ($forceUpdate || !$this->Deserialize()) {
			require_once(__dir__.'/TemplateGenerator.php');
			TemplateGenerator::Create($dir, $this->m_mode, $this->m_cache, $this->m_templates);
		}
		
		self::RegisterFunction('iteration',
			$function = function($options, $context) {
				return $context['__iteration'];
			}
		);
		self::RegisterFunction('cycle',
			$function = function($options, $context) {
				$keys = array_keys($options);
				return $keys[$context['__iteration'] % count($keys)];
			}
		);
		self::RegisterFunction('first',
			$function = function($options, $context) {
				if (count($options) !== 1) {
					throw new \Exception('Invalid option for function; only one option allowed.');
				}
				reset($options);
				$scope = explode('/', key($options));
				if (count($scope) !== 2) {
					throw new \Exception('Invalid option for function; too many parts.');
				}
				
				if (isset($context[$scope[0]])) {
					$scope_source = $context[$scope[0]];
					if (count($scope_source)) {
						$scope_source_first = $scope_source[0];
						if (isset($scope_source_first[$scope[1]])) {
							return $scope_source_first[$scope[1]];
						}
					}
				}
				
				throw new \Exception(sprintf('Invalid source for function "%s".', $childName));
			}
		);
	}
	
	public static function Create($dir, $forceUpdate = false, $cacheMode = NULL) {
		$cache_mode_map = [
			'php' => self::CACHE_MODE_PHP,
			'default' => self::CACHE_MODE_STD,
			'default-gzip' => self::CACHE_MODE_STD_GZIP,
		];
		$cacheMode = (isset($cache_mode_map[$cacheMode]) ? $cache_mode_map[$cacheMode] : self::CACHE_MODE_STD);
		$manager = new TemplateManager($dir, $forceUpdate, $cacheMode);
		return $manager;
	}
	
	public static function RegisterFunction($name, $function) {
		if (self::$c_functions === NULL) {
			self::$c_functions = [];
		}
		if (isset(self::$c_functions[$name])) {
			throw new \Exception(sprintf('Duplicate registration of function "%s".', $name));
		}
		self::$c_functions[$name] = $function;
	}
	
	public static function GetFunction($name) {
		if (isset(self::$c_functions[$name])) {
			return self::$c_functions[$name];
		}
		return NULL;
	}
	
	public static function GetTemplate($name) {
		if (isset(self::$c_manager->m_templates[$name])) {
			return self::$c_manager->m_templates[$name];
		}
		return NULL;
	}
	
	public function GetTemplateNames() {
		return array_keys($this->m_templates);
	}
	
	private function Deserialize() {
		if (file_exists($this->m_cache)) {
			if (($this->m_mode & self::CACHE_MODE_STD) !== 0) {
				$data = file_get_contents($this->m_cache);
				if ($this->m_mode === self::CACHE_MODE_STD_GZIP) {
					$data = gzdecode($data);
				}
				$this->m_templates = unserialize($data);
			}
			else if (($this->m_mode & self::CACHE_MODE_PHP) !== 0) {
				$this->m_templates = require_once($this->m_cache);
			}
			else {
				die('Invalid cache mode');
			}
		}
		return ($this->m_templates);
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
		
		return trim($output);
	}
}
