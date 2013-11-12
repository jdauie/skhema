<?php

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
	echo $str."<br>\n";
}

function LoadFiles($config) {
	WriteLine('LOADFILES');
	
	$namespace = $config->namespace;
	$files = [];
	foreach (glob('*.php') as $entry) {
		$str = file_get_contents($entry);
		if ($str !== false) {
			// check namespace declaration
			$matches = [];
			if (preg_match('/^<\?php\s+namespace '.preg_quote($namespace).';/', $str, $matches)) {
				// remove require_once statements
				preg_match('/^\<\?php\s+namespace '.preg_quote($namespace).';(\s+require_once\([^\)]+\);)*\s*(?<code>.+?)\s*\?\>\s*$/s', $str, $matches);
				
				$files[$entry] = $matches['code'];
				WriteLine("{1}{2}", str_repeat('&nbsp;', 4), $entry);
			}
		}
	}
	
	return $files;
}

function MergeFiles($config, $header, $files) {
	WriteLine('MERGEFILES');
	
	$namespace = $config->namespace;
	$merged = implode("\n\n", $files);
	$merged = "<?php\nnamespace {$namespace} {\n\n{$header}\n\n{$merged}\n\n}\n?>";
	
	file_put_contents('_deploy/Template.php', $merged);
	
	return $merged;
}

function LoadHeader($config) {
	WriteLine('LOADHEADER');
	
	$str = file_get_contents('.license');
	$str = trim($str);
	$license = explode("\n", $str);
	$url = $license[0];
	
	// remove header
	array_shift($license);
	array_shift($license);
	
	$copyright = "Copyright (c) {$config->year}, {$config->author}";
	$lines = ['/*', $copyright, ''];
	
	foreach ($license as $line) {
		$lines[] = $line;
	}
	
	$lines[] = '';
	
	$info = [
		'package'   => $config->project,
		'author'    => $config->author,
		'copyright' => "{$config->year} {$config->author}",
		'license'   => $url,
	];
	
	foreach ($info as $key => $value) {
		$lines[] = "@{$key} {$value}";
	}
	
	$str = implode("\n * ", $lines);
	
	$str .= "\n */";
	
	WriteLine(str_replace("\n", "<br>\n", $str));
	
	return $str;
}

function LoadJson($file) {
	$str = file_get_contents($file);
	return json_decode($str);
}

//WriteLine('This {1} is a {2}', 1, 'test');

// change to project root
chdir(__dir__);
chdir('..');

$config = LoadJson('.deploy');
$header = LoadHeader($config);
$files  = LoadFiles($config);
$merged = MergeFiles($config, $header, $files);

