<?php
class Config {
	
	private static $configs = NULL;
	
	private static function initConfig() {
		if(is_null(self::$configs)) {
			$config_path = dirname(dirname(__FILE__)) . "/config.php";
			
			if(!file_exists($config_path)) {
				throw new Exception("config.php missing!");
			}
			
			self::$configs = include($config_path);
		}
	}
	
	public static function get($key){
		self::initConfig();
		
		if(isset(self::$configs[$key])) return self::$configs[$key];
		
		throw new Exception("Configuration (" . $key . ") not found!");
	}
}

?>