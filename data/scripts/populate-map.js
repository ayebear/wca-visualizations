var results = {};

var yearRange = [2003, 2016];
var currentYear = 2015;
var currentStatText = 'seconds';
var currentStat = 'topBest';

function parseResults(response, stat) {
	var resultData = {};
	var responseObj = JSON.parse(response);
	var startVal = -1;

	var compFunc = function(a, b) {
		return (a < b);
	}

	if (stat == 'numCubers' || stat == 'numResults') {
		startVal = 99999999;
		compFunc = function(a, b) {
			return (a > b);
		}
	}

	// Convert country:year:result format to year:country:result
	// Also calculate missing years' data
	for (var country in responseObj) {
		var minResult = startVal;
		// Iterate through all years
		for (var year = yearRange[0]; year < yearRange[1]; ++year) {
			// Calculate minimum result from all previous years
			if (year in responseObj[country]) {
				newResult = Number(responseObj[country][year]);
				if (minResult == startVal || compFunc(newResult, minResult)) {
					minResult = newResult;
				}
			}
			// Set result in dictionary
			if (minResult != startVal) {
				if (!(year in resultData))
					resultData[year] = {}
				resultData[year][country] = minResult;
			}
		}
	}

	return resultData;
}

function getMapObject() {
	return $('#world-map').vectorMap('get', 'mapObject');
}

function getMapRegion() {
	return getMapObject().series.regions[0];
}

function setupVectorMap(resultData) {
	var colorScale = ['#FFFF00', '#00FF00', '#0000FF', '#FF0000', '#000000'];
	if (currentStat == 'numCubers' || currentStat == 'numResults') {
		colorScale.reverse();
	}

	$('#world-map').vectorMap({
		map: 'world_mill',
		series: {
			regions: [{
				values: resultData,
				scale: colorScale,
				normalizeFunction: 'polynomial'
			}]
		},
		onRegionTipShow: function(e, el, code){
			var values = getMapRegion().values;

			var resultText = 'N/A';
			if (code in values) {
				resultText = values[code] + ' ' + currentStatText;
			}
			el.html(el.html() + ': ' + resultText);
		}
	});
}

function refreshResults(params, callback) {
	$.ajax({
		url: 'query.php',
		type: 'get',
		data: params,
		success: function(response) {
			results = parseResults(response, params.stat);
			callback(results);
		},
		error: function(xhr) {
			alert("Error: " + xhr);
		}
	});
}

function updateMapYear(year) {
	currentYear = year;
	// getMapRegion().setValues(results[year]);
	$("#world-map").empty();
	setupVectorMap(results[currentYear]);
}

$(function(){
	// Refresh and setup map
	refreshResults({
		'event': '333',
		'gender': 'm',
		'stat': 'topBest'
	}, function() {
		setupVectorMap(results[currentYear]);
	});

	// Set default year slider value
	var yearSlider = document.getElementById("yearSlider");
	yearSlider.value = 2015;

	// Setup year slider handler
	var currentValue = $('#currentValue');

	$('#yearSlider').on("input", function(){
		currentValue.html(this.value);
		updateMapYear(this.value);
	});

	$('#yearSlider').change();
});

/*function data_max(arr) {
	return Math.max.apply(null, Object.keys(arr).map(function(e) {return arr[e];}));
}

function data_min(arr) {
	return Math.min.apply(null, Object.keys(arr).map(function(e) {return arr[e];}));
}*/

$(function(){
	$('.ajaxSelect').change(function(e) {
		// Get parameters from HTML select inputs
		var params = {};
		$(".ajaxSelect").each(function() {
			var name = $(this).attr("name");
			params[name] = this.value;
		});

		// Set tooltip text based on stat selected
		if (params.stat == 'numCubers')
			currentStatText = 'cubers';
		else if (params.stat == 'compsVisitedBest' || params.stat == 'compsVisitedAverage')
			currentStatText = 'competitions visited';
		else if (params.stat == 'numResults')
			currentStatText = 'results';
		else
			currentStatText = 'seconds';

		// alert(params.stat);

		// Refresh map with new data
		refreshResults(params, function() {
			$("#world-map").empty();
			currentStat = params.stat;
			setupVectorMap(results[currentYear]);

			/*getMapObject().reset();
			getMapObject().clearSelectedRegions();
			getMapRegion().clear();
			getMapRegion().setValues(results[currentYear]);*/

			// May have to re-create map to call constructor and re-calculate color scale
			// alert('New min: ' + data_min(results[currentYear]));
			// getMapRegion().scale.setMin(data_min(results[currentYear]));
			// getMapRegion().scale.setMax(data_max(results[currentYear]));
		});
	});
});
