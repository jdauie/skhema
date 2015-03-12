<?php

namespace Jacere\Skhema;

use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class Template implements IPhpSerializable {
	
	private $m_name;
	private $m_root;
	private $m_parent;
	private $m_includes;
	private $m_filters;

    /**
     * @param Node $node
     * @param string $name
     * @param string $parent
     * @param string[] $includes
     * @param string[] $filters
     */
	function __construct(Node $node, $name = NULL, $parent = NULL, array $includes = NULL, array $filters = NULL) {
		$this->m_name = ($name != NULL ? $name : $node->name());
		$this->m_root = $node;
		
		if ($includes) {
			$this->m_parent = $parent;
			$this->m_includes = $includes;
			$this->m_filters = $filters;
		}
		else {
			// check first child for inheritance
			if (($firstChild = $node->firstToken()) && ($firstChild->type() === TokenType::T_INHERIT)) {
				$this->m_parent = $firstChild->name();
			}

			// include dependency names
			$this->m_includes = array_map('strval', iterator_to_array($node->GetChildrenByType(TokenType::T_INCLUDE, true)));

			/** @var EvalNameToken $token */
			$this->m_filters = [];
			foreach ($node->GetChildrenByClass(EvalNameToken::class, true) as $token) {
				foreach ($token->filters() as $filter) {
					$this->m_filters[$filter] = $filter;
				}
			}
		}
	}
	
	public function finalize($templates) {
		$root = $this->m_root;
		
		// derived templates can only include definitions (at the top level)
		// definitions can only be included in derived templates
		if ($this->m_parent != NULL) {
			$definitions = iterator_to_array($root->GetChildrenByType(TokenType::T_DEFINE));
			$definitions = array_combine(array_map('strval', $definitions), $definitions);
			
			// replace the contents of the root with the parent contents
			// go through children and fill in definitions
			$this->m_root->replaceContents($templates[$this->m_parent]->m_root, $definitions);
		}
	}
	
	public function evaluate(TemplateManager $manager, array $context, array $current = NULL) {

		// check filters
		foreach ($this->m_filters as $filter) {
			if (!$manager->filter($filter)) {
				throw new \Exception("Undefined filter `$filter` in template `{$this->m_name}`");
			}
		}

		// search the inheritance hierarchy
		$t = $this;
		while ($t->m_parent) {
			$t = $t->m_parent;
			$t = $manager->template($t);
			
			if (isset($context[$t->m_name])) {
				// copy definitions to corresponding child
				if (!isset($context[$this->m_name])) {
					$context[$this->m_name] = [];
				}
				foreach ($context[$t->m_name] as $parentKey => $parentVal) {
					if (!isset($context[$this->m_name][$parentKey])) {
						$context[$this->m_name][$parentKey] = $parentVal;
					}
				}
			}
		}
		
		if ($this->anonymous()) {
			$rootName = $this->m_root->name();
			if ($current != NULL && isset($current[$rootName])) {
				$current = $current[$rootName];
				// check for int key to determine if this is a list
				// (this is a hack)
				reset($current);
				if (count($current) && !is_int(key($current))) {
					$current = [$current];
				}
				foreach ($current as $key => $row) {
					$row['__iteration'] = $key;
					$this->m_root->evaluate($manager, $context, $row);
				}
			}
			else {
				// how to define optional sources?
				//die(sprintf('No mapping for source "%s".', $this->m_name));
				//echo $this->m_name."<br>\n";
			}
		}
		else {
			if ($context != NULL && isset($context[$this->m_name])) {
				$current = $context[$this->m_name];
			}
			else {
				//$current = NULL;
			}
			$this->m_root->evaluate($manager, $context, $current);
		}
	}
	
	public function name() {
		return $this->m_name;
	}
	
	public function anonymous() {
		return ($this->m_root->type() === TokenType::T_SOURCE);
	}
	
	public function dependencies() {
		return array_filter(array_merge((array)$this->m_parent, $this->m_includes));
	}
	
	public function __toString() {
		return $this->m_name;
	}

	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [
			$this->m_root,
			$this->m_name,
			$this->m_parent,
			$this->m_includes,
			$this->m_filters,
		]);
	}
}
