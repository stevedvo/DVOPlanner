<?php
	include_once('site_init.php');
?>

<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
		<title>Add New Event</title>
		<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript">
			// anticipating this will be used in more interactive UI later
			function init()
			{
			}
			document.addEventListener("DOMContentLoaded", init, false);
		</script>
	</head>

	<body>
		<h1>Add New Event</h1>
		<a href="homepage.php"><button>Back To Today</button></a>
		<a href="viewevents.php"><button>View Events</button></a>
		<hr/>
		<?php
			// POSTed form returns to this page for error-checking / validation
			if ($_SERVER['REQUEST_METHOD']=='POST')
			{
				$errors = [];

				if (empty($_POST['event_date']))
				{
					$errors[] = 'Event Date';
				}

				if (!empty($_POST['event_date']))
				{
					// regular expression to ensure date is "yyyy-mm-dd"
					$n = preg_match("/\d{4}-\d{2}-\d{2}/", $_POST['event_date']);

					if (!$n)
					{
						$errors[] = 'Date must be in format yyyy-mm-dd';
					}
					else
					//if date in current format, ensures that date is valid
					//checkdate() has issues if non-numeric data entered, hence the regex first
					{
						$eventDay = substr($_POST['event_date'], 8, 2);
						$eventMonth = substr($_POST['event_date'], 5, 2);
						$eventYear = substr($_POST['event_date'], 0, 4);
						if (!checkdate($eventMonth, $eventDay, $eventYear))
						{
							$errors[] = 'Invalid Date';
						}
					}
				}

				if (!empty($_POST['event_time']))
				{
					// regular expression to ensure time is "HH:mm"
					$n = preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $_POST['event_time']);

					if (!$n)
					{
						$errors[] = 'Time must be in format HH:mm';
					}
				}

				if (empty($_POST['event_type']))
				{
					$errors[] = 'Event Type';
				}

				if (empty($_POST['event_description']))
				{
					$errors[] = 'Event Description';
				}

				//check event description not duplicated
				//this is so that a later SQL query can use event.description to find the new auto-generated event_id for use in the instances table
				if (!empty($_POST['event_description']))
				{
					$q = "	SELECT *
							FROM events
							WHERE description='".$_POST['event_description']."'";

					$r = mysqli_query($planDB, $q);

					if (mysqli_num_rows($r)>0)
					{
						$errors[] = "This event description already used. <a href='view1event.php?event_id=".mysqli_fetch_array($r, MYSQLI_ASSOC)['event_id']."'>Click to View.</a>";
					}
				}

				if (empty($_POST['event_repeat']))
				{
					$errors[] = 'Event Repeat Frequency';
				}

				if (empty($_POST['event_repeat_end']) && $_POST['event_repeat']!="No_Repeat")
				{
					// if event is repeating, an end date must be set
					$errors[] = 'Repeat Until Date';
				}

				if (!empty($_POST['event_repeat_end']))
				{
					// another regex & checkdate() combo to ensure date isn't garbage
					$n = preg_match("/\d{4}-\d{2}-\d{2}/", $_POST['event_repeat_end']);

					if (!$n)
					{
						$errors[] = 'Repeat Until Date must be in format yyyy-mm-dd';
					}
					else
					{
						$rptDay = substr($_POST['event_repeat_end'], 8, 2);
						$rptMonth = substr($_POST['event_repeat_end'], 5, 2);
						$rptYear = substr($_POST['event_repeat_end'], 0, 4);
						if (!checkdate($rptMonth, $rptDay, $rptYear))
						{
							$errors[] = 'Invalid Repeat Until Date';
						}
					}
				}

				if (!empty($errors))
				{
					echo "<h3>Error! Please Check / Complete Required Info:</h3>";
					echo "<ul>";
					foreach ($errors as $msg)
					{
						echo "<li>".$msg."</li>";
					}
					echo "</ul>";
				}
				else // do this if no validation errors
				{
					// sets a bunch of PHP variables from the validated POST array
					$firstDate = $_POST['event_date'];
					!empty($_POST['event_time']) ? $time = $_POST['event_time'] : $time = null;
					$eventType = $_POST['event_type'];
					$eventDesc = $_POST['event_description'];
					!empty($_POST['event_duration']) ? $duration = $_POST['event_duration'] : $duration = null;
					$eventRpt = $_POST['event_repeat'];
					!empty($_POST['event_repeat_end']) ? $eventRptEnd = $_POST['event_repeat_end'] : $eventRptEnd = null;
					
					// buildng a SQL query to insert new record into events table
					$q = "INSERT INTO events (type, description";
					if (!is_null($duration))
					{
						$q .= ", duration";
					}
					$q .= ") VALUES ('".$eventType."', '".$eventDesc."'";
					if (!is_null($duration))
					{
						$q .= ", ".$duration;
					}
					$q .= ")";

					$r = mysqli_query($planDB, $q);

					// SQL query to find the ID of the event just created
					$q = "	SELECT event_id
							FROM events
							WHERE description = '".$eventDesc."'";

					$r = mysqli_query($planDB, $q);

					$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

					$eventID = $row['event_id'];

					$date = new DateTime($firstDate);
				
					// building a SQL query to insert the first (or only) instance of the event into instances table
					$q = "INSERT INTO instances (event_id, date, ";
					if (!is_null($time))
					{
						$q .= "time, ";
					}
					$q .= "status) VALUES (".$eventID.", '".$date->format('Y-m-d')."', ";
					if (!is_null($time))
					{
						$q .= "'".$time."', ";
					}
					$q .= "'To Do')";

					$r = mysqli_query($planDB, $q);

					$Wkends = TRUE; // sets a flag to ensure event instances do not occur on weekends (if required)
					switch ($eventRpt) // determines the string to go into $date->modify()
					{
						case 'No_Repeat':
							$offsetStr = null;
							break;
						case 'Weekdays':
							$offsetStr = "+1 day";
							$Wkends = FALSE;
							break;
						case 'Every_Day':
							$offsetStr = "+1 day";
							break;
						case 'Weekly':
							$offsetStr = "+1 week";
							break;
						case 'Fortnightly':
							$offsetStr = "+2 weeks";
							break;
						case 'Monthly':
							$offsetStr = "+1 month";
							break;
					}

					if (!is_null($offsetStr))
					{
						// gets last (final) date for event to occur
						$lastDate = new DateTime($eventRptEnd);
						// gets next date for event to occur
						$nextDate = $date->modify($offsetStr);

						$lastDateStamp = $lastDate->getTimestamp();
						$nextDateStamp = $nextDate->getTimestamp();
						
						// compares next & last dates and inserts as many records in instances table as required
						while (false===$nextDateStamp>$lastDateStamp)
						{
							if (!$Wkends)
							{
								// if $nextDate is a Sat/Sun then add a day to the offset
								// will only happen on Weekday events so could add +2 days rather than a loop
								// but a future revision may allow any weekly/monthly event to also be "Weekdays Only"
								while ($nextDate->format('N') > 5)
								{
									$nextDate = $nextDate->modify("+1 day");
									$nextDateStamp = $nextDate->getTimestamp();
								}
							}

							$q = "INSERT INTO instances (event_id, date, ";
							if (!is_null($time))
							{
								$q .= "time, ";
							}
							$q .= "status) VALUES (".$eventID.", '".$nextDate->format('Y-m-d')."', ";
							if (!is_null($time))
							{
								$q .= "'".$time."', ";
							}
							$q .= "'To Do')";

							$r = mysqli_query($planDB, $q);
	
							// calculates date for next event before re-comparing with last (final) date
							$nextDate = $nextDate->modify($offsetStr);
							$nextDateStamp = $nextDate->getTimestamp();
						}
					}
					echo "<p>Thank you - your event & instance(s) have been created.</p>";
					echo "<p><a href='newevent.php'><button>Add Another?</button></a>";
					echo "<a href='view1event.php?event_id=".$eventID."'><button>View Event</button></a></p>";
				}
			} // end of if POST code
		?>
		<!-- form is "Sticky": all "if (isset($_POST[]))"" statements ensure info is retained in case of validation errors on other field(s)-->
		<form method="POST" action="newevent.php">
			<fieldset>
				<legend>Event Details</legend>
				<!--<table> for initial testing purposes only - replace with more mobile friendly UI later-->
				<table>
					<tr>
						<td>Date:</td>
						<td><input type="date" name="event_date" placeholder="Required [yyyy-mm-dd]" value="<?php if (isset($_POST['event_date'])) echo $_POST['event_date']; ?>"/></td>
					</tr>
					<tr>
						<td>Time:</td>
						<td><input type="time" name="event_time" placeholder="Optional [HH:mm]" value="<?php if (isset($_POST['event_time'])) echo $_POST['event_time']; ?>"/></td>
					</tr>
					<tr>
						<td>Type:</td>
						<td>
							<select name="event_type">
								<?php
									if (isset($_POST['event_type']))
									{
										// ensures that the selected option is "Sticky"
										echo "<option ";
										if ($_POST['event_type'] === "Task")
										{
											echo "selected ";
										}
										echo "value='Task'>Task</option><option ";
										if ($_POST['event_type'] === "Meeting")
										{
											echo "selected ";
										}
										echo "value='Meeting'>Meeting</option><option ";
										if ($_POST['event_type'] === "Appt")
										{
											echo "selected ";
										}
										echo "value='Appt'>Appt</option>";
									}
									else
									{
										echo // default selection if not loaded from POST
										"
											<option selected value='Task'>Task</option>
											<option value='Meeting'>Meeting</option>
											<option value='Appt'>Appt</option>
										";
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Description:</td>
						<td><input type="text" name="event_description" placeholder="Required" value="<?php if (isset($_POST['event_description'])) echo $_POST['event_description']; ?>"/></td>
					</tr>
					<tr>
						<td>Duration:</td>
						<td><input type="number" min="0" name="event_duration" placeholder="Optional (mins)" value="<?php if (isset($_POST['event_duration'])) echo $_POST['event_duration']; ?>"/></td>
					</tr>
					<tr>
						<td>Repeat:</td>
						<td>
							<select name="event_repeat">
								<?php
									if (isset($_POST['event_repeat']))
									{
										// ensures that the selected option is "Sticky"
										echo "<option ";
										if ($_POST['event_repeat'] === "No_Repeat")
										{
											echo "selected ";
										}
										echo "value='No_Repeat'>No Repeat</option><option ";
										if ($_POST['event_repeat'] === "Weekdays")
										{
											echo "selected ";
										}
										echo "value='Weekdays'>Weekdays</option><option ";
										if ($_POST['event_repeat'] === "Every_Day")
										{
											echo "selected ";
										}
										echo "value='Every_Day'>Every Day</option><option ";
										if ($_POST['event_repeat'] === "Weekly")
										{
											echo "selected ";
										}
										echo "value='Weekly'>Weekly</option><option ";
										if ($_POST['event_repeat'] === "Fortnightly")
										{
											echo "selected ";
										}
										echo "value='Fortnightly'>Fortnightly</option><option ";
										if ($_POST['event_repeat'] === "Monthly")
										{
											echo "selected ";
										}
										echo "value='Monthly'>Monthly</option>";
									}
									else
									{
										echo // default selection if not loaded from POST
										"
											<option selected value='No_Repeat'>No Repeat</option>
											<option value='Weekdays'>Weekdays</option>
											<option value='Every_Day'>Every Day</option>
											<option value='Weekly'>Weekly</option>
											<option value='Fortnightly'>Fortnightly</option>
											<option value='Monthly'>Monthly</option>
										";
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Until:</td>
						<td><input type="date" name="event_repeat_end" placeholder="Optional [yyyy-mm-dd]" value="<?php if (isset($_POST['event_repeat_end'])) echo $_POST['event_repeat_end']; ?>"/></td>
					</tr>
				</table>
				<input type="submit" value="Add Event"/>
			</fieldset>
		</form>
	</body>

</html>
