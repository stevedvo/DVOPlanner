<?php
	include_once('site_init.php');
	$pageTitle = "All Instances";
?>

<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
		<title><?= $pageTitle; ?></title>
		<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript">
			// anticipating this will be used in more interactive UI later
			function init()
			{
				// sets background of row and descendant elements according to status
				$("tr[data-status='To Do'], tr[data-status='To Do'] td *").css('background','#FFAAAA');
				$("tr[data-status='Complete'], tr[data-status='Complete'] td *").css('background','#AAFFAA');
				$("tr[data-status='Postponed'], tr[data-status='Postponed'] td *").css('background','#AAAAAA');
				$("tr[data-status='Cancelled'], [data-status='Cancelled'] td *").css('background','#AAAAAA');

				// updates displayed status to user on button click
				// performs AJAX POST to update the DB
				$(".markComp").click(function()
				{
					$(this).parent().parent().attr("data-status", "Complete");
					$(this).parent().prev().prev().html("Complete");
					$("tr[data-status='Complete'], tr[data-status='Complete'] td *").css('background','#AAFFAA');
					$.ajax(
					{
						type: 	"POST",
						url: 	"ajaxStatus.php",
						data:
						{
							instID: 	$(this).attr("data-instID"), 
							instStatus:	"'Complete'"
						}
					});
				});
				$(".markCanx").click(function()
				{
					$(this).parent().parent().attr("data-status", "Cancelled");
					$(this).parent().prev().prev().prev().html("Cancelled");
					$("tr[data-status='Cancelled'], [data-status='Cancelled'] td *").css('background','#AAAAAA');
					$.ajax(
					{
						type: 	"POST",
						url: 	"ajaxStatus.php",
						data:
						{
							instID: 	$(this).attr("data-instID"), 
							instStatus:	"'Cancelled'"
						}
					});
				});
				$(".mark2mo").click(function()
				{
					$(this).parent().parent().attr("data-status", "Postponed");
					$(this).parent().prev().prev().prev().prev().html("Postponed");
					$("tr[data-status='Postponed'], tr[data-status='Postponed'] td *").css('background','#AAAAAA');
					$.ajax(
					{
						type: 	"POST",
						url: 	"ajaxStatus.php",
						data:
						{
							instID: 	$(this).attr("data-instID"), 
							instStatus: "'Postponed'"
						}
					});
					$.ajax(
					{
						type: 	"POST",
						url: 	"ajaxNewInst.php",
						data:
						{
							eventID: 		$(this).attr("data-eventID"),
							instNewDate: 	$(this).attr("data-newDate"),  
							instTime: 		$(this).attr("data-instTime"),  
							instTravel: 	$(this).attr("data-travelTime")
						}
					});
				});

			}
			document.addEventListener("DOMContentLoaded", init, false);
		</script>
	</head>

	<body>
		<h1><?= $pageTitle; ?></h1>
		<a href="homepage.php"><button>Back To Today</button></a>
		<a href="newevent.php"><button>Add Event</button></a>
		<a href="viewevents.php"><button>View Events</button></a>
		<hr/>
		<?php
			// POSTed form returns to this page for error-checking / validation
			if ($_SERVER['REQUEST_METHOD']=='POST')
			{
				$errors = [];
				$needUpdate = FALSE; //sets a flag to avoid building an empty SQL query later

				if (!empty($_POST['inst_date']))
				{
					// regular expression to ensure date is "yyyy-mm-dd"
					$n = preg_match("/\d{4}-\d{2}-\d{2}/", $_POST['inst_date']);

					if (!$n)
					{
						$errors[] = 'Date must be in format yyyy-mm-dd';
					}
					else
					//if date in current format, ensures that date is valid
					//checkdate() has issues if non-numeric data entered, hence the regex first
					{
						$eventDay = substr($_POST['inst_date'], 8, 2);
						$eventMonth = substr($_POST['inst_date'], 5, 2);
						$eventYear = substr($_POST['inst_date'], 0, 4);
						if (!checkdate($eventMonth, $eventDay, $eventYear))
						{
							$errors[] = 'Invalid Instance Date';
						}
						else
						{
							$instDate = $_POST['inst_date'];
							$needUpdate = TRUE;
						}
					}
				}
				else
				{
					$instDate = NULL;
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

				if (!empty($_POST['travel_time']))
				{
					$travelTime = $_POST['travel_time'];
					$needUpdate = TRUE;
				}
				else
				{
					$travelTime = NULL;
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

					if ($needUpdate) // only builds SQL query if one of the POST elements not empty
					{
						// $q is SQL query to update an existing record in instances table
						$q = "UPDATE instances SET ";
						if (isset($instDate))
						{
							$q .= "date = '".$instDate."', ";
						}
						if (isset($instTime))
						{
							$q .= "time = '".$instTime."', ";
						}
						if (is_null($travelTime))
						{
							$q .= "travel_time = NULL, ";
						}
						else
						{
							$q .= "travel_time = ".$travelTime.", ";
						}
						$q .= "status = '".$_POST['currStatus']."' ";
						$q .= "WHERE instance_id = ".$instanceID;

						$r = mysqli_query($planDB, $q);
					}
				}
			} // end of if..POST statement

			// building SELECT query based on checked items

			$filter = "'To Do' ";
			if (isset($_GET['Include']))
			{
				$incStatus = $_GET['Include'];
				$filterError = FALSE;

				// checking GET array is valid, beginning with size of array
				if (sizeof($incStatus)>4)
				{
					$filterError = TRUE;
				}
				else
				{
					// check valid strings
					foreach ($incStatus as $validFilter)
					{
						if ($validFilter!="To Do" && $validFilter!="Complete" && $validFilter!="Cancelled" && $validFilter!="Postponed")
						{
							$filterError = TRUE;
						}
					}
				}

				if ($filterError)
				{
					echo "<p>Error! - Invalid Filter Selection</p>";
				}
				else
				{
					$filter = "'".$incStatus[0]."' ";
					
					if (sizeof($incStatus)>1)
					{
						for ($i=1 ; $i<sizeof($incStatus) ; $i++)
						{
							$filter .= "OR status = '".$incStatus[$i]."' ";
						}
					}
				}

			} // end of if..GET statements

			// "Sticky" checkboxes, checking $filter for matching strings
			echo
			"
				<form method='GET' action='allinstances.php'>
					<fieldset>
						<legend>Filter Events</legend>
						<input type='checkbox' ";
							if (strpos($filter,"To Do")!==FALSE)
							{
								echo "checked ";
							}
							echo "name='Include[]' value='To Do'/>To Do | 
						<input type='checkbox' ";
							if (strpos($filter,"Complete")!==FALSE)
							{
								echo "checked ";
							}
							echo "name='Include[]' value='Complete'/>Completed | 
						<input type='checkbox' ";
							if (strpos($filter,"Cancelled")!==FALSE)
							{
								echo "checked ";
							}
							echo "name='Include[]' value='Cancelled'/>Cancelled | 
						<input type='checkbox' ";
							if (strpos($filter,"Postponed")!==FALSE)
							{
								echo "checked ";
							}
							echo "name='Include[]' value='Postponed'/>Postponed | 
						<input type='submit' value='Apply Filter'/>
					</fieldset>
				</form>
			";

			// SQL query to find all event instances
			$q = "SELECT * ";
			$q.= "FROM instances ";
			$q.= "INNER JOIN events ON events.event_id = instances.event_id ";
			$q.= "WHERE status = ".$filter;
			$q.= "ORDER BY date, time";
			
			$r = mysqli_query($planDB, $q);

			if (mysqli_num_rows($r)===0)
			{
				echo "<p>No Events Available</p>";
			}
			else
			{
		?>
				<table>
				<!--	Yuk - outputting data as a table for now to test site function / logic
						Not good for mobile - will create nicer interface in time-->
					<tr><td>Date</td><td style='width: 50px;'>Start Travel</td><td style='width: 60px;'>Event Start</td><td style='width: 50px;'>Event End</td><td>Type</td><td>Description</td><td style='width: 50px;'>Travel Time</td><td>Status</td><td>Update</td><td colspan="3">Mark As:</td></tr>
		<?php
				while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
				{
					$instDateTime = new DateTime($row['date']. " ".$row['time']);
					// table returns result of SQL query, built into a form which POSTs updates back to current page
					echo "<form method='POST' action='".$_SERVER['REQUEST_URI']."'>";
					// hidden inputs contain info required in case of POST
					echo "<input type='hidden' name='instance_id' value='".$row['instance_id']."'/>";
					echo "<input type='hidden' name='event_id' value='".$row['event_id']."'/>";
					echo "<input type='hidden' name='description' value='".$row['description']."'/>";
					echo "<input type='hidden' name='currStatus' value='".$row['status']."'/>";
					// allows date that this instance of the event occurs to be updated
					echo "<tr data-status='".$row['status']."'><td><input name='inst_date' type='date' value='".$row['date']."' style='width: 125px;'/></td>";
					echo "<td style='width: 50px;'>".date('H:i',$instDateTime->getTimestamp()-60*$row['travel_time'])."</td>";
					// allows time that this instance of the event occurs to be updated
					echo "<td style='width: 60px;'><input name='inst_time' type='time' value='".$row['time']."'/></td>";
					echo "<td style='width: 50px;'>".date('H:i',$instDateTime->getTimestamp()+60*$row['duration'])."</td>";
					echo "<td><a href='view1event.php?event_id=".$row['event_id']."'>".$row['type']."</a></td>";
					echo "<td><a href='view1event.php?event_id=".$row['event_id']."'>".$row['description']."</a></td>";
					// allows travel time for selected instance of the event to be updated
					echo "<td><input name='travel_time' type='number' min='0' value='".$row['travel_time']."' style='width: 50px;'/></td>";
					echo "<td>".$row['status']."</td>";
					// included the instance_id as the Submit button ID - not used but might be useful later
					echo "<td><input id='".$row['instance_id']."' type='submit' value='Update'/></td></form>";
					// allows status for the selected instance of the event to be updated
					// THE JAVASCRIPT DEPENDS ON THE DOM PLACEMENT OF THESE BUTTONS BEING EXACTLY IN THIS POSITION!!
					echo "<td><button class='markComp' data-instID='".$row['instance_id']."'>Complete</button></td>";
					echo "<td><button class='markCanx' data-instID='".$row['instance_id']."'>Cancel</button></td>";
					echo "<td><button
						class='mark2mo' 
						data-instID='".$row['instance_id']."' 
						data-eventID='".$row['event_id']."' 
						data-newDate='".date('Y-m-d',$instDateTime->getTimestamp()+86400)."' 
						data-instTime='".$row['time']."' 
						data-travelTime='".$row['travel_time']."'>+1 day</button></td></tr>";
				}
		?>
				</table>
		<?php
			}

			mysqli_free_result($r);
			mysqli_close($planDB);
		?>
	</body>

</html>
