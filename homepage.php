<?php
	date_default_timezone_set('UTC');
	// opens DB connexion
	require ('../../init_DVOPlan.php');

	(empty($_GET['offset'])) ? $offset = 0 : $offset = $_GET['offset'];

	$timestamp = time() + $offset;
	$date = new DateTime(date('Y-m-d', $timestamp));

	include ('header.php');

	if ($_SERVER['REQUEST_METHOD']=="POST")
	{
		$errors = [];
		$needUpdate = FALSE; //sets a flag to avoid building an empty SQL query later

		if (!empty($_POST['travel_time']))
		{
			$travelTime = $_POST['travel_time'];
			$needUpdate = TRUE;
		}
		else
		{
			$travelTime = NULL;
			$needUpdate = TRUE;
		}

		if (!empty($_POST['inst_time']))
		{
			// regular expression to ensure time is "HH:mm"
			// substr() used in case POST passes time as HH:MM:ss
			$n = preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", substr($_POST['inst_time'],0,5));
			$needUpdate = TRUE;

			if (!$n)
			{
				$errors[] = 'Time for event "'.$_POST['description'].'" must be in format HH:mm';
			}
			else
			{
				$instTime = substr($_POST['inst_time'],0,5);
			}
		}
		else
		{
			$instTime = NULL;
		}

		if (!empty($errors))
		{
			echo "<h3>Error!</h3>";
			echo "<ul>";
			foreach ($errors as $msg)
			{
				echo "<li>".$msg."</li>";
			}
			echo "</ul>";
		}
		else // only triggers if no errors
		{
			$instanceID = $_POST['instance_id'];

			if ($needUpdate) // only builds query if one of the POST element not empty
			{
				// $q is SQL query to update an existing record in instances table
				$q = "UPDATE instances SET travel_time = ";
				if (is_null($travelTime))
				{
					$q .= "NULL, ";
				}
				else
				{
					$q .= $travelTime.", ";
				}
				if (isset($instTime))
				{
					$q .= "time = '".$instTime."', ";
				}
				$q .= "status = '".$_POST['currStatus']."' ";
				$q .= "WHERE instance_id = ".$instanceID;

				$r = mysqli_query($planDB, $q);
			}
		}
	} // end of if..POST code

	// SQL query to find event instances for current date displayed
	$q = "	SELECT * ";
	$q .= "	FROM instances ";
	$q .= "	INNER JOIN events ON events.event_id = instances.event_id ";
	$q .= "	WHERE date = '".$date->format('Y-m-d')."' ";
	// $q .= "	AND status = 'To Do' ";
	$q .= "	ORDER BY time";
	
	$r = mysqli_query($planDB, $q);

	if (mysqli_num_rows($r)===0)
	{
		echo "<p>No Events Today</p>";
	}
	else
	{
?>
		<table>
		<!--	Yuk - outputting data as a table for now to test site function / logic
				Not good for mobile - will create nicer interface in time-->
			<tr><td style='width: 50px;'>Start Travel</td><td style='width: 60px;'>Event Start</td><td style='width: 50px;'>Event End</td><td>Type</td><td>Description</td><td style='width: 50px;'>Travel Time</td><td>Status</td><td>Update</td><td colspan="3">Mark As:</td></tr>
<?php
		while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
		{
			$instDateTime = new DateTime($row['date']. " ".$row['time']);
			// table returns result of SQL query, built into a form which POSTs updates back to current page
			echo "<tr data-status='".$row['status']."'>";
				echo "<form method='POST' action='".$_SERVER['REQUEST_URI']."'>";
					// hidden inputs contain info required in case of POST
					echo "<input type='hidden' name='instance_id' value='".$row['instance_id']."'/>";
					echo "<input type='hidden' name='event_id' value='".$row['event_id']."'/>";
					echo "<input type='hidden' name='inst_date' value='".$row['date']."'/>";
					echo "<input type='hidden' name='description' value='".$row['description']."'/>";
					echo "<input type='hidden' name='currStatus' value='".$row['status']."'/>";
					// allows time that this instance of the event occurs to be updated
					echo "<tr data-status='".$row['status']."'><td style='width: 50px;'>".date('H:i',$instDateTime->getTimestamp()-60*$row['travel_time'])."</td>";
					echo "<td style='width: 60px;'><input name='inst_time' type='time' value='".$row['time']."'/></td>";
					echo "<td style='width: 50px;'>".date('H:i',$instDateTime->getTimestamp()+60*$row['duration'])."</td>";
					echo "<td><a href='view1event.php?event_id=".$row['event_id']."'>".$row['type']."</a></td>";
					echo "<td><a href='view1event.php?event_id=".$row['event_id']."'>".$row['description']."</a></td>";
					// allows travel time for this instance of the event to be updated
					echo "<td><input name='travel_time' type='number' min='0' value='".$row['travel_time']."' style='width: 50px;'/></td>";
					echo "<td>".$row['status']."</td>";
					// included the instance_id as the Submit button ID - not used but might be useful later
					echo "<td><input id='".$row['instance_id']."' type='submit' value='Update'/></td>";
				echo "</form>";
				// allows status for this instance of the event to be updated
				// THE JAVASCRIPT DEPENDS ON THE DOM PLACEMENT OF THESE BUTTONS BEING EXACTLY IN THIS POSITION!!
				echo "<td><button class='markComp' data-instID='".$row['instance_id']."'>Complete</button></td>";
				echo "<td><button class='markCanx' data-instID='".$row['instance_id']."'>Cancel</button></td>";
				echo "<td><button
					class='mark2mo' 
					data-instID='".$row['instance_id']."' 
					data-eventID='".$row['event_id']."' 
					data-newDate='".date('Y-m-d',$instDateTime->getTimestamp()+86400)."' 
					data-instTime='".$row['time']."' 
					data-travelTime='".$row['travel_time']."'>+1 day</button></td>";
			echo "</tr>";
		}
?>
		</table>
<?php
	}

	mysqli_free_result($r);
	mysqli_close($planDB);

	include ('footer.php');
?>