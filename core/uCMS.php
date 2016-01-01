<?php
namespace uCMS\Core;
use uCMS\Core\Extensions\FileManager\File;

class uCMS extends Object{
	const CORE_VERSION = "2.0 Alpha 7";
	const COMPATIBILITY_VERSION = "2.x";
	const MIN_PHP_VERSION = "5.4";
	const MIN_MYSQL_VERSION = "5.0";
	const ERR_NOT_FOUND = 404;
	const ERR_FORBIDDEN = 403;
	const ERR_INVALID_PACKAGE = 100;
	const ERR_HOST_FAILURE = 101;
	const ERR_NO_UPDATE_PACKAGE = 102;
	const ERR_NO_PERMISSIONS = 103;
	const SUCCESS = 0;
	const CONFIG_FILE = 'config.php';
	const CONFIG_SAMPLE = 'config-manual.php';
	const UCMS_HOST = 'http://ucms.ivan4b.ru/';
	const PUBLIC_PATH = 'pub/';

	public static function GetDirectory(){
		$storedValue = Setting::Get(Setting::UCMS_DIR);

		if( empty($storedValue) ){
			$url = parse_url(urldecode($_SERVER['REQUEST_URI']));
			$storedValue = $url['path'];
		}
		return $storedValue;
	}

	public static function GetDomain(){
		$storedValue = Setting::Get("site_domain");
		return empty($storedValue) ? $_SERVER['REQUEST_URI'] : $storedValue;
	}

	public static function GetLocation(){
		return self::GetDomain().self::GetDirectory();
	}

	public static function GetHTTPStatus($url = self::UCMS_HOST){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_HEADER,         true);
		curl_setopt($ch, CURLOPT_NOBODY,         true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT,        1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$result = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $status;
	}

	public static function IsHostAvailable($url = self::UCMS_HOST){
		$status = self::GetHTTPStatus($url);
		return ($status == 200); 
	}

	public static function IsRemoteFileExists($filepath, $host = self::UCMS_HOST){
		$status = self::GetHTTPStatus($filepath);
		return ($status == 200);
	}

	public static function GetRemoteFile($filepath, $host = self::UCMS_HOST, $asString = true){
		$exists = self::IsRemoteFileExists($filepath, $host);
		if( !$exists ) return false;

		if( $asString ){
			$content = @file_get_contents($file);
		}else{
			$content = @file($filepath);
		}
		return $content;
	}

	public static function GetLatestVersion(){
		$file = self::UCMS_HOST.self::PUBLIC_PATH."version";
		$strings = self::GetRemoteFile($file, self::UCMS_HOST, false);
		if(!empty($strings[0])) return $strings[0];
		return false;
	}

	public static function GetUpdateNotes(){
		$file = self::UCMS_HOST.self::PUBLIC_PATH."notes";
		$text = self::GetRemoteFile($file);
		if( !empty($text) ) return $text;
		return false;
	}

	public static function IsUpdateAvailable(){
		return version_compare(self::CORE_VERSION, self::GetLatestVersion(), '<');
	}

	public static function GetRemoteBasePath($version = self::CORE_VERSION){
		$versionData = explode(' ', $version, 2);
		$baseVersion = $version;
		$stageVersion = "";
		if( isset($versionData[1]) ){
			$baseVersion = $versionData[0];
			$stageVersion = mb_strtolower(str_replace(" ", "-", $versionData[1]));
		}
		$path = self::UCMS_HOST.self::PUBLIC_PATH;
		$remotepath = "{$path}ucms-$baseVersion/";
		return $remotepath;
	}

	public static function GetRemotePath($version = self::CORE_VERSION){
		$basePath = self::GetRemoteBasePath($version);
		$pathVersion = mb_strtolower(str_replace(" ", "-", $version));
		$fullpath = $basePath.$pathVersion.'/';
		return $fullpath;
	}

	public static function DownloadPackage($version){
		$remotepath = self::GetRemotePath($version)."ucms.zip";
		$localpath = ABSPATH.File::UPLOADS_PATH.'update.zip';

		$file_headers = @get_headers($remotepath);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
			$exists = false;
		}else {
			$exists = true;
		}

		if( $exists ){
			@copy($remotepath, $localpath);
			return self::SUCCESS;
		}
		return self::ERR_NOT_FOUND;
	}

	public static function GetPackageHashes(){
		$file = self::UCMS_HOST.self::PUBLIC_PATH."hashes";
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
?>