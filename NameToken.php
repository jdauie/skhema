<?php

namespace Jacere\Skhema;

class NameToken implements IToken {
	
	protected $m_type;
	protected $m_name;
	
	function __construct($type, $name) {
		$this->m_type = $type;
		$this->m_name = $name;
	}
	
	public function GetName() {
		return $this->m_name;
	}
	
	public function GetType() {
		return $this->m_type;
	}
	
	public function IsAnonymous() {
		return (strncmp($this->m_name, TokenType::T_ANONYMOUS_TEMPLATE_PREFIX, strlen(TokenType::T_ANONYMOUS_TEMPLATE_PREFIX)) === 0);
	}
	
	public function __toString() {
		return '{'.TokenType::GetTokenTypeDef($this->m_type)->Symbol.$this->m_name.'}';
	}
}
