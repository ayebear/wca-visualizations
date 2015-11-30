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

function setupPrimaryKeys($db) {
	// Setup primary keys
	$db->query('alter ignore table Persons add unique (id)');
	$db->query('alter table Events add primary key(id)');
	$db->query('alter table Persons add primary key(id)');
	$db->query('alter table Countries add primary key(iso2)');
	$db->query('alter table Competitions add primary key(id)');
	echo "Setup primary keys.<br>";
}

function generateResults($db) {
	// Setup AllResults table for use with generating statistics
	$db->query('drop table if exists AllResults');

	$db->query('create view ResultsView as select competitionId,eventId,personId,best,average,countryId,gender
		from Results join Persons on Results.personId=Persons.id');

	$db->query('create view ResultsViewCountries as select competitionId,eventId,personId,best,average,countryId,
		Countries.iso2 as countryCode,gender
		from ResultsView join Countries on ResultsView.countryId=Countries.id');

	$db->query('create table AllResults (competitionId varchar(32), eventId varchar(6), personId varchar(10),
		best int(11), average int(11), countryId varchar(50), countryCode char(2),
		gender char(1), year smallint(5) unsigned)');

	$db->query('insert into AllResults select competitionId,eventId,personId,best,average,countryId,countryCode,gender,year
		from ResultsViewCountries join Competitions on ResultsViewCountries.competitionId=Competitions.id');

	$db->query('drop view ResultsViewCountries');
	$db->query('drop view ResultsView');

	echo "Setup AllResults table.<br>";
}

$db = getDb();

stripTables($db);
dropTables($db);
setupPrimaryKeys($db);
generateResults($db);
echo "Import script finished.<br>";

$db->close();
