<?php
include 'dbconfig.php';
include 'common.php';
include 'header.php';

/*
This file renders the initial front-end interface.
All requests to the actual data for the map is handled by query.php and is loaded asynchronously.
*/
?>

<div id="world-map" style="width: 100%; height: 600px"></div>
<script src="data/scripts/populate-map.js"></script>

<br>

<div id="input-form">
	<select class="ajaxSelect" name="event">
		<?php
			// Gather events list
			$db = getDb();
			$events = getEvents($db);
			$db->close();

			// Generate select element from events list
			foreach ($events as $name => $description) {
				echo "<option value=\"$name\">$description</option>\n\t\t";
			}
		?>
	</select>
	<select class="ajaxSelect" name="gender">
		<option value="*">Any</option>
		<option value="m">Male</option>
		<option value="f">Female</option>
	</select>
	<select class="ajaxSelect" name="stat">
		<option value="topBest">Top Best</option>
		<option value="topAverage">Top Average</option>
		<option value="overallBest">Overall Best</option>
		<option value="overallAverage">Overall Average</option>
		<option value="compsVisitedBest">Competitions Visited (Best)</option>
		<option value="compsVisitedAverage">Competitions Visited (Average)</option>
		<option value="numCubers">Number Of Cubers</option>
	</select>

	<input id="yearSlider" type="range" min="2003" max="2015" style="width:300px"/>
	<p class="note">Year:
		<span id="currentValue">2015</span>
	</p>
</div>

<br>
<p><b>Note:</b> Data is from <a href="https://www.worldcubeassociation.org">https://www.worldcubeassociation.org</a>, dated 2015-11-28, and may not represent actual information. The actual information can be found here: <a href="https://www.worldcubeassociation.org/results">https://www.worldcubeassociation.org/results</a>.</p>

<?php include 'footer.php'; ?>
