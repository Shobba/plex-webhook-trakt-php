<?php
class Movie {
	public $title			= NULL;
	public $originalTitle	= NULL;
	public $summary			= NULL;
	public $year			= NULL;
	
	public function description() {
		return $this->originalTitle;
	}
}
?>