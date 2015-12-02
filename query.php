<?php
include 'dbconfig.php';

function getTimedStats($db, $event, $gender, $stat) {
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

function getStats($db, $event, $gender, $stat) {
	$data = [];

	// TODO: Use prepared statements
	// TODO: Handle other statistics

	$genderQuery = '';
	if ($gender && $gender != '' && $gender != '*') {
		$genderQuery = "and gender='$gender'";
	}

	switch ($stat) {
		case 'compsVisitedBest':
			break;

		case 'compsVisitedAverage':
			break;

		case 'numCubers':
			break;

		default:
			$data = getTimedStats($db, $event, $gender, $stat);
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



/*
Inputs:
	Event (3x3/4x4/5x5)
	Gender (Male/female/any)
	Result type (overall/top of average/best times)

Output:
	JSON object:
	{
	  "2014": {
	    "USA": 8.50,
	    "CN": 9.67,
	    "AS": 7.23
	  },
	  "2015": {
	    "USA": 7.54,
	    "CN": 9.22,
	    "AS": 6.77
	  }
	}
*/
