<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
		<title><?= $date->format('D d M'); ?></title>
		<link rel="stylesheet" type="text/css" href="assets/bootstrap-3.3.7-dist/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="assets/styles/main.css" />
		<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript" src="assets/scripts/main.js"></script>
		<script type="text/javascript">
			$(function()
			{
				availableEvents = [];
<?php
				foreach ($events as $event_id => $details)
				{
?>
					event = 
					{
						id          : <?= $event_id; ?>,
						type        : '<?= $details['type']; ?>',
						description : '<?= $details['description']; ?>',
						duration    : '<?= $details['duration']; ?>'
					}

					availableEvents.push(event);
<?php
				}
?>
			});
		</script>
	</head>

	<body>
		<header>
			<div class="container">
				<div class="container-inner">
					<h1><?= $date->format('l, d F Y') ?></h1>

					<div class="quick-add-container">
						<label for="quick-add">Quick Add: </label>
						<form method="POST">
							<input id="quick-add" type="text" name="description" />
							<input type="hidden" name="event_id" />
							<input type="hidden" name="type" />
							<input type="hidden" name="duration" />
							<input type="hidden" name="date" value="<?= $date->format('Y-m-d'); ?>" />
							<input type="submit" name="quick-add" value="Add" />
						</form>
						<ul class="autocomplete-container"></ul>
					</div>

					<div class="nav-buttons-container">
						<button class="prev week" id="prevWkBtn">&lt;&lt;7 days</button>
						<button class="prev day" id="prevDayBtn">&lt;Prev Day</button>
						<button class="" id="todayBtn">Today</button>
						<button class="next day" id="nextDayBtn">Next Day&gt;</button>
						<button class="next week" id="nextWkBtn">7 Days&gt;&gt;</button>
					</div>

					<div class="link-buttons-container">
						<a href="newevent.php"><button>New Event</button></a>
						<a href="viewevents.php"><button>View Events</button></a>
						<a href="allinstances.php"><button>All Instances</button></a>
					</div>
				</div>
			</div>
		</header>