<?php
	date_default_timezone_set('UTC');
	// opens DB connexion
	require ('../../init_DVOPlan.php');

	(empty($_GET['offset'])) ? $offset = 0 : $offset = $_GET['offset'];

	$timestamp = time() + $offset;
	$date = new DateTime(date('Y-m-d', $timestamp));

	include ('header.php');
?>
	<main class="homepage">
		<div class="container">
<?php
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
			$q = "SELECT * ";
			$q.= "FROM instances ";
			$q.= "INNER JOIN events ON events.event_id = instances.event_id ";
			$q.= "WHERE date = '".$date->format('Y-m-d')."' ";
			// $q .= "	AND status = 'To Do' ";
			$q.= "ORDER BY time";
			
			$r = mysqli_query($planDB, $q);

			if (mysqli_num_rows($r)===0)
			{
				echo "<p>No Events Today</p>";
			}
			else
			{
?>
				<div class="row">
					<section class="results-container">
<?php
						while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
						{
							$instDateTime = new DateTime($row['date']. " ".$row['time']);
?>
							<article class="" data-status="<?= $row['status']; ?>">
								<form class="" method="POST" action="<?= $_SERVER['REQUEST_URI'];?>">
									<input type="hidden" name="instance_id" value="<?= $row['instance_id']; ?>" />
									<input type="hidden" name="event_id" value="<?= $row['event_id']; ?>" />
									<input type="hidden" name="inst_date" value="<?= $row['date']; ?>" />
									<input type="hidden" name="description" value="<?= $row['description']; ?>" />
									<input type="hidden" name="currStatus" value="<?= $row['status']; ?>" />
									<div class="task-description-container result-item">
										<a href="view1event.php?event_id=<?= $row['event_id']; ?>"><?= $row['description']; ?></a>
									</div>
									<div class="task-type-container result-item">
										<a href="view1event.php?event_id=<?= $row['event_id']; ?>"><?= $row['type']; ?></a>
									</div>
									<div class="start-travel-container result-item">
										Start Travel: <?= date('H:i',$instDateTime->getTimestamp()-60*$row['travel_time']) ;?>
									</div>
									<div class="task-travel-container result-item">
										Travel: <input name="travel_time" type="number" min="0" value="<?= $row['travel_time']; ?>" />
									</div>
									<div class="start-task-container result-item">
										Start Task: <input name="inst_time" type="time" value="<?= $row['time']; ?>" />
									</div>
									<div class="end-task-container result-item">
										End Task: <?= date('H:i',$instDateTime->getTimestamp()+60*$row['duration']); ?>
									</div>
									<div class="task-status-container result-item">
										Status: <?= $row['status']; ?>
									</div>
									<div class="task-update-container result-item">
										<input id="<?= $row['instance_id']; ?>" class="fw" type="submit" value="Update" />
									</div>
									<div class="task-complete-container change-status">
										<button class="markComp" data-instID="<?= $row['instance_id']; ?>">Complete</button>
									</div>
									<div class="task-cancel-container change-status">
										<button class="markCanx" data-instID="<?= $row['instance_id']; ?>">Cancel</button>
									</div>
									<div class="task-postpone-container change-status">
										<button class="mark2mo" data-instID="<?= $row['instance_id']; ?>" data-eventID="<?= $row['event_id']; ?>" data-newDate="<?= date('Y-m-d',$instDateTime->getTimestamp()+86400); ?>" data-instTime="<?= $row['time']; ?>" data-travelTime="<?= $row['travel_time']; ?>">+1 day</button>
									</div>
								</form>
							</article>
<?php
						}
?>
					</section>
				</div>
<?php
			}

			mysqli_free_result($r);
			mysqli_close($planDB);
?>
		</div>
	</main>
<?php
	include ('footer.php');
?>