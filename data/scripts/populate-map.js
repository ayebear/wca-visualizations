var results = {};
// results[year][country] = result

var yearRange = [2003, 2016];

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
			// var test = resultData;
			var mapObject = $('#world-map').vectorMap('get', 'mapObject');
			var test = mapObject.series.regions[0].values;

			var resultText = 'N/A';
			if (code in test) {
				resultText = test[code] + ' seconds';
			}
			el.html(el.html() + ': ' + resultText);
		}
	});
}

function refreshResults(params) {
	$.ajax({
		url: 'query.php',
		type: 'get',
		data: params,
		success: function(response) {
			results = parseResults(response);
			setupVectorMap(results[2015]);
		},
		error: function(xhr) {
			alert("Error: " + xhr);
		}
	});
}

function updateMapYear(year) {
	// setupVectorMap(results[year]);
	var mapObject = $('#world-map').vectorMap('get', 'mapObject');
	mapObject.series.regions[0].setValues(results[year]);
}

$(function(){
	refreshResults({
		'event': '333',
		'gender': 'm',
		'stat': 'topBest'
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
