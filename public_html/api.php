<?php
session_start();
use App\Api;
use App\Log;
require_once 'vendor/autoload.php';
$api = new Api();
$action = (isset($_GET['action'])?filter_var($_GET['action'],FILTER_SANITIZE_STRING):'default');
$val = (isset($_GET['val'])?$_GET['val']:'');
$json = array();

switch($action){
	case 'setCurrentFloor':
		$json =$api->setCurrentFloor($val);
		break;
	case 'setTotalFloors':
		$json = $api->setTotalFloors($val);
		break;	
	case 'setDirection':
		$json = $api->setDirection($val);
		break;
	case 'setMaintenance':
		$json = $api->setMaintenance($val);
		break;
	case 'setQueueUp':
		$json = $api->setQueue('up',$val);
		break;
	case 'setQueueDown':
		$json = $api->setQueue('down',$val);
		break;
	case 'setSignal':
		$json = $api->setSignal($val);
		break;
	case 'setPressButton':
		$floor = filter_var($_GET['floor'],FILTER_SANITIZE_NUMBER_INT);
		$direction = filter_var($_GET['direction'],FILTER_SANITIZE_STRING);
		$json = $api->setPressButton($floor, $direction);
		break;
	case 'getQueue':
		$json =$api->getQueue();
		break;
	case 'getStatus':
		$json =$api->getStatus();
		break;
	case 'getStatusFromSession':
		$json =$api->getStatusFromSession();
		break;
	case 'getSessionVals':
		$json =$api->getSessionVals();
		break;
	case 'getNextFloor':
		$json =$api->getNextFloor();
		break;
	case 'setResetSession':
		$json =$api->resetSession();
		break;
	case 'setResetLog':
		$json =$api->resetLog();
		break;
	default:
		$json =$api->response('Default',true);
}
die(json_encode($json));
