<?php

/*
Queries database for all events.
Returns associative array with event IDs to names.
	array('333' => 'Rubik's Cube', ...)
*/
function getEvents($db) {
	$events = array();
	if ($results = $db->query("select id, cellName from Events where format='time' order by rank")) {
		while ($row = $results->fetch_assoc()) {
			$events[$row['id']] = $row['cellName'];
		}
		$results->close();
	}
	return $events;
}

/*
Queries database for all countries.
Returns associative array with country codes to names.
	array('GB' => 'United Kingdom', ...)
*/
function getCountries($db) {
	$countries = array();
	if ($results = $db->query("select id, iso2 from Countries")) {
		while ($row = $results->fetch_assoc()) {
			$countries[$row['iso2']] = $row['id'];
		}
		$results->close();
	}
	return $countries;
}
