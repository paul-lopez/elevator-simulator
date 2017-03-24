<?php
namespace App;
use App\Elevator;
class Log {
	protected static $filename = "";
	protected static function generateFile($unique = false) {
		date_default_timezone_set("America/Tijuana");
		if ($unique) {
			self::$filename = 'log-' . time () . '.txt';
		} else {
			self::$filename = 'log.txt';
		}
	}
	public static function reset() {
		if (empty ( self::$filename )) {
			self::generateFile ();
		}
		$fh = fopen ( self::$filename, "w" );
		fclose ( $fh );
		return true;
	}
	public static function save($msg) {
		if (empty ( self::$filename )) {
			self::generateFile ();
		}
		$msg = '['.date('d/M/Y h:i:s a').'] '.$msg.PHP_EOL;
		error_log ( $msg , 3, self::$filename );
	}
	public static function debug($msg, $show = false) {
		if ($show) {
			echo $msg . '<br>'.PHP_EOL;
		}
		self::save($msg);
	}
	public static function getContent() {
		return nl2br ( file_get_contents ( self::$filename ) );
	}

	public static function saveElevatorStatus(Elevator $elevator,$title=""){
		self::save('=============================');
		self::save($title.' BEGIN');
		self::save('Current_floor:'. $elevator->getCurrentFloor());
		self::save('Total_floors: '. $elevator->getTotalFloors());
		self::save('Direction: '. $elevator->getDirection());
		self::save('Signal: '. $elevator->getSignal());
		self::save('Queue_up: '. implode(',',array_unique($elevator->getQueue('up'))));
		self::save('Queue_down: '. implode(',',array_unique($elevator->getQueue('down')) ));
		self::save('Maintenance: '. implode(',',$elevator->getMaintenanceFloors() ));
		self::save($title.' END '.(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:''));
		self::save('============================='.PHP_EOL);
	}
	public static function printElevatorStatus(Elevator $elevator,$title=""){

		self::debug('=============================',true);
		self::debug($title.' BEGIN',true);
		self::debug('Current_floor:'. $elevator->getCurrentFloor(),true);
		self::debug('Total_floors: '. $elevator->getTotalFloors(),true);
		self::debug('Direction: '. $elevator->getDirection(),true);
		self::debug('Signal: '. $elevator->getSignal(),true);
		self::debug('Queue_up: '. implode(',',array_unique($elevator->getQueue('up'))),true);
		self::debug('Queue_down: '. implode(',',array_unique($elevator->getQueue('down')) ),true);
		self::debug('Maintenance: '. implode(',',$elevator->getMaintenanceFloors() ),true);
		self::debug($title.' END '.(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:''),true);
		self::debug('============================='.PHP_EOL,true);
	}
	public function __destruct(){
		Log::save(PHP_EOL.PHP_EOL.PHP_EOL);
	}


}
