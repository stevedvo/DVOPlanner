<?php
	require ('../../init_DVOPlan.php');

	$q2 = "INSERT INTO instances (event_id, date, ";
	if (!empty($_POST['instTime']))
	{
		$q2 .= "time, ";
	}
	if (!empty($_POST['instTravel']))
	{
		$q2 .= "travel_time, ";
	}
	$q2 .= "status) VALUES (".$_POST['eventID'].", '".$_POST['instNewDate']."', ";
	if (!empty($_POST['instTime']))
	{
		$q2 .= "'".$_POST['instTime']."', ";
	}
	if (!empty($_POST['instTravel']))
	{
		$q2 .= $_POST['instTravel'].", ";
	}
	$q2 .= "'To Do')";

	$r2 = mysqli_query($planDB, $q2);

	mysqli_close($planDB);