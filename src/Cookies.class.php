<?php
namespace App;

class Cookies{
	protected $keys =array(
			'current_floor',
			'total_floors',
			'direction',
			'queue_down',
			'queue_up',
			'maintenance'
	);
	
	protected function checkCookies(){
		foreach($this->keys as $k){
			if(!isset($_COOKIE[$k])){
				setcookie($k,(is_array($this->$k)?implode(',',$this->$k):$this->$k),$this->expires);
			}
		}
	}
	
	/**
	 * Setup all the cookie values
	 */
	public function setCookieValues(){
		Log::save('setCookieValues');
		Log::save(var_export($this->elevator,true));
	
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
	
		return $this->response('setCookieValues',true,var_export($_COOKIE,true));
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
	 * Reset all the values in the cookies
	 */
	public function resetCookies(){
		foreach($this->keys as $k){
			setcookie($k,"");
		}
		return $this->response('Reset Cookies',true);
	}
	/**
	 * Get values from cookies
	 */
	public function getCookieVals(){
		return $this->response('Cookies Vals',true,var_export($_COOKIE,true));
	}
}