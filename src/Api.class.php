<?php
namespace App;

use App\Log;
use App\Elevator;

/**
 * WebService
 *
 * @author Paul Lopez
 */
class Api {
	protected $elevator;
	protected $keys =array(
			'current_floor',
			'total_floors',
			'direction',
			'queue_down',
			'queue_up',
			'maintenance'
	);
	private $data = array ();

	protected $expires; 

	public function __construct() {
		$this->expires = time()+60*60*24;
		$this->updateObjectFromCookies();
		$this->elevator = new Elevator ( $this->current_floor, $this->total_floors,$this->direction,$this->queue_up,$this->queue_down,$this->maintenance);
		$this->inheritVals();
	}

	public function resetLog(){
		Log::reset();
		return $this->response('resetLog',true);
	}
	
	protected function inheritVals(){
		Log::save('Inerit values');
		Log::save('this->elevator->setCurrentFloor('.$this->current_floor.')');
		$this->elevator->setCurrentFloor($this->current_floor);
		if(sizeof($this->queue_up)>0){
			Log::save('Add queue up floors'.var_export($this->queue_up,true));
			$this->elevator->setQueue('up',$this->queue_up);
		}else{
			Log::save('No Queue UP');
		}
		if(sizeof($this->queue_down)>0){
			Log::save('Add queue down floors'.var_export($this->queue_down,true));
			$this->elevator->setQueue('down',$this->queue_down);
		}else{
			Log::save('No Queue Down');
		}
		if(sizeof($this->maintenance)>0){
			Log::save('Add maintenance floors: '.var_export($this->maintenance,true));
			foreach($this->maintenance as $floor){
				if(intval($floor)!==0){
					Log::save('Add Maintenance F'.$floor);
					$this->elevator->setFloorInMaintenance($floor);
				}
			}
		}else{
			Log::save('No maintenance Floors');
		}
	}
	/**
	 * Get all the values from Cookies
	 */
	protected function updateObjectFromCookies(){
		Log::save('updateObjectFromCookies');
		Log::save('Values from cookies Begin');
// 		Log::save(var_export($this->keys,true));
		foreach($this->keys as $j=>$k){	
// 			Log::save($j.'=>'.$k);
			if((isset($_COOKIE[$k])) && !empty($_COOKIE[$k])){
				$this->$k = (strpos($_COOKIE[$k],',')!==false?explode(',',$_COOKIE[$k]):$_COOKIE[$k]);
				Log::save($k.':'.$_COOKIE[$k]);
			}else{
// 				Log::save('strpos: '.strpos($k,'queue'));
				if(strpos($k,'queue')!==false){
					$this->$k = array();
					Log::save($k.':array()');
				}elseif(strpos($k,'maintenance')!==false){
					$this->$k = array();
					Log::save($k.':array()');
				}else{
					$this->$k = "";
					Log::save($k.':empty');
				}
				
			}
		}
		Log::save('Values from cookies End');
		return true;
	}
	/**
	 * Setup all the cookie values
	 */
	protected function updateCookiesFromObject(){
		Log::save('=============================');
		Log::save('updateCookiesFromObject BEGIN');
// 		$array = get_object_vars($this->elevator);
// 		Log::save('Elevator JSON: '.json_encode($array));
		if(!isset($this->elevator)){
			return false;
		}
		Log::save('current_floor:'. $this->elevator->getCurrentFloor());
		setcookie ( 'current_floor', $this->elevator->getCurrentFloor(),$this->expires);
	
		Log::save('total_floors: '. $this->elevator->getTotalFloors());
		setcookie ( 'total_floors', $this->elevator->getTotalFloors(),$this->expires );
	
		Log::save('direction: '. $this->elevator->getDirection());
		setcookie ( 'direction', $this->elevator->getDirection(),$this->expires );
	
		Log::save('signal: ', $this->elevator->getSignal());
		setcookie ( 'signal', $this->elevator->getSignal(),$this->expires);
	
		$queue = $this->elevator->getQueue();
		Log::save('queue_up: '. implode(',',array_unique($queue['up'])));
		setcookie ( 'queue_up', implode(',',array_unique($queue['up'])) ,$this->expires);
	
		Log::save('queue_down: '. implode(',',array_unique($queue['down']) ));
		setcookie ( 'queue_down', implode(',',array_unique($queue['down']) ),$this->expires );
	
		Log::save('maintenance: '. implode(',',$this->elevator->getMaintenanceFloors() ));
		setcookie ( 'maintenance', implode(',',$this->elevator->getMaintenanceFloors() ),$this->expires );
		Log::save('updateCookiesFromObject END');
		Log::save('=============================');
		
		$this->getCookieVals();
		return true;

	}
	/**
	 * If the cookie its not set, give initial value from properties
	 */
	protected function checkCookies(){
		foreach($this->keys as $k){
			if(!isset($_COOKIE[$k])){
				setcookie($k,(is_array($this->$k)?implode(',',$this->$k):$this->$k),$this->expires);
			}
		}
	}
	
