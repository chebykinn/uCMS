<?php
class Theme extends Extension{
	private static $instance;
	private $title;
	private $action;

	public static function SetCurrent($themeName){
		self::$instance = new self($themeName);
	}

	public static function IsLoaded(){
		return !is_null( self::$instance );
	}

	public static function GetCurrent(){
		if ( is_null( self::$instance ) ){
			log_add(tr("Theme is not loaded"), UC_LOG_CRITICAL);
			return false;
		}
		return self::$instance;
	}

	protected function getPath(){
		return THEMES_PATH."$this->name/";
	}

	protected function getURLPath(){
		return THEMES_URL_PATH."$this->name/";
	}

	protected function getFilePath($file){
		if( !empty($file) ){
			return THEMES_PATH."$this->name/$file";
		}
		return "";
	}

	protected function getExtensionInfoPath(){
		return $this->getFilePath(THEME_INFO);
	}

	public function getURLFilePath($file){
		return THEMES_URL_PATH."$this->name/$file";
	}

	public function load(){
		$this->includeFile(GENERAL_TEMPLATE_NAME.'.php');
	}

	public function loadTemplate($name){
		$this->includeFile($name.'.php');
	}

	public function loadBlock($name){
		$block = $this->getFilePath($this->getInfo($name));
		if( file_exists($block) ){
			$this->includeFile($this->getInfo($name));
		}
	}

	public function setTitle($title){
		$this->title = htmlspecialchars($title);
	}

	public function getTitle(){
		return $this->title;
	}

	public function setAction($action){
		$this->action = $action;
	}

	public function getAction(){
		return $this->action;
	}
}
?>