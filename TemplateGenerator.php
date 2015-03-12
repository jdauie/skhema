<?php

namespace Jacere\Skhema;

use Generator;

class TemplateGenerator {

	const T_EXTENSION = '.tpl';

	/**
	 * @param string $dir
	 * @return Template[]
	 */
	public static function generate($dir) {

		TokenType::init();

		$templates = self::parse(self::load($dir));
		$templates = self::sort($templates);

		foreach ($templates as $template) {
			$template->finalize($templates);
		}

		return $templates;
	}

	private static function load($dir) {
		foreach(glob($dir.'/*'.self::T_EXTENSION) as $name) {
			yield self::tokenize(file_get_contents($name));
		}
	}
	
	private static function tokenize($str) {
		
		// testing out whitespace removal/reduction
		$str = preg_replace('/\s{2,}/', "\n", $str);
		$split = preg_split("/(\{[^\{\}]++\})/", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		$tokenFormatBeginLength = strlen(TokenType::T_FORMAT_BEGIN);
		$tokenFormatEndLength = strlen(TokenType::T_FORMAT_END);

		$tokens = [];

		foreach ($split as $value) {
			if ($value[0] === TokenType::T_FORMAT_BEGIN) {
				// regex does not currently verify min length
				$symbol = $value[$tokenFormatBeginLength];
				$type = TokenType::type($symbol);

				if ($type) {
					if ($type === TokenType::T_CLOSE) {
						$tokens[] = TokenType::T_CLOSE;
					}
					else {
						$tokenName = substr($value, $tokenFormatBeginLength + 1, -$tokenFormatEndLength);
						// check for evaluation entries
						if ($type === TokenType::T_VARIABLE || $type === TokenType::T_FILTER) {
							$tokens[] = new EvalNameToken($type, $tokenName);
						}
						else {
							$tokens[] = new NameToken($type, $tokenName);
						}
					}
				}
				else {
					$tokens[] = $value;
				}
			}
			else {
				$tokens[] = $value;
			}
		}

		return $tokens;
	}
	
	private static function parse(Generator $files) {
		$templates = [];
		/** @var Node[] $stack */
		$stack = [];

        /** @var Node $node */
		$node = NULL;

		foreach($files as $tokens) {
			foreach($tokens as $token) {
				if (is_string($token)) {
					if ($node !== NULL) {
						$node->addChild($token);
					}
					/*else {
						if (!$token instanceof TextToken) {
							// something other than whitespace/template at the root level
						}
					}*/
				}
				else if (is_int($token)) {
					if ($token === TokenType::T_CLOSE) {
						$type = $node->type();
						if ($type === TokenType::T_TEMPLATE || $type === TokenType::T_SOURCE) {
							// add to template list
							$name = $node->name();
							if ($type === TokenType::T_SOURCE) {
								// generate name for "anonymous" template (there will be something on the stack for this type)
								$parent = end($stack);
								while ($parent->parent()) {
									$parent = $parent->parent();
								}
								$templateParentPrefix = $parent->name();
								$name = TokenType::T_ANONYMOUS_TEMPLATE_PREFIX.$templateParentPrefix.TokenType::T_ANONYMOUS_TEMPLATE_DELIMITER.$name;
							}
							if (isset($templates[$name])) {
								throw new \Exception("Duplicate template definition: `$name`");
							}
							$templates[$name] = new Template($node, $name);

							// templates don't have a parent, so check the nesting stack
							if ($parent = array_pop($stack)) {
								// this is a nested template (replace with include token)
								$includeToken = new NameToken(TokenType::T_INCLUDE, $name);
								$parent->AddChild($includeToken);
							}
							$node = $parent;
						}
						else {
							$node = $node->parent();
						}
					}
				}
				else if ($token instanceof IToken) {
					$type = $token->type();
					if ($type === TokenType::T_TEMPLATE || $type === TokenType::T_SOURCE) {
						if ($node != NULL) {
							// save current node if this is a nested definition
							$stack[] = $node;
						}
						else {
							if ($type != TokenType::T_TEMPLATE) {
								throw new \Exception('Only explicit templates allowed at root level');
							}
						}
						// start new template node (with no parent)
						$node = new Node($token);
					}
					else if (!TokenType::void($type)) {
						if ($node != NULL) {
							// this is not a self-closing tag, so start a new node now
							$child = new Node($token, $node);
							$node->addChild($child);
							$node = $child;
						}
					}
					else {
						if ($node != NULL) {
							$node->addChild($token);
						}
					}
				}
			}
		}
		
		return $templates;
	}

	/**
	 * Topographical sort
	 * @param Template[] $sources
	 * @return Template[]
	 * @throws \Exception
	 */
	private static function sort(array $sources) {
		$edges = [];
		$s = [];
		foreach ($sources as $source) {
			$requires = $source->dependencies();
			if (count($requires)) {
				foreach ($requires as $dependency) {
					if (!isset($sources[$dependency])) {
						throw new \Exception("Unknown dependency `$dependency`");
					}
					if (!isset($edges[$dependency])) {
						$edges[$dependency] = [];
					}
					$edges[$dependency][] = $source->name();
				}
			}
			else {
				$s[] = $source;
			}
		}

		$sorted = [];
		while (!empty($s)) {
			// shift/pop doesn't matter for correctness
			$nSource = array_pop($s);
			$n = $nSource->name();
			$sorted[$n] = $nSource;
			if (isset($edges[$n])) {
				$parents = &$edges[$n];
				while (count($parents) > 0) {
					$m = array_pop($parents);
					$mSource = $sources[$m];
					$dependenciesSorted = true;

					$requires = $mSource->dependencies();
					if (count($requires)) {
						foreach ($requires as $dependency) {
							if (!isset($sorted[$dependency])) {
								$dependenciesSorted = false;
								break;
							}
						}
					}
					if ($dependenciesSorted) {
						$s[] = $mSource;
					}
				}
			}
		}

		// check remaining edges
		foreach ($edges as $parents) {
			if (count($parents)) {
				throw new \Exception('Graph cycle; unable to sort');
			}
		}

		return $sorted;
	}
}
