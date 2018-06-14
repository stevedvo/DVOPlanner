<?php
	function var_debug($variable)
	{
		echo "<pre>";
		var_dump($variable);
		echo "</pre>";
	}

	function getAllEventsForQuickAdd()
	{
		global $planDB;

		$query = $planDB->prepare("SELECT event_id, type, description, duration FROM events ORDER BY description");
		$query->execute();
		$result = $query->get_result();

		if ($result->num_rows)
		{
			$rows = [];

			while ($row = $result->fetch_assoc())
			{
				$rows[$row['event_id']] = $row;
			}
		}

		return $rows;
	}

	function getInstancesForDay($date)
	{
		global $planDB;
		$instances = false;

		if ($date instanceof DateTime)
		{
			$date_string = $date->format('Y-m-d');
			$query = $planDB->prepare("SELECT i.instance_id, i.event_id, i.date, i.time, i.travel_time, i.status, e.type, e.description, e.duration FROM instances AS i INNER JOIN events AS e ON (i.event_id = e.event_id) WHERE i.date = ? ORDER BY i.time");
			$query->bind_param("s", $date_string);
			$query->execute();

			$result = $query->get_result();

			if ($result->num_rows)
			{
				$instances = [];

				while ($row = $result->fetch_assoc())
				{
					$instances[$row['instance_id']] = $row;
				}
			}
		}

		return $instances;
	}
