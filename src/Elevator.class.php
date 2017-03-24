<?php
namespace App;
use App\Log;
class Elevator{
	private static $debug = false;
	private static $debug_level='';//debug,save
	protected $direction = 'up';//up,down,stand,maintenance
	protected $current_floor = 1;
	protected $queue = array('up'=>array(),'down'=>array());
	protected $signal=  'door_close';//door_open,door_close,alarm
	protected $max_floor = 1;	
	protected $maintenance_floors = array();	
	protected $state = 'stand';//up,down,stand,maintenance

	public function __construct($total_floors=3){
		$this->setTotalFloors($total_floors);
	}
	/**
	 * Enable debugging
	 */
	public function enableDebug($debug_level='none'){
		self::$debug = true;
		$valid_levels = array('debug','save','none','');
		if(in_array($debug_level,$valid_levels)){
			self::$debug_level = $debug_level;
		}else{
			self::$debug_level = 'none';
		}
		Log::save('debug_level:  '.self::$debug_level);
	}
	/**
	 * Calculate number of floors in the building
	 * @return number
	 */
	public function getTotalFloors(){
		return $this->max_floor;
	}
	/**
	 * Set the current floor 
	 * @param integer $floor number of floor
	 * @return boolean
	 */
	public function setCurrentFloor($floor){
		self::fb('setCurrentFloor '.$floor);
//		if(intval($floor) >= $this->max_floor){
//			self::fb('floor>='.$this->max_floor);
//			$floor = $this->max_floor;
//			$this->setDirection('down');
//		}elseif(intval($floor)<1){
//			self::fb('floor<1');
//			$floor = 1;
//			$this->setDirection('up');
//		}else{
//			self::fb('ELSE');
//		}
//		self::fb('NEW CURRENT FLOOR IS '.$floor);
		if(intval($floor)==0){
			$floor = 1;
		}
		$this->current_floor = intval($floor);
		return true;
	}
	/**
	 * Get in where level is the elevator
	 * @return integer $current_floor
	 */
	public function getCurrentFloor(){
		return $this->current_floor;
	}
	/**
	 * Get elevator direction 
	 * @return string $direction up or down
	 */
	public function getDirection(){
		return $this->direction;
	}
	
	/**
	 * Set elevator direction
	 * @param string $direction up,down
	 */
	public function setDirection($direction){
		if($this->validateDirection($direction)){
// 			Log::save('setDirection: '.$direction );
			$this->direction = $direction;
			return true;
		}
		return false;
	}
	/**
	 * Change elevator direction if going up, changes to down
	 */
	public function switchDirection(){
		if($this->direction=='up'){
			return $this->setDirection('down');
		}else{
			return $this->setDirection('up');
		}
	}

	/**
	 * Change the elevator signal
	 * @param string $signal alarm,door_open,door_close
	 */
	public function setSignal($signal){
		$valid_signals = array('alarm','door_open','door_close');
		if(in_array($signal,$valid_signals)){
			$this->signal = $signal;
// 			Log::save($signal.' in '.$this->current_floor);
			self::fb('Signal set:'.$signal);
			return true;
		}
		self::fb('Cant set Signal');
		return false;
	}
	/**
	 * Get the elevator signal
	 * @return string $signal alarm,door_open,door_close
	 */
	public function getSignal(){
		return $this->signal;
	}
	/**
 * If floor its under maintenance
 * @param $floor number of floor
 */
	public function setFloorInMaintenance($floor){
		if(!$this->validateFloor($floor)){
			return false;
		}
		$this->maintenance_floors[] = $floor;
		$this->maintenance_floors = array_unique($this->maintenance_floors);
		return true;
	}
	/**
	 * The array of floors in maintenance
	 * @param array $floors floors
	 */
	public function setMaintenance($floors){
		$this->maintenance_floors = array();
		if(!is_array($floors)){
			return true;
		}
		if(sizeof($floors)==0){
			return true;
		}
		$b = true;
		foreach($floors as $k=>$floor){
			$b &= $this->setFloorInMaintenance($floor);
		}
		$this->maintenance_floors = array_unique($this->maintenance_floors);
		return $b;
	}
	/**
	 * Get the list of floors in maintenance
	 * @return array $floors
	 */
	public function getMaintenanceFloors(){
		return array_unique($this->maintenance_floors);
	}
	
