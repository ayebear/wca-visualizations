var results = {};

var yearRange = [2003, 2016];
var currentYear = 2015;

function parseResults(response) {
	var resultData = {};
	var responseObj = JSON.parse(response);

	// Convert country:year:result format to year:country:result
	// Also calculate missing years' data
	for (var country in responseObj) {
		if (responseObj.hasOwnProperty(country)) {
			var minResult = -1;
			// Iterate through all years
			for (var year = yearRange[0]; year < yearRange[1]; ++year) {
				// Calculate minimum result from all previous years
				if (year in responseObj[country]) {
					newResult = responseObj[country][year];
					if (minResult == -1 || newResult < minResult) {
						minResult = newResult;
					}
				}
				// Set result in dictionary
				if (minResult > 0) {
					if (!(year in resultData))
						resultData[year] = {}
					resultData[year][country] = minResult;
				}
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
	$('#world-map').vectorMap({
		map: 'world_mill',
		series: {
			regions: [{
				values: resultData,
				scale: ['#FFFF00', '#00FF00', '#0000FF', '#FF0000', '#000000'],
				normalizeFunction: 'polynomial'
			}]
		},
		onRegionTipShow: function(e, el, code){
			var values = getMapRegion().values;

			var resultText = 'N/A';
			if (code in values) {
				resultText = values[code] + ' seconds';
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
			results = parseResults(response);
			callback(results);
		},
		error: function(xhr) {
			alert("Error: " + xhr);
		}
	});
}

function updateMapYear(year) {
	currentYear = year;
	getMapRegion().setValues(results[year]);
}

$(function(){
	refreshResults({
		'event': '333',
		'gender': 'm',
		'stat': 'topBest'
	}, function() {
		setupVectorMap(results[currentYear]);
	});
});

$(function() {
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

		// Refresh map with new data
		refreshResults(params, function() {
			getMapObject().reset();
			getMapObject().clearSelectedRegions();
			getMapRegion().clear();

			// May have to re-create map to call constructor and re-calculate color scale
			// alert('New min: ' + data_min(results[currentYear]));
			// getMapRegion().scale.setMin(data_min(results[currentYear]));
			// getMapRegion().scale.setMax(data_max(results[currentYear]));

			getMapRegion().setValues(results[currentYear]);
		});
	});
});
