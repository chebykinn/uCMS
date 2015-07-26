<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\Extension;
use uCMS\Core\Extensions\Theme;
class Tools{

	public static function GenerateHash($length = 32){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;  
		while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];  
		}
		return $code;
	}

	public static function GetCurrentOwner(){
		$name = "core";
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $level) {
			foreach ($level as $key => $value) {
				$found = false;
				if( $key == 'file' && is_file($value) ){
					if( strpos($value, Extension::PATH) ){
						$path = explode(Extension::PATH, dirname($value));
					}else{
						$path = explode(Theme::PATH, dirname($value));
					}
					if( is_array($path) && isset($path[1]) ){
						$name = $path[1];
						if( Extension::IsLoaded($name) || Theme::IsExists($name) ){
							$found = true;
						}
					}
				}
				// TODO: Add themes detection
				if($found) break;
			}
		}
		return $name;
	}

}
?>