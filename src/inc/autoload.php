<?php
// Autoloader for own classes (inc/classes)
spl_autoload_register(function ($class) {
	$path = __DIR__ . "/classes/" . $class . ".class.php";
	if(file_exists($path)) {
		require_once($path);
	}
});
?>