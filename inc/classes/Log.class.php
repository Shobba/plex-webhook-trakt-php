<?php
class Log {
	public static function write($text, $file = "log") {
		$path = __DIR__ . "/../../logs/" . $file . ".txt";
		if(!file_exists($path)) file_put_contents($path, "");
		
		$content = file_get_contents($path);
		$content .= $content != "" ? "\n" : "";
		$content .= date("Y-m-d H:i:s") . " - " . $text;
		file_put_contents($path, $content);
	}
}
?>