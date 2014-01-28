<?php

namespace Jacere;

require_once(__dir__.'/TemplateManager.php');

class TemplateGenerator {
	
	private $m_templates;
	private $m_extension;
	private $m_cache;
	private $m_mode;
	private $m_dir;
	
	function __construct($dir, $mode, &$templates) {
		$this->m_dir = $dir;
		$this->m_mode = $mode;
		$this->m_extension = '.'.TemplateManager::TEMPLATE_EXT;
		$this->m_cache = sprintf(TemplateManager::CACHE_FORMAT, TemplateManager::TEMPLATE_EXT);
		$this->m_templates = &$templates;
	}
	
	public static function Create($dir, $mode, &$templates) {
		$g = new TemplateGenerator($dir, $mode, $templates);
		$g->UpdateTemplateCache();
	}
	
	private function UpdateTemplateCache() {
		
		$sw = NULL;
		if (class_exists('\\Jacere\\Stopwatch')) {
			$sw = Stopwatch::StartNew('UpdateCache');
		}
		
		$files = $this->LoadTemplateFiles();
		if ($sw) $sw->Save('load');
		
		$files = $this->TokenizeTemplateFiles($files);
		if ($sw) $sw->Save('tokenize');
		
		$this->ParseTokens($files);
		if ($sw) $sw->Save('parse');
		
		$this->TopoSort();
		if ($sw) $sw->Save('toposort');
		
		$this->Finalize();
		if ($sw) $sw->Save('finalize');
		
		$this->Serialize();
		if ($sw) $sw->Save('serialize');
		
		if ($sw) {
			$sw->Stop();
		}
	}
	
	private function LoadTemplateFiles() {
		// keep files separate, for debugging
		$files = [];
		foreach (glob($this->m_dir.'/*'.$this->m_extension) as $entry) {
			$fileContents = file_get_contents($entry);
			if ($fileContents !== false) {
				$files[$entry] = $fileContents;
			}
		}
		
		return $files;
	}
	
