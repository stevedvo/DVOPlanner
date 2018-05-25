<?php
	if (!isset($_GET['event_id']))
	{
		include ('viewevents.php');
	}
	else
	{
		include_once('site_init.php');

		$q = "SELECT description ".
		     "FROM events ".
		     "WHERE event_id = ".$_GET['event_id'];

		$r = mysqli_query($planDB, $q);

		if (mysqli_num_rows($r) === 0)
		{
			include ('viewevents.php');
		}
		else
		{
			$pageTitle = mysqli_fetch_array($r, MYSQLI_NUM)[0];
?>
			<!DOCTYPE html>

			<html lang="en">

				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
					<title><?= $pageTitle; ?></title>
					<link rel="stylesheet" type="text/css" href="assets/bootstrap-3.3.7-dist/css/bootstrap.min.css" />
					<link rel="stylesheet" type="text/css" href="assets/styles/main.css" />
					<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
					<script type="text/javascript" src="assets/scripts/main.js"></script>
				</head>

				<body class="event-single">
					<header>
						<div class="container">
							<div class="container-inner">
								<h1><?= $pageTitle; ?></h1>
								<div class="nav-container">
									<a href="homepage.php"><button>Back To Today</button></a>
									<a href="newevent.php"><button>Add Event</button></a>
									<a href="viewevents.php"><button>View Events</button></a>
								</div>
<?php
								if ($_SERVER['REQUEST_METHOD']==="POST")
								{
									$errors = [];

									if ($_POST['post_type']=="amend_event")
									{
										if (empty($_POST['description']))
										{
											$errors[] = "Need an event description.";
										}
										else
										{
											$eventDesc = $_POST['description'];
										}

										if (empty($_POST['duration']))
										{
											$eventDuration = NULL;
										}
										else
										{
											$eventDuration = $_POST['duration'];
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
											$q = "UPDATE events ";
											$q.= "SET type = '".$_POST['type']."', description = '".$eventDesc."', duration = ";
											if (is_null($eventDuration))
											{
												$q.= "NULL ";
											}
											else
											{
												$q.= $eventDuration." ";
											}
											$q.= "WHERE event_id = ".$_POST['event_id'];

											$r = mysqli_query($planDB, $q);
										}
									}
									if ($_POST['post_type']=="add_inst")
									{
										if (empty($_POST['inst_date']))
										{
											$errors[] = "New Instance Date required.";
										}
										else
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
													$errors[] = 'Invalid Date';
												}
												else
												{
													$firstDate = new DateTime($_POST['inst_date']);
												}
											}
										}

										if (!empty($_POST['inst_time']))
										{
											// regular expression to ensure time is "HH:mm"
											$n = preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $_POST['inst_time']);

											if (!$n)
											{
												$errors[] = 'Time must be in format HH:mm';
											}
											else
											{
												$instTime = $_POST['inst_time'];
											}
										}
										else
										{
											$instTime = NULL;
										}

										if ($_POST['event_repeat']!=="No_Repeat")
										{
											// checks repeat until date is entered and valid
											if (empty($_POST['event_repeat_end']))
											{
												$errors[] = "Repeat Until Date required.";
											}
											else
											{
												// regular expression to ensure date is "yyyy-mm-dd"
												$n = preg_match("/\d{4}-\d{2}-\d{2}/", $_POST['event_repeat_end']);

												if (!$n)
												{
													$errors[] = 'Repeat Until Date must be in format yyyy-mm-dd';
												}
												else
												//if date in current format, ensures that date is valid
												//checkdate() has issues if non-numeric data entered, hence the regex first
												{
													$eventDay = substr($_POST['event_repeat_end'], 8, 2);
													$eventMonth = substr($_POST['event_repeat_end'], 5, 2);
													$eventYear = substr($_POST['event_repeat_end'], 0, 4);
													if (!checkdate($eventMonth, $eventDay, $eventYear))
													{
														$errors[] = 'Invalid Repeat Until Date';
													}
													else
													{
														$finalDate = new DateTime($_POST['event_repeat_end']);
													}
												}
											}
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
										else
										{
											$q = "INSERT into instances (event_id, date, ";
											if (!is_null($instTime))
											{
												$q.= "time, ";
											}
											$q.= "status) VALUES (".$_POST['event_id'].", '".$firstDate->format('Y-m-d')."', ";
											if (!is_null($instTime))
											{
												$q.= "'".$instTime."', ";
											}
											$q.= "'To Do')";

											$r = mysqli_query($planDB, $q);

											$Wkends = TRUE; // sets a flag to ensure event instances do not occur on weekends (if required)
											switch ($_POST['event_repeat']) // determines the string to go into $date->modify()
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
												$nextDate = $firstDate->modify($offsetStr);
												$nextDateStamp = $nextDate->getTimestamp();

												$finalDateStamp = $finalDate->getTimestamp();

												// compares next & last dates and inserts as many records in instances table as required
												while (false===$nextDateStamp>$finalDateStamp)
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
													if (!is_null($instTime))
													{
														$q .= "time, ";
													}
													$q .= "status) VALUES (".$_POST['event_id'].", '".$nextDate->format('Y-m-d')."', ";
													if (!is_null($instTime))
													{
														$q .= "'".$instTime."', ";
													}
													$q .= "'To Do')";

													$r = mysqli_query($planDB, $q);
							
													// calculates date for next event before re-comparing with last (final) date
													$nextDate = $nextDate->modify($offsetStr);
													$nextDateStamp = $nextDate->getTimestamp();
												}
											}
										}
									}
									if ($_POST['post_type']=="amend_inst")
									{
										$errors = [];

										if (empty($_POST['inst_date']))
										{
											$errors[] = "Amended Instance Date required..";
										}
										else
										{
											// regular expression to ensure date is "yyyy-mm-dd"
											$n = preg_match("/\d{4}-\d{2}-\d{2}/", $_POST['inst_date']);

											if (!$n)
											{
												$errors[] = 'Amended date must be in format yyyy-mm-dd';
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
													$errors[] = 'Invalid Amended Instance Date';
												}
												else
												{
													$instDate = $_POST['inst_date'];
												}
											}
										}

										if (!empty($_POST['inst_time']))
										{
											// regular expression to ensure time is "HH:mm"
											// substr() used in case POST passes time as HH:MM:ss
											$n = preg_match("/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", substr($_POST['inst_time'],0,5));
											$needUpdate = TRUE;

											if (!$n)
											{
												$errors[] = 'Amended time for event instance must be in format HH:mm';
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
											// $q is SQL query to update an existing record in instances table
											$q = "UPDATE instances ";
											$q.= "SET date = '".$instDate."', time = ";
											if (!is_null($instTime))
											{
												$q.= "'".$instTime."' ";
											}
											else
											{
												$q.= "NULL ";
											}
											$q.= "WHERE instance_id = ".$_POST['instance_id'];

											$r = mysqli_query($planDB, $q);
										}
									}
								} // end of if..POST statments
