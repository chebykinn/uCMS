<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
use uCMS\Core\Extensions\Users\User;
class Tools{
	private static $isOwnerOverridden = false;
	private static $owner = "core";
	public static function GenerateHash($length = 32){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789-";
		$code = "";
		$clen = strlen($chars) - 1;  
		while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];  
		}
		return $code;
	}

	public static function GetCurrentOwner(){
		$name = "core";
		if ( self::$isOwnerOverridden ){

			// If method is called in anothed core method we prevent
			// looking for extension even if it was called from it.
			self::$isOwnerOverridden = false;
			return self::$owner;
		}
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		// \uCMS\Core\Debug::PrintVar($trace);
		foreach ($trace as $index => $level) {
			foreach ($level as $key => $value) {
				$found = false;
				if( $key == 'file' && is_file($value) ){
					if( strpos($value, Extension::PATH) !== false ){
						$path = explode(Extension::PATH, dirname($value));
					}else{
						$path = explode(Theme::PATH, dirname($value));
					}
					if( is_array($path) && isset($path[1]) ){
						$name = $path[1];
						if( strpos($name, "/") !== false){
							$name = explode("/", $name);
							$name = $name[0];
						}
						if( Extension::IsLoaded($name) ){ 
							$found = true;
							break;
						}
						if( Theme::IsExists($name) ){
							$found = true;
						}
					}
				}
			}
		}
		// TODO: some undefined behavior when extension and theme with the same name
		return $name;
	}

	public static function OverrideOwner($newOwner = "core"){
		if( strpos(__NAMESPACE__, "uCMS\\Core") !== false ){
			self::$isOwnerOverridden = true;
			if( Extension::IsExists($newOwner) || Theme::IsExists($newOwner) ){
				self::$owner = $newOwner;
			}
		}
	}

	public static function PrepareSQL($value){
		if($value == "") return false;
		$value = implode("", explode( "\\", $value));
		$value = stripslashes($value);
		$value = addcslashes($value, '%');
		return $value;
	}

	public static function PrepareXSS($value){

	}

	public static function FormatTime($time, $format = ""){
		if( empty($format) ){
			$format = Settings::Get('datetime_format');
		}
		// If user have his own timezone we will use it.
		$timezone = User::Current()->getTimezone();
		if( empty($timezone) ){
			$timezone = Settings::Get('ucms_timezone');
		}
		$datetime = new \DateTime("@$time");
		// DateTime ignores $timezone parameter when created from timestamp, so
		// we have to set in explicitly.
		$datetime->setTimezone(new \DateTimeZone($timezone)); 
		return $datetime->format($format);
	}

}
?>