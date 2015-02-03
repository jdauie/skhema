<?php

namespace Jacere\Skhema;

class TokenType {
	
	const T_TEMPLATE = 1;
	const T_VARIABLE = 2;
	const T_INCLUDE  = 3;
	const T_INHERIT  = 4;
	const T_DEFINE   = 5;
	const T_SOURCE   = 6;
	const T_CLOSE    = 7;
	const T_TEXT     = 8;
	const T_FUNCTION = 9;
	
	// delimiter flags
	const T_FORMAT_BEGIN = '{';
	const T_FORMAT_END   = '}';
	
	const T_ANONYMOUS_TEMPLATE_PREFIX   = '__';
	const T_ANONYMOUS_TEMPLATE_DELIMITER   = ':';
	
	private static $c_definitions = [];
	private static $c_symbols = [];
	private static $c_debug = [];
	
	public static function Init() {
		
		if (count(self::$c_debug) != 0)
			return;
		
		$defs = [
			new TokenDef(self::T_TEMPLATE, '@',  false),
			new TokenDef(self::T_VARIABLE, '$',  true),
			new TokenDef(self::T_INCLUDE,  '#',  true),
			new TokenDef(self::T_INHERIT,  '^',  true),
			new TokenDef(self::T_DEFINE,   '.',  false),
			new TokenDef(self::T_SOURCE,   '?',  false),
			new TokenDef(self::T_CLOSE,    '/',  false),
			new TokenDef(self::T_TEXT,     NULL, true),
			new TokenDef(self::T_FUNCTION, '%',  true),
		];
		
		// create lookups
		foreach ($defs as $def) {
			self::$c_definitions[$def->Type] = $def;
			
			if ($def->Symbol != NULL) {
				self::$c_symbols[$def->Symbol] = $def->Type;
			}
		}
		
		// save token type names for debugging
		$class = new \ReflectionClass(__CLASS__);
		$constants = $class->getConstants();
		foreach ($constants as $name => $value) {
			if (isset(self::$c_definitions[$value])) {
				self::$c_debug[$value] = substr($name, strpos($name, '_') + 1);
			}
		}
	}
	
	public static function GetTokenTypeForSymbol($symbol) {
		if (isset(self::$c_symbols[$symbol])) {
			return self::$c_symbols[$symbol];
		}
		return NULL;
	}
	
	public static function GetTokenTypeDef($type) {
		if (isset(self::$c_definitions[$type])) {
			return self::$c_definitions[$type];
		}
		return NULL;
	}
	
	public static function GetTokenTypeName($type) {
		if (isset(self::$c_debug[$type])) {
			return self::$c_debug[$type];
		}
		return NULL;
	}
	
	public static function ParseName($name, &$functions, $include_name_as_function = false) {
		preg_match_all("/(?<part>[a-zA-Z0-9\-_]++(\[('[^']*+'|[^\]]*+)\])?)/", $name, $matches);
		$parts = $matches['part'];
		//$parts = explode(':', $name);
		$name = $parts[0];
		
		if (!$include_name_as_function) {
			array_shift($parts);
		}
		
		if (count($parts)) {
			$functions = [];
			foreach ($parts as $filter) {
				$options = NULL;
				if (($pos = strpos($filter, '[')) !== false && $filter[strlen($filter)-1] === ']') {
					$options = [];
					$options_str = substr($filter, $pos + 1, -1);
					$options_split = explode(',', $options_str);
					
					// handle quoted options
					$options_split_final = [];
					$option_merge = NULL;
					foreach ($options_split as $option) {
						if ($option_merge === NULL) {
							if ($option[0] === "'" && $option[strlen($option)-1] !== "'") {
								$option_merge = $option;
							}
							else {
								$options_split_final[] = $option;
							}
						}
						else {
							$option_merge .= $option;
							if ($option[strlen($option)-1] === "'") {
								$options_split_final[] = $option_merge;
								//print_r($option_merge);
								$option_merge = NULL;
							}
						}
					}
					$options_split = $options_split_final;
					
					$filter = substr($filter, 0, $pos);
					foreach ($options_split as $option) {
						$option_val = true;
						// check for kvp
						if (strpos($option, '=') !== false) {
							list($option, $option_val) = explode('=', $option, 2);
						}
						//else if (strpos($option, '/') !== false) {
						//	$option_val = explode('/', $option);
						//}
						$options[$option] = $option_val;
					}
				}
				$functions[] = [
					'name'    => $filter,
					'options' => $options,
				];
			}
		}
		
		return $name;
	}
	
	public static function ParseNameOptions(&$name) {
		$options = NULL;
		if (($pos = strpos($name, '[')) !== false && $name[strlen($name)-1] === ']') {
			$options = [];
			$options_str = substr($name, $pos + 1, -1);
			$options_split = explode(',', $options_str);
			foreach ($options_split as $option) {
				$option_val = true;
				// check for kvp
				$option_val_split = explode('=', $option);
				if (count($option_val_split) == 2) {
					$option = $option_val_split[0];
					$option_val = $option_val_split[1];
				}
				$options[$option] = $option_val;
			}
			$name = substr($name, 0, $pos);
		}
		return $options;
	}
	
	public static function DumpArray($array) {
		$str = '';
		if ($array != NULL) {
			foreach ($array as $value)
				$str .= TokenType::Dump($value);
		}
		return $str;
	}
	
	public static function Dump($token) {
		if ($token != NULL) {
			if (is_string($token)) {
				$token = trim($token);
				if (strlen($token) > 0) {
					//return htmlentities($token).'<br>';
					return '[...]<br>';
				}
			}
			else if (is_int($token)) {
				return '['.$token.']<br>';
			}
			else {
				return '/<span style="color:green">'.self::$c_debug[$token->GetType()].'</span>/<span style="color:red">'.$token->GetName().'</span><br>';
			}
		}
		return '';
	}
}
