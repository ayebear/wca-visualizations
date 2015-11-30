<?php
include 'dbconfig.php';
include 'common.php';

/*
This file computes statistics and saves them to a new table in the database.

Statistics table with example values:
	eventId
		'333'
	gender
		'm'
	statType
		'topAverage'
	year
		'2015'
	countryCode
		'US'
	result
		7.13
*/

function createStatsTable($db) {
	$db->query("drop table if exists Statistics");
	$db->query("create table Statistics (eventId varchar(6), gender char(1),
		statType varchar(64), year smallint(5) unsigned, countryCode char(2), result decimal(10, 2))");
}

function getStat($db, $resultType, $statType, $eventId, $countryCode, $genderQuery, $year) {
	$queryStr = "select {$statType}({$resultType}) as result from AllResults where {$resultType}>0
		and eventId='$eventId' and countryCode='$countryCode' $genderQuery and year<{$year}";
	// echo "Query: $queryStr <br>";
	$results = $db->query($queryStr);
	if ($row = $results->fetch_assoc()) {
		return $row['result'] / 100.0;
	}
	return 0;
}

function saveStat($db, $eventId, $gender, $statName, $year, $countryCode, $statResult) {
	$db->query("insert into Statistics values ('$eventId', '$gender', '$statName', $year, '$countryCode', $statResult)");
}

function computeStats() {
	$db = getDb();
	$events = getEvents($db);
	$countries = getCountries($db);
	$genders = ['m', 'f', '*'];

	createStatsTable($db);

	// Compute statistics for each event, gender, country, and year
	foreach ($events as $eventId => $eventName) {
		echo '.';
		foreach ($genders as $gender) {

			$genderQuery = "and gender='$gender'";
			if ($gender == '*')
				$genderQuery = '';

			foreach ($countries as $countryCode => $countryName) {

				for ($year = 2003; $year < 2016; $year++) {
					$stats = array();

					// Compute stat results
					$stats['topBest'] = getStat($db, 'best', 'min', $eventId, $countryCode, $genderQuery, $year);
					$stats['topAverage'] = getStat($db, 'average', 'min', $eventId, $countryCode, $genderQuery, $year);
					$stats['overallBest'] = getStat($db, 'best', 'avg', $eventId, $countryCode, $genderQuery, $year);
					$stats['overallAverage'] = getStat($db, 'average', 'avg', $eventId, $countryCode, $genderQuery, $year);
					$stats['compsVisitedBest'] = 1; // E.g., 120 competitions for 3x3 - top person in US
					$stats['compsVisitedAverage'] = 2; // E.g., 15 competitions per person for 3x3 on average in US
					$stats['numCubers'] = 3; // E.g., 5000 cubers in US

					// Store stats
					foreach ($stats as $statName => $statResult) {
						// echo "$eventId, $gender, $statName, $year, $countryCode, '$statResult' <br>";
						saveStat($db, $eventId, $gender, $statName, $year, $countryCode, $statResult);
					}
				}
			}
		}
	}

	$db->close();
}

computeStats();
