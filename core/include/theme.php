<?php
class Theme extends Extention{
	private static $instance;
	private $title;

	public static function setCurrent($themeName){
		self::$instance = new self($themeName);
	}

	public static function getCurrent(){
		if ( is_null( self::$instance ) ){
			log_add(tr("Theme is not loaded"), UC_LOG_CRITICAL);
			return false;
		}
		return self::$instance;
	}

	protected function getFilePath($file){
		return THEMES_PATH."$this->name/$file";
	}

	protected function getExtentionInfoPath(){
		return $this->getFilePath(THEME_INFO);
	}

	public function getURLFilePath($file){
		return THEMES_URL_PATH."$this->name/$file";
	}

	public function loadTemplate($action){
		$this->includeFile($action.'.php');
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
}
?>