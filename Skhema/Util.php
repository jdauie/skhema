<?php

namespace Jacere;

function EndsWith($str, $end) {
	return (strlen($str) >= strlen($end) && substr_compare($str, $end, strlen($str) - strlen($end)) === 0);
}

function StartsWith($str, $start) {
	return (strncmp($str, $start, strlen($start)) === 0);
}

function ConvertToSize($size) {
	$unit = ['B','KB','MB','GB','TB','PB'];
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	//return @number_format($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}



function HashPassword($password) {
	$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 8]);
	return $hash;
}

function VerifyPassword($dbh, $username, $password) {
	
	$sth = $dbh->prepare('
		SELECT Password
		FROM Users
		WHERE Name = :username
		LIMIT 1
	');
	
	$sth->bindParam(':username', $username);
	
	$sth->execute();
	
	$user = $sth->fetch(\PDO::FETCH_OBJ);
	
	if (password_verify($password, $user->Password)) {
		return true;
	}
	
	return false;
}

?>