	/**
	 * Get if the floor its in maintenance
	 * @param integer $floor
	 */
	public function isFloorInMaintenance($floor){
		if(!$this->validateFloor($floor)){
			return false;
		}
		return in_array($floor,$this->maintenance_floors);
	}

	public function getNearestFloor(){
		$nRequest = $this->getTotalPendingRequest('both');
		if($nRequest==0){
			return $this->getCurrentFloor();
		}
		$this->getQueue('both');
		$nRequestDown = $this->getQueue('down');//number of request to down
		$nRequestUp = $this->getQueue('up');//number of request to up
		$nDiffToRoof = $this->max_floor - $this->current_floor;//floors to Roof
		$nDiffToGround = $this->current_floor-1;//floors to Ground
		if($nDiffToGround==$nDiffToRoof){
			if($nRequestUp>$nRequestDown) {
				$this->setDirection('up');
				return current($this->queue['up']);
			}
		}else{
			$this->setDirection('down');
			return current($this->queue['down']);
		}
	}
	/**
	 * Calculate the next floor according to requests
	 * @return number $current_floor
	 */
	public function nextFloor(){
		if($this->getTotalPendingRequest('both')==0){
// 			Log::save('No pending requests... Return current floor F'.$this->current_floor);
			$this->switchDirectionIfisNecesary();
			return $this->getCurrentFloor();
		}
//		if($this->direction=='stand'){
//			return getNearestFloor();
//		}
		self::fb('queue[up]  :'.implode(',',$this->queue['up']));
		self::fb('queue[down]:'.implode(',',$this->queue['down']));
		$startFloor = $this->getCurrentFloor();//in the same direction
		$nRequest = $this->getTotalPendingRequest($this->direction);
		self::fb('nRequest:'.$nRequest);
		if($nRequest==0){
			self::fb('switchDirection:'.$this->direction);
			if(!$this->switchDirection()){
				self::fb('Cant switch direction.');
			}
			self::fb('new Direction is:'.$this->direction);
			self::fb('nRequest['.$this->direction.']:'.$this->getTotalPendingRequest($this->direction));
			if($this->getTotalPendingRequest($this->direction)==0){
				$this->switchDirectionIfisNecesary();
				return 	$this->getCurrentFloor();	
			}
		}
		//set currentFloor is the first in the queue[this->direction]
		$this->setCurrentFloor(current($this->queue[$this->direction]));
		self::fb('current_floor:'.$this->getCurrentFloor() );
		//remove the first in the queue[this->direction]
		self::fb('remove from queue['.$this->direction .'] floor '.$this->getCurrentFloor());
		if(!$this->removeFloorFromQueue($this->getCurrentFloor(), $this->direction) ){
			self::fb('Cant remove '.$this->getCurrentFloor().' from queue '.$this->direction.' switch direction');
		}else{
			self::fb('F'.$this->getCurrentFloor().' removed from queue '.$this->direction);
		}
		self::fb($startFloor.' - ' .$this->getCurrentFloor());
		return $this->getCurrentFloor();
	}

	/**
	 * If is in the last floor, change direction down, if is in the first go Up
	 * @return bool
	 */
	public function switchDirectionIfisNecesary(){
		$floor = $this->getCurrentFloor();
		if(intval($floor) >= $this->max_floor){
			self::fb('floor '.$this->max_floor.' change direction DOWN');
			return $this->setDirection('down');
		}elseif(intval($floor)<=1){
			self::fb('floor 1 change direction UP');
			return $this->setDirection('up');
		}
		self::fb('No direction changes required!');
		return false;
	}
	/**
	 * Add request to the queue of the elevator
	 * @param $fromFloor level in that press button 
	 * @param $direction up,down
	 */
	public function pressButton($fromFloor,$direction){
		if(!$this->validateFloor($fromFloor)){
			return false;
		}
		$this->queue[$direction][] = $fromFloor;
		$this->sortQueue($direction);
		return true;
	}
	
