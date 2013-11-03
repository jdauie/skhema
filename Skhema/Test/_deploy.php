<?php

require_once('../Util.php');

function Deploy($root, $namespace, $output) {
	$root = '.';
	$dir = dir($root);
	$files = [];
	while (false !== ($entry = $dir->read())) {
		if (Jacere\EndsWith($entry, '.php')) {
			$fileContents = file_get_contents($root.'/'.$entry);
			if ($fileContents !== false) {
				// check namespace declaration
				$matches = [];
				if (preg_match('/^<\?php\s+namespace '.preg_quote($namespace).';/', $fileContents, $matches)) {
					// remove require_once statements
					preg_match('/^\<\?php\s+namespace '.preg_quote($namespace).';(\s+require_once\([^\)]+\);)*\s*(?<code>.+?)\s*\?\>/s', $fileContents, $matches);
					
					$files[$entry] = $matches['code'];
				}
			}
		}
	}
	$dir->close();
	
	foreach ($files as $name => $value) {
		echo $name.'<br>';
	}
	
	$merged = implode("\n\n", $files);
	$merged = <<<EOT
<?php
namespace {$namespace} {

/*
 * Copyright (c) 2013, Joshua Morey <josh@joshmorey.com>
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
 * @package templ@te
 * @author Joshua Morey <josh@joshmorey.com>
 * @copyright 2013 Joshua Morey <josh@joshmorey.com>
 * @license http://opensource.org/licenses/ISC
 */

{$merged}

}
?>
EOT;
	
	file_put_contents($root.'/'.$output, $merged);
}

Deploy('.', 'Jacere', '_deploy/Template.php');

?>