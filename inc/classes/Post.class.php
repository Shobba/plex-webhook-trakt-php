<?php
class Post {
	public static function send($url, $body = array(), $header = array()) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		
		curl_setopt($ch, CURLOPT_POST, TRUE);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, self::prepareHeaders($header));
		
		$response = curl_exec($ch);
		Log::write($response, "Curl");
		
		curl_close($ch);
		
		return $response;
	}
	
	private static function prepareHeaders($headers) {
		if(count($headers) == 0) return array("Content-type: application/json");
		
		$flattened = array();

		foreach($headers as $key => $header) {
			if (is_int($key)) {
				$flattened[] = $header;
			} else {
				$flattened[] = $key.': '.$header;
			}
		}

		return $flattened;
	}
}
?>