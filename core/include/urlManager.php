<?php
class URLManager{
	private $rawURL;
	private $keyURL;
	private $action;

	public function __construct(){
		$this->rawURL = $_SERVER['REQUEST_URI'];
		if( empty($_GET['action']) ){
			$this->keyURL = preg_split('#(/)#', $this->rawURL, -1, PREG_SPLIT_NO_EMPTY);
		}else{
			$this->keyURL[0] = $_GET['action'];
			if( !empty($_GET['key']) ){
				$this->keyURL = preg_split('#(/)#', $_GET['key'], -1, PREG_SPLIT_NO_EMPTY);
				array_unshift($this->keyURL, $_GET['action']);
			}
		}
		$this->action = "";
	}

	public function getCurrentAction(){
		if( !empty($this->keyURL[0]) ){
			return $this->keyURL[0];
		}
		return INDEX_ACTION;
	}

	public function getCurrentAdminAction(){
		return $this->getKeyValue(ADMIN_ACTION);
	}

	public function isKeyInURL($key){
		return ( is_array($this->keyURL) && in_array($key, $this->keyURL) );
	}

	public function getKeyValue($key){
		if( $this->isKeyInURL($key)){
			$index = array_search($key, $this->keyURL);
			if( !empty($this->keyURL[$index+1]) )
				return $this->keyURL[$index+1];
		}
		return "";
	}

	public function getCurrentPage(){
		$pageValue = getKeyValue('page');
		if( !empty($pageValue) ){
			return getKeyValue('page');
		}
		return 1;
	}

	public function getRaw(){
		return $this->rawURL;
	}

	public static function redirect($url){
		/**
		* @todo add some checks
		*/
		if(headers_sent()){
			echo '<a href="'.urlencode($url).'">'.tr("Click here to redirect to: @s", htmlspecialchars($url)).'</a>';
		}
		else{
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

	public static function makeLink($action, $key){
		$isCleanUrl = (bool)get_setting('clean_url');
		return (UCMS_DIR.($isCleanUrl ? "$action/$key" : "?action=$action&amp;key=$key"));
	} 
}
?>