<?php

namespace Jacere\Skhema;

use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class EvalNameToken extends NameToken implements IPhpSerializable {

	/** @var Filter[] */
	private $m_filters;

    /**
     * @param int $type
     * @param string $name
     */
	public function __construct($type, $name) {
		$name = TokenType::ParseName($name, $this->m_filters, ($type === TokenType::T_FILTER));
		parent::__construct($type, $name);
	}

	/**
	 * @return string[]
	 */
	public function filters() {
		return $this->m_filters ? array_map(function(Filter $a) {return $a->name();}, $this->m_filters) : [];
	}
	
	public function GetSerializedName() {
		$parts = [];
		if ($this->m_type === TokenType::T_VARIABLE) {
			$parts[] = $this->m_name;
		}
		
		if ($this->m_filters) {
			$parts = array_merge($parts, array_map('strval', $this->m_filters));
		}
		return implode(':', $parts);
	}
	
	public function evaluate(TemplateManager $manager, array $context = NULL) {
		$value = NULL;
		if ($this->m_type === TokenType::T_VARIABLE) {
			if ($context !== NULL && isset($context[$this->m_name])) {
				$value = $context[$this->m_name];
			}
			else {
				//die('Undefined variable: '.$this->m_name);
			}
		}
		
		if ($this->m_filters) {
			foreach ($this->m_filters as $filter) {
				$value = $filter->evaluate($manager, $context, $value);
			}
		}
		return $value;
	}

	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [
			$this->m_type,
			$this->GetSerializedName(),
		]);
	}
}
