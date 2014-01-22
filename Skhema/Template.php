<?php

namespace Jacere;

require_once(__dir__.'/TokenType.php');
require_once(__dir__.'/Node.php');
require_once(__dir__.'/TemplateManager.php');

class Template {
	
	private $m_name;
	private $m_root;
	private $m_parent;
	private $m_includes;
	
	private $m_dependencies;
	
	function __construct($node, $name = NULL, $parent = NULL, $includes = NULL) {
		$this->m_name = ($name != NULL ? $name : $node->GetName());
		$this->m_root = $node;
		$this->m_parent = NULL;
		$this->m_includes = NULL;
		$this->m_dependencies = NULL;
		
		if ($includes === NULL) {
			$this->InitializeDependencies();
		}
		else {
			$this->m_parent = $parent;
			$this->m_includes = $includes;
		}
	}
	
	private static function CreateNameMapping($array) {
		$map = [];
		if (count($array) > 0) {
			foreach($array as $value) {
				$map[$value->GetName()] = $value;
			}
		}
		return $map;
	}
	
	private function InitializeDependencies() {
		$root = $this->m_root;
		
		// check first child for inheritance
		$firstChild = $root->FirstChild(true);
		if ($firstChild != NULL && $firstChild->GetType() == TokenType::T_INHERIT) {
			//$this->m_parent = $firstChild;
			$this->m_parent = $firstChild->GetName();
		}
		//$this->m_includes = $root->GetChildrenByType(TokenType::T_INCLUDE, true);
		$includes = $root->GetChildrenByType(TokenType::T_INCLUDE, true);
		$this->m_includes = [];
		foreach ($includes as $include) {
			$this->m_includes[] = $include->GetName();
		}
	}
	
	public function Finalize() {
		$root = $this->m_root;
		
		// derived templates can only include definitions (at the top level)
		// definitions can only be included in derived templates
		if ($this->m_parent != NULL) {
			$definitions = self::CreateNameMapping($root->GetChildrenByType(TokenType::T_DEFINE));
			
			// replace the contents of the root with the parent contents
			// go through children and fill in definitions
			$this->m_root->ReplaceContents(TemplateManager::GetTemplate($this->m_parent)->m_root, $definitions);
		}
	}
	
	public function Evaluate($sources, $current = NULL) {
		// search the inheritance heirarchy
		$t = $this;
		while ($t->m_parent) {
			$t = $t->m_parent;
			$t = TemplateManager::GetTemplate($t);
			
			if (isset($sources[$t->m_name])) {
				// copy definitions to corresponding child
				if (!isset($sources[$this->m_name])) {
					$sources[$this->m_name] = [];
				}
				foreach ($sources[$t->m_name] as $parentKey => $parentVal) {
					if (!isset($sources[$this->m_name][$parentKey])) {
						$sources[$this->m_name][$parentKey] = $parentVal;
					}
				}
			}
		}
		
		if ($this->IsAnonymous()) {
			$rootName = $this->m_root->GetName();
			if ($current != NULL && isset($current[$rootName])) {
				$current = $current[$rootName];
				foreach ($current as $key => $row) {
					$this->m_root->Evaluate($sources, $row, $key);
				}
			}
			else {
				die('no mapping for source');
			}
		}
		else {
			if ($sources != NULL && isset($sources[$this->m_name])) {
				$current = $sources[$this->m_name];
			}
			else {
				//$current = NULL;
			}
			$this->m_root->Evaluate($sources, $current);
		}
	}
	
	public function GetName() {
		return $this->m_name;
	}
	
	public function IsAnonymous() {
		return ($this->m_root->GetType() == TokenType::T_SOURCE);
	}
	
	public function HasDependencies() {
		return ($this->m_parent != NULL || $this->m_includes != NULL);
	}
	
	public function GetDependencies() {
		// cache array
		if ($this->m_dependencies == NULL) {
			$this->m_dependencies = [];
			if ($this->m_parent != NULL) {
				$this->m_dependencies[] = $this->m_parent;
			}
			if ($this->m_includes != NULL) {
				foreach ($this->m_includes as $value) {
					$this->m_dependencies[] = $value;
				}
			}
		}
		return $this->m_dependencies;
	}
	
	public function __toString() {
		$str = TokenType::Dump($this->m_root);
		$str .= TokenType::Dump($this->m_parent);
		$str .= TokenType::DumpArray($this->m_includes);
		//$str .= $this->m_root;
		return $str;
	}
	
	public function Dump() {
		return sprintf("'%s' => new Template(%s, '%s', %s, %s)",
			$this->m_name,
			$this->m_root->Dump(),
			$this->m_name,
			($this->m_parent === NULL) ? 'NULL' : "'{$this->m_parent}'",
			(empty($this->m_includes) ? '[]' : sprintf("['%s']", implode("','", $this->m_includes)))
		);
	}
}
?>
