<?php

namespace Jacere\Skhema;

use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class NameToken implements IToken, IPhpSerializable {
	
	protected $m_type;
	protected $m_name;

    /**
     * @param int $type
     * @param string $name
     */
	public function __construct($type, $name) {
		$this->m_type = $type;
		$this->m_name = $name;
	}

	public function name() {
		return $this->m_name;
	}
	
	public function type() {
		return $this->m_type;
	}
	
	public function anonymous() {
		return (strncmp($this->m_name, TokenType::T_ANONYMOUS_TEMPLATE_PREFIX, strlen(TokenType::T_ANONYMOUS_TEMPLATE_PREFIX)) === 0);
	}
	
	public function __toString() {
		return $this->m_name;
	}

	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [
			$this->m_type,
			$this->m_name,
		]);
	}
}
