<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- additional options to content to prevent zooming on mobile devices, maximum-scale=1, user-scalable=no"-->
		<title><?php echo $date->format('D d M'); ?></title>
		<link rel="stylesheet" type="text/css" href="assets/bootstrap-3.3.7-dist/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="assets/styles/main.min.css" />
		<script type="text/javascript" src="jQuery/jquery-1.12.3.min.js"></script>
		<script type="text/javascript" src="assets/scripts/main.js"></script>
	</head>

	<body>
		<header>
			<div class="container">
				<h1><?php echo $date->format('l,') ?></h1>
				<h1><?php echo $date->format('d F Y') ?></h1>
				<button id="prevWkBtn">&lt;&lt;7 days</button>
				<button id="prevDayBtn">&lt;Prev Day</button>
				<button id="todayBtn">Today</button>
				<button id="nextDayBtn">Next Day&gt;</button>
				<button id="nextWkBtn">7 Days&gt;&gt;</button><br/>
				<a href="newevent.php"><button>New Event</button></a>
				<a href="viewevents.php"><button>View Events</button></a>
				<a href="allinstances.php"><button>View All Instances</button></a>
			</div>
		</header>
		<hr/>