<?php
	include_once('site_init.php');

	(empty($_GET['offset'])) ? $offset = 0 : $offset = $_GET['offset'];

	$timestamp = time() + $offset;
	$date = new DateTime(date('Y-m-d', $timestamp));
	$events = getAllEventsForQuickAdd();

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

			$instances = getInstancesForDay($date);
?>
			<div class="row">
				<section class="results-container">
<?php
					if (!$instances)
					{
						echo "<p class='no-events'>No Events Today</p>";
					}
					else
					{
						foreach ($instances as $instance_id => $instance)
						{
							$instDateTime = new DateTime($instance['date']." ".$instance['time']);
?>
							<article class="" data-status="<?= $instance['status']; ?>">
								<form class="" method="POST" action="<?= $_SERVER['REQUEST_URI'];?>">
									<input type="hidden" name="instance_id" value="<?= $instance['instance_id']; ?>" />
									<input type="hidden" name="event_id" value="<?= $instance['event_id']; ?>" />
									<input type="hidden" name="inst_date" value="<?= $instance['date']; ?>" />
									<input type="hidden" name="description" value="<?= $instance['description']; ?>" />
									<input type="hidden" name="currStatus" value="<?= $instance['status']; ?>" />
									<div class="task-description-container result-item">
										<a href="view1event.php?event_id=<?= $instance['event_id']; ?>"><?= $instance['description']; ?></a>
									</div>
									<div class="task-type-container result-item">
										<a href="view1event.php?event_id=<?= $instance['event_id']; ?>"><?= $instance['type']; ?></a>
									</div>
									<div class="start-travel-container result-item">
										Start Travel: <?= date('H:i',$instDateTime->getTimestamp()-60*$instance['travel_time']) ;?>
									</div>
									<div class="task-travel-container result-item">
										Travel: <input name="travel_time" type="number" min="0" value="<?= $instance['travel_time']; ?>" />
									</div>
									<div class="start-task-container result-item">
										Start Task: <input name="inst_time" type="time" value="<?= $instance['time']; ?>" />
									</div>
									<div class="end-task-container result-item">
										End Task: <?= date('H:i',$instDateTime->getTimestamp()+60*$instance['duration']); ?>
									</div>
									<div class="task-status-container result-item">
										<?= $instance['status']; ?>
									</div>
									<div class="event-next-instance-container result-item">
										Next: <?= $instance['next_instance'] ? "<a href='/homepage.php?offset=".($instance['next_instance']->getTimestamp() - time())."'>".$instance['next_instance']->format('d-M')."</a>" : "None"; ?>
									</div>
									<div class="task-update-container result-item">
										<input id="<?= $instance['instance_id']; ?>" class="fw" type="submit" value="Update" />
									</div>
									<div class="task-complete-container change-status">
										<button class="markComp" data-instID="<?= $instance['instance_id']; ?>">Complete</button>
									</div>
									<div class="task-cancel-container change-status">
										<button class="markCanx" data-instID="<?= $instance['instance_id']; ?>">Cancel</button>
									</div>
									<div class="task-postpone-container change-status">
										<button class="mark2mo" data-instID="<?= $instance['instance_id']; ?>" data-eventID="<?= $instance['event_id']; ?>" data-newDate="<?= date('Y-m-d',$instDateTime->getTimestamp()+86400); ?>" data-instTime="<?= $instance['time']; ?>" data-travelTime="<?= $instance['travel_time']; ?>">+1 day</button>
									</div>
								</form>
							</article>
<?php
						}
					}
?>
				</section>
			</div>
		</div>
	</main>
<?php
	include ('footer.php');
