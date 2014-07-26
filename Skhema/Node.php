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
			else if ($child instanceof EvalNameToken) {
				echo $child->Evaluate($current);
			}
		}
	}
	
	public function GetChildrenByType($type, $recursive = false, $filter = NULL) {
		$result = [];
		foreach ($this->m_children as $child) {
			if (!is_string($child) && $child->GetType() === $type) {
				if ($filter == NULL || $filter($child)) {
					$result[] = $child;
				}
			}
			if ($recursive && $child instanceof self) {
				$childResult = $child->GetChildrenByType($type, true, $filter);
				foreach ($childResult as $value) {
					$result[] = $value;
				}
			}
		}
		return $result;
	}
	
	public function GetChildrenByClass($class, $recursive = false) {
		$result = [];
		foreach ($this->m_children as $child) {
			if ($child instanceof $class) {
				$result[] = $child;
			}
			if ($recursive && $child instanceof self) {
				$childResult = $child->GetChildrenByClass($class, true);
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
		$padChar = ' ';
		$padSize = 4;
		$pad = str_repeat($padChar, ($this->GetDepth() * $padSize));
		$padChild = str_repeat($padChar, ($this->GetDepth() * $padSize) + $padSize);
		
		$str = $pad.$this->m_token.'{<br>';
		foreach ($this->m_children as $value) {
			if (is_string($value)) {
				// trim/compact for display?
				$value = trim(preg_replace('/[\r\n]/', '', $value));
				//$str .= $padChild.'[...]<br>';
				//$str .= $padChild.htmlentities($value).'<br>';
				$str .= sprintf('%s<code style="color:#00f">%s</code><br>', $padChild, htmlentities($value));
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
			else if ($child instanceof EvalNameToken) {
				// evaluation token
				$children[] = sprintf('new EvalNameToken(TokenType::T_%s, "%s")',
					TokenType::GetTokenTypeName($child->GetType()),
					$child->GetSerializedName()
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