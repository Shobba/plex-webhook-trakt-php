<?php
class Log {
	public static function write($text) {
		$path = dirname(dirname(__FILE__)) . "/log.txt";
		if(!file_exists($path)) file_put_contents($path, "");
		
		$content = file_get_contents($path);
		$content .= $content != "" ? "\n" : "";
		$content .= $text;
		file_put_contents($path, $content);
	}
}
?>