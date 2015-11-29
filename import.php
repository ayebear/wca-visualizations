<?php
include 'dbconfig.php';

/*
Maps all specified fields directly to the new table.
Expects both source and destination tables to contain correct columns.
*/
function importTable($db, $sourceTable, $destTable, $fields, $rename) {
	$results = $db->query("select * from $sourceTable");
	while ($row = $results->fetch_assoc()) {
		// Get values from source table
		$values = array();
		foreach ($fields as $key => $value) {
			$values[] = $row[$value];
		}

		$insertQuery = "insert into $destTable values (" . implode(',', $values) . ')';
		$db->query($insertQuery);
	}
}

function renameTables($db) {
	// Rename tables
	$tableNames = array('Results', 'Competitions', 'Persons', 'Countries', 'Events');
	foreach ($tableNames as $key => $value) {
		$db->query("alter table $value rename to {$value}_old");
	}
}

function stripTable($db, $tableName, $columnsToKeep) {
	$results = $db->query("select * from $tableName limit 1");
	while ($row = $results->fetch_assoc()) {
		$columnsToRemove = array();
		// Get all columns to remove
		foreach ($row as $key => $value) {
			if (!in_array($key, $columnsToKeep)) {
				$columnsToRemove[] = $key;
			}
		}

		// Remove the columns not specified to keep
		echo "Removing (" . implode(', ', $columnsToRemove) . ") from table '$tableName'...<br>";
		foreach ($columnsToRemove as $key => $value) {
			if (!$db->query("alter table $tableName drop column $value")) {
				echo "Error dropping column '$value'.<br>";
			}
		}
	}
}

function stripTables($db) {
	stripTable($db, 'Results', array('competitionId', 'eventId', 'personId', 'best', 'average'));
	stripTable($db, 'Competitions', array('id', 'year', 'month', 'day', 'eventSpecs', 'latitude', 'longitude'));
	stripTable($db, 'Persons', array('id', 'name', 'countryId', 'gender'));
	stripTable($db, 'Countries', array('id', 'name', 'latitude', 'longitude', 'iso2'));
	stripTable($db, 'Events', array('id', 'name', 'rank', 'format', 'cellName'));
}

function dropTables($db) {
	// Drop unused tables
	$tableNames = array(
		'Continents',
		'Formats',
		'RanksAverage',
		'RanksSingle',
		'Rounds',
		'Scrambles');
	foreach ($tableNames as $key => $value) {
		if ($db->query("drop table $value")) {
			echo "Dropped table '$value'.<br>";
		}
		else {
			echo "Error dropping table '$value'.<br>";
		}
	}
}

$db = getDb();

stripTables($db);
dropTables($db);

$db->close();
