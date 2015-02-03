<?php

namespace Jacere\Skhema;

class EvalNameToken extends NameToken {
	
	private $m_functions;
	
	function __construct($type, $name) {
		$name = TokenType::ParseName($name, $this->m_functions, ($type === TokenType::T_FUNCTION));
		parent::__construct($type, $name);
	}
	
	public function GetFunctionNames() {
		$result = [];
		if ($this->m_functions) {
			foreach ($this->m_functions as $function) {
				$result[] = $function['name'];
			}
		}
		return $result;
	}
	
	public function GetSerializedName() {
		$value = '';
		if ($this->m_type === TokenType::T_VARIABLE) {
			$value = $this->m_name;
		}
		
		if ($this->m_functions) {
			foreach ($this->m_functions as $function) {
				if (!empty($value)) {
					$value .= ':';
				}
				$value .= $function['name'];
				
				$options = $function['options'];
				if ($options && count($options)) {
					$options_parts = '';
					foreach ($options as $option_key => $option_val) {
						$options_str = $option_key;
						if (is_string($option_val)) {
							$options_str .= sprintf('=%s', $option_val);
						}
						$options_parts[] = $options_str;
					}
					$value .= sprintf('[%s]', implode(',', $options_parts));
				}
			}
		}
		return $value;
	}
	
	public function Evaluate($context) {
		$value = NULL;
		if ($this->m_type === TokenType::T_VARIABLE) {
			if ($context != NULL && isset($context[$this->m_name])) {
				$value = $context[$this->m_name];
			}
			else {
				//die('Undefined variable: '.$this->m_name);
			}
		}
		
		if ($this->m_functions) {
			foreach ($this->m_functions as $function_info) {
				$name = $function_info['name'];
				$options = $function_info['options'];
				
				$function = TemplateManager::GetFunction($name);
				if (!$function) {
					throw new \Exception(sprintf('Unknown function "%s".', $name));
				}
				$value = $function($options, $context, $value);
			}
		}
		return $value;
	}
}
