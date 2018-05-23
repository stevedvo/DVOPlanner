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
