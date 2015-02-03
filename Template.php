<?php

namespace Jacere\Skhema;

class Template {
	
	private $m_name;
	private $m_root;
	private $m_parent;
	private $m_includes;
	private $m_functions;
	
	private $m_dependencies;
	
	function __construct($node, $name = NULL, $parent = NULL, $includes = NULL, $functions = NULL) {
		$this->m_name = ($name != NULL ? $name : $node->GetName());
		$this->m_root = $node;
		$this->m_parent = NULL;
		$this->m_includes = NULL;
		$this->m_dependencies = NULL;
		$this->m_dependencies = NULL;
		
		if ($includes === NULL) {
			$this->InitializeDependencies();
		}
		else {
			$this->m_parent = $parent;
			$this->m_includes = $includes;
			$this->m_functions = $functions;
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
			$this->m_parent = $firstChild->GetName();
		}
		
		// PHP 5.5 can just do EvalNameToken::class
		$token_class = get_class(new EvalNameToken(TokenType::T_VARIABLE, 'ignore'));
		$tokens = $root->GetChildrenByClass($token_class, true);
		foreach ($tokens as $token) {
			foreach ($token->GetFunctionNames() as $function) {
				$this->m_functions[$function] = $function;
			}
		}
		
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
				// check for int key to determine if this is a list
				// (this is a hack)
				reset($current);
				if (count($current) && !is_int(key($current))) {
					$current = [$current];
				}
				foreach ($current as $key => $row) {
					$row['__iteration'] = $key;
					$this->m_root->Evaluate($sources, $row);
				}
			}
			else {
				// how to define optional sources?
				//die(sprintf('No mapping for source "%s".', $this->m_name));
				//echo $this->m_name."<br>\n";
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
		if ($this->m_dependencies === NULL) {
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
	
	public function GetFunctions() {
		return $this->m_functions;
	}
	
	public function __toString() {
		$str = TokenType::Dump($this->m_root);
		$str .= TokenType::Dump($this->m_parent);
		$str .= TokenType::DumpArray($this->m_includes);
		//$str .= $this->m_root;
		return $str;
	}
	
	public function Dump() {
		return sprintf("'%s' => new Template(%s, '%s', %s, %s, %s)",
			$this->m_name,
			$this->m_root->Dump(),
			$this->m_name,
			($this->m_parent === NULL) ? 'NULL' : "'{$this->m_parent}'",
			(empty($this->m_includes) ? '[]' : sprintf("['%s']", implode("','", $this->m_includes))),
			(empty($this->m_functions) ? '[]' : sprintf("['%s']", implode("','", $this->m_functions)))
		);
	}
}
