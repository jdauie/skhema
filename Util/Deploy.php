<?php

chdir(__dir__);

function Deploy($options) {
	
	foreach ($options as $key => $value) {
		echo $key.' = "'.$value.'"<br>';
	}
	
	$root      = $options['root'];
	$package   = $options['package'];
	$namespace = $options['namespace'];
	$output    = $options['output'];
	$author    = $options['author'];
	$year      = date('Y');
	
	$files = [];
	foreach (glob($root.'/*.php') as $entry) {
		$fileContents = file_get_contents($entry);
		if ($fileContents !== false) {
			// check namespace declaration
			$matches = [];
			if (preg_match('/^<\?php\s+namespace '.preg_quote($namespace).';/', $fileContents, $matches)) {
				// remove require_once statements
				preg_match('/^\<\?php\s+namespace '.preg_quote($namespace).';(\s+require_once\([^\)]+\);)*\s*(?<code>.+?)\s*\?\>\s*$/s', $fileContents, $matches);
				
				$files[$entry] = $matches['code'];
			}
		}
	}
	
	foreach ($files as $name => $value) {
		echo $name.'<br>';
	}
	
	$merged = implode("\n\n", $files);
	$merged = <<<EODEPLOY
<?php
namespace {$namespace} {

/*
 * Copyright (c) {$year}, {$author}
 * 
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 * 
 * @package {$package}
 * @author {$author}
 * @copyright {$year} {$author}
 * @license http://opensource.org/licenses/ISC
 */

{$merged}

}
?>
EODEPLOY;
	
	file_put_contents($output, $merged);
	
	// set up basic connection
	$ftp_server = "joshmorey.com";
	//$conn_id = ftp_ssl_connect($ftp_server);
	$conn_id = ftp_connect($ftp_server);
	
	// login with username and password
	$ftp_user_name = 'jdauie';
	$ftp_user_pass = '';
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
	
	// some servers need this, but some proxies can't handle it
	//ftp_pasv($conn_id, true);
	
	// check connection
	if ((!$conn_id) || (!$login_result)) {
		echo "FTP connection has failed!";
		echo "Attempted to connect to $ftp_server for user $ftp_user_name";
		exit;
	}
	else {
		echo "Connected to $ftp_server, for user $ftp_user_name";
	}
	
	//$buff = ftp_rawlist($conn_id, '.');
	//$buff = ftp_nlist($conn_id, '.');
	//var_dump($buff);
	
	
	// upload the file
	$source_file = $output;
	$destination_file = '/test.jacere.net/skhema/test.txt';
	if (ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY)) { 
		echo "Uploaded $source_file to $ftp_server as $destination_file";
	} else {
		echo "FTP upload has failed!";
	}
	if (ftp_delete($conn_id, $destination_file)) { 
		 echo "File deleted successfully\n";
	} else {
		echo "Delete failed!";
	}
	
	
	ftp_close($conn_id);
}

// move to json deployment config later
$config = [
	'root'      => '..',
	'package'   => 'skhema',
	'namespace' => 'Jacere',
	'output'    => '../_deploy/Template.php',
	'author'    => 'Joshua Morey <josh@joshmorey.com>',
];

Deploy($config);

?>