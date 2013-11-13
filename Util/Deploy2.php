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
		$value = ConvertToString($value);
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
				WriteLineClass('file', "\t".$entry);
			}
		}
	}
	
	return $files;
}

function MergeFiles($config, $header, $files) {
	WriteLine('');
	WriteLine("[merge]");
	
	$namespace = $config->namespace;
	$merged = implode("\n\n", $files);
	$merged = "<?php\nnamespace {$namespace} {\n\n{$header}\n\n{$merged}\n\n}\n?>";
	
	$temp = str_replace('\\', '/', getcwd()).'/.deploy.php';
	WriteLine("\t$temp");
	
	file_put_contents($temp, $merged);
	
	return $temp;
}

function UploadFile($private, $file) {
	WriteLine('');
	WriteLine("[upload]");
	
	$conn_id = ftp_connect($private->ftp_server);
	$login_result = ftp_login($conn_id, $private->ftp_user, $private->ftp_pwd);
	if ($private->ftp_pasv) {
		ftp_pasv($conn_id, true);
	}
	
	if ((!$conn_id) || (!$login_result)) {
		WriteLine("\tConnection Failed!");
		return;
	}
	else {
		WriteLine("\tConnected");
	}
	
	// upload the file
	$source_file = $file;
	$destination_file = $private->ftp_dest;
	if (ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY)) { 
		WriteLine("\t$destination_file");
	}
	else {
		WriteLine("\tUpload Failed!");
	}
	if (ftp_delete($conn_id, $destination_file)) {
		 WriteLine("\t(temp cleanup)");
	}
	else {
		WriteLine("Cleanup failed!");
	}
	
	ftp_close($conn_id);
}

function Init() {
	// change to project root
	chdir(__dir__);
	chdir('..');
	
	$config  = LoadJson('.deploy');
	$header  = LoadHeader($config);
	$files   = LoadFiles($config);
	$merged  = MergeFiles($config, $header, $files);
	$private = LoadJson('.private');
	UploadFile($private, $merged);
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