<?php

namespace Jacere;

require_once(__dir__.'/TokenType.php');

class Node implements IToken {
	
	private $m_token;
	private $m_parent;
	
	public $m_children;
	
	function __construct($token, $parent = NULL, array $children = NULL) {
		$this->m_token = $token;
		$this->m_parent = $parent;
		if (count($children)) {
			$this->m_children = $children;
			foreach ($this->m_children as $child) {
				if ($child instanceof self) {
					$child->m_parent = $this;
				}
			}
		}
		else {
			$this->m_children = [];
		}
	}
	
	public function HasParent() {
		return ($this->m_parent != NULL);
	}
	
	public function GetParent() {
		return $this->m_parent;
	}
	
	public function GetType() {
		return $this->m_token->GetType();
	}
	
	public function GetName() {
		return $this->m_token->GetName();
	}
	
	public function IsAnonymous() {
		return $this->m_token->IsAnonymous();
	}
	
	public function AddChild($value) {
		$this->m_children[] = $value;
		if ($value instanceof self) {
			$value->m_parent = $this;
		}
	}
	
	public function FirstChild($skipText = false) {
		foreach ($this->m_children as $child) {
			if (!$skipText || !is_string($child)) {
				return $child;
			}
		}
		return NULL;
	}
	
	public function ReplaceContents($node, $definitions = NULL) {
		// remove existing children
		foreach ($this->m_children as $child) {
			if ($child instanceof self) {
				$child->m_parent = NULL;
			}
		}
		$this->m_children = [];
		
		// add children from other node
		foreach ($node->m_children as $child) {
			if (!is_string($child)) {
				if ($child instanceof self) {
					$newChild = new Node($child->m_token, $this);
					$newChild->ReplaceContents($child, $definitions);
					$child = $newChild;
				}
				else if ($definitions != NULL && $child->GetType() == TokenType::T_VARIABLE && isset($definitions[$child->GetName()])) {
					// replace this token/node with a new node
					$def = $definitions[$child->GetName()];
					$token = ($child instanceof self) ? $child->m_token : $child;
					$child = new Node($token, $this);
					$child->ReplaceContents($def);
				}
			}
			
			$this->m_children[] = $child;
		}
	}
	
	public function Evaluate($sources, $current) {
		foreach ($this->m_children as $child) {
			if (is_string($child)) {
				echo $child;
			}
			else if ($child instanceof self) {
				$child->Evaluate($sources, $current);
			}
			else if (($childType = $child->GetType()) === TokenType::T_INCLUDE) {
				$template = TemplateManager::GetTemplate($child->GetName());
				$template->Evaluate($sources, $current);
			}
			else if ($childType == TokenType::T_VARIABLE) {
				$childName = $child->GetName();
				if ($current != NULL && isset($current[$childName])) {
					$value = $current[$childName];
					if ($child instanceof FilterNameToken) {
						$function = TemplateManager::GetFunction($child->GetFilter());
						if ($function !== NULL) {
							$value = $function($child->GetOptions(), $current, $value);
						}
						else {
						 	throw new \Exception(sprintf('Undefined filter "%s".', $child->GetFilter()));
						}
					}
					echo $value;
				}
				// wtf is this? it must have been for a test
				/*else if (isset($sources['$'][$childName])) {
					echo $sources['$'][$childName];
				}*/
				else {
					//die('Undefined variable: '.$childName);
				}
			}
			else if ($childType === TokenType::T_FUNCTION) {
				$childName = $child->GetName();
				
				$function = TemplateManager::GetFunction($childName);
				if (!$function) {
					if ($childName === 'iteration') {
						$function = function($options, $context) {
							return $context['__iteration'];
						};
					}
					else if ($childName === 'cycle') {
						$function = function($options, $context) {
							$keys = array_keys($options);
							return $keys[$context['__iteration'] % count($keys)];
						};
					}
					else if ($childName === 'first') {
						$function = function($options, $context) use ($childName) {
							if (count($options) !== 1) {
								throw new \Exception(sprintf('Invalid option for function "%s".', $childName));
							}
							reset($options);
							$scope = explode('/', key($options));
							if (count($scope) !== 2) {
								throw new \Exception(sprintf('Invalid option for function "%s".', $childName));
							}
							
							if (isset($context[$scope[0]])) {
								$scope_source = $context[$scope[0]];
								if (count($scope_source)) {
									$scope_source_first = $scope_source[0];
									print_r(array_keys($scope_source_first));
									if (isset($scope_source_first[$scope[1]])) {
										return $scope_source_first[$scope[1]];
									}
								}
							}
							
							throw new \Exception(sprintf('Invalid source for function "%s".', $childName));
						};
					}
					else {
						throw new \Exception(sprintf('Unknown function "%s".', $childName));
					}
					TemplateManager::RegisterFunction($childName, $function);
				}
				echo $function($child->GetOptions(), $current, $child);
			}
		}
	}
	
	public function GetChildrenByType($type, $recursive = false, $filter = NULL) {
		$result = [];
		foreach ($this->m_children as $child) {
			if (!is_string($child) && $child->GetType() == $type) {
				if ($filter == NULL || $filter($child)) {
					$result[] = $child;
				}
			}
			if ($recursive && $child instanceof self) {
				$childResult = $child->GetChildrenByType($type, true);
				foreach ($childResult as $value) {
					$result[] = $value;
				}
			}
		}
		return $result;
	}
	
	private function GetDepth() {
		if ($this->m_parent == NULL) {
			return 0;
		}
		return ($this->m_parent->getDepth() + 1);
	}
	
	public function __toString() {
		$padChar = '&nbsp;';
		$padSize = 4;
		$pad = str_repeat($padChar, ($this->GetDepth() * $padSize));
		$padChild = str_repeat($padChar, ($this->GetDepth() * $padSize) + $padSize);
		
		$str = $pad.$this->m_token.'{<br>';
		foreach ($this->m_children as $value) {
			if ($value instanceof TextToken) {
				$str .= $padChild.'[...]<br>';
			}
			else if ($value instanceof Template) {
				$str .= $padChild.$value;
			}
			else if ($value instanceof self) {
				$str .= $value;
			}
			else {
				$str .= $padChild.$value.'<br>';
			}
		}
		$str .= $pad.'}<br>';
		return $str;
	}
	
	public function Dump() {
		$children = [];
		foreach ($this->m_children as $child) {
			if (is_string($child)) {
				// text
				$children[] = sprintf("'%s'", str_replace("'", "\'", $child));
			}
			else if ($child instanceof self) {
				// node
				$children[] = $child->Dump();
			}
			else if ($child instanceof FilterNameToken) {
				// token
				$children[] = sprintf("new FilterNameToken(TokenType::T_%s, '%s', '%s')",
					TokenType::GetTokenTypeName($child->GetType()),
					$child->GetName(),
					$child->GetFilter()
				);
			}
			// TODO: params are not correct here
			else if ($child instanceof FunctionNameToken) {
				// token
				$children[] = sprintf("new FunctionNameToken(TokenType::T_%s, '%s')",
					TokenType::GetTokenTypeName($child->GetType()),
					$child->GetName()
				);
			}
			else {
				// token
				$children[] = sprintf("new NameToken(TokenType::T_%s, '%s')",
					TokenType::GetTokenTypeName($child->GetType()),
					$child->GetName()
				);
			}
		}
		
		return sprintf("new Node(new NameToken(TokenType::T_%s, '%s'), NULL, [%s])",
			TokenType::GetTokenTypeName($this->m_token->GetType()),
			$this->m_token->GetName(),
			implode(",", $children)
		);
	}
}

?>