<?php
namespace App;

use App\Log;
use App\Elevator;
use App\Sessions;
/**
 * WebService
 *
 * @author Paul Lopez
 */
class Api extends Elevator{
	private $debug = false;
	protected $elevator;
	protected $keys = array(
			'current_floor',
			'total_floors',
			'direction',
			'queue_down',
			'queue_up',
			'maintenance'
	);
	private $data = array ();//for magic methods
	/**
	 * Initial Setup
	 */
	public function __construct() {
		Log::reset();
		Log::save('==========================================');
		Log::save('================'.time().'=================');
		Log::save('==========================================');
		$this->elevator = new Elevator(7);
		$this->getElevatorFromSession();
	}

	public function getElevatorFromSession(){
		$this->elevator = Sessions::getElevator(true,'save');
		if(!($this->elevator instanceof Elevator)){
			$this->response('Not instance of Elevator',false);
		}
		Log::saveElevatorStatus($this->elevator,'API getElevatorFromSession');
	}
	public function enableDebug(){
		$this->debug = true;
	}

	protected function debug($msg){
		if($this->debug){
			Log::save($msg);
		}
	}

	public function resetLog(){
		return $this->response('resetLog', Log::reset());
	}

	public function getQueue(){
		$queue = $this->elevator->getQueue();
		return $this->response('Queue',false,$queue);
	}
	
	public function setQueue($direction,$str_queue){
		return $this->response('setQueue['.$direction.']: '.$str_queue,$this->elevator->setQueue($direction,explode(',',$str_queue)));
	}

	public function setSignal($signal){
		return $this->response('setSignal',$this->elevator->setSignal($signal));
	}
	/**
	 * Make request
	 * @param integer $floor
	 * @param string $direction
	 */
	public function setPressButton($floor, $direction) {
		$floor = filter_var ( $floor, FILTER_SANITIZE_NUMBER_INT );
		$direction = filter_var ( $direction, FILTER_SANITIZE_STRING );
		$msg = 'press button '.$direction.' in floor ' . $floor ;

		return $this->response ( $msg, $this->elevator->pressButton ( $floor, $direction ) );
	}
	/**
	 * Open the Elevator Door
	 * @return array $json
	 */
	public function openDoor() {
		return $this->response ( 'Door Open', $this->elevator->setSignal ( 'door_open' ) );
	}
	/**
	 * Close the Elevator Door
	 * @return array $json
	 */
	public function closeDoor() {
		return $this->response ( 'Door close', $this->elevator->setSignal ( 'door_close' ) );
	}
	/**
	 * Elevator Alarma Signal
	 * @return array $json
	 */
	public function alarm() {
		return $this->response ( 'Alarm', $this->elevator->alarm () );
	}
	
	/**
	 * Cookie setup for current floor
	 * @param integer $floor
	 */
	public function setCurrentFloor($floor) {
		Log::save ( 'setCurrentFloor '.$floor );
		return $this->response ( 'current floor is ' . $floor);
	}
	/**
	 * Cookie Setup for number of floors in the building
	 * @param integer $nFloors
	 */
	public function setTotalFloors($nFloors) {
		Log::save ( 'setTotalFloors '.$nFloors );
		return $this->response ( 'The building have ' . $nFloors . ' floors' );
	}
	/**
	 * Set Direction up,down,stand,maintenance
	 * @param string $direction
	 * @return array $json
	 */
	public function setDirection($direction) {
		Log::save ( 'setDirection: '.$direction );
		return $this->response ( 'setDirection ' . $direction,$this->elevator->setDirection($direction));
	}
	
	public function getStatusFromSession(){
		$el = Sessions::getElevator();
		$data = array(
			'current_floor' =>$el->getCurrentFloor(),
			'total_floors' =>$el->getTotalFloors(),
			'direction' =>$el->getDirection() ,
			'signal' =>$el->getSignal(),
			'queue_up' =>$el->getQueue('up'),
			'queue_down' =>$el->getQueue('down'),
			'maintenance' =>$el->getMaintenanceFloors()
		);
		$this->elevator = $el;
		return $this->response('getStatusFromSession',true,$data);
	}

