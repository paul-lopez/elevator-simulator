<?php
	namespace App;
	use App\Elevator;
	class Sessions{
		protected $keys =array(
			'current_floor',
			'total_floors',
			'direction',
			'queue_down',
			'queue_up',
			'maintenance'
		);
		public function __construct(){
			if (session_status() !== PHP_SESSION_ACTIVE) {
				if(!headers_sent()){
					session_start();
				}
			}
		}
		/**
		 * Save all the values of elevator instance in Session
		 * @param \App\Elevator $elevator
		 * @return bool
		 */
		public static function setVals(Elevator $elevator ){
			if(!($elevator instanceof Elevator)){
				Log::save('Elevator not instance of Elevator');
				Log::save(var_export($elevator instanceof Elevator,true));
				return false;
			}
			$_SESSION[ 'current_floor']= $elevator->getCurrentFloor();
			$_SESSION[ 'total_floors']= $elevator->getTotalFloors();
			$_SESSION[ 'direction']= $elevator->getDirection();

			$_SESSION[ 'signal']= $elevator->getSignal();
			if($elevator->getTotalPendingRequest('both')>=0){
				$queue = $elevator->getQueue();
				if(sizeof($queue['up'])>=0){
					$_SESSION[ 'queue_up']= $queue['up'];
				}
				if(sizeof($queue['down'])>=0) {
					$_SESSION['queue_down'] = $queue['down'];
				}
			}
			if(sizeof($elevator->getMaintenanceFloors())>0){
				$_SESSION[ 'maintenance']= $elevator->getMaintenanceFloors() ;
			}
			Log::saveElevatorStatus($elevator,'Sessions::setVals');
			return true;
		}

		/**
		 * Retrieve stored values of Elevator in Session to a new instance of Elevator
		 * @return \App\Elevator
		 */
		public static function getElevator($debug=false,$debug_level='save'){
			$elevator = new Elevator();
			if($debug){
				$elevator->enableDebug($debug_level);
			}

			if(isset($_SESSION['total_floors']) ){
				$elevator->setTotalFloors(intval($_SESSION['total_floors']) );
			}
			if(isset($_SESSION['current_floor'])){
				$elevator->setCurrentFloor(intval($_SESSION['current_floor'] ));
			}
			if(isset($_SESSION['direction']) ){
				$elevator->setDirection($_SESSION['direction']);
			}
			if(isset($_SESSION['signal'])){
				$elevator->setSignal( (!empty($_SESSION['signal'])?$_SESSION['signal']:'door_close'  ));
			}
			if(isset($_SESSION['queue_up'])){
				$elevator->setQueue('up',$_SESSION['queue_up']);
			}
			if(isset($_SESSION['queue_down'])){
				$elevator->setQueue('down',$_SESSION['queue_down']);
			}
			if(isset($_SESSION['maintenance'])){
				$elevator->setMaintenance($_SESSION['maintenance']);
			}
			Log::saveElevatorStatus($elevator,'Sessions::getElevator');
			return $elevator;
		}

		/**
		 * Reset all the values in the session for Elevator
		 */
		public function resetAll(){
			foreach($this->keys as $k){
				$_SESSION[$k] = "";
			}
			$elevator = new Elevator(7);
			$elevator->setCurrentFloor(1);//current_floor
			$elevator->setSignal('door_close');
			$elevator->setDirection('up');
			$elevator->setMaintenance([]);
			$elevator->setQueue('up',[]);
			$elevator->setQueue('down',[]);
			self::setVals($elevator);
			return $elevator;
		}



	}