# Elevator Test Case in PHP

Introduction
------------
This project used to demo Elevator Class Test

Requirements
-----------
* Composer: you can get from `https://getcomposer.org/download/`
* PHP >= 5
* PHPUnit >= 4

Before start
-----------
To make tests you will need PHPUnit

Open the command line and browse to your project path and run the command:
* `composer install`
* `composer dump-autoload --optimize`

Tests
-----------
Open the command line and browse to your project path and run the command: 
* `phpunit tests`

Example Use
-----------
* Usage example in `example.php`. 

* Api demo at `public_html/api.php`. 
	Online: http://ventasmasivasapp.com/elevator/index.php

* Below is sample api:
	1. http://yourdomain.com/elevator/api.php?action=setCurrentFloor&val=1
	2. http://yourdomain.com/elevator/api.php?action=setTotalFloors&val=7
	3. http://yourdomain.com/elevator/api.php?action=setDirection&val=up
	4. http://yourdomain.com/elevator/api.php?action=setMaintenance&val=2
	5. http://yourdomain.com/elevator/api.php?action=setPressButton&floor=6&direction=down	
	6. http://yourdomain.com/elevator/api.php?action=getQueue
	7. http://yourdomain.com/elevator/api.php?action=getStatusFromSession
	8. http://yourdomain.com/elevator/api.php?action=getStatusFromSession
	9. http://yourdomain.com/elevator/api.php?action=getNextFloor
	10. http://yourdomain.com/elevator/api.php?action=setResetSession
	11. http://yourdomain.com/elevator/api.php?action=setResetLog

* Wrapper Response from api:

	{"success":true|false,"data":[],"message":""}

* View log file: http://yourdomain.com/elevator/log.txt

License
---------------------
Copyright (c) 2017 Paul Lopez <paul.lopezm@gmail.com>
All rights reserved.
