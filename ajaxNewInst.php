<?php
	include_once('site_init.php');

	$q2 = "INSERT INTO instances (event_id, date, ";

	if (!empty($_POST['instTime']))
	{
		$q2 .= "time, ";
	}

	if (!empty($_POST['instTravel']))
	{
		$q2 .= "travel_time, ";
	}

	if (isset($_POST['instNewDate']) && !empty($_POST['instNewDate']))
	{
		$new_date = $_POST['instNewDate'];
	}
	else
	{
		$offset = empty($_GET['offset']) ? 0 : $_GET['offset'];
		$timestamp = time() + $offset;
		$date = new DateTime(date('Y-m-d', $timestamp));
		$date = $date->modify('+1 day');
		$new_date = $date->format('Y-m-d');
	}

	$q2 .= "status) VALUES (".$_POST['eventID'].", '".$new_date."', ";

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
	$id = $planDB->insert_id;

	mysqli_close($planDB);

	echo $id;