	/**
	 * Add a request in the queue
	 * @param integer $fromFloor
	 * @param integer $toFloor
	 * @return boolean $done
	 */
	public function addQueue($fromFloor,$toFloor){
		if(!$this->validateFloor($fromFloor) ){
			return false;
		}
		if(!$this->validateFloor($toFloor) ){
			return false;
		}
		//Cant go to the same floor
		if($fromFloor==$toFloor){
			$this->setSignal('door_open');//Open the door
			return false;
		}		
		//if origin floor its highest than destiny means goin down
		if($fromFloor>$toFloor){
			//Going Down
			if($this->pressButton($fromFloor, 'down')){
				return $this->pressButton($toFloor, 'down');
			}
		}else{
			//Going UP
			if($this->pressButton($fromFloor, 'up')){
				return $this->pressButton($toFloor, 'up');
			}
		}
		return false;

	}
	/**
	 * Returns the queue
	 * @return array $queue
	 */
	public function getQueue($direction='both'){
		switch($direction){
			case 'up':
				return $this->queue['up'];
				break;
			case 'down':
				return $this->queue['down'];
				break;
			default:
				return $this->queue;
		}

	}
	/**
	 * Setup the queue request
	 * @param string $direction
	 * @param array $queue
	 * @return boolean
	 */	
	public function setQueue($direction,$queue = array() ){
		if($this->validateDirection($direction)){
			if((is_array($queue))  && sizeof($queue)>0 ){
// 				Log::save('setQueue array:'.implode(',',$queue));
			}elseif(gettype($queue)=='string'){
// 				Log::save('setQueue string:'.$queue);
				$queue = explode(',',$queue);
			}
			$tmp = array();
			foreach($queue as $floor){
				if($this->validateFloor($floor)){
					$tmp[] =$floor;
				}
			}
			$this->queue[$direction] = array_unique($tmp);
			$this->sortQueue($direction);
			return true;
		}		
		return false;
	}
	/**
	 * Calculate the numbert of pending requests
	 * @param string $direction up,down,both
	 */
	public function getTotalPendingRequest($direction){
		$nPendingRequestUP = sizeof($this->queue['up']);
		$nPendingRequestDown = sizeof($this->queue['down']);
		switch($direction){
			case 'up':
				return $nPendingRequestUP;
				break;
			case 'down':
				return $nPendingRequestDown;
				break;
			default:
				return $nPendingRequestUP+$nPendingRequestDown;
		}
	}

	/**
	 * Validate direction
	 * @param string $direction
	 * @return boolean
	 */
	protected function validateDirection($direction){
		$valid_directions = array('up','down','stand','maintenance');
		if(in_array($direction,$valid_directions)){
			return true;
		}
		return false;
	}
	/**
	 * Sort queue by direction
	 * @param string $direction up,down
	 */
	protected function sortQueue($direction){
		if($direction=='up'){
			$this->queue['up']=array_unique($this->queue['up'],SORT_NUMERIC);
			sort($this->queue['up']);
		}else{
			$this->queue['down']=array_unique($this->queue['down'],SORT_NUMERIC);
			rsort($this->queue['down']);
		}
		return true;
	}
	/**
	 * Remove floor from Queue
	 * @param integer $floor
	 * @param string $direction
	 * @return boolean
	 */
	protected function removeFloorFromQueue($floor,$direction){
		if(!$this->validateDirection($direction)){
			return false;
		}
		if(!$this->validateFloor($floor)){
			return false;
		}
		$position = array_search($floor, $this->queue[$direction]);
		if(($position!==false) && isset($this->queue[$direction][$position])){
			array_shift($this->queue[$direction]);
			reset($this->queue[$direction]);
			$this->sortQueue($direction);
			return true;
		}
		return false;
	}
	/**
	 *
	 * @return integer|false
	 */
	protected function getNextStop(){
		if(sizeof($this->queue[$this->direction])==0){
// 			Log::save('No pending requests... current floor F'.$this->current_floor);
			self::fb('No pending request return current floor '.$this->getCurrentFloor()  );
			return $this->current_floor;
		}
		self::fb('queue up:  '.implode(',',$this->queue['up']) );
		self::fb('queue down:  '.implode(',',$this->queue['down']) );
// 		Log::save('Next '.$this->direction.' F'.current($this->queue[$this->direction]));
		return current($this->queue[$this->direction]);
	}
	/**
	 * Validate a floor number
	 * @param integer $floor
	 * @return integer $floor
	 */
	protected function validateFloor($floor){
		if(intval($floor) >= $this->max_floor){
			$floor = $this->max_floor;
		}elseif($floor<=1){
			$floor = 1;
		}
		return true;
	}
	
	public function setTotalFloors($total_floors){
		$this->max_floor = ( intval($total_floors)==0?1:intval($total_floors));
	}
	
	protected static function fb($msg){
//		if(self::$debug){
//			switch(self::$debug_level){
//				case ('debug'):
//					Log::debug($msg,self::$debug);
//					break;
//				case ('save'):
					Log::save($msg);
//					break;
//			}
//		}


	}
}