?>
							</div>
						</div>
					</header>

					<main>
						<div class="container">
							<div class="container-inner">
<?php
								$q = "SELECT * ";
								$q.= "FROM events ";
								$q.= "WHERE event_id = ".$_GET['event_id'];

								$r = mysqli_query($planDB, $q);
								$event = mysqli_fetch_array($r, MYSQLI_ASSOC);
?>
								<div class="row">
									<!-- form to amend basic event details -->
									<form class="amend-event col-sm-6" method='POST' action='<?= $_SERVER['REQUEST_URI']; ?>'>
										<input type='hidden' name='post_type' value='amend_event' />
										<input type='hidden' name='event_id' value='<?= $_GET['event_id']; ?>' />
										<fieldset>
											<legend>View / Amend Event Details</legend>
											<div class="input-row">
												<div class="name">
													<p>Type:</p>
												</div>
												<div class="value">
													<select name='type'>
														<option <?= $event['type'] === "Task" ? "selected" : ""; ?> value='Task'>Task</option>
														<option <?= $event['type'] === "Meeting" ? "selected" : ""; ?> value='Meeting'>Meeting</option>
														<option <?= $event['type'] === "Appointment" ? "selected" : ""; ?> value='Appointment'>Appointment</option>
													</select>
												</div>
											</div>
											<div class="input-row">
												<div class="name">
													<p>Description:</p>
												</div>
												<div class="value">
													<input name='description' value='<?= $event['description']; ?>' />
												</div>
											</div>
											<div class="input-row">
												<div class="name">
													<p>Duration (mins):</p>
												</div>
												<div class="value">
													<input name='duration' type='number' min='0' value='<?= $event['duration']; ?>' />
												</div>
											</div>
											<input type='submit' value='Amend Details' />
										</fieldset>
									</form>

									<!-- form to add new instance(s) -->
									<form class="add-instance col-sm-6" method='POST' action='<?= $_SERVER['REQUEST_URI']; ?>'>
										<input type='hidden' name='post_type' value='add_inst' >
										<input type='hidden' name='event_id' value='<?= $_GET['event_id']; ?>' />
										<fieldset>
											<legend>Add New Instance(s)</legend>
											<div class="input-row">
												<div class="name">
													<p>Date:</p>
												</div>
												<div class="value">
													<input type='date' name='inst_date' placeholder='Required [yyyy-mm-dd]' />
												</div>
											</div>
											<div class="input-row">
												<div class="name">
													<p>Time:</p>
												</div>
												<div class="value">
													<input type='time' name='inst_time' placeholder='Optional [HH:mm]' />
												</div>
											</div>
											<div class="input-row">
												<div class="name">
													<p>Repeat:</p>
												</div>
												<div class="value">
													<select name='event_repeat'>
															<option selected value='No_Repeat'>No Repeat</option>
															<option value='Weekdays'>Weekdays</option>
															<option value='Every_Day'>Every Day</option>
															<option value='Weekly'>Weekly</option>
															<option value='Fortnightly'>Fortnightly</option>
															<option value='Monthly'>Monthly</option>
													</select>
												</div>
											</div>
											<div class="input-row">
												<div class="name">
													<p>Until:</p>
												</div>
												<div class="value">
													<input type='date' name='event_repeat_end' placeholder='Optional [yyyy-mm-dd]' />
												</div>
											</div>
											<input type='submit' value='Add Instance(s)' />
										</fieldset>
									</form>
								</div>

								<!-- output table of instances for this event -->
