function parseResults(response) {
	var responseObj = JSON.parse(response);
	var resultData = {};

	// Just use current year for now
	for (var country in responseObj) {
		if (responseObj.hasOwnProperty(country)) {
			var minResult = -1;
			for (year in responseObj[country]) {
				newResult = responseObj[country][year];
				if (minResult == -1 || newResult < minResult) {
					minResult = newResult;
				}
			}
			resultData[country] = minResult;
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
			el.html(el.html() + ': ' + resultData[code]);
		}
	});
}

function refreshResults(params) {
	$.ajax({
		url: 'query.php',
		type: 'get',
		data: params,
		success: function(response) {
			resultData = parseResults(response);
			setupVectorMap(resultData);
		},
		error: function(xhr) {
			alert("Error: " + xhr);
		}
	});
}

$(function(){
	refreshResults({
		'event': '333',
		'gender': 'm',
		'stat': 'topBest'
	});
});
