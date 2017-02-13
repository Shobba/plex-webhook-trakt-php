<?php
class Episode {
	public $title		= NULL;
	public $summary		= NULL;
	public $showTitle	= NULL;
	public $season		= NULL;
	public $number		= NULL;
	
	public function getSeasonEpisodeString() {
		$str = "";
		if($this->season != NULL) 	$str .= "S" . ($this->season < 10 ? "0" : "") . $this->season;
		if($this->number != NULL)	$str .= "E" . ($this->number < 10 ? "0" : "") . $this->number;
		return $str;
	}
	
	public function description() {
		return $this->showTitle  . " - " . $this->getSeasonEpisodeString()  . " - " . $this->title;
	}
}
?>