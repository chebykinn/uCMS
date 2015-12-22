<?php
namespace uCMS\Core;
use uCMS\Core\Admin\ControlPanel;
use uCMS\Core\uCMS;
class Page{
	const INDEX_ACTION = 'home';
	const OTHER_ACTION = 'other';
	const INSTALL_ACTION = 'install';
	private $url;
	private $status;
	private $action = self::INDEX_ACTION;
	private $data;
	private $host;
	private $path;
	private $query = "";
	private static $currentPage;

	public function __construct($url = ""){
		if( $url == "" ){
			$this->url = urldecode($_SERVER['REQUEST_URI']);
		}else{
			$this->url = urldecode($url);
		}
		$url = parse_url($this->url);
		if( isset($url['host']) ){
			$this->host = $url['host'];
		}else{
			$this->host = $_SERVER['SERVER_NAME'];
		}

		if( isset($url['query']) ){
			$this->query = $url['query'];
		}

		$this->path = $url['path'];

		$this->path = preg_replace('@/+@', '/', $this->path);
		$rawData = str_replace(uCMS::GetDirectory(), "/", $this->path);
		$data = array();
		parse_str ( $this->query, $data );
		if( empty($data['action']) ){
			$this->data = preg_split('#(/)#', $rawData, -1, PREG_SPLIT_NO_EMPTY);
		}else{
			$this->data[0] = $data['action'];
			if( !empty($data['key']) ){
				$this->data = preg_split('#(/)#', $data['key'], -1, PREG_SPLIT_NO_EMPTY);
				array_unshift($this->data, $data['action']);
			}
		}

		if( !empty($this->data[0]) ){
			$this->action = $this->data[0];
		}
	}

	public function __tostring(){
		return $this->path.(!empty($this->query) ? '?'.$this->query : '');
	}

	public static function FromAction($action, $data = ""){
		$isCleanUrl = (bool)Settings::get(Settings::CLEAN_URL);
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
	* Create install page object.
	*
	* This method is an alias of Page::FromAction() called with INDEX_ACTION, this
	* will create link to the install page.
	*
	* @since 2.0
	* @param string $data Data for the install action
	* @return Page A install page object
	*/
	public static function Install($data = ""){
		return Page::FromAction(self::INSTALL_ACTION, $data);
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

	public function getActionValue(){
		return $this->getKeyValue($this->action);
	}

	public function getQuery($raw = false){
		if ($raw) return $this->query;
		$data = array();
		parse_str($this->query, $data);
		return $data;
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

	public function getHost(){
		return $this->host;
	}

	public function getPath(){
		return $this->path;
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

	public function addAction($action){
		if( !$this->containsKey($action) ){
			$this->data[] = $action;
		}
		return false;
	}

	public static function GetCurrent(){
		if( self::$currentPage === NULL ){
			self::$currentPage = new self();
		}
		return self::$currentPage;
	}

	public static function Refresh($delay = 0, $noRedirect = false){
		/**
		* @todo add params for keeping GET and keys or somewhat
		*/
		$url = $_SERVER['REQUEST_URI'];
		// $url = strtok($url, '?'); // Remove all GET parameters
		if(headers_sent()){
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="'.$delay.';url='.$url.'">';
			echo '</noscript>';
		}
		else{
			if( $noRedirect ){
				header("Refresh: $delay; url=$url");
				exit;
			}else{
				// Added this to prevent browsers ask about resending form data
				Page::Redirect($url);
			}
		}
	}

	public static function Redirect($url){
		/**
		* @todo add some checks
		*/
		session_write_close();
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

	public static function GoBack(){
		if( isset($_SERVER['HTTP_REFERER']) and preg_match("#(".uCMS::GetDomain().")#", $_SERVER['HTTP_REFERER']) ){
			$backPage = new Page($_SERVER['HTTP_REFERER']);
			$backPath = (string)$backPage;
			$currentPath = (string)Page::GetCurrent();
			if( $backPath == $currentPath ){
				$backPage = Page::Home();
			}
		}else{
			$backPage = Page::Home();
		}
		$backPage->go();
	}

	public static function Check(){
		$isCleanUrl = (bool) Settings::Get(Settings::CLEAN_URL);

		if( $isCleanUrl && preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE']) ){
			if( !in_array('mod_rewrite', apache_get_modules()) ){
				Settings::Update(Settings::CLEAN_URL, 0);
			}
		}
	}
}
?>