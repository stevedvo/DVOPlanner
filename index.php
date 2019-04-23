<?php
	$url = 'http://'.$_SERVER['HTTP_HOST'];
	$url.= rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$url.= '/homepage.php';
	header('Location: '.$url);
	die();
