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

		$rows = false;
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
			$query = $planDB->prepare("SELECT i.instance_id, i.event_id, i.date, i.time, i.travel_time, i.status, e.type, e.description, e.duration, e.archived FROM instances AS i INNER JOIN events AS e ON (i.event_id = e.event_id) WHERE i.date = ? ORDER BY i.time");
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

	function updateEvent($request)
	{
		global $planDB;
		$errors = [];
		$eventType = $eventDuration = $eventDesc = $eventId = null;

		if (isset($_POST['event_id']) && is_numeric($_POST['event_id']))
		{
			$eventId = $_POST['event_id'];
		}
		else
		{
			$errors[] = "Invalid Event ID";
		}

		if (isset($_POST['event_desc']) && !empty($_POST['event_desc']))
		{
			$eventDesc = $_POST['event_desc'];
		}
		else
		{
			$errors[] = "Please include a description for the event.";
		}

		if (isset($_POST['event_type']) && !empty($_POST['event_type']))
		{
			$eventType = $_POST['event_type'];
		}
		else
		{
			$errors[] = "Please select a type for the event.";
		}

		if (isset($_POST['event_duration']) && is_numeric($_POST['event_duration']))
		{
			$eventDuration = $_POST['event_duration'];
		}

		if (isset($_POST['e_archived']) && is_numeric($_POST['e_archived']))
		{
			$eventArchived = 1;
		}
		else
		{
			$eventArchived = 0;
		}

		if (empty($errors))
		{
			$query = $planDB->prepare("UPDATE events SET type = ?, description = ?, duration = ?, archived = ? WHERE event_id = ?");
			$query->bind_param("ssiii", $eventType, $eventDesc, $eventDuration, $eventArchived, $eventId);
			$query->execute();

			$result = $query->affected_rows;

			if (!$result)
			{
				$errors[] = "No Events updated.";
			}
		}

		return $errors;
	}

	function getAllEvents()
	{
		global $planDB;
		$events = false;

		$query = $planDB->prepare("SELECT e.event_id, e.type, e.description, e.duration, e.archived, i.instance_id, i.date, i.time, i.travel_time, i.status FROM events AS e LEFT JOIN instances AS i ON (i.event_id = e.event_id) ORDER BY e.description, i.date");
		$query->execute();

		$result = $query->get_result();

		if ($result->num_rows)
		{
			$events = $instances = $event_dictonary = [];

			while ($row = $result->fetch_assoc())
			{
				if (!array_key_exists($row['event_id'], $event_dictonary))
				{
					$event_dictonary[$row['event_id']] = $row['event_id'];
					$events[$row['event_id']]['event_id'] = $row['event_id'];
					$events[$row['event_id']]['type'] = $row['type'];
					$events[$row['event_id']]['description'] = $row['description'];
					$events[$row['event_id']]['duration'] = $row['duration'];
					$events[$row['event_id']]['archived'] = $row['archived'];

					if (!is_null($row['instance_id']))
					{
						$events[$row['event_id']]['instances'][$row['instance_id']]['date'] = $row['date'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['time'] = $row['time'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['travel_time'] = $row['travel_time'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['status'] = $row['status'];
					}
				}
				else
				{
					if (!is_null($row['instance_id']))
					{
						$events[$row['event_id']]['instances'][$row['instance_id']]['date'] = $row['date'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['time'] = $row['time'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['travel_time'] = $row['travel_time'];
						$events[$row['event_id']]['instances'][$row['instance_id']]['status'] = $row['status'];
					}
				}
			}
		}

		return $events;
	}
