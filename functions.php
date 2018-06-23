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
				$events = [];

				while ($row = $result->fetch_assoc())
				{
					$instances[$row['instance_id']] = $row;
					$events[] = $row['event_id'];
				}

				$events_string = implode(",", $events);
				$query = $planDB->prepare("SELECT i.instance_id, i.event_id, i.date, i.time FROM instances AS i WHERE i.date >= ? AND i.event_id IN ($events_string) ORDER BY i.event_id, i.date");
				$query->bind_param("s", $date_string);
				$query->execute();

				$result = $query->get_result();

				if ($result->num_rows)
				{
					$event_instances = $next_instance = [];

					while ($row = $result->fetch_assoc())
					{
						$event_instances[$row['event_id']][] = $row['date'];
					}

					foreach ($event_instances as $event_id => $next_instances)
					{
						if (count($next_instances) > 1)
						{
							$next_instance[$event_id] = $next_instances[1];
						}
					}
				}

				foreach ($instances as $instance_id => $instance)
				{
					if (array_key_exists($instance['event_id'], $next_instance))
					{
						$instances[$instance_id]['next_instance'] = DateTime::createFromFormat('Y-m-d', $next_instance[$instance['event_id']]);
					}
					else
					{
						$instances[$instance_id]['next_instance'] = false;
					}
				}
			}
		}

		return $instances;
	}
