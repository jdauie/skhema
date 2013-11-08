<?php

namespace Jacere;

function StartsWith($str, $start) {
	return (strncmp($str, $start, strlen($start)) === 0);
}

function ConvertToSize($size) {
	$unit = ['B','KB','MB','GB','TB','PB'];
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	//return @number_format($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

?>