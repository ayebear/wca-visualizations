<?php
include 'dbconfig.php';

$db = getDb();

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

/*
select countryCode, min(best) as result from AllResults where best>0 and eventId='333' and year<2013 group by countryCode;
select countryCode, year, min(best) as result from AllResults where best>0 and eventId='333' group by countryCode, year;
*/

$db->close();
