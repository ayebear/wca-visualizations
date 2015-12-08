<?php
include 'dbconfig.php';

/*
Inputs:
	Event (333/444/555)
	Gender (m/f/*)
	Statistic name (overall/top of average/best times)

Output:
	Example JSON object:
	{
		"US": {
			"1982": 22.95,
			"2014": 5.93,
			"2015": 4.9
		},
		...
	}
*/


function getTimedStats($db, $event, $genderQuery, $stat) {
	$mapping = ['topBest' => ['min', 'best'],
				'topAverage' => ['min', 'average'],
				'overallBest' => ['avg', 'best'],
				'overallAverage' => ['avg', 'average']];

	$data = [];

	// Get function and field names from statistic name
	$funcName = $mapping[$stat][0];
	$fieldName = $mapping[$stat][1];

	if (!$funcName || !$fieldName) {
		return [];
	}

	// Compute specified statistics for each country and year
	$results = $db->query("select countryCode, year, $funcName($fieldName) as result from AllResults
		where $fieldName>0 and eventId='$event' $genderQuery group by countryCode, year");
	if ($results) {
		while ($row = $results->fetch_assoc()) {
			$data[$row['countryCode']][$row['year']] = $row['result'] / 100.0;
		}
	}
	return $data;
}

function getCompsStats($db, $event, $genderQuery, $funcName) {
	// Compute number of competitions visited for each country and year
	$results = $db->query("select countryCode, year, $funcName(personId) as result from AllResults
		where personId>0 and eventId='$event' $genderQuery group by countryCode, year");
	if ($results) {
		while ($row = $results->fetch_assoc()) {
			$data[$row['countryCode']][$row['year']] = $row['result'];
		}
	}
	return $data;
}

function getCubersStats($db, $event, $genderQuery) {
	// Compute number of competitions visited for each country and year
	$results = $db->query("select countryCode, year, count(distinct personId) as result from AllResults
		where eventId='$event' $genderQuery group by countryCode, year");
	if ($results) {
		while ($row = $results->fetch_assoc()) {
			$data[$row['countryCode']][$row['year']] = $row['result'];
		}
	}
	return $data;
}

function getResultsStats($db, $event, $genderQuery) {
	// Compute number of competitions visited for each country and year
	$results = $db->query("select countryCode, year, count(*) as result from AllResults
		where eventId='$event' $genderQuery group by countryCode, year");
	if ($results) {
		while ($row = $results->fetch_assoc()) {
			$data[$row['countryCode']][$row['year']] = $row['result'];
		}
	}
	return $data;
}

function getStats($db, $event, $gender, $stat) {
	$data = [];

	// Stop tem b1tch3s frum injectin SQL
	$event = $db->real_escape_string($event);
	$gender = $db->real_escape_string($gender);
	$stat = $db->real_escape_string($stat);

	$genderQuery = '';
	if ($gender && $gender != '' && $gender != '*') {
		$genderQuery = "and gender='$gender'";
	}

	// TODO: Handle additional statistics
	switch ($stat) {
		case 'compsVisitedBest':
			$data = getCompsStats($db, $event, $genderQuery, "min");
			break;

		case 'compsVisitedAverage':
			$data = getCompsStats($db, $event, $genderQuery, "avg");
			break;

		case 'numCubers':
			$data = getCubersStats($db, $event, $genderQuery);
			break;

		case 'numResults':
			$data = getResultsStats($db, $event, $genderQuery);
			break;

		default:
			$data = getTimedStats($db, $event, $genderQuery, $stat);
			break;
	}


	return json_encode($data);
}

if ($_GET['event'] && $_GET['stat']) {
	$db = getDb();
	$jsonResults = getStats($db, $_GET['event'], $_GET['gender'], $_GET['stat']);
	echo $jsonResults;
	$db->close();
}
