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

		// $events = getAllEvents();
		$q = "SELECT * ";
		$q.= "FROM events ";
		$q.= "ORDER BY description ";

		$r = mysqli_query($planDB, $q);

		if (mysqli_num_rows($r)===0)
		{
			echo "<p>No Events To Display</p>";
		}
		else
		{
			echo "<table style='background: #AAA;'>";
				echo "<tr><td>Description</td><td>Type</td><td>Duration</td><td>Last Complete</td><td>Next Due</td></tr>";
				while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
				{
					$q_last = "SELECT date ";
					$q_last.= "FROM instances ";
					$q_last.= "WHERE event_id = ".$row['event_id']." AND status = 'Complete' ";
					$q_last.= "ORDER BY date DESC";

					$r_last = mysqli_query($planDB, $q_last);

					if (mysqli_num_rows($r_last)===0)
					{
						$lastComp = NULL;
					}
					else
					{
						$lastComp = new DateTime(mysqli_fetch_array($r_last, MYSQLI_NUM)[0]);
						$lastOffset = $lastComp->getTimestamp() - time();
					}

					$q_next = "SELECT date ";
					$q_next.= "FROM instances ";
					$q_next.= "WHERE event_id = ".$row['event_id']." AND status = 'To Do' ";
					$q_next.= "ORDER BY date";

					$r_next = mysqli_query($planDB, $q_next);

					if (mysqli_num_rows($r_next)===0)
					{
						$nextDue = NULL;
					}
					else
					{
						$nextDue = new DateTime(mysqli_fetch_array($r_next, MYSQLI_NUM)[0]);
						$nextOffset = $nextDue->getTimestamp() - time();
					}

					echo "<tr><form method='POST' action='viewevents.php'>";
					echo "<input type='hidden' name='event_id' value='".$row['event_id']."'/>";
					echo "<td><input name='event_desc' value='".$row['description']."' style='width: 320px; background: #AAA;'/></td>";
					echo "<td><a href='view1event.php?event_id=".$row['event_id']."'>".$row['type']."</a></td>";
					echo "<td><input name='event_duration' type='number' min='0' value='".$row['duration']."' style='width: 60px; background: #AAA;'/></td>";
					echo "<td>";
						if ($lastComp !== NULL)
						{
							echo "<a href='homepage.php?offset=".$lastOffset."'>".$lastComp->format('d-M-Y')."</a>";
						}
					echo "</td>";
					echo "<td>";
						if ($nextDue !== NULL)
						{
							echo "<a href='homepage.php?offset=".$nextOffset."'>".$nextDue->format('d-M-Y')."</a>";
						}
					echo "</td>";
					echo "<td><input type='submit' value='Update' style='background: #AAA;'/></td></tr></form>";
				}
			echo "</table>";
		}
		mysqli_close($planDB);
?>
	</body>
</html>
