<?php
class Trakt {
	private static $client_id = NULL;
	private static $client_secret = NULL;
	private static $api_url = "https://api.trakt.tv/scrobble";
	
	private static function getClientID() {
		if(is_null(self::$client_id)) {
			self::$client_id = Config::get("TRAKT_CLIENT_ID");
		}
		
		return self::$client_id;
	}
	
	private static function getClientSecret() {
		if(is_null(self::$client_secret)) {
			self::$client_secret = Config::get("TRAKT_CLIENT_SECRET");
		}
		
		return self::$client_secret;
	}
	
	private $plex_username = NULL;
	
	public function __construct($plex_username) {
		$this->plex_username = $plex_username;
	}
	
	public function action($event, $mediaObject, $player = NULL) {
		$log = $this->plex_username . (!is_null($player) ? "		-		" . $player : "") . "		-		" . $event . (!in_array($event, array(PlexEvent::Resume, PlexEvent::Scrobble)) ? "	" : "") . "	-		" . $mediaObject->description();
		Log::write($log);
		
		switch($event) {
			case PlexEvent::Play:
			case PlexEvent::Resume:
				$this->start($mediaObject);
				break;
				
			case PlexEvent::Pause:
				$this->pause($mediaObject);
				break;
				
			case PlexEvent::Stop:
				$this->stop($mediaObject);
				break;
				
			case PlexEvent::Scrobble:
				$this->scrobble($mediaObject);
				break;
		}
	}
	
	public function authorize() {
		header("LOCATION: https://trakt.tv/oauth/authorize?response_type=code&client_id=" . self::getClientID() . "&redirect_uri=" . urlencode(str_replace($_SERVER['SCRIPT_URL'], "/authorize", $_SERVER['SCRIPT_URI']) . "?plex_user=" . $this->plex_username) . "&state=state");
	}
	
	public function writeAccessToken($code) {
		$path = dirname(dirname(__FILE__)) . "/trakt_api_token.txt";
		if(!file_exists($path)) file_put_contents($path, json_encode(array()));
		
		$api_keys = json_decode(Encryption::decrypt(file_get_contents($path)));
		
		$trakt_response = Post::send("https://api.trakt.tv/oauth/token", array(
			"code"			=> $code,
			"client_id"		=> self::getClientID(),
			"client_secret" => self::getClientSecret(),
			"redirect_uri"	=> str_replace($_SERVER['SCRIPT_URL'], "/authorize", $_SERVER['SCRIPT_URI']) . "?plex_user=" . $this->plex_username,
			"grant_type"	=> "authorization_code"
		));
		
		$api_keys->{$this->plex_username} = json_decode($trakt_response);
		
		file_put_contents($path, Encryption::encrypt(json_encode($api_keys)));
	}
	
	private function refreshAccessToken() {
		$path = dirname(dirname(__FILE__)) . "/trakt_api_token.txt";
		if(!file_exists($path)) file_put_contents($path, json_encode(array()));
		
		$old = json_decode(Encryption::decrypt(file_get_contents($path)));
		if(!isset($old->{$this->plex_username})) return false;
		
		if(time() + 24*60*60 > $old->{$this->plex_username}->created_at + $old->{$this->plex_username}->expires_in) {
			
			$trakt_response = Post::send("https://api.trakt.tv/oauth/token", array(
				"refresh_token"	=> $old->{$this->plex_username}->refresh_token,
				"client_id"		=> self::getClientID(),
				"client_secret" => self::getClientSecret(),
				"redirect_uri"	=> str_replace($_SERVER['SCRIPT_URL'], "/authorize", $_SERVER['SCRIPT_URI']) . "?plex_user=" . $this->plex_username,
				"grant_type"	=> "authorization_code"
			));
			
			$old->{$this->plex_username} = json_decode($trakt_response);
			
			file_put_contents($path, Encryption::encrypt(json_encode($old)));
		}
		
		return true;
	}
	
	private function getAccessToken() {
		if(!$this->refreshAccessToken()) {
			throw new Exception("Did not authorize Trakt.");
		}
		
		$data = json_decode(Encryption::decrypt(file_get_contents(dirname(dirname(__FILE__)) . "/trakt_api_token.txt")));
		return $data->{$this->plex_username}->access_token;
	}
	
	private function getAuthArray() {
		return array(
			"Content-Type" => "application/json",
			"Authorization" => "Bearer " . $this->getAccessToken(),
			"trakt-api-version" => "2",
			"trakt-api-key" => self::getClientID()
		);
	}
	
	private function getMediaDataArray($mediaObject, $progress) {
		$data = array();
		
		if(is_a($mediaObject, "Episode")) {
			$data = array(
				"show" => array(
					"title" => trim(preg_replace("/\([0-9]+\)/", "", $mediaObject->showTitle))
				),
				"episode" => array(
					"season" => $mediaObject->season,
					"number" => $mediaObject->number
				),
				"progress" => $progress
			);
			
		} elseif(is_a($mediaObject, "Movie")) {
			$data = array(
				"movie" => array(
					"title" => $mediaObject->originalTitle,
					"year" => $mediaObject->year
				),
				"progress" => $progress
			);
		}
		
		return $data;
	}
	
	private function start($mediaObject) {
		$data = $this->getMediaDataArray($mediaObject, 0);
		$response = Post::send(self::$api_url . "/start", $data, $this->getAuthArray());
		Log::write("TRAKT START 0 - " . $response);
	}
	
	private function pause($mediaObject) {
		$data = $this->getMediaDataArray($mediaObject, 0);
		$response = Post::send(self::$api_url . "/pause", $data, $this->getAuthArray());
		Log::write("TRAKT PAUSE 0 - " . $response);
	}
	
	private function stop($mediaObject) {
		$data = $this->getMediaDataArray($mediaObject, 0);
		$response = Post::send(self::$api_url . "/stop", $data, $this->getAuthArray());
		Log::write("TRAKT STOP 0 - " . $response);
	}
	
	private function scrobble($mediaObject) {
		$data = $this->getMediaDataArray($mediaObject, 90);
		$response = Post::send(self::$api_url . "/stop", $data, $this->getAuthArray());
		Log::write("TRAKT STOP 90 - " . $response);
		$response = Post::send(self::$api_url . "/start", $data, $this->getAuthArray());
		Log::write("TRAKT START 90 - " . $response);
	}
}
?>