<?php

namespace Jacere\Skhema;

class TokenDef {
	
	public $Type;
	public $Symbol;
	public $SelfClosing;
	
	public function __construct($type, $symbol, $selfClosing) {
		$this->Type = $type;
		$this->Symbol = $symbol;
		$this->SelfClosing = $selfClosing;
	}

	public function __toString() {
		return $this->Symbol;
	}
}
