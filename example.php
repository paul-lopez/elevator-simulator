<?php
use App\Elevator;
use App\Log;
require_once 'vendor/autoload.php';
Log::reset();
Log::save('Begin Elevator Simulator');
$elevator = new Elevator(1,7,'up');
Log::save('CurrentFloor: '.$elevator->getCurrentFloor() );
Log::save('TotalFloors:  '.$elevator->getTotalFloors() );
Log::save('Direction:    '.$elevator->getDirection() );
//Maintenance Floors are 2 and 4
$maintenance_floors = array(2,4);
foreach($maintenance_floors as $floor){
	Log::save('Set F'.$floor.' in maintenance');
	$elevator->setFloorInMaintenance($floor);
}
$requests = array(
		array('from'=>6,'to'=>1),
		array('from'=>5,'to'=>7),
		array('from'=>3,'to'=>1),
		array('from'=>1,'to'=>7)
);
foreach($requests as $floor){
	Log::save('addRequest From F'.$floor['from'].' to F'.$floor['to']);
	$elevator->addQueue($floor['from'],$floor['to']);
}
$nRequest = $elevator->getTotalPendingRequest('both');
if($nRequest>0){
	$queue = $elevator->getQueue();
	Log::save('Queue[up]:   '.implode(',',$queue['up']));
	Log::save('Queue[down]: '.implode(',',$queue['down']));
	for($i=1;$i<=$nRequest;$i++){
		$before = $elevator->getCurrentFloor();
		$elevator->nextFloor();
		Log::save($before.' to '.$elevator->getCurrentFloor(). ' '.$elevator->getDirection() );
	}
}else{
	Log::save('No request pending');
}
?><!DOCTYPE html>
<html lang="eng">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Elevator Test Case</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<body>
	<div class="container" >
	<br>
		<div class="jumbotron">
		<h2>Logs</h2>
		<?php
			echo Log::getContent();
		?>
		</div>
	</div>
</body>
</html>