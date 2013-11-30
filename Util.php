<?php

namespace Jacere;

function StartsWith($str, $start) {
	return (strncmp($str, $start, strlen($start)) === 0);
}

function ConvertToSize($size) {
	$unit = ['B','KB','MB','GB','TB','PB'];
	// compare with @number_format
	$i = ($size === 0) ? 0 : floor(log($size, 1024));
	return @round($size / pow(1024, $i), 2).' '.$unit[$i];
}

?>