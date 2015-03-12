<?php

namespace Jacere\Skhema;

class Filter {

	private $m_name;
	private $m_options;

	public function __construct($name, $options) {
		$this->m_name = $name;
		$this->m_options = $options;
	}

	public function name() {
		return $this->m_name;
	}

	public function evaluate(TemplateManager $manager, $context, $value) {
		$filter = $manager->filter($this->m_name);
		if (!$filter) {
			throw new \Exception("Unknown filter `$this->m_name`");
		}
		return $filter($this->m_options, $context, $value);
	}

	public function __toString() {
		$str = $this->m_name;

		$options = $this->m_options;
		if ($options && count($options)) {
			$parts = [];
			foreach ($options as $key => $val) {
				$parts[] = is_string($val) ? "$key=$val" : $key;
			}
			$str .= sprintf('[%s]', implode(',', $parts));
		}
		return $str;
	}
}