<?php

chdir(__dir__);

function FormatString() {
	$args = func_get_args();
	$str = array_shift($args);
	if (count($args) === 1 && is_array($args[0])) {
		$args = $args[0];
	}
	$str = preg_replace('{{([0-9]+)}}', '%\\1$s', $str);
	return vsprintf($str, array_values($args));
}

function WriteLine() {
	$args = func_get_args();
	$str = array_shift($args);
	if (count($args)) {
		$str = FormatString($str, $args);
	}
	echo $str.'<br>';
}

function LoadFiles($dir, $namespace) {
	$files = [];
	foreach (glob($dir.'/*.php') as $entry) {
		$str = file_get_contents($entry);
		if ($str !== false) {
			// check namespace declaration
			$matches = [];
			if (preg_match('/^<\?php\s+namespace '.preg_quote($namespace).';/', $str, $matches)) {
				// remove require_once statements
				preg_match('/^\<\?php\s+namespace '.preg_quote($namespace).';(\s+require_once\([^\)]+\);)*\s*(?<code>.+?)\s*\?\>\s*$/s', $str, $matches);
				
				$files[$entry] = $matches['code'];
			}
		}
	}
	
	return $files;
}

function Simetho() {
	
	foreach ($files as $name => $value) {
		WriteLine($name);
	}
	
	return implode("\n\n", $files);
}

WriteLine('This {1} is a {2}', 1, 'test');

