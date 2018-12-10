<?php
class Encryption {
	private static $key = NULL;
	private static $iv = "1234567812345678";
	private static $debug = false;
	
	public static function encrypt($decrypted_string) {
	    if (self::$debug) {
	        return $decrypted_string;
        }
		return openssl_encrypt($decrypted_string, "AES-128-CBC", self::getKey(), OPENSSL_RAW_DATA, self::$iv);
	}
	
	public static function decrypt($encrypted_string) {
        if (self::$debug) {
            return $encrypted_string;
        }
		return openssl_decrypt($encrypted_string, "AES-128-CBC", self::getKey(), OPENSSL_RAW_DATA, self::$iv);
	}
	
	private static function getKey() {
		if(is_null(self::$key)) {
			self::$key = Config::get("ENCRYPTION_KEY");
		}
		
		return self::$key;
	}
}
?>