<?php
	include_once('site_init.php');
	$pageTitle = "View Events";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
		<title><?= $pageTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="assets/styles/events-all.css" />
		<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript" src="assets/scripts/main.js"></script>
	</head>

	<body>
		<h1><?= $pageTitle; ?></h1>
		<a href="homepage.php"><button>Back To Today</button></a>
		<a href="newevent.php"><button>Add Event</button></a>
		<a href="viewevents.php"><button>View Events</button></a>
		<hr/>
<?php
		if ($_SERVER['REQUEST_METHOD'] === "POST")
		{
			$result = updateEvent($_POST);

			if (!empty($result))
			{
?>
				<h3>Error!</h3>
				<ul>
<?php
					foreach ($result as $msg)
					{
?>
						<li><?= $msg; ?></li>
<?php
					}
?>
				</ul>
<?php
			}
		}

		$events = getAllEvents();

		if (!$events)
		{
?>
			<p>No Events To Display</p>
<?php
		}
		else
		{
?>
			<button class="show-archived-trigger">Show Archived</button>
			<table style='background: #AAA;'>
				<tr>
					<td>Description</td>
					<td>Type</td>
					<td>Duration</td>
					<td>Last Complete</td>
					<td>Next Due</td>
					<td>Archived</td>
				</tr>
<?php
				foreach ($events as $event_id => $event)
				{
					$lastComp = $lastOffset = $nextDue = $nextOffset = null;

					if (isset($event['instances']))
					{
						foreach ($event['instances'] as $instance_id => $instance)
						{
							if (is_null($nextDue) && $instance['status'] == "To Do")
							{
								$nextDue = new DateTime($instance['date']);
								$nextOffset = $nextDue->getTimestamp() - time();
							}

							if ($instance['status'] == "Complete")
							{
								$lastComp = new DateTime($instance['date']);
								$lastOffset = $lastComp->getTimestamp() - time();
							}
						}
					}
?>
					<tr class="<?= $event['archived'] ? 'archived' : ''; ?>">
						<form method='POST'>
							<input type='hidden' name='event_id' value="<?= $event['event_id']; ?>" />
							<input type='hidden' name='event_type' value="<?= $event['type']; ?>" />
							<td><input name='event_desc' value="<?= $event['description']; ?>" style='width: 320px; background: #AAA;' /></td>
							<td><a href='view1event.php?event_id=<?= $event['event_id']; ?>'><?= $event['type']; ?></a></td>
							<td><input name='event_duration' type='number' min='0' value="<?= $event['duration']; ?>" style='width: 60px; background: #AAA;' /></td>
							<td><?= $lastComp ? "<a href='homepage.php?offset=".$lastOffset."'>".$lastComp->format('d-M-Y')."</a>" : ""; ?></td>
							<td><?= $nextDue ? "<a href='homepage.php?offset=".$nextOffset."'>".$nextDue->format('d-M-Y')."</a>" : ""; ?></td>
							<td><input type="checkbox" name="e_archived" value="1" <?= $event['archived'] ? 'checked' : ''; ?> /></td>
							<td><input type='submit' value='Update' style='background: #AAA;' /></td>
						</form>
					</tr>
<?php
				}
?>
			</table>
<?php
		}
		mysqli_close($planDB);
?>
	</body>
</html>
