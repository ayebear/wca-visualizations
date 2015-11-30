<?php

/*
Queries database for all events.
Returns associative array with event IDs to names.
	array('333' => 'Rubik's Cube', ...)
*/
function getEvents($db) {
	$events = array();
	if ($results = $db->query("select id, cellName from Events where format='time' order by rank;")) {
		while ($row = $results->fetch_assoc()) {
			$events[$row['id']] = $row['cellName'];
		}
		$results->close();
	}
	return $events;
}
