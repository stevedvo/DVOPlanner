<?php
	$url = 'http://' . $_SERVER['HTTP_HOST'];            // Get the server
	$url .= rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // Get the current directory
	$url .= '/homepage.php';            // <-- Your relative path
	header('Location: ' . $url);
	die();