	private function TokenizeTemplateFiles($files) {
		
		TokenType::Init();
		
		$result = [];
		foreach($files as $name => $str) {
			// testing out whitespace removal/reduction
			$str = preg_replace('/\s{2,}/', "\n", $str);
			$split = preg_split("/(\{[^\{\}]+\})/", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			
			$tokenFormatBeginLength = strlen(TokenType::T_FORMAT_BEGIN);
			$tokenFormatEndLength = strlen(TokenType::T_FORMAT_END);
			
			$tokens = [];
			
			foreach ($split as $value) {
				if ($value[0] === TokenType::T_FORMAT_BEGIN) {
					// regex does not currently verify min length
					$symbol = $value[$tokenFormatBeginLength];
					$tokenType = TokenType::GetTokenTypeForSymbol($symbol);
					
					if ($tokenType != NULL) {
						if ($tokenType === TokenType::T_CLOSE) {
							$tokens[] = TokenType::T_CLOSE;
						}
						else {
							$tokenName = substr($value, $tokenFormatBeginLength + 1, -$tokenFormatEndLength);
							// check for filter
							if ($tokenType === TokenType::T_VARIABLE && ($tokenNameSplitPos = strpos($tokenName, ':'))) {
								$tokens[] = new FilterNameToken($tokenType, substr($tokenName, 0, $tokenNameSplitPos), substr($tokenName, $tokenNameSplitPos + 1));
							}
							else {
								$tokens[] = new NameToken($tokenType, $tokenName);
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
			
			if (count($tokens)) {
				$result[$name] = $tokens;
			}
		}
		return $result;
	}
	
	private function ParseTokens($files) {
		$templates = [];
		$stack = [];
		$node = NULL;
		
		foreach($files as $name => $tokens) {
			foreach($tokens as $token) {
				if (is_string($token)) {
					if ($node !== NULL) {
						//$node->AddChild($token);
						$node->m_children[] = $token;
					}
					/*else {
						if (!$token instanceof TextToken) {
							// something other than whitespace/template at the root level
						}
					}*/
				}
				else if (is_int($token)) {
					if ($token === TokenType::T_CLOSE) {
						$nodeType = $node->GetType();
						if ($nodeType === TokenType::T_TEMPLATE || $nodeType === TokenType::T_SOURCE) {
							// add to template list
							$templateName = $node->GetName();
							if ($nodeType === TokenType::T_SOURCE) {
								// generate name for "anonymous" template (there will be something on the stack for this type)
								$templateParent = end($stack);
								while ($templateParent->HasParent()) {
									$templateParent = $templateParent->GetParent();
								}
								$templateParentPrefix = $templateParent->GetName();
								$templateName = TokenType::T_ANONYMOUS_TEMPLATE_PREFIX.$templateParentPrefix.TokenType::T_ANONYMOUS_TEMPLATE_DELIMITER.$templateName;
							}
							if (isset($templates[$templateName])) {
								die('Duplicate template definition: '.$templateName);
							}
							$templates[$templateName] = new Template($node, $templateName);
							
							// templates don't have a parent, so check the nesting stack
							$parent = NULL;
							if (count($stack)) {
								// this is a nested template (replace with include token)
								$parent = array_pop($stack);
								$includeToken = new NameToken(TokenType::T_INCLUDE, $templateName);
								//$parent->AddChild($includeToken);
								$parent->m_children[] = $includeToken;
							}
							$node = $parent;
						}
						else {
							$node = $node->GetParent();
						}
					}
				}
				else {
					$tokenType = $token->GetType();
					if ($tokenType === TokenType::T_TEMPLATE || $tokenType === TokenType::T_SOURCE) {
						if ($node != NULL) {
							// save current node if this is a nested definition
							$stack[] = $node;
						}
						else {
							if ($tokenType != TokenType::T_TEMPLATE) {
								die('Only explicit templates allowed at root level');
							}
						}
						// start new template node (with no parent)
						$node = new Node($token);
					}
					else if (!TokenType::GetTokenTypeDef($tokenType)->SelfClosing) {
						if ($node != NULL) {
							// this is not a self-closing tag, so start a new node now
							//$child = new Node($token);
							//$node->AddChild($child);
							$child = new Node($token, $node);
							$node->m_children[] = $child;
							$node = $child;
						}
					}
					else {
						if ($node != NULL) {
							//$node->AddChild($token);
							$node->m_children[] = $token;
						}
					}
				}
			}
		}
		
		$this->m_templates = $templates;
	}
	
	private function TopoSort() {
		$edges = [];
		$s = [];
		foreach ($this->m_templates as $templateName => $template) {
			if ($template->HasDependencies()) {
				foreach ($template->GetDependencies() as $dependency) {
					if (!isset($edges[$dependency])) {
						$edges[$dependency] = [];
					}
					$edges[$dependency][] = $templateName;
				}
			}
			else {
				$s[] = $template;
			}
		}
		
		$sorted = [];
		while (!empty($s)) {
			// shift/pop doesn't matter for correctness
			$nTemplate = array_pop($s);
			$n = $nTemplate->GetName();
			$sorted[$n] = $nTemplate;
			if (isset($edges[$n])) {
				$parents = &$edges[$n];
				while (count($parents) > 0) {
					$m = array_pop($parents);
					$mTemplate = $this->m_templates[$m];
		      $dependenciesSorted = true;
		      foreach ($mTemplate->GetDependencies() as $dependency) {
		      	if (!isset($sorted[$dependency])) {
		      		$dependenciesSorted = false;
		      		break;
		      	}
		      }
		      if ($dependenciesSorted) {
		      	$s[] = $mTemplate;
		      }
				}
			}
		}
		// count edges to check for cycle
		$edgesRemaining = 0;
		foreach ($edges as $parents) {
			$edgesRemaining += count($parents);
		}
		if ($edgesRemaining != 0) {
			var_dump($edges);
			die('graph cycle');
		}
		
		$this->m_templates = $sorted;
	}
	
	private function Finalize() {
		foreach ($this->m_templates as $template) {
			$template->Finalize();
		}
	}
	
	private function Serialize() {
		$path = $this->m_dir.'/'.$this->m_cache;
		
		if (($this->m_mode & TemplateManager::CACHE_MODE_STD) !== 0) {
			$data = serialize($this->m_templates);
			if ($this->m_mode === TemplateManager::CACHE_MODE_STD_GZIP) {
				$path .= '.gz';
				$data = gzencode($data);
			}
			file_put_contents($path, [TemplateManager::CACHE_MARKER.str_pad(TemplateManager::CACHE_VERSION, TemplateManager::CACHE_VERSION_CHARS), $data]);
		}
		else if (($this->m_mode & TemplateManager::CACHE_MODE_PHP) !== 0) {
			// decent option with bytecode caching
			$path .= '.php';
			$templates = [];
			foreach ($this->m_templates as $template) {
				$templates[] = $template->Dump();
			}
			$output = implode(",", $templates);
			
			$uniqueId = uniqid();
			$output = <<<EOT
<?php

namespace Jacere\TemplateCache {
function DeserializeCachedTemplates() {
return \Jacere\Deserialize_{$uniqueId}();
}
}

namespace Jacere {
function Deserialize_{$uniqueId}() {
return [
{$output}
];
}
}
?>
EOT;
			file_put_contents($path, $output);
		}
		else {
			die('Invalid cache mode');
		}
	}
}
?>