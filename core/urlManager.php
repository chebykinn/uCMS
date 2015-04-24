<?php
class URLManager{
	private static $rawURL;
	private static $keyURL;
	private static $action;

	public static function init(){
		self::$rawURL = $_SERVER['REQUEST_URI'];
		if( empty($_GET['action']) ){
			self::$keyURL = preg_split('#(/)#', self::$rawURL, -1, PREG_SPLIT_NO_EMPTY);
		}else{
			self::$keyURL[0] = $_GET['action'];
			if( !empty($_GET['key']) ){
				self::$keyURL = preg_split('#(/)#', $_GET['key'], -1, PREG_SPLIT_NO_EMPTY);
				array_unshift(self::$keyURL, $_GET['action']);
			}
		}
		self::$action = "";
	}

	public function __construct(){
		varDump(debug_backtrace());
	}

	public static function getCurrentAction(){
		if( !empty(self::$keyURL[0]) ){
			return self::$keyURL[0];
		}
		return INDEX_ACTION;
	}

	public static function getCurrentAdminAction(){
		return self::getKeyValue(ADMIN_ACTION);
	}

	public static function isKeyInURL($key){
		return ( is_array(self::$keyURL) && in_array($key, self::$keyURL) );
	}

	public static function getKeyValue($key){
		if( self::isKeyInURL($key)){
			$index = array_search($key, self::$keyURL);
			if( !empty(self::$keyURL[$index+1]) )
				return self::$keyURL[$index+1];
		}
		return "";
	}

	public static function getCurrentPage(){
		$pageValue = getKeyValue('page');
		if( !empty($pageValue) ){
			return getKeyValue('page');
		}
		return 1;
	}

	public static function getRaw(){
		return self::$rawURL;
	}

	public static function redirect($url){
		/**
		* @todo add some checks
		*/
		if(headers_sent()){
			echo '<a href="'.urlencode($url).'">'.tr("Click here to redirect to: @s", htmlspecialchars($url)).'</a>';
		}
		else{
			/**
			* @todo url encoding
			*/
			header('Location: '.urldecode($url));
			exit;
		}
	}

	public static function refresh($seconds = 0){
		/**
		* @todo add params for keeping GET and keys or somewhat
		*/
		$url = $_SERVER['PHP_SELF'];
		if(headers_sent()){
			echo '<meta http-equiv="refresh" content="'.$seconds.'">';
		}
		else{
			header("Refresh: $seconds; url=$url");
			exit;
		}
	}

	public static function makeLink($action, $key = "", $noEncode = false){
		$isCleanUrl = (bool)Settings::get('clean_url');
		$amp = $noEncode ? '&' : '&amp;';
		return (UCMS_DIR.($isCleanUrl ? "$action/$key" : "?action=$action".( !empty($key) ? $amp."key=$key" : "" )));
	}
}
?>