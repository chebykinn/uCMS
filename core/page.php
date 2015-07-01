<?php
class Page{
	private $url;
	private $status;
	private $action = INDEX_ACTION;
	private $data;
	private static $currentPage;

	public function __construct($url = ""){
		if( $url == "" ){
			$this->url = urldecode($_SERVER['REQUEST_URI']);
		}else{
			$this->url = urldecode($url);
		}

		if( empty($_GET['action']) ){
			$this->data = preg_split('#(/)#', $this->url, -1, PREG_SPLIT_NO_EMPTY);
		}else{
			$this->data[0] = $_GET['action'];
			if( !empty($_GET['key']) ){
				$this->data = preg_split('#(/)#', $_GET['key'], -1, PREG_SPLIT_NO_EMPTY);
				array_unshift($this->data, $_GET['action']);
			}
		}

		if( !empty($this->data[0]) ){
			$this->action = $this->data[0];
		}
	}

	public function __tostring(){
		return $this->url;
	}

	public static function FromAction($action, $data){
		$isCleanUrl = (bool)Settings::get('clean_url');
		$url = UCMS_DIR.($isCleanUrl ? "$action/$data" : "?action=$action".( !empty($data) ? "&key=$data" : "" ));
		$page = new self($url);
		return $page;
	}

	public function getAction(){
		return $this->action;
	}

	public function getActionData(){
		return $this->data;
	}

	public function go(){
		self::Redirect($this->url);
	}

	public function getURL(){
		return $this->url;
	}

	public function containsKey($name){
		return ( is_array($this->data) && in_array($name, $this->data) );
	}

	public function getKeyValue($name){
		if( $this->containsKey($name)){
			$index = array_search($name, $this->data);
			if( !empty($this->data[$index+1]) )
				return $this->data[$index+1];
		}
		return "";
	}

	public function getPageNumber(){
		return $this->getKeyValue('page');
	}

	public static function GetCurrent(){
		if( self::$currentPage === NULL ){
			self::$currentPage = new Page();
		}
		return self::$currentPage;
	}

	public static function Refresh($delay = 0){
		/**
		* @todo add params for keeping GET and keys or somewhat
		*/
		$url = $_SERVER['PHP_SELF'];
		if(headers_sent()){
			echo '<meta http-equiv="refresh" content="'.$delay.'">';
		}
		else{
			header("Refresh: $delay; url=$url");
			exit;
		}
	}

	public static function Redirect($url){
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
}
?>