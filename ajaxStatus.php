<?php
	require ('../../init_DVOPlan.php');

	$q = "UPDATE instances SET status = ".$_POST['instStatus']." WHERE instance_id = ".$_POST['instID'];

	$r = mysqli_query($planDB, $q);
	
	mysqli_close($planDB);