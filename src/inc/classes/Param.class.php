<?php
class Param {
	public static function get($id) {
		if(!isset($_GET['param'])) $_GET['param'] = "";
		if($_GET['param'] != "") {
			$expl = explode("/", $_GET['param']);
			if(isset($expl[$id])) return $expl[$id];
		}
		
		return false;
	}
}
?>