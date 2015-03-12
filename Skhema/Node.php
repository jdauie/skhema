<?php

namespace Jacere\Skhema;

use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class Node implements IToken, IPhpSerializable {
	
	private $m_token;
	private $m_parent;
	
	private $m_children;

    /**
     * @param IToken $token
     * @param Node $parent
     * @param IToken[] $children
     */
	function __construct(IToken $token, Node $parent = NULL, array $children = []) {
		$this->m_token = $token;
		$this->m_parent = $parent;
		$this->m_children = $children;
		foreach ($this->m_children as $child) {
			if ($child instanceof self) {
				$child->m_parent = $this;
			}
		}
	}
	
	public function parent() {
		return $this->m_parent;
	}
	
	public function type() {
		return $this->m_token->type();
	}
	
	public function name() {
		return $this->m_token->name();
	}
	
	public function anonymous() {
		return $this->m_token->anonymous();
	}

    /**
     * @param IToken|string $value
     */
	public function addChild($value) {
		$this->m_children[] = $value;
		if ($value instanceof self) {
			$value->m_parent = $this;
		}
	}

    /**
     * @return IToken
     */
	public function firstToken() {
		foreach ($this->m_children as $child) {
			if ($child instanceof IToken) {
				return $child;
			}
		}
		return NULL;
	}

    /**
     * @param Node $node
     * @param array $definitions
     */
	public function replaceContents($node, array $definitions = NULL) {
		// remove existing children
		foreach ($this->m_children as $child) {
			if ($child instanceof self) {
				$child->m_parent = NULL;
			}
		}
		$this->m_children = [];
		
		// add children from other node
		foreach ($node->m_children as $child) {
			if ($child instanceof IToken) {
				if ($child instanceof self) {
					$newChild = new self($child->m_token, $this);
					$newChild->replaceContents($child, $definitions);
					$child = $newChild;
				}
				else if ($definitions != NULL && $child->type() === TokenType::T_VARIABLE && isset($definitions[$child->name()])) {
					// replace this token/node with a new node
					$def = $definitions[$child->name()];
					$token = ($child instanceof self) ? $child->m_token : $child;
					$child = new Node($token, $this);
					$child->replaceContents($def);
				}
			}
			
			$this->m_children[] = $child;
		}
	}
	
	public function evaluate(TemplateManager $manager, $sources, $current) {
		foreach ($this->m_children as $child) {
			if ($child instanceof self) {
				$child->evaluate($manager, $sources, $current);
			}
			else if ($child instanceof IToken && ($childType = $child->type()) === TokenType::T_INCLUDE) {
				$template = $manager->template($child->name());
				$template->evaluate($manager, $sources, $current);
			}
			else if ($child instanceof EvalNameToken) {
				echo $child->evaluate($manager, $current);
			}
            else if (is_string($child)) {
                echo $child;
            }
		}
	}

	/**
	 * @param int $type
	 * @param bool $recursive
	 * @return \Traversable
	 */
	public function GetChildrenByType($type, $recursive = false) {
		foreach ($this->m_children as $child) {
			if ($child instanceof IToken && $child->type() === $type) {
				yield $child;
			}
			if ($recursive && $child instanceof self) {
				$childResult = $child->GetChildrenByType($type, true);
				foreach ($childResult as $value) {
					yield $value;
				}
			}
		}
	}

	/**
	 * @param string $class
	 * @param bool $recursive
	 * @return IToken[]
	 */
	public function GetChildrenByClass($class, $recursive = false) {
		foreach ($this->m_children as $child) {
			if ($child instanceof $class) {
				yield $child;
			}
			if ($recursive && $child instanceof self) {
				$childResult = $child->GetChildrenByClass($class, true);
				foreach ($childResult as $value) {
					yield $value;
				}
			}
		}
	}
	
	private function GetDepth() {
		if ($this->m_parent == NULL) {
			return 0;
		}
		return ($this->m_parent->getDepth() + 1);
	}
	
	public function __toString() {
		return $this->m_token->name();
	}

	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [
			$this->m_token,
			NULL,
			$this->m_children,
		]);
	}
}
