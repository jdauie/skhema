<?php

namespace Jacere\Skhema;

class TokenType {
	
	const T_TEMPLATE = 1;
	const T_VARIABLE = 2;
	const T_FILTER   = 3;
	const T_INCLUDE  = 4;
	const T_INHERIT  = 5;
	const T_DEFINE   = 6;
	const T_SOURCE   = 7;
	const T_CLOSE    = 8;
	const T_TEXT     = 9;
	
	// delimiter flags
	const T_FORMAT_BEGIN = '{';
	const T_FORMAT_END   = '}';
	
	const T_ANONYMOUS_TEMPLATE_PREFIX   = '__';
	const T_ANONYMOUS_TEMPLATE_DELIMITER   = ':';
	
	private static $c_definitions;
	private static $c_symbols;
	
	public static function init() {
		
		if (self::$c_symbols) {
			return;
		}

		$definitions = [
			new TokenDef(self::T_TEMPLATE, '@',  false),
			new TokenDef(self::T_VARIABLE, '$',  true),
			new TokenDef(self::T_FILTER,   '%',  true),
			new TokenDef(self::T_INCLUDE,  '#',  true),
			new TokenDef(self::T_INHERIT,  '^',  true),
			new TokenDef(self::T_DEFINE,   '.',  false),
			new TokenDef(self::T_SOURCE,   '?',  false),
			new TokenDef(self::T_CLOSE,    '/',  false),
			new TokenDef(self::T_TEXT,     NULL, true),
		];
		
		// create maps
		$types = array_map(function(TokenDef $a) {return $a->Type;}, $definitions);
		self::$c_definitions = array_combine($types, $definitions);
		array_pop($definitions);
		array_pop($types);
		self::$c_symbols = array_combine(array_map('strval', $definitions), $types);
	}

	/**
	 * @param string $symbol
	 * @return int|null
	 */
	public static function type($symbol) {
		if (isset(self::$c_symbols[$symbol])) {
			return self::$c_symbols[$symbol];
		}
		return NULL;
	}

    /**
	 * Whether this is a void type (self-closing)
     * @param int $type
     * @return bool
     */
	public static function void($type) {
		return self::$c_definitions[$type]->SelfClosing;
	}

	/**
	 * @param string $name
	 * @param Filter[] $filters
	 * @param bool $include_name_as_function
	 * @return string
	 */
	public static function ParseName($name, &$filters, $include_name_as_function = false) {
		preg_match_all("/(?<part>[a-zA-Z0-9\-_]++(\[('[^']*+'|[^\]]*+)\])?)/", $name, $matches);
		$parts = $matches['part'];
		//$parts = explode(':', $name);
		$name = $parts[0];
		
		if (!$include_name_as_function) {
			array_shift($parts);
		}
		
		if (count($parts)) {
			$filters = [];
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
				$filters[] = new Filter($filter, $options);
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
}
