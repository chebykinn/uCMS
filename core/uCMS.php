<?php
namespace uCMS\Core;

class uCMS{
	const CORE_VERSION = "2.0 Alpha 5";
	const COMPATIBILITY_VERSION = "2.x";
	const MIN_PHP_VERSION = "5.4";
	const MIN_MYSQL_VERSION = "5.0";
	const ERR_NOT_FOUND = 404;
	const ERR_FORBIDDEN = 403;
	const ERR_INVALID_PACKAGE = 100;
	const ERR_HOST_FAILURE = 101;
	const SUCCESS = 0;
	const CONFIG_FILE = 'config.php';
	const CONFIG_SAMPLE = 'config-manual.php';
	const UCMS_HOST = 'http://ucms.ivan4b.ru';

	public static function GetDirectory(){
		$storedValue = Settings::Get('ucms_dir');
		// TODO: more complex default value
		return empty($storedValue) ? '/' : $storedValue;
	}

	public static function GetDomain(){
		$storedValue = Settings::Get("site_domain");
		return empty($storedValue) ? $_SERVER['REQUEST_URI'] : $storedValue;
	}

	public static function GetLocation(){
		return self::GetDomain().self::GetDirectory();
	}

	public static function GetLatestVersion(){
		$file = self::UCMS_HOST."/pub/version";
		$file_headers = @get_headers($file);
		$strings = @file($file);
		if(!empty($strings[0])) return $strings[0];
		return false;
	}

	public static function GetUpdateNotes(){
		$file = self::UCMS_HOST."/pub/notes";
		$file_headers = @get_headers($file);
		$text = @file_get_contents($file);
		if( !empty($text) ) return $text;
		return false;
	}

	public static function IsUpdateAvailable(){
		return version_compare(self::CORE_VERSION, self::GetLatestVersion(), '<');
	}

	public static function GetPackageHashes(){
		$file = self::UCMS_HOST."/pub/hashes";
		$file_headers = @get_headers($file);
		$hashes = @file($file);
		return !empty($hashes) ? $hashes : array();
	}

	public static function GetPackageInfo($packagePath, &$version, &$notes){
		$zip = new \ZipArchive();
		$result = $zip->open($packagePath);
		if( $result === true ){
			$hash = hash_file('crc32b', $packagePath);
			$remoteHashes = self::GetPackageHashes();

			if( !in_array($hash, $remoteHashes) ){
				$zip->close();
				if( empty($remoteHashes) ){
					return self::ERR_HOST_FAILURE;
				}else{
					return self::ERR_INVALID_PACKAGE;
				}
			}

			$version = $zip->getFromName('version');
			$notes = $zip->getFromName('notes');

			$zip->close();
			return self::SUCCESS;
		}
		return self::ERR_INVALID_PACKAGE;
	}
}

if( !defined("UCMS_DIR") ){
	define("UCMS_DIR", '/');
}
?>