<?php
								$q = "SELECT * ".
								     "FROM instances ".
								     "INNER JOIN events ON events.event_id = instances.event_id ".
								     "WHERE events.event_id = ".$_GET['event_id']." ".
								     "ORDER BY date";

								$r = mysqli_query($planDB, $q);

								if (mysqli_num_rows($r) === 0)
								{
									echo "No Instances of '".$_GET['description']."' to display.";
								}
								else
								{
									$now = new DateTime();
?>
									<button class="historic-trigger">Show Historic</button>

									<div class="results-container">
										<div class="list-header row">
											<div class="column-heading col-xs-5">
												<p>Date</p>
											</div>

											<div class="column-heading col-xs-2">
												<p>Time</p>
											</div>

											<div class="column-heading col-xs-3">
												<p>Status</p>
											</div>

											<div class="column-heading col-xs-2">
												<p>Update</p>
											</div>
										</div>

										<div class="list-body col-xs-12">
<?php
											while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
											{
												$dateOffset = new DateTime($row['date']);
												$diff = $now->diff($dateOffset);
												$diffMonths = ($diff->y*12 + $diff->m)*$diff->invert;
												$dateOffset = $dateOffset->getTimestamp() - time();

												$class = $diffMonths > 3 ? "historic" : "";
?>
												<div class="row list-item <?= $class; ?>" data-status="<?= $row['status']; ?>">
													<form method='POST' action='<?= $_SERVER['REQUEST_URI']; ?>'>
														<input type='hidden' name='post_type' value='amend_inst'/>
														<input type='hidden' name='instance_id' value='<?= $row['instance_id']; ?>' />

														<div class="column-data date">
															<input type='date' name='inst_date' value='<?= $row['date']; ?>' />
														</div>

														<div class="column-data time">
															<input type='time' name='inst_time' value='<?= $row['time']; ?>' />
														</div>

														<div class="column-data status">
															<a href='homepage.php?offset=<?= $dateOffset; ?>'><?= $row['status']; ?></a>
														</div>

														<div class="column-data update">
															<input type='submit' value='Update' />
														</div>
													</form>
												</div>
<?php
											}
?>
										</div>
									</div>
<?php
								}
?>
							</div>
						</div>
					</main>
				</body>
			</html>
<?php
		}
	}
