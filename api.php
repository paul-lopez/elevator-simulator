<?php 
use App\Api;
require_once 'vendor/autoload.php';
$api = new Api();
$action = (isset($_GET['action'])?$_GET['action']:'default');
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
	case 'request':
		$floor = filter_var($_GET['floor'],FILTER_SANITIZE_NUMBER_INT);
		$direction = filter_var($_GET['direction'],FILTER_SANITIZE_STRING);
		$json = $api->request($floor, $direction);
		break;
	case 'getQueue':
		$json =$api->getQueue();
		break;
	case 'getStatus':
		$json =$api->getStatusFromCookies();
		break;
	case 'getCookieVals':
		$json =$api->getCookieVals();
		break;
	case 'move':
		$json =$api->move();
		break;
	case 'resetCookies':
		$json =$api->resetCookies();
		break;
	case 'resetLog':
		$json =$api->resetLog();
		break;
	default:
		$json =$api->response('Default',true);
}
die(json_encode($json));