	public function getQueue(){
		$queue = $this->elevator->getQueue();
		return $this->response('Queue',false,$queue);
	}
	
	public function setQueue($direction,$str_queue){
		return $this->response('setQueue['.$direction.']: '.$str_queue,$this->elevator->setQueue($direction,explode(',',$str_queue)));
	}
	/**
	 * Make request
	 * @param integer $floor
	 * @param string $direction
	 */
	public function request($floor, $direction) {
		$floor = filter_var ( $floor, FILTER_SANITIZE_NUMBER_INT );
		$direction = filter_var ( $direction, FILTER_SANITIZE_STRING );
		$msg = 'Request F' . $floor . ' move ' . $direction;
		if($this->elevator->pressButton ( $floor, $direction )){
			Log::save('Update Cookies from Object... add F'.$floor);
			$this->updateCookiesFromObject();
		}else{
			Log::save('Request doesnt added');
		}
		return $this->response ( $msg, true );
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
		if (setcookie ( 'current_floor', intval ( $floor ) )) {
			return $this->response ( 'current floor is ' . $floor);
		}
	}
	/**
	 * Cookie Setup for number of floors in the building
	 * @param integer $nFloors
	 */
	public function setTotalFloors($nFloors) {
		Log::save ( 'setTotalFloors '.$nFloors );
		if (setcookie ( 'total_floors', intval ( $nFloors ) )) {
			return $this->response ( 'The building have ' . $nFloors . ' floors' );
		}
	}
	/**
	 * Set Direction up,down,stand,maintenance
	 * @param string $direction
	 * @return array $json
	 */
	public function setDirection($direction) {
		Log::save ( 'setDirection: '.$direction );
		if($this->elevator->setDirection($direction)){
			$this->updateCookiesFromObject();
			return $this->response ( 'setDirection ' . $direction);
		}else{
			return $this->response ( 'Error in setDirection ' . $direction,false);
		}
	}
	
	
	/**
	 * Get JSON with all the Cookie values, its for AJAX request
	 * @return json 
	 * 
	 */
	public function getStatusFromCookies(){
		$this->checkCookies();
		$data = array(
				'current_floor' =>$_COOKIE['current_floor'],
				'total_floors' =>$_COOKIE['total_floors'],
				'direction' => $_COOKIE['direction'],
				'signal' =>$_COOKIE['signal'],
				'queue_up' =>(isset($_COOKIE['queue_up'])?array_unique(explode(',',$_COOKIE['queue_up'])):[]),
				'queue_down' =>(isset($_COOKIE['queue_down'])?array_unique(explode(',',$_COOKIE['queue_down'])):[]),
				'maintenance' =>(isset($_COOKIE['maintenance'])?array_unique(explode(',',$_COOKIE['maintenance'])):[])
		);
		return $this->response('getStatus',true,$data);
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
	 * Move the elevator
	 * @return json
	 */
	public function move(){
		$floor_before = $this->elevator->getCurrentFloor();
		$this->elevator->nextFloor();
		$current_floor = $this->elevator->getCurrentFloor();
		$data = array(
				'floor_before'=>$floor_before,
				'current_floor'=>$current_floor				
		);
		$this->updateCookiesFromObject();
		return $this->response('move '.$this->elevator->getDirection(),true,$data );
	}
	/**
	 * Reset all the values in the cookies
	 */
	public function resetCookies(){
		foreach($this->keys as $k){
			Log::save('Reset cookie '.$k);
			setcookie($k,"");
		}
// 		$this->updateObjectFromCookies();
// 		$this->updateCookiesFromObject();
		return $this->response('Reset Cookies',true);
	}
	/**
	 * Get values from cookies
	 */
	public function getCookieVals(){
		$tmp = array();
		foreach($this->keys as $k){
			if(isset($_COOKIE[$k])){
				$tmp[$k] = $_COOKIE[$k];
			}else{
				$tmp[$k] = "";
			}
		}
		return $this->response('Cookies Values',true,$tmp );
	}
	/**
	 * Set floors in maintenance, it gets in string separated by comma and urlencode
	 * @param string $str_floors
	 */
	public function setMaintenance($str_floors){
		Log::save('setMaintenance');
		$floors = explode(',',urldecode($str_floors));
		if(sizeof($floors)>0){
			foreach($floors as $floor){
				if(intval($floor)>0){
					Log::save('F'.$floor.' now is in maintenance');
					$this->elevator->setFloorInMaintenance($floor);
				}
			}
			$this->updateCookiesFromObject();
		}
		return $this->response('setMaintenance '.$str_floors,true);
	}
	/**
	 * Generate the array for the json answer
	 * @param string $msg
	 * @param string $status
	 * @return array
	 */
	public function response($msg, $success = true,$data=array()) {
		Log::save ( $msg, false );
		$json = array(
				'success' => $success,
				'data' => $data,
				'message' => $msg
		);
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
		//$trace = debug_backtrace ();
		//trigger_error ( 'Undefined property via __get(): ' . $property . ' in ' . $trace [0] ['file'] . ' on line ' . $trace [0] ['line'], E_USER_NOTICE );
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
