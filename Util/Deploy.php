<?php

function WriteLine($str) {
	$str = str_replace('<', '&lt;', $str);
	echo $str."\n";
}

function WriteLineClass($class, $str) {
	$str = str_replace('<', '&lt;', $str);
	echo "<span class='$class'>".$str."</span>\n";
}

function ConvertToString($val) {
	if (is_bool($val)) {
		return $val ? 'true' : 'false';
	}
	else {
		return $val;
	}
}

function LoadJson($file) {
	$str = file_get_contents($file);
	$obj = json_decode($str);
	
	WriteLine('');
	WriteLine("[$file]");
	foreach ($obj as $key => $value) {
		if (is_array($value)) {
			$value = '[...]';
		}
		else {
			$value = ConvertToString($value);
		}
		WriteLine("\t$key: $value");
	}
	
	return $obj;
}

function LoadHeader($config) {
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
	
	WriteLine('');
	WriteLine("[header]");
	WriteLineClass('header', "$str");
	
	return $str;
}

function LoadFiles($config) {
	WriteLine('');
	WriteLine("[load]");
	
	$files = [];
	foreach (glob('*.php') as $entry) {
		$files[basename($entry)] = $entry;
	}
	
	$namespace = $config->namespace;
	
	$groups = [];
	foreach ($config->groups as $group) {
		$g = [];
		foreach ($group->inputs as $input) {
			$entry = $files[$input];
			$str = file_get_contents($entry);
			if ($str !== false) {
				// check namespace declaration
				$matches = [];
				if (preg_match('/^<\?php\s+namespace '.preg_quote($namespace).';/', $str, $matches)) {
					// strip require_once statements
					preg_match('/^\<\?php\s+namespace '.preg_quote($namespace).';(\s+require_once\([^\)]+\);)*\s*(?<code>.+?)\s*\?\>\s*$/s', $str, $matches);
					
					$g[$input] = $matches['code'];
					WriteLineClass('file', "\t".$entry);
				}
			}
		}
		$groups[$group->output] = &$g;
	}
	
	return $groups;
}

function MergeFiles($config, $header, $groups) {
	WriteLine('');
	WriteLine("[merge]");
	
	$namespace = $config->namespace;
	
	$temp = [];
	foreach ($groups as $key => $files) {
		$merged = implode("\n\n", $files);
		$merged = "<?php\nnamespace {$namespace} {\n\n{$header}\n\n{$merged}\n\n}\n?>";
		
		$t = str_replace('\\', '/', getcwd()).'/.tmp.'.$key;
		$temp[$t] = $key;
		file_put_contents($t, $merged);
		WriteLine("\t$t");
	}
	
	return $temp;
}

function UploadFiles($private, $files) {
	WriteLine('');
	WriteLine("[upload]");
	
	$conn = ftp_connect($private->ftp_server);
	$login = ftp_login($conn, $private->ftp_user, $private->ftp_pwd);
	if ($private->ftp_pasv) {
		ftp_pasv($conn, true);
	}
	
	if ((!$conn) || (!$login)) {
		WriteLine("\tConnection Failed!");
		return;
	}
	else {
		WriteLine("\tConnected");
	}
	
	// upload the files
	if (ftp_chdir($conn, $private->ftp_dest)) {
		foreach ($files as $source => $destination) {
			if (ftp_put($conn, $destination, $source, FTP_BINARY)) { 
				WriteLine("\t$destination");
			}
			else {
				WriteLine("\tUpload Failed!");
			}
			unlink($source);
		}
	}
	
	ftp_close($conn);
}

function Init() {
	// change to project root
	chdir(__dir__);
	chdir('..');
	
	$config  = LoadJson('.deploy');
	$header  = LoadHeader($config);
	$groups  = LoadFiles($config);
	$merged  = MergeFiles($config, $header, $groups);
	$private = LoadJson('.private');
	UploadFiles($private, $merged);
}

?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<style type="text/css">
html,pre {
	font:12px Courier New;
}
pre {
	-moz-tab-size:    4;
	-o-tab-size:      4;
	-webkit-tab-size: 4;
	-ms-tab-size:     4;
	tab-size:         4;
}
.header {
	color:green;
}
.file {
	color:blue;
}
</style>
</head>
<body>
<pre>
<?php Init(); ?>
</pre>
</body>
</html>