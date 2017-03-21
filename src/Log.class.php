<?php
namespace App;
class Log {
	protected static $filename = "";
	protected static function generateFile($unique = false) {
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
			echo $msg . PHP_EOL;
		}
	}
	public static function getContent() {
		return nl2br ( file_get_contents ( self::$filename ) );
	}
}
