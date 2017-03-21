# Elevator Test Case

Introduction
------------
This project used to demo Elevator Class Test

Tests
-----------
In the console run `phpunit tests`

Example Use
-----------
* Usage example in `example.php`. 

* Api demo at `api.php`. 

* Below is sample api:
	1. http://yourdomain.com/elevator/api.php?action=setCurrentFloor&val=1
	2. http://yourdomain.com/elevator/api.php?action=setTotalFloors&val=7
	3. http://yourdomain.com/elevator/api.php?action=setDirection&val=up
	4. http://yourdomain.com/elevator/api.php?action=setMaintenance&val=2
	5. http://yourdomain.com/elevator/api.php?action=getQueue
	6. http://yourdomain.com/elevator/api.php?action=request&floor=6&direction=down
	7. http://yourdomain.com/elevator/api.php?action=getStatus
	8. http://yourdomain.com/elevator/api.php?action=getCookieVals
	9. http://yourdomain.com/elevator/api.php?action=move
	10. http://yourdomain.com/elevator/api.php?action=resetCookies
	11. http://yourdomain.com/elevator/api.php?action=resetLog

* Wrapper Response from api:

	{"success":true|false,"data":[],"message":""}

* View log file: http://yourdomain.com/elevator/log.txt

License
---------------------
Copyright (c) 2017 Paul Lopez <paul.lopezm@gmail.com>
All rights reserved.
