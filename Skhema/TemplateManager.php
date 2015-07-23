<?php

namespace Jacere\Skhema;

use Jacere\Bramble\Core\Cache\Cache;

class TemplateManager {

	/** @var Template[] */
	private $m_templates;
	private $m_filters;
	private $m_path;

    /**
     * @param string $dir
     * @param bool $rebuild
     * @throws \Exception
     */
	private function __construct($dir, $rebuild) {
		$this->m_filters = [];
		$this->m_path = str_replace("\\", '', $dir);

		$key = 'templates_'.str_replace(['-','/',' '], '_', preg_replace('`[^a-zA-Z0-9_\-/ ]+`', '', $this->m_path));
		
		$this->m_templates = Cache::load($key, function() use($dir) {
			return TemplateGenerator::generate($dir);
		}, $rebuild);
		
		$this->register('iteration', [self::class, '_filter_iteration']);
		$this->register('cycle', [self::class, '_filter_cycle']);
		$this->register('first', [self::class, '_filter_first']);
	}

	/**
	 * @param string $dir
	 * @param bool $rebuild
	 * @return TemplateManager
	 */
	public static function create($dir, $rebuild = false) {
		return new TemplateManager($dir, $rebuild);
	}

	/**
	 * @param string $name
	 * @param callable $filter
	 * @throws \Exception
	 */
	public function register($name, callable $filter) {
		if (isset($this->m_filters[$name])) {
			throw new \Exception("Duplicate registration of function `$name`");
		}
		$this->m_filters[$name] = $filter;
	}

    /**
     * @param string $name
     * @return callable
     */
	public function filter($name) {
		return isset($this->m_filters[$name]) ? $this->m_filters[$name] : NULL;
	}

	/**
	 * @param $name
	 * @return Template
	 */
	public function template($name) {
		return isset($this->m_templates[$name]) ? $this->m_templates[$name] : NULL;
	}

	/**
	 * @param string $name
	 * @param array $source Binding source
	 * @return string
	 * @throws \Exception
	 */
	public function evaluate($name, array $source = NULL) {
		if (!isset($this->m_templates[$name])) {
			throw new \Exception("Template `$name` is not defined`");
		}
		
		ob_start();
		
		$template = $this->m_templates[$name];
		$template->evaluate($this, $source);
		
		$output = ob_get_contents();
		ob_end_clean();
		
		return trim($output);
	}

	public static function _filter_iteration($options, $context) {
		return $context['__iteration'];
	}

	public static function _filter_cycle($options, $context) {
		$keys = array_keys($options);
		return $keys[$context['__iteration'] % count($keys)];
	}

	public static function _filter_first($options, $context) {
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

		throw new \Exception(sprintf('Invalid source for function.'));
	}
}
