<?php include 'header.php'; ?>

<div id="world-map" style="width: 100%; height: 600px"></div>
<script src="data/scripts/populate-map.js"></script>

<div id="input-form">
	<form>
		<select name="event">
			<option value="333">3x3 Rubik's cube</option>
		</select>
		<select name="gender">
			<option value="*">Any</option>
			<option value="m">Male</option>
			<option value="f">Female</option>
		</select>
		<select name="stat">
			<option value="topBest">Top Best</option>
			<option value="topAverage">Top Average</option>
			<option value="overallBest">Overall Best</option>
			<option value="overallAverage">Overall Average</option>
		</select>
		<!-- <input type="submit" value="Submit"> -->
	</form>
</div>

<?php
/*
This file renders the initial front-end interface.
All requests to the actual data for the map is handled by query.php and is loaded asynchronously.
*/

/*
Setup input controls:
	Event
	Gender
	Statistic
	Year slider
*/

?>

<?php include 'footer.php'; ?>
