<?php

namespace Jacere\Skhema;

class TokenDef {
	
	public $Type;
	public $Symbol;
	public $SelfClosing;
	
	function __construct($type, $symbol, $selfClosing) {
		$this->Type = $type;
		$this->Symbol = $symbol;
		$this->SelfClosing = $selfClosing;
	}
}
