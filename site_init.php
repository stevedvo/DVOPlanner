<?php
	date_default_timezone_set('UTC');
	// opens DB connexion
	if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
	{
		require_once('../../init_DVOPlan_dev.php');
	}
	else
	{
		require_once('../../init_DVOPlan.php');
	}

	require_once('functions.php');
	