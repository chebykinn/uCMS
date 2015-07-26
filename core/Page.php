<?php
namespace uCMS\Core;
use uCMS\Core\Admin\ControlPanel;
class Page{
	const INDEX_ACTION = 'home';
	const OTHER_ACTION = 'other';
	private $url;
	private $status;
	private $action = self::INDEX_ACTION;
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

	public static function FromAction($action, $data = ""){
		$isCleanUrl = (bool)Settings::get('clean_url');
		if( $action === self::INDEX_ACTION ) $action = "";
		$urlAction = "";
		$urlData = "";
		if( !empty($action) ){
			$urlAction = ($isCleanUrl ? "$action/" : "?action=$action");
		}

		if( !empty($data) ){
			$amp = empty($action) ? "?" : "&";
			$urlData = ($isCleanUrl ? "$data/" : $amp."key=$data");
		}

		$url = uCMS::GetDirectory().$urlAction.$urlData;
		$page = new self($url);
		return $page;
	}

	/**
	* Create home page object.
	*
	* This method is an alias of Page::FromAction() called with INDEX_ACTION, this
	* will create link to the home page.
	*
	* @since 2.0
	* @param string $data Data for the home action
	* @return Page A home page object
	*/
	public static function Home($data = ""){
		return Page::FromAction(self::INDEX_ACTION, $data);
	}

	/**
	* Create control panel page object.
	*
	* This method is an alias of Page::FromAction() called with admin action, this
	* will create link to the control panel page with certain $action.
	*
	* @since 2.0
	* @param string $action A control panel action
	* @return Page A control panel page object
	*/
	public static function ControlPanel($action = ""){
		return Page::FromAction(ControlPanel::ACTION, $action);
	}

	public function getAction(){
		return $this->action;
	}

	public function getActionData(){
		$data = $this->data;
		unset($data[0]);
		$data = implode('/', $data);
		return $data;
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
			self::$currentPage = new self();
		}
		return self::$currentPage;
	}

	public static function Refresh($delay = 0){
		/**
		* @todo add params for keeping GET and keys or somewhat
		*/
		$url = $_SERVER['PHP_SELF'];
		if(headers_sent()){
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="'.$delay.';url='.$url.'">';
			echo '</noscript>';
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
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'">';
			echo '</noscript>';
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