	/**
	 * Get Status from object in Ram Memory
	 * Doesnt work with AJAX 
	 */
	public function getStatus(){
		$queue = $this->elevator->getQueue();
		$data = array(
			'current_floor' =>$this->elevator->getCurrentFloor(),
			'total_floors' =>$this->elevator->getTotalFloors(),
			'direction' => $this->elevator->getDirection(),
			'signal' =>$this->elevator->getSignal(),
			'queue_up' =>$queue['up'],
			'queue_down' =>$queue['down'],
			'maintenance' =>$this->elevator->getMaintenanceFloors()
		);
		return $this->response('getStatus',true,$data);
	}
	/**
	 * nextFloor the elevator
	 * @return json
	 */
	public function getNextFloor(){
		$floor_before = $this->elevator->getCurrentFloor();
		$next_floor = $this->elevator->nextFloor();//return $this->elevator->current_floor
		$data = array(
				'floor_before'=>$floor_before,
				'current_floor'=>$next_floor
		);
		return $this->response('nextFloor '.$this->elevator->getDirection().' is '.$next_floor,true,$data );
	}
	/**
	 * Reset all the values in the Session
	 */
	public function resetSession(){
		$sessions = new Sessions();
		$elevator  = $sessions->resetAll();
		if($elevator instanceof Elevator){
			$this->elevator = $elevator;
			if(empty($_SESSION['maintenance']) ){
				return $this->response('Reset Session OK '.var_export($_SESSION,true),true,$_SESSION);
			}else{
				return $this->response('Reset Session SESSION '.var_export($_SESSION,true),false,$_SESSION);
			}
		}
		return $this->response('Reset Session FAIL '.var_export($_SESSION,true),false,$_SESSION);
	}
	/**
	 * Get values from Session in another way
	 */
	public function getSessionVals(){
		$tmp = array();
		foreach($this->keys as $k){
			if(isset($_SESSION[$k])){
				$tmp[$k] = $_SESSION[$k];
			}else{
				$tmp[$k] = "";
			}
		}
		return $this->response('Session Values',true,$tmp );
	}
	/**
	 * Set floors in maintenance, it gets in string separated by comma and urlencode
	 * @param string $str_floors
	 */
	public function setMaintenance($str_floors=""){
		if(empty($str_floors)){
			$this->elevator->setMaintenance(array());
			return $this->response('setMaintenance to empty',true);
		}else {
			Log::save('setMaintenance');
			$floors = explode(',', urldecode($str_floors));
			if (sizeof($floors) > 0) {
				foreach ($floors as $floor) {
					if (intval($floor) > 0) {
						Log::save('F' . $floor . ' now is in maintenance');
						$this->elevator->setFloorInMaintenance(intval($floor));
					}
				}
			}
		}
		return $this->response('setMaintenance('.$str_floors.'):'.implode(',',$this->elevator->getMaintenanceFloors()),true);
	}
	/**
	 * Generate the array for the json answer
	 * @param string $msg
	 * @param string $status
	 * @return array
	 */
	public function response($msg, $success = true,$data=array()) {
//		Log::save ( $msg, false );
		$json = array(
				'success' => $success,
				'data' => $data,
				'message' => $msg
		);
		Sessions::setVals($this->elevator);
		return $json;
	}
	/**
	 * Magic Method __set
	 * @param string $property
	 * @param mixed $value
	 */
	public final function __set($property, $value) {
		$this->data [$property] = $value;
	}
	/**
	 * Magic method __get
	 * @param string $property
	 * @return mixed|NULL
	 */
	public final function __get($property) {
		if (array_key_exists ( $property, $this->data )) {
			return $this->data [$property];
		}
		return null;
	}
	/**
	 * Magic method __isset
	 * @param string $prop
	 * @return boolean
	 */
	public final function __isset($prop) {
		return isset ( $this->$prop );
	}
}
