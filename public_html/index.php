<?php
	session_start();
	use App\Elevator;
	use App\Sessions;
require_once 'vendor/autoload.php';
	define('NL','<br>'.PHP_EOL);
	if(!isset($_SESSION['current_floor'])) {
		$keys = array(
			'current_floor',
			'total_floors',
			'direction',
			'queue_down',
			'queue_up',
			'maintenance'
		);
		foreach ($keys as $k) {
			$_SESSION[ $k ] = "";
		}
		$elevator = new Elevator(7);
		$elevator->setCurrentFloor(1);//current_floor
		$elevator->setSignal('door_close');
		$elevator->setDirection('up');
		$elevator->setMaintenance([]);
		$elevator->setQueue('up', []);
		$elevator->setQueue('down', []);
		$sessions = new Sessions();
		$sessions->setVals($elevator);
	}
//	echo var_export($_SESSION,true);
//	exit();
//	}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Elevator Test Case">
	<meta name="author" content="Paul Lopez">
	<link rel="icon" href="favicon.ico">
	<!--[if IE]><link rel="shortcut icon" href="favicon.ico"><![endif]-->
	<title>Elevator Test Case</title>
	<!-- Bootstrap core CSS -->
	<link rel="stylesheet"
	      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
	      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
	      crossorigin="anonymous">
	<style>

		#controls td:first-child{
			text-align: center;
		}
		span.status_floor{
			font-size: 30px;
			width: 30px;
		}
		@media(max-width: 750px) {
			span.status_floor{
				font-size: 1.5em;
				width: 30px;
			}
		}
	</style>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php">Elevator Test Case</a>
		</div>
	</div>
</nav>
<br>
<br>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">Dashboard</h1>

			<div class="table-responsive">
				<table id="controls" class="table table-bordered table-striped">
					<thead>
					<tr>
						<th>#Floors</th>
						<th>Buttons</th>
					</tr>
					</thead>
					<tbody>
					<tr>

						<td class="door" data-id="7"><span class="status_floor" data-id="7"></span></td>
						<td>
							<button data-id="7" data-direction="down" type="button" class="btn btn-primary btnRequest"
							        aria-label="Left Align">
										<span class="glyphicon glyphicon-chevron-down"
										      aria-hidden="true"></span>
							</button>
							<button data-id="7" data-direction="maintenance" type="button" class="btn btn-danger btnRequest"
							        aria-label="Left Align">
										<span class="glyphicon glyphicon-wrench"
										      aria-hidden="true"></span>
							</button>
						</td>
					</tr>
					<?php for ($i = 6; $i >= 2; $i--) { ?>
						<tr>
							<td class="door" data-id="<?php echo $i; ?>">
								<span class="status_floor" data-id="<?php echo $i; ?>"></span>
							</td>
							<td>
								<button data-id="<?php echo $i; ?>" data-direction="down" type="button"
								        class="btn btn-primary btnRequest"
								        aria-label="Left Align">
										<span class="glyphicon glyphicon-chevron-down"
										      aria-hidden="true"></span>
								</button>
								<button data-id="<?php echo $i; ?>" data-direction="up" type="button"
								        class="btn btn-primary btnRequest"
								        aria-label="Left Align">
										<span class="glyphicon glyphicon-chevron-up"
										      aria-hidden="true"></span>
								</button>
								<button  data-id="<?php echo $i; ?>" data-direction="maintenance" type="button"
								        class="btn btn-danger btnRequest"
								        aria-label="Left Align">
										<span class="glyphicon glyphicon-wrench"
										      aria-hidden="true"></span>
								</button>
							</td>
						</tr>
					<?php } ?>
					<tr>
						<td class="door" data-id="1">
							<span class="status_floor" data-id="1" aria-hidden="true"></span>
						</td>
						<td>
							<button data-id="1" data-direction="up" type="button" class="btn btn-primary btnRequest"
							        aria-label="Left Align">
										<span class="glyphicon glyphicon-chevron-up"
										      aria-hidden="true"></span>
							</button>
							<button data-id="1" data-direction="maintenance" type="button" class="btn btn-danger btnRequest"
							        aria-label="Left Align">
										<span class="glyphicon glyphicon-wrench"
										      aria-hidden="true"></span>
							</button>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

			<button id="btnResetLog" type="button" class="btn btn-danger"
			        aria-label="Left Align">
				Reset Log<span class="glyphicon glyphicon-trash"
				               aria-hidden="true"></span>
			</button>
			<button id="btnResetServerData" type="button" class="btn btn-success"
			        aria-label="Left Align">
				Restart<span class="glyphicon glyphicon-refresh"
				             aria-hidden="true"></span>
			</button>

			<div class="table-responsive">
				<table id="debug-table" class="table table-stripped">
					<thead>
					<tr>
						<th>Status</th>
						<th>Current Floor</th>
						<th>Next Floor</th>
						<th>Direction</th>
						<th>Signal</th>
						<th>Queue UP</th>
						<th>Queue Down</th>
						<th>Maintenance Floors</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Server</td>
						<td><span id="current_floor"></span></td>
						<td><span id="next_floor"></span></td>
						<td><span id="direction"></span></td>
						<td><span id="signal"></span></td>
						<td><span id="queueUp"></span></td>
						<td><span id="queueDown"></span></td>
						<td><span id="maintenance"></span></td>
					</tr>
					<tr>
						<td>Local</td>
						<td><span id="local_current_floor"></span></td>
						<td><span id="local_tmp_floor"></span></td>
						<td><span id="local_direction"></span></td>
						<td><span id="local_signal"></span></td>
						<td><span id="local_queueUp"></span></td>
						<td><span id="local_queueDown"></span></td>
						<td><span id="local_maintenance"></span></td>
					</tr>
					</tbody>
				</table>
				<!-- debug-table -->
			</div>
			<!-- table-responsive -->

		</div>
		<!-- row -->
	</div>
	<!-- col-sm-9  -->
</div>
<!-- container-fluid -->
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="js/jquery.elevator-controller.js"></script>
</body>
</html>